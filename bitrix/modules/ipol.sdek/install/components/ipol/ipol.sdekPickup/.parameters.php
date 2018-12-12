<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!\Bitrix\Main\Loader::includeModule("sale"))
	return;

if(!cmodule::includeModule('ipol.sdek'))
	return false;

$arCities = array();
$arList = CDeliverySDEK::getListFile();
foreach($arList as $prof => $cities)
	foreach($cities as $city => $crap)
		if(!array_key_exists($city,$arCities))
			$arCities[$city]=$city;

$optCountries = CDeliverySDEK::getActiveCountries();
$arCountries = array();
foreach($optCountries as $countryCode)
	$arCountries[$countryCode] = GetMessage('IPOLSDEK_SYNCTY_'.$countryCode);

$arPayers = array();
$db_ptype = CSalePersonType::GetList(Array("SORT" => "ASC"));
while($arPayer = $db_ptype->Fetch()){
	$arPayers [$arPayer['ID']]= $arPayer['NAME'];
}

$arPaySyss=CSalePaySystem::GetList(array(),array('ACTIVE'=>'Y'));
$arPaySystems = array();
while($arPaySus=$arPaySyss->Fetch()){
	$arPaySystems [$arPaySus['ID']]= $arPaySus['NAME']; 
}

$arComponentParameters = array(
	"PARAMETERS" => array(
		/* "MODE" => array(
			"PARENT"   => "BASE",
			"NAME"     => GetMessage('IPOLSDEK_COMPOPT_MODE'),
			"TYPE"     => "LIST",
			"VALUES"   => array('both' => GetMessage('IPOLSDEK_FRNT_BOTHPROFS'), 'PVZ' => GetMessage('IPOLSDEK_PROF_PICKUP'), 'POSTOMAT' => GetMessage('IPOLSDEK_PROF_POSTOMAT')),
			"DEFAULT" => 'both'
		), */
		"NOMAPS" => array(
			"PARENT"   => "BASE",
			"NAME"     => GetMessage('IPOLSDEK_COMPOPT_NOMAPS'),
			"TYPE"     => "CHECKBOX",
		),
		"CNT_DELIV" => array(
			"PARENT"   => "BASE",
			"NAME"     => GetMessage('IPOLSDEK_COMPOPT_CNT_DELIV'),
			"TYPE"     => "CHECKBOX",
		),
		"CNT_BASKET" => array(
			"PARENT"   => "BASE",
			"NAME"     => GetMessage('IPOLSDEK_COMPOPT_CNT_BASKET'),
			"TYPE"     => "CHECKBOX",
		),
		"FORBIDDEN" => array(
			"PARENT"   => "BASE",
			"NAME"     => GetMessage('IPOLSDEK_COMPOPT_FORBIDDEN'),
			"TYPE"     => "LIST",
			"VALUES"   => array(0 => '', 'pickup' => GetMessage('IPOLSDEK_PROF_PICKUP'), 'courier' => GetMessage('IPOLSDEK_PROF_COURIER'), 'inpost' => GetMessage('IPOLSDEK_PROF_POSTOMAT')),
			"SIZE"     => 3,
			"MULTIPLE" => "Y",
		),
		"PAYER" => array(
			"PARENT"   => "BASE",
			"NAME"     => GetMessage('IPOLSDEK_COMPOPT_PAYERS'),
			"TYPE"     => "LIST",
			"VALUES"   => $arPayers,
			"SIZE"     => 3,
			"MULTIPLE" => "N",
		),
		"PAYSYSTEM" => array(
			"PARENT"   => "BASE",
			"NAME"     => GetMessage('IPOLSDEK_COMPOPT_PAYSYSTEM'),
			"TYPE"     => "LIST",
			"VALUES"   => $arPaySystems,
			"SIZE"     => 5,
			"MULTIPLE" => "N",
		),
		"COUNTRIES" => array(
			"PARENT"   => "BASE",
			"NAME"     => GetMessage('IPOLSDEK_COMPOPT_COUNTRIES'),
			"TYPE"     => "LIST",
			"VALUES"   => $arCountries,
			"SIZE"     => count($arCountries),
			"MULTIPLE" => "Y",
		),
		"CITIES" => array(
			"PARENT"   => "BASE",
			"NAME"     => GetMessage('IPOLSDEK_COMPOPT_CITIES'),
			"TYPE"     => "LIST",
			"VALUES"   => $arCities,
			"SIZE"     => min(count($arCities),30),
			"MULTIPLE" => "Y",
		)
	),
);
?>