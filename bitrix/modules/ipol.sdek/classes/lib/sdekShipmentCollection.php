<?
	class sdekShipmentCollection{
		static $shipments;
		public  static $accountId = false;
		private static $ready    = false;

		static function ready(){
			return self::$ready;
		}

		static function init($locationTo,$src,$order=false){
			self::$ready = false;
			self::$shipments = false;
			if(!$order) {
                self::$shipments[] = new sdekShipment(array(
                    'RECEIVER' => $locationTo,
                    'ITEMS' => $src,
                    'SENDER' => (isset(CDeliverySDEK::$sdekSender)) ? CDeliverySDEK::$sdekSender : false,
                    'ACCOUNT' => self::$accountId
                ));
            }else{
				$bazeSender = (CDeliverySDEK::$sdekSender) ? CDeliverySDEK::$sdekSender : CDeliverySDEK::getHomeCity();
				foreach($src as $good){
					foreach($order as $content)
						if(array_key_exists($good['ID'],$content['ITEMS'])){
							$cnt = $content['ITEMS'][$good['ID']];
							if($cnt > $good['QUANTITY'])
								$cnt = $good['QUANTITY'];
							$good['QUANTITY'] -= $cnt;
							if(!$good['QUANTITY'])
								break;
						}
					if($good['QUANTITY'] > 0){
						$order['unspreaded'] = array('SENDER'=>$bazeSender);
						if(array_key_exists($good['ID'],$order['unspreaded']['ITEMS']))
							$order['unspreaded']['ITEMS'][$good['ID']] += $good['QUANTITY'];
						else
							$order['unspreaded']['ITEMS'][$good['ID']]=intVal($good['QUANTITY']);
					}
				}
				foreach($order as $content){
					$arGoods = array();
					foreach($src as $good)
						if(array_key_exists($good['ID'],$content['ITEMS'])){
							$good['QUANTITY'] = $content['ITEMS'][$good['ID']];
							$arGoods[]=$good;
						}
					if(count($arGoods))
						self::$shipments[] = new sdekShipment(array(
							'RECEIVER' => $locationTo,
							'ITEMS'	   => $arGoods,
							'SENDER'   => intVal($content['SENDER']),
                            'ACCOUNT'  => $content['ACCOUNT']
						));
					}
			}
			self::$ready = true;
		}

		static function initGabs($locationTo){
			self::$ready = false;
			$arShip = array(
				'RECEIVER' => $locationTo,
				'GABS'	   => CDeliverySDEK::$goods,
                'ACCOUNT'  => self::$accountId
			);
			if(CDeliverySDEK::$sdekSender)
				$arShip['SENDER']  = CDeliverySDEK::$sdekSender;

			self::$shipments = array(
				new sdekShipment($arShip)
			);
			self::$ready = true;
		}

		static function formation($arOrder){
			if(array_key_exists('ITEMS',$arOrder) && $arOrder['ITEMS'])
				return $arOrder['ITEMS'];
			elseif(array_key_exists('ID',$arOrder) && $arOrder['ID'])
				return CDeliverySDEK::setOrderGoods($arOrder['ID']);
			else{
				CDeliverySDEK::setOrder($arOrder);
				return false;
			}
		}

		static function calculate($profile = false){
			if(!self::ready())
				return false;
			if(!$profile)
				$profile = array('courier','pickup');
			foreach(self::$shipments as $shipment)
				if(is_array($profile))
					$shipment->calcProfiles($profile);
				else
					$shipment->calcProfile($profile);
		}

		static function compability(){
			if(!self::ready())
				return false;
			$arKeys = false;
			foreach(self::$shipments as $shipment){
				$curCompability = $shipment->compability();
				if(!$arKeys)
					$arKeys = $curCompability;
				else
					$arKeys = array_intersect($arKeys,$curCompability);
				if(!$arKeys)
					break;
			}
			return $arKeys;
		}

		static function getProfile($profile){
			if(!self::ready())
				return false;
			$arResult = false;
			$first = true;
			foreach(self::$shipments as $shipment){
				$curProfile = $shipment->getProfile($profile);
				if(!$curProfile)
					return false;
				elseif($curProfile['RESULT'] == 'ERROR')
					return $curProfile;
				else{
					if(!$arResult){
						$arResult = array(
							'RESULT' => 'OK',
							'PRICE'  => $curProfile['PRICE'],
							'TERMS'  => $curProfile['TERMS'],
							'TARIF'  => array($shipment->sender => $curProfile['TARIF'])
						);
						$first = $curProfile['TARIF'];
					}else{
						$arResult['PRICE'] += $curProfile['PRICE'];
						$arResult['TERMS']['MIN'] = max($arResult['TERMS']['MIN'],$curProfile['TERMS']['MIN']);
						$arResult['TERMS']['MAX'] = max($arResult['TERMS']['MAX'],$curProfile['TERMS']['MAX']);
						$arResult['TARIF'][$shipment->sender] = $curProfile['TARIF'];
						if($first && $first != $curProfile['TARIF'])
							$first = false;
					}
				}
			}

			if($arResult['TERMS']['MIN'] > $arResult['TERMS']['MAX'])
				$arResult['TERMS']['MAX'] = $arResult['TERMS']['MIN'];

			$arResult['TARIF'] = ($first) ? array_pop($arResult['TARIF']) : serialize($arResult['TARIF']);
			return $arResult;
		}

		static function getProfileTarif($profile){
			$arTarif = array();
			$similar = true;
			foreach(self::$shipments as $shipment){
				$cT = $shipment->getProfileTarif($profile);
				$arTarif[] = array($shipment->sender,$cT);
				if($similar){
					if($similar === true)
						$similar = $cT;
					elseif($similar != $cT)
						$similar = false;
				}					
			}
			return ($similar) ? $similar : json_encode(sdekHelper::zajsonit($arTarif));
		}

		static function getProfileFull(){
			$arReturn = array();
			foreach(self::$shipments as $shipment)
				$arReturn[$shipment->sender] = $shipment->getProfiles();
			return $arReturn;
		}
	}
?>