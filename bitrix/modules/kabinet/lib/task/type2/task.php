<?php
namespace Bitrix\Kabinet\task\type2;

class Task{
    private $calcprice;
    private $datestart;
    private $theorend;
    private $planneddate;
    private $createf;

    public function __construct(
        $calcprice,
        $datestart,
        $theorend,
        $planneddate,
        $createf
    )
    {
        $calcprice->setConteiner($this);
        $datestart->setConteiner($this);
        $theorend->setConteiner($this);
        $planneddate->setConteiner($this);
        $createf->setConteiner($this);

        $this->calcprice = $calcprice;
        $this->datestart =  $datestart;
        $this->theorend = $theorend;
        $this->planneddate = $planneddate;
        $this->createf = $createf;
    }

    public function calcPlannedFinalePrice($task,$PlannedDate){
        return $this->calcprice->calcPlannedFinalePrice($task,$PlannedDate);
    }

    public function dateStartTask($task){
        return $this->datestart->dateStartTask($task);
    }

    public function theorDateEnd($task){
        return $this->theorend->theorDateEnd($task);
    }

    public function PlannedPublicationDate($task){
        return $this->planneddate->PlannedPublicationDate($task);
    }

    public function createFulfi($task,$PlannedDate){
        return $this->createf->createFulfi($task,$PlannedDate);
    }
}