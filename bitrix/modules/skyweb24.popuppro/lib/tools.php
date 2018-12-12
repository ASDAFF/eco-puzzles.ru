<?
namespace Skyweb24\Popuppro;
\Bitrix\Main\Loader::includeModule('iblock');
use Bitrix\Main\Mail\Event,
	Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
class Tools{
	
	public static function getBasketRules(){
		$currentModules=array();
		 $groupDiscountIterator = \Bitrix\Sale\Internals\DiscountTable::getList(array(
            'select' => array('ID', 'NAME'),
            'filter' => array('=ACTIVE' => 'Y')
        ));
        while ($groupDiscount = $groupDiscountIterator->fetch()) {
			$currentModules[$groupDiscount['ID']]=$groupDiscount['NAME'].' ['.$groupDiscount['ID'].']';
        }
		return $currentModules;
	}
	
	public static function getMailTemplates($var='SKYWEB24_POPUPPRO_SEND_COUPON'){
		$template_message=\CEventMessage::GetList($by="site_id", $order="desc", array('TYPE_ID'=>$var));
		$serviceMessage=array();
		while($t_m=$template_message->Fetch()){
			$serviceMessage[$t_m['ID']]=$t_m['EVENT_NAME'].' ['.$t_m['ID'].']';
		}
		return $serviceMessage;
	}
	
	public static function returnPropId($iblock_id, $prop_code, $prop_name){
		$properties = \CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("IBLOCK_ID"=>$iblock_id, 'CODE'=>strtoupper($prop_code)));
		if($prop_fields = $properties->GetNext()){
			return $prop_fields["ID"];
		}
		$ibp = new \CIBlockProperty;
		return $ibp->Add(array(
			"NAME" => $prop_name,
			"ACTIVE" => "Y",
			"SORT" => "100",
			"CODE" => strtoupper($prop_code),
			"PROPERTY_TYPE" => "S",
			"IBLOCK_ID" => $iblock_id
		));
	}
	
	public function getListType($idPopup, $name){
		$groups=[];
		$addType=true;
		$groupList=\Bitrix\Sender\ListTable::GetList(
			['order'=>['NAME'=>'asc']]
		);
		while($row=$groupList->fetch()){
			if($row['CODE']=='skyweb24PopupPro_'.$idPopup){
				$addType=false;
			}
			$groups[$row['ID']]=$row['NAME'];
		}
		if($addType && (int) $idPopup>0){
			$listAddDb = \Bitrix\Sender\ListTable::add([
				'NAME' => $name,
				'CODE' => 'skyweb24PopupPro_'.$idPopup,
			]);
			if($listAddDb->isSuccess()){
				$listId = $listAddDb->getId();
				$groups[$listId]=$name;
			}
		}
		return $groups;
	}

	public static function getPersonalizationValues(){
		$tempArr=[];
		foreach(self::getPersonalization() as $nextKey=>$nexrVal){
			$tempArr[$nextKey]='';
		}
		global $USER;
		if($USER->IsAuthorized()){
			$rsUser = \CUser::GetByID(\CUser::GetID());
			$arUser = $rsUser->Fetch();
			if(!empty($arUser['NAME'])){
				$tempArr['NAME']=$arUser['NAME'];
			}
			if(!empty($arUser['LAST_NAME'])){
				$tempArr['LAST_NAME']=$arUser['LAST_NAME'];
			}
			if(!empty($arUser['SECOND_NAME'])){
				$tempArr['SECOND_NAME']=$arUser['SECOND_NAME'];
			}
			if(!empty($arUser['EMAIL'])){
				$tempArr['EMAIL']=$arUser['EMAIL'];
			}
			if(!empty($arUser['PERSONAL_MOBILE'])){
				$tempArr['MOBILE']=$arUser['PERSONAL_MOBILE'];
			}
			if(!empty($arUser['PERSONAL_PHONE'])){
				$tempArr['PHONE']=$arUser['PERSONAL_PHONE'];
			}
		}
		return $tempArr;
	}
	
	public static function getPersonalization(){
		return [
			'NAME'=>Loc::getMessage("skyweb24.popuppro_PERS_NAME"),
			'LAST_NAME'=>Loc::getMessage("skyweb24.popuppro_PERS_LAST_NAME"),
			'SECOND_NAME'=>Loc::getMessage("skyweb24.popuppro_PERS_SECOND_NAME"),
			'EMAIL'=>Loc::getMessage("skyweb24.popuppro_PERS_EMAIL"),
			'PHONE'=>Loc::getMessage("skyweb24.popuppro_PERS_PHONE"),
			'MOBILE'=>Loc::getMessage("skyweb24.popuppro_PERS_MOBILE")
		];
	}
	
	public static function getUserGroup(){
		$res = \CGroup::getList(($by="ID"),($order="asc"));
		$result=array();
		while($r=$res->Fetch()){
			if($r['ID']<3){continue;}
			$result[$r['ID']]=$r['NAME'];
		}
		return $result;
	}
}

?>