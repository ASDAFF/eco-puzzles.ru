<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$frame = $this->createFrame()->begin("");

$templateData = array(
	'TEMPLATE_THEME' => $this->GetFolder().'/themes/'.$arParams['TEMPLATE_THEME'].'/style.css',
	'TEMPLATE_CLASS' => 'bx_'.$arParams['TEMPLATE_THEME']
);

$injectId = $arParams['UNIQ_COMPONENT_ID'];

if (isset($arResult['REQUEST_ITEMS']))
{
	// code to receive recommendations from the cloud
	CJSCore::Init(array('ajax'));

	// component parameters
	$signer = new \Bitrix\Main\Security\Sign\Signer;
	$signedParameters = $signer->sign(
		base64_encode(serialize($arResult['_ORIGINAL_PARAMS'])),
		'bx.bd.products.recommendation'
	);
	$signedTemplate = $signer->sign($arResult['RCM_TEMPLATE'], 'bx.bd.products.recommendation');

	?>

	<div id="<?=$injectId?>"></div>

	<script type="text/javascript">
		BX.ready(function(){
			bx_rcm_get_from_cloud(
				'<?=CUtil::JSEscape($injectId)?>',
				<?=CUtil::PhpToJSObject($arResult['RCM_PARAMS'])?>,
				{
					'parameters':'<?=CUtil::JSEscape($signedParameters)?>',
					'template': '<?=CUtil::JSEscape($signedTemplate)?>',
					'site_id': '<?=CUtil::JSEscape(SITE_ID)?>',
					'rcm': 'yes'
				}
			);
		});
	</script>
	<?
	$frame->end();
	return;

	// \ end of the code to receive recommendations from the cloud
}?>

<?if (!empty($arResult['ITEMS'])):?>
	<div id="<?=$injectId?>_items">
		<div class="tab item" id="fcBigData">
			<div id="bigDataCarousel">
				<div class="wrap">
					<ul class="slideBox productList">
						<?foreach ($arResult["ITEMS"] as $key => $arElement):?>
							<li>
								<?$APPLICATION->IncludeComponent(
									"dresscode:catalog.item", 
									"short", 
									array(
										"CACHE_TIME" => $arParams["CACHE_TIME"],
										"CACHE_TYPE" => $arParams["CACHE_TYPE"],
										"HIDE_MEASURES" => $arParams["HIDE_MEASURES"],
										"HIDE_NOT_AVAILABLE" => $arParams["HIDE_NOT_AVAILABLE"],
										"IBLOCK_ID" => $arParams["IBLOCK_ID"],
										"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
										"PRODUCT_ID" => $arElement["ID"],
										"PICTURE_HEIGHT" => "",
										"PICTURE_WIDTH" => "",
										"PRODUCT_PRICE_CODE" => $arParams["PRICE_CODE"],
										"CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
										"CURRENCY_ID" => $arParams["CURRENCY_ID"]
									),
									false,
									array("HIDE_ICONS" => "Y")
								);?>
							</li>
						<?endforeach;?>
					</ul>
					<a href="#" class="bigDataBtnLeft"></a>
					<a href="#" class="bigDataBtnRight"></a>
				</div>
			</div>
		</div>
		<script>
			$("#bigDataCarousel").dwCarousel({
				leftButton: ".bigDataBtnLeft",
				rightButton: ".bigDataBtnRight",
				countElement: 5,
				resizeElement: true,
				resizeAutoParams: {
					1920: 5,
					1700: 5,
					1500: 4,
					1200: 3,
					850: 2
				}
			});	

			$(function(){
				dwLoadBigData("<?=GetMessage("CVP_TPL_MESS_RCM")?>", "<?=$injectId?>")
			});

		</script>
	</div>
<?endif;?>
