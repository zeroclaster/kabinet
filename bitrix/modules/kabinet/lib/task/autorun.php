<?
namespace Bitrix\Kabinet\task;

use Bitrix\Main\SystemException,
    Bitrix\Main\Event,
    Bitrix\Main\Entity,
    Bitrix\Main\EventManager;

class Autorun{
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

    public function run(){
        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $HLBClassFulfi = (\KContainer::getInstance())->get('FULF_HL');
        $HLBClassTask = (\KContainer::getInstance())->get('TASK_HL');
        $TaskManager = $sL->get('Kabinet.Task');
        $runnerManager = $sL->get('Kabinet.Runner');

        $list = $HLBClassTask::getlist([
            'select'=>['ID'],
            'filter'=>[
                'UF_ACTIVE'=>1,
                'UF_CYCLICALITY' =>[2,34],
                'UF_STATUS'=>\Bitrix\Kabinet\task\Taskmanager::WORKED
            ],
            'order' => ['UF_RUN_DATE'=>'ASC'],
            'limit'	=> 5
        ])->fetchAll();

        // Начало следующего месяца
        $mouthStart = new \Bitrix\Main\Type\DateTime(
            (new \DateTime('first day of next month'))->format("d.m.Y 00:00:01"),
            "d.m.Y H:i:s"
        );

        // Конец следующего месяца
        $mouthEnd = (new \Bitrix\Main\Type\DateTime(
            (new \DateTime('last day of next month'))->format("d.m.Y 00:00:01"),
            "d.m.Y H:i:s"
        ));

        foreach ($list as $task){

            // проверяем, есть ли запланированные исполнения на след месяц
            $isExists = \Bitrix\Kabinet\taskrunner\datamanager\FulfillmentTable::getlist([
                'select' => ['ID','UF_TASK_ID'],
                'filter'=>[
                    'UF_ACTIVE'=>1,
                    '>UF_PLANNE_DATE'=>$mouthStart,
                    '<UF_PLANNE_DATE'=>$mouthEnd,
                    'UF_STATUS'=>0,
                    'UF_TASK_ID'=>$task['ID'],
                ]
            ])->fetchAll();

            // если нет, то планируем
            if (!$isExists) {
                $task = $TaskManager->getData(false,[],['ID'=>$task['ID']]);
                $runnerManager->startTask($task[0]);
            }

            $this->goToEndLine($task['ID']);
        }
    }

    public function goToEndLine($id){
        $HLBClass = (\KContainer::getInstance())->get('TASK_HL');
        $task = $HLBClass::getlist([
            'select'=>['ID','UF_RUN_DATE'],
            'filter'=>['UF_ACTIVE'=>1],
            'order' => ['UF_RUN_DATE'=>'ASC'],
            'limit'	=> 1
        ])->fetch();

        if (!$task) throw new SystemException("Отсутствует очередь.");

        if (!$task['UF_RUN_DATE']) $task['UF_RUN_DATE'] = new \Bitrix\Main\Type\DateTime;
        $task['UF_RUN_DATE']->add("-1 seconds");

        $isExists = $HLBClass::getlist([
            'select'=>['ID','UF_RUN_DATE'],
            'filter'=>['ID'=>$id],
            'limit'	=> 1
        ])->fetch();

        if (!$isExists) throw new SystemException("Отсутствует очередь.");

        $HLBClass::update($isExists['ID'],['UF_RUN_DATE'=>$task['UF_RUN_DATE']]);
    }
}