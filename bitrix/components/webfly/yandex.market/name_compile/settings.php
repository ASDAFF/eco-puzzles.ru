<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
if (!CModule::IncludeModule("iblock"))
    return;
__IncludeLang($_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/webfly/yandex.market/lang/ru/settings.php');

if (isset($_POST["selects"]))
{
    if(SITE_CHARSET != "UTF-8" && SITE_CHARSET != "utf-8"){
        $_POST["selects"] = iconv("utf-8","windows-1251",$_POST["selects"]);
    }
    $anchorSelects = $_POST["selects"];
    $anchorSelects = explode(',', $anchorSelects);
    $iterCount = count($anchorSelects);
}
else
{
    $anchorSelects = array("WF_EMPTY_RESULT");
    $iterCount = 0;
}
if (isset($_POST["inputs"]))
{
    if(SITE_CHARSET != "UTF-8" && SITE_CHARSET != "utf-8"){
        $_POST["inputs"] = iconv("utf-8","windows-1251",$_POST["inputs"]);
    }
    $anchorInputs = $_POST["inputs"];
    $anchorInputs = explode(',', $anchorInputs);
}


$arProps = array();
$iblockIds = explode(",", $_POST["iblock_id"]);
if (!empty($iblockIds) && $iblockIds[0] > 0)
{
    foreach ($iblockIds as $ibid)
    {
        $dbProperty = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $ibid));
        while ($arProperty = $dbProperty->Fetch()) {
            $arProps[$arProperty['CODE']] = "[{$arProperty['CODE']}] {$arProperty['NAME']}";
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
$dopArrayName = array("WF_EMPTY" => "", "WF_YM_WRITE" => GetMessage("WF_YM_WRITE"), "WF_PRODUCT_NAME" => GetMessage("WF_PRODUCT_NAME"));
$arProps = array_merge($dopArrayName, $arProps);
?>
<style type="text/css">
    .jc_name_form select
    {
        margin-top:20px;
        width:400px;
    }
    .jc_name_form input[type=text]
    {
        width:385px;
    }
    .jc_name_form input[type=button]
    {
        margin-top:10px;
        width:auto;
    }
    .jc_holder{
        position:relative;
    }
    .jc_holder input.btnDel{
        position:absolute !important;
        margin:0;
        left:410px;
        bottom: 15px;
    }
</style>
<script type="text/javascript">
    var jcNameForm = document.jc_name_form, //форма
        plusBtn = jcNameForm.add_name_prop,
        iter = <?=$iterCount?>;
    function selectChange() {
        var curIter = this.getAttribute("data-iter");
        switch (this.value) {
            case "WF_YM_WRITE":
                jcNameForm["jc_name_prop_inp" + curIter].disabled = false;
                if (jcNameForm["jc_name_prop_inp" + curIter].value.length > 0) {
                    plusBtn.disabled = false;
                } else {
                    plusBtn.disabled = true;
                }
                break;
            case "WF_EMPTY":
                plusBtn.disabled = true;
                break;
            default:
                jcNameForm["jc_name_prop_inp" + curIter].disabled = true;
                plusBtn.disabled = false;
                break;
        }
    }
    function inputKeyup() {
        if (this.value.length > 0) {
            plusBtn.disabled = false;
        } else {
            plusBtn.disabled = true;
        }
    }
    function xClick() {
        this.parentNode.parentNode.removeChild(this.parentNode);
        plusBtn.disabled = false;
    }
    function plusClick() {
        var options = jcNameForm.jc_name_prop0.children,
                optLength = options.length,
                JCHolder = document.createElement("div"),
                select = document.createElement("select"),
                br = document.createElement("br"),
                input = document.createElement("input"),
                inputDel = document.createElement("input");
        iter++;
        JCHolder.setAttribute("class", "jc_holder");
        select.name = "jc_name_prop" + iter;
        select.id = "jc_name_prop" + iter;
        select.setAttribute("data-iter", iter);
        input.name = "jc_name_prop_inp" + iter;
        input.id = "jc_name_prop_inp" + iter;
        input.disabled = true;
        input.type = "text";

        input.onkeyup = inputKeyup;

        inputDel.type = "button";
        inputDel.name = "del_name_prop" + iter;
        inputDel.value = "x";
        inputDel.setAttribute("class", "btnDel");
        inputDel.onclick = xClick;

        var opts = "";
        for (var i = 0; i < optLength; i++) {
            opts += '<option value="' + options[i].value + '">' + options[i].innerHTML + '</option>';
        }
        select.innerHTML = opts;
        select.onchange = selectChange;
        JCHolder.appendChild(select);
        JCHolder.appendChild(br);
        JCHolder.appendChild(input);
        JCHolder.appendChild(inputDel);

        jcNameForm.insertBefore(JCHolder, plusBtn);
        plusBtn.disabled = true;
    }
</script>
<form class="jc_name_form" action="" id="jc_name_form" name="jc_name_form">
    <p><b><?= GetMessage("CHOOSE_NAME_COMPILE_PROPS_LABEL") ?></b></p>
    <? foreach ($anchorSelects as $selI => $selVal): ?>
        <div class="jc_holder">
            <select name="jc_name_prop<?= $selI ?>" id="jc_name_prop<?= $selI ?>" data-iter="<?= $selI ?>" onchange="selectChange.call(this);">
                <? foreach ($arProps as $key => $option): ?>
                    <option value="<?= $key ?>" <? if ($selVal == $key): ?>selected<? endif ?>><?= $option ?></option>
                <? endforeach ?>
            </select>
            <br/>
            <input name="jc_name_prop_inp<?= $selI ?>" id="jc_name_prop_inp<?= $selI ?>" 
                   type="text"<? if (!empty($anchorInputs[$selI])): ?> value="<?= $anchorInputs[$selI] ?>"<? endif ?>
                       <? if (empty($anchorInputs[$selI])): ?> disabled<? endif ?>
                       onkeyup="inputKeyup.call(this);"/>
            <? if ($selI != 0): ?>
                <input type="button" name="del_name_prop<?= $selI ?>" value="x" class="btnDel" onclick="xClick.call(this);"/>
            <? endif ?>
        </div>
    <? endforeach ?>
    <input type="button" name="add_name_prop" value="+"<?if($iterCount==0):?>disabled<?endif?>/>
</form>
<script type="text/javascript">
    jcNameForm.add_name_prop.onclick = plusClick;
</script>