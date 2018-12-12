<?
/* œŒƒ Àﬁ◊≈Õ»≈ HIGHLOAD */
CModule::IncludeModule("highloadblock");

use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;

global $DB;
/* œŒƒ Àﬁ◊≈Õ»≈ HIGHLOAD  ŒÕ≈÷ */

define('DESCRIPTION_SIZE', 511);

if (!CModule::IncludeModule("iblock"))
    die();

$bCatalog = CModule::IncludeModule('catalog');

/* * ***********************************************************************
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

foreach ($arParams["PROPERTY_CODE"] as $key => $value)
{
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

if (strlen($arParams["FILTER_NAME"]) <= 0 || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"]))
{
    $arrFilter = array();
}
else
{
    global $$arParams["FILTER_NAME"];
    $arrFilter = ${$arParams["FILTER_NAME"]};
    if (!is_array($arrFilter))
        $arrFilter = array();
}

if ($arParams["SHOW_PRICE_COUNT"] <= 0)
    $arParams["SHOW_PRICE_COUNT"] = 1;



$arParams["CACHE_FILTER"] = ($arParams["CACHE_FILTER"] == "Y");
if (!$arParams["CACHE_FILTER"] && count($arrFilter) > 0)
    $arParams["CACHE_TIME"] = 0;


$arParams["PRICE_VAT_INCLUDE"] = $arParams["PRICE_VAT_INCLUDE"] !== "N";

if (empty($arParams["DISCOUNTS"]))
    $arParams["DISCOUNTS"] = "DISCOUNT_CUSTOM";

if (!function_exists("charset_modifier"))
{

    function charset_modifier($arg) {
        $ent = html_entity_decode($arg[0], ENT_QUOTES, LANG_CHARSET);

        if ($ent == $arg[0])
            return '';
        return $ent;
    }

}

if (!function_exists("xml_creator"))
{

    function xml_creator($text, $bHSC = true, $bDblQuote = false) {
        $bDblQuote = (true == $bDblQuote ? true : false);

        if ($bHSC)
        {
            $text = htmlspecialcharsBx($text);
            if ($bDblQuote)
                $text = str_replace('&quot;', '"', $text);
        }
        $text = preg_replace("/[\x1-\x8\xB-\xC\xE-\x1F]/", "", $text);
        $text = str_replace("'", "&apos;", $text);
        return $text;
    }

}

if ($arParams["DISCOUNTS"] == "PRICE_ONLY")
{

    function webfly_ymarket_GetPrice($product_id, &$arPrices, &$arOffers, $isRoundPrice) {
        $arOffers[$product_id]["PRICE"] = 0;
        foreach ($arPrices as $arProductPrice)
        {
            if ($arProductPrice['PRICE'] && ($arProductPrice['PRICE'] < $arOffers[$product_id]["PRICE"] || !$arOffers[$product_id]["PRICE"]))
            {
                if ($isRoundPrice == "Y")// Round Price if is Flag in arParams
                {
                    $arProductPrice['PRICE'] = round($arProductPrice['PRICE']);
                    $arProductPrice['PRICE'] = $arProductPrice['PRICE'] . ".00";
                }
                $arOffers[$product_id]["PRICE"] = $arProductPrice['PRICE'];
                $arOffers[$product_id]["CURRENCY"] = $arProductPrice["CURRENCY"];
            }
        }
    }

}
else
if ($arParams["DISCOUNTS"] == "DISCOUNT_CUSTOM")//ÛÔÓ˘ÂÌÌ˚È ‡Î„ÓËÚÏ
{
    $arUserGroups = $GLOBALS["USER"]->GetUserGroupArray();

    function webfly_ymarket_GetPrice($product_id, &$arPrices, &$arOffers, $isRoundPrice, $isOldPrice) {
        global $arUserGroups;
        $price = 0;
        foreach ($arPrices as &$arProductPrice)
        {
            if ($arProductPrice['PRICE'] && ($arProductPrice['PRICE'] < $price || !$price))
            {
                $price = $arProductPrice['PRICE'];
                $arOffers[$product_id]["CURRENCY"] = $arProductPrice["CURRENCY"];
            }

            $arDiscounts = CCatalogDiscount::GetDiscountByProduct($product_id, $arUserGroups, "N", $arProductPrice['CATALOG_GROUP_ID'], SITE_ID);
            foreach ($arDiscounts as &$arDiscount)
            {
                switch ($arDiscount["VALUE_TYPE"]) {
                    case 'P': $price_buf = $arProductPrice["PRICE"] - $arDiscount["VALUE"] * $arProductPrice["PRICE"] / 100; //‚ ÔÓˆÂÌÚ‡ı
                        break;
                    case 'F': $price_buf = $arProductPrice["PRICE"] - $arDiscount["VALUE"]; //ÙËÍÒËÓ‚‡ÌÌ‡ˇ
                        break;
                    default: $price_buf = $arDiscount["VALUE"]; //ÛÒÚ‡ÌÓ‚ËÚ¸ ˆÂÌÛ Ì‡ ÚÓ‚‡
                        break;
                }

                if ($price_buf && ($price_buf < $price || !$price))
                {
                    if ($isRoundPrice == "Y")// Round Price if is Flag in arParams
                    {
                        $price = round($price);
                        $price = $price . ".00";
                        $price_buf = round($price_buf);
                        $price_buf = $price_buf . ".00";
                    }
                    if ($isOldPrice == "Y")
                    {
                        $old_price = $price; //new
                    }
                    $price = $price_buf;
                    $arOffers[$product_id]["CURRENCY"] = $arProductPrice["CURRENCY"];
                }
            }
            $arDiscounts = null;
        }
        if (!empty($old_price) and $isOldPrice == "Y")
        {
            $arOffers[$product_id]["OLD_PRICE"] = $old_price; //Fill Old Price
        }
        $arOffers[$product_id]["PRICE"] = $price; //new
        /* $arOffers[$product_id]["PRICE"] = $price; */
        CCatalogDiscount::ClearDiscountCache(array('PRODUCT' => 'Y'));
    }

}
else
{
// if($arParams["DISCOUNTS"] == "DISCOUNT_API")
    $baseCurrency = CCurrency::GetBaseCurrency();

    function webfly_ymarket_GetPrice($product_id, &$arPrices, &$arOffers, $isRoundPrice, $isOldPrice) {
        global $baseCurrency;
        $arPrice = CCatalogProduct::GetOptimalPrice($product_id, 1, $GLOBALS["USER"]->GetUserGroupArray(), "N", $arPrices);
        if ($arPrice["CURRENCY"] != $baseCurrency)
        {
            $arPrice["DISCOUNT_PRICE"] = CCurrencyRates::ConvertCurrency($arPrice["DISCOUNT_PRICE"], $baseCurrency, $arPrice["PRICE"]["CURRENCY"]);
            $arPrice["DISCOUNT_PRICE"] = round($arPrice["DISCOUNT_PRICE"], 2);
        }
        if ($isRoundPrice == "Y")// Round Price if is Flag in arParams
        {
            $arPrices[0]["PRICE"] = round($arPrices[0]["PRICE"]);
            $arPrices[0]["PRICE"] = $arPrices[0]["PRICE"] . ".00";
            $arPrice["DISCOUNT_PRICE"] = round($arPrice["DISCOUNT_PRICE"]);
            $arPrice["DISCOUNT_PRICE"] = $arPrice["DISCOUNT_PRICE"] . ".00";
        }
        if ($arPrices[0]["PRICE"] > $arPrice["DISCOUNT_PRICE"] and $isOldPrice == "Y")//new
        {
            $arOffers[$product_id]["OLD_PRICE"] = $arPrices[0]["PRICE"];
        }
        $arOffers[$product_id]["PRICE"] = $arPrice["DISCOUNT_PRICE"];
        $arOffers[$product_id]["CURRENCY"] = $arPrice["PRICE"]["CURRENCY"];
        CCatalogDiscount::ClearDiscountCache(array('PRODUCT' => 'Y'));
    }

}

if (!function_exists("webfly_ymarket_GetMinPrice"))
{

    function webfly_ymarket_GetMinPrice($product_id, $arPriceTypesID) {
        if (CModule::IncludeModule("catalog"))
        {
            $dbProductPrices = CPrice::GetList(array(), array("PRODUCT_ID" => $product_id, "CATALOG_GROUP_ID" => $arPriceTypesID)); // ->Fetch();
            $arPrices = array();
            while ($arProductPrice = $dbProductPrices->Fetch()) {
                $arPrices[] = $arProductPrice;
            }
            $arPrice = CCatalogProduct::GetOptimalPrice($product_id, 1, $GLOBALS["USER"]->GetUserGroupArray(), "N", $arPrices);
            return $arPrice['DISCOUNT_PRICE'];
        }
        return false;
    }

}

if (!function_exists("webfly_ymarket_GetCurrs"))
{

    function webfly_ymarket_GetCurrs($product_id, $arPriceTypesID) {
        if (CModule::IncludeModule("catalog"))
        {
            $productPrice = CPrice::GetList(array(), array("PRODUCT_ID" => $product_id, "CATALOG_GROUP_ID" => $arPriceTypesID))->Fetch();

            $currency = $productPrice["CURRENCY"];
        }
        return $currency;
    }

}


/* AGENT */
$agentResult = CAgent::GetList(array("ID" => "DESC"), array("MODULE_ID" => "webfly.ymarket", "NAME" => "wfYmarketAgent();"));
$agentMass = array();
while ($agentob = $agentResult->GetNext()) {
    $agentMass = $agentob;
}
if ($arParams ["AGENT_CHECK"] == "Y")
{
    if (empty($agentMass))
    {
        $arResult["AGENT_FOLDER"] = $APPLICATION->GetCurDir();
        COption::SetOptionString("webfly.ymarket", "agentFolder", $arResult["AGENT_FOLDER"], false, false);

        /* Add webfly.ymarket's agent */
        CAgent::AddAgent(
            "wfYmarketAgent();", // function name
            "webfly.ymarket", // module's ID
            "N", 86400, // interval
            "", // first check - now
            "Y", // agent active
            "", // first start - now
            30);
    }
}
else
{
    if (!empty($agentMass))
    {
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

$bDesignMode = is_object($GLOBALS["USER"]) && $GLOBALS["USER"]->IsAdmin();

$bSaveInFile = $arParams["SAVE_IN_FILE"] == "Y";

if (!$bDesignMode or $bSaveInFile)
{
    $arResult["SAVE_IN_FILE"] = $bSaveInFile;

    if (!$bSaveInFile)
    {
        $APPLICATION->RestartBuffer();
        header("Content-Type: text/xml; charset=" . SITE_CHARSET);
        header("Pragma: no-cache");
    }
}
else
{
    echo "<br/><b>" . GetMessage("ADMIN_TEXT") . "</b><br/>";
    return;
}

/* * ***********************************************************************
  Work with cache
 * *********************************************************************** */
$cache_id = serialize($arrFilter) . serialize($arParams); //.$USER->GetGroups() ;
$cache_folder = '/y-market';

if ($arParams["CACHE_NON_MANAGED"] == 'Y')
{
    $obCache = new CPHPCache;
    $bCache = $obCache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_folder);
}
else
{
    $bCache = $this->StartResultCache(false, $cache_id, $cache_folder);
}

if ($bCache)
{
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

    if (!$bCatalog && !empty($arParams["PRICE_CODE"]))
    {
        $arSelect[] = "PROPERTY_" . $arParams["PRICE_CODE"];
    }

    if ($arParams['MORE_PHOTO'])
    {
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

    if ($arParams['IBLOCK_AS_CATEGORY'] == 'Y')
    {
        unset($arFilter["SECTION_ACTIVE"]);
        unset($arFilter["SECTION_GLOBAL_ACTIVE"]);
    }

    if ($arParams["DO_NOT_INCLUDE_SUBSECTIONS"] == "Y")
        $arFilter["INCLUDE_SUBSECTIONS"] = "N";

    if ((count($arParams["IBLOCK_SECTION"]) == 1 && $arParams["IBLOCK_SECTION"][0] == 0) ||
        !$arParams["IBLOCK_SECTION"])
    {
        unset($arFilter["SECTION_ID"]);
    }

    $arSort = array(
      "ID" => "DESC",
    );


    $i = 0;

    //EXECUTE

    if ($arParams["IBLOCK_TYPE"])
    {
        $rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("ID" => $arParams["IBLOCK_ID_IN"], "TYPE" => $arParams["IBLOCK_TYPE"], "ACTIVE" => "Y"));
        $arFilter["IBLOCK_TYPE"] = $arParams["IBLOCK_TYPE"];
    }
    else
    {
        $rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("ID" => $arParams["IBLOCK_ID_IN"], "TYPE" => $arParams["IBLOCK_TYPE_LIST"], "ACTIVE" => "Y"));
        $arFilter["IBLOCK_TYPE"] = $arParams["IBLOCK_TYPE_LIST"];
    }

    $arSKUiblockID = array();

    while ($res = $rsIBlock->GetNext()) {
        if ($arParams['IBLOCK_AS_CATEGORY'] == 'Y')
        {
            $arResult["CATEGORIES"][$res["ID"]] = Array("ID" => $res["ID"], "NAME" => xml_creator($res["NAME"], true), "CODE" => $res["CODE"]);
        }

        if ($bCatalog)
        {
            $rsSKU = CCatalog::GetList(array(), array("PRODUCT_IBLOCK_ID" => $res["ID"]), false, false, array("IBLOCK_ID"));
            if ($arSKUiBlock = $rsSKU->Fetch())
            {
                $arSKUiblockID[$res["ID"]] = $arSKUiBlock["IBLOCK_ID"];
            }
            unset($rsSKU);
        }
    }
    unset($rsIBlock);

//fetch sections into categories list
    if ((count($arParams["IBLOCK_SECTION"]) == 1 && $arParams["IBLOCK_SECTION"][0] == 0))
    {
        $filter = Array("IBLOCK_TYPE" => $arFilter["IBLOCK_TYPE"], "IBLOCK_ID" => $arParams["IBLOCK_ID_IN"], "ACTIVE" => "Y", "IBLOCK_ACTIVE" => "Y", "GLOBAL_ACTIVE" => "Y");
        $bSections = false;
    }
    else
    {
        $filter = Array("IBLOCK_TYPE" => $arFilter["IBLOCK_TYPE"], "IBLOCK_ID" => $arParams["IBLOCK_ID_IN"], "ID" => $arParams["IBLOCK_SECTION"], "ACTIVE" => "Y", "IBLOCK_ACTIVE" => "Y", "GLOBAL_ACTIVE" => "Y");
        $bSections = true;
    }

    if ($arParams['IBLOCK_AS_CATEGORY'] == 'Y')
    {
        unset($filter['ACTIVE']);
        unset($filter['GLOBAL_ACTIVE']);
    }

    $db_acc = CIBlockSection::GetList(array("left_margin" => "asc"), $filter, false, array("ID", "NAME", "IBLOCK_ID", "CODE", "IBLOCK_SECTION_ID", "LEFT_MARGIN", "RIGHT_MARGIN", "DEPTH_LEVEL"));

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
          "PARENT" => ($arParams['IBLOCK_AS_CATEGORY'] == 'Y') ? $arAcc["IBLOCK_ID"] : NULL
        );

        if ($arParams["DO_NOT_INCLUDE_SUBSECTIONS"] != "Y" && $bSections)
        {
            $subFilter = array(
              'IBLOCK_ID' => $arAcc['IBLOCK_ID'],
              '>LEFT_MARGIN' => $arAcc['LEFT_MARGIN'],
              '<RIGHT_MARGIN' => $arAcc['RIGHT_MARGIN'],
              '>DEPTH_LEVEL' => $arAcc['DEPTH_LEVEL']
            );

            $db_sub = CIBlockSection::GetList(array("left_margin" => "asc"), array_merge($filter, $subFilter), false, array("ID", "NAME", "IBLOCK_ID", "CODE", "IBLOCK_SECTION_ID"));

            while ($arAcc2 = $db_sub->Fetch()) {
                $id2 = $arAcc2["IBLOCK_ID"] . $arAcc2["ID"];
                $arResult["CATEGORIES"][$id2] = Array(
                  "ID" => $id2,
                  "CODE" => $arAcc2["CODE"],
                  "NAME" => xml_creator($arAcc2["NAME"], true),
                  "PARENT" => ($arParams['IBLOCK_AS_CATEGORY'] == 'Y') ? $arAcc2["IBLOCK_ID"] : NULL
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

//fetch elements
    $arParams["BIG_CATALOG_PROP"] = trim($arParams["BIG_CATALOG_PROP"]);
    if (!empty($arParams["BIG_CATALOG_PROP"]) and $arParams["SAVE_IN_FILE"] == "Y")
    {
        $wf_limit = $arParams["BIG_CATALOG_PROP"];

        if (empty($_GET["WF_PAGE"]))
        {
            unset($_SESSION["FINISH"]);
            $arResult["WF_NUM"] = 1;
        }
        else
        {
            if ($_SESSION["FINISH"] == "Yes")
                LocalRedirect($APPLICATION->GetCurDir());
            else
                $arResult["WF_NUM"] = $_GET["WF_PAGE"];
        }

        $arResult["WF_CURR"] = $wf_limit * $arResult["WF_NUM"];

        $rsElements = CIBlockElement::Getlist($arSort, array_merge($arrFilter, $arFilter), false, array("nPageSize" => $wf_limit, "iNumPage" => $arResult["WF_NUM"]), $arSelect);

        $arResult["WF_FULL"] = $rsElements->SelectedRowsCount();
    }
    else
    {
        $rsElements = CIBlockElement::Getlist($arSort, array_merge($arrFilter, $arFilter), false, false, $arSelect);
    }

    while ($arOffer = $rsElements->GetNext()) {
        $arOfferID[] = $arOffer["ID"];
        $arOffer["SKU"] = array();
        $arOffers[$arOffer["ID"]] = $arOffer;
    }
    unset($rsElements);

//work with module 'catalog'

    if ($bCatalog && $arParams['PRICE_FROM_IBLOCK'] != 'Y')
    {
        if (empty($arSKUiblockID))
        {
            $arAllID = $arOfferID; //ID of SKU and offers without any SKU
        }
        else
        {
            //fetch SKU
            $arOfferInOb = CIBlockElement::GetList(array($arParams['SKU_PROPERTY'] => 'DESC'), array("IBLOCK_ID" => $arSKUiblockID, $arParams['SKU_PROPERTY'] => $arOfferID, 'ACTIVE' => 'Y'), false, false, $arSelect);

            $arAllID = array(); //ID of SKU and offers without any SKU
            $productKey = $arParams['SKU_PROPERTY'] . '_VALUE';

            while ($arOfferIn = $arOfferInOb->GetNext()) {
                $arAllID[] = $arOfferIn["ID"];
                $productID = $arOfferIn[$productKey];
                $arOffers[$productID]["SKU"][] = $arOfferIn["ID"];
                $arOffers[$arOfferIn["ID"]] = $arOfferIn;
            }
            unset($arOfferInOb);

            foreach ($arOfferID as $offerID)
            {
                if (empty($arOffers[$offerID]["SKU"]))
                    $arAllID[] = $offerID;
            }
        }

        //process catalog products
        $arProductSelect = array(
          "ID",
          "QUANTITY",
          "QUANTITY_TRACE"
        );

        $dbProducts = CCatalogProduct::GetList(array("ID" => "DESC"), array("@ID" => $arAllID), false, false, $arProductSelect);

        while ($tr = $dbProducts->Fetch()) {
            $arOffers[$tr["ID"]]["AVAIBLE"] = "true";
            $arOffers[$tr["ID"]]["QUANTITY"] = $tr["QUANTITY"];

            if ($tr["QUANTITY_TRACE"] == "N")
                continue;
            if ($tr["QUANTITY"] > 0)
                continue;

            $arOffers[$tr["ID"]]["AVAIBLE"] = "false";
        }
        unset($tr);
        unset($dbProducts);

        //fetch price types
        $dbPriceTypes = CCatalogGroup::GetList(array("SORT" => "ASC"), array("NAME" => $arParams["PRICE_CODE"], "CAN_BUY" => "Y"));

        while ($arPriceType = $dbPriceTypes->Fetch()) {
            $arPriceTypesID[] = $arPriceType['ID'];
        }
        unset($dbPriceTypes);

        //fetch and process product prices
        $arPriceSelect = array('PRODUCT_ID', 'PRICE', 'CURRENCY', 'CATALOG_GROUP_ID');
        $dbProductPrices = CPrice::GetList(array("PRODUCT_ID" => "DESC"), array("@PRODUCT_ID" => $arAllID, "@CATALOG_GROUP_ID" => $arPriceTypesID), false, false, $arPriceSelect);

        $arPrices = array();
        if (count($arPriceTypesID) > 1)
        {
            $arProductPrice = $dbProductPrices->GetNext();
            $product_id = $arProductPrice["PRODUCT_ID"];
            $arPrices[] = $arProductPrice;
            while ($arProductPrice = $dbProductPrices->GetNext()) {
                if ($arProductPrice["PRODUCT_ID"] != $product_id)
                {
                    webfly_ymarket_GetPrice($product_id, $arPrices, $arOffers, $arParams["PRICE_ROUND"], $arParams["OLD_PRICE"]);

                    $product_id = $arProductPrice["PRODUCT_ID"];
                    $arPrices = array();
                }
                $arPrices[] = $arProductPrice;
            }
            webfly_ymarket_GetPrice($product_id, $arPrices, $arOffers, $arParams["PRICE_ROUND"], $arParams["OLD_PRICE"]);
        }
        else if ($arParams["DISCOUNTS"] == 'PRICE_ONLY')
        {
            while ($arPrice = $dbProductPrices->GetNext()) {
                $arOffers[$arPrice["PRODUCT_ID"]]["PRICE"] = $arPrice["PRICE"];
                $arOffers[$arPrice["PRODUCT_ID"]]["CURRENCY"] = $arPrice["CURRENCY"];
            }
        }
        else
        {
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
            for ($i = 0; $i < $arr_length; $i++)
            {
                webfly_ymarket_GetPrice($arAllPricesHolder[$i][0]["PRODUCT_ID"], $arAllPricesHolder[$i], $arOffers, $arParams["PRICE_ROUND"], $arParams["OLD_PRICE"]);
            }
            unset($arAllPricesHolder);
        }
        unset($dbProductPrices);
        CCatalogDiscount::ClearDiscountCache(array('SECTIONS' => 'Y', 'SECTION_CHAINS' => 'Y'));
    }

    $arResult['OFFER'] = array();
    $arResult['CURRENCIES'] = array();
    //$arResult['WF_AMOUNTS']=array();

    /* OFFER ITERATION */

    foreach ($arOfferID as &$offerID)
    {
        $arOffer = & $arOffers[$offerID];

        if ($bCatalog && empty($arOffer["SKU"]) && $arParams['PRICE_FROM_IBLOCK'] != 'Y')
        {
            if (intval($arOffer["PRICE"]) <= 0 && $arParams['PRICE_REQUIRED'] != 'N')
                continue;
            if ($arParams["IBLOCK_ORDER"] != "Y" && $arOffer["AVAIBLE"] == "false")
                continue;
        }

        //setting offer pictures
        if ($arOffer["DETAIL_PICTURE"])
        {
            $db_file = CFile::GetByID($arOffer["DETAIL_PICTURE"]);
            if ($ar_file = $db_file->Fetch())
                $arOffer["PICTURE"] = $ar_file["~src"] ? $ar_file["~src"] : $http . "://" . $_SERVER["SERVER_NAME"] . "/" . ( COption::GetOptionString("main", "upload_dir", "upload")) . "/" . $ar_file["SUBDIR"] . "/" . implode("/", array_map("rawurlencode", explode("/", $ar_file["FILE_NAME"])));
            unset($ar_file);
            unset($db_file);
        }

        if ($arOffer["PREVIEW_PICTURE"] && !$arOffer["PICTURE"])
        {
            $db_file = CFile::GetByID($arOffer["PREVIEW_PICTURE"]);
            if ($ar_file = $db_file->Fetch())
                $arOffer["PICTURE"] = $ar_file["~src"] ? $ar_file["~src"] : $http . "://" . $_SERVER["SERVER_NAME"] . "/" . (COption::GetOptionString("main", "upload_dir", "upload")) . "/" . $ar_file["SUBDIR"] . "/" . implode("/", array_map("rawurlencode", explode("/", $ar_file["FILE_NAME"])));
            unset($ar_file);
            unset($db_file);
        }

        if (isset($arParams["MORE_PHOTO"]) && $arParams["MORE_PHOTO"] != "WF_EMPT")
        {
            $ph = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("value_id" => "asc"), Array("CODE" => $arParams["MORE_PHOTO"]));
            $arOffer["MORE_PHOTO"] = array();

            while (($ob = $ph->GetNext()) && count($arOffer["MORE_PHOTO"]) < 10) {
                $arFile = CFile::GetFileArray($ob["VALUE"]);
                if (!empty($arFile))
                {
                    if (strpos($arFile["SRC"], $http) === false)
                    {
                        $pic = $http . "://" . $_SERVER["SERVER_NAME"] . implode("/", array_map("rawurlencode", explode("/", $arFile["SRC"])));
                    }
                    else
                    {
                        $ar = explode($http . "://", $arFile["SRC"]);
                        $pic = $http . "://" . implode("/", array_map("rawurlencode", explode("/", $ar[1])));
                    }
                    $arOffer["MORE_PHOTO"][] = $pic;
                }
                unset($ob);
            }
            unset($ph);

            if (!$arOffer["PICTURE"] && is_array($arOffer["MORE_PHOTO"]))
                $arOffer['PICTURE'] = array_shift($arOffer["MORE_PHOTO"]);
            $arOffer["MORE_PHOTO"] = array_slice($arOffer["MORE_PHOTO"], 0, 9);
        }


        /* UTM */
        if ($arParams ["UTM_CHECK"] == "Y")
        {
            //Take utm-source properties
            if (!empty($arParams["UTM_SOURCE"]) and $arParams["UTM_SOURCE"] != "0")
            {
                $isExistProp = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array("CODE" => $arParams["UTM_SOURCE"]))->Fetch();
                if ($isExistProp)
                {
                    $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams["UTM_SOURCE"]))->Fetch();
                    $arOffer["UTM_SOURCE"] = $arProps["VALUE_ENUM"] ? xml_creator($arProps["VALUE_ENUM"], true) : xml_creator($arProps["VALUE"], true);
                    $arOffer["UTM_SOURCE"] = str_replace("'", "&apos;", $arOffer["UTM_SOURCE"]);
                }
                else
                {
                    $arOffer["UTM_SOURCE"] = xml_creator($arParams["UTM_SOURCE"], true);
                    $arOffer["UTM_SOURCE"] = str_replace("'", "&apos;", $arOffer["UTM_SOURCE"]);
                }
                if ($arOffer["UTM_SOURCE"] == false)
                    $arOffer["UTM_SOURCE"] = "";
                unset($arProps);
            }

            //Take utm-campaign properties
            if ($arParams ["UTM_CAMPAIGN"] == "0" or empty($arParams ["UTM_CAMPAIGN"]))
            {
                $wf_arGr = CIBlockElement::GetElementGroups($arOffer["ID"]);
                $wf_ar_group = $wf_arGr->Fetch();
                $wf_groupid = $wf_ar_group["ID"];
                $res = CIBlockSection::GetByID($wf_groupid);
                if ($ar_res = $res->GetNext())
                    $group_code = $ar_res['CODE'];
                $group_code = xml_creator($group_code, true);
                $group_code = str_replace("'", "&apos;", $group_code);
                $arOffer["UTM_CAMPAIGN"] = $group_code;
                unset($res);
                unset($wf_arGr);
                unset($wf_ar_group);
                if ($arParams["IBLOCK_AS_CATEGORY"] == 'Y')
                {
                    $arOffer["UTM_CAMPAIGN"] = $arResult["CATEGORIES"][$arOffer["IBLOCK_ID"]]["CODE"];
                }
                if ($arOffer["UTM_CAMPAIGN"] == false)
                    $arOffer["UTM_CAMPAIGN"] = "";
            }
            else
            {
                $isExistProp = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array("CODE" => $arParams["UTM_CAMPAIGN"]))->Fetch();
                if ($isExistProp)
                {
                    $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams["UTM_CAMPAIGN"]))->Fetch();
                    $arOffer["UTM_CAMPAIGN"] = $arProps["VALUE_ENUM"] ? xml_creator($arProps["VALUE_ENUM"], true) : xml_creator($arProps["VALUE"], true);
                    $arOffer["UTM_CAMPAIGN"] = str_replace("'", "&apos;", $arOffer["UTM_CAMPAIGN"]);
                }
                else
                {
                    $arOffer["UTM_CAMPAIGN"] = xml_creator($arParams["UTM_CAMPAIGN"], true);
                    $arOffer["UTM_CAMPAIGN"] = str_replace("'", "&apos;", $arOffer["UTM_CAMPAIGN"]);
                }
                if ($arOffer["UTM_CAMPAIGN"] == false)
                    $arOffer["UTM_CAMPAIGN"] = "";
                unset($arProps);
            }

            //Take utm-medium properties
            if (!empty($arParams["UTM_MEDIUM"]) and $arParams["UTM_MEDIUM"] != "0")
            {
                $isExistProp = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array("CODE" => $arParams["UTM_MEDIUM"]))->Fetch();
                if ($isExistProp)
                {
                    $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams["UTM_MEDIUM"]))->Fetch();
                    $arOffer["UTM_MEDIUM"] = $arProps["VALUE_ENUM"] ? xml_creator($arProps["VALUE_ENUM"], true) : xml_creator($arProps["VALUE"], true);
                    $arOffer["UTM_MEDIUM"] = str_replace("'", "&apos;", $arOffer["UTM_MEDIUM"]);
                }
                else
                {
                    $arOffer["UTM_MEDIUM"] = xml_creator($arParams["UTM_MEDIUM"], true);
                    $arOffer["UTM_MEDIUM"] = str_replace("'", "&apos;", $arOffer["UTM_MEDIUM"]);
                }
                if ($arOffer["UTM_MEDIUM"] == false)
                    $arOffer["UTM_MEDIUM"] = "";
                unset($arProps);
            }

            //Take utm-term properties
            if (!empty($arParams["UTM_TERM"]) and $arParams["UTM_TERM"] != "0")
            {
                $isExistProp = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array("CODE" => $arParams["UTM_TERM"]))->Fetch();
                if ($isExistProp)
                {
                    $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams["UTM_TERM"]))->Fetch();
                    $arOffer["UTM_TERM"] = $arProps["VALUE_ENUM"] ? xml_creator($arProps["VALUE_ENUM"], true) : xml_creator($arProps["VALUE"], true);
                    $arOffer["UTM_TERM"] = str_replace("'", "&apos;", $arOffer["UTM_TERM"]);
                }
                else
                {
                    $arOffer["UTM_TERM"] = xml_creator($arParams["UTM_TERM"], true);
                    $arOffer["UTM_TERM"] = str_replace("'", "&apos;", $arOffer["UTM_TERM"]);
                }
                if ($arOffer["UTM_TERM"] == false)
                    $arOffer["UTM_TERM"] = "";
                unset($arProps);
            }
            else
            {
                $arOffer["UTM_TERM"] = $arOffer["CODE"];
            }

            //offer URL
            if (empty($arOffer["UTM_SOURCE"]))
                $utm_source = "";
            else
                $utm_source = "?utm_source=" . $arOffer["UTM_SOURCE"];

            if (empty($arOffer["UTM_CAMPAIGN"]))
            {
                $utm_campaign = "";
            }
            else
            {
                if (empty($arOffer["UTM_SOURCE"]))
                    $utm_campaign = "?utm_campaign=" . $arOffer["UTM_CAMPAIGN"];
                else
                    $utm_campaign = "&amp;utm_campaign=" . $arOffer["UTM_CAMPAIGN"];
            }

            if (empty($arOffer["UTM_MEDIUM"]))
            {
                $utm_medium = "";
            }
            else
            {
                if (empty($arOffer["UTM_CAMPAIGN"]))
                    $utm_medium = "?utm_medium=" . $arOffer["UTM_MEDIUM"];
                else
                    $utm_medium = "&amp;utm_medium=" . $arOffer["UTM_MEDIUM"];
            }

            if (empty($arOffer["UTM_TERM"]))
            {
                $utm_term = "";
            }
            else
            {
                if (empty($arOffer["UTM_MEDIUM"]))
                    $utm_term = "?utm_term=" . $arOffer["UTM_TERM"];
                else
                    $utm_term = "&amp;utm_term=" . $arOffer["UTM_TERM"];
            }

            $arOffer["URL"] = $http . "://" . $_SERVER["SERVER_NAME"] . $arOffer["DETAIL_PAGE_URL"] . $utm_source . $utm_campaign . $utm_medium . $utm_term;
        }
        else
        {
            //offer URL
            $arOffer["URL"] = $http . "://" . $_SERVER["SERVER_NAME"] . $arOffer["DETAIL_PAGE_URL"];
        }

        /* LOCAL_DELIVERY_COST_OFFER */
        if (!empty($arParams["LOCAL_DELIVERY_COST_OFFER"]))
        {
            $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams["LOCAL_DELIVERY_COST_OFFER"]))->Fetch();
            $arOffer["LOCAL_DELIVERY_COST_OFFER"] = $arProps["VALUE_ENUM"] ? strip_tags($arProps["VALUE_ENUM"]) : strip_tags($arProps["VALUE"]);
            $arOffer["LOCAL_DELIVERY_COST_OFFER"] = xml_creator($arOffer["LOCAL_DELIVERY_COST_OFFER"], true);
            $arOffer["LOCAL_DELIVERY_COST_OFFER"] = str_replace("'", "&apos;", $arOffer["LOCAL_DELIVERY_COST_OFFER"]);
            unset($arProps);
        }
        /* STORE_OFFER */
        if (!empty($arParams["STORE_OFFER"]))
        {
            $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams["STORE_OFFER"]))->Fetch();
            $arOffer["STORE_OFFER"] = $arProps["VALUE_ENUM"] ? strip_tags($arProps["VALUE_ENUM"]) : strip_tags($arProps["VALUE"]);
            $arOffer["STORE_OFFER"] = xml_creator($arOffer["STORE_OFFER"], true);
            $arOffer["STORE_OFFER"] = str_replace("'", "&apos;", $arOffer["STORE_OFFER"]);
            unset($arProps);
        }
        /* PICKUP_OFFER */
        if (!empty($arParams["STORE_PICKUP"]))
        {
            $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams["STORE_PICKUP"]))->Fetch();
            $arOffer["STORE_PICKUP"] = $arProps["VALUE_ENUM"] ? strip_tags($arProps["VALUE_ENUM"]) : strip_tags($arProps["VALUE"]);
            $arOffer["STORE_PICKUP"] = xml_creator($arOffer["STORE_PICKUP"], true);
            $arOffer["STORE_PICKUP"] = str_replace("'", "&apos;", $arOffer["STORE_PICKUP"]);
            unset($arProps);
        }

        /* typePrefix */
        if (!empty($arParams["PREFIX_PROP"]))
        {
            $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams["PREFIX_PROP"]))->Fetch();
            $arOffer["PREFIX_PROP"] = $arProps["VALUE_ENUM"] ? strip_tags($arProps["VALUE_ENUM"]) : strip_tags($arProps["VALUE"]);
            $arOffer["PREFIX_PROP"] = xml_creator($arOffer["PREFIX_PROP"], true);
            $arOffer["PREFIX_PROP"] = str_replace("'", "&apos;", $arOffer["PREFIX_PROP"]);
            unset($arProps);
        }

        /* ƒŒœŒÀÕ»“≈À‹Õ€≈ —¬Œ…—“¬¿ “Œ¬¿–Œ¬, ¬€¬Œƒ»Ã€≈ ¬ Œœ»—¿Õ»» Õ¿◊¿ÀŒ */
        if (!empty($arParams ["PROPDUCT_PROP"]))
        {
            foreach ($arParams["PROPDUCT_PROP"] as $key => $productProp)
            {
                if (!empty($productProp))
                {
                    $productProp = trim($productProp);
                    $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $productProp))->Fetch();

                    //Õ‡Á‚‡ÌËÂ
                    $arProps["NAME"] = xml_creator($arProps["NAME"], true);
                    $arProps["NAME"] = str_replace("'", "&apos;", $arProps["NAME"]);

                    //«Ì‡˜ÂÌËÂ
                    if (!empty($arProps["USER_TYPE_SETTINGS"]["TABLE_NAME"]))//—Ô‡‚Ó˜ÌËÍ
                    {
                        $tableName = $arProps["USER_TYPE_SETTINGS"]["TABLE_NAME"];
                        $res = $DB->Query("select id from b_hlblock_entity where table_name = '" . $tableName . "'")->Fetch();

                        $hlbl_product = intval($res["id"]);
                        $hlblock_product = HL\HighloadBlockTable::getById($hlbl_product)->fetch();

                        if (!empty($hlblock_product))
                        {
                            $entity_product = HL\HighloadBlockTable::compileEntity($hlblock_product);
                            $entity_data_class_product = $entity_product->getDataClass();
                            $bookProp = $entity_data_class_product::getList(array("select" => array("UF_NAME"), "filter" => array("UF_XML_ID" => $arProps["VALUE"])))->fetch();
                            $arProps["VAL"] = $bookProp["UF_NAME"];
                        }
                        unset($tableName);
                        unset($bookProp);
                    }
                    else
                    {
                        $arProps["VAL"] = $arProps["VALUE_ENUM"] ? strip_tags($arProps["VALUE_ENUM"]) : strip_tags($arProps["VALUE"]);
                    }
                    $arProps["VAL"] = xml_creator($arProps["VAL"], true);
                    $arProps["VAL"] = str_replace("'", "&apos;", $arProps["VAL"]);

                    //Õ‡ÍÓÔÎÂÌËÂ ‚ ÔÂÂÏÂÌÌÛ˛
                    if (!empty($arProps["VAL"]))
                    {
                        if (empty($arOffer["DOP_PROPS"]))
                            $arOffer["DOP_PROPS"] = $arProps["NAME"] . ": " . $arProps["VAL"];
                        else
                            $arOffer["DOP_PROPS"] = $arOffer["DOP_PROPS"] . ", " . $arProps["NAME"] . ": " . $arProps["VAL"];
                    }
                    unset($arProps);
                }
            }
        }
        /* ƒŒœŒÀÕ»“≈À‹Õ€≈ —¬Œ…—“¬¿ “Œ¬¿–Œ¬, ¬€¬Œƒ»Ã€≈ ¬ Œœ»—¿Õ»»  ŒÕ≈÷ */

        //setting offer description
        if ($arOffer["PREVIEW_TEXT"])
        {
            $arOffer["PREVIEW_TEXT"] = xml_creator(($arOffer["PREVIEW_TEXT_TYPE"] == "html" ? preg_replace_callback("'&[^;]*;'", "charset_modifier", strip_tags($arOffer["~PREVIEW_TEXT"])) : $arOffer["~PREVIEW_TEXT"]), true);
        }

        if ($arOffer["DETAIL_TEXT"])
        {
            $arOffer["DETAIL_TEXT"] = xml_creator(($arOffer["DETAIL_TEXT_TYPE"] == "html" ? preg_replace_callback("'&[^;]*;'", "charset_modifier", strip_tags($arOffer["~DETAIL_TEXT"])) : $arOffer["~DETAIL_TEXT"]), true);
        }

        $arOffer["DESCRIPTION"] = $arOffer["PREVIEW_TEXT"] ? $arOffer["PREVIEW_TEXT"] : $arOffer["DETAIL_TEXT"];

        if ($arParams["DETAIL_TEXT_PRIORITET"] == "Y")
        {
            $arOffer["DESCRIPTION"] = $arOffer["DETAIL_TEXT"] ? $arOffer["DETAIL_TEXT"] : $arOffer["PREVIEW_TEXT"];
        }
        if ($arParams["NO_DESCRIPTION"] == "Y")
        {
            $arOffer["DESCRIPTION"] = '';
        }

        $arOffer["CATEGORY"] = $arOffer["IBLOCK_ID"] . $arOffer["IBLOCK_SECTION_ID"];

        if (!array_key_exists($arOffer["CATEGORY"], $arResult["CATEGORIES"]) && $arOffer["IBLOCK_SECTION_ID"])
        {
            $arGr = CIBlockElement::GetElementGroups($arOffer["ID"]);
            while ($ar_group = $arGr->Fetch()) {
                if (!array_key_exists($arOffer["IBLOCK_ID"] . $ar_group["ID"], $arResult["CATEGORIES"]))
                    continue;
                $arOffer["CATEGORY"] = $arOffer["IBLOCK_ID"] . $ar_group["ID"];
                break;
            }
        }

        if ($arParams['SECTION_AS_VENDOR'] == 'Y')
        {
            if (!empty($arOffer['IBLOCK_SECTION_ID']))
            {
                $arOffer["DEVELOPER"] = $arResult["CATEGORIES"][$arOffer["IBLOCK_ID"] . $arOffer['IBLOCK_SECTION_ID']]["NAME"];
            }
        }

        if ($arParams["MARKET_CATEGORY_CHECK"] == "Y")
        {
            if (!empty($arParams['MARKET_CATEGORY_PROP']))
            {
                $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams["MARKET_CATEGORY_PROP"]))->Fetch();

                $arOffer["MARKET_CATEGORY"] = $arProps["VALUE_ENUM"] ? $arProps["VALUE_ENUM"] : $arProps["VALUE"];
                unset($arProps);
            }

            if (!$arOffer["MARKET_CATEGORY"])
            {
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
                if ($arParams["IBLOCK_AS_CATEGORY"] == 'Y')
                {
                    $arOffer["MARKET_CATEGORY"] = $arResult["CATEGORIES"][$arOffer["IBLOCK_ID"]]["NAME"]
                        . '/'
                        . $arOffer["MARKET_CATEGORY"];
                }
                $arOffer["MARKET_CATEGORY"] = substr($arOffer["MARKET_CATEGORY"], 0, -1);
            }
        }

        //setting offer name
        if (!empty($arParams['NAME_PROP']))
        {
            $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams['NAME_PROP']))->Fetch();
            $arOffer["MODEL"] = $arProps["VALUE_ENUM"] ? $arProps["VALUE_ENUM"] : $arProps["VALUE"];
            unset($arProps);
        }

        if (empty($arOffer["MODEL"]))
        {
            $arOffer["MODEL"] = $arOffer["~NAME"];
        }
        /* Œ¡–≈« ¿ Õ¿«¬¿Õ»ﬂ */
        if (!empty($arParams["NAME_CUT"]))
        {
            $arParams["NAME_CUT"] = trim($arParams["NAME_CUT"]);
            $arOffer["MODEL"] = substr($arOffer["MODEL"], 0, $arParams["NAME_CUT"]);
            $arOffer["MODEL"] = trim($arOffer["MODEL"]);
        }

        $arOffer["MODEL"] = xml_creator($arOffer["MODEL"], true);
        $arOffer["MODEL"] = str_replace("'", "&apos;", $arOffer["MODEL"]);
        /* Œ¡–≈« ¿ Õ¿«¬¿Õ»ﬂ  ŒÕ≈÷ */

        //work with offer SKU
        $flag = 0;
        foreach ($arOffer["SKU"] as &$arOfferInID)
        {
            $arOfferIn = & $arOffers[$arOfferInID];
            $flag = 1;

            //check available status
            if ($arParams["IBLOCK_ORDER"] != "Y" && $arOfferIn["AVAIBLE"] == "false")
                continue;

            if (intval($arOfferIn["PRICE"]) <= 0)
                continue;

            if ($arParams["CURRENCIES_CONVERT"] != "NOT_CONVERT")
            {
                $newval = CCurrencyRates::ConvertCurrency($arOfferIn["PRICE"], $arOfferIn["CURRENCY"], $arParams["CURRENCIES_CONVERT"]);
                /* $curTo=CCurrency::GetByID($arParams["CURRENCIES_CONVERT"]);
                  $arOfferIn["WF_AMOUNT"]=$curTo["AMOUNT"]; */
                $arOfferIn["PRICE"] = round($newval, 2);
                $arOfferIn["CURRENCY"] = $arParams["CURRENCIES_CONVERT"];
            }
            if ($arParams["PRICE_ROUND"] == "Y")
            {
                $arOffer["PRICE"] = round($arOffer["PRICE"]);
                $arOffer["PRICE"] = $arOffer["PRICE"] . ".00";
            }

            if (!in_array($arOfferIn["CURRENCY"], $arResult["CURRENCIES"]))
                $arResult["CURRENCIES"][] = $arOfferIn["CURRENCY"];

            /* if(!in_array($arOfferIn["WF_AMOUNT"],$arResult["WF_AMOUNTS"]))
              $arResult["WF_AMOUNTS"][$curTo["CURRENCY"]]=$arOfferIn["WF_AMOUNT"]; */

            $arOfferIn["CATEGORY"] = $arOffer["CATEGORY"];

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

            /* Œ¡–≈« ¿ Õ¿«¬¿Õ»ﬂ */
            if (!empty($arParams["NAME_CUT"]))
            {
                $arOfferIn["MODEL"] = substr($arOfferIn["MODEL"], 0, $arParams["NAME_CUT"]);
                $arOfferIn["MODEL"] = trim($arOfferIn["MODEL"]);
            }
            $arOfferIn["MODEL"] = xml_creator($arOfferIn["MODEL"], true);
            $arOfferIn["MODEL"] = str_replace("'", "&apos;", $arOfferIn["MODEL"]);
            /* Œ¡–≈« ¿ Õ¿«¬¿Õ»ﬂ  ŒÕ≈÷ */

            if (!$arOfferIn["DETAIL_PAGE_URL"])
            {
                $arOfferIn["URL"] = $arOffer["URL"] . "#" . $arOfferIn["ID"];
            }
            else
            {
                /* UTM */
                if ($arParams ["UTM_CHECK"] == "Y")
                {
                    //Take utm-source properties
                    if (!empty($arParams["UTM_SOURCE"]) and $arParams["UTM_SOURCE"] != "0")
                    {
                        $isExistProp = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array("CODE" => $arParams["UTM_SOURCE"]))->Fetch();
                        if ($isExistProp)
                        {
                            $arProps = CIBlockElement::GetProperty($arOfferIn["IBLOCK_ID"], $arOfferIn["ID"], array("sort" => "asc"), Array("CODE" => $arParams["UTM_SOURCE"]))->Fetch();
                            $arOfferIn["UTM_SOURCE"] = $arProps["VALUE_ENUM"] ? xml_creator($arProps["VALUE_ENUM"], true) : xml_creator($arProps["VALUE"], true);
                            $arOfferIn["UTM_SOURCE"] = str_replace("'", "&apos;", $arOfferIn["UTM_SOURCE"]);
                        }
                        else
                        {
                            $arOfferIn["UTM_SOURCE"] = xml_creator($arParams["UTM_SOURCE"], true);
                            $arOfferIn["UTM_SOURCE"] = str_replace("'", "&apos;", $arOfferIn["UTM_SOURCE"]);
                        }
                        if ($arOfferIn["UTM_SOURCE"] == false)
                            $arOfferIn["UTM_SOURCE"] = "";
                        unset($arProps);
                    }

                    //Take utm-campaign properties
                    if ($arParams ["UTM_CAMPAIGN"] == "0" or empty($arParams ["UTM_CAMPAIGN"]))
                    {
                        $wf_arGr = CIBlockElement::GetElementGroups($arOfferIn["ID"]);
                        $wf_ar_group = $wf_arGr->Fetch();
                        $wf_groupid = $wf_ar_group["ID"];
                        $res = CIBlockSection::GetByID($wf_groupid);
                        if ($ar_res = $res->GetNext())
                            $group_code = $ar_res['CODE'];
                        $group_code = xml_creator($group_code, true);
                        $group_code = str_replace("'", "&apos;", $group_code);
                        $arOfferIn["UTM_CAMPAIGN"] = $group_code;
                        unset($res);
                        unset($wf_arGr);
                        unset($wf_ar_group);
                        if ($arParams["IBLOCK_AS_CATEGORY"] == 'Y')
                        {
                            $arOfferIn["UTM_CAMPAIGN"] = $arResult["CATEGORIES"][$arOfferIn["IBLOCK_ID"]]["CODE"];
                        }
                        if ($arOfferIn["UTM_CAMPAIGN"] == false)
                            $arOfferIn["UTM_CAMPAIGN"] = "";
                    }
                    else
                    {
                        $isExistProp = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array("CODE" => $arParams["UTM_CAMPAIGN"]))->Fetch();
                        if ($isExistProp)
                        {
                            $arProps = CIBlockElement::GetProperty($arOfferIn["IBLOCK_ID"], $arOfferIn["ID"], array("sort" => "asc"), Array("CODE" => $arParams["UTM_CAMPAIGN"]))->Fetch();
                            $arOfferIn["UTM_CAMPAIGN"] = $arProps["VALUE_ENUM"] ? xml_creator($arProps["VALUE_ENUM"], true) : xml_creator($arProps["VALUE"], true);
                            $arOfferIn["UTM_CAMPAIGN"] = str_replace("'", "&apos;", $arOfferIn["UTM_CAMPAIGN"]);
                        }
                        else
                        {
                            $arOfferIn["UTM_CAMPAIGN"] = xml_creator($arParams["UTM_CAMPAIGN"], true);
                            $arOfferIn["UTM_CAMPAIGN"] = str_replace("'", "&apos;", $arOfferIn["UTM_CAMPAIGN"]);
                        }
                        if ($arOfferIn["UTM_CAMPAIGN"] == false)
                            $arOfferIn["UTM_CAMPAIGN"] = "";
                        unset($arProps);
                    }

                    //Take utm-medium properties
                    if (!empty($arParams["UTM_MEDIUM"]) and $arParams["UTM_MEDIUM"] != "0")
                    {
                        $isExistProp = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array("CODE" => $arParams["UTM_MEDIUM"]))->Fetch();
                        if ($isExistProp)
                        {
                            $arProps = CIBlockElement::GetProperty($arOfferIn["IBLOCK_ID"], $arOfferIn["ID"], array("sort" => "asc"), Array("CODE" => $arParams["UTM_MEDIUM"]))->Fetch();
                            $arOfferIn["UTM_MEDIUM"] = $arProps["VALUE_ENUM"] ? xml_creator($arProps["VALUE_ENUM"], true) : xml_creator($arProps["VALUE"], true);
                            $arOfferIn["UTM_MEDIUM"] = str_replace("'", "&apos;", $arOfferIn["UTM_MEDIUM"]);
                        }
                        else
                        {
                            $arOfferIn["UTM_MEDIUM"] = xml_creator($arParams["UTM_MEDIUM"], true);
                            $arOfferIn["UTM_MEDIUM"] = str_replace("'", "&apos;", $arOfferIn["UTM_MEDIUM"]);
                        }
                        if ($arOfferIn["UTM_MEDIUM"] == false)
                            $arOfferIn["UTM_MEDIUM"] = "";
                        unset($arProps);
                    }

                    //Take utm-term properties
                    if (!empty($arParams["UTM_TERM"]) and $arParams["UTM_TERM"] != "0")
                    {
                        $isExistProp = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array("CODE" => $arParams["UTM_TERM"]))->Fetch();
                        if ($isExistProp)
                        {
                            $arProps = CIBlockElement::GetProperty($arOfferIn["IBLOCK_ID"], $arOfferIn["ID"], array("sort" => "asc"), Array("CODE" => $arParams["UTM_TERM"]))->Fetch();
                            $arOfferIn["UTM_TERM"] = $arProps["VALUE_ENUM"] ? xml_creator($arProps["VALUE_ENUM"], true) : xml_creator($arProps["VALUE"], true);
                            $arOfferIn["UTM_TERM"] = str_replace("'", "&apos;", $arOfferIn["UTM_TERM"]);
                        }
                        else
                        {
                            $arOfferIn["UTM_TERM"] = xml_creator($arParams["UTM_TERM"], true);
                            $arOfferIn["UTM_TERM"] = str_replace("'", "&apos;", $arOfferIn["UTM_TERM"]);
                        }
                        if ($arOfferIn["UTM_TERM"] == false)
                            $arOfferIn["UTM_TERM"] = "";
                        unset($arProps);
                    }
                    else
                    {
                        $arOfferIn["UTM_TERM"] = $arOfferIn["CODE"];
                    }

                    //offer URL
                    if (empty($arOfferIn["UTM_SOURCE"]))
                        $utm_source = "";
                    else
                        $utm_source = "?utm_source=" . $arOfferIn["UTM_SOURCE"];

                    if (empty($arOfferIn["UTM_CAMPAIGN"]))
                    {
                        $utm_campaign = "";
                    }
                    else
                    {
                        if (empty($arOfferIn["UTM_SOURCE"]))
                            $utm_campaign = "?utm_campaign=" . $arOfferIn["UTM_CAMPAIGN"];
                        else
                            $utm_campaign = "&amp;utm_campaign=" . $arOfferIn["UTM_CAMPAIGN"];
                    }

                    if (empty($arOfferIn["UTM_MEDIUM"]))
                    {
                        $utm_medium = "";
                    }
                    else
                    {
                        if (empty($arOfferIn["UTM_CAMPAIGN"]))
                            $utm_medium = "?utm_medium=" . $arOfferIn["UTM_MEDIUM"];
                        else
                            $utm_medium = "&amp;utm_medium=" . $arOfferIn["UTM_MEDIUM"];
                    }

                    if (empty($arOfferIn["UTM_TERM"]))
                    {
                        $utm_term = "";
                    }
                    else
                    {
                        if (empty($arOfferIn["UTM_MEDIUM"]))
                            $utm_term = "?utm_term=" . $arOfferIn["UTM_TERM"];
                        else
                            $utm_term = "&amp;utm_term=" . $arOfferIn["UTM_TERM"];
                    }

                    $arOfferIn["URL"] = $http . "://" . $_SERVER["SERVER_NAME"] . $arOfferIn["DETAIL_PAGE_URL"] . $utm_source . $utm_campaign . $utm_medium . $utm_term;
                }
                else
                {
                    $arOfferIn["URL"] = $http . "://" . $_SERVER["SERVER_NAME"] . $arOfferIn["DETAIL_PAGE_URL"];
                }
            }

            /* LOCAL_DELIVERY_COST_OFFER */
            if (!empty($arParams["LOCAL_DELIVERY_COST_OFFER"]))
            {
                $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams["LOCAL_DELIVERY_COST_OFFER"]))->Fetch();
                $arOfferIn["LOCAL_DELIVERY_COST_OFFER"] = $arProps["VALUE_ENUM"] ? strip_tags($arProps["VALUE_ENUM"]) : strip_tags($arProps["VALUE"]);
                $arOfferIn["LOCAL_DELIVERY_COST_OFFER"] = xml_creator($arOfferIn["LOCAL_DELIVERY_COST_OFFER"], true);
                $arOfferIn["LOCAL_DELIVERY_COST_OFFER"] = str_replace("'", "&apos;", $arOfferIn["LOCAL_DELIVERY_COST_OFFER"]);
                unset($arProps);
            }
            /* STORE_OFFER */
            if (!empty($arParams["STORE_OFFER"]))
            {
                $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams["STORE_OFFER"]))->Fetch();
                $arOfferIn["STORE_OFFER"] = $arProps["VALUE_ENUM"] ? strip_tags($arProps["VALUE_ENUM"]) : strip_tags($arProps["VALUE"]);
                $arOfferIn["STORE_OFFER"] = xml_creator($arOfferIn["STORE_OFFER"], true);
                $arOfferIn["STORE_OFFER"] = str_replace("'", "&apos;", $arOfferIn["STORE_OFFER"]);
                unset($arProps);
            }
            /* PICKUP_OFFER */
            if (!empty($arParams["STORE_PICKUP"]))
            {
                $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams["STORE_PICKUP"]))->Fetch();
                $arOfferIn["STORE_PICKUP"] = $arProps["VALUE_ENUM"] ? strip_tags($arProps["VALUE_ENUM"]) : strip_tags($arProps["VALUE"]);
                $arOfferIn["STORE_PICKUP"] = xml_creator($arOfferIn["STORE_PICKUP"], true);
                $arOfferIn["STORE_PICKUP"] = str_replace("'", "&apos;", $arOfferIn["STORE_PICKUP"]);
                unset($arProps);
            }

            /* typePrefix */
            if (!empty($arParams["PREFIX_PROP"]))
            {
                $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams["PREFIX_PROP"]))->Fetch();
                $arOfferIn["PREFIX_PROP"] = $arProps["VALUE_ENUM"] ? strip_tags($arProps["VALUE_ENUM"]) : strip_tags($arProps["VALUE"]);
                $arOfferIn["PREFIX_PROP"] = xml_creator($arOfferIn["PREFIX_PROP"], true);
                $arOfferIn["PREFIX_PROP"] = str_replace("'", "&apos;", $arOfferIn["PREFIX_PROP"]);
                unset($arProps);
            }

            /* ƒŒœŒÀÕ»“≈À‹Õ€≈ —¬Œ…—“¬¿ “Œ–√Œ¬€’ œ–≈ƒÀŒ∆≈Õ»…, ¬€¬Œƒ»Ã€≈ ¬ Œœ»—¿Õ»» Õ¿◊¿ÀŒ */
            if (!empty($arParams ["OFFER_PROP"]))
            {
                foreach ($arParams["OFFER_PROP"] as $key => $offerProp)
                {
                    if (!empty($offerProp))
                    {
                        $offerProp = trim($offerProp);
                        $arProps = CIBlockElement::GetProperty($arOfferIn["IBLOCK_ID"], $arOfferIn["ID"], array("sort" => "asc"), Array("CODE" => $offerProp))->Fetch();

                        //Õ‡Á‚‡ÌËÂ
                        $arProps["NAME"] = xml_creator($arProps["NAME"], true);
                        $arProps["NAME"] = str_replace("'", "&apos;", $arProps["NAME"]);

                        //«Ì‡˜ÂÌËÂ
                        if (!empty($arProps["USER_TYPE_SETTINGS"]["TABLE_NAME"]))//—Ô‡‚Ó˜ÌËÍ
                        {
                            $tableName = $arProps["USER_TYPE_SETTINGS"]["TABLE_NAME"];
                            $res = $DB->Query("select id from b_hlblock_entity where table_name = '" . $tableName . "'")->Fetch();

                            $hlbl_offer = intval($res["id"]);
                            $hlblock_offer = HL\HighloadBlockTable::getById($hlbl_offer)->fetch();

                            if (!empty($hlblock_offer))
                            {
                                $entity_offer = HL\HighloadBlockTable::compileEntity($hlblock_offer);
                                $entity_data_class_offer = $entity_offer->getDataClass();
                                $bookProp = $entity_data_class_offer::getList(array("select" => array("UF_NAME"), "filter" => array("UF_XML_ID" => $arProps["VALUE"])))->fetch();
                                $arProps["VAL"] = $bookProp["UF_NAME"];
                            }
                            unset($tableName);
                            unset($bookProp);
                        }
                        else
                        {
                            $arProps["VAL"] = $arProps["VALUE_ENUM"] ? strip_tags($arProps["VALUE_ENUM"]) : strip_tags($arProps["VALUE"]);
                        }
                        $arProps["VAL"] = xml_creator($arProps["VAL"], true);
                        $arProps["VAL"] = str_replace("'", "&apos;", $arProps["VAL"]);

                        //Õ‡ÍÓÔÎÂÌËÂ ‚ ÔÂÂÏÂÌÌÛ˛
                        if (!empty($arProps["VAL"]))
                        {
                            if (empty($arOfferIn["DOP_PROPS"]))
                                $arOfferIn["DOP_PROPS"] = $arProps["NAME"] . ": " . $arProps["VAL"];
                            else
                                $arOfferIn["DOP_PROPS"] = $arOfferIn["DOP_PROPS"] . ", " . $arProps["NAME"] . ": " . $arProps["VAL"];
                        }
                        unset($arProps);
                    }
                }
            }
            /* ƒŒœŒÀÕ»“≈À‹Õ€≈ —¬Œ…—“¬¿ “Œ–√Œ¬€’ œ–≈ƒÀŒ∆≈Õ»…, ¬€¬Œƒ»Ã€≈ ¬ Œœ»—¿Õ»»  ŒÕ≈÷ */

            if ($arOfferIn["DETAIL_PICTURE"])
            {
                $db_file = CFile::GetByID($arOfferIn["DETAIL_PICTURE"]);
                if ($ar_file = $db_file->Fetch())
                    $arOfferIn["PICTURE"] = $ar_file["~src"] ? $ar_file["~src"] : $http . "://" . $_SERVER["SERVER_NAME"] . "/" . (COption::GetOptionString("main", "upload_dir", "upload")) . "/" . $ar_file["SUBDIR"] . "/" . implode("/", array_map("rawurlencode", explode("/", $ar_file["FILE_NAME"])));
                unset($ar_file);
                unset($db_file);
            }

            if ($arOfferIn["PREVIEW_PICTURE"] && !$arOfferIn["PICTURE"])
            {
                $db_file = CFile::GetByID($arOfferIn["PREVIEW_PICTURE"]);
                if ($ar_file = $db_file->Fetch())
                    $arOfferIn["PICTURE"] = $ar_file["~src"] ? $ar_file["~src"] : "http://" . $_SERVER["SERVER_NAME"] . "/" . (COption::GetOptionString("main", "upload_dir", "upload")) . "/" . $ar_file["SUBDIR"] . "/" . implode("/", array_map("rawurlencode", explode("/", $ar_file["FILE_NAME"])));
                unset($ar_file);
                unset($db_file);
            }

            if (isset($arParams["MORE_PHOTO"]) && $arParams["MORE_PHOTO"] != "WF_EMPT")
            {

                $ph = CIBlockElement::GetProperty($arOfferIn["IBLOCK_ID"], $arOfferIn["ID"], array("sort" => "asc"), Array("CODE" => $arParams["MORE_PHOTO"]));
                $arOfferIn["MORE_PHOTO"] = array();

                while (($ob = $ph->GetNext()) && count($arOfferIn["MORE_PHOTO"]) < 10) {
                    $arFile = CFile::GetFileArray($ob["VALUE"]);
                    if (!empty($arFile))
                    {
                        if (strpos($arFile["SRC"], $http) === false)
                        {
                            $pic = "http://" . $_SERVER["SERVER_NAME"] . implode("/", array_map("rawurlencode", explode("/", $arFile["SRC"])));
                        }
                        else
                        {
                            $ar = explode("http://", $arFile["SRC"]);
                            $pic = "http://" . implode("/", array_map("rawurlencode", explode("/", $ar[1])));
                        }
                        $arOfferIn["MORE_PHOTO"][] = $pic;
                    }
                    unset($ob);
                    unset($arFile);
                }
                unset($ph);
            }

            if (is_array($arOffer["MORE_PHOTO"]))
                foreach ($arOffer["MORE_PHOTO"] as $pic)
                {
                    if (!in_array($pic, $arOfferIn["MORE_PHOTO"]) && count($arOfferIn["MORE_PHOTO"]) < 10)
                        $arOfferIn["MORE_PHOTO"][] = $pic;
                }

            if (!$arOfferIn["PICTURE"])
            {
                if ($arOffer["PICTURE"])
                    $arOfferIn["PICTURE"] = $arOffer["PICTURE"];
                else
                if (is_array($arOfferIn["MORE_PHOTO"]))
                    $arOfferIn["PICTURE"] = array_shift($arOfferIn["MORE_PHOTO"]);
            }
            $arOfferIn["MORE_PHOTO"] = array_slice($arOfferIn["MORE_PHOTO"], 0, 9);

            if ($arOfferIn["PREVIEW_TEXT"])
            {
                $arOfferIn["PREVIEW_TEXT"] = xml_creator(($arOfferIn["PREVIEW_TEXT_TYPE"] == "html" ? preg_replace_callback("'&[^;]*;'", "charset_modifier", strip_tags($arOfferIn["~PREVIEW_TEXT"])) : $arOfferIn["~PREVIEW_TEXT"]), true);
            }

            if ($arOfferIn["DETAIL_TEXT"])
            {
                $arOfferIn["DETAIL_TEXT"] = xml_creator(($arOfferIn["DETAIL_TEXT_TYPE"] == "html" ? preg_replace_callback("'&[^;]*;'", "charset_modifier", strip_tags($arOfferIn["~DETAIL_TEXT"])) : $arOfferIn["~DETAIL_TEXT"]), true);
            }

            $arOfferIn["DESCRIPTION"] = $arOfferIn["PREVIEW_TEXT"] ? $arOfferIn["PREVIEW_TEXT"] : $arOfferIn["DETAIL_TEXT"];

            if ($arParams["DETAIL_TEXT_PRIORITET"] == "Y")
            {
                $arOfferIn["DESCRIPTION"] = $arOfferIn["DETAIL_TEXT"] ? $arOfferIn["DETAIL_TEXT"] : $arOfferIn["PREVIEW_TEXT"];
            }

            if (!$arOfferIn["DESCRIPTION"])
            {
                $arOfferIn["DESCRIPTION"] = $arOffer["DESCRIPTION"];
            }
            if ($arParams["NO_DESCRIPTION"] == "Y")
            {
                $arOfferIn["DESCRIPTION"] = '';
            }

            // MARKET_CATEGORY

            if ($arParams["MARKET_CATEGORY_CHECK"] == "Y")
            {
                $arOfferIn["MARKET_CATEGORY"] = $arOffer["MARKET_CATEGORY"];
            }

            // GROUP_ID
            $arOfferIn["GROUP_ID"] = $arOffer["ID"];
            // ID Ibloka cataloga
            $arOfferIn["IBLOCK_ID_CATALOG"] = $arOffer["IBLOCK_ID"];

            if ($arParams['SECTION_AS_VENDOR'] == 'Y')
            {
                if (!empty($arOffer['IBLOCK_SECTION_ID']))
                {
                    $arOfferIn["DEVELOPER"] = $arOffer["DEVELOPER"];
                }
            }

            $arResult["OFFER"][] = $arOfferIn;
        } // foreach ($arOffer["SKU"] as &$arOfferInID)

        if ($flag == 1)
            continue;

        if (!$bCatalog || $arParams['PRICE_FROM_IBLOCK'] == 'Y')
        {
            $arOffer["AVAIBLE"] = "true";
            if (isset($arParams["IBLOCK_QUANTITY"]) && $arParams["IBLOCK_QUANTITY"] != "WF_EMPT")
            {
                $av = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams["IBLOCK_QUANTITY"]))->Fetch();
                if (IntVal($av["VALUE"]) > 0)
                    $arOffer["AVAIBLE"] = "true";
                else
                {
                    if ($arParams["IBLOCK_ORDER"] == "Y")
                        $arOffer["AVAIBLE"] = "false";
                    else
                        continue;
                }
            }
        }

        if ($bCatalog && $arParams['PRICE_FROM_IBLOCK'] != 'Y')
        {
            if ($arOffer['CURRENCY'] == "RUR")
                $arOffer['CURRENCY'] = "RUB";

            if ($arParams["CURRENCIES_CONVERT"] != "NOT_CONVERT")
            {
                $newval = CCurrencyRates::ConvertCurrency($arOffer["PRICE"], $arOffer["CURRENCY"], $arParams["CURRENCIES_CONVERT"]);
                $arOffer["PRICE"] = round($newval, 2);
                /* $curTo=CCurrency::GetByID($arParams["CURRENCIES_CONVERT"]);
                  $arOffer["WF_AMOUNT"]=$curTo["AMOUNT"]; */
                $arOffer["CURRENCY"] = $arParams["CURRENCIES_CONVERT"];
            }
            if ($arParams["PRICE_ROUND"] == "Y")
            {
                $arOffer["PRICE"] = round($arOffer["PRICE"]);
                $arOffer["PRICE"] = $arOffer["PRICE"] . ".00";
            }

            /* if(!in_array($arOffer["WF_AMOUNT"],$arResult["WF_AMOUNTS"]))
              $arResult["WF_AMOUNTS"][$curTo["CURRENCY"]]=$arOffer["WF_AMOUNT"]; */

            if (!in_array($arOffer["CURRENCY"], $arResult["CURRENCIES"]))
                $arResult["CURRENCIES"][] = $arOffer["CURRENCY"];
        }
        else
        {

            $arProps = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer['ID'], array("sort" => "asc"), Array("CODE" => $arParams["PRICE_CODE"]))->Fetch();

            $arOffer["PRICE"] = $arProps["VALUE_ENUM"] ? $arProps["VALUE_ENUM"] : $arProps["VALUE"];
            $arOffer["PRICE"] = floatval(str_replace(" ", "", $arOffer["PRICE"]));
            if ($arParams["PRICE_ROUND"] == "Y")
            {
                $arOffer["PRICE"] = round($arOffer["PRICE"]);
                $arOffer["PRICE"] = $arOffer["PRICE"] . ".00";
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

        $arOffer["MODEL"] = xml_creator($arOffer["MODEL"], true);


        $arResult["OFFER"][] = $arOffer;

        $i++;
    }

    unset($arOffers);

    $this->IncludeComponentTemplate();

    if ($arParams["CACHE_NON_MANAGED"] == 'Y')
    {
        $obCache->EndDataCache();
    }
}
if (!$bDesignMode)
{
    $r = $APPLICATION->EndBufferContentMan();
    echo $r;
    if (defined("HTML_PAGES_FILE") && !defined("ERROR_404"))
        CHTMLPagesCache::writeFile(HTML_PAGES_FILE, $r);
    die();
}
?>