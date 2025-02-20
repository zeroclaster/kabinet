<?
namespace Bitrix\Kabinet\bootstrap;


abstract class Base{
	public function Init(){
		//$request = \Bitrix\Main\Context::getCurrent()->getRequest();
		//$serviceLocator = \Bitrix\Main\DI\ServiceLocator::getInstance();

		//$this->request->addFilter(new filters\Postfilter0);
		//$request->addFilter(new filters\Postfilter1);
	}

	public function Start(){

        $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
        $sL->addInstanceLazy("Kabinet.Project", ['constructor'=>
            static function(){
                $provider = \Bitrix\Kabinet\project\Providerproject::getInstance();
                $project = $provider->build();
                return $project;
            }
        ]);

        $sL->addInstanceLazy("Kabinet.infoProject", ['constructor'=>
            static function(){
                $provider = \Bitrix\Kabinet\project\Providerinfo::getInstance();
                $project = $provider->build();
                return $project;
            }
        ]);
        $sL->addInstanceLazy("Kabinet.detailsProject", ['constructor'=>
            static function(){
                $provider = \Bitrix\Kabinet\project\Providerdetails::getInstance();
                $project = $provider->build();
                return $project;
            }
        ]);
        $sL->addInstanceLazy("Kabinet.targetProject", ['constructor'=>
            static function(){
                $provider = \Bitrix\Kabinet\project\Providertarget::getInstance();
                $project = $provider->build();
                return $project;
            }
        ]);

        $sL->addInstanceLazy("Kabinet.Task", ['constructor'=>
            static function(){
                $provider = \Bitrix\Kabinet\task\Providertask::getInstance();
                $project = $provider->build();
                return $project;
            }
        ]);

        $sL->addInstanceLazy("Kabinet.Contract", ['constructor'=>
            static function(){
                $provider = \Bitrix\Kabinet\contract\Providercontract::getInstance();
                $project = $provider->build();
                return $project;
            }
        ]);

        $sL->addInstanceLazy("Kabinet.Bankdata", ['constructor'=>
            static function(){
                $provider = \Bitrix\Kabinet\contract\Providerbankdata::getInstance();
                $project = $provider->build();
                return $project;
            }
        ]);


        // setup HL Blocks
        (\KContainer::getInstance())->make(fn($this_)=>new \Bitrix\Kabinet\container\Hlbuilder(),'HlBuilder')
        ->make(fn($this_)=>$this_->get('HlBuilder')->get(BRIEF), BRIEF_HL)
        ->make(fn($this_)=>$this_->get('HlBuilder')->get(PROJECTSINFO), PROJECTSINFO_HL)
        ->make(fn($this_)=>$this_->get('HlBuilder')->get(PROJECTSDETAILS), PROJECTSDETAILS_HL)
        ->make(fn($this_)=>$this_->get('HlBuilder')->get(TARGETAUDIENCE), TARGETAUDIENCE_HL)
        ->make(fn($this_)=>$this_->get('HlBuilder')->get(TASK), TASK_HL)
            ->make(fn($this_)=>$this_->get('HlBuilder')->get(FULF), FULF_HL)
            ->make(fn($this_)=>$this_->get('HlBuilder')->get(CONTRACT), CONTRACT_HL)
            ->make(fn($this_)=>$this_->get('HlBuilder')->get(BANKDATE), BANKDATE_HL)
            ->make(fn($this_)=>$this_->get('HlBuilder')->get(HELP), HELP_HL)
            ->make(fn($this_)=>$this_->get('HlBuilder')->get(LMESSANGER), LMESSANGER_HL)
            ->make(fn($this_)=>$this_->get('HlBuilder')->get(BILLING), BILLING_HL)
            ->make(fn($this_)=>$this_->get('HlBuilder')->get(BILLINGHISTORY), BILLINGHISTORY_HL)
			->make(fn($this_)=>$this_->get('HlBuilder')->get(ARCHIVEFULFI), ARCHIVEFULFI_HL)
			->make(fn($this_)=>$this_->get('HlBuilder')->get(ARCHIVELMESS), ARCHIVELMESS_HL)
			->make(fn($this_)=>$this_->get('HlBuilder')->get(ARCHIVETASK), ARCHIVETASK_HL)
            ->make(function(){
                $config = new \Bitrix\Kabinet\taskrunner\states\Xmlload('/bitrix/modules/kabinet/lib/taskrunner/states/states.xml');
                return $config;
            },'states');


		(\KContainer::getInstance())->make(function(){
			$c = new \Bitrix\Kabinet\Archive();
			return $c;
		},'ARCHIVE');

        /*
         * подгружаем магазины данных при необходимости их использования
         */
        (\KContainer::getInstance())->make(function($this_){
                $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
                $projectManager = $sL->get('Kabinet.Project');

            // Bug.Fix.14.02.2025
            //$size = strlen(serialize($projectManager->catalogData()));
            $size = crc32(serialize($projectManager->catalogData()));

            $this_->addJS(SITE_TEMPLATE_PATH."/components/exi/project.list/.default/catalog_data.php?usr={$_GET['usr']}&c={$size}");
            return 'catalogStore';
        },'catalogStore')

        ->make(function($this_){
            $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
            $projectManager = $sL->get('Kabinet.Project');

            // Bug.Fix.14.02.2025
            //$size = strlen(serialize($projectManager->orderData()));
            $size = crc32(serialize($projectManager->orderData()));

            $this_->addJS(SITE_TEMPLATE_PATH."/components/exi/project.list/.default/order_data.php?usr={$_GET['usr']}&c={$size}");
            return 'orderStore';
        },'orderStore')

        ->make(function($this_){
            $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
            $projectManager = $sL->get('Kabinet.Project');

            // Bug.Fix.14.02.2025
            //$size = strlen(serialize($projectManager->getData()));
            $size = crc32(serialize($projectManager->getData()));

            $this_->addJS(SITE_TEMPLATE_PATH."/components/exi/project.list/.default/brief_data.php?usr={$_GET['usr']}&c={$size}");
            return 'briefStore';
        },'briefStore')

        ->make(function($this_){
            $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();

            // Bug.Fix.14.02.2025
            // вариант с strlen не прокатил, если к примеру UF_DATE_COMPLETION была 17.02.2025 и поменять на 28.02.2025 $size будет одинаковой
            //$size = strlen(serialize($sL->get('Kabinet.Task')->getData()));
            $size = crc32(serialize($sL->get('Kabinet.Task')->getData()));

            $this_->addJS(SITE_TEMPLATE_PATH."/components/exi/task.list/.default/task.data.php?usr={$_GET['usr']}&c={$size}");
            return 'taskStore';
        },'taskStore')

        ->make(function($this_){
            $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
            $id_task = array_column($sL->get('Kabinet.Task')->getData(), 'ID');
            $Queue_state = \CUtil::PhpToJSObject($sL->get('Kabinet.Runner')->getData($id_task), false, true);

            // Bug.Fix.14.02.2025
            //$size = strlen(serialize($Queue_state));
            $size = crc32(serialize($Queue_state));

            $this_->addJS(SITE_TEMPLATE_PATH."/components/exi/task.list/.default/queue.data.php?usr={$_GET['usr']}&c={$size}");
            return 'queueStore';
        },'queueStore')

        ->make(function($this_){
            $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
            $ClientManager = $sL->get('Kabinet.Client');
            $dataArray = $ClientManager->getData();
            $currentUser = $dataArray[0];

            // Bug.Fix.14.02.2025
            //$size = strlen(serialize($currentUser));
            $size = crc32(serialize($currentUser));

            $this_->addJS(SITE_TEMPLATE_PATH."/components/exi/profile.user/.default/user.data.php?usr={$_GET['usr']}&c={$size}");
            return 'userStore';
        },'userStore')
		
        ->make(function($this_){
            $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
            $BillingManager = $sL->get('Kabinet.Billing');
            $dataArray = $BillingManager->getData();

            // Bug.Fix.14.02.2025
            //$size = crc32(serialize($dataArray));
            $size = crc32(serialize($dataArray));

            $this_->addJS(SITE_TEMPLATE_PATH."/components/exi/billing.view/.default/billing.data.php?usr={$_GET['usr']}&c={$size}");
            return 'billingStore';
        },'billingStore');		

	}
}