<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<?error_reporting(0);?>

<?
	if(!empty($_GET["params"]) && !empty($_GET["pager_num"])){
		$arParams = \Bitrix\Main\Web\Json::decode($_GET["params"], true);

		if(!empty($arParams)){

			$arParams["FROM_AJAX"] = "Y";
			$arParams["PAGER_NUM"] = intval($_GET["pager_num"]);

			$APPLICATION->IncludeComponent(
				"dresscode:catalog.product.offers", 
				".default", 
				$arParams,
				false
			);
		}else{
			exit("arParams: json_decode false, please check charset (utf8 only)");
		}
	}

?>