<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
if (!CModule::IncludeModule("iblock"))
    return;

__IncludeLang($_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/webfly/yandex.market/lang/ru/settings.php');

$optOutletId = $_POST["outletid"];
$optOutletInstock = $_POST["outletinstock"];
$optOutletBooking = $_POST["outletbooking"];

if(SITE_CHARSET != "UTF-8" && SITE_CHARSET != "utf-8"){
        $optOutletId = iconv("utf-8","windows-1251",$optOutletId);
        $optOutletInstock = iconv("utf-8","windows-1251",$optOutletInstock);
        $optOutletBooking = iconv("utf-8","windows-1251",$optOutletBooking);
    }

$arProps = array();
$iblockIds = explode(",", $_POST["iblock_id"]);
if (!empty($iblockIds) && $iblockIds[0] > 0)
{
    foreach ($iblockIds as $ibid)
    {
        $dbProperty = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $ibid));
        while ($arProperty = $dbProperty->Fetch()) {
            $arProps[$arProperty['CODE']."_WFYMPROP"] = "[{$arProperty['CODE']}] {$arProperty['NAME']}";
        }
    }
}
else
{
    $dbProperty = CIBlockProperty::GetList();
    while ($arProperty = $dbProperty->Fetch()) {
        $arProps[$arProperty['CODE']] = "[{$arProperty['CODE']}] {$arProperty['NAME']}";
    }
}

//choose stores 
if (CModule::IncludeModule("catalog"))
{
    $stores = CCatalogStore::GetList(array("sort" => "asc"), array("ACTIVE" => "Y"), false, false, array("*"));
    while ($ob_store = $stores->Fetch()) {
        $res_store[$ob_store["ID"]."_WFYMAMOUNT"] = "[" . $ob_store["ID"] . "] " . $ob_store["TITLE"];
    }
}
?>
<style type="text/css">
    .jc_OutletForm div{
        margin-top:10px;
    }
     .jc_OutletForm select
    {
        width:400px;
    }
    .jc_OutletForm input[type=text]
    {
        width:385px;
    }
</style>
<script type="text/javascript">
    var jcOutletForm = document.jc_OutletForm;
    function outletSelectChange() {
        var selId = this.id;
        switch (this.value) {
            case "WF_YM_WRITE":
                jcOutletForm[selId+"_input"].disabled = false;
                break;
            default:
                jcOutletForm[selId+"_input"].disabled = true;
                break;
        }
    }
</script>
<form class="jc_OutletForm" action="" id="jc_OutletForm" name="jc_OutletForm">
    <div>
        <label for="jc_OutletId"><?= GetMessage("OUTLET_ID") ?></label>
        <br />
        <select name="jc_OutletId" id="jc_OutletId" onchange="outletSelectChange.call(this);">
            <option value=""></option>
            <option value="WF_YM_WRITE"<?=(!empty($optOutletId) and substr_count($optOutletId, "WFYMAMOUNT")==0 and substr_count($optOutletId, "WFYMPROP")==0)?" selected":""?>><?= GetMessage("WF_YM_WRITE")?></option>
            <? if ($res_store): ?>
                <option value="outlet_sector" disabled><?= GetMessage("OUTLET_SECTOR") ?></option>
                <?
                foreach ($res_store as $skey => $store):
                    ?>
                    <option value="<?= $skey ?>" <? if ($optOutletId == $skey): ?>selected<? endif ?>><?= $store ?></option>
                    <?
                endforeach;
            endif;
            if ($arProps):
                ?>
                <option value="property_sector" disabled><?= GetMessage("PROPERTY_SECTOR") ?></option>
                <?
                foreach ($arProps as $key => $option):
                    ?>
                    <option value="<?= $key ?>" <? if ($optOutletId == $key): ?>selected<? endif ?>><?= $option ?></option>
                    <?
                endforeach;
            endif;
            ?>
        </select>
        <br/>
        <input name="jc_OutletId_input" id="jc_OutletId_input"
               type="text"<? if (!empty($optOutletId) and substr_count($optOutletId, "WFYMAMOUNT")==0 and substr_count($optOutletId, "WFYMPROP")==0): ?> value="<?= $optOutletId ?>"<? endif ?>
               <? if (empty($optOutletId) or (substr_count($optOutletId, "WFYMAMOUNT")>0 or substr_count($optOutletId, "WFYMPROP")>0)): ?> disabled<? endif ?> />
    </div>
    <div>
        <label for="jc_OutletInstock"><?= GetMessage("OUTLET_INSTOCK") ?></label>
        <br />
        <select name="jc_OutletInstock" id="jc_OutletInstock" onchange="outletSelectChange.call(this);">
            <option value=""></option>
            <option value="WF_YM_WRITE"<?=(!empty($optOutletInstock) and substr_count($optOutletInstock, "WFYMAMOUNT")==0 and substr_count($optOutletInstock, "WFYMPROP")==0)?" selected":""?>><?= GetMessage("WF_YM_WRITE")?></option>
            <? if ($res_store): ?>
                <option value="outlet_sector" disabled><?= GetMessage("OUTLET_SECTOR") ?></option>
                <?
                foreach ($res_store as $skey => $store):
                    ?>
                    <option value="<?= $skey ?>" <? if ($optOutletInstock == $skey): ?>selected<? endif ?>><?= $store ?></option>
                    <?
                endforeach;
            endif;
            if ($arProps):
                ?>
                <option value="property_sector" disabled><?= GetMessage("PROPERTY_SECTOR") ?></option>
                <?
                foreach ($arProps as $key => $option):
                    ?>
                    <option value="<?= $key ?>" <? if ($optOutletInstock == $key): ?>selected<? endif ?>><?= $option ?></option>
                    <?
                endforeach;
            endif;
            ?>
        </select>
         <br/>
        <input name="jc_OutletInstock_input" id="jc_OutletInstock_input"
               type="text"<? if (!empty($optOutletInstock) and substr_count($optOutletInstock, "WFYMAMOUNT")==0 and substr_count($optOutletInstock, "WFYMPROP")==0): ?> value="<?= $optOutletInstock ?>"<? endif ?>
               <? if (empty($optOutletInstock) or (substr_count($optOutletInstock, "WFYMAMOUNT")>0 or substr_count($optOutletInstock, "WFYMPROP")>0)): ?> disabled<? endif ?> />
    </div>
    <div>
        <label for="jc_OutletBooking"><?= GetMessage("OUTLET_BOOKING") ?></label>
        <br />
        <select name="jc_OutletBooking" id="jc_OutletBooking" onchange="outletSelectChange.call(this);">
            <option value=""></option>
            <option value="WF_YM_WRITE"<?=(!empty($optOutletBooking) and substr_count($optOutletBooking, "WFYMAMOUNT")==0 and substr_count($optOutletBooking, "WFYMPROP")==0)?" selected":""?>><?= GetMessage("WF_YM_WRITE")?></option>
            <? if ($res_store): ?>
                <option value="outlet_sector" disabled><?= GetMessage("OUTLET_SECTOR") ?></option>
                <?
                foreach ($res_store as $skey => $store):
                    ?>
                    <option value="<?= $skey ?>" <? if ($optOutletBooking == $skey): ?>selected<? endif ?>><?= $store ?></option>
                    <?
                endforeach;
            endif;
            if ($arProps):
                ?>
                <option value="property_sector" disabled><?= GetMessage("PROPERTY_SECTOR") ?></option>
                <?
                foreach ($arProps as $key => $option):
                    ?>
                    <option value="<?= $key ?>" <? if ($optOutletBooking == $key): ?>selected<? endif ?>><?= $option ?></option>
                    <?
                endforeach;
            endif;
            ?>
        </select>
         <br/>
        <input name="jc_OutletBooking_input" id="jc_OutletBooking_input"
               type="text"<? if (!empty($optOutletBooking) and substr_count($optOutletBooking, "WFYMAMOUNT")==0 and substr_count($optOutletBooking, "WFYMPROP")==0): ?> value="<?= $optOutletBooking ?>"<? endif ?>
               <? if (empty($optOutletBooking) or (substr_count($optOutletBooking, "WFYMAMOUNT")>0 or substr_count($optOutletBooking, "WFYMPROP")>0)): ?> disabled<? endif ?> />
    </div>
</form>