<?php

return [
    'services' => [

        'user' => [
            'constructor' => function() {
                global $USER;
                if (!$USER || !$USER->IsAuthorized()) {
                    return false;
                }

                if (\PHelp::isAdmin()){
                    if ($_REQUEST['usr']){
                                $id = $_GET['usr'];
                                $object = \Bitrix\Kabinet\Usertable::getByPrimary($id)->fetchObject();
                                return $object;
                    }else{
                                $object = \Bitrix\Kabinet\Usertable::createObject();
                                $object->set('ID',0);
                                return $object;
                    }
                }

                $id = $USER->GetID();
                return \Bitrix\Kabinet\Usertable::getByPrimary($id)->fetchObject();
            }
        ],

        'adminuser' => [
            'context' => 'admin',
            'constructor' => function() {
                global $USER;
                if ($USER && $USER->IsAuthorized()) {
                    $id = $USER->GetID();
                    return \Bitrix\Kabinet\Usertable::getByPrimary($id)->fetchObject();
                }
                return false;
            }
        ],

        'siteuser' => [
            'constructor' => '@adminuser' // Просто алиас для adminuser.object
        ],

        // Новые сервисы для HL-блоков
        'HlBuilder' => ['class' => \Bitrix\Kabinet\container\Hlbuilder::class],

        'BRIEF_HL' =>           ['constructor' => '@HlBuilder->get(4)'],
        'PROJECTSINFO_HL' =>    ['constructor' => '@HlBuilder->get(8)'],
        'PROJECTSDETAILS_HL' => ['constructor' => '@HlBuilder->get(9)'],
        'TARGETAUDIENCE_HL' =>  ['constructor' => '@HlBuilder->get(12)'],
        'TASK_HL' =>            ['constructor' => '@HlBuilder->get(13)'],
        'FULF_HL' =>            ['constructor' => '@HlBuilder->get(14)'],
        'CONTRACT_HL' =>        ['constructor' => '@HlBuilder->get(16)'],
        'BANKDATE_HL' =>        ['constructor' => '@HlBuilder->get(17)'],
        'HELP_HL' =>            ['constructor' => '@HlBuilder->get(18)'],
        'LMESSANGER_HL' =>      ['constructor' => '@HlBuilder->get(19)'],
        'BILLING_HL' =>         ['constructor' => '@HlBuilder->get(20)'],
        'BILLINGHISTORY_HL' =>  ['constructor' => '@HlBuilder->get(21)'],
        'ARCHIVEFULFI_HL' =>    ['constructor' => '@HlBuilder->get(23)'],
        'ARCHIVELMESS_HL' =>    ['constructor' => '@HlBuilder->get(24)'],
        'ARCHIVETASK_HL' =>     ['constructor' => '@HlBuilder->get(25)'],

        'boot.admin' => [
            'class' => \Bitrix\Kabinet\bootstrap\Adminbootservice::class,
            'arguments' => ['@HlBuilder']
        ],
        'boot.user' => [
            'class' => \Bitrix\Kabinet\bootstrap\Userbootservice::class,
            'arguments' => ['@HlBuilder']
        ],

        'Kabinet.AdminRunner' => [
            'class' => \Bitrix\Kabinet\taskrunner\RunnerManager::class,
            'arguments' => [
                '@user',
                '@FULF_HL',
                '%runner.config.admin%'
            ],
            'context' => 'admin' // Указываем контекст
        ],

        'Kabinet.UserRunner' => [
            'class' => \Bitrix\Kabinet\taskrunner\RunnerManager::class,
            'arguments' => [
                '@user',
                '@FULF_HL',
                '%runner.config.user%'
            ],
            'context' => 'user' // Указываем контекст
        ],

        'Kabinet.Runner' => [
            'constructor' => function() {
                $locator = \Bitrix\Main\DI\ServiceLocator::getInstance();
                return \PHelp::isAdmin()
                    ? $locator->get('Kabinet.AdminRunner')
                    : $locator->get('Kabinet.UserRunner');
            }
        ],

        'Kabinet.Task' => [
            'class' => \Bitrix\Kabinet\task\TaskManager::class,
            'arguments' => [
                '@user',
                '@TASK_HL',
                [],
                '@Kabinet.Runner',
            ]
        ],
        'Kabinet.Project' => [
            'class' => \Bitrix\Kabinet\project\Projectmanager::class,
            'arguments' => [
                '@user',
                '@BRIEF_HL'
            ]
        ],
        'Kabinet.infoProject' => [
            'class' => \Bitrix\Kabinet\project\Infomanager::class,
            'arguments' => [
                '@user',
                '@PROJECTSINFO_HL'
            ]
        ],
        'Kabinet.detailsProject' => [
            'class' => \Bitrix\Kabinet\project\Detailsmanager::class,
            'arguments' => [
                '@user',
                '@PROJECTSDETAILS_HL'
            ]
        ],
        'Kabinet.targetProject' => [
            'class' => \Bitrix\Kabinet\project\Targetmanager::class,
            'arguments' => [
                '@user',
                '@TARGETAUDIENCE_HL'
            ]
        ],

        'Kabinet.Bankdata' => [
            'class' => \Bitrix\Kabinet\contract\Bankdatamanager::class,
            'arguments' => [
                '@user',
                '@BANKDATE_HL'
            ]
        ],

        'Kabinet.Contract' => [
            'class' => \Bitrix\Kabinet\contract\Contractmanager::class,
            'arguments' => [
                '@user',
                '@CONTRACT_HL'
            ]
        ],

        'Kabinet.Client' => [
            'class' => \Bitrix\Kabinet\client\Clientmanager::class,
            'arguments' => [
                '@user',
                '%client.config%'
            ]
        ],

        "Kabinet.AdminClient" => [
            'class' => \Bitrix\Kabinet\client\Clientmanager::class,
            'arguments' => [
                '@user',
                '%adminclient.config%'
            ]
        ],

        'Kabinet.BilligHistory' => [
            'class' => \Bitrix\Kabinet\billing\History::class,
            'arguments' => [
                '@user',
                '@BILLINGHISTORY_HL'
            ]
        ],

        // Админские сервисы
        'Kabinet.AdminBilling' => [
            'class' => \Bitrix\Kabinet\billing\Billing::class,
            'arguments' => ['@user', '@BILLING_HL','@Kabinet.BilligHistory','%billing.config.admin%'],
            'context' => 'admin' // Указываем контекст
        ],

        // Пользовательские сервисы
        'Kabinet.UserBilling' => [
            'class' => \Bitrix\Kabinet\billing\Billing::class,
            'arguments' => ['@user', '@BILLING_HL','@Kabinet.BilligHistory','%billing.config.user%'],
            'context' => 'user'
        ],

        // Динамический сервис (выбирает реализацию автоматически)
        'Kabinet.Billing' => [
            'constructor' => function() {
                $locator = \Bitrix\Main\DI\ServiceLocator::getInstance();
                return \PHelp::isAdmin()
                    ? $locator->get('Kabinet.AdminBilling')
                    : $locator->get('Kabinet.UserBilling');
            }
        ],

        "Kabinet.ClientMessanger" => [
            'class' => \Bitrix\Kabinet\client\Clientmanager::class,
            'arguments' => [
                '@user',
                [],
                '%ClientMessanger.config.allowFileds%'
            ]
        ],

        "Kabinet.AdminMessanger" => [
            'class' => \Bitrix\Kabinet\messanger\Messanger::class,
            'arguments' => [
                '@user',
                '@LMESSANGER_HL',
                "@Kabinet.ClientMessanger",
                '%messanger.config.admin%'
            ],
            'context' => 'admin' // Указываем контекст
        ],

        "Kabinet.UserMessanger" => [
            'class' => \Bitrix\Kabinet\messanger\Messanger::class,
            'arguments' => [
                '@user',
                '@LMESSANGER_HL',
                "@Kabinet.ClientMessanger",
                '%messanger.config.user%'
            ],
            'context' => 'user' // Указываем контекст
        ],

        'Kabinet.Messanger' => [
            'constructor' => function() {
                $locator = \Bitrix\Main\DI\ServiceLocator::getInstance();
                return \PHelp::isAdmin()
                    ? $locator->get('Kabinet.AdminMessanger')
                    : $locator->get('Kabinet.UserMessanger');
            }
        ],

        'ARCHIVE' => [
            'class' => \Bitrix\Kabinet\Archive::class
        ],

        'states' => [
            'class' => \Bitrix\Kabinet\taskrunner\states\Xmlload::class,
            'arguments' => [
                '%states.config.xmlfile%'
            ]
        ],

    ],

    'parameters' => [
        'log.file' => '/var/log/bitrix.log',
        'client.config' => [
            'UF_GROUP_REF.GROUP_ID'=>REGISTRATED,
            //'>PROJECTS.ID'=>0,
            //'PROJECTS.UF_ACTIVE'=>1
        ],
        'adminclient.config' => [
            'UF_GROUP_REF.GROUP_ID'=>MANAGER,
        ],
        'states.config.xmlfile' => '/bitrix/modules/kabinet/lib/taskrunner/states/states.xml',
        'runner.config.user' => include __DIR__.'/taskrunner/config.php',
        'runner.config.admin' => include __DIR__.'/taskrunner/admin/config.php',
        'billing.config.admin' => include __DIR__.'/billing/config.php',
        'billing.config.user' => include __DIR__.'/billing/config.php',
        'messanger.config.admin'=> include __DIR__.'/messanger/admin/config.php',
        'messanger.config.user'=> include __DIR__.'/messanger/config.php',
        'ClientMessanger.config.allowFileds' => ['ID','LOGIN','NAME','LAST_NAME','SECOND_NAME','PERSONAL_PHOTO','PERSONAL_PROFESSION'],
        'task.config.priority' => 50,
        'task.config.timeout' => 60,
        // Константы HL-блоков (оставлены для совместимости или других нужд)
        'hl.ids' => [
            'BRIEF' => 4,
            'PROJECTSINFO' => 8,
            'PROJECTSDETAILS' => 9,
            'TARGETAUDIENCE' => 12,
            'TASK' => 13,
            'FULF' => 14,
            'CONTRACT' => 16,
            'BANKDATE' => 17,
            'HELP' => 18,
            'LMESSANGER' => 19,
            'BILLING' => 20,
            'BILLINGHISTORY' => 21,
            'ARCHIVEFULFI' => 23,
            'ARCHIVELMESS' => 24,
            'ARCHIVETASK' => 25
        ],
    ]
];
