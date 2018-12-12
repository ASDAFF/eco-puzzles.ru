<?
use Bitrix\Main\Context,
    Bitrix\Main\Type,
    Bitrix\Main\Loader,
    Bitrix\Main\Config\Option,
    Bitrix\Main\Localization\Loc,
    Bitrix\Sale\Internals\ShipmentTable,
    Bitrix\Main\HttpApplication,
    Shiptor\Delivery\CShiptorAPI,
    Shiptor\Delivery\Options\Config,
    Shiptor\Delivery\Logger;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

CJSCore::Init(array('ajax'));

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if($saleModulePermissions < "U"){
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}
Loader::includeModule('sale');
Loader::includeModule('currency');
Loader::IncludeModule("shiptor.delivery");

Loc::loadMessages(__FILE__);
Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/sale/admin/order_shipment.php');

//--------------------------------------------
$oShiptorAPI = CShiptorAPI::getInstance();
$sTokenString = $oShiptorAPI->getTokenString();
//--------------------------------------------
global $DB;

$request = HttpApplication::getInstance()->getContext()->getRequest();
$bCreateAgent = (bool)($request["mode"] === "createAgent" && $request->isPost());

if($bCreateAgent){
    $agentId = CShiptorDeliveryHelper::createAgentIfNone();
    die('/bitrix/admin/agent_edit.php?ID='.$agentId.'&lang=ru');
}

$arKladrAgent = $DB->Query("select ID, ACTIVE from b_agent where NAME = 'CShiptorDeliveryHelper::clearKladr();'")->Fetch();
if(!$arKladrAgent){
    $date2Run = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")),strtotime("+1day 01:40:00"));
    CAgent::AddAgent("CShiptorDeliveryHelper::clearKladr();", "shiptor.delivery", "N", CShiptorDeliveryHelper::MONTH, $date2Run, "Y", $date2Run, 100);
}

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/sale/prolog.php");

$moduleConfig = new Config();

$oContext = Context::getCurrent();
$tableId = "shiptor_shipment_upload";
$curPage = $oContext->getRequest()->getRequestUri();
$lang = $oContext->getLanguage();
$siteId = $oContext->getSite();
$errors = '';
$sAdmin = new CAdminSorting($tableId,"ORDER_ID","DESC");
$lAdmin = new CAdminList($tableId,$sAdmin);

$filter = array(
    'filter_order_id_from',
    'filter_order_id_to',
    'filter_shipment_id_from',
    'filter_shipment_id_to',
    'filter_direct',
    'filter_price_delivery_from',
    'filter_price_delivery_to',
    'filter_delivery_doc_num',
    'filter_account_num',
    'filter_shiptor_status',
    'filter_user_id',
    'filter_user_login',
    'filter_user_email'
);

$lAdmin->InitFilter($filter);

$arFilter = array();

$filter_order_id_from = intval($filter_order_id_from);
$filter_order_id_to = intval($filter_order_id_to);

if(intval($filter_price_delivery_from) > 0)
    $arFilter['>=PRICE_DELIVERY'] = $filter_price_delivery_from;
if(intval($filter_price_delivery_to) > 0)
    $arFilter['<=PRICE_DELIVERY'] = $filter_price_delivery_to;

if(strlen($filter_delivery_doc_num) > 0)
    $arFilter['DELIVERY_DOC_NUM'] = $filter_deducted;

if($filter_order_id_from > 0)
    $arFilter['>=ORDER_ID'] = $filter_order_id_from;
if($filter_order_id_to > 0)
    $arFilter['<=ORDER_ID'] = $filter_order_id_to;

if($filter_shipment_id_from > 0)
    $arFilter['>=ID'] = $filter_shipment_id_from;
if($filter_shipment_id_to > 0)
    $arFilter['<=ID'] = $filter_shipment_id_to;

if(strlen($filter_account_num) > 0)
    $arFilter['ORDER.ACCOUNT_NUMBER'] = $filter_account_num;

if(strlen($filter_shiptor_status) > 0){
    $arFilter['TRACKING_DESCRIPTION'] = $filter_shiptor_status;
}
if(strlen($filter_user_login) > 0)
    $arFilter["ORDER.USER.LOGIN"] = trim($filter_user_login);
if(strlen($filter_user_email) > 0)
    $arFilter["ORDER.USER.EMAIL"] = trim($filter_user_email);

if(IntVal($filter_user_id) > 0)
    $arFilter["ORDER.USER_ID"] = IntVal($filter_user_id);
if(intval($filter_direct) > 0){
    $arShiptorIds = \CShiptorDeliveryHelper::getDirectDeliveries();
}else{
    $arShiptorIds = \CShiptorDeliveryHelper::getCommonDeliveries();
}
//$arShiptorIds = \CShiptorDeliveryHelper::getDeliveries();

if($arID = $lAdmin->GroupAction()){
    $shipments = array();
    $select = array('ID','ORDER_ID');
    $shipmentfilter['=STATUS.Bitrix\Sale\Internals\StatusLangTable:STATUS.LID'] = $lang;
    $shipmentfilter['DELIVERY_ID'] = $arShiptorIds;
    /*
      $shipmentfilter['=ALLOW_DELIVERY'] = 'Y';
      $shipmentfilter['=DEDUCTED'] = 'Y';
     */
    $shipmentfilter['=SYSTEM'] = 'N';

//	if($_REQUEST['shipment_id'] > 0) $shipmentfilter['ID'] = $_REQUEST['shipment_id'];
    if($request['action_target'] != 'selected')
        $shipmentfilter['ID'] = $request['ID'];

    $params = array(
        'select' => $select,
        'filter' => $shipmentfilter,
        'limit' => 1000
    );

    $result = ShipmentTable::getList($params);

    while($arResult = $result->fetch()){
        if( ! isset($shipments[$arResult['ORDER_ID']]))
            $shipments[$arResult['ORDER_ID']] = array();
        $shipments[$arResult['ORDER_ID']][] = $arResult['ID'];
    }
    if($filter_direct){
        $arDirectPackages = array();
        $arShipments = array();
    }
    if($request['action'] == 'fullfill' && Config::isAutomaticUpload()){
        $departureTypeSwitched = true;
        $moduleConfig->saveParam('departure_type',Config::DEPARTURE_TYPE_MAN);
    }
    foreach($shipments as $orderId => $ids){
        $isDeleted = false;
        /** @var \Bitrix\Sale\Order $currentOrder */
        $currentOrder = \Bitrix\Sale\Order::load($orderId);
        if( ! $currentOrder)
            continue;

        /** @var \Bitrix\Sale\ShipmentCollection $shipmentCollection */
        $shipmentCollection = $currentOrder->getShipmentCollection();

        $sStatuses = Option::get("shiptor.delivery","shiptor_tracking_map_statuses");
        $arStatuses = unserialize($sStatuses);
        $arStatuses = array_flip($arStatuses);

        foreach($ids as $id){
            if(strlen($id) <= 0)
                continue;

            /** @var \Bitrix\Sale\Shipment $shipment */
            $shipment = $shipmentCollection->getItemById($id);
            if( ! $shipment)
                continue;
            $sShipmentAccountNumber = $currentOrder->getField('ACCOUNT_NUMBER');
            switch($request['action']){
                case "update":
                    @set_time_limit(0);
                    if($shipment->getField('DEDUCTED') == "N"){
                        $lAdmin->AddGroupError(Loc::getMessage("SHIPTOR_ERROR_STATUS",array("#ID#" => $id,"#SHIPMENT_NUM#" => $sShipmentAccountNumber)));
                    }elseif($shipment->getField('DELIVERY_DOC_NUM') > 1000){
                        $arParams = array("id" => $shipment->getField('DELIVERY_DOC_NUM'));
                        $arRequest = $oShiptorAPI->Request("getPackage",$arParams);
                        if($arRequest['result']['id'] == $shipment->getField('DELIVERY_DOC_NUM')){
                            $shipment->setField('TRACKING_DESCRIPTION',$arRequest['result']['status']);
                            if( ! $shipment->getField('TRACKING_NUMBER')){
                                $shipment->setField('TRACKING_NUMBER','RP' . $arRequest['result']['id']);
                            }
                            if( ! $shipment->getField('DELIVERY_DOC_NUM')){
                                $shipment->setField('DELIVERY_DOC_NUM',$arRequest['result']['id']);
                            }
                            $shipment->setField('DELIVERY_DOC_DATE',Type\DateTime::createFromTimestamp(strtotime($arRequest['result']['history'][0]['date'])));
                            $status = $arStatuses[$arRequest['result']['status']];
                            if(!empty($status)){
                                $shipment->setField('STATUS_ID',$status);
                            }
                            //---------------------------------------------------------------
                            $res = $shipment->save();
                            if( ! $res->isSuccess())
                                $lAdmin->AddGroupError(implode('\n',$res->getErrorMessages()));
                        }
                        else{
                            $arReplaceErr = array("#ID#" => $id, "#SHIPMENT_NUM#" => $sShipmentAccountNumber, "#SHIPTOR_MESSAGE#" => $arRequest['error']['message']);
                            $lAdmin->AddGroupError(Loc::getMessage("SHIPTOR_ERROR_GET_STATUS",$arReplaceErr));
                        }
                    }else{
                        $lAdmin->AddGroupError(Loc::getMessage("SHIPTOR_ERROR_TRACK_NO",array("#ID#" => $id, "#SHIPMENT_NUM#" => $sShipmentAccountNumber)));
                    }
                    break;
                case "fullfill":
                    @set_time_limit(0);
                    if($shipment->getField('DELIVERY_DOC_NUM')){
                        $arReplaceErr = array("#ID#" => $id, "#SHIPMENT_NUM#" => $sShipmentAccountNumber, "#TRACK_NUM#" => $shipment->getField('DELIVERY_DOC_NUM'));
                        ShowNote(Loc::getMessage("SHIPTOR_ERROR_TRACK_ALREADY",$arReplaceErr));
                        continue;
                    }elseif($shipment->getField('DEDUCTED') == "Y"){
                        $arReplaceErr = array("#ID#" => $id, "#SHIPMENT_NUM#" => $sShipmentAccountNumber, "#TRACK_NUM#" => $shipment->getField('TRACKING_NUMBER'));
                        ShowNote(Loc::getMessage("SHIPTOR_ERROR_SENT_ALREADY",$arReplaceErr));
                        continue;
                    }elseif($shipment->getField('ALLOW_DELIVERY') == "N"){
                        $arReplaceErr = array("#ID#" => $id, "#SHIPMENT_NUM#" => $sShipmentAccountNumber);
                        ShowNote(Loc::getMessage("SHIPTOR_ERROR_SEND_NOT_ALLOWED",$arReplaceErr));
                        continue;
                    }else{
                        if($filter_direct){
                            /*$arPackage = CShiptorDeliveryHelper::getDirectOrderPackage($shipment);
                            if($arPackage["ERROR"]){
                                $shipment->setField("MARKED","Y");
                                $shipment->setField("REASON_MARKED",$arPackage["ERROR"]);
                                $res = $shipment->save();
                                $lAdmin->AddGroupError($arPackage["ERROR"]);
                            }else{*/
                                $arShipments[$sShipmentAccountNumber] = $shipment;
                                /*$arDirectPackages[$arPackage["DELIVERY_ID"]]["shipment"] = $arPackage["SHIPMENT"];
                                $arDirectPackages[$arPackage["DELIVERY_ID"]]["packages"][] = $arPackage["PACKAGE"];
                            }*/
                        }else{
                            try{
                                $result = CShiptorDeliveryHelper::sendOrder($shipment);
                                CAdminMessage::ShowNote($result);
                            }catch(\Exception $e){
                                $shipment->setField("MARKED","Y");
                                $shipment->setField("REASON_MARKED",$e->getMessage());
                                $res = $shipment->save();
                                $lAdmin->AddGroupError($e->getMessage());
                                Logger::exception($e);
                            }
                        }
                    }
                    break;
                case "remove":
                    if($shipment->getField('DELIVERY_DOC_NUM') && $shipment->getField('DEDUCTED') == "Y"){
                        $arRequest = $oShiptorAPI->Request("removePackage",array("external_id" => $sShipmentAccountNumber));
                        if(empty($arRequest["error"])){
                            $shipment->setField('DEDUCTED','N');
                            $shipment->setField('XML_ID',"");
                            $shipment->setField('DELIVERY_DOC_NUM',"");
                            $shipment->setField('DELIVERY_DOC_DATE',null);
                            $shipment->setField('TRACKING_NUMBER',"");
                            $shipment->setField('TRACKING_DESCRIPTION',$arRequest['result']['status']);
                            $res = $shipment->save();
                            CAdminMessage::ShowNote(Loc::getMessage("SHIPTOR_REMOVE_SUCCESS",array("#ORDER#" => $sShipmentAccountNumber)));
                        }else{
                            $arReplaceErr = array("#ORDER#" => $sShipmentAccountNumber, "#SHIPTOR_MESSAGE#" => $arRequest["error"]["message"]);
                            $lAdmin->AddGroupError(Loc::getMessage("SHIPTOR_REMOVE_ERROR", $arReplaceErr));
                        }
                    }else{
                        $lAdmin->AddGroupError(Loc::getMessage("SHIPTOR_ERROR_STATUS",array("#ID#" => $id,"#SHIPMENT_NUM#" => $sShipmentAccountNumber)));
                    }
                    break;
            }
        }
    }
    if($departureTypeSwitched){
        $moduleConfig->saveParam('departure_type',Config::DEPARTURE_TYPE_AUTO);
    }
    if(!empty($arShipments) && $filter_direct){
        try{
            $arDirectResult = CShiptorDeliveryHelper::sendDirectOrders($arShipments);
            if(!empty($arDirectResult["SUCCESS"])){
                CAdminMessage::ShowNote(Loc::getMessage("SHIPTOR_SEND_SUCCESS_MULTY", array("#ORDER_IDS#" => implode(",",$arDirectResult["SUCCESS"]))));
            }
            if(!empty($arDirectResult["ERROR"])){
                $lAdmin->AddGroupError(Loc::getMessage("SHIPTOR_SEND_ERROR_MULTY", array("#ORDER_IDS#" => implode(",",$arDirectResult["ERROR"]))));
            }
        } catch (\Exception $e) {
            $lAdmin->AddGroupError($e->getMessage());
            Logger::exception($e);
        }
    }
}

$headers = array(
    array("id" => "ID","content" => "ID","sort" => "ID","default" => true),
    array("id" => "ORDER_ID","content" => Loc::getMessage("SALE_ORDER_ID"),"sort" => "ORDER_ID","default" => true),
    array("id" => "ACCOUNT_NUMBER","content" => Loc::getMessage("SALE_ORDER_ACCOUNT_NUMBER"),"sort" => "ORDER.ACCOUNT_NUMBER","default" => false),
    array("id" => "ORDER_USER_NAME","content" => Loc::getMessage("SALE_ORDER_USER_NAME"),"sort" => "ORDER_USER_NAME","default" => true),
    array("id" => "STATUS","content" => Loc::getMessage("SALE_ORDER_STATUS"),"sort" => 'STATUS.ID',"default" => true),
    array("id" => "PRICE_DELIVERY","content" => Loc::getMessage("SALE_ORDER_PRICE_DELIVERY"),"sort" => "PRICE_DELIVERY","default" => true),
    array("id" => "DELIVERY_DOC_NUM","content" => Loc::getMessage("SALE_ORDER_DELIVERY_DOC_NUM"),"sort" => "DELIVERY_DOC_NUM","default" => true),
    array("id" => "DELIVERY_DOC_DATE","content" => Loc::getMessage("SALE_ORDER_DELIVERY_DOC_DATE"),"sort" => "DELIVERY_DOC_DATE","default" => true),
    array("id" => "RESPONSIBLE_BY","content" => Loc::getMessage("SALE_ORDER_DELIVERY_RESPONSIBLE_ID"),"sort" => "","default" => true),
    array("id" => "TRACKING_NUMBER","content" => Loc::getMessage("SALE_ORDER_TRACKING_NUMBER"),"sort" => "TRACKING_NUMBER","default" => false),
    array("id" => "TRACKING_DESCRIPTION","content" => Loc::getMessage("SHIPTOR_CARRIAGE_STATUS"),"sort" => "TRACKING_DESCRIPTION","default" => false),
    array("id" => "XML_ID","content" => "XML_ID","sort" => "XML_ID","default" => false),
    array("id" => "PARAMETERS","content" => Loc::getMessage("SALE_ORDER_PARAMETERS"),"default" => false),
    array("id" => "CANCELED","content" => Loc::getMessage("SALE_ORDER_CANCELED"),"sort" => "CANCELED","default" => false),
    array("id" => "REASON_CANCELED","content" => Loc::getMessage("SALE_ORDER_REASON_CANCELED"),"default" => false),
    array("id" => "MARKED","content" => Loc::getMessage("SHIPTOR_SALE_ORDER_MARKED"),"sort" => "MARKED","default" => false),
    array("id" => "REASON_MARKED","content" => Loc::getMessage("SALE_ORDER_REASON_MARKED_ID"),"default" => false),
);
$select = array(
    '*',
    'STATUS_NAME' => 'STATUS.Bitrix\Sale\Internals\StatusLangTable:STATUS.NAME',
    'ORDER.CURRENCY',
    'ORDER.ACCOUNT_NUMBER',
    'COMPANY_BY.NAME',
    'EMP_DEDUCTED_BY_NAME' => 'EMP_DEDUCTED_BY.NAME',
    'EMP_DEDUCTED_BY_LAST_NAME' => 'EMP_DEDUCTED_BY.LAST_NAME',
    'EMP_ALLOW_DELIVERY_BY_NAME' => 'EMP_ALLOW_DELIVERY_BY.NAME',
    'EMP_ALLOW_DELIVERY_BY_LAST_NAME' => 'EMP_ALLOW_DELIVERY_BY.LAST_NAME',
    'EMP_MARKED_BY_BY_NAME' => 'EMP_MARKED_BY.NAME',
    'EMP_MARKED_BY_LAST_NAME' => 'EMP_MARKED_BY.LAST_NAME',
    'ORDER_USER_NAME' => 'ORDER.USER.NAME',
    'ORDER_USER_LAST_NAME' => 'ORDER.USER.LAST_NAME',
    'ORDER_USER_ID' => 'ORDER.USER_ID',
    'RESPONSIBLE_BY_LAST_NAME' => 'RESPONSIBLE_BY.LAST_NAME',
    'RESPONSIBLE_BY_NAME' => 'RESPONSIBLE_BY.NAME'
);
$arFilter['=STATUS.Bitrix\Sale\Internals\StatusLangTable:STATUS.LID'] = $lang;
$arFilter['DELIVERY_ID'] = $arShiptorIds;
$arFilter['!=SYSTEM'] = 'Y';
$arFilter["CANCELED"] = 'N';

$params = array(
    'select' => $select,
    'filter' => $arFilter,
    'order' => array($by => $order),
);

$usePageNavigation = true;
$navyParams = array();

$navyParams = CDBResult::GetNavParams(CAdminResult::GetNavSize($tableId));
if($navyParams['SHOW_ALL']){
    $usePageNavigation = false;
}else{
    $navyParams['PAGEN'] = (int)$navyParams['PAGEN'];
    $navyParams['SIZEN'] = (int)$navyParams['SIZEN'];
}

if($usePageNavigation){
    $params['limit'] = $navyParams['SIZEN'];
    $params['offset'] = $navyParams['SIZEN'] * ($navyParams['PAGEN'] - 1);
}

$totalPages = 0;

if($usePageNavigation){
    $countQuery = new \Bitrix\Main\Entity\Query(ShipmentTable::getEntity());
    $countQuery->addSelect(new \Bitrix\Main\Entity\ExpressionField('CNT','COUNT(1)'));
    $countQuery->setFilter($params['filter']);
    $totalCount = $countQuery->setLimit(null)->setOffset(null)->exec()->fetch();
    unset($countQuery);
    $totalCount = (int)$totalCount['CNT'];

    if($totalCount > 0){
        $totalPages = ceil($totalCount / $navyParams['SIZEN']);

        if($navyParams['PAGEN'] > $totalPages)
            $navyParams['PAGEN'] = $totalPages;

        $params['limit'] = $navyParams['SIZEN'];
        $params['offset'] = $navyParams['SIZEN'] * ($navyParams['PAGEN'] - 1);
    }
    else{
        $navyParams['PAGEN'] = 1;
        $params['limit'] = $navyParams['SIZEN'];
        $params['offset'] = 0;
    }
}

$dbResultList = new CAdminResult(ShipmentTable::getList($params),$tableId);

if($usePageNavigation){
    $dbResultList->NavStart($params['limit'],$navyParams['SHOW_ALL'],$navyParams['PAGEN']);
    $dbResultList->NavRecordCount = $totalCount;
    $dbResultList->NavPageCount = $totalPages;
    $dbResultList->NavPageNomer = $navyParams['PAGEN'];
}else{
    $dbResultList->NavStart();
}


$lAdmin->NavText($dbResultList->GetNavPrint(Loc::getMessage("group_admin_nav")));

$lAdmin->AddHeaders($headers);

$allSelectedFields = array(
    "ORDER_ID" => false,
    "PAID" => false,
    "DATE_PAID" => false
);

$visibleHeaders = $lAdmin->GetVisibleHeaderColumns();
$allSelectedFields = array_merge($allSelectedFields,array_fill_keys($visibleHeaders,true));

while($shipment = $dbResultList->Fetch()){
    $row = & $lAdmin->AddRow($shipment['ID'],$shipment);
    $filterParams = GetFilterParams("filter_");
    $sShipmentLink = <<<HTML
        <a href="sale_order_shipment_edit.php?order_id={$shipment['ORDER_ID']}&shipment_id={$shipment['ID']}&lang={$lang}{$filterParams}" target="_blank">{$shipment['ID']}</a>
HTML;
    $row->AddField("ID",$sShipmentLink);
    $sOrderLink = <<<HTML
        <a href="sale_order_edit.php?ID={$shipment['ORDER_ID']}&lang={$lang}{$filterParams}" target="_blank">{$shipment['ORDER_ID']}</a>
HTML;
    $row->AddField("ORDER_ID",$sOrderLink);
    $deliveryName = htmlspecialcharsbx($shipment['DELIVERY_NAME']);
    $sDeliveryLink = <<<HTML
        <a href="sale_delivery_service_edit.php?ID={$shipment['DELIVERY_ID']}&lang={$lang}{$filterParams}">{$deliveryName}</a>
HTML;
    $row->AddField("DELIVERY_NAME",$sDeliveryLink);
    $row->AddField("ACCOUNT_NUMBER",htmlspecialcharsbx($shipment['SALE_INTERNALS_SHIPMENT_ORDER_ACCOUNT_NUMBER']));
    $row->AddField("ALLOW_DELIVERY",($shipment["ALLOW_DELIVERY"] == "Y") ? Loc::getMessage("SHIPMENT_ORDER_YES") : Loc::getMessage("SHIPMENT_ORDER_NO"));
    $row->AddField("COMPANY_BY","<a href=\"sale_company_edit.php?ID=" . $shipment['COMPANY_ID'] . "&lang=" . $lang . GetFilterParams("filter_") . "\">" . htmlspecialcharsbx($shipment['SALE_INTERNALS_SHIPMENT_COMPANY_BY_NAME']) . "</a>");
    $row->AddField("ORDER_USER_NAME","<a href='/bitrix/admin/user_edit.php?ID=" . $shipment['ORDER_USER_ID'] . "&lang=" . $lang . "'>" . htmlspecialcharsbx($shipment['ORDER_USER_NAME']) . " " . htmlspecialcharsbx($shipment['ORDER_USER_LAST_NAME']) . "</a>");
    $row->AddField("PRICE_DELIVERY",\CCurrencyLang::CurrencyFormat($shipment['PRICE_DELIVERY'],$shipment['SALE_INTERNALS_SHIPMENT_ORDER_CURRENCY']));

    $row->AddField("DEDUCTED",(($shipment["DEDUCTED"] == "Y") ? Loc::getMessage("SHIPMENT_ORDER_YES") : Loc::getMessage("SHIPMENT_ORDER_NO")) . "<br><a href=\"user_edit.php?ID=" . $shipment['EMP_DEDUCTED_ID'] . "\">" . htmlspecialcharsbx($shipment['SALE_INTERNALS_SHIPMENT_EMP_DEDUCTED_BY_LAST_NAME']) . " " . htmlspecialcharsbx($shipment['SALE_INTERNALS_SHIPMENT_EMP_DEDUCTED_BY_NAME']) . "</a><br>" . htmlspecialcharsbx($shipment['DATE_DEDUCTED']));
    $row->AddField("RESPONSIBLE_BY","<a href=\"user_edit.php?ID=" . $shipment['RESPONSIBLE_ID'] . "\">" . htmlspecialcharsbx($shipment['RESPONSIBLE_BY_NAME']) . " " . htmlspecialcharsbx($shipment['RESPONSIBLE_BY_LAST_NAME']) . "</a>");
    $row->AddField("ALLOW_DELIVERY",(($shipment["ALLOW_DELIVERY"] == "Y") ? Loc::getMessage("SHIPMENT_ORDER_YES") : Loc::getMessage("SHIPMENT_ORDER_NO")) . "<br><a href=\"user_edit.php?ID=" . $shipment['EMP_ALLOW_DELIVERY_ID'] . "\">" . htmlspecialcharsbx($shipment['EMP_ALLOW_DELIVERY_BY_LAST_NAME']) . " " . htmlspecialcharsbx($shipment['EMP_ALLOW_DELIVERY_BY_NAME']) . "</a><br>" . htmlspecialcharsbx($shipment['DATE_ALLOW_DELIVERY']));
    $row->AddField("CANCELED",(($shipment["CANCELED"] == "Y") ? Loc::getMessage("SHIPMENT_ORDER_YES") : Loc::getMessage("SHIPMENT_ORDER_NO")) . "<br><a href=\"user_edit.php?ID=" . $shipment['EMP_CANCELED_ID'] . "\">" . htmlspecialcharsbx($shipment['EMP_CANCELED_BY_LAST_NAME']) . " " . htmlspecialcharsbx($shipment['EMP_CANCELED_BY_NAME']) . "</a><br>" . htmlspecialcharsbx($shipment['DATE_CANCELED']));
    $row->AddField("MARKED",(($shipment["MARKED"] == "Y") ? Loc::getMessage("SHIPMENT_ORDER_YES") : Loc::getMessage("SHIPMENT_ORDER_NO")) . "<br><a href=\"user_edit.php?ID=" . $shipment['EMP_MARKED_ID'] . "\">" . htmlspecialcharsbx($shipment['EMP_MARKED_BY_LAST_NAME']) . " " . htmlspecialcharsbx($shipment['EMP_MARKED_BY_NAME']) . "</a><br>" . htmlspecialcharsbx($shipment['DATE_MARKED']));
    $row->AddField("REASON_MARKED",$shipment["REASON_MARKED"]);
    $row->AddField("STATUS",htmlspecialcharsbx($shipment['STATUS_NAME']));

    /*$arActions = array();
    $row->AddActions($arActions);*/
}

$lAdmin->AddGroupActionTable([
        "fullfill" => Loc::getMessage("SHIPTOR_SEND_FULLFILL"),
        "update" => Loc::getMessage("SHIPTOR_UPDATE_STATUS"),
        "remove" => Loc::getMessage("SHIPTOR_DELETE")
    ]
);

$aContext[] = array(
    "ICON" => "properties",
    "TEXT" => Loc::getMessage("SHIPTOR_BTN_SETTINGS"),
    "TITLE" => Loc::getMessage("SHIPTOR_BTN_SETTINGS_TEXT"),
    "LINK" => "javascript:go2params()"
);
if(intval($filter_direct) == 0){
    $aContext[] = array(
        "ICON" => "properties",
        "TEXT" => Loc::getMessage("SHIPTOR_BTN_CREATE_AGENT"),
        "TITLE" => Loc::getMessage("SHIPTOR_BTN_CREATE_AGENT_TEXT"),
        "LINK" => "javascript:createAgent()"
    );
}


$lAdmin->AddAdminContextMenu($aContext);?>
<?$lAdmin->AddFooter(
    array(
        array(
            "title" => Loc::getMessage("MAIN_ADMIN_LIST_SELECTED"),
            "value" => $dbResultList->SelectedRowsCount()
        ),
        array(
            "counter" => true,
            "title" => Loc::getMessage("MAIN_ADMIN_LIST_CHECKED"),
            "value" => "0"
        ),
    )
);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage("SHIPTOR_TITLE"));
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?=$curPage?>?">
    <?
    $cafilter = array(
        Loc::getMessage("SHIPMENT_ORDER_ID"),
        Loc::getMessage("SHIPMENT_ID"),
        Loc::getMessage("SHIPTOR_DELIVERY_TYPE"),
        Loc::getMessage("SALE_ORDER_PRICE_DELIVERY"),
        Loc::getMessage("SALE_ORDER_DELIVERY_DOC_NUM"),
        Loc::getMessage("SALE_ORDER_ACCOUNT_NUM"),
        Loc::getMessage("SHIPTOR_CARRIAGE_STATUS"),
        Loc::getMessage("SALE_SHIPMENT_F_USER_ID"),
        Loc::getMessage("SALE_SHIPMENT_F_USER_LOGIN"),
        Loc::getMessage("SALE_SHIPMENT_F_USER_EMAIL")
    );
    $arShiptorStatuses = array("new","sent","delivered","removed");
    $oFilter = new CAdminFilter(
        $tableId . "_filter", $cafilter
    );

    $oFilter->Begin();
    ?>
    <tr>
        <td><?=Loc::getMessage("SHIPMENT_ORDER_ID")?>:</td>
        <td>
            <script type="text/javascript">
                function changeFilterOrderIdFrom(){
                    if (document.find_form.filter_order_id_to.value.length <= 0)
                        document.find_form.filter_order_id_to.value = document.find_form.filter_order_id_from.value;
                }
            </script>
            <?=Loc::getMessage("SHIPMENT_ORDER_ID_FROM");?>
            <input type="text" name="filter_order_id_from" OnChange="changeFilterOrderIdFrom()" value="<?=(intval($filter_order_id_from) > 0) ? intval($filter_order_id_from) : ""?>" size="10">
            <?=Loc::getMessage("SHIPMENT_ORDER_ID_TO");?>
            <input type="text" name="filter_order_id_to" value="<?=(intval($filter_order_id_to) > 0) ? intval($filter_order_id_to) : ""?>" size="10">
        </td>
    </tr>
    <tr>
        <td><?=Loc::getMessage("SHIPMENT_ID")?>:</td>
        <td>
            <script type="text/javascript">
                function changeFilterOrderIdFrom(){
                    if (document.find_form.filter_shipment_id_to.value.length <= 0)
                        document.find_form.filter_shipment_id_to.value = document.find_form.filter_shipment_id_from.value;
                }
            </script>
            <?=Loc::getMessage("SHIPMENT_ORDER_ID_FROM");?>
            <input type="text" name="filter_shipment_id_from" OnChange="changeFilterOrderIdFrom()" value="<?=(intval($filter_shipment_id_from) > 0) ? intval($filter_shipment_id_from) : ""?>" size="10">
            <?=Loc::getMessage("SHIPMENT_ORDER_ID_TO");?>
            <input type="text" name="filter_shipment_id_to" value="<?=(intval($filter_shipment_id_to) > 0) ? intval($filter_shipment_id_to) : ""?>" size="10">
        </td>
    </tr>
    <tr>
        <td><?=Loc::getMessage("SHIPTOR_DELIVERY_TYPE");?>:</td>
        <td>
            <select name="filter_direct">
                <option value="0" <?if($filter_direct == 0):?>selected<?endif?>><?= Loc::getMessage("SHIPTOR_DELIVERY_TYPE_COMMON")?></option>
                <option value="1" <?if($filter_direct == 1):?>selected<?endif?>><?= Loc::getMessage("SHIPTOR_DELIVERY_TYPE_DIRECT")?></option>
            </select>
        </td>
    </tr>
    <tr>
        <td><?=Loc::getMessage("SALE_ORDER_PRICE_DELIVERY");?>:</td>
        <td>
            <?= Loc::getMessage("PRICE_DELIVERY_FROM");?>
            <input type="text" name="filter_price_delivery_from" value="<?=($filter_price_delivery_from != 0) ? htmlspecialcharsbx($filter_price_delivery_from) : '';?>" size="3">

            <?= Loc::getMessage("PRICE_DELIVERY_TO");?>
            <input type="text" name="filter_price_delivery_to" value="<?=($filter_price_delivery_to != 0) ? htmlspecialcharsbx($filter_price_delivery_to) : '';?>" size="3">
        </td>
    </tr>
    <tr>
        <td><?=Loc::getMessage("SALE_ORDER_DELIVERY_DOC_NUM");?>:</td>
        <td>
            <input type="text" name="filter_delivery_doc_num" value="<?=htmlspecialcharsbx($filter_delivery_doc_num);?>">
        </td>
    </tr>
    <tr>
        <td><?=Loc::getMessage("SALE_ORDER_ACCOUNT_NUM");?>:</td>
        <td>
            <input type="text" name="filter_account_num" value="<?=htmlspecialcharsbx($filter_account_num)?>">
        </td>
    </tr>
    <tr>
        <td><?=Loc::getMessage("SHIPTOR_CARRIAGE_STATUS");?>:</td>
        <td>
            <select name="filter_shiptor_status">
                <?foreach($arShiptorStatuses as $statusName):?>
                    <option value="<?=$statusName?>" <?if($statusName == $filter_shiptor_status):?>selected<?endif?>><?=$statusName?></option>
                <?endforeach?>
            </select>
        </td>
    </tr>
    <tr>
        <td><?= Loc::getMessage("SALE_SHIPMENT_F_USER_ID");?>:</td>
        <td>
            <?= FindUserID("filter_user_id",$filter_user_id,"","find_form");?>
        </td>
    </tr>
    <tr>
        <td><?= Loc::getMessage("SALE_SHIPMENT_F_USER_LOGIN");?>:</td>
        <td>
            <input type="text" name="filter_user_login" value="<?echo htmlspecialcharsEx($filter_user_login)?>" size="40">
        </td>
    </tr>
    <tr>
        <td><?= Loc::getMessage("SALE_SHIPMENT_F_USER_EMAIL");?>:</td>
        <td>
            <input type="text" name="filter_user_email" value="<?echo htmlspecialcharsEx($filter_user_email)?>" size="40">
        </td>
    </tr>
    <?
    $oFilter->Buttons(
        array(
            "table_id" => $tableId,
            "url" => $curPage,
            "form" => "find_form"
        )
    );

    $oFilter->End();
    ?>
</form>
<?
$lAdmin->DisplayList();
?>
<script type="text/javascript">
    function go2params(){
        window.open("/bitrix/admin/settings.php?lang=ru&mid=shiptor.delivery&mid_menu=1");
    }
    function createAgent(){
        var wait = SHshowWait();
        BX.ajax.post(location.href,{mode:"createAgent"},function(d){
            var tableList = BX("shiptor_shipment_upload_result_div"),
                divAnswer = BX("create_agent_answer");
            if(!divAnswer){
                divAnswer = BX.create("div");
                divAnswer.id = "create_agent_answer";
                divAnswer.style.fontWeight = "bold";
                divAnswer.style.margin = "10px";
            }
            if(!!d){
                location.href = d;
                //divAnswer.innerHTML = d.toString();
            }else{
                divAnswer.innerHTML = "<?=Loc::getMessage("SHIPTOR_WRONG")?>";
            }
            if(!BX("create_agent_answer")){
                tableList.parentNode.insertBefore(divAnswer,tableList);
            }
            BX.closeWait(wait);
        });
    }
    function SHshowWait(){
        var overlayDiv = BX.showWait(document),
            waitDiv = BX.create("div");
        overlayDiv.style.position = "fixed";
        overlayDiv.style.background = "rgba(0,0,0,0.2)";
        overlayDiv.style.border = "none";
        overlayDiv.style.width = "100%";
        overlayDiv.style.height = "100%";
        overlayDiv.style.left = "0";
        overlayDiv.style.top = "0";
        waitDiv.style.background = '#e0e9ec';
        waitDiv.style.color = "black";
        waitDiv.style.fontSize = "1.2em";
        waitDiv.style.margin = "30% auto";
        waitDiv.style.width = "20em";
        waitDiv.style.padding = "0.9em";
        waitDiv.style.height= "3em";
        waitDiv.style.boxSizing = "border-box";
        waitDiv.innerHTML = "<?=Loc::getMessage("SHIPTOR_WAIT")?>";
        overlayDiv.innerHTML = waitDiv.outerHTML;
        return overlayDiv;
    }
</script>
<?require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");