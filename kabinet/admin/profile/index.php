<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Профиль");



$user = (\KContainer::getInstance())->get('user');
?>
    <section class="section-xs">
        <div class="container-fluid">
            <div class="row row-30">
                <div class="col-md-12 d-xxl-flex">
                    <h1>Профиль пользователя</h1>
                </div>
            </div>
        </div>
    </section>


<section class="section-xs">
    <div class="container-fluid">
        <div class="row row-30">
            <div class="col-md-12">
                <?$APPLICATION->IncludeComponent("exi:profile.user", "admin", Array(
                    )
                );?>
            </div>
        </div>
    </div>
</section>

<?ob_start();?>

<?
$addScriptinPage = trim(ob_get_contents());
ob_end_clean();
$addscript = (\KContainer::getInstance())->get('addscript');
if (!$addscript) $addscript = [];
$addscript[] = $addScriptinPage;
(\KContainer::getInstance())->maked($addscript,'addscript');
?>

	  
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>