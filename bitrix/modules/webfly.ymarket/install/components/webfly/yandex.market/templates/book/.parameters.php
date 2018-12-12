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
  "AUTHOR" => Array(
    "PARENT" => "WEBFLY_YM_VENDOR_BOOK",
    "NAME" => GetMessage("AUTHOR"),
    "TYPE" => "LIST",
    "VALUES" => $arProps,
  ),
  "PUBLISHER" => Array(
    "PARENT" => "WEBFLY_YM_VENDOR_BOOK",
    "NAME" => GetMessage("PUBLISHER"),
    "TYPE" => "LIST",
    "VALUES" => $arProps,
  ),
  "SERIES" => Array(
    "PARENT" => "WEBFLY_YM_VENDOR_BOOK",
    "NAME" => GetMessage("SERIES"),
    "TYPE" => "LIST",
    "VALUES" => $arProps,
  ),
  "YEAR" => Array(
    "PARENT" => "WEBFLY_YM_VENDOR_BOOK",
    "NAME" => GetMessage("YEAR"),
    "TYPE" => "LIST",
    "VALUES" => $arProps,
  ),
  "ISBN" => Array(
    "PARENT" => "WEBFLY_YM_VENDOR_BOOK",
    "NAME" => GetMessage("ISBN"),
    "TYPE" => "LIST",
    "VALUES" => $arProps,
  ),
  "VOLUME" => Array(
    "PARENT" => "WEBFLY_YM_VENDOR_BOOK",
    "NAME" => GetMessage("VOLUME"),
    "TYPE" => "LIST",
    "VALUES" => $arProps,
  ),
  "PART" => Array(
    "PARENT" => "WEBFLY_YM_VENDOR_BOOK",
    "NAME" => GetMessage("PART"),
    "TYPE" => "LIST",
    "VALUES" => $arProps,
  ),
  "BINDING" => Array(
    "PARENT" => "WEBFLY_YM_VENDOR_BOOK",
    "NAME" => GetMessage("BINDING"),
    "TYPE" => "LIST",
    "VALUES" => $arProps,
  ),
  "PAGE_EXTENT" => Array(
    "PARENT" => "WEBFLY_YM_VENDOR_BOOK",
    "NAME" => GetMessage("PAGE_EXTENT"),
    "TYPE" => "LIST",
    "VALUES" => $arProps,
  ),
);
?> 