<?php
include $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/shiptor.delivery/install/version.php";

$APPLICATION->SetAdditionalCSS('/bitrix/gadgets/shiptor.delivery/check/styles.css');

CJSCore::init(array("ajax"));
?>
<img src="/bitrix/images/shiptor.delivery/shiptor_logo.png" style="float:left;width:20%"/>
<h3 id='shiptor_updates_h3'>
    <?php echo GetMessage("SHIPTOR_CURRENT_VERSION",array("#VERSION#" => $arModuleVersion["VERSION"], "#DATE#" => $arModuleVersion["VERSION_DATE"]))?>
</h3>
<script type="text/javascript">
    BX.message({
        "SHIPTOR_NO_UPDATES":"<?php echo GetMessage("SHIPTOR_NO_UPDATES")?>",
        "SHIPTOR_YES_UPDATES": "<?php echo GetMessage("SHIPTOR_YES_UPDATES")?>",
        "SHIPTOR_FAIL_UPDATES": "<?php echo GetMessage("SHIPTOR_FAIL_UPDATES")?>"
    });
    BX.ready(function(){
        var version = "<?php echo $arModuleVersion["VERSION"]?>";
        BX.ajax.loadJSON(
            "http://bitrix.shiptor.ru/check_updates/index.php",
            function(json){
                var p = document.createElement("p");
                if(json.VERSION != version){
                    p.innerHTML = BX.message("SHIPTOR_YES_UPDATES").replace("#VERSION#",json.VERSION);
                    p.style.color = "green";
                    p.style.fontWeight = "bold";
                }else{
                    p.innerHTML = BX.message("SHIPTOR_NO_UPDATES");
                }
                document.querySelector("#shiptor_updates_h3").parentNode.insertBefore(p,null);
            },
            function (result){
                var p = document.createElement("p");
                p.innerHTML = BX.message("SHIPTOR_FAIL_UPDATES");
                p.style.color = "red";
            }
        );
    });
</script>