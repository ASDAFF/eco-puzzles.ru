<?
use Bitrix\Main\Data\Cache,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\Type,
    Bitrix\Main\Entity,
    Bitrix\Main\IO\File,
    Bitrix\Main\Application,
    Bitrix\Main\FileTable,
    Bitrix\Sale\Location\LocationTable,
    Bitrix\Sale\Internals\ShipmentTable,
    Bitrix\Sale\Internals\PersonTypeTable,
    Bitrix\Sale\Internals\OrderPropsTable,
    Bitrix\Sale\Delivery\Services\Table as DST,
    Shiptor\Delivery\Options\Config,
    Shiptor\Delivery\ShiptorService,
    Shiptor\Delivery\ShiptorHandler,
    Shiptor\Delivery\CShiptorAPI,
    Shiptor\Delivery\Logger;

Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/shiptor.delivery/lib/include.php');
Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/shiptor.delivery/admin/unload.php');

class CShiptorDeliveryHelper{
    const MODULE_ID = "shiptor.delivery";
    const HOUR = 3600;
    const DAY = 86400;
    const WEEK = 604800;
    const MONTH = 2419200;
    const YEAR = 31557600;
    const CACHE_DIR = "/shiptor/delivery";
    const LOGO_DESCRIPTION_BB = "shiptor.delivery.bb logo";
    const LOGO_DESCRIPTION_CDEK = "shiptor.delivery.cdek logo";
    const LOGO_DESCRIPTION_DPD = "shiptor.delivery.dpd logo";
    const LOGO_DESCRIPTION_IML = "shiptor.delivery.iml logo";
    const LOGO_DESCRIPTION_PP = "shiptor.delivery.pp logo";
    const LOGO_DESCRIPTION_RP = "shiptor.delivery.rp logo";
    const LOGO_DESCRIPTION = "shiptor.delivery logo";
    const LOGO_BB = "/bitrix/images/shiptor.delivery/boxberry.png";
    const LOGO_CDEK = "/bitrix/images/shiptor.delivery/cdek.png";
    const LOGO_DPD = "/bitrix/images/shiptor.delivery/dpd.png";
    const LOGO_IML = "/bitrix/images/shiptor.delivery/iml.png";
    const LOGO_PP = "/bitrix/images/shiptor.delivery/pickpoint.png";
    const LOGO_RP = "/bitrix/images/shiptor.delivery/russian-post.png";
    const LOGO_PATH = "/bitrix/images/shiptor.delivery/shiptor_logo.png";
    public static function getLogoId($path = null,$desc = null){
        if(empty($path) && empty($desc)){
            $path = self::LOGO_PATH;
            $desc = self::LOGO_DESCRIPTION;
        }
        $sDocumentRoot = Application::getDocumentRoot();
        $logo = new File($sDocumentRoot . $path);
        if($logo->isExists()){
            $fileId = FileTable::getList(array("filter" => array("DESCRIPTION" => $desc), "select" => array("ID")))->fetch();
            if(empty($fileId)){
                $logoArray = \CFile::MakeFileArray($sDocumentRoot . $path);
                $logoArray["description"] = $desc;
                $logoArray["MODULE_ID"] = self::MODULE_ID;
                $fileId = intval(\CFile::SaveFile($logoArray,self::MODULE_ID));
            }else{
                $fileId = $fileId["ID"];
            }
        }
        return $fileId;
    }
    public static function getDefaultLogo($courier){
        switch($courier){
            case "boxberry":
                return self::getLogoId(self::LOGO_BB,self::LOGO_DESCRIPTION_BB);
            case "cdek":
                return self::getLogoId(self::LOGO_CDEK,self::LOGO_DESCRIPTION_CDEK);
            case "dpd":
                return self::getLogoId(self::LOGO_DPD,self::LOGO_DESCRIPTION_DPD);
            case "iml":
                return self::getLogoId(self::LOGO_IML,self::LOGO_DESCRIPTION_IML);
            case "pickpoint":
                return self::getLogoId(self::LOGO_PP,self::LOGO_DESCRIPTION_PP);
            case "russian-post":
                return self::getLogoId(self::LOGO_RP,self::LOGO_DESCRIPTION_RP);
            default:
                return self::getLogoId();
        }
    }
    public static function getDeliveries($filter = array()){
        $arFilter = array(
            'PARENT.CLASS_NAME' => "%ShiptorHandler",
            'ACTIVE' => "Y",
            '>PARENT_ID' => 0
        );
        if(!empty($filter)){
            $arFilter = array_merge($arFilter,$filter);
        }
        $arDeliveries = DST::GetList(array(
                'filter' => $arFilter,
                'select' => array(
                    "ID"
                )
            ))->fetchAll();
        foreach($arDeliveries as $key => $item){
            $arDeliveries[$key] = $item["ID"];
        }
        return $arDeliveries;
    }
    public static function getCommonDeliveries(){
        return self::getDeliveries(array("!CONFIG" => "%DIRECT%"));
    }
    public static function getDirectDeliveries(){
        return self::getDeliveries(array("CONFIG" => "%DIRECT%"));
    }
    public static function getLocationByCode($locationCode){
        if(empty($locationCode)) return false;
        $arLocation = array("CODE" => $locationCode);
        $dbLocation = LocationTable::getList(array(
                'filter' => array(
                    '=CODE' => $locationCode,
                    '=PARENTS.NAME.LANGUAGE_ID' => LANGUAGE_ID,
                    '=PARENTS.TYPE.NAME.LANGUAGE_ID' => LANGUAGE_ID,
                    '=PARENTS.TYPE.CODE' => array("COUNTRY","REGION","CITY","VILLAGE"),
                ),
                'select' => array(
                    'I_NAME' => 'PARENTS.NAME.NAME',
                    'I_TYPE_CODE' => 'PARENTS.TYPE.CODE',
                ),
                'order' => array(
                    'PARENTS.DEPTH_LEVEL' => 'asc'
                )
        ));
        while($arLoc = $dbLocation->fetch()){
            if($arLoc["I_TYPE_CODE"] == "CITY" && !empty($arLocation["CITY"])){
                $arLocation["REGION"] = $arLocation["CITY"];
            }
            $arLocation[$arLoc["I_TYPE_CODE"]] = $arLoc["I_NAME"];
            if(!empty($arLoc["KLADR"])){
                $arLocation["KLADR"] = $arLoc["KLADR"];
            }
        }
        if(empty($arLocation["CITY"])){
            $arLocation["CITY"] = $arLocation["VILLAGE"];
            $arLocation["NOT_CITY"] = true;
        }
        $arLocation["KLADR"] = self::getKladr($arLocation);
        return $arLocation;
    }
    public static function getPersonTypes(){
        $arSelectPt = array("ID","NAME_WS");
        $arOrderPt = array("SORT" => "ASC");
        $arFilter = array("ACTIVE" => "Y");
        $arRuntime = array(new Entity\ExpressionField('NAME_WS', "CONCAT('(',%s,') ',%s)",array("LID","NAME")));
        $dbPersonTypes = PersonTypeTable::getList(array("select" => $arSelectPt, "order" => $arOrderPt,
                "filter" => $arFilter, "runtime" => $arRuntime));
        $arPersonTypes = array();
        while($arPersonType = $dbPersonTypes->fetch()){
            $arPersonTypes[$arPersonType["ID"]] = array("NAME" => $arPersonType["NAME_WS"]);
        }
        return $arPersonTypes;
    }
    public static function getOrderProps(){
        $arPersonTypes = self::getPersonTypes();
        $arSelect = array("ID","PERSON_TYPE_ID","NAME");
        $arOrder = array("ID" => "ASC");
        $arFilter["PERSON_TYPE_ID"] = array_keys($arPersonTypes);
        $dbOrderProps = OrderPropsTable::getList(array("select" => $arSelect, "filter" => $arFilter,
                "order" => $arOrder));
        while($arProp = $dbOrderProps->fetch()){
            $arProp["NAME"] = "[{$arProp["ID"]}] ".$arProp["NAME"];
            $arPersonTypes[$arProp["PERSON_TYPE_ID"]]["PROPS"][$arProp["ID"]] = $arProp["NAME"];
        }
        return $arPersonTypes;
    }
    public static function getPaytype($pid){
        $cashPaymentId = Config::getCashPayments();
        return (in_array($pid,$cashPaymentId)?"cash":"card");
    }
    public static function getPvz($arParams = false){
        if(empty($arParams)){
            return false;
        }
        $requestHash = md5(serialize($arParams));
        $cache = Cache::createInstance();
        if($cache->initCache(self::DAY, $requestHash, "/pvz")){
            $arPVZ = $cache->getVars();
        }elseif($cache->startDataCache()){
            $shiptorApi = CShiptorAPI::getInstance();
            $arRes = $shiptorApi->Request("getDeliveryPoints",$arParams);
            $arPVZ = $arRes['result'];
            if(!empty($arPVZ)){
                $cache->endDataCache($arPVZ);
            }
            $cache->abortDataCache();
        }
        return $arPVZ;
    }
    public static function isPVZProfile($oDelivery){
        if(!is_callable(array($oDelivery,"isPvz"))){
            return false;
        }
        return $oDelivery->isPvz();
    }
    public static function isShiptorCourier($oDelivery){
        if(!is_callable(array($oDelivery,"isShiptorCourier"))){
            return false;
        }
        return $oDelivery->isShiptorCourier();
    }
    public static function shDump($var,$hid = false){
        if($hid){
            echo '<pre style="display:none;">';
        }else{
            echo '<pre>';
        }
        var_dump($var);
        echo '</pre>';
    }
    public static function getKladr($arLocation){
        $oService = ShiptorService::getInstance();
        $kladr = $oService->getCode($arLocation["CODE"]);
        if(!empty($kladr)){
            return $kladr;
        }else{
            $arLocCity = self::getKladrFromApi($arLocation);
            if(empty($arLocCity) || empty($arLocCity["kladr_id"])){
                return false;
            }
            $kladr = $arLocCity["kladr_id"];
            $resultAdd = $oService->addByCode($arLocation["CODE"],$kladr);
            if( ! $resultAdd){
                return false;
            }
        }
        return $kladr;
    }
    public static function getKladrFromApi($arLocation){
        $sReplacesString = Loc::getMessage("SHIPTOR_REPLACE_STRING");
        $arReplacesString = explode("|",$sReplacesString);
        $bCityVillage = !empty($arLocation['VILLAGE']) && empty($arLocation["NOT_CITY"]);
        $city = $arLocation['CITY'];
        if($bCityVillage){
            $city = $arLocation['VILLAGE'];
        }
        $cityToSearch = trim(str_replace($arReplacesString,"",$city));
        $arLocation["CITY_RIGHT"] = $cityToSearch;

        if(in_array($cityToSearch,array(
            Loc::getMessage('SHIPTOR_MOSCOW'), Loc::getMessage('SHIPTOR_SPB'),
            Loc::getMessage('SHIPTOR_SPB2'), Loc::getMessage('SHIPTOR_SEVAS')
            )) || strpos($arLocation['REGION'],Loc::getMessage('SHIPTOR_MOS_OBL')) !== false){
            $result = self::runKladrQuery($arLocation,false);
        }else{
            $result = self::runKladrQuery($arLocation,true);
            if(empty($result)){
                $result = self::runKladrQuery($arLocation,false);
            }
        }
        return $result;
    }
    public static function runKladrQuery($arLocation, $withRegion = true){
        $apiShiptor = CShiptorAPI::getInstance();
        if($withRegion){
            $query = $arLocation['CITY_RIGHT']." ".$arLocation["REGION"];
        }else{
            $query = $arLocation['CITY_RIGHT'];
        }
        $arParams = array("query" => $query);
        $arResult = $apiShiptor->Request("suggestSettlement",$arParams);
        $bFound = false;
        //-----------------------------------------------------------------------
        $arCity = array();
        if(isset($arResult["result"]) && count($arResult["result"]) > 0){
            if(count($arResult["result"]) > 1){
                foreach($arResult["result"] as $arResValue){
                    if(self::checkNasPoint($arLocation,$arResValue)){
                        $arCity[] = $arResValue;
                    }
                }
            }else{
                $bFound = true;
                $arCity = $arResult["result"];
            }
            if(count($arCity) > 1){
                $arCityNew = array();
                foreach($arCity as $city){
                    if($bCityVillage && strpos(strtolower($city["readable_parents"]),strtolower($arLocation["CITY"])) !== false){
                        $arCityNew[] = $city;
                    }elseif($city["readable_parents"] == $arLocation["REGION"] || ($city["administrative_area"] == $arLocation["REGION"]) 
                        || strpos(strtolower($city["readable_parents"]),strtolower($arLocation["REGION"])) !== false 
                        || ($bCityVillage && strpos(strtolower($city["readable_parents"]),strtolower($arLocation["CITY"])) !== false)){
                        $arCityNew[] = $city;
                    }elseif(strpos(strtolower($city["short_readable"]),strtolower($arLocation["CITY"])) !== false
                            && (strtolower($city["name"]) == strtolower($arLocation["CITY"]))){
                        $arCityNew[] = $city;
                    }
                }
                $bFound = true;
                $arCity = $arCityNew;
            }else{
                $bFound = true;
            }
        }
        if($bFound){
            return $arCity[0];
        }
        return false;
    }
    public static function checkNasPoint($arLocation,$arCity){
        $bCityMatch = (bool)(strtolower($arLocation["CITY_RIGHT"]) == strtolower($arCity["name"]));
        if($arLocation["NOT_CITY"]){
            $arVillages = explode("|",Loc::getMessage("SHIPTOR_TYPE_CITY_VILLAGE"));
            $bTypeCityMatch = in_array($arCity["type"],$arVillages);
        }else{
            if(!empty($arLocation["VILLAGE"])){
                $arVillages = explode("|",Loc::getMessage("SHIPTOR_TYPE_CITY_VILLAGE"));
                $bTypeCityMatch = in_array($arCity["type"],$arVillages);
            }else{
                $arCities = explode("|",Loc::getMessage("SHIPTOR_TYPE_CITY_TEXT"));
                $bTypeCityMatch = in_array($arCity["type"],$arCities);
            }
        }
        return ($bTypeCityMatch && $bCityMatch);
    }
    public static function getDeliveryPrice($arData){
        $arConfig = $arData["CONFIG"];
        if($arData["ORDER"]["COD"]){
            $arConfig['MAIN']['COD_VALUE'] = $arData["PRICE"];
            $declaredCost = $arData["PRICE"];
        }else{
            $arConfig['MAIN']['COD_VALUE'] = 0;
            if($arConfig["COD"]["COST_DECLARING"] == "Y"){
                $declaredCost = $arData["PRICE"];
            }else{
                $declaredCost = 10;
            }
        }
        $arParams = array(
            "weight" => $arData["ORDER"]["WEIGHT"],
            "declared_cost" => $declaredCost,
            "kladr_id" => $arData["ADDRESS"]["KLADR"],
            "length" => $arData['ORDER']['DIMENSIONS']["LENGTH"],
            "width" => $arData['ORDER']['DIMENSIONS']["WIDTH"],
            "height" => $arData['ORDER']['DIMENSIONS']["HEIGHT"],
            "cod" => $arConfig['MAIN']['COD_VALUE'],
            "country_code" => $arData["ADDRESS"]["COUNTRY_CODE"]
        );
        if(!empty($arData["SENDER"]["KLADR"])){
            $arParams["kladr_id_from"] = $arData["SENDER"]["KLADR"];
        }
        $cacheId = serialize($arParams);
        $oCache = Cache::createInstance();
        if($oCache->initCache(self::WEEK, $cacheId, "/shiptr.delivprice")){
            $arMethods = $oCache->getVars();
        }elseif($oCache->startDataCache()){
            $apiShiptor = CShiptorAPI::getInstance();
            $arResult = $apiShiptor->Request("calculateShipping",$arParams);
            if(empty($arResult)){
                $oCache->abortDataCache();
                throw new \Exception($arData['NAME'].' '.Loc::getMessage("SHIPTOR_CALC_ERROR_FAIL"));
            }
            if(!empty($arResult['error']['message'])){
                $oCache->abortDataCache();
                throw new \Exception($arResult['error']['message']);
            }
            $arMethods = $arResult['result']['methods'];
            $oCache->endDataCache($arMethods);
        }
        if(empty($arMethods)){
            throw new \Exception($arData['NAME'].' '.Loc::getMessage("SHIPTOR_CALC_ERROR_FAIL"));
        }
        foreach($arMethods as $arMethod){
            if($arData["METHOD_ID"] == $arMethod["method"]["id"]){
                $total = $arMethod["cost"]["total"]["sum"];
                if($arConfig['COD']['INCLUDE_COD'] == "N"){
                    $cod = 0;
                    foreach($arMethod['cost']['services'] as $service){
                        if($service['service'] == "cod"){
                            $cod = $service["sum"];
                            break;
                        }
                    }
                    $total -= $cod;
                }
                $result = array("PRICE" => $total, "DAYS" => $arMethod["days"]);
            }
        }
        if(empty($result)){
           throw new \Exception($arData['NAME'].' '.Loc::getMessage("SHIPTOR_CALC_ERROR_FAIL"));
        }
        return array("RESULT" => $result);
    }
    public static function getShippingMethods(){
        $cacheId = "methodCacheId";
        $oCache = Cache::createInstance();
        if($oCache->initCache(self::WEEK,$cacheId,"/methods")){
            $arResult = $oCache->getVars();
        }elseif($oCache->startDataCache()){
            $apiShiptor = CShiptorAPI::getInstance();
            $arResult = $apiShiptor->Request("getShippingMethods",array());
            if(!empty($arResult['error']['message'])){
                //$oCache->abortDataCache();
                return array("ERRORS" => $arResult['error']['message']);
            }
            $oCache->endDataCache($arResult);
        }
        return $arResult['result'];
    }
    public static function internationalPhoneNumber($sPhone){
        $sCodeRU = '+7';
        $sPhone = preg_replace(array('/\D^\+/'),'',$sPhone);
        if(substr($sPhone,0,3) == '375'){
            $sPhone = '+'.$sPhone;
        }
        if(strlen($sPhone) == 10){
            $sArea = substr($sPhone,0,3);
            $sPrefix = substr($sPhone,3,3);
            $sNum1 = substr($sPhone,6,2);
            $sNum2 = substr($sPhone,8,2);
        }elseif(strlen($sPhone) == 11){
            $sArea = substr($sPhone,1,3);
            $sPrefix = substr($sPhone,4,3);
            $sNum1 = substr($sPhone,7,2);
            $sNum2 = substr($sPhone,9,2);
        }elseif(strlen($sPhone) == 7){
            $sPrefix = substr($sPhone,0,3);
            $sNum1 = substr($sPhone,4,2);
            $sNum2 = substr($sPhone,6,2);
            $sPhone = $sPrefix . "-" . $sNum1 . "-" . $sNum2;
            return $sPhone;
        }else{
            return $sPhone;
        }
        $sPhone = $sCodeRU . $sArea . $sPrefix . $sNum1 . $sNum2;
        return($sPhone);
    }
    public static function getPickupTime($courier){
        $cacheId = "pickupTimeCourier$courier";
        $oCache = Cache::createInstance();
        if($oCache->initCache(self::WEEK,$cacheId,"/pickuptimes")){
            $arResult = $oCache->getVars();
        }elseif($oCache->startDataCache()){
            $apiShiptor = CShiptorAPI::getInstance();
            $arResult = $apiShiptor->Request("getCourierPickUpTime",array($courier));
            if(!empty($arResult['error']['message'])){
                //$oCache->abortDataCache();
                return array("ERRORS" => $arResult['error']['message']);
            }
            $oCache->endDataCache($arResult);
        }
        return $arResult['result'];
    }
    public static function checkOrders(){
        global $USER;
        if (!is_object($USER)){
            $GLOBALS['USER'] = new CUser();
        }
        $arShiptorIds = self::getDeliveries();
        $oContext = \Bitrix\Main\Context::getCurrent();
        $lang = $oContext->getLanguage();
        $sStatuses = Config::getDataValue("tracking_map_statuses");
        $arStatuses = unserialize($sStatuses);
        $arStatuses = array_flip($arStatuses);
        $arFilter['=STATUS.Bitrix\Sale\Internals\StatusLangTable:STATUS.LID'] = $lang;
        $arFilter['DELIVERY_ID'] = $arShiptorIds;
        $arFilter['!=SYSTEM'] = 'Y';
        $arFilter["CANCELED"] = 'N';
        $params = array(
            'select' => array("ID","ORDER_ID","DELIVERY_DOC_NUM","TRACKING_NUMBER","TRACKING_DESCRIPTION","STATUS_ID"),
            'filter' => $arFilter,
            'order' => array("ID" => "DESC"),
        );
        $arShipments = ShipmentTable::getList($params)->fetchAll();
        $arData2Log = array();
        $arPackages = self::getPackages();
        foreach($arShipments as $arItem){
            $trackId = $arItem['DELIVERY_DOC_NUM'];
            $orderId = $arItem['ORDER_ID'];
            $id = $arItem['ID'];
            $currentOrder = \Bitrix\Sale\Order::load($orderId);
            $shipmentCollection = $currentOrder->getShipmentCollection();
            $shipment = $shipmentCollection->getItemById($id);
            if(!$shipment || in_array($arItem["TRACKING_DESCRIPTION"],["removed","recieved","delivered"])) continue;
            if(empty($arItem["TRACKING_DESCRIPTION"])){
                $errorText = "";
                $oDelivery = $shipment->getDelivery();
                if($oDelivery->isDirect()){
                    continue;
                }
                $arParentConfig = $oDelivery->getParentService()->getConfigOuter();
                $isAutomatic = Config::isAutomaticUpload();
                $isExists = !!$orderId;
                if($shipment->isShipped()){
                    $errorText = Loc::getMessage("SHIPTOR_API_ERROR_ALREADY_SHIPPED");
                }
                if(!$shipment->isAllowDelivery()){
                    $errorText = Loc::getMessage("SHIPTOR_API_ERROR_SHIP_NOT_ALLOWED");
                }
                if($isAutomatic && $isExists && strlen($errorText) == 0){
                    try{
                        $result = CShiptorDeliveryHelper::sendOrder($shipment);
                    } catch (\Exception $e) {
                        $errorText = $e->getMessage();
                        Logger::exception($e);
                    }
                }
                if(strlen($errorText) > 0){
                    $shipment->setField("MARKED","Y");
                    $shipment->setField("REASON_MARKED",$errorText);
                    $res = $shipment->save();
                    $arData2Log[] = "#".$orderId.": ".$errorText;
                }
            }else{
                if(!empty($arPackages[$trackId]['id']) && $arPackages[$trackId]["status"] != $arItem["TRACKING_DESCRIPTION"]){
                    $arData2Log[] = Loc::getMessage("SHIPTOR_ORDER_CHANGED_STATUS", array("#ID#" => $orderId, "#STATUS#" => $arPackages[$trackId]["status"]));
                    $shipment->setField('TRACKING_DESCRIPTION',$arPackages[$trackId]['status']);
                    $shipment->setField('DELIVERY_DOC_DATE',Type\DateTime::createFromTimestamp(strtotime($arPackages[$trackId]["last_hitory"])));
                    if(!empty($arStatuses[$arPackages[$trackId]['status']])){
                        $shipment->setField('STATUS_ID',$arStatuses[$arPackages[$trackId]['status']]);
                    }
                    $res = $shipment->save();
                }
            }
        }
        if(count($arData2Log) > 0){
            $sDataLog = implode("\r\n",$arData2Log);
            Logger::force($sDataLog);
        }
        return __CLASS__."::".__FUNCTION__."();";
    }
    public static function clearKladr(){
        ShiptorService::getInstance()->flush();
        return __CLASS__."::".__FUNCTION__."();";
    }
    public static function getOrderData($shipment){
        $oShiptorAPI = CShiptorAPI::getInstance();
        $oDelivery = $shipment->getDelivery();
        $deliveryId = $oDelivery->getId();
        $shipmentId = $shipment->getId();
        $arOrder = $oDelivery->getOrderData($shipment);
        $arOrder["DELIVERY"] = DST::getList(array(
            'filter' => array('ID' => $deliveryId),
            'select' => array('ID','NAME','CONFIG')
        ))->fetch();
        if(empty($arOrder["DELIVERY"])){
            throw new \Exception(Loc::getMessage("SHIPTOR_FATAL_ERROR_SETTINGS"));
        }
        $currentOrder = $shipment->getCollection()->getOrder();
        $arOrder["ACCOUNT_NUMBER"] = $currentOrder->getField('ACCOUNT_NUMBER');
        $arOrder["DEPARTURE"]["TYPE"] = Config::getDataValue('departure_type');
        $arOrder["DEPARTURE"]["STATUS"] = Config::getTriggerStatus();
        $arOrder["DEPARTURE"]["COD_VALUE"] = 0;
        $arOrder["DEPARTURE"]["COST_DECLARING"] = $arOrder["PRICE"];
        if(!$arOrder["ORDER"]["PAYED"]){
            switch($arOrder["CONFIG"]["COD"]["CALCULATION_TYPE"]){
                case ShiptorHandler::COD_ALWAYS:
                    $arOrder["DEPARTURE"]["COD_VALUE"] = $arOrder["ORDER"]["PRICE"] - $currentOrder->getSumPaid();
                    break;
                case ShiptorHandler::COD_NEVER:
                    return ["ERROR" => Loc::getMessage("SHIPTOR_ERROR_CANT_UPLOAD_NO_NALOZH_PLATEZH")];
                case ShiptorHandler::COD_CERTAIN:
                    if($arOrder["ORDER"]["COD"]){
                        $arOrder["DEPARTURE"]["COD_VALUE"] = $arOrder["ORDER"]["PRICE"] - $currentOrder->getSumPaid();
                    }else{
                        throw new \Exception(Loc::getMessage("SHIPTOR_ERROR_CANT_UPLOAD_NALOZH_PLATEZH_PS"));
                    }
                    break;
            }
            if($arOrder["DEPARTURE"]["COD_VALUE"] > 0){
                $arOrder["DEPARTURE"]["COST_DECLARING"] = $arOrder["DEPARTURE"]["COD_VALUE"];
            }
        }
        if($arOrder["DEPARTURE"]["COD_VALUE"] == 0){
            $arOrder["DEPARTURE"]["COST_DECLARING"] = 10;
            if($arOrder["CONFIG"]["COD"]["COST_DECLARING"] == "Y"){
                $arOrder["DEPARTURE"]["COST_DECLARING"] = $arOrder["PRICE"];
            }
        }
        if(self::isPVZProfile($oDelivery) && empty($arOrder["PVZ_CODE"])){
           throw new \Exception($arOrder["NAME"].' '.Loc::getMessage("SHIPTOR_API_ERROR_NO_PVZ"));
        }
        $arOrder["FIO"] = explode(" ",$arOrder["FIO"]);
        if($arOrder["FIO"][3]){
            $arOrder["FIO"][2] .= ' ' . $arOrder["FIO"][3];
        }
        if($arOrder["FIO"][4]){
            $arOrder["FIO"][2] .= ' ' . $arOrder["FIO"][4];
        }
        $arOrder["PHONE"] = self::internationalPhoneNumber($arOrder["PHONE"]);
        $arOrder["SERVICE"] = array("name" => Loc::getMessage("SHIPTOR_ADD_SERVICE_NAME"), "shopArticle" => "SD".$deliveryId.$shipmentId,
            "price" => $arOrder["DEPARTURE"]["COD_VALUE"] > 0? round($currentOrder->getDeliveryPrice(),2):0);
        $arRequest = $oShiptorAPI->Request("addService",$arOrder["SERVICE"]);
        $arOrder["PRODUCTS"] = array();
        foreach ($arOrder["ORDER"]["ITEMS"] as $arBasketItem) {
            $arItem = array("shopArticle" => $arBasketItem["ID"],"name" => $arBasketItem["NAME"], 
                "price" => $arBasketItem["PRICE"], "retailPrice" => $arBasketItem["PRICE"], "fragile" => false, 
                "danger" => false, "perishable" => false, "needBox" => false);
            if($arBasketItem["WEIGHT"] > 0){
                $arItem["weight"] = $arBasketItem["WEIGHT"];
            }
            if(!empty($arBasketItem["WIDTH"])){
                $arItem["width"] = $arBasketItem["WIDTH"]/10;
            }
            if(!empty($arBasketItem["HEIGHT"])){
                $arItem["height"] = $arBasketItem["HEIGHT"]/10;
            }
            if(!empty($arBasketItem["LENGTH"])){
                $arItem["length"] = $arBasketItem["LENGTH"]/10;
            }
            $price = ($arOrder["DEPARTURE"]["COD_VALUE"] > 0)?$arBasketItem["PRICE"] : 0;
            if(empty($_SESSION['Shiptor']['PRODUCTS'][$arItem['shopArticle']])){
                $arRequest = $oShiptorAPI->Request("getProducts",array('shopArticle' => $arItem['shopArticle']));
                if(empty($arRequest['result'])){
                    $arRequest = $oShiptorAPI->Request("addProduct",$arItem);
                }else{
                    $arRequest['result'] = $arRequest['result'][0];
                }
                $arProduct = array("shopArticle" => $arRequest['result']['shopArticle']?:$arBasketItem["ID"], "count" => $arBasketItem["QUANTITY"]);
                $_SESSION['Shiptor']['PRODUCTS'][$arItem['shopArticle']] = $arRequest['result'];
            }else{
                $arProduct = array("shopArticle" => $_SESSION['Shiptor']['PRODUCTS'][$arItem['shopArticle']]['shopArticle'], "count" => $arBasketItem["QUANTITY"]);
            }
            if($arOrder["DEPARTURE"]["COD_VALUE"] > 0){
                $arProduct["price"] = $price;
            }
            $arOrder["PRODUCTS"][] = $arProduct;
        }
        return $arOrder;
    }
    public static function getPackageData($oDelivery,$arOrder){
        $arParams = array(
            "length" => floatval($arOrder["ORDER"]["DIMENSIONS"]["LENGTH"]),
            "width" => floatval($arOrder["ORDER"]["DIMENSIONS"]["WIDTH"]),
            "height" => floatval($arOrder["ORDER"]["DIMENSIONS"]["HEIGHT"]),
            "weight" => floatval($arOrder["ORDER"]["WEIGHT"]),
            "cod" => round($arOrder["DEPARTURE"]["COD_VALUE"],2),
            "declared_cost" => round($arOrder["DEPARTURE"]["COST_DECLARING"],2),
            "external_id" => $arOrder["ACCOUNT_NUMBER"],
            "departure" => array(
                "shipping_method" => intval($arOrder["METHOD_ID"]),
                "delivery_point" => intval($arOrder["PVZ_CODE"]),
                "comment" => $arOrder["DESC"],
                "address" => array(
                    "country" => $arOrder["ADDRESS"]["COUNTRY_CODE"],
                    "name" => $arOrder["FIO"][1]?:$arOrder["FIO"][0],
                    "surname" => $arOrder["FIO"][0],
                    "patronymic" => $arOrder["FIO"][2],
                    "email" => $arOrder["EMAIL"],
                    "phone" => $arOrder["PHONE"],
                    "postal_code" => $arOrder["ADDRESS"]["ZIP"],
                    "administrative_area" => $arOrder["ADDRESS"]["REGION"],
                    "settlement" => $arOrder["ADDRESS"]["CITY"],
                    "kladr_id" => $arOrder["ADDRESS"]["KLADR"]
                )
            ),
            "products" => $arOrder["PRODUCTS"]
        );
        $deliveryConfig = $oDelivery->getConfig();
        $isFulfilment = (bool)($deliveryConfig['MAIN']['ITEMS']['IS_FULFILMENT']['VALUE'] == 'Y');
        if(!$isFulfilment){
            $arParams['is_fulfilment'] = false;
        }
        $dDate = strtotime($arOrder["ORDER"]["DATE_DELIVERY"]);
        if($dDate > strtotime("+1day") && $dDate < strtotime("+8day")){
            $arParams["departure"]["delayed_delivery_at"] = date("Y-m-d", $dDate);
        }
        if(is_numeric($arOrder["ORDER"]["TIME_DELIVERY"])){
            $arParams["departure"]["delivery_time"] = $arOrder["ORDER"]["TIME_DELIVERY"];
        }
        if(Config::isAddressSimple()){
            $arParams["departure"]["address"]["address_line_1"] = $arOrder["ADDRESS"]["STREET"];
        }else{
            $arParams["departure"]["address"]["street"] = $arOrder["ADDRESS"]["STREET"];
            $arParams["departure"]["address"]["house"] = $arOrder["ADDRESS"]["BLD"]." ".($arOrder["ADDRESS"]["CORP"]? Loc::getMessage("SHIPTOR_ADDRESS_CORP_WORD").$arOrder["ADDRESS"]["CORP"]:"");
            $arParams["departure"]["address"]["apartment"] = $arOrder["ADDRESS"]["FLAT"];
        }
        if($arOrder["ORDER"]["COD"] == true && $arOrder["ORDER"]["PAYMENT"] == "card"){
            $arParams["departure"]["cashless_payment"] = true;
        }
        if($arOrder["DEPARTURE"]["COD_VALUE"] > 0){
            unset($arOrder["SERVICE"]["name"]);
            $arOrder["SERVICE"]["count"] = 1;
            $arParams["services"] = array($arOrder["SERVICE"]);
        }
        if(self::isPVZProfile($oDelivery)){
            unset($arParams['departure']['address']['address_line_1']);
            unset($arParams['departure']['address']['postal_code']);
            unset($arParams['departure']['address']['street']);
            unset($arParams['departure']['address']['house']);
            unset($arParams['departure']['address']['apartment']);
        }else{
            unset($arParams['departure']['delivery_point']);
        }
        return $arParams;
    }
    public static function sendOrder($shipment){
        $oShiptorAPI = CShiptorAPI::getInstance();
        $oDelivery = $shipment->getDelivery();
        $shipmentId = $shipment->getId();
        $arOrder = self::getOrderData($shipment);
        $arPackage = self::getPackageData($oDelivery, $arOrder);
        $arRequest = $oShiptorAPI->Request("addPackage",$arPackage);
        if($arRequest['result']['id'] > 0){
            $changeStatus = Config::getChangeStatus();
            $shipment->setField('DEDUCTED','Y');
            if(!empty($changeStatus) && $changeStatus != Config::getTriggerStatus()){
                $shipment->setField('STATUS_ID',$changeStatus);
            }
            $shipment->setField('XML_ID',$arRequest['result']['id']);
            $shipment->setField('TRACKING_NUMBER','RP' . $arRequest['result']['id']);
            $shipment->setField('DELIVERY_DOC_NUM',$arRequest['result']['id']);
            $shipment->setField('DELIVERY_DOC_DATE',new Type\DateTime());
            $shipment->setField("MARKED","N");
            $shipment->setField("REASON_MARKED","");
            $shipment->setField("EMP_MARKED_ID","");
            $shipment->setField('TRACKING_DESCRIPTION',$arRequest['result']['status']);
            $res = $shipment->getCollection()->getOrder()->save();
            if(!$res->isSuccess()){
                throw new \Exception(implode('\r\n',$res->getErrorMessages()));
            }else{
                return Loc::getMessage("SHIPTOR_SEND_SUCCESS",array("#ORDER#" => $arOrder["ACCOUNT_NUMBER"]));
            }
        }else{
            $arReplacesErr = array("#ID#" => $shipmentId, "#SHIPMENT_NUM#" => $arOrder["ACCOUNT_NUMBER"],"#SHIPTOR_MESSAGE#" => $arRequest['error']['message']);
            throw new \Exception(Loc::getMessage("SHIPTOR_ERROR_SENT_WRONG",$arReplacesErr));
        }
    }
    public static function getDirectOrderPackage($shipment){
        $oDelivery = $shipment->getDelivery();
        $arOrder = self::getOrderData($shipment);
        if(!empty($arOrder["ERROR"])){
            return $arOrder;
        }
        $arPackage["package"] = self::getPackageData($oDelivery, $arOrder);
        $arPackage["delivery_id"] = $oDelivery->getId();
        $arPackage["change_status"] = Config::getChangeStatus();
        if(!empty($arOrder["SENDER"]["PVZ_CODE"])){
            $arPackage["shipment"] = array(
                "type" => "delivery-point",
                "courier" => $oDelivery->getCourier(),
                "address" => array(
                    "receiver" => $arOrder["SENDER"]["FIO"],
                    "phone" => $arOrder["SENDER"]["PHONE"],
                    "email" => $arOrder["SENDER"]["EMAIL"]
                ),
                "delivery_point" => intval($arOrder["SENDER"]["PVZ_CODE"]),
                "date" => $arOrder["SENDER"]["DATE"],
                "comment" => $arOrder["SENDER"]["COMMENT"]
            );
        }else{
            $arPackage["shipment"] = array(
                "type" => "courier",
                "courier" => $oDelivery->getCourier(),
                "address" => array(
                    "receiver" => $arOrder["SENDER"]["FIO"],
                    "phone" => $arOrder["SENDER"]["PHONE"],
                    "email" => $arOrder["SENDER"]["EMAIL"],
                    "country" => $arOrder["SENDER"]["COUNTRY_CODE"],
                    "administrative_area" => $arOrder["SENDER"]["REGION"],
                    "settlement" => $arOrder["SENDER"]["CITY"],
                    "postal_code" => $arOrder["SENDER"]["ZIP"],
                    "street" => $arOrder["SENDER"]["STREET"],
                    "house" => $arOrder["SENDER"]["HOUSE"],
                    "kladr_id" => $arOrder["SENDER"]["KLADR"]
                ),
                "comment" => $arOrder["SENDER"]["COMMENT"],
                "date" => $arOrder["SENDER"]["DATE"]
            );
            if(!empty($arOrder["SENDER"]["FLAT"])){
                $arPackage["shipment"]["address"]["apartment"] = $arOrder["SENDER"]["FLAT"];
            }
        }
        return $arPackage;
    }
    public static function sendDirectOrders($arShipments){
        $oShiptorAPI = CShiptorAPI::getInstance();
        $arResult = array("ERROR" => array(), "SUCCESS" => array());
        $arDirectPackages = array();
        $changeStatus = false;
        $erredDeliveries = array();
        foreach($arShipments as $orderId => $shipment){
            $arPackage = self::getDirectOrderPackage($shipment);
            if($arPackage["ERROR"]){
                $arResult["ERROR"][] = $orderId;
                $shipment->setField("MARKED","Y");
                $shipment->setField("REASON_MARKED",$arPackage["ERROR"]);
                $shipment->save();
                $erredDeliveries[$shipment->getDeliveryId()][] = "#{$orderId}: ".$arPackage["ERROR"];
                continue;
            }
            if(empty($changeStatus) && !empty($arPackage["change_status"])){
                $changeStatus = $arPackage["change_status"];
            }
            $arDirectPackages[$arPackage["delivery_id"]]["shipment"] = $arPackage["shipment"];
            $arDirectPackages[$arPackage["delivery_id"]]["packages"][] = $arPackage["package"];
        }
        foreach($arDirectPackages as $deliveryId => $arPackage){
            if(in_array($deliveryId,array_keys($erredDeliveries))){
                foreach($arPackage['packages'] as $package){
                    if(!in_array($package["external_id"],$arResult["ERROR"])){
                        $arResult["ERROR"][] = $package["external_id"];
                    }
                    $shipment = $arShipments[$package["external_id"]];
                    $shipment->setField("MARKED","Y");
                    $shipment->setField("REASON_MARKED",implode(",",$erredDeliveries[$deliveryId]));
                    $shipment->save();
                }
                continue;
            }
            $arRequest = $oShiptorAPI->Request("addPackages",$arPackage);
            if(!empty($arRequest['result']['packages'])){
                foreach($arRequest['result']['packages'] as $package){
                    if(!empty($package["tracking_number"])){
                        $arResult["SUCCESS"][] = $package["external_id"];
                        $shipment = $arShipments[$package["external_id"]];
                        $shipment->setField('DEDUCTED','Y');
                        if(!empty($changeStatus)){
                            $shipment->setField('STATUS_ID',$changeStatus);
                        }
                        $shipment->setField('XML_ID',$package["id"]);
                        $shipment->setField('TRACKING_NUMBER',$package["tracking_number"]);
                        $shipment->setField('DELIVERY_DOC_NUM',$package["id"]);
                        $shipment->setField('DELIVERY_DOC_DATE',new Type\DateTime());
                        $shipment->setField("MARKED","N");
                        $shipment->setField("REASON_MARKED","");
                        $shipment->setField("EMP_MARKED_ID","");
                        $shipment->setField('TRACKING_DESCRIPTION',$package["status"]);
                        $shipment->getCollection()->getOrder()->save();
                    }
                }
            }
            if(!empty($arRequest['error']['message'])){
                $keyError = intval(array_pop(explode("#",$arRequest['error']['message'])));
                $orderErrorId = $arPackage['packages'][$keyError]['external_id'];
                $arRequest['error']['message'] = str_replace("#".$keyError,"#".$orderErrorId,$arRequest['error']['message']);
                foreach($arPackage['packages'] as $key => $package){
                    $shipment = $arShipments[$package["external_id"]];
                    $shipment->setField("MARKED","Y");
                    $shipment->setField("REASON_MARKED",$arRequest['error']['message']);
                    $shipment->save();
                }
            }
        }
        return $arResult;
    }
    public static function getPackages(){
        $shiptorApi = CShiptorAPI::getInstance();
        $arPackages = array();
        $i = 1;
        while(true){
            $arResult = $shiptorApi->Request("getPackages",array("page" => $i, "per_page" => 100, "archived" => false));
            if(count($arResult["result"]) > 0){
                foreach($arResult["result"] as $arItem){
                    $arPackages[$arItem["id"]] = ["id" => $arItem["id"], "status" => $arItem["status"],
                        "last_hitory" => $arItem['history'][0]['date']];
                }
                $i++;
            }else{
                break;
            }
            if($i > 20) break;
        }
        return $arPackages;
    }
    public static function getDaysOff($from,$till){
        $arParams = array(
            "from" => $from,
            "till" => $till
        );
        $cache = Cache::createInstance();
        $caheId = serialize($arParams);
        if($cache->initCache(self::DAY, $caheId, "/doff")){
            $arDaysOff = $cache->getVars();
        }elseif($cache->startDataCache()){
            $shiptorApi = CShiptorAPI::getInstance();
            $arRes = $shiptorApi->Request("getDaysOff",$arParams);
            $arDaysOff = $arRes['result'];
            $cache->endDataCache($arDaysOff);
        }
        return $arDaysOff;
    }
    public static function getDeliveryTime(){
        $cache = Cache::createInstance();
        if($cache->initCache(self::DAY, "sh_dtimes", "/dtimes")){
            $arDeliveryTimes = $cache->getVars();
        }elseif($cache->startDataCache()){
            $shiptorApi = CShiptorAPI::getInstance();
            $arRes = $shiptorApi->Request("getDeliveryTime",array());
            $arDeliveryTimes = $arRes['result'];
            $cache->endDataCache($arDeliveryTimes);
        }
        return $arDeliveryTimes;
    }
    public static function createAgentIfNone(){
        global $DB, $USER;
        $found = $DB->Query("select ID, ACTIVE, USER_ID from b_agent where NAME = 'CShiptorDeliveryHelper::checkOrders();'")->Fetch();
        if(!$found){
            $date2Run = date($DB->DateFormatToPHP(\CSite::GetDateFormat("FULL")),strtotime("+1day 01:30:00"));
            $agentId = \CAgent::AddAgent("CShiptorDeliveryHelper::checkOrders();", "shiptor.delivery", "N", self::DAY, $date2Run, "Y", $date2Run, 100, $USER->GetID());
        }else{
            $agentId = $found["ID"];
            if(!$found["USER_ID"]){
                \CAgent::Update($found["ID"],array("USER_ID" => $USER->GetID()));
            }
        }
        return $agentId;
    }
}