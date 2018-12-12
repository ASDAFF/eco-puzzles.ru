<?
namespace Shiptor\Delivery;

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Web\Json,
    Shiptor\Delivery\Options\Config,
    Shiptor\Delivery\Logger,
    Bitrix\Main\Web\HttpClient;

Loc::loadMessages(__FILE__);

class CShiptorAPI{
    private $debug = false;
    private $id;
    private $url;
    private $token = null;
    private $arMethod = null;
    private $codeError = null;
    private $codeStatusIncomingPackage = null;
    private $codeStatusPickUp = null;
    private $codeStatusOutgoingPackage = null;
    const PUBLIC_API_URL = "https://api.shiptor.ru/public/v1";
    const SHIPPING_API_URL = "https://api.shiptor.ru/shipping/v1";
    private static $instance = null;
    private function __construct(){
        $this->token = Config::getApiKey();
        $this->debug = Config::isDebug();
        $this->id = mt_rand(11111,99999);
        $this->url = self::SHIPPING_API_URL;
        $this->setMethods();
        $this->setErrors();
        $this->setCodeStatuses();
    }
    public static function getInstance(){
        if (!isset(self::$instance)) {
            self::$instance = new CShiptorAPI();
        }
        return self::$instance;
    }
    public function setToken ($token){
        $this->token = $token;
    }
    public function isTokenValid(){
        return (bool)(strlen($this->token) === 40);
    }
    public function getTokenString(){
        return substr($this->token,0,15);
    }
    private function setMethods(){
        $this->arMethod = array(
            'getSettlements' => Loc::getMessage("SHITOR_API_METHOD_GET_SETTLEMENT"),
            'suggestSettlement' => Loc::getMessage("SHITOR_API_METHOD_SUGGEST_SETTLEMENT"),
            'getShippingMethods' => Loc::getMessage("SHITOR_API_METHOD_GET_SHIPPING_VARIANTS"),
            'getDeliveryPoints' => Loc::getMessage("SHITOR_API_METHOD_GET_DELIVERY_POINTS"),
            'getDaysOff' => Loc::getMessage("SHITOR_API_METHOD_GET_DAYS_OFF"),
            'getDeliveryTime' => Loc::getMessage("SHITOR_API_METHOD_GET_DELIVERY_TIME"),
            'calculateShipping' => Loc::getMessage("SHITOR_API_METHOD_CALCULATE_SHIPMENT"),
            'addPackage' => Loc::getMessage("SHITOR_API_METHOD_ADD_PACKAGE"),
            'addPackages' => Loc::getMessage("SHITOR_API_METHOD_ADD_PACKAGES"),
            'removePackage' => Loc::getMessage("SHITOR_API_METHOD_REMOVE_PACKAGE"),
            'getPackage' => Loc::getMessage("SHITOR_API_METHOD_GET_PACKAGE"),
            'getPackages' => Loc::getMessage("SHITOR_API_METHOD_GET_PACKAGES"),
            'addProduct' => Loc::getMessage("SHITOR_API_METHOD_ADD_PRODUCT"),
            'addService' => loc::getMessage("SHITOR_API_METHOD_ADD_SERVICE"),
            'addPickUp' => Loc::getMessage("SHITOR_API_METHOD_ADD_PICKUP"),
            'cancelPickUp' => Loc::getMessage("SHITOR_API_METHOD_CANCEL_PICKUP"),
            'getPickUp' => Loc::getMessage("SHITOR_API_METHOD_GET_PICKUP"),
            'getPickUpTime' => Loc::getMessage("SHITOR_API_METHOD_GET_PICKUP_TIME"),
            'getCourierPickUpTime' => Loc::getMessage("SHITOR_API_METHOD_GET_COURIER_PICKUP_TIME"),
            'getProducts' => Loc::getMessage("SHITOR_API_METHOD_GET_PRODUCTS"),
            'getTracking' => Loc::getMessage("SHITOR_API_METHOD_GET_TRACKING")
        );
    }
    private function setErrors(){
        $this->codeError = array(
        '1001' => Loc::getMessage("SHIPTOR_API_ERROR_1001"),
        '1002' => Loc::getMessage("SHIPTOR_API_ERROR_1002"),
        '1100' => Loc::getMessage("SHIPTOR_API_ERROR_1100"),
        '1103' => Loc::getMessage("SHIPTOR_API_ERROR_1103"),
        '1104' => Loc::getMessage("SHIPTOR_API_ERROR_1104"),
        '1201' => Loc::getMessage("SHIPTOR_API_ERROR_1201"),
        '1087' => Loc::getMessage("SHIPTOR_API_ERROR_1087"),
        '2033' => Loc::getMessage("SHIPTOR_API_ERROR_2033"),
        '2044' => Loc::getMessage("SHIPTOR_API_ERROR_2044"),
        '2045' => Loc::getMessage("SHIPTOR_API_ERROR_2045"),
        '2055' => Loc::getMessage("SHIPTOR_API_ERROR_2055"),
        '2022' => Loc::getMessage("SHIPTOR_API_ERROR_2022"),
        '1400' => Loc::getMessage("SHIPTOR_API_ERROR_1400"),
        '1401' => Loc::getMessage("SHIPTOR_API_ERROR_1401"),
        '-32700' => Loc::getMessage("SHIPTOR_API_ERROR_-32700"),
        '-32600' => Loc::getMessage("SHIPTOR_API_ERROR_-32600"),
        '-32602' => Loc::getMessage("SHIPTOR_API_ERROR_-32602")
    );
    }
    private function setCodeStatuses(){
        $this->codeStatusIncomingPackage = array(
            'awaiting_arrival' => Loc::getMessage("SHIPTOR_API_INCOMING_STATUS_AWAITS"),
            'in_stock' => Loc::getMessage("SHIPTOR_API_INCOMING_STATUS_IN_STOCK"),
            'removed' => Loc::getMessage("SHIPTOR_API_INCOMING_STATUS_REMOVED")
        );
        $this->codeStatusPickUp = array(
            'waiting-process' => Loc::getMessage("SHIPTOR_API_PICKUP_STATUS_WAITING"),
            'in-work' => Loc::getMessage("SHIPTOR_API_PICKUP_STATUS_IN_WORK"),
            'in-store' => Loc::getMessage("SHIPTOR_API_PICKUP_STATUS_IN_STORE"),
            'canceled' => Loc::getMessage("SHIPTOR_API_PICKUP_STATUS_CANCELED"),
            'completed' => Loc::getMessage("SHIPTOR_API_PICKUP_STATUS_COMPLETED")
        );
        $this->codeStatusOutgoingPackage = array(
            'new' => Loc::getMessage("SHIPTOR_API_OUTGOING_STATUS_NEW"),
            'checking-declaration' => Loc::getMessage("SHIPTOR_API_OUTGOING_CHECKING_DECLARATION"),
            'declaration-checked' => Loc::getMessage("SHIPTOR_API_OUTGOING_DECLARATION_CHECKED"),
            'waiting-pickup' => Loc::getMessage("SHIPTOR_API_OUTGOING_WAITING_PICKUP"),
            'waiting-send' => Loc::getMessage("SHIPTOR_API_OUTGOING_WAITING_SEND"),
            'arrived-to-warehouse' => Loc::getMessage("SHIPTOR_API_OUTGOING_ARRIVED_TO_WAREHOUSE"),
            'packed' => Loc::getMessage("SHIPTOR_API_OUTGOING_PACKED"),
            'prepared-to-send' => Loc::getMessage("SHIPTOR_API_OUTGOING_PREPARED_TO_SEND"),
            'sent' => Loc::getMessage("SHIPTOR_API_OUTGOING_STATUS_SENT"),
            'delivered' => Loc::getMessage("SHIPTOR_API_OUTGOING_STATUS_RECIEVED"),
            'removed' => Loc::getMessage("SHIPTOR_API_OUTGOING_STATUS_REMOVED"),
            'recycled' => Loc::getMessage("SHIPTOR_API_OUTGOING_STATUS_RECYCLED"),
            'returned' => Loc::getMessage("SHIPTOR_API_OUTGOING_STATUS_RETURNED"),
            'reported' => Loc::getMessage("SHIPTOR_API_OUTGOING_STATUS_REPORTED"),
            'lost' => Loc::getMessage("SHIPTOR_API_OUTGOING_STATUS_LOST"),
            'resend' => Loc::getMessage("SHIPTOR_API_OUTGOING_STATUS_RESEND"),
            'waiting-on-delivery-point' => Loc::getMessage("SHIPTOR_API_OUTGOING_STATUS_WAITING_IN_PVZ"),
        );
    }
    public function getMethod($method){
        if($method == '')
            return $this->arMethod;
        elseif(isset($this->arMethod[$method]) && $this->arMethod[$method] != '')
            return $this->arMethod[$method];
        else
            return false;
    }
    public function getError($code = ''){
        if($code == '')
            return $this->codeError;
        elseif(isset($this->codeError[$code]) && $this->codeError[$code] != '')
            return $this->codeError[$code];
        else
            return Loc::getMessage("SHIPTOR_API_ERROR_UNKNOWN");
    }
    public function getStatusIncomingPackage($status = ''){
        if($status == '')
            return $this->codeStatusIncomingPackage;
        elseif(isset($this->codeStatusIncomingPackage[$status]) && $this->codeStatusIncomingPackage[$status] != '')
            return $this->codeStatusIncomingPackage[$status];
        else
            return false;
    }
    public function getStatusPickUp($status = ''){
        if($status == '')
            return $this->codeStatusIncomingPackage;
        elseif(isset($this->codeStatusIncomingPackage[$status]) && $this->codeStatusIncomingPackage[$status] != '')
            return $this->codeStatusIncomingPackage[$status];
        else
            return false;
    }
    public function getStatusOutgoingPackage($status = ''){
        if($status == '')
            return $this->codeStatusOutgoingPackage;
        elseif(isset($this->codeStatusOutgoingPackage[$status]) && $this->codeStatusOutgoingPackage[$status] != '')
            return $this->codeStatusOutgoingPackage[$status];
        else
            return false;
    }
    public function Request($method,$arParams = array()){
        if(!is_scalar($method)){
            return array("error" => array("code" => 1,"message" => Loc::getMessage("SHIPTOR_API_REQUEST_ERROR_WRONG_TYPE")),"status" => "error");
        }
        if(!isset($this->arMethod[$method]) && empty($this->arMethod[$method])){
            return array("error" => array("code" => 2,"message" => Loc::getMessage("SHIPTOR_API_REQUEST_ERROR_UNKNOWN_METHOD")),"status" => "error");
        }
        if(!is_array($arParams)){
            return array("error" => array("code" => 3,"message" => Loc::getMessage("SHIPTOR_API_REQUEST_ERROR_NOT_ARRAY")),"status" => "error");
        }
        $currentId = $this->id;
        $this->id = mt_rand(100000,999999);
        $arOptions = array(
            "redirect" => false,
            "waitResponse" => true,
            "socketTimeout" => 3,
            "streamTimeout" => 0,
            "version" => HttpClient::HTTP_1_1,
            "charset" => "UTF-8",
            "disableSslVerification" => true
        );
        $oHttpClient = new HttpClient($arOptions);
        $oHttpClient->setHeader("Host", "api.shiptor.ru");
        $oHttpClient->setHeader("Content-type", "application/json");
        if(in_array($method,array("getDaysOff"))){
            $oHttpClient->setHeader("POST", "/public/v1 HTTP/1.1");
            $this->url = self::PUBLIC_API_URL;
        }else{
            $oHttpClient->setHeader("X-Authorization-Token", $this->token);
            $oHttpClient->setHeader("POST", "/shipping/v1 HTTP/1.1");
            $this->url = self::SHIPPING_API_URL;
        }
        $oHttpClient->setHeader("Integration-Name", "Bitrix");
        include(dirname(__DIR__) . "/install/version.php");
        $oHttpClient->setHeader("Integration-Version", $arModuleVersion["VERSION"]);
        $data = Json::encode(array(
            'id' => $currentId,
            "jsonrpc" => "2.0",
            'method' => $method,
            'params' => $arParams
        ));
        Logger::info($data);
        $response = $oHttpClient->post($this->url,$data);
        if(!empty($response)){
            if(strpos($response,"jsonrpc") === false){
                return array("error" => array("code" => 5,"message" => $response),"status" => "error");
            }
            try{
                $response = Json::decode($response,true);
                Logger::info($response);
            }catch(Exception $e){
                return array("error" => array("code" => 4, "message" => $e->getMessage()), "status" => "error");
            }
        }else{
            return array("error" => array("code" => 4,"message" => Loc::getMessage("SHIPTOR_API_RESPONSE_ERROR_404",array("#URL#" => $this->url))),"status" => "error");
        }
        //--------------------------------------------------------------------------------
        if(isset($response['error']) || $response['status'] == 'error'){
            return $response;
        }elseif($response['status'] == 'warning'){
            return $response;
        }elseif($response['id'] != $currentId)
            return array("error" => array("code" => 5,"message" => Loc::getMessage("SHIPTOR_API_RESPONSE_ERROR_WRONG",array("#ID#" => $currentId, "#RES_ID#" => $response['id']))),"status" => "error");
        //--------------------------------------------------------------------------------
        return $response;
    }
}
