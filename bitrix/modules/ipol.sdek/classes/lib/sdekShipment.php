<?
	class sdekShipment{
	    public $accountId;
		// города
		public $sender;
		public $receiver;

		// товары
		public $gabs;
		public $goods;

		// расчет
		public $profiles;
		public $error = false;
		public $arErrors;

		// параметризация

		function sdekShipment($params=array()){
			if(!self::checkField('RECEIVER',$params))
				$this->addError(GetMessage('IPOLSDEK_SHIPMENT_ERRRECEIVER'));
			if(!self::checkField('ITEMS',$params) && !self::checkField('GABS',$params))
				$this->addError(GetMessage('IPOLSDEK_SHIPMENT_ERRGOODS'));

			$this->sender    = (self::checkField('SENDER',$params)) ? $params['SENDER'] : CDeliverySDEK::getHomeCity();
			$this->receiver  = $params['RECEIVER'];
			$this->accountId = (self::checkField('ACCOUNT',$params)) ? $params['ACCOUNT'] : false;

			if(self::checkField('ITEMS',$params)){
				$this->goods = $params['ITEMS'];
				CDeliverySDEK::setGoods($this->goods);
				$this->gabs = CDeliverySDEK::$goods;
			}else
				$this->gabs = $params['GABS'];
		}

		function calcProfile($profile){
			if(!$this->getError()){
				CDeliverySDEK::$goods      = $this->gabs;
				CDeliverySDEK::$sdekSender = $this->sender;
				CDeliverySDEK::$sdekCity   = $this->receiver;

				foreach(GetModuleEvents(CDeliverySDEK::$MODULE_ID, "onBeforeRequestDelivery", true) as $arEvent)
					ExecuteModuleEventEx($arEvent,Array($profile));

				$cache = new Ipolh\SDEK\Bitrix\Entity\cache();
				$cachename = "calculate|$profile|".$this->sender."|".$this->receiver."|".implode('|',$this->gabs)."|".$this->accountId;
				if($cache->checkCache($cachename)){
					$result = $cache->getCache($cachename);
				}else{
					$result = CDeliverySDEK::formCalcRequest($profile,$this->accountId);
					if($result['success']){
						$cache->setCache($cachename,$result);
					}
				}

				if(!isset($this->profiles) || !is_array($this->profiles))
					$this->profiles = array();

				if($result['success']){
					$addTerm = intval(COption::GetOptionString(CDeliverySDEK::$MODULE_ID,'termInc',false));
					$this->profiles[$profile] = array(
						'RESULT'   => 'OK',
						'PRICE'    => $result['price'],
						'CURRENCY'  => $result['currency'],
						'PRICE_CUR' => $result['priceByCurrency'],
						'TERMSBAZE' => array(
							'MIN' => $result['termMin'],
							'MAX' => $result['termMax']
						),
						'TERMS' => array(
							'MIN' => $result['termMin']+$addTerm,
							'MAX' => $result['termMax']+$addTerm
						),
						'TARIF' => $result['tarif']
					);
				}else{
					$erStr = '';
					foreach($result as $erCode => $erLabl)
						$erStr.="$erLabl ($erCode) ";
					$this->profiles[$profile] = array(
						'RESULT' => 'ERROR',
						'TEXT'	 => CDeliverySDEK::zaDEjsonit($erStr)
					);
				}
			}else{
				$this->profiles[$profile] = array(
					'RESULT' => 'ERROR',
					'TEXT'	 => $this->getError()
				);
			}
		}

		function calcProfiles($arProfiles){
			foreach($arProfiles as $profile)
				$this->calcProfile($profile);
		}

		function compability(){
			if(!is_array($this->profiles))
				return false;
			$arReturn = array();
			foreach($this->profiles as $profile => $result)
				if($result['RESULT'] == 'OK')
					$arReturn[] = $profile;
			return $arReturn;
		}

		private function checkField($wat,$src){
			return (array_key_exists($wat,$src) && $src[$wat]);
		}

		function getProfiles(){
			return $this->profiles;
		}

		function getProfile($profile){
			return (array_key_exists($profile,$this->profiles)) ? $this->profiles[$profile] : false;
		}

		function getProfileTarif($profile){
			return (is_array($this->profiles) && array_key_exists($profile,$this->profiles) && $this->profiles[$profile]['RESULT'] == 'OK') ? $this->profiles[$profile]['TARIF'] : false;
		}
		
		private function addError($errMess){
			$this->error = true;
			if(!isset($this->arErrors))
				$this->arErrors = array();
			$this->arErrors []= $errMess;
		}
		
		public function getError(){
			if(!$this->error)
				return false;
			else
				return implode(', ',$this->arErrors);
		}
	}
?>