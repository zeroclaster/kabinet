<?php
namespace Bitrix\Kabinet\taskrunner\states\type\reviews;

use Bitrix\Main\SystemException,
    Bitrix\Main\Entity,
    Bitrix\Main\Event;

// Для отладки, можно установить свою дату задав константу TESTDATE
//use \Bitrix\Kabinet\helper\DateTime;
use \Bitrix\Main\Type\DateTime;

/*
6.1 -Публикуется;

Администратор может вручную сменить статус на:

2-Пишется текст
4-В работе у специалиста

6-1 Публикуется
+ копируется ответственный за стадию, если он не был задан

Если «Отчетность» = «есть», то возможен переход на:
7-Готовится отчет
+ копируется ответственный за стадию, если он не был задан

Если «Отчетность» = «нет», то возможен переход на
9-Выполнено
+ копируется ответственный за стадию, если он не был задан

Для типа «Множественные»
добавить кнопку «Запланировано»


Нет кнопок

Фиксация просрочки — если прошла плановая дата публикации.
 *
 *
 */


class Stage7_1 extends \Bitrix\Kabinet\taskrunner\states\Basestate implements \Bitrix\Kabinet\taskrunner\states\contracts\Istage{
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
        $runnerFields = $this->runnerFields;
        if(\PHelp::isAdmin()) {
            $states = [2]; // Пишется текст
            $states[] = 4; // В работе у специалиста


            $TaskData = \Bitrix\Kabinet\task\datamanager\TaskTable::getById($runnerFields['UF_TASK_ID'])->fetch();
            if ($TaskData['UF_REPORTING'] == \Bitrix\Kabinet\task\Taskmanager::LINK_SCREENHOT)
                $states[] = 7; // Готовится отчет
            else
                $states[] = 9; //Выполнено

            return $states;
        }else{
            return [];
        }
    }

    // условия что бы включить этот статус
    public function conditionsTransition($oldData){
        $runnerFields = $this->runnerFields;
        $PRODUCT = $this->getProduct();

        if (\PHelp::isAdmin()) {
            // Для админа
            if (
                ($PRODUCT['VIEW_UF_REVIEW_TEXT']['VALUE_XML_ID'] == '529f3954e3cce751af50dbf5a8f84712') &&
                (!$runnerFields['UF_REVIEW_TEXT'])
            ) throw new SystemException("Вы не ввели текст отзыва");
        }else{
            if (
                $PRODUCT['COORDINATION']['VALUE_XML_ID'] == \Bitrix\Kabinet\task\Taskmanager::IS_SOGLACOVANIE &&
                !$runnerFields['UF_COMMENT'] &&
                $oldData['UF_STATUS'] != 3 &&
                $oldData['UF_STATUS'] != 5
            )
                throw new SystemException("EmptyUF_COMMENT", \Bitrix\kabinet\Controller\Runnerevents::END_WITH_SCRIPT);
        }

        return true;
    }

    // уходят со статуса
    public function leaveStage($object){

        if (!$object->get('UF_ACTUAL_DATE')) throw new SystemException("Вы не ввели дату публикации");

        $object->set('UF_COMMENT','');
        $object->set('UF_HITCH',0);

        $UF_STATUS = $object->get('UF_STATUS');
        $UF_RESPONSIBLE = $object->get('UF_RESPONSIBLE');

        // Статусы, которые требуют добавления ответственного
        $STATUSES_FOR_RESPONSIBLE_ADDITION = [61,7,9];

        if (in_array($UF_STATUS, $STATUSES_FOR_RESPONSIBLE_ADDITION)) {
            $updatedResponsible = $this->addResponsibleEntry($UF_RESPONSIBLE, $UF_STATUS);
            $object->set('UF_RESPONSIBLE', $updatedResponsible);
        }
    }

    public function execute(){
        $event = new Event("kabinet", "OnBeforeStartStage", ['id'=>$this->id,'name'=>$this->getName(),'title'=>$this->getTitle()]);
        $event->send();

        //Фиксация просрочки — через 72 часа.
        $this->isFixHitch2();
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