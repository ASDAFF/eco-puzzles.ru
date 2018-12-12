<?
define("STOP_STATISTICS", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck", true);
define("BX_SECURITY_SHOW_MESSAGE", true);
header('Content-Type: application/json; charset=utf-8');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Context,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\Web\Json;

$oRequest = Context::getCurrent()->getRequest();

Loc::loadMessages(__FILE__);

if($_SERVER["REQUEST_METHOD"] == 'POST' && check_bitrix_sessid() && isset($oRequest['id']) 
    && intval($oRequest['id']) > 0 && intval($oRequest["deliveryId"]) > 0){
	$_SESSION['Shiptor'][$oRequest["deliveryId"]]['PVZ_ID'] = intval($oRequest['id']);
        $_SESSION['Shiptor'][$oRequest["deliveryId"]]['PVZ_INFO'] = $oRequest["currentPVZ"];
	$arResult['success'] = true;
	$arResult['message'] = Loc::getMessage("ST_SHIPTOR_SELECT_PVZ_SUCCESS",array("#SHIPP_ID#" => intval($oRequest['id'])));
}else{
	$arResult['success'] = false;
	$arResult['message'] = Loc::getMessage("ST_SHIPTOR_SELECT_PVZ_FAIL");
}
die(Json::encode($arResult));