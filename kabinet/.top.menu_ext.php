<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
global $APPLICATION;

$projectManager = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Project');
$taskManager = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Task');

$project_list = $projectManager->getData();
$taskList = $taskManager->getData();

$aMenuLinksExt = [];

$usr_id_const = (\PHelp::isAdmin())? '&usr=' . $_REQUEST['usr'] : '';


if (!\PHelp::isAdmin()) {
$aMenuLinksExt[] = Array(
    "Проекты",
    "/kabinet/",
    Array(),
    Array(
        "ICON" => "fa fa-tachometer",
        "DEPTH_LEVEL" => 1
    ),
    ""
);
}

// Группируем задачи по проектам
$tasksByProject = [];
foreach($taskList as $task) {
    $projectId = $task['UF_PROJECT_ID'];
    if (!isset($tasksByProject[$projectId])) {
        $tasksByProject[$projectId] = [];
    }
    $tasksByProject[$projectId][] = $task;
}

foreach($project_list as $item) {
    $projectId = $item['ID'];
    $hasTasks = isset($tasksByProject[$projectId]) && !empty($tasksByProject[$projectId]);

    // Проект
    $aMenuLinksExt[] = Array(
        $item['UF_NAME'],
        "/kabinet/projects/planning/?p=" . $projectId . $usr_id_const,
        Array(),
        Array(
            "ICON" => "fa fa-folder-open",
            "DEPTH_LEVEL" => 1,
            "IS_PARENT" => $hasTasks
        ),
        ""
    );

    // Добавляем задачи проекта
    if ($hasTasks) {
        foreach($tasksByProject[$projectId] as $task) {
            $productImage = "";
            try {
                $productData = $taskManager->getProductByTask($task);
                if ($productData && !empty($productData['PREVIEW_PICTURE_SRC'])) {
                    $productImage = $productData['PREVIEW_PICTURE_SRC'];
                }
            } catch (Exception $e) {
                $productImage = "";
            }

            $aMenuLinksExt[] = Array(
                $task['UF_NAME'],
                "/kabinet/projects/reports/?t=" . $task['ID'] . $usr_id_const,
                Array(),
                Array(
                    "IMAGE" => $productImage,
                    "TASK_ID" => $task['ID'],
                    "PRODUCT_ID" => $task['UF_PRODUKT_ID'],
                    "DEPTH_LEVEL" => 2
                ),
                ""
            );
        }

        // Добавляем "+ Заказать услугу" в КОНЦЕ списка задач
        $aMenuLinksExt[] = Array(
            "Заказать услугу",
            "/kabinet/projects/planning/?p=" . $projectId . $usr_id_const,
            Array(),
            Array(
                "ICON" => "rd-navbar-icon mdi-plus",
                "DEPTH_LEVEL" => 2,
                "CLASS" => "menu-item-add-service",
                "STYLE" => "color: #2ecc71; font-weight: bold;",
                "ACTION" => "add_service"
            ),
            ""
        );
    }
}

if (!\PHelp::isAdmin()) {
$aMenuLinksExt[] = Array(
    "Новый проект",
    "/kabinet/projects/breif/",
    Array(),
    Array(
        "ICON" => "mdi-plus",
        "DEPTH_LEVEL" => 1
    ),
    ""
);
}

$aMenuLinks = array_merge($aMenuLinks, $aMenuLinksExt);
?>