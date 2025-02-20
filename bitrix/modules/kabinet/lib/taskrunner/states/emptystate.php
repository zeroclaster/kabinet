<?php
namespace Bitrix\Kabinet\taskrunner\states;

class Emptystate implements \Bitrix\Kabinet\taskrunner\states\contracts\Istage{
    protected $title = 'Empty stage';
    public $id = 0;
    public $status = 0;

    public function __construct($id)
    {
        $this->id = $id;
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

    public function execute(){

    }

    public function getStatus(){
        return $this->status;
    }

    public function getId(){
        return $this->id;
    }
}
