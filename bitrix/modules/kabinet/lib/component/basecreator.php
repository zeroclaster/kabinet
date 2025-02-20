<?
namespace Bitrix\Kabinet\component;

use Bitrix\Main,
    Bitrix\Main\Error,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\ErrorCollection,
    Bitrix\Main\Engine\ActionFilter,
    Bitrix\Main\Loader;

class Basecreator extends \CBitrixComponent
{
    const ERROR_TEXT = 1;
    const ERROR_404 = 2;

    protected $errorCollection;

    /**
     * Base constructor.
     * @param \CBitrixComponent|null $component     Component object if exists.
     */
    public function __construct($component = null)
    {
        Loader::includeModule('kabinet');

        parent::__construct($component);
        $this->errorCollection = new ErrorCollection();
    }

    /**
     * Return true if errors exist.
     *
     * @return bool
     */
    protected function hasErrors()
    {
        return (bool)count($this->errorCollection);
    }

    /**
     * Errors processing depending on error codes.
     *
     * @return bool
     */
    protected function processErrors()
    {
        if (!empty($this->errorCollection))
        {
            /** @var Error $error */
            foreach ($this->errorCollection as $error)
            {
                $code = $error->getCode();

                if ($code == self::ERROR_404)
                {
                    if ($this->arParams['SHOWMESSAGE_404'] === 'Y')
                        Tools::process404(
                            trim($this->arParams['MESSAGE_404']) ?: $error->getMessage(),
                            true,
                            $this->arParams['SET_STATUS_404'] === 'Y',
                            $this->arParams['SHOW_404'] === 'Y',
                            $this->arParams['FILE_404']
                        );
                }
                elseif ($code == self::ERROR_TEXT)
                {
                    ShowError($error->getMessage());
                }
            }
        }

        return false;
    }

    public function executeComponent()
    {
        if ($this->hasErrors())
        {
            return $this->processErrors();
        }

        $this->doitAction();
        $this->prepareData();

        $this->includeComponentTemplate($this->template);

        return true;
    }

    public function getUserFields($ID = 0){
        $arParams = $this->arParams;
        $ID = ($ID)? $ID : $arParams['HB_ID'];
        $fields = $GLOBALS["USER_FIELD_MANAGER"]->getUserFields('HLBLOCK_'.$ID,null,LANGUAGE_ID);

        return $fields;
    }

    public function extrudeSysFifelds(array $fields){
        $sysFields = [
            'UF_SORT',
            'UF_EXT_KEY',
            'UF_USER_EDIT_ID',
            'UF_AUTHOR_ID',
            'UF_ACTIVE',
            'UF_PUBLISH_DATE',
            'UF_STATUS',
            'UF_PROJECT_ID',
			'UF_ORDER_ID',
            'UF_PRODUKT_ID',
            //'UF_COMP_LOGO'
        ];

        $newfields = [];
        foreach ($fields as $field) {
            if (in_array($field['FIELD_NAME'],$sysFields)) continue;
            $newfields[] = $field;
        }

        return $newfields;
    }


    /**
     * Getting array of errors.
     * @return Error[]
     */
    public function getErrors()
    {
        return $this->errorCollection->toArray();
    }

    /**
     * Getting once error with the necessary code.
     * @param string $code Code of error.
     * @return Error
     */
    public function getErrorByCode($code)
    {
        return $this->errorCollection->getErrorByCode($code);
    }

}
