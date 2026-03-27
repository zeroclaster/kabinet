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
------------------------------------------------------------------------
для JS
usr_id_const: usr_id_const ? '&usr=' + usr_id_const : '',
usr_id_const2: usr_id_const ? '?usr=' + usr_id_const : '',	

для PHP
$usr_id_const = (\PHelp::isAdmin())? '&usr=' . $_REQUEST['usr'] : '';
------------------------------------------------------------------------------
посмотреть все исполнения у которых нет задач
SELECT f.*
FROM b_kabinet_fulfillment f
LEFT JOIN b_kabinet_task t ON f.UF_TASK_ID = t.ID
WHERE t.ID IS NULL;

Если хочешь увидеть только количество таких записей:

sql
SELECT COUNT(*) as orphaned_count
FROM b_kabinet_fulfillment f
LEFT JOIN b_kabinet_task t ON f.UF_TASK_ID = t.ID
WHERE t.ID IS NULL;

SELECT f.*
FROM b_kabinet_fulfillment f
LEFT JOIN b_kabinet_task t ON f.UF_TASK_ID = t.ID
WHERE t.ID IS NULL
  AND f.UF_CREATE_DATE >= '2026-02-06 00:00:00';
-----------------------------------------------------------------------------
