window['messangerTemplateAdmin'] = `
<div :id="$id('messangerBlock')" class="row messanger-block message-dashboard">

        <div class="col-md-4 messange-user-list p-0">
        
        <div class="ml-0 mr-0 user_item p-3" :class="{ active: $root.active_user === 'allusers' }">
            <div class="d-flex align-items-center p-0" @click="$root.userChange()">
                <div class="avatar-block"><div><img src="/bitrix/templates/kabinet/assets/images/users/user_nofoto.jpeg" style="width: 50px;"></div></div>
                <div class="pl-2">Все сообщения</div>
            </div>
        </div>
        
            <div v-for="user_item in $root.alluser" class="ml-0 mr-0 user_item p-3" :class="{ active: $root.active_user == user_item.ID }">
                <div class="d-flex align-items-center p-0" @click="$root.userChange(user_item.ID)">
                <div class="avatar-block"><div><img :src="user_item.PERSONAL_PHOTO_ORIGINAL_300x300.src" style="width: 50px;"></div></div>
                <div class="pl-2">{{user_item.PRINT_NAME}} #{{user_item.ID}}</div>
                </div>
            </div>
        </div>

    <div ref="messagelist" class="col-md-8 messange-list p-2"> 
    
    <!-- Добавляем заголовок с именем выбранного пользователя -->
        <div class="selected-user-header p-2 mb-3 border-bottom" v-if="$root.active_user !== 'allusers' && $root.selectedUserName">
            <h5>Сообщения пользователя: {{ $root.selectedUserName() }}</h5>
        </div>
          
        <div ref="showmoreblock" class="mess p-2 mb-4" v-if="datamessage.length>limit">
            <button type="button" class="btn btn-sm btn-link" @click="showMore()">Показать еще <i class="fa fa-refresh" aria-hidden="true"></i></button>
        </div>
      
    <!-- datamessage -> $arResult["MESSAGE_DATA"] bitrix/components/exi/messanger.view/class.php -->
		<div v-for="mess_item in datamessage">
		<div :class="'mess p-2 pb-4 mb-0 '+isNewMessage(mess_item)+' '+isAdminMessage(mess_item)">
				
			{{(fulfi = $parent.getQualityById(mess_item.UF_QUEUE_ID), null)}}		
				
			<div class="row" v-if="mess_item.UF_TYPE == 3">
				<div class="col-2 avatar-block pr-0 mr-2"><div @click="$root.userChange(mess_item.UF_AUTHOR_ID_ORIGINAL.ID,mess_item.UF_AUTHOR_ID_ORIGINAL.IS_ADMIN)"><img :src="mess_item.UF_AUTHOR_ID_ORIGINAL.PERSONAL_PHOTO_ORIGINAL_300x300.src"></div></div>
				<div class="col-10 text-block-mess">			  			
					<div class="d-flex">
						<div class="user-title mr-3" @click="$root.userChange(mess_item.UF_AUTHOR_ID_ORIGINAL.ID,mess_item.UF_AUTHOR_ID_ORIGINAL.IS_ADMIN)">{{mess_item.UF_AUTHOR_ID_ORIGINAL.PRINT_NAME}}</div>
						<div class="datetime-message">{{mess_item.UF_PUBLISH_DATE_ORIGINAL.FORMAT3}}</div>
					</div>
									
					<div v-if="mess_item.UF_PROJECT_ID>0">				
						{{(project = projectlist[mess_item.UF_PROJECT_ID],null)}}
						<div>
						    <form :ref="'formfilter1-'+mess_item.ID" action="/kabinet/admin/performances/" method="post">				
						    Проект <span class="">«{{project.UF_NAME}}» #{{project.UF_EXT_KEY}}</span>				
                            <span v-if="mess_item.UF_TASK_ID>0">
                                {{(task = tasklist[mess_item.UF_TASK_ID],null)}}                            
                                , Задача <a href="#" @click.prevent="$refs['formfilter1-' + mess_item.ID][0].submit()">«{{task.UF_NAME}}» #{{task.UF_EXT_KEY}}</a>                 
                            </span>                    
                                    <template v-if="fulfi">
                                    <input name="executionidsearch" type="hidden" :value="fulfi.UF_EXT_KEY">
                                    </template>
                            </form>				        
						</div>											
					</div>
					
					<div v-for="uplodfile in mess_item.UF_UPLOADFILE_ORIGINAL" class="mb-3">
					    <a :href="uplodfile.SRC" target="_blank" v-if="uplodfile.MIME == 'image/jpeg'"><img :src="uplodfile.SRC" alt="" style="width: 300px;"></a>
						<a :href="uplodfile.SRC" target="_blank" v-if="uplodfile.MIME != 'image/jpeg'" style="font-size: 12px;"><i class="fa fa-file-text-o" aria-hidden="true"></i> Файл скачать</a>
                    </div>
					
					<div v-html="mess_item.UF_MESSAGE_TEXT_ORIGINAL" class=""></div>
				</div>
			</div>
			
			<div class="row" v-if="mess_item.UF_TYPE == 4">	
				<div class="col-12 text-block-mess">				
					<div>
						<div class="user-title mb-1"></div><div class="datetime-message">{{mess_item.UF_PUBLISH_DATE_ORIGINAL.FORMAT3}}</div>
					</div>
					<div v-html="mess_item.UF_MESSAGE_TEXT_ORIGINAL" class=""></div>
					<div v-if="mess_item.UF_PROJECT_ID>0">
						{{(project = projectlist[mess_item.UF_PROJECT_ID],null)}}	
						<div>
						    <form :ref="'formfilter2-'+mess_item.ID" action="/kabinet/admin/performances/" method="post">
                            Проект «{{project.UF_NAME}}»	#{{project.UF_EXT_KEY}}			
                            <span v-if="mess_item.UF_TASK_ID>0">
                                {{(task = tasklist[mess_item.UF_TASK_ID],null)}}
                                , Задача <a href="#" @click.prevent="$refs['formfilter2-' + mess_item.ID][0].submit()">«{{task.UF_NAME}}» #{{task.UF_EXT_KEY}}</a>
                            </span>
                                <template v-if="fulfi">
                                    <input name="executionidsearch" type="hidden" :value="fulfi.UF_EXT_KEY">
                                </template>
                            </form>
						</div>	
					</div>
				</div>
			</div>
			
			<div class="status-mark" v-html="printStatus(mess_item)"></div>
         </div>
		 </div>
		
		<div class="mess p-2 mb-2" v-if="datamessage.length==0">
			Нет сообщений
         </div>
         
         	<div class="p-2 mt-3" v-if="$root.active_user !== 'allusers'">
            <div ref="senderblock" class="sender-block">
                <form action="">
                    <div class="d-flex">
                        <div class="upload-file-block">
                            <messUploadFileComponent v-model="fields.UF_UPLOADFILE"/>
                        </div>
                        <div class="message-text-block">
                            <div class="upload-file-list d-flex flex-wrap" v-if="fields.UF_UPLOADFILE.length>0">
                     
                                <div class="mr-2 p-2" v-for="(upl_file,fileIndex) of fields.UF_UPLOADFILE">{{upl_file.name}} <div class="remove-upload-file text-primary" @click="removeUplFile(fileIndex)"><i class="fa fa-times" aria-hidden="true"></i></div></div>
                              
                            </div>
                            <richtext ref="richtextref" :original="fields.UF_MESSAGE_TEXT_ORIGINAL" v-model="fields.UF_MESSAGE_TEXT"/>
                        </div>
                        <div class="sender-block ml-auto d-flex align-items-center">
                            <button class="btn btn-primary btn-sm send-message-button" type="button" @click="sendMessage" :disabled="isDisabled"><i class="fa fa-paper-plane-o" aria-hidden="true"></i> Отправить</button>
                        </div>
                    </div>
                </form>
            </div>
            </div>
         
         		 		  
    </div> 
</div>
`;