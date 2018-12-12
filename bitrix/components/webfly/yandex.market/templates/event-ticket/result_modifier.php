<?

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
    $code = "PLACE";
    $props = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams[$code]))->Fetch();
    $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]] = CIBlockFormatProperties::GetDisplayValue($arResult["OFFER"], $props, "wf_ymout");
    $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"] = $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["VALUE_ENUM"] ? $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["VALUE_ENUM"] : strip_tags($arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"]);
    if (empty($arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"]))
    {
        $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"] = strip_tags($arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["VALUE"]);
    }
    $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"] = xml_creator($arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"], true);
    $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"] = str_replace("'", "&apos;", $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"]);

    $code = "DATE";
    $props = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams[$code]))->Fetch();
    $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]] = CIBlockFormatProperties::GetDisplayValue($arResult["OFFER"], $props, "wf_ymout");
    $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"] = $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["VALUE_ENUM"] ? $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["VALUE_ENUM"] : strip_tags($arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"]);
    if (empty($arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"]))
    {
        $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"] = strip_tags($arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["VALUE"]);
    }
    $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"] = xml_creator($arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"], true);
    $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"] = str_replace("'", "&apos;", $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"]);

    $code = "IS_PREMIERE";
    $props = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams[$code]))->Fetch();
    $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]] = CIBlockFormatProperties::GetDisplayValue($arResult["OFFER"], $props, "wf_ymout");
    $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"] = $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["VALUE_ENUM"] ? $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["VALUE_ENUM"] : strip_tags($arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"]);
    if (empty($arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"]))
    {
        $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"] = strip_tags($arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["VALUE"]);
    }
    $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"] = xml_creator($arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"], true);
    $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"] = str_replace("'", "&apos;", $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"]);

    $code = "IS_KIDS";
    $props = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams[$code]))->Fetch();
    $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]] = CIBlockFormatProperties::GetDisplayValue($arResult["OFFER"], $props, "wf_ymout");
    $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"] = $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["VALUE_ENUM"] ? $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["VALUE_ENUM"] : strip_tags($arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"]);
    if (empty($arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"]))
    {
        $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"] = strip_tags($arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["VALUE"]);
    }
    $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"] = xml_creator($arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"], true);
    $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"] = str_replace("'", "&apos;", $arOffer["DISPLAY_PROPERTIES"][$arParams[$code]]["DISPLAY_VALUE"]);
}
?>
