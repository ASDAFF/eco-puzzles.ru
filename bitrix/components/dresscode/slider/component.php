<?
	if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
		die();

		if(!CModule::IncludeModule("iblock") || !CModule::IncludeModule("catalog") || !CModule::IncludeModule("sale"))
			die();

		if ($this->StartResultCache()){
			$arSelect = Array("ID", "NAME", "IBLOCK_ID", "PREVIEW_PICTURE", "DETAIL_TEXT", "DETAIL_PICTURE", "PROPERTY_POSITION", "EDIT_LINK", "DELETE_LINK");
			$arFilter = Array("IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"], "IBLOCK_ID" => $arParams["IBLOCK_ID"], "ACTIVE_DATE" => "Y", "ACTIVE" => "Y");
			$res = CIBlockElement::GetList(array("SORT" => "ASC"), $arFilter, false, Array(), $arSelect);
			while($ob = $res->GetNextElement()){
				$fields = $ob->GetFields();
				$fields["PROPERTIES"] = $ob->GetProperties();
				$arButtons = CIBlock::GetPanelButtons(
					$fields["IBLOCK_ID"],
					$fields["ID"],
					$fields["ID"],
					array("SECTION_BUTTONS" => false,
						  "SESSID" => false, 
						  "CATALOG" => true
					)
				);
				$fields["EDIT_LINK"] = $arButtons["edit"]["edit_element"]["ACTION_URL"];
				$fields["DELETE_LINK"] = $arButtons["edit"]["delete_element"]["ACTION_URL"];

				if(!empty($fields['PREVIEW_PICTURE']) && !empty($fields['DETAIL_PICTURE'])){
					$fields["PREVIEW_PICTURE"] = CFile::ResizeImageGet($fields['PREVIEW_PICTURE'], array('width' => $arParams["PICTURE_WIDTH"], 'height' => $arParams["PICTURE_HEIGHT"]), BX_RESIZE_IMAGE_PROPORTIONAL, true);
					$fields["DETAIL_PICTURE"] = CFile::ResizeImageGet($fields['DETAIL_PICTURE'], array('width' => $arParams["PICTURE_WIDTH"], 'height' => $arParams["PICTURE_HEIGHT"]), BX_RESIZE_IMAGE_PROPORTIONAL, true);
				}elseif(!empty($fields['PREVIEW_PICTURE']) && empty($fields['DETAIL_PICTURE'])){
					$fields["DETAIL_PICTURE"] = $fields["PREVIEW_PICTURE"] = CFile::ResizeImageGet($fields['PREVIEW_PICTURE'], array('width' => $arParams["PICTURE_WIDTH"], 'height' => $arParams["PICTURE_HEIGHT"]), BX_RESIZE_IMAGE_PROPORTIONAL, true);
				}elseif(!empty($fields['DETAIL_PICTURE']) && empty($fields['PREVIEW_PICTURE'])){
					$fields["PREVIEW_PICTURE"] = $fields["DETAIL_PICTURE"] = CFile::ResizeImageGet($fields['DETAIL_PICTURE'], array('width' => $arParams["PICTURE_WIDTH"], 'height' => $arParams["PICTURE_HEIGHT"]), BX_RESIZE_IMAGE_PROPORTIONAL, true);
				}
				$arResult["ITEMS"][] = $fields;
			}
			$this->setResultCacheKeys(array());
			$this->IncludeComponentTemplate();
		}

?>