<?if(!empty($arResult["DELIVERY_ITEMS"])):?>
	<?foreach($arResult["DELIVERY_ITEMS"] as $ix => $arNextDelivery):?>
		<div class="delivery-item-container">
			<div class="tb delivery-item">
				<?if(!empty($arNextDelivery["LOGOTIP"])):?>
					<div class="tc image">
						<img src="<?=$arNextDelivery["LOGOTIP"]["src"]?>" alt="<?=$arNextDelivery["NAME"]?>">
					</div>
				<?endif;?>
				<div class="tc">
					<div class="delivery-name"><?=$arNextDelivery["NAME"]?></div>
					<?if(!empty($arNextDelivery["DESCRIPTION"])):?>
						<div class="delivery-descr"><?=$arNextDelivery["DESCRIPTION"]?></div>
					<?endif;?>
				</div>
				<div class="tc price"><?=$arNextDelivery["PRICE_FORMATED"]?></div>
			</div>
		</div>
	<?endforeach;?>
<?endif;?>