<?php
// field-settings.php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

// Массив с русскими названиями полей для отображения
$fieldLabels = [
    'id' => 'id',
    'UF_EXT_KEY' => '#id',
    'planned_date' => 'Плановая дата выполнения',
    'client' => 'Клиент',
    'project' => 'Проект',
    'task' => 'Задача',
    'created_date' => 'Дата создания',
    'completion_date' => 'Дата завершения',
    'coordination' => 'Согласование',
    'reporting' => 'Отчетность',
    'process_type' => 'Тип процесса',
    'link' => 'Ссылка',
    'photo' => 'Фото',
    'review_text' => 'Текст отзыва',
    'status' => 'Статус',
    'responsible' => 'Ответственный',
    'publication_date' => 'Дата публикации',
    'account_name' => 'Имя аккаунта',
    'login' => 'Логин',
    'password' => 'Пароль',
    'ip_address' => 'IP размещения',
    'UF_REPORT_LINK' => 'Ссылка отчета',
    'UF_REPORT_SCREEN' => 'Скриншот отчета',
    'UF_REPORT_FILE' => 'Файл отчета',
    'UF_REPORT_TEXT' => 'Текст отчета'
];

// Поля, которые можно редактировать
$editableFields = [
    'planned_date',
    'review_text',
    'responsible',
    'publication_date',
    'account_name',
    'login',
    'password',
    'ip_address',
    'UF_REPORT_LINK',
    'UF_REPORT_SCREEN',
    'UF_REPORT_FILE',
    'UF_REPORT_TEXT'
];