<?php
namespace Bitrix\Kabinet\project;

use \Bitrix\Main\SystemException,
    \Bitrix\Kabinet\exceptions\ProjectException,
    \Bitrix\Kabinet\exceptions\TestException;

class Infomanager extends \Bitrix\Kabinet\container\Abstracthighloadmanager {
    public $fieldsType = [
        "UF_TOPICS_LIST"=>1,
        "UF_PROJECT_GOAL"=>1,
        "UF_SITE"=>1,
        "UF_OFFICIAL_NAME"=>1,
        "UF_REVIEWS_NAME"=>1,
        "UF_CONTACTS_PUBLIC"=>1,
        "UF_COMP_PREVIEW_TEXT"=>1,
        "UF_COMP_DESCRIPTION_TEXT"=>1,
        "UF_COMP_LOGO"=>1,
        "UF_ORG_ADDRESS"=>1,
        "UF_WORKING_HOURS"=>1,
    ];

    protected $user;

    public function __construct($user, $HLBCClass)
    {
        $this->user = $user;
        parent::__construct($HLBCClass);

        AddEventHandler("", "\Projectsinfo::OnBeforeAdd", [$this,"OnBeforeAddHendler"]);
        AddEventHandler("", "\Projectsinfo::OnBeforeUpdate", [$this,'clearCacheHandler'],100);
        AddEventHandler("", "\Projectsinfo::OnBeforeDelete", [$this,"DeleteclearCache"]);
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
        $cache->clean($cacheId, "kabinet/infoproject");

    }

    public function DeleteclearCache($id, $primary, $oldFields)
    {
        global $CACHE_MANAGER;

        $project_id = $oldFields['UF_PROJECT_ID'];

        $cacheId = '';
        $cacheId = SITE_ID."|".$cacheId;
        $cacheId .= "|".serialize(intval($project_id));

        $cache = new \CPHPCache;
        $cache->clean($cacheId, "kabinet/infoproject");
    }

    public function getData($project_id,$clear=false){
        global $CACHE_MANAGER;

        $cacheId = '';
        $cacheId = SITE_ID."|".$cacheId;
        $cacheId .= "|".serialize(intval($project_id));

        $cache = new \CPHPCache;
        if ($clear) $cache->clean($cacheId, "kabinet/infoproject");

        // for debugg!
        //$cache->clean($cacheId, "kabinet/infoproject");

        // сколько времени кешировать
        $ttl = 14400;
        // hack: $ttl = 0 то не кешировать

        if ($cache->StartDataCache($ttl, $cacheId, "kabinet/infoproject"))
        {
            $infoprojects = \Bitrix\Kabinet\project\datamanager\ProjectsInfoTable::getlist([
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