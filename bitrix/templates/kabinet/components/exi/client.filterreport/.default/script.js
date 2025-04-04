var client_filter_report = document.client_filter_report || {};
client_filter_report = (function () {
    return {
        start(PHPPARAMS) {
            $(function(){

                const $fromDatepicker = $("#fromdate1");
                const $toDatepicker = $("#todate1");
                const findform = BX.findChild(document.body,{attribute:{name:'clientfindreportform'}},true,false);
				
				BX.bind(findform.alertfind,'click',(e)=>findform.submit());

                $fromDatepicker.datetimepicker({
                    locale: moment.locale('ru'),
                    format: 'DD.MM.YYYY',
                    //minDate: newDate.toDate()
                });

                $toDatepicker.datetimepicker({
                    locale: moment.locale('ru'),
                    format: 'DD.MM.YYYY',
                    //minDate: newDate.toDate()
                });

                const datepicker1 = $toDatepicker.data('DateTimePicker');
                const datepicker2 = $fromDatepicker.data('DateTimePicker');

                $fromDatepicker.on('dp.change', (event) => {
                    //console.log(event.date);

                    if (event.date) {
                        const newDate = moment(event.date, "DD.MM.YYYY");
                        let d = $toDatepicker.val();
                        datepicker1.minDate(newDate);
                        if (!d) datepicker1.date(null);
                    }else{
                        let d = $toDatepicker.val();
                        datepicker1.minDate(false);
                        if (!d) datepicker1.date(null);
                    }

                });

                $toDatepicker.on('dp.change', (event) => {
                    //console.log(event.date);
                    if (event.date) {
                        const newDate = moment(event.date, "DD.MM.YYYY");
                        let d = $fromDatepicker.val();
                        datepicker2.maxDate(newDate);
                        if (!d) datepicker2.date(null);
                    }else{
                        let d = $fromDatepicker.val();
                        datepicker2.maxDate(false);
                        if (!d) datepicker2.date(null);
                    }

                });

                BX.bind(BX('clearform'),'click',function (e) {
                    // TODO AKULA разобраться почему не работает form.reset()
                    //findform.reset();

                    for(index in findform.elements)
                        findform.elements[index].value = '';

                    findform.clearflag.value = 'y';

                    findform.submit();
                })

            });
        }
    }
}());