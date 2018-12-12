<form action="<?= $APPLICATION->GetCurPage() ?>" name="shiptor_delivery_install" style="background-color: white;width: 60%;padding: 15px;border-radius: 6px;">
<?= bitrix_sessid_post() ?>
    <input type="hidden" name="lang" value="<?= LANG ?>"/>
    <input type="hidden" name="id" value="shiptor.delivery"/>
    <input type="hidden" name="install" value="Y"/>
    <input type="hidden" name="step" value="2"/>

    <table cellpadding="3" cellspacing="0" border="0" width="100%">
        <tr>
            <td colspan="2"><p><?= GetMessage('WSD_STEP1_CONTENT')?></p></td>
        </tr>
        <tr>
            <td width="20%"><?= GetMessage('WSD_STEP1_API_LABEL')?></td>
            <td width="80%">
                <input type="text" name="shd_api_key" value="" id="shd_api_key" size="50" style="font-size:13px;height:25px;padding:0 5px;margin:0;border-radius:4px;color:#000;display:inline-block;outline:none;vertical-align:middle;box-shadow: 0 1px 0 0 rgba(255,255,255,0.3), inset 0 2px 2px -1px rgba(180,188,191,0.7);border: 1px solid;border-color: #87919c #959ea9 #9ea7b1 #959ea9;"/>
            </td>
        </tr>
    </table>
    <br>
    <input type="button" name="inst" value="<?= GetMessage("WSD_NEXT") ?>" id="shd_step_1"/>
</form>
<script type="text/javascript">
    BX.ready(function(){
        BX.bind(BX("shd_step_1"), "click",function(event){
            var apiKey = BX("shd_api_key").value;
            if(apiKey.length === 40){
                this.form.submit();
            }else{
               var errContainer = BX("shd_api_key").parentNode.querySelector("small.shd_err");
               if(!errContainer){
                   errContainer = BX.create("small");
                   errContainer.className = 'shd_err';
                   errContainer.innerHTML = '<?=GetMessage('WSD_STEP2_ERROR_NO_API_KEY')?>';
                   BX("shd_api_key").parentNode.appendChild(errContainer);
               }
               errContainer.style.display = 'inline';
               setTimeout(function(){
                   errContainer.style.display = 'none';
               },1500);
            }
        });
    });
</script>