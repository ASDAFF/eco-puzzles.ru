<?
define("STOP_STATISTICS",true);
define("NO_KEEP_STATISTIC","Y");
define("NO_AGENT_STATISTIC","Y");
define("DisableEventsCheck",true);
define("BX_SECURITY_SHOW_MESSAGE",true);
header('Content-Type: application/json; charset=utf-8');

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Context,
    Bitrix\Main\Loader,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\Web\Json;

$oRequest = Context::getCurrent()->getRequest();

Loader::IncludeModule("sale");
Loader::IncludeModule("shiptor.delivery");
Loc::loadMessages(__FILE__);

if($oRequest->isPost() && check_bitrix_sessid() && isset($oRequest['id'])
    && $oRequest['id'] > 0 && isset($oRequest['kladr']) && intval($oRequest["deliveryId"]) > 0){
    $idShipmentMethod = $oRequest['id'];
    $arParams = array(
        "kladr_id" => $oRequest['kladr'],
        "shipping_method" => intval($idShipmentMethod)
    );
    if(!empty($oRequest['limits'])){
        $arParams['limits']['weight'] = floatval($oRequest['limits']['weight']);
        $arParams['limits']['length'] = floatval($oRequest['limits']['length']);
        $arParams['limits']['height'] = floatval($oRequest['limits']['height']);
        $arParams['limits']['width'] = floatval($oRequest['limits']['width']);
    }
    if(!empty($oRequest['selfPickup'])){
        $arParams['self_pick_up'] = true;
    }
    $arPVZ = CShiptorDeliveryHelper::getPvz($arParams);
    if(count($arPVZ) > 0){
        $arResult['pvz'] = $arPVZ;
        $arResult['success'] = true;
        $arResult['message'] = Loc::getMessage("ST_SHIPTOR_SHOW_PVZ_SUCCESS",array("#SHIPSP_ID#" => $idShipmentMethod));
    }else{
        $arResult['success'] = false;
        $arResult['message'] = Loc::getMessage("ST_SHIPTOR_SHOW_PVZ_FAIL_NO_PVZ");
    }
}else{
    $arResult['success'] = false;
    $arResult['message'] = Loc::getMessage("ST_SHIPTOR_SHOW_PVZ_FAIL");
}
die(Json::encode($arResult));