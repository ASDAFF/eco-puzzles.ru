<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

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

$additionalProps = array("CONDITION_PROPERTIES" => "COND_PARAMS", "DISPLAY_PROPERTIES" => "PARAMS", "SALES_NOTES", "SALES_NOTES_TEXT", "COUNTRY", "DEVELOPER", "MANUFACTURER_WARRANTY", "VENDOR_CODE", "MARKET_CATEGORY_PROP","MODEL");

foreach ($arResult["OFFER"] as $num => &$arOffer)
{
    if ($arParams["PHOTO_CHECK"]=="Y" and empty($arOffer["PICTURE"]) and count($arOffer["MORE_PHOTO"])==0){
        unset($arResult["OFFER"][$num]);
        continue;
    }
    foreach ($additionalProps as $paramK => $paramCode)
    {
        if (!empty($arParams[$paramCode]))
        {
            /* svojstva, znacheniya kotoryh dolzhny byt dostupny v shablone dlya sozdaniya uslovij, i parametry */
            if ($paramCode == "COND_PARAMS" or $paramCode == "PARAMS")
            {
                foreach ($arParams[$paramCode] as $key => $productProp)
                {
                    if (empty($productProp))
                        continue;
                    if ($productProp == "EMPTY")
                        continue;

                    if (!empty($productProp))
                    {
                        $props = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $productProp))->GetNext();
                        if (($props["VALUE"] == "" or empty($props["VALUE"])) and ! empty($arOffer["GROUP_ID"]))
                        {
                            $props = CIBlockElement::GetProperty($arOffer["IBLOCK_ID_CATALOG"], $arOffer["GROUP_ID"], array("sort" => "asc"), Array("CODE" => $productProp))->GetNext();
                        }
                        $arOffer[$paramK][$productProp] = CIBlockFormatProperties::GetDisplayValue($arOffer, $props, "wf_ymout");
                        $arOffer[$paramK][$productProp]["DISPLAY_VALUE"] = $arOffer[$paramK][$productProp]["VALUE_ENUM"] ? strip_tags($arOffer[$paramK][$productProp]["VALUE_ENUM"]) : strip_tags($arOffer[$paramK][$productProp]["DISPLAY_VALUE"]);

                        if (empty($arOffer[$paramK][$productProp]["DISPLAY_VALUE"]))
                        {
                            $arOffer[$paramK][$productProp]["DISPLAY_VALUE"] = strip_tags($arOffer[$paramK][$productProp]["VALUE"]);
                        }

                        $arOffer[$paramK][$productProp]["DISPLAY_VALUE"] = xml_creator($arOffer[$paramK][$productProp]["DISPLAY_VALUE"], true);

                        if (substr_count($arOffer[$paramK][$productProp]["DISPLAY_VALUE"], "a href") > 0){
                            $arOffer[$paramK][$productProp]["DISPLAY_VALUE"] = htmlspecialcharsBack($arOffer[$paramK][$productProp]["DISPLAY_VALUE"]);
                            $arOffer[$paramK][$productProp]["DISPLAY_VALUE"] = strip_tags($arOffer[$paramK][$productProp]["DISPLAY_VALUE"]);
                            $arOffer[$paramK][$productProp]["DISPLAY_VALUE"] = xml_creator($arOffer[$paramK][$productProp]["DISPLAY_VALUE"], true);
                        }
      
                        $arOffer[$paramK][$productProp]["DISPLAY_NAME"] = $props["NAME"];
                        $arOffer[$paramK][$productProp]["DISPLAY_DESCRIPTION"] = $props["DESCRIPTION"];
                        unset($props);
                    }
                }
            }
            /* vse ostalnye dopolnitelnye svojstva */
            else
            {
                /* Не SALES_NOTES_TEXT */
                if ($paramCode != "SALES_NOTES_TEXT")
                {
                    $isExistProp = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array("CODE" => $arParams[$paramCode]))->Fetch();
                    if ($isExistProp)
                    {
                        $props = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams[$paramCode]))->GetNext();
                        if (($props["VALUE"] == "" or empty($props["VALUE"])) and ! empty($arOffer["GROUP_ID"]))
                        {
                            $props = CIBlockElement::GetProperty($arOffer["IBLOCK_ID_CATALOG"], $arOffer["GROUP_ID"], array("sort" => "asc"), Array("CODE" => $arParams[$paramCode]))->GetNext();
                        }

                        $dispProp = CIBlockFormatProperties::GetDisplayValue($arOffer, $props, "wf_ymout");
                        if ($paramCode=="MODEL")
                            $paramCode="MODEL_DEFAULT";
                        $arOffer[$paramCode] = $dispProp["VALUE_ENUM"] ? xml_creator($dispProp["VALUE_ENUM"], true) : xml_creator($dispProp["DISPLAY_VALUE"], true);
                        $arOffer[$paramCode] = strip_tags($arOffer[$paramCode]);
                        if (substr_count($arOffer[$paramCode], "a href") > 0){
                            $arOffer[$paramCode] = htmlspecialcharsBack($arOffer[$paramCode]);
                            $arOffer[$paramCode] = strip_tags($arOffer[$paramCode]);
                            $arOffer[$paramCode] = xml_creator($arOffer[$paramCode], true);
                       }
                    }
                    else
                    {
                        $arOffer[$paramCode] = xml_creator($arParams[$paramCode], true);
                        $arOffer[$paramCode] = strip_tags($arOffer[$paramCode]);
                    }
                    unset($isExistProp);
                    unset($props);
                    unset($dispProp);
                }
                /* SALES_NOTES_TEXT */
                else
                {
                    $arParams["SALES_NOTES_TEXT"] = trim($arParams["SALES_NOTES_TEXT"]);
                    $arOffer["SALES_NOTES"] = xml_creator($arParams["SALES_NOTES_TEXT"], true);
                    $arOffer["SALES_NOTES"] = strip_tags($arOffer["SALES_NOTES"]);
                }
            }
        }
    }
}
?>