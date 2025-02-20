<?
namespace Bitrix\Kabinet;


class Bootadmin extends \Bitrix\Kabinet\bootstrap\Base{
    public function Start(){

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();

        if ($_REQUEST['usr']){
            (\KContainer::getInstance())->make(function(){
                global $USER;
                if ($USER && $USER->IsAuthorized()) {
                    $id = $_GET['usr'];
                    $Groups = \CUser::GetUserGroup($id);
                    $object = \Bitrix\Kabinet\Usertable::getByPrimary($id)->fetchObject();
                    //echo \Bitrix\Main\Entity\Query::getLastQuery();
                    return $object;
                }else
                    return false;
            },'user');
        }else{
            (\KContainer::getInstance())->make(function(){
                global $USER;
                if ($USER && $USER->IsAuthorized()) {
                    $object = \Bitrix\Kabinet\Usertable::createObject();
                    $object->set('ID',0);
                    return $object;
                }else
                    return false;
            },'user');
        }


        (\KContainer::getInstance())->make(function(){
            global $USER;
            if ($USER && $USER->IsAuthorized()) {
                $id = $USER->GetID();
                $Groups = \CUser::GetUserGroup($id);
                $object = \Bitrix\Kabinet\Usertable::getByPrimary($id)->fetchObject();
                //echo \Bitrix\Main\Entity\Query::getLastQuery();
                return $object;
            }else
                return false;
        },'adminuser');

        (\KContainer::getInstance())->make(function(){

                $object = (\KContainer::getInstance())->get('adminuser');
                return $object;

        },'siteuser');

        $sL->addInstanceLazy("Kabinet.Client", ['constructor'=>
            static function(){
                $provider = \Bitrix\Kabinet\client\Providerclient::getInstance();
                $project = $provider->build();
                return $project;
            }
        ]);

        $sL->addInstanceLazy("Kabinet.AdminClient", ['constructor'=>
            static function(){
                $provider = \Bitrix\Kabinet\client\Provideradminclient::getInstance();
                $project = $provider->build();
                return $project;
            }
        ]);

        $sL->addInstanceLazy("Kabinet.Runner", ['constructor'=>
            static function(){
                $provider = \Bitrix\Kabinet\taskrunner\admin\Providerrunner::getInstance();
                $project = $provider->build();
                return $project;
            }
        ]);

        $sL->addInstanceLazy("Kabinet.Messanger", ['constructor'=>
            static function(){
                $provider = \Bitrix\Kabinet\messanger\admin\Providermessanger::getInstance();
                $project = $provider->build();
                return $project;
            }
        ]);
		
        $sL->addInstanceLazy("Kabinet.Billing", ['constructor'=>
            static function(){
                $provider = \Bitrix\Kabinet\billing\admin\Providerbilling::getInstance();
                $project = $provider->build();
                return $project;
            }
        ]);		

        // Выполняем операции запуска по умолчанию!
        parent::Start();

    }
}