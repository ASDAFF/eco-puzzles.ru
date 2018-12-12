<?php
namespace Shiptor\Delivery;

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Config\Option,
    Bitrix\Main\Loader,
    Bitrix\Main\Context,
    Bitrix\Main\Application,
    Bitrix\Main\Page\Asset,
    Bitrix\Sale\Order,
    Bitrix\Sale\Basket,
    Bitrix\Sale\Shipment,
    Bitrix\Sale\Delivery\Services\Manager,
    Bitrix\Sale\Delivery\Services\Table as DST,
    Shiptor\Delivery\Options\Config,
    Shiptor\Delivery\CShiptorAPI,
    Shiptor\Delivery\ShiptorHandler,
    Shiptor\Delivery\Services\DateDelivery,
    Shiptor\Delivery\Services\TimeDelivery,
    Shiptor\Delivery\Logger;

Loc::loadMessages(__FILE__);
Loader::includeModule('shiptor.delivery');
Loader::includeModule('sale');

class ProfileHandler extends \Bitrix\Sale\Delivery\Services\Base{
    protected static $isProfile = true;
    protected static $parent = null;
    protected static $whetherAdminExtraServicesShow = false;
    private $category = null;
    private $courier = null;
    private $group = null;
    private $profileName = null;
    const MODULE_ID = 'shiptor.delivery';
    const COUNTRY_RU = 'RU';
    const COUNTRY_KZ = 'KZ';
    const COUNTRY_BY = 'BY';
    public function __construct(array $initParams) {
        if($initParams['CONFIG'] === false) $initParams['CONFIG'] = array();
        parent::__construct($initParams);
        $this->parent = Manager::getObjectById($this->parentId);
        if($this->id <= 0 && strlen($initParams['PROFILE_ID']) > 0){
            $arAvailableProfiles = $this->getParentService()->getAvailableProfiles();
            $arProfileParams = $arAvailableProfiles[$initParams['PROFILE_ID']];
            $this->category = $arProfileParams['category'];
            $this->courier = $arProfileParams['courier'];
            $this->group = $arProfileParams['group'];
            $this->name = $arProfileParams['name'];
            $this->profileName = $arProfileParams['name'];
            $this->description = $arProfileParams['description'];
        }else{
            $this->category = $this->config['MAIN']['CATEGORY'];
            $this->courier = $this->config['MAIN']['COURIER'];
            $this->group = $this->config['MAIN']['GROUP'];
            $this->profileName = $this->config['MAIN']['NAME'];
        }
        $this->setDefaultLogo();
        if($this->isShiptorCourier()){
            self::$whetherAdminExtraServicesShow = true;
        }
        if($this->isPvz()){
            $oRequest = Context::getCurrent()->getRequest();
            if($oRequest->isAdminSection() && '/bitrix/admin/sale_order_edit.php' == $oRequest->getRequestedPage()){
                $this->addAdminJS();
            }
        }
    }
    public static function getClassTitle() {
        return Loc::getMessage('SHIPTOR_CLASS_TITLE');
    }
    public static function getClassDescription() {
        return Loc::getMessage('SHIPTOR_CLASS_DESC');
    }
    public function getCourier(){
        return $this->courier;
    }
    private function setDefaultLogo(){
        $sDocumentRoot = Application::getDocumentRoot();
        $logoPath = \CFile::GetPath($this->logotip);
        if(!$this->logotip || !file_exists($sDocumentRoot.$logoPath) || empty($logoPath)){
            $this->logotip = \CShiptorDeliveryHelper::getDefaultLogo($this->courier);
        }
        return $this->logotip;
    }
    protected function calculateConcrete(Shipment $shipment = null) {
        $oCalculationResult = new \Bitrix\Sale\Delivery\CalculationResult();
        try{
            $arData = $this->getOrderData($shipment);
            $calculation = \CShiptorDeliveryHelper::getDeliveryPrice($arData);
            if(empty($calculation['RESULT']['PRICE'])){
                throw new \Exception(Loc::getMessage('SHIPTOR_ZERO_PRICE',array('#METHOD_NAME#' => $this->name)));
            }
            if(is_numeric($this->config['MAIN']['FIXED_PRICE'])){
                $arDeliveryPrice['PRICE'] = roundEx($this->config['MAIN']['FIXED_PRICE'],SALE_VALUE_PRECISION);
            }else{
                $arDeliveryPrice = $this->calculateMargin($calculation['RESULT']['PRICE']);
            }
            $oCalculationResult->setDeliveryPrice($arDeliveryPrice['PRICE']);
            $days = $this->getTerms($calculation['RESULT']['DAYS']);
            switch(Config::getSortProfiles()){
                case Config::SORT_PROFILES_DAYS:
                    $sort = intval($days)*50;
                    $this->setSort($sort);
                    break;
                case Config::SORT_PROFILES_PRICE:
                    $sort = intval($arDeliveryPrice['PRICE'] / 10);
                    $this->setSort($sort);
                    break;
            }
            if(!empty($days)){
                $oCalculationResult->setPeriodDescription($days);
            }
        }catch(\Exception $e){
            $oCalculationResult->addError(new \Bitrix\Main\Error($e->getMessage()));
            Logger::exception($e);
        }
        return $oCalculationResult;
    }
    public function isCompatible(Shipment $shipment){
        $calcResult = $this->calculateConcrete($shipment);
        return $calcResult->isSuccess();
    }
    public function setSort($sort){
        if($sort > 0){
            $this->sort = $sort;
        }
    }
    public function getCountryCode($country){
        if(strpos(Loc::getMessage('SHIPTOR_COUNTRY_RU'),$country) !== false){
            return self::COUNTRY_RU;
        }
        if($country == Loc::getMessage('SHIPTOR_COUNTRY_KZ')){
            return self::COUNTRY_KZ;
        }
        if(strpos(Loc::getMessage('SHIPTOR_COUNTRY_BY'),$country) !== false){
            return self::COUNTRY_BY;
        }
        return self::COUNTRY_RU;
    }
    public function getOrderData(Shipment $shipment){
        $oShiptorApi = CShiptorAPI::getInstance();
        if (!$oShiptorApi->isTokenValid()) {
            throw new \Exception(Loc::getMessage('SHIPTOR_NA_API_KEY'));
        }
        $order = $shipment->getCollection()->getOrder();
        $personTypeId = $order->getPersonTypeId();

        $propertyCollection = $order->getPropertyCollection();

        $arOrder = array(
            'METHOD' => $this->getCategory(),
            'COURIER' => $this->getCourier(),
            'NAME' => $this->name
        );
        foreach($order->getAvailableFields() as $arField){
            if($order->getField($arField) && ! is_object($order->getField($arField))){
                $arOrder[$arField] = $order->getField($arField);
            }
        }
        $propertyOrder = $order->getPropertyCollection();
        foreach($propertyOrder->getArray() as $arPropS){
            foreach($arPropS as $arProp){
                if($arProp['VALUE'][0]){
                    $arOrder['PROPERTIES'][$arProp['CODE']] = $arProp['VALUE'][0];
                }
            }
        }
        $DeliveryLocation = $propertyCollection->getDeliveryLocation();
        if(empty($DeliveryLocation)){
            throw new \Exception(Loc::getMessage('SHIPTOR_NO_LOCATION_PROP'));
        }

        $locationCode = $DeliveryLocation->getValue();
        if(empty($locationCode)){
            throw new \Exception(Loc::getMessage('SHIPTOR_EMPTY_LOCATION'));
        }
        $arLocation = $this->getLocation($locationCode);
        $arLocation['COUNTRY_CODE'] = $this->getCountryCode($arLocation['COUNTRY']);
        if(!$arLocation || empty($arLocation['CITY'])){
            throw new \Exception(Loc::getMessage('SHIPTOR_EMPTY_LOCATION'));
        }
        if(empty($arLocation['KLADR'])){
            throw new \Exception(Loc::getMessage('SHIPTOR_EMPTY_KLADR'));
        }
        $arOrder['CONFIG'] = $this->getParentService()->getConfigOuter();
        $arOrder['LOCAL_CONFIG'] = $this->config;
        $propertyFio = $propertyCollection->getProfileName();
        if(empty($propertyFio)){
            $propertyFio = $propertyCollection->getPayerName();
            if(empty($propertyFio)){
                throw new \Exception(Loc::getMessage('SHIPTOR_NO_FIO_PROP'));
            }
        }
        $arOrder['FIO'] = $propertyFio->getValue();
        $propertyEmail = $propertyCollection->getUserEmail();
        if(empty($propertyEmail)){
            throw new \Exception(Loc::getMessage('SHIPTOR_NO_EMAIL_PROP'));
        }
        $arOrder['EMAIL'] = $propertyEmail->getValue();
        $arOrder['DESC'] = $order->getField('USER_DESCRIPTION');
        $propertyPhone = $propertyCollection->getPhone();
        if(empty($propertyPhone)){
            throw new \Exception(Loc::getMessage('SHIPTOR_NO_PHONE_PROP'));
        }
        $arOrder['PHONE'] = $propertyPhone->getValue();
        $propertyZip = $propertyCollection->getDeliveryLocationZip();
        if(!empty($propertyZip)){
            $arLocation['ZIP'] = $propertyZip->getValue();
        }
        $orderBasket = $order->getBasket();
        $arOrder['ORDER']['ITEMS'] = $this->getBasket($orderBasket);
        $arOrder['ORDER']['DIMENSIONS'] = $this->getOrderDimensions($arOrder);
        $arOrder['ORDER']['WEIGHT'] = $this->getOrderWeight($arOrder);
        $arOrder['PRICE'] = roundEx($orderBasket->getPrice(),SALE_VALUE_PRECISION);
        $arOrder['ORDER']['ID'] = intval($order->getId());
        if($arOrder['ORDER']['ID'] > 0){
            $arOrder['ORDER']['PAYED'] = $order->isPaid();
            $arOrder['ORDER']['PRICE'] = $order->getPrice();
        }else{
            $arOrder['ORDER']['PAYED'] = false;
        }
        $arOrder['ADDRESS'] = $arLocation;
        if(Config::isAddressSimple()){
            $addressPropId = Config::getAddressPropId($personTypeId);
            $propAddress = $propertyCollection->getAddress();
            if(empty($propAddress)){
                $propAddress = $propertyCollection->getItemByOrderPropertyId($addressPropId);
            }
            if(empty($propAddress)){
                throw new \Exception(Loc::getMessage('SHIPTOR_EMPTY_ADDRESS'));
            }
            $arOrder['ADDRESS']['STREET'] = $propAddress->getValue();
        }else{
            $streetPropId = Config::getStreetPropId($personTypeId);
            $streetProp = $propertyCollection->getItemByOrderPropertyId($streetPropId);
            if(empty($streetProp)){
                throw new \Exception(Loc::getMessage('SHIPTOR_EMPTY_STREET'));
            }
            $arOrder['ADDRESS']['STREET'] = $streetProp->getValue();
            $bldPropId = Config::getBldPropId($personTypeId);
            $bldProp = $propertyCollection->getItemByOrderPropertyId($bldPropId);
            if(empty($bldProp)){
                throw new \Exception(Loc::getMessage('SHIPTOR_EMPTY_BLD'));
            }
            $arOrder['ADDRESS']['BLD'] = $bldProp->getValue();
            $corpPropId = Config::getCorpPropId($personTypeId);
            $corpProp = $propertyCollection->getItemByOrderPropertyId($corpPropId);
            if(!empty($corpProp)){
                $arOrder['ADDRESS']['CORP'] = $corpProp->getValue();
            }
            $flatPropId = Config::getFlatPropId($personTypeId);
            $flatProp = $propertyCollection->getItemByOrderPropertyId($flatPropId);
            if(empty($flatProp)){
                throw new \Exception(Loc::getMessage('SHIPTOR_EMPTY_FLAT'));
            }
            $arOrder['ADDRESS']['FLAT'] = $flatProp->getValue();
        }
        $paymentIds = $order->getPaymentSystemId();
        switch($arOrder['CONFIG']['COD']['CALCULATION_TYPE']){
            case ShiptorHandler::COD_ALWAYS:default:
                $arOrder['ORDER']['COD'] = true;
                break;
            case ShiptorHandler::COD_NEVER:
                $arOrder['ORDER']['COD'] = false;
                break;
            case ShiptorHandler::COD_CERTAIN:
                if(empty($paymentIds) || $paymentIds[0] == 0){
                    if($arOrder['ADDRESS']['COUNTRY_CODE'] == self::COUNTRY_RU){
                        $arOrder['ORDER']['COD'] = true;
                    }else{
                        $arOrder['ORDER']['COD'] = false;
                    }
                }else{
                    $codPaymentIds = $arOrder['CONFIG']['COD']['SERVICES_LIST'];
                    $arOrder['ORDER']['COD'] = array_intersect($paymentIds,$codPaymentIds)?true:false;
                }
                break;
        }
        if(empty($paymentIds) || $paymentIds[0] == 0){
            $arOrder['ORDER']['PAYMENT'] = 'cash';
        }else{
            $cashPaymentId = Config::getCashPayments();
            $arOrder['ORDER']['PAYMENT'] = array_intersect($cashPaymentId,$paymentIds)?'cash':'card';
        }
        $arOrder['METHOD_ID'] = $this->getMethodId();
        if($this->isPvz()){
            if($arOrder['ORDER']['ID'] > 0){
                $order = $shipment->getCollection()->getOrder();
                $propertyCollection = $order->getPropertyCollection();
                $propPvzId = Config::getPvzPropId($personTypeId);
                $propPvz = $propertyCollection->getItemByOrderPropertyId($propPvzId);
                if(empty($propPvz)){
                    throw new \Exception(Loc::getMessage('SHIPTOR_NO_PVZ_PROP'));
                }
                $arOrder['PVZ_CODE'] = $propPvz->getValue();
            }
            $arPvz = $this->checkAvailablePvz($arOrder);
            if(empty($arPvz)){
                throw new \Exception($this->name.' - '.Loc::getMessage('SHIPTOR_NO_PVZ_AVAILABLE'));
            }
            if(count($arPvz) == 1 && empty($arOrder['PVZ_CODE']) && empty($_SESSION['Shiptor'][$this->id]['PVZ_ID'])){
                $arOrder['PVZ_CODE'] = $arPvz[0]['id'];
                $_SESSION['Shiptor'][$this->id]['PVZ_ID'] = $arPvz[0]['id'];
                $_SESSION['Shiptor'][$this->id]['PVZ_INFO'] = $arPvz[0];
            }
        }
        if($arOrder['ORDER']['ID'] > 0){
            if($arOrder['ORDER']['COD'] && $arOrder['ADDRESS']['COUNTRY_CODE'] != self::COUNTRY_RU){
                throw new \Exception($this->name.' - '.Loc::getMessage('SHIPTOR_NO_COD'));
            }
            if($this->isShiptorCourier()){
                $dateExtraServiceId = DateDelivery::getId($this->id);
                $extraServices = $shipment->getExtraServices();
                $arOrder['ORDER']['DATE_DELIVERY'] = $extraServices[$dateExtraServiceId]?:date('d.m.Y',strtotime('+1day'));
                $timeExtraServiceId = TimeDelivery::getId($this->id);
                if(!empty($arOrder['ORDER']['DATE_DELIVERY']) && !empty($extraServices[$timeExtraServiceId])){
                    $arDaysOff = DateDelivery::getDaysOff();
                    if(!in_array($arOrder['ORDER']['DATE_DELIVERY'],$arDaysOff)){
                        $arOrder['ORDER']['TIME_DELIVERY'] = intval($extraServices[$timeExtraServiceId]);
                    }
                }
            }
        }else{
            $now12 = strtotime(date('d.m.Y 12:00'));
            $now21 = strtotime(date('d.m.Y 20:50'));
            if($this->isShiptorToday() && (time() > $now12) && (time() < $now21)){
                throw new \Exception(Loc::getMessage('SHIPTOR_ONE_DAY_NOT_AVAILABLE'));
            }
        }
        $arOrder['IS_DIRECT'] = $this->isDirect();
        if($this->isDirect()){
            $arOrder['SENDER'] = $this->getSenderData();
            if(in_array($this->category,array('delivery-point-to-door','delivery-point-to-delivery-point'))){
                $arParamsPvz = array(
                    'kladr_id' => $arOrder['SENDER']['KLADR'],
                    'shipping_method' => $arOrder['METHOD_ID'],
                    'courier' => $arOrder['COURIER'],
                    'self_pick_up' => true,
                    'limits' => array(
                        'length' => floatval($arOrder['ORDER']['DIMENSIONS']['LENGTH']),
                        'width' => floatval($arOrder['ORDER']['DIMENSIONS']['WIDTH']),
                        'height' => floatval($arOrder['ORDER']['DIMENSIONS']['HEIGHT']),
                        'weight' => $arOrder['ORDER']['WEIGHT']
                    )
                );
                $arAvailablePvz = \CShiptorDeliveryHelper::getPvz($arParamsPvz);
                if(empty($arAvailablePvz)){
                    throw new \Exception($this->name.' - '.Loc::getMessage('SHIPTOR_NO_PVZ_AVAILABLE_SENDER'));
                }
                $bPvzFound = false;
                foreach($arAvailablePvz as $pvz){
                    if($pvz['id'] == $arOrder['SENDER']['PVZ_CODE']){
                        $bPvzFound = true;
                    }
                }
                if(!$bPvzFound){
                    throw new \Exception($this->name.' - '.Loc::getMessage('SHIPTOR_NO_PVZ_AVAILABLE_SENDER'));
                }
            }
        }
        return $arOrder;
    }
    private function getSenderData(){
        $senderLocation = $this->config['DIRECT']['LOCATION'];
        if(empty($senderLocation)){
            throw new \Exception($this->name.' - '.Loc::getMessage('SHIPTOR_NO_SENDER_LOCATION'));
        }
        $arOrder['SENDER'] = \CShiptorDeliveryHelper::getLocationByCode($senderLocation);
        if(empty($arOrder['SENDER'])){
            throw new \Exception($this->name.' - '.Loc::getMessage('SHIPTOR_NO_SENDER_LOCATION'));
        }
        $arOrder['SENDER']['COUNTRY_CODE'] = $this->getCountryCode($arOrder['SENDER']['COUNTRY']);
        if($this->config['DIRECT']['RECIEVER_TYPE'] == 'SET'){
            $arOrder['SENDER']['FIO'] = Config::getDataValue('direct_reciever');
            $arOrder['SENDER']['PHONE'] = Config::getDataValue('direct_phone');
            $arOrder['SENDER']['EMAIL'] = Config::getDataValue('direct_email');
        }else{
            $arOrder['SENDER']['FIO'] = $this->config['DIRECT']['RECIEVER'];
            $arOrder['SENDER']['PHONE'] = $this->config['DIRECT']['PHONE'];
            $arOrder['SENDER']['EMAIL'] = $this->config['DIRECT']['EMAIL'];
        }
        if(empty($arOrder['SENDER']['FIO'])){
            throw new \Exception($this->name.' - '.Loc::getMessage('SHIPTOR_NO_SENDER_FIO'));
        }
        if(empty($arOrder['SENDER']['PHONE'])){
            throw new \Exception($this->name.' - '.Loc::getMessage('SHIPTOR_NO_SENDER_PHONE'));
        }
        if(empty($arOrder['SENDER']['EMAIL'])){
            throw new \Exception($this->name.' - '.Loc::getMessage('SHIPTOR_NO_SENDER_EMAIL'));
        }
        if($this->config['DIRECT']['DATE_TIME_TYPE'] == 'SET'){
            $arOrder['SENDER']['DATE_TYPE'] = Config::getDataValue('direct_date_type');
            $arOrder['SENDER']['DELAY'] = Config::getDataValue('direct_date_delay');
        }else{
            $arOrder['SENDER']['DATE_TYPE'] = $this->config['DIRECT']['DATE_TYPE'];
            $arOrder['SENDER']['DELAY'] = $this->config['DIRECT']['DELAY'];
        }
        switch($arOrder['SENDER']['DATE_TYPE']){
            case Config::DATE_NEAR:default:
                $arOrder['SENDER']['DATE'] = date('d.m.Y',strtotime('+1 day'));
                break;
            case Config::DATE_DELAY:
                $delay = $arOrder['SENDER']['DELAY'] + 1;
                $arOrder['SENDER']['DATE'] = date('d.m.Y',strtotime("+{$delay} day"));
                break;
        }
        if(in_array($this->category,array('door-to-door','door-to-delivery-point'))){
            if($this->config['DIRECT']['ADDRESS_TYPE'] == 'SET'){
                $arOrder['SENDER']['ZIP'] = Config::getDataValue('direct_zip');
                $arOrder['SENDER']['STREET'] = Config::getDataValue('direct_street');
                $arOrder['SENDER']['HOUSE'] = Config::getDataValue('direct_house');
                $arOrder['SENDER']['FLAT'] = Config::getDataValue('direct_flat');
            }else{
                $arOrder['SENDER']['ZIP'] = $this->config['DIRECT']['ZIP'];
                $arOrder['SENDER']['STREET'] = $this->config['DIRECT']['STREET'];
                $arOrder['SENDER']['HOUSE'] = $this->config['DIRECT']['HOUSE'];
                $arOrder['SENDER']['FLAT'] = $this->config['DIRECT']['FLAT'];
            }
            if(empty($arOrder['SENDER']['ZIP'])){
                throw new \Exception($this->name.' - '.Loc::getMessage('SHIPTOR_NO_SENDER_ZIP'));
            }
            if(empty($arOrder['SENDER']['STREET'])){
                throw new \Exception($this->name.' - '.Loc::getMessage('SHIPTOR_NO_SENDER_STREET'));
            }
            if(empty($arOrder['SENDER']['HOUSE'])){
                throw new \Exception($this->name.' - '.Loc::getMessage('SHIPTOR_NO_SENDER_HOUSE'));
            }
        }elseif(in_array($this->category,array('delivery-point-to-door','delivery-point-to-delivery-point'))){
            $arOrder['SENDER']['PVZ_CODE'] = $this->config['DIRECT']['PVZ_CODE'];
            $arOrder['SENDER']['PVZ_ADDRESS'] = $this->config['DIRECT']['PVZ_ADDRESS'];
            if(empty($arOrder['SENDER']['PVZ_CODE'])){
                throw new \Exception($this->name.' - '.Loc::getMessage('SHIPTOR_NO_SENDER_PVZ_CODE'));
            }
        }
        $arOrder['SENDER']['COMMENT'] = $this->config['DIRECT']['COMMENT'];
        return $arOrder['SENDER'];
    }
    protected function checkAvailablePvz($arOrder){
        $arParams = array(
            'kladr_id' => $arOrder['ADDRESS']['KLADR'],
            'shipping_method' => intval($arOrder['METHOD_ID']),
            'cod' => $arOrder['ORDER']['COD'],
            'card' => (bool)($arOrder['ORDER']['PAYMENT'] == 'card'),
            'limits' => array(
                'weight' => floatval($arOrder['ORDER']['WEIGHT']),
                'length' => intval($arOrder['ORDER']['DIMENSIONS']['LENGTH']),
                'width' => intval($arOrder['ORDER']['DIMENSIONS']['WIDTH']),
                'height' => intval($arOrder['ORDER']['DIMENSIONS']['HEIGHT']),
            )
        );
        if(!$arOrder['ORDER']['COD']){
            unset($arParams['cod'],$arParams['card']);
        }
        $arPvz = \CShiptorDeliveryHelper::getPvz($arParams);
        return $arPvz;
    }
    public function getOrderDimensions($arOrder){
        if(count($arOrder['ORDER']['ITEMS']) == 1 && !empty($arOrder['ORDER']['ITEMS'][0]['WIDTH'])
                && !empty($arOrder['ORDER']['ITEMS'][0]['HEIGHT']) && !empty($arOrder['ORDER']['ITEMS'][0]['LENGTH'])
                && $arOrder['ORDER']['ITEMS'][0]['QUANTITY'] == 1){
            $arOrderDimensions = array(
                'LENGTH' => ceil($arOrder['ORDER']['ITEMS'][0]['LENGTH']/10),
                'WIDTH' => ceil($arOrder['ORDER']['ITEMS'][0]['WIDTH']/10),
                'HEIGHT' => ceil($arOrder['ORDER']['ITEMS'][0]['HEIGHT']/10)
            );
        }else{
            if($arOrder['CONFIG']['MAIN']['CALC_ALGORITM'] == 'Y'){
                $arOrderDimensions = $this->getEffectiveDimensions($arOrder);
            }else{
                $arOrderDimensions = array(
                    'LENGTH' => $arOrder['CONFIG']['MAIN']['LENGTH_VALUE'],
                    'WIDTH' => $arOrder['CONFIG']['MAIN']['WIDTH_VALUE'],
                    'HEIGHT' => $arOrder['CONFIG']['MAIN']['HEIGHT_VALUE'],
                );
            }
        }
        return $arOrderDimensions;
    }
    public function getEffectiveDimensions($arOrder){
        $arBasket = $arOrder['ORDER']['ITEMS'];
        $defaultDimensions = array(
            'LENGTH' => $arOrder['CONFIG']['MAIN']['LENGTH_VALUE'],
            'WIDTH' => $arOrder['CONFIG']['MAIN']['WIDTH_VALUE'],
            'HEIGHT' => $arOrder['CONFIG']['MAIN']['HEIGHT_VALUE']
        );
        $sumVolume = 0;
        $arDimensions = array();
        $arEffectiveDimensions = array();
        foreach($arBasket as $arItem){
            if(!empty($arItem['WIDTH']) && !empty($arItem['HEIGHT']) && !empty($arItem['LENGTH'])){
                $sumVolume += $arItem['WIDTH'] * $arItem['HEIGHT'] * $arItem['LENGTH'] * $arItem['QUANTITY']/1000;
                $arDimensions['WIDTH'][] = $arItem['WIDTH']/10;
                $arDimensions['HEIGHT'][] = $arItem['HEIGHT']/10;
                $arDimensions['LENGTH'][] = $arItem['LENGTH']/10;
            }else{
                $sumVolume += $defaultDimensions['WIDTH'] * $defaultDimensions['HEIGHT'] * $defaultDimensions['LENGTH'] * $arItem['QUANTITY'];
                $arDimensions['WIDTH'][] = $defaultDimensions['WIDTH'];
                $arDimensions['HEIGHT'][] = $defaultDimensions['HEIGHT'];
                $arDimensions['LENGTH'][] = $defaultDimensions['LENGTH'];
            }
        }
        $effectiveDimension = round(ceil(pow($sumVolume,1/3)*10)/10,1);
        $maxDimensionKey = null;
        $maxDimensionValue = $effectiveDimension;
        foreach($arDimensions as $key => $arItems){
            $arItems = array_unique($arItems);
            $maxDimension = max($arItems);
            if($maxDimensionValue < $maxDimension){
                $maxDimensionKey = $key;
                $maxDimensionValue = $maxDimension;
            }
        }
        if($maxDimensionKey === null){
            $arEffectiveDimensions = ['WIDTH' => ceil($maxDimensionValue), 'HEIGHT' => ceil($maxDimensionValue),
                'LENGTH' => ceil($maxDimensionValue)];
        }else{
            $arEffectiveDimensions['LENGTH'] = $maxDimensionValue;
            $remainingEffectiveDimension = round(ceil(sqrt($sumVolume/$maxDimensionValue)),1);
            foreach($arDimensions as $key => $arItems){
                if($key == 'LENGTH'){
                    continue;
                }
                $arEffectiveDimensions[$key] = ceil($remainingEffectiveDimension);
            }
        }
        return $arEffectiveDimensions;
    }
    public function weightToKg($weight){
        return round($weight/1000,3);
    }
    public function getOrderWeight($arOrder){
        if($arOrder['CONFIG']['MAIN']['CALC_ALGORITM'] == 'Y'){
            $weight = 0;
            foreach($arOrder['ORDER']['ITEMS'] as $arItem){
                if($arItem['WEIGHT'] > 0){
                    $weight += $arItem['WEIGHT']*$arItem['QUANTITY'];
                }else{
                    $weight += $arOrder['CONFIG']['MAIN']['WEIGHT_VALUE']*$arItem['QUANTITY'];
                }
            }
        }else{
            $weight = $arOrder['CONFIG']['MAIN']['WEIGHT_VALUE'];
        }
        return ceil($weight/0.01) * 0.01;
    }
    public function getBasket(Basket $basket){
        $arBasketItems = $basket->getBasketItems();
        $arItems = array();
        $articleOption = Config::getDataValue('productArticle');
        $compleXMLIDoption = (bool)Config::getDataValue('xmlIdComplex');
        $articleProp = Config::getDataValue('articleProperty');
        if(!empty($articleProp) && $articleOption == 'PROP'){
            Loader::includeModule('iblock');
            Loader::includeModule('catalog');
            $arPropExploded = explode('|',$articleProp);
            $arFilter = array('ID' => $arPropExploded[0], 'VERSION' => $arPropExploded[1], 'MULTIPLE' => $arPropExploded[2]);
            $arProperty = \CIBlockProperty::GetList(array('ID' => 'ASC'),$arFilter)->Fetch();
            if(!empty($arProperty)){
                $iblockInfo = \CIBlock::GetByID($arProperty['IBLOCK_ID'])->Fetch();
                $arProperty['IBLOCK_ID'] = $iblockInfo['ID'];
                $arProperty['IBLOCK_CODE'] = $iblockInfo['IBLOCK_TYPE_ID'];
                $arProperty['IS_CATALOG'] = false;
                if(Loader::includeModule('catalog')){
                    if(\CCatalog::GetByID($iblockInfo['ID'])){
                        $arProperty['IS_CATALOG'] = true;
                    }
                }
            }
        }
        foreach ($arBasketItems as $oBasketItem) {
            $name = $oBasketItem->getField('NAME');
            $dimensions = $oBasketItem->getField('DIMENSIONS')?unserialize($oBasketItem->getField('DIMENSIONS')):false;
            $quantity = $oBasketItem->getQuantity();
            $weight = self::weightToKg($oBasketItem->getWeight());
            switch($articleOption){
                case 'PROP':
                    if(!empty($arProperty)){
                        $sPropCode = 'PROPERTY_'.$arProperty['CODE'];
                        $arFilter = array("!$sPropCode" => false, 'ACTIVE' => 'Y',
                            'ID' => $oBasketItem->getProductId());
                        if(empty($arOrder)){
                            $arOrder = array('ID' => 'ASC');
                        }
                        $arSelect = array('ID','NAME','IBLOCK_ID',$sPropCode);
                        $arWares = \CIBlockElement::GetList($arOrder,$arFilter,false,false,$arSelect)->Fetch();
                        if(!empty($arWares[$sPropCode.'_VALUE'])){
                            $article = $arWares[$sPropCode.'_VALUE'];
                            break;
                        }
                    }
                case 'ID':default:
                    $article = $oBasketItem->getProductId();
                    break;
                case 'XML_ID':
                    $article = $oBasketItem->getField('PRODUCT_XML_ID');
                    if($compleXMLIDoption && strpos($article,'#') !== false){
                        $arArticle = explode('#',$article);
                        $article = $arArticle[1];
                    }
                    break;
            }
            $arItems[] = array('NAME' => $name, 'QUANTITY' => $quantity, 'WEIGHT' => $weight, 'PRICE' => $oBasketItem->getPrice(),
                'WIDTH' => $dimensions['WIDTH'], 'HEIGHT' => $dimensions['HEIGHT'], 'LENGTH' => $dimensions['LENGTH'], 
                'ID' => $article);
        }
        return $arItems;
    }
    public function isPvz(){
        return in_array(
                $this->category,
                array('delivery-point','door-to-delivery-point','delivery-point-to-delivery-point'));
    }
    public function isDirect(){
        return in_array(
            $this->category,
            array('delivery-point-to-door','door-to-delivery-point','delivery-point-to-delivery-point',
                'door-to-door'));
    }
    public function isShiptorCourier(){
        return boolval($this->group == 'shiptor_courier');
    }
    public function isShiptorToday(){
        return boolval($this->group == 'shiptor_one_day');
    }
    public function getCategory(){
        return $this->category;
    }
    public function getGroup(){
        return $this->group;
    }
    public function getMethodId(){
        $arMethods = $this->getParentService()->getAvailableProfiles();
        return $arMethods[$this->getGroup()]['id'];
    }
    public function getLogo(){
        return $this->logotip;
    }
    public function isCalculatePriceImmediately() {
        return $this->getParentService()->isCalculatePriceImmediately();
    }
    public static function isProfile() {
        return self::$isProfile;
    }
    public function getParentService() {
        return $this->parent;
    }
    public function getLocation($locationCode){
        return \CShiptorDeliveryHelper::getLocationByCode($locationCode);
    }
    private function calculateMargin($price){
        return $this->getParentService()->calculateMargin($price);
    }
    protected function getConfigStructure() {
        if(empty($this->profileName)){
            $arAvailableProfiles = $this->getParentService()->getAvailableProfiles();
            $this->profileName = $arAvailableProfiles[$this->group]['name'];
        }
        $result = array(
            'MAIN' => array(
                'TITLE' => Loc::getMessage('SHIPTOR_CONFIG_MAIN'),
                'DESCRIPTION' => Loc::getMessage('SHIPTOR_CONFIG_MAIN'),
                'ITEMS' => array(
                    'FIXED_PRICE' => array(
                        'TYPE' => 'STRING',
                        'NAME' => Loc::getMessage('SHIPTOR_CONFIG_FIXED_PRICE'),
                        'SIZE' => 10,
                        'DEFAULT' => '',
                        'ONBLUR' => <<<JS
                            if(this.value < 0 || isNaN(this.value) || this.value.length == 0){
                                this.value = '';
                            }
JS
                    ),
                    'NAME' => array(
                        'TYPE' => 'STRING',
                        'NAME' => Loc::getMessage('SHIPTOR_PROFILE_CONFIG_NAME'),
                        'READONLY' => true,
                        'DEFAULT' => $this->profileName,
                        'VALUE' => $this->profileName
                    ),
                    'CATEGORY' => array(
                        'TYPE' => 'STRING',
                        'NAME' =>'category',
                        'HIDDEN' => true,
                        'DEFAULT' => $this->category
                    ),
                    'COURIER' => array(
                        'TYPE' => 'STRING',
                        'NAME' =>'courier',
                        'HIDDEN' => true,
                        'DEFAULT' => $this->courier
                    ),
                    'GROUP' => array(
                        'TYPE' => 'STRING',
                        'NAME' =>'group',
                        'READONLY' => true,
                        'DEFAULT' => $this->group
                    )
                )
            )
        );
        if($this->isDirect()){
            $result['DIRECT'] = array(
                'TITLE' => Loc::getMessage('SHIPTOR_DIRECT_HANDLER_CONFIG_DIRECT'),
                'DESCRIPTION' => Loc::getMessage('SHIPTOR_DIRECT_HANDLER_CONFIG_DIRECT_DESC'),
                'ITEMS' => array(
                    'LOCATION' => array(
                        'TYPE' => 'LOCATION',
                        'NAME' => Loc::getMessage('SHIPTOR_DIRECT_HANDLER_CONFIG_DIRECT_LOCATION'),
                        'DEFAULT' => Option::get('sale','location')
                    )
                )
            );
            $result['DIRECT']['ITEMS']['RECIEVER_TYPE'] = array(
                'TYPE' => 'ENUM',
                'NAME' => Loc::getMessage('SHIPTOR_DIRECT_HANDLER_CONFIG_DIRECT_RECIEVER_TYPE'),
                'DEFAULT' => 'SET',
                'OPTIONS' => array(
                    'MANUAL' => Loc::getMessage('SHIPTOR_DIRECT_HANDLER_CONFIG_DIRECT_TYPE_MANUAL'),
                    'SET' => Loc::getMessage('SHIPTOR_DIRECT_HANDLER_CONFIG_DIRECT_TYPE_SET')
                ),
                'ONCHANGE' => 'this.form.submit();'
            );
            switch($this->config['DIRECT']['RECIEVER_TYPE']){
                case 'MANUAL':
                    $arRecConfig = $this->getRecieverConfig();
                    foreach($arRecConfig as $key => $arItem){
                        $result['DIRECT']['ITEMS'][$key] = $arItem;
                    }
                    break;
                case 'SET':default:
                    $arRecConfig = $this->getRecieverConfig(true);
                    foreach($arRecConfig as $key => $arItem){
                        $result['DIRECT']['ITEMS'][$key] = $arItem;
                    }
                    break;
            }
            if(in_array($this->category,array('door-to-door','door-to-delivery-point'))){
                $result['DIRECT']['ITEMS']['ADDRESS_TYPE'] = array(
                    'TYPE' => 'ENUM',
                    'NAME' => Loc::getMessage('SHIPTOR_DIRECT_HANDLER_CONFIG_DIRECT_ADDRESS_TYPE'),
                    'DEFAULT' => 'SET',
                    'OPTIONS' => array(
                        'MANUAL' => Loc::getMessage('SHIPTOR_DIRECT_HANDLER_CONFIG_DIRECT_TYPE_MANUAL'),
                        'SET' => Loc::getMessage('SHIPTOR_DIRECT_HANDLER_CONFIG_DIRECT_TYPE_SET')
                    ),
                    'ONCHANGE' => 'this.form.submit();'
                );
                switch($this->config['DIRECT']['ADDRESS_TYPE']){
                    case 'MANUAL':
                        $arAddressConfig = $this->getAddressConfig();
                        foreach($arAddressConfig as $key => $arItem){
                            $result['DIRECT']['ITEMS'][$key] = $arItem;
                        }
                        break;
                    case 'SET':default:
                        $arAddressConfig = $this->getAddressConfig(true);
                        foreach($arAddressConfig as $key => $arItem){
                            $result['DIRECT']['ITEMS'][$key] = $arItem;
                        }
                        break;
                }
            }
            $result['DIRECT']['ITEMS']['COMMENT'] = [
                    'TYPE' => 'STRING',
                    'MULTILINE' => 'Y',
                    'NAME' => Loc::getMessage('SHIPTOR_DIRECT_HANDLER_CONFIG_DIRECT_COMMENT'),
                    'ROWS' => 4,
                    'COLS' => 20,
                    'DEFAULT' => Config::getDataValue('direct_comment')
            ];
            $result['DIRECT']['ITEMS']['DATE_TIME_TYPE'] = [
                    'TYPE' => 'ENUM',
                    'NAME' => Loc::getMessage('SHIPTOR_DIRECT_HANDLER_CONFIG_DIRECT_DATE_TIME_TYPE'),
                    'DEFAULT' => 'SET',
                    'OPTIONS' => array(
                        'MANUAL' => Loc::getMessage('SHIPTOR_DIRECT_HANDLER_CONFIG_DIRECT_TYPE_MANUAL'),
                        'SET' => Loc::getMessage('SHIPTOR_DIRECT_HANDLER_CONFIG_DIRECT_TYPE_SET')
                    ),
                    'ONCHANGE' => 'this.form.submit();'
            ];
            switch($this->config['DIRECT']['DATE_TIME_TYPE']){
                case 'MANUAL':
                    $arDateConfig = $this->getDateConfig();
                    foreach($arDateConfig as $key => $arItem){
                        $result['DIRECT']['ITEMS'][$key] = $arItem;
                    }
                    break;
                case 'SET':default:
                    $arDateConfig = $this->getDateConfig(true);
                    foreach($arDateConfig as $key => $arItem){
                        $result['DIRECT']['ITEMS'][$key] = $arItem;
                    }
                    break;
            }
            if(in_array($this->category,array('delivery-point-to-door','delivery-point-to-delivery-point'))){
                $result['DIRECT']['ITEMS']['PVZ_CODE'] = array(
                    'TYPE' => 'STRING',
                    'NAME' => Loc::getMessage('SHIPTOR_DIRECT_HANDLER_CONFIG_DIRECT_PVZ'),
                    'HIDDEN' => true,
                );
                $result['DIRECT']['ITEMS']['PVZ_ADDRESS'] = array(
                    'TYPE' => 'STRING',
                    'NAME' => Loc::getMessage('SHIPTOR_DIRECT_HANDLER_CONFIG_DIRECT_PVZ_ADDR'),
                    'READONLY' => true,
                    'SIZE' => '60'
                );
                $oAsset = Asset::getInstance();
                $oAsset->addJs(SHIPTOR_DELIVERY_YMAPS_URL, true);
                \CJSCore::Init(array('shiptor_pickup'));
                $locationCode = $this->config['DIRECT']['LOCATION']?:$result['DIRECT']['ITEMS']['LOCATION']['DEFAULT'];
                $arLocation = \CShiptorDeliveryHelper::getLocationByCode($locationCode);
                $kladr = $arLocation['KLADR'];
                $method = $this->getMethodId();
                $pvzCode = $this->config['DIRECT']['PVZ_CODE'];
                $jsPVZselect = <<<JS
                    <script type="text/javascript">
                        BX.ready(function(){
                            var ePvzField = document.querySelector('[name="CONFIG[DIRECT][PVZ_CODE]"]'),
                                ePvzAddrField = document.querySelector('[name="CONFIG[DIRECT][PVZ_ADDRESS]"]'),
                                eLocationField = document.querySelector('[name="CONFIG[DIRECT][LOCATION]"]');
                            if(!!ePvzField && !!eLocationField){
                                var params = {PVZ_FIELD:ePvzField, delivery:"$this->courier", kladr:"$kladr",
                                    deliveryId: "{$this->id}", method: "$method", LOCATION_FIELD: eLocationField,
                                    pvzCode: "$pvzCode", PVZ_ADDR_FIELD: ePvzAddrField
                                };
                                window.Shiptor.DirectPvz.init(params);
                            }
                        });
                    </script>
JS;
                $oAsset->addString($jsPVZselect);
            }
        }else{
            $result['MAIN']['ITEMS']['IS_FULFILMENT'] = array(
                'TYPE' => 'Y/N',
                'NAME' => Loc::getMessage("SHIPTOR_PROFILE_IS_FULFILMENT"),
                'DEFAULT' => 'Y',
                'TITLE' => Loc::getMessage('SHIPTOR_PROFILE_IS_FULFILMENT_HINT')
            );
        }
        return $result;
    }
    public function addAdminJS(){
        $cAsset = Asset::getInstance();
        $oRequest = Context::getCurrent()->getRequest();
        if(empty($oRequest['ID'])){
            return true;
        }
        $order = Order::load($oRequest['ID']);
        $propertyCollection = $order->getPropertyCollection();
        $locationCode = $propertyCollection->getDeliveryLocation()->getValue();
        $locationPropId = $propertyCollection->getDeliveryLocation()->getPropertyId();
        $arLocation = $this->getLocation($locationCode);
        if(!$arLocation){
            return true;
        }
        foreach($order->getShipmentCollection() as $shipment){
            if($shipment->isSystem()){
                continue;
            }
            $deliveryId = $shipment->getDeliveryId();
        }
        if($this->id != $deliveryId || !empty($_POST['init'.$oRequest['ID']])) return true;
        $_POST['init'.$oRequest['ID']] = $this->id;
        $personTypeId = $order->getPersonTypeId();
        $arConfig = $this->getParentService()->getConfigOuter();
        $propPvzId = Config::getPvzPropId($personTypeId);
        $oPropPvz = $propertyCollection->getItemByOrderPropertyId($propPvzId);
        if(!empty($oPropPvz)){
            $currentPvz = $oPropPvz->getValue();
        }
        $orderBasket = $order->getBasket();
        $arOrder = array(
            'ORDER' => array(
                'ITEMS' => $this->getBasket($orderBasket),
            ),
            'CONFIG' => $arConfig
        );
        $orderDimensions = $this->getOrderDimensions($arOrder);
        $weight = $this->getOrderWeight($arOrder);
        $paymentIds = $order->getPaymentSystemId();
        $cashPaymentId = Config::getCashPayments();
        $paymentType = array_intersect($cashPaymentId,$paymentIds)?'cash':'card';
        $bPayed = $order->isPaid();
        switch($arConfig['COD']['CALCULATION_TYPE']){
            case ShiptorHandler::COD_ALWAYS:default:
                $bCod = true;
                break;
            case ShiptorHandler::COD_NEVER:
                $bCod = false;
                break;
            case ShiptorHandler::COD_CERTAIN:
                if($bPayed){
                    $bCod = false;
                }else{
                    if($arConfig['COD']['SERVICE_COD'] == 'N'){
                        $bCod = false;
                    }else{
                        $codPaymentIds = $arConfig['COD']['SERVICES_LIST'];
                        $bCod = array_intersect($paymentIds,$codPaymentIds)?true:false;
                    }
                }
                break;
        }
        $cAsset->addJs(SHIPTOR_DELIVERY_YMAPS_URL, true);
        \CJSCore::Init(array('shiptor_pickup'));
        $arJSParams = array(
            'ID' => $this->id,
            'CONFIG' => $this->config,
            'METHOD' => $this->getMethodId(),
            'CITY' => $arLocation['CITY'],
            'CURRENT_PVZ' => $currentPvz,
            'PVZ_PROP_ID' => $propPvzId,
            'ADDR_PROP_ID' => (Config::isAddressSimple()?Config::getAddressPropId($personTypeId):''),
            'LOCATION_PROP_ID' => $locationPropId,
            'LOCATION_CODE' => $locationCode,
            'KLADR' => $arLocation['KLADR'],
            'PAYMENT_TYPE' => $paymentType,
            'BPAYED' => $bPayed,
            'BCOD' => $bCod,
            'LIMITS' => array('weight' => $weight, 'length' => $orderDimensions['LENGTH'],
                'width' => $orderDimensions['WIDTH'], 'heigth' => $orderDimensions['HEIGHT']
            )
        );
        $jsonJSParams = \CUtil::PhpToJSObject($arJSParams);
        $jsParams = <<<JS
        <script type="text/javascript">
            BX.ready(function(){
                window.Shiptor.AdminPvz.init({$jsonJSParams});
            });
        </script>
JS;
        $cAsset->addString($jsParams);
    }
    public function getTerms($text){
        return $this->getParentService()->getTerms($text);
    }
    public function addDescription($addDescription){
        $sDesc = $this->getDescription();
        if(strpos($sDesc,$addDescription) === false){
            $this->setDescription($sDesc.$addDescription);
        }
    }
    public function getDescription(){
        return $this->description;
    }
    public function setDescription($description){
        if (!$description){
            return false;
        }
        $this->description = $description;
        return true;
    }
    public function addName($text){
        $this->name .= $text;
    }
    private function getRecieverConfig($hidden = false){
        $arConfig = array(
            'RECIEVER' => array(
                'TYPE' => 'STRING',
                'NAME' => Loc::getMessage('SHIPTOR_DIRECT_HANDLER_CONFIG_DIRECT_RECIEVER'),
                'SIZE' => 50,
            ),
            'PHONE' => array(
                'TYPE' => 'STRING',
                'NAME' => Loc::getMessage('SHIPTOR_DIRECT_HANDLER_CONFIG_DIRECT_PHONE'),
                'SIZE' => 15,
            ),
            'EMAIL' => array(
                'TYPE' => 'STRING',
                'NAME' => Loc::getMessage('SHIPTOR_DIRECT_HANDLER_CONFIG_DIRECT_EMAIL'),
                'SIZE' => 20,
            )
        );
        if($hidden){
            $arConfig['RECIEVER']['READONLY'] = true;
            $arConfig['RECIEVER']['VALUE'] = Config::getDataValue('direct_reciever');
            $arConfig['PHONE']['READONLY'] = true;
            $arConfig['PHONE']['VALUE'] = Config::getDataValue('direct_phone');
            $arConfig['EMAIL']['READONLY'] = true;
            $arConfig['EMAIL']['VALUE'] = Config::getDataValue('direct_email');
        }
        return $arConfig;
    }
    private function getAddressConfig($hidden = false){
        $arConfig = array(
            'ZIP' => array(
                'TYPE' => 'STRING',
                'NAME' => Loc::getMessage('SHIPTOR_DIRECT_HANDLER_CONFIG_DIRECT_ZIP'),
                'SIZE' => 10
            ),
            'STREET' => array(
                'TYPE' => 'STRING',
                'NAME' => Loc::getMessage('SHIPTOR_DIRECT_HANDLER_CONFIG_DIRECT_STREET'),
                'SIZE' => 50
            ),
            'HOUSE' => array(
                'TYPE' => 'STRING',
                'NAME' => Loc::getMessage('SHIPTOR_DIRECT_HANDLER_CONFIG_DIRECT_HOUSE'),
                'SIZE' => 8
            ),
            'FLAT' => array(
                'TYPE' => 'STRING',
                'NAME' => Loc::getMessage('SHIPTOR_DIRECT_HANDLER_CONFIG_DIRECT_FLAT'),
                'SIZE' => 8
            )
        );
        if($hidden){
            $arConfig['ZIP']['READONLY'] = true;
            $arConfig['ZIP']['VALUE'] = Config::getDataValue('direct_zip');
            $arConfig['STREET']['READONLY'] = true;
            $arConfig['STREET']['VALUE'] = Config::getDataValue('direct_street');
            $arConfig['HOUSE']['READONLY'] = true;
            $arConfig['HOUSE']['VALUE'] = Config::getDataValue('direct_house');
            $arConfig['FLAT']['READONLY'] = true;
            $arConfig['FLAT']['VALUE'] = Config::getDataValue('direct_flat');
        }
        return $arConfig;
    }
    private function getDateConfig($hidden = false){
        $arConfig = array(
            'DATE_TYPE' => array(
                'TYPE' => 'ENUM',
                'NAME' => Loc::getMessage('SHIPTOR_DIRECT_HANDLER_CONFIG_DIRECT_DATE_TYPE'),
                'OPTIONS' => array(
                    Config::DATE_NEAR => Loc::getMessage('SHIPTOR_DIRECT_HANDLER_CONFIG_DIRECT_DATE_NEAR'),
                    Config::DATE_DELAY => Loc::getMessage('SHIPTOR_DIRECT_HANDLER_CONFIG_DIRECT_DATE_DELAY'),
                ),
                'ONCHANGE' => 'this.form.submit();'
            )
        );
        if($this->config['DIRECT']['DATE_TYPE'] == 'delay'){
            $arConfig['DELAY'] = array(
                'TYPE' => 'STRING',
                'NAME' => Loc::getMessage('SHIPTOR_DIRECT_HANDLER_CONFIG_DIRECT_DATE_DELAY_NAME'),
            );
        }
        if($hidden){
            $arConfig['DATE_TYPE']['DISABLED'] = true;
            $arConfig['DATE_TYPE']['VALUE'] = Config::getDataValue('direct_date_type');
            if(!empty($arConfig['DELAY']) && $this->config['DIRECT']['DATE_TYPE'] == 'delay'){
                $arConfig['DELAY']['READONLY'] = true;
                $arConfig['DELAY']['VALUE'] = Config::getDataValue('direct_date_delay');
            }
        }
        return $arConfig;
    }
    public static function whetherAdminExtraServicesShow(){
        return self::$whetherAdminExtraServicesShow;
    }
    public function getEmbeddedExtraServicesList(){
        $extraServices = [
            'DATE_DELIVERY' => array(
                'NAME' => Loc::getMessage('SHIPTOR_DATE_DELIVERY_NAME'),
                'SORT' => 100,
                'RIGHTS' => 'NYY',
                'ACTIVE' => 'Y',
                'CLASS_NAME' => '\Shiptor\Delivery\Services\DateDelivery',
                'DESCRIPTION' => Loc::getMessage('SHIPTOR_DATE_DELIVERY_DESCRIPTION'),
                'INIT_VALUE' => date('d.m.Y'),
                'PARAMS' => array('PRICE' => 0)
            ),
            'TIME_DELIVERY' => array(
                'NAME' => Loc::getMessage('SHIPTOR_TIME_DELIVERY_NAME'),
                'SORT' => 100,
                'RIGHTS' => 'NYY',
                'ACTIVE' => 'Y',
                'CLASS_NAME' => '\Shiptor\Delivery\Services\TimeDelivery',
                'DESCRIPTION' => Loc::getMessage('SHIPTOR_TIME_DELIVERY_DESCRIPTION'),
                'INIT_VALUE' => array(0),
                'PARAMS' => array('PRICE' => 0)
            )
        ];
        return $extraServices;
    }
    public static function onAfterAdd($serviceId, $fields){
        if(substr($fields["CLASS_NAME"],0,1) != "\\"){
            $fields["CLASS_NAME"] = "\\".$fields["CLASS_NAME"];
            DST::update($serviceId,array('CLASS_NAME' => $fields['CLASS_NAME']));
        }
        return true;
    }
    public static function onAfterUpdate($serviceId, array $fields = array()){
        if(substr($fields["CLASS_NAME"],0,1) != "\\"){
            $fields["CLASS_NAME"] = "\\".$fields["CLASS_NAME"];
            DST::update($serviceId,array('CLASS_NAME' => $fields['CLASS_NAME']));
        }
        return true;
    }
}