<?php
namespace Shiptor\Delivery\Services;

use Bitrix\Sale\Delivery\ExtraServices\Base as ExtraServicesBase,
    Bitrix\Sale\Delivery\ExtraServices\Table as DEST,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\Loader;

Loader::includeModule("sale");
Loc::loadMessages(__FILE__);

class TimeDelivery extends ExtraServicesBase{
    public function __construct($id, array $structure, $currency, $value = null, array $additionalParams = array()){
        $structure["PARAMS"]["ONCHANGE"] = $this->createJSOnchange($id, $structure["PARAMS"]["PRICE"]);
        parent::__construct($id, $structure, $currency, $value);
        $this->params["ID"] = "_sh_time_delivery";
        $this->params["TYPE"] = "ENUM";
        $this->params["OPTIONS"] = $this->getDeliveryTimes();
    }
    public function getClassTitle(){
        return Loc::getMessage("SHIPTOR_TIME_DELIVERY_NAME");
    }
    public static function getAdminParamsName(){
        return Loc::getMessage("SHIPTOR_TIME_DELIVERY_COST");
    }
    public function setValue($value){
        $this->value = (string)($value);
    }
    public static function getAdminParamsControl($name, array $params = array(), $currency = ""){
        if(!empty($params["PARAMS"]["PRICE"]))
            $price = roundEx(floatval($params["PARAMS"]["PRICE"]), SALE_VALUE_PRECISION);
        else
            $price = 0;

        return '<input type="text" name="'.$name.'[PARAMS][PRICE]" value="'.$price.'">'.(strlen($currency) > 0 ? " (".htmlspecialcharsbx($currency).")" : "");
    }
    public function setOperatingCurrency($currency){
        $this->params["ONCHANGE"] = $this->createJSOnchange($this->id, $this->getPrice());
        parent::setOperatingCurrency($currency);
    }
    protected function createJSOnchange($id, $price){
        return <<<JS
BX.onCustomEvent('onDeliveryExtraServiceValueChange', [{'id' : '{$id}', 'value': this.value, 'price': this.value ? '{$price}' : '0'}]);
JS;
    }
    public static function getId($deliveryId){
        $destParams = array(
            "filter" => array("CLASS_NAME" => "%Service%TimeDelivery", "ACTIVE" => "Y", "DELIVERY_ID" => $deliveryId),
            "select" => array("ID")
        );
        $res = DEST::getList($destParams)->fetch();
        return intval($res["ID"]);
    }
    private function getDeliveryTimes(){
        return \CShiptorDeliveryHelper::getDeliveryTime();
    }
    public function disable(){
        $this->params["DISABLED"] = true;
    }
    public function setAttribute($attrName, $value){
        $this->params[$attrName] = $value;
    }
    public function setTitle($text){
        $this->setAttribute("TITLE", $text);
    }
}