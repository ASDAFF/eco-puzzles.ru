<?
class Log{
	static $LOG_DIRECTORY = null;
	static $LOG_FILE = null;
	function __construct(){
		self::$LOG_FILE = 'boxberrydelivery.log';
		self::$LOG_DIRECTORY = $_SERVER['DOCUMENT_ROOT']. '/bitrix/cache/log/';
		if (COption::GetOptionString(CBoxberry::$module_id, 'BB_LOG') == 'Y'){
			if(!file_exists(self::$LOG_DIRECTORY)){
				mkdir(self::$LOG_DIRECTORY,0777,1);
			}
		}else{
			if (file_exists(self::$LOG_DIRECTORY.self::$LOG_FILE)){
				unlink(self::$LOG_DIRECTORY.self::$LOG_FILE);
			} 
		}
	}
	function save($data){
		file_put_contents(self::$LOG_DIRECTORY. self::$LOG_FILE,print_r($data,1),FILE_APPEND );
	}
}
class curl_bxb_api {
	var $timeout;
	var $url;
	var $file_contents;
	function getFile($url,$timeout=0) {
		$ch = curl_init();
		$this->url = $url;
		$this->timeout = $timeout;
			curl_setopt ($ch, CURLOPT_URL, $this->url);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
			$this->file_contents = curl_exec($ch);
		if ( curl_getinfo($ch,CURLINFO_HTTP_CODE) !== 200 ) {
			return false;
		} else {
			return $this->file_contents;
		}
	}
}
class CBoxberry 
{
    static $module_id = "up.boxberrydelivery";
	static $err = null;
	static $curl = null;
	static $api_token = null;
	static $api_url = null;
	static $DS = DIRECTORY_SEPARATOR;
	static $file_cache =null;
	static $allow_url_fopen = false;
	static $log = false;

	function init_api()
	{ 
		$api_token = trim(COption::GetOptionString(self::$module_id, 'API_TOKEN'));
		if (!empty($api_token)){
			self::$api_token = $api_token;
		}else{
			return GetMessage('WRONG_TOKEN');
		}
		self::$log = new Log();
		
		self::$api_url = COption::GetOptionString(self::$module_id, 'API_URL');
		self::$allow_url_fopen = ini_get('allow_url_fopen');
		self::$file_cache = $_SERVER['DOCUMENT_ROOT'] . self::$DS . 'bitrix'. self::$DS . 'cache' . self::$DS;
		if (self::$allow_url_fopen != "1"){
			if (extension_loaded('curl')){
				self::$curl = new curl_bxb_api();
				return true;
			}else{
				return false;
			}
		}
		return true;
	}
	function get_cache($key,$cache_time=5)
	{
		$file = $key . '.cache';
		
		if (is_file(self::$file_cache.$file) && (filemtime(self::$file_cache.$file) >= (time() - (3600 * $cache_time)))){
			return @file_get_contents(self::$file_cache.$file);
		}
		return false;
	}
	function set_cache($key, $cnt)
	{
		$file = $key . '.cache';
		return @file_put_contents(self::$file_cache.$file, $cnt);
	}
	function api_get($url,$cache_time=5)
	{
		$cache_key = md5($url);
		if (empty($cache_time)){
			if (false !== ($cnt = (self::$allow_url_fopen == "1" ? @file_get_contents($url) : (isset(self::$curl) ? self::$curl->getFile($url) : false)))){
				$data=json_decode($cnt,true);
				return $cnt;
			}
		}
		if ($cnt = self::get_cache($cache_key,$cache_time)) {
			return $cnt;
		}else{			
			if (false !== ($cnt = (self::$allow_url_fopen == "1" ? @file_get_contents($url) : (isset(self::$curl) ? self::$curl->getFile($url) : false)))){
				$data=json_decode($cnt,true);
				if(count($data)>=0 && !isset($data[0]['err']))
				{
					self::set_cache($cache_key, $cnt);
				}
				return $cnt;
			}
		}
		return false;
	}
	function method_exec($method , $params = NULL, $nocache=FALSE)
	{
		$cache_time = ($nocache == TRUE ? 0 : 5);
		$url_token = '?token=' . self::$api_token;
		$url_params = '';
		if (isset ($method) && !empty($method) ){
			if (is_array ($params)){
				$params = implode ('&', $params);
				$url_params = '&'.$params;
			}else{
				$url_params = $params;
			}
			$exec_string = self::$api_url . $url_token . '&method=' . $method . $url_params;
			if ($method == 'GetKeyIntegration'){
				$data = self::api_get($exec_string, 3650);
			}else{				
				$data = self::api_get($exec_string, $cache_time);
			}
			$data = json_decode($data, true);

			if (strtoupper(LANG_CHARSET) != 'UTF-8'){
				$data = self::iconv_array($data, 'UTF-8', LANG_CHARSET);
			}
			if (COption::GetOptionString(self::$module_id, 'BB_LOG') == 'Y'){
				self::$log->save($method . $url_params);
				self::$log->save($data);
			}
			return $data;
		}else{
			return false;
		}
	}
	function method_exec_post($method , $in_data = NULL)
	{

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::$api_url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array(
			  'token'=>self::$api_token,
			  'method'=>$method,
			  'sdata'=>json_encode($in_data)
			));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		self::$log->save($in_data);
		$out_data = json_decode(curl_exec($ch),1);
		self::$log->save($out_data);
		
		return $out_data;
    }
    
    function GetFullOrderData($ORDER_ID)
    {	
    	if(intval($ORDER_ID) <= 0) return false;
			
			$order = CSaleOrder::GetByID($ORDER_ID);
			$bxb_order_info = CBoxberryOrder::GetByOrderId($ORDER_ID);
			$order["PVZ_CODE"] = $bxb_order_info["PVZ_CODE"];
			if(!$order) return false;
			
			$dbProps = CSaleOrderPropsValue::GetOrderProps($ORDER_ID);
			while($prop = $dbProps->Fetch())
			{
				$order['PROPS'][$prop['ORDER_PROPS_ID']] = $prop;
			}
			$order['ITEMS'] = array();
			
			$dbBasket = CSaleBasket::GetList(Array('ID'=>'ASC'), Array('ORDER_ID'=>$ORDER_ID));
			$BB_WEIGHT = COption::GetOptionString(self::$module_id, 'BB_WEIGHT');
			while ($arItem = $dbBasket->Fetch())
			{ 
				$order['ITEMS'][] = $arItem;      
				$order['PACKAGE_WEIGHT']+= $arItem["QUANTITY"] * ($arItem['WEIGHT'] == '0.00' ?  $BB_WEIGHT : $arItem['WEIGHT']);
			} 
		 return $order;
	}
    
    
    function MakePropsArray($order)
    {
        global $USER;
    	$arReturn = array();
		
        if(!$arReturn['BB_PVZ'] = COption::GetOptionString(self::$module_id, 'BB_PVZ')) return false;
			$arReturn['VID'] = (($order['DELIVERY_ID'] == 'boxberry:PVZ' || $order['DELIVERY_ID'] == 'boxberry:PVZ_COD') ? 1 : 2);
			$arUserFields = $USER->GetByID($order['USER_ID'])->Fetch();
			$arOptFields = array(
				'BB_FIO'=> COption::GetOptionString('up.boxberrydelivery', 'BB_FIO'),
				'BB_CONTACT_PERSON'=> COption::GetOptionString('up.boxberrydelivery', 'BB_CONTACT_PERSON'),
				'BB_PHONE'=> COption::GetOptionString('up.boxberrydelivery', 'BB_PHONE'),
				'BB_EMAIL'=> COption::GetOptionString('up.boxberrydelivery', 'BB_EMAIL'),
				'BB_ZIP'=> COption::GetOptionString('up.boxberrydelivery', 'BB_ZIP'),
				'BB_LOCATION'=> COption::GetOptionString('up.boxberrydelivery', 'BB_LOCATION'),
				'BB_ADDRESS'=> COption::GetOptionString('up.boxberrydelivery', 'BB_ADDRESS'),
				'BB_JUR_ADDRESS'=> COption::GetOptionString('up.boxberrydelivery', 'BB_JUR_ADDRESS'),
				'BB_COMPANY_NAME'=> COption::GetOptionString('up.boxberrydelivery', 'BB_COMPANY_NAME'),
				'BB_INN'=> COption::GetOptionString('up.boxberrydelivery', 'BB_INN'),
				'BB_KPP'=> COption::GetOptionString('up.boxberrydelivery', 'BB_KPP'),
			);
			foreach($arOptFields as $key=> $optName)
			{
				foreach($order['PROPS'] as $cur_prop)
				{
					if ($optName==$cur_prop['CODE'])
					{
						$arReturn[$key] = $cur_prop["VALUE"]; 
					}
				}
			}
		return (count($arReturn) > 0 ? $arReturn : FALSE);
	}
	
	function changeAddress ($ORDER_ID=NULL, $address=NULL){
		if (!empty($address)){
			$dbProps = CSaleOrderPropsValue::GetOrderProps($ORDER_ID);
			$address_prop_bb = COption::GetOptionString('up.boxberrydelivery', 'BB_ADDRESS');			
			$location_prop_bb = COption::GetOptionString('up.boxberrydelivery', 'BB_LOCATION');
			if (strtoupper(LANG_CHARSET) != 'UTF-8'){
				$address = mb_convert_encoding ($address,'CP1251','UTF-8');			
			}
			
			while($prop = $dbProps->Fetch())
			{
				if ($prop['CODE'] == $address_prop_bb)
				{					
					CSaleOrderPropsValue::Update($prop['ID'], array("ORDER_ID"=>$ORDER_ID, "CODE"=>$prop['CODE'] ,"VALUE"=>$address));
				}				
			}
		}
		
	}
	
	function updatePVZ($id_pvz, $ORDER_ID=NULL, $address=NULL){		
		$arFields = array(
			'ORDER_ID' 			=> $ORDER_ID,
			'PVZ_CODE' 			=> $id_pvz,
			
		);
		CBoxberryOrder::Update($ORDER_ID,$arFields);
		self::changeAddress($ORDER_ID, $address);
		
	}
	
	function saveadminPVZ($id_pvz, $ORDER_ID=NULL, $address=NULL){
		$current_date = date('d.m.Y H:i:s');
		$arFields = array(				
				'PVZ_CODE' 			=> $id_pvz,				
				'STATUS_DATE' 		=> $current_date,
			);		
		CBoxberryOrder::Update($ORDER_ID, $arFields);
		self::changeAddress($ORDER_ID, $address);
	}
	function savePVZ($id_pvz){
		$_SESSION['selPVZ'] = $id_pvz;
	}
	function removePVZ(){
		unset($_SESSION['selPVZ']);
	}
	function checkPVZ()
	{
		if (isset($_SESSION['selPVZ']) && !empty($_SESSION['selPVZ']))
		{
			return $data['not_selected']=false;
		}
		else
		{
			return $data['not_selected']=true;
		}
	}
	function iconv_array($aJson, $from, $to)
	{	
		foreach ($aJson as $key => $value) 
	    {

			if (is_array($value)) 
	        {
	            $aJson[$key] = self::iconv_array($value, $from, $to);
	        } 
	        else 
	        {
	            $aJson[$key] = iconv('UTF-8',LANG_CHARSET, $value);
			}
	    }
		return $aJson;
	}
	function mb_conv_array($aJson)
	{	
		foreach ($aJson as $key => $value) 
	    {

			if (is_array($value)) 
	        {
	            $aJson[$key] = self::mb_conv_array($value);
	        } 
	        else 
	        {
	        	$aJson[$key] = mb_convert_encoding ($value,'UTF-8','CP1251');
			}
	    } 
		return $aJson;
	}
}

?>