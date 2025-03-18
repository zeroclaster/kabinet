'use strict';

components.typeahead.init = null;
components.datetimepicker.init = null;
components.modal.init = null;
components.ckeditor.init = null;
components.select2.init = null;

var SiteDefaultStyles = [];
for(let nameComp in components){
    if(['badge','input'].indexOf(nameComp) != -1) SiteDefaultStyles.push(components[nameComp].styles);
}

console.log(SiteDefaultStyles)

components.kabinetproject= {
    selector: '.kabinet-project',
    styles: SiteDefaultStyles
};

components.table= {
    selector: '[data-loadtable]',
        styles: './components/table/table.css'
};

let mutademodal = Object.assign({}, components.modal);
mutademodal.selector = '[data-modalload]';
components.mymodal = mutademodal;

let mutadeselect2 = Object.assign({}, components.select2);
mutadeselect2.selector = '[data-select2]';
components.myselect2 = mutadeselect2;

components.vuetypeahead = {
    selector: '[data-vuetypeahead]',
    script: [
        './js/kabinet/vue-componets/typeahead.js',
    ],
    init:null
}

components.messangerUser = {
    selector: "[data-usermessanger='report']",
    script: [
        './js/kabinet/vue-componets/messanger/uploadfile.js',
        './js/kabinet/vue-componets/messanger/templates/user.report.js',
        './js/kabinet/vue-componets/messanger/messanger.js',
    ],
	styles: './css/messanger.css',
	dependencies:'vuerichtext',
    init:null
}

components.messangerUserDashbord = {
    selector: "[data-usermessanger='dashbord']",
    script: [
        './js/kabinet/vue-componets/messanger/uploadfile.js',
        './js/kabinet/vue-componets/messanger/templates/user.dashbord.js',
        './js/kabinet/vue-componets/messanger/messanger.js',
    ],
    styles: './css/messanger.css',
    dependencies:'vuerichtext',
    init:null
}


components.messangerUsernotification = {
    selector: "[data-usermessanger='notification']",
    script: [
        './js/kabinet/vue-componets/messanger/uploadfile.js',
        './js/kabinet/vue-componets/messanger/templates/user.notification.js',
        './js/kabinet/vue-componets/messanger/messanger.js',
    ],
    styles: './css/messanger.css',
    dependencies:'vuerichtext',
    init:null
}

components.messangerUserProject = {
    selector: "[data-usermessanger='project']",
    script: [
        './js/kabinet/vue-componets/messanger/uploadfile.js',
        './js/kabinet/vue-componets/messanger/templates/user.project.js',
        './js/kabinet/vue-componets/messanger/messanger.js',
    ],
    styles: './css/messanger.css',
    dependencies:'vuerichtext',
    init:null
}

components.messangerAdmin = {
    selector: '[data-adminmessanger]',
    script: [
        './js/kabinet/vue-componets/messanger/uploadfile.js',
        './js/kabinet/vue-componets/messanger/templates/admin.performances.js',
        './js/kabinet/vue-componets/messanger/messanger.js',
    ],
    styles: './css/messanger.css',
    dependencies:'vuerichtext',
    init:null
}

components.messangerUsersupport = {
    selector: "[data-usermessanger='support']",
    script: [
        './js/kabinet/vue-componets/messanger/uploadfile.js',
        './js/kabinet/vue-componets/messanger/templates/user.support.js',
        './js/kabinet/vue-componets/messanger/messanger.js',
    ],
    styles: './css/messanger.css',
    dependencies:'vuerichtext',
    init:null
}


components.messangerForProjectmainPage = {
    selector: "[data-usermessangerr='projectmainpage']",
    script: [
        './js/kabinet/vue-componets/messanger/templates/project.mainpage.js',
        './js/kabinet/vue-componets/messanger/messanger.js',
    ],
    init:null
}

components.vuerichtext = {
    selector: '[data-vuerichtext]',
    script: [
        './js/kabinet/vue-componets/richtext.js',
    ],
	dependencies:'ckeditor',
    init:null
}

components.tasklist = {
    selector: '[data-usertasklist]',
    script: [
        './js/kabinet/vue-componets/questiona.ativity.js',
    ],
    init:null
}

components.fullcalendar2 = {
    selector: '[data-fullcalendar2]',
    styles: [
        './components/fullcalendar/fullcalendar.css',
        './components/fullcalendar/scheduler.min.css',
        './components/button/button.css',
        './components/table/table.css',
        './components/alert/alert.css',
        './components/card/card.css',
        './components/tooltip/tooltip.css',
        './components/font-awesome/font-awesome.css'
    ],
    script: [
        './components/base/jquery-3.4.1.min.js',
        './components/base/jquery-ui.min.js',
        './components/base/moment.min.js',
        './components/fullcalendar/fullcalendar.min.js',
        './components/fullcalendar/locale-all.js',
        //'./components/fullcalendar/scheduler.min.js',

    ]
}


function initComponent_( component ) {
    let
        stylesPromises = [],
        scriptsPromises = [];

        if ( component.styles && !(component.styles instanceof Array) ) {
            component.styles = [ component.styles ];
        }

        if ( component.script && !(component.script instanceof Array) ) {
            component.script = [ component.script ];
        }

        if ( component.dependencies && !(component.dependencies instanceof Array) ) {
            component.dependencies = [ component.dependencies ];
        }

        component.state = 'pending';
        let componentPromises = initComponent( component );
        stylesPromises.push( componentPromises.stylesState );
        scriptsPromises.push( componentPromises.scriptsState );

    Promise.all( scriptsPromises ).then( function () {
        window.dispatchEvent( new Event( 'components:ready' ) );
    });

    Promise.all( stylesPromises ).then( function () {
        window.dispatchEvent( new Event( 'components:stylesReady' ) );
    });
}

$(function (){
    //initComponent_( components.typeahead );
});