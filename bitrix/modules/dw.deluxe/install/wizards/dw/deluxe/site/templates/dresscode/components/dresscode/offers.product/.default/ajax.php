<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<?error_reporting(0);?>

<?
	if(!empty($_GET["params"]) && !empty($_GET["groupID"]) && isset($_GET["page"])){

		$arParams = \Bitrix\Main\Web\Json::decode($_GET["params"], true);

		if(!empty($arParams)){

			$arParams["AJAX"] = "Y";
			$arParams["GROUP_ID"] = $_GET["groupID"]; // all or int 0-9
			$arParams["PAGE"] = intval($_GET["page"]);

			$APPLICATION->IncludeComponent(
				"dresscode:offers.product", 
				".default", 
				$arParams,
				false
			);
		}else{
			exit("arParams: json_decode false, please check charset (utf8 only)");
		}
	}

?>