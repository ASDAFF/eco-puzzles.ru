<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

if (!CModule::IncludeModule("iblock"))
    return;

$arIBlockType = CIBlockParameters::GetIBlockTypes();
$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["IBLOCK_TYPE_LIST"], "ACTIVE" => "Y"));

$iblocks = array();

$arSKUProps = array();
$arProps = array();

$dbIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("ACTIVE" => "Y"));
while ($arIb = $dbIBlock->Fetch()) {
    $dbProperty = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $arIb['ID'], "USER_TYPE" => "SKU"));
    while ($arProperty = $dbProperty->Fetch())
        $arSKUProps['PROPERTY_' . $arProperty['CODE']] = "[{$arIb['CODE']}] [{$arProperty['CODE']}] {$arProperty['NAME']}";
}

while ($arr = $rsIBlock->Fetch()) {
    if (CModule::IncludeModule('catalog') && $arCurrentValues['IBLOCK_CATALOG'] != 'N')
    {
        if (!($arCatalog = CCatalog::GetById($arr["ID"])))
            continue;
        if ($arCatalog["PRODUCT_IBLOCK_ID"] != 0)
            continue;
    }
    $arIBlock[$arr["ID"]] = $arIBlockType[$arr["IBLOCK_TYPE_ID"]] . " / " . $arr["NAME"];
    $iblocks[] = $arr["ID"];

    if (empty($arCurrentValues["IBLOCK_ID_IN"][0]))
    {
        $dbProperty = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $arr['ID']));
        while ($arProperty = $dbProperty->Fetch())
            $arProps[$arProperty['CODE']] = "[{$arProperty['CODE']}] {$arProperty['NAME']}";
    }
}

$arIBlockAll = $arIBlock;
$arIBlock[0] = GetMessage("ALL");
ksort($arIBlock);

if (is_array($arCurrentValues["IBLOCK_ID_IN"]))
{
    foreach ($arCurrentValues["IBLOCK_ID_IN"] as $key => $id)
    {
        if (!array_key_exists($id, $arIBlock))
            unset($arCurrentValues["IBLOCK_ID_IN"][$key]);
    }
    if (!empty($arCurrentValues["IBLOCK_ID_IN"][0]))
    {
        foreach ($arCurrentValues["IBLOCK_ID_IN"] as $id)
        {
            $dbProperty = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $id));
            while ($arProperty = $dbProperty->Fetch())
                $arProps[$arProperty['CODE']] = "[{$arProperty['CODE']}] {$arProperty['NAME']}";
        }
    }
}

ksort($arProps);
array_unshift($arProps, '');

/*foreach ($arCurrentValues["IBLOCK_ID_IN"] as $id)
    if ($id > 0)
    {
        $rsProp = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $id, array()));
        while ($arr = $rsProp->Fetch()) {
            if (!in_array($arr["NAME"], $arProp))
            {
                $arProp[$arr["CODE"]] = $arr["NAME"];
            }
        }
    }


$arProp["EMPTY"] = "				";
natsort($arProp);*/

$arTemplateParameters = array(
  "WORLDREGION" => Array(
    "PARENT" => "WEBFLY_YM_VENDOR_TOUR",
    "NAME" => GetMessage("WORLDREGION"),
    "TYPE" => "LIST",
    "VALUES" => $arProps,
  ),
  "REGION" => Array(
    "PARENT" => "WEBFLY_YM_VENDOR_TOUR",
    "NAME" => GetMessage("REGION"),
    "TYPE" => "LIST",
    "VALUES" => $arProps,
  ),
  "DAYS" => Array(
    "PARENT" => "WEBFLY_YM_VENDOR_TOUR",
    "NAME" => GetMessage("DAYS"),
    "TYPE" => "LIST",
    "VALUES" => $arProps,
  ),
  "DATATOUR" => Array(
    "PARENT" => "WEBFLY_YM_VENDOR_TOUR",
    "NAME" => GetMessage("DATATOUR"),
    "TYPE" => "LIST",
    "VALUES" => $arProps,
  ),
  "HOTEL_STARS" => Array(
    "PARENT" => "WEBFLY_YM_VENDOR_TOUR",
    "NAME" => GetMessage("HOTEL_STARS"),
    "TYPE" => "LIST",
    "VALUES" => $arProps,
  ),
  "COUNTRY" => Array(
    "PARENT" => "WEBFLY_YM_VENDOR_TOUR",
    "NAME" => GetMessage("COUNTRY"),
    "TYPE" => "LIST",
    "VALUES" => $arProps,
  ),
  "ROOM" => Array(
    "PARENT" => "WEBFLY_YM_VENDOR_TOUR",
    "NAME" => GetMessage("ROOM"),
    "TYPE" => "LIST",
    "VALUES" => $arProps,
  ),
  "MEAL" => Array(
    "PARENT" => "WEBFLY_YM_VENDOR_TOUR",
    "NAME" => GetMessage("MEAL"),
    "TYPE" => "LIST",
    "VALUES" => $arProps,
  ),
  "INCLUDED" => Array(
    "PARENT" => "WEBFLY_YM_VENDOR_TOUR",
    "NAME" => GetMessage("INCLUDED"),
    "TYPE" => "LIST",
    "VALUES" => $arProps,
  ),
  "TRANSPORT" => Array(
    "PARENT" => "WEBFLY_YM_VENDOR_TOUR",
    "NAME" => GetMessage("TRANSPORT"),
    "TYPE" => "LIST",
    "VALUES" => $arProps,
  ),
);
?> 