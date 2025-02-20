<?php
namespace Bitrix\Kabinet\taskrunner\states\type\reviews;

use Bitrix\Kabinet\taskrunner\states\Commandmanager;
use Bitrix\Main\SystemException,
    Bitrix\Main\Entity,
    Bitrix\Main\Event;

//use \Bitrix\Kabinet\DateTime;
use \Bitrix\Main\Type\DateTime;

/*
 * 8-Отчет на проверке у клиента;

Автоматический переход
Если поле отчет = «есть», то через 72 часа, в стадию: 9-Выполнена;
Если поле отчет = «нет», то через 0ч, в стадию: 9-Выполнена;


Отчет принят ► Выполнена
Отклонить с комментарием ► В работе у специалиста

 *
 *
 */

class Stage9 extends \Bitrix\Kabinet\taskrunner\states\Basestate implements \Bitrix\Kabinet\taskrunner\states\contracts\Istage{
    protected $title = '';
    public $runnerFields = [];
    public $id = 0;
    public $status = 1;

    public function __construct($runnerFields)
    {
        $this->runnerFields = $runnerFields;
        $this->id = $runnerFields['ID'];

        $this->command = new Commandmanager($this);
        $this->command->setCommand("auto_walk", new \Bitrix\Kabinet\taskrunner\states\commands\Autowalk(9));
    }

    public function setTitle(string $title){
        $this->title = $title;
    }

    public function getTitle(){
        return $this->title;
    }

    public function getName(){
        return implode('',array_slice(explode('\\',__CLASS__),-2,2));
    }

    public function getRoutes(){
        if(\PHelp::isAdmin()) {
            return [];
        }else{
            return [
                4,      // Stage5 В работе у специалиста
                9,      // Stage10 Выполнена
            ];
        }
    }

    // условия что бы включить этот статус
    public function conditionsTransition($oldData){
        $runnerFields = $this->runnerFields;

        if (\PHelp::isAdmin()) {
            // Для админа
            if (
                !$runnerFields['UF_REPORT_TEXT'] &&
                !$runnerFields['UF_REPORT_SCREEN'] &&
                !$runnerFields['UF_REPORT_FILE'] &&
                !$runnerFields['UF_REPORT_LINK']
            ) throw new SystemException("Вы не сделали не одного отчета");
        }else{
            // Для клиента
        }

        return true;
    }

    // уходят со статуса
    public function leaveStage($object){
        $object->set('UF_HITCH',0);
    }

    // когда пришли на статус
    public function cameTo($object){
        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $messanger = $sL->get('Kabinet.Messanger');

        $QUEUE_ID=$object->get('ID');
        $TASK_ID=$object->get('UF_TASK_ID');
        $upd_id = $messanger->sendSystemMessage(
            $messanger->config('postupil_otchet_na_proverku'),
            $QUEUE_ID,
            $TASK_ID
        );
    }

    public function execute(){
        $event = new Event("kabinet", "OnBeforeStartStage", ['id'=>$this->id,'name'=>$this->getName(),'title'=>$this->getTitle()]);
        $event->send();
		$runnerFields = $this->runnerFields;

        //echo "<pre>";
        //print_r($runnerFields);
       // echo "</pre>";
	   
	    $TASK = $this->getTask();

        //echo "<pre>";
        //print_r($TASK);
        //echo "</pre>";
		
		//throw new SystemException("TEST STOP");
		
		// Отчетность
		// 9 не требуется
		if ($TASK['UF_REPORTING'] == 9 || $TASK['UF_REPORTING'] == NULL || !$TASK['UF_REPORTING']){
			$this->goToState(9);		// Выполнена
		}else{			   
			$d = (new DateTime())->add("-72 hours");		
			if ($d->getTimestamp() > $runnerFields['UF_CREATE_DATE']){
				$this->goToState(9);		// Выполнена
			}
		}
		
        $Queue = \Bitrix\Kabinet\taskrunner\states\Queue::getInstance();
        $Queue->goToEndLine($this->id);
    }

    public function getStatus(){
        return $this->status;
    }

    public function getId(){
        return $this->id;
    }

}