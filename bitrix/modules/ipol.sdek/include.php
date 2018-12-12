<?
IncludeModuleLangFile(__FILE__);
// IPOLSDEK_LOG - ����� ����

/*
	onPVZListReady
*/

class sdekHelper{
    static $MODULE_ID = "ipol.sdek";

    public static function getAjaxAction($action,$subaction){
        if(method_exists('sdekHelper',$action))
            sdekHelper::$action($_POST);
        elseif(method_exists('sdekdriver',$action))
            sdekdriver::$action($_POST);
        elseif(method_exists('CDeliverySDEK',$action))
            CDeliverySDEK::$action($_POST);
        elseif(method_exists('sdekExport',$action))
            sdekExport::$action($_POST);
        elseif(method_exists('sdekOption',$action))
            sdekOption::$action($_POST);
        else{
            if(method_exists('sdekHelper',$subaction))
                sdekHelper::$subaction($_POST);
            elseif(method_exists('CDeliverySDEK',$subaction))
                CDeliverySDEK::$subaction($_POST);
        }
    }

    /*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
                                                        LOGS & ERRORS
            == toLog ==  == errorLog ==  == getErrors ==  == toAnswer ==  == getAnswer ==
    ()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


    // ������� ���
    static function toLog($wat,$sign='',$noAction=false){
        if($noAction && ($_REQUEST['isdek_action']=='countDelivery' || $_REQUEST['action']=='countDelivery')) return;
        if($sign) $sign.=" ";
        if(!$GLOBALS['isdek_logfile']){
            $GLOBALS['isdek_logfile'] = fopen($_SERVER['DOCUMENT_ROOT'].'/SDEKLog.txt','w');
            fwrite($GLOBALS['isdek_logfile'],"\n\n".date('H:i:s d.m')."\n");
        }
        fwrite($GLOBALS['isdek_logfile'],$sign.print_r($wat,true)."\n");
    }
    // ��� ������
    static $ERROR_REF = '';
    static function errorLog($error){
        if(!COption::GetOptionString(self::$MODULE_ID,'logged',false))
            return;
        self::$ERROR_REF .= $error."\n";
        $file=fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".self::$MODULE_ID."/errorLog.txt","a");
        fwrite($file,"\n".date("d.m.Y H:i:s")." ".self::zaDEjsonit($error));
        fclose($file);
    }
    static function getErrors(){
        return self::$ERROR_REF;
    }
    // ��� �������
    static $ANSWER_REF;
    static function toAnswer($wat,$sign=''){
        if($sign) $sign.=" ";
        if(self::$ANSWER_REF) self::$ANSWER_REF.="\n";
        self::$ANSWER_REF.=$sign.print_r($wat,true);
    }
    static function getAnswer(){
        return self::$ANSWER_REF;
    }


    /*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
                                                        ENCODING
            == zajsonit ==  == zaDEjsonit ==  == toUpper ==
    ()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


    static function zajsonit($handle){
        if(LANG_CHARSET !== 'UTF-8'){
            if(is_array($handle))
                foreach($handle as $key => $val){
                    unset($handle[$key]);
                    $key=self::zajsonit($key);
                    $handle[$key]=self::zajsonit($val);
                }
            else
                $handle=$GLOBALS['APPLICATION']->ConvertCharset($handle,LANG_CHARSET,'UTF-8');
        }
        return $handle;
    }
    static function zaDEjsonit($handle){
        if(LANG_CHARSET !== 'UTF-8'){
            if(is_array($handle))
                foreach($handle as $key => $val){
                    unset($handle[$key]);
                    $key=self::zaDEjsonit($key);
                    $handle[$key]=self::zaDEjsonit($val);
                }
            else
                $handle=$GLOBALS['APPLICATION']->ConvertCharset($handle,'UTF-8',LANG_CHARSET);
        }
        return $handle;
    }

    static function toUpper($str){
        $str = str_replace( //H8 ANSI
            array(
                GetMessage('IPOLSDEK_LANG_YO_S'),
                GetMessage('IPOLSDEK_LANG_CH_S'),
                GetMessage('IPOLSDEK_LANG_YA_S')
            ),
            array(
                GetMessage('IPOLSDEK_LANG_YO_B'),
                GetMessage('IPOLSDEK_LANG_CH_B'),
                GetMessage('IPOLSDEK_LANG_YA_B')
            ),
            $str
        );
        if(function_exists('mb_strtoupper'))
            return mb_strtoupper($str,LANG_CHARSET);
        else
            return strtoupper($str);
    }


    /*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
                                                        SENDING TO SDEK
            == sendToSDEK ==  == getXMLHeaders ==
    ()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


    public static function sendToSDEK($XML=false,$where=false,$get=false){
        if(!$where) return false;
        $where .= '.php' . (($get) ? "?".$get : '');
        $ch = curl_init();
        if($where == 'new_orders.php' && COption::GetOptionString(self::$MODULE_ID,'crazyHosters','N') != 'Y' && COption::GetOptionString(self::$MODULE_ID,'blockSwitch','N') != 'Y')
            curl_setopt($ch,CURLOPT_URL,'http://proxy.apiship.ru/cdek/new_orders.php');
        else
            curl_setopt($ch,CURLOPT_URL,'https://integration.cdek.ru/'.$where);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if($XML){
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, self::zajsonit(array('xml_request' => $XML)));
        }
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return array(
            'code'   => $code,
            'result' => $result
        );
    }

    static function getXMLHeaders($auth = false){
        $auth = self::defineAuth($auth);
        $date = date('Y-m-d');
        return array(
            'date'    => $date,
            'account' => $auth['ACCOUNT'],
            'secure'  => md5($date."&".$auth['SECURE']),
            'ID'	  => $auth['ID']
        );
    }


    /*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
                                                            AUTHORIZATION
            == defineAuth ==  == getBasicAuth ==  == getOrderAcc ==
    ()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


    static function defineAuth($params=false){
        if(!$params)
            $auth = self::getBasicAuth();
        else{
            if(is_array($params) && array_key_exists('ID',$params) && $params['ID'])
                $params = $params['ID'];
            if(is_numeric($params))
                $auth = sqlSdekLogs::getById($params);
            else{
                // ���������� �� ������
                $svd = self::getCountryOptions();
                if(array_key_exists($params['COUNTRY'],$svd) && $svd[$params['COUNTRY']]['acc']){
                    $auth = sqlSdekLogs::getById($svd[$params['COUNTRY']]['acc']);
                    if($auth['ACTIVE'] != 'Y')
                        $auth = self::getBasicAuth();
                }else
                    $auth = self::getBasicAuth();
            }
        }

        return array('ACCOUNT' => $auth['ACCOUNT'],'SECURE' => $auth['SECURE'],'ID'=>$auth['ID'],'LABEL'=>$auth['LABEL']);
    }

    static function getBasicAuth($onlyId = false){
        $idBasic = COption::GetOptionString(self::$MODULE_ID,'logged',false);
        if($idBasic !== false && $auth = sqlSdekLogs::getById($idBasic))
            return ($onlyId) ? $idBasic : $auth;
        else
            return false;
    }

    static function getOrderAcc($src,$mode=false){
        if(!self::isAdmin('R')) return false;
        if(!is_array($src))
            $src = ($mode == 'shipment') ? sqlSdekOrders::GetBySI($src) : sqlSdekOrders::GetByOI($src);
        return ($src['ACCOUNT']) ? $src['ACCOUNT'] : false;
    }


    /*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
                                                        TARIFS
            == getTarifList ==  == checkTarifAvail ==  == getDoorTarifs ==  == getExtraTarifs ==
    ()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


    static function getTarifList($params=array()){
        // type - ���, pickup ��� courier
        // mode - ��� ��������:
        // answer - �������� ������� (string) ��� �������� �� ���������� (array)
        $arList = array(
            'pickup'  => array(
                'usual'   => array(234,136,138),
                'heavy'   => array(15,17),
                'express' => array(62,63,5,10,12)
            ),
            'courier' => array(
                'usual'   => array(233,137,139),
                'heavy'   => array(16,18),
                'express' => array(11,1,3,61,60,59,58,57,83)
            )
        );
        $blocked = unserialize(COption::GetOptionString(sdekdriver::$MODULE_ID,'tarifs','a:{}'));
        if(count($blocked) && (!array_key_exists('fSkipCheckBlocks',$params) || !$params['fSkipCheckBlocks'])){
            foreach($blocked as $key => $val)
                if(!array_key_exists('BLOCK',$val))
                    unset($blocked[$key]);
            if(count($blocked))
                foreach($arList as $tarType => $arTars)
                    foreach($arTars as $tarMode => $arTarIds)
                        foreach($arTarIds as $key => $arTarId)
                            if(array_key_exists($arTarId,$blocked))
                                unset($arList[$tarType][$tarMode][$key]);
        }
        $answer = $arList;
        if($params['type']){
            if(is_numeric($params['type'])) $type = ($params['type']==136)?$type='pickup':$type='courier';
            else $type = $params['type'];
            $answer = $answer[$type];

            if($params['mode'] && array_key_exists($params['mode'],$answer))
                $answer = $answer[$params['mode']];
        }

        if(array_key_exists('answer',$params)){
            $answer = self::arrVals($answer);
            if($params['answer'] == 'string'){
                $answer = implode(',',$answer);
                $answer = substr($answer,0,strlen($answer));
            }
        }
        return $answer;
    }

    static function checkTarifAvail($profile = false){ // ��������� ����������� �������� �������� �� ����������� �������
        $tarifs = self::getTarifList(array('type'=>$profile,'answer'=>'array'));
        return (count($tarifs)>0);
    }

    static function getDoorTarifs($isStr=false){ // ����������, ����� ������ - �� �����
        $arList = array(1,12,17,18,138,139);
        if($isStr){
            $arList = implode(',',$arList);
            $arList = substr($arList,0,strlen($arList));
        }
        return $arList;
    }

    static function getExtraTarifs(){ // ���. ��������� ��� �������
        $arTarifs = array(136,137,138,139,233,234,1,3,5,10,11,12,15,16,17,18,57,58,59,60,61,62,63,83);
        $svdOpts = unserialize(COption::getOptionString(sdekdriver::$MODULE_ID,'tarifs','a:0:{}'));
        $arReturn = array();
        foreach($arTarifs as $tarifId)
            $arReturn[$tarifId] = array(
                'NAME'  => GetMessage("IPOLSDEK_tarif_".$tarifId."_NAME")." (".$tarifId.")",
                'DESC'  => GetMessage("IPOLSDEK_tarif_".$tarifId."_DESCR"),
                'SHOW'  => ($svdOpts[$tarifId]['SHOW']) ? $svdOpts[$tarifId]['SHOW'] : "N",
                'BLOCK' => ($svdOpts[$tarifId]['BLOCK']) ? $svdOpts[$tarifId]['BLOCK'] : "N",
            );
        return $arReturn;
    }


    /*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
                                                        HANDLING DELIVERIES
            == getDeliveryId ==  == defineDelivery ==  == getDelivery ==  == isActive ==  == getDeliveryConfig ==
    ()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


    static function getDeliveryId($profile){ // ���������� id �������� �������
        $profiles = array();
        if(self::isConverted()){
            $dTS = Bitrix\Sale\Delivery\Services\Table::getList(array(
                'order'  => array('SORT' => 'ASC', 'NAME' => 'ASC'),
                'filter' => array('CODE' => 'sdek:'.$profile)
            ));
            while($dPS = $dTS->Fetch())
                $profiles[]=$dPS['ID'];
        }else
            $profiles = array('sdek_'.$profile);
        return $profiles;
    }

    static function defineDelivery($id){ // ���������� ������� ��������
        if(self::isConverted() && strpos($id,':') === false){
            $dTS = Bitrix\Sale\Delivery\Services\Table::getList(array(
                'order'  => array('SORT' => 'ASC', 'NAME' => 'ASC'),
                'filter' => array('ID' => $id)
            ))->Fetch();
            $delivery = $dTS['CODE'];
        }else
            $delivery = $id;
        $position = strpos($delivery,'sdek:');
        return ($position === 0) ? substr($delivery,5) : false;
    }

    static function getDelivery($skipSite = false,$curId = false){// �������� ���������� ��
        if(!cmodule::includeModule("sale")) return false;
        $cite = ($skipSite) ? false : SITE_ID;
        if(self::isConverted()){
            $arFilter = ($curId) ? array('ID' => $curId) : array('CODE' => 'sdek');
			$request = Bitrix\Sale\Delivery\Services\Table::getList(array(
                'order'  => array('SORT' => 'ASC', 'NAME' => 'ASC'),
                'filter' => $arFilter
            ));
			while($dS = $request->Fetch()){
				if($dS['ACTIVE'] == 'Y')
					break;
			}
        }else
            $dS = CSaleDeliveryHandler::GetBySID('sdek',$cite)->Fetch();
        return $dS;
    }

    static function isActive(){
        $dS = self::getDelivery();
        return ($dS && $dS['ACTIVE'] == 'Y');
    }

    static function checkProfileActive($profile,$skipSite = false){
        cmodule::includeModule('sale');
        $cite = ($skipSite) ? false : SITE_ID;
        if(self::isConverted()){
            $dTS = Bitrix\Sale\Delivery\Services\Table::getList(array(
                'order'  => array('SORT' => 'ASC', 'NAME' => 'ASC'),
                'filter' => array('CODE' => 'sdek:'.$profile)
            ));
            while($dPS = $dTS->Fetch())
                if($dPS['ACTIVE'] == 'Y')
                    return true;
        }else{
            $dS = CSaleDeliveryHandler::GetBySID('sdek',$cite)->Fetch();
            return (array_key_exists($profile,$dS['PROFILES']) && $dS['PROFILES'][$profile]['ACTIVE']=='Y');
        }
        return false;
    }

    static function getDeliveryConfig($deliveryId=false,$skipSite = false){
        cmodule::includeModule('sale');
        $cite = ($skipSite) ? false : SITE_ID;
        if(self::isConverted()) {
            $dTS = Bitrix\Sale\Delivery\Services\Table::getList(array(
                'order'  => array('SORT' => 'ASC', 'NAME' => 'ASC'),
                'filter' => array('ID'   => $deliveryId)
            ))->Fetch();
            if($dTS && array_key_exists('PARENT_ID',$dTS) && $dTS['PARENT_ID']){
                $dTS = Bitrix\Sale\Delivery\Services\Table::getList(array(
                    'order'  => array('SORT' => 'ASC', 'NAME' => 'ASC'),
                    'filter' => array('ID'   => $dTS['PARENT_ID'])
                ))->Fetch();
            }

			if($dTS){
				$oldSettings = unserialize(unserialize($dTS['CONFIG']['MAIN']['OLD_SETTINGS']));
				foreach($oldSettings as $name => $value){
					$oldSettings[$name] = array('VALUE' => $value);
				}
			} else {
				$oldSettings = array();
			}

            return $oldSettings;
        } else {
            $dS = CSaleDeliveryHandler::GetBySID('sdek',$cite)->Fetch();
            return $dS['CONFIG']['CONFIG'];
        }
    }

    /*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
                                                        ������ � �������� � ����������������
            == getErrCities ==  == getNormalCity ==  == isLocation20 ==  == isCityAvail ==
    ()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


    static function getErrCities($link = 'rus'){//��������� ������
        $fileName = ($link == 'rus') ? 'errCities' : 'errCities_'.$link;
        if(!file_exists($_SERVER['DOCUMENT_ROOT']."/bitrix/js/".self::$MODULE_ID."/".$fileName.".json"))
            return false;
        return self::zaDEjsonit(json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT']."/bitrix/js/".self::$MODULE_ID."/".$fileName.".json"),true));
    }

    static function getNormalCity($cityId,$onlyCity = false){// �������������� 2.0, �������� id �����a
        if(self::isLocation20() && $cityId){//getLocationIDbyCODE
            $cityType    = \Bitrix\Sale\Location\TypeTable::getList(array('filter'=>array('=CODE'=>'CITY')))->Fetch();
            $villageType = \Bitrix\Sale\Location\TypeTable::getList(array('filter'=>array('=CODE'=>'VILLAGE')))->Fetch();
            if(strlen($cityId) >= 10 || !is_numeric($cityId))
                $city = \Bitrix\Sale\Location\LocationTable::getList(array('filter' => array('=CODE' => $cityId)))->Fetch();
            else
                $city = \Bitrix\Sale\Location\LocationTable::getById($cityId)->Fetch();

            if(
				$city['TYPE_ID'] != $cityType['ID'] && 
				($onlyCity || !$villageType || $city['TYPE_ID'] != $villageType['ID'])
			){
                $newCityId = false;
                while(!$newCityId){
                    if(empty($city['PARENT_ID']))
                        break;
                    $city = \Bitrix\Sale\Location\LocationTable::getList(array('filter' => array('=ID' => $city['PARENT_ID'])))->Fetch();
                    if($city['TYPE_ID'] == $cityType['ID'])
                        $newCityId = $city['ID'];
                }
            }
            $cityId = $city['ID'];
        }
        return $cityId;
    }

    static function isLocation20(){
        return (method_exists("CSaleLocation","isLocationProMigrated") && CSaleLocation::isLocationProMigrated());
    }

    static function isCityAvail($city,$mode=false){// �������� ����������� �������� � �����
        if(!is_numeric($city)){
            $cityName = str_replace(GetMessage('IPOLSDEK_LANG_YO_S'),GetMessage('IPOLSDEK_LANG_YE_S'),$city);
            $city = CSaleLocation::getList(array(),array('CITY_NAME'=>self::zaDEjsonit($city)))->Fetch();
            if($city)
                $cityId = $city['ID'];
        }else{
            $cityId = $city;
            $city = CSaleLocation::GetByID($cityId);
            $cityName = str_replace(GetMessage('IPOLSDEK_LANG_YO_S'),GetMessage('IPOLSDEK_LANG_YE_S'),$city['CITY_NAME']);
        }
        $return = false;
        if($city){
            $arCity = self::getSQLCityBI($cityId);
            if($arCity['SDEK_ID']){
                $return = array('courier');
                if(CDeliverySDEK::checkPVZ($cityName))
                    $return[]='pickup';
            }
        }
        return $return;
    }

    public static function getCity($location,$ifFull = false){ // �������� ����� �� �� �� ��� ���� / id
        if(!$location)
            return false;
        $arCity = self::getSQLCityBI($location);
		if($arCity){
			if($ifFull)
				return $arCity;
			else
				return $arCity['SDEK_ID'];
		}
		return false;
    }

	
	public static function getSQLCityBI($bitrixID,$skipAPI=false)
	{
		if(!$bitrixID && !self::getNormalCity($bitrixID))
			return false;
		$arCity = sqlSdekCity::getByBId($bitrixID);
		if(!$arCity){
            $arCity = sqlSdekCity::getByBId(self::getNormalCity($bitrixID));
		}
		if(
			!$arCity && 
			!$skipAPI && 
			is_numeric($bitrixID) &&
			COption::GetOptionString(self::$MODULE_ID,'autoAddCities','N') == 'Y'
		){
			$cityAdder = new sdekCityGetter(self::getNormalCity($bitrixID),COption::GetOptionString(self::$MODULE_ID,'dostTimeout',6));
			$cityAdder->search();
			if($cityAdder->getSDEK()){
				$arCity = $cityAdder->getSDEK();
			}
		}
		return $arCity;
	}

    public static function getHomeCity(){ // �������� �����
        return self::getCity(COption::GetOptionString(self::$MODULE_ID,'departure'));
    }

    public static function getCountryCities($countries=false){
        $cities = sqlSdekCity::getCitiesByCountry($countries);

        $arCities = array();
        while($city=$cities->Fetch()){
            if(!$city['COUNTRY'])
                $city['COUNTRY'] = 'rus';
            $city['COUNTRY_NAME'] = GetMessage('IPOLSDEK_SYNCTY_'.$city['COUNTRY']);
            $arCities[] = $city;
        }

        return $arCities;
    }

    /*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
                                                        ��������� � ��� ������
            == getListFile ==  == arrVals ==  == isEqualArrs ==  == isLogged ==  == isConverted ==  == isAdmin ==  == getSaleVersion ==  == oIdByShipment ==
    ()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


    static function getVolumeWeight($length,$width,$height){
        return $length*$width*$height / 5000000;
    }

    static function getListFile($noEnc=false){// ������� ������ �� LIST - ����� � ��� �������, � ������� ���... ������... ����...
        if(!file_exists($_SERVER['DOCUMENT_ROOT']."/bitrix/js/".self::$MODULE_ID."/list.php")) return array();
        $arList = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT']."/bitrix/js/".self::$MODULE_ID."/list.php"),true);
        if(!$noEnc)
            $arList = self::zaDEjsonit($arList);
        foreach(GetModuleEvents(self::$MODULE_ID,"onPVZListReady",true) as $arEvent)
            ExecuteModuleEventEx($arEvent,Array(&$arList));
        return $arList;
    }

    static function isLogged(){
        return COption::GetOptionString(self::$MODULE_ID,"logged",false);
    }

    static function isConverted(){
        return (COption::GetOptionString("main","~sale_converted_15",'N') == 'Y');
    }

    protected static $skipAdminCheck = false;
    static function isAdmin($min = 'W'){
        if(self::$skipAdminCheck) return true;
        $rights = CMain::GetUserRight(self::$MODULE_ID);
        $DEPTH = array('D'=>1,'R'=>2,'W'=>3);
        return($DEPTH[$min] <= $DEPTH[$rights]);
    }

    protected static function getSaleVersion(){
        include($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/install/version.php');
        return $arModuleVersion['VERSION'];
    }

    static function getCountryOptions(){
		$result = self::zaDEjsonit(json_decode(COption::GetOptionString(self::$MODULE_ID,'countries','{"rus":{"act":"Y"}}'),true));
        return (is_array($result)) ? $result : array();
    }

    // �����������
    static function oIdByShipment($shipmentID){
        if(!self::isConverted())
            return false;
        \Bitrix\Main\Loader::includeModule('sale');
        $shipment = self::getShipmentById($shipmentID);
        return $shipment['ORDER_ID'];
    }

    protected static function setShipmentField($shipmentId,$field,$value){
        if(!$shipmentId || !self::isConverted())
            return false;
        $order = \Bitrix\Sale\Order::load(self::oIdByShipment($shipmentId));
        $shipmentCollection = $order->getShipmentCollection();
        $shipment = $shipmentCollection->getItemById($shipmentId);
        $shipment->setField($field,$value);
        $order->save();
        return true;
    }

    static function getShipmentById($shipmentId){
        if(!self::isConverted())
            return false;
        \Bitrix\Main\Loader::includeModule('sale');
        return Bitrix\Sale\Shipment::getList(array('filter'=>array('ID' => $shipmentId)))->Fetch();
    }

    static function canShipment(){
        return (self::isConverted() && COption::GetOptionString(self::$MODULE_ID,'shipments','N') == 'Y');
    }


    /*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
                                                        SERVICE
            == round2 ==  == arrVals ==  == isEqualArrs ==
    ()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/

    static function round2($wat){
        return floor($wat * 100) / 100;
    }

    static function arrVals($arr){ // ����� ���������
        $return = array();
        foreach($arr as $key => $val)
            if(is_array($val))
                $return = array_merge($return,self::arrVals($val));
            else
                $return []= $val;
        return $return;
    }

    static function isEqualArrs($arr1,$arr2){ // ��� ����� ���������
        foreach($arr1 as $key => $val)
            if(!array_key_exists($key,$arr2) || $arr1[$key] != $arr2[$key])
                return false;
            else
                unset($arr2[$key]);

        if(count($arr2))
            return false;

        return true;
    }


    /*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
                                                        LEGACY
            == cntDelivs ==  == defineProto ==
    ()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/

    static function cntDelivs($arOrder){//������ ���� � ��������� �������� ��� �������
        return CDeliverySDEK::countDelivery($arOrder);
    }

    static function defineProto(){
        return (
            !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ||
            $_SERVER['SERVER_PORT'] == 443 ||
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' ||
            isset($_SERVER['HTTP_X_HTTPS']) && $_SERVER['HTTP_X_HTTPS'] ||
            isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] == 'https'
        ) ? 'https' : 'http';
    }
}

spl_autoload_register(function($className){
	if (strpos($className, 'Ipolh\SDEK') === 0)
	{
		$classPath = implode(DIRECTORY_SEPARATOR, explode('\\', substr($className,11)));

		$filename = __DIR__ . DIRECTORY_SEPARATOR . "classes".DIRECTORY_SEPARATOR."lib" . DIRECTORY_SEPARATOR . $classPath . ".php";

		if (is_readable($filename) && file_exists($filename))
			require_once $filename;
	}
});

CModule::AddAutoloadClasses(
    sdekHelper::$MODULE_ID,
    array(
        'sdekdriver'				 => '/classes/general/sdekclass.php',
        'CDeliverySDEK'				 => '/classes/general/sdekdelivery.php',
        'sdekOption'				 => '/classes/general/sdekoption.php',
        'sdekExport'				 => '/classes/general/sdekexport.php',
        'sqlSdekOrders'				 => '/classes/mysql/sqlSdekOrders.php',
        'sqlSdekCity'				 => '/classes/mysql/sqlSdekCity.php',
        'sqlSdekLogs'				 => '/classes/mysql/sqlSdekLogs.php',
        'CalculatePriceDeliverySdek' => '/classes/sdekMercy/calculator.php',
        'cityExport'				 => '/classes/sdekMercy/syncCityClass.php',
        'sdekCityGetter'			 => '/classes/sdekMercy/getCityClass.php',
        'sdekShipment'				 => '/classes/lib/sdekShipment.php',
        'sdekShipmentCollection'	 => '/classes/lib/sdekShipmentCollection.php'
    )
);



?>