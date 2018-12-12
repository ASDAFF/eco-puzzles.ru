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
    "parent_menu" => "global_menu_marketing", // �������� � ������ "���������"
    "sort"        => 100,                    // ��� ������ ����
    "url"         => "",  // ������ �� ������ ����
    "text"        => GetMessage("skyweb24.popuppro_MENU_MAIN"),       // ����� ������ ����
    "title"       => GetMessage("skyweb24.popuppro_MENU_MAIN_TITLE"), // ����� ����������� ���������
    "icon"        => "skwb24_popuppro_menu_icon", // ����� ������
   // "page_icon"   => "skwb24_refsales_page_icon", // ������� ������
    "items_id"    => "skyweb24_popuppro",  // ������������� �����
    "items"       => array(// ��������� ������ ���� ���������� ����.
		array(
			"url"         => "skyweb24_popuppro.php?lang=".LANGUAGE_ID,  // ������ �� ������ ����
			"title"       => $title, // ����� ����������� ���������
			 "text"        => $text,       // ����� ������ ����
		),
		array(
			"url"         => "/bitrix/admin/settings.php?lang=".LANGUAGE_ID."&mid=skyweb24.popuppro",  // ������ �� ������ ����
			"title"       => GetMessage("skyweb24.popuppro_MENU_MAIN_SETTING"), // ����� ����������� ���������
			"text"        => GetMessage("skyweb24.popuppro_MENU_MAIN_SETTING_TITLE"),       // ����� ������ ����
		)
	)   
  );

return $aMenu;
?>