<?
class CBoxberryParsel
{
	static $module_id = "up.boxberrydelivery";

    function ParselCreate($ORDER_ID)
    {	
    	if (!CBoxberry::init_api()) array("ERROR" => GetMessage('WRONG_API_CONNECT'));
		if(intval($ORDER_ID) <= 0) return array("ERROR" => "Invalid argument.");
		if(!extension_loaded('curl')) return array("ERROR" => "cURL is not installed.");    
		if(!$order = CBoxberry::GetFullOrderData($ORDER_ID)) return array('ERROR' => 'Order not found.');
        if(!$arProps = CBoxberry::MakePropsArray($order)) return array('ERROR' => 'Wrong module settings.');
		$order_id = (COption::GetOptionString('up.boxberrydelivery', 'BB_ACCOUNT_NUMBER') == 'Y' ?  $order['ACCOUNT_NUMBER'] :  $order['ID']);
		
		
		$SDATA = array(
			'order_id' 		=> $order_id,
			'price' 		=> $order['PRICE'],
			'payment_sum' 	=> ($order['PAYED'] == 'Y' ? 0 : $order['PRICE']),
			'delivery_sum' 	=> $order['PRICE_DELIVERY'],
			'vid' 			=> $arProps['VID'],
		);
		
		$SDATA['shop'] = array(
			'name' 	=> ($order['PVZ_CODE'] ? $order['PVZ_CODE'] : ''),
			'name1'	=> $arProps['BB_PVZ']
		);
		$bxbOptions['bb_paid_person_jur'] = COption::GetOptionString('up.boxberrydelivery', 'BB_PAID_PERSON_JUR');
		$bxbOptions['bb_paid_person_jur'] = (!empty($bxbOptions['bb_paid_person_jur']) ? $bxbOptions['bb_paid_person_jur'] : 2);
		
		if($order['PERSON_TYPE_ID'] == $bxbOptions['bb_paid_person_jur'])
		{
			$SDATA['customer'] = array(
				'fio' 		=> $arProps['BB_CONTACT_PERSON'],
				'phone'	    => $arProps['BB_PHONE'],
				'email' 	=> $arProps['BB_EMAIL'],
				'name'	    => $arProps['BB_COMPANY_NAME'],
				'address'	=> $arProps['BB_JUR_ADDRESS'],
				'inn'		=> $arProps['BB_INN'],
				'kpp'		=> $arProps['BB_KPP']
			);
		}
		else
		{
			$SDATA['customer'] = array(
				'fio' 		=> $arProps['BB_FIO'],
				'phone'	    => $arProps['BB_PHONE'],
				'email' 	=> $arProps['BB_EMAIL']
			);
		}
		
		$SDATA['weights'] 	= array(
				'weight' 	=>  $order['PACKAGE_WEIGHT']
			);	
		
		if($arProps['VID'] == 2){
			$SDATA['kurdost'] = array(
				'index' 	=> $arProps['BB_ZIP'],
				'citi'  	=> $CityName,
				'addressp' 	=> $arProps['BB_ADDRESS'],
				'timep' 	=> '09:00 - 20:00'
			);
		}
		$SDATA['items']=array();
		
		foreach ($order["ITEMS"] as $item){
				$SDATA['items'][]=
					($item["MEASURE_CODE"]==796 
					?
						array(
							'id'=>$item["PRODUCT_ID"],
							'name'=>$item["NAME"],
							'UnitName'=>'?',
							'price'=>$item["PRICE"],
							'quantity'=>$item["QUANTITY"]
						)
					:
						array(
							'id'=>$item["PRODUCT_ID"],
							'name'=>$item["NAME"],
							'UnitName'=>'?',
							'price'=>$item['PRICE']*$item["QUANTITY"],
							'quantity'=>1
						)
					);

		}
		if (strtoupper(LANG_CHARSET) != 'UTF-8'){
			$SDATA = CBoxberry::mb_conv_array($SDATA);
		}
		
		$data = CBoxberry::method_exec_post('ParselCreate',$SDATA);
			
		if (isset($data['track']) && !empty($data['track'])){
			$CacheDir = '/bitrix/pdf/';
			$ServerCacheDir = $_SERVER['DOCUMENT_ROOT'].$CacheDir;
			$PathToPDF = $ServerCacheDir.$ORDER_ID.'-'.date('d_m_Y-h_i_s').'.pdf';
			$LinkToPDF = $CacheDir.$ORDER_ID.'-'.date('d_m_Y-h_i_s').'.pdf';
			if (!file_exists($ServerCacheDir)){
				mkdir($ServerCacheDir,0775,1);						
			}
		    $curl = curl_init();
		    $fp = fopen($PathToPDF, "w");
		    curl_setopt($curl, CURLOPT_URL, $data['label']);
		    curl_setopt($curl, CURLOPT_FILE, $fp);
		    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		    curl_exec($curl);

		    if(curl_getinfo($curl, CURLINFO_HTTP_CODE) != 404)
			{
				$contents = curl_exec($curl);
   				fwrite($fp, $contents);
   				curl_close($curl);
				fclose($fp);
			}
			$arFields = array(
				'ORDER_ID' 			=> $ORDER_ID,
				'DATE_CHANGE' 		=> date('d.m.Y H:i:s'),
				'LID' 				=> $order['LID'],
				'TRACKING_CODE' 	=> $data['track'],
				'PVZ_CODE' 			=> $order['PVZ_CODE'],
				'STATUS'			=> '1',
				'STATUS_TEXT' 		=> 'CREATED',
				'STATUS_DATE' 		=> date('d.m.Y H:i:s'),
				'CHECK_REQUEST' 	=> 'Y',
				'CHECK_REQUEST_DATE'=> date('d.m.Y H:i:s'),
				'CHECK_PDF_LINK' 	=> ($contents ? $LinkToPDF : $data['label']),
				
			);
	
			CBoxberryOrder::Update($ORDER_ID,$arFields);
			return $data;
		}else{			
			if ($data['err']) 
			{
				if (strtoupper(LANG_CHARSET) == 'WINDOWS-1251')
				{
					 $data['err'] = mb_convert_encoding($data['err'],'CP1251','UTF-8');
				}
				return array("ERROR" => $data['err']);    
			}
			return array("ERROR" => "API_REQUEST_ERROR");    
		}
	}
}
?>