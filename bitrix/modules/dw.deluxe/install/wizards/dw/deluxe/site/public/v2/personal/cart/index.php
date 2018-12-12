<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Корзина");
?><h1>Корзина</h1><?$APPLICATION->IncludeComponent("dresscode:sale.basket.basket", "standartOrder", array(
		"HIDE_MEASURES" => "N",
		"BASKET_PICTURE_WIDTH" => "220",
		"BASKET_PICTURE_HEIGHT" => "200",
		"HIDE_NOT_AVAILABLE" => "N",
		"PRODUCT_PRICE_CODE" => array(
		),
		"GIFT_CONVERT_CURRENCY" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000"
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>