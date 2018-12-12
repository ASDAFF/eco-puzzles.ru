<?if (!function_exists("xml_creator"))
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
$arCur = array();
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

foreach ($arResult["OFFER"] as &$arOffer)
{
    foreach ($arParams["PARAMS"] as $k => $v)
    {
        if ($v == "EMPTY")
            continue;

        $code = $v;
        $props = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $code))->Fetch();

        $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code] = CIBlockFormatProperties::GetDisplayValue($arResult["OFFER"], $props, "ym_out");

        $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"] = $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["VALUE_ENUM"] ?
            $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["VALUE_ENUM"] :
            strip_tags($arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"]);

        $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"] = xml_creator($arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"], true);
        $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"] = str_replace("'", "&apos;", $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"]);

        $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_NAME"] = $props["NAME"];
        $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_DESCRIPTION"] = $props["DESCRIPTION"];

        unset($props);
        if ($arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"] != "")
        {
            array_push($arOffer["LIST_PROPERTIES"]["PARAMS"][$v], $v);
        }
        elseif (!empty($arOffer["GROUP_ID"]))
        {
            $props = CIBlockElement::GetProperty($arOffer["IBLOCK_ID_CATALOG"], $arOffer["GROUP_ID"], array("sort" => "asc"), Array("CODE" => $code))->Fetch();

            $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code] = CIBlockFormatProperties::GetDisplayValue($arResult["OFFER"], $props, "ym_out");
            $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"] = $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["VALUE_ENUM"] ? $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["VALUE_ENUM"] : strip_tags($arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"]);

            $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"] = xml_creator($arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"], true);
            $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"] = str_replace("'", "&apos;", $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"]);

            $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_NAME"] = $props["NAME"];
            $arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_DESCRIPTION"] = $props["DESCRIPTION"];
            if ($arOffer["DISPLAY_PROPERTIES_OPTIONAL"][$code]["DISPLAY_VALUE"] != "")
            {
                array_push($arOffer["LIST_PROPERTIES"]["PARAMS"][$v], $v);
            }
            unset($props);
        }
    }

    $i = 0;
    $f = 0;
    foreach ($arParams as $k => $v)
    {
        if ($v == "NONE")
            $f = 1;
        if (strpos($k, "_UNIT"))
        {
            $s = explode("_UNIT", $k);
            $arOffer['UNIT'][$s[0]] = $v;
            continue;
        }

        if (strpos($k, "~") !== false)
            continue;

        if ($k == "DEVELOPER" || $k == "COUNTRY" || $k == "VENDOR_CODE" || $k == "MANUFACTURER_WARRANTY")
            $i = 1;

        if ($k == "PARAMS" || $k == "COND_PARAMS")
            $i = 0;

        if ($v == "EMPTY")
            continue;

        if ($i == 0)
            continue;

        $code = $v;

        $props = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $code))->Fetch();

        $arOffer["DISPLAY_PROPERTIES"][$code] = CIBlockFormatProperties::GetDisplayValue($arResult["OFFER"], $props, "ym_out");

        $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = $arOffer["DISPLAY_PROPERTIES"][$code]["VALUE_ENUM"] ?
            $arOffer["DISPLAY_PROPERTIES"][$code]["VALUE_ENUM"] :
            strip_tags($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]);

        $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = xml_creator($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"], true);
        $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = str_replace("'", "&apos;", $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]);

        $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_NAME"] = $props["NAME"];
        $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_DESCRIPTION"] = $props["DESCRIPTION"];

        unset($props);

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
            $props = CIBlockElement::GetProperty($arOffer["IBLOCK_ID_CATALOG"], $arOffer["GROUP_ID"], array("sort" => "asc"), Array("CODE" => $code))->Fetch();

            $arOffer["DISPLAY_PROPERTIES"][$code] = CIBlockFormatProperties::GetDisplayValue($arResult["OFFER"], $props, "ym_out");
            $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = $arOffer["DISPLAY_PROPERTIES"][$code]["VALUE_ENUM"] ? $arOffer["DISPLAY_PROPERTIES"][$code]["VALUE_ENUM"] : strip_tags($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]);

            $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = xml_creator($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"], true);
            $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = str_replace("'", "&apos;", $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]);

            $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_NAME"] = $props["NAME"];
            $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_DESCRIPTION"] = $props["DESCRIPTION"];

            if ($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] != "")
                array_push($arOffer["LIST_PROPERTIES"][$k], $k);
            unset($props);
        }

        if (!$f && empty($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]) && $k == "GENDER")
        {
            $arOffer["LIST_PROPERTIES"][$k][] = $code;
            $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_NAME"] = GetMessage("NAME_GENDER");
            $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = $code;
        }
    }

    foreach ($arParams['COND_PARAMS'] as $k => $code)
    {
        $props = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $code))->Fetch();

        $arOffer["CONDITION_PROPERTIES"][$code] = CIBlockFormatProperties::GetDisplayValue($arResult["OFFER"], $props, "ym_out");
        $arOffer["CONDITION_PROPERTIES"][$code]["DISPLAY_VALUE"] = $arOffer["CONDITION_PROPERTIES"][$code]["VALUE_ENUM"] ? $arOffer["CONDITION_PROPERTIES"][$code]["VALUE_ENUM"] : strip_tags($arOffer["CONDITION_PROPERTIES"][$code]["DISPLAY_VALUE"]);

        $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = xml_creator($arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"], true);
        $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"] = str_replace("'", "&apos;", $arOffer["DISPLAY_PROPERTIES"][$code]["DISPLAY_VALUE"]);

        $arOffer["CONDITION_PROPERTIES"][$code]["DISPLAY_NAME"] = $props["NAME"];
        $arOffer["CONDITION_PROPERTIES"][$code]["DISPLAY_DESCRIPTION"] = $props["DESCRIPTION"];
        unset($props);

        if ($arOffer["CONDITION_PROPERTIES"][$code]["DISPLAY_VALUE"] == '' && !empty($arOffer["GROUP_ID"]))
        {
            $props = CIBlockElement::GetProperty($arOffer["IBLOCK_ID_CATALOG"], $arOffer["GROUP_ID"], array("sort" => "asc"), Array("CODE" => $code))->Fetch();

            $arOffer["CONDITION_PROPERTIES"][$code] = CIBlockFormatProperties::GetDisplayValue($arResult["OFFER"], $props, "ym_out");
            $arOffer["CONDITION_PROPERTIES"][$code]["DISPLAY_VALUE"] = $arOffer["CONDITION_PROPERTIES"][$code]["VALUE_ENUM"] ? $arOffer["CONDITION_PROPERTIES"][$code]["VALUE_ENUM"] : strip_tags($arOffer["CONDITION_PROPERTIES"][$code]["DISPLAY_VALUE"]);
            $arOffer["CONDITION_PROPERTIES"][$code]["DISPLAY_NAME"] = $props["NAME"];
            $arOffer["CONDITION_PROPERTIES"][$code]["DISPLAY_DESCRIPTION"] = $props["DESCRIPTION"];
            unset($props);
        }
    }

    foreach ($arParams['MULTI_STRING_PROP'] as $k => $code)
    {
        $dbProp = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $code));

        while ($arProp = $dbProp->Fetch()) {
            $cod = $code . '_' . $arProp['PROPERTY_VALUE_ID'];

            $arOffer["DISPLAY_CHARACTERISTICS"][$cod] = CIBlockFormatProperties::GetDisplayValue($arResult["OFFER"], $arProp, "ym_out");

            $arOffer["DISPLAY_CHARACTERISTICS"][$cod]["DISPLAY_VALUE"] = $arOffer["DISPLAY_CHARACTERISTICS"][$cod]["VALUE_ENUM"] ?
                $arOffer["DISPLAY_CHARACTERISTICS"][$cod]["VALUE_ENUM"] :
                strip_tags($arOffer["DISPLAY_CHARACTERISTICS"][$cod]["DISPLAY_VALUE"]);

            $arOffer["DISPLAY_CHARACTERISTICS"][$cod]["DISPLAY_VALUE"] = xml_creator($arOffer["DISPLAY_CHARACTERISTICS"][$cod]["DISPLAY_VALUE"], true);
            $arOffer["DISPLAY_CHARACTERISTICS"][$cod]["DISPLAY_VALUE"] = str_replace("'", "&apos;", $arOffer["DISPLAY_CHARACTERISTICS"][$cod]["DISPLAY_VALUE"]);

            $arOffer["DISPLAY_CHARACTERISTICS"][$cod]["DISPLAY_NAME"] = $arProp['NAME'];
            unset($arProp);
        }
        unset($dbProp);
    }

    if ($arParams ["SALES_NOTES"] != "0")
    {
        $props = CIBlockElement::GetProperty($arOffer["IBLOCK_ID"], $arOffer["ID"], array("sort" => "asc"), Array("CODE" => $arParams["SALES_NOTES"]))->Fetch();
        $arOffer["SALES_NOTES"] = $props["VALUE_ENUM"] ? xml_creator($props["VALUE_ENUM"], true) : xml_creator($props["VALUE"], true);
        $arOffer["SALES_NOTES"] = str_replace("'", "&apos;", $arOffer["SALES_NOTES"]);
        unset($props);
    }

    if (!empty($arParams ["SALES_NOTES_TEXT"]))
    {
        $arParams["SALES_NOTES_TEXT"] = trim($arParams["SALES_NOTES_TEXT"]);
        $arOffer["SALES_NOTES"] = xml_creator($arParams["SALES_NOTES_TEXT"], true);
        $arOffer["SALES_NOTES"] = str_replace("'", "&apos;", $arOffer["SALES_NOTES"]);
        unset($props);
    }
}
/* if ($resCurrency == NULL) {
  $curTo = CCurrency::GetByID($cur);
  if ($curTo["AMOUNT_CNT"] != "1")
  $resCurrency[$curTo["CURRENCY"]] = round($curTo["AMOUNT_CNT"] / $curTo["AMOUNT"], 4);
  else
  $resCurrency[$curTo["CURRENCY"]] = $curTo["AMOUNT"];
  } */
?>

