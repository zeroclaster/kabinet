window['messangerTemplate'] = `
<div :id="$id('messangerBlock')" class="messanger-block message-dashboard">  
    <div ref="messagelist" class="messange-list p-2">
       
        <div ref="showmoreblock" class="mess p-2 mb-4" v-if="datamessage.length>limit">
            <button type="button" class="btn btn-sm btn-link" @click="showMore()">Показать еще <i class="fa fa-refresh" aria-hidden="true"></i></button>
        </div>
    
    <!-- datamessage -> $arResult["MESSAGE_DATA"] bitrix/components/exi/messanger.view/class.php -->
		<div v-for="mess_item in datamessage">
		<div :class="'mess p-2 pb-4 mb-4 '+isNewMessage(mess_item)">
				
			<div class="row" v-if="mess_item.UF_TYPE == 3">
				<div class="col-2 avatar-block pr-0"><div><img :src="mess_item.UF_AUTHOR_ID_ORIGINAL.PERSONAL_PHOTO_ORIGINAL_300x300.src"></div></div>
				<div class="col-10 text-block-mess">					
					<div class="d-flex">
						<div class="user-title mr-3">{{mess_item.UF_AUTHOR_ID_ORIGINAL.PRINT_NAME}}</div>
						<div class="datetime-message">{{mess_item.UF_PUBLISH_DATE_ORIGINAL.FORMAT3}}</div>
					</div>
									
					<div v-if="mess_item.UF_PROJECT_ID>0">				
						{{(project = projectlist[mess_item.UF_PROJECT_ID],null)}}
						<div>				
						    Проект <span class="">«{{project.UF_NAME}}» #{{project.UF_EXT_KEY}}</span>				
                            <span v-if="mess_item.UF_TASK_ID>0">
                                {{(task = tasklist[mess_item.UF_TASK_ID],null)}}
                                {{(order = data2[project.UF_ORDER_ID][task.UF_PRODUKT_ID],null)}}
                                , Задача <a :href="'/kabinet/projects/reports/?t='+task.ID">«{{task.UF_NAME}}» #{{task.UF_EXT_KEY}}</a>                 
                            </span>					        
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
						<div>Проект «{{project.UF_NAME}}»	#{{project.UF_EXT_KEY}}			
						<span v-if="mess_item.UF_TASK_ID>0">
							{{(task = tasklist[mess_item.UF_TASK_ID],null)}}
							{{(order = data2[project.UF_ORDER_ID][task.UF_PRODUKT_ID],null)}}
							, Задача <a :href="'/kabinet/projects/reports/?t='+task.ID">«{{task.UF_NAME}}» #{{task.UF_EXT_KEY}}</a>
						</span>
						</div>	
					</div>
				</div>
			</div>
			
			<div class="status-mark" v-html="printStatus(mess_item)"></div>
         </div>
		 </div>
		
		<div class="mess p-2 mb-2" v-if="datamessage.length==0">
			Нет комментариев
         </div>		 		  
    </div> 
</div>
`;