<?php
use Bitrix\Main\Context,
    Bitrix\Main\Loader,
    Shiptor\Delivery\Options\Config;

IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/options.php');
IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sale/options.php');

$moduleId = "shiptor.delivery";

Loader::includeModule($moduleId);

$SHIPTOR_RIGHT = $APPLICATION->GetGroupRight($moduleId);
if( ! ($SHIPTOR_RIGHT >= "R"))
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$SOptions = new Config();
$request = Context::getCurrent()->getRequest();
if($request->isPost()){
    $SOptions->saveSettings();
}else{
    $SOptions->drawSettingsForm();
}