/*
window.addEventListener("typeahead:readyScripts", function(event) {
    var strs = ["tiyutiu"];
    var node = $('#search-client');
    node.typeahead(
        {
            hint: true,
            highlight: true,
            minLength: 0
        },
        {
            limit: 10,
            name: node.attr( 'placeholder' ),
            displayKey: 'value',
            source: function findMatches( q, cb ) {
                let matches = [];
                strs.forEach( function( str ) {
                    if ( ( new RegExp( q, 'i' ) ).test( str ) ) matches.push({ value: str });
                });
                cb( matches );
            }
        }
    );

});


 */


const filter1 = {
    seach_result:[],
    clients: [],
    projects: [],
    tasks: [],
    loadDataClient(){
        const this_ = this;
        let formData = new FormData;
        const kabinetStore = usekabinetStore();
        var data = BX.ajax.runComponentAction("exi:admin.filterclient", "getclients", {
            mode: 'class',
            data: formData,
            timeout: 300
        }).then(function (response) {
            this_.clients = response.data;
            $('#clientidsearch').val(0);


            // устанавливаем значение выбранное пользователем
            if(typeof this_.seach_result.clientidsearch != 'undefined'){
                this_.projects = [];
                this_.tasks = [];

                $('#projectidsearch').val(0);
                $('#taskidsearch').val(0);

                for(element of this_.clients){
                    if (element.id == this_.seach_result.clientidsearch){
                        $('#search-client').typeahead('val', element.value);
                        break;
                    }
                }

                // загружаем проекты выбранного клиента
                this_.loadDataProjects(this_.seach_result.clientidsearch);
                $('#clientidsearch').val(this_.seach_result.clientidsearch);
            }

        }, function (response) {
            //console.log(response);
            response.errors.forEach((error) => {
                kabinetStore.Notify = '';
                kabinetStore.Notify = error.message;
            });
        });
    },
    loadDataProjects(client = 0){
        const this_ = this;
        let formData = new FormData;
		formData.append("ID",client);
        const kabinetStore = usekabinetStore();
        var data = BX.ajax.runComponentAction("exi:admin.filterclient", "getproject", {
            mode: 'class',
            data: formData,
            timeout: 300
        }).then(function (response) {

            $('#projectidsearch').val(0);
            $('#taskidsearch').val(0);

            this_.projects = response.data;

            if (typeof $('#search-task').typeahead != "undefined") {
                $('#search-task').typeahead('val', "");
                $('#search-task').typeahead('destroy');
            }

            var input = $('#search-project');
            if (typeof input.typeahead != "undefined") {
                input.typeahead('val', "");
                input.typeahead('destroy');
            }

            input.typeahead(
                {
                    hint: true,
                    highlight: true,
                    minLength: 0
                },
                {
                    limit: 1000000,
                    name: input.attr('placeholder'),
                    displayKey: 'value',
                    source: function findMatches(q, cb, async) {
                        let matches = [];
                        this_.projects.forEach(function (element) {
                            if ((new RegExp(q, 'i')).test(element.value)) matches.push(element);
                        });
                        cb(matches);
                    }
                }
            );
            $('#search-project').bind('typeahead:select', function (ev, suggestion) {
                $('#projectidsearch').val(suggestion.id);
                this_.loadDataTasks(suggestion.id);
            });

            // устанавливаем значение выбранное пользователем
            if(typeof this_.seach_result.projectidsearch != 'undefined'){
                this_.tasks = [];
                $('#taskidsearch').val(0);

                for(element of this_.projects){
                    if (element.id == this_.seach_result.projectidsearch){
                        $('#search-project').typeahead('val', element.value);
                        break;
                    }
                }

                this_.loadDataTasks(this_.seach_result.projectidsearch);
                $('#projectidsearch').val(this_.seach_result.projectidsearch);
            }

        }, function (response) {
            //console.log(response);
            response.errors.forEach((error) => {
                kabinetStore.Notify = '';
                kabinetStore.Notify = error.message;
            });
        });
    },
    loadDataTasks(project = 0){
        const this_ = this;
        let formData = new FormData;
		formData.append("ID",project);
        const kabinetStore = usekabinetStore();
        var data = BX.ajax.runComponentAction("exi:admin.filterclient", "gettask", {
            mode: 'class',
            data: formData,
            timeout: 300
        }).then(function (response) {

            this_.tasks = response.data;
            var input = $('#search-task');
            if (typeof input.typeahead != "undefined") {
                input.typeahead('val', "");
                input.typeahead('destroy');
            }

            input.typeahead(
                    {
                        hint: true,
                        highlight: true,
                        minLength: 0
                    },
                    {
                        limit: 1000000,
                        name: input.attr('placeholder'),
                        displayKey: 'value',
                        source: function findMatches(q, cb, async) {
                            let matches = [];
                            this_.tasks.forEach(function (element) {
                                if ((new RegExp(q, 'i')).test(element.value)) matches.push(element);
                            });
                            cb(matches);
                        }
                    }
            );

            input.bind('typeahead:select', function (ev, suggestion) {
                    $('#taskidsearch').val(suggestion.id);
            });

            // устанавливаем значение выбранное пользователем
            if(typeof this_.seach_result.taskidsearch != 'undefined'){
                for(element of this_.tasks){
                    if (element.id == this_.seach_result.taskidsearch){
                        $('#search-task').typeahead('val', element.value);
                        break;
                    }
                }

                $('#taskidsearch').val(this_.seach_result.taskidsearch);
            }


        }, function (response) {
            //console.log(response);
            response.errors.forEach((error) => {
                kabinetStore.Notify = '';
                kabinetStore.Notify = error.message;
            });
        });
    },
    addtypeahead(){
        const this_ = this;

        var input = $('#search-client');
        input.typeahead(
            {
                hint: true,
                highlight: true,
                minLength: 0
            },
            {
                limit: 1000000,
                name: input.attr( 'placeholder' ),
                displayKey: 'value',
                source:function findMatches( q, cb,async ) {
                    let matches = [];
                    this_.clients.forEach( function( element ) {
                        if ( ( new RegExp( q, 'i' ) ).test( element.value ) ) matches.push(element);
                    });
                    cb( matches );
                }
            }
        );


        input.bind('typeahead:select', function(ev, suggestion) {
            $('#clientidsearch').val(suggestion.id);
            this_.loadDataProjects(suggestion.id);
        });

    },
    init(phpparams){
        const this_ = this;
        this_.seach_result = phpparams.SEARCH_RESULT;

        window.addEventListener("components:ready", function(event) {
            this_.loadDataClient();
            if(typeof this_.seach_result.clientidsearch == 'undefined') this_.loadDataProjects();
            if(typeof this_.seach_result.projectidsearch == 'undefined') this_.loadDataTasks();
            this_.addtypeahead();

            const form = BX.findChild(document.body,{attribute:{name:'filterform1'}},true,false);
            BX.bind(form, 'submit', function (event) {
                const form = event.target;

                if (form.elements.clienttextsearch.value == '') form.elements.clientidsearch.value = '0';
                if (form.elements.projecttextsearch.value == '') form.elements.projectidsearch.value = '0';
                if (form.elements.tasktextsearch.value == '') form.elements.taskidsearch.value = '0';


                BX.findChild(document.body,{attribute:{name:'clientidsearch'}},true,false);

                let sum = 0;
                for(input of form.elements) {
                    if(input.type == 'hidden') sum = sum + parseInt(input.value);
                    if(input.type == 'text') sum = sum + input.value.length;
                }
                const kabinetStore = usekabinetStore();
                // не отправляем, ничего не выбрано
                if (!sum) {
                    kabinetStore.Notify = '';
                    kabinetStore.Notify = 'Вы не выбрали не одного поля!';

                    event.preventDefault();
                    event.stopPropagation()
                    return false;
                }
            });

            BX.bind(form.elements.clienttextsearch,'change',function () {
                form.elements.clientidsearch.value = '0';
            });
            BX.bind(form.elements.projecttextsearch,'change',function () {
                form.elements.projectidsearch.value = '0';
            });
            BX.bind(form.elements.tasktextsearch,'change',function () {
                form.elements.taskidsearch.value = '0';
            });

            BX.bind(BX("clearfilter"),'click',function (e) {

                for(input of form.elements)
                    input.value = '';

                form.submit();

                event.preventDefault();
                event.stopPropagation()
                return false;
            })

        });
    }
};


