<?
use Bitrix\Main\Application,
    Bitrix\Main\Loader,
    Bitrix\Main\Config\Option,
    Bitrix\Main\Localization\Loc,
    Bitrix\Sale\Internals\ShipmentTable,
    Bitrix\Sale\Delivery\Services\Shiptor,
    Bitrix\Sale\Delivery\Services\Shiptor\CShiptorAPI;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "U") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

Loader::includeModule('sale');
Loader::includeModule('currency');
Loader::IncludeModule("shiptor.delivery");

Loc::loadMessages(__FILE__);
Loc::loadMessages($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/sale/admin/order_shipment.php');

//--------------------------------------------
$SiptorAPI = CShiptorAPI::getInstance();
//--------------------------------------------

global $DB;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

$tableId = "shiptor_shipment_trecking";
$curPage = Application::getInstance()->getContext()->getCurrent()->getRequest()->getRequestUri();
$lang    = Application::getInstance()->getContext()->getLanguage();
$siteId  = Application::getInstance()->getContext()->getSite();
$errors = '';
$sAdmin = new CAdminSorting($tableId, "ORDER_ID", "DESC");
$lAdmin = new CAdminList($tableId, $sAdmin);

$filter = array(
	'filter_order_id_from',
	'filter_order_id_to',
	'filter_delivery_doc_num',
	'filter_price_delivery_from',
	'filter_price_delivery_to',
	'filter_account_num',
	'filter_shipment_id_from',
	'filter_shipment_id_to',
	'filter_user_id',
	'filter_user_login',
	'filter_user_email'
);

$lAdmin->InitFilter($filter);

$arFilter = array();

$filter_order_id_from = intval($filter_order_id_from);
$filter_order_id_to = intval($filter_order_id_to);

if (intval($filter_price_delivery_from) > 0)
	$arFilter['>=PRICE_DELIVERY'] = $filter_price_delivery_from;
if (intval($filter_price_delivery_to) > 0)
	$arFilter['<=PRICE_DELIVERY'] = $filter_price_delivery_to;

if (strlen($filter_delivery_doc_num) > 0)
	$arFilter['DELIVERY_DOC_NUM'] = $filter_deducted;

if ($filter_order_id_from > 0)
	$arFilter['>=ORDER_ID'] = $filter_order_id_from;
if ($filter_order_id_to > 0)
	$arFilter['<=ORDER_ID'] = $filter_order_id_to;

if ($filter_shipment_id_from > 0)
	$arFilter['>=ID'] = $filter_shipment_id_from;
if ($filter_shipment_id_to > 0)
	$arFilter['<=ID'] = $filter_shipment_id_to;

if (strlen($filter_account_num) > 0)
	$arFilter['ORDER.ACCOUNT_NUMBER'] = $filter_account_num;

if (strlen($filter_user_login)>0)
	$arFilter["ORDER.USER.LOGIN"] = trim($filter_user_login);

if (strlen($filter_user_email)>0)
	$arFilter["ORDER.USER.EMAIL"] = trim($filter_user_email);

if (IntVal($filter_user_id)>0)
	$arFilter["ORDER.USER_ID"] = IntVal($filter_user_id);

if($arID = $lAdmin->GroupAction())
{

	$shipments = array();

	$select = array( 'ID', 'ORDER_ID' );
	$filter['=STATUS.Bitrix\Sale\Internals\StatusLangTable:STATUS.LID'] = $lang;
/*
	$filter['=DELIVERY_NAME'] = 'Shiptor';
	$filter['=ALLOW_DELIVERY'] = 'Y';
	$filter['=DEDUCTED'] = 'Y';
*/
	$filter['=SYSTEM'] = 'N';

//	if($_REQUEST['shipment_id'] > 0) $filter['ID'] = $_REQUEST['shipment_id'];
	if($_REQUEST['action_target'] != 'selected') $filter['ID'] = $_REQUEST['ID'];

	$params = array(
		'select' => $select,
		'filter' => $filter,
		'limit' => 1000
	);

	$result = ShipmentTable::getList($params);

	while ($arResult = $result->fetch())
	{
		if (!isset($shipments[$arResult['ORDER_ID']]))
			$shipments[$arResult['ORDER_ID']] = array();
		$shipments[$arResult['ORDER_ID']][] = $arResult['ID'];
	}

#pre(array('Group'=>'Action','arID'=>$arID,'REQUEST'=>$_REQUEST,'params'=>$params,'shipments'=>$shipments));

	foreach ($shipments as $orderId => $ids)
	{
		$isDeleted = false;
		/** @var \Bitrix\Sale\Order $currentOrder */
		$currentOrder = \Bitrix\Sale\Order::load($orderId);
		if (!$currentOrder) continue;

		/** @var \Bitrix\Sale\ShipmentCollection $shipmentCollection */
		$shipmentCollection = $currentOrder->getShipmentCollection();

		foreach ($ids as $id)
		{
			if (strlen($id) <= 0) continue;

			/** @var \Bitrix\Sale\Shipment $shipment */
			$shipment = $shipmentCollection->getItemById($id);
			if (!$shipment) continue;

			switch ($_REQUEST['action'])
			{
				case "update":
					@set_time_limit(0);
					if($shipment->getField('XML_ID') > 1000){
						$arParams = array( "id" => $shipment->getField('XML_ID') );
						$arRequest = $SiptorAPI->Request("getPackage", $arParams);
						if($arRequest['result']['id'] == $shipment->getField('XML_ID')){
							$shipment->setField('TRACKING_DESCRIPTION', $arRequest['result']['status']);
							$res = $shipment->save();
							if ($res->isSuccess())
								$isGod = true;
							else
								$lAdmin->AddGroupError(implode('\n', $res->getErrorMessages()));
						}else{
							$lAdmin->AddGroupError('Не удалось получить статус посылки ID-'.$id.' №'.$shipment->getField('ACCOUNT_NUMBER').' '.$arRequest['error']['message']);
						}
					}else{
						$lAdmin->AddGroupError('У посылки ID-'.$id.' №'.$shipment->getField('ACCOUNT_NUMBER').' нет Трек-номера!');
					}
				break;
			}
		}
		if ($isDeleted)
		{
			$res = $currentOrder->save();
			if (!$res->isSuccess())
				$lAdmin->AddGroupError(implode('\n', $res->getErrorMessages()));
		}
	}
}

$headers = array(
	array("id" => "ID", "content" => "ID", "sort" => "ID", "default" => true),
	array("id" => "ORDER_ID", "content" => GetMessage("SALE_ORDER_ID"), "sort" => "ORDER_ID", "default" => true),
	array("id" => "ACCOUNT_NUMBER", "content" => GetMessage("SALE_ORDER_ACCOUNT_NUMBER"), "sort" => "ORDER.ACCOUNT_NUMBER", "default" => false),
	array("id" => "ORDER_USER_NAME", "content" => GetMessage("SALE_ORDER_USER_NAME"), "sort" => "ORDER_USER_NAME", "default" => true),
	array("id" => "STATUS", "content" => GetMessage("SALE_ORDER_STATUS"), "sort" => 'STATUS.ID', "default" => true),
	array("id" => "PRICE_DELIVERY", "content" => GetMessage("SALE_ORDER_PRICE_DELIVERY"), "sort" => "PRICE_DELIVERY", "default" => true),
	array("id" => "DELIVERY_DOC_NUM", "content" => GetMessage("SALE_ORDER_DELIVERY_DOC_NUM"), "sort"=> "DELIVERY_DOC_NUM", "default" => true),
	array("id" => "DELIVERY_DOC_DATE", "content" => GetMessage("SALE_ORDER_DELIVERY_DOC_DATE"), "sort"=> "DELIVERY_DOC_DATE", "default" => true),
	array("id" => "RESPONSIBLE_BY", "content" => GetMessage("SALE_ORDER_DELIVERY_RESPONSIBLE_ID"), "sort"=> "", "default" => true),
	array("id" => "TRACKING_NUMBER", "content" => GetMessage("SALE_ORDER_TRACKING_NUMBER"), "sort"=> "TRACKING_NUMBER", "default" => false),
#	array("id" => "TRACKING_STATUS", "content" => 'Статус посылки в Shiptor', "sort"=> "TRACKING_STATUS", "default" => false),
	array("id" => "TRACKING_DESCRIPTION", "content" => 'Статус посылки в Shiptor', "sort"=> "TRACKING_DESCRIPTION", "default" => false),
	array("id" => "XML_ID", "content" => "XML_ID", "sort"=> "XML_ID", "default" => false),
	array("id" => "PARAMETERS", "content" => GetMessage("SALE_ORDER_PARAMETERS"), "default" => false),
	array("id" => "CANCELED", "content" => GetMessage("SALE_ORDER_CANCELED"), "sort"=> "CANCELED", "default" => false),
	array("id" => "REASON_CANCELED", "content" => GetMessage("SALE_ORDER_REASON_CANCELED"), "default" => false),
	array("id" => "MARKED", "content" => GetMessage("SALE_ORDER_MARKED"), "sort"=> "MARKED", "default" => false),
	array("id" => "REASON_MARKED_ID", "content" => GetMessage("SALE_ORDER_REASON_MARKED_ID"), "default" => false),
);

$select = array(
	'*',
	'STATUS_NAME' => 'STATUS.Bitrix\Sale\Internals\StatusLangTable:STATUS.NAME',
	'ORDER.CURRENCY',
	'ORDER.ACCOUNT_NUMBER',
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
$arFilter['=DELIVERY_NAME'] = 'Shiptor';
$arFilter['=ALLOW_DELIVERY'] = 'Y';
$arFilter['!=SYSTEM'] = 'Y';

$params = array(
	'select' => $select,
	'filter' => $arFilter,
	'order'  => array($by => $order),
);

$usePageNavigation = true;
$navyParams = array();

$navyParams = CDBResult::GetNavParams(CAdminResult::GetNavSize($tableId));
if ($navyParams['SHOW_ALL'])
{
	$usePageNavigation = false;
}
else
{
	$navyParams['PAGEN'] = (int)$navyParams['PAGEN'];
	$navyParams['SIZEN'] = (int)$navyParams['SIZEN'];
}



if ($usePageNavigation)
{
	$params['limit'] = $navyParams['SIZEN'];
	$params['offset'] = $navyParams['SIZEN']*($navyParams['PAGEN']-1);
}

$totalPages = 0;

if ($usePageNavigation)
{
	$countQuery = new \Bitrix\Main\Entity\Query(ShipmentTable::getEntity());
	$countQuery->addSelect(new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(1)'));
	$countQuery->setFilter($params['filter']);
	$totalCount = $countQuery->setLimit(null)->setOffset(null)->exec()->fetch();
	unset($countQuery);
	$totalCount = (int)$totalCount['CNT'];

	if ($totalCount > 0)
	{
		$totalPages = ceil($totalCount/$navyParams['SIZEN']);

		if ($navyParams['PAGEN'] > $totalPages)
			$navyParams['PAGEN'] = $totalPages;

		$params['limit'] = $navyParams['SIZEN'];
		$params['offset'] = $navyParams['SIZEN']*($navyParams['PAGEN']-1);
	}
	else
	{
		$navyParams['PAGEN'] = 1;
		$params['limit'] = $navyParams['SIZEN'];
		$params['offset'] = 0;
	}
}

$dbResultList = new CAdminResult(ShipmentTable::getList($params), $tableId);

if ($usePageNavigation)
{
	$dbResultList->NavStart($params['limit'], $navyParams['SHOW_ALL'], $navyParams['PAGEN']);
	$dbResultList->NavRecordCount = $totalCount;
	$dbResultList->NavPageCount = $totalPages;
	$dbResultList->NavPageNomer = $navyParams['PAGEN'];
}
else
{
	$dbResultList->NavStart();
}


//$dbResultList = new CAdminResult($shipments, $tableId);
//$dbResultList->NavStart();
$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("group_admin_nav")));

$lAdmin->AddHeaders($headers);

$allSelectedFields = array(
	"ORDER_ID" => false,
	"PAID" => false,
	"DATE_PAID" => false
);

$visibleHeaders = $lAdmin->GetVisibleHeaderColumns();
$allSelectedFields = array_merge($allSelectedFields, array_fill_keys($visibleHeaders, true));

while ($shipment = $dbResultList->Fetch())
{
	$row =& $lAdmin->AddRow($shipment['ID'], $shipment);

	$row->AddField("ID", "<a href=\"sale_order_shipment_edit.php?order_id=".$shipment['ORDER_ID']."&shipment_id=".$shipment['ID']."&lang=".$lang.GetFilterParams("filter_")."\">".$shipment['ID']."</a>");

	$row->AddField("ORDER_ID", "<a href=\"sale_order_edit.php?ID=".$shipment['ORDER_ID']."&lang=".$lang.GetFilterParams("filter_")."\">".$shipment['ORDER_ID']."</a>");

	$row->AddField("ACCOUNT_NUMBER", htmlspecialcharsbx($shipment['SALE_INTERNALS_SHIPMENT_ORDER_ACCOUNT_NUMBER']));

	$row->AddField("ORDER_USER_NAME", "<a href='/bitrix/admin/user_edit.php?ID=".$shipment['ORDER_USER_ID']."&lang=".$lang."'>".htmlspecialcharsbx($shipment['ORDER_USER_NAME'])." ".htmlspecialcharsbx($shipment['ORDER_USER_LAST_NAME'])."</a>");

	$row->AddField("PRICE_DELIVERY", \CCurrencyLang::CurrencyFormat($shipment['PRICE_DELIVERY'], $shipment['SALE_INTERNALS_SHIPMENT_ORDER_CURRENCY']));

	$row->AddField("RESPONSIBLE_BY", "<a href=\"user_edit.php?ID=".$shipment['RESPONSIBLE_ID']."\">".htmlspecialcharsbx($shipment['RESPONSIBLE_BY_NAME'])." ".htmlspecialcharsbx($shipment['RESPONSIBLE_BY_LAST_NAME'])."</a>");

	$row->AddField("MARKED", (($shipment["MARKED"] == "Y") ? GetMessage("SHIPMENT_ORDER_YES") : GetMessage("SHIPMENT_ORDER_NO"))."<br><a href=\"user_edit.php?ID=".$shipment['EMP_MARKED_ID']."\">".htmlspecialcharsbx($shipment['EMP_MARKED_BY_LAST_NAME'])." ".htmlspecialcharsbx($shipment['EMP_MARKED_BY_NAME'])."</a><br>".htmlspecialcharsbx($shipment['DATE_MARKED']));

	$row->AddField("TRACKING_DESCRIPTION", htmlspecialcharsbx($shipment['TRACKING_DESCRIPTION']));
	$row->AddField("STATUS", htmlspecialcharsbx($shipment['STATUS_NAME']));

	$arActions = array();
	$arActions[] = array("ICON"=>"update", "TEXT"=>'Обновить статус посылки', "ACTION"=>$lAdmin->ActionRedirect("shiptor.delivery_tracking.php?action=update&shipment_id=".$shipment['ID']."&lang=".$lang.GetFilterParams("filter_").""), "DEFAULT"=>true);
	$arActions[] = array("ICON"=>"delete", "TEXT"=>'Удалить статус посылки', "ACTION"=>$lAdmin->ActionRedirect("shiptor.delivery_tracking.php?action=update&shipment_id=".$shipment['ID']."&lang=".$lang.GetFilterParams("filter_").""), "DEFAULT"=>true);

  $arActions[] = array(
    "ICON" => "rename",
    "TEXT" => "FILEMAN_RENAME_SAVE",
    "ACTION" => $lAdmin->ActionPost("?action=update&ID=".$shipment['ID']."&lang=".$lang.GetFilterParams("filter_")."")
  );

	$row->AddActions($arActions);
}

$lAdmin->AddGroupActionTable(
	array(
		"update" => 'Обновить статус посылки',
	)
);

$lAdmin->AddAdminContextMenu();

$lAdmin->AddFooter(
	array(
		array(
			"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $dbResultList->SelectedRowsCount()
		),
		array(
			"counter" => true,
			"title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
			"value" => "0"
		),
	)
);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage("SHIPTOR_TRACKING"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<form name="find_form" method="GET" action="<?=$curPage?>?">
<?
$filter = array(
	"filter_order_id_from"	=> GetMessage("PAYMENT_ORDER_ID"),
	"filter_order_paid"     => GetMessage("PAYMENT_ORDER_PAID"),
	"filter_date_paid"			=> GetMessage("PAYMENT_DATE_PAID"),
	"filter_account_num"		=> GetMessage("PAYMENT_ACCOUNT_NUM"),
	"filter_user_id"				=> GetMessage("SALE_SHIPMENT_F_USER_ID"),
	"filter_user_login"			=> GetMessage("SALE_SHIPMENT_F_USER_LOGIN"),
	"filter_user_email"			=> GetMessage("SALE_SHIPMENT_F_USER_EMAIL")
);

$oFilter = new CAdminFilter( $tableId."_filter", $filter );

$oFilter->Begin();
?>
<tr>
	<td><?=GetMessage("SHIPMENT_ORDER_ID");?>:</td>
	<td>
		<script type="text/javascript">
			function changeFilterOrderIdFrom()
			{
				if (document.find_form.filter_order_id_to.value.length<=0)
					document.find_form.filter_order_id_to.value = document.find_form.filter_order_id_from.value;
			}
		</script>
		<?=GetMessage("SHIPMENT_ORDER_ID_FROM");?>
		<input type="text" name="filter_order_id_from" OnChange="changeFilterOrderIdFrom()" value="<?=(intval($filter_order_id_from)>0)?intval($filter_order_id_from):""?>" size="10">
		<?=GetMessage("SHIPMENT_ORDER_ID_TO");?>
		<input type="text" name="filter_order_id_to" value="<?=(intval($filter_order_id_to)>0)?intval($filter_order_id_to):""?>" size="10">
	</td>
</tr>
<tr>
	<td><?=GetMessage("SHIPMENT_ID");?>:</td>
	<td>
		<script type="text/javascript">
			function changeFilterOrderIdFrom()
			{
				if (document.find_form.filter_shipment_id_to.value.length<=0)
					document.find_form.filter_shipment_id_to.value = document.find_form.filter_shipment_id_from.value;
			}
		</script>
		<?=GetMessage("SHIPMENT_ORDER_ID_FROM");?>
		<input type="text" name="filter_shipment_id_from" OnChange="changeFilterOrderIdFrom()" value="<?=(intval($filter_shipment_id_from) > 0) ? intval($filter_shipment_id_from) : ""?>" size="10">
		<?=GetMessage("SHIPMENT_ORDER_ID_TO");?>
		<input type="text" name="filter_shipment_id_to" value="<?=(intval($filter_shipment_id_to) > 0) ? intval($filter_shipment_id_to) : ""?>" size="10">
	</td>
</tr>
<tr>
	<td><?=GetMessage("SALE_ORDER_PRICE_DELIVERY");?>:</td>
	<td>
		<?echo GetMessage("PRICE_DELIVERY_FROM");?>
		<input type="text" name="filter_price_delivery_from" value="<?=($filter_price_delivery_from!=0) ? htmlspecialcharsbx($filter_price_delivery_from) : '';?>" size="3">

		<?echo GetMessage("PRICE_DELIVERY_TO");?>
		<input type="text" name="filter_price_delivery_to" value="<?=($filter_price_delivery_to!=0) ? htmlspecialcharsbx($filter_price_delivery_to) : '';?>" size="3">
	</td>
</tr>
<tr>
	<td><?=GetMessage("SALE_ORDER_DELIVERY_DOC_NUM");?>:</td>
	<td>
		<input type="text" name="filter_delivery_doc_num" value="<?=htmlspecialcharsbx($filter_delivery_doc_num);?>">
	</td>
</tr>
<tr>
	<td><?=GetMessage("SALE_ORDER_ACCOUNT_NUM");?>:</td>
	<td>
		<input type="text" name="filter_account_num" value="<?=htmlspecialcharsbx($filter_account_num)?>">
	</td>
</tr>
<tr>
	<td><?echo Loc::getMessage("SALE_SHIPMENT_F_USER_ID");?>:</td>
	<td>
		<?echo FindUserID("filter_user_id", $filter_user_id, "", "find_form");?>
	</td>
</tr>
<tr>
	<td><?echo Loc::getMessage("SALE_SHIPMENT_F_USER_LOGIN");?>:</td>
	<td>
		<input type="text" name="filter_user_login" value="<?echo htmlspecialcharsEx($filter_user_login)?>" size="40">
	</td>
</tr>
<tr>
	<td><?echo Loc::getMessage("SALE_SHIPMENT_F_USER_EMAIL");?>:</td>
	<td>
		<input type="text" name="filter_user_email" value="<?echo htmlspecialcharsEx($filter_user_email)?>" size="40">
	</td>
</tr>
<?
$oFilter->Buttons(
	array(
		"form" 			=> "find_form",
		"table_id"	=> $tableId,
		"url"				=> $curPage,
	)
);

$oFilter->End();
?>
</form>
<?
$lAdmin->DisplayList();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");