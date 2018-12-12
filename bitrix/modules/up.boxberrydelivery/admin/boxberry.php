<?
$ModuleID = "up.boxberrydelivery";
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php'); 
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$ModuleID.'/include.php');
 
$GLOBALS['APPLICATION']->IncludeComponent("bberry:boxberry.widget", "", array(),false);
IncludeModuleLangFile(__FILE__);
$POST_RIGHT = $APPLICATION->GetGroupRight($ModuleID);   
if ($POST_RIGHT <= "D")    
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED")); 


$sTableID = "tbl_boxberry_export";
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);
$arOrderProps = array();


$strAdminMessage = '';
$bBreak = FALSE;
if(($arID = $lAdmin->GroupAction()) && $POST_RIGHT=="W"){ 
	$bbObject = new CBoxberryParsel;
	foreach($arID as $ID){
			if(strlen($ID)<=0) continue;
	       	$ID = IntVal($ID);
	        switch($_REQUEST['action'])
	        {

		        case "exportOrder":
		        {
	        		$result = $bbObject->ParselCreate($ID);
	        		if($result["ERROR"])
	        		{	
	               		$lAdmin->AddGroupError($ID." > ".strip_tags($result["ERROR"]), $ID);
					}
					else
					{	
						$strAdminMessage .= $ID." > ".GetMessage("BB_TRACK_CODE").": ".$result["track"]."<br />";
					}
		            break;
				}
				
	        }
	        
	       	if($bBreak) 
				break;
	    }	
}

if(strlen($strAdminMessage) > 1) 
	CAdminMessage::ShowMessage(array("MESSAGE" => $strAdminMessage, "HTML"=>true, "TYPE" => "OK"));
	
$arHeaders = array(
    array(    
        "id"         =>"ID",
        "content"    =>"ID",
        "sort"       =>"id",
        "align"      =>"left",
        "default"    =>true,
    ),  
    array(    
        "id"         => "PVZ",
        "content"    => GetMessage("BB_FIELD_PVZ"),
        "default"    => false,
    ),
	array(    
        "id"         => "CITY_DELIVERY",
        "content"    => GetMessage("BB_CITY_DELIVERY"),
        "default"    => false,
    ), 
	array(    
        "id"         => "ADDRESS_DELIVERY",
        "content"    => GetMessage("BB_ADDRESS_DELIVERY"),
        "default"    => false,
    ),      
    array(    
        "id"         => "DATE_UPDATE",
        "content"    => GetMessage("BB_FIELD_DATE_UPDATE"),
        "sort"       => "date_update",
        "default"    => false,
    ),
    array(    
        "id"         => "PERSON_TYPE_ID",
        "content"    => GetMessage("BB_FIELD_PERSON_TYPE_ID"),
        "sort"       => "person_type_id",
        "default"    => false,
    ),    
    array(    
        "id"         => "STATUS_ID",
        "content"    => GetMessage("BB_FIELD_STATUS"),
        "sort"       => "status",
        "default"    => true,
    ),  
    array(    
        "id"         => "DELIVERY_ID",
        "content"    => GetMessage("BB_FIELD_DELIVERY"),
        "sort"       => "delivery",
        "default"    => false,
    ),       
    array(    
        "id"         => "PAYED",
        "content"    => GetMessage("BB_FIELD_PAYED"),
        "sort"       => "payed",
        "default"    => true,
    ),   
    array(    
        "id"         =>"CANCELED",
        "content"    => GetMessage("BB_FIELD_CANCELED"),
        "sort"       =>"canceled",
        "default"    => true,
    ),
    array(    
        "id"         =>"COMMENTS",
        "content"    => GetMessage("BB_FIELD_COMMENT"),
        "default"    => true,
    ),   
        
);

$lAdmin->AddHeaders($arHeaders);

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();
$bNeedProps = false;
foreach ($arVisibleColumns as $visibleColumn)
{
    if (!$bNeedProps && SubStr($visibleColumn, 0, StrLen("PROP_")) == "PROP_"){
        $bNeedProps = true;        
    }

    if(SubStr($visibleColumn, 0, StrLen("PROP_")) != "PROP_") {
        $arSelectFields[] = $visibleColumn;         
   	}
}

$arSelectFields[] = 'DATE_INSERT';
$arSelectFields[] = 'ID';
$arSelectFields[] = 'DELIVERY_ID';
$arSelectFields[] = 'USER_ID';
$arSelectFields[] = 'PERSON_TYPE_ID';

$allDeliverys = \Bitrix\Sale\Delivery\Services\Manager::getActiveList();
	foreach ($allDeliverys as $profile){	
		if (strpos($profile['CODE'],'boxberry')!==false && strpos($profile['CODE'],'KD')!==false){		
			$boxberry_profiles[] = $profile['ID'];
			$boxberry_profiles_kd[] = $profile['ID'];
			
		}elseif (strpos($profile['CODE'],'boxberry')!==false && strpos($profile['CODE'],'PVZ')!==false){		 
			$boxberry_profiles[] = $profile['ID'];
			$boxberry_profiles_pvz[] = $profile['ID'];
			
		}
	}
	function CheckFilter()
	{
		global $FilterArr, $lAdmin;
		foreach ($FilterArr as $f) global $$f;
		return count($lAdmin->arFilterErrors) == 0; //   ,  false;
	}
	$FilterArr = Array(
		"find_id_from",
		"find_id_to",
		"find_tracking_code",
		"find_date_insert_from",
		"find_date_insert_to",
		"find_payed",
		"find_canceled"  
	);
	$lAdmin->InitFilter($FilterArr);

	if (CheckFilter())
	{
		$arFilter = Array(
			">=ID"				=> $find_id_from,
			"<=ID"				=> $find_id_to,
			">=DATE_INSERT" 	=> $find_date_insert_from,
			"<=DATE_INSERT" 	=> $find_date_insert_to,
			"STATUS_ID" 		=> $find_status,
			"PAYED"            	=> $find_payed,
			"CANCELED"         	=> $find_canceled, 
			"DELIVERY_ID"		=> $boxberry_profiles
		);
	}	
	
	
	
	foreach($arFilter as $key => &$value)
	{
		if(empty($value)) 
		unset($arFilter[$key]);
	}


	$obOrder = CSaleOrder::GetList(
	   array($by => $order), 
	   $arFilter,
	   false,
	   array("nPageSize" => CAdminResult::GetNavSize($sTableID)),
	   $arSelectFields
	);
	
$rsData = new CAdminResult($obOrder, $sTableID); 
$rsData->NavStart(); 
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("BB_PAGING_TITLE")));




while($arRes = $rsData->NavNext(true, "f_"))
{		
	

		$arBbOrder = CBoxberryOrder::GetByOrderId($f_ID);					
		$row =&$lAdmin->AddRow($f_ID, $arRes);
		

		$IdField = '<a style="width:150px;display:block;" href="sale_order_view.php?ID='.$arRes["ID"].'&lang='.LANG.'" target="_blank"><b>'.$arRes["ID"].'</b></a>'.GetMessage("BB_FROM").' ' .$arRes["DATE_INSERT"];
		
		if(intval($arBbOrder["STATUS"]) > 0)
		{	
			$IdField .= '<br />'.GetMessage("BB_STATUS");
			
			switch ($arBbOrder["STATUS"])
			{
				case "1": 
					$IdField .= ' <span style="color:green">'. GetMessage("BB_SEND_TO_API"). '</span>'; 
					break;
				default:
					break;
			}
			
			$arStatusHistory = unserialize($arBbOrder["STATUS_HISTORY"]);
			if(count($arStatusHistory["SHOP"]) > 0 || count($arStatusHistory["BOXBERRY"]) > 0)
			{	
				global $DB;
				$IdField .= '<br />
							<a href="javascript:void(0);" onclick="openStatusBox(\'stat_history_'.$arBbOrder["ORDER_ID"].'\')">'
								.GetMessage("BB_STATUS_HISTORY").
							'</a>
							<div style="display:none; margin: 2px 0px 0px 10px;padding: 3px;border: 1px solid;" id="stat_history_'.$arBbOrder["ORDER_ID"].'">';
				
				$IdField .= '</div><div style="clear:both;"></div>';
			}
		}
		
		if($arBbOrder["CHECK_PDF_LINK"])
		{	if(count($arStatusHistory["SHOP"]) <= 0 && count($arStatusHistory["BOXBERRY"]) <= 0) $IdField .= '<br>';
			$IdField .= '<a target="_blank" href="'.$arBbOrder["CHECK_PDF_LINK"].'">'.GetMessage("BB_CHECK_PDF_LINK").'</a>';
		}

		if($arBbOrder["TRACKING_CODE"] && $arBbOrder["STATUS_TEXT"] != "DELETED")
		{
			$IdField .= '<br /> '.GetMessage("BB_TRACK_CODE").': '.$arBbOrder["TRACKING_CODE"];
		}
		$row->AddViewField("ID", $IdField);

		$status_res = CSaleStatus::GetByID($arRes["STATUS_ID"]);
		
		$row->AddViewField("STATUS_ID", '['.$status_res["ID"].'] '.$status_res["NAME"]);
		$row->AddViewField('PAYED', 	($f_PAYED == "Y" 	? GetMessage("BB_YES") : GetMessage("BB_NO")));
		$row->AddViewField('CANCELED',  ($f_CANCELED == "Y" ? GetMessage("BB_YES") : GetMessage("BB_NO")));
		
		$obProps = Bitrix\Sale\Internals\OrderPropsValueTable::getList(array('filter' => array('ORDER_ID' => $f_ID)));
		
		$address_prop_bb = COption::GetOptionString('up.boxberrydelivery', 'BB_ADDRESS');
		$location_prop_bb = COption::GetOptionString('up.boxberrydelivery', 'BB_LOCATION');
		while($prop = $obProps->Fetch()){
		   if ($prop["CODE"] == $location_prop_bb){
				$arLocs = CSaleLocation::GetByID($prop["VALUE"], LANGUAGE_ID);
				$row->AddViewField("CITY_DELIVERY",$arLocs["CITY_NAME"]);				
		   }
		   if ($prop["CODE"] == $address_prop_bb){
			   $row->AddViewField("ADDRESS_DELIVERY",$prop["VALUE"]);				
		   }
		}
		
		
		
		if(in_array('PVZ', $arVisibleColumns))
		{
			
			if (empty($arBbOrder["STATUS"]) && in_array($arRes["DELIVERY_ID"],$boxberry_profiles_pvz))
			{				
				
				if (empty($arBbOrder["PVZ_CODE"])){
					$arBbOrder["PVZ_CODE"] = GetMessage("BB_SELECT_PVZ_ON_WIDGET");
					$row->AddViewField('PVZ', '<a class="js-bxb-select-' . $arRes["ID"] . '" href="javascript:void(0);" onclick="selected_bxb_id='. $arRes["ID"] .'; boxberry.open(admin_delivery,\'\', \''.$arLocs["CITY_NAME"].'\');return false;" >'.$arBbOrder["PVZ_CODE"].'</a>');
				}else{
					$row->AddViewField('PVZ', '<a class="js-bxb-select-' . $arRes["ID"] . '" href="javascript:void(0);" onclick="selected_bxb_id='. $arRes["ID"] .'; boxberry.open(delivery,\'\', \''.$arLocs["CITY_NAME"].'\');return false;" >'.$arBbOrder["PVZ_CODE"].'</a>');
				}
			}else{
				$row->AddViewField("PVZ",$arBbOrder["PVZ_CODE"]);
			}
			
		}
		
				
		
		$arActions = array(
			"exportOrder" => array(
					"ICON" 		=> "edit",
					"TEXT" 		=> GetMessage("BB_ACTION_EXPORT"),
					"ACTION" 	=> $lAdmin->ActionDoGroup($f_ID, "exportOrder"),
			),

		);
		
		
		if($arBbOrder["STATUS_TEXT"] == "CREATED" || $arBbOrder["STATUS_TEXT"] == "SENT")
		{
			$arActions["exportOrder"]["DISABLED"]	 = true;
		}
		 $row->AddActions($arActions); 

	
}

$lAdmin->AddFooter(
    array(
        array("title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value" => $rsData->SelectedRowsCount()),
        array("counter" => true, "title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value" => "0"),
    )
);


$arActionsTable = Array(
    "exportOrder" 	=> GetMessage("BB_ACTION_EXPORT"),       
);

$arActionsParams = array("select_onchange" =>
	"if(this[this.selectedIndex].value == 'sendOrder' && !confirm('".GetMessage("BB_ACTION_DELETE_CONFIRM")."')){ 
		this.selectedIndex = 0;
	}");
$lAdmin->AddGroupActionTable($arActionsTable, $arActionsParams);
$lAdmin->AddAdminContextMenu(); 
$lAdmin->CheckListMode();
$APPLICATION->SetTitle(GetMessage('BB_PAGE_TITLE'));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

?>

<form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">

<? $arFilterOpts =   array(
	"ID",
	GetMessage("BB_FILTER_TRACKING_CODE"),
	GetMessage("BB_FILTER_DATE_INSERT"),
	GetMessage("BB_FIELD_STATUS"),
	GetMessage("BB_FIELD_PAYED"),
	GetMessage("BB_FIELD_CANCELED"),
);

$oFilter = new CAdminFilter(
	$sTableID."_filter",
	$arFilterOpts
);

$oFilter->Begin();
?>
<tr>
  <td nowrap><?="ID";?>:</td>
  <td nowrap>
    <?=GetMessage("BB_FROM_ALT");?><input type="text" name="find_id_from" size="20" value="<?echo htmlspecialchars($find_id_from)?>">
    <?=GetMessage("BB_TO_ALT");?><input type="text" name="find_id_to" size="20" value="<?echo htmlspecialchars($find_id_to)?>">
  </td>
</tr>
<tr>
    <td nowrap><?=GetMessage("BB_FILTER_DATE_INSERT")?>:</td>
    <td nowrap><?echo CalendarPeriod("find_date_insert_from", $find_date_insert_from, "find_date_insert_to", $find_date_insert_to, "find_form", "Y")?></td>
</tr>
<tr>
    <td valign="top"><?echo GetMessage("BB_FIELD_STATUS")?>:<br /><img src="/bitrix/images/sale/mouse.gif" width="44" height="21" border="0" alt=""></td>
    <td valign="top">
	    <select name="find_status[]" multiple size="4">
	    	<option <?if(!$find_status) echo "selected";?>>(<?=strtolower(GetMessage("BB_NO"));?>)</option>
	    <?
	        $dbStatusListFillter = array("LID" => LANGUAGE_ID);
	        if($StatusExclude){
	            $dbStatusListFillter["!ID"] = $StatusExclude;            
	        }
	        $dbStatusList = CSaleStatus::GetList(
	            array("SORT" => "ASC"),
	            $dbStatusListFillter,
	            false,
	            false,
	            array("ID", "NAME", "SORT")
	        );
	        while ($arStatusList = $dbStatusList->Fetch())
	        {
	        ?><option value="<?= htmlspecialchars($arStatusList["ID"]) ?>"<?if (is_array($find_status) && in_array($arStatusList["ID"], $find_status)) echo " selected"?>>[<?= htmlspecialchars($arStatusList["ID"]) ?>] <?= htmlspecialcharsEx($arStatusList["NAME"]) ?></option><?
	        }
	    ?>
	    </select>
	</td>
</tr>

<tr>
    <td><?echo GetMessage("BB_FIELD_PAYED");?>:</td>
    <td>
        <select name="find_payed">
            <option value=""><?echo GetMessage("BB_ALL")?></option>
            <option value="Y"<?if ($filter_payed=="Y") echo " selected"?>><?echo GetMessage("BB_YES")?></option>
            <option value="N"<?if ($filter_payed=="N") echo " selected"?>><?echo GetMessage("BB_NO")?></option>
        </select>
    </td>
</tr>
<tr>
    <td><?echo GetMessage("BB_FIELD_CANCELED")?>:</td>
    <td>
        <select name="find_canceled">
            <option value=""><?echo GetMessage("BB_ALL")?></option>
            <option value="Y"<?if ($find_canceled=="Y") echo " selected"?>><?echo GetMessage("BB_YES")?></option>
            <option value="N"<?if ($find_canceled=="N") echo " selected"?>><?echo GetMessage("BB_NO")?></option>
        </select>
    </td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"find_form"));
$oFilter->End();
?>
</form>

<script type="text/javascript">
var selected_bxb_id = null;
openStatusBox = function (val){
	var block = document.getElementById(val);
	block.style['display'] = (block.style['display'] == 'none' ? 'block' : 'none');
}
</script>

<?$lAdmin->DisplayList();?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>