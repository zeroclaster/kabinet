<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Бриф проекта");
?>
<?
$user = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('user');
?>

<section class="section-xs">
    <div class="container-fluid" id="kabinetcontent">

    </div>
</section>
<script type="text/html" id="kabinet-content">
    <h2>Бриф проекта</h2>
<?$APPLICATION->IncludeComponent("exi:form.maker", "brief_full", Array(
		"ID" => $_REQUEST['id'],
        "GROUPS" =>[
            0=>"Общая информация о проекте",
            1=>"Информация о компании и бренде",
            2=>"Подробности для отзывов об услугах или товаров",
            3=>"Целевая аудитория",
            4=>"Дополнительно"
        ],
        "GROUP0"=>[
            0=>"UF_NAME",
            1=>"UF_TOPICS_LIST",
        ],
        "GROUP1"=>[
            0=>"UF_PROJECT_GOAL",
            1=>"UF_SITE",
            2=>"UF_OFFICIAL_NAME",
            3=>"UF_REVIEWS_NAME",
            4=>"UF_CONTACTS_PUBLIC",
            5=>"UF_COMP_PREVIEW_TEXT",
            6=>"UF_COMP_DESCRIPTION_TEXT",
            7=>"UF_COMP_LOGO",
            8=>"UF_ORG_ADDRESS",
            9=>"UF_WORKING_HOURS",
        ],
        "GROUP2"=>[
            0=>"UF_ABOUT_REVIEW",
            1=>"UF_POSITIVE_SIDES",
            2=>"UF_MINUSES",
            3=>"UF_MINUSES_USER",
            4=>"UF_ORDER_PROCESS",
            5=>"UF_ORDER_PROCESS_USER",
            6=>"UF_EXAMPLES_REVIEWS",
            7=>"UF_MENTION_REVIEWS",
            8=>"UF_KEYWORDS",
        ],
        "GROUP3"=>[
              0=>"UF_TARGET_AUDIENCE",
              1=>"UF_COUNTRY",
              2=>"UF_REGION",
              3=>"UF_CITY",
              4=>"UF_RATIO_GENDERS",
        ],
        "GROUP4"=>[
            0=>"UF_ADDITIONAL_WISHES",
        ],
        "HB_ID" => BRIEF,
        "FIELDS" => 'ALL',
        "QUERY_VARIABLE" => "sourcehtml",
        "QUERYVARIABLE" => ["sourcehtml"],
    )
);?>

</script>



<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>