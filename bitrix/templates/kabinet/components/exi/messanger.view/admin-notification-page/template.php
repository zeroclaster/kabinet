<?
use Bitrix\Main\Localization\Loc as Loc;
use Bitrix\Main\Page\Asset;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */


Loc::loadMessages(__FILE__);
$this->setFrameMode(true);
?>
<?/*
шаблон
data-usermessanger="notification"
bitrix/templates/kabinet/assets/js/kabinet/vue-componets/messanger/templates/user.notification.js
нет кнопки отправить, только чтение

*/?>
<div id="messangerblock" class="form-group" data-ckeditor="" data-vuerichtext="" data-usermessanger="notification"></div>

<script type="text/html" id="messangerviewtemolate">
    <section class="section-xs">
        <div class="container-fluid">
            <div class="row row-30">
                <div class="col-md-12">
                    <div class="panel">
                            <messangerperformances ref="messangerRef" :projectID="0" :taskID="0" :targetUserID="datauser.ID" :queue_id="0"/>
                    </div>
                </div>
            </div>
        </div>
    </section>
</script>

<?
(\KContainer::getInstance())->get('catalogStore','orderStore','userStore');
?>

<script>
    const briefListStoreData = <?=CUtil::PhpToJSObject($arResult["PROJECT_DATA"], false, true)?>;
    const  brieflistStore = BX.Vue3.Pinia.defineStore('brieflist', {
        state: () => ({
            data:briefListStoreData
        }),
    });

    const taskListStoreData = <?=CUtil::PhpToJSObject($arResult["TASK_DATA"], false, true)?>;
    const  tasklistStore = BX.Vue3.Pinia.defineStore('tasklist', {
        state: () => (
            {
                datatask:taskListStoreData
            })
    });

    const FulfiListStoreData = <?=CUtil::PhpToJSObject($arResult["RUNNER_DATA"], false, true)?>;
    const  FulfilistStore = BX.Vue3.Pinia.defineStore('fulfilist', {
        state: () => (
            {
                datafulfi:FulfiListStoreData
            })
    });

    const all_users = <?=CUtil::PhpToJSObject($arResult["ALL_USER"], false, true)?>;
</script>


<script>
    components.messangerUsernotification = {
        selector: "[data-usermessanger='notification']",
        script: [
            './js/kabinet/vue-componets/messanger/uploadfile.js',
            './js/kabinet/vue-componets/messanger/templates/admin.notification.js',
            './js/kabinet/vue-componets/messanger/messanger.factory.js'
        ],
        styles: './css/messanger.css',
        dependencies:'vuerichtext',
        init:null
    }
    var messangerperformances = null;
    window.addEventListener("components:ready", function(event) {
        const messangerSystem2 = createMessangerSystem();
        messangerperformances = messangerSystem2.component.start(<?=CUtil::PhpToJSObject([
            'VIEW_COUNT' => $arParams['COUNT'],
            'TEMPLATE' => 'messangerTemplateAdmin'
        ], false, true)?>);
        messangerSystem2.store().$patch({ datamessage: <?=CUtil::PhpToJSObject($arResult["MESSAGE_DATA"], false, true)?> });

        const messangerViewApplication = BX.Vue3.BitrixVue.createApp({
            data() {
                return {
                    alluser : all_users,
                    active_user : 'allusers'
                }
            },
            computed: {
                ...BX.Vue3.Pinia.mapState(userStore, ['datauser']),
                ...BX.Vue3.Pinia.mapState(FulfilistStore, ['datafulfi']),
            },
            methods: {
                // Добавляем вычисляемое свойство для имени выбранного пользователя
                selectedUserName() {
                    if (this.$root.active_user && this.$root.active_user !== 'allusers') {
                        const user = this.$root.alluser.find(u => u.ID == this.$root.active_user);
                        return user ? user.PRINT_NAME : '';
                    }
                    return '';
                },
                getQualityById(id){
                    return this.datafulfi.find(item => item.ID === id) || null;
                },
                userChange(id = 0, IS_ADMIN = false) {

                    if (IS_ADMIN) return;

                    this.active_user = id || 'allusers';
                    kabinet.loading();

                    BX.ajax.runComponentAction('exi:messanger.view', 'filterByUser', {
                        mode: 'class',
                        data: {
                            userId: id,
                            count: <?=$arParams['COUNT']?>,
                            new_reset: 'n'
                        }
                    }).then(response => {
                        brieflistStore().$patch({ data: response.data.PROJECT_DATA });
                        tasklistStore().$patch({ datatask: response.data.TASK_DATA });
                        FulfilistStore().$patch({ datafulfi: response.data.RUNNER_DATA });
                        messangerSystem2.store().$patch({ datamessage: response.data.MESSAGE_DATA });

                        // Устанавливаем UF_TARGET_USER_ID через $root
                        if (this.$refs.messangerRef) {
                            this.$refs.messangerRef.fields.UF_TARGET_USER_ID = id;
                        }

                        kabinet.loading(false);

                        // Добавляем прокрутку после загрузки данных
                        this.$nextTick(() => {
                            this.scrollToAppropriateBlock();
                        });

                    }).catch(error => {
                        console.error(error);
                        kabinet.loading(false);
                    });
                },
                // Метод для прокрутки к нужному блоку
                scrollToAppropriateBlock() {
                    if (this.$refs.messangerRef) {
                        if (this.active_user === 'allusers') {
                            // Прокрутка к последнему сообщению
                            this.$refs.messangerRef.scrollEnd();
                        } else {
                            // Прокрутка к блоку отправки сообщений
                            this.$refs.messangerRef.scrollToSender();
                        }
                    }
                }
            },
            components: {
                messangerperformances
            },
            // language=Vue
            template: '#messangerviewtemolate'
        });

        configureVueApp(messangerViewApplication,'#messangerblock');
    });


    window.addEventListener("components:ready", function(event) {
        $( '.to-bottom' ).on( ( 'mousedown' ), function () {
            this.classList.add( 'active' );
            const footer = document.querySelector('footer');
            const targetPosition = footer ? footer.offsetTop : document.body.scrollHeight;
            $( 'html, body' ).stop().animate( { scrollTop:targetPosition }, 500, 'swing', (function () {
                this.classList.remove( 'active' );
            }).bind( this ));
        });
    });
</script>
