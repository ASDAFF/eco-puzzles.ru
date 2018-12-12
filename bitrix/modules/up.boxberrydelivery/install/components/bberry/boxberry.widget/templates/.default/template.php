<?	global $APPLICATION;
CJSCore::Init(array("jquery"));
$widget_url = COption::GetOptionString('up.boxberrydelivery', 'WIDGET_URL');
$APPLICATION->AddHeadScript($widget_url);
IncludeModuleLangFile(__FILE__);

if (!function_exists('findParentBXB')) {
    function findParentBXB($profiles){
        if ($profiles['CODE']=='boxberry'){
            return $profiles['ID'];
        }
    }
}

$allDeliverys = \Bitrix\Sale\Delivery\Services\Manager::getActiveList();
$parent = array_filter ($allDeliverys, 'findParentBXB');
$boxberry_profiles=array();

foreach ($allDeliverys as $profile){
    foreach ($parent as $key=>$value){
        if($profile["PARENT_ID"]==$key && (strpos($profile['CODE'],'PVZ_COD')!==false)){
			$boxberry_profiles_cod[] = $profile["ID"];
		}elseif($profile["PARENT_ID"]==$key && (strpos($profile['CODE'],'PVZ')!==false)){
            $boxberry_profiles[] = $profile["ID"];
        }
	}
}

$bxbOptions['address'] = COption::GetOptionString('up.boxberrydelivery', 'BB_ADDRESS');
$bxbOptions['bb_custom_link'] = COption::GetOptionString('up.boxberrydelivery', 'BB_CUSTOM_LINK');
$bxbOptions['bb_paid_person_ph'] = COption::GetOptionString('up.boxberrydelivery', 'BB_PAID_PERSON_PH');
$bxbOptions['bb_paid_person_jur'] = COption::GetOptionString('up.boxberrydelivery', 'BB_PAID_PERSON_JUR');


$arOrderProps = array();
$arOrderPropsCode = array();
$dbProps = CSaleOrderProps::GetList(
    array("PERSON_TYPE_ID" => "ASC", "SORT" => "ASC"),
    array(),
    false,
    false,
    array()
);

$adminBoxberry = true;
while ($arProps = $dbProps->GetNext())
{
    if(strlen($arProps["CODE"]) > 0)    {
        $arOrderPropsCode[$arProps["CODE"]][$arProps["PERSON_TYPE_ID"]] = $arProps;
    }
}
$bxbOptions['bb_paid_person_ph'] = (!empty($bxbOptions['bb_paid_person_ph']) ? $bxbOptions['bb_paid_person_ph'] : 1);
if ($arOrder = CSaleOrder::GetByID($_REQUEST['orderId']))
{
   if (strpos($arOrder['DELIVERY_ID'], 'boxberry') === false ){ $adminBoxberry=false; }

}
?>
<? if ($adminBoxberry){?>
<script>
    var bxb_errors = [];
    var bx_soa_delivery = false;
    var bb_custom_link = false;
	var selected_cod_profile = true;
    var boxberry_delivery_profiles = {};
		boxberry_delivery_profiles.widget=<?=CUtil::PhpToJSObject($boxberry_profiles)?>;
		boxberry_delivery_profiles.widget_cod=<?=CUtil::PhpToJSObject($boxberry_profiles_cod)?>;
		boxberry_delivery_profiles.module_addr_options=<?=CUtil::PhpToJSObject($arOrderPropsCode[$bxbOptions['address']])?>;
		

    function admin_delivery(result){
		
		 $.ajax({
                url: '/bitrix/js/up.boxberrydelivery/ajax.php',
                type: 'POST',
                dataType: 'JSON',
                data: {save_admin_pvz_id:result.id,order_id:selected_bxb_id,address:'Boxberry: '+ result.address + " #" + result.id},
                success:function(data){$('.js-bxb-select-'+selected_bxb_id).html(result.id);}
            });
	}
    function delivery(result){
		
		if (typeof(selected_bxb_id) !== 'undefined'){			
			if ($('.js-bxb-select-'+selected_bxb_id).length > 0){
				$.ajax({
					url: '/bitrix/js/up.boxberrydelivery/ajax.php',
					type: 'POST',
					dataType: 'JSON',
					data: {change_pvz_id:result.id,order_id:selected_bxb_id,address:'Boxberry: '+ result.address + " #" + result.id, change_location:result.name},
					success:function(data){$('.js-bxb-select-'+selected_bxb_id).html(result.id);}
				});
			}
		}
        if (boxberry_delivery_profiles.widget_element != undefined){
            element = document.getElementById(boxberry_delivery_profiles.widget_element);
        }else{
            person_type = $('input[name="PERSON_TYPE"]:checked').val();
            if (person_type != undefined){
                prop_id = boxberry_delivery_profiles.module_addr_options[person_type].ID
            }else{
                prop_id = boxberry_delivery_profiles.module_addr_options[<?=$bxbOptions['bb_paid_person_ph'];?>].ID
            }
            element = document.getElementById('ORDER_PROP_'+ prop_id);
        }
        if (element != undefined){
            element.value = 'Boxberry: '+ result.address + " #" + result.id ;
            if (boxberry_delivery_profiles.widget_element != undefined){
                bxb_errors=[];
            }
            $.ajax({
                url: '/bitrix/js/up.boxberrydelivery/ajax.php',
                type: 'POST',
                dataType: 'JSON',
                data: {save_pvz_id:result.id, change_location:result.name},
                success:function(data){checkSelectPvz();BX.Sale.OrderAjaxComponent.sendRequest();}
            });
        }
        return false;
    }
    function checkSelectPvz(){
        $.ajax({
            url: '/bitrix/js/up.boxberrydelivery/ajax.php',
            type: 'POST',
            dataType: 'JSON',
            data: {check_pvz:1},
            success: function(not_selected){
                $('#bx-soa-orderSave a').show();
                bxb_errors=[];
                if (not_selected==true){
                    $('#bx-soa-orderSave a').hide();
                    bxb_errors[0]=('<?=GetMessage("PVZ_REQUIRED");?>');
                }
                if (typeof (BX.Sale.OrderAjaxComponent.showBlockErrors) === 'function'){
                    BX.Sale.OrderAjaxComponent.result.ERROR.DELIVERY = bxb_errors;
                    BX.Sale.OrderAjaxComponent.showBlockErrors(BX.Sale.OrderAjaxComponent.deliveryBlockNode);
                }else if (typeof (BX.Sale.OrderAjaxComponent.showError)  === 'function' && bxb_errors.length >0){
                    BX.Sale.OrderAjaxComponent.showError(BX.Sale.OrderAjaxComponent.deliveryBlockNode, bxb_errors[0]);
                }

            }
        });
    }
	
	function makeWidgetString( params )
	{
		WidgetString = '';
		for(var index in params) { 
		  if (selected_cod_profile && index == 'paysum'){			  
			  params[index] = params['ordersum'];
		  }
		   WidgetString = WidgetString + "'" + params[index]+ "',";  
		}
		return WidgetString;
	}
	
	function getLink()
	{
		$.ajax({
			url: '/bitrix/js/up.boxberrydelivery/ajax.php',
			type: 'POST',
			dataType: 'JSON',
			data: {get_link:'1'},
			success: function(return_data){
				$('#'+bb_custom_link).html("");
				$('#'+bb_custom_link).append('<a href="#" onclick="boxberry.checkLocation(1);boxberry.open('+  makeWidgetString(return_data) +');return false;" ><?=GetMessage("BB_CUSTOM_LINK");?></a>');
			}
		});
	}
    function checkPVZ(code)
	{
		
		if (boxberry_delivery_profiles.widget.indexOf(code)!=-1){
			selected_cod_profile = true;
			if (bb_custom_link) getLink();
			checkSelectPvz();
		} else if (boxberry_delivery_profiles.widget_cod.indexOf(code)!=-1){
			selected_cod_profile = false;
			if (bb_custom_link) getLink();
			checkSelectPvz();
		} else {
			if ($('#'+bb_custom_link).html() && bb_custom_link) $('#'+bb_custom_link).html("");
			
            $('#bx-soa-orderSave a').show();
        }
    }
	
    function afterFormReload(e) 
	{
        
		if (e!=undefined){
            if (e.order!=undefined && boxberry_delivery_profiles.module_options != false){
                for (var key in e.order.PERSON_TYPE) {

                    if (e.order.PERSON_TYPE[key].CHECKED != undefined){
                        if (key < 1){
                            key = <?=$bxbOptions['bb_paid_person_ph'];?>;
                        }else{
							key = e.order.PERSON_TYPE[key].ID;
						}
                        boxberry_delivery_profiles.type_person = key;
                        boxberry_delivery_profiles.widget_element = 'soa-property-'+boxberry_delivery_profiles.module_addr_options[key].ID;
                    }
                }
                e.order.DELIVERY.forEach(function(item, i, arr) {
                    if (item.CHECKED != undefined){
                        checkPVZ(item.ID)
                    }
                });
            }
        }else{
            return false;
        }
    }
	if (window.jQuery || window.$){
		$(document).ready(function() {				
			$('#bx-soa-region').on ('focusout', '#zipProperty', function(){
				BX.Sale.OrderAjaxComponent.sendRequest();
			});	
			bx_soa_delivery = document.querySelector('#bx-soa-delivery');
			<?=(!empty($bxbOptions['bb_custom_link']) ? 'bb_custom_link="'.$bxbOptions['bb_custom_link'].'";' : '');?>
			$.ajax({
				url: '/bitrix/js/up.boxberrydelivery/ajax.php',
				type: 'POST',
				dataType: 'JSON',
				data: {remove_pvz:1}
			});

			if (bx_soa_delivery){
				BX.addCustomEvent('onAjaxSuccess', afterFormReload);
				BX.Sale.OrderAjaxComponent.sendRequest();
			}else{
				$("input[name='DELIVERY_ID']" ).each(function(i,el) {
					if ($(el).prop('checked')){
						checkPVZ($(el).val());
					}
				})
			}
		});					
	}
	
</script>
<?}?>