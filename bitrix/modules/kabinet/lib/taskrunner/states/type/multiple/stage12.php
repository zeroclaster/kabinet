<?php
namespace Bitrix\Kabinet\taskrunner\states\type\multiple;

use Bitrix\Kabinet\exceptions\FulfiException;
use Bitrix\Main\SystemException,
    Bitrix\Main\Entity,
    Bitrix\Main\Event;

// Для отладки, можно установить свою дату задав константу TESTDATE
//use \Bitrix\Kabinet\helper\DateTime;
use \Bitrix\Main\Type\DateTime;

/*
 * 11-Требуется информация;
В кабинете администратора статус “Требуется информация” выводить на всех стадиях. У администратора на стадии “Требуется информация” выводить все кнопки для переключения в любую стадию. У клиента выводить кнопку “Отправить на публикацию”.
Статус будет проставляться, например когда исполнитель по заданию пришлёт комментарий, что ссылка на карточку клиента в авито, на которой нужно разместить отзыв не работает или объявление снято с публикации.
При проставлении статуса клиенту автоматически приходит сообщение (почта-телеграм, что настроено)):

«“Требуется информация” для исполнения #000000  задачи #000000
Проверьте, верна ли ссылка, которую вы указали в задаче, заполнен ли вами бриф проекта. Работа по задаче может быть приостановлена, просьба перейти в кабинет и дополнить задачу данными.
 */


class Stage12 extends \Bitrix\Kabinet\taskrunner\states\Basestate implements \Bitrix\Kabinet\taskrunner\states\contracts\Istage{
    protected $title = '';
    public $runnerFields = [];
    public $id = 0;
    public $status = 1;

    public function __construct($runnerFields)
    {
        $this->runnerFields = $runnerFields;
        $this->id = $runnerFields['ID'];
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
            return [
                0,
                1,
                6,
                61,
                7,
                8,
                9,
                10
            ];
        }else{
            return [
                6 // Публикация
            ];
        }
    }

    // условия что бы включить этот статус
    public function conditionsTransition($oldData){
        if (\PHelp::isAdmin()) {
            // Для админа
        }else{
        }
        return true;
    }

    // уходят со статуса
    public function leaveStage($object){
        $object->set('UF_HITCH',0);

    }

    // когда пришли на статус
    public function cameTo($object){
        $runnerFields = $this->runnerFields;
        $messanger = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Messanger');
        $task = $this->getTask();

        // если статус устанавливает Админ
        if (\PHelp::isAdmin()) {
            $QUEUE_ID = $object->get('ID');
            $TASK_ID = $object->get('UF_TASK_ID');
            // отправить сообщение в чат
            $upd_id = $messanger->sendSystemMessage(
                $messanger->config('trebuetca_informacia'),
                $QUEUE_ID,
                $TASK_ID
            );

        }
    }

    public function execute(){
        $event = new Event("kabinet", "OnBeforeStartStage", ['id'=>$this->id,'name'=>$this->getName(),'title'=>$this->getTitle()]);
        $event->send();



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