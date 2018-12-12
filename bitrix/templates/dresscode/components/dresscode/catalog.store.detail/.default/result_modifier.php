<?
	$APPLICATION->AddChainItem($arResult["TITLE"], "/stores/".$arResult["ID"]."/");
	$APPLICATION->SetPageProperty("title", $arResult["TITLE"]);
	$APPLICATION->SetTitle($arResult["TITLE"]);
?>