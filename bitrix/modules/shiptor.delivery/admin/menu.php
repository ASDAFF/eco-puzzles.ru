<?
IncludeModuleLangFile(__FILE__);
if($APPLICATION->GetGroupRight("shiptor.delivery")!="D"){
    $aMenu = array(
            "parent_menu" => "global_menu_store",
            "section" => "shiptor",
            "sort" => 101,
            "text" => GetMessage("SHIPTOR_TITLE"),
            "title" => GetMessage("SHIPTOR_TITLE"),
            "icon" => "shiptor_menu_icon",
            "page_icon" => "shiptor_page_icon",
            "items_id" => "menu_shiptor",
            "url" => "shiptor.delivery_unload.php?lang=".LANG,
    );
    return $aMenu;
}
return false;