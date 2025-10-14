var searchProduct = function (){

    return {
        async searchfilter1(event){
            const needle = event.target.value.trim();

            if(needle.length < 2) {
                if(needle.length === 0) {
                    this.listprd = [...Object.values(this.data3)];
                    BX.hide(this.$refs.buttonclearsearch);
                }
                return;
            }

            BX.show(this.$refs.buttonclearsearch);

            // Очищаем предыдущий таймер
            if (this.searchTimer) {
                clearTimeout(this.searchTimer);
            }

            // Устанавливаем новый таймер с задержкой 800ms
            this.searchTimer = setTimeout(async () => {
                try {
                    const response = await BX.ajax.runAction('bitrix:kabinet.evn.briefevents.search', {
                        data: {
                            q: needle,
                        },
                        getParameters: {usr : usr_id_const}
                    });

                    if(response.data) {
                        this.listprd = response.data;
                    } else {
                        this.listprd = [];
                    }

                } catch (error) {
                    console.error('Search failed:', error);
                    const kabinetStore = usekabinetStore();
                    kabinetStore.Notify = '';
                    kabinetStore.Notify = "Возникла системная ошибка! Пожалуйста обратитесь к администратору сайта.";
                }
            }, 800);
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