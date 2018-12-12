<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
	if($arResult["isFormErrors"] == "Y"){
		if(!empty($arResult["FORM_ERRORS"])){
			$arReturn["ERROR"] = $arResult["FORM_ERRORS"];
		}

		$arReturn["CAPTCHA"] = array(
			"CODE" => htmlspecialcharsbx($arResult["CAPTCHACode"]),
			"PICTURE" => "/bitrix/tools/captcha.php?captcha_sid=".htmlspecialcharsbx($arResult["CAPTCHACode"])
		);
	}else{
		$arReturn["SUCCESS"] = "Y";
	}

	echo \Bitrix\Main\Web\Json::encode($arReturn);

?>