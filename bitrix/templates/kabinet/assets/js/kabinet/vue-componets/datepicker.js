
//https://momentjs.com/docs/#/displaying/
/*
moment().day(-7); // last Sunday (0 - 7)
moment().day(0); // this Sunday (0)
moment().day(7); // next Sunday (0 + 7)
moment().day(10); // next Wednesday (3 + 7)
moment().day(24); // 3 Wednesdays from now (3 + 7 + 7 + 7)
 */
const mydatepicker = BX.Vue3.BitrixVue.mutableComponent('date-picker', {
    template: `
<input ref="input" :id="id_input" type="text" @input="sendvalinput" v-model="localModelValue" class="form-control"/>
<div class="input-group-append">
    <label class="input-group-text" :for="id_input"><span class="fa fa-calendar"></span></label>
</div>
`,	data(){
        return{
            datechenge: {},
            id_input:'#inpid'+kabinet.uniqueId()
        }
    },
    props: ['modelValue','tindex','mindd','original'],
    computed: {
        localModelValue: {
            /* liveHack
             нужна что бы можно было обновлять modelValue, и не возникала ошибка modelValue только для чтения
             тут v-model приходит в переменной props: ['modelValue'
            <mytypeahead v-model="runner.UF_LINK" ....
            и ее же помещаем в
            <input v-model="modelValue" ....
            для обновления сохраненного значения, но при изменении идет обращение к modelValue, но она только для чтения
             */
            get() {
                if (typeof this.modelValue != "undefined" &&  this.modelValue != "") {
                    let mindate = moment.unix(this.modelValue);
                    return mindate.format("DD.MM.YYYY");
                }
                else return '';
            },
            set(newValue) {
                const newDate = moment(newValue, "DD.MM.YYYY");
                this.$emit('update:modelValue', newDate.unix())
            },
        },
    },
    mounted () {
        // Add event handler
        let mindate = moment();
        if (typeof this.mindd != "undefined") mindate = moment.unix(this.mindd);

        $(this.$refs.input).datetimepicker({
            locale: moment.locale('ru'),
            format: 'DD.MM.YYYY',
            minDate: mindate.toDate()//new Date()
        })
            .on('dp.change', (event) => {
                this.updateValue(event.date);
                //console.log(event.date);
            });
        // Set datepicker's value to initial date

        //YYYY-MM-DD[T]HH:mm:ssZ

        //const newDate = moment(this.original, "DD.MM.YYYY");

        //$(this.$refs.input).data('DateTimePicker').date(newDate);
        //$(this.$refs.input).data('DateTimePicker').date(newDate);

        this.$root.datechenge = $(this.$refs.input).data('DateTimePicker');
    },
    methods: {
        updateValue (value) {
            this.$emit('update:modelValue', value.format('X'));
            this.$root.savetask(this.tindex);
        },
        sendvalinput(event){
            const inp = event.target;
            if(moment(inp.value, "DD.MM.YYYY",true).isValid()){
                //console.log(['update input']);
                this.$root.savetask(this.tindex);
            }
        }
    }

});