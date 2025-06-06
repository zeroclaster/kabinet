window['messangerTemplate'] =    `
<div :id="$id('messangerBlock')" class="messanger-block mt-5" v-if="typeof datamessage[queue_id] != 'undefined'">  
    <div ref="messagelist" class="messange-list p-2" v-if="datamessage[queue_id].length>0">
    
        <div ref="showmoreblock" class="mess p-2 mb-4" v-if="datamessage[queue_id].length>4">
            <button type="button" class="btn btn-sm btn-link" @click="showMore()">Показать еще <i class="fa fa-refresh" aria-hidden="true"></i></button>
        </div>
    
		<div v-for="mess_item in datamessage[queue_id]">
		<div :class="'mess p-2 pb-4 mb-4 '+isNewMessage(mess_item)">
			<div class="row">
				<div class="col-2 avatar-block pr-0"><div><img :src="mess_item.UF_AUTHOR_ID_ORIGINAL.PERSONAL_PHOTO_ORIGINAL_300x300.src"></div></div>
				<div class="col-10 text-block-mess">
					<div class="d-flex">
						<div class="user-title mr-3">{{mess_item.UF_AUTHOR_ID_ORIGINAL.PRINT_NAME}}</div>
						<div class="ml-auto datetime-message">{{mess_item.UF_PUBLISH_DATE_ORIGINAL.FORMAT3}}</div>
					</div>
					
					<div v-for="uplodfile in mess_item.UF_UPLOADFILE_ORIGINAL" class="mb-3">
					    <a :href="uplodfile.SRC" target="_blank" v-if="uplodfile.MIME == 'image/jpeg'"><img :src="uplodfile.SRC" alt=""></a>
						<a :href="uplodfile.SRC" target="_blank" v-if="uplodfile.MIME != 'image/jpeg'" style="font-size: 12px;"><i class="fa fa-file-text-o" aria-hidden="true"></i> Файл скачать</a>
                    </div>
					
					<div v-html="mess_item.UF_MESSAGE_TEXT_ORIGINAL" class="mb-5"></div>
				</div>
			</div>
			
			
			
			<div class="remove-message text-primary" @click="removemess(mess_item.ID)" v-if="accessAction(mess_item)"><i class="fa fa-times" aria-hidden="true"></i></div>
			<div class="edit-message text-primary" @click="(event) => editmess(mess_item,event)" v-if="accessAction(mess_item)"><i class="fa fa-pencil" aria-hidden="true"></i>Изменить</div>
			<div class="answer-message text-primary" @click="(event) => answermess(mess_item,event)" v-if="mess_item.UF_AUTHOR_ID != datauser.ID"><i class="fa fa-share" aria-hidden="true"></i>Ответить</div>
			<div class="cansel-edit" @click="canseledit">Отменить изменения</div>
			<div class="status-mark" v-html="printStatus(mess_item)"></div>
         </div>
		 </div>
		
		<!--
		<div class="mess p-2 mb-2" v-if="datamessage[queue_id].length==0">
			Нет комментариев
         </div>
		-->
		 
    </div> 
	<div class="p-2 mt-3">
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
`;