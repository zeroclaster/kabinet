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
        /*
         * подгружаем магазины данных при необходимости их использования
         */
        (\KContainer::getInstance())->make(function($this_){
             $projectManager = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Project');

            // Bug.Fix.14.02.2025
            //$size = strlen(serialize($projectManager->catalogData()));
            $size = crc32(serialize($projectManager->catalogData()));

            $this_->addJS(SITE_TEMPLATE_PATH."/components/exi/project.list/.default/catalog_data.php?usr={$_GET['usr']}&c={$size}");
            return 'catalogStore';
        },'catalogStore')

        ->make(function($this_){
            $projectManager = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Project');

            // Bug.Fix.14.02.2025
            //$size = strlen(serialize($projectManager->orderData()));
            $size = crc32(serialize($projectManager->orderData()));

            $this_->addJS(SITE_TEMPLATE_PATH."/components/exi/project.list/.default/order_data.php?usr={$_GET['usr']}&c={$size}");
            return 'orderStore';
        },'orderStore')

        ->make(function($this_){
            $sL = \Bitrix\Main\DI\ServiceLocator::getInstance();
            $projectManager = $sL->get('Kabinet.Project');
            $infoManager = $sL->get('Kabinet.infoProject');
            $detailsManager = $sL->get('Kabinet.detailsProject');
            $targetManager = $sL->get('Kabinet.targetProject');

            $data = $projectManager->getData();
            $info_state = [];
            $details_state = [];
            $target_state = [];
            if ($data) {
                foreach ($data as $project) {
                    $info_state = array_merge($info_state, $infoManager->getData($project['ID']));
                    $details_state = array_merge($details_state, $detailsManager->getData($project['ID']));
                    $target_state = array_merge($target_state, $targetManager->getData($project['ID']));
                }
            }

            // Bug.Fix.14.02.2025
            //$size = strlen(serialize($projectManager->getData()));
            $size = crc32(serialize($projectManager->getData()));
            $size2 = crc32(serialize($info_state));
            $size3 = crc32(serialize($details_state));
            $size4 = crc32(serialize($target_state));

            $this_->addJS(SITE_TEMPLATE_PATH."/components/exi/project.list/.default/brief_data.php?usr={$_GET['usr']}&c={$size}_{$size2}_{$size3}_{$size4}");
            return 'briefStore';
        },'briefStore')

        ->make(function($this_){
            // Bug.Fix.14.02.2025
            // вариант с strlen не прокатил, если к примеру UF_DATE_COMPLETION была 17.02.2025 и поменять на 28.02.2025 $size будет одинаковой
            //$size = strlen(serialize($sL->get('Kabinet.Task')->getData()));
            $size = crc32(serialize(\Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Task')->getData()));

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
            $dataArray = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Client')->getData();
            $currentUser = $dataArray[0];

            // Bug.Fix.14.02.2025
            //$size = strlen(serialize($currentUser));
            $size = crc32(serialize($currentUser));

            $this_->addJS(SITE_TEMPLATE_PATH."/components/exi/profile.user/.default/user.data.php?usr={$_GET['usr']}&c={$size}");
            return 'userStore';
        },'userStore')
		
        ->make(function($this_){
            $dataArray = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('Kabinet.Billing')->getData();

            // Bug.Fix.14.02.2025
            //$size = crc32(serialize($dataArray));
            $size = crc32(serialize($dataArray));

            $this_->addJS(SITE_TEMPLATE_PATH."/components/exi/billing.view/.default/billing.data.php?usr={$_GET['usr']}&c={$size}");
            return 'billingStore';
        },'billingStore');		

	}
}