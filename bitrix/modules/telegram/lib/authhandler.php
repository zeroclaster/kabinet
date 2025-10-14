<?php
namespace Bitrix\telegram;

/**
 * Класс AuthHandler обрабатывает авторизацию пользователей через Telegram в системе Bitrix
 * Выполняет валидацию данных, поиск/создание пользователей, авторизацию и перенаправление
 */
class Authhandler
{
    /** @var string Токен Telegram бота */
    private $botToken;

    /** @var array Данные авторизации из Telegram */
    private $authData;

    /** @var array|null Данные пользователя Bitrix */
    private $user;

    /** @var bool Флаг нового пользователя */
    private $isNewUser = false;

    /** @var bool Флаг автоматического создания пользователей */
    private $autoCreateUsers = false;

    /**
     * Конструктор класса
     * @param array $authData Данные авторизации из Telegram
     * @param bool $autoCreateUsers Разрешить автоматическое создание пользователей
     */
    public function __construct($authData, $autoCreateUsers = false)
    {
        // Получаем токен бота из настроек Bitrix
        $botToken = \COption::GetOptionString("telegram", "bottoken", "");
        $this->botToken = $botToken;
        $this->authData = $authData;
        $this->autoCreateUsers = $autoCreateUsers;
    }

    /**
     * Основной метод авторизации
     * Обрабатывает все сценарии входа через Telegram
     */
    public function authenticate()
    {
        // Проверяем валидность данных
        if (!$this->validateAuthData()) {
            die('Неверные данные авторизации');
        }

        global $USER;

        // Если пользователь уже авторизован в Bitrix
        if ($USER->IsAuthorized()) {
            $this->updateTelegramId($USER->GetID());
            LocalRedirect('/kabinet/profile/');
            return;
        }

        // Ищем пользователя в системе
        $this->findUser();

        // Если пользователь найден - авторизуем
        if ($this->user) {
            $this->authorizeUser();
            LocalRedirect('/kabinet/');
            return;
        }

        // Если разрешено автоматическое создание пользователей
        if ($this->autoCreateUsers) {
            $this->createNewUser();
            $this->authorizeUser();
            $this->sendWelcomeMessage();
            LocalRedirect('/kabinet');
            return;
        }

        // Иначе перенаправляем на регистрацию
        $this->redirectToRegistration();
    }

    /**
     * Валидация данных авторизации Telegram
     * @return bool Результат проверки
     */
    private function validateAuthData()
    {
        $check_hash = $this->authData['hash'];
        unset($this->authData['hash']);

        // Формируем массив для проверки
        $data_check_arr = [];
        foreach ($this->authData as $key => $value) {
            $data_check_arr[] = $key . '=' . $value;
        }

        // Сортируем и формируем строку для хеширования
        sort($data_check_arr);
        $data_check_string = implode("\n", $data_check_arr);

        // Генерируем секретный ключ
        $secret_key = hash('sha256', $this->botToken, true);

        // Сравниваем хеши и проверяем срок действия
        $hash = hash_hmac('sha256', $data_check_string, $secret_key);
        return strcmp($hash, $check_hash) === 0 && (time() - $this->authData['auth_date']) <= 86400;
    }

    /**
     * Поиск пользователя по Telegram ID
     */
    private function findUser()
    {
        $filter = ["UF_TELEGRAM_ID" => $this->authData['id']];
        $rsUser = \CUser::GetList("ID", "DESC", $filter);
        $this->user = $rsUser->Fetch();
    }

    /**
     * Создание нового пользователя
     */
    private function createNewUser()
    {
        // Формируем логин из username или id
        $login = !empty($this->authData['username'])
            ? $this->authData['username']
            : 'tg_'.$this->authData['id'];

        // Генерируем случайный пароль
        $password = bin2hex(random_bytes(8));

        $user = new \CUser;
        $arFields = [
            "LOGIN" => $login,
            "NAME" => $this->authData['first_name'] ?? '',
            "LAST_NAME" => $this->authData['last_name'] ?? '',
            "EMAIL" => $login."@telegram.user", // Формируем временный email
            "PASSWORD" => $password,
            "CONFIRM_PASSWORD" => $password,
            "ACTIVE" => "Y",
            "GROUP_ID" => [2, 10], // Группы для нового пользователя
            "UF_TELEGRAM_ID" => $this->authData['id'] // Сохраняем Telegram ID
        ];

        // Добавляем пользователя
        $userId = $user->Add($arFields);
        if (!$userId) {
            die($user->LAST_ERROR);
        }

        // Обрабатываем фото профиля
        $this->handleUserPhoto($userId);
        $this->user = \CUser::GetByID($userId)->Fetch();
        $this->isNewUser = true;
    }

    /**
     * Перенаправление на страницу регистрации
     */
    private function redirectToRegistration()
    {
        // Сохраняем данные в сессию для формы регистрации
        $_SESSION['TELEGRAM_REGISTER_DATA'] = [
            'telegram_id' => $this->authData['id'],
            'first_name' => $this->authData['first_name'] ?? '',
            'last_name' => $this->authData['last_name'] ?? '',
            'username' => $this->authData['username'] ?? '',
            'photo_url' => $this->downloadPhotoForRegistrate(),
            'UF_TELEGRAM_ID' => $this->authData['id']
        ];

        LocalRedirect('/login/?register=yes&from=telegram');
    }

    /**
     * Обновление Telegram ID для пользователя
     * @param int $userId ID пользователя Bitrix
     */
    private function updateTelegramId($userId)
    {
        $user = new \CUser;
        $user->Update($userId, [
            "UF_TELEGRAM_ID" => $this->authData['id']
        ]);

        $this->user = \CUser::GetByID($userId)->Fetch();
    }

    /**
     * Обработка фото профиля из Telegram
     * @param int $userId ID пользователя Bitrix
     */
    private function handleUserPhoto($userId)
    {
        if (empty($this->authData['photo_url'])) {
            return;
        }

        $photoPath = $this->downloadPhoto($this->authData['photo_url'], $this->authData['id']);
        if ($photoPath) {
            $userObj = new \CUser;
            $userObj->Update($userId, [
                'PERSONAL_PHOTO' => CFile::MakeFileArray($photoPath)
            ]);
            unlink($photoPath); // Удаляем временный файл
        }
    }

    /**
     * Загрузка фото для регистрации
     * @return string Путь к временному файлу
     */
    private function downloadPhotoForRegistrate()
    {
        if (empty($this->authData['photo_url'])) {
            return '';
        }

        $photoPath = $this->downloadPhoto($this->authData['photo_url'], $this->authData['id']);
        if ($photoPath) return $photoPath;

        return '';
    }

    /**
     * Загрузка фото из Telegram
     * @param string $photoUrl URL фото
     * @param int $userId ID пользователя Telegram
     * @return string|false Путь к файлу или false при ошибке
     */
    private function downloadPhoto($photoUrl, $userId)
    {
        // Создаем временную директорию
        $tempDir = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/telegram_photos/';
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        // Формируем имя файла
        $tempFile = $tempDir . 'photo_' . $userId . '_' . time() . '.jpg';

        // Загружаем фото
        $ch = curl_init($photoUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $photoData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Сохраняем файл
        if ($httpCode === 200 && !empty($photoData)) {
            file_put_contents($tempFile, $photoData);
            return $tempFile;
        }

        return false;
    }

    /**
     * Авторизация пользователя в Bitrix
     */
    private function authorizeUser()
    {
        global $USER;
        $USER->Authorize($this->user['ID']);
    }

    /**
     * Отправка приветственного сообщения в Telegram
     */
    private function sendWelcomeMessage()
    {
        if (!$this->user['UF_TELEGRAM_ID']) {
            return;
        }

        $bot = new \Bitrix\telegram\Telegrambothandler($this->botToken);
        $bot->sendMessage(
            $this->user['UF_TELEGRAM_ID'],
            "Добро пожаловать! Вы успешно зарегистрировались через Telegram."
        );
    }
}