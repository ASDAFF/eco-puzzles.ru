<?
global $MESS;
$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-strlen("/install/index.php"));
include(GetLangFileName($strPath2Lang."/lang/", "/install/index.php"));

Class dw_deluxe extends CModule
{
	var $MODULE_ID = "dw.deluxe";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	function dw_deluxe()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

		$this->MODULE_NAME = GetMessage("SCOM_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("SCOM_INSTALL_DESCRIPTION");
		$this->PARTNER_NAME = GetMessage("SPER_PARTNER");
		$this->PARTNER_URI = GetMessage("PARTNER_URI");
	}

	function InstallDB()
	{
		RegisterModule("dw.deluxe");
		return true;
	}

	function UnInstallDB($arParams = Array())
	{
		UnRegisterModule("dw.deluxe");
		return true;
	}

	function InstallFiles()
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/dw.deluxe/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/dw.deluxe/install/module", $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/dw.deluxe", true, true);
		return true;
	}

	function UnInstallFiles()
	{
		return true;
	}

	function DoInstall()
	{
		global $APPLICATION;
		$this->InstallFiles();
		RegisterModule("dw.deluxe");
		return true;
	}

	function DoUninstall()
	{
		global $APPLICATION, $step;
	    DeleteDirFilesEx("/bitrix/modules/dw.deluxe");
	    DeleteDirFilesEx("/bitrix/wizards/dw");
	    UnRegisterModule("dw.deluxe");
		return true;
	}
}
?>