<?
namespace Bitrix\Kabinet\task\type;


abstract class Itemtask{

    abstract public function startItemTask($task);

    abstract public function calcPlannedFinalePrice($task,$PlannedDate);

    abstract public function dateStartTask($task);

    abstract public function theorDateEnd(array $task);

    abstract public function PlannedPublicationDate($task);
}