<?
use Bitrix\Main\Localization\Loc,
    \Bitrix\Main\SystemException;

if(!$USER->IsAdmin())
	return;

if (!\Bitrix\Main\Loader::includeModule('telegram'))
{
	return;
}

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/options.php");
Loc::loadMessages(__FILE__);

include_once(__DIR__.'/default_option.php');
$arDefaultValues['default'] = $telegram_default_option;


$arAllOptions = array(
    array("bottoken", "API-токен вашего бота", "", array("text", 100),true),
);

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "ib_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);



if($_SERVER["REQUEST_METHOD"]=="POST" && ($_POST['Update'] || $_POST['Apply'] || $_POST['RestoreDefaults'])>0 && check_bitrix_sessid())
{
	if($_POST['RestoreDefaults'] <> '')
	{
		$arDefValues = $arDefaultValues['default'];
		foreach($arDefValues as $key=>$value)
		{
			COption::RemoveOption("telegram", $key);
		}
	}
	else
	{
	    foreach($arAllOptions as $arOption)
		{
			$name=$arOption[0];
            $required = $arOption[4];
			$val=$_REQUEST[$name];

			if($arOption[3][0]=="checkbox" && $val!="Y")
				$val="N";

			if (is_array($val)) {
                $val = serialize($val);
            }

			if ($required && empty($val)) throw new SystemException("Поле ".$arOption[1]. ' обязательное для заполнения!');

            COption::SetOptionString("telegram", $name, $val, $arOption[1]);
		}
	}
	if($_POST['Update'] <> '' && $_REQUEST["back_url_settings"] <> '')
		LocalRedirect($_REQUEST["back_url_settings"]);
	else
		LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam());
}


$tabControl->Begin();
?>
<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($mid)?>&amp;lang=<?echo LANGUAGE_ID?>">
	<?$tabControl->BeginNextTab();?>
	<?
	foreach($arAllOptions as $arOption):
        $val = COption::GetOptionString("telegram", $arOption[0], $arOption[2]);

		$type = $arOption[3];

        if ($arOption[0] == "bottoken")
            echo  "<tr><td colspan=\"2\">Для подключения обработчика Telegram: <a target='_blank' href='https://api.telegram.org/bot{$val}/setWebhook?url=https://kupi-otziv.ru/telegram_bot_handler.php'>https://api.telegram.org/bot{$val}/setWebhook?url=https://kupi-otziv.ru/telegram_bot_handler.php</a></td></tr>";
        echo  "<tr><td colspan=\"2\">Узнать ссылку обработчика Telegram Битрикс24: <a target='_blank' href='https://api.telegram.org/bot{$val}/getWebhookInfo'>https://api.telegram.org/bot{$val}/getWebhookInfo</a></td></tr>";
        ?>
		<tr>
			<td width="40%" nowrap <?if($type[0]=="textarea") echo 'class="adm-detail-valign-top"'?>>
				<label for="<?echo htmlspecialcharsbx($arOption[0])?>"><?echo $arOption[1]?>:</label>
			<td width="60%">
				<?if($type[0]=="checkbox"):?>
					<input type="checkbox" id="<?echo htmlspecialcharsbx($arOption[0])?>" name="<?echo htmlspecialcharsbx($arOption[0])?>" value="Y"<?if($val=="Y")echo" checked";?>>
				<?elseif($type[0]=="text"):?>
					<input type="text" size="<?echo $type[1]?>" maxlength="255" value="<?echo htmlspecialcharsbx($val)?>" name="<?echo htmlspecialcharsbx($arOption[0])?>">
				<?elseif($type[0]=="textarea"):?>
					<textarea rows="<?echo $type[1]?>" cols="<?echo $type[2]?>" name="<?echo htmlspecialcharsbx($arOption[0])?>"><?echo htmlspecialcharsbx($val)?></textarea>
				<?elseif($type[0]=="selectbox"):?>
					<?
                    $val = unserialize($val);
                    ?>

                    <select name="<?echo htmlspecialcharsbx($arOption[0])?>[]" multiple>
                        <option value="0">Не выбрано</option>
                        <?
						foreach ($type[1] as $key => $value)
						{
							?><option value="<?= $key ?>"<?= (in_array($key,$val)) ? " selected" : "" ?>><?= $value ?></option><?
						}
						?>
					</select>
				<?endif?>
			</td>			
		</tr>
		
	<?endforeach?>


	<?$tabControl->Buttons();?>
	<input type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" title="<?=GetMessage("MAIN_OPT_SAVE_TITLE")?>" class="adm-btn-save">
	<input type="submit" name="Apply" value="<?=GetMessage("MAIN_OPT_APPLY")?>" title="<?=GetMessage("MAIN_OPT_APPLY_TITLE")?>">
	<?if($_REQUEST["back_url_settings"] <> ''):?>
		<input type="button" name="Cancel" value="<?=GetMessage("MAIN_OPT_CANCEL")?>" title="<?=GetMessage("MAIN_OPT_CANCEL_TITLE")?>" onclick="window.location='<?echo htmlspecialcharsbx(CUtil::addslashes($_REQUEST["back_url_settings"]))?>'">
		<input type="hidden" name="back_url_settings" value="<?=htmlspecialcharsbx($_REQUEST["back_url_settings"])?>">
	<?endif?>
	<input type="submit" name="RestoreDefaults" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="return confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>')" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
	<?=bitrix_sessid_post();?>
	<?$tabControl->End();?>
</form>