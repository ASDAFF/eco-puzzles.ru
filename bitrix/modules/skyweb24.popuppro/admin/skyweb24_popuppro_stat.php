<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
use \Bitrix\Main\Application,
	Bitrix\Main\Page\Asset,
	Bitrix\Main\Request,
	Bitrix\Sale\Internals,
	Bitrix\Main\Localization\Loc;
	
Loc::loadMessages(__FILE__);

$module_id="skyweb24.popuppro";

//head
\Bitrix\Main\Loader::IncludeModule($module_id);
$APPLICATION->SetTitle(GetMessage("skyweb24.popuppro_STAT_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
$APPLICATION->IncludeFile("/bitrix/modules/".$module_id."/include/headerInfo.php", Array());

echo GetMessage("skyweb24.popuppro_STAT_TITLE");
?>

popupPro STAT
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>