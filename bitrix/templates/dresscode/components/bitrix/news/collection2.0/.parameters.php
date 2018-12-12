<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Loader;
use Bitrix\Iblock;
use Bitrix\Currency;

global $USER_FIELD_MANAGER;

if (!Loader::includeModule('iblock'))
	return;
$catalogIncluded = Loader::includeModule('catalog');
$iblockExists = (!empty($arCurrentValues['PRODUCT_IBLOCK_ID']) && (int)$arCurrentValues['PRODUCT_IBLOCK_ID'] > 0);

$arIBlockType = CIBlockParameters::GetIBlockTypes();

$arIBlock = array();
$iblockFilter = (
	!empty($arCurrentValues['PRODUCT_IBLOCK_TYPE'])
	? array('TYPE' => $arCurrentValues['PRODUCT_IBLOCK_TYPE'], 'ACTIVE' => 'Y')
	: array('ACTIVE' => 'Y')
);
$rsIBlock = CIBlock::GetList(array('SORT' => 'ASC'), $iblockFilter);
while ($arr = $rsIBlock->Fetch())
	$arIBlock[$arr['ID']] = '['.$arr['ID'].'] '.$arr['NAME'];
unset($arr, $rsIBlock, $iblockFilter);

$arProperty = array();
$arProperty_N = array();
$arProperty_X = array();
if ($iblockExists)
{
	$propertyIterator = Iblock\PropertyTable::getList(array(
		'select' => array('ID', 'IBLOCK_ID', 'NAME', 'CODE', 'PROPERTY_TYPE', 'MULTIPLE', 'LINK_IBLOCK_ID', 'USER_TYPE'),
		'filter' => array('=IBLOCK_ID' => $arCurrentValues['PRODUCT_IBLOCK_ID'], '=ACTIVE' => 'Y'),
		'order' => array('SORT' => 'ASC', 'NAME' => 'ASC')
	));
	while ($property = $propertyIterator->fetch())
	{
		$propertyCode = (string)$property['CODE'];
		if ($propertyCode == '')
			$propertyCode = $property['ID'];
		$propertyName = '['.$propertyCode.'] '.$property['NAME'];

		if ($property['PROPERTY_TYPE'] != Iblock\PropertyTable::TYPE_FILE)
		{
			$arProperty[$propertyCode] = $propertyName;

			if ($property['MULTIPLE'] == 'Y')
				$arProperty_X[$propertyCode] = $propertyName;
			elseif ($property['PROPERTY_TYPE'] == Iblock\PropertyTable::TYPE_LIST)
				$arProperty_X[$propertyCode] = $propertyName;
			elseif ($property['PROPERTY_TYPE'] == Iblock\PropertyTable::TYPE_ELEMENT && (int)$property['LINK_IBLOCK_ID'] > 0)
				$arProperty_X[$propertyCode] = $propertyName;
		}

		if ($property['PROPERTY_TYPE'] == Iblock\PropertyTable::TYPE_NUMBER)
			$arProperty_N[$propertyCode] = $propertyName;

	}
	unset($propertyCode, $propertyName, $property, $propertyIterator);
}

$arProperty_UF = array();
$arSProperty_LNS = array();
$arSProperty_F = array();
if ($iblockExists)
{
	$arUserFields = $USER_FIELD_MANAGER->GetUserFields('IBLOCK_'.$arCurrentValues['PRODUCT_IBLOCK_ID'].'_SECTION', 0, LANGUAGE_ID);

	foreach( $arUserFields as $FIELD_NAME => $arUserField)
	{
		$arUserField['LIST_COLUMN_LABEL'] = (string)$arUserField['LIST_COLUMN_LABEL'];
		$arProperty_UF[$FIELD_NAME] = $arUserField['LIST_COLUMN_LABEL'] ? '['.$FIELD_NAME.']'.$arUserField['LIST_COLUMN_LABEL'] : $FIELD_NAME;
		if ($arUserField["USER_TYPE"]["BASE_TYPE"] == "string")
			$arSProperty_LNS[$FIELD_NAME] = $arProperty_UF[$FIELD_NAME];
		if ($arUserField["USER_TYPE"]["BASE_TYPE"] == "file" && $arUserField['MULTIPLE'] == 'N')
			$arSProperty_F[$FIELD_NAME] = $arProperty_UF[$FIELD_NAME];
	}
	unset($arUserFields);
}

$offers = false;
$arProperty_Offers = array();
$arProperty_OffersWithoutFile = array();
if ($catalogIncluded && $iblockExists)
{
	$offers = CCatalogSKU::GetInfoByProductIBlock($arCurrentValues['PRODUCT_IBLOCK_ID']);
	if (!empty($offers))
	{
		$propertyIterator = Iblock\PropertyTable::getList(array(
			'select' => array('ID', 'IBLOCK_ID', 'NAME', 'CODE', 'PROPERTY_TYPE', 'MULTIPLE', 'LINK_IBLOCK_ID', 'USER_TYPE'),
			'filter' => array('=IBLOCK_ID' => $offers['IBLOCK_ID'], '=ACTIVE' => 'Y', '!=ID' => $offers['SKU_PROPERTY_ID']),
			'order' => array('SORT' => 'ASC', 'NAME' => 'ASC')
		));
		while ($property = $propertyIterator->fetch())
		{
			$propertyCode = (string)$property['CODE'];
			if ($propertyCode == '')
				$propertyCode = $property['ID'];
			$propertyName = '['.$propertyCode.'] '.$property['NAME'];

			$arProperty_Offers[$propertyCode] = $propertyName;
			if ($property['PROPERTY_TYPE'] != Iblock\PropertyTable::TYPE_FILE)
				$arProperty_OffersWithoutFile[$propertyCode] = $propertyName;
		}
		unset($propertyCode, $propertyName, $property, $propertyIterator);
	}
}

$arSort = CIBlockParameters::GetElementSortFields(
	array('SHOWS', 'SORT', 'TIMESTAMP_X', 'NAME', 'ID', 'ACTIVE_FROM', 'ACTIVE_TO'),
	array('KEY_LOWERCASE' => 'Y')
);

$arPrice = array();
if ($catalogIncluded)
{
	$arSort = array_merge($arSort, CCatalogIBlockParameters::GetCatalogSortFields());
	$arPrice = CCatalogIBlockParameters::getPriceTypesList();
}
else
{
	$arPrice = $arProperty_N;
}

$arAscDesc = array(
	"asc" => GetMessage("IBLOCK_SORT_ASC"),
	"desc" => GetMessage("IBLOCK_SORT_DESC"),
);


$arTemplateParameters = array(
	"HIDE_NOT_AVAILABLE" => array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("HIDE_NOT_AVAILABLE"),
		"TYPE" => "CHECKBOX",
		"REFRESH" => "Y"
	),
	"PRODUCT_IBLOCK_TYPE" => array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("IBLOCK_TYPE"),
		"TYPE" => "LIST",
		"VALUES" => $arIBlockType,
		"REFRESH" => "Y",
	),
	"PRODUCT_IBLOCK_ID" => array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("IBLOCK_IBLOCK"),
		"TYPE" => "LIST",
		"ADDITIONAL_VALUES" => "Y",
		"VALUES" => $arIBlock,
		"REFRESH" => "Y",
	),
	"PRODUCT_FILTER_NAME" => array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("IBLOCK_FILTER_NAME_IN"),
		"TYPE" => "STRING",
		"DEFAULT" => "arrFilter",
	),
	"PRODUCT_PROPERTY_CODE" => array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("IBLOCK_PROPERTY"),
		"TYPE" => "LIST",
		"MULTIPLE" => "Y",
		"VALUES" => $arProperty,
		"ADDITIONAL_VALUES" => "Y",
	),
	"PRODUCT_PRICE_CODE" => array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("IBLOCK_PRICE_CODE"),
		"TYPE" => "LIST",
		"MULTIPLE" => "Y",
		"VALUES" => $arPrice,
	),
	"FILTER_PRICE_CODE" => array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("IBLOCK_FILTER_PRICE_CODE"),
		"TYPE" => "LIST",
		"MULTIPLE" => "Y",
		"VALUES" => $arPrice,
	),
	"HIDE_MEASURES" => array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("HIDE_MEASURES"),
		"TYPE" => "CHECKBOX",
		"REFRESH" => "Y"
	),	
);

if ($catalogIncluded)
{

	$arTemplateParameters['PRODUCT_CONVERT_CURRENCY'] = array(
		'PARENT' => 'BASE',
		'NAME' => GetMessage('CP_BCS_CONVERT_CURRENCY'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
		'REFRESH' => 'Y',
	);

	if (isset($arCurrentValues['PRODUCT_CONVERT_CURRENCY']) && $arCurrentValues['PRODUCT_CONVERT_CURRENCY'] == 'Y')
	{
		$arTemplateParameters['PRODUCT_CURRENCY_ID'] = array(
			'PARENT' => 'BASE',
			'NAME' => GetMessage('CP_BCS_CURRENCY_ID'),
			'TYPE' => 'LIST',
			'VALUES' => Currency\CurrencyManager::getCurrencyList(),
			'DEFAULT' => Currency\CurrencyManager::getBaseCurrency(),
			"ADDITIONAL_VALUES" => "Y",
		);
	}
}
