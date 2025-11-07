<?php
// Функция для получения названия статуса
function getStatusTitle($runner, $statusList) {
    foreach ($statusList as $status) {
        if ($status['ID'] == $runner['UF_STATUS']) {
            return $status['TITLE'];
        }
    }
    return '';
}

// Функция для получения имени ответственного
function getResponsibleName($responsibleData, $adminList) {
    if (!$responsibleData) return '';

    try {
        $data = json_decode($responsibleData, true);
        if (is_array($data) && count($data) > 0) {
            $lastResponsible = end($data);
            $responsibleId = $lastResponsible['id'];

            foreach ($adminList as $admin) {
                if ($admin['id'] == $responsibleId) {
                    return $admin['value'];
                }
            }
        }
    } catch (Exception $e) {
        // В случае ошибки парсинга возвращаем пустую строку
    }

    return '';
}

// Функция для получения первой ссылки на фото
function getFirstPhoto($photos) {
    if (!is_array($photos) || empty($photos)) return '';

    $firstPhoto = reset($photos);
    return $firstPhoto['SRC'] ?? '';
}

// Функция для поиска в массиве по ID (аналог findinArrayByID из Vue)
function findInArrayById($findArray, $needle) {
    if (!is_array($findArray)) {
        return null;
    }

    foreach ($findArray as $item) {
        if (isset($item['ID']) && $item['ID'] == $needle) {
            return $item;
        }
    }

    return null;
}

// Функция для получения значения поля с использованием оригинальных данных
function getFieldValueWithOriginal($taskData, $fieldName) {
    if (!isset($taskData[$fieldName])) {
        return '';
    }

    $fieldValue = $taskData[$fieldName];
    $originalFieldName = $fieldName . '_ORIGINAL';

    // Если есть оригинальные данные, ищем значение в них
    if (isset($taskData[$originalFieldName]) && is_array($taskData[$originalFieldName])) {
        $foundItem = findInArrayById($taskData[$originalFieldName], $fieldValue);
        if ($foundItem && isset($foundItem['VALUE'])) {
            return $foundItem['VALUE'];
        }
    }

    return $fieldValue;
}

// Функция для подготовки данных исполнений
function prepareExecutionsData($arResult, $arParams, $runnerManager) {
    $executionsData = [];

    foreach ($arResult["RUNNER_DATA"] as $runner) {
        $taskId = $runner['UF_TASK_ID'];
        $authorId = $arResult["TASK_DATA"][$taskId]['UF_AUTHOR_ID'] ?? 0;
        $projectId = $arResult["TASK_DATA"][$taskId]['UF_PROJECT_ID'] ?? 0;

        $taskData = $arResult["TASK_DATA"][$taskId] ?? [];

        // Получаем список доступных статусов для этого исполнения
        //$statusList = $runnerManager->allowStates($runner);

        $statusList = $runnerManager->getStatus($runner['UF_ELEMENT_TYPE']);

        $execution = [
            'id' => $runner['ID'],
            'planned_date' => $runner['UF_PLANNE_DATE_ORIGINAL']['FORMAT1'] ?? '',
            'client' => $arResult["CLIENT_DATA"][$authorId]['PRINT_NAME'] ?? '',
            'project' => $arResult["PROJECT_DATA"][$projectId]['UF_NAME'] ?? '',
            'task' => $arResult["TASK_DATA"][$taskId]['UF_NAME'] ?? '',
            'created_date' => $arResult["TASK_DATA"][$taskId]['UF_PUBLISH_DATE_ORIGINAL']['FORMAT1'] ?? '',
            'completion_date' => $arResult["TASK_DATA"][$taskId]['UF_DATE_COMPLETION_ORIGINAL']['FORMAT1'] ?? '',
            'coordination' => getFieldValueWithOriginal($taskData, 'UF_COORDINATION'),
            'reporting' => getFieldValueWithOriginal($taskData, 'UF_REPORTING'),
            'process_type' => getFieldValueWithOriginal($taskData, 'UF_CYCLICALITY'),
            'link' => $runner['UF_LINK'] ?? '',
            'photo' => getFirstPhoto($runner['UF_PIC_REVIEW_ORIGINAL'] ?? []),
            // Обработка review_text: очистка от HTML и обрезка до 100 символов
            'review_text' => !empty($runner['UF_REVIEW_TEXT']) ?
                mb_substr(strip_tags($runner['UF_REVIEW_TEXT']), 0, 1000) : '',
            'status' => getStatusTitle($runner, $statusList),
            'responsible' => getResponsibleName($runner['UF_RESPONSIBLE'] ?? '', $arParams["ADMINLIST"]),
            'publication_date' => $runner['UF_ACTUAL_DATE_ORIGINAL']['FORMAT1'] ?? '',
            'account_name' => '',
            'login' => '',
            'password' => '',
            'ip_address' => '',
            // Новые поля для отчетов
            'UF_REPORT_LINK' => $runner['UF_REPORT_LINK'] ?? '',
            'UF_REPORT_SCREEN' => $runner['UF_REPORT_SCREEN'] ?? '',
            'UF_REPORT_FILE' => $runner['UF_REPORT_FILE'] ?? '',
            'UF_REPORT_TEXT' => $runner['UF_REPORT_TEXT'] ?? ''
        ];

        // Парсим данные аккаунта
        if (!empty($runner['UF_SITE_SETUP'])) {
            try {
                $accountData = json_decode($runner['UF_SITE_SETUP'], true);
                if (is_array($accountData)) {
                    $execution['account_name'] = $accountData['accaunt'] ?? '';
                    $execution['login'] = $accountData['login'] ?? '';
                    $execution['password'] = $accountData['pass'] ?? '';
                    $execution['ip_address'] = $accountData['ip'] ?? '';
                }
            } catch (Exception $e) {
                // В случае ошибки парсинга оставляем пустые значения
            }
        }

        $executionsData[] = $execution;
    }

    return $executionsData;
}