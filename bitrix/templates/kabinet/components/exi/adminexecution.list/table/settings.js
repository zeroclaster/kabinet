window.handsontableConfig = {
    rowHeaders: true,
    colHeaders: true,
    columnSorting: true,
    filters: true,
    dropdownMenu: true,
    contextMenu: true,
    manualColumnResize: true,
    manualRowResize: true,
    manualColumnMove: true,
    licenseKey: 'non-commercial-and-evaluation',
    // height: 1550, // Убираем фиксированную высоту, теперь рассчитывается динамически
    width: '100%',
    wordWrap: false,
    autoWrapRow: true,
    autoWrapCol: true,
    language: 'ru-RU',
    search: true,
    stretchH: 'all',
    customBorders: true,
    currentRowClassName: 'current-row',
    currentColClassName: 'current-col',
    enterMoves: {row: 0, col: 1},
    tabMoves: {row: 0, col: 1},
    autoColumnSize: {
        samplingRatio: 23
    },
    afterChange: function(changes, source) {
        if (source === 'loadData') {
            return;
        }

        if (changes) {
            changes.forEach(function(change) {
                var row = change[0];
                var field = change[1];
                var oldValue = change[2];
                var newValue = change[3];

                console.log('Изменение:', {
                    row: row,
                    field: field,
                    oldValue: oldValue,
                    newValue: newValue,
                    executionId: window.executionsArray ? window.executionsArray[row].id : 'unknown'
                });

                // Вызываем функцию сохранения с задержкой
                if (window.tableSaveManager) {
                    window.tableSaveManager.handleCellChange(row, field, oldValue, newValue);
                }
            });
        }
    },
    selectionMode: 'range',
    fillHandle: {
        direction: 'vertical',
        autoInsertRow: false
    }
};