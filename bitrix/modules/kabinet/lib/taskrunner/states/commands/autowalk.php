<?php
namespace Bitrix\Kabinet\taskrunner\states\commands;

use Bitrix\Main\SystemException,
    Bitrix\Main\Error;

class Autowalk extends Base implements \Bitrix\Kabinet\taskrunner\states\contracts\Icommand{
    protected $status;

    public function __construct($status)
    {
        $this->status = $status;
    }

    public function execute(array $params = []){
        $stage = $this->Object;
        $runnerFields = $stage->runnerFields;
        \Bitrix\Kabinet\taskrunner\datamanager\FulfillmentTable::update($runnerFields['ID'],['UF_STATUS'=>$this->status]);

        return true;
    }
}