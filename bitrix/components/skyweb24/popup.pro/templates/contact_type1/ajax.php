<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
use Bitrix\Main\Application;
\Bitrix\Main\Loader::includeModule('skyweb24.popuppro');
define("NO_KEEP_STATISTIC", true);
$popup=new popuppro;
$setting=$popup->getSetting($_REQUEST['id_popup']);
if($setting['row']['active']=='Y'){
	if(!check_bitrix_sessid()){
		die("ACCESS_DENIED");
	}else{
		$context = Application::getInstance()->getContext();
		$request = $context->getRequest();
		$temlateName=$request->get("template_name");
		$idPopup=$request->get("id_popup");
		if(!empty($temlateName) && !empty($idPopup)){
			$APPLICATION->IncludeComponent("skyweb24:popup.pro", $temlateName, array(
				"ID_POPUP" => $idPopup
			),false);
		}
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
	}
}else{
	echo 'not active window';
}

?>
