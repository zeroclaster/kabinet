var searchProduct = function (){

    return {
        searchfilter1(event){
            const needle = event.target.value;
            if(needle.length < 2 && needle.length > 1) return;

            if (needle.length >= 1) BX.show(this.$refs.buttonclearsearch);
            else BX.hide(this.$refs.buttonclearsearch);

            let result = [];
            let reg = new RegExp(needle,"i");
            for(index in this.data3){
                if (reg.test(this.data3[index].NAME)) result.push(this.data3[index]);
                if(needle == '') result.push(this.data3[index]);
            }

            if (typeof this.inpSaveTimer2 != 'undefined') clearTimeout(this.inpSaveTimer2);
            this.inpSaveTimer2 = setTimeout(()=>{this.listprd = result;},2000);
        },
        clearsearchinput(){
            if (typeof this.inpSaveTimer2 != 'undefined') clearTimeout(this.inpSaveTimer2);
            this.$refs.inputclearsearch.value = '';
            let result = [];
            for(index in this.data3) result.push(this.data3[index]);
            this.listprd = result;
            BX.hide(this.$refs.buttonclearsearch);
        }
    }
}