class pagehelp {

    constructor(phpparams) {
        this.phpparams = phpparams;
        this.cookie_prefix = phpparams.cookie_prefix;

        if (typeof phpparams.CONTAINER_ID === "undefined" || phpparams.CONTAINER_ID == '')
            throw "Field CONTAINER_ID not found!";

        if (typeof phpparams.cookie_prefix === "undefined" || phpparams.cookie_prefix == '')
            throw "Field cookie_prefix not found!";

        if (typeof phpparams.CODE === "undefined" || phpparams.CODE == '')
            throw "Field CODE not found!";

        BX.ready(BX.delegate(this.init,this));
    }

    init(){
        const this_ = this;
        let phpparams = this.phpparams;
        this.CONTAINER_NODE = BX(phpparams.CONTAINER_ID);

        let node = BX.findChild(this.CONTAINER_NODE,{class:'close-button'},true,false);
        BX.bind(node, 'click', BX.delegate(this_.closeHelp, this_));

        node = BX.findChild(document.body,{attribute:['data-component']},true,true);
        node.forEach((element) =>{
                const code = BX.data(element,'code');
                if (code && code == this.phpparams.CODE) {
                    this.buttom = element;
                    BX.bind(element, 'click', BX.delegate(this.openHelp, this));
                }
        });

        const cookieCode = this.cookie_prefix + "_pagehelp"+this.phpparams.CODE;
        if (typeof BX.getCookie(cookieCode) == "undefined" || BX.getCookie(cookieCode) == '') {
            //BX.show(this.CONTAINER_NODE);
            //this.openHelp();
        }

    }

    closeHelp(){
        BX.hide(this.CONTAINER_NODE);
        this.saveCookie("y");
        BX.show(this.buttom);
    }

    openHelp(){
        BX.show(this.CONTAINER_NODE);
        BX.hide(this.buttom);
    }

    saveCookie(s){
        var cookie_prefix = this.cookie_prefix;
        var cookie_date = new Date();

        if (s == null) s = '';

        //cookie_date.setDate(cookie_date.getDate() + 1);
        cookie_date.setMonth(cookie_date.getMonth() + 12);
        document.cookie = cookie_prefix + "_pagehelp"+this.phpparams.CODE+"="+s+"; expires="+cookie_date.toGMTString()+"; path=/;";
    }

}