<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!CModule::IncludeModule("iblock") || !CModule::IncludeModule("catalog"))
	return;

if(COption::GetOptionString("eshop", "wizard_installed", "N", WIZARD_SITE_ID) == "Y" && !WIZARD_INSTALL_DEMO_DATA)
	return;

//update iblocks, demo discount and precet
$shopLocalization = $wizard->GetVar("shopLocalization");

if ($_SESSION["WIZARD_CATALOG_IBLOCK_ID"])
{
	$IBLOCK_CATALOG_ID = $_SESSION["WIZARD_CATALOG_IBLOCK_ID"];
	unset($_SESSION["WIZARD_CATALOG_IBLOCK_ID"]);
}
if ($_SESSION["WIZARD_OFFERS_IBLOCK_ID"])
{
	$IBLOCK_OFFERS_ID = $_SESSION["WIZARD_OFFERS_IBLOCK_ID"];
	unset($_SESSION["WIZARD_OFFERS_IBLOCK_ID"]);
}
//reference update
/*$rsIBlock = CIBlock::GetList(array(), array("XML_ID" => "clothes_colors", "TYPE" => "references"));
if ($arIBlock = $rsIBlock->Fetch())
{
	if (WIZARD_INSTALL_DEMO_DATA)
	{
		$ib = new CIBlock;
		$ib->Update($arIBlock["ID"], array("XML_ID" => "clothes_colors_".WIZARD_SITE_ID));
	}
}*/

if ($IBLOCK_OFFERS_ID)
{
	$iblockCodeOffers = "deluxe_offers_".WIZARD_SITE_ID;
	//IBlock fields
	$iblock = new CIBlock;
	$arFields = Array(
		"ACTIVE" => "Y",
		"FIELDS" => array (
			'IBLOCK_SECTION' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ),
			'ACTIVE' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => 'Y', ), 'ACTIVE_FROM' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ),
			'ACTIVE_TO' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'SORT' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ),
			'NAME' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => '', ),
			'PREVIEW_PICTURE' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => array ( 'FROM_DETAIL' => 'N', 'SCALE' => 'N', 'WIDTH' => '', 'HEIGHT' => '', 'IGNORE_ERRORS' => 'N', 'METHOD' => 'resample', 'COMPRESSION' => 95, 'DELETE_WITH_DETAIL' => 'N', 'UPDATE_WITH_DETAIL' => 'N', ), ),
			'PREVIEW_TEXT_TYPE' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => 'text', ), 'PREVIEW_TEXT' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ),
			'DETAIL_PICTURE' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => array ( 'SCALE' => 'N', 'WIDTH' => '', 'HEIGHT' => '', 'IGNORE_ERRORS' => 'N', 'METHOD' => 'resample', 'COMPRESSION' => 95, ), ),
			'DETAIL_TEXT_TYPE' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => 'text', ),
			'DETAIL_TEXT' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ),
			'XML_ID' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ),
			'CODE' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => array ( 'UNIQUE' => 'Y', 'TRANSLITERATION' => 'Y', 'TRANS_LEN' => 100, 'TRANS_CASE' => 'L', 'TRANS_SPACE' => '_', 'TRANS_OTHER' => '_', 'TRANS_EAT' => 'Y', 'USE_GOOGLE' => 'Y', ), ),
			'TAGS' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'SECTION_NAME' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ),
			'SECTION_PICTURE' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => array ( 'FROM_DETAIL' => 'N', 'SCALE' => 'N', 'WIDTH' => '', 'HEIGHT' => '', 'IGNORE_ERRORS' => 'N', 'METHOD' => 'resample', 'COMPRESSION' => 95, 'DELETE_WITH_DETAIL' => 'N', 'UPDATE_WITH_DETAIL' => 'N', ), ),
			'SECTION_DESCRIPTION_TYPE' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => 'text', ),
			'SECTION_DESCRIPTION' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'SECTION_DETAIL_PICTURE' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => array ( 'SCALE' => 'N', 'WIDTH' => '', 'HEIGHT' => '', 'IGNORE_ERRORS' => 'N', 'METHOD' => 'resample', 'COMPRESSION' => 95, ), ),
			'SECTION_XML_ID' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ),
			'SECTION_CODE' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => array ( 'UNIQUE' => 'Y', 'TRANSLITERATION' => 'Y', 'TRANS_LEN' => 100, 'TRANS_CASE' => 'L', 'TRANS_SPACE' => '_', 'TRANS_OTHER' => '_', 'TRANS_EAT' => 'Y', 'USE_GOOGLE' => 'Y', ), ), ),
		"CODE" => "deluxe_offers",
		"XML_ID" => $iblockCodeOffers
	);
	$iblock->Update($IBLOCK_OFFERS_ID, $arFields);
}

if ($IBLOCK_CATALOG_ID)
{
	$iblockCode = "92_".WIZARD_SITE_ID;
	//IBlock fields
	$iblock = new CIBlock;
	$arFields = Array(
		"ACTIVE" => "Y",
		"FIELDS" => array ( 'IBLOCK_SECTION' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => '', ), 'ACTIVE' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => 'Y', ), 'ACTIVE_FROM' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'ACTIVE_TO' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'SORT' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'NAME' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => '', ), 'PREVIEW_PICTURE' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => array ( 'FROM_DETAIL' => 'N', 'SCALE' => 'N', 'WIDTH' => '', 'HEIGHT' => '', 'IGNORE_ERRORS' => 'N', 'METHOD' => 'resample', 'COMPRESSION' => 95, 'DELETE_WITH_DETAIL' => 'N', 'UPDATE_WITH_DETAIL' => 'N', ), ), 'PREVIEW_TEXT_TYPE' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => 'text', ), 'PREVIEW_TEXT' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'DETAIL_PICTURE' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => array ( 'SCALE' => 'N', 'WIDTH' => '', 'HEIGHT' => '', 'IGNORE_ERRORS' => 'N', 'METHOD' => 'resample', 'COMPRESSION' => 95, ), ), 'DETAIL_TEXT_TYPE' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => 'text', ), 'DETAIL_TEXT' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'XML_ID' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'CODE' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => array ( 'UNIQUE' => 'Y', 'TRANSLITERATION' => 'Y', 'TRANS_LEN' => 100, 'TRANS_CASE' => 'L', 'TRANS_SPACE' => '_', 'TRANS_OTHER' => '_', 'TRANS_EAT' => 'Y', 'USE_GOOGLE' => 'Y', ), ), 'TAGS' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'SECTION_NAME' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => '', ), 'SECTION_PICTURE' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => array ( 'FROM_DETAIL' => 'N', 'SCALE' => 'N', 'WIDTH' => '', 'HEIGHT' => '', 'IGNORE_ERRORS' => 'N', 'METHOD' => 'resample', 'COMPRESSION' => 95, 'DELETE_WITH_DETAIL' => 'N', 'UPDATE_WITH_DETAIL' => 'N', ), ), 'SECTION_DESCRIPTION_TYPE' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => 'text', ), 'SECTION_DESCRIPTION' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'SECTION_DETAIL_PICTURE' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => array ( 'SCALE' => 'N', 'WIDTH' => '', 'HEIGHT' => '', 'IGNORE_ERRORS' => 'N', 'METHOD' => 'resample', 'COMPRESSION' => 95, ), ), 'SECTION_XML_ID' => array ( 'IS_REQUIRED' => 'N', 'DEFAULT_VALUE' => '', ), 'SECTION_CODE' => array ( 'IS_REQUIRED' => 'Y', 'DEFAULT_VALUE' => array ( 'UNIQUE' => 'Y', 'TRANSLITERATION' => 'Y', 'TRANS_LEN' => 100, 'TRANS_CASE' => 'L', 'TRANS_SPACE' => '_', 'TRANS_OTHER' => '_', 'TRANS_EAT' => 'Y', 'USE_GOOGLE' => 'Y', ), ), ),
		"CODE" => "92",
		"XML_ID" => $iblockCode
	);
	$iblock->Update($IBLOCK_CATALOG_ID, $arFields);

	if ($IBLOCK_OFFERS_ID)
	{
		$ID_SKU = CCatalog::LinkSKUIBlock($IBLOCK_CATALOG_ID, $IBLOCK_OFFERS_ID);

		$rsCatalogs = CCatalog::GetList(
			array(),
			array('IBLOCK_ID' => $IBLOCK_OFFERS_ID),
			false,
			false,
			array('IBLOCK_ID')
		);
		if ($arCatalog = $rsCatalogs->Fetch())
		{
			CCatalog::Update($IBLOCK_OFFERS_ID,array('PRODUCT_IBLOCK_ID' => $IBLOCK_CATALOG_ID,'SKU_PROPERTY_ID' => $ID_SKU));
		}
		else
		{
			CCatalog::Add(array('IBLOCK_ID' => $IBLOCK_OFFERS_ID, 'PRODUCT_IBLOCK_ID' => $IBLOCK_CATALOG_ID, 'SKU_PROPERTY_ID' => $ID_SKU));
		}

		//create facet index
		$index = \Bitrix\Iblock\PropertyIndex\Manager::createIndexer($IBLOCK_CATALOG_ID);
		$index->startIndex();
		$index->continueIndex(0);
		$index->endIndex();

		$index = \Bitrix\Iblock\PropertyIndex\Manager::createIndexer($IBLOCK_OFFERS_ID);
		$index->startIndex();
		$index->continueIndex(0);
		$index->endIndex();

		\Bitrix\Iblock\PropertyIndex\Manager::checkAdminNotification();

	}

	//user fields for sections
	$arLanguages = Array();
	$rsLanguage = CLanguage::GetList($by, $order, array());
	while($arLanguage = $rsLanguage->Fetch())
		$arLanguages[] = $arLanguage["LID"];

	$arUserFields = array("UF_BROWSER_TITLE", "UF_KEYWORDS", "UF_META_DESCRIPTION", "UF_DESC", "UF_IMAGES", "UF_POPULAR", "UF_MARKER", "UF_PHOTO", "UF_BANNER", "UF_BANNER_LINK");

	foreach ($arUserFields as $userField)
	{
		$arLabelNames = Array();
		foreach($arLanguages as $languageID)
		{
			WizardServices::IncludeServiceLang("property_names.php", $languageID);
			$arLabelNames[$languageID] = GetMessage($userField);
		}

		$arProperty["EDIT_FORM_LABEL"] = $arLabelNames;
		$arProperty["LIST_COLUMN_LABEL"] = $arLabelNames;
		$arProperty["LIST_FILTER_LABEL"] = $arLabelNames;

		$dbRes = CUserTypeEntity::GetList(Array(), Array("ENTITY_ID" => 'IBLOCK_'.$IBLOCK_CATALOG_ID.'_SECTION', "FIELD_NAME" => $userField));
		if ($arRes = $dbRes->Fetch())
		{
			$userType = new CUserTypeEntity();
			$userType->Update($arRes["ID"], $arProperty);
		}
		//if($ex = $APPLICATION->GetException())
			//$strError = $ex->GetString();
	}

//demo discount
	$dbDiscount = CCatalogDiscount::GetList(array(), Array("SITE_ID" => WIZARD_SITE_ID));
	if(!($dbDiscount->Fetch()))
	{
		if (CModule::IncludeModule("iblock"))
		{
			$dbSect = CIBlockSection::GetList(Array(), Array("IBLOCK_TYPE" => "catalog", "IBLOCK_ID"=>$IBLOCK_CATALOG_ID, "CODE" => "underwear", "IBLOCK_SITE_ID" => WIZARD_SITE_ID));
			if ($arSect = $dbSect->Fetch())
				$sofasSectId = $arSect["ID"];
		}
		$dbSite = CSite::GetByID(WIZARD_SITE_ID);
		if($arSite = $dbSite -> Fetch())
			$lang = $arSite["LANGUAGE_ID"];
		$defCurrency = "EUR";
		if($lang == "ru")
			$defCurrency = "RUB";
		elseif($lang == "en")
			$defCurrency = "USD";
		$arF = Array (
			"SITE_ID" => WIZARD_SITE_ID,
			"ACTIVE" => "Y",
			//"ACTIVE_FROM" => ConvertTimeStamp(mktime(0,0,0,12,15,2011), "FULL"),
			//"ACTIVE_TO" => ConvertTimeStamp(mktime(0,0,0,03,15,2012), "FULL"),
			"RENEWAL" => "N",
			"NAME" => GetMessage("WIZ_DISCOUNT"),
			"SORT" => 100,
			"MAX_DISCOUNT" => 0,
			"VALUE_TYPE" => "P",
			"VALUE" => 10,
			"CURRENCY" => $defCurrency,
			"CONDITIONS" => Array (
				"CLASS_ID" => "CondGroup",
				"DATA" => Array("All" => "OR", "True" => "True"),
				"CHILDREN" => Array(Array("CLASS_ID" => "CondIBSection", "DATA" => Array("logic" => "Equal", "value" => $sofasSectId)))
			)
		);
		CCatalogDiscount::Add($arF);
	}
//precet
	$dbProperty = CIBlockProperty::GetList(Array(), Array("IBLOCK_ID"=>$IBLOCK_CATALOG_ID, "CODE"=>"SALELEADER"));
	$arFields = array();
	while($arProperty = $dbProperty->GetNext())
	{
		$arFields["find_el_property_".$arProperty["ID"]] = "";
	}
	$dbProperty = CIBlockProperty::GetList(Array(), Array("IBLOCK_ID"=>$IBLOCK_CATALOG_ID, "CODE"=>"NEWPRODUCT"));
	while($arProperty = $dbProperty->GetNext())
	{
		$arFields["find_el_property_".$arProperty["ID"]] = "";
	}
	$dbProperty = CIBlockProperty::GetList(Array(), Array("IBLOCK_ID"=>$IBLOCK_CATALOG_ID, "CODE"=>"SPECIALOFFER"));
	while($arProperty = $dbProperty->GetNext())
	{
		$arFields["find_el_property_".$arProperty["ID"]] = "";
	}
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/interface/admin_lib.php");
	CAdminFilter::AddPresetToBase( array(
			"NAME" => GetMessage("WIZ_PRECET"),
			"FILTER_ID" => "tbl_product_admin_".md5($iblockType.".".$IBLOCK_CATALOG_ID)."_filter",
			"LANGUAGE_ID" => $lang,
			"FIELDS" => $arFields
		)
	);
	CUserOptions::SetOption("filter", "tbl_product_admin_".md5($iblockType.".".$IBLOCK_CATALOG_ID)."_filter", array("rows" => "find_el_name, find_el_active, find_el_timestamp_from, find_el_timestamp_to"), true);

	CAdminFilter::SetDefaultRowsOption("tbl_product_admin_".md5($iblockType.".".$IBLOCK_CATALOG_ID)."_filter", array("miss-0","IBEL_A_F_PARENT"));

//delete 1c props
	$arPropsToDelete = array("CML2_TAXES", "CML2_BASE_UNIT", "CML2_TRAITS", "CML2_ATTRIBUTES", "CML2_ARTICLE", "CML2_BAR_CODE", "CML2_FILES", "CML2_MANUFACTURER", "CML2_PICTURES");
	foreach ($arPropsToDelete as $code)
	{
		$dbProperty = CIBlockProperty::GetList(Array(), Array("IBLOCK_ID"=>$IBLOCK_CATALOG_ID, "XML_ID"=>$code));
		if($arProperty = $dbProperty->GetNext())
		{
			CIBlockProperty::Delete($arProperty["ID"]);
		}
		if ($IBLOCK_OFFERS_ID)
		{
			$dbProperty = CIBlockProperty::GetList(Array(), Array("IBLOCK_ID"=>$IBLOCK_OFFERS_ID, "XML_ID"=>$code));
			if($arProperty = $dbProperty->GetNext())
			{
				CIBlockProperty::Delete($arProperty["ID"]);
			}
		}
	}

	$IBLOCK_CATALOG_TYPE = "catalog";

	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/_index.php", array("CATALOG_IBLOCK_TYPE" => $IBLOCK_CATALOG_TYPE));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/catalog/index.php", array("CATALOG_IBLOCK_TYPE" => $IBLOCK_CATALOG_TYPE));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/search/index.php", array("CATALOG_IBLOCK_TYPE" => $IBLOCK_CATALOG_TYPE));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/.left.menu_ext.php", array("CATALOG_IBLOCK_TYPE" => $IBLOCK_CATALOG_TYPE));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/sale/index.php", array("CATALOG_IBLOCK_TYPE" => $IBLOCK_CATALOG_TYPE));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/wishlist/index.php", array("CATALOG_IBLOCK_TYPE" => $IBLOCK_CATALOG_TYPE));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/recommend/index.php", array("CATALOG_IBLOCK_TYPE" => $IBLOCK_CATALOG_TYPE));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/popular/index.php", array("CATALOG_IBLOCK_TYPE" => $IBLOCK_CATALOG_TYPE));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/new/index.php", array("CATALOG_IBLOCK_TYPE" => $IBLOCK_CATALOG_TYPE));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/discount/index.php", array("CATALOG_IBLOCK_TYPE" => $IBLOCK_CATALOG_TYPE));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/compare/index.php", array("CATALOG_IBLOCK_TYPE" => $IBLOCK_CATALOG_TYPE));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/.left.menu_ext.php", array("CATALOG_IBLOCK_TYPE" => $IBLOCK_CATALOG_TYPE));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/brands/index.php", array("CATALOG_IBLOCK_TYPE" => $IBLOCK_CATALOG_TYPE));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/collection/index.php", array("CATALOG_IBLOCK_TYPE" => $IBLOCK_CATALOG_TYPE));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/blog/index.php", array("CATALOG_IBLOCK_TYPE" => $IBLOCK_CATALOG_TYPE));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/news/index.php", array("CATALOG_IBLOCK_TYPE" => $IBLOCK_CATALOG_TYPE));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/stock/index.php", array("CATALOG_IBLOCK_TYPE" => $IBLOCK_CATALOG_TYPE));

	
	#####
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/_index.php", array("CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/catalog/index.php", array("CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/search/index.php", array("CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/.left.menu_ext.php", array("CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/sale/index.php", array("CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/wishlist/index.php", array("CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/recommend/index.php", array("CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/popular/index.php", array("CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/new/index.php", array("CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/discount/index.php", array("CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/compare/index.php", array("CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/.left.menu_ext.php", array("CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/brands/index.php", array("CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/collection/index.php", array("CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/blog/index.php", array("CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/news/index.php", array("CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/stock/index.php", array("CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));

	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/sect_searchLine.php", array("CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/sect_searchLine.php", array("CATALOG_IBLOCK_TYPE" => $IBLOCK_CATALOG_TYPE));

	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/sect_searchLine2.php", array("CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/sect_searchLine2.php", array("CATALOG_IBLOCK_TYPE" => $IBLOCK_CATALOG_TYPE));

	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/sect_searchLine3.php", array("CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/sect_searchLine3.php", array("CATALOG_IBLOCK_TYPE" => $IBLOCK_CATALOG_TYPE));


	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/sect_searchLine4.php", array("CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/sect_searchLine4.php", array("CATALOG_IBLOCK_TYPE" => $IBLOCK_CATALOG_TYPE));


	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/sect_footerTabs.php", array("CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/sect_footerTabs.php", array("CATALOG_IBLOCK_TYPE" => $IBLOCK_CATALOG_TYPE));

	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/settings.php", array("CATALOG_IBLOCK_ID" => $IBLOCK_CATALOG_ID));

	// #PROP VALUES
	$COUNTER = 0;
	$CATALOG_PROP_VALUES = "array(";
    if(CModule::IncludeModule("iblock")){ 
		$property_enums = CIBlockPropertyEnum::GetList(Array("DEF" => "DESC", "SORT" => "ASC"), Array("IBLOCK_ID" => $IBLOCK_CATALOG_ID, "CODE" => "OFFERS"));
		while($enum_fields = $property_enums->GetNext()){
			$CATALOG_PROP_VALUES.= $COUNTER.' => "'.$enum_fields["ID"].'",';
			$COUNTER++;
		}
		$CATALOG_PROP_VALUES .=")";
		CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/_index.php", array("CATALOG_PROP_VALUES" => $CATALOG_PROP_VALUES));
	}
}
if(CModule::IncludeModule("iblock") && CModule::IncludeModule("catalog") && CModule::IncludeModule("form")){
	WizardServices::IncludeServiceLang("web_form_names.php", "ru");

$rsCheaperForm = CForm::GetBySID("DW_CHEAPER_FORM");

	if($arCheaperForm = $rsCheaperForm->Fetch()){
		$CHEAPER_FORM_ID = $arCheaperForm["ID"];
	}else{

		// Cheaper Form

	    $arFields = array(
	        "NAME"              => $_LANG_UPDATE["C1_CALL"],
	        "SID"               => "DW_CHEAPER_FORM",
	        "C_SORT"            => 300,
	        "BUTTON"            => $_LANG_UPDATE["C1_SEND"],
	        "DESCRIPTION"       => "",
	        "DESCRIPTION_TYPE"  => "text",
	        "STAT_EVENT1"       => "form",
	        "arSITE"            => array("s1", "s2", "s3", "s4", "s5"),
	        "arMENU"            => array("ru" => $_LANG_UPDATE["C1_CALL"], "en" => "Cheaper Form"),
	        "arGROUP"           => array(""),
	    );

	    $CHEAPER_FORM_ID = $NEW_ID = CForm::Set($arFields);
	    if ($NEW_ID > 0 ){

	        $arTemplates = CForm::SetMailTemplate($NEW_ID, "Y", "DW_CHEAPER_FORM", $NEW_ID, false);
	        CForm::Set(
	            array("arMAIL_TEMPLATE" => array("ID" => $arTemplates[0]["ID"]), $NEW_ID)
	        );

	        $formFileds = array();

	        //name
	        $formFileds[] = array(
	            "FORM_FIELDS" => array(
	                "FORM_ID"             => $NEW_ID,
	                "ACTIVE"              => "Y",
	                "TITLE"               => $_LANG_UPDATE["C1_NAME"],
	                "ADDITIONAL"          => "N",
	                "SID"                 => "NAME",
	                "C_SORT"              => 100,
	                "REQUIRED"            => "N",
	                "IN_RESULTS_TABLE"    => "Y",
	                "IN_EXCEL_TABLE"      => "Y",
	                "FIELD_TYPE"          => "text",
	                "FILTER_TITLE"        => $_LANG_UPDATE["C1_NAME"],
	                "RESULTS_TABLE_TITLE" => $_LANG_UPDATE["C1_NAME"],
	                // "arIMAGE"             => CFile::MakeFileArray(dirname(__FILE__)."/install/files/form-images/name.png"),
	                "arFILTER_FIELD"      => array("text"),
	            )
	        );

	        //telephone
	        $formFileds[] = array(
	            "FORM_FIELDS" => array(
	                "FORM_ID"             => $NEW_ID,
	                "ACTIVE"              => "Y",
	                "TITLE"               => $_LANG_UPDATE["C1_PHONE"],
	                "SID"                 => "TELEPHONE",
	                "ADDITIONAL"          => "N",
	                "C_SORT"              => 100,
	                "REQUIRED"            => "Y",
	                "IN_RESULTS_TABLE"    => "Y",
	                "IN_EXCEL_TABLE"      => "Y",
	                "FIELD_TYPE"          => "text",
	                "FILTER_TITLE"        => $_LANG_UPDATE["C1_PHONE"],
	                "RESULTS_TABLE_TITLE" => $_LANG_UPDATE["C1_PHONE"],
	                // "arIMAGE"             => CFile::MakeFileArray(dirname(__FILE__)."/install/files/form-images/telephone.png"),
	                "arFILTER_FIELD"      => array("text"),
	            )
	        );

	        //email
	        $formFileds[] = array(
	            "FORM_FIELDS" => array(
	                "FORM_ID"             => $NEW_ID,
	                "ACTIVE"              => "Y",
	                "TITLE"               => $_LANG_UPDATE["C1_MAIL"],
	                "SID"                 => "EMAIL",
	                "ADDITIONAL"          => "N",
	                "C_SORT"              => 100,
	                "REQUIRED"            => "N",
	                "IN_RESULTS_TABLE"    => "Y",
	                "IN_EXCEL_TABLE"      => "Y",
	                "FIELD_TYPE"          => "email",
	                "FILTER_TITLE"        => $_LANG_UPDATE["C1_MAIL"],
	                "RESULTS_TABLE_TITLE" => $_LANG_UPDATE["C1_MAIL"],
	                // "arIMAGE"             => CFile::MakeFileArray(dirname(__FILE__)."/install/files/form-images/telephone.png"),
	                "arFILTER_FIELD"      => array("email"),
	            )
	        );

	        //product name
	        $formFileds[] = array(
	            "FORM_FIELDS" => array(
	                "FORM_ID"             => $NEW_ID,
	                "ACTIVE"              => "Y",
	                "TITLE"               => $_LANG_UPDATE["C1_PRODUCT_NAME"],
	                "SID"                 => "PRODUCT_NAME",
	                "ADDITIONAL"          => "N",
	                "C_SORT"              => 100,
	                "REQUIRED"            => "Y",
	                "IN_RESULTS_TABLE"    => "Y",
	                "IN_EXCEL_TABLE"      => "Y",
	                "FIELD_TYPE"          => "text",
	                "TITLE_TYPE"		  => "html",
	                "FILTER_TITLE"        => $_LANG_UPDATE["C1_PRODUCT_NAME"],
	                "RESULTS_TABLE_TITLE" => $_LANG_UPDATE["C1_PRODUCT_NAME"],
	                // "arIMAGE"             => CFile::MakeFileArray(dirname(__FILE__)."/install/files/form-images/telephone.png"),
	                "arFILTER_FIELD"      => array("text"),
	            )
	        );

	        //link
	        $formFileds[] = array(
	            "FORM_FIELDS" => array(
	                "FORM_ID"             => $NEW_ID,
	                "ACTIVE"              => "Y",
	                "TITLE"               => $_LANG_UPDATE["C1_LINK"],
	                "SID"                 => "LINK",
	                "ADDITIONAL"          => "N",
	                "C_SORT"              => 100,
	                "REQUIRED"            => "Y",
	                "IN_RESULTS_TABLE"    => "Y",
	                "IN_EXCEL_TABLE"      => "Y",
	                "FIELD_TYPE"          => "url",
	                "FILTER_TITLE"        => $_LANG_UPDATE["C1_LINK"],
	                "RESULTS_TABLE_TITLE" => $_LANG_UPDATE["C1_LINK"],
	                // "arIMAGE"             => CFile::MakeFileArray(dirname(__FILE__)."/install/files/form-images/telephone.png"),
	                "arFILTER_FIELD"      => array("url"),
	            )
	        );

	        foreach ($formFileds as $in => $arNextFormField){
	            $NEW_FIELD_ID = CFormField::Set($arNextFormField["FORM_FIELDS"]);
	            if(!empty($NEW_FIELD_ID) && $arNextFormField["FORM_FIELDS"]["FIELD_TYPE"] != "dropdown"){
	                if(empty($arNextFormField["FORM_FIELDS"]["TITLE_TYPE"])){
	                	$arNextFormField["FORM_FIELDS"]["TITLE_TYPE"] = "text";
	                }
	                $arFields = array(
	                    "QUESTION_ID"   => $NEW_FIELD_ID,
	                    "MESSAGE"       => " ",
	                    "C_SORT"        => 100,
	                    "ACTIVE"        => "Y",
	                    "FIELD_TYPE"    => $arNextFormField["FORM_FIELDS"]["FIELD_TYPE"],
	                    "TITLE_TYPE"	=> $arNextFormField["FORM_FIELDS"]["TITLE_TYPE"],
	                    "FIELD_WIDTH"   => "40"
	                );
	                $NEW_ANSWER_ID = CFormAnswer::Set($arFields);
	            }
	        }

			$arFields = array(
			    "FORM_ID"             => $NEW_ID,
			    "C_SORT"              => 100,
			    "ACTIVE"              => "Y",
			    "TITLE"               => $_LANG_UPDATE["C1_PUBLICATE"],
			    "DESCRIPTION"         => $_LANG_UPDATE["C1_STATUS"],
			    "CSS"                 => "statusgreen",
			    "HANDLER_OUT"         => "",
			    "HANDLER_IN"          => "",
			    "DEFAULT_VALUE"       => "Y",
			    "arPERMISSION_VIEW"   => array(2),
			    "arPERMISSION_MOVE"   => array(2),
			    "arPERMISSION_EDIT"   => array(2),
			    "arPERMISSION_DELETE" => array(2),
			);

			$NEW_STATUS_ID = CFormStatus::Set($arFields);

	    }
	}

	$rsCallbackForm = CForm::GetBySID("DW_CALLBACK_FORM");

	if($arCallbackForm = $rsCallbackForm->Fetch()){
		$CALLBACK_FORM_ID = $arCallbackForm["ID"];
	}else{

		// callback Form

	    $arFields = array(
	        "NAME"              => GetMessage("C1_CALL"),
	        "SID"               => "DW_CALLBACK_FORM",
	        "C_SORT"            => 300,
	        "BUTTON"            => GetMessage("C1_SEND"),
	        "DESCRIPTION"       => GetMessage("C1_CALLBACK_MESSAGE"),
	        "DESCRIPTION_TYPE"  => "text",
	        "STAT_EVENT1"       => "form",
	        "arSITE"            => array("s1", "s2", "s3", "s4", "s5"),
	        "arMENU"            => array("ru" => GetMessage("C1_CALL"), "en" => "Callback Form"),
	        "arGROUP"           => array(""),
	    );

	    $CALLBACK_FORM_ID = $NEW_ID = CForm::Set($arFields);
	    if ($NEW_ID > 0 ){

	        $arTemplates = CForm::SetMailTemplate($NEW_ID, "Y", "DW_CALLBACK_FORM", $NEW_ID, false);
	        CForm::Set(
	            array("arMAIL_TEMPLATE" => array("ID" => $arTemplates[0]["ID"]), $NEW_ID)
	        );

	        $formFileds = array();

	        //telephone
	        $formFileds[] = array(
	            "FORM_FIELDS" => array(
	                "FORM_ID"             => $NEW_ID,
	                "ACTIVE"              => "Y",
	                "TITLE"               => GetMessage("C1_PHONE"),
	                "SID"                 => "TELEPHONE",
	                "ADDITIONAL"          => "N",
	                "C_SORT"              => 100,
	                "REQUIRED"            => "Y",
	                "IN_RESULTS_TABLE"    => "Y",
	                "IN_EXCEL_TABLE"      => "Y",
	                "FIELD_TYPE"          => "text",
	                "FILTER_TITLE"        => GetMessage("C1_PHONE"),
	                "RESULTS_TABLE_TITLE" => GetMessage("C1_PHONE"),
	                "arIMAGE"             => CFile::MakeFileArray(WIZARD_RELATIVE_PATH."/files/form-images/telephone.png"),
	                "arFILTER_FIELD"      => array("text"),
	            )
	        );

	        //name
	        $formFileds[] = array(
	            "FORM_FIELDS" => array(
	                "FORM_ID"             => $NEW_ID,
	                "ACTIVE"              => "Y",
	                "TITLE"               => GetMessage("C1_NAME"),
	                "ADDITIONAL"          => "N",
	                "SID"                 => "NAME",
	                "C_SORT"              => 100,
	                "REQUIRED"            => "N",
	                "IN_RESULTS_TABLE"    => "Y",
	                "IN_EXCEL_TABLE"      => "Y",
	                "FIELD_TYPE"          => "text",
	                "FILTER_TITLE"        => GetMessage("C1_NAME"),
	                "RESULTS_TABLE_TITLE" => GetMessage("C1_NAME"),
	                "arIMAGE"             => CFile::MakeFileArray(WIZARD_RELATIVE_PATH."/files/form-images/name.png"),
	                "arFILTER_FIELD"      => array("text"),
	            )
	        );

	        foreach ($formFileds as $in => $arNextFormField){
	            $NEW_FIELD_ID = CFormField::Set($arNextFormField["FORM_FIELDS"]);
	                if(!empty($NEW_FIELD_ID) && $arNextFormField["FORM_FIELDS"]["FIELD_TYPE"] != "dropdown"){
	                $arFields = array(
	                    "QUESTION_ID"   => $NEW_FIELD_ID,
	                    "MESSAGE"       => " ",
	                    "C_SORT"        => 100,
	                    "ACTIVE"        => "Y",
	                    "FIELD_TYPE"    => $arNextFormField["FORM_FIELDS"]["FIELD_TYPE"],
	                    "FIELD_WIDTH"   => "40"
	                );
	                $NEW_ANSWER_ID = CFormAnswer::Set($arFields);
	            }
	        }

			$arFields = array(
			    "FORM_ID"             => $NEW_ID,
			    "C_SORT"              => 100,
			    "ACTIVE"              => "Y",
			    "TITLE"               => GetMessage("C1_PUBLICATE"),
			    "DESCRIPTION"         => GetMessage("C1_STATUS"),
			    "CSS"                 => "statusgreen",
			    "HANDLER_OUT"         => "",
			    "HANDLER_IN"          => "",
			    "DEFAULT_VALUE"       => "Y",
			    "arPERMISSION_VIEW"   => array(2),
			    "arPERMISSION_MOVE"   => array(2),
			    "arPERMISSION_EDIT"   => array(2),
			    "arPERMISSION_DELETE" => array(2),
			);

			$NEW_STATUS_ID = CFormStatus::Set($arFields);

	    }
	}

    $arFields = array(
        "NAME"              => GetMessage("C1_FEEDBACK"),
        "SID"               => "DW_FEEDBACK_FORM",
        "C_SORT"            => 300,
        "BUTTON"            => GetMessage("C1_SEND"),
        "DESCRIPTION"       => GetMessage("C1_FEEDBACK_MESSAGE"),
        "DESCRIPTION_TYPE"  => "text",
        "STAT_EVENT1"       => "form",
        "arSITE"            => array("s1", "s2", "s3", "s4", "s5"),
        "arMENU"            => array("ru" => GetMessage("C1_FEEDBACK"), "en" => "Feedback Form"),
        "arGROUP"           => array(""),
    );

	$rsFeedbackForm = CForm::GetBySID("DW_FEEDBACK_FORM");

	if($arFeedbackForm = $rsFeedbackForm->Fetch()){
		$FEEDBACK_FORM_ID = $arFeedbackForm["ID"];
	}else{

	    $FEEDBACK_FORM_ID = $NEW_ID = CForm::Set($arFields);
	    if ($NEW_ID > 0 ){

	        $arTemplates = CForm::SetMailTemplate($NEW_ID, "Y", "DW_FEEDBACK_FORM", $NEW_ID, false);
	        CForm::Set(
	            array("arMAIL_TEMPLATE" => array("ID" => $arTemplates[0]["ID"]), $NEW_ID)
	        );

	        $formFileds = array();

	        //name
	        $formFileds[] = array(
	            "FORM_FIELDS" => array(
	                "FORM_ID"             => $NEW_ID,
	                "ACTIVE"              => "Y",
	                "TITLE"               => GetMessage("C1_NAME"),
	                "SID"                 => "NAME",
	                "C_SORT"              => 1,
	                "REQUIRED"            => "Y",
	                "IN_RESULTS_TABLE"    => "Y",
	                "ADDITIONAL"          => "N",
	                "IN_EXCEL_TABLE"      => "Y",
	                "FIELD_TYPE"          => "text",
	                "FILTER_TITLE"        => GetMessage("C1_NAME"),
	                "RESULTS_TABLE_TITLE" => GetMessage("C1_NAME"),
	                "arIMAGE"             => CFile::MakeFileArray(WIZARD_RELATIVE_PATH."/files/form-images/name.png"),
	                "arFILTER_FIELD"      => array("text"),
	            )
	        );

	        //email
	        $formFileds[] = array(
	            "FORM_FIELDS" => array(
	                "FORM_ID"             => $NEW_ID,
	                "ACTIVE"              => "Y",
	                "TITLE"               => GetMessage("C1_MAIL"),
	                "SID"                 => "EMAIL",
	                "C_SORT"              => 10,
	                "ADDITIONAL"          => "N",
	                "REQUIRED"            => "Y",
	                "IN_RESULTS_TABLE"    => "Y",
	                "IN_EXCEL_TABLE"      => "Y",
	                "FIELD_TYPE"          => "email",
	                "FILTER_TITLE"        => GetMessage("C1_MAIL"),
	                "RESULTS_TABLE_TITLE" => GetMessage("C1_MAIL"),
	                "arIMAGE"             => CFile::MakeFileArray(WIZARD_RELATIVE_PATH."/files/form-images/email.png"),
	                "arFILTER_FIELD"      => array("text"),
	            )
	        );

	        //telephone
	        $formFileds[] = array(
	            "FORM_FIELDS" => array(
	                "FORM_ID"             => $NEW_ID,
	                "ACTIVE"              => "Y",
	                "TITLE"               => GetMessage("C1_PHONE"),
	                "SID"                 => "TELEPHONE",
	                "ADDITIONAL"          => "N",
	                "C_SORT"              => 10,
	                "REQUIRED"            => "N",
	                "IN_RESULTS_TABLE"    => "Y",
	                "IN_EXCEL_TABLE"      => "Y",
	                "FIELD_TYPE"          => "text",
	                "FILTER_TITLE"        => GetMessage("C1_PHONE"),
	                "RESULTS_TABLE_TITLE" => GetMessage("C1_PHONE"),
	                "arIMAGE"             => CFile::MakeFileArray(WIZARD_RELATIVE_PATH."/files/form-images/telephone.png"),
	                "arFILTER_FIELD"      => array("text"),
	            )
	        );

	        //theme
	        $formFileds[] = array(
	            "FORM_FIELDS" => array(
	                "FORM_ID"             => $NEW_ID,
	                "ACTIVE"              => "Y",
	                "TITLE"               => GetMessage("C1_THEME"),
	                "SID"                 => "THEME",
	                "ADDITIONAL"          => "N",
	                "C_SORT"              => 100,
	                "REQUIRED"            => "N",
	                "IN_RESULTS_TABLE"    => "Y",
	                "IN_EXCEL_TABLE"      => "Y",
	                "FIELD_TYPE"          => "dropdown",
	                "FILTER_TITLE"        => GetMessage("C1_THEME"),
	                "RESULTS_TABLE_TITLE" => GetMessage("C1_THEME"),
	                "arIMAGE"             => CFile::MakeFileArray(WIZARD_RELATIVE_PATH."/files/form-images/theme.png"),
	                "arFILTER_FIELD"      => array("dropdown"),
	                "arANSWER"            => array(
	                    array(
	                        "FIELD_TYPE" => "dropdown",
	                        "MESSAGE" => GetMessage("C1_QUESTION"),
	                        "FIELD_PARAM" => "checked",
	                        "C_SORT" => 200,
	                        "ACTIVE" => "Y",
	                    ),
	                    array(
	                        "FIELD_TYPE" => "dropdown",
	                        "MESSAGE" => GetMessage("C1_REQUEST"),
	                        "C_SORT" => 200,
	                        "ACTIVE" => "Y",
	                    ),
	                    array(
	                        "FIELD_TYPE" => "dropdown",
	                        "MESSAGE" => GetMessage("C1_QUESTION_MAGAZINE"),
	                        "C_SORT" => 200,
	                        "ACTIVE" => "Y",
	                    ),
	                    array(
	                        "FIELD_TYPE" => "dropdown",
	                        "MESSAGE" => GetMessage("C1_ABUSE"),
	                        "C_SORT" => 200,
	                        "ACTIVE" => "Y"
	                    )
	                ),
	            )
	        );

	        //message
	        $formFileds[] = array(
	            "FORM_FIELDS" => array(
	                "FORM_ID"             => $NEW_ID,
	                "ACTIVE"              => "Y",
	                "TITLE"               => GetMessage("C1_MESSAGE"),
	                "TITLE_TYPE"          => "text",
	                "ADDITIONAL"          => "N",
	                "SID"                 => "MESSAGE",
	                "C_SORT"              => 1000,
	                "REQUIRED"            => "Y",
	                "IN_RESULTS_TABLE"    => "Y",
	                "IN_EXCEL_TABLE"      => "Y",
	                "FIELD_TYPE"          => "textarea",
	                "FILTER_TITLE"        => GetMessage("C1_MESSAGE"),
	                "RESULTS_TABLE_TITLE" => GetMessage("C1_MESSAGE"),
	                "arIMAGE"             => CFile::MakeFileArray(WIZARD_RELATIVE_PATH."/files/form-images/message.png"),
	                "arFILTER_FIELD"      => array("text"),
	            )
	        );

	        foreach ($formFileds as $in => $arNextFormField){
	            $NEW_FIELD_ID = CFormField::Set($arNextFormField["FORM_FIELDS"]);
	                if(!empty($NEW_FIELD_ID) && $arNextFormField["FORM_FIELDS"]["FIELD_TYPE"] != "dropdown"){
	                $arFields = array(
	                    "QUESTION_ID"   => $NEW_FIELD_ID,
	                    "MESSAGE"       => " ",
	                    "C_SORT"        => 100,
	                    "ACTIVE"        => "Y",
	                    "FIELD_TYPE"    => $arNextFormField["FORM_FIELDS"]["FIELD_TYPE"],
	                    "FIELD_WIDTH"   => "40"
	                );
	                $NEW_ANSWER_ID = CFormAnswer::Set($arFields);
	            }
	        }

			$arFields = array(
			    "FORM_ID"             => $NEW_ID,
			    "C_SORT"              => 100,
			    "ACTIVE"              => "Y",
			    "TITLE"               => GetMessage("C1_PUBLICATE"),
			    "NAME"				  => GetMessage("C1_PUBLICATE"),
			    "DESCRIPTION"         => GetMessage("C1_STATUS"),
			    "CSS"                 => "statusgreen",
			    "HANDLER_OUT"         => "",
			    "HANDLER_IN"          => "",
			    "DEFAULT_VALUE"       => "Y",
			    "CODE"				  => "FORM_FEEDBACK_STATUS_".$NEW_ID,
			    "arPERMISSION_VIEW"   => array(2),
			    "arPERMISSION_MOVE"   => array(2),
			    "arPERMISSION_EDIT"   => array(2),
			    "arPERMISSION_DELETE" => array(2),
			);

			$NEW_STATUS_ID = CFormStatus::Set($arFields);

	    }
	}

}
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/callback/index.php", array("FEEDBACK_FORM_ID" => $FEEDBACK_FORM_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/about/contacts/index.php", array("FEEDBACK_FORM_ID" => $FEEDBACK_FORM_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/sect_phone.php", array("CALLBACK_FORM_ID" => $CALLBACK_FORM_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/sect_callBack.php", array("CALLBACK_FORM_ID" => $CALLBACK_FORM_ID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/catalog/index.php", array("CHEAPER_FORM_ID" => $CHEAPER_FORM_ID));
?>