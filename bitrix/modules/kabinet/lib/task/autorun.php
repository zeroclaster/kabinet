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
        $TaskManager = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Task');
        $runnerManager = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Runner');

        $list = \Bitrix\Kabinet\task\datamanager\TaskTable::getlist([
            'select'=>['*'],
            'filter'=>[
                'UF_ACTIVE'=>1,
                'UF_CYCLICALITY' =>[2,34],
                'UF_STATUS'=>\Bitrix\Kabinet\task\Taskmanager::WORKED
            ],
            'order' => ['UF_RUN_DATE'=>'ASC'],
            'limit'	=> 5
        ])->fetchAll();

        [$mouthStart,$mouthEnd] = \PHelp::nextMonth();

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
                $task['UF_DATE_COMPLETION'] = $TaskManager->getItem($task)->theorDateEnd($task);
                $taskConvert = $TaskManager->remakeData([$task]);
                $runnerManager->startTask($taskConvert[0]);
            }

            $this->goToEndLine($task['ID']);
        }
    }

    public function goToEndLine($id){
        $HLBClass = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('TASK_HL');
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