<?
use Bitrix\Main,    
    Bitrix\Main\Localization\Loc as Loc,    
    Bitrix\Main\Loader,    
    Bitrix\Main\Config\Option,    
    Bitrix\Sale\Delivery,    
    Bitrix\Sale\PaySystem,
    Bitrix\Sale\PersonType,
    Bitrix\Sale,    
    Bitrix\Sale\Order,    
    Bitrix\Sale\DiscountCouponsManager,    
    Bitrix\Main\Context;

class CDelivery extends CBitrixComponent{

    public static function getCalculatedItems($arParams){

        //check modules
        if (!CModule::IncludeModule("sale") ||
            !CModule::IncludeModule("catalog") ||
            !CModule::IncludeModule("iblock")){
            return false;
        }

        //params
        if(empty($arParams["CALC_ALL_PRODUCTS"])){
            $arParams["CALC_ALL_PRODUCTS"] = "N";
        }

        if(empty($arParams["PRODUCT_QUANTITY"])){
            $arParams["PRODUCT_QUANTITY"] = 1;
        }

        //vars

        //arrays
        $arReturn["DELIVERY_ITEMS"] = array();

        //globals
        global $USER;

        //other vars
        $productBasketExist = false;

        //site id
        $siteId = !empty($arParams["SITE_ID"]) ? $arParams["SITE_ID"] : SITE_ID;

        //productId
        $productId = intval($arParams["PRODUCT_ID"]);

        //product quantity
        $productQuantity = $arParams["PRODUCT_QUANTITY"];

        //get base currency
        $currencyCode = \Bitrix\Currency\CurrencyManager::getBaseCurrency();

        //create order d7
        $personTypes = array_keys(PersonType::load($siteId));

        //get current user id       
        $userId = intval($USER->GetID());

        //coupons        
        DiscountCouponsManager::init();

        //create virtual order
        $order = Order::create($siteId, !empty($userId) ? $userId : \CSaleUser::GetAnonymousUserID());

        //set first person type
        $order->setPersonTypeId($personTypes[0]);

        //laad current basket
        if($arParams["CALC_ALL_PRODUCTS"] == "Y"){
            $extraBasket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), $siteId)->getOrderableItems();
            $extraBasketItems = $extraBasket->getBasketItems(); 
        }

        //create new virtual basket
        $basket = Bitrix\Sale\Basket::create($siteId);

        //copy basket
        if(!empty($extraBasketItems)){
            foreach ($extraBasketItems as $ix => $obExtraBasketItem){

                //get data
                $extraProductId = $obExtraBasketItem->getProductId();
                $extraQuantity = $obExtraBasketItem->getQuantity();

                //set exist flag && set current qty from input
                if($extraProductId == $productId){
                    $extraQuantity = $productQuantity;
                    $productBasketExist = true;
                }

                $item = $basket->createItem("catalog", $extraProductId);
                $item->setFields([
                    "PRODUCT_PROVIDER_CLASS" => "CCatalogProductProvider",
                    "QUANTITY" => $extraQuantity,
                    "CURRENCY" => $currencyCode,
                    "LID" => $siteId
                ]);

            }
        }

        //add basket item
        if(!$productBasketExist){
            $item = $basket->createItem("catalog", $productId);
            $item->setFields([
                "PRODUCT_PROVIDER_CLASS" => "CCatalogProductProvider",
                "QUANTITY" => $productQuantity,
                "CURRENCY" => $currencyCode,
                "LID" => $siteId
            ]);
        }

        //bind basket to order
        $order->setBasket($basket);

        //set location
        if(!empty($_SESSION["USER_GEO_POSITION"]["locationID"])){

            // property collection
            $propertyCollection = $order->getPropertyCollection();

            //get location code
            $locationValueId = $_SESSION["USER_GEO_POSITION"]["locationID"];
            $locationValueCode = CSaleLocation::getLocationCODEbyID($locationValueId);
            $locationValueVariant = !empty($locationValueCode) ? $locationValueCode : $locationValueId;

            //get location zip
            $obZipLocs = CSaleLocation::GetLocationZIP($locationValueId);
            if($arZipLocs = $obZipLocs->Fetch()){
                if(!empty($arZipLocs["ZIP"])){
                    $locationValueZip = $arZipLocs["ZIP"];
                }
            }

            //set location order fields
            $order->setFields(array(
                "DELIVERY_LOCATION" => $locationValueVariant,
                "DELIVERY_LOCATION_ZIP" => $locationValueZip
            ));

            //set location order user fileds
            if($locationProperty = $propertyCollection->getDeliveryLocation()){
                $locationProperty->setValue($locationValueVariant);
            }

            //zip      
            if($locationPropertyZip = $propertyCollection->getDeliveryLocationZip()){
                $locationPropertyZip->setValue($locationValueZip);
            }

        }

        //$service = Delivery\Services\Manager::getById(Delivery\Services\EmptyDeliveryService::getEmptyDeliveryServiceId());

        /*Shipment*/
        $shipmentCollection = $order->getShipmentCollection();
        $shipment = $shipmentCollection->createItem();
        //set currency
        $order->setFields(array("CURRENCY" => $currencyCode));
        $shipmentItemCollection = $shipment->getShipmentItemCollection();

        foreach($order->getBasket() as $item){
            $shipmentItem = $shipmentItemCollection->createItem($item);
            $shipmentItem->setQuantity($item->getQuantity());
            //set dimensions
            if(strlen($shipmentItem->getField("DIMENSIONS"))){
                $shipmentItem->setField("DIMENSIONS", unserialize($shipmentItem->getField("DIMENSIONS")));
            }
        }

        $arDeliveryServiceAll = Delivery\Services\Manager::getRestrictedObjectsList($shipment);
        // $shipmentCollection = $shipment->getCollection();

        //each delivery systems
        if(!empty($arDeliveryServiceAll)){

            foreach($arDeliveryServiceAll as $ix => $arNextDelivery){

                //vars
                $deliveryLogo = null;
                $deliveryParentId = null;
                $deliveryIsProfile = false;

                //is automatic delivery system
                if($arNextDelivery->isProfile()){
                    $deliveryName = $arNextDelivery->getNameWithParent();
                    $deliveryParentId = $arNextDelivery->getParentId();
                    $deliveryId = $arNextDelivery->getId();
                    $deliveryIsProfile = true;
                }

                else{
                    $deliveryName = $arNextDelivery->getName();
                    $deliveryId = $arNextDelivery->getId();
                }

                $shipment->setFields(array(
                    "DELIVERY_ID" => $arNextDelivery->getId(),
                    "DELIVERY_NAME" => $deliveryName
                ));

                //calc
                $shipment->calculateDelivery();
                $calcResult = $arNextDelivery->calculate($shipment);
                $deliveryDescription = $arNextDelivery->getDescription();
                $deliveryPrice = Sale\PriceMaths::roundPrecision($calcResult->getPrice());
                $deliveryPriceFormated = SaleFormatCurrency($deliveryPrice, $currencyCode);
                if(!empty($arNextDelivery->getLogotip())){
                    $deliveryLogo = CFile::ResizeImageGet($arNextDelivery->getLogotip(), array("width" => 100, "height" => 100), BX_RESIZE_IMAGE_PROPORTIONAL, false, false, false, 100);
                }
                
                //write
                $arReturn["DELIVERY_ITEMS"][$deliveryId] = array(
                    "ID" => $deliveryId,
                    "PARENT_ID" => $deliveryParentId,
                    "IS_PROFILE" => $deliveryIsProfile,
                    "NAME" => $deliveryName,
                    "DESCRIPTION" => $deliveryDescription,
                    "PRICE" => $deliveryPrice,
                    "PRICE_FORMATED" => $deliveryPriceFormated,
                    "LOGOTIP" => $deliveryLogo,
                );

            }
        }

        return $arReturn["DELIVERY_ITEMS"];

    }

    public static function getMeasureRatio($productId, $productMeasureRatio = 1){

        //check modules
        if (!CModule::IncludeModule("catalog")){
            return false;
        }
        
        //get ratio from BD
        $rsMeasureRatio = CCatalogMeasureRatio::getList(
            array(),
            array("PRODUCT_ID" => $productId),
            false,
            false,
            array()
        );

        if($arProductMeasureRatio = $rsMeasureRatio->Fetch()){
            if(!empty($arProductMeasureRatio["RATIO"])){
                $productMeasureRatio = $arProductMeasureRatio["RATIO"];
            }
        }

        return $productMeasureRatio;

    }

}?>