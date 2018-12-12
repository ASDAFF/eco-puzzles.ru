<?
/* ПОДКЛЮЧЕНИЕ HIGHLOAD */
CModule::IncludeModule("highloadblock");

use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;

global $DB;
/* ПОДКЛЮЧЕНИЕ HIGHLOAD КОНЕЦ */

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
        return $text;
    }

}

function getBaseCurrency() {
    if (CModule::IncludeModule('currency'))
    {
        $res = CCurrency::GetList(($by = "name"), ($order = "asc"), "RU");
        while ($arRes = $res->Fetch()) {
            if ($arRes["AMOUNT"] == 1)
                return $arRes["CURRENCY"];
        }
    }
}

$baseCur = getBaseCurrency();
if (!CModule::IncludeModule('currency'))
    $baseCur = $arParams["CURRENCY"];
$resCurrency = array();
$arCur[0] = $baseCur;
foreach ($arResult["CURRENCIES"] as $cur)
{
    $cur = trim($cur);
    if ($cur == 'RUR')
    {
        $cur = 'RUB';
    }

    if (!in_array($cur, $arCur))
        $arCur[] = $cur;

    if (CModule::IncludeModule('currency'))
    {
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
        }
        if ($resCurrency == NULL)
        {
            $curTo = CCurrency::GetByID($cur);
            if (!in_array($curTo, $resCurrency))
            {
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

foreach ($arParams['COND_PARAMS'] as $k => $code)
{
    if (empty($code))
        continue;
    if ($code == "EMPTY")
        continue;

    foreach ($arResult["OFFER"] as &$arOffer)
    {
        $props = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $code))->Fetch();
        $arOffer["CONDITION_PROPERTIES"][$code] = CIBlockFormatProperties::GetDisplayValue($arResult["OFFER"], $props, "ym_out");
        $arOffer["CONDITION_PROPERTIES"][$code]["DISPLAY_VALUE"] = $arOffer["CONDITION_PROPERTIES"][$code]["VALUE_ENUM"] ? strip_tags($arOffer["CONDITION_PROPERTIES"][$code]["VALUE_ENUM"]) : strip_tags($arOffer["CONDITION_PROPERTIES"][$code]["DISPLAY_VALUE"]);
        if (empty($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]))
        {
            $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = strip_tags($arOffer["DISPLAY_PROPERTIES"][$code]["VALUE"]);
        }
        $arOffer["CONDITION_PROPERTIES"][$code]["DISPLAY_VALUE"] = xml_creator($arOffer["CONDITION_PROPERTIES"][$code]["DISPLAY_VALUE"], true);
        $arOffer["CONDITION_PROPERTIES"][$code]["DISPLAY_VALUE"] = str_replace("'", "&apos;", $arOffer["CONDITION_PROPERTIES"][$code]["DISPLAY_VALUE"]);
        $arOffer["CONDITION_PROPERTIES"][$code]["DISPLAY_NAME"] = $props["NAME"];
        $arOffer["CONDITION_PROPERTIES"][$code]["DISPLAY_DESCRIPTION"] = $props["DESCRIPTION"];
        unset($props);
    }
}

foreach ($arParams['PARAMS'] as $k => $code)
{

    if (empty($code))
        continue;
    if ($code == "EMPTY")
        continue;

    foreach ($arResult["OFFER"] as &$arOffer)
    {
        $props = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $code))->Fetch();
        $arOffer["DISPLAY_PROPERTIES"][$code] = CIBlockFormatProperties::GetDisplayValue($arResult["OFFER"], $props, "ym_out");
        $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = $arOffer["DISPLAY_PROPERTIES"][$code]["VALUE_ENUM"] ? strip_tags($arOffer["DISPLAY_PROPERTIES"][$code]["VALUE_ENUM"]) : strip_tags($arOffer["DISPLAY_PROPERTIES"][$code]["VALUE"]);
        if (empty($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]))
        {
            $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = strip_tags($arOffer["DISPLAY_PROPERTIES"][$code]["VALUE"]);
        }
        $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = xml_creator($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"], true);
        $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = str_replace("'", "&apos;", $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]);
        $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_NAME"] = $props["NAME"];

        unset($props);
    }
}

if ($arParams ["SALES_NOTES"] != "0")
{
    foreach ($arResult["OFFER"] as &$arOffer)
    {
        $props = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams["SALES_NOTES"]))->Fetch();
        $arOffer["SALES_NOTES"] = $props["VALUE_ENUM"] ? xml_creator($props["VALUE_ENUM"], true) : xml_creator($props["VALUE"], true);
        $arOffer["SALES_NOTES"] = str_replace("'", "&apos;", $arOffer["SALES_NOTES"]);
        unset($props);
    }
}
if (!empty($arParams ["SALES_NOTES_TEXT"]))
{
    foreach ($arResult["OFFER"] as &$arOffer)
    {
        $arParams["SALES_NOTES_TEXT"] = trim($arParams["SALES_NOTES_TEXT"]);
        $arOffer["SALES_NOTES"] = xml_creator($arParams["SALES_NOTES_TEXT"], true);
        $arOffer["SALES_NOTES"] = str_replace("'", "&apos;", $arOffer["SALES_NOTES"]);
        unset($props);
    }
}

/* ADDITIONAL PROPS */
$additionalProps = array("COUNTRY", "DEVELOPER", "MANUFACTURER_WARRANTY", "VENDOR_CODE", "MARKET_CATEGORY_PROP");

foreach ($additionalProps as $key => $addProp)
{
    if (!empty($arParams[$addProp]) and $arParams[$addProp] != "0")
    {
        $isExistProp = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array("CODE" => $arParams[$addProp]))->Fetch();
        foreach ($arResult["OFFER"] as &$arOffer)
        {
            if ($isExistProp)
            {
                $props = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams[$addProp]))->Fetch();
                if (!empty($props["USER_TYPE_SETTINGS"]["TABLE_NAME"]))//Справочник
                {
                    $tableName = $props["USER_TYPE_SETTINGS"]["TABLE_NAME"];
                    $res = $DB->Query("select id from b_hlblock_entity where table_name = '" . $tableName . "'")->Fetch();

                    $hlbl_product = intval($res["id"]);
                    $hlblock_product = HL\HighloadBlockTable::getById($hlbl_product)->fetch();

                    if (!empty($hlblock_product))
                    {
                        $entity_product = HL\HighloadBlockTable::compileEntity($hlblock_product);
                        $entity_data_class_product = $entity_product->getDataClass();
                        $bookProp = $entity_data_class_product::getList(array("select" => array("UF_NAME"), "filter" => array("UF_XML_ID" => $props["VALUE"])))->fetch();
                        $arOffer[$addProp] = xml_creator($bookProp["UF_NAME"]);
                        $arOffer[$addProp] = str_replace("'", "&apos;", $arOffer[$addProp]);
                    }
                    unset($tableName);
                    unset($bookProp);
                }
                else
                {
                    $arOffer[$addProp] = $props["VALUE_ENUM"] ? xml_creator($props["VALUE_ENUM"], true) : xml_creator($props["VALUE"], true);
                    $arOffer[$addProp] = str_replace("'", "&apos;", $arOffer[$addProp]);
                }
            }
            else
            {
                $arOffer[$addProp] = xml_creator($arParams[$addProp], true);
                $arOffer[$addProp] = str_replace("'", "&apos;", $arOffer[$addProp]);
            }
            if ($arOffer[$addProp] == false)
                $arOffer[$addProp] = "";
            unset($props);
        }
        unset($isExistProp);
    }
}
?>