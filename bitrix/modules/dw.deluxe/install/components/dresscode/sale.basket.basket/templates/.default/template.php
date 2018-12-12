<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$this->setFrameMode(true);?>
<script>
	var ajaxDir = "<?=$this->GetFolder();?>";
</script>
<?if(!empty($arResult["ITEMS"])):?>
<?$countPos = 0?>
<?$OPTION_CURRENCY = CCurrency::GetBaseCurrency();?>
<div id="orderSuccess">
	<h2><?=GetMessage("ORDER_MAKE")?></h2>
	<p><?=GetMessage("ORDER_MAKE_NEXT")?></p>
</div>
<div id="personalCart">
	<table class="mainTable">
		<tbody>
			<tr>
				<td>
					<table class="productTable">
						<thead>
							<tr>
								<th colspan="7" class="clear"><a href="#" id="allClear"><?=GetMessage("CLEAR_CART")?></a></th>
							</tr>
							<tr>
								<th><?=GetMessage("TOP_IMAGE")?></th>
								<th><?=GetMessage("TOP_NAME")?></th>														
								<th><?=GetMessage("TOP_QTY")?></th>
								<th><?=GetMessage("TOP_AVAILABLE")?></th>
								<th><?=GetMessage("TOP_PRICE")?></th>
								<th><?=GetMessage("TOP_SUM")?></th>													
								<th><?=GetMessage("TOP_DELETE")?></th>
							</tr>
						</thead>
						<tbody>
							<?foreach ($arResult["ITEMS"] as $key => $arValues):?>
							<?$countPos +=$arValues["QUANTITY"] ?>
								<tr>
									<td><a href="<?=$arValues["INFO"]["DETAIL_PAGE_URL"]?>" target="_blank" class="pic"><img src="<?=!empty($arValues["INFO"]["PICTURE"]["src"]) ? $arValues["INFO"]["PICTURE"]["src"] : SITE_TEMPLATE_PATH."/images/empty.png"?>" alt="<?=$arValues["INFO"]["NAME"]?>"></a></td>
									<td class="name"><a href="<?=$arValues["INFO"]["DETAIL_PAGE_URL"]?>" target="_blank"><?=$arValues["INFO"]["NAME"]?></a></td>
									<td class="bQty">		
										<div id="basketQty">
											<a href="#" class="minus" data-id="<?=$arValues["ID"]?>"></a>
		   									<input name="qty" type="text" value="<?=intVal($arValues["QUANTITY"])?>" class="qty" data-id="<?=$arValues["ID"]?>" />
		   									<a href="#" class="plus" data-id="<?=$arValues["ID"]?>"></a> 
											</div>
		       						</td>
									<td>                            
										<?if($arValues["INFO"]["CATALOG_QUANTITY"] > 0):?>
			                                <span class="available"><?=GetMessage("AVAILABLE")?></span>
			                            <?else:?>
			                                <span class="noAvailable"><?=GetMessage("NOAVAILABLE")?></span>       
			                            <?endif;?>
                            		</td>
									<td>
										<span class="price">		      
											<?=($arValues["INFO"]["OLD_PRICE"] != $arValues["PRICE"] ? '<s>'.str_replace(GetMessage("RUB"),'<span class="rouble">P<i>-</i></span>', FormatCurrency($arValues["INFO"]["OLD_PRICE"],$OPTION_CURRENCY)).'</s>' : '')?>
				      						<?=str_replace(GetMessage("RUB"),'<span class="rouble">P<i>-</i></span>', FormatCurrency($arValues["PRICE"], $OPTION_CURRENCY));?> 
				      					</span>
				      				</td>
				      				<td>
				      					<span class="sum" data-price="<?=$arValues["PRICE"]?>"><?=str_replace(GetMessage("RUB"),'<span class="rouble">P<i>-</i></span>', FormatCurrency($arValues["PRICE"] * round($arValues["QUANTITY"]), $OPTION_CURRENCY));?> </span>
				      				</td>
									<td class="elementDelete"><a href="#" class="delete" data-id="<?=$arValues["ID"]?>"></a></td>
								</tr>
							<?endforeach;?>
						</tbody>
					</table>
					<div class="orderLine">
						<div id="sum">
							<?=GetMessage("TOTAL_QTY")?> <span class="price" id="countItems"><?=$countPos?></span> <span class="label"><?=GetMessage("TOTAL_SUM")?></span> <span class="price"><span id="allSum"><?=str_replace(GetMessage("RUB"),'<span class="rouble">P<i>-</i></span>', FormatCurrency($arResult["SUM"], $OPTION_CURRENCY));?></span></span>								
						</div>
					</div>
					<div id="order">
						<span class="heading"><?=GetMessage("ORDER_HEADING")?></span> 
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
														elseif($arProperty["CODE"] == "LOCATION"){
															$arProperty["DEFAULT_VALUE"] = $arResult["USER"]["PERSONAL_CITY"];
														}?>

															<li>
																<?if($arProperty["TYPE"] != "CHECKBOX"):?>
																	<span class="label"><?=$arProperty["NAME"]?><?if($arProperty["REQUIED"] === "Y"):?>*<?endif;?></span>
																	<label><?=$arProperty["DESCRIPTION"]?></label>
																<?endif;?>
																<?if($arProperty["TYPE"] == "TEXT"):?>
																	<input type="text" name="ORDER_PROP_<?=$arProperty["ID"]?>" value="<?=$arProperty["DEFAULT_VALUE"]?>" <?if($arProperty["IS_EMAIL"] === "Y"):?>data-mail="Y"<?endif;?> data-requied="<?if($arProperty["REQUIED"] === "Y"):?>Y<?endif;?>" id="<?=$arProperty["ID"]?>">
																<?elseif($arProperty["TYPE"] == "LOCATION"):?>
																	<input type="text" name="ORDER_PROP_<?=$arProperty["ID"]?>" value="<?=$arProperty["DEFAULT_VALUE"]?>" data-requied="<?if($arProperty["REQUIED"] === "Y"):?>Y<?endif;?>" id="<?=$arProperty["ID"]?>" class="location">
																<?elseif($arProperty["TYPE"] == "TEXTAREA"):?>
																	<textarea name="ORDER_PROP_<?=$arProperty["ID"]?>" value="<?=$arProperty["DEFAULT_VALUE"]?>" data-requied="<?if($arProperty["REQUIED"] === "Y"):?>Y<?endif;?>" id="<?=$arProperty["ID"]?>"></textarea>
																<?elseif($arProperty["TYPE"] == "CHECKBOX"):?>
																	<input type="checkbox" value="Y"<?if($arProperty["DEFAULT_VALUE"] == "Y"):?> checked<?endif;?> data-requied="<?if($arProperty["REQUIED"] === "Y"):?>Y<?endif;?>" id="<?=$arProperty["ID"]?>" class="electroCheck" data-class="electroCheck_div" name=name="ORDER_PROP_<?=$arProperty["ID"]?>">
																	<label for="<?=$arProperty["ID"]?>"><?=$arProperty["NAME"]?><?if($arProperty["REQUIED"] === "Y"):?>*<?endif;?></label>
																<?elseif($arProperty["TYPE"] == "SELECT"):?>
															        <select name="ORDER_PROP_<?=$arProperty["ID"]?>" data-requied="<?if($arProperty["REQUIED"] === "Y"):?>Y<?endif;?>">
															        <?
															        $db_vars = CSaleOrderPropsVariant::GetList(
														                array("SORT" => "ASC"),
														                array("ORDER_PROPS_ID" => $arProperty["ID"])
															        );
															        while ($vars = $db_vars->Fetch()):?>
															            <option value="<?=$vars["VALUE"]?>"<?=(($vars["VALUE"] == $arProperty["DEFAULT_VALUE"]) ? " selected" : "")?>><?=htmlspecialchars($vars["NAME"])?></option>
															        <?endwhile;?>
															        ?>
															        </select>
																	<?elseif($arProperty["TYPE"] == "RADIO"):?>
																	<?$db_vars = CSaleOrderPropsVariant::GetList(
																		array("SORT" => "ASC"),
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
																				array("SORT" => "ASC"),
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
								<?if(!empty($arResult["DELIVERY"])):?>
									<tr>
										<td>
											<span><?=GetMessage("ORDER_DELIVERY")?></span>
										</td>
										<td>
											<span class="label"><?=GetMessage("ORDER_DELIVERY")?></span>
											<select class="deliSelect" name="DEVIVERY_TYPE">
												<?foreach ($arResult["DELIVERY"] as $arDevivery):?>
													<?if(empty($arResult["DELIVERY"]["FIRST"])){
														$arResult["DELIVERY"]["FIRST"] = $arDevivery["ID"];
													}?>
													<option data-price="<?=intval($arDevivery["PRICE"])?>" value="<?=$arDevivery["ID"]?>"><?=$arDevivery["NAME"]?> <?=str_replace("-", ".", CurrencyFormat($arDevivery["PRICE"], $arDevivery["CURRENCY"]))?></option>
												<?endforeach;?>
											</select>
											<?if(!empty($arResult["DELIVERY_PROPS"])):?>
												<ul class="userProp">
													<?foreach ($arResult["DELIVERY_PROPS"] as $i => $arProperty):?>
														<?$visibile = $arResult["DELIVERY"]["FIRST"] == $arProperty["DELIVERY_ID"] ?  "" : "disabled" ?>
														<li id="deli_<?=$arProperty["DELIVERY_ID"]?>" class="deliProps<?if(!empty($visibile)):?> hidden<?endif;?>">
															<?if($arProperty["TYPE"] != "CHECKBOX"):?>
																<span class="label"><?=$arProperty["NAME"]?><?if($arProperty["REQUIED"] === "Y"):?>*<?endif;?></span>
																<label><?=$arProperty["DESCRIPTION"]?></label>
															<?endif;?>
															<?if($arProperty["TYPE"] == "TEXT"):?>
																<input type="text" name="ORDER_PROP_<?=$arProperty["ID"]?>" value="<?=$arProperty["DEFAULT_VALUE"]?>" data-requied="<?if($arProperty["REQUIED"] === "Y"):?>Y<?endif;?>" id="<?=$arProperty["ID"]?>" <?=$visibile?>>
															<?elseif($arProperty["TYPE"] == "TEXTAREA"):?>
																<textarea name="ORDER_PROP_<?=$arProperty["ID"]?>" value="<?=$arProperty["DEFAULT_VALUE"]?>" data-requied="<?if($arProperty["REQUIED"] === "Y"):?>Y<?endif;?>" id="<?=$arProperty["ID"]?>" <?=$visibile?>></textarea>
															<?endif;?>
														</li>
													<?endforeach;?>
												</ul>
											<?endif;?>
										</td>
									</tr>
								<?endif;?>
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
								<tr>
									<td></td>
									<td>
										<span class="label"><?=GetMessage("ORDER_COMMENT")?></span>
										<textarea name="COMMENT"></textarea>
									</td>
								</tr>
								</table>
								<input type="hidden" name="PERSON_TYPE" value="<?=$arPersonType["ID"]?>">
							</form>
						<?endforeach;?>
						<div class="orderLine">
							<div id="sum">
								<?=GetMessage("TOTAL_QTY")?> <span class="price" id="countOrderItems"><?=$countPos?></span>
								<span class="label"><?=GetMessage("ORDER_DELIVERY")?>:</span>
								<span class="price"><span id="allDevilerySum"><?=str_replace(GetMessage("RUB"),'<span class="rouble">P<i>-</i></span>', FormatCurrency($arResult["SUM_DELIVERY"], $OPTION_CURRENCY));?></span></span> 
								<span class="label"><?=GetMessage("TOTAL_SUM")?></span> 
								<span class="price"><span id="allOrderSum"><?=str_replace(GetMessage("RUB"),'<span class="rouble">P<i>-</i></span>', FormatCurrency($arResult["SUM"] + $arResult["SUM_DELIVERY"], $OPTION_CURRENCY));?></span></span>
							</div>
							<div>
								<a href="#" class="order" id="orderMake"><?=GetMessage("ORDER_GO")?></a>
							</div>
						</div>
					</div>
					<script>
			            $(window).bind('load', function(){
			                $("#accessoriesCarousel").electroCarousel({
			                    speed: 500,
			                    countElement: 4,
			                    leftButton: ".accessoriesLeft",
			                    rightButton: ".accessoriesRight"
			                });
			            });
			        </script>
					<?if(!empty($arResult["ACCESSORIES"])):?>
					<div id="accessoriesCarousel">
						<span class="heading"><?=GetMessage("ACCESSORIES")?></span>
						<ul class="productList">
							<?foreach($arResult["ACCESSORIES"]["ITEMS"] as $arElement):?>
							<li class="product">
								<div class="wrap">
									 <?=(!empty($arElement["PROPERTY_MARKER_VALUE"]) ? '<ins class="marker">'.$arElement["PROPERTY_MARKER_VALUE"].'</ins>' : '')?>
									<span class="rating"><i class="m" style="width:<?=($arElement["PROPERTY_RATING_VALUE"] * 100 / 5)?>%"></i><i class="h"></i></span>
									<a href="<?=$arElement["DETAIL_PAGE_URL"]?>" class="pic" target="_blank">
										<img src="<?=$arElement["PICTURE"]["src"]?>" alt="<?=$arElement["NAME"]?>">
									</a>
									<a href="<?=$arElement["DETAIL_PAGE_URL"]?>" class="name" target="_blank" title="<?=$arElement["NAME"]?>"><?=$arElement["NAME"]?></a>
									<span class="price">
										<?if(empty($arElement["SKU_PRICE"])):?>
											<?=str_replace(GetMessage("RUB"),'<span class="rouble">P<i>-</i></span>', $arElement["PRICE"]);?>
											<?=($arElement["OLD_PRICE"] ? '<s>'.str_replace(GetMessage("RUB"),'<span class="rouble">P<i>-</i></span>', $arElement["OLD_PRICE"]).'</s>' : '')?>			                           
									    <?else:?>
			                                <?=GetMessage("FROM")?><?=str_replace(GetMessage("RUB"),'<span class="rouble">Р<i>-</i></span>', $arElement["SKU_PRICE"]);?>
			                            <?endif;?>
									</span>
									<a href="#" class="<?=!empty($arElement["SKU"]) ? "addSku" : "addCart"?><?if(!$arElement["ADDCART"]):?> disabled<?endif;?>" data-ibl="<?=$arElement["IBLOCK_ID"]?>" data-id="<?=$arElement["ID"]?>" data-reload="Y"><?=!empty($arElement["SKU"]) ? GetMessage("ADDSKU") : GetMessage("ADDCART")?></a>
									</div>
							</li>
							<?endforeach;?>
						</ul>
						 <a href="#" class="accessoriesLeft"></a>
	  					 <a href="#" class="accessoriesRight"></a>
					</div>
					<?endif;?>
				</td>
				<?$APPLICATION->IncludeComponent(
					"electro:sale.viewed.product", 
					".default", 
					array(
						"VIEWED_COUNT" => "6",
						"VIEWED_NAME" => "Y",
						"VIEWED_IMAGE" => "Y",
						"VIEWED_PRICE" => "Y",
						"VIEWED_CANBUY" => "N",
						"VIEWED_CANBUSKET" => "N",
						"VIEWED_IMG_HEIGHT" => "150",
						"VIEWED_IMG_WIDTH" => "150",
						"BASKET_URL" => "/personal/basket.php",
						"ACTION_VARIABLE" => "action",
						"PRODUCT_ID_VARIABLE" => "id",
						"VIEWED_CURRENCY" => "RUB",
						"VIEWED_CANBASKET" => "N",
						"SET_TITLE" => "N"
					),
					false
				);?>
			</tr>
		</tbody>
	</table>
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
	<img src="<?=SITE_TEMPLATE_PATH?>/images/emptyCart.png" alt="<?=GetMessage("EMPTY_HEADING")?>" class="emptyImg">
	<div class="info">
		<h3><?=GetMessage("EMPTY_HEADING")?></h3>
		<p><?=GetMessage("EMPTY_TEXT")?></p>
		<a href="<?=SITE_DIR?>" class="back"><?=GetMessage("MAIN_PAGE")?></a>
	</div>
</div>
<?endif;?>