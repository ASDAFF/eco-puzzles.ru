<?
IncludeModuleLangFile(__FILE__);

class sdekCityGetter{
	protected $arBXCity = false;
	protected $arSCity  = false;
	protected $bitrixId = false;
	protected $ready    = false;
	protected $added    = false;
	
	protected static $MODULE_ID = false;
	
	protected static $locationTypes = false;

	protected $timeout  = 6;
	
	protected $cacheCity    = false;
	protected $cacheConnect = false; // not working now because of SDEK BLET DIDNT KNOW THE DIFFERENSE BETWEEN DEAD SERVER AND NO ANSWER

	public function __construct($bitrixId,$timeout=false)
	{
		if($bitrixId && cmodule::includeModule('sale')){
			$this->ready    = true;
			$this->bitrixId = $bitrixId; 
		}
		
		if(intval($timeout)){
			$this->timeout = intval($timeout);
		}
		
		if(self::checkHelper()){
			self::$MODULE_ID = sdekHelper::$MODULE_ID;
		}
		
		$this->cacheCity    = new Ipolh\SDEK\Bitrix\Entity\cache();
		// $this->cacheConnect = new Ipolh\SDEK\Bitrix\Entity\cache();
		// $this->cacheConnect->setLife(300);
		
		$this->arBXCity = self::getCityChain($this->bitrixId);

		if(empty($this->arBXCity) || !$this->arBXCity['CITY']){
			if($this->bitrixId !== $this->arBXCity['BITRIX_ID']){
				$this->bitrixId = $this->arBXCity['BITRIX_ID'];
			}
			$this->ready = false;
		}
	}
	
	public function getSDEK()
	{
		return $this->arSCity;
	}

		// way of  search - first 4 requests till found. If found - trying to match regions. If fail - search without subregions - maybe, that would help
	public function search($write=true)
	{
		$hash = 'foundCity|'.$this->bitrixId;
		if($this->cacheCity && $this->cacheCity->checkCache($hash) && $this->cacheCity->getCache($hash)){
			return false;
		}

		if($this->ready){
			$country = self::guessCountry($this->arBXCity['COUNTRY']);
			
			$arRequest = self::zaDEjsonit(
				$this->callCitySearch()
			);
			
			if($arRequest['result']){
				$arMatches = $this->checkRegionMatches($arRequest);

				if(empty($arMatches) && $this->arBXCity['SUBREGION']){
					$arRequest = self::zaDEjsonit(
						$this->callCitySearch(true)
					);
					if($arRequest['result']){
						$arMatches = $this->checkRegionMatches($arRequest);
					}
				}
				
				if(!empty($arMatches)){
					// checking 4 existed stuff
					$arExisted = false;
					if(!empty($arMatches)){
						$arExisted = $this->checkExisted($arMatches);
					}

					if($write){
						$this->add($arMatches,$arExisted);
					}
					
					if(!$this->added){
						$this->arSCity = sqlSdekCity::getByBId($this->bitrixId);
					}
				}
				
				if(!$this->arSCity && $this->cacheCity){
					$this->cacheCity->setCache($hash,true);
				}
			}
		}
	}
	
	protected function callCitySearch($skipSubregion = false){
		$arReturn = array(
			'result'    => false,
			'strict'    => true, // was city simpled while searching
			'subregion' => false // was founded by subregion
		);
		
		$result = false;
		
		if($this->arBXCity['SUBREGION'] && !$skipSubregion){
			$arResult = $this->makeRequest($this->arBXCity['CITY'].', '.cityExport::simpleDistrict($this->arBXCity['SUBREGION']));
			if($arResult['result']){
				$arResult = $this->makeRequest(cityExport::simpleCityExt($this->arBXCity['CITY']).', '.cityExport::simpleDistrict($this->arBXCity['SUBREGION']));
				if($arResult['result'])
					$arReturn['strict'] = false;
			}
		}
		
		if(!$arResult['result']){
			$arResult = $this->makeRequest($this->arBXCity['CITY']);
			if(!$arResult['result']){
				$arResult = $this->makeRequest(cityExport::simpleCityExt($this->arBXCity['CITY']));
				$arReturn['strict'] = false;
			} else {
				$arReturn['strict'] = false;
			}
		} else {
			$arReturn['subregion'] = true;
		}
		
		$arReturn['result'] = ($arResult['result']) ? $arResult['result'] : false;
		
		return $arReturn;
	}
	
		// check city by region + GET PAY
	protected function checkRegionMatches($arResult)
	{
		$arMatches = array();
		foreach($arResult['result'] as $arCity){
			$cityNameCheck = ($arResult['strict']) ? $this->arBXCity['CITY'] : cityExport::simpleCityExt($this->arBXCity['CITY']);
			if(
				self::checkRegions($arCity['regionName'],$this->arBXCity['REGION']) &&
				self::checkCityNames($arCity['cityName'],$cityNameCheck)
			){
				$cityDescr = self::getCityFromFile($arCity['id'],$country);
				if($cityDescr){
					$arCity['pay'] = $cityDescr[5];
				}
				$arMatches []= $arCity;
			}
		}
		
		return $arMatches;
	}
	
	protected function checkExisted($arRegions)
	{
		$arIds = array();
		$arExisted = array();
		foreach($arRegions as $arRegion){
			$arIds []= $arRegion['id'];
		}
		
		if(class_exists('sqlSdekCity')){
			foreach($arIds as $key => $id){
				if(sqlSdekCity::getBySId($id)){
					$arExisted []= $id;
				}
			}
		}

		return $arExisted;
	}
		// getting information about country
	public static function getCityFromFile($sdekId,$country = 'rus')
	{
		if(!class_exists('sdekOption'))
			return false;

		$countryParams = sdekOption::getCountryDescr($country);
		if(!$countryParams)
			return false;
		
		$fileName = $_SERVER['DOCUMENT_ROOT'].'/bitrix/js/'.sdekOption::$MODULE_ID.'/'.$countryParams['FILE'];
		if(!file_exists($fileName) || filemtime($fileName) > 1440000){
			if(!sdekOption::requestCityFile($countryLink)){
				return false;
			}
		}
		
		$arCityArray = explode("\n",file_get_contents($_SERVER['DOCUMENT_ROOT'].'/bitrix/js/'.sdekOption::$MODULE_ID.'/'.$countryParams['FILE']));
		
		foreach($arCityArray as $city){
			$essence = explode(';',$city);
			if($essence[0] == $sdekId){
				return $essence;
			}
		}
		
		return false;
	}
	
	protected function add($wat,$existed = false)
	{
		$arWorked = array();

		// founded new city
		$arNewCity = array();
		if(!$existed || empty($existed)){
			$arNewCity = $this->getBestVariant($wat);

			if(!empty($arNewCity)){
				$foundedSearched = true; 
				
				// searching for better variants
				$arVariants = self::getCityByNameRegion($this->arBXCity['CITY'],$this->arBXCity['REGION']);
				
				$arAddingCity = false;
				if(count($arVariants) > 1){
					foreach($arVariants as $bitrixId => $val){
						if($val['SUBREGION'] && strpos($arNewCity['name'],cityExport::simpleDistrict($val['SUBREGION']))){
							$arAddingCity    = $val;
							$foundedSearched = false;
							break;
						}
					}
				}
				if(!$arAddingCity){
					$arAddingCity = $this->arBXCity;
				}
					// endAdding4Bettervariants
				
				$country = self::guessCountry($arAddingCity['COUNTRY']);
				
				$newCity = array(
					'BITRIX_ID' => $arAddingCity['BITRIX_ID'],
					'SDEK_ID'   => $arNewCity['id'],
					'NAME'      => $arAddingCity['CITY'],
					'REGION'    => $arAddingCity['REGION'],
					'PAYNAL'    => $arNewCity['pay'],
					'COUNTRY'	=> $country
				);

				sqlSdekCity::Add($newCity);
				
				$arWorked['new'] = $arNewCity['id'];
				$arWorked['handle'][$arNewCity['id']] = $arNewCity['id'];

				$this->arSCity = $newCity;
				$this->added   = $foundedSearched;
			}
		}

		if(!empty($wat) || !empty($arNewCity)){
			$this->workOutErrorCities($wat,$arNewCity,$arWorked,$existed);
		}
	}
	
	protected function workOutErrorCities($wat,$arNewCity,$arWorked,$existed){
		$rewriteConflict = false;
		$pathToErrCities = $_SERVER['DOCUMENT_ROOT'].'/bitrix/js/'.self::$MODULE_ID.'/errCities.json';
		if(file_exists($pathToErrCities)){

			foreach($wat as $founded){
				if(!empty($arNewCity) && $founded['id'] == $arNewCity['id'])
					continue;
				$arWorked['founded'] []= $founded['id'];
				$arWorked['handle'][$founded['id']]= $founded['id']; // all
			}
			
			$arConflicts = json_decode(file_get_contents($pathToErrCities),true);
			
			// worked unfounded - only new
			if(array_key_exists('new',$arWorked)){ // only new
				foreach($arConflicts['notFound'] as $key => $arNotFound){
					// if(array_key_exists($arNotFound['sdekId'],$arWorked['handle'])){ // all
					if($arNotFound['sdekId'] == $arWorked['new']){
						unset($arConflicts['notFound'][$key]);
						// unset($arWorked['handle'][$arNotFound['sdekId']]); // all
						$rewriteConflict = true;
						break; // only new
					}
					// if(empty($arWorked)){ // all
						// break;
					// }
				}
			}

			$bitrixId = ($foundedSearched) ? $this->bitrixId : $arAddingCity['BITRIX_ID'];
			// need to add new conflict ONLY IF existed - the city exists, otherwise CRAP happened
			if(empty($existed) &&  array_key_exists('new',$arWorked) && count($wat) > 1){
				if(!array_key_exists($bitrixId,$arConflicts['many'])){
					$arConflicts['many'][$bitrixId] = array(
						'takenLbl' => $arNewCity['regionName'].", ".$arNewCity['cityName'],
						'sdekCity' => array()
					);
					foreach($wat as $pretends){
						if(!empty($arNewCity) && $founded['id'] == $arNewCity['id'])
							continue;
						$arConflicts['many'][$bitrixId]['sdekCity'][$pretends['id']] = array(
							'name'   => $pretends['cityName'],
							'region' => $pretends['regionName']
						);
						$rewriteConflict = true;
					}
				}
			}
			// need to delete conflict if exists
			elseif(array_key_exists('new',$arWorked)){
				if(!array_key_exists($bitrixId,$arConflicts['many'])){
					$founded = false;
					foreach($arConflicts['many'] as $bitrixId => $arVals){
						foreach($arVals['sdekCity'] as $sdekId => $arParams){
							if($sdekId == $arWorked['new']){
								unset($arConflicts['many'][$bitrixId]['sdekCity'][$sdekId]);
								$founded = true;
								$rewriteConflict = true;
								break;
							}
						}
						if($founded){
							if(empty($arConflicts['many'][$bitrixId]['sdekCity'])){
								unset($arConflicts['many'][$bitrixId]);
							}
							break;
						}
					}
				}
			}
			
			if($rewriteConflict){
				file_put_contents($pathToErrCities,json_encode($arConflicts));
			}
		}
	}
	
	protected static function guessCountry($country=false){
		$arLinks = array(
			'rus' => 3,
			'blr' => 2,
			'kaz' => 1
		);
		
		foreach($arLinks as $countryKey => $incs){
			for($i = 1; $i <= $incs; $i++){
				$_i = ($i == 1) ? '' : $i;
				if(strpos(GetMessage('IPOLSDEK_SYNCTY_'.$countryKey.$_i),$country) !== false){
					return $countryKey;
				}
			}
		}
		
		return ($country) ? false : 'rus';
	}
	
	protected function getBestVariant($arPretendents)
	{
		if($this->arBXCity['SUBREGION']){
			foreach($arPretendents as $arPretender){
				if(strpos($arPretender['name'],cityExport::simpleDistrict($this->arBXCity['SUBREGION'])) !== false){
					return $arPretender;
				}
			}
		}
		
		return array_shift($arPretendents);
	}
	
	protected static function checkLocationTypes(){
		if(self::isLocation20()){
			self::$locationTypes = array(
				'COUNTRY'   => \Bitrix\Sale\Location\TypeTable::getList(array('select'=>array('*'),'filter'=>array('CODE'=>'COUNTRY')))->Fetch(),
				'REGION'    => \Bitrix\Sale\Location\TypeTable::getList(array('select'=>array('*'),'filter'=>array('CODE'=>'REGION')))->Fetch(),
				'SUBREGION' => \Bitrix\Sale\Location\TypeTable::getList(array('select'=>array('*'),'filter'=>array('CODE'=>'SUBREGION')))->Fetch()
			);
		} else {
			self::$locationTypes = array('COUNTRY' => true, 'REGION' => true, 'SUBREGION' => true);
		}
	}
	
	protected static function locTypeExists($locType){
		if(!self::$locationTypes)
			self::checkLocationTypes();
		return (array_key_exists($locType,self::$locationTypes) && self::$locationTypes[$locType]);
	}
	
	protected function makeRequest($city)
	{
		$hash = 'timeoutFindCity';
		$arReturn = array('error' => true, 'error' => 'dead');
		if(!$this->cacheConnect || !$this->cacheCity->checkCache($hash) || !$this->cacheCity->getCache($hash)){
			$arResult = self::getAPICity(self::zajsonit($city),50,$this->timeout);
			if($arResult['success']){
				$arReturn = array('result' => (empty($arResult['result'])) ? false : $arResult['result'],'error' => false);
			} elseif(!$arResult['code']){
				if($this->cacheConnect)
					$this->cacheConnect->setCache($hash,true);
				$arReturn = array('result' => false,'error' => 'dead');
			} else{
				$arReturn = array('result' => false,'error' => 'badCode');
			}
		}

		return $arReturn;
	}

	// COMMON
	public static function checkHelper(){
		return class_exists('sdekHelper');
	}
	
	public static function getCityByNameRegion($name,$region,$country=false){
		$arCities = array();
		if(self::getSaleModule()){
			if(self::isLocation20()){
				$cities = \Bitrix\Sale\Location\LocationTable::getList(array('filter' => array('=NAME.NAME' => $name)));
				while($city = $cities->Fetch()){
					$cityChain = self::getCityChain($city['ID']);
					if($cityChain['REGION'] == $region){
						$arCities[$city['ID']] = $cityChain;
					}
				}
			} else {
				$cities = CSaleLocation::GetList(array(),array('REGION_NAME'=>$region,'CITY_NAME'=>$name,'REGION_LID'=>'ru','CITY_LID'=>'ru','COUNTRY_LID'=> 'ru'));
				while($city = $cities->Fetch()){
					$arCities[$city['ID']] = array(
						'BITRIX_ID' => $city['ID'],
						'CITY'		=> $city['CITY_NAME'],
						'COUNTRY'	=> $city['COUNTRY_NAME'],
						'REGION'    => $city['REGION_NAME'],
						'SUBREGION' => false
					);
				}
			}
		}
		
		return $arCities;
	}
	
	public static function isLocation20(){
		if(self::getSaleModule() && self::checkHelper() && method_exists(sdekHelper,'isLocation20')){
			return sdekHelper::isLocation20();
		} else {
			return (method_exists("CSaleLocation","isLocationProMigrated") && CSaleLocation::isLocationProMigrated());
		}
    }
	
	public static function zaDEjsonit($wat){
		return (self::checkHelper()) ? sdekHelper::zaDEjsonit($wat) : $wat;
	}
	
	public static function zajsonit($wat){
		return (self::checkHelper()) ? sdekHelper::zajsonit($wat) : $wat;
	}
	
	public static function getSaleModule(){
		return cmodule::includeModule('sale');
	}
	
		// check weither region1 similar to region2. Uses method from export - for equality
	public static function checkRegions($region1,$region2)
	{
		$arSimpledRegions = array();
		if(class_exists('cityExport')){
			if(method_exists(cityExport,'getRegLinks')){
				$arSimpledRegions = cityExport::getRegLinks();
			}
			if(method_exists(cityExport,'simpleRegion')){
				$region1 = cityExport::simpleRegion($region1,$arSimpledRegions);
				$region2 = cityExport::simpleRegion($region2,$arSimpledRegions);
			}
		}
		return ($region1 == $region2 && $region1 !== 'UNDEFINED');
	}
	
		// check weither city1 similar to city2
	public static function checkCityNames($city1,$city2)
	{
		if(!self::checkHelper())
			return ($city1 === $city2);
		$city1 = trim(sdekhelper::toUpper($city1));
		$city2 = trim(sdekhelper::toUpper($city2));
		if($city1 === $city2)
			return true;
		else{
			return (
				preg_match("/(^|[ ,])".$city1."($|[ ,])/i",$city2) ||
				preg_match("/(^|[ ,])".$city2."($|[ ,])/i",$city1)
			);
		}
		
		return false;
	}

		// making location chain for the city
	public static function getCityChain($bitrixID)
	{
		$arCity = array(
			'BITRIX_ID' => $bitrixID,
			'COUNTRY'   => false,
			'REGION'    => false,
			'SUBREGION' => false,
			'CITY'   	=> false
		);
		if(self::getSaleModule()){
			if(self::isLocation20()){
				 if(strlen($bitrixID) >= 10 || !is_numeric($bitrixID)){
					$city = \Bitrix\Sale\Location\LocationTable::getList(array('filter' => array('=CODE' => $bitrixID)))->Fetch();
					if($city && $city['ID']){
						$bitrixID = $city['ID'];
						$arCity['BITRIX_ID'] = $bitrixID;
						// $this->bitrixId = $bitrixID;
					} else {
						return false;
					}
				 }
				 
				$city   = self::searchLocationUntill($bitrixID,array('CITY','VILLAGE'));
				if($city){
					$arCity['CITY']     = $city['LOCATION_NAME'];
					
					foreach(array('COUNTRY','REGION','SUBREGION') as $locationType){
						$arCity[$locationType] = false;
						if(self::locTypeExists($locationType)){
							$location = self::searchLocationUntill($bitrixID,$locationType);
							$arCity[$locationType] = ($location) ? $location['LOCATION_NAME'] : false;
						}
					}
				}
			} else {
				$arDBCity = CSaleLocation::GetByID($bitrixID);
				if($arDBCity){
					if($arDBCity['COUNTRY_NAME_LANG']){
						$arCity['COUNTRY'] = $arDBCity['COUNTRY_NAME_LANG'];
					}
					if($arDBCity['REGION_NAME_LANG']){
						$arCity['REGION'] = $arDBCity['REGION_NAME_LANG'];
					}
					$arCity['CITY'] = $arDBCity['CITY_NAME_LANG'];
					$arCity['SUBREGION'] = false;
				}
			}
		}
		
		return $arCity;
	}
	
	public static function getLocationById($id)
	{
		$location = false;
		if(self::getSaleModule()){
			if(self::isLocation20()){
				$location = \Bitrix\Sale\Location\LocationTable::getList(array(
					'filter' => array(
						'=ID' => $id,
						'=NAME.LANGUAGE_ID' => 'ru'
					),
					'select' => array(
						'ID', 
						'LOCATION_NAME' => 'NAME.NAME', 
						'TYPE_CODE'     => 'TYPE.CODE',
						'PARENT_LOCATION_ID'=>'PARENT.ID',
					)
				))->Fetch();
			} else {
				$location = CSaleLocation::GetById($id);
			}
		}
		
		return $location;
	}
	
		// gets location and search the closest location of $Type for making chain
	public static function searchLocationUntill($bitrixId,$TYPE){
		if(!is_array($TYPE)){
			$TYPE = array($TYPE);
		}
		$location = array('TYPE_CODE' => 'NOT WHAT U WANT','PARENT_LOCATION_ID' =>$bitrixId);
		while(!in_array($location['TYPE_CODE'],$TYPE) && $location['PARENT_LOCATION_ID']){
			$location = self::getLocationById($location['PARENT_LOCATION_ID']);
		}
		if(in_array($location['TYPE_CODE'],$TYPE)){
			return $location;
		} else {
			return false;
		}
	}

	public static function getAPICity($cityName,$limit=false,$timeout=false)
	{
		if(function_exists('curl_init')){
			$url = 'http://api.cdek.ru/city/getListByTerm/json.php';
			$ch  = curl_init();
			
			$arGet = array('q' => $cityName);
			
			if(intval($limit)){
				$arGet['limit']=$limit;
			}
			
			$arReturn = array(
				'success' => false,
				'result'  => false,
				'code'    => false
			);

			curl_setopt($ch,CURLOPT_URL,$url.'?'.http_build_query($arGet));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			
			if(floatval($timeout) <= 0) $timeout = 6;
			
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,$timeout);
			curl_setopt($ch, CURLOPT_TIMEOUT,$timeout);
			
			$result = curl_exec($ch);
			$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);

			if($code = '200'){
				$result = json_decode($result,true);
				if($result && array_key_exists('geonames',$result) && !empty($result['geonames'])){
					$arReturn = array(
						'success' => true,
						'result'  => $result['geonames'],
						'code'    => $code
					);
				}
			} else {
				$arReturn['code'] = $code;
			}
		}
		
		return $arReturn;
	}
}