<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Оформить заказ");?><h1>Оформление заказа</h1><?global $USER;
if ($USER->IsAuthorized()):?>
	<div class="personal-order-info">*Нажимая на кнопку оформить заказ, я даю согласие на <a href="/personal-info/" class="pilink">обработку персональных данных.</a><br /></div>
<?endif?>
<?$APPLICATION->IncludeComponent(
	"bitrix:sale.order.ajax", 
	".default", 
	array(
		"COMPONENT_TEMPLATE" => ".default",
		"PAY_FROM_ACCOUNT" => "Y",
		"ONLY_FULL_PAY_FROM_ACCOUNT" => "N",
		"COUNT_DELIVERY_TAX" => "N",
		"ALLOW_AUTO_REGISTER" => "Y",
		"SEND_NEW_USER_NOTIFY" => "Y",
		"DELIVERY_NO_AJAX" => "Y",
		"DELIVERY_NO_SESSION" => "N",
		"TEMPLATE_LOCATION" => ".default",
		"DELIVERY_TO_PAYSYSTEM" => "d2p",
		"USE_PREPAYMENT" => "N",
		"PROP_1" => array(
		),
		"PROP_2" => array(
		),
		"ALLOW_NEW_PROFILE" => "Y",
		"SHOW_PAYMENT_SERVICES_NAMES" => "Y",
		"SHOW_STORES_IMAGES" => "N",
		"PATH_TO_BASKET" => "#SITE_DIR#personal/cart/",
		"PATH_TO_PERSONAL" => "#SITE_DIR#personal/",
		"PATH_TO_PAYMENT" => "#SITE_DIR#personal/cart/payment/",
		"PATH_TO_AUTH" => "#SITE_DIR#auth/",
		"SET_TITLE" => "Y",
		"DISABLE_BASKET_REDIRECT" => "N",
		"PRODUCT_COLUMNS" => array(
		)
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>