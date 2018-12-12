<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
use \Bitrix\Main\Application,
	Bitrix\Main\Request,
	Bitrix\Main\Localization\Loc;
	Loc::loadMessages(__FILE__);


$context = Application::getInstance()->getContext();
$request = $context->getRequest();
$title = GetMessage("skyweb24.popuppro_MENU_MAIN_LIST_TITLE");
$text = GetMessage("skyweb24.popuppro_MENU_MAIN_LIST");
if(!empty($request['id'])){
	$title = GetMessage("skyweb24.popuppro_MENU_MAIN_DETAIL_TITLE");
	$text = GetMessage("skyweb24.popuppro_MENU_MAIN_DETAIL");	
}

//IncludeModuleLangFile(__FILE__, LANGUAGE_ID);

  $aMenu = array(
    "parent_menu" => "global_menu_marketing", // поместим в раздел "Маркетинг"
    "sort"        => 100,                    // вес пункта меню
    "url"         => "",  // ссылка на пункте меню
    "text"        => GetMessage("skyweb24.popuppro_MENU_MAIN"),       // текст пункта меню
    "title"       => GetMessage("skyweb24.popuppro_MENU_MAIN_TITLE"), // текст всплывающей подсказки
    "icon"        => "skwb24_popuppro_menu_icon", // малая иконка
   // "page_icon"   => "skwb24_refsales_page_icon", // большая иконка
    "items_id"    => "skyweb24_popuppro",  // идентификатор ветви
    "items"       => array(// остальные уровни меню сформируем ниже.
		array(
			"url"         => "skyweb24_popuppro.php?lang=".LANGUAGE_ID,  // ссылка на пункте меню
			"title"       => $title, // текст всплывающей подсказки
			 "text"        => $text,       // текст пункта меню
		),
		array(
			"url"         => "/bitrix/admin/settings.php?lang=".LANGUAGE_ID."&mid=skyweb24.popuppro",  // ссылка на пункте меню
			"title"       => GetMessage("skyweb24.popuppro_MENU_MAIN_SETTING"), // текст всплывающей подсказки
			"text"        => GetMessage("skyweb24.popuppro_MENU_MAIN_SETTING_TITLE"),       // текст пункта меню
		)
	)   
  );

return $aMenu;
?>