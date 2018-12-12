<?
//double cache
class DwItemInfo{

    //functions
    public static function get_extra_content($cacheTime = 21285912, $cacheType = "Y", $cacheID = array(), $cacheDir = "/", $arParams = array(), $arGlobalParams = array(), $arElement = array(), $opCurrency = null){

    	//global vars
    	global $USER;

    	//set cache name
    	$cacheID["NAME"] = "DOUBLE_CATALOG_ITEM_CACHE";

    	//set currency
    	$cacheID["CURRECY"] = $opCurrency;

    	//set extra params
    	$cacheID["EXTRA_PARAMS"] = serialize($arParams);

		//extra settings from cache
		$oExtraCache = new CPHPCache();
 
		//init cache cache
		if($cacheType != "N" && $oExtraCache->InitCache($cacheTime, serialize($cacheID), $cacheDir)){
			//get info by cache
			$arElement = $oExtraCache->GetVars();
		}

		elseif($oExtraCache->StartDataCache()){

			//check include modules
			if(
				   !\Bitrix\Main\Loader::includeModule("iblock")
				|| !\Bitrix\Main\Loader::includeModule('highloadblock')
				|| !\Bitrix\Main\Loader::includeModule("catalog")
				|| !\Bitrix\Main\Loader::includeModule("sale")
			){

				$obExtraCache->AbortDataCache();
				ShowError("modules not installed!");
				return 0;

			}

			//set vars
			$parentElementId = !empty($arElement["PARENT_PRODUCT"]) ? $arElement["PARENT_PRODUCT"]["ID"] : $arElement["ID"];
			$userId = $USER->GetID();
			$sectionIds = array();
			$arSection = array();

			//get last section
			if(!empty($arParams["DISPLAY_LAST_SECTION"]) && $arParams["DISPLAY_LAST_SECTION"] == "Y" ||
			   !empty($arParams["DISPLAY_SIMILAR"]) && $arParams["DISPLAY_SIMILAR"] == "Y"
			){

				$rsGroups = CIBlockElement::GetElementGroups($parentElementId, false);
				while($arNextGroup = $rsGroups->Fetch()){
					$arSection[$arNextGroup["DEPTH_LEVEL"]] = $arNextGroup["ID"];
				}

				// sort array reverse order
				if(!empty($arSection)){
					krsort($arSection);
				}

				if(!empty($arSection)){
					$arElement["LAST_SECTION"] = array_slice($arSection, 0, 1);
					$rsLastSection = CIBlockSection::GetByID($arElement["LAST_SECTION"][0]);
					if($arLastSection = $rsLastSection->GetNext()){
						$arElement["LAST_SECTION"] = $arLastSection;
					}
				}

				//get section path list
				$nav = CIBlockSection::GetNavChain(false, $arLastSection["ID"]);
				while($arSectionPath = $nav->GetNext()){
					$arElement["SECTION_PATH_LIST"][$arSectionPath["ID"]] = $arSectionPath;
					$sectionIds[$arSectionPath["ID"]] = $arSectionPath["ID"];
				}

				//get show_sku_table property
				if(!empty($sectionIds)){
					$rsList = CIBlockSection::GetList(array(), array("ID" => $sectionIds, "IBLOCK_ID" => $arGlobalParams["IBLOCK_ID"]), false, array("ID", "IBLOCK_ID", "UF_SHOW_SKU_TABLE"));
					while($arNextSection = $rsList->GetNext()){
						if(!empty($arNextSection["UF_SHOW_SKU_TABLE"])){
							$arElement["SECTION_PATH_LIST"][$arSectionPath["ID"]]["UF_SHOW_SKU_TABLE"] = $arNextSection["UF_SHOW_SKU_TABLE"];
						}
					}
				}

			}

			// related products
			if(!empty($arParams["DISPLAY_RELATED"]) && $arParams["DISPLAY_RELATED"] == "Y"){
				if(!empty($arElement["PROPERTIES"]["RELATED_PRODUCT"]["VALUE"])){
					$arSelect = array("ID");
					$arFilter = array("IBLOCK_ID" => $arGlobalParams["IBLOCK_ID"], "ACTIVE_DATE" => "Y", "ACTIVE" => "Y", "ID" => $arElement["PROPERTIES"]["RELATED_PRODUCT"]["VALUE"]);
					$rsRelated = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
					$arElement["RELATED_COUNT"] = $rsRelated->SelectedRowsCount();
				}
			}

			// similar products
			if(!empty($arParams["DISPLAY_SIMILAR"]) && $arParams["DISPLAY_SIMILAR"] == "Y"){
				if(!empty($arSection) || !empty($arElement["PROPERTIES"]["SIMILAR_PRODUCT"]["VALUE"])){

					if(empty($arElement["PROPERTIES"]["SIMILAR_PRODUCT"]["VALUE"])){
						$similarFilter = array("IBLOCK_ID" => $arGlobalParams["IBLOCK_ID"], "ACTIVE_DATE" => "Y", "ACTIVE" => "Y", "SECTION_ID" => array_slice($arSection, 0, 1), "!ID" => $parentElementId);
						$rsSimilar = CIBlockElement::GetList(array(), $similarFilter, false, false, array("ID"));
					}
					else{
						$similarFilter = array("IBLOCK_ID" => $arGlobalParams["IBLOCK_ID"], "ACTIVE_DATE" => "Y", "ACTIVE" => "Y", "ID" => $arElement["PROPERTIES"]["SIMILAR_PRODUCT"]["VALUE"]);
						$rsSimilar = CIBlockElement::GetList(array(), $similarFilter, false, false, $arSelect);
					}

					$arElement["SIMILAR_COUNT"] = $rsSimilar->SelectedRowsCount();
					$arElement["SIMILAR_FILTER"] = $similarFilter;

				}	
			}

			//get brand
			if(!empty($arParams["DISPLAY_BRAND"]) && $arParams["DISPLAY_BRAND"] == "Y"){
				if(!empty($arElement["PROPERTIES"]["ATT_BRAND"]["VALUE"])){
					$arBrandFilter = Array("ID" => $arElement["PROPERTIES"]["ATT_BRAND"]["VALUE"], "ACTIVE" => "Y");
					$rsBrand = CIBlockElement::GetList(array(), $arBrandFilter, false, false, array("ID", "IBLOCK_ID", "NAME", "DETAIL_PAGE_URL", "DETAIL_PICTURE"));
					if($brandElement = $rsBrand->GetNextElement()){
						$arElement["BRAND"] = $brandElement->GetFields();
						$arElement["BRAND"]["PICTURE"] = CFile::ResizeImageGet($arElement["BRAND"]["DETAIL_PICTURE"], array("width" => 250, "height" => 50), BX_RESIZE_IMAGE_PROPORTIONAL, false);
					}
				}
			}

			// video and files
			if(!empty($arParams["DISPLAY_FILES_VIDEO"]) && $arParams["DISPLAY_FILES_VIDEO"] == "Y"){
				if(!empty($arElement["PROPERTIES"])){
					foreach ($arElement["PROPERTIES"] as $ips => $arProperty) {
						if($arProperty["PROPERTY_TYPE"] == "F" && $arProperty["CODE"] != "MORE_PHOTO" && !empty($arProperty["VALUE"])){
							if(is_array($arProperty["VALUE"])){
								foreach ($arProperty["VALUE"] as $ipv => $arPropertyValue) {
									$arTmpFile = CFile::GetByID($arPropertyValue)->Fetch();
									$arTmpFile["PARENT_NAME"] = $arProperty["NAME"];
									$arTmpFile["SRC"] = CFile::GetPath($arTmpFile["ID"]);
									$arElement["FILES"][] = $arTmpFile;
								}
							}else{
								$arTmpFile = CFile::GetByID($arProperty["VALUE"])->Fetch();
								$arTmpFile["PARENT_NAME"] = $arProperty["NAME"];
								$arTmpFile["SRC"] = CFile::GetPath($arTmpFile["ID"]);
								$arElement["FILES"][] = $arTmpFile;
							}
						}elseif($arProperty["CODE"] == "VIDEO" && !empty($arProperty["VALUE"])){
							if(is_array($arProperty["VALUE"])){
								foreach ($arProperty["VALUE"] as $ipv => $arPropertyValue) {
									$arElement["VIDEO"][] = $arPropertyValue;
								}
							}else{
								$arElement["VIDEO"][] = $arProperty["VALUE"];
							}
						}
					}
				}
			}

			// get pictures for slider
			if(!empty($arParams["DISPLAY_MORE_PICTURES"]) && $arParams["DISPLAY_MORE_PICTURES"] == "Y"){
				// resize pictures params for get_more_pictures function
				$arResizeParams = array(
					"SMALL_PICTURE" => array(
						"HEIGHT" => 50,
						"WIDTH" => 50
					),
					"REGULAR_PICTURE" => array(
						"HEIGHT" => 300,
						"WIDTH" => 300
					),
					"MEDIUM_PICTURE" => array(
						"HEIGHT" => 500,
						"WIDTH" => 500
					),
					"LARGE_PICTURE" => array(
						"HEIGHT" => 1200,
						"WIDTH" => 1200
					)
				);

				// push more pictures from detail page
				// get_more_pictures you find in class.php (component)
				if(!empty($arElement["DETAIL_PICTURE"]) && is_numeric($arElement["DETAIL_PICTURE"])){
					// push detail picture in images array
					$arElement["IMAGES"][] = DwItemInfo::get_more_pictures($arElement["DETAIL_PICTURE"], $arResizeParams);
				}else{
					// get picture from parent product
					if(!empty($arElement["PARENT_PRODUCT"]["DETAIL_PICTURE"])){
						// get more images
						$arElement["IMAGES"][] = DwItemInfo::get_more_pictures($arElement["PARENT_PRODUCT"]["DETAIL_PICTURE"], $arResizeParams);
					}
					else{
						// if detail picture is empty
						$arElement["IMAGES"][] = array(
							"SMALL_IMAGE" => array("SRC" => SITE_TEMPLATE_PATH."/images/empty.png"),
							"MEDIUM_IMAGE" => array("SRC" => SITE_TEMPLATE_PATH."/images/empty.png"),
							"LARGE_IMAGE" => array("SRC" => SITE_TEMPLATE_PATH."/images/empty.png")
						);
					}
				}

				// push more pictures from more_photo property
				if(!empty($arElement["PROPERTIES"]["MORE_PHOTO"]["VALUE"])){
					foreach ($arElement["PROPERTIES"]["MORE_PHOTO"]["VALUE"] as $nextPictureID){
						$arElement["IMAGES"][] = DwItemInfo::get_more_pictures($nextPictureID, $arResizeParams);
					}
				}
			}


			if(!empty($arParams["DISPLAY_FORMAT_PROPERTIES"]) && $arParams["DISPLAY_FORMAT_PROPERTIES"] == "Y"){
				//create display properties
				foreach ($arElement["PROPERTIES"] as $arNextProperty){
					$arElement["DISPLAY_PROPERTIES"][$arNextProperty["CODE"]] = CIBlockFormatProperties::GetDisplayValue($arElement, $arNextProperty, "catalog_out");
				}
			}

			//target cache
			global $CACHE_MANAGER;
			$CACHE_MANAGER->StartTagCache($cacheDir);
			$CACHE_MANAGER->RegisterTag("iblock_id_".$arElement["IBLOCK_ID"]);
			$CACHE_MANAGER->EndTagCache();
			
			//save cache
			$oExtraCache->EndDataCache($arElement);
			
			//drop
			unset($oExtraCache);

		}

    	//return result
        return $arElement;

    }

	//resize pictures
    public static function get_more_pictures($pictureID, $arResizeParams, $arPushImage = array()){

    	//vars
    	$arWaterMark = array();
    	
    	//get settings
		$arTemplateSettings = DwSettings::getInstance()->getCurrentSettings();

		//watermark options
		if(!empty($arTemplateSettings["TEMPLATE_USE_AUTO_WATERMARK"]) && $arTemplateSettings["TEMPLATE_USE_AUTO_WATERMARK"] == "Y"){
	    	$arWaterMark = Array(
	            array(
	                "alpha_level" => $arTemplateSettings["TEMPLATE_WATERMARK_ALPHA_LEVEL"],
	                "coefficient" => $arTemplateSettings["TEMPLATE_WATERMARK_COEFFICIENT"],
	                "position" => $arTemplateSettings["TEMPLATE_WATERMARK_POSITION"],
	                "file" => $arTemplateSettings["TEMPLATE_WATERMARK_PICTURE"],
					"color" => $arTemplateSettings["TEMPLATE_WATERMARK_COLOR"],
	                "type" => $arTemplateSettings["TEMPLATE_WATERMARK_TYPE"],
	                "size" => $arTemplateSettings["TEMPLATE_WATERMARK_SIZE"],
	                "fill" => $arTemplateSettings["TEMPLATE_WATERMARK_FILL"],
					"font" => $arTemplateSettings["TEMPLATE_WATERMARK_FONT"],
					"text" => $arTemplateSettings["TEMPLATE_WATERMARK_TEXT"],
	                "name" => "watermark",
	            )
	        );
		}

        $arPushImage["SMALL_IMAGE"] = array_change_key_case(CFile::ResizeImageGet($pictureID, array("width" => $arResizeParams["SMALL_PICTURE"]["WIDTH"], "height" => $arResizeParams["SMALL_PICTURE"]["HEIGHT"]), BX_RESIZE_IMAGE_PROPORTIONAL, false), CASE_UPPER);  
        $arPushImage["REGULAR_IMAGE"] = array_change_key_case(CFile::ResizeImageGet($pictureID, array("width" => $arResizeParams["REGULAR_PICTURE"]["WIDTH"], "height" => $arResizeParams["REGULAR_PICTURE"]["HEIGHT"]), BX_RESIZE_IMAGE_PROPORTIONAL, false), CASE_UPPER);  
        $arPushImage["MEDIUM_IMAGE"] = array_change_key_case(CFile::ResizeImageGet($pictureID, array("width" => $arResizeParams["MEDIUM_PICTURE"]["WIDTH"], "height" => $arResizeParams["MEDIUM_PICTURE"]["HEIGHT"]), BX_RESIZE_IMAGE_PROPORTIONAL, false, $arWaterMark), CASE_UPPER);  
        $arPushImage["LARGE_IMAGE"] = array_change_key_case(CFile::ResizeImageGet($pictureID, array("width" => $arResizeParams["LARGE_PICTURE"]["WIDTH"], "height" => $arResizeParams["LARGE_PICTURE"]["HEIGHT"]), BX_RESIZE_IMAGE_PROPORTIONAL, false, $arWaterMark), CASE_UPPER);  
        return $arPushImage;
    }


}
