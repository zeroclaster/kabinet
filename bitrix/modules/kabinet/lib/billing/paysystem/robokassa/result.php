<?
namespace Bitrix\Kabinet\billing\paysystem\robokassa;
/*
https://docs.robokassa.ru/code-examples/
https://auth.robokassa.ru/Merchant/WebService/Service.asmx
https://github.com/unetway/robokassa/blob/master/src/Robokassa.php
*/

class Result extends \Bitrix\Kabinet\billing\paysystem\Baseresult{
    protected $trans_id = 0;
    public $description = 'Система оплаты Robokassa';

    public function __construct($inv_id=0)
    {
        $MerchantLogin = \Bitrix\Main\Config\Option::get("kabinet","MerchantLogin");
        $RobokassaPass1 = \Bitrix\Main\Config\Option::get("kabinet","RobokassaPass1");
        $RobokassaPass2 = \Bitrix\Main\Config\Option::get("kabinet","RobokassaPass2");
        $RobokassaTest = \Bitrix\Main\Config\Option::get("kabinet","RobokassaTest");

        if ($inv_id)
            $this->setTansId($inv_id);
        else
            $this->setTansId($_REQUEST["InvId"]);
    }

    protected function setTansId($trans_id){
        $this->trans_id = $trans_id;
    }

    protected function getTansId(){
        return $this->trans_id;
    }

    public function isSuccess(){
        $mrh_pass2 = \Bitrix\Main\Config\Option::get("kabinet","RobokassaPass2"); //"vYqarTf5Fs7qPaM1j8Z5";
		$mrh_pass1 = \Bitrix\Main\Config\Option::get("kabinet","RobokassaPass1");
        $out_summ = $_REQUEST["OutSum"];
        $inv_id = $this->getTansId();

        // Транзакция создается в bitrix/modules/kabinet/lib/controller/bilingevents.php
        $trans = $this->getTransaction($inv_id);

        if (!$trans) {
            $this->setError('Неудалось найти ID транзакции.');
            return false;
        }

        $crc = strtoupper($_REQUEST["SignatureValue"]);
        $my_crc = strtoupper(md5("$out_summ:$inv_id:$mrh_pass1"));

        if ($my_crc != $crc) {
            $this->setError('При пополнении баланса возникла ошибка!');
            return false;
        }

        // включить транзакцию
        return $this->involveTransaction($trans['ID'],$out_summ);
    }

    protected function involveTransaction($id,$sum){
        $trans = $this->getTransaction($id);
        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $billing = $sL->get('Kabinet.Billing');

        $calc_sum =  round($trans['SUM'] *0.93,2);

        try {
            $billing->addMoney($calc_sum, $trans['USER_ID'], $this);
        }catch (SystemException $exception){
            $this->setError($exception->getMessage());
            return false;
        }

        // Завершаем транзакцию
        $this->endTransaction($id);

        return true;
    }

    public function makeCRC(){
        $mrh_pass2 = \Bitrix\Main\Config\Option::get("kabinet","RobokassaPass2"); //"vYqarTf5Fs7qPaM1j8Z5";
        $out_summ = $_REQUEST["OutSum"];
        $inv_id = $_REQUEST["InvId"];

        return strtoupper(md5("$out_summ:$inv_id:$mrh_pass2"));
    }

    public function generatePayLink($sum,$bank = ''){
        /*
         * <Currency Label="AlwaysYes36PSR" Alias="AlwaysYes" Name="ВсегдаДа 36 месяцев" MinValue="1500" MaxValue="500000"/>
<Currency Label="AlwaysYes3PSR" Alias="AlwaysYes" Name="ВсегдаДа 3 месяца" MinValue="1500" MaxValue="500000"/>
<Currency Label="AlwaysYes4PSR" Alias="AlwaysYes" Name="ВсегдаДа 4 месяца" MinValue="1500" MaxValue="500000"/>
<Currency Label="AlwaysYes6PSR" Alias="AlwaysYes" Name="ВсегдаДа 6 месяцев" MinValue="1500" MaxValue="500000"/>
<Currency Label="AlwaysYes8PSR" Alias="AlwaysYes" Name="ВсегдаДа 8 месяцев" MinValue="1500" MaxValue="500000"/>
<Currency Label="BankCardPSR" Alias="BankCard" Name="Банковская карта"/>
<Currency Label="OTP3_300PSR" Alias="OTP" Name="ОТП 3 месяца до 300 000 RUB" MinValue="2000" MaxValue="300000"/>
<Currency Label="OTP4_300PSR" Alias="OTP" Name="ОТП 4 месяца до 300 000 RUB" MinValue="2000" MaxValue="300000"/>
<Currency Label="OTP6_300PSR" Alias="OTP" Name="ОТП 6 месяцев до 300 000 RUB" MinValue="2000" MaxValue="300000"/>
<Currency Label="OTPCredit_300PSR" Alias="OTP" Name="ОТП Кредит до 300 000 RUB" MinValue="2000" MaxValue="300000"/>
<Currency Label="YandexPayPSR" Alias="YandexPay" Name="Яндекс Pay" MaxValue="100000"/>
<Currency Label="Card120DaysPSR" Alias="Card120Days" Name="Карта 120 дней без %"/>
<Currency Label="CardHalvaPSR" Alias="BankCardHalva" Name="Карта Халва"/>
<Currency Label="MirPayPSR" Alias="MirPay" Name="МIR Pay"/>
         */

        // your registration data
        $mrh_login = \Bitrix\Main\Config\Option::get("kabinet","MerchantLogin");
        $mrh_pass1 = \Bitrix\Main\Config\Option::get("kabinet","RobokassaPass1");

        $inv_id = $this->getTansId();

        $data = [
           'MerchantLogin' => \Bitrix\Main\Config\Option::get("kabinet","MerchantLogin"),
            'OutSum' => $sum,
            'InvId' => $inv_id,
            'Description' => \Bitrix\Main\Config\Option::get("kabinet","RobokassaDesc"),
            'SignatureValue' => '',
        ];

        if ($bank) $data['IncCurrLabel'] = $bank;
        if (\Bitrix\Main\Config\Option::get("kabinet","RobokassaTest") == 'Y')  $data['IsTest'] = 1;

        $mrh_login = $data['MerchantLogin'];
        $out_summ = $data['OutSum'];
        $mrh_pass1 = \Bitrix\Main\Config\Option::get("kabinet","RobokassaPass1");
        // build CRC value
        $crc =  md5("$mrh_login:$out_summ:$inv_id:$mrh_pass1");
        $data['SignatureValue'] = $crc;

        return "https://auth.robokassa.ru/Merchant/Index.aspx?". http_build_query($data);
    }

}