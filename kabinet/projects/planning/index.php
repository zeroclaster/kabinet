<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Заказ услуг и планирование задач");


$context = \Bitrix\Main\Application::getInstance()->getContext();
$server = $context->getServer();
$request = $context->getRequest();

$p = $request->get('p');
$user = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('user');
$user_id = $user->get('ID');
if ($p == null) LocalRedirect("/404.php");
$project = \Bitrix\Kabinet\project\datamanager\ProjectsTable::getlist([
    'select'=>['*'],
    'filter' => ['ID'=>$p,'UF_AUTHOR_ID' =>$user_id],
    'limit'=>1
])->fetch();
if (!$project) LocalRedirect("/404.php");

$APPLICATION->AddChainItem("Проект", "/kabinet/projects/?id=".$p);
$APPLICATION->AddChainItem("Заказ услуг и планирование задач", "");
?>




<section class="section-xs">
    <div class="container-fluid">
        <div class="d-flex no-d-flex justify-content-between">
            <div>
                <h4 style="margin: 0;">Проект: <?=$project['UF_NAME']?> #<?=$project['UF_EXT_KEY']?></h4>
                <div class="h1"><i class="fa fa-calendar" aria-hidden="true"></i> Заказ услуг и планирование</div>
            </div>
            <div class="pagehelp-button text-primary" data-component="pagehelp" data-code="PLANNING" style="margin-right: 15px;"><i class="fa fa-info-circle text-warning" aria-hidden="true"></i> Помощь</div>
        </div>
    </div>
</section>

<section class="">
    <div class="container-fluid">
        <?$APPLICATION->IncludeComponent("exi:page.help", "", Array(
                'CODE' => 'PLANNING',
            )
        );?>
    </div>
</section>

<section class="">
    <div class="container-fluid">
        <div class="row row-30">
            <div class="col-md-12">
                <?$APPLICATION->IncludeComponent("exi:task.list", "", Array(
                        'PROJECT' => $project['ID']
                    )
                );?>
            </div>
        </div>
    </div>
</section>

	  
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>