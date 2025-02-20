<?php
namespace Bitrix\Kabinet\taskrunner\states;

class Maker{
    private $stageList;
    private $type;

    public function __construct($stages)
    {
        $this->stageList = $stages;
    }

    public function __call($codeStage, $arguments)
    {
        $stages = $this->stageList;
        $runnerFields = $arguments[0];

        if (empty($stages) || empty($stages[$codeStage])){
            return new Emptystate($id);
        }

        $className = $stages[$codeStage]->class->__toString();
        $title = $stages[$codeStage]->title->__toString();
        $state = new $className($runnerFields);
        $state->setTitle($title);
        return $state;
    }

}
