<?
namespace Bitrix\Kabinet\bootstrap;


class Adminbootservice extends Base{

    public function __construct(\Bitrix\Kabinet\container\Hlbuilder $Hlbuilder)
    {
    }

    public function run(){

        // Выполняем операции запуска по умолчанию!
        parent::Start();

    }
}
