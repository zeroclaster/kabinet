<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if($arResult["SHOW_FORM"]):?>
    <section class="section-lg section-one-screen">
    <div class="container">
    <div class="row justify-content-center">
    <div class="col-lg-8 col-xl-6">
    <div class="row align-items-end">
        <div class="col-12 text-right"><a href="<?=$arResult["AUTH_AUTH_URL"]?>"><?=GetMessage("AUTH_AUTH")?></a></div>
    </div>
    <div class="panel">
    <div class="panel-header">
        <p><?echo $arResult["MESSAGE_TEXT"]?></p>
    </div>
    <div class="panel-body">
<?//here you can place your own messages
	switch($arResult["MESSAGE_CODE"])
	{
	case "E01":
		?><? //When user not found
		break;
	case "E02":
		?><? //User was successfully authorized after confirmation
		break;
	case "E03":
		?><? //User already confirm his registration
		break;
	case "E04":
		?><? //Missed confirmation code
		break;
	case "E05":
		?><? //Confirmation code provided does not match stored one
		break;
	case "E06":
		?><? //Confirmation was successfull
		break;
	case "E07":
		?><? //Some error occured during confirmation
		break;
	}
?>
	<form method="post" action="<?echo $arResult["FORM_ACTION"]?>">
        <div class="form-group">
            <label for="field1"><?echo GetMessage("CT_BSAC_LOGIN")?></label>
            <input type="text" name="<?echo $arParams["LOGIN"]?>" maxlength="50" value="<?echo $arResult["LOGIN"]?>" size="17" class="form-control" id="field1" aria-describedby="field1Help">
            <small id="field1Help" class="form-text text-muted"></small>
        </div>

        <div class="form-group">
            <label for="field2"><?echo GetMessage("CT_BSAC_CONFIRM_CODE")?></label>
            <input type="text" name="<?echo $arParams["CONFIRM_CODE"]?>" maxlength="50" value="<?echo $arResult["CONFIRM_CODE"]?>" size="17" class="form-control" id="field2" aria-describedby="field2Help">
            <small id="field2Help" class="form-text text-muted"></small>
        </div>

        <div class="form-group">
				<input class="btn btn-danger" type="submit" value="<?echo GetMessage("CT_BSAC_CONFIRM")?>" />
        </div>
		<input type="hidden" name="<?echo $arParams["USER_ID"]?>" value="<?echo $arResult["USER_ID"]?>" />
	</form>
    </div>
    </div>
    </div>
    </div>
    </div>
</section>
<?elseif(!$USER->IsAuthorized()):?>
	<?$APPLICATION->IncludeComponent("bitrix:system.auth.authorize", "", array());?>
<?endif?>
