<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<?
	if(!empty($_GET["act"])){
		if(CModule::IncludeModule("sale") && CModule::IncludeModule("catalog") && CModule::IncludeModule("iblock")){
			if($_GET["act"] == "newReview"){
				global $USER;
				if ($USER->IsAuthorized()){
					if(!empty($_GET["iblock_id"])){
						//check fields
						if(!empty($_GET["review-name"]) && !empty($_GET["review-rating"]) && !empty($_GET["review-text"])){
							$curUserID = $USER->GetID();
							if(!empty($curUserID)){
								$res = CIBlockElement::GetList(
									Array(),
									Array(
										"IBLOCK_ID" => intval($_GET["iblock_id"]),
										"PROPERTY_USER_ID" => $curUserID
									),
									false,
									false,
									Array(
										"ID",
										"IBLOCK_ID",
									)
								);
								if(!$res->SelectedRowsCount()){
			
									$newReviewElement = new CIBlockElement;
									$PROP = array(
										"USER_NAME" => (BX_UTF == 1) ? htmlspecialcharsbx($_GET["review-name"]) : iconv("UTF-8","windows-1251//IGNORE", htmlspecialcharsbx($_GET["review-name"])),
										"RATING" => intval($_GET["review-rating"]),
										"USER_ID" => $curUserID
									);

									$arLoadReviewArray = Array(
										"IBLOCK_SECTION_ID" => false,
										"PROPERTY_VALUES"=> $PROP,
										"DETAIL_TEXT"    => (BX_UTF == 1) ? htmlspecialcharsbx($_GET["review-text"]) : iconv("UTF-8","windows-1251//IGNORE", htmlspecialcharsbx($_GET["review-text"])),
										"MODIFIED_BY"    => $curUserID,
										"IBLOCK_ID"      => intval($_GET["iblock_id"]),
										"NAME"           => (BX_UTF == 1) ? htmlspecialcharsbx($_GET["review-name"]) : iconv("UTF-8","windows-1251//IGNORE", htmlspecialcharsbx($_GET["review-name"]))." (".date($DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")), time()).") uid:".$curUserID,
										"ACTIVE"         => "N",
										"CODE"           => date($DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")), time())." uid:".$curUserID
									);

									if($REVIEW_ID = $newReviewElement->Add($arLoadReviewArray)){
										echo \Bitrix\Main\Web\Json::encode(
											array(
												"SUCCESS" => "Y",
												"ERROR" => "N"
											)
										);
									}
			
								}else{
									echo \Bitrix\Main\Web\Json::encode(
										array(
											"ERROR" => "Y",
											"ERROR_TEXT" => "YOU ALREADY VOTED",
											"ERROR_TYPE" => "1"
										)
									);
								}
							}
						}else{
							echo \Bitrix\Main\Web\Json::encode(
								array(
									"ERROR" => "Y",
									"ERROR_TEXT" => "CHECK REQUIDER FIELDS",
									"ERROR_TYPE" => "2"
								)
							);
						}
					}else{
						echo \Bitrix\Main\Web\Json::encode(
							array(
								"ERROR" => "Y",
								"ERROR_TEXT" => "IBLOCK ID NOT DETECTED",
								"ERROR_TYPE" => "3"
							)
						);
					}
				}else{
					echo \Bitrix\Main\Web\Json::encode(
						array(
							"ERROR" => "Y",
							"ERROR_TEXT" => "ACCESS DENIDED",
							"ERROR_TYPE" => "4"
						)
					);
				}
			}elseif($_GET["act"] == "utileBad"){
				global $USER;
				$curUserID = $USER->GetID();
				$arUsers[$curUserID] = $curUserID;
				if ($USER->IsAuthorized()){
					if(!empty($_GET["id"])){
						if(!empty($_GET["iblock_id"])){
							$dbUserVoteProp = CIBlockElement::GetProperty(intval($_GET["iblock_id"]), intval($_GET["id"]), array("sort" => "asc"), Array("CODE" => "USER_ID_VOTE"));
							while($arNextVoteProp = $dbUserVoteProp->Fetch()){
								if($arNextVoteProp["VALUE"] == $curUserID){
									echo \Bitrix\Main\Web\Json::encode(
										array(
											"ERROR" => "Y",
											"ERROR_TEXT" => "YOU ALREADY VOTED",
											"ERROR_TYPE" => "8"
										)
									);
									exit();
								}
							}

							$res = CIBlockElement::GetList(
								Array(),
								Array(
									"IBLOCK_ID" => intval($_GET["iblock_id"]),
									"ID" => intval($_GET["id"])
								),
								false,
								false,
								Array(
									"ID",
									"IBLOCK_ID",
									"PROPERTY_BAD_REVIEW"
								)
							);
							if($arReview = $res->GetNext()){
								$arReview["PROPERTY_BAD_REVIEW_VALUE"] = !empty($arReview["PROPERTY_BAD_REVIEW_VALUE"]) ? $arReview["PROPERTY_BAD_REVIEW_VALUE"] : 0;
								$arReview["PROPERTY_BAD_REVIEW_VALUE"]++;
								$db_props = CIBlockElement::GetProperty(intval($_GET["iblock_id"]), intval($_GET["id"]), array("sort" => "asc"), Array("CODE" => "USER_ID_VOTE"));
								if($arProps = $db_props->Fetch()){
									$arUsers[$arProps["VALUE"]] = $arProps["VALUE"];
								}
								CIBlockElement::SetPropertyValuesEx(intval($_GET["id"]), intval($_GET["iblock_id"]), array("USER_ID_VOTE" => $arUsers, "BAD_REVIEW" => $arReview["PROPERTY_BAD_REVIEW_VALUE"]));
								echo \Bitrix\Main\Web\Json::encode(
									array(
										"ERROR" => "N",
										"SUCCESS" => "Y",
										"VOTE_COUNT" => $arReview["PROPERTY_BAD_REVIEW_VALUE"]
									)
								);
							}else{
								echo \Bitrix\Main\Web\Json::encode(
									array(
										"ERROR" => "Y",
										"ERROR_TEXT" => "REVIEW NOT FOUND",
										"ERROR_TYPE" => "7"
									)
								);
							}
						}else{
							echo \Bitrix\Main\Web\Json::encode(
								array(
									"ERROR" => "Y",
									"ERROR_TEXT" => "IBLOCK ID NOT FOUND",
									"ERROR_TYPE" => "6"
								)
							);
						}
					}else{
						echo \Bitrix\Main\Web\Json::encode(
							array(
								"ERROR" => "Y",
								"ERROR_TEXT" => "REVIEW ID NOT FOUND",
								"ERROR_TYPE" => "5"
							)
						);
					}
				}else{
					echo \Bitrix\Main\Web\Json::encode(
						array(
							"ERROR" => "Y",
							"ERROR_TEXT" => "ACCESS DENIDED",
							"ERROR_TYPE" => "4"
						)
					);
				}
			}elseif($_GET["act"] == "utileGood"){
				global $USER;
				$curUserID = $USER->GetID();
				$arUsers[$curUserID] = $curUserID;
				if ($USER->IsAuthorized()){
					if(!empty($_GET["id"])){
						if(!empty($_GET["iblock_id"])){
							$dbUserVoteProp = CIBlockElement::GetProperty(intval($_GET["iblock_id"]), intval($_GET["id"]), array("sort" => "asc"), Array("CODE" => "USER_ID_VOTE"));
							while($arNextVoteProp = $dbUserVoteProp->Fetch()){
								if($arNextVoteProp["VALUE"] == $curUserID){
									echo \Bitrix\Main\Web\Json::encode(
										array(
											"ERROR" => "Y",
											"ERROR_TEXT" => "YOU ALREADY VOTED",
											"ERROR_TYPE" => "8"
										)
									);
									exit();
								}
							}

							$res = CIBlockElement::GetList(
								Array(),
								Array(
									"IBLOCK_ID" => intval($_GET["iblock_id"]),
									"ID" => intval($_GET["id"])
								),
								false,
								false,
								Array(
									"ID",
									"IBLOCK_ID",
									"PROPERTY_BAD_REVIEW"
								)
							);
							if($arReview = $res->GetNext()){
								$arReview["PROPERTY_GOOD_REVIEW_VALUE"] = !empty($arReview["PROPERTY_GOOD_REVIEW_VALUE"]) ? $arReview["PROPERTY_GOOD_REVIEW_VALUE"] : 0;
								$arReview["PROPERTY_GOOD_REVIEW_VALUE"]++;
								$db_props = CIBlockElement::GetProperty(intval($_GET["iblock_id"]), intval($_GET["id"]), array("sort" => "asc"), Array("CODE" => "USER_ID_VOTE"));
								if($arProps = $db_props->Fetch()){
									$arUsers[$arProps["VALUE"]] = $arProps["VALUE"];
								}
								CIBlockElement::SetPropertyValuesEx(intval($_GET["id"]), intval($_GET["iblock_id"]), array("USER_ID_VOTE" => $arUsers, "GOOD_REVIEW" => $arReview["PROPERTY_BAD_REVIEW_VALUE"]));
								echo \Bitrix\Main\Web\Json::encode(
									array(
										"ERROR" => "N",
										"SUCCESS" => "Y",
										"VOTE_COUNT" => $arReview["PROPERTY_GOOD_REVIEW_VALUE"]
									)
								);
							}else{
								echo \Bitrix\Main\Web\Json::encode(
									array(
										"ERROR" => "Y",
										"ERROR_TEXT" => "REVIEW NOT FOUND",
										"ERROR_TYPE" => "7"
									)
								);
							}
						}else{
							echo \Bitrix\Main\Web\Json::encode(
								array(
									"ERROR" => "Y",
									"ERROR_TEXT" => "IBLOCK ID NOT FOUND",
									"ERROR_TYPE" => "6"
								)
							);
						}
					}else{
						echo \Bitrix\Main\Web\Json::encode(
							array(
								"ERROR" => "Y",
								"ERROR_TEXT" => "REVIEW ID NOT FOUND",
								"ERROR_TYPE" => "5"
							)
						);
					}
				}else{
					echo \Bitrix\Main\Web\Json::encode(
						array(
							"ERROR" => "Y",
							"ERROR_TEXT" => "ACCESS DENIDED",
							"ERROR_TYPE" => "4"
						)
					);
				}
			}
		}
	}
?>