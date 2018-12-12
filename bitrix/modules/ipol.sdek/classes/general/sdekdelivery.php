<?
cmodule::includeModule('sale');
IncludeModuleLangFile(__FILE__);

/*
	IPOLSDEK_CACHE_TIME - время кэша в секундах
	IPOLSDEK_NOCACHE    - если задан - не использовать кэш

	onBeforeDimensionsCount - габариты товаров
	onCompabilityBefore - годнота профилей
	onCalculate - готовность расчета
*/

class CDeliverySDEK extends sdekHelper{
	static $profiles     = false;
	static $hasPVZ       = false;//грузим ли ПВЗ

	static $date         = false; // срок доставки
	private static $_date = false; // дата доставки

	static $price        = false;

	static $orderWeight  = false;
	static $orderPrice   = false;

	static $sdekCity     = false;
	static $sdekCityCntr = false;
	static $sdekSender   = false;
	private static $extSdekSender = false;
	static $goods        = false; // кг, см
	static $PVZcities    = false;
	
	static $payerType = false;
	static $paysystem = false;

	private static $auth    = false;
    private static $account = false;

	static $preSet       = false; // флаг установки габаритов
	static $lastCnt      = false; // значение последнего расчета стоимости

	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
													База службы доставки
		== Init ==  == SetSettings ==  == GetSettings ==  == Compability ==  == Calculate ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


	static function Init(){
		return array(
			// Basic description
			"SID" => "sdek",
			"NAME" => GetMessage("IPOLSDEK_DELIV_NAME"),
			"DESCRIPTION" => GetMessage('IPOLSDEK_DELIV_DESCR'),
			"DESCRIPTION_INNER" => GetMessage('IPOLSDEK_DELIV_DESCRINNER'),
			"BASE_CURRENCY" => COption::GetOptionString("sale", "default_currency", "RUB"),
			"HANDLER" => __FILE__,

			// Handler methods
			"DBGETSETTINGS" => array("CDeliverySDEK", "GetSettings"),
			"DBSETSETTINGS" => array("CDeliverySDEK", "SetSettings"),
			"GETCONFIG"     => array("CDeliverySDEK", "GetConfig"),

			"COMPABILITY" => array("CDeliverySDEK", "Compability"),
			"CALCULATOR" => array("CDeliverySDEK", "Calculate"),

			// List of delivery profiles
			"PROFILES" => array(
				"courier" => array(
					"TITLE" => GetMessage('IPOLSDEK_DELIV_COURIER_TITLE'),
					"DESCRIPTION" => GetMessage('IPOLSDEK_DELIV_COURIER_DESCR'),

					"RESTRICTIONS_WEIGHT" => array(0,300000),
					"RESTRICTIONS_SUM" => array(0),

					"RESTRICTIONS_MAX_SIZE" => "1000",
					"RESTRICTIONS_DIMENSIONS_SUM" => "1500",
				),
				"pickup" => array(
					"TITLE" => GetMessage('IPOLSDEK_DELIV_PICKUP_TITLE'),
					"DESCRIPTION" => GetMessage('IPOLSDEK_DELIV_PICKUP_DESCR'),

					"RESTRICTIONS_WEIGHT" => array(0,300000),
					"RESTRICTIONS_SUM" => array(0),
					"RESTRICTIONS_MAX_SIZE" => "1000",
					"RESTRICTIONS_DIMENSIONS_SUM" => "1500"
				),
				"inpost" => array(
					"TITLE" => GetMessage('IPOLSDEK_DELIV_INPOST_TITLE'),
					"DESCRIPTION" => GetMessage('IPOLSDEK_DELIV_INPOST_DESCR'),

					"RESTRICTIONS_WEIGHT" => array(0,20000),
					"RESTRICTIONS_SUM" => array(0),
					"RESTRICTIONS_MAX_SIZE" => "640",
					"RESTRICTIONS_DIMENSIONS_SUM" => "1430"
				),
			)
		);
	}

	static function GetConfig(){
	    // Account
        $arActiveAccs = sqlSdekLogs::getAccountsList(true);
        $arAccs = array(false => GetMessage("IPOLSDEK_DELCONFIG_DEFAULT"));
        foreach ($arActiveAccs as $id => $arActiveAcc) {
            if($arActiveAcc['LABEL']) {
                $arAccs[$id] = $arActiveAcc['ACCOUNT']." ({$arActiveAcc['LABEL']})";
            }else{
                $arAccs[$id] = $arActiveAcc['ACCOUNT'];
            }
        }

        // SenderCity
        $city = self::getSQLCityBI(COption::GetOptionString(self::$MODULE_ID,'departure'));
        $arSenders   = array(false => GetMessage('IPOLSDEK_DELCONFIG_DEFAULT'),$city['SDEK_ID']=>$city['NAME']." (".GetMessage('IPOLSDEK_DELCONFIG_BACIS').")");
        $citySenders = unserialize(COption::GetOptionString(self::$MODULE_ID,'addDeparture','a:{}'));
        if(!empty($citySenders)){
            foreach($citySenders as $cityId){
                $city = sqlSdekCity::getBySId($cityId);
                $arSenders[$city['SDEK_ID']] = $city['NAME']." (".$city['REGION'].")";
            }
        }

        // Countries
        $countries   = self::getActiveCountries();
        $arCountries = array();
        foreach ($countries as $key => $country) {
            //$arCountries[$country] = GetMessage('IPOLSDEK_SYNCTY_'.$country);
            $arCountries [($key+1)]= GetMessage('IPOLSDEK_SYNCTY_'.$country);
        }

	    return array(
	        "CONFIG_GROUPS" => array(
	            "request" => GetMessage('IPOLSDEK_DELCONFIG_REQUEST')
            ),
            "CONFIG" => array(
                "ACCOUNT" => array(
                    'TITLE'   => GetMessage('IPOLSDEK_DELCONFIG_ACCOUNT'),
                    'TYPE'    => 'DROPDOWN',
                    'DEFAULT' => false,
                    'GROUP'   => "request",
                    'VALUES'  => $arAccs
                ),
                "SENDER" => array(
                    'TITLE'   => GetMessage('IPOLSDEK_DELCONFIG_SENDER'),
                    'TYPE'    => 'DROPDOWN',
                    'DEFAULT' => false,
                    'GROUP'   => "request",
                    'VALUES'  => $arSenders
                ),
                "COUNTRIES" => array(
                    'TITLE'   => GetMessage('IPOLSDEK_DELCONFIG_COUNTRIES'),
                    'TYPE'    => 'MULTISELECT',
                    'DEFAULT' => false,
                    'GROUP'   => "request",
                    'VALUES'  => $arCountries
                ),
                "UID" => array(
                    'TITLE'   => GetMessage('IPOLSDEK_DELCONFIG_UID'),
                    'TYPE'    => 'STRING',
                    'DEFAULT' => '',
                    'GROUP'   => "request",
                )
            )
        );
    }

	static function SetSettings($arSettings){
		return serialize($arSettings);
	}

	static function GetSettings($strSettings){
		return unserialize($strSettings);
	}

	// метод проверки совместимости в данном случае практически аналогичен рассчету стоимости
	static function Compability($arOrder, $arConfig){
		self::$orderWeight = $arOrder['WEIGHT'];
		self::$orderPrice  = $arOrder['PRICE'];
		self::$payerType   = $arOrder['PERSON_TYPE_ID'];

	    if(
	        !self::checkAviable($arOrder, $arConfig) ||
            empty($arOrder['LOCATION_TO'])
        )
	        return false;

		$arKeys = array();

		$_SESSION['IPOLSDEK_CHOSEN'] = array();

		// defining cityTo
		$arCity = self::getCity($arOrder['LOCATION_TO'],true);
		
		if($arCity){
			self::$sdekCity = $arCity['SDEK_ID'];
			self::$sdekCityCntr = ($arCity['COUNTRY']) ? $arCity['COUNTRY'] : 'rus';
			if(self::$sdekCity){
				$countries = self::getActiveCountries();
				$curdeliveryCountryAv = true;

				// checking country
				if(
					!empty($arConfig) &&
					array_key_exists('COUNTRIES',$arConfig) &&
					array_key_exists('VALUE',$arConfig['COUNTRIES']) &&
					$arConfig['COUNTRIES']['VALUE']
				){
					foreach ($countries as $key => $country){
						if(self::$sdekCityCntr == $country){
							if(!in_array(($key+1),$arConfig['COUNTRIES']['VALUE'])){
								$curdeliveryCountryAv = false;
							}
							break;
						}
					}
				}

				if(
					(
						in_array($arCity['COUNTRY'],$countries) ||
						(!$arCity['COUNTRY'] && in_array('rus',$countries))
					) &&
					$curdeliveryCountryAv
				){
					self::$city = $arCity['NAME'];
					self::$cityId = $arOrder['LOCATION_TO'];
					$arKeys[]='courier';
					if(self::checkPVZ())
						$arKeys[]='pickup';
				}
			}

			// from where
			if(self::$extSdekSender){
				CDeliverySDEK::$sdekSender = self::$extSdekSender;
			}else{
				CDeliverySDEK::$sdekSender = self::getDeliverySender($arConfig);
			}

			// account
			if(self::$account){
				sdekShipmentCollection::$accountId = self::$account;
			}elseif(
				!empty($arConfig) &&
				array_key_exists('VALUE',$arConfig['ACCOUNT']) &&
				$arConfig['ACCOUNT']['VALUE']
			){
				sdekShipmentCollection::$accountId = $arConfig['ACCOUNT']['VALUE'];
			}

			// отключаем те профили, где нет включенных тарифов
			foreach($arKeys as $key => $profile)
				if(!self::checkTarifAvail($profile))
					unset($arKeys[$key]);

			if(!COption::GetOptionString(self::$MODULE_ID,'pvzPicker',false))
				foreach($arKeys as $ind => $profile)
					if($profile=='pickup'){
						unset($arKeys[$ind]);
						break;
					}

			//проверяем возможность доставки, если не редактирование заказа
			if(strpos($_SERVER['REQUEST_URI'],"bitrix/admin/sale_order_new.php") === false && !$_POST['isdek_action']){
				if(!self::$preSet && $arItems = sdekShipmentCollection::formation($arOrder)){
					// событие заполнения порядка
					$order = array();
					foreach(GetModuleEvents(self::$MODULE_ID, "onBeforeShipment", true) as $arEvent)
						ExecuteModuleEventEx($arEvent,Array(&$order,$arItems));

					sdekShipmentCollection::init(self::$sdekCity,$arItems,$order);
				}else {
					sdekShipmentCollection::initGabs(self::$sdekCity);
				}

				if(count($arKeys)){
					sdekShipmentCollection::calculate((self::$preSet)?self::$preSet:$arKeys);
					$arKeys = sdekShipmentCollection::compability();
				}

				if(!array_key_exists('IPOLSDEK_CHOSEN',$_SESSION))
					$_SESSION['IPOLSDEK_CHOSEN'] = array();
				
				if(!empty($arKeys))
					foreach($arKeys as $profile)
						$_SESSION['IPOLSDEK_CHOSEN'][$profile] = sdekShipmentCollection::getProfileTarif($profile);
			}

			$ifPrevent=true;
			foreach(GetModuleEvents(self::$MODULE_ID, "onCompabilityBefore", true) as $arEvent)
				$ifPrevent = ExecuteModuleEventEx($arEvent,Array($arOrder,$arConfig,$arKeys));

			if(is_array($ifPrevent)){
				$newKeys = array();
				foreach($ifPrevent as $val)
					if(in_array($val, $arKeys))
						$newKeys[] = $val;
				$arKeys = $newKeys;
			}

			if(!$ifPrevent) return array();
		
		}

		// Подключение FrontEnd (для многостраничного компонента)
		if($_POST['CurrentStep'] > 1 && $_POST['CurrentStep'] < 4 && in_array('pickup',$arKeys))
			self::pickupLoader();

		return $arKeys;
	}

	static function Calculate($profile, $arConfig, $arOrder, $STEP, $TEMP = false){
        if(
            !self::checkAviable($arOrder, $arConfig)
        )
            return array(
                "RESULT" => "ERROR",
                "TEXT"   => GetMessage('IPOLSDEK_DELIV_ERR_NOCNT'),
            );

		if(!self::$sdekCity){
			$arCity = self::getCity($arOrder['LOCATION_TO'],true);
			
			if($arCity){
				self::$sdekCity = $arCity['SDEK_ID'];
				self::$sdekCityCntr = ($arCity['COUNTRY']) ? $arCity['COUNTRY'] : 'rus';
			}
		}
		
		if(self::$sdekCity){
			//if(!sdekShipmentCollection::ready()){
				if(!self::$preSet && $arItems = sdekShipmentCollection::formation($arOrder)){
					$order = array();
					foreach(GetModuleEvents(self::$MODULE_ID, "onBeforeShipment", true) as $arEvent)
						ExecuteModuleEventEx($arEvent,Array(&$order,$arItems));

					// DELIVERY
					if(self::$extSdekSender){
						CDeliverySDEK::$sdekSender = self::$extSdekSender;
					}else{
						CDeliverySDEK::$sdekSender = self::getDeliverySender($arConfig);
					}

					// ACCOUNT
					if(self::$account){
						sdekShipmentCollection::$accountId = self::$account;
					}elseif(
						!empty($arConfig) &&
						array_key_exists('VALUE',$arConfig['ACCOUNT']) &&
						$arConfig['ACCOUNT']['VALUE']
					){
						sdekShipmentCollection::$accountId = $arConfig['ACCOUNT']['VALUE'];
					}

					// событие заполнения порядка
					if($arItems)
						sdekShipmentCollection::init(self::$sdekCity,$arItems,$order);
				}else
					sdekShipmentCollection::initGabs(self::$sdekCity);
			//}
			$tarifs = (self::$preSet) ? self::$preSet : $profile;
			sdekShipmentCollection::calculate($tarifs);
			$curProfile = sdekShipmentCollection::getProfile($tarifs);

			if($curProfile){
				if($curProfile['RESULT'] == "OK"){
					// определение валют
					/* нет
					$currency = self::getCountryOptions();
					if(
						array_key_exists(self::$sdekCityCntr,$currency) && 
						array_key_exists('cur',$currency[self::$sdekCityCntr]) && 
						$currency[self::$sdekCityCntr]['cur']
					)
						$curProfile['PRICE'] = floatval(sdekExport::formatCurrency(array('FROM'=>$currency[self::$sdekCityCntr]['cur'],'SUM'=>$curProfile['PRICE'])));
					*/

					if(COption::GetOptionString(self::$MODULE_ID,'mindEnsure','N') == 'Y'){
						$ensurance = $arOrder['PRICE']*floatval(COption::GetOptionString(self::$MODULE_ID,'ensureProc','1.5'))/100;
						if(COption::GetOptionString(self::$MODULE_ID,'mindNDSEnsure','Y') == 'Y'){
							$ensurance +=  $ensurance * 18 /100;
						}
						$curProfile['PRICE'] += $ensurance;
					}

					$arReturn = array(
						"RESULT"  => "OK",
						"VALUE"   => $curProfile['PRICE'],
						"TRANSIT" => ($curProfile['TERMS']['MIN'] == $curProfile['TERMS']['MAX']) ? $curProfile['TERMS']['MAX'] : $curProfile['TERMS']['MIN'].'-'.$curProfile['TERMS']['MAX'],
						"TARIF"   => $curProfile['TARIF'],
					);
					if(CheckVersion(self::getSaleVersion(),'15.0.0')){
						$time = $curProfile['TERMS']['MAX'];
						if($time > 4 && $time < 21 || $time == 0)
							$arReturn['TRANSIT'] .= ' '.GetMessage('IPOLSDEK_DELIV_days');
						else{
							$lst = $time % 10;
							if($lst == 1)
								$arReturn['TRANSIT'] .= ' '.GetMessage('IPOLSDEK_DELIV_day');
							elseif($lst < 5)
								$arReturn['TRANSIT'] .= ' '.GetMessage('IPOLSDEK_DELIV_daya');
							else
								$arReturn['TRANSIT'] .= ' '.GetMessage('IPOLSDEK_DELIV_days');
						}
					}
				}else
					$arReturn = array(
						"RESULT" => "ERROR",
						"TEXT"   => $curProfile['TEXT'],
					);
			}else
				$arReturn = array(
					"RESULT" => "ERROR",
					"TEXT"   => GetMessage('IPOLSDEK_DELIV_ERR_NOCNT'),
				);
		} else {
			$arReturn = array(
					"RESULT" => "ERROR",
					"TEXT"   => GetMessage('IPOLSDEK_DELIV_ERR_NOCTO'),
				);
		}

		foreach(GetModuleEvents(self::$MODULE_ID, "onCalculate", true) as $arEvent)
			ExecuteModuleEventEx($arEvent,Array(&$arReturn,$profile,$arConfig,$arOrder));

		if($arReturn['RESULT'] == 'OK'){
			self::$profiles[$profile] = array(
				"VALUE"       => $curProfile['PRICE'],
				"PRINT_VALUE" => CCurrencyLang::CurrencyFormat($curProfile['PRICE'],'RUB',true),
				"TRANSIT"     => ($curProfile['TERMS']['MIN'] == $curProfile['TERMS']['MAX']) ? $curProfile['TERMS']['MAX'] : $curProfile['TERMS']['MIN'].'-'.$curProfile['TERMS']['MAX'],
			);

			if(!array_key_exists('IPOLSDEK_CHOSEN',$_SESSION))
				$_SESSION['IPOLSDEK_CHOSEN'] = array();
			$_SESSION['IPOLSDEK_CHOSEN'][$profile] = sdekShipmentCollection::getProfileTarif($profile);

			if(!self::$price)
				self::$price = array();
			self::$price[$profile] = $arReturn['VALUE'];

			self::$_date = array(
				date('d.m.Y',time()+($curProfile['TERMS']['MIN'])*86400),
				date('d.m.Y',time()+($curProfile['TERMS']['MAX'])*86400)
			);
		}

		return $arReturn;
	}

	protected static function checkAviable($arOrder, $arConfig){
        if(
            !self::isLogged()
        )
            return false;

        if(
            array_key_exists('VALUE',$arConfig['ACCOUNT']) &&
            $arConfig['ACCOUNT']['VALUE']
        ){
            $acc = sqlSdekLogs::getById($arConfig['ACCOUNT']['VALUE']);
            if(!$acc || $acc['ACTIVE'] != 'Y'){
                return false;
            }
        }

        return true;
    }
	
	public static function getDeliverySender($arConfig = array())
	{
		$defaultSender = COption::GetOptionString(self::$MODULE_ID,'departure',false);
		$citySenders   = unserialize(COption::GetOptionString(self::$MODULE_ID,'addDeparture','a:{}'));
		
		if($defaultSender){
			$defaultSender = sqlSdekCity::getByBId($cityId);
		}

		if(is_array($citySenders)){
			$citySenders []= $defaultSender;
		}else{
			$citySenders = array($defaultSender);
		}

		if(
		    !empty($arConfig) &&
            array_key_exists('VALUE',$arConfig['SENDER']) &&
            $arConfig['SENDER']['VALUE'] &&
			in_array($arConfig['SENDER']['VALUE'],$citySenders)
        ){
            return $arConfig['SENDER']['VALUE'];
        } else {
			return $defaultSender;
		}
	}


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
													Запрос расчета
		== formCalcRequest ==  == calculateDost ==  == getActiveCountries ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


	// суть - проверяет тяжелогрузы и экспрессы и делает столько запросов, сколько надо
	static function formCalcRequest($profile,$account = false){
		$timeOutCheck = COption::GetOptionString(self::$MODULE_ID,'sdekDeadServer',false);
		if($timeOutCheck && (time() - $timeOutCheck) <  60 * COption::GetOptionString(self::$MODULE_ID,'timeoutRollback',15))
			$result = array('error' => GetMessage('IPOLSDEK_DEAD_SERVER'));
		else{
			$mode = false;
			if(array_key_exists('W',self::$goods) && self::$goods['W'] > 30)
				$mode = 'heavy';

			if(!self::$sdekCityCntr){
				$arCity = sqlSdekCity::getBySId(self::$sdekCity);
				self::$sdekCityCntr = ($arCity['COUNTRY']) ? $arCity['COUNTRY'] : 'rus';
			}

			self::setAuth(self::defineAuth(
                ($account) ? $account : array('COUNTRY'=>self::$sdekCityCntr)
            ));

			$result = self::calculateDost($profile,$mode);
			if(
				!is_numeric($profile) && 
				(
					$mode == 'heavy' || $result['price'] > floatval(COption::GetOptionString(self::$MODULE_ID,'cntExpress',500))
				)
			){
				$newResult = self::calculateDost($profile,"express");
				if(!array_key_exists('price',$result) || ($result['price'] > $newResult['price'] && array_key_exists('price',$newResult)))
					$result = $newResult;
			}
		}
		return $result;
	}

	// обертка класса расчета доставки
	static function calculateDost($tarif,$mode = false){
		try {
			$calc = new CalculatePriceDeliverySdek();
			$timeOut = COption::GetOptionString(self::$MODULE_ID,'dostTimeout',6);
			if(floatval($timeOut) <= 0) $timeOut = 6;
			$calc->setTimeout($timeOut);
			$calc->setAuth(self::$auth['account'],self::$auth['password']);
			$calc->setSenderCityId(self::$sdekSender);
			$calc->setReceiverCityId(self::$sdekCity);
			// $calc->setDateExecute(date()); 2012-08-20 //устанавливаем дату планируемой отправки
			//устанавливаем тариф по-умолчанию
			if(is_numeric($tarif))
				$calc->setTariffId($tarif);
			//задаём список тарифов с приоритетами
			else{
				$arPriority = self::getListOfTarifs($tarif,$mode);

                foreach(GetModuleEvents(self::$MODULE_ID,"onTarifPriority",true) as $arEvent)
                    ExecuteModuleEventEx($arEvent,Array(&$arPriority,$tarif));

				if(!count($arPriority))
					return array('error' => 'no_tarifs');
				else
					foreach($arPriority as $tarId)
						$calc->addTariffPriority($tarId);
			}

			// $calc->setModeDeliveryId(3); //устанавливаем режим доставки
			//добавляем места в отправление
			// кг, см
			if(array_key_exists('W',self::$goods)){
				$calc->addGoodsItemBySize(self::$goods['W'],self::$goods['D_W'],self::$goods['D_H'],self::$goods['D_L']);
			}else
				foreach(self::$goods as $arGood)
					$calc->addGoodsItemBySize($arGood['W'],$arGood['D_W'],$arGood['D_H'],$arGood['D_L']);
			
			$arServices = false;
			foreach(GetModuleEvents(self::$MODULE_ID, "onCalculatePriceDelivery", true) as $arEvent){
				$arServices = ExecuteModuleEventEx($arEvent,Array($tarif,$mode,array(
					'CITY_FROM' => self::$sdekSender,
					'CITY_TO'   => self::$sdekCity,
					'GOODS'     => self::$goods
				)));
			}
			if($arServices && is_array($arServices)){
				$calc->setServices($arServices);
			}

			if($calc->calculate()===true){
				COption::SetOptionString(self::$MODULE_ID,'sdekDeadServer',false);
				$res = $calc->getResult();
				if(!is_array($res))
					$arReturn['error'] = GetMessage('IPOLSDEK_DELIV_SDEKISDEAD');
				else{
					self::$lastCnt = $res['result']['price'];
					$arReturn = array(
						'success'  		  => true,
						'price'    		  => $res['result']['price'],
						'termMin'  		  => $res['result']['deliveryPeriodMin'],
						'termMax'  		  => $res['result']['deliveryPeriodMax'],
						'dateMin'  		  => $res['result']['deliveryDateMin'],
						'dateMax'  		  => $res['result']['deliveryDateMax'],
						'tarif'    		  => $res['result']['tariffId'],
						'currency' 		  => $res['result']['currency'],
						'priceByCurrency' => $res['result']['priceByCurrency']
					);
					if(array_key_exists('cashOnDelivery',$res['result']))
						$arReturn['priceLimit'] = $res['result']['cashOnDelivery'];
				}
			}elseif($calc->getResult() == 'noanswer'){
				COption::SetOptionString(self::$MODULE_ID,'sdekDeadServer',time());
				$arReturn['error'] = GetMessage('IPOLSDEK_DEAD_SERVER');
			}else{
				$err = $calc->getError();
				if(isset($err['error'])&&!empty($err))
					foreach($err['error'] as $e)
						$arReturn[$e['code']] = $e['text'];
			}
		} catch (Exception $e){
			$arReturn['error'] = $e->getMessage();
		}

		return $arReturn;
	}

	static function getActiveCountries(){
		$svdCountries = self::getCountryOptions();
		$arCountries = array();
		foreach($svdCountries as $code => $vals)
			if($vals['act'] == 'Y')
				$arCountries[]=$code;
		return $arCountries;
	}


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
													Расчеты товаров
		== setOrderGoods ==  == setShipmentGoods ==  == setGoods ==  == handleBitrixComplects ==  == getGoodsDimensions ==  == getBasketGoods ==  == sumSizeOneGoods ==  == sumSize ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


	// расчитывает товары для заказа
	public static function setOrderGoods($orderId=false){
		if (isset($orderId) && $orderId > 0)
			$arFilter = array("ORDER_ID" => $orderId);
		else
			$arFilter = array("FUSER_ID" => CSaleBasket::GetBasketUserID(),"ORDER_ID" => "NULL", "LID"=>SITE_ID);

		$goods = self::getBasketGoods($arFilter);

		self::setGoods($goods);

		return $goods;
	}

	// рассчитывает товары для отгрузки
	public static function setShipmentGoods($shipmentID,$orderId=false){
		if(!self::canShipment())
			return false;
		if(!$orderId)
			$orderId = self::oIdByShipment($shipmentID);

		$arOrderGoods = self::getBasketGoods(array("ORDER_ID" => $orderId));
		$arOrderGoods = self::filterShipmentGoods($shipmentID,$arOrderGoods);

		self::setGoods($arOrderGoods);
	}

	public static function filterShipmentGoods($shipmentID,$goods){ // фильтрует товары по наличию их в отправлении setShipmentGoods && sdekclass::getGoodsArray
		if(!self::canShipment())
			return false;
		$arGoods = array();
		$dbGoods = Bitrix\Sale\ShipmentItem::getList(array('filter'=>array('ORDER_DELIVERY_ID'=>$shipmentID)));
		while($arGood = $dbGoods->Fetch())
			$arGoods[$arGood['BASKET_ID']] = $arGood['QUANTITY'];

		$ttlPrice = 0;

		foreach($goods as $key => $vals){
			if(!array_key_exists($vals['ID'],$arGoods)){
				unset($goods[$key]);
				continue;
			}

			if($vals['QUANTITY'] == $arGoods[$vals['ID']])
				$ttlPrice += $vals['PRICE'] * $vals['QUANTITY'];
			else{
				$goods[$key]['QUANTITY'] = $arGoods[$vals['ID']];
				$ttlPrice += $goods[$key]['PRICE'] * $arGoods[$vals['ID']];
			}
		}

		return $goods;
	}

	// устанавнивает $goods по $arOrderGoods
	public static function setGoods($arOrderGoods){
		self::$goods = false;
		$arGoods = array();
		$arDefSetups = array(
			'W'   => COption::GetOptionString(self::$MODULE_ID,"weightD",1000) / 1000,
			'D_L' => COption::GetOptionString(self::$MODULE_ID,"lengthD",400 ) / 10,
			'D_W' => COption::GetOptionString(self::$MODULE_ID,"widthD" ,300 ) / 10,
			'D_H' => COption::GetOptionString(self::$MODULE_ID,"heightD",200 ) / 10,
		);
		$isDef = ("O"==COption::GetOptionString(self::$MODULE_ID,"defMode","O"));
		$arOrderGoods = self::handleBitrixComplects($arOrderGoods);

		if(!self::$orderPrice){
			self::$orderPrice = 0;
			foreach($arOrderGoods as $arGood)
				self::$orderPrice += $arGood['QUANTITY'] * $arGood['PRICE'] ;
		}

		$arGoods = self::getGoodsDimensions($arOrderGoods,$arDefSetups,$isDef);

		$TW = 0;
		foreach($arGoods['goods'] as $good){
			if(!$arGoods['isNoG'] || ($good['D_L'] && $good['D_W'] && $good['D_H']))
				$yp[]=self::sumSizeOneGoods($good['D_L'],$good['D_W'],$good['D_H'],$good['Q']);
			$TW += $good['W'] * $good['Q'];
		}

		$result = self::sumSize($yp);

		if($arGoods['isNoG']){
			$vDef = $arDefSetups['D_L'] * $arDefSetups['D_W'] * $arDefSetups['D_H'];
			$vCur = $result['L'] * $result['W'] * $result['H'];
			if($vCur < $vDef)
				$result = array(
					"L" => $arDefSetups['D_L'],
					"W" => $arDefSetups['D_W'],
					"H" => $arDefSetups['D_H']
				);
		}
		if($arGoods['isNoW'])
			$TW = ($TW > $arDefSetups['W']) ? $TW : $arDefSetups['W'];

		// СДЭК не воспринимает габариты меньше сантиметра
		foreach(array('L','W','H') as $lbl)
			if($result[$lbl] < 1)
				$result[$lbl] = 1;

		// перераспределение LWH в магии sumSize
		self::$goods = array(
			"D_L" => $result['L'],
			"D_W"  => $result['W'],
			"D_H" => $result['H'],
			"W" => $TW
		);
		if(!self::$orderWeight)
			self::$orderWeight=$TW*1000;
	}

	// режет товары из комплектов
	static function handleBitrixComplects($goods){
		$arComplects = array();
		foreach($goods as $good)
			if(
				array_key_exists('SET_PARENT_ID',$good) &&
				$good['SET_PARENT_ID'] &&
				$good['SET_PARENT_ID'] != $good['ID']
			)
				$arComplects[$good['SET_PARENT_ID']]=true;
		if(defined("IPOLSDEK_DOWNCOMPLECTS") && IPOLSDEK_DOWNCOMPLECTS == true){
			foreach($goods as $key => $good)
				if(array_key_exists($good['ID'],$arComplects))
					unset($goods[$key]);
		}else
			foreach($goods as $key => $good)
				if(
					array_key_exists('SET_PARENT_ID',$good) &&
					array_key_exists($good['SET_PARENT_ID'],$arComplects) &&
					$good['SET_PARENT_ID'] != $good['ID']
				)
					unset($goods[$key]);
		return $goods;
	}

	// засовывает в товары габариты по установленным дефолтам
	public static function getGoodsDimensions($arOrderGoods,$arDefSetups=false,$isDef='ungiven'){
		if(!$arDefSetups)
			$arDefSetups = array(
				'W'   => COption::GetOptionString(self::$MODULE_ID,"weightD",1000) / 1000,
				'D_L' => COption::GetOptionString(self::$MODULE_ID,"lengthD",400 ) / 10,
				'D_W' => COption::GetOptionString(self::$MODULE_ID,"widthD" ,300 ) / 10,
				'D_H' => COption::GetOptionString(self::$MODULE_ID,"heightD",200 ) / 10,
			);
		if($isDef == 'ungiven')
			$isDef = ("O"==COption::GetOptionString(self::$MODULE_ID,"defMode","O"));

		$arGoods = array();
		$isNoW = false;
		$isNoG = false;

		foreach(GetModuleEvents(self::$MODULE_ID,"onBeforeDimensionsCount",true) as $arEvent)
			ExecuteModuleEventEx($arEvent,Array(&$arOrderGoods));

		foreach($arOrderGoods as $key => $arGood){
			$gabs = array_key_exists('~DIMENSIONS',$arGood)?$arGood['~DIMENSIONS']:$arGood['DIMENSIONS'];
			if(!is_array($gabs) && $gabs)
				$gabs = unserialize($gabs);

			$gWeight = (float)$arGood['WEIGHT'];

			if($isDef && !$isNoW && !$gWeight)
				$isNoW = true;
			if($isDef && !$isNoG && (!$gabs['LENGTH'] || !$gabs['WIDTH'] || !$gabs['HEIGHT']))
				$isNoG = true;
			$arGoods[$key]=array(
				'W'   => ($gWeight)        ? ($gWeight/1000)      : ((!$isDef) ? $arDefSetups['W']   : false),
				'D_L' => ($gabs['LENGTH']) ? ($gabs['LENGTH']/10) : ((!$isDef) ? $arDefSetups['D_L'] : false),
				'D_W' => ($gabs['WIDTH'])  ? ($gabs['WIDTH']/10)  : ((!$isDef) ? $arDefSetups['D_W'] : false),
				'D_H' => ($gabs['HEIGHT']) ? ($gabs['HEIGHT']/10) : ((!$isDef) ? $arDefSetups['D_H'] : false),
				'Q'   => $arGood['QUANTITY'],
			);
		}

		return array(
			'goods' => $arGoods,
			'isNoW' => $isNoW,
			'isNoG' => $isNoG
		);
	}

	// берет товары из корзин по фильтру arFilter, считает общую цену | setOrderGoods, packController
	static function getBasketGoods($arFilter=array()){
		$arGoods = array();

		$dbBasketItems = CSaleBasket::GetList(
			array(),
			$arFilter,
			false,
			false,
			array("ID","PRODUCT_ID", "PRICE", "QUANTITY",'CAN_BUY','DELAY',"NAME","DIMENSIONS","WEIGHT","PRICE","SET_PARENT_ID","LID","CURRENCY","VAT_RATE")
		);
		while ($arItems = $dbBasketItems->Fetch())
			if ($arItems['CAN_BUY'] == 'Y' && $arItems['DELAY'] == 'N'){
				$arItems['DIMENSIONS'] = unserialize($arItems['DIMENSIONS']);
				// $arItems['NAME'] = str_replace('"',"'",$arItems['NAME']);
				$arGoods[]=$arItems;
				$ttlPrice+=$arItems['PRICE']*$arItems['QUANTITY'];
			}

		return $arGoods;
	}

	// отсортировать грузы по возрастанию
	static function sumSizeOneGoods($xi,$yi,$zi,$qty){
		$ar = array($xi,$yi,$zi);
		sort($ar);
		if ($qty<=1) return (array('X'=>$ar[0],'Y'=>$ar[1],'Z'=>$ar[2]));

		$x1 = 0;
		$y1 = 0;
		$z1 = 0;
		$l = 0;

		$max1 = floor(Sqrt($qty));
		for($y=1;$y<=$max1;$y++){
			$i = ceil($qty/$y);
			$max2 = floor(Sqrt($i));
			for($z=1;$z<=$max2;$z++){
				$x = ceil($i/$z);
				$l2 = $x*$ar[0] + $y*$ar[1] + $z*$ar[2];
				if(($l==0)||($l2<$l)){
					$l = $l2;
					$x1 = $x;
					$y1 = $y;
					$z1 = $z;
				}
			}
		}
		return (array('X'=>$x1*$ar[0],'Y'=>$y1*$ar[1],'Z'=>$z1*$ar[2]));
	}

	//Суммируем размеры груза для вычисления объемного веса
	static function sumSize($a){
		$n = count($a);
		if (!($n>0)) return(array('L'=>'0','W'=>'0','H'=>'0'));
		for($i3=1;$i3<$n;$i3++){
			// отсортировать размеры по убыванию
			for($i2=$i3-1;$i2<$n;$i2++){
				for($i=0;$i<=1;$i++){
					if($a[$i2]['X']<$a[$i2]['Y']){
						$a1 = $a[$i2]['X'];
						$a[$i2]['X'] = $a[$i2]['Y'];
						$a[$i2]['Y'] = $a1;
					};
					if(($i==0) && ($a[$i2]['Y']<$a[$i2]['Z'])){
						$a1 = $a[$i2]['Y'];
						$a[$i2]['Y'] = $a[$i2]['Z'];
						$a[$i2]['Z'] = $a1;
					}
				}
				$a[$i2]['Sum'] = $a[$i2]['X'] + $a[$i2]['Y'] + $a[$i2]['Z']; // сумма сторон
			}
			// отсортировать грузы по возрастанию
			for($i2=$i3;$i2<$n;$i2++)
				for($i=$i3;$i<$n;$i++)
					if($a[$i-1]['Sum']>$a[$i]['Sum']){
						$a2 = $a[$i];
						$a[$i] = $a[$i-1];
						$a[$i-1] = $a2;
					}
			// расчитать сумму габаритов двух самых маленьких грузов
			if($a[$i3-1]['X']>$a[$i3]['X']) $a[$i3]['X'] = $a[$i3-1]['X'];
			if($a[$i3-1]['Y']>$a[$i3]['Y']) $a[$i3]['Y'] = $a[$i3-1]['Y'];
			$a[$i3]['Z'] = $a[$i3]['Z'] + $a[$i3-1]['Z'];
			$a[$i3]['Sum'] = $a[$i3]['X'] + $a[$i3]['Y'] + $a[$i3]['Z']; // сумма сторон
		}

		return( array(
			'L'=>Round($a[$n-1]['X'],2),
			'W'=>Round($a[$n-1]['Y'],2),
			'H'=>Round($a[$n-1]['Z'],2))
		);
	}


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
													Виджет
		== pickupLoader ==  == loadComponent ==  == onBufferContent ==  == no_json ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


	static $city = '';
	static $cityId = 0; // bitrix
	static $selDeliv = '';

	static function pickupLoader($arResult,$arUR){//подготавливает данные о доставке
		if(!self::isActive()) return;

		self::$orderWeight = $arResult['ORDER_WEIGHT'];
		self::$orderPrice  = $arResult['ORDER_PRICE'];

		$city = self::getCity($arUR['DELIVERY_LOCATION'],true);
		self::$cityId = $arUR['DELIVERY_LOCATION'];
		if($city){
			$city = str_replace(GetMessage('IPOLSDEK_LANG_YO_S'),GetMessage('IPOLSDEK_LANG_YE_S'),$city['NAME']);
			self::$city = $city;
		}
		self::$selDeliv = $arUR['DELIVERY_ID'];
	}

	static function loadComponent($arParams = array()){ // подключает компонент
		if(!is_array($arParams))
			$arParams = array();
		if(self::isActive() && $_REQUEST['is_ajax_post'] != 'Y' && $_REQUEST["AJAX_CALL"] != 'Y' && !$_REQUEST["ORDER_AJAX"]){
			if(COption::GetOptionString(self::$MODULE_ID,'noYmaps','N') == 'Y' || defined('BX_YMAP_SCRIPT_LOADED') || defined('IPOL_YMAPS_LOADED'))
				$arParams['NOMAPS'] = 'Y';
			elseif(!array_key_exists('NOMAPS',$arParams) || $arParams['NOMAPS'] != 'Y')
				define('IPOL_YMAPS_LOADED',true);
			$GLOBALS['APPLICATION']->IncludeComponent("ipol:ipol.sdekPickup", "order", $arParams,false);
		}
	}

	public static function onBufferContent(&$content) {
		if(self::$city && self::isActive()){
			$noJson = self::no_json($content);
			if(($_REQUEST['is_ajax_post'] == 'Y' || $_REQUEST["AJAX_CALL"] == 'Y' || $_REQUEST["ORDER_AJAX"]) && $noJson){
				$content .= '<input type="hidden" id="sdek_city" name="sdek_city" value=\''.self::$city.'\' />';//вписываем город
				$content .= '<input type="hidden" id="sdek_cityID" name="sdek_cityID" value=\''.self::$cityId.'\' />';//вписываем город
				$content .= '<input type="hidden" id="sdek_dostav" name="sdek_dostav" value=\''.self::$selDeliv.'\' />';//вписываем выбранный вариант доставки
				$content .= '<input type="hidden" id="sdek_payer" name="sdek_payer" value=\''.self::$payerType.'\' />';//вписываем плательщика
				$content .= '<input type="hidden" id="sdek_paysystem" name="sdek_paysystem" value=\''.self::$paysystem.'\' />';//вписываем платежную систему
			}elseif(($_REQUEST['soa-action'] == 'refreshOrderAjax' || $_REQUEST['action'] == 'refreshOrderAjax') && !$noJson)
				$content = substr($content,0,strlen($content)-1).',"sdek":{"city":"'.self::zajsonit(self::$city).'","cityId":"'.self::$cityId.'","dostav":"'.self::$selDeliv.'","payer":"'.self::$payerType.'","paysystem":"'.self::$paysystem.'"}}';
		}
	}

	static function no_json($wat){
		return is_null(json_decode(self::zajsonit($wat),true));
	}

	static function onAjaxAnswer(&$result){
		if(
			self::$city && 
			self::isActive() &&
			!array_key_exists('REDIRECT_URL',$result['order']) // $why = $because
		)
			$result['sdek'] = array(
				'city'   => self::$city,
				'cityId' => self::$cityId,
				'dostav' => self::$selDeliv
			);
	}


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
													Проверки ПВЗ и Почтоматов
		== wegihtPVZ ==  == checkPVZ ==  == checkPOSTOMAT ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


	public static function wegihtPVZ($weight=false,$src=false){
		if($src)
			self::$PVZcities = $src;
		if(!self::$PVZcities){
			self::$PVZcities = self::getListFile();
			self::$PVZcities = self::$PVZcities['PVZ'];
		}
		$arPVZs = self::$PVZcities;
		if(!self::$orderWeight && !$weight)
			return self::$PVZcities;
		if($weight)
		    $check = $weight;
		elseif(COption::GetOptionString(self::$MODULE_ID,'mindVWeight','Y') === 'Y' && self::$goods){
		    $vWeight = self::getVolumeWeight(self::$goods['D_L']*10,self::$goods['D_W']*10,self::$goods['D_H']*10) * 1000;
            $check   = max(self::$orderWeight,$vWeight);
		}else{
            $check = self::$orderWeight;
        }
		$check /= 1000;

		if(count($arPVZs))
			foreach($arPVZs as $city => $arPVZ)
				foreach($arPVZ as $code => $val)
					if(array_key_exists('WeightLim',$val)){
						if(
							$val['WeightLim']['MIN'] > $check ||
							$val['WeightLim']['MAX'] < $check
						)
							unset($arPVZs[$city][$code]);
					}
		return $arPVZs;
	}

	public static function checkPVZ($city = ''){
		if(!self::$PVZcities){
			self::$PVZcities = self::getListFile();
			self::$PVZcities = self::$PVZcities['PVZ'];
		}
		if(!$city)
			$city = self::$city;
		elseif(!self::$city)
			self::$city = $city;
		return array_key_exists(self::$city,self::$PVZcities);
	}

	public static function checkPOSTOMAT($city = ''){
		return false;
	}


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
												проверки на возможность оплаты нал / безнал
		== checkNalD2P ==  == checkNalP2D ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


	static function checkNalD2P(&$arResult,&$arUserResult,$arParams){
		if(
			$arParams['DELIVERY_TO_PAYSYSTEM'] == 'd2p' &&
			self::defineDelivery($arUserResult['DELIVERY_ID'])
		){
			$hNal = (COption::GetOptionString(self::$MODULE_ID,"hideNal","Y") == 'Y');
			$hNOC = (COption::GetOptionString(self::$MODULE_ID,"hideNOC","Y") == 'Y');
			if($hNal || $hNOC){
				$arBesnalPaySys = unserialize(COption::GetOptionString(self::$MODULE_ID,'paySystems','a:{}'));
				$MP = self::getSQLCityBI($arUserResult['DELIVERY_LOCATION']);
				$isAvail = true;
				if($hNal)
					$isAvail = self::defilePayNal($MP['PAYNAL'],$arResult['ORDER_PRICE']);
				if($isAvail && $hNOC)
					$isAvail = self::definePayCountry($MP['COUNTRY']);
				if(!$isAvail){
					foreach($arResult['PAY_SYSTEM'] as $id => $payDescr)
						if(!in_array($payDescr['ID'],$arBesnalPaySys)){
							if($payDescr['ID'] == $arUserResult['PAY_SYSTEM_ID'])
								$arUserResult['PAY_SYSTEM_ID'] = false;
							unset($arResult['PAY_SYSTEM'][$id]);
						}
					sort($arResult['PAY_SYSTEM']);
				}
			}
		}
		if($arParams['DELIVERY_TO_PAYSYSTEM'] == 'd2p' && $arUserResult['PAY_SYSTEM_ID']){
			self::$paysystem = $arUserResult['PAY_SYSTEM_ID'];
		}
	}

	static function checkNalP2D(&$arResult,$arUserResult,$arParams){
		if($arParams['DELIVERY_TO_PAYSYSTEM'] == 'p2d'){
			$hNal = (COption::GetOptionString(self::$MODULE_ID,"hideNal","Y") == 'Y');
			$hNOC = (COption::GetOptionString(self::$MODULE_ID,"hideNOC","Y") == 'Y');
			if($hNal || $hNOC){
				$arBesnalPaySys = unserialize(COption::GetOptionString(self::$MODULE_ID,'paySystems','a:{}'));
				$MP = self::getSQLCityBI($arUserResult['DELIVERY_LOCATION']);
				$isAvail = true;
				if($hNal)
					$isAvail = self::defilePayNal($MP['PAYNAL'],$arResult['ORDER_PRICE']);
				if($isAvail && $hNOC)
					$isAvail = self::definePayCountry($MP['COUNTRY']);
				if(
					!$isAvail &&
					!in_array($arUserResult['PAY_SYSTEM_ID'],$arBesnalPaySys)
				){
					if(self::isConverted()){
						foreach($arResult['DELIVERY'] as $delId => $someVals)
							if(self::defineDelivery($delId))
								unset($arResult['DELIVERY'][$delId]);
					}elseif(array_key_exists('sdek',$arResult['DELIVERY']))
						unset($arResult['DELIVERY']['sdek']);
				}
			}
			
			self::$paysystem = $arUserResult['PAY_SYSTEM_ID'];
		}
	}

	static function defilePayNal($payNal,$orderPrice=0){
		if($payNal == '')
			$res = true;
		else{
			switch($payNal){
				case 'no limit': $res = true; break;
				case '0.00': $res = false; break;
				default: $res = ($res['PAYNAL'] <= $orderPrice); break;
			}
		}
		return $res;
	}

	static function definePayCountry($country){
		return ($country == '' || $country == 'rus');
	}


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
												блокировка оформления заказа без ПВЗ
		== noPVZOldTemplate ==  == noPVZNewTemplate ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


	static function noPVZOldTemplate(&$arResult,&$arUserResult){
		if(
			$arUserResult['CONFIRM_ORDER'] == 'Y' && 
			COption::GetOptionString(self::$MODULE_ID,'noPVZnoOrder','N') == 'Y' &&
			self::defineDelivery($arUserResult['DELIVERY_ID']) == 'pickup' &&
			self::isActive()
		){
			if($propAddr = COption::GetOptionString(self::$MODULE_ID,'pvzPicker','')){
				$checked = 1;
				$props = CSaleOrderProps::GetList(array(),array('CODE' => $propAddr));
				while($prop=$props->Fetch()){
					if(array_key_exists($prop['ID'],$arUserResult['ORDER_PROP'])){
						if(strpos($arUserResult['ORDER_PROP'][$prop['ID']],'#S') === false && $checked != 2)
							$checked = 0;
						else
							$checked = 2;
					}
				}
				if($checked === 0)
				{
					$arResult['ERROR'] []= GetMessage('IPOLSDEK_DELIV_ERR_NOPVZ');
				}
			}
		}
	}
	
	static function noPVZNewTemplate($entity,$values){
		if(
            (!defined('ADMIN_SECTION') || ADMIN_SECTION === false) &&
            self::isActive() &&
			COption::GetOptionString(self::$MODULE_ID,'noPVZnoOrder','N') == 'Y' &&
			cmodule::includeModule('sale')
        ) {
			if($propAddr = COption::GetOptionString(self::$MODULE_ID,'pvzPicker','')){
				$props = CSaleOrderProps::GetList(array(),array('CODE' => $propAddr));
				$arPVZPropsIds = array();
				while($element=$props->Fetch()){
					$arPVZPropsIds []= $element['ID'];
				}
				if(!empty($arPVZPropsIds)){
					$orderProps = $entity->getPropertyCollection()->getArray();
					$checked = 1;
					foreach($orderProps['properties'] as $propVals){
						if(in_array($propVals['ID'],$arPVZPropsIds)){
							if(strpos($propVals['VALUE'][0],'#S') === false && $checked != 2)
								$checked = 0;
							else
								$checked = 2;
						}
					}
					if($checked == 0){
						$shipmentCollection = $entity->getShipmentCollection();
						foreach ($shipmentCollection as $something => $shipment) {
							if ($shipment->isSystem())
								continue;

							$delivery = self::defineDelivery($shipment->getField('DELIVERY_ID'));
							if ($delivery === 'pickup') {
								return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::ERROR, new \Bitrix\Sale\ResultError(GetMessage('IPOLSDEK_DELIV_ERR_NOPVZ'), 'code'), 'sale');
							}
						}
					}
				}
            }
		}
	}


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
												расчет стороннего заказа
		== setOrder ==  == countDelivery ==  == cntDelivsOld ==  == cntDelivsConverted ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


	static function setOrder($params=array()){ // устанавливает данные для заказа-пустышки
		self::$orderWeight = ($params['WEIGHT']) ? $params['WEIGHT'] : COption::GetOptionString(self::$MODULE_ID,'weightD',1000);
		self::$orderPrice  = ($params['PRICE'])  ? $params['PRICE']  : 1000;
		if($params['CITY_TO'])
			self::$sdekCity = self::getCity($params['CITY_TO']);

		if(!$params['GOODS'])
			$params['GOODS'] = array(array("WEIGHT"=>self::$orderWeight,'QUANTITY'=>1));
		if(!$params['GABS'])
			self::setGoods($params['GOODS']);
		else
			self::$goods = $params['GABS'];
	}

	static function countDelivery($arOrder){
		cmodule::includeModule('sale');

		// CITY_TO
		if(!$arOrder['CITY_TO_ID']){
			$cityTo = CSaleLocation::getList(array(),array('CITY_NAME'=>self::zaDEjsonit($arOrder['CITY_TO'])));
			$arPriority = array(array(),array());
			while($city=$cityTo->Fetch()){
				$rezArr = array_intersect(
					array($city['COUNTRY_NAME_ORIG'],$city['COUNTRY_NAME'],$city['COUNTRY_NAME_LANG']),
					array('Russia','Russian Federation',GetMessage('IPOLSDEK_SYNCTY_rus'),GetMessage('IPOLSDEK_SYNCTY_rus2'))
				);
				if(!empty($rezArr))
					$arPriority[0] []= $city;
				else
					$arPriority[1] []= $city;
			}

			if(!empty($arPriority[0]))
				$cityTo = $arPriority[0][0];
			elseif(!empty($arPriority[1]))
				$cityTo = $arPriority[1][0];
			else
				$cityTo = false;
			
			if($cityTo){
				$_SESSION['IPOLSDEK_city'] = self::zaDEjsonit($arOrder['CITY_TO']);
				$arOrder['CITY_TO_ID'] = $cityTo['ID'];
			}
		}

		if(!self::$sdekCityCntr && $arOrder['CITY_TO_ID']){
			$arCity = self::getCity($arOrder['CITY_TO_ID'],true);
			self::$sdekCityCntr  = ($arCity['COUNTRY']) ? $arCity['COUNTRY'] : 'rus';
		}elseif($arOrder['CITY_TO_ID']){
			self::$sdekCityCntr = 'rus';
		}
		if(!self::$sdekCityCntr){
			self::$sdekCityCntr = 'rus';
		}
			
		// GABS
		if($arOrder["DIMS"]) {
            $arOrder['GOODS'] = array(
                array(
                    "QUANTITY" => 1,
                    "PRICE" => ($arOrder['PRICE']) ? $arOrder['PRICE'] : self::$orderPrice,
                    "WEIGHT" => ($arOrder['WEIGHT']) ? $arOrder['WEIGHT'] : self::$orderWeight,
                    "DIMENSIONS" => array(
                        "WIDTH" => $arOrder["DIMS"]["WIDTH"],
                        "HEIGHT" => $arOrder["DIMS"]["HEIGHT"],
                        "LENGTH" => $arOrder["DIMS"]["LENGTH"],
                    ),
                )
            );
        }

        // ACCOUNT & CITY_FROM
        self::$account       = (array_key_exists('SDEK_ACCOUNT',$arOrder)   && $arOrder['SDEK_ACCOUNT'])   ? $arOrder['SDEK_ACCOUNT']   : false;
        self::$extSdekSender = (array_key_exists('SDEK_CITY_FROM',$arOrder) && $arOrder['SDEK_CITY_FROM']) ? $arOrder['SDEK_CITY_FROM'] : false;

		$arProfiles = (self::isConverted()) ? self::cntDelivsConverted($arOrder) : self::cntDelivsOld($arOrder);

		$currency = self::getCountryOptions();
		$currency = (
			array_key_exists(self::$sdekCityCntr,$currency) && 
			array_key_exists('cur',$currency[self::$sdekCityCntr]) && 
			$currency[self::$sdekCityCntr]['cur']
		) ? $currency[self::$sdekCityCntr]['cur'] : false;

		foreach($arProfiles as $profileName => $profCost){
			if($profCost){
				if($currency)
					$arProfiles[$profileName] = floatval(sdekExport::formatCurrency(array('TO'=>$currency,'SUM'=>$arProfiles[$profileName])));
				else
					$currency = 'RUB';
				$arProfiles[$profileName] = CCurrencyLang::CurrencyFormat($arProfiles[$profileName],$currency,true);
			}else
				$arProfiles[$profileName] = GetMessage("IPOLSDEK_FREEDELIV");
		}

		if(self::$preSet){
			$result = array_pop($arProfiles);
			$sourse = sdekShipmentCollection::getProfile(self::$preSet);
			if(!$sourse){
				$arReturn = array('success' => false, 'error' => GetMessage('IPOLSDEK_DELIV_ERR_NOTRF'));
			}elseif($sourse['RESULT'] == 'ERROR')
				$arReturn = array('success' => false, 'error' => $sourse['TEXT']);
			else
				$arReturn = array('success' => true, 'price' => $result, 'termMin' => $sourse['TERMS']['MIN'], 'termMax' => $sourse['TERMS']['MAX']);
		}else
			$arReturn = array(
					'courier' => (array_key_exists('courier',$arProfiles)) ? $arProfiles['courier'] : 'no',
					'pickup'  => (array_key_exists('pickup',$arProfiles))  ? $arProfiles['pickup']  : 'no',
					'inpost'  => 'no',
					'date'    => self::$date,
					'c_date'  => self::$profiles['courier']['TRANSIT'],
					'p_date'  => self::$profiles['pickup']['TRANSIT'],
					'i_date'  => false,
				);

		if($arOrder['isdek_action'] || $arOrder['action'])
			echo json_encode(self::zajsonit($arReturn));
		else
			return $arReturn;
	}

	static function cntDelivsOld($arOrder){//Выдает срок и стоимость доставки для виджета
		$cityFrom = COption::getOptionString(self::$MODULE_ID,'departure');

		if(!self::$preSet)
			self::setOrder($arOrder);
		$list = self::getListFile();

		$psevdoOrder = array(
			"LOCATION_TO"   => $arOrder['CITY_TO_ID'],
			"LOCATION_FROM" => $cityFrom,
			"PRICE"         => ($arOrder['PRICE'])  ? $arOrder['PRICE']  : self::$orderPrice,
			"WEIGHT"        => ($arOrder['WEIGHT']) ? $arOrder['WEIGHT'] : self::$orderWeight,
		);
		if(array_key_exists("GOODS",$arOrder) && is_array($arOrder['GOODS']) && count($arOrder['GOODS']))
			$psevdoOrder['ITEMS']=$arOrder['GOODS'];

		$arHandler   = CSaleDeliveryHandler::GetBySID('sdek')->Fetch();
		$arProfiles  = CSaleDeliveryHandler::GetHandlerCompability($psevdoOrder,$arHandler);
		$arShipments = array();

		foreach($arProfiles as $profName => $someArray){
			if(is_array($arOrder['FORBIDDEN']) && in_array($profName,$arOrder['FORBIDDEN'])) continue;
			$calc = CSaleDeliveryHandler::CalculateFull('sdek',$profName,$psevdoOrder,"RUB");
			if($calc['RESULT'] != 'ERROR')
				$arShipments[$profName] = $calc['VALUE'];
		}

		return $arShipments;
	}

	static function cntDelivsConverted($arOrder){
		$basket = Bitrix\Sale\Basket::create(SITE_ID);
		if(array_key_exists('GOODS',$arOrder) && is_array($arOrder['GOODS']) && count($arOrder['GOODS']))
			foreach($arOrder['GOODS'] as $key => $arGood){
				$basketItem = Bitrix\Sale\BasketItem::create($basket,self::$MODULE_ID,$key+1);
				$arGood['DIMENSIONS'] = ($arGood['DIMENSIONS']) ? serialize($arGood['DIMENSIONS']) : 'a:3:{s:5:"WIDTH";i:0;s:6:"HEIGHT";i:0;s:6:"LENGTH";i:0;}';
				$basketItem->initFields(
					array_merge(
						$arGood,
						array('DELAY'=>'N','CAN_BUY'=>'Y','CURRENCY'=>'RUB','RESERVED'=>'N','NAME'=>'testGood','SUBSCRIBE'=>'N')
					)
				);
				$basket->addItem($basketItem);
			}

		$order = Bitrix\Sale\Order::create(SITE_ID);
		$order->setBasket($basket);
		$propertyCollection = $order->getPropertyCollection();
		$locVal = CSaleLocation::getLocationCODEbyID($arOrder['CITY_TO_ID']);
		$arProps = array();
		foreach ($propertyCollection as $property){
			$arProperty = $property->getProperty();
			if($arProperty["TYPE"] == 'LOCATION')
				$arProps[$arProperty["ID"]] = $locVal;
		}
		$propertyCollection->setValuesFromPost(array('PROPERTIES'=>$arProps),array());
		
		if($arOrder['PERSON_TYPE_ID']){
			$order->setField('PERSON_TYPE_ID',$arOrder['PERSON_TYPE_ID']);
			if(!self::$payerType)
				self::$payerType = $arOrder['PERSON_TYPE_ID'];
		}

		$shipmentCollection = $order->getShipmentCollection();
		$shipment = $shipmentCollection->createItem();
		$shipmentItemCollection = $shipment->getShipmentItemCollection();
		$shipment->setField('CURRENCY', $order->getCurrency());
		foreach ($order->getBasket() as $item){
			$shipmentItem = $shipmentItemCollection->createItem($item);
			$shipmentItem->setQuantity($item->getQuantity());
		}

		if($arOrder['PAY_SYSTEM_ID']){
			$paymentCollection = $order->getPaymentCollection();
			$payment = $paymentCollection->createItem();
			$psService = \Bitrix\Sale\PaySystem\Manager::getObjectById($arOrder['PAY_SYSTEM_ID']);
			$paymentFields = array(
				'PAY_SYSTEM_ID' => $arOrder['PAY_SYSTEM_ID'],
				'COMPANY_ID' => 0,
				'PAY_VOUCHER_NUM' => '',
				'PAY_RETURN_NUM' => '',
				'PAY_RETURN_COMMENT' => '',
				'COMMENTS' => '',
				'PAY_SYSTEM_NAME' => ($psService) ? $psService->getField('NAME') : ''
			);
			$payment->setFields($paymentFields);
			$payment->setField('SUM', $order->getPrice());
			
			if(!self::$paysystem){
				self::$paysystem = $arOrder['PAY_SYSTEM_ID'];
			}
		}

		$arShipments = array();
		$arDeliveryServiceAll = Bitrix\Sale\Delivery\Services\Manager::getRestrictedObjectsList($shipment);
		
		if(array_key_exists('DELIVERY',$arOrder) && $arOrder['DELIVERY'] && !self::defineDelivery($arOrder['DELIVERY']))
			$arOrder['DELIVERY'] = false;

		foreach($arDeliveryServiceAll as $id => $deliveryObj){
			if(
				$deliveryObj->isProfile() &&
				method_exists($deliveryObj->getParentService(),'getSid') &&
				$deliveryObj->getParentService()->getSid() == 'sdek'
			){
				$profName = self::defineDelivery($id);
				if(array_key_exists('DELIVERY',$arOrder) && $arOrder['DELIVERY'] && $arOrder['DELIVERY'] != $id) continue;
				if(is_array($arOrder['FORBIDDEN']) && in_array($profName,$arOrder['FORBIDDEN'])) continue;
				$resCalc = Bitrix\Sale\Delivery\Services\Manager::calculateDeliveryPrice($shipment,$id);
				if($resCalc->isSuccess())
					$arShipments[$profName] = $resCalc->getDeliveryPrice();
			}
		}

		return $arShipments;
	}


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
													Авторизации
		== setAuth ==  == setAuthById ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/

	static function setAuth($account,$password=false){
		if(is_array($account)){
			$password = $account['SECURE'];
			$account  = $account['ACCOUNT'];
		}

		self::$auth = array(
			'account'  => $account,
			'password' => $password
		);
	}

	static function setAuthById($id){
		$log = sqlSdekLogs::getById($id);
		if($log)
			self::setAuth($log['ACCOUNT'],$log['SECURE']);
		return ($log);
	}

	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
													Общие функции модуля
		== getListOfTarifs ==  == getDateDeliv ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/

	static function getListOfTarifs($profile,$mode = false,$fSkipCheckBlocks = false){
		return self::getTarifList(array('type'=>$profile,'mode'=>$mode,'answer'=>'array','fSkipCheckBlocks' => $fSkipCheckBlocks));
	}

	static function getDateDeliv($format = false){
		if(!self::$_date) return;
		if(self::$_date[0] == self::$_date[1]) $format = 0;
		return ($format === false) ? self::$_date[0]." - ".self::$_date[1] : self::$_date[$format];
	}

	static function getSenderById($id = 0){
		$op = unserialize(COption::GetOptionString(self::$MODULE_ID,'addDeparture','a:0:{}'));
		return (array_key_exists($id,$op)) ? $op[$id] : self::getHomeCity();
	}

	// legacy
	public static function forceSetGoods($orderId){
		self::setOrderGoods($orderId);
	}
}
?>