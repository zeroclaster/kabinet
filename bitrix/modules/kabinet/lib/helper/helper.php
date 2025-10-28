<?
namespace Bitrix\Kabinet\helper;

use Bitrix\Main\Application,
    Bitrix\Kabinet\helper\Gbstorage;

class Helper extends Datesite{

	static function userName($data){

		return current(array_filter([
            implode(" ", [$data['LAST_NAME'], $data['NAME'], $data['SECOND_NAME']]),
            $data['LOGIN']
        ]));
	}

    static function uniqueId(){
        $container = \KContainer::getInstance();
        $uniqueid = $container->getArgs('unique_counter');

        // first instalizate!
        if (!$uniqueid)
            $uniqueid = 1;
        else
            $uniqueid = $uniqueid + 1;

        $container->setArgs($uniqueid,'unique_counter');
        return $uniqueid;
    }

    static function getElementByField(array $data, $id, $fieldname = 'ID'){
	    if ($id === NULL) return [];
        $key = array_search($id, array_column($data, 'ID'));
        if ($key === false) return [];

        return $data[$key];
    }

    static function isAdmin(){
	    global $USER;

	    if (!is_object($USER))
            return false;

        if (!$USER->IsAuthorized())
            return false;

        $id = $USER->GetID();
        $GroupArray = \CUser::GetUserGroup($id);

        return !empty(array_intersect([MANAGER], $GroupArray));
    }

    static function usersGroup($groupId){
	    $users = [];

        $dbUsers = \CUser::GetList(
            'ID', 'ASC',
            ['GROUPS_ID' => [$groupId]], // фильтр по группе
            ['SELECT' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN', 'EMAIL']]
        );

        while ($user = $dbUsers->Fetch()) {
            $users[] = [
                'ID' => $user['ID'],
                'NAME' => trim($user['NAME'] . ' ' . $user['SECOND_NAME'] . ' ' . $user['LAST_NAME']),
                'LOGIN' => $user['LOGIN'],
                'EMAIL' => $user['EMAIL']
            ];
        }
        return $users;
    }

    static function array_value_recursive($key, array $arr){
        $val = array();
        array_walk_recursive($arr, function($v, $k) use($key, &$val){
            if($k == $key) array_push($val, $v);
        });
        return count($val) > 1 ? $val : array_pop($val);
    }


    static function array_keys_multi(array $array,$find_key)
    {
        $result=[];

        foreach ($array as $key => $value) {

            if ($find_key == $key) return $value;

            if (is_array($value)) {
                $r = self::array_keys_multi($value,$find_key);
                if ($r) return $r;
            }
        }

        return $result;
    }


    /**
     * Рекурсивно находит все JS файлы в папке шаблона кабинета, исключая указанные директории.

    Возвращает: Массив с информацией о каждом JS файле:
    name - имя файла
    path - относительный путь от папки шаблона
    full_path - полный путь к файлу
    timestamp - комбинированная метка (время изменения + размер файла)

    Особенности:
    Использует кеширование на 4 часа для повышения производительности
    Исключает папки assets/components/ и components/bitrix/
    Рекурсивный поиск во всех подпапках шаблона
     *
     * @return array Массив с информацией о JS файлах
     */
    static function getTemplateJsFiles() {
        // Настройка кеширования через константу
        $cacheEnabled = defined('TEMPLATE_JS_FILES_CACHE_TTL') ? TEMPLATE_JS_FILES_CACHE_TTL : 4 * 3600;

        // Если TTL = 0, работаем без кеша
        if ($cacheEnabled !== 0) {
            // Ключ для кеша
            $cacheKey = 'template_js_files_' . md5(__METHOD__);
            $cacheTtl = $cacheEnabled;

            // Пытаемся получить данные из кеша
            $cache = \Bitrix\Main\Data\Cache::createInstance();
            if ($cache->initCache($cacheTtl, $cacheKey, '/template/js_files/')) {
                return $cache->getVars();
            }

            // Если кеша нет, инициализируем буфер
            $cache->startDataCache();
        }

        $jsFiles = array();

        // Путь к папке шаблона
        $templatePath = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/templates/kabinet/';

        // Проверяем существование папки
        if (!file_exists($templatePath) || !is_dir($templatePath)) {
            if (isset($cache)) {
                $cache->endDataCache($jsFiles);
            }
            return $jsFiles;
        }

        // Пути для исключения
        $excludePaths = [
            $templatePath . 'assets/components/',
            $templatePath . 'components/bitrix/'
        ];

        // Создаем рекурсивный итератор для поиска во всех подпапках
        $directoryIterator = new \RecursiveDirectoryIterator($templatePath);
        $iterator = new \RecursiveIteratorIterator($directoryIterator);

        // Фильтруем только JS файлы
        $jsIterator = new \RegexIterator($iterator, '/^.+\.js$/i', \RecursiveRegexIterator::GET_MATCH);

        foreach ($jsIterator as $file) {
            $fullPath = $file[0];

            // Пропускаем файлы из исключенных папок
            $excludeFile = false;
            foreach ($excludePaths as $excludePath) {
                if (strpos($fullPath, $excludePath) === 0) {
                    $excludeFile = true;
                    break;
                }
            }

            if ($excludeFile) {
                continue;
            }

            // Получаем относительный путь от папки шаблона
            $relativePath = str_replace($templatePath, '', $fullPath);

            // Получаем имя файла
            $fileName = basename($fullPath);

            $jsFiles[] = array(
                'name' => $fileName,
                'path' => $relativePath,
                'full_path' => $fullPath,
                'timestamp' => filemtime($fullPath) . filesize($fullPath)
            );
        }

        // Сохраняем результат в кеш (если кеширование включено)
        if (isset($cache)) {
            $cache->endDataCache($jsFiles);
        }

        return $jsFiles;
    }

    /**
        Группирует JS файлы по имени и суммирует их временные метки.

        Возвращает: Ассоциативный массив, где:
        Ключ - имя файла
        Значение - суммарная временная метка всех файлов с таким именем

        Особенности:
        Использует данные из getTemplateJsFiles()
        Объединяет временные метки файлов-дубликатов
        Полезен для создания уникальных идентификаторов версий файлов
    */
    static function getTemplateJsFilesWithSummedTimestamps() {
        // Получаем все JS файлы из основного метода
        $jsFiles = self::getTemplateJsFiles();

        $result = array();

        foreach ($jsFiles as $fileInfo) {
            $fileName = $fileInfo['name'];
            $timestamp = $fileInfo['timestamp'];

            // Если файл с таким именем уже есть в результате, суммируем временные метки
            if (isset($result[$fileName])) {
                $result[$fileName] += $timestamp;
            } else {
                $result[$fileName] = $timestamp;
            }
        }

        return $result;
    }
}