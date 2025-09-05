<?php
// Подключаем ядро Битрикс
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');


// ID Highload-блока
$hlblockId = 21;

// Получаем информацию о Highload-блоке
$hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($hlblockId)->fetch();
if (!$hlblock) {
    die("Highload-блок с ID {$hlblockId} не найден.");
}

// Получаем класс сущности
$entity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
$entityClass = $entity->getDataClass();

echo "Начинаем удаление записей из Highload-блока: " . $hlblock['NAME'] . "<br>";

try {
    // Получаем все записи
    $records = $entityClass::getList([
        'select' => ['ID'],
        'order' => ['ID' => 'ASC']
    ]);

    $deletedCount = 0;

    // Удаляем записи по одной
    while ($record = $records->fetch()) {
        $deleteResult = $entityClass::delete($record['ID']);

        if ($deleteResult->isSuccess()) {
            $deletedCount++;
            echo "Удалена запись ID: " . $record['ID'] . "<br>";
        } else {
            echo "Ошибка при удалении записи ID: " . $record['ID'] . " - " . implode(', ', $deleteResult->getErrorMessages()) . "<br>";
        }

        // Чтобы не перегружать сервер при большом количестве записей
        if ($deletedCount % 100 === 0) {
            sleep(1);
        }
    }

    echo "<br><strong>Удаление завершено. Всего удалено записей: " . $deletedCount . "</strong>";

} catch (Exception $e) {
    echo "Произошла ошибка: " . $e->getMessage();
}
?>