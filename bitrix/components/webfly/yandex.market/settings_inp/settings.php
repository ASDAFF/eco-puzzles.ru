<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
if (!CModule::IncludeModule("iblock"))
    return;

__IncludeLang($_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/webfly/yandex.market/lang/ru/settings.php');

$optCost = $_POST["cost"];
$optDays = $_POST["days"];
$optOrderBefore = $_POST["orderBefore"];

?>
<style>
    label
    {
       display:inline-block;
       width:180px;
    }
    div
    {
        margin-bottom:20px;
    }
</style>
<form class="jc_form" action="">
    <div>
    <label for="jc_cost_shop"><?= GetMessage("DELIVERY_COST_NAME") ?></label>
    <input name="jc_cost_shop" id="jc_cost_shop" value="<?=$optCost?>" type="text">
    </div>
    <div>
    <label for="jc_days_shop"><?= GetMessage("DELIVERY_DAYS_NAME") ?></label>
    <input name="jc_days_shop" id="jc_days_shop" value="<?=$optDays?>" type="text">
    </div>
    <div>
    <label for="jc_orderBefore_shop"><?= GetMessage("DELIVERY_ORDER_TIME") ?></label>
    <input name="jc_orderBefore_shop" id="jc_orderBefore_shop" value="<?=$optOrderBefore?>" type="text">
    </div>
</form>