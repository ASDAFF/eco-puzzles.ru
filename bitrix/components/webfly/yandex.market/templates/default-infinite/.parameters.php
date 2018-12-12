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

$arTemplateParameters = array(
  "PARAMS" => Array(
    "PARENT" => "COMMON",
    "NAME" => GetMessage("PARAMS"),
    "TYPE" => "LIST",
    "MULTIPLE" => "Y",
    "VALUES" => $arProps,
  ),
  "COND_PARAMS" => Array(
    "PARENT" => "COMMON",
    "NAME" => GetMessage("COND_PARAMS"),
    "TYPE" => "LIST",
    "MULTIPLE" => "Y",
    "VALUES" => $arProps,
  ),
  "SALES_NOTES" => array(
    "PARENT" => "WEBFLY_SALES_NOTES",
    "NAME" => GetMessage("SALES_NOTES"),
    "TYPE" => "LIST",
    "VALUES" => $arProps,
    "DEFAULT" => ""
  ),
  "SALES_NOTES_TEXT" => array(
    "PARENT" => "WEBFLY_SALES_NOTES",
    "NAME" => GetMessage("SALES_NOTES_TEXT"),
    "TYPE" => "STRING",
    "DEFAULT" => "",
  ),
  "MARKET_CATEGORY_PROP" => array(
    "PARENT" => "DOP_OPTIONS_DEFAULT",
    "NAME" => GetMessage("MARKET_CATEGORY_PROP"),
    "TYPE" => "LIST",
    "VALUES" => $arProps,
    'ADDITIONAL_VALUES' => 'Y'
  ),
   "DEVELOPER" => array(
    "PARENT" => "DOP_OPTIONS_DEFAULT",
    "NAME" => GetMessage("DEVELOPER"),
    "TYPE" => "LIST",
    "VALUES" => $arProps,
    'ADDITIONAL_VALUES' => 'Y'
  ),
    "VENDOR_CODE" => array(
    "PARENT" => "DOP_OPTIONS_DEFAULT",
    "NAME" => GetMessage("VENDOR_CODE"),
    "TYPE" => "LIST",
    "VALUES" => $arProps,
    'ADDITIONAL_VALUES' => 'Y'
  ),
    "COUNTRY" => array(
    "PARENT" => "DOP_OPTIONS_DEFAULT",
    "NAME" => GetMessage("COUNTRY"),
    "TYPE" => "LIST",
    "VALUES" => $arProps,
    'ADDITIONAL_VALUES' => 'Y'
  ),
    "MANUFACTURER_WARRANTY" => array(
    "PARENT" => "DOP_OPTIONS_DEFAULT",
    "NAME" => GetMessage("MANUFACTURER_WARRANTY"),
    "TYPE" => "LIST",
    "VALUES" => $arProps,
    'ADDITIONAL_VALUES' => 'Y'
  ),
);
?> 