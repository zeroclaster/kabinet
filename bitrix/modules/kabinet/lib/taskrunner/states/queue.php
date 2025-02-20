<?
namespace Bitrix\Kabinet\taskrunner\states;

use Bitrix\Main\SystemException,
    Bitrix\Main\Event,
    Bitrix\Main\Entity,
    Bitrix\Main\EventManager,
    Bitrix\Kabinet\taskrunner\datamanager\FulfillmentTable;

class Queue{
    private static $instance;
    public $context;

    protected function __construct() {}

    protected function __clone() { }


    public function __wakeup()
    {
        throw new SystemException("Cannot unserialize a singleton.");
    }

    public static function getInstance()
    {
        $cls = static::class;
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    public function isEmpty(){
        $HLBClass = (\KContainer::getInstance())->get('FULF_HL');
        $isQueue = $HLBClass::getlist([
            'select'=>[new Entity\ExpressionField('CNT', 'COUNT(*)')],
            'filter'=>['UF_ACTIVE'=>1]
        ])->fetch();


        return (int)$isQueue['CNT'] == 0;
    }


	public function getQueue($id=0){
		$filter = [];
		if ($id) $filter['=UF_TASK_ID'] = $id;
        $HLBClass = (\KContainer::getInstance())->get('FULF_HL');
        //$HLBClass = \Bitrix\Kabinet\taskrunner\datamanager\FulfillmentTable::class;
        $Queue = $HLBClass::getlist([
            'select'=>['*'],
            'filter'=>$filter,
            'order' => ['ID'=>'DESC'],
        ])->fetchAll();

        //echo \Bitrix\Main\Entity\Query::getLastQuery();

		return $Queue;
	}

    public function getQueueById($id=0){
        $filter = [];
        if ($id) $filter['ID'] = $id;
        //$HLBClass = (\KContainer::getInstance())->get('FULF_HL');
        $HLBClass = \Bitrix\Kabinet\taskrunner\datamanager\FulfillmentTable::class;
        $Queue = $HLBClass::getlist([
            'select'=>['*'],
            'filter'=>$filter,
            'order' => ['ID'=>'DESC'],
        ])->fetch();

        //echo \Bitrix\Main\Entity\Query::getLastQuery();

        return $Queue;
    }

    public function run(){
        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $HLBClass = (\KContainer::getInstance())->get('FULF_HL');

        // Filter
        $SQL_data = $HLBClass::getlist([
            'select'=>['ID'],
            'filter'=>[
                'UF_ACTIVE'=>1,
                '!UF_STATUS'=>[9,10],   // исключаем статус Выпонин и Отменен
            ],
            'order' => ['UF_RUN_DATE'=>'DESC'],
            'limit'	=> 2
        ])->fetchAll();

        if (!$SQL_data)
            throw new SystemException("Отсутствует очередь.");

        $idList = array_column($SQL_data, 'ID');

        $runnerManager = $sL->get('Kabinet.Runner');
        $list = $runnerManager->getData([],true,$id=$idList);

        foreach ($list as $fields) {			
            $stage = $runnerManager->makeState($fields);			
            $stage->execute();
        }

    }

    public function goToEndLine($id){
        $HLBClass = (\KContainer::getInstance())->get('FULF_HL');
        $Queue = $HLBClass::getlist([
            'select'=>['ID','UF_RUN_DATE'],
            'filter'=>['UF_ACTIVE'=>1],
            'order' => ['UF_RUN_DATE'=>'ASC'],
            'limit'	=> 1
        ])->fetch();

        if (!$Queue)
            throw new SystemException("Отсутствует очередь.");

        $Queue['UF_RUN_DATE']->add("-1 seconds");

        $isExists = $HLBClass::getlist([
            'select'=>['ID','UF_RUN_DATE'],
            'filter'=>['ID'=>$id],
            'limit'	=> 1
        ])->fetch();

        if (!$isExists)
            throw new SystemException("Отсутствует очередь.");

        $HLBClass::update($isExists['ID'],['UF_RUN_DATE'=>$Queue['UF_RUN_DATE']]);
    }
}
