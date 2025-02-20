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
        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $runnerManager = $sL->get('Kabinet.Runner');

        $stage = $this->Object;
        $runnerFields = $stage->runnerFields;
        $runnerFields['UF_STATUS'] = $this->status;
        $runnerManager->update($runnerFields);

        return true;
    }
}