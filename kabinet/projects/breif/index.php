<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Бриф проекта");
?>
<?
$user = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('user');
?>
    <section class="">
        <div class="container-fluid">
            <div class="d-flex justify-content-between">
                <div class="pagehelp-button text-primary" data-component="pagehelp" data-code="BRIEF" style="margin-right: 15px;"><i class="fa fa-info-circle text-warning" aria-hidden="true"></i> Помощь</div>
            </div>
        </div>
    </section>

    <section class="">
        <div class="container-fluid">
            <?$APPLICATION->IncludeComponent("exi:page.help", "", Array(
                    'CODE' => 'BRIEF',
                )
            );?>
        </div>
    </section>

<?$APPLICATION->IncludeComponent("exi:form.brief", "", Array(
		"ID" => $_REQUEST['id'],
        "GROUPS" =>[
            0=>"Общая информация о проекте",
            1=>"Информация о компании и бренде",
            2=>"Подробности для отзывов об услугах или товаров",
            3=>"Целевая аудитория",
            4=>"Дополнительно"
        ],
        "GROUP0"=>[
            0=>"HLBLOCK_4_UF_NAME",
			1=>"HLBLOCK_8_UF_PROJECT_GOAL",
            2=>"HLBLOCK_8_UF_TOPICS_LIST",
        ],
        "GROUP1"=>[
            0=>"HLBLOCK_8_UF_SITE",
            1=>"HLBLOCK_8_UF_OFFICIAL_NAME",
            2=>"HLBLOCK_8_UF_REVIEWS_NAME",
            3=>"HLBLOCK_8_UF_CONTACTS_PUBLIC",
            4=>"HLBLOCK_8_UF_COMP_PREVIEW_TEXT",
            5=>"HLBLOCK_8_UF_COMP_DESCRIPTION_TEXT",
            6=>"HLBLOCK_8_UF_COMP_LOGO",
            7=>"HLBLOCK_8_UF_ORG_ADDRESS",
            8=>"HLBLOCK_8_UF_WORKING_HOURS",
        ],
        "GROUP2"=>[
            0=>"HLBLOCK_9_UF_ABOUT_REVIEW",
            1=>"HLBLOCK_9_UF_POSITIVE_SIDES",
            2=>"HLBLOCK_9_UF_MINUSES",
            3=>"HLBLOCK_9_UF_MINUSES_USER",
            4=>"HLBLOCK_9_UF_ORDER_PROCESS",
            5=>"HLBLOCK_9_UF_ORDER_PROCESS_USER",
            6=>"HLBLOCK_9_UF_EXAMPLES_REVIEWS",
            7=>"HLBLOCK_9_UF_MENTION_REVIEWS",
            8=>"HLBLOCK_9_UF_KEYWORDS",
        ],
        "GROUP3"=>[
              0=>"HLBLOCK_12_UF_TARGET_AUDIENCE",
              1=>"HLBLOCK_12_UF_COUNTRY",
              2=>"HLBLOCK_12_UF_REGION",
              3=>"HLBLOCK_12_UF_CITY",
              4=>"HLBLOCK_12_UF_RATIO_GENDERS",
        ],
        "GROUP4"=>[
            0=>"HLBLOCK_4_UF_ADDITIONAL_WISHES",
        ],
        "HB_ID" => 4,
        "FIELDS" => 'ALL',
        "QUERY_VARIABLE" => "sourcehtml",
        "QUERYVARIABLE" => ["sourcehtml"],
    )
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>