<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

define("STOP_STATISTICS", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC","Y");
define("NO_AGENT_CHECK","Y");
define("DisableEventsCheck", true);
define("BX_SECURITY_SHOW_MESSAGE", true);
header('Content-Type: application/json; charset='.SITE_CHARSET);

use Bitrix\Main\Context,
    Bitrix\Main\Loader,
    Bitrix\Main\Web\Json,
    Shiptor\Delivery\ShiptorHandler;

$moduleId = "shiptor.delivery";
$oRequest = Context::getCurrent()->getRequest();

$result = array("success" => false);

Loader::includeModule($moduleId);

switch($oRequest["action"]){
    case "create_all_profiles":
        $result["success"] = ShiptorHandler::createAllProfiles($oRequest["parentId"], $oRequest["currency"]);
        break;
}
echo Json::encode($result);