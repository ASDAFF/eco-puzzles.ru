<?
$module_id = 'up.boxberrydelivery';   
IncludeModuleLangFile(__FILE__);
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$module_id.'/include.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$module_id.'/classes/general/CModuleOptions.php');


$arStatusId = array();
$arStatusName = array();
$arStatusIdChange = array('0' => '');
$arStatusNameChange = array('0' => GetMessage('BB_NO_CHANGE'));

$arTabs = array(
   array(
      'DIV' => 'edit1',
      'TAB' => GetMessage("edit1_tab"),
      'ICON' => '',
      'TITLE' => GetMessage("edit1_title")
   ), 
   array(
      'DIV' => 'edit20',
      'TAB' => GetMessage("edit20_tab"),
      'ICON' => '',
      'TITLE' => GetMessage("edit20_title")
   )  
);


$arGroups = array(
   'OPTION_150' => array('TITLE' => GetMessage("BB_OPTION_1_TITLE"), 	'TAB' => 0),  
   'OPTION_200' => array('TITLE' => GetMessage("BB_OPTION_2_TITLE"), 	'TAB' => 0),        
   'OPTION_250' => array('TITLE' => GetMessage("BB_OPTION_AUTO_TITLE"), 'TAB' => 0),        
   'OPTION_300' => array('TITLE' => GetMessage("BB_OPTION_3_TITLE"), 	'TAB' => 1),   
);

$arOptions = array(
   'API_TOKEN' => array(
            'GROUP' => 'OPTION_150',
            'TITLE' => GetMessage("BB_API_TOKEN"),
            'TYPE' => 'STRING',
            'DEFAULT' => '',
            'SIZE' => 20,
            'SORT' => '0',
            'REFRESH' => 'N',
      ), 
      'API_URL' => array(
            'GROUP' => 'OPTION_150',
            'TITLE' => GetMessage("BB_API_URL"),
            'TYPE' => 'STRING',
            'DEFAULT' => 'https://api.boxberry.de/json.php',
            'SIZE' => 20,
            'SORT' => '10',
            'REFRESH' => 'N',

      ),  
      'WIDGET_URL' => array(
            'GROUP' => 'OPTION_150',
            'TITLE' => GetMessage("BB_WIDGET_URL"),
            'TYPE' => 'STRING',
            'DEFAULT' => 'https://points.boxberry.de/js/boxberry.js',
            'SIZE' => 20,
            'SORT' => '15',
            'REFRESH' => 'N',

      ),
      'BB_CUSTOM_LINK' => array(
            'GROUP' => 'OPTION_150',
            'TITLE' => GetMessage("BB_CUSTOM_LINK"),
            'TYPE' => 'STRING',
            'DEFAULT' => '',
            'SIZE' => 20,
            'SORT' => '25',
            'REFRESH' => 'N',

      ),
	'BB_ACCOUNT_NUMBER' => array(
		'GROUP' => 'OPTION_150',
		'TITLE' => GetMessage("BB_ACCOUNT_NUMBER"),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => '',
		'SORT' => '70',
		'REFRESH' => 'N',
	),
	'BB_KD_SURCH' => array(
		'GROUP' => 'OPTION_150',
		'TITLE' => GetMessage("BB_KD_SURCH"),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => '',
		'SORT' => '100',
		'REFRESH' => 'N',
	),
	'BB_LOG' => array(
		'GROUP' => 'OPTION_150',
		'TITLE' => GetMessage("BB_LOG"),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => '',
		'SORT' => '1500',
		'REFRESH' => 'N',
	),
      'BB_PVZ' => array(
            'GROUP' => 'OPTION_150',
            'TITLE' => GetMessage("BB_SHOP_PVZ"),
            'TYPE' => 'STRING',
            'DEFAULT' => '',
            'SIZE' => 20,
            'SORT' => '20',
            'REFRESH' => 'N',

      ), 
      'BB_WEIGHT' => array(
            'GROUP' => 'OPTION_150',
            'TITLE' => GetMessage("BB_WEIGHT"),
            'TYPE' => 'STRING',
            'DEFAULT' => '1000',
            'SIZE' => 20,
            'SORT' => '25',
            'REFRESH' => 'N',

      ),
      'BB_FIO' => array(
            'GROUP' => 'OPTION_300',
            'TITLE' => GetMessage("BB_FIO"),
            'TYPE' => 'STRING',
            'DEFAULT' => 'FIO',
            'SIZE' => 20,
            'SORT' => '20',
            'REFRESH' => 'N',
      ),
      'BB_PHONE' => array(
            'GROUP' => 'OPTION_300',
            'TITLE' => GetMessage("BB_PHONE"),
            'TYPE' => 'STRING',
            'DEFAULT' => 'PHONE',
            'SIZE' => 20,
            'SORT' => '120',
            'REFRESH' => 'N',
      ),
      'BB_EMAIL' => array(
            'GROUP' => 'OPTION_300',
            'TITLE' => GetMessage("BB_EMAIL"),
            'TYPE' => 'STRING',
            'DEFAULT' => 'EMAIL',
            'SIZE' => 20,
            'SORT' => '220',
            'REFRESH' => 'N',
      ),
      'BB_ZIP' => array(
            'GROUP' => 'OPTION_300',
            'TITLE' => GetMessage("BB_ZIP"),
            'TYPE' => 'STRING',
            'DEFAULT' => 'ZIP',
            'SIZE' => 20,
            'SORT' => '320',
            'REFRESH' => 'N',
      ),
	'BB_LOCATION' => array(
            'GROUP' => 'OPTION_300',
            'TITLE' => GetMessage("BB_LOCATION"),
            'TYPE' => 'STRING',
            'DEFAULT' => 'LOCATION',
            'SIZE' => 20,
            'SORT' => '420',
            'REFRESH' => 'N',
	),
	'BB_ADDRESS' => array(
            'GROUP' => 'OPTION_300',
            'TITLE' => GetMessage("BB_ADDRESS"),
            'TYPE' => 'STRING',
            'DEFAULT' => 'ADDRESS',
            'SIZE' => 20,
            'SORT' => '520',
            'REFRESH' => 'N',
	),
	'BB_JUR_ADDRESS' => array(
            'GROUP' => 'OPTION_300',
            'TITLE' => GetMessage("BB_JUR_ADDRESS"),
            'TYPE' => 'STRING',
            'DEFAULT' => 'JUR_ADDRESS',
            'SIZE' => 20,
            'SORT' => '620',
            'REFRESH' => 'N',
	),
	'BB_INN' => array(
            'GROUP' => 'OPTION_300',
            'TITLE' => GetMessage("BB_INN"),
            'TYPE' => 'STRING',
            'DEFAULT' => 'INN',
            'SIZE' => 20,
            'SORT' => '720',
            'REFRESH' => 'N',
	),
	'BB_KPP' => array(
            'GROUP' => 'OPTION_300',
            'TITLE' => GetMessage("BB_KPP"),
            'TYPE' => 'STRING',
            'DEFAULT' => 'KPP',
            'SIZE' => 20,
            'SORT' => '820',
            'REFRESH' => 'N',
	),
	'BB_CONTACT_PERSON' => array(
		'GROUP' => 'OPTION_300',
		'TITLE' => GetMessage("BB_CONTACT_PERSON"),
		'TYPE' => 'STRING',
		'DEFAULT' => 'CONTACT_PERSON',
		'SIZE' => 20,
		'SORT' => '920',
		'REFRESH' => 'N',
      ),
      'BB_COMPANY_NAME' => array(
		'GROUP' => 'OPTION_300',
		'TITLE' => GetMessage("BB_COMPANY"),
		'TYPE' => 'STRING',
		'DEFAULT' => 'COMPANY_NAME',
		'SIZE' => 20,
		'SORT' => '940',
		'REFRESH' => 'N',
	),
	'BB_PAID_PERSON_PH' => array(
		'GROUP' => 'OPTION_300',
		'TITLE' => GetMessage("BB_PAID_PERSON_PH"),
		'TYPE' => 'STRING',
		'DEFAULT' => '1',
		'SIZE' => 20,
		'SORT' => '1000',
		'REFRESH' => 'N',
	),
	'BB_PAID_PERSON_JUR' => array(
		'GROUP' => 'OPTION_300',
		'TITLE' => GetMessage("BB_PAID_PERSON_JUR"),
		'TYPE' => 'STRING',
		'DEFAULT' => '2',
		'SIZE' => 20,
		'SORT' => '1020',
		'REFRESH' => 'N',
	),
);

$RIGHT = $APPLICATION->GetGroupRight($module_id);

if($RIGHT != "D"){
    $opt = new CModuleOptions($module_id, $arTabs, $arGroups, $arOptions, true);
    $opt->ShowHTML();    
}
?>