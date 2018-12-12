<?php
/**
 * View - Shiptor shipments table (using widget)
 */
if (!defined('SHIPTOR_DIR')) define('SHIPTOR_DIR', $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shiptor.delivery/");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if($APPLICATION->GetGroupRight("main") < "R") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");
require_once ($DOCUMENT_ROOT.BX_ROOT."/modules/main/include/prolog_admin_after.php");


/*
CModule::IncludeModule("Shiptor.delivery");
$config = (array)CShiptor::getConfig();
$culture = (int)CShiptor::GetCultureId();
*/

$APPLICATION->SetTitle(GetMessage('SHIPTOR_DELIVERIS'));
$APPLICATION->AddHeadString('<script type="text/javascript" charset="utf-8" src="'.$config['jsUrl'].'"></script>');
$APPLICATION->AddHeadString('<script type="text/javascript">Shiptor.init({apikey: \''.$config['adminApiKey'].'\',cultureId: '.$culture.'});</script>');
$APPLICATION->AddHeadString('<meta http-equiv="X-UA-Compatible" content="IE=9" />');
$APPLICATION->AddHeadString('<link rel="stylesheet" type="text/css" href="'.$config['cssUrl'].'" />');
$APPLICATION->AddHeadString('<link rel="stylesheet" type="text/css" href="/bitrix/js/Shiptor.delivery/css/Shiptor.css" />');
?>

<script type="text/javascript">
    Shiptor.init({apikey: '<?=$config['adminApiKey'];?>', cultureId: <?=$culture?>});
    Shiptor.call_registry.ready = function() {
        Shiptor.get_shipments('#Shiptor-sdk-list', 1);
    }
</script>
<div id="Shiptor-sdk-list"></div>

<? require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php"); ?>