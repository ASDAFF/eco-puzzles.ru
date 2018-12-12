<?
use Bitrix\Main\Loader,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!function_exists('boolval')) {
    function boolval($val) {
        return (bool) $val;
    }
}

$moduleId = "shiptor.delivery";
define("SHIPTOR_DELIVERY_YMAPS_URL","//api-maps.yandex.ru/2.1/?lang=ru_RU");
define("SHIPTOR_DELIVERY_FA_URL","https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css");

Loader::includeModule("sale");

Loader::registerAutoLoadClasses($moduleId, array(
    '\Shiptor\Delivery\CShiptorAPI' => "lib/CShiptorAPI.php",
    '\Shiptor\Delivery\ShiptorService' => 'lib/ShiptorService.php',
    '\Shiptor\Delivery\ShiptorHandler' => 'lib/ShiptorHandler.php',
    '\Shiptor\Delivery\ProfileHandler' => 'lib/ProfileHandler.php',
    '\Shiptor\Delivery\Restrictions\ExcludeLocation' => 'lib/restrictions/ExcludeLocation.php',
    '\Shiptor\Delivery\Services\DateDelivery' => 'lib/services/DateDelivery.php',
    '\Shiptor\Delivery\Services\TimeDelivery' => 'lib/services/TimeDelivery.php',
    '\Shiptor\Delivery\Options\Config' => 'lib/options/Config.php',
    '\Shiptor\Delivery\Options\Helper' => 'lib/options/Helper.php',
    '\Shiptor\Delivery\Logger' => 'lib/Logger.php',
    'CShiptorDeliveryHandler' => 'lib/include/handler.php',
    'CShiptorDeliveryHelper' => 'lib/include/helper.php'
));

include_once ($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/shiptor.delivery/lib/include/handler.php');
include_once ($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/shiptor.delivery/lib/include/helper.php');

if(!\CJSCore::IsExtRegistered('shiptor_pickup')){
    \CJSCore::RegisterExt(
        "shiptor_pickup",
        array(
            "js" => "/bitrix/js/{$moduleId}/pickup.js",
            "css" => "/bitrix/css/{$moduleId}/styles.css",
            "lang" => "/bitrix/modules/{$moduleId}/lang/".LANGUAGE_ID."/js/pickup.php",
            "rel" => Array("ajax","popup"),
            "skip_core" => false,
        )
    );
}
function wfDump($var){
    CShiptorDeliveryHelper::shDump($var);
}
function wfDumpHid($var){
    CShiptorDeliveryHelper::shDump($var,true);
}