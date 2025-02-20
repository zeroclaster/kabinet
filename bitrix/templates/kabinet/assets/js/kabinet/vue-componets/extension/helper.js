/*
for use
Asset::getInstance()->addJs(SITE_TEMPLATE_PATH."/assets/js/kabinet/vue-componets/extension/helper.js");

methods: {
...helperVueComponents()
}

 */

var helperVueComponents = function(){

    return {
        findinArrayByID(findArray,needle){

            let finded;

            for(index in findArray){
                if (needle == findArray[index]['ID']) {
                    finded = findArray[index];
                    break;
                }
            }
            return finded;
        },
        viewListFieldTitle(object,field){
            let orgField = field+'_ORIGINAL';
            let finded = this.findinArrayByID(object[orgField],object[field]);
            if (typeof finded == "undefined") return "";
            return finded.VALUE
        }
    };
}