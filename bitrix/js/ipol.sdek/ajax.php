<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("ipol.sdek");

sdekHelper::getAjaxAction($_POST['isdek_action'],$_POST['action']);
?>