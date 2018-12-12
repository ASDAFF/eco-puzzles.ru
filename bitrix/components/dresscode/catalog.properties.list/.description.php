<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$arComponentDescription = array(
   "NAME" => GetMessage("NAME"),
   "DESCRIPTION" => GetMessage("DESCRIPTION"),
   "ICON" => "/images/offers.gif",
   "PATH" => array(
      "ID" => "DRESSCODE",
      "CHILD" => array(
         "ID" => "catalogPropertiesList",
         "NAME" => GetMessage("NAME")
      )
   )
);
?>