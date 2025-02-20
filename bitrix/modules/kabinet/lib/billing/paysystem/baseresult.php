<?
namespace Bitrix\Kabinet\billing\paysystem;

use \Bitrix\Kabinet\billing\datamanager\TransactionTable,
    \Bitrix\Main\SystemException,
    \Bitrix\Main\Error;

abstract class Baseresult{
    const COMPLETED = 1;

    protected $errors = '';
    protected $trans = [];

    public function getErrors(){
        return $this->errors;
    }

    public function setError($mess){
        $this->errors = $mess;
    }

    protected function getTransaction($id){
        if (isset($this->trans[$id])) return $this->trans[$id];

        $trans = TransactionTable::getlist([
            'select'=>['*'],
            'filter'=>['ID'=>$id,'STATUS'=>0],
            'limit'=>1
        ])->fetch();

        if (!$trans) return false;

        $this->trans[$id] = $trans;
        return $this->trans[$id];
    }

    protected function endTransaction($id){
        TransactionTable::update($id,[
            'STATUS'=> self::COMPLETED,
            'DATE_OPERATION' => new \Bitrix\Main\Type\DateTime(),
        ]);
    }

    protected function involveTransaction($id,$sum){
        $trans = $this->getTransaction($id);
        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $billing = $sL->get('Kabinet.Billing');

        try {
            $billing->addMoney($trans['SUM'], $trans['USER_ID'], $this);
        }catch (SystemException $exception){
            $this->setError($exception->getMessage());
            return false;
        }

        // Завершаем транзакцию
        $this->endTransaction($id);

        return true;
    }

    public function getDescription(){
        return $this->description;
    }

    public function failpay(){
        $trans_id = $this->getTansId();
        TransactionTable::delete($trans_id);
    }

    abstract protected function setTansId($trans_id);
    abstract protected function getTansId();
}