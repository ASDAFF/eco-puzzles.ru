<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<?error_reporting(0);?>

<?
	if(!empty($_GET["params"]) && isset($_GET["page"])){

		$arParams = json_decode($_GET["params"], true);

		if(!empty($arParams)){

			$arParams["AJAX"] = Y;
			$arParams["PAGE"] = intval($_GET["page"]);

			$APPLICATION->IncludeComponent(
				"dresscode:brands.list", 
				".default", 
				$arParams,
				false
			);
		}else{
			exit("arParams: json_decode false, please check charset (utf8 only)");
		}
	}

?>