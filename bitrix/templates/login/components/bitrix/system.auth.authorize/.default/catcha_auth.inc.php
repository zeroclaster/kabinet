<?if($arResult["CAPTCHA_CODE"]):?>


    <div class="form-group">
        <input type="hidden" name="captcha_sid" value="<?echo $arResult["CAPTCHA_CODE"]?>" />
        <img src="/bitrix/tools/captcha.php?captcha_sid=<?echo $arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" />
    </div>


    <div class="form-group">
        <label for="capchaword"><?echo GetMessage("AUTH_CAPTCHA_PROMT")?>:</label>
        <input id="capchaword" class="form-control" type="text" name="captcha_word" maxlength="50" value="" size="15" autocomplete="off" />

    </div>
<?endif;?>