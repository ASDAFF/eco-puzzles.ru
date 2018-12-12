<?
global $APPLICATION;

use Bitrix\Main,
    Bitrix\Main\Loader,
    Bitrix\Main\Config\Option,
    Bitrix\Sale,
    Shiptor\Delivery\CShiptorAPI,
    Shiptor\Delivery\Options\Helper,
    Shiptor\Delivery\Options\Config;

Loader::includeModule("sale");

IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/options.php');
IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sale/options.php');

$MODULE_ID = "shiptor.delivery";

$SHIPTOR_RIGHT = $APPLICATION->GetGroupRight($MODULE_ID);
if( ! ($SHIPTOR_RIGHT >= "R"))
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$aTabs = array(
    array("DIV" => "shiptor_edit_settings","TAB" => GetMessage("MAIN_TAB_SET"),"TITLE" => GetMessage("SHIPTOR_EDITSETTINGS_TITLE")),
    array("DIV" => "shiptor_delivery_settings","TAB" => GetMessage("SHIPTOR_DELIVERY"),"TITLE" => GetMessage("SHIPTOR_DELIVERY_TITLE")),
    array("DIV" => "shiptor_statuses_settings","TAB" => GetMessage("SHIPTOR_STATUSES"),"TITLE" => GetMessage("SHIPTOR_STATUSES_TITLE")),
    array("DIV" => "shiptor_props_settings","TAB" => GetMessage("SHIPTOR_ORDER_PROPS"),"TITLE" => GetMessage("SHIPTOR_ORDER_PROPS_TITLE")),
    array("DIV" => "shiptor_upload_settings","TAB" => GetMessage("SHIPTOR_UPLOAD"),"TITLE" => GetMessage("SHIPTOR_UPLOAD_TITLE")),
    array("DIV" => "shiptor_rights","TAB" => GetMessage("MAIN_TAB_RIGHTS"),"TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS"))
);

$dbPaySystem = \Bitrix\Sale\PaySystem\Manager::getList(array('filter' => array('ACTIVE' => 'Y'),
        'select' => array("ID","NAME","IS_CASH")));
$arCashes = array();
while ($arPaySystem = $dbPaySystem->fetch()){
    if($arPaySystem["IS_CASH"] == "Y"){
        $arCashes[] = $arPaySystem["ID"];
    }
    $arPaySystems[$arPaySystem["ID"]] = "[".$arPaySystem["ID"]."] ".$arPaySystem["NAME"];
}

$tabControl = new CAdminTabControl("tabControl",$aTabs);
$tabControl->Begin();
$api = CShiptorAPI::getInstance();
$arStatuses = $api->getStatusOutgoingPackage();

$arPvzStrictOptions = array(
    "N" => GetMessage('SHIPTOR_SETTINGS_STRICT_PVZ_N'),
    "Y" => GetMessage('SHIPTOR_SETTINGS_STRICT_PVZ_Y')
);
$arYNOptions = array("1" => GetMessage('SHIPTOR_YES'), "0" => GetMessage('SHIPTOR_NO'));
$arProductArticles = array(
    "ID" => GetMessage('SHIPTOR_ARTICLE_AS_ID'),
    "XML_ID" => GetMessage('SHIPTOR_ARTICLE_AS_XML_ID'),
    "PROP" => GetMessage('SHIPTOR_ARTICLE_AS_PROP')
);
$arAddressTypeOptions = array(
    Config::ADDRESS_SIMPLE => GetMessage('SHIPTOR_SETTINGS_ADDRESS_TYPE_SIMPLE'),
    Config::ADDRESS_COMPLEX => GetMessage('SHIPTOR_SETTINGS_ADDRESS_TYPE_COMPLEX')
);
$arSortOptions = array(
    "" => GetMessage('SHIPTOR_SETTINGS_SORT_PROFILES_NONE'),
    Config::SORT_PROFILES_DAYS => GetMessage('SHIPTOR_SETTINGS_SORT_PROFILES_DAYS'),
    Config::SORT_PROFILES_PRICE => GetMessage('SHIPTOR_SETTINGS_SORT_PROFILES_PRICE'),
);
$roundNone = Config::ROUND_TYPE_NONE;
$arRoundingTypes = array(
    $roundNone => GetMessage("SHIPTOR_EDITSETTINGS_ROUND_TYPE_NONE"),
    Config::ROUND_TYPE_MATH => GetMessage("SHIPTOR_EDITSETTINGS_ROUND_TYPE_MATH"),
    Config::ROUND_TYPE_FLOOR => GetMessage("SHIPTOR_EDITSETTINGS_ROUND_TYPE_FLOOR"),
    Config::ROUND_TYPE_CEIL => GetMessage("SHIPTOR_EDITSETTINGS_ROUND_TYPE_CEIL")
);

$arOptions = array(
    "shiptor_edit_settings" => array(
        "NOTE" => array("type" => "NOTE", "name" => GetMessage("SHIPTOR_NOTE")),
        'adminApiKey' => array("type" => "text", "size" => "50", "name" => GetMessage('SHIPTOR_SETTINGS_ADMINAPIKEY_NAME'), "hint" => GetMessage('SHIPTOR_SETTINGS_ADMINAPIKEY_NAME_HINT'), 'href' => 'https://shiptor.ru/account/settings/api'),
        'apiUrl' => array("type" => "text", "size" => "50", "name" => GetMessage('SHIPTOR_SETTINGS_APIURL_NAME'), "default" => "http://api.shiptor.ru", "disabled" => "true"),
        'checkout' => array("type" => "text", "size" => "50", "name" => GetMessage('SHIPTOR_SETTINGS_CHEKOUT_URL_TITLE'), "default" => "/personal/order/make/", "hint" => GetMessage('SHIPTOR_SETTINGS_CHEKOUT_URL_TITLE_HINT')),
        'rounding_type' => array("type" => "select", "multiple" => false, "options" => $arRoundingTypes, "onchange" => <<<JS
            if(this.value === '{$roundNone}'){this.form['rounding_precision'].disabled = true;}
            else{this.form['rounding_precision'].disabled = false;}
JS
            , "name" => GetMessage('SHIPTOR_SETTINGS_ROUND'), 'hint' => GetMessage('SHIPTOR_SETTINGS_ROUND_HINT')),
        'rounding_precision' => array("type" => "text", "onchange" => <<<JS
            if(this.value < 0.01 || isNaN(this.value)){this.value = 0.01;}
JS
            , "name" => GetMessage('SHIPTOR_SETTINGS_ROUND_PRECISION'), "default" => 0.01, 'hint' => GetMessage('SHIPTOR_SETTINGS_ROUND_PRECISION_HINT')),
        //'is_fulfilment' => array("type" => "select", "multiple" => false, "options" => $arYNOptions, "name" => GetMessage('SHIPTOR_SETTINGS_IS_FULLFILMENT'), "hint" => GetMessage('SHIPTOR_SETTINGS_IS_FULLFILMENT_HINT'), 'default' => "1"),
        'is_pvz_haunt' => array("type" => "select", "multiple" => false, "options" => $arYNOptions, "name" => GetMessage('SHIPTOR_SETTINGS_PVZ_HAUNT'), "hint" => GetMessage('SHIPTOR_SETTINGS_PVZ_HAUNT_HINT')),
        'is_date_time_mirror' => array("type" => "select", "multiple" => false, "options" => $arYNOptions, "name" => GetMessage('SHIPTOR_SETTINGS_DATE_TIME_MIRROR'), "hint" => GetMessage('SHIPTOR_SETTINGS_DATE_TIME_MIRROR_HINT')),            
        "include_yamaps" => array("type" => "select", "multiple" => false, "options" => $arYNOptions, "name" => GetMessage('SHIPTOR_SETTINGS_INCLUDE_YAMAPS'), "hint" => GetMessage('SHIPTOR_SETTINGS_INCLUDE_YAMAPS_HINT')),
        "sortProfiles" => array("type" => "select", "multiple" => false, "options" => $arSortOptions, "name" => GetMessage('SHIPTOR_SETTINGS_SORT_PROFILES'), "hint" => GetMessage('SHIPTOR_SETTINGS_SORT_PROFILES_HINT')),
        "pvzStrict" => array("type" => "select", "multiple" => false, "options" => $arPvzStrictOptions, "name" => GetMessage('SHIPTOR_SETTINGS_STRICT_PVZ'), "hint" => GetMessage('SHIPTOR_SETTINGS_STRICT_PVZ_HINT')),
        "debug" => array("type" => "select", "multiple" => false, "options" => $arYNOptions, "name" => GetMessage('SHIPTOR_SETTINGS_DEBUG'), "hint" => GetMessage('SHIPTOR_SETTINGS_DEBUG_HINT'), 'href' => '/bitrix/admin/event_log.php?lang=ru'),
        "productArticle" => array("type" => "select", "multiple" => false, "options" => $arProductArticles, "name" => GetMessage('SHIPTOR_PRODUCT_ARTICLE'), "onchange" => "this.form.submit();", "hint" => GetMessage('SHIPTOR_PRODUCT_ARTICLE_HINT')),
    ),
    "shiptor_props_settings" => array(
        "NOTE" => array("type" => "NOTE", "name" => GetMessage("SHIPTOR_NOTE3")),
        "address_type" => array("type" => "select", "multiple" => false, "onchange" => "this.form.submit();", "options" => $arAddressTypeOptions, "name" => GetMessage('SHIPTOR_SETTINGS_ADDRESS_TYPE'))
    )
);
if($shiptorSettings["rounding_type"] == $roundNone){
    $arOptions["shiptor_edit_settings"]["rounding_precision"]["disabled"] = "true";
}
if(empty($shiptorSettings["direct_zip"])){
    $shiptorSettings["direct_zip"] = Option::get("sale","location_zip");
}
$arDirectDateOptions = array(
    Config::DATE_NEAR => GetMessage("SHIPTOR_DIRECT_DATE_TYPE_NEAR"),
    Config::DATE_DELAY => GetMessage("SHIPTOR_DIRECT_DATE_TYPE_DELAY")
);
$arOptions["shiptor_delivery_settings"] = array(
    "NOTE" => array("type" => "NOTE", "name" => GetMessage("SHIPTOR_NOTE2")),
    "NOTE_reciever" => array("type" => "heading", "name" => GetMessage("SHIPTOR_DIRECT_RECIEVER_SETTINGS")),
    "direct_reciever" => array("type" => "text", "size" => 50, "name" => GetMessage("SHIPTOR_DIRECT_RECIEVER")),
    "direct_phone" => array("type" => "text", "size" => 20, "name" => GetMessage("SHIPTOR_DIRECT_PHONE")),
    "direct_email" => array("type" => "email", "size" => 50, "name" => GetMessage("SHIPTOR_DIRECT_EMAIL")),
    "NOTE_address" => array("type" => "heading", "name" => GetMessage("SHIPTOR_DIRECT_ADDRESS_SETTINGS")),
    "direct_zip" => array("type" => "text", "size" => 10, "name" => GetMessage("SHIPTOR_DIRECT_ADDRESS_ZIP")),
    "direct_street" => array("type" => "text", "size" => 50, "name" => GetMessage("SHIPTOR_DIRECT_ADDRESS_STREET")),
    "direct_house" => array("type" => "text", "size" => 10, "name" => GetMessage("SHIPTOR_DIRECT_ADDRESS_HOUSE")),
    "direct_flat" => array("type" => "text", "size" => 10, "name" => GetMessage("SHIPTOR_DIRECT_ADDRESS_FLAT")),
    "direct_comment" => array("type" => "textarea", "name" => GetMessage("SHIPTOR_DIRECT_ADDRESS_COMMENT")),
    "NOTE_date" => array("type" => "heading", "name" => GetMessage("SHIPTOR_DIRECT_DATE_SETTINGS")),
    "direct_date_type" => array("type" => "select", "multiple" => false, "onchange" => "this.form.submit();", "options" => $arDirectDateOptions, "name" => GetMessage('SHIPTOR_DIRECT_DATE_TYPE'))
);

if($shiptorSettings["direct_date_type"] == "delay"){
    $arOptions["shiptor_delivery_settings"]["direct_date_delay"] = array("type" => "text", "size" => 15, "name" => GetMessage("SHIPTOR_DIRECT_DATE_DELAY"));
}
$arPersonTypes = CShiptorDeliveryHelper::getOrderProps();

$isAddressSimple = (bool)($shiptorSettings['address_type'] == Config::ADDRESS_SIMPLE || empty($shiptorSettings['address_type']));

foreach($arPersonTypes as $key => $personType){
    if(!empty($personType["PROPS"])){
        if($isAddressSimple){
            $arOptions['shiptor_props_settings']["address_prop_id_{$key}"] =
                array("type" => "select", "multiple" => false, "options" => $personType["PROPS"], "name" => GetMessage('SHIPTOR_SETTINGS_ADDRESS_PROP_ID',array("#PERSON_TYPE#" => $personType["NAME"])));
        }else{
            $arOptions['shiptor_props_settings']["street_prop_id_{$key}"] =
                array("type" => "select", "multiple" => false, "options" => $personType["PROPS"], "name" => GetMessage('SHIPTOR_SETTINGS_STREET_PROP_ID',array("#PERSON_TYPE#" => $personType["NAME"])));
            $arOptions['shiptor_props_settings']["bld_prop_id_{$key}"] =
                array("type" => "select", "multiple" => false, "options" => $personType["PROPS"], "name" => GetMessage('SHIPTOR_SETTINGS_BLD_PROP_ID',array("#PERSON_TYPE#" => $personType["NAME"])));
            $arOptions['shiptor_props_settings']["corp_prop_id_{$key}"] =
                array("type" => "select", "multiple" => false, "options" => $personType["PROPS"], "name" => GetMessage('SHIPTOR_SETTINGS_CORP_PROP_ID',array("#PERSON_TYPE#" => $personType["NAME"])));
            $arOptions['shiptor_props_settings']["flat_prop_id_{$key}"] =
                array("type" => "select", "multiple" => false, "options" => $personType["PROPS"], "name" => GetMessage('SHIPTOR_SETTINGS_FLAT_PROP_ID',array("#PERSON_TYPE#" => $personType["NAME"])));
        }
        $arOptions['shiptor_props_settings']["pvz_prop_{$key}"] =
                array("type" => "select", "multiple" => false, "options" => $personType["PROPS"], "name" => GetMessage('SHIPTOR_SETTINGS_PVZ_PROP_ID',array("#PERSON_TYPE#" => $personType["NAME"])));
    }
}
if($isAddressSimple){
    $arOptions['shiptor_props_settings']['mirror_pvz_address'] = array("type" => "select", "multiple" => false, "options" => $arYNOptions, "name" => GetMessage('SHIPTOR_SETTINGS_MIRROR_PVZ_ADDRESS'));
}
switch($shiptorSettings['productArticle']){
    case "PROP":
        Loader::includeModule("iblock");
        $arOrder = array("SORT" => "DESC", "NAME" => "DESC");
        $arFilter = array("PROPERTY_TYPE" => "S", "ACTIVE" => "Y", "MULTIPLE" => "N");

        $dbProps = CIBlockProperty::GetList($arOrder,$arFilter);
        $arProps = array(0 => "---");
        while($arItem = $dbProps->Fetch()){
            $id = $arItem["ID"]."|".$arItem["VERSION"]."|".$arItem["MULTIPLE"];
            $arProps[$id] = "[{$arItem["ID"]}] ".$arItem["NAME"]." (".$arItem["CODE"].")";
        }
        $arOptions["shiptor_edit_settings"]["articleProperty"] = array(
            "type" => "select", "multiple" => false, "options" => $arProps, "name" => GetMessage('SHIPTOR_CAT_PROPERTY')
        );
        break;
    case "XML_ID":
        $arOptions["shiptor_edit_settings"]["xmlIdComplex"] = array(
            "type" => "select", "multiple" => false, "options" => $arYNOptions, "name" => GetMessage('SHIPTOR_COMPLEX_XML_ID')
        );
        break;
}
$arOptions["shiptor_edit_settings"]["cashPaymentsIds"] = array(
    "type" => "select", "multiple" => true, "options" => $arPaySystems, "name" => GetMessage('SHIPTOR_CASH_PAYMENTS')
);
$shipmentStatuses = array();
$context = Main\Application::getInstance()->getContext();
$dbRes = Sale\Internals\StatusTable::getList(array(
        'select' => array('ID','Bitrix\Sale\Internals\StatusLangTable:STATUS.NAME'),
        'filter' => array(
            '=Bitrix\Sale\Internals\StatusLangTable:STATUS.LID' => $context->getLanguage(),
            '=TYPE' => 'D'
        ),
        'order' => array('SORT' => 'ASC')
));

while($shipmentStatus = $dbRes->fetch()){
    $shipmentStatuses[$shipmentStatus["ID"]] = $shipmentStatus["SALE_INTERNALS_STATUS_SALE_INTERNALS_STATUS_LANG_STATUS_NAME"] . " [" . $shipmentStatus["ID"] . "]";
}
$arUploadTypes = array(
    Config::DEPARTURE_TYPE_AUTO => GetMessage("SHIPTOR_SETTINGS_UPLOAD_TYPE_AUTO"),
    Config::DEPARTURE_TYPE_MAN => GetMessage("SHIPTOR_SETTINGS_UPLOAD_TYPE_MAN")
);
$arOptions["shiptor_upload_settings"] = array(
    'NOTE5' => array("type" => "NOTE", "name" => GetMessage("SHIPTOR_NOTE5")),
    "departure_type" => array("type" => "select", "multiple" => false, "options" => $arUploadTypes, "name" => GetMessage('SHIPTOR_SETTINGS_UPLOAD_TYPE'), "onchange" => "this.form.submit();")
);
if(Config::DEPARTURE_TYPE_AUTO == $shiptorSettings['departure_type']){
    $arOptions["shiptor_upload_settings"]['departure_status'] = array(
        "type" => "select", "multiple" => false, "options" => $shipmentStatuses, "name" => GetMessage('SHIPTOR_SETTINGS_UPLOAD_STATUS'), "onchange" => "checkDoubleStatuses(this);"
    );
}
$arOptions["shiptor_upload_settings"]['change_status'] = array(
        "type" => "select", "multiple" => false, "options" => $shipmentStatuses, "name" => GetMessage('SHIPTOR_SETTINGS_UPLOAD_STATUS_AFTER'), "onchange" => "checkDoubleStatuses(this);"
    );
?>

<form method="POST" action="<?= $APPLICATION->GetCurPage() . '?mid=' . $MODULE_ID . '&amp;lang=' . LANG . '&amp;mid_menu=1';?>" name="form1" onSubmit="return prepareData()">
    <input type="hidden" name="lang" value="<?=LANG?>" />
    <input type="hidden" name="SID" value="<?=htmlspecialchars($SID)?>" />
    <?=bitrix_sessid_post()?>
    <?$tabControl->BeginNextTab();?>
    <tr class="heading">
        <td colspan="2"><?=GetMessage("SALE_SERVICE_AREA")?></td>
    </tr>
    <?
    Helper::generate($arOptions["shiptor_edit_settings"],$shiptorSettings);
    $tabControl->BeginNextTab();
    Helper::generate($arOptions["shiptor_delivery_settings"],$shiptorSettings);
    $tabControl->BeginNextTab();
    ?>
    <td colspan="2" align="center">
        <?=BeginNote();?>
        <img src="/bitrix/js/main/core/images/hint.gif" style="margin-right: 5px;"/><?=GetMessage("SHIPTOR_NOTE4")?>
        <?=EndNote();?>
    </td>
    <?
    if(!is_array($shiptorSettings["tracking_map_statuses"])){
        $shiptorSettings["tracking_map_statuses"] = unserialize($shiptorSettings["tracking_map_statuses"]);
    }
    ?>
    <tr>
        <td class="field-name" colspan="2" style="text-align: center;"><h3><?=GetMessage("SHIPTOR_TRACKING_HEAD")?></h3></td>
    </tr>
    <tr class="sale-option-tracking-auto">
        <td class="field-name">
            <b><?=GetMessage("SHIPTOR_LIST_STATUSES_B")?></b>
        </td>
        <td>
            <b><?=GetMessage("SHIPTOR_LIST_STATUSES")?></b>
        </td>
    </tr>
    <?foreach($shipmentStatuses as $sStatusId => $sStatusName):?>
        <tr class="sale-option-tracking-auto">
            <td class="field-name"><?=$sStatusName?>:</td>
            <td>
                <select name="tracking_map_statuses[<?=$sStatusId?>]">
                    <option value=""><?=GetMessage("SALE_TRACKING_NOT_USE")?></option>
                    <?foreach($arStatuses as $tStatusId => $tStatusName):?>
                        <option value="<?=$tStatusId?>"<?= !empty($shiptorSettings["tracking_map_statuses"][$sStatusId]) && $shiptorSettings["tracking_map_statuses"][$sStatusId] == $tStatusId ? " selected" : ""?>><?=$tStatusName?> (<?=$tStatusId?>)</option>
                    <?endforeach;?>
                </select>
            </td>
        </tr>
    <?endforeach;
    $tabControl->BeginNextTab();
    Helper::generate($arOptions["shiptor_props_settings"],$shiptorSettings);
    $tabControl->BeginNextTab();
    Helper::generate($arOptions["shiptor_upload_settings"],$shiptorSettings);
    ?>
    <tr>
        <td class="field-name">
            <b><?=GetMessage("SHIPTOR_CHECK_AGENT")?></b>
        </td>
        <td>
            <?php
            $agentId = CShiptorDeliveryHelper::createAgentIfNone();
            ?>
            <a href='/bitrix/admin/agent_edit.php?ID=<?=$agentId?>&lang=ru' target='_blank'><?=GetMessage("SHIPTOR_CHECK_AGENT_URL")?></a>
            <img src="/bitrix/js/main/core/images/hint.gif" style="margin-left: 5px;" title='<?=GetMessage("SHIPTOR_CHECK_AGENT_INFO")?>'/>
        </td>
    </tr>
    <?
    $tabControl->BeginNextTab();
    $module_id = $MODULE_ID;
    $Update = $_REQUEST["Update"].$_REQUEST["Apply"];
    $REQUEST_METHOD = $_SERVER["REQUEST_METHOD"];
    $GROUPS = $_REQUEST["GROUPS"];
    $RIGHTS = $_REQUEST["RIGHTS"];
    ?>
    <?require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/admin/group_rights.php");?>
    <?$tabControl->Buttons();?>
    <script type="text/javascript">
        function RestoreDefaults(){
            if (confirm('<?= AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>'))
                window.location = "<?= $APPLICATION->GetCurPage()?>?RestoreDefaults=Y&lang=<?= LANG?>&mid=<?= urlencode($mid)?>&<?=bitrix_sessid_get()?>";
        }
        BX.ready(function(){
            checkDoubleStatuses(document.querySelector('[name="departure_status"]'));
        });
        function checkDoubleStatuses(source){
            var departureAutomatic = document.querySelector('[name="departure_type"]'),
                valuesListConts,
                arStatuses = [],
                departureStatusSelect = document.querySelector('[name="departure_status"]'),
                changeStatusSelect = document.querySelector('[name="change_status"]');
            if(!!departureAutomatic && !!departureStatusSelect){
                if(departureAutomatic.value === 'auto' && departureStatusSelect.value == changeStatusSelect.value){
                    valuesListConts = changeStatusSelect.querySelectorAll('option');
                    for(var i = 0; i < valuesListConts.length; i++){
                        if(departureStatusSelect.value != valuesListConts[i].getAttribute('value')){
                            arStatuses.push(valuesListConts[i].getAttribute('value'));
                        }
                    }
                    switch(source.getAttribute('name')){
                        case "departure_status":
                            changeStatusSelect.value = arStatuses[0];
                            break;
                        case "change_status":
                            departureStatusSelect.value = arStatuses[0];
                            break;

                    }
                }
            }
        }
    </script>
    <input type="submit" <?if($SHIPTOR_RIGHT < "W") echo "disabled"?> name="Update" value="<?echo GetMessage("MAIN_SAVE")?>">
    <input type="hidden" name="Update" value="Y">
    <input type="reset" name="reset" value="<?echo GetMessage("MAIN_RESET")?>">
    <?$tabControl->End();?>
    <?/*
      $tabControl->BeginNextTab(); ?>
      <tr>
      <td><?=$shiptorLog?></td>
      </tr>
      <?$tabControl->Buttons();?>
      <? $shiptor_RIGHT = $APPLICATION->GetGroupRight('shiptor.delivery'); ?>
      <input type="submit" <?if ($shiptor_RIGHT<"W") echo "disabled" ?> name="Update" value="<?echo GetMessage("SHIPTOR_SETTINGS_SAVE_TITLE")?>"/>
      <input type="hidden" name="save" value="Y"/>
      <?$tabControl->End();
     */?>
</form>