/*
 * Copyright (c) 24.05.2024
 * Suharkov Sergey (sexiterra@mail.ru)
 */

var canbesaved__ = function (){

    var data_s;
    var defaultdata;

    const makeData = function (data_store){
        data_s = data_store;
        defaultdata = JSON.parse(JSON.stringify(data_s));
    }

    const canBeSaved_ = function (taskindex){
        //debugger
        if (data_s[taskindex].ID > 0) var a = 1;

        const regex = new RegExp('_ORIGINAL');

        if (defaultdata.length > 0)
            for (key in data_s){
                for (field in data_s[key]){

                    if (regex.test(field)) continue;

                    if (typeof data_s[key][field] == 'string') {
                        if (data_s[key][field] != defaultdata[key][field])
                            return false;
                        /*
                            console.log([
                            this.datatask[key][field],
                            this.$root.defaultdatatask[key][field]
                        ]);
                        */
                    }
                    if (typeof defaultdata[key][field] == 'object' && defaultdata[key][field].length>0) {
                        for (k in defaultdata[key][field]){
                            if (defaultdata[key][field][k].VALUE) {
                                if (data_s[key][field][k].VALUE != defaultdata[key][field][k].VALUE)
                                    return false;
                                /*
                                    console.log([
                                    field,
                                    this.datatask[key][field][k].VALUE,
                                    this.$root.defaultdatatask[key][field][k].VALUE
                                ]);
                                 */
                            }
                        }

                    }
                }
            }
        return true;
    }


    return {makeData,canBeSaved_};

}