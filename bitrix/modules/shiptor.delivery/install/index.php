<?
use Bitrix\Main\Context,
    Bitrix\Main\Loader,
    Bitrix\Main\ModuleManager,
    Bitrix\Main\Config\Option,
    Bitrix\Sale\Internals\PersonTypeTable,
    Bitrix\Sale\Internals\OrderPropsTable,
    Bitrix\Sale\Internals\OrderPropsGroupTable,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\EventManager,
    Bitrix\Sale\Delivery\Services\Table as DST,
    Shiptor\Delivery\ShiptorHandler;

Loc::loadMessages(__FILE__);

if (class_exists("shiptor_delivery"))
    return;

Class shiptor_delivery extends CModule {
    const MODULE_ID = 'shiptor.delivery';
    var $MODULE_ID = 'shiptor.delivery';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;
    var $MODULE_GROUP_RIGHTS = "Y";
    var $strError = '';
    var $arHandlers = array("sale" => array(
            "onSaleDeliveryHandlersClassNamesBuildList" => array('CShiptorDeliveryHandler', 'addCustomDeliveryServices'),
            "OnSaleOrderBeforeSaved" => array('CShiptorDeliveryHandler', 'saveInNewOrderMethodPVZ'),
            "OnSaleDeliveryServiceCalculate" => array('CShiptorDeliveryHandler', 'onDeliveryServiceCalculate'),
            "OnBeforeSaleShipmentSetField" => array('CShiptorDeliveryHandler', 'sendOrderToShiptor'),
            "OnSaleComponentOrderShowAjaxAnswer" => array('CShiptorDeliveryHandler', 'showAjaxAnswer'),
            "OnSaleComponentOrderCreated" => array('CShiptorDeliveryHandler', 'showCreateAnswer'),
            "onSaleDeliveryRestrictionsClassNamesBuildList" => array("CShiptorDeliveryHandler", "addCustomRestrictions"),
            "onSaleDeliveryExtraServicesClassNamesBuildList" => array("CShiptorDeliveryHandler", "addCustomExtraServices"),
        ),
        "main" => array(
            "OnEpilog" => array("CShiptorDeliveryHandler", "onEpilog")
        ));
    function __construct() {
        $arModuleVersion = array();
        include(dirname(__FILE__) . "/version.php");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = GetMessage("SHIPTOR_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("SHIPTOR_MODULE_DESC");

        $this->PARTNER_NAME = GetMessage("SHIPTOR_PARTNER_NAME");
        $this->PARTNER_URI = GetMessage("SHIPTOR_PARTNER_URI");
        $this->MODULE_CSS = "/bitrix/modules/" . $this->MODULE_ID . "/shiptor_admin.css";
    }
    function InstallDB() {
        if (!Loader::includeModule('sale')){
            return false;
        }
        $arSelect = array("ID");
        $arOrder = array("SORT" => "ASC");
        $arFilter = array("ACTIVE" => "Y");
        $dbPersonTypes = PersonTypeTable::getList(array("select" => $arSelect, "order" => $arOrder, "filter" => $arFilter));
        while($arPersonType = $dbPersonTypes->fetch()){
            $dbPropGroup = OrderPropsGroupTable::getList(array("filter" => array("PERSON_TYPE_ID" => $arPersonType["ID"]),"select" => array("ID")));
            $dbPropGroup->fetch();
            $arPropGroup = $dbPropGroup->fetch();
            $arSelect = array("ID");
            $arFilter = array("ACTIVE" => "Y", "PERSON_TYPE_ID" => $arPersonType["ID"], "CODE" => "PVZ_ID");
            $arPvzProp = OrderPropsTable::getList(array("select" => $arSelect, "filter" => $arFilter))->fetch();
            if (!$arPvzProp){
                $arPvzProp = array(
                    'PERSON_TYPE_ID' => $arPersonType["ID"],
                    'NAME' => Loc::getMessage('SHIPTOR_PVZ_NAME'),
                    'CODE' => 'PVZ_ID',
                    'TYPE' => 'STRING',
                    'REQUIRED' => 'N',
                    'USER_PROPS' => 'N',
                    'IS_LOCATION' => 'N',
                    'PROPS_GROUP_ID' => $arPropGroup["ID"],
                    'IS_EMAIL' => 'N',
                    'IS_PROFILE_NAME' => 'N',
                    'IS_PAYER' => 'N',
                    'IS_FILTERED' => 'Y',
                    'IS_ZIP' => 'N',
                    'UTIL' => 'Y'
                );
                $oPvzProp = OrderPropsTable::add($arPvzProp);
                $arPvzProp["ID"] = $oPvzProp->getID();
            }
            Option::set($this->MODULE_ID, 'idOrderPropPVZ_'.$arPersonType["ID"], $arPvzProp["ID"]);
        }
        return true;
    }
    function UnInstallDB() {
        return true;
    }
    function InstallEvents() {
        $eventManager = EventManager::getInstance();
        foreach($this->arHandlers as $moduleTo => $arEvents){
            foreach($arEvents as $eventName => $eventValues){
                $className = $eventValues[0];
                $funcName = $eventValues[1];
                $eventManager->registerEventHandler($moduleTo,$eventName,$this->MODULE_ID,$className,$funcName);
            }
        }
        return true;
    }
    function UnInstallEvents() {
        COption::RemoveOption($this->MODULE_ID);
        $eventManager = EventManager::getInstance();
        foreach($this->arHandlers as $moduleTo => $arEvents){
            foreach($arEvents as $eventName => $eventValues){
                $className = $eventValues[0];
                $funcName = $eventValues[1];
                $eventManager->unRegisterEventHandler($moduleTo,$eventName,$this->MODULE_ID,$className,$funcName);
            }
        }
        return true;
    }
    function InstallFiles() {
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/css", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/css/" . $this->MODULE_ID, true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/js", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/js/" . $this->MODULE_ID, true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/tools", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/tools/" . $this->MODULE_ID, true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/images", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/images/" . $this->MODULE_ID, true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/themes", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes", true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/gadgets", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/gadgets/" . $this->MODULE_ID, true, true);

        if (is_dir($p = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/admin')) {
            if ($dir = opendir($p)) {
                while (false !== $item = readdir($dir)) {
                    if ($item == '..' || $item == '.' || $item == 'menu.php')
                        continue;
                    file_put_contents($file = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/' . $this->MODULE_ID . '_' . $item, '<' . '? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/' . $this->MODULE_ID . '/admin/' . $item . '");?' . '>');
                }
                closedir($dir);
            }
        }
        return true;
    }
    function UnInstallFiles() {
        DeleteDirFilesEx("/bitrix/css/" . $this->MODULE_ID . "/");
        DeleteDirFilesEx("/bitrix/js/" . $this->MODULE_ID . "/");
        DeleteDirFilesEx("/bitrix/tools/" . $this->MODULE_ID . "/");
        DeleteDirFilesEx("/bitrix/images/" . $this->MODULE_ID . "/");
        DeleteDirFilesEx("/bitrix/gadgets/" . $this->MODULE_ID . "/");
        DeleteDirFilesEx("/bitrix/themes/.default/icons/" . $this->MODULE_ID . "/"); //icons
        DeleteDirFilesEx("/bitrix/themes/.default/start_menu/" . $this->MODULE_ID . "/"); //start_menu

        if (is_dir($p = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/admin')) {
            if ($dir = opendir($p)) {
                while (false !== $item = readdir($dir)) {
                    if ($item == '..' || $item == '.'){
                        continue;
                    }
                    unlink($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/' . $this->MODULE_ID . '_' . $item);
                }
                closedir($dir);
            }
        }
        return true;
    }
    function DoInstall() {
        global $APPLICATION;
        $request = Context::getCurrent()->getRequest();
        $step = intval($request["step"]);
        CModule::IncludeModule('sale');
        include_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.$this->MODULE_ID.'/lib/include/helper.php');
        include_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.$this->MODULE_ID.'/lib/ShiptorHandler.php');
        switch ($step){
            default:
                $this->InstallFiles();
                $this->InstallDB();
            case 1:
                $path = $_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.$this->MODULE_ID.'/install/step1.php';
                $APPLICATION->IncludeAdminFile(GetMessage("WSD_STEP1_TITLE"), $path);
                break;
            case 2:
                $arParams = [
                    "filter" => ["CLASS_NAME" => "%ShiptorHandler", 'ACTIVE' => 'Y'],
                    "select" => ["CONFIG", "ID", 'CURRENCY']
                ];
                if($result = DST::getList($arParams)->fetch()){
                    $parentId = $result["ID"];
                    $_SESSION['_SHIPTOR'] = $result["CONFIG"];
                }else{
                    $_SESSION['_SHIPTOR'] = ShiptorHandler::getDefaultConfigValues();
                }
                $_SESSION['_SHIPTOR']['API_KEY'] = $request['shd_api_key'];
                $path = $_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.$this->MODULE_ID.'/install/step2.php';
                $APPLICATION->IncludeAdminFile(GetMessage("WSD_STEP2_TITLE"), $path);
                break;
            case 3:
                ModuleManager::registerModule($this->MODULE_ID);
                $this->InstallEvents();
                Option::set($this->MODULE_ID,'shiptor_adminApiKey', $_SESSION['_SHIPTOR']['API_KEY']);
                Option::set($this->MODULE_ID,'shiptor_address_type', $request['shd_address_type']);
                foreach($request['shd_pvz_prop'] as $personId => $value){
                    Option::set($this->MODULE_ID,'shiptor_pvz_prop_'.$personId, $value);
                    if($request['shd_address_type'] == "complex"){
                        Option::set($this->MODULE_ID,'shiptor_street_prop_id_'.$personId, $request['shd_street_prop'][$personId]);
                        Option::set($this->MODULE_ID,'shiptor_bld_prop_id_'.$personId, $request['shd_bld_prop'][$personId]);
                        Option::set($this->MODULE_ID,'shiptor_corp_prop_id_'.$personId, $request['shd_corp_prop'][$personId]);
                        Option::set($this->MODULE_ID,'shiptor_flat_prop_id_'.$personId, $request['shd_flat_prop'][$personId]);
                    }else{
                        Option::set($this->MODULE_ID,'shiptor_address_prop_id_'.$personId, $request['shd_address_prop'][$personId]);
                    }
                }
                $_SESSION['_SHIPTOR']['LENGTH_VALUE'] = $request["shd_length"];
                $_SESSION['_SHIPTOR']['WIDTH_VALUE'] = $request["shd_width"];
                $_SESSION['_SHIPTOR']['HEIGHT_VALUE'] = $request["shd_height"];
                $_SESSION['_SHIPTOR']['WEIGHT_VALUE'] = $request["shd_weight"];
                $_SESSION['_SHIPTOR']['CALC_ALGORITM'] = $request["shd_calc"];
                $path = $_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.$this->MODULE_ID.'/install/step3.php';
                $this->createShiptorDeliveryIfNone();
                $APPLICATION->IncludeAdminFile(GetMessage("WSD_FINALSTEP_TITLE"), $path);
                unset($_SESSION['_SHIPTOR']);
                break;
            case 4:
                
        }
    }
    function DoUninstall() {
        $this->UnInstallDB();
        $this->UnInstallFiles();
        $this->UnInstallEvents();
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }
    private function createShiptorDeliveryIfNone(){
        include_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.$this->MODULE_ID.'/lib/ShiptorHandler.php');
        include_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.$this->MODULE_ID.'/lib/CShiptorAPI.php');
        include_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.$this->MODULE_ID.'/lib/options/Config.php');
        $arParams = [
            "filter" => ["CLASS_NAME" => "%ShiptorHandler", 'ACTIVE' => 'Y'],
            "select" => ["CONFIG", "ID", 'CURRENCY']
        ];
        if($result = DST::getList($arParams)->fetch()){
            $parentId = $result["ID"];
            $arConfig = $result["CONFIG"];
        }else{
            $arFields = array("PARENT_ID" => 0, 'NAME' => ShiptorHandler::getClassTitle(),
                'DESCRIPTION' => ShiptorHandler::getClassDescription(), 'ACTIVE' => 'Y',
                "CLASS_NAME" => '\Shiptor\Delivery\ShiptorHandler', 'CURRENCY' => 'RUB',
                'ALLOW_EDIT_SHIPMENT' => 'Y', 'CONFIG' => 'Y', 'SORT' => 100);
            $arFields['CONFIG'] = ShiptorHandler::getDefaultConfigValues();
            $arConfig = $arFields['CONFIG'];
            $result = DST::add($arFields);
            if($result->isSuccess()){
                $parentId = $result->getId();
                ShiptorHandler::createAllProfiles($parentId, 'RUB');
            }
        }
        if(!empty($parentId)){
            $_SESSION['_SHIPTOR']["PARENT_ID"] = $parentId;
            $arConfig['MAIN']['LENGTH_VALUE'] = $_SESSION['_SHIPTOR']['LENGTH_VALUE'];
            $arConfig['MAIN']['WIDTH_VALUE'] = $_SESSION['_SHIPTOR']['WIDTH_VALUE'];
            $arConfig['MAIN']['HEIGHT_VALUE'] = $_SESSION['_SHIPTOR']['HEIGHT_VALUE'];
            $arConfig['MAIN']['WEIGHT_VALUE'] = $_SESSION['_SHIPTOR']['WEIGHT_VALUE'];
            $arConfig['MAIN']['CALC_ALGORITM'] = $_SESSION['_SHIPTOR']['CALC_ALGORITM'];
            DST::update($parentId,array("CONFIG" => $arConfig));
        }
    }
}