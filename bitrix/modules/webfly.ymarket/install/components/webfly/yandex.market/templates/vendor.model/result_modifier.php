<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();
//test
CModule::IncludeModule("catalog");
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

function getBaseCurrency() {
    if (CModule::IncludeModule('currency')) {
        $res = CCurrency::GetList(($by = "name"), ($order = "asc"), "RU");
        while ($arRes = $res->Fetch()) {
            if ($arRes["BASE"] == "Y")
                return $arRes["CURRENCY"];
        }
    }
}

$baseCur = getBaseCurrency();

if (!CModule::IncludeModule('currency'))
    $baseCur = $arParams["CURRENCY"];
$resCurrency = array();
$arCur[0] = $baseCur;


foreach ($arResult["CURRENCIES"] as $cur) {

    $cur = trim($cur);
    if ($cur == 'RUR') {
        $cur = 'RUB';
    }

    if (!in_array($cur, $arCur)) {
        $arCur[] = $cur;
    }

    if (CModule::IncludeModule('currency')) {
        /* Take curr curency START */
        $arFilter = array(
            "CURRENCY" => $cur,
        );
        $by = "date";
        $order = "asc";

        $db_rate = CCurrencyRates::GetList($by, $order, $arFilter);

        while ($ar_rate = $db_rate->Fetch()) {
            if ($ar_rate["RATE_CNT"] != "1")
                $resCurrency[$ar_rate["CURRENCY"]] = round($ar_rate["RATE_CNT"] / $ar_rate["RATE"], 4);
            else
                $resCurrency[$ar_rate["CURRENCY"]] = $ar_rate["RATE"];
            $ar_rate_res = $ar_rate;
        }

        if ($resCurrency[$ar_rate_res["CURRENCY"]] == NULL || empty($resCurrency[$ar_rate_res["CURRENCY"]])) {
            $curTo = CCurrency::GetByID($cur);
            if (!in_array($curTo, $resCurrency)) {
                if ($curTo["AMOUNT_CNT"] != "1")
                    $resCurrency[$curTo["CURRENCY"]] = round($curTo["AMOUNT_CNT"] / $curTo["AMOUNT"], 4);
                else
                    $resCurrency[$curTo["CURRENCY"]] = $curTo["AMOUNT"];
            }
        }
        /* Take curr curency END */
    }
}

$arResult["CURRENCIES"] = $arCur;
$arResult["WF_AMOUNTS"] = $resCurrency;

foreach ($arResult["OFFER"] as $num => &$arOffer)
{
    if ($arParams["PHOTO_CHECK"]=="Y" and empty($arOffer["PICTURE"]) and count($arOffer["MORE_PHOTO"])==0){
        unset($arResult["OFFER"][$num]);
        continue;
    }
    /* po dopolnitelnye svedeniya */
    foreach ($arParams["PARAMS"] as $k => $v)
    {
        /* Если пустое значение - пропустить */
        if ($v == "EMPTY" or $v == "" or $v == "0")
            continue;

        $code = $v;
        $props = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $code))->GetNext();

        $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code] = CIBlockFormatProperties::GetDisplayValue($arResult["OFFER"], $props, "wf_ymout");

        $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"] = $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["VALUE_ENUM"] ?
            $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["VALUE_ENUM"] :
            strip_tags($arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"]);

        $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"] = xml_creator($arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"], true);
        if (substr_count($arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"], "a href") > 0)
        {
            $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"] = htmlspecialcharsBack($arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"]);
            $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"] = strip_tags($arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"]);
            $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"] = xml_creator($arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"], true);
        }
        $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_NAME"] = $props["NAME"];
        $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_DESCRIPTION"] = $props["DESCRIPTION"];

        unset($props);

        /* esli torgovoe predlozhenie i svojstvo zapolneno, to beretsya svojstvo torgovogo predlozheniya, po umolchaniyu - beretsya svojstvo tovara */
        if ($arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"] != "")
        {
            $arOffer["LIST_PROPERTIES"]["PARAMS"][$v] = $v;
        }
        elseif (!empty($arOffer["GROUP_ID"]))
        {
            $props = CIBlockElement::GetProperty($arOffer["IBLOCK_ID_CATALOG"], $arOffer["GROUP_ID"], array("sort" => "asc"), Array("CODE" => $code))->GetNext();

            $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code] = CIBlockFormatProperties::GetDisplayValue($arResult["OFFER"], $props, "wf_ymout");
            $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"] = $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["VALUE_ENUM"] ? $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["VALUE_ENUM"] : strip_tags($arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"]);

            $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"] = xml_creator($arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"], true);
            $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"] = strip_tags($arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"]);
            if (substr_count($arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"], "a href") > 0)
            {
                $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"] = htmlspecialcharsBack($arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"]);
                $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"] = strip_tags($arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"]);
                $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"] = xml_creator($arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"], true);
            }
            $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_NAME"] = $props["NAME"];
            $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_DESCRIPTION"] = $props["DESCRIPTION"];
            if ($arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"] != "")
            {
                $arOffer["LIST_PROPERTIES"]["PARAMS"][$v] = $v;
            }
            unset($props);
        }
    }

    $i = 0;
    $f = 0;


    foreach ($arParams as $k => $v)
    {
        /* esli pustoe znachenie svojstva */
        if ($v == "NONE")
            $f = 1;

        /* znacheniya unit - perekhod na sleduyushchij shag */
        if (strpos($k, "_UNIT"))
        {
            $s = explode("_UNIT", $k);
            $arOffer['UNIT'][$s[0]] = $v;
            continue;
        }

        if (strpos($k, "~") !== false)
            continue;

        if ($v == "")
            continue;

        /* esli svojstva Proizvoditel, Strana proizvoditel, Kod proizvoditelya, Garantiya proizvoditelya */
        if ($k == "DEVELOPER" || $k == "COUNTRY" || $k == "VENDOR_CODE" || $k == "MANUFACTURER_WARRANTY")
            $i = 1;

        /* esli svojstva Dopolnitelnye svedeniya, Svojstva znacheniya kotoryh dolzhny byt dostupny v shablone dlya sozdaniya uslovij */
        if ($k == "PARAMS" || $k == "COND_PARAMS")
            continue;

        /* esli svojstvo ne vybrano - perekhod na sleduyushchij shag */
        if ($v == "EMPTY")
            continue;

        /* na sleduyushchij shag, esli ne svojstva Proizvoditel, Strana proizvoditel, Kod proizvoditelya, Garantiya proizvoditelya */
        /*if ($i == 0)
            continue;*/

        $code = $v;
        if (is_array($code)) {
            continue;
        } else if (strlen($code) < 3) {
            continue;
        }

        //vse usloviya s manufacturer_warranty poyavilis potomu chto my dobavili dlya nego vozmozhnost vpisyvaniya
        if ($k == "MANUFACTURER_WARRANTY")
        {
            $isExistProp = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array("CODE" => $code))->Fetch();
        }

        //unset($arParams['MARKET_CATEGORY_PROP']);
        if (false && $k != "MANUFACTURER_WARRANTY" or ( $k == "MANUFACTURER_WARRANTY" and $isExistProp))//vozmozhnost vpisyvaniya
        {
            $props = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $code))->GetNext();

            $arOffer["DISPLAY_PROPERTIES"][$code] = CIBlockFormatProperties::GetDisplayValue($arResult["OFFER"], $props, "wf_ymout");

            $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = $arOffer["DISPLAY_PROPERTIES"][$code]["VALUE_ENUM"] ?
                $arOffer["DISPLAY_PROPERTIES"][$code]["VALUE_ENUM"] :
                strip_tags($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]);
            $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = xml_creator($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"], true);
            if (substr_count($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"], "a href") > 0)
            {
                $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = htmlspecialcharsBack($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]);
                $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = strip_tags($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]);
                $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = xml_creator($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"], true);
            }

            $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_NAME"] = $props["NAME"];
            $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_DESCRIPTION"] = $props["DESCRIPTION"];
        }

        if ($k == "MANUFACTURER_WARRANTY" and ! $isExistProp)//vozmozhnost vpisyvaniya
        {
            $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = xml_creator($arParams["MANUFACTURER_WARRANTY"], true);
        }
        unset($props);
        unset($isExistProp);

        if (!empty($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]))
        {
            $arOffer["LIST_PROPERTIES"][$k][] = $k;
        }

        $x = 0;
        if (is_array($arOffer['LIST_PROPERTIES']))
            foreach ($arOffer["LIST_PROPERTIES"] as $k_prop => $v_prop)
            {
                if ($k == $k_prop)
                    $x++;
            }

        if ($x == 0 && !empty($arOffer["GROUP_ID"]))
        {
            $props = CIBlockElement::GetProperty($arOffer["IBLOCK_ID_CATALOG"], $arOffer["GROUP_ID"], array("sort" => "asc"), Array("CODE" => $code))->GetNext();

            $arOffer["DISPLAY_PROPERTIES"][$code] = CIBlockFormatProperties::GetDisplayValue($arResult["OFFER"], $props, "wf_ymout");
            $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = $arOffer["DISPLAY_PROPERTIES"][$code]["VALUE_ENUM"] ? $arOffer["DISPLAY_PROPERTIES"][$code]["VALUE_ENUM"] : strip_tags($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]);
            $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = xml_creator($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"], true);
            $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = strip_tags($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]);
            if (substr_count($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"], "a href") > 0)
            {
                $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = htmlspecialcharsBack($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]);
                $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = strip_tags($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]);
                $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = xml_creator($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"], true);
            }
            $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_NAME"] = $props["NAME"];
            $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_DESCRIPTION"] = $props["DESCRIPTION"];

            if ($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] != "" and $arOffer["LIST_PROPERTIES"][$k])
                $arOffer["LIST_PROPERTIES"][$k] = $k;
            unset($props);
        }

        if (!$f && empty($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]) && $k == "GENDER")
        {
            $arOffer["LIST_PROPERTIES"][$k][] = $code;
            $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_NAME"] = GetMessage("NAME_GENDER");
            $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = $code;
        }
    }

    /* Svojstva, znacheniya kotoryh dolzhny byt dostupny v shablone dlya sozdaniya uslovij */
    foreach ($arParams['COND_PARAMS'] as $k => $code)
    {
        $props = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $code))->GetNext();

        $arOffer["CONDITION_PROPERTIES"][$code] = CIBlockFormatProperties::GetDisplayValue($arResult["OFFER"], $props, "wf_ymout");
        $arOffer["CONDITION_PROPERTIES"][$code]["DISPLAY_VALUE"] = $arOffer["CONDITION_PROPERTIES"][$code]["VALUE_ENUM"] ? $arOffer["CONDITION_PROPERTIES"][$code]["VALUE_ENUM"] : strip_tags($arOffer["CONDITION_PROPERTIES"][$code]["DISPLAY_VALUE"]);

        $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = xml_creator($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"], true);
        $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = strip_tags($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]);
        if (substr_count($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"], "a href") > 0)
        {
            $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = htmlspecialcharsBack($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]);
            $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = strip_tags($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]);
            $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = xml_creator($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"], true);
        }
        $arOffer["CONDITION_PROPERTIES"][$code]["DISPLAY_NAME"] = $props["NAME"];
        $arOffer["CONDITION_PROPERTIES"][$code]["DISPLAY_DESCRIPTION"] = $props["DESCRIPTION"];
        unset($props);

        if ($arOffer["CONDITION_PROPERTIES"][$code]["DISPLAY_VALUE"] == '' && !empty($arOffer["GROUP_ID"]))
        {
            $props = CIBlockElement::GetProperty($arOffer["IBLOCK_ID_CATALOG"], $arOffer["GROUP_ID"], array("sort" => "asc"), Array("CODE" => $code))->GetNext();

            $arOffer["CONDITION_PROPERTIES"][$code] = CIBlockFormatProperties::GetDisplayValue($arResult["OFFER"], $props, "wf_ymout");
            $arOffer["CONDITION_PROPERTIES"][$code]["DISPLAY_VALUE"] = $arOffer["CONDITION_PROPERTIES"][$code]["VALUE_ENUM"] ? $arOffer["CONDITION_PROPERTIES"][$code]["VALUE_ENUM"] : strip_tags($arOffer["CONDITION_PROPERTIES"][$code]["DISPLAY_VALUE"]);
            $arOffer["CONDITION_PROPERTIES"][$code]["DISPLAY_NAME"] = $props["NAME"];
            $arOffer["CONDITION_PROPERTIES"][$code]["DISPLAY_DESCRIPTION"] = $props["DESCRIPTION"];
            unset($props);
        }
    }

    /* vygruzhat rekvezity */
    foreach ($arParams['MULTI_STRING_PROP'] as $k => $code)
    {
        $dbProp = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $code));

        while ($arProp = $dbProp->GetNext()) {
            $cod = $code . '_' . $arProp['PROPERTY_VALUE_ID'];

            $arOffer["DISPLAY_CHARACTERISTICS"][$cod] = CIBlockFormatProperties::GetDisplayValue($arResult["OFFER"], $arProp, "wf_ymout");

            $arOffer["DISPLAY_CHARACTERISTICS"][$cod]["DISPLAY_VALUE"] = $arOffer["DISPLAY_CHARACTERISTICS"][$cod]["VALUE_ENUM"] ?
                $arOffer["DISPLAY_CHARACTERISTICS"][$cod]["VALUE_ENUM"] :
                strip_tags($arOffer["DISPLAY_CHARACTERISTICS"][$cod]["DISPLAY_VALUE"]);

            $arOffer["DISPLAY_CHARACTERISTICS"][$cod]["DISPLAY_VALUE"] = xml_creator($arOffer["DISPLAY_CHARACTERISTICS"][$cod]["DISPLAY_VALUE"], true);
            $arOffer["DISPLAY_CHARACTERISTICS"][$cod]["DISPLAY_VALUE"] = strip_tags($arOffer["DISPLAY_CHARACTERISTICS"][$cod]["DISPLAY_VALUE"]);
            if (substr_count($arOffer["DISPLAY_CHARACTERISTICS"][$cod]["DISPLAY_VALUE"], "a href") > 0)
            {
                $arOffer["DISPLAY_CHARACTERISTICS"][$cod]["DISPLAY_VALUE"] = htmlspecialcharsBack($arOffer["DISPLAY_CHARACTERISTICS"][$cod]["DISPLAY_VALUE"]);
                $arOffer["DISPLAY_CHARACTERISTICS"][$cod]["DISPLAY_VALUE"] = strip_tags($arOffer["DISPLAY_CHARACTERISTICS"][$cod]["DISPLAY_VALUE"]);
                $arOffer["DISPLAY_CHARACTERISTICS"][$cod]["DISPLAY_VALUE"] = xml_creator($arOffer["DISPLAY_CHARACTERISTICS"][$cod]["DISPLAY_VALUE"], true);
            }

            $arOffer["DISPLAY_CHARACTERISTICS"][$cod]["DISPLAY_NAME"] = $arProp['NAME'];
            unset($arProp);
        }
        unset($dbProp);
    }

    if ($arParams ["SALES_NOTES"] != "0")
    {
        $props = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams["SALES_NOTES"]))->GetNext();
        if (($props["VALUE"] == "" or empty($props["VALUE"])) and ! empty($arOffer["GROUP_ID"]))
        {
            $props = CIBlockElement::GetProperty($arOffer["IBLOCK_ID_CATALOG"], $arOffer["GROUP_ID"], array("sort" => "asc"), Array("CODE" => $arParams["SALES_NOTES"]))->GetNext();
        }
        $arOffer["SALES_NOTES"] = CIBlockFormatProperties::GetDisplayValue($arOffer, $props, "wf_ymout");
        $arOffer["SALES_NOTES"] = $arOffer["SALES_NOTES"]["VALUE_ENUM"] ? xml_creator($arOffer["SALES_NOTES"]["VALUE_ENUM"], true) : xml_creator($arOffer["SALES_NOTES"]["DISPLAY_VALUE"], true);
        $arOffer["SALES_NOTES"] = strip_tags($arOffer["SALES_NOTES"]);
        if (substr_count($arOffer["SALES_NOTES"], "a href") > 0){
            $arOffer["SALES_NOTES"] = htmlspecialcharsBack($arOffer["SALES_NOTES"]);
            $arOffer["SALES_NOTES"] = strip_tags($arOffer["SALES_NOTES"]);
            $arOffer["SALES_NOTES"] = xml_creator($arOffer["SALES_NOTES"],true);
        }
        unset($props);
    }
    if (!empty($arParams ["SALES_NOTES_TEXT"]))
    {
        $arParams["SALES_NOTES_TEXT"] = trim($arParams["SALES_NOTES_TEXT"]);
        $arOffer["SALES_NOTES"] = xml_creator($arParams["SALES_NOTES_TEXT"], true);
        $arOffer["SALES_NOTES"] = strip_tags($arOffer["SALES_NOTES"]);
    }
}


$arResult["CLOTHES_PARAMS"] = array(
    "SIZE",
    "COLOR",
    "GENDER",
    "AGE",
    "MATERIAL",
    "GROWTH",
    "CHEST",
    "NECK_GIRTH",
    "WAIST",
    "GIRTH_AT_BREAST",
    "CUP",
    "SIZE_UNDERWEAR",
);
?>