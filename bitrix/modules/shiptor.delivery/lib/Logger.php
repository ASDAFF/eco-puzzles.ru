<?php
namespace Shiptor\Delivery;

use Shiptor\Delivery\Options\Config,
    Bitrix\Main\Web\Json;

class Logger{
    const MODULE_ID = 'shiptor.delivery';
    public static function info($data, $force = false){
        if(Config::isDebug() || $force){
            $arFields = ["SEVERITY" => "INFO", "AUDIT_TYPE_ID" => "SHIPTOR",
                "MODULE_ID" => self::MODULE_ID];
            switch(gettype($data)){
                case "array":
                    $arFields["DESCRIPTION"] = serialize($data);
                    break;
                default:
                    $arFields["DESCRIPTION"] = $data;
            }
            \CEventLog::Add($arFields);
        }
    }
    public static function force($data){
        self::info($data,true);
    }
    public static function exception($e){
        if($e instanceof \Exception){
            self::info($e->getMessage().' '.$e->getFile().':'.$e->getLine(),true);;
        }
    }
}