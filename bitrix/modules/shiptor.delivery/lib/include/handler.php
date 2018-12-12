<?
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Page\Asset,
    Bitrix\Main\Context,
    Bitrix\Main\Event,
    Bitrix\Main\Web\Json,
    Shiptor\Delivery\Options\Config,
    Shiptor\Delivery\ShiptorHandler,
    Shiptor\Delivery\Services\DateDelivery,
    Shiptor\Delivery\Services\TimeDelivery,
    Shiptor\Delivery\Logger;

Loc::loadMessages(__FILE__);

class CShiptorDeliveryHandler{
    const MODULE_ID = "shiptor.delivery";
    const PICK_PVZ_HTML = <<<PICK_PVZ
        <div id="shd_pvz_pick" data-json='%s' data-delivery="%s" data-force="%s">
            <button type="button" onclick="window.Shiptor.Pvz.onPickerClick(this);">%s</button>
        </div>
PICK_PVZ;
    const PVZ_PACEHOLDER = '<div id="shd_pvz_info"><small>%s %s</small></div>';

    public function addCustomDeliveryServices(/* \Bitrix\Main\Event $event */) {
        $libPath = sprintf("/bitrix/modules/%s/lib",self::MODULE_ID);
        $result = new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS, array(
                '\Shiptor\Delivery\ShiptorHandler' => $libPath."/ShiptorHandler.php",
                '\Shiptor\Delivery\ProfileHandler' => $libPath."/ProfileHandler.php"
            )
        );
        return $result;
    }
    public function addCustomRestrictions(){
        $libPath = sprintf("/bitrix/modules/%s/lib",self::MODULE_ID);
        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            array(
                '\Shiptor\Delivery\Restrictions\ExcludeLocation' => $libPath."/restrictions/ExcludeLocation.php",
            )
        );
    }
    public function addCustomExtraServices(){
        $libPath = sprintf("/bitrix/modules/%s/lib",self::MODULE_ID);
        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            array(
                '\Shiptor\Delivery\Services\DateDelivery' => $libPath."/services/DateDelivery.php",
                '\Shiptor\Delivery\Services\TimeDelivery' => $libPath."/services/TimeDelivery.php",
            )
        );
    }
    public function showAjaxAnswer(&$arResult){
    }
    public function showCreateAnswer($order, $arUserResult, $request, &$arParams, &$arResult, &$arDeliveryServiceAll, &$arPaySystemServiceAll){
        $checkoutUrls = Config::getDataValue("checkout");
        $arCheckoutUrl = explode(";",$checkoutUrls);
        $oRequest = Context::getCurrent()->getRequest();
        $currentUrl = $oRequest->getRequestedPageDirectory()."/";
        $cAsset = Asset::getInstance();
        if(in_array($currentUrl,$arCheckoutUrl)){
            if(Config::isIncludeYaMaps()){
                $cAsset->addJs(SHIPTOR_DELIVERY_YMAPS_URL, true);
            }
            \CJSCore::Init(array("shiptor_pickup"));
            $cAsset->addCss(SHIPTOR_DELIVERY_FA_URL);
        }
        $propertyCollection = $order->getPropertyCollection();
        $oLocationCode = $propertyCollection->getDeliveryLocation();
        if(empty($oLocationCode)){
            return;
        }
        $sLocationCode = $oLocationCode->getValue();
        $arLocation = CShiptorDeliveryHelper::getLocationByCode($sLocationCode);
        if(empty($arLocation)){
            return;
        }
        if(empty($arLocation["CITY"])){
            $arResult["WARNING"]["REGION"][] = Loc::getMessage("SHIPTOR_NO_CITY");
            $arResult["WARNING"]["DELIVERY"][] = Loc::getMessage("SHIPTOR_NO_CITY");
        }
        $paymentType = CShiptorDeliveryHelper::getPaytype($arUserResult["PAY_SYSTEM_ID"]);
        foreach($arDeliveryServiceAll as $key => $oDelivery){
            $deliveryId = $oDelivery->getId();
            if(CShiptorDeliveryHelper::isPVZProfile($oDelivery)){
                $methodId = $oDelivery->getMethodId();
                $pvzInfo = $_SESSION['Shiptor'][$deliveryId]["PVZ_INFO"];
                if("windows-1251" == LANG_CHARSET){
                    $pvzInfo["address"] = iconv('utf-8',LANG_CHARSET,$pvzInfo["address"]);
                    $pvzInfo["work_schedule"] = iconv('utf-8',LANG_CHARSET,$pvzInfo["work_schedule"]);
                }
                if($arLocation['KLADR'] != $pvzInfo["kladr_id"]){
                    unset($pvzInfo,$_SESSION['Shiptor'][$deliveryId]["PVZ_INFO"]);
                }
                $arParentConfig = $oDelivery->getParentService()->getConfigOuter();
                switch($arParentConfig['COD']["CALCULATION_TYPE"]){
                    case ShiptorHandler::COD_ALWAYS:default:
                        $isCod = true;
                        break;
                    case ShiptorHandler::COD_NEVER:
                        $isCod = false;
                        break;
                    case ShiptorHandler::COD_CERTAIN:
                        $isCod = array_intersect(array($arUserResult["PAY_SYSTEM_ID"]),$arParentConfig['COD']['SERVICES_LIST'])?true:false;
                        break;
                }
                $pvzCode = false;
                $pvzAddress = false;
                if(!empty($pvzInfo)){
                    $pvzCode = $pvzInfo["id"];
                    $pvzAddress = $pvzInfo["address"].'<br/>'.$pvzInfo["work_schedule"];
                    if($pvzInfo["card"] == false && $paymentType == "card" && $isCod){
                        $pvzCode = false;
                        $pvzAddress = false;
                    }
                    if($pvzInfo["cod"] == false && $paymentType == "cash" && $isCod){
                        $pvzCode = false;
                        $pvzAddress = false;
                    }
                }
                $personTypeId = $order->getPersonTypeId();
                $orderBasket = $order->getBasket();
                $arOrder = array(
                    "ORDER" => array(
                        "ITEMS" => $oDelivery->getBasket($orderBasket),
                    ),
                    "CONFIG" => $arParentConfig
                );
                $orderDimensions = $oDelivery->getOrderDimensions($arOrder);
                $weight = $oDelivery->getOrderWeight($arOrder);
                $json = Json::encode(array(
                    "kladr" => $arLocation["KLADR"],
                    "pvz" => $pvzCode,
                    "cod" => $isCod,
                    "payment" => $paymentType,
                    "method" => $methodId,
                    "delivery" => $deliveryId,
                    "address_prop_id" => Config::getAddressPropId($personTypeId),
                    "pvz_address" => $pvzInfo["address"],
                    "limits" => array(
                        "weight" => $weight,
                        "length" => $orderDimensions["LENGTH"],
                        "width" => $orderDimensions["WIDTH"],
                        "height" => $orderDimensions["HEIGHT"]
                    )
                ));
                $hauntUserNoPvz = Config::isPvzHaunt()?1:0;
                if(empty($pvzCode)){
                    $pvzCode = '<span style="color:red;">'.Loc::getMessage("SHIPTOR_NO_PVZ").'</span>';
                    if($arUserResult["DELIVERY_ID"] == $deliveryId){
                        $arResult['WARNING']['DELIVERY'][] = Loc::getMessage("SHIPTOR_NO_PVZ");
                    }
                    $buttonText = Loc::getMessage("SHIPTOR_PICK_PVZ");
                }else{
                    $pvzCode = "#".$pvzCode;
                    $buttonText = Loc::getMessage("SHIPTOR_CHANGE_PVZ");
                }
                $jsNoPvz = <<<JS
                        <script type="text/javascript">
                            BX.ready(function(){
                                window.Shiptor.checkPvz({order:{DELIVERY:[{CHECKED:"Y", ID: {$deliveryId}}]}});
                            });
                        </script>
JS;
                $htmlPickPVZ = sprintf(self::PICK_PVZ_HTML,$json,$deliveryId,$hauntUserNoPvz,$buttonText);
                $cAsset->addString($jsNoPvz);
                $htmlPlaceholder = sprintf(self::PVZ_PACEHOLDER,$pvzCode,$pvzAddress);
                $oDelivery->addDescription($htmlPlaceholder.$htmlPickPVZ);
            }
            $dateDateliveryId = DateDelivery::getId($deliveryId);
            $timeExtraServiceId = TimeDelivery::getId($deliveryId);
            if($oDelivery instanceof \Shiptor\Delivery\ProfileHandler
                && $oDelivery->getExtraServices()->getItem($dateDateliveryId)){
                $dateDeliveryDefault = $oDelivery->getExtraServices()->getItem($dateDateliveryId)->getValue();
                $nextDayTime = strtotime("+1day");
                $sevenDaysTime = strtotime("+7day");
                if($nextDayTime > strtotime($dateDeliveryDefault) || strtotime($dateDeliveryDefault) > $sevenDaysTime){
                    if($nextDayTime > strtotime($dateDeliveryDefault)){
                        $date2Set = date("d.m.Y",$nextDayTime);
                    }else{
                        $date2Set = date("d.m.Y",$sevenDaysTime);
                    }
                    $oDelivery->getExtraServices()->getItem($dateDateliveryId)->setValue($date2Set);
                    foreach($order->getShipmentCollection() as $shipment){
                        if(!$shipment->isSystem()){
                            $arExtraServices = $shipment->getExtraServices();
                            $arExtraServices[$dateDateliveryId] = $date2Set;
                            $shipment->setExtraServices($arExtraServices);
                        }
                    }
                }
                if($oDelivery->getExtraServices()->getItem($timeExtraServiceId)){
                    $arDaysOff = DateDelivery::getDaysOff();
                    if(in_array($dateDeliveryDefault,$arDaysOff)){
                        $oDelivery->getExtraServices()->getItem($timeExtraServiceId)->setValue(0);
                        $oDelivery->getExtraServices()->getItem($timeExtraServiceId)->disable();
                        $oDelivery->getExtraServices()->getItem($timeExtraServiceId)->setTitle(Loc::getMessage("SHIPTOR_DATE_DELIVERY_TIME_RESTRICTION"));
                        foreach($order->getShipmentCollection() as $shipment){
                            if(!$shipment->isSystem()){
                                $arExtraServices = $shipment->getExtraServices();
                                $arExtraServices[$timeExtraServiceId] = 0;
                                $shipment->setExtraServices($arExtraServices);
                            }
                        }
                    }
                }
            }
        }
    }
    public function saveInNewOrderMethodPVZ(Event $event) {
        $order = $event->getParameter("ENTITY");
        $showNoPvzError = Config::getDataValue("pvzStrict");
        $bShowNoPvzError = (bool)($showNoPvzError == "Y");
        if ($order->isNew()) {
            $order = $event->getParameter("ENTITY");  // order
            $shipmentCollection = $order->getShipmentCollection();
            foreach($shipmentCollection as $shipment){
                if($shipment->isSystem()){
                    continue;
                }
                $oDelivery = $shipment->getDelivery();
                $deliveryId = $oDelivery->getId();
            }
            if(CShiptorDeliveryHelper::isPVZProfile($oDelivery)){
                $personTypeId = $order->getPersonTypeId();
                $propPvzId = Config::getPvzPropId($personTypeId);
                $pvzCode = $_SESSION["Shiptor"][$deliveryId]['PVZ_ID'];
                if (!empty($pvzCode)) {
                    $propertyCollection = $order->getPropertyCollection();
                    $oPropPvz = $propertyCollection->getItemByOrderPropertyId($propPvzId);
                    if(!empty($oPropPvz)){
                        $oPropPvz->setValue($pvzCode);
                    }else{
                        Logger::force(Loc::getMessage('SHIPTOR_NO_PVZ_PROP'));
                    }
                    if(Config::isMirrorPvz() && Config::isAddressSimple()){
                        $propAddrId = Config::getAddressPropId($personTypeId);
                        $oAddressPvz = $propertyCollection->getItemByOrderPropertyId($propAddrId);
                        if(!empty($oAddressPvz)){
                            $pvzAddress = $_SESSION["Shiptor"][$deliveryId]["PVZ_INFO"]["address"];
                            if("windows-1251" == LANG_CHARSET){
                                $pvzAddress = iconv('utf-8',LANG_CHARSET,$pvzAddress);
                            }
                            $oAddressPvz->setValue(Loc::getMessage("SHIPTOR_API_PVZ_MARKER",array("#ADDRESS#" => $pvzAddress)));
                        }
                    }
                }elseif($bShowNoPvzError && !defined("ADMIN_SECTION")){
                    return new \Bitrix\Main\EventResult(
                        \Bitrix\Main\EventResult::ERROR,
                        new \Bitrix\Sale\ResultError("Shiptor($deliveryId) ". $oDelivery->getName().": ".Loc::getMessage("SHIPTOR_NO_PVZ"), 'SALE_EVENT_NO_PVZ'),
                        'sale'
                    );
                }
                unset($_SESSION["Shiptor"]);
            }
            if(Config::isDateTimeMirror() && CShiptorDeliveryHelper::isShiptorCourier($oDelivery)){
                $dateExtraServiceId = DateDelivery::getId($oDelivery->getId());
                $extraServices = $shipment->getExtraServices();
                $sDateDelivery = $extraServices[$dateExtraServiceId]?:date('d.m.Y',strtotime('+1day'));
                $timeExtraServiceId = TimeDelivery::getId($oDelivery->getId());
                $arCommentText = array();
                if(!empty($sDateDelivery)){
                    $arCommentText[] = $sDateDelivery;
                    if(!empty($extraServices[$timeExtraServiceId])){
                        $arDaysOff = DateDelivery::getDaysOff();
                        if(!in_array($sDateDelivery,$arDaysOff)){
                            $arTimeIntervals = CShiptorDeliveryHelper::getDeliveryTime();
                            $arCommentText[] = $arTimeIntervals[intval($extraServices[$timeExtraServiceId])];
                        }
                    }
                }
                if(!empty($arCommentText)){
                    $comment = $order->getField('USER_DESCRIPTION');
                    $comment .= "\n\r".Loc::getMessage('SHIPTOR_API_DATE_TIME_SHC').implode(", ",$arCommentText);
                    $order->setField('USER_DESCRIPTION', $comment);
                }
            }
        }
    }
    public function sendOrderToShiptor(Event $event) {
        $name = $event->getParameter('NAME');
        $value = $event->getParameter('VALUE');
        if($name != 'STATUS_ID') return true;
        $shipment = $event->getParameter('ENTITY');

        if (!$shipment ){
            return true;
        }
        $arShiptorIds = \CShiptorDeliveryHelper::getDeliveries();
        $oDelivery = $shipment->getDelivery();
        if(!$oDelivery){
            return true;
        }
        $arOrderShipmentId = $shipment->getDeliveryId();
        $isShiptor = (bool)(in_array($arOrderShipmentId,$arShiptorIds));
        if(!$isShiptor){
            return true;
        }
        if($oDelivery->isDirect()){
            return true;
        }
        $arShiptorShipment["CONFIG"] = $oDelivery->getParentService()->getConfigOuter();

        $order = $shipment->getCollection()->getOrder();
        $orderId = $order->getId();
        $paySystemId = null;
        $arPaymentCollection = $order->getPaymentCollection();
        foreach($arPaymentCollection as $payment){
            if($payment->isInner()) continue;
            $paySystemId = $payment->getPaymentSystemId();
        }
        $isExists = !!$orderId;
        $isStatusMatched = (bool)($value == Config::getTriggerStatus());
        $isAutomatic = Config::isAutomaticUpload();
        if(!$isExists || !$isStatusMatched || !$isAutomatic){
            return true;
        }
        $isShipped = $shipment->isShipped();
        $isAllowed = $shipment->isAllowDelivery();
        if (!$isShipped && $isAllowed) {
            try{
                $result = CShiptorDeliveryHelper::sendOrder($shipment);
            } catch (\Exception $e) {
                $shipment->setField("MARKED","Y");
                $shipment->setField("REASON_MARKED",$e->getMessage());
                $res = $shipment->save();
                Logger::exception($e);
                return new \Bitrix\Main\EventResult(
                    \Bitrix\Main\EventResult::ERROR, 
                    new \Bitrix\Sale\ResultError($e->getMessage(), 'code'), 'sale');
            }
            return true;
        }else{
            if($isShipped){
                return new \Bitrix\Main\EventResult(
                    \Bitrix\Main\EventResult::ERROR, 
                    new \Bitrix\Sale\ResultError(Loc::getMessage("SHIPTOR_API_ERROR_ALREADY_SHIPPED"), 'code'), 'sale');
            }
            if(!$isAllowed){
                return new \Bitrix\Main\EventResult(
                    \Bitrix\Main\EventResult::ERROR, 
                    new \Bitrix\Sale\ResultError(Loc::getMessage("SHIPTOR_API_ERROR_SHIP_NOT_ALLOWED"), 'code'), 'sale');
            }
        }
        return true;
    }
    function onDeliveryServiceCalculate(Event $event) {

    }
    function onEpilog(){
        
    }
}