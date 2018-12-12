<?
namespace Shiptor\Delivery;

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Loader,
    Bitrix\Currency,
    Bitrix\Sale\Shipment,
    Bitrix\Sale\Internals\StatusTable,
    Bitrix\Main\Page\Asset,
    Bitrix\Sale\Delivery\Services\Manager,
    Shiptor\Delivery\Options\Config;

Loader::IncludeModule("sale");
Loader::IncludeModule("shiptor.delivery");

Loc::loadMessages(__FILE__);

class ShiptorHandler extends \Bitrix\Sale\Delivery\Services\Base {
    const MODULE_ID = "shiptor.delivery";
    const MARGIN_PERCENT = 'PERCENT';
    const MARGIN_CURRENCY = 'CURRENCY';
    const COD_ALWAYS = 'always';
    const COD_CERTAIN = 'coded';
    const COD_NEVER = 'never';
    protected static $isCalculatePriceImmediately = true;
    protected static $whetherAdminExtraServicesShow = false;
    protected static $canHasProfiles = true;
    protected static $isProfile = false;

    public function __construct(array $initParams) {
        parent::__construct($initParams);
        $this->setDefaultLogo();
    }
    private function setDefaultLogo(){
        $fileId = \CShiptorDeliveryHelper::getLogoId();
        if(!$this->logotip && $fileId){
            $this->logotip = $fileId;
        }
    }
    public static function getClassTitle() {
        return Loc::getMessage("SHIPTOR_DLVR_HANDL_AUT_NAME");
    }
    public static function getClassDescription() {
        return Loc::getMessage("SHIPTOR_DLVR_HANDL_AUT_DESCRIPTION");
    }
    public function isCalculatePriceImmediately() {
        return self::$isCalculatePriceImmediately;
    }
    public static function whetherAdminExtraServicesShow() {
        return self::$whetherAdminExtraServicesShow;
    }
    public static function getDefaultConfigValues(){
        return array(
            'MAIN' => array(
                'MARGIN_VALUE' => 0,
                'MARGIN_TYPE' => '%',
                'ADD_TERMS' => 0,
                'SHOW_TERMS' => 'Y',
                'CALC_ALGORITM' => 'N',
                'LENGTH_VALUE' => 30,
                'WIDTH_VALUE' => 20,
                'HEIGHT_VALUE' => 10,
                'WEIGHT_VALUE' => 3
            ),
            'COD' => array(
                'CALCULATION_TYPE' => self::COD_ALWAYS,
                'INCLUDE_COD' => 'Y',
                'COST_DECLARING' => 'N'
            )
        );
    }
    protected function getConfigStructure() {
        $currencyList = Currency\CurrencyManager::getCurrencyList();
        if (isset($currencyList[$this->currency])){
            $currency = $currencyList[$this->currency];
        }
        unset($currencyList);

        $dbPaySystem = \Bitrix\Sale\PaySystem\Manager::getList(array(
            'filter' => array('ACTIVE' => 'Y'),
            'select' => array("ID", "NAME")
        ));
        while ($arPaySystem = $dbPaySystem->fetch()){
            $arPaySystems[$arPaySystem["ID"]] = $arPaySystem["NAME"];
        }
        $defaultValues = self::getDefaultConfigValues();

        $result = array(
            'MAIN' => array(
                'TITLE' => Loc::getMessage("SHIPTOR_DLVR_HANDL_AUT_HANDLER_SETTINGS"),
                'DESCRIPTION' => Loc::getMessage("SHIPTOR_DLVR_HANDL_AUT_HANDLER_SETTINGS_DSCR"),
                'ITEMS' => array(
                    'MARGIN_SECTION' => array(
                        'TYPE' => 'DELIVERY_SECTION',
                        'NAME' => Loc::getMessage("SHIPTOR_DLVR_HANDL_MARGIN_SECTION_NAME")
                    ),
                    "MARGIN_VALUE" => array(
                        "TYPE" => "STRING",
                        "NAME" => Loc::getMessage("SHIPTOR_DLVR_HANDL_AUT_MARGIN_VALUE"),
                        'SIZE' => 11,
                        "DEFAULT" => $defaultValues['MAIN']['MARGIN_VALUE']
                    ),
                    'MARGIN_TYPE' => array(
                        'TYPE' => 'ENUM',
                        "NAME" => Loc::getMessage("SALE_DLVR_HANDL_AUT_MARGIN_TYPE"),
                        'DEFAULT' => $defaultValues['MAIN']['MARGIN_TYPE'],
                        "OPTIONS" => array(
                            self::MARGIN_PERCENT => "%",
                            self::MARGIN_CURRENCY => $currency
                        )
                    ),
                    'TERMS_SECTION' => array(
                        'TYPE' => 'DELIVERY_SECTION',
                        'NAME' => Loc::getMessage("SHIPTOR_DLVR_HANDL_TERMS_SECTION_NAME")
                    ),
                    'ADD_TERMS' => array(
                        "TYPE" => "STRING",
                        "NAME" => Loc::getMessage("SHIPTOR_DLVR_HANDL_ADD_TERMS_NAME"),
                        'SIZE' => 11,
                        "DEFAULT" => $defaultValues['MAIN']['ADD_TERMS']
                    ),
                    'SHOW_TERMS' => array(
                        'TYPE' => 'Y/N',
                        'NAME' => Loc::getMessage("SHIPTOR_DLVR_HANDL_AUT_SHOW_TERMS"),
                        'DEFAULT' => $defaultValues['MAIN']['SHOW_TERMS']
                    ),
                    'DEFAULT_VALUE' => array(
                        'TYPE' => 'DELIVERY_SECTION',
                        'NAME' => Loc::getMessage("SHIPTOR_DLVR_HANDL_SECTION_NAME")
                    ),
                    "CALC_ALGORITM" => array(
                        'TYPE' => 'ENUM',
                        'NAME' => Loc::getMessage("SHIPTOR_DLVR_HANDL_CALC_ALGORITM"),
                        'DEFAULT' => $defaultValues['MAIN']['CALC_ALGORITM'],
                        "OPTIONS" => array(
                            "N" => Loc::getMessage("SHIPTOR_DLVR_HANDL_CALC_ALGORITM_SINGLE"),
                            "Y" => Loc::getMessage("SHIPTOR_DLVR_HANDL_CALC_ALGORITM_COMPLEX"),
                        )
                    ),
                    "LENGTH_VALUE" => array(
                        "TYPE" => "STRING",
                        "NAME" => Loc::getMessage("SHIPTOR_DLVR_HANDL_PACKAGE_LENGTH"),
                        'SIZE' => 11,
                        "DEFAULT" => $defaultValues['MAIN']['LENGTH_VALUE']
                    ),
                    "WIDTH_VALUE" => array(
                        "TYPE" => "STRING",
                        "NAME" => Loc::getMessage("SHIPTOR_DLVR_HANDL_PACKAGE_WIDTH"),
                        'SIZE' => 11,
                        "DEFAULT" => $defaultValues['MAIN']['WIDTH_VALUE']
                    ),
                    "HEIGHT_VALUE" => array(
                        "TYPE" => "STRING",
                        "NAME" => Loc::getMessage("SHIPTOR_DLVR_HANDL_PACKAGE_HEIGHT"),
                        'SIZE' => 11,
                        "DEFAULT" => $defaultValues['MAIN']['HEIGHT_VALUE']
                    ),
                    "WEIGHT_VALUE" => array(
                        "TYPE" => "STRING",
                        "NAME" => Loc::getMessage("SHIPTOR_DLVR_HANDL_PACKAGE_WEIGHT"),
                        'SIZE' => 11,
                        "DEFAULT" => $defaultValues['MAIN']['WEIGHT_VALUE'],
                        "ONCHANGE" => <<<JS
                            if(!!this.value){
                                if(this.value.indexOf(',') !== -1){this.value = this.value.replace(',','.')}
                        }
JS
                    )
                )
            ),
            'COD' => array(
                'TITLE' => Loc::getMessage("SHIPTOR_DLVR_HANDL_AUT_NALOZH_PLATEZH"),
                'DESCRIPTION' => Loc::getMessage("SHIPTOR_DLVR_HANDL_AUT_NALOZH_PLATEZH_DESC"),
                'ITEMS' => array()
            )
        );

        //-----------------------------------------------------------------------
        // Наложенный платеж
        $result['COD']['ITEMS'] = array(
            'CALCULATION_TYPE' => array(
                'TYPE' => 'ENUM',
                "NAME" => Loc::getMessage("SHIPTOR_DLVR_HANDL_CALCULATION_TYPE"),
                'DEFAULT' => $defaultValues['COD']['CALCULATION_TYPE'],
                "OPTIONS" => array(
                    self::COD_ALWAYS => Loc::getMessage("SHIPTOR_DLVR_HANDL_CALCULATION_TYPE_ALWAYS"),
                    self::COD_CERTAIN => Loc::getMessage("SHIPTOR_DLVR_HANDL_CALCULATION_TYPE_CODED"),
                    self::COD_NEVER => Loc::getMessage("SHIPTOR_DLVR_HANDL_CALCULATION_TYPE_NEVER"),
                ),
                "ONCHANGE" => "this.form.submit();"
            ),
        );
        if($this->config['COD']['CALCULATION_TYPE'] == self::COD_CERTAIN){
                $result['COD']['ITEMS']['SERVICES_LIST'] = array(
                    'TYPE' => 'ENUM',
                    'NAME' => Loc::getMessage("SHIPTOR_DLVR_HANDL_PICK_PS_COD"),
                    "DEFAULT" => 1,
                    "MULTIPLE" => "Y",
                    'SIZE' => 7,
                    "OPTIONS" => $arPaySystems
                );
        }
        if(in_array($this->config['COD']['CALCULATION_TYPE'],array(self::COD_CERTAIN,self::COD_ALWAYS))){
            $result['COD']['ITEMS']['INCLUDE_COD'] = array(
                'TYPE' => 'Y/N',
                 'NAME' => Loc::getMessage("SHIPTOR_DLVR_HANDL_INCLUDE_COD"),
                 'DEFAULT' => $defaultValues['COD']['INCLUDE_COD'],
                 "ONCHANGE" => "this.form.submit();"
            );
        }
        $result['COD']['ITEMS']['COST_DECLARING'] = array(
            'TYPE' => 'Y/N',
            'NAME' => Loc::getMessage("SHIPTOR_DLVR_HANDL_INCL_INSURANCE_DELIVERY_COST"),
            'DEFAULT' => $defaultValues['COD']['COST_DECLARING']
        );
        return $result;
    }
    protected function calculateConcrete(Shipment $shipment = null) {
        throw new \Bitrix\Main\SystemException(Loc::getMessage("SHIPTOR_DLVR_HANDL_PROFILES_CALCULATE"));
    }
    public static function canHasProfiles() {
        return self::$canHasProfiles;
    }
    public static function getChildrenClassNames() {
        return ['\Shiptor\Delivery\ProfileHandler'];
    }
    public function getProfilesList() {
        $arProfiles = array();
        foreach($this->getAvailableProfiles() as $groupId => $profile){
            $arProfiles[$groupId] = $profile['name'].' ('.$groupId.')';
        }
        return $arProfiles;
    }
    public function getAvailableProfiles(){
        $arAvailableProfiles = \CShiptorDeliveryHelper::getShippingMethods();
        $arProfiles = array();
        foreach($arAvailableProfiles as $arItem){
            $arProfiles[$arItem["group"]] = $arItem;
        }
        return $arProfiles;
    }
    public function calculateMargin($price){
        if($this->config["MAIN"]["MARGIN_VALUE"] > 0){
            switch($this->config["MAIN"]["MARGIN_TYPE"]){
                case self::MARGIN_PERCENT:default:
                    $price *= 1 + ($this->config["MAIN"]["MARGIN_VALUE"])/100;
                    break;
                case self::MARGIN_CURRENCY:
                    $price += $this->config["MAIN"]["MARGIN_VALUE"];
                    break;
            }
        }
        $result = array("PRICE" => $this->round($price));
        return $result;
    }
    public function round($price){
        $precision = Config::getRoundingPrecision();
        switch(Config::getRoundingType()){
            case Config::ROUND_TYPE_MATH:
                $price = round($price/$precision) * $precision;
                break;
            case Config::ROUND_TYPE_FLOOR:
                $price = floor($price/$precision) * $precision;
                break;
            case Config::ROUND_TYPE_CEIL:
                $price = ceil($price/$precision) * $precision;
                break;
            case Config::ROUND_TYPE_NONE:default:
                $price = roundEx($price,SALE_VALUE_PRECISION);
        }
        return $price;
    }
    public function getTerms($daysText){
        $strDescription = "";
        if($this->config['MAIN']['SHOW_TERMS'] == "Y" && !empty($daysText)){
            $iAddTerms = intval($this->config['MAIN']['ADD_TERMS']);
            if($iAddTerms > 0){
                $strDescription .= $this->addDaysText($daysText, $iAddTerms);
            }else{
                $strDescription .= $this->addDaysText($daysText, 0);
            }
        }
        return $strDescription;
    }
    private function addDaysText($daysText,$i){
        if(strpos($daysText,"-") !== false){
            $periods = explode("-",$daysText);
            $dStart = intval($periods[0]) + $i;
            $dEnd = intval($periods[1]) + $i;
            $daysText = $dStart."-".$this->getPluralEnumDays($dEnd);
        }else{
            $dStart = intval($daysText) + $i;
            $daysText = $this->getPluralEnumDays($dStart);
        }
        return $daysText;
    }
    private function getPluralEnumDays($number){
        $labels = explode("|",Loc::getMessage("SHIPTOR_WDAYS_PLURALS"));
        $variant = array (2, 0, 1, 1, 1, 2);
        return $number." ".$labels[ ($number%100 > 4 && $number%100 < 20)? 2 : $variant[min($number%10, 5)] ];
    }
    public function getConfigOuter(){
        return $this->config;
    }
    public function execAdminAction(){
        $result = new \Bitrix\Sale\Result();
        \CJSCore::init(array("ajax"));
        $allAvailableProfilesText = Loc::getMessage("SHIPTOR_ADD_ALL_AVAILABLE_PROFILES");
        $ifAllAvailableProfilesText = Loc::getMessage("SHIPTOR_IF_ADD_ALL_AVAILABLE_PROFILES");
        $allAvailableProfilesTitle = Loc::getMessage("SHIPTOR_ADD_ALL_AVAILABLE_PROFILES_TITLE");
        $allAvailableProfilesError = Loc::getMessage("SHIPTOR_ADD_ALL_AVAILABLE_PROFILES_ERROR");
        Asset::getInstance()->addString(<<<JS
            <script type='text/javascript'>
                BX.ready(function(){
                    var eCreateProfileButton = document.querySelector("#tbl_sale_delivery_subservice_result_div .adm-btn-menu"),
                        eCreateAllProfilesButton = (eCreateProfileButton?eCreateProfileButton.parentNode.querySelector(".shd_add_all_profiles"):null);
                    if(!!eCreateProfileButton && !eCreateAllProfilesButton){
                        var eCreateAllProfilesButton = BX.create("a"),
                            currentUrl = location.href;
                        eCreateAllProfilesButton.className = "adm-btn adm-btn-add shd_add_all_profiles";
                        eCreateAllProfilesButton.innerHTML = "{$allAvailableProfilesText}";
                        eCreateAllProfilesButton.setAttribute("title","{$allAvailableProfilesTitle}");
                        eCreateAllProfilesButton.onclick = function(){
                            if(confirm("{$ifAllAvailableProfilesText}")){
                                var ajParams = {
                                    "action": "create_all_profiles",
                                    "parentId": "{$this->id}",
                                    "currency": "{$this->currency}"
                                };
                                BX.ajax.post("/bitrix/tools/shiptor.delivery/ajax/actions.php",ajParams,function(result){
                                    var jsResult = JSON.parse(result);
                                    if(!jsResult.success){
                                        alert("{$allAvailableProfilesError}");
                                    }
                                    location.href = currentUrl;
                                });
                            }
                        };
                        eCreateProfileButton.parentNode.appendChild(eCreateAllProfilesButton);
                    }
                });
            </script>
JS
        );
        return $result;
    }
    public static function createAllProfiles($parentId,$currency){
        $arAvailableProfilesList = \CShiptorDeliveryHelper::getShippingMethods();
        if(empty($arAvailableProfilesList) || !empty($arAvailableProfilesList['ERRORS'])){
            return false;
        }
        foreach($arAvailableProfilesList as $profile){
            $res = Manager::add(array(
                    "CODE" => "",
                    "PARENT_ID" => $parentId,
                    "NAME" => $profile['name'],
                    "ACTIVE" => "N",
                    "SORT" => 100,
                    "DESCRIPTION" => $profile["description"],
                    "CLASS_NAME" => '\Shiptor\Delivery\ProfileHandler',
                    "CURRENCY" => $currency,
                    "LOGOTIP" => \CShiptorDeliveryHelper::getDefaultLogo($profile['courier']),
                    "CONFIG" => array(
                        "MAIN" => array(
                            "CATEGORY" => $profile['category'],
                            "COURIER" => $profile['courier'],
                            "GROUP" => $profile["group"],
                            "NAME" => $profile["name"]
                        )
                    )
                )
            );
            if(!$res->isSuccess()){
                return false;
            }
        }
        return true;
    }
}