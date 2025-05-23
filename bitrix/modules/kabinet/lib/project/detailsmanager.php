<?php
namespace Bitrix\Kabinet\project;

use \Bitrix\Main\SystemException,
    \Bitrix\Kabinet\exceptions\ProjectException,
    \Bitrix\Kabinet\exceptions\TestException;

class Detailsmanager extends \Bitrix\Kabinet\container\Abstracthighloadmanager {
    public $fieldsType = [
        "UF_ABOUT_REVIEW"=>1,
        "UF_POSITIVE_SIDES"=>1,
        "UF_MINUSES"=>1,
        "UF_MINUSES_USER"=>1,
        "UF_ORDER_PROCESS"=>1,
        "UF_ORDER_PROCESS_USER"=>1,
        "UF_EXAMPLES_REVIEWS"=>1,
        "UF_MENTION_REVIEWS"=>1,
        "UF_KEYWORDS"=>1,
    ];

    protected $user;

    public function __construct($user, $HLBCClass)
    {
        $this->user = $user;
        parent::__construct($HLBCClass);

        AddEventHandler("", "\Projectsdetails::OnBeforeAdd", [$this,"OnBeforeAddHendler"]);
        AddEventHandler("", "\Projectsdetails::OnBeforeUpdate", [$this,'clearCacheHandler'],100);
        AddEventHandler("", "\Projectsdetails::OnBeforeDelete", [$this,"DeleteclearCache"]);
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
        $cache->clean($cacheId, "kabinet/detailsproject");
    }

    public function DeleteclearCache($id, $primary, $oldFields)
    {
        global $CACHE_MANAGER;

        $project_id = $oldFields['UF_PROJECT_ID'];

        $cacheId = '';
        $cacheId = SITE_ID."|".$cacheId;
        $cacheId .= "|".serialize(intval($project_id));

        $cache = new \CPHPCache;
        $cache->clean($cacheId, "kabinet/detailsproject");
    }

    public function getData($project_id,$clear=false){
        global $CACHE_MANAGER;

        $cacheId = '';
        $cacheId = SITE_ID."|".$cacheId;
        $cacheId .= "|".serialize(intval($project_id));

        $cache = new \CPHPCache;
        if ($clear) $cache->clean($cacheId, "kabinet/detailsproject");

        //for debugg
        //$cache->clean($cacheId, "kabinet/detailsproject");

        // сколько времени кешировать
        $ttl = 14400;
        // hack: $ttl = 0 то не кешировать
        if ($cache->StartDataCache($ttl, $cacheId, "kabinet/detailsproject"))
        {
            $infoprojects = \Bitrix\Kabinet\project\datamanager\ProjectsDetailsTable::getlist([
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