<?
include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/urlrewrite.php');
CHTTP::SetStatus("404 Not Found");
@define("ERROR_404", "Y");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Страница не найдена");
?>
<div id="error404">
	<div class="wrapper">
		<a href="<?=SITE_DIR?>" class="errorPic"><img src="<?=SITE_TEMPLATE_PATH?>/images/404.jpg"></a>
		<h1>Такой страницы не существует</h1>
		<div class="errorText">начните поиск с <a href="<?=SITE_DIR?>">главной страницы</a> или выберите нужный товар в <a href="<?=SITE_DIR?>catalog/">каталоге</a>:</div>
	</div>
	<div id="empty">
		<div class="wrapper">
			<?$APPLICATION->IncludeComponent("bitrix:menu", "emptyMenu", Array(
				"ROOT_MENU_TYPE" => "left",
					"MENU_CACHE_TYPE" => "N",
					"MENU_CACHE_TIME" => "3600",
					"MENU_CACHE_USE_GROUPS" => "Y",
					"MENU_CACHE_GET_VARS" => "",
					"MAX_LEVEL" => "1",
					"CHILD_MENU_TYPE" => "left",
					"USE_EXT" => "Y",
					"DELAY" => "N",
					"ALLOW_MULTI_SELECT" => "N",
				),
				false
			);?>
		</div>
	</div>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>