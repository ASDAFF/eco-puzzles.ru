<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
use Bitrix\Main\Application;
define("NO_KEEP_STATISTIC", true);
\Bitrix\Main\Loader::includeModule('skyweb24.popuppro');
$context = Application::getInstance()->getContext();
$request = $context->getRequest();
$temlateName=$request->get("template_name");
$idPopup=$request->get("id_popup");
if(!empty($temlateName) && !empty($idPopup)){
	echo 'end';
	$APPLICATION->IncludeComponent("skyweb24:popup.pro", $temlateName, array(
		"ID_POPUP" => $idPopup
	),false);
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>
