/*
for use
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/assets/js/kabinet/vue-componets/extension/addnewmethods.js");

methods: {
...addNewMethods()
}

 */

var addNewMethods = function(){
	
/*
	function myFormData() {
	  FormData.constructor.call(this); // call super constructor.
	}

	myFormData.__proto__ = FormData.prototype;
	
	
	
	console.log(new myFormData());
	console.log(new FormData());
	
	var t = new myFormData();
	console.log(t.append('id','12'));
*/
	
	return {
		dataToFormData(instance,formdata=null,prefix=''){

				var form_data = formdata? formdata : new FormData();

				 for ( var key in instance ) {
						if ((typeof instance[key] == 'object')	&& (Object.prototype.toString.call(instance[key]) == '[object FileList]')){

							if (instance[key].length == 1) {
								if (instance[key].length == 0) form_data.append(prefix + key, 0);
								for (const file of instance[key]) form_data.append(prefix + key, file);
							}else {
								if (instance[key].length == 0) form_data.append(prefix + key+ '[]', 0);
								for (const file of instance[key]) form_data.append(prefix + key+ '[]', file);
							}
						}

						if (Array.isArray(instance[key])){
							instance[key].forEach(function (item,index) {
								if (typeof item == 'object' && typeof item.VALUE != 'undefined')
										form_data.append(prefix+key + '[]', item.VALUE);
								else
									form_data.append(prefix+key + '[]', item);
							});
						}else
							form_data.append(prefix+key, instance[key]);
				}	

				return form_data;		
		},
		saveData(query,formdata,callback){
				const kabinetStore = usekabinetStore();
				kabinet.loading();
				BX.ajax.runAction(query, {
					data : formdata,
					// usr_id_const нужен для админа, задается в footer.php
					getParameters: {usr : usr_id_const},
					//processData: false,
					//preparePost: false
				})
					.then(function(response) {
						const data = response.data;
						kabinetStore.NotifyOk = '';
						kabinetStore.NotifyOk = data.message;

						callback(data);

						kabinet.loading(false);
					}, function (response) {
						kabinet.loading(false);
							if (response.errors[0].code != 0) {
								kabinetStore.Notify = '';
								kabinetStore.Notify = response.errors[0].message;
							}else {
								kabinetStore.Notify = '';
								kabinetStore.Notify = "Возникла системная ошибка! Пожалуйста обратитесь к администратору сайта.";
							}
					});
		},
	};
}