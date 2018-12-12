<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);
?>
<?if(!empty($arResult["SECTIONS"])):?>
	<div class="global-block-container">
		<div class="global-content-block">
			<?foreach($arResult["SECTIONS"] as $arNextSection):?>
				<?if(!empty($arNextSection["ITEMS"])):?>
					<div class="questions-answers">
						<?if(!empty($arNextSection["NAME"])):?>
							<div class="h2 ff-medium"><?=$arNextSection["NAME"]?></div>
						<?endif;?>
						<div class="questions-answers-list">
							<?foreach ($arNextSection["ITEMS"] as $ii => $arNextElement):?>
								<?
									$this->AddEditAction($arNextElement["ID"], $arNextElement["EDIT_LINK"], CIBlock::GetArrayByID($arNextElement["IBLOCK_ID"], "ELEMENT_EDIT"));
									$this->AddDeleteAction($arNextElement["ID"], $arNextElement["DELETE_LINK"], CIBlock::GetArrayByID($arNextElement["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage("CT_BNL_ELEMENT_DELETE_CONFIRM")));
								?>
								<div class="question-answer-wrap" id="<?=$this->GetEditAreaId($arNextElement["ID"]);?>">
									<div class="question"><?=$arNextElement["NAME"]?>
										<div class="open-answer"><span class="hide-answer-text"><?=GetMessage("FAQ_CLOSE_LABEL")?></span><span class="open-answer-text"><?=GetMessage("FAQ_MORE_LABEL")?></span><div class="open-answer-btn"></div></div>
									</div>
									<?if(!empty($arNextElement["DETAIL_TEXT"])):?>
										<div class="answer"><?=$arNextElement["DETAIL_TEXT"]?></div>
									<?endif;?>
								</div>							
							<?endforeach;?>
						</div>
					</div>
				<?endif;?>
			<?endforeach;?>
		</div>
		<div class="global-information-block"></div>
	</div>
<?endif;?>