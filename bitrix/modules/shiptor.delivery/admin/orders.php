<?
if(!defined('SHIPTOR_DIR'))
    define('SHIPTOR_DIR',$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shiptor.delivery/");
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Sale\Internals\StatusTable;
use Bitrix\Sale;
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/admin_tool.php");

Loc::loadMessages(__FILE__);
Loc::loadMessages($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/admin/order.php');

if($APPLICATION->GetGroupRight("main")<"R")
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/include.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$APPLICATION->SetTitle(GetMessage('SHIPTOR_ORDERS'));

$arUserGroups=$USER->GetUserGroupArray();
$intUserID=(int)$USER->GetID();

$arAccessibleSites=array();
$dbAccessibleSites=CSaleGroupAccessToSite::GetList(
    array(),array("GROUP_ID"=>$arUserGroups),false,false,array("SITE_ID")
);
while($arAccessibleSite=$dbAccessibleSites->Fetch()){
    if(!in_array($arAccessibleSite["SITE_ID"],$arAccessibleSites))
        $arAccessibleSites[]=$arAccessibleSite["SITE_ID"];
}

$bExport=(isset($_REQUEST['mode'])&&$_REQUEST['mode']=='excel');

$sTableID="tbl_sale_order";
$oSort=new CAdminSorting($sTableID,"ID","desc");
$lAdmin=new CAdminSaleList($sTableID,$oSort);
$runtimeFields=array();

$arFilterFields=array(
  "filter_universal",
  "filter_id_from",
  "filter_id_to",
  "filter_account_number",
  "filter_date_from",
  "filter_date_to",
  "filter_date_update_from",
  "filter_date_update_to",
  "filter_date_paid_from",
  "filter_date_paid_to",
  "filter_lang",
  "filter_currency",
  "filter_price_from",
  "filter_price_to",
  "filter_status",
  "filter_date_status_from",
  "filter_by_recommendation",
  "filter_date_status_to",
  "filter_payed",
  "filter_canceled",
  "filter_deducted",
  "filter_allow_delivery",
  "filter_date_allow_delivery_to",
  "filter_date_allow_delivery_from",
  "filter_marked",
  "filter_buyer",
  "filter_product_id",
  "filter_product_xml_id",
  "filter_discount_coupon",
  "filter_person_type",
  "filter_user_login",
  "filter_user_email",
  "filter_group_id",
  "filter_sum_paid",
  "filter_pay_system",
  "filter_delivery_service",
  "filter_xml_id",
  "filter_tracking_number",
  "filtrer_source"
);

#	======	arOrderProps  ======
$arOrderProps=array();
$arOrderPropsCode=array();
$dbProps=\Bitrix\Sale\Internals\OrderPropsTable::getList(array(
    'order'=>array("PERSON_TYPE_ID"=>"ASC","SORT"=>"ASC"),
    'select'=>array("ID","NAME","PERSON_TYPE_NAME"=>"PERSON_TYPE.NAME","LID"=>"PERSON_TYPE.LID","PERSON_TYPE_ID","SORT","IS_FILTERED","TYPE","CODE","SETTINGS"),
  ));

while($arProps=$dbProps->fetch()){
    $key="";
    $propAdded=false;

    if(strlen($arProps["CODE"])>0){
        $key=$arProps["CODE"];

        if(empty($arOrderPropsCode[$key])){
            $arOrderPropsCode[$key]=$arProps;
            $propAdded=true;
        }
    }

    if(!$propAdded){
        $key=intval($arProps["ID"]);
        if(empty($arOrderProps[$key])){
            $arOrderProps[$key]=$arProps;
        }
    }

    if($key){
        if($arProps["IS_FILTERED"]=="Y"&&$arProps["TYPE"]!="MULTISELECT"&&$arProps["TYPE"]!="FILE"){
            $arFilterFields[]="filter_prop_".$key;
        }
    }
}
$lAdmin->InitFilter($arFilterFields);


# ============= ORDERS ==============

foreach($arOrderProps as $key=> $value){
    $propIterator++;

    if($value["IS_FILTERED"]!="Y"||$value["TYPE"]=="MULTIPLE")
        continue;

    if(
      (isset($filterOrderProps["PROPERTY_VALUE_".$key])&&strlen($filterOrderProps["PROPERTY_VALUE_".$key])>0)||(isset($filterOrderProps["%PROPERTY_VALUE_".$key])&&strlen($filterOrderProps["%PROPERTY_VALUE_".$key])>0)
    ){
        $runtimeFields['PROP_'.$propIterator]=array(
          'data_type'=>'Bitrix\Sale\Internals\OrderPropsValueTable',
          'reference'=>array(
            'ref.ORDER_ID'=>'this.ID',
          ),
          'join_type'=>'inner'
        );

        $arFilterTmp["=PROP_".$propIterator.".ORDER_PROPS_ID"]=$key;

        if(isset($filterOrderProps["%PROPERTY_VALUE_".$key]))
            $arFilterTmp["%PROP_".$propIterator.".VALUE"]=$filterOrderPropValue[$key];
        else
            $arFilterTmp["PROP_".$propIterator.".VALUE"]=$filterOrderPropValue[$key];
    }
}

foreach(GetModuleEvents("sale","OnOrderListFilter",true) as $arEvent)
    $arFilterTmp=ExecuteModuleEventEx($arEvent,array($arFilterTmp));

$arID=array();

if(($arID=$lAdmin->GroupAction())&&$saleModulePermissions>="U"){
    $arAffectedOrders=array();
    $forAll=($_REQUEST['action_target']=='selected');

    if($forAll){
        $filter=$arFilterTmp;
        $arID=array();
    }else{
        $filter=array(
          "ID"=>$arID,
          "=STATUS_ID"=>$allowedStatusesView
        );
    }

    $dbOrderList=\Bitrix\Sale\Internals\OrderTable::getList(array(
        'order'=>array($by=>$order),
        'filter'=>$filter,
        'select'=>array("ID","PERSON_TYPE_ID","PAYED","CANCELED","DEDUCTED","STATUS_ID")
    ));

    while($arOrderList=$dbOrderList->fetch()){
        if($forAll)
            $arID[]=$arOrderList['ID'];

        $arAffectedOrders[$arOrderList["ID"]]=$arOrderList;
    }

    foreach($arID as $ID){
        if(strlen($ID)<=0)
            continue;

        if(CSaleOrder::IsLocked($ID,$lockedBY,$dateLock)&&$_REQUEST['action']!="unlock"){
            $lAdmin->AddGroupError(str_replace("#DATE#","$dateLock",str_replace("#ID#","$lockedBY",Loc::getMessage("SOE_ORDER_LOCKED"))),$ID);
        }else{
            switch($_REQUEST['action']){
                case "delete":
                    if(!($saleOrder=\Bitrix\Sale\Order::load($ID))){
                        $lAdmin->AddGroupError(Loc::getMessage("SALE_DELETE_ERROR_CANT_FIND",array("#ID#"=>$ID)));
                        break;
                    }
                    if(!CSaleOrder::CanUserDeleteOrder($ID,$arUserGroups,$intUserID)){
                        $lAdmin->AddGroupError(Loc::getMessage("SO_NO_PERMS2DEL",array("#ID#"=>$ID)),$ID);
                        break;
                    }

                    $res=\Bitrix\Sale\Order::delete($ID);
                    if(!$res->isSuccess())
                        $lAdmin->AddGroupError(implode("<br>\n",$res->getErrorMessages()));
                    break;
                case "unlock":
                    CSaleOrder::UnLock($ID);
                    break;
                case "cancel":
                    if(!CSaleOrder::CanUserCancelOrder($ID,$arUserGroups,$intUserID)){
                        $lAdmin->AddGroupError(Loc::getMessage("SOA_PERMS_CANCEL_GROUP",array("#ID#"=>$ID)),$ID);
                        break;
                    }
                    /** @var \Bitrix\Sale\Order $saleOrder */
                    if(!$saleOrder=\Bitrix\Sale\Order::load($ID)){
                        $lAdmin->AddGroupError(Loc::getMessage("SO_NO_ORDER",array("#ID#"=>$ID)),$ID);
                        break;
                    }
                    if($saleOrder->getField("CANCELED")=="Y"){
                        $lAdmin->AddGroupError(Loc::getMessage("SOA_PERMS_CANCEL_GROUP_CANCEL",array("#ID#"=>$ID)),$ID);
                        break;
                    }
                    /** @var \Bitrix\Sale\Result $res */
                    $res=$saleOrder->setField("CANCELED","Y");
                    if(!$res->isSuccess()){
                        $lAdmin->AddGroupError(implode("<br>\n",$res->getErrorMessages()),$ID);
                        break;
                    }
                    $res=$saleOrder->save();
                    if(!$res->isSuccess())
                        $lAdmin->AddGroupError(implode("<br>\n",$res->getErrorMessages()),$ID);
                    break;
                case "cancel_n":
                    if(!CSaleOrder::CanUserCancelOrder($ID,$arUserGroups,$intUserID)){
                        $lAdmin->AddGroupError(Loc::getMessage("SOA_PERMS_CANCEL_GROUP",array("#ID#"=>$ID)),$ID);
                        break;
                    }
                    /** @var \Bitrix\Sale\Order $saleOrder */
                    if(!$saleOrder=\Bitrix\Sale\Order::load($ID)){
                        $lAdmin->AddGroupError(Loc::getMessage("SO_NO_ORDER",array("#ID#"=>$ID)),$ID);
                        break;
                    }
                    if($saleOrder->getField("CANCELED")=="N"){
                        $lAdmin->AddGroupError(Loc::getMessage("SOA_PERMS_CANCEL_GROUP_CANCEL_N",array("#ID#"=>$ID)),$ID);
                        break;
                    }
                    /** @var \Bitrix\Sale\Result $res */
                    $res=$saleOrder->setField("CANCELED","N");
                    if(!$res->isSuccess()){
                        $lAdmin->AddGroupError(implode("<br>\n",$res->getErrorMessages()),$ID);
                        break;
                    }
                    $res=$saleOrder->save();
                    if(!$res->isSuccess())
                        $lAdmin->AddGroupError(implode("<br>\n",$res->getErrorMessages()),$ID);
                    break;

                default:
                    if(substr($_REQUEST['action'],0,strlen("status_"))=="status_"){
                        $statusID=substr($_REQUEST['action'],strlen("status_"));

                        if(strlen($statusID)>0){
                            $resStatus=StatusTable::getList(array(
                                'select'=>array('ID','NAME'=>'Bitrix\Sale\Internals\StatusLangTable:STATUS.NAME'),
                                'filter'=>array(
                                  '=Bitrix\Sale\Internals\StatusLangTable:STATUS.LID'=>LANGUAGE_ID,
                                  '=ID'=>$statusID
                                ),
                            ));

                            if($arStatus=$resStatus->fetch()){
                                if(CSaleOrder::CanUserChangeOrderStatus($ID,$statusID,$arUserGroups)){
                                    if($arAffectedOrders[$ID]["STATUS_ID"]!=$statusID){
                                        $saleOrder=\Bitrix\Sale\Order::load($ID);
                                        $res=$saleOrder->setField("STATUS_ID",$statusID);

                                        if(!$res->isSuccess()){
                                            $errMsgs=$res->getErrorMessages();

                                            if(count($errMsgs)>0)
                                                $lAdmin->AddGroupError(Loc::getMessage("SOA_ERROR_STATUS",array("#ID#"=>$ID,"#STATUS#"=>$arStatus["NAME"])).": ".implode("<br>\n",$errMsgs),$ID);
                                            else
                                                $lAdmin->AddGroupError(Loc::getMessage("SOA_ERROR_STATUS",array("#ID#"=>$ID,"#STATUS#"=>$arStatus["NAME"])),$ID);
                                        }
                                        else{
                                            $res=$saleOrder->save();

                                            if(!$res->isSuccess())
                                                $lAdmin->AddGroupError(implode("<br>\n",$res->getErrorMessages()),$ID);
                                        }
                                    }
                                    else{
                                        $lAdmin->AddGroupError(Loc::getMessage("SOA_ERROR_STATUS_ALREADY",Array("#ID#"=>$ID,"#STATUS#"=>$arStatus["NAME"])),$ID);
                                    }
                                }else{
                                    $lAdmin->AddGroupError(Loc::getMessage("SOA_PERMS_STATUS_GROUP",Array("#ID#"=>$ID,"#STATUS#"=>$arStatus["NAME"])),$ID);
                                }
                            }
                        }
                    }

                    break;
            }
        }
    }
}

#====================

$arColumn2Field=array(
  "ID"=>array("ID"),
  "ACCOUNT_NUMBER"=>array("ACCOUNT_NUMBER"),
  "LID"=>array("LID"),
  "PERSON_TYPE"=>array("PERSON_TYPE_ID"),
  "PAYED"=>array("PAYED","DATE_PAYED","EMP_PAYED_ID"),
  "CANCELED"=>array("CANCELED","DATE_CANCELED","EMP_CANCELED_ID"),
  "DEDUCTED"=>array("DEDUCTED"),
  "MARKED"=>array("MARKED","DATE_MARKED","EMP_MARKED_ID","REASON_MARKED"),
  "STATUS_ID"=>array("STATUS_ID","DATE_STATUS","EMP_STATUS_ID"),
  "STATUS"=>array("STATUS_ID","DATE_STATUS","EMP_STATUS_ID"),
  "PRICE_DELIVERY"=>array("PRICE_DELIVERY","CURRENCY"),
  "PRICE"=>array("PRICE","CURRENCY"),
  "SUM_PAID"=>array("SUM_PAID","CURRENCY"),
  "USER"=>array("USER_ID"),
  "DATE_INSERT"=>array("DATE_INSERT"),
  "DATE_UPDATE"=>array("DATE_UPDATE"),
  "TAX_VALUE"=>array("TAX_VALUE","CURRENCY"),
  "LOCK_STATUS"=>array("LOCK_STATUS","LOCK_USER_NAME"),
  "BASKET"=>array(),
  "COMMENTS"=>array("COMMENTS"),
  "REASON_CANCELED"=>array("REASON_CANCELED"),
  "REASON_MARKED"=>array("REASON_MARKED"),
  "USER_EMAIL"=>array("USER_EMAIL"),
  "USER_DESCRIPTION"=>array("USER_DESCRIPTION"),
  "EXTERNAL_ORDER"=>array("EXTERNAL_ORDER"),
  "SOURCE_NAME"=>array("SOURCE_NAME"),
);

$arHeaders=array(
  array("id"=>"DATE_INSERT","content"=>Loc::getMessage("SI_DATE_INSERT"),"sort"=>"DATE_INSERT","default"=>true),
  array("id"=>"ID","content"=>"ID","sort"=>"ID","default"=>true),
  array("id"=>"USER","content"=>Loc::getMessage("SI_BUYER"),"sort"=>"USER_ID","default"=>true),
  array("id"=>"STATUS_ID","content"=>Loc::getMessage("SI_STATUS"),"sort"=>"STATUS_ID","default"=>true,"title"=>Loc::getMessage("SO_S_DATE_STATUS")),
  array("id"=>"PAYED","content"=>Loc::getMessage("SI_PAID"),"sort"=>"PAYED","default"=>true,"title"=>Loc::getMessage("SO_S_DATE_PAYED")),
  array("id"=>"ALLOW_DELIVERY","content"=>Loc::getMessage("SI_ALLOW_DELIVERY"),"sort"=>"ALLOW_DELIVERY","default"=>false),
  array("id"=>"CANCELED","content"=>Loc::getMessage("SI_CANCELED"),"sort"=>"CANCELED","default"=>true),
  array("id"=>"DEDUCTED","content"=>Loc::getMessage("SI_DEDUCTED"),"sort"=>"DEDUCTED","default"=>true),
  array("id"=>"MARKED","content"=>Loc::getMessage("SI_MARKED"),"sort"=>"MARKED","default"=>true),
  array("id"=>"PRICE","content"=>Loc::getMessage("SI_SUM"),"sort"=>"PRICE","default"=>true),
  array("id"=>"BASKET","content"=>Loc::getMessage("SI_ITEMS"),"sort"=>"","default"=>true),
  array("id"=>"DATE_UPDATE","content"=>Loc::getMessage("SI_DATE_UPDATE"),"sort"=>"DATE_UPDATE","default"=>false),
  array("id"=>"LID","content"=>Loc::getMessage("SI_SITE"),"sort"=>"LID"),
  array("id"=>"PERSON_TYPE","content"=>Loc::getMessage("SI_PAYER_TYPE"),"sort"=>"PERSON_TYPE_ID"),
  array("id"=>"PAY_VOUCHER_NUM","content"=>Loc::getMessage("SI_NO_PP"),"sort"=>"","default"=>false),
  array("id"=>"PAY_VOUCHER_DATE","content"=>Loc::getMessage("SI_DATE_PP"),"sort"=>"","default"=>false),
  array("id"=>"STATUS","content"=>Loc::getMessage("SI_STATUS_OLD"),"sort"=>"STATUS_ID","default"=>false),
  array("id"=>"PRICE_DELIVERY","content"=>Loc::getMessage("SI_DELIVERY"),"sort"=>"PRICE_DELIVERY","default"=>false),
  array("id"=>"DELIVERY_DOC_NUM","content"=>Loc::getMessage("SI_DELIVERY_DOC_NUM"),"sort"=>"","default"=>false),
  array("id"=>"DELIVERY_DOC_DATE","content"=>Loc::getMessage("SI_DELIVERY_DOC_DATE"),"sort"=>"","default"=>false),
  array("id"=>"SUM_PAID","content"=>Loc::getMessage("SI_SUM_PAID"),"sort"=>"SUM_PAID"),
  array("id"=>"USER_EMAIL","content"=>Loc::getMessage("SALE_F_USER_EMAIL"),"sort"=>"USER_EMAIL","default"=>false),
  array("id"=>"PAY_SYSTEM","content"=>Loc::getMessage("SI_PAY_SYS"),"sort"=>"","default"=>false),
  array("id"=>"DELIVERY","content"=>Loc::getMessage("SI_DELIVERY_SYS"),"sort"=>"","default"=>false),
  array("id"=>"PS_STATUS","content"=>Loc::getMessage("SI_PAYMENT_PS"),"sort"=>"","default"=>false),
  array("id"=>"PS_SUM","content"=>Loc::getMessage("SI_PS_SUM"),"sort"=>"","default"=>false),
  array("id"=>"TAX_VALUE","content"=>Loc::getMessage("SI_TAX"),"sort"=>"TAX_VALUE"),
  array("id"=>"BASKET_NAME","content"=>Loc::getMessage("SOA_BASKET_NAME"),"sort"=>""),
  array("id"=>"BASKET_PRODUCT_ID","content"=>Loc::getMessage("SOA_BASKET_PRODUCT_ID"),"sort"=>""),
  array("id"=>"BASKET_PRICE","content"=>Loc::getMessage("SOA_BASKET_PRICE"),"sort"=>""),
  array("id"=>"BASKET_QUANTITY","content"=>Loc::getMessage("SOA_BASKET_QUANTITY"),"sort"=>""),
  array("id"=>"BASKET_WEIGHT","content"=>Loc::getMessage("SOA_BASKET_WEIGHT"),"sort"=>""),
  array("id"=>"BASKET_NOTES","content"=>Loc::getMessage("SOA_BASKET_NOTES"),"sort"=>""),
  array("id"=>"BASKET_DISCOUNT_PRICE","content"=>Loc::getMessage("SOA_BASKET_DISCOUNT_PRICE"),"sort"=>""),
  array("id"=>"BASKET_CATALOG_XML_ID","content"=>Loc::getMessage("SOA_BASKET_CATALOG_XML_ID"),"sort"=>""),
  array("id"=>"BASKET_PRODUCT_XML_ID","content"=>Loc::getMessage("SOA_BASKET_PRODUCT_XML_ID"),"sort"=>""),
  array("id"=>"BASKET_DISCOUNT_NAME","content"=>Loc::getMessage("SALE_ORDER_LIST_HEADER_NAME_DISCOUNTS"),"sort"=>""),
  array("id"=>"BASKET_DISCOUNT_VALUE","content"=>Loc::getMessage("SOA_BASKET_DISCOUNT_VALUE"),"sort"=>""),
  array("id"=>"BASKET_DISCOUNT_COUPON","content"=>Loc::getMessage("SALE_ORDER_LIST_HEADER_NAME_COUPONS_MULTI"),"sort"=>""),
  array("id"=>"BASKET_VAT_RATE","content"=>Loc::getMessage("SOA_BASKET_VAT_RATE"),"sort"=>""),
  array("id"=>"DATE_ALLOW_DELIVERY","content"=>Loc::getMessage("SALE_F_DATE_ALLOW_DELIVERY"),"sort"=>"","default"=>false),
  array("id"=>"ACCOUNT_NUMBER","content"=>Loc::getMessage("SOA_ACCOUNT_NUMBER"),"sort"=>""),
  array("id"=>"TRACKING_NUMBER","content"=>Loc::getMessage("SOA_TRACKING_NUMBER"),"sort"=>"","default"=>false),
  array("id"=>"EXTERNAL_ORDER","content"=>Loc::getMessage("SOA_EXTERNAL_ORDER"),"sort"=>"","default"=>false),
  array("id"=>"SHIPMENTS","content"=>Loc::getMessage("SOA_SHIPMENTS"),"sort"=>"","default"=>true),
  array("id"=>"PAYMENTS","content"=>Loc::getMessage("SOA_PAYMENTS"),"sort"=>"","default"=>true),
  array("id"=>"SOURCE_NAME","content"=>Loc::getMessage("SALE_F_SOURCE"),"sort"=>"SOURCE_NAME","default"=>false)
);

if($DBType=="mysql"){
    $arHeaders[]=array("id"=>"COMMENTS","content"=>Loc::getMessage("SI_COMMENTS"),"sort"=>"COMMENTS","default"=>false);
    $arHeaders[]=array("id"=>"PS_STATUS_DESCRIPTION","content"=>Loc::getMessage("SOA_PS_STATUS_DESCR"),"sort"=>"","default"=>false);
    $arHeaders[]=array("id"=>"USER_DESCRIPTION","content"=>Loc::getMessage("SI_USER_DESCRIPTION"),"sort"=>"","default"=>false);
    $arHeaders[]=array("id"=>"REASON_CANCELED","content"=>Loc::getMessage("SI_REASON_CANCELED"),"sort"=>"","default"=>false);
    $arHeaders[]=array("id"=>"REASON_MARKED","content"=>Loc::getMessage("SI_REASON_MARKED"),"sort"=>"","default"=>false);
}

foreach($arOrderProps as $key=> $value){
    $arHeaders[]=array("id"=>"PROP_".$key,"content"=>htmlspecialcharsbx($value["NAME"])." (".htmlspecialcharsbx($value["PERSON_TYPE_NAME"]).")","sort"=>"","default"=>false);
    $arColumn2Field["PROP_".$key]=array();
}
foreach($arOrderPropsCode as $key=> $value){
    $arHeaders[]=array("id"=>"PROP_".$key,"content"=>htmlspecialcharsbx($value["NAME"]),"sort"=>"","default"=>false);
    $arColumn2Field["PROP_".$key]=array();
}

$lAdmin->AddHeaders($arHeaders);

#====================

$arSelectFields=array();
$arSelectFields[]="ID";
$arSelectFields[]="LID";
$arSelectFields[]="LOCK_STATUS";
$arSelectFields[]="LOCK_USER_NAME";

$arVisibleColumns=$lAdmin->GetVisibleHeaderColumns();
$bNeedProps=false;
$bNeedBasket=false;
foreach($arVisibleColumns as $visibleColumn){
    if(!$bNeedProps&&SubStr($visibleColumn,0,StrLen("PROP_"))=="PROP_")
        $bNeedProps=true;
    if(
      !$bNeedBasket&&$visibleColumn!='BASKET_DISCOUNT_COUPON'&&$visibleColumn!='BASKET_DISCOUNT_NAME'&&strpos($visibleColumn,"BASKET")!==false
    )
        $bNeedBasket=true;

    if(array_key_exists($visibleColumn,$arColumn2Field)){
        if(is_array($arColumn2Field[$visibleColumn])&&count($arColumn2Field[$visibleColumn])>0){
            $countArColumn=count($arColumn2Field[$visibleColumn]);
            for($i=0; $i<$countArColumn; $i++){
                if(!in_array($arColumn2Field[$visibleColumn][$i],$arSelectFields))
                    $arSelectFields[]=$arColumn2Field[$visibleColumn][$i];
            }
        }
    }
}

#====================



require_once ($DOCUMENT_ROOT.BX_ROOT."/modules/main/include/prolog_admin_after.php");
//add Shiptor admin headers
include_once(SHIPTOR_DIR.DIRECTORY_SEPARATOR.'include.php');

if(isset($_REQUEST['action'])&&$_REQUEST['action']=='send'&&isset($_REQUEST['SID'])&&is_numeric($_REQUEST['SID'])){
    $OrderId=intval($_REQUEST['SID']);
    switch($_REQUEST['action']){
        case 'send':
            CShiptorDb::MarkOrderSent($OrderId);
            break;
        case 'resend':
            CShiptorDb::MarkOrderReSend($OrderId);
            break;
        /* case 'forsesync':
          CShiptorDb::MarkOrdersToForce($amount);
          break; */
        case 'markerror':
            CShiptorDb::MarkOrderError($OrderId);
            break;
    }
    LocalRedirect($APPLICATION->GetCurPage().'?mid='.htmlspecialcharsbx($mid).'&amp;lang='.LANG);
}
?>
<div class="adm-c-bigdatabar">
    <div class="adm-c-bigdatabar-container">
        <div class="adm-c-bigdatabar-summ">

        </div>
        <div class="adm-c-bigdatabar-content">
            <div class="adm-c-bigdatabar-line">
                <p><?=GetMessage('SHIPTOR_ORDERS_TITLE')?></p>
            </div>
        </div>
        <div class="clb"></div>
    </div>
</div>

<?
$lAdmin->BeginEpilogContent();
echo "<script>",$sScript,"\nif(document.getElementById('order_sum')) {setTimeout(function(){document.getElementById('order_sum').innerHTML = '".CUtil::JSEscape($order_sum)."';}, 10);}\n","</script>";
echo "<script>",$sScript,"\nif(document.getElementById('bigdatabar')) {setTimeout(function(){document.getElementById('bigdatabar').innerHTML = '".CUtil::JSEscape($bigdataWidgetHtml)."';}, 10);}\n","</script>";
?>
<script>
    function exportData(val)
    {
        var oForm = document.form_<?=$sTableID?>;
        var expType = oForm.action_target.checked;

        var par = "mode=excel";
        if (!expType)
        {
            var num = oForm.elements.length;
            for (var i = 0; i < num; i++)
            {
                if (oForm.elements[i].tagName.toUpperCase() == "INPUT"
                        && oForm.elements[i].type.toUpperCase() == "CHECKBOX"
                        && oForm.elements[i].name.toUpperCase() == "ID[]"
                        && oForm.elements[i].checked == true)
                {
                    par += "&OID[]=" + oForm.elements[i].value;
                }
            }
        }

        if (expType)
        {
            par += "<?=CUtil::JSEscape(GetFilterParams("filter_",false));?>";
        }

        if (par.length > 0)
        {
            var url = 'sale_order_export.php';
            if (val == "excel")
            {
                url = 'sale_order.php';
            }

            window.open(url + "?EXPORT_FORMAT=" + val + "&" + par, "vvvvv");
        }
    }
</script>
<?
$lAdmin->EndEpilogContent();

$arGroupActionsTmp=array(
  "delete"=>Loc::getMessage("MAIN_ADMIN_LIST_DELETE"),
  "cancel"=>Loc::getMessage("SOAN_LIST_CANCEL"),
  "cancel_n"=>Loc::getMessage("SOAN_LIST_CANCEL_N"),
);

$allowedStatusesFrom=\Bitrix\Sale\OrderStatus::getStatusesUserCanDoOperations($intUserID,array('from'));

foreach($allowedStatusesFrom as $status){
    if(!isset($LOCAL_STATUS_CACHE[$status])||empty($LOCAL_STATUS_CACHE[$status])){
        $arStatus=StatusTable::getList(array(
            'select'=>array(
              'NAME'=>'Bitrix\Sale\Internals\StatusLangTable:STATUS.NAME',
            ),
            'filter'=>array(
              '=ID'=>$status,
              '=Bitrix\Sale\Internals\StatusLangTable:STATUS.LID'=>LANGUAGE_ID,
              '=TYPE'=>'O'
            ),
            'limit'=>1,
          ))->fetch();

        if($arStatus)
            $LOCAL_STATUS_CACHE[$status]=htmlspecialcharsEx($arStatus["NAME"]);
    }

    $arGroupActionsTmp["status_".$status]=Loc::getMessage("SOAN_LIST_STATUS_CHANGE").' "'.$LOCAL_STATUS_CACHE[$status].'"';
}

$arGroupActionsTmp["export_csv"]=array(
  "action"=>"exportData('csv')",
  "value"=>"export_csv",
  "name"=>str_replace("#EXP#","CSV",Loc::getMessage("SOAN_EXPORT_2"))
);
$arGroupActionsTmp["export_commerceml"]=array(
  "action"=>"exportData('commerceml')",
  "value"=>"export_commerceml",
  "name"=>str_replace("#EXP#","CommerceML",Loc::getMessage("SOAN_EXPORT_2"))
);
$arGroupActionsTmp["export_commerceml2"]=array(
  "action"=>"exportData('commerceml2')",
  "value"=>"export_commerceml2",
  "name"=>str_replace("#EXP#","CommerceML 2.0",Loc::getMessage("SOAN_EXPORT_2"))
);

$strPath2Export=BX_PERSONAL_ROOT."/php_interface/include/sale_export/";
if(file_exists($_SERVER["DOCUMENT_ROOT"].$strPath2Export)){
    if($handle=opendir($_SERVER["DOCUMENT_ROOT"].$strPath2Export)){
        while(($file=readdir($handle))!==false){
            if($file=="."||$file=="..")
                continue;
            if(is_file($_SERVER["DOCUMENT_ROOT"].$strPath2Export.$file)&&substr($file,strlen($file)-4)==".php"){
                $export_name=substr($file,0,strlen($file)-4);
                $arGroupActionsTmp["export_".$export_name]=array(
                  "action"=>"exportData('".$export_name."')",
                  "value"=>"export_".$export_name,
                  "name"=>str_replace("#EXP#",$export_name,Loc::getMessage("SOAN_EXPORT_2"))
                );
            }
        }
    }
    closedir($handle);
}

$lAdmin->AddGroupActionTable($arGroupActionsTmp);
$aContext=array();

if($saleModulePermissions=="U")
    $allowedStatusesUpdate=\Bitrix\Sale\OrderStatus::getStatusesUserCanDoOperations($intUserID,array('update'));

if($saleModulePermissions=="W"||($saleModulePermissions=="U"&&!empty($allowedStatusesUpdate))){
    $siteLID="";
    $arSiteMenu=array();
    $arSitesShop=array();
    $arSitesTmp=array();
    $rsSites=CSite::GetList($b="id",$o="asc",Array("ACTIVE"=>"Y"));
    while($arSite=$rsSites->GetNext()){
        $site=Option::get("sale","SHOP_SITE_".$arSite["ID"],"");
        if($arSite["ID"]==$site){
            $arSitesShop[]=array("ID"=>$arSite["ID"],"NAME"=>$arSite["NAME"]);
        }
        $arSitesTmp[]=array("ID"=>$arSite["ID"],"NAME"=>$arSite["NAME"]);
    }

    $rsCount=count($arSitesShop);
    if($rsCount<=0){
        $arSitesShop=$arSitesTmp;
        $rsCount=count($arSitesShop);
    }

    if($rsCount==1){
        $siteLID="&SITE_ID=".$arSitesShop[0]["ID"];
    }else{
        foreach($arSitesShop as &$val){
            $arSiteMenu[]=array(
              "TEXT"=>$val["NAME"]." (".$val["ID"].")",
              "ACTION"=>"window.location = 'sale_order_create.php?lang=".LANGUAGE_ID."&SITE_ID=".$val["ID"]."';"
            );
        }
        if(isset($val))
            unset($val);
    }

    $aContext=array(
      array(
        "TEXT"=>Loc::getMessage("SALE_A_NEWORDER"),
        "ICON"=>"btn_new",
        "LINK"=>"sale_order_create.php?lang=".LANGUAGE_ID.$siteLID,
        "TITLE"=>Loc::getMessage("SALE_A_NEWORDER_TITLE"),
        "MENU"=>$arSiteMenu
      ),
    );
}

$lAdmin->AddAdminContextMenu($aContext);
$lAdmin->CheckListMode();

\Bitrix\Main\Page\Asset::getInstance()->addString('<style>.adm-filter-item-center, .adm-filter-content {overflow: visible !important;}</style>');

/* * ****************************************************************** */
/* * ******************  PAGE  **************************************** */
/* * ****************************************************************** */

$APPLICATION->SetTitle(Loc::getMessage("SALE_SECTION_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<script type="text/javascript">
    function fToggleSetItems(setParentId)
    {
        var elements = document.getElementsByClassName('set_item_' + setParentId);
        var hide = false;

        for (var i = 0; i < elements.length; ++i)
        {
            if (elements[i].style.display == 'none' || elements[i].style.display == '')
            {
                elements[i].style.display = 'table-row';
                hide = true;
            }
            else
                elements[i].style.display = 'none';
        }

        if (hide)
            BX("set_toggle_link_" + setParentId).innerHTML = '<?=Loc::getMessage("SOA_HIDE_SET")?>';
        else
            BX("set_toggle_link_" + setParentId).innerHTML = '<?=Loc::getMessage("SOA_SHOW_SET")?>';
    }
</script>
<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPage()?>?">
<?
$arFilterFieldsTmp=array(
  "filter_universal"=>Loc::getMessage("SOA_ROW_BUYER"),
  "filter_date_insert"=>Loc::getMessage("SALE_F_DATE"),
  "filter_date_update"=>Loc::getMessage("SALE_F_DATE_UPDATE"),
  "filter_id_from"=>Loc::getMessage("SALE_F_ID"),
  "filter_account_number"=>Loc::getMessage("SALE_F_ACCOUNT_NUMBER"),
  "filter_currency"=>Loc::getMessage("SALE_F_LANG_CUR"),
  "filter_price"=>Loc::getMessage("SOA_F_PRICE"),
  "filter_status"=>Loc::getMessage("SALE_F_STATUS"),
  "filter_date_status_from"=>Loc::getMessage("SALE_F_DATE_STATUS"),
  "filter_by_recommendation"=>Loc::getMessage("SALE_F_BY_RECOMMENDATION"),
  "filter_payed"=>Loc::getMessage("SALE_F_PAYED"),
  "filter_pay_system"=>Loc::getMessage("SALE_F_PAY_SYSTEM"),
  "filter_delivery_service"=>Loc::getMessage("SALE_F_DELIVERY_SERVICE"),
  "filter_person_type"=>Loc::getMessage("SALE_F_PERSON_TYPE"),
  "filter_canceled"=>Loc::getMessage("SALE_F_CANCELED"),
  "filter_deducted"=>Loc::getMessage("SALE_F_DEDUCTED"),
  "filter_allow_delivery"=>Loc::getMessage("SALE_F_ALLOW_DELIVERY"),
  "filter_date_paid"=>Loc::getMessage("SALE_F_DATE_PAID"),
  "filter_date_allow_delivery"=>Loc::getMessage("SALE_F_DATE_ALLOW_DELIVERY"),
  "filter_marked"=>Loc::getMessage("SALE_F_MARKED"),
  "filter_user_id"=>Loc::getMessage("SALE_F_USER_ID"),
  "filter_user_login"=>Loc::getMessage("SALE_F_USER_LOGIN"),
  "filter_user_email"=>Loc::getMessage("SALE_F_USER_EMAIL"),
  "filter_group_id"=>Loc::getMessage("SALE_F_USER_GROUP_ID"),
  "filter_product_id"=>Loc::getMessage("SO_PRODUCT_ID"),
  "filter_product_xml_id"=>Loc::getMessage("SO_PRODUCT_XML_ID"),
  "filter_affiliate_id"=>Loc::getMessage("SO_AFFILIATE_ID"),
  "filter_coupon"=>Loc::getMessage("SALE_ORDER_LIST_HEADER_NAME_COUPONS"),
  "filter_sum_paid"=>Loc::getMessage("SO_SUM_PAID"),
  "filter_xml_id"=>Loc::getMessage("SO_XML_ID"),
  "filter_tracking_number"=>Loc::getMessage("SOA_TRACKING_NUMBER"),
  "filter_source"=>Loc::getMessage("SALE_F_SOURCE")
);

$isManyPersonTypes=false;
$allOrderProps=($arOrderProps+$arOrderPropsCode);

$propsIndex=array();
$orderPropertyFilterList=array();
$orderPropertyFilterListTmp=array();
foreach($allOrderProps as $key=> $data){
    if($data["IS_FILTERED"]=="Y"&&$data["TYPE"]!="MULTIPLE"){
        if(!$isManyPersonTypes){
            if(array_key_exists($data['NAME'],$propsIndex)){
                if(count($propsIndex[$data['NAME']])>1){
                    $isManyPersonTypes=true;
                }elseif(!in_array($data['PERSON_TYPE_ID'],$propsIndex[$data['NAME']])){
                    $isManyPersonTypes=true;
                }
            }

            $propsIndex[$data['NAME']][]=$data['PERSON_TYPE_ID'];
        }

        $orderPropertyFilterListTmp[$data['LID']][$key]=$data;
    }
}

foreach($orderPropertyFilterListTmp as $propertyLid=> $propertyListData){
    if(!empty($propertyListData)&&is_array($propertyListData)){
        foreach($propertyListData as $key=> $propertyData){
            $orderPropertyFilterList[$key]=$propertyData;
            $arFilterFieldsTmp[]=$propertyData["NAME"].($isManyPersonTypes?" (".htmlspecialcharsbx($propertyData["PERSON_TYPE_NAME"]).") [".htmlspecialcharsbx($propertyData["LID"])."]":"");
        }
    }
}

$oFilter=new CAdminFilter(
  $sTableID."_filter",$arFilterFieldsTmp
);

$oFilter->SetDefaultRows(array("filter_universal","filter_status","filter_canceled"));

$oFilter->AddPreset(array(
  "ID"=>"find_prioritet",
  "NAME"=>Loc::getMessage("SOA_PRESET_PRIORITET"),
  "FIELDS"=>array(
    "filter_status"=>"N",
    "filter_price_from"=>"10000",
    "filter_price_to"=>""
  ),
  //"SORT_FIELD" => array("DATE_INSERT" => "DESC"),
));

$oFilter->AddPreset(array(
  "ID"=>"find_allow_payed",
  "NAME"=>Loc::getMessage("SOA_PRESET_PAYED"),
  "FIELDS"=>array(
    "filter_canceled"=>"N",
    "filter_payed"=>"Y"
  ),
  //"SORT_FIELD" => array("DATE_UPDATE" => "DESC"),
));

$oFilter->AddPreset(array(
  "ID"=>"find_order_null",
  "NAME"=>Loc::getMessage("SOA_PRESET_ORDER_NULL"),
  "FIELDS"=>array(
    "filter_canceled"=>"N",
    "filter_payed"=>"",
    "filter_status"=>array("N","P"),
    "filter_date_update_from_FILTER_PERIOD"=>"before",
    "filter_date_update_from_FILTER_DIRECTION"=>"previous",
    "filter_date_update_to"=>ConvertTimeStamp(AddToTimeStamp(Array("DD"=>-7))),
  ),
  //"SORT_FIELD" => array("DATE_UPDATE" => "DESC"),
));

$oFilter->Begin();
?>
    <tr>
        <td><?=Loc::getMessage('SOA_ROW_BUYER')?>:</td>
        <td>
            <input type="text" name="filter_universal" value="<?echo htmlspecialcharsbx($filter_universal)?>" size="40">
        </td>
    </tr>
    <tr>
        <td><b><?echo Loc::getMessage("SALE_F_DATE");?>:</b></td>
        <td>
    <?echo CalendarPeriod("filter_date_from",$filter_date_from,"filter_date_to",$filter_date_to,"find_form","Y")?>
        </td>
    </tr>
    <tr>
        <td><?echo Loc::getMessage("SALE_F_DATE_UPDATE");?>:</td>
        <td>
    <?echo CalendarPeriod("filter_date_update_from",$filter_date_update_from,"filter_date_update_to",$filter_date_update_to,"find_form","Y")?>
        </td>
    </tr>
    <tr>
        <td><?echo Loc::getMessage("SALE_F_ID");?>:</td>
        <td>
            <script type="text/javascript">
                function filter_id_from_Change()
                {
                    if (document.find_form.filter_id_to.value.length <= 0)
                    {
                        document.find_form.filter_id_to.value = document.find_form.filter_id_from.value;
                    }
                }
            </script>
    <?echo Loc::getMessage("SALE_F_FROM");?>
            <input type="text" name="filter_id_from" OnChange="filter_id_from_Change()" value="<?echo (IntVal($filter_id_from)>0)?IntVal($filter_id_from):""?>" size="10">
    <?echo Loc::getMessage("SALE_F_TO");?>
            <input type="text" name="filter_id_to" value="<?echo (IntVal($filter_id_to)>0)?IntVal($filter_id_to):""?>" size="10">
        </td>
    </tr>
    <tr>
        <td><?echo Loc::getMessage("SALE_F_ACCOUNT_NUMBER");?>:</td>
        <td>
            <input type="text" name="filter_account_number" value="<?echo htmlspecialcharsEx($filter_account_number)?>" size="10">
        </td>
    </tr>
    <tr>
        <td><?echo Loc::getMessage("SALE_F_LANG_CUR");?>:</td>
        <td>
            <select name="filter_lang">
                <option value=""><?=htmlspecialcharsex(Loc::getMessage("SALE_F_ALL"))?></option>
    <?
    $b1="SORT";
    $o1="ASC";
    $dbSitesList=CLang::GetList($b1,$o1);
    while($arSitesList=$dbSitesList->Fetch()){
        if(!in_array($arSitesList["LID"],$arAccessibleSites)&&$saleModulePermissions<"W")
            continue;
        ?><option value="<?=htmlspecialcharsbx($arSitesList["LID"])?>"<?if($arSitesList["LID"]==$filter_lang) echo " selected";?>>[<?=htmlspecialcharsex($arSitesList["LID"])?>]&nbsp;<?=htmlspecialcharsex($arSitesList["NAME"])?></option><?
    }
    ?>
            </select>
            /
    <?echo CCurrency::SelectBox("filter_currency",$filter_currency,Loc::getMessage("SALE_F_ALL"),false,"","");?>
        </td>
    </tr>
    <tr>
        <td><?=Loc::getMessage("SOA_F_PRICE");?>:</td>
        <td>
    <?echo Loc::getMessage("SOA_F_PRICE_FROM");?>
            <input type="text" name="filter_price_from" value="<?=(floatval($filter_price_from)>0)?floatval($filter_price_from):""?>" size="3">

    <?echo Loc::getMessage("SOA_F_PRICE_TO");?>
            <input type="text" name="filter_price_to" value="<?=(floatval($filter_price_to)>0)?floatval($filter_price_to):""?>" size="3">
        </td>
    </tr>
    <tr>
        <td valign="top"><?echo Loc::getMessage("SALE_F_STATUS")?>:<br /><img src="/bitrix/images/sale/mouse.gif" width="44" height="21" border="0" alt=""></td>
        <td valign="top">
            <select name="filter_status[]" multiple size="3">
    <?
    $statusesList=\Bitrix\Sale\OrderStatus::getStatusesUserCanDoOperations(
        $USER->GetID(),array('view')
    );

    $allStatusNames=\Bitrix\Sale\OrderStatus::getAllStatusesNames();

    foreach($statusesList as $statusCode){
        if(!$statusName=$allStatusNames[$statusCode])
            continue;
        ?><option value="<?=htmlspecialcharsbx($statusCode)?>"<?if(is_array($filter_status)&&in_array($statusCode,$filter_status)) echo " selected"?>>[<?=htmlspecialcharsbx($statusCode)?>] <?=htmlspecialcharsEx($statusName)?></option><?
    }
    ?>
            </select>
        </td>
    </tr>
    <tr>
        <td><?echo Loc::getMessage("SALE_F_DATE_STATUS");?>:</td>
        <td>
<?echo CalendarPeriod("filter_date_status_from",$filter_date_status_from,"filter_date_status_to",$filter_date_status_to,"find_form","Y")?>
        </td>
    </tr>
    <tr>
        <td><?echo Loc::getMessage("SALE_F_BY_RECOMMENDATION")?>:</td>
        <td>
            <select name="filter_by_recommendation">
                <option value=""><?echo GetMessage("SALE_F_ALL")?></option>
                <option value="Y"<?if($filter_by_recommendation=="Y") echo " selected"?>><?echo GetMessage("SALE_YES")?></option>
                <option value="N"<?if($filter_by_recommendation=="N") echo " selected"?>><?echo GetMessage("SALE_NO")?></option>
            </select>
        </td>
    </tr>
    <tr>
        <td><?echo Loc::getMessage("SALE_F_PAYED")?>:</td>
        <td>
            <select name="filter_payed">
                <option value=""><?echo Loc::getMessage("SALE_F_ALL")?></option>
                <option value="Y"<?if($filter_payed=="Y") echo " selected"?>><?echo Loc::getMessage("SALE_YES")?></option>
                <option value="N"<?if($filter_payed=="N") echo " selected"?>><?echo Loc::getMessage("SALE_NO")?></option>
            </select>
        </td>
    </tr>
    <tr>
        <td><?=Loc::getMessage("SALE_F_PAY_SYSTEM")?>:</td>
        <td>
<?
$ptRes=Sale\Internals\PersonTypeTable::getList(array(
    'order'=>array("SORT"=>"ASC","NAME"=>"ASC")
  ));

$personTypes=array();
while($personType=$ptRes->fetch())
    $personTypes[$personType['ID']]=$personType;
?>
            <select name="filter_pay_system[]" multiple size="3">
                <option value=""><?echo GetMessage("SALE_F_ALL")?></option>
                <?
                $res=\Bitrix\Sale\PaySystem\Manager::getList(array(
                    'select'=>array('ID','NAME'),
                    'filter'=>array('ACTIVE'=>'Y'),
                    'order'=>array("SORT"=>"ASC","NAME"=>"ASC")
                ));

                $paySystemList=array();
                while($paySystem=$res->fetch())
                    $paySystemList[$paySystem['ID']]['NAME']=$paySystem['NAME'];

                if($paySystemList):
                    $dbRestRes=Sale\Services\PaySystem\Restrictions\Manager::getList(array(
                        'select'=>array('SERVICE_ID','PARAMS'),
                        'filter'=>array(
                          '=CLASS_NAME'=>'\Bitrix\Sale\Services\PaySystem\Restrictions\PersonType',
                          'SERVICE_ID'=>array_keys($paySystemList)
                        )
                    ));

                    while($ptParams=$dbRestRes->fetch())
                        $paySystemList[$ptParams['SERVICE_ID']]['PERSON_TYPE_ID']=$ptParams['PARAMS']['PERSON_TYPE_ID'];

                    foreach($paySystemList as $psId=> $paySystem):
                        $personTypeString='';
                        if($paySystem['PERSON_TYPE_ID']){
                            $psPt=array();
                            foreach($paySystem['PERSON_TYPE_ID'] as $ptId)
                                $psPt[]=((strlen($personTypes[$ptId]['NAME'])>15)?substr($personTypes[$ptId]['NAME'],0,6)."...".substr($personTypes[$ptId]['NAME'],-7):$personTypes[$ptId]['NAME'])."/".$personTypes[$ptId]["LID"]."";
                            if($psPt)
                                $personTypeString=' ('.join(', ',$psPt).')';
                        }
                        ?><option title="<?echo htmlspecialcharsbx($paySystem["NAME"].$personTypeString);?>" value="<?echo htmlspecialcharsbx($psId)?>"<?if(is_array($filter_pay_system)&&in_array($psId,$filter_pay_system)) echo " selected"?>>[<?echo htmlspecialcharsbx($psId)?>] <?echo htmlspecialcharsbx($paySystem["NAME"].$personTypeString);?></option>
                    <?endforeach;?>
                <?endif;?>
            </select>
        </td>
    </tr>
    <tr>
        <td><?=Loc::getMessage("SALE_F_DELIVERY_SERVICE")?>:</td>
        <td>
            <select name="filter_delivery_service[]" multiple size="3">
                <option value=""><?echo GetMessage("SALE_F_ALL")?></option>
                <?
                $deliveryServiceParentListParent=array();
                $deliveryServiceList=array();

                $res=Sale\Delivery\Services\Table::getList(array(
                    'select'=>array('ID','NAME','PARENT_ID'),
                    'filter'=>array('ACTIVE'=>'Y'),
                    'order'=>array("SORT"=>"ASC","NAME"=>"ASC")
                ));
                while($deliveryService=$res->fetch()){
                    if(intval($deliveryService['PARENT_ID'])==0){
                        $deliveryServiceParentListParent[$deliveryService['ID']]=$deliveryService['NAME'];
                    }else{
                        $deliveryServiceListAll[$deliveryService['PARENT_ID']][$deliveryService['ID']]=$deliveryService['NAME'];
                    }
                }

                foreach($deliveryServiceParentListParent as $deliveryServiceParentId=> $deliveryServiceParentName){
                    if(!empty($deliveryServiceListAll[$deliveryServiceParentId])){
                        foreach($deliveryServiceListAll[$deliveryServiceParentId] as $deliveryServiceId=> $deliveryServiceName){
                            $deliveryServiceList[$deliveryServiceId]=$deliveryServiceParentName.":".$deliveryServiceName;
                        }
                    }else{
                        $deliveryServiceList[$deliveryServiceParentId]=$deliveryServiceParentName;
                    }
                }

                if(!empty($deliveryServiceList)){
                    foreach($deliveryServiceList as $deliveryServiceId=> $deliveryServiceName){
                        ?><option title="<?echo htmlspecialcharsbx($deliveryServiceName);?>" value="<?echo htmlspecialcharsbx($deliveryServiceId)?>"<?if(is_array($filter_delivery_service)&&in_array($deliveryServiceId,$filter_delivery_service)) echo " selected"?>>[<?echo htmlspecialcharsbx($deliveryServiceId)?>] <?echo htmlspecialcharsbx($deliveryServiceName);?></option><?
                    }
                }
                ?>
            </select>
        </td>
    </tr>
    <tr>
        <td><?echo Loc::getMessage("SALE_F_PERSON_TYPE");?>:</td>
        <td>
            <select name="filter_person_type[]" multiple size="3">
                <option value=""><?echo Loc::getMessage("SALE_F_ALL")?></option>
            <?
            foreach($personTypes as $personType):
                ?><option value="<?echo htmlspecialcharsbx($personType["ID"])?>"<?if(is_array($filter_person_type)&&in_array($personType["ID"],$filter_person_type)) echo " selected"?>>[<?echo htmlspecialcharsbx($personType["ID"])?>] <?echo htmlspecialcharsbx($personType["NAME"])?> <?echo "(".htmlspecialcharsbx($personType["LID"]).")";?></option><?
            endforeach;
            ?>
            </select>
        </td>
    </tr>
    <tr>
        <td><?echo Loc::getMessage("SALE_F_CANCELED")?>:</td>
        <td>
            <select name="filter_canceled">
                <option value=""><?echo Loc::getMessage("SALE_F_ALL")?></option>
                <option value="Y"<?if($filter_canceled=="Y") echo " selected"?>><?echo Loc::getMessage("SALE_YES")?></option>
                <option value="N"<?if($filter_canceled=="N") echo " selected"?>><?echo Loc::getMessage("SALE_NO")?></option>
            </select>
        </td>
    </tr>
    <tr>
        <td><?echo Loc::getMessage("SALE_F_DEDUCTED")?>:</td>
        <td>
            <select name="filter_deducted">
                <option value=""><?echo Loc::getMessage("SALE_F_ALL")?></option>
                <option value="Y"<?if($filter_deducted=="Y") echo " selected"?>><?echo Loc::getMessage("SALE_YES")?></option>
                <option value="N"<?if($filter_deducted=="N") echo " selected"?>><?echo Loc::getMessage("SALE_NO")?></option>
            </select>
        </td>
    </tr>
    <tr>
        <td><?echo Loc::getMessage("SALE_F_ALLOW_DELIVERY")?>:</td>
        <td>
            <select name="filter_allow_delivery">
                <option value=""><?echo Loc::getMessage("SALE_F_ALL")?></option>
                <option value="Y"<?if($filter_deducted=="Y") echo " selected"?>><?echo Loc::getMessage("SALE_YES")?></option>
                <option value="N"<?if($filter_deducted=="N") echo " selected"?>><?echo Loc::getMessage("SALE_NO")?></option>
            </select>
        </td>
    </tr>
    <tr>
        <td><?echo Loc::getMessage("SALE_F_DATE_PAID");?>:</td>
        <td>
                <?echo CalendarPeriod("filter_date_paid_from",$filter_date_paid_from,"filter_date_paid_to",$filter_date_paid_to,"find_form","Y")?>
        </td>
    </tr>
    <tr>
        <td><?echo Loc::getMessage("SALE_F_DATE_ALLOW_DELIVERY");?>:</td>
        <td>
<?echo CalendarPeriod("filter_date_allow_delivery_from",$filter_date_allow_delivery_from,"filter_date_allow_delivery_to",$filter_date_allow_delivery_to,"find_form","Y")?>
        </td>
    </tr>
    <tr>
        <td><?echo Loc::getMessage("SALE_F_MARKED")?>:</td>
        <td>
            <select name="filter_marked">
                <option value=""><?echo Loc::getMessage("SALE_F_ALL")?></option>
                <option value="Y"<?if($filter_marked=="Y") echo " selected"?>><?echo Loc::getMessage("SALE_YES")?></option>
                <option value="N"<?if($filter_marked=="N") echo " selected"?>><?echo Loc::getMessage("SALE_NO")?></option>
            </select>
        </td>
    </tr>
    <tr>
        <td><?echo Loc::getMessage("SALE_F_USER_LOGIN");?>:</td>
        <td>
            <input type="text" name="filter_user_login" value="<?echo htmlspecialcharsEx($filter_user_login)?>" size="40">
        </td>
    </tr>
    <tr>
        <td><?echo Loc::getMessage("SALE_F_USER_EMAIL");?>:</td>
        <td>
            <input type="text" name="filter_user_email" value="<?echo htmlspecialcharsEx($filter_user_email)?>" size="40">
        </td>
    </tr>
    <tr>
        <td><?echo Loc::getMessage("SALE_F_USER_GROUP_ID")?>:</td>
        <td>
                <?
                $z=CGroup::GetDropDownList("AND ID!=2");
                echo SelectBoxM("filter_group_id[]",$z,$filter_group_id,"",false,5);
                ?>
        </td>
    </tr>
    <tr>
        <td><?echo Loc::getMessage("SO_PRODUCT_ID")?></td>
        <td>
            <script type="text/javascript">
                function FillProductFields(arParams)
                {
                    if (arParams["id"])
                        document.find_form.filter_product_id.value = arParams["id"];

                    el = document.getElementById("product_name_alt");
                    if (el)
                        el.innerHTML = arParams["name"] ? arParams["name"] : '';
                }

                function showProductSearchDialog()
                {
                    var popup = makeProductSearchDialog({
                        caller: 'order',
                        lang: '<?=LANGUAGE_ID?>',
                        callback: 'FillProductFields'
                    });
                    popup.Show();
                }

                function makeProductSearchDialog(params)
                {
                    var caller = params.caller || '',
                            lang = params.lang || 'ru',
                            site_id = params.site_id || '',
                            callback = params.callback || '',
                            store_id = params.store_id || '0';

                    var popup = new BX.CDialog({
                        content_url: '/bitrix/admin/cat_product_search_dialog.php?lang=' + lang + '&LID=' + site_id + '&caller=' + caller + '&func_name=' + callback + '&STORE_FROM_ID=' + store_id,
                        height: Math.max(500, window.innerHeight - 400),
                        width: Math.max(800, window.innerWidth - 400),
                        draggable: true,
                        resizable: true,
                        min_height: 500,
                        min_width: 800
                    });
                    BX.addCustomEvent(popup, 'onWindowRegister', BX.defer(function () {
                        popup.Get().style.position = 'fixed';
                        popup.Get().style.top = (parseInt(popup.Get().style.top) - BX.GetWindowScrollPos().scrollTop) + 'px';
                    }));
                    return popup;
                }
            </script>
            <input name="filter_product_id" value="<?=htmlspecialcharsbx($filter_product_id)?>" size="5" type="text">&nbsp;<input type="button" value="..." id="cat_prod_button" onClick="showProductSearchDialog()"><span id="product_name_alt" class="adm-filter-text-search"></span>
        </td>
    </tr>
    <tr>
        <td><?=Loc::getMessage("SO_PRODUCT_XML_ID")?>:</td>
        <td><input name="filter_product_xml_id" value="<?=htmlspecialcharsbx($filter_product_xml_id)?>" size="40" type="text"></td>
    </tr>
    <tr>
        <td><?=Loc::getMessage("SALE_ORDER_LIST_HEADER_NAME_COUPONS")?>:</td>
        <td><input name="filter_discount_coupon" value="<?=htmlspecialcharsbx($filter_discount_coupon)?>" size="40" type="text"></td>
    </tr>
    <tr>
        <td><?=Loc::getMessage("SO_SUM_PAID")?>:</td>
        <td>
            <select name="filter_sum_paid">
                <option value=""><?echo Loc::getMessage("SALE_F_ALL")?></option>
                <option value="Y"<?if($filter_sum_paid=="Y") echo " selected"?>><?echo Loc::getMessage("SALE_YES")?></option>
                <option value="N"<?if($filter_sum_paid=="N") echo " selected"?>><?echo Loc::getMessage("SALE_NO")?></option>
            </select>
        </td>
    </tr>
    <tr>
        <td><?=Loc::getMessage('SO_XML_ID')?>:</td>
        <td>
            <input type="text" name="filter_xml_id" value="<?echo htmlspecialcharsbx($filter_xml_id)?>" size="40">
        </td>
    </tr>

    <tr>
        <td><?=Loc::getMessage('SOA_TRACKING_NUMBER')?>:</td>
        <td>
            <input type="text" name="filter_tracking_number" value="<?echo htmlspecialcharsbx($filter_tracking_number)?>" size="40">
        </td>
    </tr>
<?
$tPlatformList=array(
  0=>Loc::getMessage("SALE_F_ALL"),
  -1=>Loc::getMessage("SALE_F_NONE")
);

$dbRes=Bitrix\Sale\TradingPlatformTable::getList(array(
    'select'=>array('ID','NAME')
  ));

while($tPlatform=$dbRes->fetch())
    $tPlatformList[$tPlatform['ID']]=$tPlatform['NAME'];
?>
    <tr>
        <td><?=Loc::getMessage("SALE_F_SOURCE")?>:</td>
        <td>
            <select name="filter_source">
            <?foreach($tPlatformList as $id=> $name):?>
                    <option value="<?=$id?>"<?=$filter_source==$id?' selected':''?>><?=$name?></option>
            <?endforeach;?>
            </select>
        </td>
    </tr>

<?
foreach($orderPropertyFilterList as $key=> $value){
    if($value["IS_FILTERED"]=="Y"&&$value["TYPE"]!="MULTIPLE"){
        ?>
            <tr>
                <td valign="top"><?=$value["NAME"]?>:
        <?
        if($isManyPersonTypes){
            ?><small><?=(htmlspecialcharsbx($value["PERSON_TYPE_NAME"])." [".htmlspecialcharsbx($value["LID"])."]")?></small><?
        }
        ?></td>
                <td valign="top" style="overflow: visible; ">
        <?
        $inputParams=$value["SETTINGS"];
        $inputParams["TYPE"]=$value["TYPE"];
        $inputParams["IS_FILTER_FIELD"]=true;

        if($value["TYPE"]=="ENUM"){
            $inputParams["OPTIONS"]=array(""=>Loc::getMessage("SALE_F_ALL"));
            $inputParams["OPTIONS"]=$inputParams["OPTIONS"]+\Bitrix\Sale\PropertyValue::loadOptions($value["ID"]);
        }

        echo \Bitrix\Sale\Internals\Input\Manager::getFilterEditHtml(
          "filter_prop_".$key,$inputParams,${"filter_prop_".$key}
        );
        ?>
        <?=ShowFilterLogicHelp()?>
                </td>
            </tr>
        <?
    }
}

$oFilter->Buttons(
  array(
    "table_id"=>$sTableID,
    "url"=>$APPLICATION->GetCurPage(),
    "form"=>"find_form"
  )
);
$oFilter->End();
?>
</form>
<?
if(count($_orders)>0){
    $lOrders=new CAdminList('tbl_Shiptor_orders',false);
    $lOrders->AddHeaders(array(
      array("id"=>"ID","content"=>'№',"sort"=>"ID","default"=>true),
      array("id"=>"STATUS","content"=>GetMessage("Shiptor_ORDER_STATUS"),"sort"=>"STATUS","default"=>true),
    ));

    //TODO: make pagination
    //$from = ((!empty($_REQUEST['page']) ? $_REQUEST['page'] : 0) * 20) + 1;

    $statuses=array(
      0=>GetMessage('SHIPTOR_ORDER_STATUS_NOTSENT'),
      1=>GetMessage('SHIPTOR_ORDER_STATUS_SENT')
    );

    foreach(CShiptorDb::GetShiptorOrdersEx(false,false) as $order){
        $row=$lOrders->AddRow($order['order_id']);
        $row->AddField("ID",$order['order_id']);
        $row->AddField("STATUS",isset($statuses[$order['send']])?$statuses[$order['send']]:GetMessage('SHIPTOR_ORDER_STATUS_UNKNOWN'));
        $arActions=array();
        $arActions[]=array("TEXT"=>GetMessage("Shiptor_ORDER_MARK_SEND"),"ACTION"=>$lOrders->ActionRedirect($APPLICATION->GetCurPage().'?mid='.htmlspecialcharsbx($mid)."&SID=".$order['order_id']."&lang=".LANG."&action=send"),"DEFAULT"=>true);
        $arActions[]=array("TEXT"=>GetMessage("Shiptor_ORDER_MARK_RESEND"),"ACTION"=>$lOrders->ActionRedirect($APPLICATION->GetCurPage().'?mid='.htmlspecialcharsbx($mid)."&SID=".$order['order_id']."&lang=".LANG."&action=resend"),"DEFAULT"=>true);
        //$arActions[] = array("TEXT"=>GetMessage("Shiptor_ORDER_MARK_FORSESYNC"), "ACTION"=>$lOrders->ActionRedirect($APPLICATION->GetCurPage().'?mid='.htmlspecialcharsbx($mid)."&SID=".$order['order_id']."&lang=".LANG."&action=forsesync"), "DEFAULT"=>true);
        $arActions[]=array("TEXT"=>GetMessage("Shiptor_ORDER_MARK_ERROR"),"ACTION"=>$lOrders->ActionRedirect($APPLICATION->GetCurPage().'?mid='.htmlspecialcharsbx($mid)."&SID=".$order['order_id']."&lang=".LANG."&action=markerror"),"DEFAULT"=>true);
        //$arActions[] = array("TEXT"=>GetMessage("Shiptor_ORDER_" .($order['send'] > 0 ? "RE" : ""). "SEND"), "ACTION"=>$lOrders->ActionRedirect($APPLICATION->GetCurPage().'?mid='.htmlspecialcharsbx($mid)."&SID=".$order['order_id']."&lang=".LANG."&action=send"), "DEFAULT"=>true);
        $row->AddActions($arActions);
    }

    $lOrders->DisplayList();
}else{
    ?>
    <div class="adm-info-message-wrap">
        <div class="adm-info-message">
            <div><?=Loc::getMessage('SHIPTOR_NO_ORDERS')?></div>
        </div>
    </div>
        <?
    }
    ?>


    <?
    $lAdmin->DisplayList();

    echo BeginNote();
    ?>
<span id="order_sum"><?echo $order_sum;?></span>
    <?
    echo EndNote();

    require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
    ?>