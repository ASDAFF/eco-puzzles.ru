<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$APPLICATION->RestartBuffer();
unset($arResult["COMBO"]);
unset($arResult["FACET_FILTER"][0]);
echo CUtil::PHPToJSObject($arResult, true);
?>