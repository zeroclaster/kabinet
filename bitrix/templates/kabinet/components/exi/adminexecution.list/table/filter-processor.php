<?php
// filter-processor.php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

function processAppliedFilters($arParams, $arResult, $runnerManager) {
    $appliedFilters = [];
    $filterLabels = [
        'executionidsearch' => 'ID исполнения',
        'planedaterangesearchfrom' => 'Плановая дата от',
        'planedaterangesearchto' => 'Плановая дата до',
        'publicdatefromsearch' => 'Дата публикации от',
        'publicdatetosearch' => 'Дата публикации до',
        'statusexecutionsearch' => 'Статус',
        'accountsearch' => 'Имя аккаунта',
        'loginsearch' => 'Логин',
        'ipsearch' => 'IP размещения',
        'responsibleidsearch' => 'Ответственный',
        'clientidsearch' => 'Клиент',
        'clienttextsearch' => 'Клиент',
        'projectidsearch' => 'Проект',
        'projecttextsearch' => 'Проект',
        'taskidsearch' => 'Задача',
        'tasktextsearch' => 'Задача',
        'attention' => 'Требует внимания'
    ];

    // Получаем список статусов из runnerManager
    $statusList = $runnerManager->getStatusList();

    // Обрабатываем фильтры
    foreach ($arParams['FILTER'] as $key => $value) {
        if (!empty($value) && isset($filterLabels[$key])) {
            $displayValue = '';

            switch ($key) {
                case 'statusexecutionsearch':
                    if (is_array($value)) {
                        $statusValues = [];
                        foreach ($value as $statusId) {
                            // Используем реальные названия статусов из runnerManager
                            $statusValues[] = $statusList[$statusId] ?? $statusId;
                        }
                        $displayValue = implode(', ', $statusValues);
                    } else {
                        // Обработка одиночного значения
                        $displayValue = $statusList[$value] ?? $value;
                    }
                    break;

                case 'attention':
                    $attentionLabels = [
                        'clientattention' => 'Клиента',
                        'adminattention' => 'Администратора',
                        'hitchstade' => 'С просроченными стадиями'
                    ];
                    $displayValue = $attentionLabels[$value] ?? $value;
                    break;

                case 'responsibleidsearch':
                    if ($value == 0) {
                        $displayValue = 'Не задано';
                    } else {
                        $responsibleName = '';
                        foreach ($arParams['ADMINLIST'] as $admin) {
                            if ($admin['id'] == $value) {
                                $responsibleName = $admin['value'];
                                break;
                            }
                        }
                        $displayValue = $responsibleName ?: $value;
                    }
                    break;

                case 'clientidsearch':
                    $displayValue = getClientDisplayValue($value, $arResult);
                    break;

                case 'projectidsearch':
                    $displayValue = getProjectDisplayValue($value, $arResult);
                    break;

                case 'taskidsearch':
                    $displayValue = getTaskDisplayValue($value, $arResult);
                    break;

                default:
                    $displayValue = is_array($value) ? implode(', ', $value) : $value;
            }

            if ($displayValue) {
                $appliedFilters[] = [
                    'label' => $filterLabels[$key],
                    'value' => $displayValue
                ];
            }
        }
    }

    return $appliedFilters;
}

// Функция для получения отображаемого значения клиента
function getClientDisplayValue($clientId, $arResult) {
    if (isset($arResult["CLIENT_DATA"][$clientId])) {
        $client = $arResult["CLIENT_DATA"][$clientId];
        return $client['PRINT_NAME'] ?? formatClientName($client);
    } else {
        // Если данных нет в arResult, пытаемся получить из базы
        return getClientNameFromDB($clientId);
    }
}

// Функция для получения отображаемого значения проекта
function getProjectDisplayValue($projectId, $arResult) {
    if (isset($arResult["PROJECT_DATA"][$projectId])) {
        $project = $arResult["PROJECT_DATA"][$projectId];
        return formatProjectName($project);
    } else {
        // Если данных нет в arResult, пытаемся получить из базы
        return getProjectNameFromDB($projectId);
    }
}

// Функция для получения отображаемого значения задачи
function getTaskDisplayValue($taskId, $arResult) {
    if (isset($arResult["TASK_DATA"][$taskId])) {
        $task = $arResult["TASK_DATA"][$taskId];
        return formatTaskName($task);
    } else {
        // Если данных нет в arResult, пытаемся получить из базы
        return getTaskNameFromDB($taskId);
    }
}

// Форматирование имени клиента (аналогично getclientsAction)
function formatClientName($client) {
    $userName = current(array_filter([
        trim(implode(" ", [$client['LAST_NAME'], $client['NAME'], $client['SECOND_NAME']])),
        $client['LOGIN']
    ]));

    return $userName .' '. $client['EMAIL']. ' (ID'.$client['ID'].')';
}

// Форматирование имени проекта (аналогично getprojectAction)
function formatProjectName($project) {
    return $project['UF_NAME']. ' (#'.$project['UF_EXT_KEY'].')';
}

// Форматирование имени задачи (аналогично gettaskAction)
function formatTaskName($task) {
    return $task['UF_NAME']. ' (#'.$task['UF_EXT_KEY'].')';
}

// Получение имени клиента из базы данных
function getClientNameFromDB($clientId) {
    try {
        $clientData = \Bitrix\Kabinet\UserTable::getlist([
            'select' => ['ID', 'LOGIN', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL'],
            'filter' => ['ID' => $clientId],
            'limit' => 1
        ])->fetch();

        if ($clientData) {
            return formatClientName($clientData);
        }
    } catch (Exception $e) {
        // В случае ошибки возвращаем ID
    }

    return $clientId;
}

// Получение имени проекта из базы данных
function getProjectNameFromDB($projectId) {
    try {
        $projectData = \Bitrix\Kabinet\project\datamanager\ProjectsTable::getlist([
            'select' => ['ID', 'UF_NAME', 'UF_EXT_KEY'],
            'filter' => ['ID' => $projectId],
            'limit' => 1
        ])->fetch();

        if ($projectData) {
            return formatProjectName($projectData);
        }
    } catch (Exception $e) {
        // В случае ошибки возвращаем ID
    }

    return $projectId;
}

// Получение имени задачи из базы данных
function getTaskNameFromDB($taskId) {
    try {
        $taskData = \Bitrix\Kabinet\task\datamanager\TaskTable::getlist([
            'select' => ['ID', 'UF_NAME', 'UF_EXT_KEY'],
            'filter' => ['ID' => $taskId],
            'limit' => 1
        ])->fetch();

        if ($taskData) {
            return formatTaskName($taskData);
        }
    } catch (Exception $e) {
        // В случае ошибки возвращаем ID
    }

    return $taskId;
}