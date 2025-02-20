<?
namespace Bitrix\Kabinet\helper;

use Bitrix\Main\Application,
    Bitrix\Kabinet\helper\Gbstorage;

class Helper extends Datesite{

	static function userName($data){

		return current(array_filter([
            implode(" ", [$data['LAST_NAME'], $data['NAME'], $data['SECOND_NAME']]),
            $data['LOGIN']
        ]));
	}

    static function uniqueId(){
        $container = \KContainer::getInstance();
        $uniqueid = $container->getArgs('unique_counter');

        // first instalizate!
        if (!$uniqueid)
            $uniqueid = 1;
        else
            $uniqueid = $uniqueid + 1;

        $container->setArgs($uniqueid,'unique_counter');
        return $uniqueid;
    }

    static function getElementByField(array $data, $id, $fieldname = 'ID'){
	    if ($id === NULL) return [];
        $key = array_search($id, array_column($data, 'ID'));
        if ($key === false) return [];

        return $data[$key];
    }

    static function isAdmin(){
	    global $USER;

	    if (!is_object($USER))
            return false;

        if (!$USER->IsAuthorized())
            return false;

        $id = $USER->GetID();
        $GroupArray = \CUser::GetUserGroup($id);

        return !empty(array_intersect([MANAGER], $GroupArray));
    }

    static function array_value_recursive($key, array $arr){
        $val = array();
        array_walk_recursive($arr, function($v, $k) use($key, &$val){
            if($k == $key) array_push($val, $v);
        });
        return count($val) > 1 ? $val : array_pop($val);
    }


    static function array_keys_multi(array $array,$find_key)
    {
        $result=[];

        foreach ($array as $key => $value) {

            if ($find_key == $key) return $value;

            if (is_array($value)) {
                $r = self::array_keys_multi($value,$find_key);
                if ($r) return $r;
            }
        }

        return $result;
    }

}