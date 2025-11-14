window.columnConfigs = {
    getColumnConfig: function(key, fieldLabels, editableFields) {
        const columnConfig = {
            data: key,
            title: fieldLabels[key],
            width: 150,
            readOnly: !editableFields.includes(key)
        };

        // Настройки для specific полей
        switch(key) {
            case 'UF_EXT_KEY':
                columnConfig.width = 80;
                columnConfig.renderer = function(instance, td, row, col, prop, value) {
                    if (value) {
                        td.innerHTML = '<a href="/kabinet/admin/performances/?executionidsearch=' + value + '" target="_blank" title="' + value + '">' + value +'</a>';
                    } else {
                        td.textContent = '';
                    }
                    return td;
                };
                break;
            case 'id':
                columnConfig.width = 80;
                columnConfig.type = 'numeric';
                break;
            case 'planned_date':
            case 'created_date':
            case 'completion_date':
            case 'publication_date':
                columnConfig.width = 120;
                columnConfig.type = 'date';
                columnConfig.dateFormat = 'DD.MM.YYYY';
                columnConfig.correctFormat = true;
                columnConfig.defaultDate = new Date().toISOString().split('T')[0];
                if (key === 'planned_date' || key === 'publication_date') {
                    columnConfig.className = 'editable-cell';
                }
                break;
            case 'client':
            case 'project':
                columnConfig.width = 200;
                break;
            case 'task':
                columnConfig.width = 250;
                break;
            case 'review_text':
                columnConfig.width = 300;
                columnConfig.className = 'editable-cell';
                columnConfig.renderer = function(instance, td, row, col, prop, value) {
                    const rowData = instance.getDataAtRow(row);
                    const executionId = rowData ? rowData[0] : null;

                    if (value && executionId) {
                        const displayText = value.length > 30 ? value.substring(0, 30) + '...' : value;
                        td.innerHTML = '<a href="/kabinet/admin/performances/?executionidsearch=' + executionId +
                            '" target="_blank" title="' + value + '">' + displayText + '</a>';
                    } else if (value) {
                        td.textContent = value;
                        td.title = value;
                    } else {
                        td.textContent = '-';
                    }
                    return td;
                };
                break;
            case 'photo':
                columnConfig.width = 200;
                columnConfig.renderer = function(instance, td, row, col, prop, value) {
                    if (value) {
                        td.innerHTML = '<a href="' + value + '" target="_blank" title="' + value + '">' +
                            (value.length > 30 ? value.substring(0, 30) + '...' : value) +
                            '</a>';
                    } else {
                        td.textContent = '-';
                    }
                    return td;
                };
                break;
            case 'link':
                columnConfig.width = 200;
                columnConfig.renderer = function(instance, td, row, col, prop, value) {
                    if (value) {
                        const displayText = value.length > 30 ? value.substring(0, 30) + '...' : value;
                        td.innerHTML = '<button type="button" class="copy-link-btn" data-url="' +
                            value + '" title="Копировать ссылку: ' + value + '">' +
                            displayText + '</button>';

                        setTimeout(() => {
                            const btn = td.querySelector('.copy-link-btn');
                            if (btn) {
                                btn.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    copyToClipboard(this.getAttribute('data-url'));
                                });
                            }
                        }, 0);
                    } else {
                        td.textContent = '-';
                    }
                    return td;
                };
                break;
            case 'UF_REPORT_LINK':
            case 'UF_REPORT_SCREEN':
            case 'UF_REPORT_FILE':
                columnConfig.width = 200;
                columnConfig.renderer = function(instance, td, row, col, prop, value) {
                    if (value) {
                        td.innerHTML = '<a href="' + value + '" target="_blank" title="' + value + '">' +
                            (value.length > 30 ? value.substring(0, 30) + '...' : value) +
                            '</a>';
                    } else {
                        td.textContent = '-';
                    }
                    return td;
                };
                break;
            case 'responsible':
            case 'account_name':
            case 'login':
            case 'password':
            case 'ip_address':
            case 'UF_REPORT_TEXT':
                columnConfig.className = 'editable-cell';
                columnConfig.width = 200;
                break;
        }

        return columnConfig;
    }
};