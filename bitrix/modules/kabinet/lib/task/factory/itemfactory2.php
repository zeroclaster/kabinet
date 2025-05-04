<?
namespace Bitrix\Kabinet\task\factory;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Kabinet\task\type2\Task;
use Bitrix\Kabinet\task\type2\Calcfinaleprice;
use Bitrix\Kabinet\task\type2\Bounddatestarttask;
use Bitrix\Kabinet\task\type2\Boundtheordateend;
use Bitrix\Kabinet\task\type2\Boundplannedpublicationdate;
use Bitrix\Kabinet\task\type2\Createf;
use Bitrix\Kabinet\task\type2\Cyclicalitydatestarttask;
use Bitrix\Kabinet\task\type2\Cycliacalitytheordateend;
use Bitrix\Kabinet\task\type2\Cycliacalityplannedpublicationdate;
use Bitrix\Kabinet\task\type2\Cycliacalitycalcfinaleprice_34;
use Bitrix\Kabinet\task\type2\Multiplecalcfinaleprice;
use Bitrix\Kabinet\task\type2\Multipleplannedpublicationdate;
use Bitrix\Kabinet\task\type2\Multiplecreatefulfi;

class Itemfactory2 extends Abstractfactory {

    public function getObject($task) {
        $components = $this->createComponents($task);
        return new Task(
            $components['price'],
            $components['datastart'],
            $components['dataend'],
            $components['planned'],
            $components['create']
        );
    }

    private function createComponents($task) {
        $TaskManager = ServiceLocator::getInstance()->get('Kabinet.Task');
        $PRODUCT = $TaskManager->getProductByTask($task);

        //Элемент тип
        $type = $PRODUCT['ELEMENT_TYPE']['VALUE'];
        $CYCLICALITY = $task['UF_CYCLICALITY'];

        $componets = [];

        if ($CYCLICALITY == 1 || $CYCLICALITY == 33) {
            $componets = [
                'price' => new Calcfinaleprice(),
                'datastart' => new Bounddatestarttask(),
                'dataend' => new Boundtheordateend(),
                'planned' => new Boundplannedpublicationdate(),
                'create' => new Createf(),
            ];
        }

        if ($CYCLICALITY == 2 || $CYCLICALITY == 34) {
            $componets = [
                'price' => new Calcfinaleprice(),
                'datastart' => new Cyclicalitydatestarttask(),
                'dataend' => new Cycliacalitytheordateend(),
                'planned' => new Cycliacalityplannedpublicationdate(),
                'create' => new Createf(),
            ];
        }

        if ($CYCLICALITY == 34) {
            $componets['price'] = new Cycliacalitycalcfinaleprice_34();
        }

        if ($type == 'multiple') {
            $componets['price'] = new Multiplecalcfinaleprice();
            $componets['planned'] = new Multipleplannedpublicationdate();
            $componets['create'] = new Multiplecreatefulfi();
        }

        return $componets;
    }
}