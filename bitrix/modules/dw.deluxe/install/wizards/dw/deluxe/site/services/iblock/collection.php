<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!CModule::IncludeModule("iblock"))
	return;
	
$iblockXMLFile = WIZARD_SERVICE_RELATIVE_PATH."/xml/".LANGUAGE_ID."/collection.xml";
$iblockCode = "5_".WIZARD_SITE_ID; 
$iblockType = "info";

$rsIBlock = CIBlock::GetList(array(), array("CODE" => $iblockCode, "TYPE" => $iblockType));
$iblockID = false; 
if ($arIBlock = $rsIBlock->Fetch())
{
	$iblockID = $arIBlock["ID"];
}

if($iblockID == false)
{
	$iblockID = WizardServices::ImportIBlockFromXML(
		$iblockXMLFile, 
		'5', 
		$iblockType, 
		WIZARD_SITE_ID, 
		$permissions = Array(
			"1" => "X",
			"2" => "R",
			WIZARD_PORTAL_ADMINISTRATION_GROUP => "X",
			WIZARD_PERSONNEL_DEPARTMENT_GROUP => "W",
		)
	);
        
	if ($iblockID < 1)
		return;

	$iblock = new CIBlock;
	$arFields = Array(
		"ACTIVE" => "Y",
		"FIELDS" => array(
                    "SECTION_CODE" => array(
                        "IS_REQUIRED" => "Y",
                        "DEFAULT_VALUE" => array
                        (
                            "UNIQUE" => "Y",
                            "TRANSLITERATION" => "Y",
                            "TRANS_LEN" => 50,
                            "TRANS_CASE" => "L",
                            "TRANS_SPACE" => "_",
                            "TRANS_OTHER" => "_",
                            "TRANS_EAT" => "Y",
                            "USE_GOOGLE" => "Y",
                        )
                    )
                ),
		"CODE" => $iblockCode, 
		"XML_ID" => $iblockCode, 
	);
        
	$iblock->Update($iblockID, $arFields);
}
else
{
	$arSites = array(); 
	$db_res = CIBlock::GetSite($iblockID);
	while ($res = $db_res->Fetch())
		$arSites[] = $res["LID"]; 
	if (!in_array(WIZARD_SITE_ID, $arSites))
	{
		$arSites[] = WIZARD_SITE_ID; 
		$iblock = new CIBlock;
		$iblock->Update($iblockID, array("LID" => $arSites));
	}
}



	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/_index.php", array("COLLECTION_IBLOCK_TYPE" => "info"));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/collection/index.php", array("COLLECTION_IBLOCK_TYPE" => "info"));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/sect_leftBlock.php", array("COLLECTION_IBLOCK_TYPE" => "info"));
	
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/_index.php", array("COLLECTION_IBLOCK_ID" => $iblockID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/collection/index.php", array("COLLECTION_IBLOCK_ID" => $iblockID));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/sect_leftBlock.php", array("COLLECTION_IBLOCK_ID" => $iblockID));


?>