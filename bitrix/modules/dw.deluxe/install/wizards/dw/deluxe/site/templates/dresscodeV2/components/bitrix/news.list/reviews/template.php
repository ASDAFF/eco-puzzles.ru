<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$this->setFrameMode(true);
?>
<div class="shop-reviews">
	<div class="shop-reviews-container">
		<div class="shop-reviews-container-left">
			<div class="shop-reviews-heading"><?=GetMessage("SHOP_REVIEW_HEADING")?></div>
			<div class="shop-reviews-rating-count"><?=GetMessage("SHOP_REVIEW_COUNT_LABEL")?> <?=$arResult["COUNT_RATING_ITEMS"]?></div>
			<div class="shop-reviews-top-rating">
				<div class="shop-reviews-rating">
					<i class="m" style="width:<?=($arResult["RATING_SUM"] * 100 / 5)?>%"></i>
					<i class="h"></i>
				</div>
			</div>
		</div>
		<div class="shop-reviews-container-right">
			<div class="shop-review-top-new-container">
				<a href="<?=SITE_DIR?>auth/" class="shop-review-top-new-button<?if(!$USER->IsAuthorized()):?> no-auth<?endif;?>"><?=GetMessage("SHOP_REVIEW_NEW_BUTTON")?></a>
			</div>
		</div>
	</div>
	<div class="shop-reviews-text"><?=GetMessage("SHOP_REVIEW_HEADING_TEXT")?></div>
	<?if(!empty($arResult["ITEMS"])):?>
		<div class="shop-reviews-list">
			<?foreach($arResult["ITEMS"] as $arNextElement):?>
				<?
					$this->AddEditAction($arNextElement["ID"], $arNextElement["EDIT_LINK"], CIBlock::GetArrayByID($arNextElement["IBLOCK_ID"], "ELEMENT_EDIT"));
					$this->AddDeleteAction($arNextElement["ID"], $arNextElement["DELETE_LINK"], CIBlock::GetArrayByID($arNextElement["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage("CT_BNL_ELEMENT_DELETE_CONFIRM")));
				?>
				<?if(!empty($arNextElement["DETAIL_TEXT"])):?>
					<div class="shop-reviews-list-item" id="<?=$this->GetEditAreaId($arNextElement["ID"]);?>">
						<div class="shop-review-item-table">
							<div class="shop-review-item-cell shop-review-item-col-autor">
								<div class="shop-review-item-date"><?=CIBlockFormatProperties::DateFormat("j F Y", MakeTimeStamp($arNextElement["DATE_CREATE"], CSite::GetDateFormat()));?></div>
								<div class="shop-review-item-author">
									<?=GetMessage("SHOP_REVIEW_AUTHOR")?> <?=$arNextElement["PROPERTIES"]["USER_NAME"]["VALUE"]?>
								</div>
								<div class="shop-review-item-rating">
									<div class="shop-reviews-rating">
										<i class="m" style="width:<?=($arNextElement["PROPERTIES"]["RATING"]["VALUE"] * 100 / 5)?>%"></i>
										<i class="h"></i>
									</div>
								</div>
								<div class="shop-review-item-utile">
									<div class="shop-review-item-utile-heading"><?=GetMessage("SHOP_REVIEW_UTILE")?></div>
									<a href="#" class="shop-review-item-utile-good" data-id="<?=$arNextElement["ID"]?>" data-iblock-id="<?=$arParams["IBLOCK_ID"]?>"><?=GetMessage("SHOP_REVIEW_UTILE_YES")?> (<span><?=intval($arNextElement["PROPERTIES"]["GOOD_REVIEW"]["VALUE"])?></span>)</a>
									<a href="#" class="shop-review-item-utile-bad" data-id="<?=$arNextElement["ID"]?>" data-iblock-id="<?=$arParams["IBLOCK_ID"]?>"><?=GetMessage("SHOP_REVIEW_UTILE_NO")?> (<span><?=intval($arNextElement["PROPERTIES"]["BAD_REVIEW"]["VALUE"])?></span>)</a>
								</div>
							</div>
							<div class="shop-review-item-cell shop-review-item-col-text">
								<div class="shop-review-item-text">
									<?=$arNextElement["DETAIL_TEXT"]?>
								</div>
								<?if(!empty($arNextElement["PROPERTIES"]["ANSWER"]["VALUE"])):?>
									<div class="shop-review-item-answer">
										<a class="shop-review-item-answer-link"><?=GetMessage("SHOP_REVIEW_ANSWER")?></a>
										<div class="shop-review-item-answer-text">
											<?=$arNextElement["PROPERTIES"]["ANSWER"]["VALUE"]?>
										</div>
									</div>
								<?endif;?>
							</div>
						</div>
					</div>
				<?endif;?>
			<?endforeach;?>
			<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
				<br /><?=$arResult["NAV_STRING"]?>
			<?endif;?>
		</div>
	<?endif;?>
</div>
<?if($USER->IsAuthorized()):?>
	<div class="shop-review-form-container">
		<div class="shop-review-form-heading"><?=GetMessage("SHOP_REVIEW_FORM_HEADING")?></div>
		<form method="get" name="shop-review-form" action="<?=SITE_DIR?>" class="shop-review-form" data-iblock-id="<?=$arParams["IBLOCK_ID"]?>">
			<div class="shop-review-form-in">
				<div class="shop-review-form-in-left">
					<label><?=GetMessage("SHOP_REVIEW_FORM_RATING")?>*</label>
					<select name="review-rating" data-required="Y">
						<option value="0"><?=GetMessage("SHOP_REVIEW_FORM_RATING_SELECT")?></option>
						<option value="5">5 - <?=GetMessage("SHOP_REVIEW_FORM_RATING_VALUE_5")?></option>
						<option value="4">4 - <?=GetMessage("SHOP_REVIEW_FORM_RATING_VALUE_4")?></option>
						<option value="3">3 - <?=GetMessage("SHOP_REVIEW_FORM_RATING_VALUE_3")?></option>
						<option value="2">2 - <?=GetMessage("SHOP_REVIEW_FORM_RATING_VALUE_2")?></option>
						<option value="1">1 - <?=GetMessage("SHOP_REVIEW_FORM_RATING_VALUE_1")?></option>
					</select>
				</div>
				<div class="shop-review-form-in-right">
					<label><?=GetMessage("SHOP_REVIEW_FORM_NAME")?>*</label>
					<input type="text" name="review-name" value="<?if(!empty($arResult["USER"]["NAME"])):?><?=$arResult["USER"]["NAME"]?><?endif;?>" data-required="Y">
				</div>
			</div>
			<div class="shop-review-form-row">
				<label><?=GetMessage("SHOP_REVIEW_FORM_TEXT")?>*</label>
				<textarea name="review-text" data-required="Y"></textarea>
			</div>
			<div class="shop-review-form-submit-container">
				<input type="hidden" name="iblock_id" value="<?=$arParams["IBLOCK_ID"]?>">
				<input type="submit" name="submit" value="<?=GetMessage("SHOP_REVIEW_FORM_SUBMIT")?>" class="shop-review-form-submit">
				<span id="shop-review-form-submit-fast-loader">
					<span class="f_circleG2" id="frotateG2_01"></span>
					<span class="f_circleG2" id="frotateG2_02"></span>
					<span class="f_circleG2" id="frotateG2_03"></span>
					<span class="f_circleG2" id="frotateG2_04"></span>
					<span class="f_circleG2" id="frotateG2_05"></span>
					<span class="f_circleG2" id="frotateG2_06"></span>
					<span class="f_circleG2" id="frotateG2_07"></span>
					<span class="f_circleG2" id="frotateG2_08"></span>
				</span>
			</div>
		</form>
	</div>
<?endif;?>
<div class="shop-review-message-window">
	<div class="shop-review-message-window-cn">
		<div class="shop-review-message-window-heading">
			<div class="window-heading-text"></div>
			<a href="#" class="shop-review-message-window-close"></a>
		</div>
		<div class="shop-review-message-window-message"></div>
		<div class="shop-review-message-exit-cn">
			<a href="#" class="shop-review-message-window-exit"><?=GetMessage("SHOP_REVIEW_WINDOW_EXIT")?></a>
		</div>
	</div>
</div>
<script type="text/javascript">
	var reviewFormAjaxDir = "<?=$templateFolder?>";
	var reviewFormLang = {
		errorHeading: "<?=GetMessage("SHOP_REVIEW_WINDOWS_ERROR_HEADING")?>",
		successHeading: "<?=GetMessage("SHOP_REVIEW_WINDOWS_SUCCESS_HEADING")?>",
		successMessage: "<?=GetMessage("SHOP_REVIEW_WINDOWS_SUCCESS_MESSAGE")?>",
		errorTypeMessage_1: "<?=GetMessage("SHOP_REVIEW_WINDOWS_ERROR_TYPE_1")?>",
		errorTypeMessage_2: "<?=GetMessage("SHOP_REVIEW_WINDOWS_ERROR_TYPE_2")?>",
		errorTypeMessage_3: "<?=GetMessage("SHOP_REVIEW_WINDOWS_ERROR_TYPE_3")?>",
		errorTypeMessage_4: "<?=GetMessage("SHOP_REVIEW_WINDOWS_ERROR_TYPE_4")?>",
		errorTypeMessage_5: "<?=GetMessage("SHOP_REVIEW_WINDOWS_ERROR_TYPE_5")?>",
		errorTypeMessage_6: "<?=GetMessage("SHOP_REVIEW_WINDOWS_ERROR_TYPE_6")?>",
		errorTypeMessage_7: "<?=GetMessage("SHOP_REVIEW_WINDOWS_ERROR_TYPE_7")?>",
		errorTypeMessage_8: "<?=GetMessage("SHOP_REVIEW_WINDOWS_ERROR_TYPE_8")?>",
	};
</script>
