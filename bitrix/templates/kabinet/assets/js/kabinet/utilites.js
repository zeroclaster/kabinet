var uniqueIterator = 0;
const kabinet = {
    loading:function (status = true) {
        if (status)
        BX.addClass(
            BX('loading'),
            "active"
        );
        else
            setTimeout(()=>
            BX.removeClass(
                BX('loading'),
                "active"
            ),500);
    },
	timeConvert:function(value,to){
		
		const defaultType = ['years', 'months', 'days', 'weeks', 'hours', 'minutes', 'seconds'];
		

		if (defaultType.indexOf(to) >= 0) {
			
				let ret = 0;
				switch(to){
					case 'days':							
						ret = Math.round(value / 24);
						break;
					case 'hours':							
						ret = value;
						break;						
				}
				
				return ret;
		}
		
		return 0;
		
	},
	uniqueId:function(){
		uniqueIterator = uniqueIterator + 1;
		return uniqueIterator;
	},
	gotoElement(element){
		const y = element.getBoundingClientRect().top + window.scrollY;
		window.scroll({
			top: y,
			behavior: 'smooth'
		});
	}
}

// Общие утилиты
const commonUtils = {
	getId(component, indicator = '') {
		const componentCounters = new WeakMap();
		if (!componentCounters.has(component)) {
			componentCounters.set(component, kabinet.uniqueId());
		}
		const componentCounter = componentCounters.get(component);
		return `uid-${componentCounter}${indicator ? `-${indicator}` : ''}`;
	}
};

/**
 * Настраивает общие свойства и методы для Vue приложения
 * @param {Vue.App} app - Экземпляр Vue приложения
 */
function configureVueApp(app, contianerId = '#kabinetcontent') {
	// Добавляем метод $id для генерации уникальных идентификаторов
	app.config.globalProperties.$id = function(indicator) {
		return commonUtils.getId(this, indicator);
	};

	// Добавляем метод $href для генерации ссылок с хэшем
	app.config.globalProperties.$href = function(indicator) {
		return `#${this.$id(indicator)}`;
	};

	// Подключаем хранилище Pinia
	app.use(store);

	// Монтируем приложение в указанный элемент
	app.mount(contianerId);
}