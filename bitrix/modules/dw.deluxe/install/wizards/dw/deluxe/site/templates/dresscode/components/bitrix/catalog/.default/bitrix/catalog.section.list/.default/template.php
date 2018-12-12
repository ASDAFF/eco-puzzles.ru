<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

	$this->setFrameMode(true);

	$i = 0;
	$b = 0;

	foreach($arResult["SECTIONS"] as $arElement){
		if($arElement["DEPTH_LEVEL"] == 1){
			$i++;
			$result[$i] = array(
				"NAME" => $arElement["NAME"],
				"SECTION_PAGE_URL" => $arElement["SECTION_PAGE_URL"],
				"DETAIL_PICTURE" => CFile::ResizeImageGet($arElement['DETAIL_PICTURE'], array("width" => 360, "height" => 300), BX_RESIZE_IMAGE_PROPORTIONAL, false)
			);

			$rsSect = CIBlockSection::GetList(false, array("ID" => $arElement["ID"], "IBLOCK_ID" => $arElement["IBLOCK_ID"]), false, array("ID", "IBLOCK_ID", "UF_DESC", "UF_MARKER"));
			if ($arSect = $rsSect->GetNext()){

				if(!empty($arSect["~UF_DESC"])){
					$result[$i]["UF_DESC"] = $arSect["~UF_DESC"];
				}

				if(!empty($arSect["UF_MARKER"])){
					$result[$i]["UF_MARKER"] = $arSect["UF_MARKER"];
				}

			}

		}
		elseif($arElement["DEPTH_LEVEL"] == 2){
			$result[$i]["ELEMENTS"][] = array(
				"NAME" => $arElement["NAME"],
				"SECTION_PAGE_URL" => $arElement["SECTION_PAGE_URL"],
				"PICTURE" => CFile::ResizeImageGet($arElement["PICTURE"], array("width" => 25, "height" => 20), BX_RESIZE_IMAGE_PROPORTIONAL, false)
			);
		}elseif($arElement["DEPTH_LEVEL"] == 3){
			$result[$i]["ELEMENTS"][$b]["ELEMENTS"][] = array(
				"NAME" => $arElement["NAME"],
				"SECTION_PAGE_URL" => $arElement["SECTION_PAGE_URL"]
			);
		}
	}

	?>

	<div id="catalogSection">
		<?if(count($result) > 0):?>
			<div class="sectionItems">
				<?foreach($result as $nextElement):?>
					<div class="item">
						<div class="itemContainer">
							<?if(!empty($nextElement["DETAIL_PICTURE"]["src"])):?>
								<div class="column bigPicture">
									<?if(!empty($nextElement["UF_MARKER"])):?> 
										<div class="markerContainer">
											<div class="marker"><?=$nextElement["UF_MARKER"]?></div>
										</div>
									<?endif;?>
									<a href="<?=$nextElement["SECTION_PAGE_URL"]?>"><img src="<?=$nextElement["DETAIL_PICTURE"]["src"]?>" alt="<?=$nextElement["NAME"]?>"></a>
								</div>
							<?endif;?>
							<div class="column">
								<a href="<?=$nextElement["SECTION_PAGE_URL"]?>" class="bigTitle"><?=$nextElement["NAME"]?></a>
								<?if(!empty($nextElement["UF_DESC"])):?>
									<div class="description"><?=$nextElement["UF_DESC"]?></div>
								<?endif;?>
								<?if(count($nextElement["ELEMENTS"])):?>
									<div class="sectionList">
										<?foreach($nextElement["ELEMENTS"] as $next2Elements):?>
											<div class="section"><a href="<?=$next2Elements["SECTION_PAGE_URL"]?>"><?if(!empty($next2Elements["PICTURE"]["src"])):?><img src="<?=$next2Elements["PICTURE"]["src"]?>" alt="<?=$next2Elements["NAME"]?>"><?endif;?><?=$next2Elements["NAME"]?></a></div>
										<?endforeach;?>	
									</div>
								<?endif;?>	
							</div>
						</div>
					</div>
				<?endforeach;?>
			</div>
		<?endif;?>	
	</div>