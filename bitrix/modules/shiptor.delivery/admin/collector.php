<?/*************************************************
	 * View - Shiptor shipments table (using widget) *
	 *************************************************/

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Sale\Internals\StatusTable;
use Bitrix\Sale\Shipment;
use Bitrix\Sale;

$MODULE_ID = 'shiptor.delivery';

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
Loader::includeModule('sale');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/admin_tool.php");

Loc::loadMessages(__FILE__);
Loc::loadMessages($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/admin/order.php');

$saleModulePermissions = $APPLICATION->GetGroupRight($MODULE_ID);
if($saleModulePermissions == "D")
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));


$APPLICATION->SetTitle(GetMessage('SHIPTOR_COLLECTOR'));


require_once ($DOCUMENT_ROOT.BX_ROOT."/modules/main/include/prolog_admin_after.php");
/*
CModule::IncludeModule("Shiptor.delivery");
$config = (array)CShiptor::getConfig();
$culture = (int)CShiptor::GetCultureId();
*/

/*
$APPLICATION->AddHeadString('<script type="text/javascript" charset="utf-8" src="'.$config['jsUrl'].'"></script>');
$APPLICATION->AddHeadString('<script type="text/javascript">Shiptor.init({apikey: \''.$config['adminApiKey'].'\',cultureId: '.$culture.'});</script>');
$APPLICATION->AddHeadString('<meta http-equiv="X-UA-Compatible" content="IE=9" />');
$APPLICATION->AddHeadString('<link rel="stylesheet" type="text/css" href="'.$config['cssUrl'].'" />');
$APPLICATION->AddHeadString('<link rel="stylesheet" type="text/css" href="/bitrix/js/Shiptor.delivery/css/Shiptor.css" />');
*/
?>

<script type="text/javascript">
/*
    Shiptor.init({apikey: '<?=$config['adminApiKey'];?>', cultureId: <?=$culture?>});
    Shiptor.call_registry.ready = function() {
        Shiptor.get_shipments('#Shiptor-sdk-list', 1);
    }
*/
</script>
<div id="Shiptor-sdk-list"></div>

<? require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php"); ?>