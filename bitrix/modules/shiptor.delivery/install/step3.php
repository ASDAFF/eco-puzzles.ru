<div style="background-color: white;width: 60%;padding: 15px;border-radius: 6px;">
    <b><p><?=GetMessage('WSD_FINALSTEP_CONTENT_HEAD')?></p></b>
    <b><p><?=GetMessage('WSD_FINALSTEP_CONTENT_FAST_DOC_LINK')?></p></b>
    <b><p><?=GetMessage('WSD_FINALSTEP_CONTENT_MODULE_LINK')?></p></b>
    <b><p><?=GetMessage('WSD_FINALSTEP_CONTENT_DELIVERY_LINK', array('#ID#' => $_SESSION['_SHIPTOR']["PARENT_ID"]))?></p></b>


    <form action="<?= $APPLICATION->GetCurPage() ?>" name="shiptor_delivery_install">
    <?= bitrix_sessid_post() ?>
        <input type="hidden" name="lang" value="<?= LANG ?>"/>
        <input type="hidden" name="id" value="shiptor.delivery"/>
        <input type="hidden" name="install" value="Y"/>
        <input type="hidden" name="step" value="4"/>
        <input type="submit" name="inst" value="<?= GetMessage("WSD_FINISH") ?>"/>
    </form>
</div>