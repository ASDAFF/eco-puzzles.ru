<?
class DwSettings{

	#note

	#if use events & multi site technology please create
	#in init.php const

	#DELUXE_SITE_DIR (base site dir)
	#DELUXE_SITE_TEMPLATE_PATH (base site template path)

	//class const
	const DEFAULT_SITE_DIR = "/";

	//class vars
	private static $optionTemplateSettings = array();
	private static $templateSettings = array();
	private static $uploadFiles = array();
	private static $instance = false;
	private static $siteTemplatePath;
	private static $cachePictures;
	private static $settingsPath;
	private static $templatePath;
	private static $sitePath;
	private static $siteDir;
	private static $absPath;

	//constuct
	function __construct(){

		//check site dir && site template path (for events & admin panels)
		self::$siteDir = self::getSiteDir();
		self::$siteTemplatePath = self::getSiteTemplatePath();

		//set vars
		self::$cachePictures = $_SERVER["DOCUMENT_ROOT"]."/bitrix/upload/dwUploadPicutes";
		self::$settingsPath = $_SERVER["DOCUMENT_ROOT"].self::$siteDir."settings.php";
		self::$templatePath = $_SERVER["DOCUMENT_ROOT"].self::$siteTemplatePath;
		self::$sitePath = $_SERVER["DOCUMENT_ROOT"].self::$siteDir;

	}

	//singleton
	public static function getInstance(){

		if (!self::$instance){
			self::$instance = new DwSettings();
		}

		return self::$instance;
	}


	//functions
	public function scanTemplate($templateDirectory){

    	//vars
    	$arReturn = array();

    	//check dir
		if(is_dir($templateDirectory)){
			//check header conf file
			if(file_exists($templateDirectory."/template.config")){
				//read config
				$serializeString = file_get_contents($templateDirectory."/template.config");
				$arTemplateInfo = \Bitrix\Main\Web\Json::decode($serializeString);
				$arReturn = $arTemplateInfo;
			}
		}

    	return $arReturn;

	}

	public function scanHeaders($headersDirectory){

    	//vars
    	$arReturn = array();

    	//scan
    	if(!empty($headersDirectory) && is_dir($headersDirectory)){

    		//get directories
			$arHeaders = array_diff(scandir($headersDirectory), array('..', '.'));

			//check dir
			if(!empty($arHeaders)){

				//each directories
				foreach($arHeaders as $inx => $nextHeader){

					//check header conf file
					if(file_exists($headersDirectory.$nextHeader."/header.config")){
						//read config
						$serializeString = file_get_contents($headersDirectory.$nextHeader."/header.config");
						$arHeaderInfo = \Bitrix\Main\Web\Json::decode($serializeString);
						$arReturn[$nextHeader] = $arHeaderInfo;
					}

				}

			}

		}

		return $arReturn;
    }

    public function scanThemes($themesDirectory){

    	//vars
    	$arReturn = array();

    	if(!empty($themesDirectory) && is_dir($themesDirectory)){

    		//get directories
			$arThemes = array_diff(scandir($themesDirectory), array('..', '.'));

			//check dir
			if(!empty($arThemes)){

				//each themes
				foreach($arThemes as $inx => $nextTheme){

					if(!file_exists($themesDirectory.$nextTheme."/style.css")){
						//check 2 level
						$arThemesByLevel = array_diff(scandir($themesDirectory.$nextTheme), array('..', '.'));
						if(!empty($arThemesByLevel)){
							foreach($arThemesByLevel as $ix => $nextThemeByLevel){
								if(!file_exists($themesDirectory.$nextTheme.$nextThemeByLevel."/style.css")){
									//theme level 2 found
									$arReturn[$nextTheme]["VARIANTS"][$nextThemeByLevel] = $nextThemeByLevel;
								}
							}
						}
					}

					else{
						//theme level 1 found
						$arReturn[$nextTheme] = $nextTheme;
					}
				}
			}
		}

		return $arReturn;
    }

    public function getBgVariantsByData($arThemesData){

    	//vars
    	$arReturn = array();

    	if(!empty($arThemesData)){
    		foreach ($arThemesData as $itx => $nextTheme){
    			if(!empty($nextTheme["VARIANTS"])){
    				$arReturn[$itx] = $itx;
    			}
    		}
    	}

    	return $arReturn;
    }

	public function getPropertyByIblock($iblockId, $arPropertyTypes = array()){

		//vars
		$arReturn = array();

		//check iblockId
		if(empty($iblockId)){
			return false;
		}

		//get properties by iblock id
		$rsIblockProperty = CIBlock::GetProperties($iblockId, array("SORT" => "ASC", "NAME" => "ASC"), array("ACTIVE" => "Y"));
		while($arNextProperty = $rsIblockProperty->Fetch()){
			//check property type
			if(empty($arPropertyTypes) || in_array($arNextProperty["PROPERTY_TYPE"], $arPropertyTypes)){
				//write property data
				$arReturn[$arNextProperty["CODE"]."_".$arNextProperty["ID"]] = $arNextProperty;
			}
		}

		return $arReturn;

	}

    public function getIblocksWithProperty(){

    	//load modules
    	\Bitrix\Main\Loader::includeModule("iblock");

    	//vars
    	$arReturn = array();

		//get iblocks for sku settings
		$rsIblock = CIBlock::GetList(
		    Array(),
		    Array(
		        "SITE_ID" => SITE_ID,
		        "CNT_ACTIVE" => "Y",
		        "ACTIVE" => "Y",
		    )
		);

		while($arNextIblock = $rsIblock->Fetch()){

			//is sku iblock id (set current array index)
			$caseIblock = CCatalogSKU::GetInfoByOfferIBlock($arNextIblock["ID"]) ? "SKU_IBLOCKS" : "PRODUCT_IBLOCKS";
			$iblockId = $arNextIblock["ID"];

			//write iblock data
			$arReturn[$caseIblock][$iblockId] = $arNextIblock;
			$arPropertyTypes = $caseIblock == "SKU_IBLOCKS" ? array("L", "E") : array();

			//get properties for products
			$arReturn[$caseIblock][$iblockId]["PROPERTIES"] = self::getPropertyByIblock($iblockId, $arPropertyTypes);

		}

		return $arReturn;

    }

    public function getPriceCodes(){

    	//load modules
    	\Bitrix\Main\Loader::includeModule("catalog");

    	//vars
    	$arReturn = array();

    	//get price codes
		$rsDb = CCatalogGroup::GetList(array("SORT" => "ASC"));
		while($priceType = $rsDb->Fetch()){
		    $arReturn[$priceType["ID"]] = $priceType;
		}

		return $arReturn;

    }

	public function getCurrentSettings(){

		if(!empty(self::$templateSettings)){
			return self::$templateSettings;
		}

		//vars
		$arReturn = array();

		//settings names
		$arSettingsName = array(
			"TEMPLATE_USE_AUTO_DEACTIVATE_PRODUCTS",
			"TEMPLATE_COLLECTION_PROPERTY_CODE",
			"TEMPLATE_METRICA_REVIEW_MAGAZINE",
			"TEMPLATE_METRICA_REVIEW_PRODUCT",
			"TEMPLATE_WATERMARK_ALPHA_LEVEL",
			"TEMPLATE_WATERMARK_COEFFICIENT",
			"TEMPLATE_COLLECTION_IBLOCK_ID",
			"TEMPLATE_USE_AUTO_SAVE_PRICE",
			"TEMPLATE_USE_AUTO_COLLECTION",
			"TEMPLATE_BRAND_PROPERTY_CODE",
			"TEMPLATE_WATERMARK_POSITION",
			"TEMPLATE_USE_AUTO_WATERMARK",
			"TEMPLATE_CATALOG_MENU_COLOR",
			"TEMPLATE_FOOTER_LINE_COLOR",
			"TEMPLATE_WATERMARK_PICTURE",
			"TEMPLATE_METRICA_SUBSCRIBE",
			"TEMPLATE_METRICA_FAST_CART",
			"TEMPLATE_PRODUCT_IBLOCK_ID",
			"TEMPLATE_METRICA_ADD_CART",
			"TEMPLATE_METRICA_FAST_BUY",
			"TEMPLATE_SUBHEADER_COLOR",
			"TEMPLATE_BACKGROUND_NAME",
			"TEMPLATE_BRAND_IBLOCK_ID",
			"TEMPLATE_WATERMARK_COLOR",
			"TEMPLATE_WATERMARK_TEXT",
			"TEMPLATE_WATERMARK_TYPE",
			"TEMPLATE_WATERMARK_SIZE",
			"TEMPLATE_WATERMARK_FONT",
			"TEMPLATE_TOP_MENU_FIXED",
			"TEMPLATE_USE_AUTO_BRAND",
			"TEMPLATE_FOOTER_VARIANT",
			"TEMPLATE_WATERMARK_FILL",
			"TEMPLATE_SKU_IBLOCK_ID",
			"TEMPLATE_COUNTERS_CODE",
			"TEMPLATE_METRICA_ORDER",
			"TEMPLATE_SLIDER_HEIGHT",
			"TEMPLATE_PANELS_COLOR",
			"TEMPLATE_HEADER_COLOR",
			"TEMPLATE_METRICA_CODE",
			"TEMPLATE_PRICE_CODES",
			"TEMPLATE_GOOGLE_CODE",
			"TEMPLATE_HEADER_TYPE",
			"TEMPLATE_METRICA_ID",
			"TEMPLATE_THEME_NAME",
			"TEMPLATE_HEADER"
		);

		if(!self::checkSettingsFile()){
			showError("settings.php - not found! - ".self::$settingsPath);
			return false;
		}

		//load settings file
		if(!include(self::$settingsPath)){
			showError("include settings fail!");
			return false;
		}

		//load settings vars
		foreach($arSettingsName as $settingName){

			//get var
			$settingsVar = ${$settingName};

			//check empty
			if(!empty($settingsVar)){

				//check base64
				if(self::isBase64($settingName)){
					$settingsVar = base64_decode($settingsVar);
				}

				//convert strings
				$settingsVar = self::convertEncoding($settingsVar);

				//clear bitrix script moving
				$settingsVar = self::clearScriptMoving($settingsVar);

				//write
				$arReturn[$settingName] = $settingsVar;

			}

		}

		//return settings
		return self::$templateSettings = $arReturn;

	}

	public function getSettingsFromOption(){

		if(!empty(self::$optionTemplateSettings)){
			return self::$optionTemplateSettings;
		}

		//vars
		$arReturn = array();

		//default siteId
		$arSites[SITE_ID] = array("ID" => SITE_ID);

		//check admin location
		if(SITE_ID == LANGUAGE_ID){
			$arSites = self::getSiteFromBirtixApi();
		}

		//settings names
		$arSettingsName = array(
			"TEMPLATE_USE_AUTO_DEACTIVATE_PRODUCTS",
			"TEMPLATE_COLLECTION_PROPERTY_CODE",
			"TEMPLATE_COLLECTION_IBLOCK_ID",
			"TEMPLATE_USE_AUTO_SAVE_PRICE",
			"TEMPLATE_USE_AUTO_COLLECTION",
			"TEMPLATE_BRAND_PROPERTY_CODE",
			"TEMPLATE_BRAND_IBLOCK_ID",
			"TEMPLATE_USE_AUTO_BRAND"
		);

		//get settings values from bitrix options container
		foreach ($arSites as $arNextSite){
			foreach($arSettingsName as $nextSettingsName){
				if($currentOptionValue = Bitrix\Main\Config\Option::get("dw.deluxe", $nextSettingsName, false, $arNextSite["ID"])){
					$arReturn[$arNextSite["ID"]][$nextSettingsName] = $currentOptionValue;
				}
			}
		}

		//return settings
		return self::$optionTemplateSettings = $arReturn;

	}

	public function setPropertySort($iblockId, $propertyId, $sort = 99){

    	//load modules
    	\Bitrix\Main\Loader::includeModule("iblock");

    	//vars
    	$return = false;

    	if(!empty($iblockId) && !empty($propertyId)){

	    	//property fields
			$arFields = Array(
			    "IBLOCK_ID" => $iblockId,
			    "SORT" => $sort,
			);

			$iblockProperty = new CIBlockProperty;
			if($iblockProperty->Update($propertyId, $arFields)){
			    return true;
			}

    	}

		return $return;

	}

	public function saveIblockSettings($requestData){

    	//load modules
    	\Bitrix\Main\Loader::includeModule("iblock");

    	//check properties sku
		if(!empty($requestData["TEMPLATE_SKU_IBLOCK_ID"]) && !empty($requestData["TEMPLATE_SKU_PROPERTIES"])){

			//vars
			$iblockId = $requestData["TEMPLATE_SKU_IBLOCK_ID"];

			//get properties
			$rsIblockProperty = CIBlock::GetProperties($iblockId, Array(), Array("ACTIVE" => "Y"));
			while($arNextProperty = $rsIblockProperty->Fetch()){

				//check property type
				if(in_array($arNextProperty["PROPERTY_TYPE"], array("L", "E"))){

					//set active
					if($arNextProperty["SORT"] > 100 && in_array($arNextProperty["ID"], $requestData["TEMPLATE_SKU_PROPERTIES"])){
						self::setPropertySort($iblockId, $arNextProperty["ID"], 99);
					}

					//unset active
					elseif($arNextProperty["SORT"] <= 100 && !in_array($arNextProperty["ID"], $requestData["TEMPLATE_SKU_PROPERTIES"])){
						self::setPropertySort($iblockId, $arNextProperty["ID"], 101);
					}

				}
			}

		}

		//check product properties
		if(!empty($requestData["TEMPLATE_PRODUCT_IBLOCK_ID"]) && !empty($requestData["TEMPLATE_PRODUCT_PROPERTIES"])){

			//vars
			$iblockId = $requestData["TEMPLATE_PRODUCT_IBLOCK_ID"];

			//get properties
			$rsIblockProperty = CIBlock::GetProperties($iblockId, Array(), Array("ACTIVE" => "Y"));
			while($arNextProperty = $rsIblockProperty->Fetch()){

				//set active
				if($arNextProperty["SORT"] > 5000 && in_array($arNextProperty["ID"], $requestData["TEMPLATE_PRODUCT_PROPERTIES"])){
					self::setPropertySort($iblockId, $arNextProperty["ID"], 4999);
				}

				//unset active
				elseif($arNextProperty["SORT"] <= 5000 && !in_array($arNextProperty["ID"], $requestData["TEMPLATE_PRODUCT_PROPERTIES"])){
					self::setPropertySort($iblockId, $arNextProperty["ID"], 6001);
				}

			}
		}

		return true;

	}

	public function saveUploadFiles($requestFiles){

		//vars
		$return = true;

		//check upload files
		if(!empty($requestFiles)){

			//check logo
			if(!empty($requestFiles["TEMPLATE_LOGOTIP"])){
				//check file type
				if(self::checkImageFileType($requestFiles["TEMPLATE_LOGOTIP"])){
					self::pushImageResizedFilePng($requestFiles["TEMPLATE_LOGOTIP"]["tmp_name"], self::$templatePath."/images/logo.png", 230, 60);
				}
			}

			//check favicon
			if(!empty($requestFiles["TEMPLATE_FAVICON"])){
				//check file type
				if(self::checkImageFileType($requestFiles["TEMPLATE_FAVICON"])){
					self::pushImageResizedFilePng($requestFiles["TEMPLATE_FAVICON"]["tmp_name"], self::$templatePath."/images/favicon.ico", 64, 64);
					self::pushImageResizedFilePng($requestFiles["TEMPLATE_FAVICON"]["tmp_name"], self::$sitePath."favicon.ico", 64, 64);
				}
			}

			//check watermark picture
			if(!empty($requestFiles["TEMPLATE_WATERMARK_PICTURE"])){

				//check file type
				if(self::checkImageFileType($requestFiles["TEMPLATE_WATERMARK_PICTURE"])){

					//generate new rand file name
					$newRandPath = self::$cachePictures."/".self::getRandFileName("png");

					//upload image
					if(self::pushImageResizedFilePng($requestFiles["TEMPLATE_WATERMARK_PICTURE"]["tmp_name"], $newRandPath, 500, 500)){
						self::$uploadFiles[] = array("paramName" => "TEMPLATE_WATERMARK_PICTURE", "path" => $newRandPath, "result" => true);
					}

				}

			}

		}

		return $return;

	}

	public function saveSettings($requestData, $requestFiles){

		//vars
		$settingsWrite = "";

		//processing upload files
		if(!empty($requestFiles)){
			self::saveUploadFiles($requestFiles);
		}

		//check request data
		if(!empty($requestData)){

			//save options to deluxe module
			self::saveModuleSettings($requestData);

			//save iblock data
			self::saveIblockSettings($requestData);

			//generate string
			foreach($requestData as $settingName => $settingValue){

				//prepare array
				if(is_array($settingValue)){
					$settingValue = implode(", ", $settingValue);
				}

				//write string
				$settingsWrite .= "\t$".$settingName." = \"".$settingValue."\";\r\n";

			}

			//append into string
			if(!empty(self::$uploadFiles)){
				foreach(self::$uploadFiles as $inf => $arNextFile){
					if(!empty($arNextFile["result"])){
						$settingsWrite .= "\t$".$arNextFile["paramName"]." = \"".$arNextFile["path"]."\";\r\n";
					}

				}
			}

			//bitrix options
			self::saveBitrixOptions();

			//save settings
			return file_put_contents(self::$settingsPath, "<?\r\n".$settingsWrite."?>");

		}

		return false;

	}

 	public static function saveModuleSettings($requestData){

 		//check data
 		if(!empty($requestData)){

	 		//module settings names
			$arSettingsName = array(
				"TEMPLATE_USE_AUTO_DEACTIVATE_PRODUCTS",
				"TEMPLATE_COLLECTION_PROPERTY_CODE",
				"TEMPLATE_COLLECTION_IBLOCK_ID",
				"TEMPLATE_USE_AUTO_SAVE_PRICE",
				"TEMPLATE_USE_AUTO_COLLECTION",
				"TEMPLATE_BRAND_PROPERTY_CODE",
				"TEMPLATE_BRAND_IBLOCK_ID",
				"TEMPLATE_USE_AUTO_BRAND"
			);

			//save settings to module
			foreach($arSettingsName as $nextSettingsName){
				//check current index to request data
				if(!empty($requestData[$nextSettingsName])){
					//save to bitrix options container
					Bitrix\Main\Config\Option::set("dw.deluxe", $nextSettingsName, $requestData[$nextSettingsName], SITE_ID);
				}
			}

		}

		return true;

 	}

 	public function createPropertiesByArray($arPropertiesData, $iblockId){

    	//load modules
    	\Bitrix\Main\Loader::includeModule("iblock");

    	//vars
    	$arReturn = array();

    	//check input properties
    	if(!empty($arPropertiesData) && !empty($iblockId)){

    		//each properties
			foreach ($arPropertiesData as $inx => $arNextProperty){

				//append fileds
				$arNextProperty["IBLOCK_ID"] = intval($iblockId);

				//if property not already created
				$oProperty = CIBlockProperty::GetByID($arNextProperty["CODE"], $arNextProperty["IBLOCK_ID"]);

				//check
				if(!$arProperty = $oProperty->GetNext()){

					//create property object
					$oNewProperty = new CIBlockProperty;

					//success create
					if($iNewPropertyID = $oNewProperty->Add($arNextProperty)){
						$arReturn[$arNextProperty["CODE"]] = array(
							"PROPERTY_NAME" => $arNextProperty["NAME"],
							"PROPERTY_CODE" => $arNextProperty["CODE"],
							"PROPERTY_ID" => $iNewPropertyID,
							"SUCCESS" => "Y"
						);
					}

					//error
					else{
						$arReturn[$arNextProperty["CODE"]] = array(
							"PROPERTY_NAME" => $arNextProperty["NAME"],
							"PROPERTY_CODE" => $arNextProperty["CODE"],
							"ERROR_DATA" => $oNewProperty->LAST_ERROR,
							"ERROR" => "Y",
						);
					}

				}

				//error
				else{
					$arReturn[$arNextProperty["CODE"]] = array(
						"PROPERTY_NAME" => $arNextProperty["NAME"],
						"PROPERTY_CODE" => $arNextProperty["CODE"],
						"PROPERTY_ALLREADY_CREATED" => "Y",
						"PROPERTY_ID" => $arProperty["ID"],
						"ERROR" => "Y",
					);
				}

			}

    	}

		return $arReturn;

    }

 	public function createUserTypePropertiesByArray($arPropertiesData, $iblockId){

    	//load modules
    	\Bitrix\Main\Loader::includeModule("iblock");

    	//vars
    	$arReturn = array();

    	//check input properties
    	if(!empty($arPropertiesData) && !empty($iblockId)){

    		//each properties
			foreach ($arPropertiesData as $inx => $arNextProperty){

				//check property allready created
				$oUserProp = CUserTypeEntity::GetList(array(), array(
					"ENTITY_ID" => "IBLOCK_".$iblockId."_SECTION",
					"FIELD_NAME" => $arNextProperty["FIELD_NAME"]
				));

				if(!$arUserProp = $oUserProp->Fetch()){

					//append fields
					$arNextProperty["ENTITY_ID"] = "IBLOCK_".$iblockId."_SECTION";

					//create property object
					$obUserField  = new CUserTypeEntity;

					//success create
					if($iNewPropertyID = $obUserField->Add($arNextProperty)){
						$arReturn[$arNextProperty["FIELD_NAME"]] = array(
							"PROPERTY_NAME" => $arNextProperty["EDIT_FORM_LABEL"]["ru"],
							"PROPERTY_CODE" => $arNextProperty["FIELD_NAME"],
							"PROPERTY_ID" => $iNewPropertyID,
							"SUCCESS" => "Y",
						);
					}

					//error
					else{
						$arReturn[$arNextProperty["FIELD_NAME"]] = array(
							"PROPERTY_NAME" => $arNextProperty["EDIT_FORM_LABEL"]["ru"],
							"PROPERTY_CODE" => $arNextProperty["FIELD_NAME"],
							"ERROR_DATA" => "create fail",
							"ERROR" => "Y",
						);
					}

				}

				//error
				else{
					$arReturn[$arNextProperty["FIELD_NAME"]] = array(
						"PROPERTY_NAME" => $arNextProperty["EDIT_FORM_LABEL"]["ru"],
						"PROPERTY_CODE" => $arNextProperty["FIELD_NAME"],
						"PROPERTY_ALLREADY_CREATED" => "Y",
						"PROPERTY_ID" => $arUserProp["ID"],
						"ERROR" => "Y",
					);
				}

			}

    	}

    	return $arReturn;

 	}

 	//for hide secret settings
 	public static function checkSecretSettingsByIndex($index){

 		//check data
 		if(empty($index)){
 			return false;
 		}

 		//vars
 		$arSecretsIndex = array(
			"TEMPLATE_WATERMARK_PICTURE",
			"TEMPLATE_WATERMARK_FONT",
			"TEMPLATE_COUNTERS_CODE",
			"TEMPLATE_METRICA_CODE",
			"TEMPLATE_GOOGLE_CODE"
		);

 		//check
 		return array_search($index, $arSecretsIndex) !== false;

 	}


 	public static function saveBitrixOptions(){
 		//option for deactivate not available products
		Bitrix\Main\Config\Option::set("catalog", "enable_processing_deprecated_events", "Y");
 	}

	//util functions
    public function readConfigFile($filePath){
    	return include($filePath);
    }

 	public function checkSettingsFile(){
 		//check settings file
		return file_exists(self::$settingsPath);
	}

	public function checkImageFileType($uploadFile){
		$allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
		$detectedType = exif_imagetype($uploadFile["tmp_name"]);
		return in_array($detectedType, $allowedTypes);
	}

	public function convertImageToPng($inputFilePath){

		//tmp file name
		$tempFile = tempnam(sys_get_temp_dir(), "convertImage");

		//convert image
		imagepng(
		    imagecreatefromstring(
		        file_get_contents($inputFilePath)
		    ),
		    $tempFile
		);

		return $tempFile;

	}

	public function pushImageResizedFilePng($inputFilePath, $outputFilePath, $imageWidth = 200, $imageHeight = 200){

		//check params
		if(!empty($inputFilePath) && !empty($outputFilePath)){

			//get bitrix image data
			$arImageData = CFile::MakeFileArray($inputFilePath);

			//check
			if(!empty($arImageData)){

				//check dir
				if(!is_dir(dirname($outputDirPath))){
					mkdir(dirname($outputDirPath), 0775);
				}

				//check file type
				if(exif_imagetype($inputFilePath) != IMAGETYPE_PNG){
				    $inputFilePath = self::convertImageToPng($inputFilePath);
				}

				//resize picture
				if(CFile::ResizeImageFile($inputFilePath, $outputFilePath, array("width" => $imageWidth, "height" => $imageHeight), BX_RESIZE_IMAGE_PROPORTIONAL)){
					return true;
				}

			}

		}

		return false;

	}

	public static function getSiteFromBirtixApi(){

		//vars
		$arReturn = array();

		//get sites
		$rsSites = CSite::GetList($by = "sort", $order = "desc", array("ACTIVE" => "Y"));
		while($arSite = $rsSites->Fetch()){
			$arReturn[$arSite["ID"]] = $arSite;
		}

		return $arReturn;

	}

	public function getRandFileName($extension = "png"){
		return uniqid(rand(0, 9999999), false).".".$extension;
	}

	public function clearRootFilePath($string){
		if(!empty($string)){
			return str_replace($_SERVER["DOCUMENT_ROOT"], "", $string);
		}
	}

	public function isBase64($index){

		//settings names
		$arSettingsName = array(
			"TEMPLATE_COUNTERS_CODE",
			"TEMPLATE_METRICA_CODE",
			"TEMPLATE_GOOGLE_CODE",
		);

		//check
		return array_search($index, $arSettingsName) !== false;

	}

	public function convertEncoding($string){

		if(!defined("BX_UTF")){
			$string = iconv("UTF-8", "windows-1251//ignore", $string);
		}

		return $string;

	}

	public function clearScriptMoving($string){
		return str_replace(array(
			"<script>",
			"<script >",
			"< script>",
			"<script type=\"text/javascript\">",
			"<script type=\"text/javascript\" >"
		), "<script data-skip-moving=\"true\">", $string);
	}

	public function getSiteDir(){

		//check admin location
		if(defined("LANGUAGE_ID") && defined("SITE_ID")){

			//check admin location
			if(LANGUAGE_ID != SITE_ID){

				if(defined("DELUXE_SITE_DIR")){
					return self::DELUXE_SITE_DIR;
				}

				else{

					if(defined("SITE_DIR") && !empty(SITE_DIR)){
						return SITE_DIR;
					}

					else{
						return self::DEFAULT_SITE_DIR;
					}

				}

			}

		}

		//other variants
		return self::DEFAULT_SITE_DIR;

	}

	public function getSiteTemplatePath(){
		return (!empty(SITE_TEMPLATE_PATH) ? SITE_TEMPLATE_PATH : (
			defined("DELUXE_SITE_TEMPLATE_PATH") ? DELUXE_SITE_TEMPLATE_PATH : "/bitrix/templates/dresscode")
		);
	}

	//scan pagen param from request
	public static function isPagen(){
		$arPagen = preg_grep("/PAGEN_*/", array_keys($_GET));
		return !empty($arPagen);
	}

	public static function getPagenCanonical(){

		//globals
		global $APPLICATION;

		//vars
		$serverProtocol = self::isHttps() ? "https://" : "http://";
		$serverHost = $_SERVER["HTTP_HOST"];
		$serverPath = $APPLICATION->GetCurPage();

		//return full link
		return sprintf("%s%s%s", $serverProtocol, $serverHost, $serverPath);

	}

	public static function isHttps(){
  		return (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") || !empty($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] == 443;
	}

}