<?php
namespace Shiptor\Delivery\Options;

use Bitrix\Main\Loader,
    Bitrix\Main\Context,
    Bitrix\Main\Config\Option;

Loader::includeModule("sale");

class Config{
    const MODULE_ID = "shiptor.delivery";
    const ADDRESS_SIMPLE = "simple";
    const ADDRESS_COMPLEX = "complex";
    const DATE_NEAR = "near";
    const DATE_DELAY = "delay";
    const SORT_PROFILES_DAYS = "days";
    const SORT_PROFILES_PRICE = "price";
    const DEPARTURE_TYPE_AUTO = "auto";
    const DEPARTURE_TYPE_MAN = "man";
    const ROUND_TYPE_NONE = 'n';
    const ROUND_TYPE_MATH = 'm';
    const ROUND_TYPE_FLOOR = 'f';
    const ROUND_TYPE_CEIL = 'c';
    private $fields = array(
        'adminApiKey',
        'publicApiKey',
        'apiUrl',
        'jsUrl',
        'cssUrl',
        'pvzStrict',
        'checkout',
        'debug',
        'sortProfiles',
        'productArticle',
        'xmlIdComplex',
        'articleProperty',
        'cashPaymentsIds',
        'tracking_map_statuses',
        'direct_reciever',
        'direct_phone',
        'direct_email',
        'direct_zip',
        'direct_street',
        'direct_house',
        'direct_flat',
        'direct_comment',
        'direct_date_type',
        'direct_date_delay',
        'direct_time',
        'address_type',
        'configOk',
        'adminOrderViewUrl',
        'adminOrderAddUrl',
        'adminOrderEditUrl',
        'syncCreateOrderLimit',
        'mirror_pvz_address',
        'include_yamaps',
        'departure_type',
        'departure_status',
        'change_status',
        'rounding_type',
        'rounding_precision',
        'is_fulfilment',
        'is_pvz_haunt',
        'is_date_time_mirror'
    );
    private $data;
    public function __construct(){
        $this->getOptionsData();
    }
    public function getOptionsData(){
        $this->data = array();
        foreach($this->fields as $field){
            $this->data[$field] = $this->getDataValue($field);
        }
        $this->data['debug'] = boolval($this->data['debug']);
        $this->data['direct'] = boolval($this->data['direct']);
        $arPersonTypes = \CShiptorDeliveryHelper::getPersonTypes();
        $isAddressSimple = boolval($this->data["address_type"] != self::ADDRESS_COMPLEX);
        if($isAddressSimple){
            $this->data["mirror_pvz_address"] = boolval($this->getDataValue('mirror_pvz_address'));
        }
        foreach($arPersonTypes as $id => $name){
            $this->fields[] = 'pvz_prop_'.$id;
            $this->data['pvz_prop_'.$id] = $this->getDataValue('pvz_prop_'.$id);
            if($isAddressSimple){
                $this->fields[] = 'address_prop_id_'.$id;
                $this->data['address_prop_id_'.$id] = $this->getDataValue('address_prop_id_'.$id);
            }else{
                $this->fields[] = 'street_prop_id_'.$id;
                $this->data['street_prop_id_'.$id] = $this->getDataValue('street_prop_id_'.$id);
                $this->fields[] = 'corp_prop_id_'.$id;
                $this->data['corp_prop_id_'.$id] = $this->getDataValue('corp_prop_id_'.$id);
                $this->fields[] = 'bld_prop_id_'.$id;
                $this->data['bld_prop_id_'.$id] = $this->getDataValue('bld_prop_id_'.$id);
                $this->fields[] = 'flat_prop_id_'.$id;
                $this->data['flat_prop_id_'.$id] = $this->getDataValue('flat_prop_id_'.$id);
            }
        }
    }
    public function getDataValue($name){
        return Option::get(self::MODULE_ID,"shiptor_{$name}");
    }
    public function getFields(){
        return $this->fields;
    }
    public function saveSettings(){
        $request = Context::getCurrent()->getRequest();
        if($request->isPost()){
            foreach($this->getFields() as $field){
                if(isset($request[$field])){
                    $this->saveParam($field,$request[$field]);
                }
            }
        }
        $this->drawSettingsForm();
    }
    public function saveParam($name, $value){
        $this->data[$name] = $value;
        switch($name){
            case "cashPaymentsIds":
                $value = implode("|",$value);
                $this->data[$name] = $value;
                break;
            case "tracking_map_statuses":
                $value = serialize($value);
                break;
        }
        Option::set(self::MODULE_ID, "shiptor_" . $name, $value);
    }
    public function drawSettingsForm(){
        $shiptorSettings = $this->data;
        include(__DIR__.'/../../templates/drawSettingsForm.php');
    }
    public static function isDirect(){
        $direct = self::getDataValue("direct");
        return boolval($direct);
    }
    public static function isAddressSimple(){
        $addressType = self::getDataValue("address_type");
        return boolval($addressType != self::ADDRESS_COMPLEX);
    }
    public static function isDebug(){
        $debug = self::getDataValue("debug");
        return boolval($debug);
    }
    public static function isMirrorPvz(){
        $mirror = self::getDataValue("mirror_pvz_address");
        return boolval($mirror);
    }
    public static function isPvzHaunt(){
        $isPvzHaunt = self::getDataValue("is_pvz_haunt");
        return !($isPvzHaunt === "0");
    }
    public static function isIncludeYaMaps(){
        $isIncludeYamaps = self::getDataValue("include_yamaps");
        return !($isIncludeYamaps === "0");
    }
    public static function isFulfilment(){
        $isFulfilment = self::getDataValue("is_fulfilment");
        return !($isFulfilment === "0");
    }
    public static function isDateTimeMirror(){
        $isFulfilment = self::getDataValue("is_date_time_mirror");
        return !($isFulfilment === "0");
    }
    public static function getApiKey(){
        return self::getDataValue("adminApiKey");
    }
    public static function getAddressPropId($personTypeId){
        return self::getDataValue("address_prop_id_".$personTypeId);
    }
    public static function getStreetPropId($personTypeId){
        return self::getDataValue("street_prop_id_".$personTypeId);
    }
    public static function getBldPropId($personTypeId){
        return self::getDataValue("bld_prop_id_".$personTypeId);
    }
    public static function getCorpPropId($personTypeId){
        return self::getDataValue("corp_prop_id_".$personTypeId);
    }
    public static function getFlatPropId($personTypeId){
        return self::getDataValue("flat_prop_id_".$personTypeId);
    }
    public static function getPvzPropId($personTypeId){
        return self::getDataValue("pvz_prop_".$personTypeId);
    }
    public static function getSortProfiles(){
        return self::getDataValue("sortProfiles");
    }
    public static function getCashPayments(){
        $cashPayments = self::getDataValue("cashPaymentsIds");
        return explode("|",$cashPayments);
    }
    public static function isAutomaticUpload(){
        $uploadType = self::getDataValue("departure_type");
        return (bool)($uploadType == self::DEPARTURE_TYPE_AUTO);
    }
    public static function getTriggerStatus(){
        $triggerStatus = self::getDataValue("departure_status");
        return $triggerStatus;
    }
    public static function getChangeStatus(){
        $changeStatus = self::getDataValue("change_status");
        return $changeStatus;
    }
    public static function getRoundingType(){
        return self::getDataValue("rounding_type");
    }
    public static function getRoundingPrecision(){
        return self::getDataValue("rounding_precision");
    }
    public static function getCheckoutUrl(){
        
    }
}