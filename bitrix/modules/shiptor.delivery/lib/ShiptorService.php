<?
namespace Shiptor\Delivery;

use Bitrix\Main\Application,
    Bitrix\Sale\Location\ExternalTable as ET,
    Bitrix\Sale\Location\ExternalServiceTable as EST,
    Bitrix\Sale\Location\LocationTable as LT;

class ShiptorService{
    const CODE = "SHPTOR_KLADR";
    private $id;
    private static $instance;
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function __construct() {
        $this->create();
    }
    private function create() {
        if (!$this->check()) {
            $res = EST::add(array("CODE" => self::CODE));
            if ($res->isSuccess()) {
                $this->check();
            }
        }
    }
    private function check() {
        $res = EST::getList(array('filter' => array('=CODE' => self::CODE), 'limit' => 1))->fetch();
        if (!empty($res)) {
            $this->id = $res["ID"];
            return true;
        } else {
            return false;
        }
    }
    public function getId(){
        return $this->id;
    }
    public function getLocation() {
        $arParams = array(
            'filter' => array(
                '=SERVICE.CODE' => self::CODE,
            ),
            'select' => array(
                'LOCATION_ID',
            )
        );
        $res = ET::getList($arParams);
        $items = array();
        while ($loc = $res->fetch()) {
            $items[] = $loc["LOCATION_ID"];
        }
        return !empty($items) ? $items : false;
    }
    public function getCode($locationCode = false) {
        if (empty($locationCode)) {
            return false;
        }
        $item = LT::getRow(array(
            'filter' => array(
                "=CODE" => $locationCode,
                "=EXTERNAL.SERVICE.ID" => $this->id
            ),
            'select' => array(
                "ID",
                self::CODE => "EXTERNAL.XML_ID"
            )
        ));
        return $item[self::CODE] ?: false;
    }
    public function addByCode($locationCode,$ppCode){
        $arFilter = array("=CODE" => $locationCode);
        $arSelect = array("ID");
        $arLocation = LT::getList(array("filter" => $arFilter, "select" => $arSelect))->fetch();
        if(!empty($arLocation["ID"])){
            return $this->add($arLocation["ID"],$ppCode);
        }else{
            return false;
        }
    }
    public function add($locationId, $ppCode) {
        $res = ET::add(array(
            'LOCATION_ID' => $locationId,
            'XML_ID' => $ppCode,
            'SERVICE_ID' => $this->id
        ));
        return $res->isSuccess();
    }
    public function delete($locationId) {
        $res = ET::delete(array('LOCATION_ID' => $locationId, 'SERVICE_ID' => $this->id));
        return $res->isSuccess();
    }
    public function flush(){
        $connection = Application::getConnection();
        $tableName = ET::getTableName();
        $sqlText = <<<SQL
delete from $tableName where SERVICE_ID = $this->id
SQL;
        $connection->query($sqlText);
    }
}