<?php 
CModule::IncludeModule("sale");
IncludeModuleLangFile(__FILE__);
class CDeliveryBoxberry
{
    public static $api;
    public static $selPVZ = NULL;
    public static $isActive = true;
    public static $address_field;
    public static $widget=array('key','settings');
    public static $possible_delivery = array();
    const MIN_WEIGHT = 5;
    protected static $region_bitrix_name;
    protected static $city_bitrix_name;
    protected static $city_widget_name;
    protected static $settings;
    protected static $module_id='up.boxberrydelivery';
	
    public static function Init()
    {   

		
		CModule::IncludeModule(self::$module_id);
		if (!CBoxberry::init_api()) return array("ERROR" => GetMessage('WRONG_API_CONNECT'));
		self::$widget = CBoxberry::method_exec('GetKeyIntegration'); 
		self::$widget['settings'] = CBoxberry::method_exec('WidgetSettings',NULL,TRUE); 
		$GLOBALS['bxb_settings'] = $settings;
		$GLOBALS['key'] = self::$widget['key'];
		$GLOBALS['widget_settings'] = self::$widget['settings'];
		return array(
          "SID" => "boxberry", 
          "NAME" => GetMessage('DELIVERY_NAME'),
          "DESCRIPTION" => "",
          "DESCRIPTION_INNER" => GetMessage('DESCRIPTION_INNER'),
          "BASE_CURRENCY" => COption::GetOptionString("sale", "default_currency", "RUB"),
		  "HANDLER" => __FILE__,
          "DBGETSETTINGS" => array("CDeliveryBoxberry", "GetSettings"),
          "DBSETSETTINGS" => array("CDeliveryBoxberry", "SetSettings"),
          "GETCONFIG" => array("CDeliveryBoxberry", "GetConfig"),
          
          "COMPABILITY" => array("CDeliveryBoxberry", "Compability"),      
          "CALCULATOR" => array("CDeliveryBoxberry", "Calculate"), 
		
			'PROFILES' => array(
				'PVZ' => array(
					'TITLE' => GetMessage('BOXBERRY_PVZ'),
					'DESCRIPTION' => "",
					),
				'KD' => array(
					'TITLE' => GetMessage('BOXBERRY_KD'),
					'DESCRIPTION' => "",
					),
				'PVZ_COD' => array(
					'TITLE' => GetMessage('BOXBERRY_PVZ_COD'),
					'DESCRIPTION' => "",
					),
				'KD_COD' => array(
					'TITLE' => GetMessage('BOXBERRY_KD_COD'),
					'DESCRIPTION' => "",
					)
				)
		);
    }
	public static function WidgetInit() {
	global $APPLICATION;
		if (strpos($APPLICATION->GetCurPage(), 'bitrix/admin') === false || !ADMIN_SECTION)
		{
			$GLOBALS['APPLICATION']->IncludeComponent("bberry:boxberry.widget", "", array(),false);
		}		
	}
	public static function GetConfig()
	{    
	   $arConfig = array(
			"CONFIG" => array(
				"default" => array(),
			)
		); 
		return $arConfig; 
	}
  
   
    public static function SetSettings($arSettings)
    {
		foreach ($arSettings as $key => $value) 
        {
            if (strlen($value) > 0)
				$arSettings[$key] = ($value);
			else
				unset($arSettings[$key]);
        }
    
        return serialize($arSettings);
    }  
	
    public static function GetSettings($strSettings) 
    { 
		$settings = unserialize($strSettings); 
		if (empty($settings)) return;
			return $settings; 
	} 
	private function plural_form($number, $after) 
	{
      $cases = array (2, 0, 1, 1, 1, 2);
      return $number.' '.$after[ ($number%100>4 && $number%100<20)? 2: $cases[min($number%10, 5)] ].' ';
    }
	private function ceilPrice ($p,$d=1)
	{
        return ceil($p/$d) * $d;
    }
	private function upString($name)
	{
		return str_replace(GetMessage('yo'), GetMessage('ye'), (LANG_CHARSET == 'windows-1251' ? mb_strtoupper($name,'CP1251') : mb_strtoupper($name)));
	}
	
	private function getFullDimensions($arOrder, $arConfig)
	{
		$weight_default = COption::GetOptionString(self::$module_id, 'BB_WEIGHT');
		if (count($arOrder["ITEMS"]) == 1 && $arOrder["ITEMS"][0]["QUANTITY"]==1){
				$multiplier = 10;
				$full_package["WIDTH"] =  $arOrder["ITEMS"][0]["DIMENSIONS"]["WIDTH"] / $multiplier;
				$full_package["HEIGHT"] = $arOrder["ITEMS"][0]["DIMENSIONS"]["HEIGHT"] / $multiplier;
				$full_package["LENGTH"] = $arOrder["ITEMS"][0]["DIMENSIONS"]["LENGTH"] / $multiplier;
				$full_package["WEIGHT"] = ($arOrder["ITEMS"][0]['WEIGHT'] == '0.00' || (float)$arOrder["ITEMS"][0]['WEIGHT'] < (float)self::MIN_WEIGHT ? $weight_default : $arOrder["ITEMS"][0]['WEIGHT']);
		} else {
				$full_package["WIDTH"] = 0;
				$full_package["HEIGHT"] = 0;
				$full_package["LENGTH"] = 0;
				$full_package["WEIGHT"] = 0;
				
				foreach ($arOrder["ITEMS"] as $item){
					$full_package["WEIGHT"] += $item["QUANTITY"] * ($item['WEIGHT'] == '0.00' || $item['WEIGHT'] < (float)self::MIN_WEIGHT  ? $weight_default : $item['WEIGHT'] );
				}
		}
		return $full_package;
	}
    public static function GetPointCode($city_code)
	{
        if ($possible_boxberry_points = CBoxberry::method_exec('ListPoints', array('CityCode='.$city_code.'&prepaid=1'))){
            return $possible_boxberry_points[0]['Code'];
        }else{
            return false;
        }
    }
	
	public static function GetBitrixRegionNames($location)
	{
		
		self::$city_bitrix_name = false;
		self::$region_bitrix_name = false;		
		if (!empty($location)){
			  	$parameters = array();
        		$parameters['filter']['=CODE'] = $location;
        		$parameters['filter']['NAME.LANGUAGE_ID'] = "ru";
        		$parameters['limit'] = 1;
        		$parameters['select'] = array('*','LNAME' => 'NAME.NAME');

				$arVal = Bitrix\Sale\Location\LocationTable::getList( $parameters )->fetch();
				$fullCityName = Bitrix\Sale\Location\Admin\LocationHelper::getLocationPathDisplay( $location );
				
				if ( $arVal && strlen( $arVal[ 'LNAME' ] ) > 0 )
				{
					$val = $arVal[ 'LNAME' ];
					self::$city_bitrix_name = self::upString($val);
					self::$region_bitrix_name = self::upString($fullCityName);
					$city_widget_name = explode (",", self::$region_bitrix_name);
					$city_widget_name = array_reverse ($city_widget_name);
					self::$city_widget_name = $city_widget_name[0] . ' ' . (strpos($city_widget_name[1], GetMessage("BOXBERRY_REGION"))!==false ? $city_widget_name[1] : '');

				}
				
		}
	}
	public static function GetCityCode()
	{
		$boxberry_list = Cboxberry::method_exec('ListCities');	
		$i=0;

			foreach($boxberry_list as $boxberry_cities){				
				$city_name = self::upString($boxberry_cities['Name']);	
				$region_name = self::upString($boxberry_cities['Region']);	
				$boxberry_city[$i]['Name']= $city_name;
				$boxberry_city[$i]['Region'] = $region_name;	
				$boxberry_city[$i]['Code'] = $boxberry_cities['Code'];	
				$i++;

				if (strpos(self::$region_bitrix_name, $region_name) !== false){
					if (strpos(self::$city_bitrix_name, $city_name) !== false){	
						return $boxberry_cities["Code"];			
					}
				}
			}
			foreach($boxberry_city as $cities){									
				if (strpos(self::$city_bitrix_name, $cities['Name'])  !== false){					
					return $cities["Code"];			
				}
			}
		return false;
    }
    public static function GetZipKD ($zip_code){        
        $possible_zip = CBoxberry::method_exec('ZipCheck', array('Zip='.$zip_code));		
		if ($possible_zip[0]["ExpressDelivery"] == 1 ){
			return true;
		}else{
			return false;
		}
	}
    
    public static function Compability($arOrder, $arConfig)
    {
		
		$api_url = COption::GetOptionString(self::$module_id, 'API_URL');
		$api_token = COption::GetOptionString(self::$module_id, 'API_TOKEN');
		self::GetBitrixRegionNames($arOrder['LOCATION_TO']);	

		if ($location_to = self::GetCityCode()){
			if (!in_array($location_to ,$GLOBALS['widget_settings']["result"][1]["CityCode"])){
				$arReturn[] = 'PVZ';
				$arReturn[] = 'PVZ_COD';
			}
		}
		if (!in_array($location_to ,$GLOBALS['widget_settings']["result"][1]["CityCode"])){
			if (self::GetZipKD ($arOrder['LOCATION_ZIP'])){
					$arReturn[] = 'KD';
					$arReturn[] = 'KD_COD';
				}
		}
		
        return $arReturn;
    }
	private static function setLink_params($arrParams=array())
	{
		$_SESSION['link_params'] = json_encode($arrParams);
	}
	public static function getLink_params()
	{
		if (isset($_SESSION['link_params']) && !empty($_SESSION['link_params'])){
			return $_SESSION['link_params'];
		}
		return false;
	}
	public static function Calculate($profile, $arConfig, $arOrder, $STEP, $TEMP = false)
    {
		$bxb_custom_link = COption::GetOptionString('up.boxberrydelivery', 'BB_CUSTOM_LINK');
		$pvz_to = self::GetPointCode (self::GetCityCode()); // 2BXB_PointCode to
		
		$parcel_size = self::getFullDimensions($arOrder, $arConfig);
		$kd_surch = COption::GetOptionString(self::$module_id, 'BB_KD_SURCH');
		self::setLink_params(array(
				'callback_function'=>'delivery',
				'widget_key'=>$GLOBALS['key'],
				'custom_city'=>self::$city_widget_name ,
				'target_start'=>'',
				'ordersum'=>$arOrder["PRICE"],
				'weight'=>$parcel_size['WEIGHT'] ,
				'paysum'=>0 ,
				'height'=>$parcel_size['HEIGHT'] ,
				'width'=>$parcel_size['WIDTH'] ,
				'depth'=>$parcel_size['LENGTH']
			)
		);
		if ($profile == 'PVZ'){
				$arrParams=array(
					'target='. $pvz_to,
					'weight=' . $parcel_size['WEIGHT'],
					'height='. $parcel_size['HEIGHT'],
					'width='. $parcel_size['WIDTH'],
					'depth='. $parcel_size['LENGTH'],
					'ordersum=' . $arOrder['PRICE'], 
					'paysum=' . $arOrder['PRICE'], 
					'sucrh=1', 
					'version=2.2', 
					'cms=bitrix', 
					'url='.$_SERVER['SERVER_NAME'], 
				);
				$price_delivery = CBoxberry::method_exec('DeliveryCosts',$arrParams, TRUE);
				if (isset($price_delivery['price'])){
					$price = $price_delivery['price'];
				}else{
					return array(
						"RESULT" => "ERROR",
						"TEXT" => "",						
					);
				}

			
			if ($GLOBALS['widget_settings']["result"][3]['hide_delivery_day']!=1){
				$period = $price_delivery['delivery_period'];
				$period = self::plural_form($period, array(GetMessage("DAY"),GetMessage("DAYS"),GetMessage("DAYSS")));
			}else{
				$period = NULL;
			}
			if (empty($bxb_custom_link)){
				$link_pvz = "<br/><a href=\"#\" onclick=\"boxberry.checkLocation(1);boxberry.open(delivery, '".$GLOBALS['key'] ."' , '". self::$city_widget_name . "' , '', '". $arOrder["PRICE"] ."' , '".$parcel_size['WEIGHT'] ."' ,'". $arOrder["PRICE"]  ."' ,'". $parcel_size['HEIGHT'] ."','". $parcel_size['WIDTH'] ."','". $parcel_size['LENGTH'] ."' ); return false;\">". GetMessage("SELECT_LINK_TEXT") ."</a>" ;
			}
            return array(
                "RESULT" => "OK",
                "VALUE" => $price,
                "TRANSIT" => $period. $link_pvz
			);
            
        }elseif ($profile == 'PVZ_COD'){
			$arrParams=array(
					'target='. $pvz_to,
					'weight=' . $parcel_size['WEIGHT'],
					'height='. $parcel_size['HEIGHT'],
					'width='. $parcel_size['WIDTH'],
					'depth='. $parcel_size['LENGTH'],
					'ordersum=' . $arOrder['PRICE'], 
					'paysum=0',
					'sucrh=1', 
					'version=2.2', 
					'cms=bitrix', 
					'url='.$_SERVER['SERVER_NAME'], 
					
				);
				$price_delivery = CBoxberry::method_exec('DeliveryCosts',$arrParams, TRUE);
				if (isset($price_delivery['price'])){
					$price = $price_delivery['price'];
				}else{
					return array(
						"RESULT" => "ERROR",
						"TEXT" => "",						
					);
				}		
			
			if ($GLOBALS['widget_settings']["result"][3]['hide_delivery_day']!=1){
				$period = $price_delivery['delivery_period'];
				$period = self::plural_form($period, array(GetMessage("DAY"),GetMessage("DAYS"),GetMessage("DAYSS")));
			}else{
				$period = NULL;
			}
			
			if (empty($bxb_custom_link)){
				$link_pvz_cod = "<br/><a href=\"#\" onclick=\"boxberry.checkLocation(1);boxberry.open(delivery, '".$GLOBALS['key'] ."' , '". self::$city_widget_name . "' , '', '". $arOrder["PRICE"] ."' , '".$parcel_size['WEIGHT'] ."' , '0' ,'". $parcel_size['HEIGHT'] ."','". $parcel_size['WIDTH'] ."','". $parcel_size['LENGTH'] ."' ); return false;\">". GetMessage("SELECT_LINK_TEXT") ."</a>" ;
			}
            
			return array(
                "RESULT" => "OK",
                "VALUE" => $price,
                "TRANSIT" => $period. $link_pvz_cod
			);
            
        }elseif ($profile == 'KD'){
            $arrParams=array(
                    'target='. $pvz_to,
                    'weight=' . $parcel_size['WEIGHT'],
                    'height='. $parcel_size['HEIGHT'],
					'width='. $parcel_size['WIDTH'],
					'depth='. $parcel_size['LENGTH'],
                    'ordersum=' . $arOrder['PRICE'], 
					'paysum=' . $arOrder['PRICE'], 
					($kd_surch=="Y" ? "" : 'sucrh=1'), 
					'version=2.2', 
					'cms=bitrix', 
					'url='.$_SERVER['SERVER_NAME'], 
					'zip=' .$arOrder['LOCATION_ZIP'] 
                );
            
            $price_delivery = CBoxberry::method_exec('DeliveryCosts',$arrParams, TRUE);
			if (isset($price_delivery['price'])){
				$price = $price_delivery['price'];
			}else{
				return array(
					"RESULT" => "ERROR",
					"TEXT" => "",						
				);
			}
			if ($GLOBALS['widget_settings']["result"][3]['hide_delivery_day']!=1){
				$period = $price_delivery['delivery_period'];
				$period = self::plural_form($period, array(GetMessage("DAY"),GetMessage("DAYS"),GetMessage("DAYSS")));
			}else{
				$period = NULL;
			}
		    return array(
                  "RESULT" => "OK",
                  "VALUE" => $price,
                  "TRANSIT" => $period
            );
        }elseif ($profile == 'KD_COD'){
            
            $arrParams=array(
                    'target='. $pvz_to,
					'height='. $parcel_size['HEIGHT'],
					'width='. $parcel_size['WIDTH'],
					'depth='. $parcel_size['LENGTH'],
                    'weight=' . $parcel_size['WEIGHT'],
                    'ordersum=' . $arOrder['PRICE'], 
					($kd_surch=="Y" ? "" : 'sucrh=1'), 
					'version=2.2', 
					'cms=bitrix', 
					'url='.$_SERVER['SERVER_NAME'], 
					'zip=' .$arOrder['LOCATION_ZIP'] 
                );
            
            $price_delivery = CBoxberry::method_exec('DeliveryCosts',$arrParams, TRUE);
            if (isset($price_delivery['price'])){
				$price = $price_delivery['price'];
			}else{
				return array(
					"RESULT" => "ERROR",
					"TEXT" => "",						
				);
			}
			if ($GLOBALS['widget_settings']["result"][3]['hide_delivery_day']!=1){
				$period = $price_delivery['delivery_period'];
				$period = self::plural_form($period, array(GetMessage("DAY"),GetMessage("DAYS"),GetMessage("DAYSS")));
			}else{
				$period = NULL;
			}
			
            return array(
                  "RESULT" => "OK",
                  "VALUE" => $price,
                  "TRANSIT" => $period
            );
			
        }
    }
	function orderCreate($id,$arOrder){
		if (COption::GetOptionString(self::$module_id, 'BB_LOG') == 'Y'){
				CBoxberry::$log->save($id);
				CBoxberry::$log->save($arOrder);
				CBoxberry::$log->save($_SESSION);
		}
		if (!function_exists('findParentBXB'))
		{
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
				if($profile["PARENT_ID"]==$key){
					$boxberry_profiles[] = $profile["ID"];
				}
			}
		}

		if (!empty($id) && in_array($arOrder['DELIVERY_ID'],$boxberry_profiles))
		{
			$result = CBoxberry::MakePropsArray($arOrder);
			$arFields = array(
				'ORDER_ID' 			=> $id,
				'DATE_CHANGE' 		=> date('d.m.Y H:i:s'),
				'LID' 				=> $result['LID'],
				'PVZ_CODE' 			=> (isset($_SESSION['selPVZ']) && !empty($_SESSION['selPVZ']) ? $_SESSION['selPVZ'] : "" ),
				'STATUS'			=> '0',
				'STATUS_TEXT' 		=> 'NEW',
				'STATUS_DATE' 		=> date('d.m.Y H:i:s'),
			);

			CBoxberryOrder::Add($arFields);
		}
		return true;
	}

}

AddEventHandler("sale", "OnSaleComponentOrderOneStepDelivery", array('CDeliveryBoxberry', 'WidgetInit'));
AddEventHandler("sale", "onSaleDeliveryHandlersBuildList", array('CDeliveryBoxberry', 'Init'));
AddEventHandler("sale", "OnSaleComponentOrderOneStepComplete", array('CDeliveryBoxberry', 'orderCreate')); 
AddEventHandler("sale", "OnOrderUpdate", array('CDeliveryBoxberry', 'orderCreate')); 
?>