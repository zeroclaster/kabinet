<?
global $USER;
$userId = $USER->GetID();
$aMenuLinks = Array();

// Пункты, которые нужно скрыть для пользователя 983
if ($userId != 983) {
    $aMenuLinks = array_merge($aMenuLinks, Array(
        Array(
            "Клиенты и проекты",
            "/kabinet/admin/",
            Array(),
            Array("ICON"=>"mdi-file-outline"),
            ""
        ),
        Array(
            "Исполнения",
            "/kabinet/admin/performances/",
            Array(),
            Array("ICON"=>"mdi-cart-outline"),
            ""
        ),
        Array(
            "Пользователи",
            "/kabinet/admin/users/",
            Array(),
            Array("ICON"=>"mdi-border-all"),
            ""
        ),
        Array(
            "Рассылка",
            "/kabinet/admin/malling/",
            Array(),
            Array("ICON"=>"mdi-border-all"),
            ""
        ),
        Array(
            "Все сообщения",
            "/kabinet/admin/notifications/",
            Array(),
            Array("ICON"=>"mdi-border-all"),
            ""
        ),
    ));
}

// Пункт "Финансы" - только для группы 13
$aMenuLinks[] = Array(
    "Финансы",
    "/kabinet/admin/finance/",
    Array(),
    Array("ICON"=>"mdi-border-all"),
    "CSite::InGroup(array(13))"
);

$aMenuLinks[] = Array(
    "Изменение баланса",
    "/kabinet/admin/balancecorrection/",
    Array(),
    Array("ICON"=>"mdi-border-all"),
    "CSite::InGroup(array(13))"
);
?>