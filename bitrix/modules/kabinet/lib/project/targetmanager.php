<?php
namespace Bitrix\Kabinet\project;

use \Bitrix\Main\SystemException,
    \Bitrix\Kabinet\exceptions\ProjectException,
    \Bitrix\Kabinet\exceptions\TestException;

class Targetmanager extends \Bitrix\Kabinet\container\Abstracthighloadmanager {
    public $fieldsType = [
        "UF_TARGET_AUDIENCE"=>1,
        "UF_COUNTRY"=>1,
        "UF_REGION"=>1,
        "UF_CITY"=>1,
        "UF_RATIO_GENDERS"=>1,
    ];
    protected $user;

    public function __construct($user, $HLBCClass)
    {
        $this->user = $user;
        parent::__construct($HLBCClass);

        AddEventHandler("", "\Targetaudience::OnBeforeAdd", [$this,"OnBeforeAddHendler"]);
        AddEventHandler("", "\Targetaudience::OnBeforeUpdate", [$this,'clearCacheHandler'],100);
        AddEventHandler("", "\Targetaudience::OnBeforeDelete", [$this,"DeleteclearCache"]);
    }

    public function OnBeforeAddHendler($fields,$object)
    {
    }

    public function clearCacheHandler($id,$primary,$fields,$object,$oldData){
        global $CACHE_MANAGER;

        $project_id = $oldData['UF_PROJECT_ID'];

        $cacheId = '';
        $cacheId = SITE_ID."|".$cacheId;
        $cacheId .= "|".serialize(intval($project_id));

        $cache = new \CPHPCache;
        $cache->clean($cacheId, "kabinet/targetproject");
    }

    public function DeleteclearCache($id, $primary, $oldFields)
    {
        global $CACHE_MANAGER;

        $project_id = $oldFields['UF_PROJECT_ID'];

        $cacheId = '';
        $cacheId = SITE_ID."|".$cacheId;
        $cacheId .= "|".serialize(intval($project_id));

        $cache = new \CPHPCache;
        $cache->clean($cacheId, "kabinet/targetproject");
    }

    public function getData($project_id,$clear=false){
        global $CACHE_MANAGER;

        $cacheId = '';
        $cacheId = SITE_ID."|".$cacheId;
        $cacheId .= "|".serialize(intval($project_id));

        $cache = new \CPHPCache;
        if ($clear) $cache->clean($cacheId, "kabinet/targetproject");

        // for debugg
        //$cache->clean($cacheId, "kabinet/targetproject");

        // сколько времени кешировать
        $ttl = 14400;
        // hack: $ttl = 0 то не кешировать
        $listdata = [];
        if ($cache->StartDataCache($ttl, $cacheId, "kabinet/targetproject"))
        {
            $infoprojects = \Bitrix\Kabinet\project\datamanager\TargetAudienceTable::getlist([
                'select'=>['*'],
                'filter'=>['UF_PROJECT_ID'=>$project_id],
            ])->fetchAll();

            foreach ($infoprojects as $data) {
                $c = $this->convertData($data, $this->getUserFields());
                // используется в отоюражении календаря
                $listdata[$c['UF_PROJECT_ID']] = $c;
            }

            $cache->EndDataCache(array($listdata));
        }
        else
        {
            $vars = $cache->GetVars();
            $listdata = $vars[0];
        }

        return $listdata;
    }

    public function clearCache(){
        // Empty!
    }
}