<?
global $MESS;
$strPath2Lang = str_replace('\\', '/', __FILE__);

$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-strlen("/install/index.php"));
include(GetLangFileName($strPath2Lang."/lang/", "/install/index.php"));
include($strPath2Lang."/install/version.php");

Class webfly_ymarket extends CModule																								// <------------ HERE------------- CLASS NAME MUST BE CHANGED
{
        var $MODULE_ID = 'webfly.ymarket';																							
        var $MODULE_VERSION;
        var $MODULE_VERSION_DATE;
        var $MODULE_NAME;
        var $MODULE_DESCRIPTION;
		
        function webfly_ymarket()																												// <------------ HERE------------- CONSTRUCTOR NAME MUST BE CHANGED
        {        		
                $arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->PARTNER_NAME = "webfly";
		$this->PARTNER_URI = "http://www.webfly.pro/";
		$this->MODULE_NAME = GetMessage("YMARKET_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("YMARKET_MODULE_DESC");

		$this->PARTNER_NAME = GetMessage("YMARKET_PARTNER_NAME");
		$this->PARTNER_URI = GetMessage("YMARKET_PARTNER_URI");

		return true;
        }

		
        function DoInstall(){		
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/webfly.ymarket/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/webfly.ymarket/install/public_html", $_SERVER["DOCUMENT_ROOT"]."/", true, true);    
		RegisterModule($this->MODULE_ID);
	}

        function DoUninstall(){
            DeleteDirFilesEx("/bitrix/components/webfly/yandex.market");
            DeleteDirFilesEx("/y-market");
            UnRegisterModule($this->MODULE_ID);
	}	
}
?>