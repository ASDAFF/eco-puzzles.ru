<?php
use Bitrix\Main\Entity,
    Bitrix\Sale\Internals\OrderPropsTable,
    Bitrix\Sale\Internals\PersonTypeTable;

$arConfig = $_SESSION['_SHIPTOR'];

$arSelectPt = array("ID","NAME_WS");
$arOrderPt = array("SORT" => "ASC");
$arFilter = array("ACTIVE" => "Y");
$arRuntime = array(new Entity\ExpressionField('NAME_WS', "CONCAT('(',%s,') ',%s)",array("LID","NAME")));
$dbPersonTypes = PersonTypeTable::getList(array("select" => $arSelectPt, "order" => $arOrderPt,
        "filter" => $arFilter, "runtime" => $arRuntime));
$arPersonTypes = array();
while($arPersonType = $dbPersonTypes->fetch()){
    $arPersonTypes[$arPersonType["ID"]] = array("NAME" => $arPersonType["NAME_WS"]);
}
$arSelect = array("ID","PERSON_TYPE_ID","NAME");
$arOrder = array("ID" => "ASC");
$arFilter["PERSON_TYPE_ID"] = array_keys($arPersonTypes);
$dbOrderProps = OrderPropsTable::getList(array("select" => $arSelect, "filter" => $arFilter,
        "order" => $arOrder));
while($arProp = $dbOrderProps->fetch()){
    $arProp["NAME"] = "[{$arProp["ID"]}] ".$arProp["NAME"];
    $arPersonTypes[$arProp["PERSON_TYPE_ID"]]["PROPS"][$arProp["ID"]] = $arProp["NAME"];
}

?>
<form action="<?= $APPLICATION->GetCurPage() ?>" name="shiptor_delivery_install" style="background-color: white;width: 70%;padding: 15px;border-radius: 6px;">
<?= bitrix_sessid_post() ?>
    <input type="hidden" name="lang" value="<?= LANG ?>"/>
    <input type="hidden" name="id" value="shiptor.delivery"/>
    <input type="hidden" name="install" value="Y"/>
    <input type="hidden" name="step" value="3"/>

    <table cellpadding="3" cellspacing="0" border="0" width="100%">
        <tr>
            <td colspan="2"><h3 style="border-bottom: 1px solid #808080;padding-bottom: 6px;"><?= GetMessage('WSD_STEP2_DEFAULT_SIZE_PARAMS') ?></h3></td>
        </tr>
        <tr>
            <td width="20%"><b><?=GetMessage('WSD_STEP2_DP_LENGTH')?></b></td>
            <td width="60%"><input type="text" name="shd_length" value="<?=$arConfig["MAIN"]["LENGTH_VALUE"]?>" id="shd_length" size="10" style="font-size:13px;height:25px;padding:0 5px;margin:0;border-radius:4px;color:#000;display:inline-block;outline:none;vertical-align:middle;box-shadow: 0 1px 0 0 rgba(255,255,255,0.3), inset 0 2px 2px -1px rgba(180,188,191,0.7);border: 1px solid;border-color: #87919c #959ea9 #9ea7b1 #959ea9;"/></td>
        </tr>
        <tr>
            <td width="20%"><b><?=GetMessage('WSD_STEP2_DP_WIDTH')?></b></td>
            <td width="60%"><input type="text" name="shd_width" value="<?=$arConfig["MAIN"]["WIDTH_VALUE"]?>" id="shd_width" size="10" style="font-size:13px;height:25px;padding:0 5px;margin:0;border-radius:4px;color:#000;display:inline-block;outline:none;vertical-align:middle;box-shadow: 0 1px 0 0 rgba(255,255,255,0.3), inset 0 2px 2px -1px rgba(180,188,191,0.7);border: 1px solid;border-color: #87919c #959ea9 #9ea7b1 #959ea9;"/></td>
        </tr>
        <tr>
            <td width="20%"><b><?=GetMessage('WSD_STEP2_DP_HEIGHT')?></b></td>
            <td width="60%"><input type="text" name="shd_height" value="<?=$arConfig["MAIN"]["HEIGHT_VALUE"]?>" id="shd_height" size="10" style="font-size:13px;height:25px;padding:0 5px;margin:0;border-radius:4px;color:#000;display:inline-block;outline:none;vertical-align:middle;box-shadow: 0 1px 0 0 rgba(255,255,255,0.3), inset 0 2px 2px -1px rgba(180,188,191,0.7);border: 1px solid;border-color: #87919c #959ea9 #9ea7b1 #959ea9;"/></td>
        </tr>
        <tr>
            <td width="20%"><b><?=GetMessage('WSD_STEP2_DP_WEIGHT')?></b></td>
            <td width="60%"><input type="text" name="shd_weight" value="<?=$arConfig["MAIN"]["WEIGHT_VALUE"]?>" id="shd_weight" size="10" style="font-size:13px;height:25px;padding:0 5px;margin:0;border-radius:4px;color:#000;display:inline-block;outline:none;vertical-align:middle;box-shadow: 0 1px 0 0 rgba(255,255,255,0.3), inset 0 2px 2px -1px rgba(180,188,191,0.7);border: 1px solid;border-color: #87919c #959ea9 #9ea7b1 #959ea9;"/></td>
        </tr>
        <tr>
            <td colspan="2"><h3 style="border-bottom: 1px solid #808080;padding-bottom: 6px;"><?= GetMessage('WSD_STEP2_DEFAULT_CALC_ALGO') ?></h3></td>
        </tr>
        <tr>
            <td width="40%" style="vertical-align: top;"><b><?=GetMessage('WSD_STEP2_DP_CALC_ALGORITM')?><b></td>
            <td width="60%">
                <select name="shd_calc" id="shd_calc" style="background: #fff;border-radius: 4px;border: 1px solid #a3a5a5;color: #000;box-shadow: 0 1px 0 0 rgba(255,255,255,0.3), inset 0 2px 2px -1px rgba(180,188,191,0.7);font-size: 13px;font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;height: 27px;margin: 0;vertical-align: middle;padding: 4px;">
                    <option value="N" <?if($arConfig["MAIN"]["CALC_ALGORITM"] == "N"):?> selected<?endif?>><?=GetMessage('WSD_STEP2_DEFAULT_CALC_ALGO_SIMPLE')?></option>
                    <option value="Y" <?if($arConfig["MAIN"]["CALC_ALGORITM"] == "Y"):?> selected<?endif?>><?=GetMessage('WSD_STEP2_DEFAULT_CALC_ALGO_COMPLEX')?></option>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <p style="display:none;" id="shd_calc_simple" class="adm-info-message"><?=GetMessage('WSD_STEP2_DEFAULT_CA_SIMPLE')?></p>
                <p style="display:none;" id="shd_calc_complex" class="adm-info-message"><?=GetMessage('WSD_STEP2_DEFAULT_CA_COMPLEX')?></p>
            </td>
        </tr>
        <tr>
            <td colspan="2"><h3 style="border-bottom: 1px solid #808080;padding-bottom: 6px;"><?= GetMessage('WSD_STEP2_ADDRESS_PROPS') ?></h3></td>
        </tr>
        <tr>
            <td width="40%"><b><?=GetMessage('WSD_STEP2_ADDRESS_TYPE')?><b></td>
            <td width="60%">
                <select name="shd_address_type" id="shd_address_type" 
                        style="background: #fff;border-radius: 4px;border: 1px solid #a3a5a5;color: #000;box-shadow: 0 1px 0 0 rgba(255,255,255,0.3), inset 0 2px 2px -1px rgba(180,188,191,0.7);font-size: 13px;font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;height: 27px;margin: 0;vertical-align: middle;padding: 4px;">
                    <option value="simple"><?=GetMessage('WSD_STEP2_ADDRESS_TYPE_SIMPLE')?></option>
                    <option value="complex"><?=GetMessage('WSD_STEP2_ADDRESS_TYPE_COMPLEX')?></option>
                </select>
            </td>
        </tr>
        <?foreach($arPersonTypes as $key => $personType):?>
            <tr data-address="simple" style="display:table-row">
                <td width="40%"><b><?=GetMessage('WSD_STEP2_ADDRESS_PROP_ID', array("#PERSON_TYPE#" => $personType["NAME"]))?><b></td>
                <td width="60%">
                    <select name="shd_address_prop[<?=$key?>]" id="shd_address_prop_<?=$key?>" 
                            style="background: #fff;border-radius: 4px;border: 1px solid #a3a5a5;color: #000;box-shadow: 0 1px 0 0 rgba(255,255,255,0.3), inset 0 2px 2px -1px rgba(180,188,191,0.7);font-size: 13px;font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;height: 27px;margin: 0;vertical-align: middle;padding: 4px;">
                        <?foreach($personType['PROPS'] as $value => $text):?>
                            <option value="<?=$value?>"><?=$text?></option>
                        <?endforeach?>
                    </select>
                </td>
            </tr>
            <tr data-address="complex" style="display:none;">
                <td width="40%"><b><?=GetMessage('WSD_STEP2_STREET_PROP_ID', array("#PERSON_TYPE#" => $personType["NAME"]))?><b></td>
                <td width="60%">
                    <select name="shd_street_prop[<?=$key?>]" id="shd_street_prop_<?=$key?>" 
                            style="background: #fff;border-radius: 4px;border: 1px solid #a3a5a5;color: #000;box-shadow: 0 1px 0 0 rgba(255,255,255,0.3), inset 0 2px 2px -1px rgba(180,188,191,0.7);font-size: 13px;font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;height: 27px;margin: 0;vertical-align: middle;padding: 4px;">
                        <?foreach($personType['PROPS'] as $value => $text):?>
                            <option value="<?=$value?>"><?=$text?></option>
                        <?endforeach?>
                    </select>
                </td>
            </tr>
            <tr data-address="complex" style="display:none;">
                <td width="40%"><b><?=GetMessage('WSD_STEP2_BLD_PROP_ID', array("#PERSON_TYPE#" => $personType["NAME"]))?><b></td>
                <td width="60%">
                    <select name="shd_bld_prop[<?=$key?>]" id="shd_bld_prop_<?=$key?>" 
                            style="background: #fff;border-radius: 4px;border: 1px solid #a3a5a5;color: #000;box-shadow: 0 1px 0 0 rgba(255,255,255,0.3), inset 0 2px 2px -1px rgba(180,188,191,0.7);font-size: 13px;font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;height: 27px;margin: 0;vertical-align: middle;padding: 4px;">
                        <?foreach($personType['PROPS'] as $value => $text):?>
                            <option value="<?=$value?>"><?=$text?></option>
                        <?endforeach?>
                    </select>
                </td>
            </tr>
            <tr data-address="complex" style="display:none;">
                <td width="40%"><b><?=GetMessage('WSD_STEP2_CORP_PROP_ID', array("#PERSON_TYPE#" => $personType["NAME"]))?><b></td>
                <td width="60%">
                    <select name="shd_corp_prop[<?=$key?>]" id="shd_corp_prop_<?=$key?>" 
                            style="background: #fff;border-radius: 4px;border: 1px solid #a3a5a5;color: #000;box-shadow: 0 1px 0 0 rgba(255,255,255,0.3), inset 0 2px 2px -1px rgba(180,188,191,0.7);font-size: 13px;font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;height: 27px;margin: 0;vertical-align: middle;padding: 4px;">
                        <?foreach($personType['PROPS'] as $value => $text):?>
                            <option value="<?=$value?>"><?=$text?></option>
                        <?endforeach?>
                    </select>
                </td>
            </tr>
            <tr data-address="complex" style="display:none;">
                <td width="40%"><b><?=GetMessage('WSD_STEP2_FLAT_PROP_ID', array("#PERSON_TYPE#" => $personType["NAME"]))?><b></td>
                <td width="60%">
                    <select name="shd_flat_prop[<?=$key?>]" id="shd_flat_prop_<?=$key?>" 
                            style="background: #fff;border-radius: 4px;border: 1px solid #a3a5a5;color: #000;box-shadow: 0 1px 0 0 rgba(255,255,255,0.3), inset 0 2px 2px -1px rgba(180,188,191,0.7);font-size: 13px;font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;height: 27px;margin: 0;vertical-align: middle;padding: 4px;">
                        <?foreach($personType['PROPS'] as $value => $text):?>
                            <option value="<?=$value?>"><?=$text?></option>
                        <?endforeach?>
                    </select>
                </td>
            </tr>
            <tr style="display:table-row">
                <td width="40%"><b><?=GetMessage('WSD_STEP2_PVZ_PROP_ID', array("#PERSON_TYPE#" => $personType["NAME"]))?><b></td>
                <td width="60%">
                    <select name="shd_pvz_prop[<?=$key?>]" id="shd_pvz_prop_<?=$key?>" 
                            style="background: #fff;border-radius: 4px;border: 1px solid #a3a5a5;color: #000;box-shadow: 0 1px 0 0 rgba(255,255,255,0.3), inset 0 2px 2px -1px rgba(180,188,191,0.7);font-size: 13px;font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;height: 27px;margin: 0;vertical-align: middle;padding: 4px;">
                        <?foreach($personType['PROPS'] as $value => $text):?>
                            <option value="<?=$value?>"><?=$text?></option>
                        <?endforeach?>
                    </select>
                </td>
            </tr>
        <?endforeach?>
    </table>
    <br>
    <input type="submit" name="inst" value="<?= GetMessage("WSD_NEXT") ?>"/>
</form>
<script type="text/javascript">
    BX.ready(function(){
        checkCalcAlgo();
        BX.bind(BX("shd_calc"),"change",checkCalcAlgo);
        function checkCalcAlgo(){
            var calcAlgorithm = BX("shd_calc");
            switch(calcAlgorithm.value){
                case "N":default:
                    BX("shd_calc_simple").style.display = "block";
                    BX("shd_calc_complex").style.display = "none";
                    break;
                case "Y":
                    BX("shd_calc_simple").style.display = "none";
                    BX("shd_calc_complex").style.display = "block";
                    break;
            }
        };
        BX.bind(BX("shd_address_type"),"change",function(event){
            var addrType = this.value;
            switch(addrType){
                case "simple":default:
                    massHide('complex');
                    massShow('simple');
                    break;
                case "complex":
                    massHide('simple');
                    massShow('complex');
                    break;
            }
        });
        function massHide(type){
            massDisplayToggle(type,'none');
        }
        function massShow(type){
            massDisplayToggle(type,'table-row');
        }
        function massDisplayToggle(type,display){
            var list = document.querySelectorAll('[data-address="'+type+'"]');
            for(var i = 0; i < list.length; i++){
                list[i].style.display = display;
            }
        }
    });
</script>