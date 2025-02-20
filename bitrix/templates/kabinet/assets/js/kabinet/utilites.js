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