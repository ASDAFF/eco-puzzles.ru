<?
use Bitrix\Main\Localization\Loc,
	Bitrix\Main\Config\Option,
	Bitrix\Main\Page\Asset;
	
Loc::loadMessages(__FILE__);
Loc::loadMessages($_SERVER["DOCUMENT_ROOT"].BR_ROOT.'/modules/main/options.php');

$module_id='skyweb24.popuppro';
\Bitrix\Main\Loader::includeModule($module_id);
\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule('sale');
Asset::getInstance()->addJs('/bitrix/js/'.$module_id.'/script.js');

$request = \Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest();
$aTabs = [
	[
		"DIV" => "sw24_general_settings_main",
		"TAB" => Loc::getMessage("skyweb24.popuppro_GENERAL_MAIN"),
		"TITLE" => Loc::getMessage("skyweb24.popuppro_GENERAL_MAIN_TITLE"),
		"OPTIONS"=>[
			['popup_active', Loc::getMessage("skyweb24.popuppro_PARAM_ACTIVE"), '', ['checkbox']],
			
			
		]
	],
];
if($request->isPost() && $request['Update'] && check_bitrix_sessid()){
	foreach($aTabs as $aTab){
		if(!empty($aTab['OPTIONS'])){
			__AdmSettingsSaveOptions($module_id, $aTab['OPTIONS']);
		}
	}
}
?><form class="multiparser_settings" method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($module_id)?>&amp;lang=<?=LANGUAGE_ID?>"><?
$tabControl = new CAdminTabControl("tabControl_sw24", $aTabs);
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();

$notifyOverduechecked=(Bitrix\Main\Config\Option::get($module_id, 'popup_active')=='Y')?' checked="checked"':'';

?>
<tr><td>
<table width="100%">
	<tr class="heading">
		<td colspan="2"><?=GetMessage("skyweb24.popuppro_PARAM_MAIN")?></td>
	</tr>
	<tr>
		<td width="50%" class="adm-detail-content-cell-l"><?=GetMessage("skyweb24.popuppro_PARAM_ACTIVE")?></td>
		<td width="50%" class="adm-detail-content-cell-r"><input type="checkbox" id="popup_active" name="popup_active" value="Y"<?=$notifyOverduechecked?>></td>
	</tr>
	</table>
</td></tr>
	<?
	$tabControl->Buttons();
	?>
<input type="submit" name="Update" class="adm-btn-save" value="<?=Loc::getMessage("MAIN_SAVE")?>">
<input type="reset" name="reset" value="<?=Loc::getMessage("MAIN_RESET")?>">

<?=bitrix_sessid_post();?>

<?
$tabControl->End();
?>
</form>
