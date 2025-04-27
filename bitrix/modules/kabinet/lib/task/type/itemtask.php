<?
namespace Bitrix\Kabinet\task\type;


abstract class Itemtask{

    abstract public function startItemTask($task);

    abstract public function calcPlannedFinalePrice($task,$PlannedDate);

    abstract public function dateStartTask($task);

    abstract public function theorDateEnd($task);

    abstract public function PlannedPublicationDate($task);

    abstract public function createFulfi($task,$PlannedDate);
}