<?

if (!$_GET["market_stop"]) {

    define('DESCRIPTION_SIZE', 3000);

    if (!CModule::IncludeModule("iblock"))
        die();
    $hlBlock = CModule::IncludeModule('highloadblock');

    $bCatalog = CModule::IncludeModule('catalog');

    /*     * ***********************************************************************
      Processing of received parameters
     * *********************************************************************** */

    if (!isset($arParams["CACHE_TIME"]))
        $arParams["CACHE_TIME"] = 3600;

    if (!isset($arParams["DO_NOT_INCLUDE_SUBSECTIONS"]))
        $arParams["DO_NOT_INCLUDE_SUBSECTIONS"] = "N";

    if (!is_array($arParams["PROPERTY_CODE"]))
        $arParams["PROPERTY_CODE"] = array();

    if (!$arParams['SKU_PROPERTY'])
        $arParams['SKU_PROPERTY'] = 'PROPERTY_CML2_LINK';

    $arParams['SKU_PROPERTY'] = strtoupper($arParams['SKU_PROPERTY']);

    foreach ($arParams["PROPERTY_CODE"] as $key => $value) {
        if ($value === "")
            unset($arParams["PROPERTY_CODE"][$key]);
        else
            $arProperty[] = "PROPERTY_" . trim($value);
    }

    if ($arParams['IBLOCK_AS_CATEGORY'] != 'N')
        $arParams['IBLOCK_AS_CATEGORY'] = 'Y';



    $arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);

    $arParams["COMPANY"] = trim($arParams["COMPANY"]);

    if (!is_array($arParams["IBLOCK_ID_IN"]))
        $arParams["IBLOCK_ID_IN"] = array();
    foreach ($arParams["IBLOCK_ID_IN"] as $k => $v)
        if ($v === "")
            unset($arParams["IBLOCK_ID_IN"][$k]);

    if ((count($arParams["IBLOCK_ID_IN"]) > 0 && $arParams["IBLOCK_ID_IN"][0] === '0'))
        $arParams["IBLOCK_ID_IN"] = '';


    if (!is_array($arParams["IBLOCK_ID_EX"]))
        $arParams["IBLOCK_ID_EX"] = array();
    foreach ($arParams["IBLOCK_ID_EX"] as $k => $v)
        if ($v === "")
            unset($arParams["IBLOCK_ID_EX"][$k]);

    //Filter for element
    if (strlen($arParams["FILTER_NAME"]) <= 0 || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"])) {
        $arrFilter = array();
    }
    else {
        $arrFilter = $GLOBALS[$arParams["FILTER_NAME"]];
        if (!is_array($arrFilter))
            $arrFilter = array();
    }
    //Filter for sku
    if (strlen($arParams["FILTER_NAME_SKU"]) <= 0 || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME_SKU"])) {
        $arrFilterSKU = array();
    }
    else {
        $arrFilterSKU = $GLOBALS[$arParams["FILTER_NAME_SKU"]];
        if (!is_array($arrFilterSKU))
            $arrFilterSKU = array();
    }

    if ($arParams["SHOW_PRICE_COUNT"] <= 0)
        $arParams["SHOW_PRICE_COUNT"] = 1;



    $arParams["CACHE_FILTER"] = ($arParams["CACHE_FILTER"] == "Y");
    if (!$arParams["CACHE_FILTER"] && count($arrFilter) > 0)
        $arParams["CACHE_TIME"] = 0;


    $arParams["PRICE_VAT_INCLUDE"] = $arParams["PRICE_VAT_INCLUDE"] !== "N";

    if (empty($arParams["DISCOUNTS"]))
        $arParams["DISCOUNTS"] = "DISCOUNT_CUSTOM";

    $arResult["FOR_DELETE"] = array(
      "PROP_ALGORITHM_VALUE", "NAME_PROP", "DEVELOPER", "MODEL", "VENDOR_CODE", "COUNTRY", "MANUFACTURER_WARRANTY", "SALES_NOTES",
      "SALES_NOTES_TEXT", "BIG_CATALOG_PROP", "UTM_SOURCE", "UTM_MEDIUM", "UTM_CAMPAIGN", "UTM_TERM", "LOCAL_DELIVERY_COST_OFFER", "STORE_OFFER",
      "STORE_PICKUP", "PREFIX_PROP", "AGE_CATEGORY", "DELIVERY_OPTIONS_EX", "EXPIRY", "WEIGHT", "DIMENSIONS", "ADULT", "ADULT_ALL",
      "OUTLETS", "FEE", "CATEGORY_NAME_PROPERTY", "RECOMMENDATION", "DELIVERY_TO_AVAILABLE", "STORE_DELIVERY", "OLD_PRICE_CODE", "IBLOCK_QUANTITY", "MORE_PHOTO", "BARCODE",
      "URL_PROPERTY_CHECK", "URL_PROPERTY", "URL_PROPERTY_WITH_DOMEN", "PURCHASE_PRICE_CODE");

    Class WFYMRoundPrices {

        const postfix = ".00";

        /*
         * return round accuracy by id
         * type string $accuracy
         */

        function getPriceAccuracy($accuracy) {
            $accuracies = array(
              "0" => "0.0001",
              "1" => "0.001",
              "2" => "0.005",
              "3" => "0.01",
              "4" => "0.02",
              "5" => "0.05",
              "6" => "0.1",
              "7" => "0.2",
              "8" => "0.5",
              "9" => "1",
              "10" => "2",
              "11" => "5",
              "12" => "10",
              "13" => "20",
              "14" => "50",
              "15" => "100",
              "16" => "200",
              "17" => "500",
              "18" => "1000",
              "19" => "5000",
            );
            return ($accuracies[$accuracy]);
        }

        /**
         * 
         * @param int $value rounding price
         * @param int $precision round accuracy
         * @param string $type rounding type
         * @return int rounded price
         */
        function roundValue($value, $precision, $type) {
            return ($precision < 1 ? self::roundFraction($value, $precision, $type) : self::roundWhole($value, $precision, $type)
                );
        }

        /**
         * 
         * @param int $value rounding price
         * @param int $precision round accuracy
         * @param string $type rounding type
         * @return int rounded price
         */
        function roundWhole($value, $precision, $type) {
            $quotient = $value / $precision;
            $quotientFloor = floor($quotient);
            switch ($type) {
                case "STORE":
                    if (($quotient - $quotientFloor) > 1E-5)
                        $quotientFloor += 1;
                    break;
                case "CLIENTS":
                    break;
                case "MATH":
                default:
                    if (($quotient - $quotientFloor) >= .5)
                        $quotientFloor += 1;
                    break;
            }

            return $quotientFloor * $precision;
        }

        /**
         *
         * @param int $value rounding price
         * @param int $precision round accuracy
         * @param string $type rounding type
         * @return int rounded price fraction
         */
        function roundFraction($value, $precision, $type) {
            $valueFloor = floor($value);
            $fraction = $value - $valueFloor;
            if ($fraction <= 1E-5)
                return $value;

            return $valueFloor + self::roundWhole($fraction, $precision, $type);
        }

    }

    if (!function_exists("unparse_url")) {

        function unparse_url($parsed_url) {
            $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
            $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
            $port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
            $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
            $pass = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
            $pass = ($user || $pass) ? "$pass@" : '';
            $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
            $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
            $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
            return "$scheme$user$pass$host$port$path$query$fragment";
        }

    }

    if (!function_exists("charset_modifier")) {

        function charset_modifier($arg) {
            $ent = html_entity_decode($arg[0], ENT_QUOTES, LANG_CHARSET);

            if ($ent == $arg[0])
                return '';
            return $ent;
        }

    }

    if (!function_exists("xml_creator")) {

        function xml_creator($text, $bHSC = true, $bDblQuote = false) {
            $bDblQuote = (true == $bDblQuote ? true : false);

            if ($bHSC) {
                $text = htmlspecialcharsBx($text);
                if ($bDblQuote)
                    $text = str_replace('&quot;', '"', $text);
            }
            $text = preg_replace("/[\x1-\x8\xB-\xC\xE-\x1F]/", "", $text);
            $text = str_replace("'", "&apos;", $text);
            return $text;
        }

    }
    if (!function_exists("xhtml_modifier")) {

        function xhtml_modifier($description) {
            //delete bad html
            $description = strip_tags(html_entity_decode($description), "<h3><p><br><ul><li>");
            $description = str_replace(array("<br>", "<br />"), "<br/>", $description);
            $description = preg_replace("/(\<br\/\>)+\s*(\<br\/\>)+/", "<br/>", $description);
            $description = "<![CDATA[" . $description . "]]>";
            return $description;
        }

    }

    if ($arParams["DISCOUNTS"] == "PRICE_ONLY") {

        function webfly_ymarket_GetPrice($product_id, &$arPrices, &$arOffers, $isRoundPrice) {
            $arOffers[$product_id]["PRICE"] = 0;
            foreach ($arPrices as $arProductPrice) {
                if ($arProductPrice['PRICE'] && ($arProductPrice['PRICE'] < $arOffers[$product_id]["PRICE"] || !$arOffers[$product_id]["PRICE"])) {
                    if ($isRoundPrice["ROUND"] == "Y") {// Round Price if is Flag in arParams
                        if ((abs($arProductPrice['PRICE']) > $isRoundPrice["MINIMUM_PRICE_ROUND"]) or $isRoundPrice["MINIMUM_PRICE_ROUND"] == 0) {
                            $arProductPrice['PRICE'] = WFYMRoundPrices::roundValue($arProductPrice['PRICE'], $isRoundPrice["ACCURACY_PRICE_ROUND"], $isRoundPrice["TYPE_PRICE_ROUND"]);
                            if (substr_count($arProductPrice['PRICE'], ".") == 0)
                                $arProductPrice['PRICE'] = $arProductPrice['PRICE'] . WFYMRoundPrices::postfix;
                        }
                    }
                    $arOffers[$product_id]["PRICE"] = $arProductPrice['PRICE'];
                    $arOffers[$product_id]["CURRENCY"] = $arProductPrice["CURRENCY"];
                }
            }
        }

    }
    else
    if ($arParams["DISCOUNTS"] == "DISCOUNT_CUSTOM") {//uproshchennyj algoritm
        if ($arParams["DONT_CHECK_PRICE_RIGHTS"] == "Y") {
            $arUserGroups = array();
        }
        else {
            $arUserGroups = $GLOBALS["USER"]->GetUserGroupArray();
        }

        function webfly_ymarket_GetPrice($product_id, &$arPrices, &$arOffers, $isRoundPrice, $isOldPrice) {
            global $arUserGroups;
            $price = 0;
            foreach ($arPrices as &$arProductPrice) {
                if ($arProductPrice['PRICE'] && ($arProductPrice['PRICE'] < $price || !$price)) {
                    $price = $arProductPrice['PRICE'];
                    $arOffers[$product_id]["CURRENCY"] = $arProductPrice["CURRENCY"];
                }
                $simple_round = $price;
                $arDiscounts = CCatalogDiscount::GetDiscountByProduct($product_id, $arUserGroups, "N", $arProductPrice['CATALOG_GROUP_ID'], SITE_ID);
                foreach ($arDiscounts as &$arDiscount) {
                    switch ($arDiscount["VALUE_TYPE"]) {
                        case 'P': $price_buf = $arProductPrice["PRICE"] - $arDiscount["VALUE"] * $arProductPrice["PRICE"] / 100; //v procentah
                            break;
                        case 'F': $price_buf = $arProductPrice["PRICE"] - $arDiscount["VALUE"]; //fiksirovannaya
                            break;
                        default: $price_buf = $arDiscount["VALUE"]; //ustanovit cenu na tovar
                            break;
                    }

                    if ($price_buf && (abs($price_buf) < abs($price) || !$price)) {
                        if ($isRoundPrice["ROUND"] == "Y") {// Round Price if is Flag in arParams
                            $simple_round = round($price);
                            if (substr_count($simple_round, ".") == 0)
                                $simple_round = $simple_round . WFYMRoundPrices::postfix;
                            if ((abs($price) > $isRoundPrice["MINIMUM_PRICE_ROUND"]) or $isRoundPrice["MINIMUM_PRICE_ROUND"] == 0) {
                                $price = WFYMRoundPrices::roundValue($price, $isRoundPrice["ACCURACY_PRICE_ROUND"], $isRoundPrice["TYPE_PRICE_ROUND"]);
                                if (substr_count($price, ".") == 0)
                                    $price = $price . WFYMRoundPrices::postfix;
                            }
                            if ((abs($price_buf) > $isRoundPrice["MINIMUM_PRICE_ROUND"]) or $isRoundPrice["MINIMUM_PRICE_ROUND"] == 0) {
                                $price_buf = WFYMRoundPrices::roundValue($price_buf, $isRoundPrice["ACCURACY_PRICE_ROUND"], $isRoundPrice["TYPE_PRICE_ROUND"]);
                                if (substr_count($price_buf, ".") == 0)
                                    $price_buf = $price_buf . WFYMRoundPrices::postfix;
                            }
                        }

                        if ($isOldPrice == "Y") {
                            $old_price = $price; //new
                        }

                        $price = $price_buf;
                        $arOffers[$product_id]["CURRENCY"] = $arProductPrice["CURRENCY"];
                    }
                }
                $arDiscounts = null;
            }
            if (!empty($old_price) and $isOldPrice == "Y" and ( abs($price) < abs($simple_round))) {
                $arOffers[$product_id]["OLD_PRICE"] = $simple_round; //Fill Old Price
            }
            $arOffers[$product_id]["PRICE"] = $price; //new
            /* $arOffers[$product_id]["PRICE"] = $price; */
            CCatalogDiscount::ClearDiscountCache(array('PRODUCT' => 'Y'));
        }

    }
    else {
        // if($arParams["DISCOUNTS"] == "DISCOUNT_API")
        $GLOBALS["baseCurrency"] = CCurrency::GetBaseCurrency();
        if ($arParams["DONT_CHECK_PRICE_RIGHTS"] == "Y") {
            $arUserGroups = array();
        }
        else {
            $arUserGroups = $GLOBALS["USER"]->GetUserGroupArray();
        }

        function webfly_ymarket_GetPrice($product_id, &$arPrices, &$arOffers, $isRoundPrice, $isOldPrice) {
            global $baseCurrency;
            global $arUserGroups;
            $arPrice = CCatalogProduct::GetOptimalPrice($product_id, 1, $arUserGroups, "N", $arPrices);
            if ($arPrices[0]["CURRENCY"] != $baseCurrency) {
                $arPrices[0]["PRICE"] = CCurrencyRates::ConvertCurrency($arPrices[0]["PRICE"], $arPrices[0]["CURRENCY"], $baseCurrency);
            }
            $arPrices[0]["SIMPLE_ROUND"] = $arPrices[0]["PRICE"];
            if ($isRoundPrice["ROUND"] == "Y") {// Round Price if is Flag in arParams
                $arPrices[0]["SIMPLE_ROUND"] = round($arPrices[0]["PRICE"]);

                if (substr_count($arPrices[0]["SIMPLE_ROUND"], ".") == 0)
                    $arPrices[0]["SIMPLE_ROUND"] = $arPrices[0]["SIMPLE_ROUND"] . WFYMRoundPrices::postfix;

                //1.03.2017 START
                if ((abs($arPrices[0]["PRICE"]) > $isRoundPrice["MINIMUM_PRICE_ROUND"]) or $isRoundPrice["MINIMUM_PRICE_ROUND"] == 0) {
                    $arPrices[0]["PRICE"] = WFYMRoundPrices::roundValue($arPrices[0]["PRICE"], $isRoundPrice["ACCURACY_PRICE_ROUND"], $isRoundPrice["TYPE_PRICE_ROUND"]);
                    if (substr_count($arPrices[0]["PRICE"], ".") == 0)
                        $arPrices[0]["PRICE"] = $arPrices[0]["PRICE"] . WFYMRoundPrices::postfix;
                }
                //1.03.2017 END
                if ((abs($arPrice["DISCOUNT_PRICE"]) > $isRoundPrice["MINIMUM_PRICE_ROUND"]) or $isRoundPrice["MINIMUM_PRICE_ROUND"] == 0) {
                    $arPrice["DISCOUNT_PRICE"] = WFYMRoundPrices::roundValue($arPrice["DISCOUNT_PRICE"], $isRoundPrice["ACCURACY_PRICE_ROUND"], $isRoundPrice["TYPE_PRICE_ROUND"]);
                    if (substr_count($arPrice["DISCOUNT_PRICE"], ".") == 0)
                        $arPrice["DISCOUNT_PRICE"] = $arPrice["DISCOUNT_PRICE"] . WFYMRoundPrices::postfix;
                }
            }

            if ((abs($arPrices[0]["PRICE"]) > abs($arPrice["DISCOUNT_PRICE"]) and $isOldPrice == "Y")) {//new
                $arOffers[$product_id]["OLD_PRICE"] = $arPrices[0]["SIMPLE_ROUND"];
            }

            $arOffers[$product_id]["PRICE"] = $arPrice["DISCOUNT_PRICE"];
            $arOffers[$product_id]["CURRENCY"] = $arPrice["PRICE"]["CURRENCY"];

            CCatalogDiscount::ClearDiscountCache(array('PRODUCT' => 'Y'));
        }

    }


    /* AGENT */
    $arResult["AGENT_FOLDER"] = $APPLICATION->GetCurDir();
    COption::SetOptionString("webfly.ymarket", "agentFolder", $arResult["AGENT_FOLDER"], false, false);
    $agentResult = CAgent::GetList(array("ID" => "DESC"), array("MODULE_ID" => "webfly.ymarket", "NAME" => "wfYmarketAgent();"));
    $agentMass = array();
    while ($agentob = $agentResult->GetNext()) {
        $agentMass = $agentob;
    }
    if ($arParams ["AGENT_CHECK"] == "Y") {
        if (empty($agentMass)) {
            /* Add webfly.ymarket's agent */
            CAgent::AddAgent(
                "wfYmarketAgent();", // function name
                "webfly.ymarket", // module's ID
                "N", 86400, // interval
                date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), time() + 10800), // first check - now
                "Y", // agent active
                date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), time() + 10800), // first start - now
                30);
        }
    }
    else {
        if (!empty($agentMass)) {
            CAgent::RemoveAgent(
                "wfYmarketAgent();", "webfly.ymarket", false
            );
        }
    }

    /* HTTPS */
    if ($arParams ["HTTPS_CHECK"] == "Y")
        $http = "https";
    else
        $http = "http";

    //DELIVERY OPTIONS SHOP
    if (!empty($arParams["DELIVERY_OPTIONS_SHOP_EX"])) {
        $deliveryShop = explode("|", $arParams["DELIVERY_OPTIONS_SHOP_EX"]);
        foreach ($deliveryShop as $dsk => $dval) {
            $deliveryShop[$dsk] = explode(",", $dval);
        }
        foreach ($deliveryShop as $deliveryK => $deliveryVal) {
            if ($deliveryVal[0] == "")
                $deliveryShop[$deliveryK][0] = "0";
        }
        $arResult["DELIVERY_OPTION_SHOP"] = $deliveryShop;
        unset($deliveryShop);
    }

    //DELIVERY OPTIONS PRODUCT
    if (!empty($arParams["DELIVERY_OPTIONS_EX"])) {
        $productDeliv = explode("|", $arParams["DELIVERY_OPTIONS_EX"]);
        foreach ($productDeliv as $pdsk => $pdval) {
            $explodeVal = explode(",", $pdval);
            $productDeliv[$pdsk] = $explodeVal;
            if (empty($delivList))
                $delivList = $explodeVal;
            else
                $delivList = array_merge($delivList, $explodeVal);
            unset($explodeVal);
        }
    }

    //NAME_PROP_COMPILE
    if (!empty($arParams["NAME_PROP_COMPILE"])) {
        $nameCompile = explode("|", $arParams["NAME_PROP_COMPILE"]);
        foreach ($nameCompile as $nck => $ncval) {
            if ($nck == 0)
                $nameSelects = explode(",", $ncval);
            else
                $nameInps = explode(",", $ncval);
        }
    }

    //OUTLETS
    if (!empty($arParams["OUTLETS"])) {
        $outlets = explode("|", $arParams["OUTLETS"]);
        foreach ($outlets as $outk => $outval) {
            $explodeoutval = explode(",", $outval);
            $outlets[$outk] = $explodeoutval;
            unset($explodeoutval);
        }
    }

    if ($arParams["USE_SITE"] == "Y")
        $server_name = $arParams["SITE"];
    else
        $server_name = $_SERVER["SERVER_NAME"];
    $bDesignMode = is_object($GLOBALS["USER"]) && $GLOBALS["USER"]->IsAdmin();

    $bSaveInFile = $arParams["SAVE_IN_FILE"] == "Y";

    if (!$bDesignMode or $bSaveInFile) {
        $arResult["SAVE_IN_FILE"] = $bSaveInFile;

        if (!$bSaveInFile) {
            $APPLICATION->RestartBuffer();
            header("Content-Type: text/xml; charset=" . SITE_CHARSET);
            header("Pragma: no-cache");
        }
    }
    else {
        echo "<br/><b>" . GetMessage("ADMIN_TEXT") . "</b><br/>";
        return;
    }

    /*     * ***********************************************************************
      Work with cache
     * *********************************************************************** */
    $cache_id = serialize($arrFilter) . serialize($arParams) . $_GET["WF_PAGE"]; //.$USER->GetGroups() ;
    $cache_folder = '/y-market';

    if ($arParams["CACHE_TYPE"] != "N") {
        if ($arParams["CACHE_NON_MANAGED"] == 'Y') {
            $obCache = new CPHPCache;
            $bCache = $obCache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_folder);
        }
        else {
            $bCache = $this->StartResultCache(false, $cache_id, $cache_folder);
        }
    }
    else {
        $bCache = true;
    }

    if ($bCache) {
        //echo admin info
        if ($arParams["ECHO_ADMIN_INFO"] == "Y") {
            if (($arParams["BIG_CATALOG_PROP"] and ( $_GET["WF_PAGE"] == 1 or ! $_GET["WF_PAGE"])) or ! $arParams["BIG_CATALOG_PROP"]) {
                file_put_contents("ym_log.txt", GetMessage("HOSTING_SETTINGS"), LOCK_EX);
                file_put_contents("ym_log.txt", GetMessage("HOSTING_LIMIT") . ini_get("memory_limit") . "\r\n", FILE_APPEND | LOCK_EX);
                file_put_contents("ym_log.txt", GetMessage("HOSTING_LIMIT_TIME") . ini_get("max_execution_time") . "Ñ\r\n\r\n", FILE_APPEND | LOCK_EX);
                $LOG_STEP = 1;
            }
            else {
                $LOG_STEP = $_GET["WF_PAGE"];
            }
            $stepStartTime = date("d.m.Y H:i:s");
            file_put_contents("ym_log.txt", GetMessage("STEP") . $LOG_STEP . "***\r\n", FILE_APPEND | LOCK_EX);
            file_put_contents("ym_log.txt", GetMessage("COMPONENT_WORK"), FILE_APPEND | LOCK_EX);
            file_put_contents("ym_log.txt", GetMessage("SCRIPT_START") . $stepStartTime . "\r\n", FILE_APPEND | LOCK_EX);
        }
        $arResult["DATE"] = Date("Y-m-d H:i");
        $arResult["COMPANY"] = strip_tags(html_entity_decode($arParams["COMPANY"]));
        $arResult["SITE"] = $arParams["SITE"];
        $arResult["URL"] = $http . '://' . htmlspecialcharsEx(COption::GetOptionString("main", "server_name", ""));


// list of the element fields that will be used in selection
        $arSelect = array(
          "ID",
          "NAME",
          "IBLOCK_ID",
          "IBLOCK_SECTION_ID",
          "DATE_CREATE",
          "DETAIL_PAGE_URL",
          "DETAIL_TEXT",
          "PREVIEW_TEXT"
        );

        if (!$bCatalog && !empty($arParams["PRICE_CODE"])) {
            $arSelect[] = "PROPERTY_" . $arParams["PRICE_CODE"];
        }

        if ($arParams['MORE_PHOTO']) {
            $arSelect[] = "DETAIL_PICTURE";
            $arSelect[] = "PREVIEW_PICTURE";
        }

        if (is_array($arProperty))
            $arSelect = array_merge($arProperty, $arSelect);

        $arFilter = array(
          "IBLOCK_LID" => SITE_ID,
          "IBLOCK_ID" => $arParams["IBLOCK_ID_IN"],
          "SECTION_ID" => $arParams["IBLOCK_SECTION"],
          "INCLUDE_SUBSECTIONS" => "Y",
          "IBLOCK_ACTIVE" => "Y",
          "ACTIVE_DATE" => "Y",
          "ACTIVE" => "Y",
          "CHECK_PERMISSIONS" => "Y",
          "SECTION_ACTIVE" => "Y", //New bitrix API can't fetch from IBLOCK root with this filter
          "SECTION_GLOBAL_ACTIVE" => "Y"
        );

        if ($arParams['IBLOCK_AS_CATEGORY'] == 'Y') {
            unset($arFilter["SECTION_ACTIVE"]);
            unset($arFilter["SECTION_GLOBAL_ACTIVE"]);
        }

        if ($arParams["DO_NOT_INCLUDE_SUBSECTIONS"] == "Y")
            $arFilter["INCLUDE_SUBSECTIONS"] = "N";

        if ((count($arParams["IBLOCK_SECTION"]) == 1 && $arParams["IBLOCK_SECTION"][0] == 0) ||
            !$arParams["IBLOCK_SECTION"]) {
            unset($arFilter["SECTION_ID"]);
        }

        $arSort = array(
          "ID" => "DESC",
        );


        $i = 0;

//EXECUTE

        if ($arParams["IBLOCK_TYPE"]) {
            $rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("ID" => $arParams["IBLOCK_ID_IN"], "TYPE" => $arParams["IBLOCK_TYPE"], "ACTIVE" => "Y"));
            $arFilter["IBLOCK_TYPE"] = $arParams["IBLOCK_TYPE"];
        }
        else {
            $rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("ID" => $arParams["IBLOCK_ID_IN"], "TYPE" => $arParams["IBLOCK_TYPE_LIST"], "ACTIVE" => "Y"));
            $arFilter["IBLOCK_TYPE"] = $arParams["IBLOCK_TYPE_LIST"];
        }

        $arSKUiblockID = array();

        while ($res = $rsIBlock->GetNext()) {
            if ($arParams['IBLOCK_AS_CATEGORY'] == 'Y') {
                $arResult["CATEGORIES"][$res["ID"]] = Array("ID" => $res["ID"], "NAME" => xml_creator($res["NAME"], true), "CODE" => $res["CODE"]);
            }

            if ($arParams["DONT_USE_SKU"] == "N" || empty($arParams["DONT_USE_SKU"])) {
                if ($bCatalog) {
                    $rsSKU = CCatalog::GetList(array(), array("PRODUCT_IBLOCK_ID" => $res["ID"]), false, false, array("IBLOCK_ID"));
                    if ($arSKUiBlock = $rsSKU->Fetch()) {
                        $arSKUiblockID[$res["ID"]] = $arSKUiBlock["IBLOCK_ID"];
                    }
                    unset($rsSKU);
                }
            }
        }

        unset($rsIBlock);

//fetch sections into categories list
        if ((count($arParams["IBLOCK_SECTION"]) == 1 && $arParams["IBLOCK_SECTION"][0] == 0)) {
            $filter = Array("IBLOCK_TYPE" => $arFilter["IBLOCK_TYPE"], "IBLOCK_ID" => $arParams["IBLOCK_ID_IN"], "ACTIVE" => "Y", "IBLOCK_ACTIVE" => "Y", "GLOBAL_ACTIVE" => "Y");
            $bSections = false;
        }
        else {
            $filter = Array("IBLOCK_TYPE" => $arFilter["IBLOCK_TYPE"], "IBLOCK_ID" => $arParams["IBLOCK_ID_IN"], "ID" => $arParams["IBLOCK_SECTION"], "ACTIVE" => "Y", "IBLOCK_ACTIVE" => "Y", "GLOBAL_ACTIVE" => "Y");
            $bSections = true;
        }

        if ($arParams['IBLOCK_AS_CATEGORY'] == 'Y') {
            unset($filter['ACTIVE']);
            unset($filter['GLOBAL_ACTIVE']);
        }


        $categoriesSelectParent = array("ID", "NAME", "IBLOCK_ID", "CODE", "IBLOCK_SECTION_ID", "LEFT_MARGIN", "RIGHT_MARGIN", "DEPTH_LEVEL");
        $categoriesSelectChilds = array("ID", "NAME", "IBLOCK_ID", "CODE", "IBLOCK_SECTION_ID");
        $db_acc = CIBlockSection::GetList(array("left_margin" => "asc"), $filter, false, $categoriesSelectParent);

        unset($filter["ID"]);
        unset($filter["IBLOCK_ID"]);

        while ($arAcc = $db_acc->Fetch()) {

            $id = $arAcc["IBLOCK_ID"] . $arAcc["ID"];
            if (array_key_exists($id, $arResult["CATEGORIES"]))
                continue;

            $arResult["CATEGORIES"][$id] = Array(
              "ID" => $id,
              "CODE" => $arAcc["CODE"],
              "NAME" => xml_creator($arAcc["NAME"], true),
              "PARENT" => ($arParams['IBLOCK_AS_CATEGORY'] == 'Y') ? $arAcc["IBLOCK_ID"] : NULL,
              "IBLOCK_ID" => $arAcc["IBLOCK_ID"],
              "SHOP_ID" => $arAcc["ID"]
            );

            if ($arParams["DO_NOT_INCLUDE_SUBSECTIONS"] != "Y" && $bSections) {
                $subFilter = array(
                  'IBLOCK_ID' => $arAcc['IBLOCK_ID'],
                  '>LEFT_MARGIN' => $arAcc['LEFT_MARGIN'],
                  '<RIGHT_MARGIN' => $arAcc['RIGHT_MARGIN'],
                  '>DEPTH_LEVEL' => $arAcc['DEPTH_LEVEL']
                );

                $db_sub = CIBlockSection::GetList(array("left_margin" => "asc"), array_merge($filter, $subFilter), false, $categoriesSelectChilds);

                while ($arAcc2 = $db_sub->Fetch()) {
                    $id2 = $arAcc2["IBLOCK_ID"] . $arAcc2["ID"];
                    $arResult["CATEGORIES"][$id2] = Array(
                      "ID" => $id2,
                      "CODE" => $arAcc2["CODE"],
                      "NAME" => xml_creator($arAcc2["NAME"], true),
                      "PARENT" => ($arParams['IBLOCK_AS_CATEGORY'] == 'Y') ? $arAcc2["IBLOCK_ID"] : NULL,
                      "IBLOCK_ID" => $arAcc2["IBLOCK_ID"],
                      "SHOP_ID" => $arAcc2["ID"]
                    );
                    if (IntVal($arAcc2["IBLOCK_SECTION_ID"]) < 1)
                        continue;

                    $key2 = $arAcc2["IBLOCK_ID"] . $arAcc2["IBLOCK_SECTION_ID"];
                    if (!array_key_exists($key2, $arResult["CATEGORIES"]))
                        continue;

                    $arResult["CATEGORIES"][$id2]["PARENT"] = $key2;
                }
                unset($db_sub);
            }
            if (IntVal($arAcc["IBLOCK_SECTION_ID"]) < 1)
                continue;

            $key = $arAcc["IBLOCK_ID"] . $arAcc["IBLOCK_SECTION_ID"];
            if (!array_key_exists($key, $arResult["CATEGORIES"]))
                continue;

            $arResult["CATEGORIES"][$id]["PARENT"] = $key;
        }

        unset($arAcc);
        unset($db_acc);
        //custom category name

        if ($arParams["CATEGORY_NAME_PROPERTY"]) {
            $categoryNameProp = trim($arParams["CATEGORY_NAME_PROPERTY"]);
            foreach ($arResult["CATEGORIES"] as $catID => $catInfo) {
                $categoryNameVal = "";
                if ($catInfo["IBLOCK_ID"]) {
                    $categoryNameVal = CIBlockSection::GetList(array(), array("IBLOCK_ID" => $catInfo["IBLOCK_ID"], "ID" => $catInfo["SHOP_ID"]), false, array("ID", $categoryNameProp), false)->Fetch();
                    if ($categoryNameVal[$categoryNameProp]) {
                        $arResult["CATEGORIES"][$catID]["NAME"] = $categoryNameVal[$categoryNameProp];
                    }
                }
            }
        }
//fetch elements
        $arParams["BIG_CATALOG_PROP"] = trim($arParams["BIG_CATALOG_PROP"]);
        if (!empty($arParams["BIG_CATALOG_PROP"]) and $arParams["SAVE_IN_FILE"] == "Y") {
            $wf_limit = $arParams["BIG_CATALOG_PROP"];

            if (empty($_GET["WF_PAGE"])) {
                unset($_SESSION["WFYM_FINISH"]);
                $arResult["WF_NUM"] = 1;
            }
            else {
                if ($_SESSION["WFYM_FINISH"] == "Yes") {
                    LocalRedirect($APPLICATION->GetCurDir());
                }
                else {
                    $arResult["WF_NUM"] = $_GET["WF_PAGE"];
                }
            }

            $arResult["WF_CURR"] = $wf_limit * $arResult["WF_NUM"];

            $rsElements = CIBlockElement::Getlist($arSort, array_merge($arrFilter, $arFilter), false, array("nPageSize" => $wf_limit, "iNumPage" => $arResult["WF_NUM"]), $arSelect);

            $arResult["WF_FULL"] = $rsElements->SelectedRowsCount();
        }
        else {
            $rsElements = CIBlockElement::Getlist($arSort, array_merge($arrFilter, $arFilter), false, false, $arSelect);
        }

        while ($arOffer = $rsElements->GetNext()) {
            $arOfferID[] = $arOffer["ID"];
            $arOffer["SKU"] = array();
            $arOffers[$arOffer["ID"]] = $arOffer;
        }

        unset($rsElements);

//work with module 'catalog'

        if ($bCatalog && $arParams['PRICE_FROM_IBLOCK'] != 'Y') {
            if (empty($arSKUiblockID)) {
                $arAllID = $arOfferID; //ID of SKU and offers without any SKU
            }
            else {
                if ($arParams["DONT_USE_SKU"] == "N" || empty($arParams["DONT_USE_SKU"])) {
                    //fetch SKU
                    $filterSKU = array("IBLOCK_ID" => $arSKUiblockID, $arParams['SKU_PROPERTY'] => $arOfferID, 'ACTIVE' => 'Y');
                    if (is_array($arrFilterSKU))
                        $filterSKU = array_merge($filterSKU, $arrFilterSKU);
                    $arOfferInOb = CIBlockElement::GetList(array($arParams['SKU_PROPERTY'] => 'DESC'), $filterSKU, false, false, $arSelect);

                    $arAllID = array(); //ID of SKU and offers without any SKU
                    $productKey = $arParams['SKU_PROPERTY'] . '_VALUE';

                    while ($arOfferIn = $arOfferInOb->GetNext()) {
                        $arAllID[] = $arOfferIn["ID"];
                        $productID = $arOfferIn[$productKey];
                        $arOffers[$productID]["SKU"][] = $arOfferIn["ID"];
                        $arOffers[$arOfferIn["ID"]] = $arOfferIn;
                    }
                    unset($arOfferInOb);

                    foreach ($arOfferID as $offerID) {
                        if (empty($arOffers[$offerID]["SKU"]))
                            $arAllID[] = $offerID;
                    }
                }
            }

//opredelenie dostupnosti tovara po odnomu iz trekh algoritmov (zdes tolko pervye dva)
            if ($arParams["AVAILABLE_ALGORITHM"] == "BITRIX_ALGORITHM" or $arParams["AVAILABLE_ALGORITHM"] == "QUANTITY_ZERO" or empty($arParams["AVAILABLE_ALGORITHM"])) {
//process catalog products
                $arProductSelect = array(
                  "ID",
                  "QUANTITY",
                  "QUANTITY_TRACE",
                  "CAN_BUY_ZERO"
                );
                $dbProducts = CCatalogProduct::GetList(array("ID" => "DESC"), array("@ID" => $arAllID), false, false, $arProductSelect);
                while ($tr = $dbProducts->Fetch()) {
                    $arOffers[$tr["ID"]]["AVAIBLE"] = "true";
                    $arOffers[$tr["ID"]]["QUANTITY"] = $tr["QUANTITY"];

                    switch ($arParams["AVAILABLE_ALGORITHM"]) {
                        case "BITRIX_ALGORITHM":default:
                            if ($tr["QUANTITY_TRACE"] == "N")//esli otklyuchen uchet - dostupen
                                continue;
//esli vklyuchen uchet
                            if ($tr["QUANTITY"] > 0)//esli kol-vo > 0 - dostupen
                                continue;
                            if ($tr["CAN_BUY_ZERO"] == "Y")//esli mozhno pokupat pri kol-ve 0 - dostupen
                                continue;
                            $arOffers[$tr["ID"]]["AVAIBLE"] = "false";
                            break;
                        case "QUANTITY_ZERO":
                            if ($tr["QUANTITY"] > 0)//esli kol-vo 0 - dostupen
                                continue;
                            $arOffers[$tr["ID"]]["AVAIBLE"] = "false";
                            break;
                    }
                }
                unset($tr);
                unset($dbProducts);
            }
            //NEW
            foreach ($arOffers as $newKey => $newVal) {
                if (empty($newVal["AVAIBLE"]) or $newVal["AVAIBLE"] == "")
                    $arOffers[$newKey]["AVAIBLE"] = "false";
            }

//fetch price types

            $priceFilter = array("NAME" => $arParams["PRICE_CODE"]);
            if ($arParams["DONT_CHECK_PRICE_RIGHTS"] == "N" or empty($arParams["DONT_CHECK_PRICE_RIGHTS"])) {
                $priceFilterRights = array("CAN_BUY" => "Y");
                $priceFilter = array_merge($priceFilter, $priceFilterRights);
            }
            $dbPriceTypes = CCatalogGroup::GetList(array("SORT" => "ASC"), $priceFilter);

            while ($arPriceType = $dbPriceTypes->Fetch()) {
                $arPriceTypesID[] = $arPriceType['ID'];
            }
            unset($dbPriceTypes);

//fetch and process product prices
            $arPriceSelect = array('PRODUCT_ID', 'PRICE', 'CURRENCY', 'CATALOG_GROUP_ID');
            $dbProductPrices = CPrice::GetList(["PRICE" => "ASC"], array("@PRODUCT_ID" => $arAllID, "@CATALOG_GROUP_ID" => $arPriceTypesID), false, false, $arPriceSelect);

            $arPrices = array();
            //Get price rounding params START
            if ($arParams["PRICE_ROUND"] == "Y") {
                //round?
                $roundSettings["ROUND"] = "Y";
                //minimum round price
                $roundSettings["MINIMUM_PRICE_ROUND"] = $arParams["MINIMUM_PRICE_ROUND"] ? abs($arParams["MINIMUM_PRICE_ROUND"]) : 0;
                //type round price
                $roundSettings["TYPE_PRICE_ROUND"] = $arParams["TYPE_PRICE_ROUND"]? : "MATH";
                //accuracy
                $roundSettings["ACCURACY_PRICE_ROUND"] = $arParams["ACCURACY_PRICE_ROUND"] ? WFYMRoundPrices::getPriceAccuracy($arParams["ACCURACY_PRICE_ROUND"]) : 1;
            }
            else {
                $roundSettings = array("ROUND" => "N");
            }

            //Get price rounding params END
            if (count($arPriceTypesID) > 1) {
                $arProductPrice = $dbProductPrices->GetNext();
                $product_id = $arProductPrice["PRODUCT_ID"];

                $arPrices[] = $arProductPrice;
                while ($arProductPrice = $dbProductPrices->GetNext()) {
                    if ($arProductPrice["PRODUCT_ID"] != $product_id) {
                        webfly_ymarket_GetPrice($product_id, $arPrices, $arOffers, $roundSettings, $arParams["OLD_PRICE"]);

                        $product_id = $arProductPrice["PRODUCT_ID"];
                        $arPrices = array();
                    }
                    $arPrices[] = $arProductPrice;
                }

                webfly_ymarket_GetPrice($product_id, $arPrices, $arOffers, $roundSettings, $arParams["OLD_PRICE"]);
            }
            /* else if ($arParams["DISCOUNTS"] == 'PRICE_ONLY') {
              while ($arPrice = $dbProductPrices->GetNext()) {
              $arOffers[$arPrice["PRODUCT_ID"]]["PRICE"] = $arPrice["PRICE"];
              $arOffers[$arPrice["PRODUCT_ID"]]["CURRENCY"] = $arPrice["CURRENCY"];
              }
              } */
            else {
                $arAllPricesHolder = array();
                while ($tmpPrice = $dbProductPrices->GetNext()) {
                    $arPrices[0]["PRODUCT_ID"] = $tmpPrice["PRODUCT_ID"];
                    $arPrices[0]["PRICE"] = $tmpPrice["PRICE"];
                    $arPrices[0]["CURRENCY"] = $tmpPrice["CURRENCY"];
                    $arPrices[0]["CATALOG_GROUP_ID"] = $tmpPrice["CATALOG_GROUP_ID"];
                    $arAllPricesHolder[] = $arPrices;
                    unset($tmpPrice);
                }
                unset($arPrices);

                $arr_length = count($arAllPricesHolder);
                for ($i = 0; $i < $arr_length; $i++) {
                    webfly_ymarket_GetPrice($arAllPricesHolder[$i][0]["PRODUCT_ID"], $arAllPricesHolder[$i], $arOffers, $roundSettings, $arParams["OLD_PRICE"]);
                }
                unset($arAllPricesHolder);
            }
            unset($dbProductPrices);
            CCatalogDiscount::ClearDiscountCache(array('SECTIONS' => 'Y', 'SECTION_CHAINS' => 'Y'));
        }

        $arResult['OFFER'] = array();
        $arResult['CURRENCIES'] = array();

        //CPA_SHOP
        if (is_numeric($arParams["CPA_SHOP"]))
            $arResult["CPA_SHOP"] = trim($arParams["CPA_SHOP"]);

        /** 1.4.6 */
        $marketCatTable = null;
        if (!empty($arParams['MARKET_CATEGORY_PROP']) && $hlBlock ) {
            $prop_res = CIBlockProperty::GetList([],["CODE"=>$arParams['MARKET_CATEGORY_PROP']]);
            if ($prop = $prop_res->Fetch()) {
                if ($prop["PROPERTY_TYPE"] == "S") {
                    $hl_res = \Bitrix\Highloadblock\HighloadBlockTable::getList(['filter'=>['TABLE_NAME'=>$prop["USER_TYPE_SETTINGS"]["TABLE_NAME"]]]);
                    if ($hl = $hl_res->Fetch()) {
                        $hlentity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hl);
                        $marketCatTable = $hlentity->GetDataClass();
                    }
                }
            }
        }
        /** 1.4.6  END */

        /* OFFER ITERATION */
        foreach ($arOfferID as &$offerID) {
            $arOffer = & $arOffers[$offerID];


//setting offer pictures
            //6.03.2017
            if ($arParams["USE_ONLY_PROP_PICTURE"] !== "Y") {
                $main_picture = $arOffer["DETAIL_PICTURE"];
                $add_picture = $arOffer["PREVIEW_PICTURE"];
                if ($arParams["GET_OVER_FIELDS_ANONCE"] == "Y") {
                    $main_picture = $arOffer["PREVIEW_PICTURE"];
                    $add_picture = $arOffer["DETAIL_PICTURE"];
                }
                if ($main_picture) {
                    $db_file = CFile::GetByID($main_picture);
                    if ($ar_file = $db_file->Fetch())
                        $arOffer["PICTURE"] = $ar_file["~src"] ? $ar_file["~src"] : $http . "://" . $server_name . "/" . ( COption::GetOptionString("main", "upload_dir", "upload")) . "/" . $ar_file["SUBDIR"] . "/" . implode("/", array_map("rawurlencode", explode("/", $ar_file["FILE_NAME"])));
                    unset($ar_file);
                    unset($db_file);
                }

                if ($add_picture && !$arOffer["PICTURE"]) {
                    $db_file = CFile::GetByID($add_picture);
                    if ($ar_file = $db_file->Fetch())
                        $arOffer["PICTURE"] = $ar_file["~src"] ? $ar_file["~src"] : $http . "://" . $server_name . "/" . (COption::GetOptionString("main", "upload_dir", "upload")) . "/" . $ar_file["SUBDIR"] . "/" . implode("/", array_map("rawurlencode", explode("/", $ar_file["FILE_NAME"])));
                    unset($ar_file);
                    unset($db_file);
                }
//6.03.2017
            }

            /* MORE PHOTO START */
            if (!empty($arParams["MORE_PHOTO"]) && $arParams["MORE_PHOTO"] != "WF_EMPT") {
                $ph = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("value_id" => "asc"), Array("CODE" => $arParams["MORE_PHOTO"]));
                $arOffer["MORE_PHOTO"] = array();

                while (($ob = $ph->GetNext()) && count($arOffer["MORE_PHOTO"]) < 10) {
                    $arFile = CFile::GetFileArray($ob["VALUE"]);
                    if (!empty($arFile)) {//file proprty
                        if (strpos($arFile["SRC"], $http) === false) {
                            $pic = $http . "://" . $server_name . implode("/", array_map("rawurlencode", explode("/", $arFile["SRC"])));
                        }
                        else {
                            $ar = explode($http . "://", $arFile["SRC"]);
                            $pic = $http . "://" . implode("/", array_map("rawurlencode", explode("/", $ar[1])));
                        }
                    }
                    else {//another property
                        $ob["VALUE"] = parse_url($ob["VALUE"]);
                        if ($ob["VALUE"]["path"])
                            $ob["VALUE"]["path"] = implode("/", array_map("rawurlencode", explode("/", $ob["VALUE"]["path"])));
                        $pic = unparse_url($ob["VALUE"]);
                    }
                    $arOffer["MORE_PHOTO"][] = $pic;
                    unset($ob);
                }
                unset($ph);

                if (!$arOffer["PICTURE"] && is_array($arOffer["MORE_PHOTO"]))
                    $arOffer['PICTURE'] = array_shift($arOffer["MORE_PHOTO"]);
                $arOffer["MORE_PHOTO"] = array_slice($arOffer["MORE_PHOTO"], 0, 9);
            }
            /* MORE PHOTO END */

            //url from prop
            if ($arParams["URL_PROPERTY_CHECK"] == "Y" && !empty($arParams["URL_PROPERTY"])) {
                $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams["URL_PROPERTY"]))->GetNext();
                $dispProp = CIBlockFormatProperties::GetDisplayValue($arOffer, $arProps, "wf_ymout");
                $arProps["URL_VAL"] = $dispProp["VALUE_ENUM"] ? xml_creator($dispProp["VALUE_ENUM"], true) : xml_creator($dispProp["DISPLAY_VALUE"], true);
                $arProps["URL_VAL"] = strip_tags($arProps["URL_VAL"]);
                if (substr_count($arProps["URL_VAL"], "a href") > 0) {
                    $arProps["URL_VAL"] = htmlspecialcharsBack($arProps["URL_VAL"]);
                    $arProps["URL_VAL"] = strip_tags($arProps["URL_VAL"]);
                    $arProps["URL_VAL"] = xml_creator($arProps["URL_VAL"], true);
                }
                $arOffer["DETAIL_PAGE_URL_FROM_PROP"] = $arProps["URL_VAL"];
                unset($arProps);
                unset($dispProp);
            }

            /* UTM START */
            if ($arParams ["UTM_CHECK"] == "Y") {
//Take utm-source properties
                if (!empty($arParams["UTM_SOURCE"]) and $arParams["UTM_SOURCE"] != "0") {
                    $isExistProp = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array("CODE" => $arParams["UTM_SOURCE"]))->Fetch();
                    if ($isExistProp) {
                        $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams["UTM_SOURCE"]))->GetNext();
                        $dispProp = CIBlockFormatProperties::GetDisplayValue($arOffer, $arProps, "wf_ymout");
                        $arOffer["UTM_SOURCE"] = $dispProp["VALUE_ENUM"] ? xml_creator($dispProp["VALUE_ENUM"], true) : xml_creator($dispProp["DISPLAY_VALUE"], true);
                        $arOffer["UTM_SOURCE"] = strip_tags($arOffer["UTM_SOURCE"]);
                        if (substr_count($arOffer["UTM_SOURCE"], "a href") > 0) {
                            $arOffer["UTM_SOURCE"] = htmlspecialcharsBack($arOffer["UTM_SOURCE"]);
                            $arOffer["UTM_SOURCE"] = strip_tags($arOffer["UTM_SOURCE"]);
                            $arOffer["UTM_SOURCE"] = xml_creator($arOffer["UTM_SOURCE"], true);
                        }
                    }
                    else {
                        $arOffer["UTM_SOURCE"] = xml_creator($arParams["UTM_SOURCE"], true);
                        $arOffer["UTM_SOURCE"] = strip_tags($arOffer["UTM_SOURCE"]);
                    }

                    if ($arOffer["UTM_SOURCE"] == false)
                        $arOffer["UTM_SOURCE"] = "";
                    unset($arProps);
                    unset($dispProp);
                }

//Take utm-campaign properties
                if ($arParams ["UTM_CAMPAIGN"] == "0" or empty($arParams ["UTM_CAMPAIGN"])) {
                    $wf_arGr = CIBlockElement::GetElementGroups($arOffer["ID"]);
                    $wf_ar_group = $wf_arGr->Fetch();
                    $wf_groupid = $wf_ar_group["ID"];
                    $res = CIBlockSection::GetByID($wf_groupid);
                    if ($ar_res = $res->GetNext())
                        $group_code = $ar_res['CODE'];
                    $group_code = xml_creator($group_code, true);
                    $arOffer["UTM_CAMPAIGN"] = $group_code;
                    unset($res);
                    unset($wf_arGr);
                    unset($wf_ar_group);
                    if ($arParams["IBLOCK_AS_CATEGORY"] == 'Y' and empty($group_code)) {
                        $arOffer["UTM_CAMPAIGN"] = $arResult["CATEGORIES"][$arOffer["IBLOCK_ID"]]["CODE"];
                    }
                    if ($arOffer["UTM_CAMPAIGN"] == false)
                        $arOffer["UTM_CAMPAIGN"] = "";
                }
                else {
                    $isExistProp = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array("CODE" => $arParams["UTM_CAMPAIGN"]))->Fetch();
                    if ($isExistProp) {
                        $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams["UTM_CAMPAIGN"]))->GetNext();
                        $dispProp = CIBlockFormatProperties::GetDisplayValue($arOffer, $arProps, "wf_ymout");
                        $arOffer["UTM_CAMPAIGN"] = $dispProp["VALUE_ENUM"] ? xml_creator($dispProp["VALUE_ENUM"], true) : xml_creator($dispProp["DISPLAY_VALUE"], true);
                        $arOffer["UTM_CAMPAIGN"] = strip_tags($arOffer["UTM_CAMPAIGN"]);
                    }
                    else {
                        $arOffer["UTM_CAMPAIGN"] = xml_creator($arParams["UTM_CAMPAIGN"], true);
                        $arOffer["UTM_CAMPAIGN"] = strip_tags($arOffer["UTM_CAMPAIGN"]);
                    }

                    if (substr_count($arOffer["UTM_CAMPAIGN"], "a href") > 0)
                        $arOffer["UTM_CAMPAIGN"] = xml_creator($arOffer["UTM_CAMPAIGN"], true);
                    if ($arOffer["UTM_CAMPAIGN"] == false)
                        $arOffer["UTM_CAMPAIGN"] = "";
                    unset($arProps);
                    unset($dispProp);
                }

//Take utm-medium properties
                if (!empty($arParams["UTM_MEDIUM"]) and $arParams["UTM_MEDIUM"] != "0") {
                    $isExistProp = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array("CODE" => $arParams["UTM_MEDIUM"]))->Fetch();
                    if ($isExistProp) {
                        $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams["UTM_MEDIUM"]))->GetNext();
                        $dispProp = CIBlockFormatProperties::GetDisplayValue($arOffer, $arProps, "wf_ymout");
                        $arOffer["UTM_MEDIUM"] = $dispProp["VALUE_ENUM"] ? xml_creator($dispProp["VALUE_ENUM"], true) : xml_creator($dispProp["DISPLAY_VALUE"], true);
                        if (substr_count($arOffer["UTM_MEDIUM"], "a href") > 0) {
                            $arOffer["UTM_CAMPAIGN"] = strip_tags($arOffer["UTM_CAMPAIGN"]);
                        }
                    }
                    else {
                        $arOffer["UTM_MEDIUM"] = xml_creator($arParams["UTM_MEDIUM"], true);
                    }
                    $arOffer["UTM_MEDIUM"] = strip_tags($arOffer["UTM_MEDIUM"]);
                    if (substr_count($arOffer["UTM_MEDIUM"], "a href") > 0)
                        $arOffer["UTM_MEDIUM"] = xml_creator($arOffer["UTM_MEDIUM"], true);
                    if ($arOffer["UTM_MEDIUM"] == false)
                        $arOffer["UTM_MEDIUM"] = "";
                    unset($arProps);
                    unset($dispProp);
                }

//Take utm-term properties
                if (!empty($arParams["UTM_TERM"]) and $arParams["UTM_TERM"] != "0") {
                    if ($arParams["UTM_TERM"] == "WEBFLY_ID") {
                        $arOffer["UTM_TERM"] = $arOffer["ID"];
                    }
                    else {
                        $isExistProp = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array("CODE" => $arParams["UTM_TERM"]))->Fetch();
                        if ($isExistProp) {
                            $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams["UTM_TERM"]))->GetNext();
                            $dispProp = CIBlockFormatProperties::GetDisplayValue($arOffer, $arProps, "wf_ymout");
                            $arOffer["UTM_TERM"] = $dispProp["VALUE_ENUM"] ? xml_creator($dispProp["VALUE_ENUM"], true) : xml_creator($dispProp["DISPLAY_VALUE"], true);
                            $arOffer["UTM_TERM"] = strip_tags($arOffer["UTM_TERM"]);
                            if (substr_count($arOffer["UTM_TERM"], "a href") > 0) {
                                $arOffer["UTM_TERM"] = htmlspecialcharsBack($arOffer["UTM_TERM"]);
                                $arOffer["UTM_TERM"] = strip_tags($arOffer["UTM_TERM"]);
                                $arOffer["UTM_TERM"] = xml_creator($arOffer["UTM_TERM"], true);
                            }
                        }
                        else {
                            $arOffer["UTM_TERM"] = xml_creator($arParams["UTM_TERM"], true);
                            $arOffer["UTM_TERM"] = strip_tags($arOffer["UTM_TERM"]);
                        }
                    }

                    if ($arOffer["UTM_TERM"] == false)
                        $arOffer["UTM_TERM"] = "";
                    unset($arProps);
                    unset($dispProp);
                }
                else {
                    $arOffer["UTM_TERM"] = $arOffer["CODE"];
                }

//offer URL
                //proverka na nalichie get-aparametrov v iskhodnom urle
                if (substr_count($arOffer["DETAIL_PAGE_URL"], "?") > 0)
                    $symbol = "&amp;";
                else
                    $symbol = "?";
                if (empty($arOffer["UTM_SOURCE"]))
                    $utm_source = "";
                else
                    $utm_source = $symbol . "utm_source=" . strip_tags($arOffer["UTM_SOURCE"]);

                if (empty($arOffer["UTM_CAMPAIGN"])) {
                    $utm_campaign = "";
                }
                else {
                    if (empty($arOffer["UTM_SOURCE"]))
                        $utm_campaign = $symbol . "utm_campaign=" . strip_tags($arOffer["UTM_CAMPAIGN"]);
                    else
                        $utm_campaign = "&amp;utm_campaign=" . strip_tags($arOffer["UTM_CAMPAIGN"]);
                }

                if (empty($arOffer["UTM_MEDIUM"])) {
                    $utm_medium = "";
                }
                else {
                    if (empty($arOffer["UTM_CAMPAIGN"]) and empty($arOffer["UTM_SOURCE"]))
                        $utm_medium = $symbol . "utm_medium=" . strip_tags($arOffer["UTM_MEDIUM"]);
                    else
                        $utm_medium = "&amp;utm_medium=" . strip_tags($arOffer["UTM_MEDIUM"]);
                }

                if (empty($arOffer["UTM_TERM"])) {
                    $utm_term = "";
                }
                else {
                    if (empty($arOffer["UTM_MEDIUM"]) and empty($arOffer["UTM_CAMPAIGN"]) and empty($arOffer["UTM_SOURCE"]))
                        $utm_term = $symbol . "utm_term=" . strip_tags($arOffer["UTM_TERM"]);
                    else
                        $utm_term = "&amp;utm_term=" . strip_tags($arOffer["UTM_TERM"]);
                }

                if (!empty($arOffer["DETAIL_PAGE_URL_FROM_PROP"])) {
                    if ($arParams["URL_PROPERTY_WITH_DOMEN"] == "Y")
                        $arOffer["URL"] = $http . "://" . $server_name . $arOffer["DETAIL_PAGE_URL_FROM_PROP"] . $utm_source . $utm_campaign . $utm_medium . $utm_term;
                    else
                        $arOffer["URL"] = $arOffer["DETAIL_PAGE_URL_FROM_PROP"] . $utm_source . $utm_campaign . $utm_medium . $utm_term;
                }else {
                    $arOffer["URL"] = $http . "://" . $server_name . $arOffer["DETAIL_PAGE_URL"] . $utm_source . $utm_campaign . $utm_medium . $utm_term;
                }
            }
            else {
//offer URL
                if (!empty($arOffer["DETAIL_PAGE_URL_FROM_PROP"])) {
                    if ($arParams["URL_PROPERTY_WITH_DOMEN"] == "Y")
                        $arOffer["URL"] = $http . "://" . $server_name . $arOffer["DETAIL_PAGE_URL_FROM_PROP"];
                    else
                        $arOffer["URL"] = $arOffer["DETAIL_PAGE_URL_FROM_PROP"];
                }else {
                    $arOffer["URL"] = $http . "://" . $server_name . $arOffer["DETAIL_PAGE_URL"];
                }
            }
            /* UTM END */
// LOCAL_DELIVERY_COST_OFFER, STORE_OFFER, PICKUP_OFFER, typePrefix, PROP_ALGORITHM_VALUE, DESCRIPTION
// PROPDUCT_PROP - dopolnitelnye svojstva tovarov vyvodimye v opisanii
//            $propsArray = array("LOCAL_DELIVERY_COST_OFFER", "STORE_OFFER", "STORE_PICKUP", "ADULT", "PREFIX_PROP", "PROPDUCT_PROP", "DELIVERY_OPTIONS_EX", "STORE_DELIVERY", "OUTLETS", "AGE_CATEGORY", "BID", "CBID", "FEE", "CPA_OFFERS", "EXPIRY", "WEIGHT", "DIMENSIONS", "RECOMMENDATION", "BARCODE");
            // remove CPA
            $propsArray = array("LOCAL_DELIVERY_COST_OFFER", "STORE_OFFER", "STORE_PICKUP", "ADULT", "PREFIX_PROP", "PROPDUCT_PROP", "DELIVERY_OPTIONS_EX", "STORE_DELIVERY", "OUTLETS", "AGE_CATEGORY", "EXPIRY", "WEIGHT", "DIMENSIONS", "RECOMMENDATION", "BARCODE");
//esli vybran algoritm opredeleniya dostupnosti tovara iz svojstva
            if ($arParams["AVAILABLE_ALGORITHM"] == "PROP_ALGORITHM")
                $propsArray[] = "PROP_ALGORITHM_VALUE";
            if (!empty($arParams["DESCRIPTION"]) and $arParams["NO_DESCRIPTION"] != "Y")
                $propsArray[] = "DESCRIPTION";
            foreach ($propsArray as $propKey => $propVal) {
                if (!empty($arParams[$propVal])) {
// dopolnitelnye svojstva tovarov vyvodimye v opisanii
                    switch ($propVal) {
                        case "PROPDUCT_PROP":
                            foreach ($arParams[$propVal] as $key => $productProp) {
                                if (!empty($productProp)) {
                                    $productProp = trim($productProp);
                                    $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $productProp))->GetNext();

//nazvanie
                                    $arProps["NAME"] = xml_creator($arProps["NAME"], true);

//znachenie
                                    $dispProp = CIBlockFormatProperties::GetDisplayValue($arOffer, $arProps, "wf_ymout");
                                    $arProps["VAL"] = ($dispProp["VALUE_ENUM"] or $dispProp["VALUE_ENUM"] === "0") ? xml_creator($dispProp["VALUE_ENUM"], true) : xml_creator($dispProp["DISPLAY_VALUE"], true);
                                    $arProps["VAL"] = strip_tags($arProps["VAL"]);
                                    if (substr_count($arProps["VAL"], "a href") > 0) {
                                        $arProps["VAL"] = htmlspecialcharsBack($arProps["VAL"]);
                                        $arProps["VAL"] = strip_tags($arProps["VAL"]);
                                        $arProps["VAL"] = xml_creator($arProps["VAL"], true);
                                    }
//nakoplenie v peremennuyu
                                    if (!empty($arProps["VAL"])) {
                                        if (empty($arOffer["DOP_PROPS"]))
                                            $arOffer["DOP_PROPS"] = $arProps["NAME"] . ": " . $arProps["VAL"];
                                        else
                                            $arOffer["DOP_PROPS"] = $arOffer["DOP_PROPS"] . ", " . $arProps["NAME"] . ": " . $arProps["VAL"];
                                    }
                                    unset($arProps);
                                }
                            }
                            break;
                        case "DELIVERY_OPTIONS_EX":
                            if ($productDeliv) {
                                foreach ($productDeliv as $dkey => $dProp) {
                                    foreach ($dProp as $dk => $dv) {
                                        if (!empty($dv)) {
                                            $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $dv))->GetNext();
                                            $dispProp = CIBlockFormatProperties::GetDisplayValue($arOffer, $arProps, "wf_ymout");
                                            if ($dispProp["VALUE_ENUM"] or $dispProp["VALUE_ENUM"] === "0")
                                                $arOffer[$propVal][$dkey][$dk] = xml_creator($dispProp["VALUE_ENUM"], true);
                                            else
                                                $arOffer[$propVal][$dkey][$dk] = xml_creator($dispProp["DISPLAY_VALUE"], true);
                                            $arOffer[$propVal][$dkey][$dk] = strip_tags($arOffer[$propVal][$dkey][$dk]);
                                            if (substr_count($arOffer[$propVal][$dkey][$dk], "a href") > 0) {
                                                $arOffer[$propVal][$dkey][$dk] = htmlspecialcharsBack($arOffer[$propVal][$dkey][$dk]);
                                                $arOffer[$propVal][$dkey][$dk] = strip_tags($arOffer[$propVal][$dkey][$dk]);
                                                $arOffer[$propVal][$dkey][$dk] = xml_creator($arOffer[$propVal][$dkey][$dk], true);
                                            }
                                        }
                                    }
                                }
                            }
                            break;
                        case "OUTLETS":
                            if ($outlets) {
                                foreach ($outlets as $outKey => $outProp) {
                                    foreach ($outProp as $outk => $outv) {
                                        $productAmount = "";
                                        $amountPropCode = "";
                                        if (substr_count($outv, "WFYMAMOUNT") > 0) {//value from amount//amount
                                            switch ($outk) {
                                                case "0"://amount ID
                                                    $arOffer[$propVal][$outKey][$outk] = str_replace("_WFYMAMOUNT", "", $outv);
                                                    break;
                                                case "1"://products quantity in amount
                                                    $productAmount = CCatalogStoreProduct::GetList(array("sort" => "asc"), array("ACTIVE" => "Y", "SITE_ID" => SITE_ID, "STORE_ID" => str_replace("_WFYMAMOUNT", "", $outv), "PRODUCT_ID" => $arOffer["ID"]), false, false, array("AMOUNT"))->Fetch();
                                                    if (!empty($productAmount["AMOUNT"])) {
                                                        if (substr_count($productAmount["AMOUNT"], "-") > 0)
                                                            $productAmount["AMOUNT"] = "0";
                                                        $productAmount = intval($productAmount["AMOUNT"]);
                                                    }else {
                                                        $productAmount = 0;
                                                    }
                                                    $arOffer[$propVal][$outKey][$outk] = $productAmount;
                                                    break;
                                                case "2":
                                                    $arOffer[$propVal][$outKey][$outk] = "";
                                                    break;
                                            }
                                        }
                                        if (substr_count($outv, "WFYMPROP") > 0) {//value from property
                                            $amountPropCode = str_replace("_WFYMPROP", "", $outv);
                                            $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $amountPropCode))->GetNext();
                                            $dispProp = CIBlockFormatProperties::GetDisplayValue($arOffer, $arProps, "wf_ymout");
                                            if ($dispProp["VALUE_ENUM"] or $dispProp["VALUE_ENUM"] === "0")
                                                $arOffer[$propVal][$outKey][$outk] = xml_creator($dispProp["VALUE_ENUM"], true);
                                            else
                                                $arOffer[$propVal][$outKey][$outk] = xml_creator($dispProp["DISPLAY_VALUE"], true);
                                            $arOffer[$propVal][$outKey][$outk] = strip_tags($arOffer[$propVal][$outKey][$outk]);
                                            if (substr_count($arOffer[$propVal][$outKey][$outk], "a href") > 0) {
                                                $arOffer[$propVal][$outKey][$outk] = htmlspecialcharsBack($arOffer[$propVal][$outKey][$outk]);
                                                $arOffer[$propVal][$outKey][$outk] = strip_tags($arOffer[$propVal][$outKey][$outk]);
                                                $arOffer[$propVal][$outKey][$outk] = xml_creator($arOffer[$propVal][$outKey][$outk], true);
                                            }
                                        }
                                        if (substr_count($outv, "WFYMAMOUNT") == 0 and substr_count($outv, "WFYMPROP") == 0) {//write
                                            $arOffer[$propVal][$outKey][$outk] = $outv;
                                        }
                                    }
                                }
                            }
                            break;
                        //rec for offer - multiple prop
                        case "RECOMMENDATION":case "BARCODE":
                            $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams[$propVal]));
                            $multItem = array();
                            while ($ob_props = $arProps->GetNext()) {
                                $multItem[] = $ob_props["VALUE"];
                            }
                            if ($propVal == "RECOMMENDATION")
                                $arOffer[$propVal] = implode(", ", $multItem);
                            else
                                $arOffer[$propVal] = $multItem;
                            unset($arProps);
                            break;
                        default:
                            if ($propVal == "STORE_OFFER" or $propVal == "STORE_PICKUP" or $propVal == "STORE_DELIVERY" or $propVal == "ADULT") {//vozmozhnost vpisyvaniya
                                $isExistProp = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array("CODE" => $arParams[$propVal]))->Fetch();
                            }
                            if (($propVal != "STORE_OFFER" and $propVal != "STORE_PICKUP" and $propVal != "STORE_DELIVERY" and $propVal != "ADULT") or ( ($propVal == "STORE_OFFER" or $propVal == "STORE_PICKUP" or $propVal == "STORE_DELIVERY" or $propVal == "ADULT") and $isExistProp)) {//vozmozhnost vpisyvaniya
                                if ($propVal == "WEIGHT" and $arParams[$propVal] == "WEBFLY_WEIGHT") {
                                    $productWeight = CIBlockElement::GetList(array(), array("IBLOCK_ID" => $arOffer["IBLOCK_ID"], "ID" => $arOffer["ID"]), false, false, array("ID", "CATALOG_WEIGHT"))->Fetch();
                                    if ($productWeight["CATALOG_WEIGHT"]) {
                                        $dispProp["DISPLAY_VALUE"] = round($productWeight["CATALOG_WEIGHT"] / 1000, 3);
                                        $dispProp["DISPLAY_VALUE"] = sprintf("%.03f", $dispProp["DISPLAY_VALUE"]);
                                    }
                                }
                                elseif ($propVal == "DIMENSIONS" and $arParams[$propVal] == "WEBFLY_DIMENSIONS") {//1.4.2
                                    $pdimensions = CIBlockElement::GetList(array(), array("IBLOCK_ID" => $arOffer["IBLOCK_ID"], "ID" => $arOffer["ID"]), false, false, array("ID", "CATALOG_LENGTH", "CATALOG_WIDTH", "CATALOG_HEIGHT"))->Fetch();
                                    if ($pdimensions["CATALOG_LENGTH"] != 0 && $pdimensions["CATALOG_WIDTH"] != 0 && $pdimensions["CATALOG_HEIGHT"] != 0) {
                                        $allDmns = array("CATALOG_LENGTH" => $pdimensions["CATALOG_LENGTH"], "CATALOG_WIDTH" => $pdimensions["CATALOG_WIDTH"], "CATALOG_HEIGHT" => $pdimensions["CATALOG_HEIGHT"]);
                                        foreach ($allDmns as $dimk => $dmn) {
                                            $allDmns[$dimk] = sprintf("%.03f", round($dmn / 10, 3));
                                        }
                                        $dispProp["DISPLAY_VALUE"] = implode($allDmns, "/");
                                    }
                                }
                                else {
                                    $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams[$propVal]))->GetNext();
                                    $dispProp = CIBlockFormatProperties::GetDisplayValue($arOffer, $arProps, "wf_ymout");
                                }


                                if ($propVal == "DESCRIPTION") {
                                    $descriptionVal = "";
                                    if ($dispProp["~VALUE"]["TEXT"])
                                        $descriptionVal = $dispProp["~VALUE"]["TEXT"];
                                    else
                                        $descriptionVal = $dispProp["~VALUE"];
                                    if (!empty($descriptionVal)) {
                                        if ($arParams["DESCRIPTION_XHTML"] == "Y")
                                            $arOffer[$propVal] = xhtml_modifier($descriptionVal);
                                        else
                                            $arOffer[$propVal] = xml_creator(preg_replace_callback("'&[^;]*;'", "charset_modifier", strip_tags($descriptionVal)), true);
                                    }
                                }
                                else {
                                    $arOffer[$propVal] = ($dispProp["VALUE_ENUM"] or $dispProp["VALUE_ENUM"] === "0") ? xml_creator($dispProp["VALUE_ENUM"], true) : xml_creator($dispProp["DISPLAY_VALUE"], true);
                                    $arOffer[$propVal] = strip_tags($arOffer[$propVal]);
                                    if (substr_count($arOffer[$propVal], "a href") > 0) {
                                        $arOffer[$propVal] = htmlspecialcharsBack($arOffer[$propVal]);
                                        $arOffer[$propVal] = strip_tags($arOffer[$propVal]);
                                        $arOffer[$propVal] = xml_creator($arOffer[$propVal], true);
                                    }
                                }
                            }
                            if (($propVal == "STORE_OFFER" or $propVal == "STORE_PICKUP" or $propVal == "STORE_DELIVERY" or $propVal == "ADULT") and ! $isExistProp) {//vozmozhnost vpisyvaniya
                                $arOffer[$propVal] = xml_creator($arParams[$propVal], true);
                            }

//dostupnost tovarov
                            if ($propVal == "PROP_ALGORITHM_VALUE") {
                                $arOffer["AVAIBLE"] = "false";
                                if ($arOffer[$propVal] == "true" or $arOffer[$propVal] == "Y" or $arOffer[$propVal] == "1")
                                    $arOffer["AVAIBLE"] = "true";
                                if ($arOffer[$propVal] == "false" or $arOffer[$propVal] == "N" or $arOffer[$propVal] == "0" or empty($arOffer[$propVal]))
                                    $arOffer["AVAIBLE"] = "false";
                            }
                            break;
                    }
                }
                //otdelnaya vyborka informacii o dostavke na osnove znacheniya svojstva i ukazannyh parametrov dostavki
                if ($propVal == "STORE_DELIVERY") {
                    if (empty($arOffer[$propVal])) {
                        if ($arParams["LOCAL_DELIVERY_COST"] != ""
                            or ! empty($arOffer["LOCAL_DELIVERY_COST_OFFER"])
                            or ( !empty($arOffer["DELIVERY_OPTIONS_EX"]) and count($arOffer["DELIVERY_OPTIONS_EX"]) > 0)
                            or ( !empty($arResult["DELIVERY_OPTION_SHOP"]) and count($arResult["DELIVERY_OPTION_SHOP"]) > 0)
                        ) {
                            $arOffer[$propVal] = "true";
                        }
                    }
                }
                unset($arProps);
                unset($dispProp);
                unset($isExistProp);
            }
            //clear empty outlets
            if ($arOffer["OUTLETS"]) {
                foreach ($arOffer["OUTLETS"] as $ouletsKey => $ouletsVals) {
                    if ($ouletsVals[0] === "" or $ouletsVals[1] === "")
                        unset($arOffer["OUTLETS"][$ouletsKey]);
                }
            }
            //clear invalid delivery-options
            if (!empty($arOffer["DELIVERY_OPTIONS_EX"]) and count($arOffer["DELIVERY_OPTIONS_EX"]) > 0) {
                foreach ($arOffer["DELIVERY_OPTIONS_EX"] as $deliveryKey => $deliveryVals) {
                    if ($deliveryVals[0] === "")
                        unset($arOffer["DELIVERY_OPTIONS_EX"][$deliveryKey]);
                }
            }

            //change delivery options for not available products
            if ($arParams["DELIVERY_TO_AVAILABLE"] == "Y" and $arOffer["AVAIBLE"] == "false") {
                if (!empty($arOffer["DELIVERY_OPTIONS_EX"]) and count($arOffer["DELIVERY_OPTIONS_EX"]) > 0) {
                    foreach ($arOffer["DELIVERY_OPTIONS_EX"] as $deliveryKey2 => $deliveryVals2) {
                        $arOffer["DELIVERY_OPTIONS_EX"][$deliveryKey2][1] = "32";
                    }
                }
                else {
                    if (!empty($arResult["DELIVERY_OPTION_SHOP"])) {
                        foreach ($arResult["DELIVERY_OPTION_SHOP"] as $shopDelKey => $shopDelVals) {
                            $arOffer["DELIVERY_OPTIONS_EX"][$shopDelKey] = $shopDelVals;
                            $arOffer["DELIVERY_OPTIONS_EX"][$shopDelKey][1] = "32";
                        }
                    }
                }
            }
            /* ADDITIONAL PROPS END */
            //NEW_IBLOCK_ORDER
            if ($bCatalog && empty($arOffer["SKU"]) && $arParams['PRICE_FROM_IBLOCK'] != 'Y') {
                if (intval($arOffer["PRICE"]) <= 0 && $arParams['PRICE_REQUIRED'] != 'N')
                    continue;
                if ($arParams["IBLOCK_ORDER"] != "Y" && $arOffer["AVAIBLE"] == "false")
                    continue;
            }
//NEW_IBLOCK_ORDER
            if ($arParams["NO_DESCRIPTION"] != "Y") {
                if (empty($arParams["DESCRIPTION"])) {
                    //setting offer description
                    if ($arParams["DESCRIPTION_XHTML"] == "Y") {
                        if ($arOffer["PREVIEW_TEXT"])
                            $arOffer["PREVIEW_TEXT"] = xhtml_modifier($arOffer["~PREVIEW_TEXT"]);
                        if ($arOffer["DETAIL_TEXT"])
                            $arOffer["DETAIL_TEXT"] = xhtml_modifier($arOffer["~DETAIL_TEXT"]);
                    }
                    else {
                        if ($arOffer["PREVIEW_TEXT"])
                            $arOffer["PREVIEW_TEXT"] = xml_creator(($arOffer["PREVIEW_TEXT_TYPE"] == "html" ? preg_replace_callback("'&[^;]*;'", "charset_modifier", strip_tags($arOffer["~PREVIEW_TEXT"])) : $arOffer["~PREVIEW_TEXT"]), true);

                        if ($arOffer["DETAIL_TEXT"])
                            $arOffer["DETAIL_TEXT"] = xml_creator(($arOffer["DETAIL_TEXT_TYPE"] == "html" ? preg_replace_callback("'&[^;]*;'", "charset_modifier", strip_tags($arOffer["~DETAIL_TEXT"])) : $arOffer["~DETAIL_TEXT"]), true);
                    }

                    if ($arParams["DETAIL_TEXT_PRIORITET"] == "Y") {
                        $arOffer["DESCRIPTION"] = $arOffer["DETAIL_TEXT"] ? $arOffer["DETAIL_TEXT"] : $arOffer["PREVIEW_TEXT"];
                    }
                    else {
                        $arOffer["DESCRIPTION"] = $arOffer["PREVIEW_TEXT"] ? $arOffer["PREVIEW_TEXT"] : $arOffer["DETAIL_TEXT"];
                    }
                }
            }
            else {
                $arOffer["DESCRIPTION"] = '';
            }

            $arOffer["CATEGORY"] = $arOffer["IBLOCK_ID"] . $arOffer["IBLOCK_SECTION_ID"];

            if (!array_key_exists($arOffer["CATEGORY"], $arResult["CATEGORIES"]) && $arOffer["IBLOCK_SECTION_ID"]) {
                $arGr = CIBlockElement::GetElementGroups($arOffer["ID"]);
                while ($ar_group = $arGr->Fetch()) {
                    if (!array_key_exists($arOffer["IBLOCK_ID"] . $ar_group["ID"], $arResult["CATEGORIES"]))
                        continue;
                    $arOffer["CATEGORY"] = $arOffer["IBLOCK_ID"] . $ar_group["ID"];
                    break;
                }
            }

            if ($arParams['SECTION_AS_VENDOR'] == 'Y') {
                if (!empty($arOffer['IBLOCK_SECTION_ID'])) {
                    $arOffer["DEVELOPER"] = $arResult["CATEGORIES"][$arOffer["IBLOCK_ID"] . $arOffer['IBLOCK_SECTION_ID']]["NAME"];
                }
            }

            if ($arParams["MARKET_CATEGORY_CHECK"] == "Y") {
                if (!empty($arParams['MARKET_CATEGORY_PROP'])) {
                    $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], [], ["CODE" => $arParams["MARKET_CATEGORY_PROP"]])->Fetch();

                    /** 1.4.6 */
                    // get name from HLBlock list property
                    if ($hlBlock && $arProps["PROPERTY_TYPE"] == "S" && $marketCatTable) {
                        if ($arProps['VALUE']) {
                            $rsData = $marketCatTable::getList([
                                "select" => array("UF_NAME"),
                                "filter" => array("UF_XML_ID"=>$arProps['VALUE']),
                            ]);
                            if ($arItem = $rsData->Fetch()) {
                                $arProps["VALUE"] = $arItem["UF_NAME"];
                            }
                        }
                    }
                    /** 1.4.6  END */

                    $arOffer["MARKET_CATEGORY"] = $arProps["VALUE_ENUM"] ? $arProps["VALUE_ENUM"] : $arProps["VALUE"];
                    unset($arProps);
                }

                if (!$arOffer["MARKET_CATEGORY"]) {
                    $arGr = CIBlockElement::GetElementGroups($arOffer["ID"]);
                    $ar_group = $arGr->Fetch();
                    $groupid = $ar_group["ID"];

                    $res = CIBlockSection::GetNavChain(false, $groupid);
                    while ($el = $res->GetNext()) {
                        $arOffer["MARKET_CATEGORY"] .= $el['NAME'];
                        $arOffer["MARKET_CATEGORY"] .= "/";
                    }
                    unset($res);
                    unset($arGr);
                    unset($ar_group);
                    if ($arParams["IBLOCK_AS_CATEGORY"] == 'Y') {
                        $arOffer["MARKET_CATEGORY"] = $arResult["CATEGORIES"][$arOffer["IBLOCK_ID"]]["NAME"]
                            . '/'
                            . $arOffer["MARKET_CATEGORY"];
                    }
                    $arOffer["MARKET_CATEGORY"] = substr($arOffer["MARKET_CATEGORY"], 0, -1);
                }
            }

//setting offer name
            if (empty($arParams["NAME_PROP_COMPILE"])) {//esli ne sostavnoe nazvanie
                if (!empty($arParams['NAME_PROP'])) {
                    $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams['NAME_PROP']))->Fetch();
                    $arOffer["MODEL"] = $arProps["VALUE_ENUM"] ? $arProps["VALUE_ENUM"] : $arProps["VALUE"];
                    unset($arProps);
                }

                if (empty($arOffer["MODEL"])) {
                    $arOffer["MODEL"] = $arOffer["~NAME"];
                }
            }
            else {//sbor sostavnogo nazvaniya
                foreach ($nameSelects as $selKey => $selVal) {
                    switch ($selVal) {
                        case "WF_YM_WRITE":
                            $arOffer["MODEL"] .= $nameInps[$selKey] . " ";
                            break;
                        case "WF_PRODUCT_NAME":
                            $arOffer["MODEL"] .= $arOffer["~NAME"] . " ";
                            break;
                        default:
                            $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $selVal))->GetNext();
                            $dispProp = CIBlockFormatProperties::GetDisplayValue($arOffer, $arProps, "wf_ymout");
                            $selVal = $dispProp["VALUE_ENUM"] ? xml_creator($dispProp["VALUE_ENUM"], true) : xml_creator($dispProp["DISPLAY_VALUE"], true);
                            $selVal = strip_tags($selVal);
                            if (substr_count($selVal, "a href") > 0) {
                                $selVal = htmlspecialcharsBack($selVal);
                                $selVal = strip_tags($selVal);
                                $selVal = xml_creator($selVal, true);
                            }
                            $arOffer["MODEL"] .= $selVal . " ";
                            unset($arProps);
                            unset($dispProp);
                            break;
                    }
                }
                $arOffer["MODEL"] = trim($arOffer["MODEL"]);
            }
            /* obrezka nazvaniya */
            if (!empty($arParams["NAME_CUT"])) {
                $arParams["NAME_CUT"] = trim($arParams["NAME_CUT"]);
                $arOffer["MODEL"] = substr($arOffer["MODEL"], 0, $arParams["NAME_CUT"]);
                $arOffer["MODEL"] = trim($arOffer["MODEL"]);
            }

            $arOffer["MODEL"] = xml_creator($arOffer["MODEL"], true);
            /* obrezka nazvaniya konec */

//work with offer SKU
            $flag = 0;

            foreach ($arOffer["SKU"] as &$arOfferInID) {

                $arOfferIn = & $arOffers[$arOfferInID];
                $flag = 1;

                if ($arParams["CURRENCIES_CONVERT"] != "NOT_CONVERT") {

                    if ($arParams["DISCOUNTS"] != "DISCOUNT_API") {
                        $arOfferIn["PRICE"] = CCurrencyRates::ConvertCurrency($arOfferIn["PRICE"], $arOfferIn["CURRENCY"], $arParams["CURRENCIES_CONVERT"]);
                        $arOfferIn["OLD_PRICE"] = CCurrencyRates::ConvertCurrency($arOfferIn["OLD_PRICE"], $arOfferIn["CURRENCY"], $arParams["CURRENCIES_CONVERT"]);
                        if ($roundSettings["ROUND"] == "Y") {// Round Price if is Flag in arParams
                            if ((abs($arOfferIn["PRICE"]) > $roundSettings["MINIMUM_PRICE_ROUND"]) or $roundSettings["MINIMUM_PRICE_ROUND"] == 0) {
                                $arOfferIn["PRICE"] = WFYMRoundPrices::roundValue($arOfferIn["PRICE"], $roundSettings["ACCURACY_PRICE_ROUND"], $roundSettings["TYPE_PRICE_ROUND"]);
                                if (substr_count($arOfferIn["PRICE"], ".") == 0)
                                    $arOfferIn["PRICE"] = $arOfferIn["PRICE"] . WFYMRoundPrices::postfix;
                            }
                            if ($arOfferIn["OLD_PRICE"] and $arParams["DISCOUNTS"] != "PRICE_ONLY") {
                                $arOfferIn["OLD_PRICE"] = round($arOfferIn["OLD_PRICE"]);
                                if (substr_count($arOfferIn["OLD_PRICE"], ".") == 0)
                                    $arOfferIn["OLD_PRICE"] = $arOfferIn["OLD_PRICE"] . WFYMRoundPrices::postfix;
                            }
                        }
                    }
                    $arOfferIn["CURRENCY"] = $arParams["CURRENCIES_CONVERT"];
                }
                if (!in_array($arOfferIn["CURRENCY"], $arResult["CURRENCIES"]))
                    $arResult["CURRENCIES"][] = $arOfferIn["CURRENCY"];

                //1.4.2 start
                if ($arParams["PURCHASE_PRICE_CODE"] != "WF_EMPT") {
                    //get purchase price
                    if ($arParams["PURCHASE_PRICE_CODE"] == "WEBFLY_PURCHASE_PRICE") {//standart PURCHASE_PRICE
                        $purPrice = CCatalogProduct::GetList(array(), array("ID" => $arOfferIn["ID"]), false, false, array("PURCHASING_PRICE", "PURCHASING_CURRENCY"))->fetch();
                    }
                    else { //PURCHASE_PRICE from price type
                        $purResInfo = CCatalogGroup::GetList(array("SORT" => "ASC"), array("NAME" => $arParams["PURCHASE_PRICE_CODE"]))->Fetch();
                        if ($purResInfo["ID"]) {
                            $pur_res = CPrice::GetList(
                                    array(), array(
                                  "PRODUCT_ID" => $arOfferIn["ID"],
                                  "CATALOG_GROUP_ID" => $purResInfo["ID"]
                                    )
                                )->fetch();
                            if (!empty($pur_res["PRICE"])) {
                                $purPrice["PURCHASING_PRICE"] = $pur_res["PRICE"];
                                $purPrice["PURCHASING_CURRENCY"] = $pur_res["CURRENCY"];
                            }
                        }
                    }
                    //convert and round
                    if (!empty($purPrice["PURCHASING_PRICE"])) {
                        if ($purPrice["PURCHASING_CURRENCY"] != $arOfferIn["CURRENCY"])
                            $arOfferIn["PURCHASE_PRICE"] = CCurrencyRates::ConvertCurrency($purPrice["PURCHASING_PRICE"], $purPrice["PURCHASING_CURRENCY"], $arOfferIn["CURRENCY"]);
                        else
                            $arOfferIn["PURCHASE_PRICE"] = $purPrice["PURCHASING_PRICE"];
                        if ($roundSettings["ROUND"] == "Y") {// Round Price if is Flag in arParams
                            if ((abs($arOffer["PURCHASE_PRICE"]) > $roundSettings["MINIMUM_PRICE_ROUND"]) or $roundSettings["MINIMUM_PRICE_ROUND"] == 0) {
                                $arOfferIn["PURCHASE_PRICE"] = WFYMRoundPrices::roundValue($arOfferIn["PURCHASE_PRICE"], $roundSettings["ACCURACY_PRICE_ROUND"], $roundSettings["TYPE_PRICE_ROUND"]);
                                if (substr_count($arOfferIn["PURCHASE_PRICE"], ".") == 0)
                                    $arOfferIn["PURCHASE_PRICE"] = $arOfferIn["PURCHASE_PRICE"] . WFYMRoundPrices::postfix;
                            }
                        }
                    }
                }
                //1.4.2 end

                $arOfferIn["CATEGORY"] = $arOffer["CATEGORY"];

                if (empty($arParams["NAME_PROP_COMPILE"])) {//nesostavnoe nazvanie
                    $tmpName = $arOffer["MODEL"];

                    switch ($arParams["SKU_NAME"]) {
                        case "PRODUCT_NAME":
                            $arOfferIn["MODEL"] = xml_creator($tmpName, true);
                            break;

                        case "SKU_NAME":
                            $arOfferIn["MODEL"] = xml_creator(empty($arOfferIn["~NAME"]) ? $tmpName : $arOfferIn["~NAME"], true);
                            break;

                        default:
                            if (!empty($arOfferIn["~NAME"]))
                                $tmpName .= " / " . $arOfferIn["~NAME"];
                            $arOfferIn["MODEL"] = xml_creator($tmpName, true);
                            break;
                    }
                }
                else {//sostavnoe nazvanie
                    foreach ($nameSelects as $selKey => $selVal) {
                        switch ($selVal) {
                            case "WF_YM_WRITE":
                                $arOfferIn["MODEL"] .= $nameInps[$selKey] . " ";
                                break;
                            case "WF_PRODUCT_NAME":
                                $arOfferIn["MODEL"] .= $arOfferIn["~NAME"] . " ";
                                break;
                            default:
                                $arProps = CIBlockElement::GetProperty($arOfferIn["IBLOCK_ID"], $arOfferIn["ID"], array("sort" => "asc"), Array("CODE" => $selVal))->GetNext();
                                if ($arProps["VALUE"] == "" or empty($arProps["VALUE"])) {
                                    $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $selVal))->GetNext();
                                    $dispProp = CIBlockFormatProperties::GetDisplayValue($arOffer, $arProps, "wf_ymout");
                                }
                                else {
                                    $dispProp = CIBlockFormatProperties::GetDisplayValue($arOfferIn, $arProps, "wf_ymout");
                                }
                                $selVal = $dispProp["VALUE_ENUM"] ? xml_creator($dispProp["VALUE_ENUM"], true) : xml_creator($dispProp["DISPLAY_VALUE"], true);
                                $selVal = strip_tags($selVal);
                                if (substr_count($selVal, "a href") > 0) {
                                    $selVal = htmlspecialcharsBack($selVal);
                                    $selVal = strip_tags($selVal);
                                    $selVal = xml_creator($selVal, true);
                                }
                                $arOfferIn["MODEL"] .= $selVal . " ";
                                unset($arProps);
                                unset($dispProp);
                                break;
                        }
                    }
                    $arOfferIn["MODEL"] = trim($arOfferIn["MODEL"]);
                }

                /* obrezka nazvaniya */
                if (!empty($arParams["NAME_CUT"])) {
                    $arOfferIn["MODEL"] = substr($arOfferIn["MODEL"], 0, $arParams["NAME_CUT"]);
                    $arOfferIn["MODEL"] = trim($arOfferIn["MODEL"]);
                }
                $arOfferIn["MODEL"] = xml_creator($arOfferIn["MODEL"], true);
                /* obrezka nazvaniya konec */


                //url from prop
                if ($arParams["URL_PROPERTY_CHECK"] == "Y" && !empty($arParams["URL_PROPERTY"])) {
                    $arProps = CIBlockElement::GetProperty($arOfferIn["IBLOCK_ID"], $arOfferIn["ID"], array("sort" => "asc"), Array("CODE" => $arParams["URL_PROPERTY"]))->GetNext();
                    if ($arProps["VALUE"] == "" or empty($arProps["VALUE"])) {
                        $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams["URL_PROPERTY"]))->GetNext();
                        $dispProp = CIBlockFormatProperties::GetDisplayValue($arOffer, $arProps, "wf_ymout");
                    }
                    else {
                        $dispProp = CIBlockFormatProperties::GetDisplayValue($arOfferIn, $arProps, "wf_ymout");
                    }
                    $arProps["URL_VAL"] = $dispProp["VALUE_ENUM"] ? xml_creator($dispProp["VALUE_ENUM"], true) : xml_creator($dispProp["DISPLAY_VALUE"], true);
                    $arProps["URL_VAL"] = strip_tags($arProps["URL_VAL"]);
                    if (substr_count($arProps["URL_VAL"], "a href") > 0) {
                        $arProps["URL_VAL"] = htmlspecialcharsBack($arProps["URL_VAL"]);
                        $arProps["URL_VAL"] = strip_tags($arProps["URL_VAL"]);
                        $arProps["URL_VAL"] = xml_creator($arProps["URL_VAL"], true);
                    }
                    $arOfferIn["DETAIL_PAGE_URL_FROM_PROP"] = $arProps["URL_VAL"];
                    unset($arProps);
                    unset($dispProp);
                }

                /* UTM START */
                if ($arParams ["UTM_CHECK"] == "Y") {
//Take utm-source properties
                    if (!empty($arParams["UTM_SOURCE"]) and $arParams["UTM_SOURCE"] != "0") {
                        $isExistProp = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array("CODE" => $arParams["UTM_SOURCE"]))->Fetch();
                        if ($isExistProp) {
                            $arProps = CIBlockElement::GetProperty($arOfferIn["IBLOCK_ID"], $arOfferIn["ID"], array("sort" => "asc"), Array("CODE" => $arParams["UTM_SOURCE"]))->GetNext();
//esli u predlozheniya ne zapolneno svojstvo, vybiraem ego u tovara
                            if ($arProps["VALUE"] == "" or empty($arProps["VALUE"])) {
                                $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams["UTM_SOURCE"]))->GetNext();
                            }
                            $dispProp = CIBlockFormatProperties::GetDisplayValue($arOfferIn, $arProps, "wf_ymout");
                            $arOfferIn["UTM_SOURCE"] = $dispProp["VALUE_ENUM"] ? xml_creator($dispProp["VALUE_ENUM"], true) : xml_creator($dispProp["DISPLAY_VALUE"], true);
                            $arOfferIn["UTM_SOURCE"] = strip_tags($arOfferIn["UTM_SOURCE"]);
                            if (substr_count($arOfferIn["UTM_SOURCE"], "a href") > 0) {
                                $arOfferIn["UTM_SOURCE"] = htmlspecialcharsBack($arOfferIn["UTM_SOURCE"]);
                                $arOfferIn["UTM_SOURCE"] = strip_tags($arOfferIn["UTM_SOURCE"]);
                                $arOfferIn["UTM_SOURCE"] = xml_creator($arOfferIn["UTM_SOURCE"], true);
                            }
//esli u predlozheniya ne zapolneno svojstvo, vybiraem ego u tovara end
                        }
                        else {
                            $arOfferIn["UTM_SOURCE"] = xml_creator($arParams["UTM_SOURCE"], true);
                            $arOfferIn["UTM_SOURCE"] = strip_tags($arOfferIn["UTM_SOURCE"]);
                        }

                        if ($arOfferIn["UTM_SOURCE"] == false)
                            $arOfferIn["UTM_SOURCE"] = "";
                        unset($arProps);
                        unset($dispProp);
                    }

//Take utm-campaign properties
                    if ($arParams ["UTM_CAMPAIGN"] == "0" or empty($arParams ["UTM_CAMPAIGN"])) {
                        $wf_arGr = CIBlockElement::GetElementGroups($arOfferIn["ID"]);
                        $wf_ar_group = $wf_arGr->Fetch();
//esli net roditelskoj gruppy predlozheniya - berem roditelya tovara
                        if (!$wf_ar_group) {
                            $wf_arGr = CIBlockElement::GetElementGroups($arOffer["ID"]);
                            $wf_ar_group = $wf_arGr->Fetch();
                        }
                        $wf_groupid = $wf_ar_group["ID"];
                        $res = CIBlockSection::GetByID($wf_groupid);
                        if ($ar_res = $res->GetNext())
                            $group_code = $ar_res['CODE'];
                        $group_code = xml_creator($group_code, true);
                        $arOfferIn["UTM_CAMPAIGN"] = $group_code;
                        unset($res);
                        unset($wf_arGr);
                        unset($wf_ar_group);
                        if ($arParams["IBLOCK_AS_CATEGORY"] == 'Y' and empty($group_code)) {
                            $arOfferIn["UTM_CAMPAIGN"] = $arResult["CATEGORIES"][$arOffer["IBLOCK_ID"]]["CODE"];
                        }
                        if ($arOfferIn["UTM_CAMPAIGN"] == false)
                            $arOfferIn["UTM_CAMPAIGN"] = "";
                    }
                    else {
                        $isExistProp = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array("CODE" => $arParams["UTM_CAMPAIGN"]))->Fetch();
                        if ($isExistProp) {
                            $arProps = CIBlockElement::GetProperty($arOfferIn["IBLOCK_ID"], $arOfferIn["ID"], array("sort" => "asc"), Array("CODE" => $arParams["UTM_CAMPAIGN"]))->GetNext();
//esli u predlozheniya ne zapolneno svojstvo, vybiraem ego u tovara
                            if ($arProps["VALUE"] == "" or empty($arProps["VALUE"])) {
                                $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams["UTM_SOURCE"]))->GetNext();
                            }
                            $dispProp = CIBlockFormatProperties::GetDisplayValue($arOfferIn, $arProps, "wf_ymout");
                            $arOfferIn["UTM_CAMPAIGN"] = $dispProp["VALUE_ENUM"] ? xml_creator($dispProp["VALUE_ENUM"], true) : xml_creator($dispProp["DISPLAY_VALUE"], true);
                            $arOfferIn["UTM_CAMPAIGN"] = strip_tags($arOfferIn["UTM_CAMPAIGN"]);
                            if (substr_count($arOfferIn["UTM_CAMPAIGN"], "a href") > 0) {
                                $arOfferIn["UTM_CAMPAIGN"] = htmlspecialcharsBack($arOfferIn["UTM_CAMPAIGN"]);
                                $arOfferIn["UTM_CAMPAIGN"] = strip_tags($arOfferIn["UTM_CAMPAIGN"]);
                                $arOfferIn["UTM_CAMPAIGN"] = xml_creator($arOfferIn["UTM_CAMPAIGN"], true);
                            }
                        }
                        else {
                            $arOfferIn["UTM_CAMPAIGN"] = xml_creator($arParams["UTM_CAMPAIGN"], true);
                            $arOfferIn["UTM_CAMPAIGN"] = strip_tags($arOfferIn["UTM_CAMPAIGN"]);
                        }

                        if ($arOfferIn["UTM_CAMPAIGN"] == false)
                            $arOfferIn["UTM_CAMPAIGN"] = "";
                        unset($arProps);
                        unset($dispProp);
                    }

//Take utm-medium properties
                    if (!empty($arParams["UTM_MEDIUM"]) and $arParams["UTM_MEDIUM"] != "0") {
                        $isExistProp = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array("CODE" => $arParams["UTM_MEDIUM"]))->Fetch();
                        if ($isExistProp) {
                            $arProps = CIBlockElement::GetProperty($arOfferIn["IBLOCK_ID"], $arOfferIn["ID"], array("sort" => "asc"), Array("CODE" => $arParams["UTM_MEDIUM"]))->GetNext();
//esli u predlozheniya ne zapolneno svojstvo, vybiraem ego u tovara
                            if ($arProps["VALUE"] == "" or empty($arProps["VALUE"])) {
                                $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams["UTM_SOURCE"]))->GetNext();
                            }
                            $dispProp = CIBlockFormatProperties::GetDisplayValue($arOfferIn, $arProps, "wf_ymout");
                            $arOfferIn["UTM_MEDIUM"] = $dispProp["VALUE_ENUM"] ? xml_creator($dispProp["VALUE_ENUM"], true) : xml_creator($dispProp["DISPLAY_VALUE"], true);
                            $arOfferIn["UTM_MEDIUM"] = strip_tags($arOfferIn["UTM_MEDIUM"]);
                            if (substr_count($arOfferIn["UTM_MEDIUM"], "a href") > 0) {
                                $arOfferIn["UTM_MEDIUM"] = htmlspecialcharsBack($arOfferIn["UTM_MEDIUM"]);
                                $arOfferIn["UTM_MEDIUM"] = strip_tags($arOfferIn["UTM_MEDIUM"]);
                                $arOfferIn["UTM_MEDIUM"] = xml_creator($arOfferIn["UTM_MEDIUM"], true);
                            }
                        }
                        else {
                            $arOfferIn["UTM_MEDIUM"] = xml_creator($arParams["UTM_MEDIUM"], true);
                            $arOfferIn["UTM_MEDIUM"] = strip_tags($arOfferIn["UTM_MEDIUM"]);
                        }

                        if ($arOfferIn["UTM_MEDIUM"] == false)
                            $arOfferIn["UTM_MEDIUM"] = "";
                        unset($arProps);
                        unset($dispProp);
                    }

//Take utm-term properties
                    if (!empty($arParams["UTM_TERM"]) and $arParams["UTM_TERM"] != "0") {
                        if ($arParams["UTM_TERM"] == "WEBFLY_ID") {
                            $arOfferIn["UTM_TERM"] = $arOfferIn["ID"];
                        }
                        else {
                            $isExistProp = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array("CODE" => $arParams["UTM_TERM"]))->Fetch();
                            if ($isExistProp) {
                                $arProps = CIBlockElement::GetProperty($arOfferIn["IBLOCK_ID"], $arOfferIn["ID"], array("sort" => "asc"), Array("CODE" => $arParams["UTM_TERM"]))->GetNext();
//esli u predlozheniya ne zapolneno svojstvo, vybiraem ego u tovara
                                if ($arProps["VALUE"] == "" or empty($arProps["VALUE"])) {
                                    $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams["UTM_SOURCE"]))->GetNext();
                                }
                                $dispProp = CIBlockFormatProperties::GetDisplayValue($arOfferIn, $arProps, "wf_ymout");
                                $arOfferIn["UTM_TERM"] = $dispProp["VALUE_ENUM"] ? xml_creator($dispProp["VALUE_ENUM"], true) : xml_creator($dispProp["DISPLAY_VALUE"], true);
                                $arOfferIn["UTM_TERM"] = strip_tags($arOfferIn["UTM_TERM"]);
                                if (substr_count($arOfferIn["UTM_MEDIUM"], "a href") > 0) {
                                    $arOfferIn["UTM_TERM"] = htmlspecialcharsBack($arOfferIn["UTM_TERM"]);
                                    $arOfferIn["UTM_TERM"] = strip_tags($arOfferIn["UTM_TERM"]);
                                    $arOfferIn["UTM_TERM"] = xml_creator($arOfferIn["UTM_TERM"], true);
                                }
                            }
                            else {
                                $arOfferIn["UTM_TERM"] = xml_creator($arParams["UTM_TERM"], true);
                                $arOfferIn["UTM_TERM"] = strip_tags($arOfferIn["UTM_TERM"]);
                            }
                        }


                        if ($arOfferIn["UTM_TERM"] == false)
                            $arOfferIn["UTM_TERM"] = "";
                        unset($arProps);
                        unset($dispProp);
                    }
                    else {
                        $arOfferIn["UTM_TERM"] = $arOfferIn["CODE"];
                    }

//offer URL
                    if (substr_count($arOfferIn["DETAIL_PAGE_URL"], "?") > 0)
                        $symbol = "&amp;";
                    else
                        $symbol = "?";
                    if (empty($arOfferIn["UTM_SOURCE"]))
                        $utm_source = "";
                    else
                        $utm_source = $symbol . "utm_source=" . strip_tags($arOfferIn["UTM_SOURCE"]);

                    if (empty($arOfferIn["UTM_CAMPAIGN"])) {
                        $utm_campaign = "";
                    }
                    else {
                        if (empty($arOfferIn["UTM_SOURCE"]))
                            $utm_campaign = $symbol . "utm_campaign=" . strip_tags($arOfferIn["UTM_CAMPAIGN"]);
                        else
                            $utm_campaign = "&amp;utm_campaign=" . strip_tags($arOfferIn["UTM_CAMPAIGN"]);
                    }

                    if (empty($arOfferIn["UTM_MEDIUM"])) {
                        $utm_medium = "";
                    }
                    else {
                        if (empty($arOfferIn["UTM_CAMPAIGN"]) and empty($arOfferIn["UTM_SOURCE"]))
                            $utm_medium = $symbol . "utm_medium=" . strip_tags($arOfferIn["UTM_MEDIUM"]);
                        else
                            $utm_medium = "&amp;utm_medium=" . strip_tags($arOfferIn["UTM_MEDIUM"]);
                    }

                    if (empty($arOfferIn["UTM_TERM"])) {
                        $utm_term = "";
                    }
                    else {
                        if (empty($arOfferIn["UTM_MEDIUM"]) and empty($arOfferIn["UTM_CAMPAIGN"]) and empty($arOfferIn["UTM_SOURCE"]))
                            $utm_term = $symbol . "utm_term=" . strip_tags($arOfferIn["UTM_TERM"]);
                        else
                            $utm_term = "&amp;utm_term=" . strip_tags($arOfferIn["UTM_TERM"]);
                    }
                    if (!empty($arOfferIn["DETAIL_PAGE_URL_FROM_PROP"])) {
                        if ($arParams["URL_PROPERTY_WITH_DOMEN"] == "Y")
                            $arOfferIn["URL"] = $http . "://" . $server_name . $arOfferIn["DETAIL_PAGE_URL"] . $utm_source . $utm_campaign . $utm_medium . $utm_term;
                        else
                            $arOfferIn["URL"] = $arOfferIn["DETAIL_PAGE_URL"] . $utm_source . $utm_campaign . $utm_medium . $utm_term;
                    }else {
                        if (!$arOfferIn["DETAIL_PAGE_URL"]) {
                            $arOfferIn["URL"] = $http . "://" . $server_name . $arOffer["DETAIL_PAGE_URL"] . "#" . $arOfferIn["ID"] . $utm_source . $utm_campaign . $utm_medium . $utm_term;
                        }
                        else {
                            $arOfferIn["URL"] = $http . "://" . $server_name . $arOfferIn["DETAIL_PAGE_URL"] . $utm_source . $utm_campaign . $utm_medium . $utm_term;
                        }
                    }
                }
                else {
                    if (!empty($arOfferIn["DETAIL_PAGE_URL_FROM_PROP"])) {
                        if ($arParams["URL_PROPERTY_WITH_DOMEN"] == "Y")
                            $arOfferIn["URL"] = $http . "://" . $server_name . $arOfferIn["DETAIL_PAGE_URL"];
                        else
                            $arOfferIn["URL"] = $arOfferIn["DETAIL_PAGE_URL"];
                    }else {
                        if (!$arOfferIn["DETAIL_PAGE_URL"]) {
                            $arOfferIn["URL"] = $arOffer["URL"] . "#" . $arOfferIn["ID"];
                        }
                        else {
                            $arOfferIn["URL"] = $http . "://" . $server_name . $arOfferIn["DETAIL_PAGE_URL"];
                        }
                    }
                }

                /* ADDITIONAL PROPS OFFERS START */
// LOCAL_DELIVERY_COST_OFFER, STORE_OFFER, PICKUP_OFFER, typePrefix
// PROPDUCT_PROP - dopolnitelnye svojstva predlozhenij vyvodimye v opisanii
                // remove CPA
                //$propsArrayOffers = array("LOCAL_DELIVERY_COST_OFFER", "STORE_OFFER", "STORE_PICKUP", "ADULT", "PREFIX_PROP", "OFFER_PROP", "DELIVERY_OPTIONS_EX", "STORE_DELIVERY", "OUTLETS", "AGE_CATEGORY", "BID", "CBID", "FEE", "CPA_OFFERS", "EXPIRY", "WEIGHT", "DIMENSIONS", "RECOMMENDATION", "BARCODE");
                $propsArrayOffers = array("LOCAL_DELIVERY_COST_OFFER", "STORE_OFFER", "STORE_PICKUP", "ADULT", "PREFIX_PROP", "OFFER_PROP", "DELIVERY_OPTIONS_EX", "STORE_DELIVERY", "OUTLETS", "AGE_CATEGORY", "EXPIRY", "WEIGHT", "DIMENSIONS", "RECOMMENDATION", "BARCODE");
//esli vybran algoritm opredeleniya dostupnosti tovara iz svojstva
                if ($arParams["AVAILABLE_ALGORITHM"] == "PROP_ALGORITHM")
                    $propsArrayOffers[] = "PROP_ALGORITHM_VALUE";
                if (!empty($arParams["DESCRIPTION"]) and $arParams["NO_DESCRIPTION"] != "Y")
                    $propsArrayOffers[] = "DESCRIPTION";
                foreach ($propsArrayOffers as $propKey => $propVal) {
                    if (!empty($arParams[$propVal])) {
// dopolnitelnye svojstva sku, vyvodimye v opisanii
                        switch ($propVal) {
                            case "OFFER_PROP":
                                foreach ($arParams[$propVal] as $key => $productProp) {
                                    if (!empty($productProp)) {
                                        $productProp = trim($productProp);
                                        $arProps = CIBlockElement::GetProperty($arOfferIn["IBLOCK_ID"], $arOfferIn["ID"], array("sort" => "asc"), Array("CODE" => $productProp))->GetNext();
//nazvanie
                                        $arProps["NAME"] = xml_creator($arProps["NAME"], true);

//znachenie
                                        $dispProp = CIBlockFormatProperties::GetDisplayValue($arOfferIn, $arProps, "wf_ymout");
                                        $arProps["VAL"] = ($dispProp["VALUE_ENUM"] or $dispProp["VALUE_ENUM"] === "0") ? xml_creator($dispProp["VALUE_ENUM"], true) : xml_creator($dispProp["DISPLAY_VALUE"], true);
                                        $arProps["VAL"] = strip_tags($arProps["VAL"]);
                                        if (substr_count($arProps["VAL"], "a href") > 0) {
                                            $arProps["VAL"] = htmlspecialcharsBack($arProps["VAL"]);
                                            $arProps["VAL"] = strip_tags($arProps["VAL"]);
                                            $arProps["VAL"] = xml_creator($arProps["VAL"], true);
                                        }

//nakoplenie v peremennuyu
                                        if (!empty($arProps["VAL"])) {
                                            if (empty($arOfferIn["DOP_PROPS"]))
                                                $arOfferIn["DOP_PROPS"] = $arProps["NAME"] . ": " . $arProps["VAL"];
                                            else
                                                $arOfferIn["DOP_PROPS"] = $arOfferIn["DOP_PROPS"] . ", " . $arProps["NAME"] . ": " . $arProps["VAL"];
                                        }
                                        unset($arProps);
                                    }
                                }
                                break;
                            case "DELIVERY_OPTIONS_EX":
                                if ($productDeliv) {
                                    foreach ($productDeliv as $dkey => $dProp) {
                                        foreach ($dProp as $dk => $dv) {
                                            if (!empty($dv)) {
                                                $arProps = CIBlockElement::GetProperty($arOfferIn["IBLOCK_ID"], $arOfferIn["ID"], array("sort" => "asc"), Array("CODE" => $dv))->GetNext();
                                                //esli u predlozheniya ne zapolneno svojstvo, vybiraem ego u tovara
                                                if ($arProps["VALUE"] == "" or empty($arProps["VALUE"])) {
                                                    $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $dv))->GetNext();
                                                    $dispProp = CIBlockFormatProperties::GetDisplayValue($arOffer, $arProps, "wf_ymout");
                                                }
                                                else {
                                                    $dispProp = CIBlockFormatProperties::GetDisplayValue($arOfferIn, $arProps, "wf_ymout");
                                                }
                                                if ($dispProp["VALUE_ENUM"] or $dispProp["VALUE_ENUM"] === "0")
                                                    $arOfferIn[$propVal][$dkey][$dk] = xml_creator($dispProp["VALUE_ENUM"], true);
                                                else
                                                    $arOfferIn[$propVal][$dkey][$dk] = xml_creator($dispProp["DISPLAY_VALUE"], true);
                                                $arOfferIn[$propVal][$dkey][$dk] = strip_tags($arOfferIn[$propVal][$dkey][$dk]);
                                                if (substr_count($arOfferIn[$propVal][$dkey][$dk], "a href") > 0) {
                                                    $arOfferIn[$propVal][$dkey][$dk] = htmlspecialcharsBack($arOfferIn[$propVal][$dkey][$dk]);
                                                    $arOfferIn[$propVal][$dkey][$dk] = strip_tags($arOfferIn[$propVal][$dkey][$dk]);
                                                    $arOfferIn[$propVal][$dkey][$dk] = xml_creator($arOfferIn[$propVal][$dkey][$dk], true);
                                                }
                                            }
                                        }
                                    }
                                }
                                break;
                            case "OUTLETS":
                                if ($outlets) {
                                    foreach ($outlets as $outKey => $outProp) {
                                        foreach ($outProp as $outk => $outv) {
                                            $productAmount = "";
                                            $amountPropCode = "";
                                            if (substr_count($outv, "WFYMAMOUNT") > 0) {//value from amount//amount
                                                switch ($outk) {
                                                    case "0"://amount ID
                                                        $arOfferIn[$propVal][$outKey][$outk] = str_replace("_WFYMAMOUNT", "", $outv);
                                                        break;
                                                    case "1"://products quantity in amount
                                                        $productAmount = CCatalogStoreProduct::GetList(array("sort" => "asc"), array("ACTIVE" => "Y", "SITE_ID" => SITE_ID, "STORE_ID" => str_replace("_WFYMAMOUNT", "", $outv), "PRODUCT_ID" => $arOfferIn["ID"]), false, false, array("AMOUNT"))->Fetch();
                                                        if (!empty($productAmount["AMOUNT"])) {
                                                            if (substr_count($productAmount["AMOUNT"], "-") > 0)
                                                                $productAmount["AMOUNT"] = "0";
                                                            $productAmount = intval($productAmount["AMOUNT"]);
                                                        }else {
                                                            $productAmount = 0;
                                                        }
                                                        $arOfferIn[$propVal][$outKey][$outk] = $productAmount;
                                                        break;
                                                    case "2":
                                                        $arOfferIn[$propVal][$outKey][$outk] = "";
                                                        break;
                                                }
                                            }
                                            if (substr_count($outv, "WFYMPROP") > 0) {//value from property
                                                $amountPropCode = str_replace("_WFYMPROP", "", $outv);
                                                $arProps = CIBlockElement::GetProperty($arOfferIn["IBLOCK_ID"], $arOfferIn["ID"], array("sort" => "asc"), Array("CODE" => $amountPropCode))->GetNext();
                                                //esli u predlozheniya ne zapolneno svojstvo, vybiraem ego u tovara
                                                if ($arProps["VALUE"] == "" or empty($arProps["VALUE"])) {
                                                    $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $amountPropCode))->GetNext();
                                                    $dispProp = CIBlockFormatProperties::GetDisplayValue($arOffer, $arProps, "wf_ymout");
                                                }
                                                else {
                                                    $dispProp = CIBlockFormatProperties::GetDisplayValue($arOfferIn, $arProps, "wf_ymout");
                                                }
                                                if ($dispProp["VALUE_ENUM"] or $dispProp["VALUE_ENUM"] === "0")
                                                    $arOfferIn[$propVal][$outKey][$outk] = xml_creator($dispProp["VALUE_ENUM"], true);
                                                else
                                                    $arOfferIn[$propVal][$outKey][$outk] = xml_creator($dispProp["DISPLAY_VALUE"], true);
                                                $arOfferIn[$propVal][$outKey][$outk] = strip_tags($arOfferIn[$propVal][$outKey][$outk]);
                                                if (substr_count($arOfferIn[$propVal][$outKey][$outk], "a href") > 0) {
                                                    $arOfferIn[$propVal][$outKey][$outk] = htmlspecialcharsBack($arOfferIn[$propVal][$outKey][$outk]);
                                                    $arOfferIn[$propVal][$outKey][$outk] = strip_tags($arOfferIn[$propVal][$outKey][$outk]);
                                                    $arOfferIn[$propVal][$outKey][$outk] = xml_creator($arOfferIn[$propVal][$outKey][$outk], true);
                                                }
                                            }
                                            if (substr_count($outv, "WFYMAMOUNT") == 0 and substr_count($outv, "WFYMPROP") == 0) {//write
                                                $arOfferIn[$propVal][$outKey][$outk] = $outv;
                                            }
                                        }
                                    }
                                }
                                break;
                            case "RECOMMENDATION": case "BARCODE":
                                $arProps = CIBlockElement::GetProperty($arOfferIn["IBLOCK_ID"], $arOfferIn["ID"], array("sort" => "asc"), Array("CODE" => $arParams[$propVal]));
                                $multItem = array();
                                while ($ob_props = $arProps->GetNext()) {
                                    $multItem[] = $ob_props["VALUE"];
                                }
                                if ($propVal == "RECOMMENDATION")
                                    $arOfferIn[$propVal] = implode(", ", $multItem);
                                else
                                    $arOfferIn[$propVal] = $multItem;
                                unset($arProps);
                                //esli u predlozheniya ne zapolneno svojstvo, vybiraem ego u tovara
                                if (empty($arOfferIn[$propVal]) or count($arOfferIn[$propVal] == 0)) {
                                    $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams[$propVal]));
                                    $multItem = array();
                                    while ($ob_props = $arProps->GetNext()) {
                                        $multItem[] = $ob_props["VALUE"];
                                    }
                                    if ($propVal == "RECOMMENDATION")
                                        $arOfferIn[$propVal] = implode(", ", $multItem);
                                    else
                                        $arOfferIn[$propVal] = $multItem;
                                }
                                unset($arProps);
                                break;
                            default:
                                if ($propVal == "STORE_OFFER" or $propVal == "STORE_PICKUP" or $propVal == "STORE_DELIVERY" or $propVal == "ADULT") {//vozmozhnost vpisyvaniya
                                    $isExistProp = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array("CODE" => $arParams[$propVal]))->Fetch();
                                }
                                if (($propVal != "STORE_OFFER" and $propVal != "STORE_PICKUP" and $propVal != "STORE_DELIVERY" and $propVal != "ADULT") or ( ($propVal == "STORE_OFFER" or $propVal == "STORE_PICKUP" or $propVal == "STORE_DELIVERY" or $propVal == "ADULT") and $isExistProp)) {//vozmozhnost vpisyvaniya
                                    //NEW 24.04.2017 START
                                    if ($propVal == "WEIGHT" and $arParams[$propVal] == "WEBFLY_WEIGHT") {
                                        $productWeight = CIBlockElement::GetList(array(), array("IBLOCK_ID" => $arOfferIn["IBLOCK_ID"], "ID" => $arOfferIn["ID"]), false, false, array("ID", "CATALOG_WEIGHT"))->Fetch();
                                        if ($productWeight["CATALOG_WEIGHT"]) {
                                            $dispProp["DISPLAY_VALUE"] = round($productWeight["CATALOG_WEIGHT"] / 1000, 3);
                                            $dispProp["DISPLAY_VALUE"] = sprintf("%.03f", $dispProp["DISPLAY_VALUE"]);
                                        }
                                        $arProps["VALUE"] = "WF_weight_field";
                                        //NEW 24.04.2017 END
                                    }
                                    elseif ($propVal == "DIMENSIONS" and $arParams[$propVal] == "WEBFLY_DIMENSIONS") {//1.4.2
                                        $pdimensions = CIBlockElement::GetList(array(), array("IBLOCK_ID" => $arOfferIn["IBLOCK_ID"], "ID" => $arOfferIn["ID"]), false, false, array("ID", "CATALOG_LENGTH", "CATALOG_WIDTH", "CATALOG_HEIGHT"))->Fetch();
                                        if ($pdimensions["CATALOG_LENGTH"] != 0 && $pdimensions["CATALOG_WIDTH"] != 0 && $pdimensions["CATALOG_HEIGHT"] != 0) {
                                            $allDmns = array("CATALOG_LENGTH" => $pdimensions["CATALOG_LENGTH"], "CATALOG_WIDTH" => $pdimensions["CATALOG_WIDTH"], "CATALOG_HEIGHT" => $pdimensions["CATALOG_HEIGHT"]);
                                            foreach ($allDmns as $dimk => $dmn) {
                                                $allDmns[$dimk] = sprintf("%.03f", round($dmn / 10, 3));
                                            }
                                            $dispProp["DISPLAY_VALUE"] = implode($allDmns, "/");
                                            $arProps["VALUE"] = "WF_dimension_field";
                                        }
                                    }
                                    else {
                                        $arProps = CIBlockElement::GetProperty($arOfferIn["IBLOCK_ID"], $arOfferIn["ID"], array("sort" => "asc"), Array("CODE" => $arParams[$propVal]))->GetNext();
                                    }
//esli u predlozheniya ne zapolneno svojstvo, vybiraem ego u tovara
                                    if ($propVal == "PROP_ALGORITHM_VALUE") {
                                        if ($arProps) {//sku contain this prop
                                            $dispProp = CIBlockFormatProperties::GetDisplayValue($arOfferIn, $arProps, "wf_ymout");
                                        }
                                        else {
                                            $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams[$propVal]))->GetNext();
                                            $dispProp = CIBlockFormatProperties::GetDisplayValue($arOffer, $arProps, "wf_ymout");
                                        }
                                    }
                                    else {
                                        if ($arProps["VALUE"] == "" or empty($arProps["VALUE"])) {
                                            $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams[$propVal]))->GetNext();
                                            $dispProp = CIBlockFormatProperties::GetDisplayValue($arOffer, $arProps, "wf_ymout");
                                        }
                                        else {
                                            if ($arProps["VALUE"] != "WF_weight_field" && $arProps["VALUE"] != "WF_dimension_field")
                                                $dispProp = CIBlockFormatProperties::GetDisplayValue($arOfferIn, $arProps, "wf_ymout");
                                        }
                                    }
                                    if ($propVal == "DESCRIPTION") {
                                        $descriptionVal = "";
                                        if ($dispProp["~VALUE"]["TEXT"])
                                            $descriptionVal = $dispProp["~VALUE"]["TEXT"];
                                        else
                                            $descriptionVal = $dispProp["~VALUE"];
                                        if (!empty($descriptionVal)) {
                                            if ($arParams["DESCRIPTION_XHTML"] == "Y")
                                                $arOfferIn[$propVal] = xhtml_modifier($descriptionVal);
                                            else
                                                $arOfferIn[$propVal] = xml_creator(preg_replace_callback("'&[^;]*;'", "charset_modifier", strip_tags($descriptionVal)), true);
                                        }
                                    }
                                    else {
                                        $arOfferIn[$propVal] = ($dispProp["VALUE_ENUM"] or $dispProp["VALUE_ENUM"] === "0") ? xml_creator($dispProp["VALUE_ENUM"], true) : xml_creator($dispProp["DISPLAY_VALUE"], true);
                                        $arOfferIn[$propVal] = strip_tags($arOfferIn[$propVal]);
                                        if (substr_count($arOfferIn[$propVal], "a href") > 0) {
                                            $arOfferIn[$propVal] = htmlspecialcharsBack($arOfferIn[$propVal]);
                                            $arOfferIn[$propVal] = strip_tags($arOfferIn[$propVal]);
                                            $arOfferIn[$propVal] = xml_creator($arOfferIn[$propVal], true);
                                        }
                                    }
                                }
                                if (($propVal == "STORE_OFFER" or $propVal == "STORE_PICKUP" or $propVal == "STORE_DELIVERY" or $propVal == "ADULT") and ! $isExistProp) {//vozmozhnost vpisyvaniya
                                    $arOfferIn[$propVal] = xml_creator($arParams[$propVal], true);
                                }
//dostupnost tovarov
                                if ($propVal == "PROP_ALGORITHM_VALUE") {
                                    $arOfferIn["AVAIBLE"] = "false";
                                    if ($arOfferIn[$propVal] == "true" or $arOfferIn[$propVal] == "Y" or $arOfferIn[$propVal] == "1")
                                        $arOfferIn["AVAIBLE"] = "true";
                                    if ($arOfferIn[$propVal] == "false" or $arOfferIn[$propVal] == "N" or $arOfferIn[$propVal] == "0" or empty($arOfferIn[$propVal]))
                                        $arOfferIn["AVAIBLE"] = "false";
                                }
                                break;
                        }
                    }
                    //otdelnaya vyborka informacii o dostavke na osnove znacheniya svojstva i ukazannyh parametrov dostavki
                    if ($propVal == "STORE_DELIVERY") {
                        if (empty($arOfferIn[$propVal])) {
                            if ($arParams["LOCAL_DELIVERY_COST"] != ""
                                or ! empty($arOfferIn["LOCAL_DELIVERY_COST_OFFER"])
                                or ( !empty($arOfferIn["DELIVERY_OPTIONS_EX"]) and count($arOfferIn["DELIVERY_OPTIONS_EX"]) > 0)
                                or ( !empty($arResult["DELIVERY_OPTION_SHOP"]) and count($arResult["DELIVERY_OPTION_SHOP"]) > 0)
                            ) {
                                $arOfferIn[$propVal] = "true";
                            }
                        }
                    }
                    unset($arProps);
                    unset($dispProp);
                    unset($isExistProp);
                }
                if ($arOfferIn["OUTLETS"]) {//clear empty outlets
                    foreach ($arOfferIn["OUTLETS"] as $ouletsKeyIn => $ouletsValsIn) {
                        if ($ouletsValsIn[0] === "" or $ouletsValsIn[1] === "")
                            unset($arOfferIn["OUTLETS"][$ouletsKeyIn]);
                    }
                }
                //clear invalid delivery-options
                if (!empty($arOfferIn["DELIVERY_OPTIONS_EX"]) and count($arOfferIn["DELIVERY_OPTIONS_EX"]) > 0) {
                    foreach ($arOfferIn["DELIVERY_OPTIONS_EX"] as $deliveryKeyIn => $deliveryValsIn) {
                        if ($deliveryValsIn[0] === "")
                            unset($arOfferIn["DELIVERY_OPTIONS_EX"][$deliveryKeyIn]);
                    }
                }
                //change delivery options for not available products

                if ($arParams["DELIVERY_TO_AVAILABLE"] == "Y" and $arOfferIn["AVAIBLE"] == "false") {
                    if (!empty($arOfferIn["DELIVERY_OPTIONS_EX"]) and count($arOfferIn["DELIVERY_OPTIONS_EX"]) > 0) {
                        foreach ($arOfferIn["DELIVERY_OPTIONS_EX"] as $deliveryKeyIn2 => $deliveryValsIn2) {
                            $arOfferIn["DELIVERY_OPTIONS_EX"][$deliveryKeyIn2][1] = "32";
                        }
                    }
                    else {
                        if (!empty($arResult["DELIVERY_OPTION_SHOP"])) {
                            foreach ($arResult["DELIVERY_OPTION_SHOP"] as $shopDelKeyIn => $shopDelValsIn) {
                                $arOfferIn["DELIVERY_OPTIONS_EX"][$shopDelKeyIn] = $shopDelValsIn;
                                $arOfferIn["DELIVERY_OPTIONS_EX"][$shopDelKeyIn][1] = "32";
                            }
                        }
                    }
                }

                /* ADDITIONAL PROPS OFFERS END */
                //NEW_IBLOCK_ORDER
                if ($arParams["IBLOCK_ORDER"] != "Y" && $arOfferIn["AVAIBLE"] == "false")
                    continue;

                if (intval($arOfferIn["PRICE"]) <= 0)
                    continue;
                //NEW_IBLOCK_ORDER
                //6.03.2017
                if ($arParams["USE_ONLY_PROP_PICTURE"] !== "Y") {
                    $main_picture_in = $arOfferIn["DETAIL_PICTURE"];
                    $add_picture_in = $arOfferIn["PREVIEW_PICTURE"];
                    if ($arParams["GET_OVER_FIELDS_ANONCE"] == "Y") {
                        $main_picture_in = $arOfferIn["PREVIEW_PICTURE"];
                        $add_picture_in = $arOfferIn["DETAIL_PICTURE"];
                    }

                    if ($main_picture_in) {
                        $db_file = CFile::GetByID($main_picture_in);
                        if ($ar_file = $db_file->Fetch())
                            $arOfferIn["PICTURE"] = $ar_file["~src"] ? $ar_file["~src"] : $http . "://" . $server_name . "/" . (COption::GetOptionString("main", "upload_dir", "upload")) . "/" . $ar_file["SUBDIR"] . "/" . implode("/", array_map("rawurlencode", explode("/", $ar_file["FILE_NAME"])));
                        unset($ar_file);
                        unset($db_file);
                    }

                    if ($add_picture_in && !$arOfferIn["PICTURE"]) {
                        $db_file = CFile::GetByID($add_picture_in);
                        if ($ar_file = $db_file->Fetch())
                            $arOfferIn["PICTURE"] = $ar_file["~src"] ? $ar_file["~src"] : $http . "://" . $server_name . "/" . (COption::GetOptionString("main", "upload_dir", "upload")) . "/" . $ar_file["SUBDIR"] . "/" . implode("/", array_map("rawurlencode", explode("/", $ar_file["FILE_NAME"])));
                        unset($ar_file);
                        unset($db_file);
                    }
                }

//6.03.2017

                if (!empty($arParams["MORE_PHOTO"]) && $arParams["MORE_PHOTO"] != "WF_EMPT") {

                    $ph = CIBlockElement::GetProperty($arOfferIn["IBLOCK_ID"], $arOfferIn["ID"], array("sort" => "asc"), Array("CODE" => $arParams["MORE_PHOTO"]));
                    $arOfferIn["MORE_PHOTO"] = array();

                    while (($ob = $ph->GetNext()) && count($arOfferIn["MORE_PHOTO"]) < 10) {
                        $arFile = CFile::GetFileArray($ob["VALUE"]);
                        if (!empty($arFile)) {
                            if (strpos($arFile["SRC"], $http) === false) {
                                $pic = $http . "://" . $server_name . implode("/", array_map("rawurlencode", explode("/", $arFile["SRC"])));
                            }
                            else {
                                $ar = explode($http . "://", $arFile["SRC"]);
                                $pic = $http . "://" . implode("/", array_map("rawurlencode", explode("/", $ar[1])));
                            }
                        }
                        else {
                            $ob["VALUE"] = parse_url($ob["VALUE"]);
                            if ($ob["VALUE"]["path"])
                                $ob["VALUE"]["path"] = implode("/", array_map("rawurlencode", explode("/", $ob["VALUE"]["path"])));
                            $pic = unparse_url($ob["VALUE"]);
                        }
                        $arOfferIn["MORE_PHOTO"][] = $pic;
                        unset($ob);
                        unset($arFile);
                    }
                    unset($ph);
                }

                if (is_array($arOffer["MORE_PHOTO"]))
                    foreach ($arOffer["MORE_PHOTO"] as $pic) {
                        if (!in_array($pic, $arOfferIn["MORE_PHOTO"]) && count($arOfferIn["MORE_PHOTO"]) < 10)
                            $arOfferIn["MORE_PHOTO"][] = $pic;
                    }

                if (!$arOfferIn["PICTURE"]) {
                    if ($arOffer["PICTURE"])
                        $arOfferIn["PICTURE"] = $arOffer["PICTURE"];
                    else
                    if (is_array($arOfferIn["MORE_PHOTO"]))
                        $arOfferIn["PICTURE"] = array_shift($arOfferIn["MORE_PHOTO"]);
                }
                $arOfferIn["MORE_PHOTO"] = array_slice($arOfferIn["MORE_PHOTO"], 0, 9);

                if ($arParams["NO_DESCRIPTION"] != "Y") {//ispolzovat description
                    if (empty($arParams["DESCRIPTION"])) {
                        if ($arParams["DESCRIPTION_XHTML"] == "Y") {
                            if ($arOfferIn["PREVIEW_TEXT"])
                                $arOfferIn["PREVIEW_TEXT"] = xhtml_modifier($arOfferIn["~PREVIEW_TEXT"]);
                            if ($arOfferIn["DETAIL_TEXT"])
                                $arOfferIn["DETAIL_TEXT"] = xhtml_modifier($arOfferIn["~DETAIL_TEXT"]);
                        }
                        else {
                            if ($arOfferIn["PREVIEW_TEXT"])
                                $arOfferIn["PREVIEW_TEXT"] = xml_creator(($arOfferIn["PREVIEW_TEXT_TYPE"] == "html" ? preg_replace_callback("'&[^;]*;'", "charset_modifier", strip_tags($arOfferIn["~PREVIEW_TEXT"])) : $arOfferIn["~PREVIEW_TEXT"]), true);

                            if ($arOfferIn["DETAIL_TEXT"])
                                $arOfferIn["DETAIL_TEXT"] = xml_creator(($arOfferIn["DETAIL_TEXT_TYPE"] == "html" ? preg_replace_callback("'&[^;]*;'", "charset_modifier", strip_tags($arOfferIn["~DETAIL_TEXT"])) : $arOfferIn["~DETAIL_TEXT"]), true);
                        }

                        if ($arParams["DETAIL_TEXT_PRIORITET"] == "Y") {//prioritet detalnogo teksta
                            $arOfferIn["DESCRIPTION"] = $arOfferIn["DETAIL_TEXT"] ? $arOfferIn["DETAIL_TEXT"] : $arOfferIn["PREVIEW_TEXT"];
                        }
                        else {//prioritet anonsa teksta
                            $arOfferIn["DESCRIPTION"] = $arOfferIn["PREVIEW_TEXT"] ? $arOfferIn["PREVIEW_TEXT"] : $arOfferIn["DETAIL_TEXT"];
                        }
                        if (empty($arOfferIn["DESCRIPTION"]))
                            $arOfferIn["DESCRIPTION"] = $arOffer["DESCRIPTION"];
                    }
                }
                else {//ne ispolzovat description
                    $arOfferIn["DESCRIPTION"] = '';
                }


// MARKET_CATEGORY

                if ($arParams["MARKET_CATEGORY_CHECK"] == "Y") {
                    $arOfferIn["MARKET_CATEGORY"] = $arOffer["MARKET_CATEGORY"];
                }

// GROUP_ID
                $arOfferIn["GROUP_ID"] = $arOffer["ID"];
// ID Ibloka cataloga
                $arOfferIn["IBLOCK_ID_CATALOG"] = $arOffer["IBLOCK_ID"];

                if ($arParams['SECTION_AS_VENDOR'] == 'Y') {
                    if (!empty($arOffer['IBLOCK_SECTION_ID'])) {
                        $arOfferIn["DEVELOPER"] = $arOffer["DEVELOPER"];
                    }
                }

                $arResult["OFFER"][] = $arOfferIn;
            } // foreach ($arOffer["SKU"] as &$arOfferInID)

            if ($flag == 1)
                continue;

            if (!$bCatalog || $arParams['PRICE_FROM_IBLOCK'] == 'Y') {
                $arOffer["AVAIBLE"] = "true";
                if (isset($arParams["IBLOCK_QUANTITY"]) && $arParams["IBLOCK_QUANTITY"] != "WF_EMPT") {
                    $av = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams["IBLOCK_QUANTITY"]))->Fetch();
                    if (IntVal($av["VALUE"]) > 0)
                        $arOffer["AVAIBLE"] = "true";
                    else {
                        if ($arParams["IBLOCK_ORDER"] == "Y")
                            $arOffer["AVAIBLE"] = "false";
                        else
                            continue;
                    }
                }
            }

            if ($bCatalog && $arParams['PRICE_FROM_IBLOCK'] != 'Y') {
                if ($arOffer['CURRENCY'] == "RUR")
                    $arOffer['CURRENCY'] = "RUB";

                if ($arParams["CURRENCIES_CONVERT"] != "NOT_CONVERT") {
                    if ($arParams["DISCOUNTS"] != "DISCOUNT_API") {
                        $arOffer["PRICE"] = CCurrencyRates::ConvertCurrency($arOffer["PRICE"], $arOffer["CURRENCY"], $arParams["CURRENCIES_CONVERT"]);
                        $arOffer["OLD_PRICE"] = CCurrencyRates::ConvertCurrency($arOffer["OLD_PRICE"], $arOffer["CURRENCY"], $arParams["CURRENCIES_CONVERT"]);
                        if ($roundSettings["ROUND"] == "Y") {// Round Price if is Flag in arParams
                            if ((abs($arOffer["PRICE"]) > $roundSettings["MINIMUM_PRICE_ROUND"]) or $roundSettings["MINIMUM_PRICE_ROUND"] == 0) {
                                $arOffer["PRICE"] = WFYMRoundPrices::roundValue($arOffer["PRICE"], $roundSettings["ACCURACY_PRICE_ROUND"], $roundSettings["TYPE_PRICE_ROUND"]);
                                if (substr_count($arOffer["PRICE"], ".") == 0)
                                    $arOffer["PRICE"] = $arOffer["PRICE"] . WFYMRoundPrices::postfix;
                            }
                            if ($arOffer["OLD_PRICE"] and $arParams["DISCOUNTS"] != "PRICE_ONLY") {
                                $arOffer["OLD_PRICE"] = round($arOffer["OLD_PRICE"]);
                                if (substr_count($arOffer["OLD_PRICE"], ".") == 0)
                                    $arOffer["OLD_PRICE"] = $arOffer["OLD_PRICE"] . WFYMRoundPrices::postfix;
                            }
                        }
                        //$arOffer["PRICE"] = round($newval, 2);
                    }
                    $arOffer["CURRENCY"] = $arParams["CURRENCIES_CONVERT"];
                }
                if (!in_array($arOffer["CURRENCY"], $arResult["CURRENCIES"]))
                    $arResult["CURRENCIES"][] = $arOffer["CURRENCY"];
                //1.4.2 start
                if ($arParams["PURCHASE_PRICE_CODE"] != "WF_EMPT") {

                    //get purchase price
                    if ($arParams["PURCHASE_PRICE_CODE"] == "WEBFLY_PURCHASE_PRICE") {//standart PURCHASE_PRICE
                        $purPrice = CCatalogProduct::GetList(array(), array("ID" => $arOffer["ID"]), false, false, array("PURCHASING_PRICE", "PURCHASING_CURRENCY"))->fetch();
                    }
                    else { //PURCHASE_PRICE from price type
                        $purResInfo = CCatalogGroup::GetList(array("SORT" => "ASC"), array("NAME" => $arParams["PURCHASE_PRICE_CODE"]))->Fetch();
                        if ($purResInfo["ID"]) {
                            $pur_res = CPrice::GetList(
                                    array(), array(
                                  "PRODUCT_ID" => $arOffer["ID"],
                                  "CATALOG_GROUP_ID" => $purResInfo["ID"]
                                    )
                                )->fetch();
                            if (!empty($pur_res["PRICE"])) {
                                $purPrice["PURCHASING_PRICE"] = $pur_res["PRICE"];
                                $purPrice["PURCHASING_CURRENCY"] = $pur_res["CURRENCY"];
                            }
                        }
                    }
                    //convert and round
                    if (!empty($purPrice["PURCHASING_PRICE"])) {
                        if ($purPrice["PURCHASING_CURRENCY"] != $arOffer["CURRENCY"])
                            $arOffer["PURCHASE_PRICE"] = CCurrencyRates::ConvertCurrency($purPrice["PURCHASING_PRICE"], $purPrice["PURCHASING_CURRENCY"], $arOffer["CURRENCY"]);
                        else
                            $arOffer["PURCHASE_PRICE"] = $purPrice["PURCHASING_PRICE"];
                        if ($roundSettings["ROUND"] == "Y") {// Round Price if is Flag in arParams
                            if ((abs($arOffer["PURCHASE_PRICE"]) > $roundSettings["MINIMUM_PRICE_ROUND"]) or $roundSettings["MINIMUM_PRICE_ROUND"] == 0) {
                                $arOffer["PURCHASE_PRICE"] = WFYMRoundPrices::roundValue($arOffer["PURCHASE_PRICE"], $roundSettings["ACCURACY_PRICE_ROUND"], $roundSettings["TYPE_PRICE_ROUND"]);
                                if (substr_count($arOffer["PURCHASE_PRICE"], ".") == 0)
                                    $arOffer["PURCHASE_PRICE"] = $arOffer["PURCHASE_PRICE"] . WFYMRoundPrices::postfix;
                            }
                        }
                    }
                }
                //1.4.2 end
            }
            else {
//iblock price
                $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer['ID'], array("sort" => "asc"), Array("CODE" => $arParams["PRICE_CODE"]))->Fetch();

                $arOffer["PRICE"] = $arProps["VALUE_ENUM"] ? $arProps["VALUE_ENUM"] : $arProps["VALUE"];
                $arOffer["PRICE"] = floatval(str_replace(" ", "", $arOffer["PRICE"]));
                //old iblock price
                if ($arParams["OLD_PRICE"] == "Y") {
                    unset($arProps);
                    $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer['ID'], array("sort" => "asc"), Array("CODE" => $arParams["OLD_PRICE_CODE"]))->Fetch();
                    $arOffer["OLD_PRICE"] = $arProps["VALUE_ENUM"] ? $arProps["VALUE_ENUM"] : $arProps["VALUE"];
                    $arOffer["OLD_PRICE"] = floatval(str_replace(" ", "", $arOffer["OLD_PRICE"]));
                }
                if ($roundSettings["ROUND"] == "Y") {// Round Price if is Flag in arParams
                    if ((abs($arOffer["PRICE"]) > $roundSettings["MINIMUM_PRICE_ROUND"]) or $roundSettings["MINIMUM_PRICE_ROUND"] == 0) {
                        $arOffer["PRICE"] = WFYMRoundPrices::roundValue($arOffer["PRICE"], $roundSettings["ACCURACY_PRICE_ROUND"], $roundSettings["TYPE_PRICE_ROUND"]);
                        if (substr_count($arOffer["PRICE"], ".") == 0)
                            $arOffer["PRICE"] = $arOffer["PRICE"] . WFYMRoundPrices::postfix;
                    }
                    if ($arOffer["OLD_PRICE"]) {
                        $arOffer["OLD_PRICE"] = round($arOffer["OLD_PRICE"]);
                        if (substr_count($arOffer["OLD_PRICE"], ".") == 0)
                            $arOffer["OLD_PRICE"] = $arOffer["OLD_PRICE"] . WFYMRoundPrices::postfix;
                    }
                }
                unset($arProps);

                if (intval($arOffer["PRICE"]) <= 0 && $arParams['PRICE_REQUIRED'] != 'N')
                    continue;

                if (!empty($arParams["CURRENCIES_PROP"]))
                    $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer['ID'], array("sort" => "asc"), Array("CODE" => $arParams["CURRENCIES_PROP"]))->Fetch();

                $arOffer["CURRENCY"] = empty($arProps["VALUE_XML_ID"]) ? $arParams["CURRENCY"] : $arProps["VALUE_XML_ID"];
                $arProps = null;

                if (!in_array($arOffer["CURRENCY"], $arResult["CURRENCIES"]))
                    $arResult["CURRENCIES"][] = $arOffer["CURRENCY"];
            }

            $arResult["OFFER"][] = $arOffer;

            $i++;
        }

        //DELIVERY OPTIONS SETTINGS
        if (!empty($arResult["OFFER"])) {
            foreach ($arResult["OFFER"] as &$arOffer) {
                if (!empty($arOffer["DELIVERY_OPTIONS_EX"])) {
                    foreach ($arOffer["DELIVERY_OPTIONS_EX"] as $kde => $kdv) {
                        foreach ($kdv as $kdvK => $kdvV) {
                            if ($kdvK == 0 and $kdvV == "") {
                                unset($arOffer["DELIVERY_OPTIONS_EX"][$kde]);
                            }
                        }
                    }
                }
            }
        }

        //CURRENCIES SETTINGS
        if (!empty($arParams["BIG_CATALOG_PROP"]) and $arParams["SAVE_IN_FILE"] == "Y" and $arParams["CURRENCIES_CONVERT"] == "NOT_CONVERT" and $arResult["WF_NUM"] == 1 and $arParams['PRICE_FROM_IBLOCK'] != 'Y') {
            $db_res = CCatalogGroup::GetList(
                    array(), array(
                  "NAME" => $arParams["PRICE_CODE"]
                    ), false, false, array("ID")
            );
            while ($ar_res = $db_res->Fetch()) {
                $result[] = $ar_res["ID"];
            }

            if (is_array($arFilter["IBLOCK_ID"]) and is_array($arSKUiblockID))
                $arFilter["IBLOCK_ID"] = array_merge($arFilter["IBLOCK_ID"], $arSKUiblockID);
            foreach ($result as $k => $re) {
                $curInfo = CIBlockElement::GetList(array(), array(array_merge($arrFilter, $arFilter)), false, false, array("ID", "CATALOG_GROUP_" . $re));
                while ($curob = $curInfo->Fetch()) {
                    if ($arParams["IBLOCK_ORDER"] != "Y") {
                        if ($arParams["AVAILABLE_ALGORITHM"] == "BITRIX_ALGORITHM" or $arParams["AVAILABLE_ALGORITHM"] == "QUANTITY_ZERO" or empty($arParams["AVAILABLE_ALGORITHM"])) {
                            switch ($arParams["AVAILABLE_ALGORITHM"]) {
                                case "BITRIX_ALGORITHM":default:
                                    if ($curob["CATALOG_QUANTITY_TRACE"] == "N")
                                        continue;
                                    if ($curob["CATALOG_QUANTITY"] > 0)
                                        continue;
                                    if ($curob["CATALOG_CAN_BUY_ZERO"] == "Y")
                                        continue;
                                    break;
                                case "QUANTITY_ZERO":
                                    if ($curob["CATALOG_QUANTITY"] <= 0)
                                        continue;
                                    break;
                            }
                        }
                    }

                    if (!in_array($curob["CATALOG_CURRENCY_" . $re], $resCur) and ! empty($curob["CATALOG_CURRENCY_" . $re]))
                        $resCur[] = $curob["CATALOG_CURRENCY_" . $re];
                }
            }

            $arResult["CURRENCIES"] = $resCur;
        }

        unset($arOffers);

        /**
         * YANDEX PROMOS
         */
        if ($arParams['YM_PROMO_FLASH_DISCOUNT'] == 'Y') {
            // Get actions from iblock
            $promos = [];
            $promoIDs = [];
            $res = CIBlockElement::Getlist([],
                ['IBLOCK_ID'=>$arParams['YM_PROMO_IBLOCK'],'ACTIVE'=>'Y','ACTIVE_DATE'=>'Y', '!DATE_ACTIVE_FROM'=>false,'!DATE_ACTIVE_TO'=>false],
                false, false,
                ['ID','NAME','PREVIEW_TEXT','DETAIL_PAGE_URL','DATE_ACTIVE_FROM','DATE_ACTIVE_TO']);
            while ($promo = $res->GetNext()) {
                $promo['START_DATE'] = date('Y-m-d H:i', strtotime($promo['DATE_ACTIVE_FROM']));
                $promo['END_DATE'] = date('Y-m-d H:i', strtotime($promo['DATE_ACTIVE_TO']));
                $promos[$promo['ID']] = $promo;
                $promoIDs[] = $promo['ID'];
            }

            if (count($promoIDs) > 0) {
                //get selected price
                $priceCode = $arParams['PRICE_CODE'][0];
                $arPrice = CIBlockPriceTools::GetCatalogPrices('*', [$priceCode]);
                $priceSelector = $arPrice[$priceCode]['SELECT'];

                // Get products with link to actions
                $productsFilter = array_merge($arrFilter, $arFilter, ['PROPERTY_YM_PROMO'=>$promoIDs]);
                $res = CIBlockElement::Getlist(
                    [], $productsFilter, false, false,
                    ['IBLOCK_ID','ID','NAME','PROPERTY_YM_PROMO',$priceSelector]
                );
                $promoProducts = [];
                while ($product = $res->GetNext()) {
                    if (empty($product["PROPERTY_YM_PROMO_VALUE"])) continue;
                    $prices = CIBlockPriceTools::GetItemPrices('*', $arPrice, $product, true);
                    $product['DISCOUNT_PRICE'] = $prices[$priceCode]['DISCOUNT_VALUE'];
                    $promos[$product["PROPERTY_YM_PROMO_VALUE"]]['PRODUCTS'][] = $product;
                    $haveOffers = CCatalogSKU::IsExistOffers(intval($product['ID']), intval($product['IBLOCK_ID']));
                    if (!$haveOffers || $arParams["DONT_USE_SKU"] == "Y") {
                        $promoProducts[] = $product;
                    }
                }
                // Get offers with link to actions
                if ($arParams["DONT_USE_SKU"] == "N") {
                    $offersFilter = array_merge($filterSKU, $arrFilterSKU, ['PROPERTY_YM_PROMO'=>$promoIDs]);
                    $res = CIBlockElement::GetList(
                        [], $offersFilter, false, false,
                        ['IBLOCK_ID','ID','CODE','NAME','PROPERTY_YM_PROMO',$priceSelector ]
                    );
                    while ($offer = $res->GetNext()) {
                        $prices = CIBlockPriceTools::GetItemPrices('*', $arPrice, $offer, true);
                        //$oprice = CCatalogProduct::GetOptimalPrice($offer['ID'], 1, $USER->GetUserGroupArray(), 'N', 's1');
                        $offer['DISCOUNT_PRICE'] = $prices[$priceCode]['DISCOUNT_VALUE'];
                        $promos[$offer["PROPERTY_YM_PROMO_VALUE"]]['PRODUCTS'][] = $offer;
                        $promoProducts[] = $offer;
                    }
                }
                $arResult['PROMOS'] = $promos;
                //$arResult['PROMO_PRODUCTS'] = $promoProducts;
            }
        }
        /**
         * YANDEX PROMOS END
         */

        if ($arParams["ECHO_ADMIN_INFO"] == "Y") {
            if (!function_exists("convert_size")) {

                function convert_size($size) {
                    $unit = array('B', 'Kb', 'M', 'Gb', 'Tb', 'Pb');
                    return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
                }

            }

            $stepEndTime = date("d.m.Y H:i:s");
            file_put_contents("ym_log.txt", GetMessage("SCRIPT_END") . $stepEndTime . "\r\n", FILE_APPEND | LOCK_EX);
            file_put_contents("ym_log.txt", GetMessage("TIME_SUM") . FormatDate("sdiff", strtotime($stepStartTime), strtotime($stepEndTime)) . "\r\n", FILE_APPEND | LOCK_EX);
            file_put_contents("ym_log.txt", GetMessage("MEMORY_GET") . convert_size(memory_get_usage(true)) . "\r\n", FILE_APPEND | LOCK_EX);
        }
        $this->IncludeComponentTemplate();

        if ($arParams["CACHE_NON_MANAGED"] == 'Y' && $obCache) {
            $obCache->EndDataCache();
        }
    }

    if (!$bDesignMode and ! $bSaveInFile) {
        $r = $APPLICATION->EndBufferContentMan();
        echo $r;
        if (defined("HTML_PAGES_FILE") && !defined("ERROR_404"))
            CHTMLPagesCache::writeFile(HTML_PAGES_FILE, $r);
        die();
    }
}
else {
    echo GetMessage("ADMIN_TEXT_STOP");
}
