<?
namespace Bitrix\Kabinet;

class User extends EO_User{

    public function getAvatar(){
        $file_id = $this->get('PERSONAL_PHOTO');

        $avatar_default = $avatar_default = SITE_TEMPLATE_PATH.'/assets/images/users/user-17-60x60.jpg'; 
        if(empty($file_id)) return $avatar_default;

        $avatar = \CFile::GetPath($file_id);
        if (!empty($avatar)) $avatar_default = $avatar;

        return $avatar_default;
    }
	
    public function getAvatar60x60(){
        $file_id = $this->get('PERSONAL_PHOTO');
        // задается в bitrix/modules/kabinet/include.php
        $config = (\KContainer::getInstance())->get('config');

        $avatar_default = $avatar_default = $config['USER']['photo_default'];
        if(empty($file_id)) return $avatar_default;

		//BX_RESIZE_IMAGE_EXACT - масштабирует в прямоугольник $arSize c сохранением пропорций, обрезая лишнее;
        $avatar = \CFile::ResizeImageGet($file_id, array('width'=>60, 'height'=>60), BX_RESIZE_IMAGE_EXACT, true);
        if (!empty($avatar)) $avatar_default = $avatar['src'];

        return $avatar_default;
    }	

    public function printName(){

        return current(array_filter([
            trim(implode(" ", [$this->get('LAST_NAME'), $this->get('NAME'), $this->get('SECOND_NAME')])),
            $this->get('LOGIN')
        ]));
    }

/*
    public function getSocialLinks(){
        $id = $this->getID();

        $arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("USER", $id);
        return $arUserFields["UF_LINKS"]["VALUE"];
    }
*/

/*
    public function getPrintSocialLinks(){
        $ret = [];
        $l = $this->getSocialLinks();
        foreach ($l as $link){
            //$r = ['icon'=>'','link'=>''];
            $r['link'] = $link;
            $r['icon'] = \Bitrix\Portal\helpers\Social::printIcon($link);
            $ret[] = $r;
        }
        return $ret;
    }
*/

    public function addGroup($gID){
        $id = $this->getID();

        $g = \CUser::GetUserGroup($id);
        $g = array_merge($g, array($gID));
        \CUser::SetUserGroup($id, $g);
    }

    public function removeGroup($gID){
        $id = $this->getID();

        $g = \CUser::GetUserGroup($id);
		
        $key = array_search($gID, $g);
        if ($key !== false) {
            unset($g[$key]);			
            \CUser::SetUserGroup($id, $g);
        }
    }

}