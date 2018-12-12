<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
if (!CModule::IncludeModule("iblock")) return;

__IncludeLang($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/webfly/yandex.market/lang/ru/settings.php');

$optCost = $_POST["cost"];
$optDays = $_POST["days"];
$optOrderBefore = $_POST["orderBefore"];

$arProps = array();
$iblockIds = explode(",",$_POST["iblock_id"]);
if(!empty($iblockIds) && $iblockIds[0] > 0){
    foreach($iblockIds as $ibid){
        $dbProperty = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $ibid));
        while ($arProperty = $dbProperty->Fetch()){
            $arProps[$arProperty['CODE']] = "[{$arProperty['CODE']}] {$arProperty['NAME']}";
        }
    }
}else{
    $dbProperty = CIBlockProperty::GetList();
    while ($arProperty = $dbProperty->Fetch()){
        $arProps[$arProperty['CODE']] = "[{$arProperty['CODE']}] {$arProperty['NAME']}";
    }
}
?>
<form class="jc_form" action="">
    <label for="jc_cost"><?=GetMessage("DELIVERY_COST_NAME")?></label>
    <select name="jc_cost" id="jc_cost">
        <option value=""></option>
        <?foreach($arProps as $key => $option):?>
            <option value="<?=$key?>" <?if($optCost == $key):?>selected<?endif?>><?=$option?></option>
        <?endforeach?>
    </select>
    <label for="jc_days"><?=GetMessage("DELIVERY_DAYS_NAME")?></label>
    <select name="jc_days" id="jc_days">
        <option value=""></option>
        <?foreach($arProps as $key => $option):?>
            <option value="<?=$key?>" <?if($optDays == $key):?>selected<?endif?>><?=$option?></option>
        <?endforeach?>
    </select>
    <label for="jc_orderBefore"><?=GetMessage("DELIVERY_ORDER_TIME")?></label>
    <select name="jc_orderBefore" id="jc_orderBefore">
        <option value=""></option>
        <?foreach($arProps as $key => $option):?>
            <option value="<?=$key?>" <?if($optOrderBefore == $key):?>selected<?endif?>><?=$option?></option>
        <?endforeach?>
    </select>
</form>