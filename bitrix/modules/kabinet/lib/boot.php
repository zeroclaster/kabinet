<?
namespace Bitrix\Kabinet;


class Boot extends \Bitrix\Kabinet\bootstrap\Base{
    public function Start(){

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();

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
            },'user');

        (\KContainer::getInstance())->make(function(){

            $object = (\KContainer::getInstance())->get('user');
            return $object;

        },'siteuser');

        $sL->addInstanceLazy("Kabinet.Client", ['constructor'=>
            static function(){
                $provider = \Bitrix\Kabinet\client\Providerclient::getInstance();
                $project = $provider->build();
                return $project;
            }
        ]);

        $sL->addInstanceLazy("Kabinet.Runner", ['constructor'=>
            static function(){
                $provider = \Bitrix\Kabinet\taskrunner\Providerrunner::getInstance();
                $project = $provider->build();
                return $project;
            }
        ]);

        $sL->addInstanceLazy("Kabinet.Messanger", ['constructor'=>
            static function(){
                $provider = \Bitrix\Kabinet\messanger\Providermessanger::getInstance();
                $project = $provider->build();
                return $project;
            }
        ]);
		
        $sL->addInstanceLazy("Kabinet.Billing", ['constructor'=>
            static function(){
                $provider = \Bitrix\Kabinet\billing\Providerbilling::getInstance();
                $project = $provider->build();
                return $project;
            }
        ]);

        // Выполняем операции запуска по умолчанию!
        parent::Start();
    }
}