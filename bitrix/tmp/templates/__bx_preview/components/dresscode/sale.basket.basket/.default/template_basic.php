<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$this->setFrameMode(false);?>
<?
	$arBasketTemplates = array(
		"SQUARES" => array(
			"CHANGE_URL" => $APPLICATION->GetCurPageParam("basketView=squares", array("basketView")),
			"TEMPLATE_FILE" => "/include/basket_squares.php",
			"CLASS_NAME" => "squares"
		),
		"TABLE" => array(
			"CHANGE_URL" => $APPLICATION->GetCurPageParam("basketView=table", array("basketView")),
			"TEMPLATE_FILE" => "/include/basket_table.php",
			"CLASS_NAME" => "table"
		)
	);

	if(!empty($_GET["basketView"]) && !empty($arBasketTemplates[strtoupper($_GET["basketView"])])){
		setcookie("DW_BASKET_TEMPLATE", strtolower($_GET["basketView"]), time() + 3600000);
		$arBasketTemplates[strtoupper($_GET["basketView"])]["SELECTED"] = "Y";
		$_COOKIE["DW_BASKET_TEMPLATE"] = strtolower($_GET["basketView"]);
	}elseif(!empty($_COOKIE["DW_BASKET_TEMPLATE"])){
		$arBasketTemplates[strtoupper($_COOKIE["DW_BASKET_TEMPLATE"])]["SELECTED"] = "Y";
	}else{
		$arBasketTemplates[key($arBasketTemplates)]["SELECTED"] = "Y";
	}

?>

<script>
	var ajaxDir = "<?=$componentPath?>";
</script>

<?if(!empty($arResult["ITEMS"])):?>
	
	<?$countPos = 0?>
	<?$OPTION_CURRENCY = CCurrency::GetBaseCurrency();?>

	<div id="orderSuccess">
		<h2><?=GetMessage("ORDER_MAKE")?></h2>
		<p><?=GetMessage("ORDER_MAKE_NEXT")?></p>
	</div>

	<div id="personalCart">
		
		<div id="basketTopLine">
			<div id="tabsControl">
				<div class="item"><?=GetMessage("BASKET_TABS_ACTIONS")?></div>
				<div class="item"><a href="<?=SITE_DIR?>personal/cart/order/" id="scrollToOrder" class="selected"><?=GetMessage("BASKET_TABS_ORDER_MAKE")?></a></div>
				<div class="item"><a href="<?=SITE_DIR?>catalog/"><?=GetMessage("BASKET_TABS_CONTINUE")?></a></div>
				<div class="item"><a href="#" id="allClear"><?=GetMessage("BASKET_TABS_CLEAR")?></a></div>
			</div>
			<?if(!empty($arBasketTemplates)):?>
				<div id="basketView">
						<div class="item">
							<span><?=GetMessage("BASKET_VIEW_LABEL")?></span>
						</div>
					<?foreach ($arBasketTemplates as $arNextBasketTemplate):?>
						<div class="item">
							<a href="<?=$arNextBasketTemplate["CHANGE_URL"]?>" class="<?=$arNextBasketTemplate["CLASS_NAME"]?><?if($arNextBasketTemplate["SELECTED"] == "Y"):?> selected<?endif;?>"></a>
						</div>
					<?endforeach;?>
				</div>
			<?endif;?>
		</div>
		<div id="basketProductList">
			<?if(!empty($_COOKIE["DW_BASKET_TEMPLATE"]) && $_COOKIE["DW_BASKET_TEMPLATE"] == "table"):?>
				<?if(!empty($arBasketTemplates["TABLE"]["TEMPLATE_FILE"])):?>
					<?include($_SERVER["DOCUMENT_ROOT"].$templateFolder.$arBasketTemplates["TABLE"]["TEMPLATE_FILE"]);?>
				<?endif;?>
			<?else:?>
				<?if(!empty($arBasketTemplates["TABLE"]["TEMPLATE_FILE"])):?>
					<?include($_SERVER["DOCUMENT_ROOT"].$templateFolder.$arBasketTemplates["SQUARES"]["TEMPLATE_FILE"]);?>
				<?endif;?>
			<?endif;?>
		</div>
		<div class="orderLine">
			<div id="sum">
				<span class="label hd"><?=GetMessage("TOTAL_QTY")?></span>
				<span class="price hd" id="countItems"><?=$countPos?></span> 
				<span class="label"><?=GetMessage("TOTAL_SUM")?></span> 
				<span class="price">
					<span id="allSum"><?=FormatCurrency($arResult["SUM"], $OPTION_CURRENCY);?></span>
				</span>								
			</div>
			<form id="coupon">
				<input placeholder="<?=GetMessage("COUPON_LABEL")?>" name="user" class="couponField"><input type="submit" value="<?=GetMessage("COUPON_ACTIVATE")?>" class="couponActivate">
			</form>
		</div>
		<div id="giftContainer">
			<?$APPLICATION->IncludeComponent("bitrix:sale.gift.basket", ".default", Array(
					"APPLIED_DISCOUNT_LIST" => $arResult["APPLIED_DISCOUNT_LIST"],
					"HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
					"CONVERT_CURRENCY" => $arParams["GIFT_CONVERT_CURRENCY"],
					"FULL_DISCOUNT_LIST" => $arResult["FULL_DISCOUNT_LIST"],
					"PRODUCT_PRICE_CODE" => $arParams["PRODUCT_PRICE_CODE"],
					"CURRENCY_ID" => $arParams["GIFT_CURRENCY_ID"],
					"HIDE_MEASURES" => $arParams["HIDE_MEASURES"],
					"PAGE_ELEMENT_COUNT" => "12",
					"LINE_ELEMENT_COUNT" => "12",
					"CACHE_GROUPS" => "Y",
				),
				false
			);?>
		</div>
		<div id="order" class="orderContainer">
			<span class="title"><?=GetMessage("ORDER_HEADING")?></span> 
			<table class="personSelect">
				<tr>
					<td>
						<span><?=GetMessage("ORDER_PERSON")?></span>
					</td>
					<td>
						<?if(!empty($arResult["PERSON_TYPE"])):?>
							<label><?=GetMessage("ORDER_PERSON_SELECT")?></label>
							<select id="personSelect">
								<?foreach ($arResult["PERSON_TYPE"] as $arPersonType):?>
									<?if($arPersonType["ACTIVE"] === "Y"):?>
										<option value="<?=$arPersonType["ID"]?>" data-id="<?=$arPersonType["ID"]?>"><?=$arPersonType["NAME"]?></option>
									<?endif;?>
								<?endforeach;?>
							</select>
						<?endif;?>
					</td>
				</tr>
			</table>
			<?if(!empty($arResult["PERSON_TYPE"])):?>
				<?foreach ($arResult["PERSON_TYPE"] as $personIndex => $arPersonType):?>
					<form id="orderForm_<?=$arPersonType["ID"]?>">
						<table class="orderProps<?if($arPersonType["FIRST"] == "Y"):?> active<?endif;?>" data-id="person_<?=$arPersonType["ID"]?>">
							<?foreach ($arResult["PROP_GROUP"][$personIndex] as $groupIndex => $arGroup):?>
								<?if(!empty($arResult["PROPERTIES"][$groupIndex])):?>
									<tr>
										<td>
											<span><?=$arGroup["NAME"]?></span>
										</td>
										<td>
											<ul class="userProp">
												<?foreach ($arResult["PROPERTIES"][$groupIndex] as $arProperty):?>
													<?if($arProperty["IS_EMAIL"] == "Y"){
														$arProperty["DEFAULT_VALUE"] = $arResult["USER"]["EMAIL"];
													}elseif($arProperty["CODE"] == "PHONE"){
														$arProperty["DEFAULT_VALUE"] = $arResult["USER"]["PERSONAL_MOBILE"];
													}elseif($arProperty["CODE"] == "FIO"){
														$arProperty["DEFAULT_VALUE"] = trim($arResult["USER"]["NAME"]." ".$arResult["USER"]["LAST_NAME"]." ".$arResult["USER"]["SECOND_NAME"]);
													}
													elseif($arProperty["TYPE"] == "LOCATION" ){
														if(!empty($arResult["LOCATION"])){
															$arProperty["DEFAULT_VALUE"] = $arResult["LOCATION"]["COUNTRY_NAME"];
															if(!empty($arResult["LOCATION"]["REGION_NAME"])){
																$arProperty["DEFAULT_VALUE"].=", ".$arResult["LOCATION"]["REGION_NAME"];
															}
															if(!empty($arResult["LOCATION"]["CITY_NAME"])){
																$arProperty["DEFAULT_VALUE"].=", ".$arResult["LOCATION"]["CITY_NAME"];
															}
														}else{
															$arProperty["DEFAULT_VALUE"] = false;
														}
														$arProperty["LOCATION_ID"] = !empty($arResult["LOCATION"]) ? $arResult["LOCATION"]["ID"] : false;

													}?>

													<li>
														<?if($arProperty["TYPE"] != "CHECKBOX"):?>
															<span class="label"><?=$arProperty["NAME"]?><?if($arProperty["REQUIED"] === "Y"):?>*<?endif;?></span>
															<label><?=$arProperty["DESCRIPTION"]?></label>
														<?endif;?>
														<?if($arProperty["TYPE"] == "TEXT"):?>
															<input type="text" name="ORDER_PROP_<?=$arProperty["ID"]?>" value="<?=$arProperty["DEFAULT_VALUE"]?>" <?if($arProperty["IS_EMAIL"] === "Y"):?>data-mail="Y"<?endif;?> data-requied="<?if($arProperty["REQUIED"] === "Y"):?>Y<?endif;?>" id="<?=$arProperty["ID"]?>"<?if($arProperty["IS_PAYER"] == "Y"):?> data-payer="Y"<?endif?><?if($arProperty["IS_PHONE"] == "Y"):?> data-mobile="Y"<?endif?>>
														<?elseif($arProperty["TYPE"] == "LOCATION"):?>
															<input type="text" name="ORDER_PROP_<?=$arProperty["ID"]?>" value="<?=$arProperty["DEFAULT_VALUE"]?>" data-requied="<?if($arProperty["REQUIED"] === "Y"):?>Y<?endif;?>" id="<?=$arProperty["ID"]?>" class="location"<?if(!empty($arProperty["LOCATION_ID"])):?> autocomplete="off" data-location="<?=$arProperty["LOCATION_ID"]?>"<?endif;?>>
														<?elseif($arProperty["TYPE"] == "TEXTAREA"):?>
															<textarea name="ORDER_PROP_<?=$arProperty["ID"]?>" value="<?=$arProperty["DEFAULT_VALUE"]?>" data-requied="<?if($arProperty["REQUIED"] === "Y"):?>Y<?endif;?>" id="<?=$arProperty["ID"]?>"<?if($arProperty["IS_ADDRESS"] === "Y"):?> data-address="Y"<?endif;?>></textarea>
														<?elseif($arProperty["TYPE"] == "CHECKBOX"):?>
															<input type="checkbox" value="Y"<?if($arProperty["DEFAULT_VALUE"] == "Y"):?> checked<?endif;?> data-requied="<?if($arProperty["REQUIED"] === "Y"):?>Y<?endif;?>" id="<?=$arProperty["ID"]?>" class="electroCheck" data-class="electroCheck_div" name=name="ORDER_PROP_<?=$arProperty["ID"]?>">
															<label for="<?=$arProperty["ID"]?>"><?=$arProperty["NAME"]?><?if($arProperty["REQUIED"] === "Y"):?>*<?endif;?></label>
														<?elseif($arProperty["TYPE"] == "SELECT"):?>
													        <select name="ORDER_PROP_<?=$arProperty["ID"]?>" data-requied="<?if($arProperty["REQUIED"] === "Y"):?>Y<?endif;?>">
													        <?
													        $db_vars = CSaleOrderPropsVariant::GetList(
												                array("SORT" => "ASC", "NAME" => "ASC"),
												                array("ORDER_PROPS_ID" => $arProperty["ID"])
													        );
													        while ($vars = $db_vars->Fetch()):?>
													            <option value="<?=$vars["VALUE"]?>"<?=(($vars["VALUE"] == $arProperty["DEFAULT_VALUE"]) ? " selected" : "")?>><?=htmlspecialchars($vars["NAME"])?></option>
													        <?endwhile;?>
													        ?>
													        </select>
															<?elseif($arProperty["TYPE"] == "RADIO"):?>
															<?$db_vars = CSaleOrderPropsVariant::GetList(
																array("SORT" => "ASC", "NAME" => "ASC"),
																array("ORDER_PROPS_ID" => $arProperty["ID"])
															);?>
															<?while($vars = $db_vars->Fetch()):?>
																<input type="radio" name="ORDER_PROP_<?=$arProperty["ID"]?>" value="<?=$vars["VALUE"]?>" data-requied="<?if($arProperty["REQUIED"] === "Y"):?>Y<?endif;?>" id="<?=$arProperty["ID"]?>" <?=(($vars["VALUE"] == $arProperty["DEFAULT_VALUE"]) ? " checked" : "")?>><label for="<?=$arProperty["ID"]?>"><?=htmlspecialchars($vars["NAME"])?></label>
															<?endwhile;?>
													    	<?elseif($arProperty["TYPE"] == "MULTISELECT"):?>
																<select multiple name="ORDER_PROP_<?=$arProperty["ID"]?>[]" size="<?=((IntVal($arProperty["SIZE1"]) > 0) ? $arProperty["SIZE1"] : 5)?>" class="multi" data-requied="<?if($arProperty["REQUIED"] === "Y"):?>Y<?endif;?>">
																<?
																	$arDef = Split(",", $arProperty["DEFAULT_VALUE"]);
																	for ($i = 0; $i < count($arDef); $i++)
																	$arDef[$i] = Trim($arDef[$i]);

																	$db_vars = CSaleOrderPropsVariant::GetList(
																		array("SORT" => "ASC", "NAME" => "ASC"),
																		array("ORDER_PROPS_ID" => $arProperty["ID"])
																	);
																?>
																<?while ($vars = $db_vars->Fetch()):?>
																	<option value="<?=$vars["VALUE"]?>"<?=(in_array($vars["VALUE"], $arDef) ? " selected" : "")?>><?=htmlspecialchars($vars["NAME"])?></option>
																<?endwhile;?>
																</select>
	   													<?endif;?>
													</li>
												<?endforeach;?>
											</ul>
										</td>
									</tr>
								<?endif;?>
							<?endforeach;?>
						</table>
						<table class="orderProps<?if($arPersonType["FIRST"] == "Y"):?> active<?endif;?>" data-id="person_<?=$arPersonType["ID"]?>">
						<?if(!empty($arResult["PAYSYSTEM"][$personIndex])):?>
							<tr>
								<td>
									<span><?=GetMessage("ORDER_PAY")?></span>
								</td>
								<td>
									<span class="label"><?=GetMessage("ORDER_PAY")?></span>
									<select class="paySelect" name="PAY_TYPE">
										<?foreach ($arResult["PAYSYSTEM"][$personIndex] as $arPay):?>
											<option value="<?=$arPay["ID"]?>"><?=$arPay["NAME"]?></option>
										<?endforeach;?>
									</select>
								</td>
						</tr>
						<?endif;?>
						<?if(!empty($arResult["DELIVERY"])):?>
							<tr>
								<td>
									<span><?=GetMessage("ORDER_DELIVERY")?></span>
								</td>
								<td>
									<span class="label"><?=GetMessage("ORDER_DELIVERY")?></span>
									<select class="deliSelect" name="DEVIVERY_TYPE">
										<?foreach ($arResult["DELIVERY"][$arPersonType["ID"]] as $arDevivery):?>
											<?if(empty($arResult["DELIVERY"][$arPersonType["ID"]]["FIRST"])){
												$arResult["DELIVERY"][$arPersonType["ID"]]["FIRST"] = $arDevivery["ID"];
											}?>
											<?if(!isset($arResult["FIRST_DELIVERY_PRICE"])):?>
												<?$arResult["FIRST_DELIVERY_PRICE"] = $arDevivery["PRICE"];?>
											<?endif;?>
											<option data-price="<?=intval($arDevivery["PRICE"])?>" value="<?=$arDevivery["ID"]?>"><?=$arDevivery["NAME"]?> <?=str_replace("-", ".", CurrencyFormat($arDevivery["PRICE"], $arDevivery["CURRENCY"]))?></option>
										<?endforeach;?>
									</select>
									<?if(!empty($arResult["DELIVERY_PROPS"])):?>
										<ul class="userProp">
											<?foreach ($arResult["DELIVERY_PROPS"] as $i => $arProperty):?>
												<?$visibile = $arResult["DELIVERY"][$arPersonType["ID"]]["FIRST"] == $arProperty["DELIVERY_ID"] ?  "" : "disabled" ?>
												<li data-id="deli_<?=$arProperty["DELIVERY_ID"]?>" class="deliProps<?if(!empty($visibile)):?> hidden<?endif;?>">
													<?if($arProperty["TYPE"] != "CHECKBOX"):?>
														<span class="label"><?=$arProperty["NAME"]?><?if($arProperty["REQUIED"] === "Y"):?>*<?endif;?></span>
														<label><?=$arProperty["DESCRIPTION"]?></label>
													<?endif;?>
													<?if($arProperty["TYPE"] == "TEXT"):?>
														<input type="text" name="ORDER_PROP_<?=$arProperty["ID"]?>" value="<?=$arProperty["DEFAULT_VALUE"]?>" data-requied="<?if($arProperty["REQUIED"] === "Y"):?>Y<?endif;?>" id="<?=$arProperty["ID"]?>" <?=$visibile?>>
													<?elseif($arProperty["TYPE"] == "TEXTAREA"):?>
														<textarea name="ORDER_PROP_<?=$arProperty["ID"]?>" value="<?=$arProperty["DEFAULT_VALUE"]?>" data-requied="<?if($arProperty["REQUIED"] === "Y"):?>Y<?endif;?>" id="<?=$arProperty["ID"]?>" <?if($arProperty["IS_ADDRESS"] === "Y"):?> data-address="Y"<?endif;?> <?=$visibile?>><?if($arProperty["IS_ADDRESS"] === "Y"):?><?=$arResult["USER"]["PERSONAL_STREET"]?><?endif;?></textarea>
													<?elseif($arProperty["TYPE"] == "LOCATION"):?>
														<input type="text" name="ORDER_PROP_<?=$arProperty["ID"]?>" value="<?=$arProperty["DEFAULT_VALUE"]?>" data-requied="<?if($arProperty["REQUIED"] === "Y"):?>Y<?endif;?>" id="<?=$arProperty["ID"]?>" class="location"<?if(!empty($arProperty["LOCATION_ID"])):?> autocomplete="off" data-location="<?=$arProperty["LOCATION_ID"]?>"<?endif;?>>
													<?elseif($arProperty["TYPE"] == "SELECT"):?>
												        <select name="ORDER_PROP_<?=$arProperty["ID"]?>" data-requied="<?if($arProperty["REQUIED"] === "Y"):?>Y<?endif;?>">
												        <?
												        $db_vars = CSaleOrderPropsVariant::GetList(
											                array("SORT" => "ASC", "NAME" => "ASC"),
											                array("ORDER_PROPS_ID" => $arProperty["ID"])
												        );
												        while ($vars = $db_vars->Fetch()):?>
												            <option value="<?=$vars["VALUE"]?>"<?=(($vars["VALUE"] == $arProperty["DEFAULT_VALUE"]) ? " selected" : "")?>><?=htmlspecialchars($vars["NAME"])?></option>
												        <?endwhile;?>
												        ?>
												        </select>
													<?elseif($arProperty["TYPE"] == "RADIO"):?>
														<?$db_vars = CSaleOrderPropsVariant::GetList(
															array("SORT" => "ASC", "NAME" => "ASC"),
															array("ORDER_PROPS_ID" => $arProperty["ID"])
														);?>
														<?while($vars = $db_vars->Fetch()):?>
															<input type="radio" name="ORDER_PROP_<?=$arProperty["ID"]?>" value="<?=$vars["VALUE"]?>" data-requied="<?if($arProperty["REQUIED"] === "Y"):?>Y<?endif;?>" id="<?=$arProperty["ID"]?>" <?=(($vars["VALUE"] == $arProperty["DEFAULT_VALUE"]) ? " checked" : "")?>><label for="<?=$arProperty["ID"]?>"><?=htmlspecialchars($vars["NAME"])?></label>
														<?endwhile;?>
												    <?elseif($arProperty["TYPE"] == "MULTISELECT"):?>
														<select multiple name="ORDER_PROP_<?=$arProperty["ID"]?>[]" size="<?=((IntVal($arProperty["SIZE1"]) > 0) ? $arProperty["SIZE1"] : 5)?>" class="multi" data-requied="<?if($arProperty["REQUIED"] === "Y"):?>Y<?endif;?>">
														<?
															$arDef = Split(",", $arProperty["DEFAULT_VALUE"]);
															for ($i = 0; $i < count($arDef); $i++)
															$arDef[$i] = Trim($arDef[$i]);

															$db_vars = CSaleOrderPropsVariant::GetList(
																array("SORT" => "ASC", "NAME" => "ASC"),
																array("ORDER_PROPS_ID" => $arProperty["ID"])
															);
														?>
														<?while ($vars = $db_vars->Fetch()):?>
															<option value="<?=$vars["VALUE"]?>"<?=(in_array($vars["VALUE"], $arDef) ? " selected" : "")?>><?=htmlspecialchars($vars["NAME"])?></option>
														<?endwhile;?>
														</select>
	   												<?endif;?>

												</li>
											<?endforeach;?>
										</ul>
									<?endif;?>
								</td>
							</tr>
						<?endif;?>
						<tr>
							<td></td>
							<td>
								<span class="label"><?=GetMessage("ORDER_COMMENT")?></span>
								<textarea name="COMMENT"></textarea>
								<div class="personalInfoLabel"><?=GetMessage("PERSONTAL_INFO_ORDER_LABEL")?></div>
							</td>
						</tr>
						</table>
						<input type="hidden" name="PERSON_TYPE" value="<?=$arPersonType["ID"]?>">
					</form>
				<?endforeach;?>
			<?endif;?>
			<div class="orderLine bottom">
				<div id="sum">
					<a href="#" class="order" id="orderMake"><img src="<?=SITE_TEMPLATE_PATH?>/images/order.png"> <?=GetMessage("ORDER_GO")?></a>
					<span class="label hd"><?=GetMessage("TOTAL_QTY")?></span> <span class="price hd" id="countOrderItems"><?=$countPos?></span>
					<span class="label"><?=GetMessage("ORDER_DELIVERY")?>:</span>
					<span class="price"><span id="allDevilerySum"><?=FormatCurrency($arResult["FIRST_DELIVERY_PRICE"], $OPTION_CURRENCY);?></span></span> 
					<span class="label"><?=GetMessage("TOTAL_SUM")?></span> 
					<span class="price"><span id="allOrderSum"><?=FormatCurrency($arResult["SUM"] + $arResult["FIRST_DELIVERY_PRICE"], $OPTION_CURRENCY);?></span></span>
				</div>
			</div>
		</div>
		<div class="clear"></div>
	</div>
	<div id="elementError">
	  <div id="elementErrorContainer">
	    <span class="heading"><?=GetMessage("ORDER_ERROR2")?></span>
	    <a href="#" id="elementErrorClose"></a>
	    <p class="message"></p>
	    <a href="#" class="close"><?=GetMessage("ORDER_CLOSE")?></a>
	  </div>
	</div>
<?else:?>
	<div id="empty">
		<div class="emptyWrapper">
			<div class="pictureContainer">
				<img src="<?=SITE_TEMPLATE_PATH?>/images/emptyFolder.png" alt="<?=GetMessage("EMPTY_HEADING")?>" class="emptyImg">
			</div>
			<div class="info">
				<h3><?=GetMessage("EMPTY_HEADING")?></h3>
				<p><?=GetMessage("EMPTY_TEXT")?></p>
				<a href="<?=SITE_DIR?>" class="back"><?=GetMessage("MAIN_PAGE")?></a>
			</div>
		</div>
		<?$APPLICATION->IncludeComponent("bitrix:menu", "emptyMenu", Array(
			"ROOT_MENU_TYPE" => "left",
				"MENU_CACHE_TYPE" => "N",
				"MENU_CACHE_TIME" => "3600",
				"MENU_CACHE_USE_GROUPS" => "Y",
				"MENU_CACHE_GET_VARS" => "",
				"MAX_LEVEL" => "1",
				"CHILD_MENU_TYPE" => "left",
				"USE_EXT" => "Y",
				"DELAY" => "N",
				"ALLOW_MULTI_SELECT" => "N",
			),
			false
		);?>
	</div>
<?endif;?>
<?if(!empty($arResult["ERRORS"])):?>
	<script type="text/javascript">
		<?foreach ($arResult["ERRORS"] as $ie => $nextError):?>
			console.error('<?=$nextError?>');
		<?endforeach;?>
	</script>
<?endif;?>

<script>
	var personalCartLANG = {
		"max-quantity": '<?=GetMessage("MAX_QUANTITY")?>'
	};
</script>