<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$this->setFrameMode(true);?>
<?if(!empty($arResult)):?>
	<?
		$uniqID = CAjax::GetComponentID($this->__component->__name, $this->__component->__template->__name, false);
	?>
	<div style="margin-top:12px;padding-top:18px;padding-bottom:18px;border:1px solid #e7e8ea;">
		<table width="100%" cellpadding="0" cellspacing="0" border="0">
			<tbody>
				<tr>
					<td style="width:190px;padding-right:8px;vertical-align:middle;text-align:center;">
						<a href="<?=$arParams["SITE_URL"]?><?=$arResult["DETAIL_PAGE_URL"]?>" target="_blank" style="display:inline-block;">
							<img src="<?=$arParams["SITE_URL"]?><?=$arResult["PICTURE"]["src"]?>" alt="<?if(!empty($arResult["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"])):?><?=$arResult["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"]?><?else:?><?=$arResult["NAME"]?><?endif;?>" title="<?if(!empty($arResult["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"])):?><?=$arResult["IPROPERTY_VALUES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"]?><?else:?><?=$arResult["NAME"]?><?endif;?>" style="display:block;width:auto;height:auto;max-width:190px;max-height:130px;">
						</a>
					</td>
					<td style="text-align:left;padding-left:12px;">
						<div style="padding-left:12px;padding-right:12px;">
							<?if(!empty($arResult["PROPERTIES"]["CML2_ARTICLE"]["VALUE"])):?>
								<div style="margin-bottom:6px;font-weight:normal;font-family:\'Arial\',sans-serif;font-size:14px;line-height:20px;color:#888888;"><?=GetMessage("CATALOG_ARTICLE_LABEL")?> <?=$arResult["PROPERTIES"]["CML2_ARTICLE"]["VALUE"]?></div>
							<?endif;?>
							<div style="width:240px;margin-bottom:12px;height:36px;overflow:hidden;margin-top:6px;font-weight:normal;font-family:\'Arial\',sans-serif;font-size:14px;line-height:36px;color:#000000;"><div style="display: inline-block; line-height:18px; vertical-align: middle;"><?=$arResult["NAME"]?></div></div>
							<?if(!empty($arResult["PRICE"])):?>
								<div style="margin-bottom:12px;font-weight:bold;font-family:\'Arial Bold\',Gadget,sans-serif;font-size:18px;line-height:20px;color:#000000;">
									<?=CCurrencyLang::CurrencyFormat($arResult["PRICE"]["DISCOUNT_PRICE"], $arResult["EXTRA_SETTINGS"]["CURRENCY"], true)?>
									<?if(!empty($arResult["PRICE"]["DISCOUNT"])):?>
										<s style="margin-left: 6px; font-size: 14px; display: inline-block; vertical-align: middle; color: #888888;"><?=CCurrencyLang::CurrencyFormat($arResult["PRICE"]["RESULT_PRICE"]["BASE_PRICE"], $arResult["EXTRA_SETTINGS"]["CURRENCY"], true)?></s>
									<?endif;?>
								</div>
							<?else:?>
								<div style="margin-bottom:12px;font-weight:bold;font-family:\'Arial Bold\',Gadget,sans-serif;font-size:18px;line-height:20px;color:#000000;"><?=GetMessage("REQUEST_PRICE_LABEL")?></div>
							<?endif;?>
							<a href="<?=$arParams["SITE_URL"]?><?=$arResult["DETAIL_PAGE_URL"]?>" style="display:inline-block;padding-top:4px;padding-right:12px;padding-bottom:6px;padding-left:12px;text-align:center;font-weight:normal;font-family:\'Arial\',sans-serif;font-size:14px;line-height:16px;border:1px solid #63c322;color:#63c322;text-decoration:none;border-radius:2px;"><?=GetMessage("CATALOG_PRODUCT_DETAIL_LABEL")?></a>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
<?endif;?>

