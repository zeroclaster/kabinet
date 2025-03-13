https://fontawesome.com/v4/icons/


<a class="btn btn-sm btn-danger fa-sign-out icon-button"></a>
<a class="btn btn-primary mdi-menu-right icon-button-right" href="/kabinet/projects/planning/?p=28">планирование</a>

        // Если у пользователя уже были компании (хотя это врят ли, он новый!)
        $arUF = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("USER", $this->user);
		if (!is_array($arUF['UF_MYCOMPANY_ID']['VALUE'])) $arUF['UF_MYCOMPANY_ID']['VALUE'] = [];	
        $newValue = array_unique(
            array_merge($arUF['UF_MYCOMPANY_ID']['VALUE'], [$id])
        );

        $GLOBALS["USER_FIELD_MANAGER"]->Update("USER", $this->user, ['UF_MYCOMPANY_ID' => $newValue]);



$config = (\KContainer::getInstance())->get('config');

...BX.Vue3.Pinia.mapState(usekabinetStore, ['config']),


-------------------------------------------------------------
$localStorage = \Bitrix\Main\Application::getInstance()->getLocalSession('dataKabinet');
if (!isset($localStorage['orderData'])) {
$localStorage->set('orderData', $data);
}else{
$data = $localStorage->get('orderData');
}

--------------------------------------------------------------------------
создать массив из реактивного массива
this.$root.defaultdatatask = JSON.parse(JSON.stringify(this.datatask));

----------------------------------------------------------------------------------

if (window.location.hash) document.querySelector(window.location.hash).scrollIntoView({behavior: 'smooth'});

------------------------------------------------------------------------------------

Reactive clone of a ref. By default, it use JSON.parse(JSON.stringify()) to do the clone.


--------------------------------------------------------------------------------------
    $cache = new \CPHPCache();
    $cache->CleanDir('portal/bannerblock');

