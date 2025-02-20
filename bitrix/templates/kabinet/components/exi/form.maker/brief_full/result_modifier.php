<?php

foreach ($arParams['GROUPS'] as $key => $GROUP_TITLE){
    foreach ($arParams['GROUP'.$key] as &$fieldParams){

        $fieldParams['FIELD_TITLE_VIEW'] = $fieldParams['EDIT_FORM_LABEL'];

        if ($fieldParams['MANDATORY'] == 'Y') $fieldParams['FIELD_TITLE_VIEW'] .= '<span class="requiredfield-label"></span>';

    }
}
