<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
use \Bitrix\Main\Application,
	Bitrix\Main\Page\Asset,
	Bitrix\Main\Request,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\Internals;
	Loc::loadMessages(__FILE__);
$module_id='skyweb24.popuppro';
\Bitrix\Main\Loader::includeModule($module_id);
\Bitrix\Main\Loader::includeModule('iblock');
//head
CJSCore::Init(array("jquery", "ajax", "fx",'drag_drop', 'color_picker'));
if(\Bitrix\Main\Loader::includeModule('catalog')){
	CJSCore::Init(array('core_condtree'));
}else{
	CJSCore::Init(array('core','date','window'));
	Asset::getInstance()->addJs('/bitrix/js/'.$module_id.'/core_tree.js');
	Asset::getInstance()->addString("<link href='/bitrix/themes/.default/".$module_id."/catalog_cond.css' rel='stylesheet' type='text/css'>");
 	Asset::getInstance()->addString("<script type='text/javascript'>(window.BX||top.BX).message({'JC_CORE_TREE_SELECT_CONTROL':'".GetMessage('skyweb24.JC_CORE_TREE_SELECT_CONTROL')."','JC_CORE_TREE_ADD_CONTROL':'".GetMessage('skyweb24.JC_CORE_TREE_ADD_CONTROL')."','JC_CORE_TREE_DELETE_CONTROL':'".GetMessage('skyweb24.JC_CORE_TREE_DELETE_CONTROL')."','JC_CORE_TREE_CONTROL_DATETIME_ICON':'".GetMessage('skyweb24.JC_CORE_TREE_CONTROL_DATETIME_ICON')."','JC_CORE_TREE_CONDITION_ERROR':'".GetMessage('skyweb24.JC_CORE_TREE_CONDITION_ERROR')."','JC_CORE_TREE_CONDITION_FATAL_ERROR':'".GetMessage('skyweb24.popuppro_IMG_BLOCK_DELIMG')."'});</script>");	
}

Asset::getInstance()->addJs('/bitrix/js/'.$module_id.'/script.js');
//include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/default_option.php");
Asset::getInstance()->addJs('/bitrix/js/fileman/block_editor/dialog.js');

$context = Application::getInstance()->getContext();
$request = $context->getRequest();

function imageBusy($popupArr, $img){
	$impPropArr=array('IMG_1_SRC','IMG_2_SRC','IMG_3_SRC','IMG_4_SRC','IMG_5_SRC');
	foreach($popupArr as $keyPopup=>$valPopup){
		foreach($impPropArr as $nextProp){
			if(
				!empty($valPopup['view']['props'][$nextProp])
				&& (
					$valPopup['view']['props'][$nextProp]==$img['id']
					|| $valPopup['view']['props'][$nextProp]==$img['path']
				)
			){
				return $keyPopup;
			}
		}
	}
	return false;
}
if(!empty($request['action_button'])&&$request['action_button']=='copy'&&!empty($request['ID'])){
	$editableWindow=new popupproEdit();
	$newIdPopup=$editableWindow->CopyPopup($request['ID']);
	global $APPLICATION;
	$CURRENT_PAGE = (CMain::IsHTTPS()) ? "https://" : "http://";
	$CURRENT_PAGE .= $_SERVER["HTTP_HOST"];
	header("Location: ".$CURRENT_PAGE."/bitrix/admin/skyweb24_popuppro.php?id=".$newIdPopup);
}
//ajax operations
if(!empty($request['ajax']) && $request['ajax']=='y'){
	if(!empty($request['command'])){
		if($request['command']=='gettemplate'){
			global $APPLICATION;
			$APPLICATION->IncludeComponent(
				"skyweb24:popup.pro",
				$request['template'],
				Array(
					"MODE" => "TEMPLATE",
					"ID_POPUP" => "NEW"
				)
			);
		}elseif($request['command']=='gettemplatepath'){
			global $APPLICATION;
			$APPLICATION->IncludeComponent(
				"skyweb24:popup.pro",
				$request['template'],
				Array(
					"MODE" => "GET_PATH",
					"ID_POPUP" => "NEW"
				)
			);
		}elseif($request['command']=='gettimertemplate') {
			global $APPLICATION;
			$APPLICATION->IncludeComponent(
				"skyweb24:popup.pro.timer", ''
			);
		}elseif($request['command']=='get_img'){
			$editableWindow=new popupproEdit();
			$allSettings=$editableWindow->getAllPopups();
			$firstQueue=$lastQueue='';
			$res = CFile::GetList(array("ID"=>"desc"), array("MODULE_ID"=>$module_id));
			while($res_arr = $res->GetNext()){
				$keyPopup=imageBusy($allSettings, array('id'=>$res_arr['ID'], 'path'=>'/upload/'.$res_arr['SUBDIR'].'/'.$res_arr['FILE_NAME']));
				$deleteLink='<a href="javascript:void(0);" onclick="delPopupImg(this);" class="del_img" title="'.GetMessage("skyweb24.popuppro_IMG_BLOCK_DELIMG").'" data-id="'.$res_arr['ID'].'">&nbsp;</a>';
				$existKey='';
				if($keyPopup!==false){
					$deleteLink='';
					$existKey='popup ['.$keyPopup.']';
				}
				$tmpFigure= '<figure>'.
						$deleteLink
						.'<img title="'.GetMessage("skyweb24.popuppro_IMG_BLOCK_ALTIMG").'" alt="'.GetMessage("skyweb24.popuppro_IMG_BLOCK_ALTIMG").'" data-id="'.$res_arr['ID'].'" src="/upload/'.$res_arr['SUBDIR'].'/'.$res_arr['FILE_NAME'].'" />
						<figcaption>'.$existKey.'</figcaption>
					</figure>';
				if($keyPopup!==false){
					$firstQueue.=$tmpFigure;
				}else{
					$lastQueue.=$tmpFigure;
				}
			}
			echo $firstQueue.$lastQueue.'<a href="javascript:void(0);" onclick="showHideImgs(\'show_all\')" class="show_all">'.GetMessage("skyweb24.popuppro_IMG_SHOWALL").'</a><a href="javascript:void(0);" onclick="showHideImgs(\'hide_all\')" class="hide_all">'.GetMessage("skyweb24.popuppro_IMG_HIDEALL").'</a>';
		}elseif($request['command']=='del_img'){
			CFile::Delete($request['id']);
			echo json_encode('success');
		}elseif($request['command']=='add_custom_colortheme'){
			$editableWindow=new popupproEdit();
			$dataStatus=true;
			$requiredVal=array('template', 'color_style', 'name', 'type');
			foreach($requiredVal as $nextVal){
				if(empty($request[$nextVal])){
					$dataStatus=false;
					break;
				}
			}
			if($dataStatus){
				$retArr=$editableWindow->setColorTheme($request['type'], $request['template'], $request['color_style'], $request['name']);
			}else{
				$retArr=array('status'=>'do not data');
			}
			echo json_encode($retArr);
		}elseif($request['command']=='add_custom_template'){
			$editableWindow=new popupproEdit();
			$dataStatus=true;
			$requiredVal=array('template', 'name', 'type');
			foreach($requiredVal as $nextVal){
				if(empty($request[$nextVal])){
					$dataStatus=false;
					break;
				}
			}
			if($dataStatus){
				$retArr=$editableWindow->setTemplate($request['type'], $request['template'], $request['name']);
				if($retArr===false){
					$retArr=array('status'=>'error copy');
				}
			}else{
				$retArr=array('status'=>'do not data');
			}
			echo json_encode($retArr);
		}
	}
	die();
}

$editableWindow=new popupproEdit();

if(!empty($request['id'])){
	$idPopup=$request['id'];
	$APPLICATION->SetTitle(GetMessage("skyweb24.popuppro_MAIN_TITLE"));
}else{
	$APPLICATION->SetTitle(GetMessage("skyweb24.popuppro_LIST_TITLE"));

	$sTableID = $editableWindow->getTableSetting();
	$oSort = new CAdminSorting($sTableID, "ID", "desc");
	$lAdmin = new CAdminList($sTableID, $oSort);

	if($lAdmin->EditAction()){
		foreach($FIELDS as $ID=>$arFields){
			if(!$lAdmin->IsUpdated($ID))
				continue;
			$editableWindow->editFromTableList($ID, $arFields);
        }
	}

	if($arID = $lAdmin->GroupAction()){
		if($_REQUEST['action_target']=='selected'){
			$rsData = $DB->Query('SELECT * FROM '.$sTableID.';', false, $err_mess.__LINE__);
			while($arRes = $rsData->Fetch()){
				$arID[] = $arRes['id'];
			}
		}
		foreach($arID as $ID){
			if(strlen($ID)<=0)
				continue;
			$ID = IntVal($ID);
			switch($_REQUEST['action']){
				case "delete":
					$DB->Query('DELETE FROM '.$sTableID.' WHERE id='.$ID.';', false, $err_mess.__LINE__);
					break;
				case "activate":
				case "deactivate":
					$cData = $DB->Query('SELECT * FROM '.$sTableID.' WHERE id='.$ID.';', false, $err_mess.__LINE__);
					if($arFields = $cData->Fetch()){
						$arFields["active"]=($_REQUEST['action']=="activate"?"Y":"N");
						$tmpSetting=unserialize($arFields['settings']);
						$tmpSetting['condition']['active']=$arFields['active'];
						$DB->Query('UPDATE '.$sTableID.' SET active="'.$arFields["active"].'", settings=\''.$DB->ForSql(serialize($tmpSetting)).'\' WHERE id='.$ID.';', false, $err_mess.__LINE__);
					}else
						$lAdmin->AddGroupError(GetMessage("skyweb24.popuppro_SAVE_ERROR")." ".GetMessage("skyweb24.popuppro_POPUP_EMPTY"), $ID);
					break;
			}
		}
	}

	$rsData = $DB->Query('SELECT * FROM '.$sTableID.' order by '.$by.' '.$order.';', false, $err_mess.__LINE__);
	$rsData->NavStart(CAdminResult::GetNavSize());
	$rsData = new CAdminResult($rsData, $sTableID);
	$lAdmin->NavText($rsData->GetNavPrint(GetMessage("skyweb24.popuppro_TABLELIST_PAGINATOR")));
	$lAdmin->AddHeaders(array(
	  array("id"    =>"ID",
		"content"  =>"ID",
		"sort"     =>"id",
		"default"  =>true,
	  ),
	  array("id"    =>"SORT",
		"content"  =>GetMessage("skyweb24.popuppro_TABLELIST_SORT"),
		"sort"     =>"sort",
		"default"  =>true,
	  ),
	  array("id"    =>"active",
		"content"  =>GetMessage("skyweb24.popuppro_TABLELIST_ACTIVE"),
		"sort"     =>"active",
		"default"  =>true,
	  ),
	  array("id"    =>"NAME",
		"content"  =>GetMessage("skyweb24.popuppro_TABLELIST_NAME"),
		"sort"     =>"name",
		"default"  =>true,
	  ),
	  array("id"    =>"TYPE",
		"content"  =>GetMessage("skyweb24.popuppro_TABLELIST_TYPE"),
		"sort"     =>"type",
		"default"  =>true,
	  ),
	  array("id"    =>"STAT_SHOW",
		"content"  =>GetMessage("skyweb24.popuppro_TABLELIST_STAT_SHOW"),
		"sort"     =>"stat_show",
		"default"  =>true,
	  ),
	  array("id"    =>"STAT_TIME",
		"content"  =>GetMessage("skyweb24.popuppro_TABLELIST_STAT_TIME"),
		"sort"     =>"stat_time",
		"default"  =>true,
	  ),
	  array("id"    =>"STAT_ACTION",
		"content"  =>GetMessage("skyweb24.popuppro_TABLELIST_STAT_ACTION"),
		"sort"     =>"stat_action",
		"default"  =>true,
	  )
	));

	$typesArr=$editableWindow->getTypes();
	while($arRes = $rsData->NavNext(true, "f_")){
		$row =$lAdmin->AddRow($f_id, $arRes);
		$row->AddViewField("ID", '<a href="./skyweb24_popuppro.php?lang='.SITE_ID.'&id='.$f_id.'">'.$f_id.'</a>');
		$row->AddViewField("SORT", $f_sort);
		$row->AddInputField("SORT", array("size"=>10, 'value'=>$f_sort));
		$row->AddCheckField("active");
		$row->AddInputField("NAME", array("size"=>15, 'value'=>$f_name));
		$row->AddViewField("NAME", '<a href="./skyweb24_popuppro.php?lang='.SITE_ID.'&id='.$f_id.'">'.$f_name.'</a>');
		$f_type=(empty($typesArr[$f_type]))?$f_type:$typesArr[$f_type]['name'];
		$row->AddViewField("TYPE", $f_type);
		$row->AddViewField("STAT_SHOW", number_format((double)$f_stat_show,0,'.',' '));
		$row->AddViewField("STAT_TIME", popuppro::convertTimeFromSecond($f_stat_time));
		$row->AddViewField("STAT_ACTION", number_format((double)$f_stat_action,0,'.',' '));
		$f_settings=unserialize($arRes['settings']);

		$arActions = Array();
		$arActions[] = array(
			"ICON"=>"edit",
			"DEFAULT"=>true,
			"TEXT"=>GetMessage("skyweb24.popuppro_TABLE_EDIT"),
			"ACTION"=>$lAdmin->ActionRedirect("./skyweb24_popuppro.php?id=".$f_id)
		);
		$arActions[]=array(
			"ICON"=>"copy",
			"TEXT"=>GetMessage("skyweb24.popuppro_TABLE_COPY"),
			"ACTION"=>"if(confirm('".GetMessage('skyweb24.popuppro_TABLE_COPY_CONFIRM')."')) ".$lAdmin->ActionRedirect("skyweb24_popuppro.php?action_button=copy&ID=".$f_id)
		);
		$arActions[] = array(
			"ICON"=>"delete",
			"TEXT"=>GetMessage("skyweb24.popuppro_TABLE_DELETE"),
			"ACTION"=>"if(confirm('".GetMessage('skyweb24.popuppro_TABLE_DELETE_CONFIRM')."')) ".$lAdmin->ActionDoGroup($f_id, "delete")
		);

		$row->AddActions($arActions);
	}

	$lAdmin->AddFooter(
	  array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()), // кол-во элементов
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"), // счетчик выбранных элементов
	  )
	);
	$lAdmin->AddGroupActionTable(Array(
		"delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"),
		"activate"=>GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate"=>GetMessage("MAIN_ADMIN_LIST_DEACTIVATE")
	));

	$aContext = array(
		array(
			"TEXT"=>GetMessage("skyweb24.popuppro_LIST_CREATE_NEW_POPUP"),
			"LINK"=>"skyweb24_popuppro.php?lang=".LANG."&id=new",
			"TITLE"=>GetMessage("skyweb24.popuppro_LIST_CREATE_NEW_POPUP"),
			"ICON"=>"btn_new",
		)
	);
	$lAdmin->AddAdminContextMenu($aContext);

	$lAdmin->CheckListMode();
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
$APPLICATION->IncludeFile("/bitrix/modules/".$module_id."/include/headerInfo.php", Array());
if(\Bitrix\Main\Loader::includeModule($module_id)){
$APPLICATION->SetAdditionalCSS("/bitrix/js/fileman/block_editor/dialog.css");
//create or edit popup
$editFlag = $request->getPost("id_popup");
if(!empty($editFlag)){
	$editableWindow->setPopupId($editFlag);
	$upd=$editableWindow->editPopup($request);
	if($upd['status']=='error'){
		CAdminMessage::ShowMessage(Array("TYPE"=>"ERROR", "MESSAGE"=>GetMessage("skyweb24.popuppro_ERROR_SAVE").':'.$upd['data']));
	}else{
		if($idPopup=='new'){
			$tmpMess=GetMessage("skyweb24.popuppro_SUCCESS_ADD", array('ID'=>$upd['data']));
			$idPopup=$upd['data'];
		}else{
			$tmpMess=GetMessage("skyweb24.popuppro_SUCCESS_UPDATE", array('ID'=>$upd['data']));
		}
		CAdminMessage::ShowMessage(Array("TYPE"=>"OK", "MESSAGE"=>$tmpMess));
	}
}

//consent out Message
$consentOutMessage = new CAdminMessage(array(
		'TYPE'=>'ERROR',
		'MESSAGE'=>GetMessage("skyweb24.popuppro_CONSENT_OUT"),
		"HTML"=>true
	));

$tmpConsent=$editableWindow->getConsentList();
if(empty($idPopup)){
	$lAdmin->DisplayList();
	if(count($tmpConsent)==0){
		echo $consentOutMessage->Show();
	}
}else{
	$editableWindow->setPopupId($idPopup);
	if(count($tmpConsent)==0){
		echo $consentOutMessage->Show();
	}
	
	?>
	<script>
		(window.BX||top.BX).message({'JSADM_FILES':'<?=GetMessage("skyweb24.popuppro_JSADM_FILES")?>'});
	</script>
	<section class="popuppro_detail"><form action="" enctype="multipart/form-data" method="post" name="detail_popup">
		<input type="hidden" name="id_popup" value="<?=$idPopup?>" /><?
	$aTabs = array(
		array("DIV" => "sw24_popup_settings".$nextPopup["id"], "TAB" => GetMessage("skyweb24.popuppro_TAB_SETTING_NAME"), "TITLE" => GetMessage("skyweb24.popuppro_TAB_SETTING_DESC"), "ONSELECT"=>'selectPreviewTab()'),
		array("DIV" => "sw24_popup_condition".$nextPopup["id"], "TAB" => GetMessage("skyweb24.popuppro_TAB_CONDITION_NAME"), "TITLE" => GetMessage("skyweb24.popuppro_TAB_CONDITION_DESC")),
		/*array("DIV" => "sw24_popup_contact".$nextPopup["id"], "TAB" => GetMessage("skyweb24.popuppro_TAB_CONDITION_CONTACT"), "TITLE" => GetMessage("skyweb24.popuppro_TAB_CONTACT_DESC"), "ONSELECT"=>'selectContactTab()')*/
	);
	$tabControl = new CAdminTabControl("tabControl".$nextPopup["id"], $aTabs);
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	$types=$editableWindow->getTypes();
	$templates=$editableWindow->getTemplates();
	?>
	<tr>
		<td>
			<h3><?=GetMessage("skyweb24.popuppro_VIEWS_STEP_1")?></h3>
			<div class="slide_type">
			<?
			$formType='';
			foreach($types as $nextType){
				$activeClass='';
				if(!empty($nextType['active']) && $nextType['active']==true){
					$activeClass=' active';
					$formType=$nextType['code'];
				}?>

				<a href="javascript:void(0);" data-id="<?=$nextType['code']?>" data-target="<?=$nextType['target']?>" data-description="<?=$nextType['description']?>" class="slide<?=$activeClass?>" title="<?=$nextType['name']?>">
					<h4><?=$nextType['name']?></h4>
					<img src="/bitrix/themes/.default/<?=$module_id?>/types/<?=$nextType['code']?>.jpg" alt="<?=$nextType['name']?>" />
				</a>
			<?}?>
			</div>
			<input type="hidden" name="type" value="<?=$formType?>" />
			<div class="window_description">
				<p><b><?=GetMessage("skyweb24.popuppro_TYPE_DESCRIPTION")?>:</b> <span id="subslider_desc"><?=GetMessage("skyweb24.popuppro_TYPE_NAME_BANNER_DESCRIPTION")?></span></p>
				<p><b><?=GetMessage("skyweb24.popuppro_TYPE_TARGET")?>:</b> <span id="subslider_target"><?=GetMessage("skyweb24.popuppro_TYPE_NAME_BANNER_TARGET")?></span></p>
			</div>
			<h3><?=GetMessage("skyweb24.popuppro_VIEWS_STEP_2")?></h3>
			<div class="select_block">
				<header>
					<div id="templates_list"></div>
					<div id="edit_view"></div>
				</header>
				<h3><?=GetMessage("skyweb24.popuppro_VIEWS_STEP_DEMO")?></h3>
				<section class="preview">
				<?/*--new preview--*/?>
					<div id="detail_template_area_outer">
						<div class="bx-editor-block-panel preview-panel" style="">
							<div class="bx-block-editor-preview-container" style="display: block;">
								<div class="shadow">
									<div class="edit-text"></div>
									<div class="error-text"></div>
								</div>
								<div class="devices">
									<div class="device phone" data-bx-preview-device-class="phone" data-bx-preview-device-width="337" data-bx-preview-device-height="480">
										<span><?=GetMessage("skyweb24.popuppro_PREVIEW_PHONE")?></span>
									</div>
									<div class="device tablet" data-bx-preview-device-class="tablet" data-bx-preview-device-width="537" data-bx-preview-device-height="716">
										<span><?=GetMessage("skyweb24.popuppro_PREVIEW_TABLET")?></span>
									</div>
									<div class="device desktop" data-bx-preview-device-class="desktop" data-bx-preview-device-width="1024" data-bx-preview-device-height="768">
										<span><?=GetMessage("skyweb24.popuppro_PREVIEW_DESKTOP")?></span>
									</div>
								</div>
								<center>
									<div class="iframe-wrapper" id="iframe-wrapper" style="margin-bottom:20px;">
										<div class="iframe_background_wrapper" style="position:absolute;width: 100%;height: 100%;">
											<div class="background_opacity" id="overlay_simulator" style="height: 100%;width: 100%;background: #00000075; position:absolute;z-index: 1;"></div>
											<iframe src="/" frameborder="0" class="site_background" style="width: 100%;height: 100%;position:relative;" scrolling="no"></iframe>
										</div>
										<?/*?><iframe class="preview-iframe" scrolling="no" src="about:blank" style="width: 768px; height: 1024px;"></iframe><?
										<iframe class="preview-iframe" scrolling="no" src="about:srcdoc" srcdoc="<html><head><title>hjkjkjkj</title></head><body><div></div><script>console.log(document);</script></body></html>" style="width: 768px; height: 1024px;"></iframe>*/?>
										<iframe name="preview_iframe" src="/bitrix/js/skyweb24.popuppro/iframe.html" class="preview-iframe" scrolling="no" style="z-index: 2;position:relative;width: 768px; height: 1024px;"></iframe>
									</div>
								</center>
							</div>
							<div style="clear:both;"></div>
						</div>
					</div>
					<script>

					</script>
				<?/*--e. o. new preview--*/?>
					<div id="detail_template_area"></div>
				</section>
				<h3><?=GetMessage("skyweb24.popuppro_VIEWS_STEP_3")?></h3>
				<section>

					<div id="edit_content"></div>
	<?
		$db_iblock_type = CIBlockType::GetList();
		$iblockTypes=array();
		while($ar_iblock_type = $db_iblock_type->Fetch()){
			$arIBType = CIBlockType::GetByIDLang($ar_iblock_type["ID"], LANG);
			$iblockTypes[$ar_iblock_type['ID']]=$arIBType['NAME'];
		}
		$res = CIBlock::GetList(Array('TYPE'=>'ASC', 'NAME'=>'ASC'), Array(), false);
		$avIblocks=array();
		$tmpType='';
		while($ar_res = $res->Fetch()){
			if($tmpType!=$ar_res['IBLOCK_TYPE_ID']){
				$tmpType=$ar_res['IBLOCK_TYPE_ID'];
			}
			$avIblocks[$tmpType][$ar_res['ID']]=$ar_res['NAME'];
		}

		$editArr=$editableWindow->getConditions();
		$checkSaveToList=(!empty($editArr['contact']['emailList']))?' checked="checked"':'';
		$checkRegister=(!empty($editArr['contact']['register']))?' checked="checked"':'';
		$checkSaveToIblock=(!empty($editArr['contact']['iblock']))?' checked="checked"':'';
		$checkSendToManager=$templateLink='';
		if(!empty($editArr['contact']['posttemplate'])){
			$checkSendToManager=' checked="checked"';
			$templateLink=' &nbsp; <a href="/bitrix/admin/message_edit.php?ID='.$editArr['contact']['posttemplate'].'" target="_blank">'.GetMessage("skyweb24.popuppro_CONTACT_EMAIL_TEMPLATE").' #'.$editArr['contact']['posttemplate'].'</a>';
		}
	?>
	<div class="block contacts ">
		<div class="info"><?=GetMessage("skyweb24.popuppro_CONTACT_NAME")?></div>
			<label>
			    <span><?=GetMessage("skyweb24.popuppro_CONTACT_SEND_MAIL")?></span>
				<span class="skwb24-item-hint" id="hint_contact_send_mail">?</span>
				<script>
				new top.BX.CHint({
					parent: top.BX("hint_contact_send_mail"),
					show_timeout: 10,
					hide_timeout: 200,
					dx: 2,
					preventHide: true,
					min_width: 400,
					hint: '<?=GetMessage("skyweb24.popuppro_CONTACT_SEND_MAIL_HINT")?>'
				});
				</script>
			    <input type="checkbox" name="contact_send_mail" value="Y"<?=$checkSendToManager?> /><?=$templateLink?>
			</label>
			<label class="pref">
			    <span><?=GetMessage("skyweb24.popuppro_CONTACT_SAVE_TO_IBLOCK")?></span>
				<span class="skwb24-item-hint" id="hint_contact_save_to_iblock">?</span>
				<script>
				new top.BX.CHint({
					parent: top.BX("hint_contact_save_to_iblock"),
					show_timeout: 10,
					hide_timeout: 200,
					dx: 2,
					preventHide: true,
					min_width: 400,
					hint: '<?=GetMessage("skyweb24.popuppro_CONTACT_SAVE_TO_IBLOCK_HINT")?>'
				});
				</script>
				<input type="checkbox" name="contact_save_to_iblock" value="Y"<?=$checkSaveToIblock?> />
			</label>

			<?if(count($avIblocks)>0){
				$listDisplay=(!empty($checkSaveToIblock))?'':' style="display:none;"';
				?>
				<label class="pref"<?=$listDisplay?>>
				    <span><?=GetMessage("skyweb24.popuppro_CONTACT_SAVE_LIST_IBLOCK")?></span>

					<select name="contact_iblock"><option value="">...</option><?
					$list='';
					foreach($avIblocks as $keyType=>$nextType){?>
						<optgroup label="<?=$iblockTypes[$keyType]?>"><?
						foreach($nextType as $keyBlock=>$valBlock){
							$selected=(!empty($editArr['contact']['iblock']) && $editArr['contact']['iblock']==$keyBlock)?' selected="selected"':'';
							?>
							<option value="<?=$keyBlock?>"<?=$selected?>><?=$valBlock?> [<?=$keyBlock?>]</option>
						<?}?></optgroup>
					<?}?></select>
				</label>
			<?}
			if(\Bitrix\Main\Loader::IncludeModule('sender')){?>
			<label class="pref">
			    <span><?=GetMessage("skyweb24.popuppro_CONTACT_SAVE_TO_LIST")?></span>
				<span class="skwb24-item-hint" id="hint_contact_save_to_list">?</span>
				<script>
				new top.BX.CHint({
					parent: top.BX("hint_contact_save_to_list"),
					show_timeout: 10,
					hide_timeout: 200,
					dx: 2,
					preventHide: true,
					min_width: 400,
					hint: '<?=GetMessage("skyweb24.popuppro_CONTACT_SAVE_TO_LIST_HINT")?>'
				});
				</script>
			    <input type="checkbox" name="contact_save_to_list" value="Y"<?=$checkSaveToList?> />
			</label>
			
			<?
				$tmp=$editableWindow->getSetting($editableWindow->getId());
				$tmpContactList=Skyweb24\Popuppro\Tools::getListType($tmp['row']['id'], $tmp['service_name']);
				$displayGroup=(!empty($checkSaveToList))?'':' style="display:none;"';
			?>
			<label class="pref"<?=$displayGroup?>>
				<span><?=GetMessage("skyweb24.popuppro_CONTACT_EMAILGROUP")?></span>
				<span class="skwb24-item-hint" id="hint_contact_emailgroup">?</span>
				<script>
					new top.BX.CHint({
					parent: top.BX("hint_contact_emailgroup"),
					show_timeout: 10,
					hide_timeout: 200,
					dx: 2,
					preventHide: true,
					min_width: 400,
					hint: '<?=GetMessage("skyweb24.popuppro_CONTACT_EMAILGROUP_HINT")?>'
				});
				</script>
				<select name="contact_groupmail"><option value="0">...</option>
				<?foreach($tmpContactList as $keyGroup=>$nameGroup){
					$selected=(!empty($editArr['contact']['groupmail']) && $editArr['contact']['groupmail']==$keyGroup)?' selected="selected"':'';?>
					<option value="<?=$keyGroup?>"<?=$selected?>><?=$nameGroup?></option>
				<?}?>
				</select>
			</label>
			<?}?>
			<label class="pref">
				<span><?=GetMessage("skyweb24.popuppro_CONTACT_REGISTER")?></span>
				<span class="skwb24-item-hint" id="hint_contact_register">?</span>
				<script>
					new top.BX.CHint({
					parent: top.BX("hint_contact_register"),
					show_timeout: 10,
					hide_timeout: 200,
					dx: 2,
					preventHide: true,
					min_width: 400,
					hint: '<?=GetMessage("skyweb24.popuppro_CONTACT_REGISTER_HINT")?>'
				});
				</script>
				<input type="checkbox" name="contact_register" value="Y" <?=$checkRegister?>/>
			</label>
	</div>
	<div class="block timer">

		<div class="info"><?=GetMessage("skyweb24.popuppro_TIMER_NAME")?></div>
		<label class="pref">
			<span><?=GetMessage("skyweb24.popuppro_TIMER_ENABLE")?></span>
			<span class="skwb24-item-hint" id="hint_timer_enable">?</span>
			<script>
				new top.BX.CHint({
					parent: top.BX("hint_timer_enable"),
					show_timeout:10,
					hide_timeout:200,
					dx:2,
					preventHide:true,
					min_width:400,
					hint:'<?=GetMessage("skyweb24.popuppro_TIMER_ENABLE_HINT")?>'
				});
			</script>
			<?$checkTimer=($editArr['timer']['enabled']=='Y')?'checked="checked"':'';?>
			<input type="checkbox" name="timer_enable" value="Y" <?=$checkTimer?>>
		</label>
		<label class="pref toggle">
			<span><?=GetMessage("skyweb24.popuppro_TIMER_TIME")?></span>
			<span class="skwb24-item-hint" id="hint_time_hint">?</span>
			<script>
				new top.BX.CHint({
					parent: top.BX("hint_time_hint"),
					show_timeout:10,
					hide_timeout:200,
					dx:2,
					preventHide:true,
					min_width:400,
					hint:'<?=GetMessage("skyweb24.popuppro_TIMER_TIME_HINT")?>'
				});
			</script>
			<div class="adm-input-wrap adm-input-wrap-calendar">
				<input class="adm-input adm-input-calendar" type="text" name="timer_date" value="<?=$editArr['timer']['date']?>">
				<span class="adm-calendar-icon" title="<?=GetMessage("skyweb24.popuppro_TIMER_TIME_TITLE")?>" onclick="BX.calendar({node:this,field:'timer_date',form:'',bTime:true,bHideTime:false})"></span>
			</div>
		</label>
		<label class="pref toggle">
			<span><?=GetMessage("skyweb24.popuppro_SERVER_TIME")?></span>
			<span class="skwb24-item-hint" id="hint_server_time_hint">?</span>
			<script>
				new top.BX.CHint({
					parent: top.BX("hint_server_time_hint"),
					show_timeout:10,
					hide_timeout:200,
					dx:2,
					preventHide:true,
					min_width:400,
					hint:'<?=GetMessage("skyweb24.popuppro_SERVER_TIME_HINT")?>'
				});
			</script>
			<div class="dateServer">
				<?
				$today = date("d.m.Y H:i:s");
				echo $today;
				?>
			</div>
		</label>
		<label class="pref toggle">
			<span><?=GetMessage("skyweb24.popuppro_TIMER_TEXT")?></span>
			<span class="skwb24-item-hint" id="hint_timer_text">?</span>
			<script>
				new top.BX.CHint({
					parent: top.BX("hint_timer_text"),
					show_timeout:10,
					hide_timeout:200,
					dx:2,
					preventHide:true,
					min_width:400,
					hint:'<?=GetMessage("skyweb24.popuppro_TIMER_TEXT_HINT")?>'
				});
			</script>
			<input type="text" name="timer_text" value="<?echo (!empty($editArr['timer']['text']))?$editArr['timer']['text']:GetMessage("skyweb24.popuppro_TIMER_TEXT_DEFAULT")?>">
		</label>
		<label class="pref">
			<span><?=GetMessage("skyweb24.popuppro_TIMER_LEFT")?></span>
			<?$checkTimerLeft=($editArr['timer']['left']=='Y'||empty($editArr['timer']['left']))?'checked="checked"':'';?>
			<input type="checkbox" value="Y" <?=$checkTimerLeft?>>

			<input type="hidden" name="timer_left" value="<?=(!empty($editArr['timer']['left']))?$editArr['timer']['left']:'Y'?>">
		</label>
		<label class="pref">
			<span><?=GetMessage("skyweb24.popuppro_TIMER_RIGHT")?></span>
			<?$checkTimerRight=($editArr['timer']['right']=='Y')?'checked="checked"':'';?>
			<input type="checkbox" value="Y" <?=$checkTimerRight?>>

			<input type="hidden" name="timer_right" value="<?=(!empty($editArr['timer']['right']))?$editArr['timer']['right']:'N'?>">
		</label>
		<label class="pref">
			<span><?=GetMessage("skyweb24.popuppro_TIMER_TOP")?></span>
			<?$checkTimerTop=($editArr['timer']['top']=='Y'||empty($editArr['timer']['top']))?'checked="checked"':'';?>
			<input type="checkbox" value="Y" <?=$checkTimerTop?>>

			<input type="hidden" name="timer_top" value="<?=(!empty($editArr['timer']['top']))?$editArr['timer']['top']:'Y'?>">
		</label>
		<label class="pref">
			<span><?=GetMessage("skyweb24.popuppro_TIMER_BOTTOM")?></span>
			<?$checkTimerBottom=($editArr['timer']['bottom']=='Y')?'checked="checked"':'';?>
			<input type="checkbox" value="Y" <?=$checkTimerBottom?>>

			<input type="hidden" name="timer_bottom" value="<?=(!empty($editArr['timer']['bottom']))?$editArr['timer']['bottom']:'N'?>">
		</label>
	</div>
	<div class="block roulette">
		<?
			$colors = array(
				'#ff9ff3'=>'Jigglypuff',
				'#f368e0'=>'Lian Hong lotus pink',

				'#00d2d3'=>'Jade dust',
				'#01a3a4'=>'Aqua velvet',

				'#feca57'=>'Casandora yellow',
				'#ff9f43'=>'Double dragon skin',

				'#54a0ff'=>'Joust blue',
				'#2e86de'=>'Bleu de france',

				'#ff6b6b'=>'Pastel red',
				'#ee5253'=>'Amour',

				'#5f27cd'=>'Nasu purple',
				'#341f97'=>'Bluebell',

				'#48dbfb'=>'Megaman',
				'#0abde3'=>'Cyanite',

				'#1dd1a1'=>'Wild caribbean green',
				'#10ac84'=>'Dark mountain meadow',

				'#576574'=>'Fuel town',
				'#222f3e'=>'Imperial primer',

			);
			$tmpBasketRule=array();
			$tmpBasketRule['nothing']=GetMessage("skyweb24.popuppro_ROULETTE_NOTHING");
			$tmpBasketRule['win']=GetMessage("skyweb24.popuppro_ROULETTE_WIN");
			$tmpLastBasketRule=0;
			$tmpFirstBasketRule=0;
			if (\Bitrix\Main\Loader::IncludeModule('sale')){
				$tmpBasketRule_=Skyweb24\Popuppro\Tools::getBasketRules();
				foreach($tmpBasketRule_ as $key=>$rule){
					if($tmpFirstBasketRule==0)$tmpFirstBasketRule=$key;
					$tmpBasketRule[$key]=$rule;
					$tmpLastBasketRule=$key;
				}
			}
			if(empty($editArr['roulett'][1])){
				$editArr['roulett']=array(
					'1'=>array('text'=>GetMessage("skyweb24.popuppro_TYPE_ROULETTE_TEXT_1_DEFAULT"),'color'=>GetMessage("skyweb24.popuppro_TYPE_ROULETTE_COLOR_1_DEFAULT")),
					'2'=>array('text'=>GetMessage("skyweb24.popuppro_TYPE_ROULETTE_TEXT_2_DEFAULT"),'color'=>GetMessage("skyweb24.popuppro_TYPE_ROULETTE_COLOR_2_DEFAULT")),
					'3'=>array('text'=>GetMessage("skyweb24.popuppro_TYPE_ROULETTE_TEXT_3_DEFAULT"),'color'=>GetMessage("skyweb24.popuppro_TYPE_ROULETTE_COLOR_3_DEFAULT")),
					'4'=>array('text'=>GetMessage("skyweb24.popuppro_TYPE_ROULETTE_TEXT_4_DEFAULT"),'color'=>GetMessage("skyweb24.popuppro_TYPE_ROULETTE_COLOR_4_DEFAULT")),
					'5'=>array('text'=>GetMessage("skyweb24.popuppro_TYPE_ROULETTE_TEXT_5_DEFAULT"),'color'=>GetMessage("skyweb24.popuppro_TYPE_ROULETTE_COLOR_5_DEFAULT")),
					'6'=>array('text'=>GetMessage("skyweb24.popuppro_TYPE_ROULETTE_TEXT_6_DEFAULT"),'color'=>GetMessage("skyweb24.popuppro_TYPE_ROULETTE_COLOR_6_DEFAULT")),
					'7'=>array('text'=>GetMessage("skyweb24.popuppro_TYPE_ROULETTE_TEXT_7_DEFAULT"),'color'=>GetMessage("skyweb24.popuppro_TYPE_ROULETTE_COLOR_7_DEFAULT")),
					'8'=>array('text'=>GetMessage("skyweb24.popuppro_TYPE_ROULETTE_TEXT_8_DEFAULT"),'color'=>GetMessage("skyweb24.popuppro_TYPE_ROULETTE_COLOR_8_DEFAULT"))
				);
				$editArr['roulett']['count']=8;
			}
		?>
		<script>
			var colors_for_roulette=<?=CUtil::PhpToJSObject($colors)?>;
			var basket_rule_for_roulette=<?=CUtil::PhpToJSObject($tmpBasketRule)?>;
			var tmpLastBasketRule=<?=$tmpLastBasketRule?>;
			var tmpFirstBasketRule=<?=$tmpFirstBasketRule?>;
			var basket_rule_basic='<?=GetMessage("skyweb24.popuppro_ROULETTE_BASIC")?>';
			var basket_rule_rules='<?=GetMessage("skyweb24.popuppro_ROULETTE_RULES")?>';
			var minimum_message='<?=GetMessage("skyweb24.popuppro_ROULETTE_MINIMUM")?>';
			var rule_info='<?=GetMessage("skyweb24.popuppro_ROULETTE_RULE_INFO")?>';
		</script>
		<table class="adm-list-table">
			<thead>
				<tr class="adm-list-table-header">
					<td class="adm-list-table-cell"></td>
					<td class="adm-list-table-cell"><div class="adm-list-table-cell-inner"><?=GetMessage("skyweb24.popuppro_ROULETTE_SORT")?></div></td>
					<td class="adm-list-table-cell"><div class="adm-list-table-cell-inner"><?=GetMessage("skyweb24.popuppro_ROULETTE_TEXT")?></div></td>
					<td class="adm-list-table-cell"><div class="adm-list-table-cell-inner"><?=GetMessage("skyweb24.popuppro_ROULETTE_COLOR")?></div></td>
					<td class="adm-list-table-cell"><div class="adm-list-table-cell-inner"><?=GetMessage("skyweb24.popuppro_ROULETTE_RULE")?>
					<span class="skwb24-item-hint" id="hint_roulette_rule">?</span>
					<script>
						new top.BX.CHint({
							parent: top.BX("hint_roulette_rule"),
							show_timeout:10,
							hide_timeout:200,
							dx:2,
							preventHide:true,
							min_width:400,
							hint:'<?echo GetMessage("skyweb24.popuppro_ROULETTE_RULE_INFO_BASIC"); echo (\Bitrix\Main\Loader::IncludeModule('sale'))?GetMessage("skyweb24.popuppro_ROULETTE_RULE_INFO_SALE"):''?>'
						});
					</script>
					</div></td>
					<td class="adm-list-table-cell"><div class="adm-list-table-cell-inner"><?=GetMessage("skyweb24.popuppro_ROULETTE_CHANCE")?>
					<span class="skwb24-item-hint" id="hint_roulette_chance">?</span>
					<script>
						new top.BX.CHint({
							parent: top.BX("hint_roulette_chance"),
							show_timeout:10,
							hide_timeout:200,
							dx:2,
							preventHide:true,
							min_width:400,
							hint:'<?echo GetMessage("skyweb24.popuppro_ROULETTE_CHANCE_HINT");?>'
						});
					</script>
					</div></td>
					<td class="adm-list-table-cell"><div class="adm-list-table-cell-inner"><?=GetMessage("skyweb24.popuppro_ROULETTE_CONTROL")?></div></td>
				</tr>
			</thead>
			<tbody class="drag_container">
				<tr class="adm-list-table-row draggable">
					<td class="adm-list-table-cell">
						<div class="adm-list-table-popup drag_key"></div>
					</td>
					<td class="adm-list-table-cell">
						1
					</td>
					<td class="adm-list-table-cell">
						<input type="text" name="roulette_1_text" size="50" value="<?=$editArr['roulett'][1]['text']?>">
					</td>
					<td class="adm-list-table-cell">
						<select name="roulette_1_color" class="color_selector">
							<?foreach($colors as $hex=>$colorname){?>
								<option style="background:<?=$hex?>;color:<?=$hex?>" <?echo ($editArr['roulett'][1]['color']==$hex)?'selected':''?> value="<?=$hex?>"><?=$colorname?></option>
							<?}?>
						</select>
					</td>
					<td class="adm-list-table-cell">
						<select name="roulette_1_rule" class="rule_selector">
						<?
						foreach($tmpBasketRule as $rule=>$name){
							echo ($rule=='nothing')?'<optgroup label="'.GetMessage("skyweb24.popuppro_ROULETTE_BASIC").'">':'';
							echo ($tmpFirstBasketRule==$rule)?'<optgroup label="'.GetMessage("skyweb24.popuppro_ROULETTE_RULES").'">':'';
							?>
							<option value="<?=$rule?>" <?echo ($editArr['roulett'][1]['rule']==$rule)?'selected':''?> ><?=$name?></option>
						<?
							echo ($rule=='win')?'</optgroup>':'';
							echo ($tmpLastBasketRule==$rule)?'</optgroup>':'';
						}?>
						</select>
					</td>
					<td class="adm-list-table-cell">
						<select class="roulette_chance_gravity" name="roulette_1_gravity">
							<option <?if($editArr['roulett'][1]['gravity']=='100'||empty($editArr['roulett'][1]['gravity'])) echo 'selected="selected"'?>>100</option>
							<option <?if($editArr['roulett'][1]['gravity']=='90') echo 'selected="selected"'?>>90</option>
							<option <?if($editArr['roulett'][1]['gravity']=='80') echo 'selected="selected"'?>>80</option>
							<option <?if($editArr['roulett'][1]['gravity']=='70') echo 'selected="selected"'?>>70</option>
							<option <?if($editArr['roulett'][1]['gravity']=='60') echo 'selected="selected"'?>>60</option>
							<option <?if($editArr['roulett'][1]['gravity']=='50') echo 'selected="selected"'?>>50</option>
							<option <?if($editArr['roulett'][1]['gravity']=='40') echo 'selected="selected"'?>>40</option>
							<option <?if($editArr['roulett'][1]['gravity']=='30') echo 'selected="selected"'?>>30</option>
							<option <?if($editArr['roulett'][1]['gravity']=='20') echo 'selected="selected"'?>>20</option>
							<option <?if($editArr['roulett'][1]['gravity']=='10') echo 'selected="selected"'?>>10</option>
							<option <?if($editArr['roulett'][1]['gravity']=='0') echo 'selected="selected"'?>>0</option>
						</select>
						<input name="roulette_1_chance" class="roulette_chance_hidden" step="0.01" type="hidden" value="">
						<span class="roulette_chance"><?=$editArr['roulett'][1]['chance']?>%</span>
					</td>
					<td class="adm-list-table-cell">
						<a href="javascript:;" onclick="remove_roulette_row(this);">
							<img width="20px" height='25px' src="data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTkuMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeD0iMHB4IiB5PSIwcHgiIHZpZXdCb3g9IjAgMCAzNzguMzAzIDM3OC4zMDMiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDM3OC4zMDMgMzc4LjMwMzsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHdpZHRoPSI1MTJweCIgaGVpZ2h0PSI1MTJweCI+Cjxwb2x5Z29uIHN0eWxlPSJmaWxsOiNGRjM1MDE7IiBwb2ludHM9IjM3OC4zMDMsMjguMjg1IDM1MC4wMTgsMCAxODkuMTUxLDE2MC44NjcgMjguMjg1LDAgMCwyOC4yODUgMTYwLjg2NywxODkuMTUxIDAsMzUwLjAxOCAgIDI4LjI4NSwzNzguMzAyIDE4OS4xNTEsMjE3LjQzNiAzNTAuMDE4LDM3OC4zMDIgMzc4LjMwMywzNTAuMDE4IDIxNy40MzYsMTg5LjE1MSAiLz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPC9zdmc+Cg==">
						</a>
					</td>
				</tr>
				<tr class="adm-list-table-row draggable">
					<td class="adm-list-table-cell">
						<div class="adm-list-table-popup drag_key"></div>
					</td>
					<td class="adm-list-table-cell">
						2
					</td>
					<td class="adm-list-table-cell">
						<input type="text" name="roulette_2_text" size="50"  value="<?=$editArr['roulett'][2]['text']?>">
					</td>
					<td class="adm-list-table-cell">
						<select name="roulette_2_color" class="color_selector">
							<?foreach($colors as $hex=>$colorname){?>
								<option style="background:<?=$hex?>;color:<?=$hex?>" <?echo ($editArr['roulett'][2]['color']==$hex)?'selected':''?> value="<?=$hex?>"><?=$colorname?></option>
							<?}?>
						</select>
					</td>
					<td class="adm-list-table-cell">
						<select name="roulette_2_rule" class="rule_selector">
						<?
						foreach($tmpBasketRule as $rule=>$name){
							echo ($rule=='nothing')?'<optgroup label="'.GetMessage("skyweb24.popuppro_ROULETTE_BASIC").'">':'';
							echo ($tmpFirstBasketRule==$rule)?'<optgroup label="'.GetMessage("skyweb24.popuppro_ROULETTE_RULES").'">':'';
							?>
							<option value="<?=$rule?>" <?echo ($editArr['roulett'][2]['rule']==$rule)?'selected':''?> ><?=$name?></option>
						<?
							echo ($rule=='win')?'</optgroup>':'';
							echo ($tmpLastBasketRule==$rule)?'</optgroup>':'';
						}?>
						</select>
					</td>
					<td class="adm-list-table-cell">
						<select class="roulette_chance_gravity" name="roulette_2_gravity">
							<option <?if($editArr['roulett'][2]['gravity']=='100'||empty($editArr['roulett'][2]['gravity'])) echo 'selected="selected"'?>>100</option>
							<option <?if($editArr['roulett'][2]['gravity']=='90') echo 'selected="selected"'?>>90</option>
							<option <?if($editArr['roulett'][2]['gravity']=='80') echo 'selected="selected"'?>>80</option>
							<option <?if($editArr['roulett'][2]['gravity']=='70') echo 'selected="selected"'?>>70</option>
							<option <?if($editArr['roulett'][2]['gravity']=='60') echo 'selected="selected"'?>>60</option>
							<option <?if($editArr['roulett'][2]['gravity']=='50') echo 'selected="selected"'?>>50</option>
							<option <?if($editArr['roulett'][2]['gravity']=='40') echo 'selected="selected"'?>>40</option>
							<option <?if($editArr['roulett'][2]['gravity']=='30') echo 'selected="selected"'?>>30</option>
							<option <?if($editArr['roulett'][2]['gravity']=='20') echo 'selected="selected"'?>>20</option>
							<option <?if($editArr['roulett'][2]['gravity']=='10') echo 'selected="selected"'?>>10</option>
							<option <?if($editArr['roulett'][2]['gravity']=='0') echo 'selected="selected"'?>>0</option>
						</select>
						<input name="roulette_2_chance" class="roulette_chance_hidden" step="0.01" type="hidden" value="<?=$editArr['roulett'][2]['chance']?>">
						<span class="roulette_chance"><?=$editArr['roulett'][2]['chance']?>%</span>
					</td>
					<td class="adm-list-table-cell">
						<a href="javascript:;" onclick="remove_roulette_row(this);"><img width="20px" height='25px' src="data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTkuMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeD0iMHB4IiB5PSIwcHgiIHZpZXdCb3g9IjAgMCAzNzguMzAzIDM3OC4zMDMiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDM3OC4zMDMgMzc4LjMwMzsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHdpZHRoPSI1MTJweCIgaGVpZ2h0PSI1MTJweCI+Cjxwb2x5Z29uIHN0eWxlPSJmaWxsOiNGRjM1MDE7IiBwb2ludHM9IjM3OC4zMDMsMjguMjg1IDM1MC4wMTgsMCAxODkuMTUxLDE2MC44NjcgMjguMjg1LDAgMCwyOC4yODUgMTYwLjg2NywxODkuMTUxIDAsMzUwLjAxOCAgIDI4LjI4NSwzNzguMzAyIDE4OS4xNTEsMjE3LjQzNiAzNTAuMDE4LDM3OC4zMDIgMzc4LjMwMywzNTAuMDE4IDIxNy40MzYsMTg5LjE1MSAiLz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPC9zdmc+Cg=="></a>
					</td>
				</tr>
				<tr class="adm-list-table-row draggable">
					<td class="adm-list-table-cell">
						<div class="adm-list-table-popup drag_key"></div>
					</td>
					<td class="adm-list-table-cell">
						3
					</td>
					<td class="adm-list-table-cell">
						<input type="text" name="roulette_3_text" size="50" value="<?=$editArr['roulett'][3]['text']?>">
					</td>
					<td class="adm-list-table-cell">
						<select name="roulette_3_color"  class="color_selector">
							<?foreach($colors as $hex=>$colorname){?>
								<option style="background:<?=$hex?>;color:<?=$hex?>" <?echo ($editArr['roulett'][3]['color']==$hex)?'selected':''?> value="<?=$hex?>"><?=$colorname?></option>
							<?}?>
						</select>
					</td>
					<td class="adm-list-table-cell draggable">
						<select name="roulette_3_rule" class="rule_selector">
						<?
						foreach($tmpBasketRule as $rule=>$name){
							echo ($rule=='nothing')?'<optgroup label="'.GetMessage("skyweb24.popuppro_ROULETTE_BASIC").'">':'';
							echo ($tmpFirstBasketRule==$rule)?'<optgroup label="'.GetMessage("skyweb24.popuppro_ROULETTE_RULES").'">':'';
							?>
							<option value="<?=$rule?>" <?echo ($editArr['roulett'][3]['rule']==$rule)?'selected':''?> ><?=$name?></option>
						<?
							echo ($rule=='win')?'</optgroup>':'';
							echo ($tmpLastBasketRule==$rule)?'</optgroup>':'';
						}?>
						</select>
					</td>
					<td class="adm-list-table-cell">
						<select class="roulette_chance_gravity" name="roulette_3_gravity">
							<option <?if($editArr['roulett'][3]['gravity']=='100'||empty($editArr['roulett'][3]['gravity'])) echo 'selected="selected"'?>>100</option>
							<option <?if($editArr['roulett'][3]['gravity']=='90') echo 'selected="selected"'?>>90</option>
							<option <?if($editArr['roulett'][3]['gravity']=='80') echo 'selected="selected"'?>>80</option>
							<option <?if($editArr['roulett'][3]['gravity']=='70') echo 'selected="selected"'?>>70</option>
							<option <?if($editArr['roulett'][3]['gravity']=='60') echo 'selected="selected"'?>>60</option>
							<option <?if($editArr['roulett'][3]['gravity']=='50') echo 'selected="selected"'?>>50</option>
							<option <?if($editArr['roulett'][3]['gravity']=='40') echo 'selected="selected"'?>>40</option>
							<option <?if($editArr['roulett'][3]['gravity']=='30') echo 'selected="selected"'?>>30</option>
							<option <?if($editArr['roulett'][3]['gravity']=='20') echo 'selected="selected"'?>>20</option>
							<option <?if($editArr['roulett'][3]['gravity']=='10') echo 'selected="selected"'?>>10</option>
							<option <?if($editArr['roulett'][3]['gravity']=='0') echo 'selected="selected"'?>>0</option>
						</select>
						<input name="roulette_3_chance" class="roulette_chance_hidden" step="0.01" type="hidden" value="<?=$editArr['roulett'][3]['chance']?>">
						<span class="roulette_chance"><?=$editArr['roulett'][3]['chance']?>%</span>
					</td>
					<td class="adm-list-table-cell">
						<a href="javascript:;" onclick="remove_roulette_row(this);"><img width="20px" height='25px' src="data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTkuMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeD0iMHB4IiB5PSIwcHgiIHZpZXdCb3g9IjAgMCAzNzguMzAzIDM3OC4zMDMiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDM3OC4zMDMgMzc4LjMwMzsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHdpZHRoPSI1MTJweCIgaGVpZ2h0PSI1MTJweCI+Cjxwb2x5Z29uIHN0eWxlPSJmaWxsOiNGRjM1MDE7IiBwb2ludHM9IjM3OC4zMDMsMjguMjg1IDM1MC4wMTgsMCAxODkuMTUxLDE2MC44NjcgMjguMjg1LDAgMCwyOC4yODUgMTYwLjg2NywxODkuMTUxIDAsMzUwLjAxOCAgIDI4LjI4NSwzNzguMzAyIDE4OS4xNTEsMjE3LjQzNiAzNTAuMDE4LDM3OC4zMDIgMzc4LjMwMywzNTAuMDE4IDIxNy40MzYsMTg5LjE1MSAiLz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPC9zdmc+Cg=="></a>
					</td>
				</tr>
				<tr class="adm-list-table-row draggable">
					<td class="adm-list-table-cell">
						<div class="adm-list-table-popup drag_key"></div>
					</td>
					<td class="adm-list-table-cell">
						4
					</td>
					<td class="adm-list-table-cell">
						<input type="text" name="roulette_4_text" size="50" value="<?=$editArr['roulett'][4]['text']?>">
					</td>
					<td class="adm-list-table-cell">
						<select name="roulette_4_color" class="color_selector">
							<?foreach($colors as $hex=>$colorname){?>
								<option style="background:<?=$hex?>;color:<?=$hex?>" <?echo ($editArr['roulett'][4]['color']==$hex)?'selected':''?> value="<?=$hex?>"><?=$colorname?></option>
							<?}?>
						</select>
					</td>
					<td class="adm-list-table-cell">
						<select name="roulette_4_rule" class="rule_selector">
						<?
						foreach($tmpBasketRule as $rule=>$name){
							echo ($rule=='nothing')?'<optgroup label="'.GetMessage("skyweb24.popuppro_ROULETTE_BASIC").'">':'';
							echo ($tmpFirstBasketRule==$rule)?'<optgroup label="'.GetMessage("skyweb24.popuppro_ROULETTE_RULES").'">':'';
							?>
							<option value="<?=$rule?>" <?echo ($editArr['roulett'][4]['rule']==$rule)?'selected':''?> ><?=$name?></option>
						<?
							echo ($rule=='win')?'</optgroup>':'';
							echo ($tmpLastBasketRule==$rule)?'</optgroup>':'';
						}?>
						</select>
					</td>
					<td class="adm-list-table-cell">
						<select class="roulette_chance_gravity" name="roulette_4_gravity">
							<option <?if($editArr['roulett'][4]['gravity']=='100'||empty($editArr['roulett'][4]['gravity'])) echo 'selected="selected"'?>>100</option>
							<option <?if($editArr['roulett'][4]['gravity']=='90') echo 'selected="selected"'?>>90</option>
							<option <?if($editArr['roulett'][4]['gravity']=='80') echo 'selected="selected"'?>>80</option>
							<option <?if($editArr['roulett'][4]['gravity']=='70') echo 'selected="selected"'?>>70</option>
							<option <?if($editArr['roulett'][4]['gravity']=='60') echo 'selected="selected"'?>>60</option>
							<option <?if($editArr['roulett'][4]['gravity']=='50') echo 'selected="selected"'?>>50</option>
							<option <?if($editArr['roulett'][4]['gravity']=='40') echo 'selected="selected"'?>>40</option>
							<option <?if($editArr['roulett'][4]['gravity']=='30') echo 'selected="selected"'?>>30</option>
							<option <?if($editArr['roulett'][4]['gravity']=='20') echo 'selected="selected"'?>>20</option>
							<option <?if($editArr['roulett'][4]['gravity']=='10') echo 'selected="selected"'?>>10</option>
							<option <?if($editArr['roulett'][4]['gravity']=='0') echo 'selected="selected"'?>>0</option>
						</select>
						<input name="roulette_4_chance" class="roulette_chance_hidden" step="0.01" type="hidden" value="<?=$editArr['roulett'][4]['chance']?>">
						<span class="roulette_chance"><?=$editArr['roulett'][4]['chance']?>%</span>
					</td>
					<td class="adm-list-table-cell">
						<a href="javascript:;" onclick="remove_roulette_row(this);"><img width="20px" height='25px' src="data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTkuMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeD0iMHB4IiB5PSIwcHgiIHZpZXdCb3g9IjAgMCAzNzguMzAzIDM3OC4zMDMiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDM3OC4zMDMgMzc4LjMwMzsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHdpZHRoPSI1MTJweCIgaGVpZ2h0PSI1MTJweCI+Cjxwb2x5Z29uIHN0eWxlPSJmaWxsOiNGRjM1MDE7IiBwb2ludHM9IjM3OC4zMDMsMjguMjg1IDM1MC4wMTgsMCAxODkuMTUxLDE2MC44NjcgMjguMjg1LDAgMCwyOC4yODUgMTYwLjg2NywxODkuMTUxIDAsMzUwLjAxOCAgIDI4LjI4NSwzNzguMzAyIDE4OS4xNTEsMjE3LjQzNiAzNTAuMDE4LDM3OC4zMDIgMzc4LjMwMywzNTAuMDE4IDIxNy40MzYsMTg5LjE1MSAiLz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPC9zdmc+Cg=="></a>
					</td>
				</tr>
				<?if(!empty($editArr['roulett']['count'])&&$editArr['roulett']['count']>4){
					for($i=5;$i<=$editArr['roulett']['count'];$i++){?>
							<tr class="adm-list-table-row draggable">
								<td class="adm-list-table-cell"><div class="adm-list-table-popup drag_key"></div></td>
								<td class="adm-list-table-cell"><?=$i?></td>
								<td class="adm-list-table-cell"><input type="text" name="roulette_<?=$i?>_text" size="50" value="<?=$editArr['roulett'][$i]['text']?>"></td>
								<td class="adm-list-table-cell"><select name="roulette_<?=$i?>_color" class="color_selector"><?foreach($colors as $hex=>$colorname){?><option style="background:<?=$hex?>;color:<?=$hex?>" <?echo ($editArr['roulett'][$i]['color']==$hex)?'selected':''?> value="<?=$hex?>"><?=$colorname?></option><?}?></select></td>
								<td class="adm-list-table-cell"><select name="roulette_<?=$i?>_rule" class="rule_selector"><?foreach($tmpBasketRule as $rule=>$name){echo ($rule=='nothing')?'<optgroup label="'.GetMessage("skyweb24.popuppro_ROULETTE_BASIC").'">':'';echo ($tmpFirstBasketRule==$rule)?'<optgroup label="'.GetMessage("skyweb24.popuppro_ROULETTE_RULES").'">':'';?><option value="<?=$rule?>" <?=($editArr['roulett'][$i]['rule']==$rule)?'selected':''?> ><?=$name?></option><?echo ($rule=='win')?'</optgroup>':'';echo ($tmpLastBasketRule==$rule)?'</optgroup>':'';}?></select></td>
								<td class="adm-list-table-cell">
									<select class="roulette_chance_gravity" name="roulette_<?=$i?>_gravity">
										<option <?if($editArr['roulett'][$i]['gravity']=='100'||empty($editArr['roulett'][$i]['gravity'])) echo 'selected="selected"'?>>100</option>
										<option <?if($editArr['roulett'][$i]['gravity']=='90') echo 'selected="selected"'?>>90</option>
										<option <?if($editArr['roulett'][$i]['gravity']=='80') echo 'selected="selected"'?>>80</option>
										<option <?if($editArr['roulett'][$i]['gravity']=='70') echo 'selected="selected"'?>>70</option>
										<option <?if($editArr['roulett'][$i]['gravity']=='60') echo 'selected="selected"'?>>60</option>
										<option <?if($editArr['roulett'][$i]['gravity']=='50') echo 'selected="selected"'?>>50</option>
										<option <?if($editArr['roulett'][$i]['gravity']=='40') echo 'selected="selected"'?>>40</option>
										<option <?if($editArr['roulett'][$i]['gravity']=='30') echo 'selected="selected"'?>>30</option>
										<option <?if($editArr['roulett'][$i]['gravity']=='20') echo 'selected="selected"'?>>20</option>
										<option <?if($editArr['roulett'][$i]['gravity']=='10') echo 'selected="selected"'?>>10</option>
										<option <?if($editArr['roulett'][$i]['gravity']=='0') echo 'selected="selected"'?>>0</option>
									</select>
									<input name="roulette_<?=$i?>_chance" class="roulette_chance_hidden" type="hidden" step="0.01" value="<?=$editArr['roulett'][$i]['chance']?>">
									<span class="roulette_chance"><?=$editArr['roulett'][$i]['chance']?>%</span>
								</td>
								<td class="adm-list-table-cell"><a href="javascript:;" onclick="remove_roulette_row(this);"><img width="20px" height='25px' src="data:image/svg+xml;utf8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pgo8IS0tIEdlbmVyYXRvcjogQWRvYmUgSWxsdXN0cmF0b3IgMTkuMC4wLCBTVkcgRXhwb3J0IFBsdWctSW4gLiBTVkcgVmVyc2lvbjogNi4wMCBCdWlsZCAwKSAgLS0+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeD0iMHB4IiB5PSIwcHgiIHZpZXdCb3g9IjAgMCAzNzguMzAzIDM3OC4zMDMiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDM3OC4zMDMgMzc4LjMwMzsiIHhtbDpzcGFjZT0icHJlc2VydmUiIHdpZHRoPSI1MTJweCIgaGVpZ2h0PSI1MTJweCI+Cjxwb2x5Z29uIHN0eWxlPSJmaWxsOiNGRjM1MDE7IiBwb2ludHM9IjM3OC4zMDMsMjguMjg1IDM1MC4wMTgsMCAxODkuMTUxLDE2MC44NjcgMjguMjg1LDAgMCwyOC4yODUgMTYwLjg2NywxODkuMTUxIDAsMzUwLjAxOCAgIDI4LjI4NSwzNzguMzAyIDE4OS4xNTEsMjE3LjQzNiAzNTAuMDE4LDM3OC4zMDIgMzc4LjMwMywzNTAuMDE4IDIxNy40MzYsMTg5LjE1MSAiLz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPGc+CjwvZz4KPC9zdmc+Cg=="></a></td>
							</tr>
					<?}
				}?>
			</tbody>
		</table>
		<input type="hidden" value="<?=(!empty($editArr['roulett']['count'])?$editArr['roulett']['count']:8)?>" name="roulette_element_count">
		<a href="javascript:;" class="adm-btn-save adm-btn-add adm-btn add-roulette-row"><?=GetMessage("skyweb24.popuppro_ROULETTE_ADD")?></a>
	</div>

	<div class="exampleWindow" style="display:none;">
		<div class="exampleWindowHeader">
			<div class="exampleWindowHeaderButtons">
				<span></span>
				<span></span>
				<span></span>
			</div>
			<div class="exampleWindowHeaderSearch"></div>
		</div>
		<div class="positionBlockAnimator">
			<div class="left top" title="<?=GetMessage("skyweb24.popuppro_POSITION_LeftTop")?>"></div>
			<div class="top" title="<?=GetMessage("skyweb24.popuppro_POSITION_Top")?>"></div>
			<div class="right top" title="<?=GetMessage("skyweb24.popuppro_POSITION_RightTop")?>"></div>
			<div class="left" title="<?=GetMessage("skyweb24.popuppro_POSITION_Left")?>"></div>
			<div class="center" title="<?=GetMessage("skyweb24.popuppro_POSITION_Center")?>"></div>
			<div class="right" title="<?=GetMessage("skyweb24.popuppro_POSITION_Right")?>"></div>
			<div class="bottom left" title="<?=GetMessage("skyweb24.popuppro_POSITION_LeftBottom")?>"></div>
			<div class="bottom" title="<?=GetMessage("skyweb24.popuppro_POSITION_Bottom")?>"></div>
			<div class="bottom right" title="<?=GetMessage("skyweb24.popuppro_POSITION_RightBottom")?>"></div>
		</div>
	</div>
	<?/*?><label id="fixed_popup">
		<span><?=GetMessage("skyweb24.popuppro_POSITION_fixed")?></span>
		<input type="checkbox" name="fixed_popup" value="Y" />
	</label><?*/?>

	<div class="positionTimer toggle" style="display:none;">
		<div class="left top" title="<?=GetMessage("skyweb24.popuppro_POSITION_LeftTop")?>"></div>
		<div class="right top" title="<?=GetMessage("skyweb24.popuppro_POSITION_RightTop")?>"></div>
		<div class="examplePopup"><p><?=GetMessage("skyweb24.popuppro_POPUP_example")?></p></div>
		<div class="bottom left" title="<?=GetMessage("skyweb24.popuppro_POSITION_LeftBottom")?>"></div>
		<div class="bottom right" title="<?=GetMessage("skyweb24.popuppro_POSITION_RightBottom")?>"></div>
	</div>


				</section>
				<script>
					var templatesType=<?=CUtil::PhpToJSObject($types)?>;
					var templatesPopup=<?=CUtil::PhpToJSObject($templates)?>;
				</script>
			</div>

			<div id="popuppro_manager_files" style="display:none">
				<div id="popuppro_img_list"></div>
				<?$APPLICATION->IncludeComponent("bitrix:main.file.input", "drag_n_drop",
				   array(
					  "INPUT_NAME"=>"UPLOAD_IMG_POPUPPRO",
					  "MULTIPLE"=>"N",
					  "MODULE_ID"=>$module_id,
					  //"MODULE_ID"=>'iblock',
					  "MAX_FILE_SIZE"=>"5000000",
					  "ALLOW_UPLOAD"=>"F",
					  //"ALLOW_UPLOAD_EXT"=>array("jpeg", "jpg", "png", "gif")
					  "ALLOW_UPLOAD_EXT"=>"jpeg,jpg,png,gif"
				   ),
				   false
				);?>
			</div>
			<div class="personalizationList" style="display:none;">
				<div class="personalizationListDesc"><?=GetMessage("skyweb24.popuppro_PERSONALISATION_LINK_DESC");?></div>
				<?$personalize=Skyweb24\Popuppro\Tools::getPersonalization();
				foreach($personalize as $keyPersonal=>$nextPersonal){?>
					<p><b>#<?=$keyPersonal?>#</b> - <span><?=$nextPersonal?></span></p>
				<?}?>
			</div>
		</td>
	</tr>


	<?$tabControl->BeginNextTab();
	$cProps=$editableWindow->getAvaliableProps();
	$sProps=$editableWindow->getSimilarProps($idPopup);
	?>
	<tr>
		<td colspan="2">
			<div class="window_description warning">
				<p><?=GetMessage("skyweb24.popuppro_TEST_INFO");?></p>
			</div>
		</td>
	</tr>
	<script>
		var popupProps=<?=\Bitrix\Main\Web\Json::encode($cProps, JSON_HEX_QUOT|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_OBJECT_AS_ARRAY );?>;
		var condPopupPros=<?=\Bitrix\Main\Web\Json::encode($sProps, JSON_HEX_QUOT|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_OBJECT_AS_ARRAY);?>;
	</script>
	

	<?
	$editArr=$editableWindow->getConditions();
	$activeCheckBox=($editArr['active'])?' checked="checked"':'';
	$activeAlreadygoing=($editArr['alreadygoing'])?' checked="checked"':'';
	$selectSite='<select multiple="multiple" size="'.min(3, count($editArr['sites'])).'" name="sites[]">';
	$period_from='';
	$period_to='';
	$editArr['dateStart']=(empty($editArr['dateStart']))?'':ConvertTimeStamp($editArr['dateStart'], "SHORT", LANGUAGE_ID);
	$editArr['dateFinish']=(empty($editArr['dateFinish']))?'':ConvertTimeStamp($editArr['dateFinish'], "SHORT", LANGUAGE_ID);
	if(!empty($editArr['timeInterval'])){
		$tmpPeriod=explode('#', $editArr['timeInterval']);
		$period_from=(!empty($tmpPeriod[0]))?$tmpPeriod[0]:'';
		$period_to=(!empty($tmpPeriod[1]))?$tmpPeriod[1]:'';
	}
	foreach($editArr['sites'] as $nextSite){
		$selectOption=($nextSite['active'])?' selected="selected"':'';
		$selectSite.='<option value="'.$nextSite['id'].'"'.$selectOption.'>'.$nextSite['name'].'</option>';
	}
	$selectSite.='</select>';
	$selectnextGroups='<select multiple="multiple" size="4" name="groups[]">';
	foreach($editArr['groups'] as $nextGroup){
		$selectOption=($nextGroup['active'])?' selected="selected"':'';
		$selectnextGroups.='<option value="'.$nextGroup['id'].'"'.$selectOption.'>'.$nextGroup['name'].'</option>';
	}
	$selectnextGroups.='</select>';
	$serviceName=(!empty($editArr['service_name']))?$editArr['service_name']:GetMessage("skyweb24.popuppro_TABCOND_SERVICE_NAME").'_'.$idPopup;

	?>
	<tr><th colspan="2"><?=GetMessage("skyweb24.popuppro_TABCOND_TITLE_MAIN")?></th></tr>

	<tr>
		<td><?=GetMessage("skyweb24.popuppro_TABCOND_ACTIVE")?>
			<span class="skwb24-item-hint" id="hint_tabcond_title_main">?</span>
			<script>
			new top.BX.CHint({
				parent: top.BX("hint_tabcond_title_main"),
				show_timeout: 10,
				hide_timeout: 200,
				dx: 2,
				preventHide: true,
				min_width: 400,
				hint: '<?=GetMessage("skyweb24.popuppro_TABCOND_ACTIVE_HINT")?>'
			});
			</script>
		</td>
		<td><input type="checkbox" name="active" value="Y"<?=$activeCheckBox?> /></td>
	</tr>
	<tr>
		<td><?=GetMessage("skyweb24.popuppro_TABCOND_SERVICE_NAME")?>
			<span class="skwb24-item-hint" id="hint_service_name">?</span>
			<script>
			new top.BX.CHint({
				parent: top.BX("hint_service_name"),
				show_timeout: 10,
				hide_timeout: 200,
				dx: 2,
				preventHide: true,
				min_width: 400,
				hint: '<?=GetMessage("skyweb24.popuppro_TABCOND_SERVICE_NAME_HINT")?>'
			});
			</script>
		</td>
		<td><input type="text" name="service_name" value="<?=$serviceName?>" /></td>
	</tr>
	<tr>
		<td><?=GetMessage("skyweb24.popuppro_TABCOND_SORT")?>
			<span class="skwb24-item-hint" id="hint_sort">?</span>
			<script>
			new top.BX.CHint({
				parent: top.BX("hint_sort"),
				show_timeout: 10,
				hide_timeout: 200,
				dx: 2,
				preventHide: true,
				min_width: 400,
				hint: '<?=GetMessage("skyweb24.popuppro_TABCOND_SORT_HINT")?>'
			});
			</script>
		</td>
		<td><input type="number" min="1" step="1" size="4" name="sort" value="<?=$editArr['sort']?>" /></td>
	</tr>
	<tr>
		<th colspan="2"><?=GetMessage("skyweb24.popuppro_TABCOND_TITLE_ADDITIONAL")?></th>
	</tr>
	<tr><td colspan="2">
		<div id="popupPropsCont">
	</div>

	</td></tr>
	<?$tabControl->Buttons();?>
	<input type="submit" class="adm-btn-save" name="save" value="<?=GetMessage("skyweb24.popuppro_TAB_BUTTON_SAVE")?>" title="<?=GetMessage("skyweb24.popuppro_TAB_BUTTON_SAVE")?>" />&nbsp;
	<input type="submit" class="button" name="apply" value="<?=GetMessage("skyweb24.popuppro_TAB_BUTTON_APPLY")?>" title="<?=GetMessage("skyweb24.popuppro_TAB_BUTTON_APPLY")?>" />&nbsp;
	<input  type="button" name="cancel" value="<?=GetMessage("skyweb24.popuppro_TAB_BUTTON_CANCEL")?>" title="<?=GetMessage("skyweb24.popuppro_TAB_BUTTON_CANCEL")?>" />
	<?$tabControl->End();?>
	<script>
		<?
		$agreements=$editableWindow->getAgreements(array('button_caption'=>'#BUTTON_TEXT#'));
		if(count($agreements)>0){

			?>var agreements=<?=\CUtil::phpToJSObject($agreements);?>;<?
		}
		
		$personalize=Skyweb24\Popuppro\Tools::getPersonalizationValues();
		?>var personalize=<?=\CUtil::phpToJSObject($personalize);?>;<?
		?>
		var popupMessages={
			'uploadImg':'<?=GetMessage("skyweb24.popuppro_IMG_BLOCK_UPLOADIMG")?>',
			'titlePopupImgBlock':'<?=GetMessage("skyweb24.popuppro_POPUP_IMGBLOCKTITLE")?>',
			'titleSetcontent':'<?=GetMessage("skyweb24.popuppro_SET_CONTENT")?>',
			'titleSetservice':'<?=GetMessage("skyweb24.popuppro_SET_SETTINGS")?>',
			'titleSeteffects':'<?=GetMessage("skyweb24.popuppro_SET_EFFECTS")?>',
			'titleSetpositionpopup':'<?=GetMessage("skyweb24.popuppro_TABCOND_WINDOW_POSITION")?>',
			'errorContactTabSetting':'<?=GetMessage("skyweb24.popuppro_ERROR_CONTACT_TAB_SETTING")?>',
			'hideBlock':'<?=GetMessage("skyweb24.popuppro_HIDE_BLOCK")?>',
			'ShowBlock':'<?=GetMessage("skyweb24.popuppro_SHOW_BLOCK")?>',
			'selectImg':'<?=GetMessage("skyweb24.popuppro_JS_SELECT_IMG")?>',
			'addColorTheme':'<?=GetMessage("skyweb24.popuppro_ADD_COLOR_THEME")?>',
			'addColorTemplate':'<?=GetMessage("skyweb24.popuppro_ADD_TEMPLATE")?>',
			'confirmAddColorTheme':'<?=GetMessage("skyweb24.popuppro_CONFIRM_ADD_COLOR_THEME")?>',
			'create':'<?=GetMessage("skyweb24.popuppro_CREATE_BLOCK")?>',
			'enterName':'<?=GetMessage("skyweb24.popuppro_CONFIRM_ADD_TEMPLATE_ENTERNAME")?>',
			'enterNameColor':'<?=GetMessage("skyweb24.popuppro_CONFIRM_ADD_COLOR_ENTERNAME")?>',
			'nameIsRequired':'<?=GetMessage("skyweb24.popuppro_NAMEISREQUIRED_BLOCK")?>',
			'colorThemeCreateSuccess':'<?=GetMessage("skyweb24.popuppro_COLORTHEME_CREATESUCCESS")?>',
			'customTemplateCreateSuccess':'<?=GetMessage("skyweb24.popuppro_CUSTOMTEMPLATE_CREATESUCCESS")?>',
			'apply':'<?=GetMessage("skyweb24.popuppro_APPLY")?>',
			'edit':'<?=GetMessage("skyweb24.popuppro_TABLE_EDIT")?>',
			'additional':'<?=GetMessage("skyweb24.popuppro_TABLE_ADDITIONAL")?>',
			'personalisation':'<?=GetMessage("skyweb24.popuppro_PERSONALISATION_LINK")?>',
			'personalisationMarker':'<?=GetMessage("skyweb24.popuppro_PERSON_HINT")?>',
			'showPostTemplate':'<?=GetMessage("skyweb24.popuppro_CONTACT_EMAIL_TEMPLATE")?>',

			'color_main':'<?=GetMessage("skyweb24.popuppro_COLOR_MAIN")?>',
			'color_grad':'<?=GetMessage("skyweb24.popuppro_COLOR_GRAD")?>',
			'color_ca':'<?=GetMessage("skyweb24.popuppro_COLOR_CA")?>',
			'color_au':'<?=GetMessage("skyweb24.popuppro_COLOR_AU")?>',
			'color_ru':'<?=GetMessage("skyweb24.popuppro_COLOR_RU")?>',
		};
		//(window.BX||top.BX).message({'JSADM_FILES':'<?=GetMessage("skyweb24.popuppro_JSADM_FILES")?>'});
	</script>
	</form></section>
<?}?>

<?
if(isset($_REQUEST['save']) && $_REQUEST['save']==GetMessage("skyweb24.popuppro_TAB_BUTTON_SAVE") && !empty($_REQUEST['id'])){
	$CURRENT_PAGE = (CMain::IsHTTPS()) ? "https://" : "http://";
	$CURRENT_PAGE .= $_SERVER["HTTP_HOST"];
	$CURRENT_PAGE .= $APPLICATION->GetCurPage(true);
	header('Location: '.$CURRENT_PAGE);
	exit;
}
}else{
	CAdminMessage::ShowMessage(Array("TYPE"=>"ERROR", "HTML"=>true, "MESSAGE"=>GetMessage("skyweb24.popuppro_NOT_INCLUDE")));
}
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
