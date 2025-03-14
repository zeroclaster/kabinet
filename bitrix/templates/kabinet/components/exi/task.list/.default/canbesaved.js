/*
 * Copyright (c) 24.05.2024
 * Suharkov Sergey (sexiterra@mail.ru)
 */

var canbesaved__ = function (){
    var data_s;
    var defaultdata;
    const regex = new RegExp('_ORIGINAL');

    const makeData = function (data_store){
        data_s = data_store;
        defaultdata = JSON.parse(JSON.stringify(data_s));
    }

    const canBeSaved_ = function (taskindex){
        if (defaultdata.length == 0) return true;
        for (key in data_s){
            if (key != taskindex) continue;

            for (field in data_s[key]){
                // пропускаем поля _ORIGINAL
                if (regex.test(field)) continue;

                if (typeof data_s[key][field] == 'string') {
                    if (data_s[key][field] != defaultdata[key][field])
                        return false;
                }
                if (typeof defaultdata[key][field] == 'object' && defaultdata[key][field].length>0)
                    for (k in defaultdata[key][field])
                        if (typeof defaultdata[key][field][k].VALUE != "undefined") {
                            if (data_s[key][field][k].VALUE != defaultdata[key][field][k].VALUE)
                                return false;
                        }
            }
        }
        return true;
    }

    return {makeData,canBeSaved_};
}