<?

$arOptsPhisical = array(
    "FIO"=>array(
        array(
            "TYPE"=>"PROPERTY",
             "VALUE"=>"1"
        ),     
    ), 
    "PHONE"=>array(
        array(
            "TYPE"=>"PROPERTY",
            "VALUE"=>"3"
        ),    
    ),
    "EMAIL"=>array(
        array(
            "TYPE"=>"PROPERTY",
            "VALUE"=>"2"
        ),    
    ),
    "ZIP"=>array(
        array(
            "TYPE"=>"PROPERTY",
            "VALUE"=>"4"
        ),    
    ),
    "LOCATION"=>array(
        array(
            "TYPE"=>"PROPERTY",
            "VALUE"=>"6"
        ),    
    ),
    "ADDRESS"=> array(
        array(
            "TYPE"=>"PROPERTY",
            "VALUE"=>"7"
        ),    
    ),
);

$arOptsUridical = array(
    "FIO_2"=>array(
        array(
            "TYPE"=>"PROPERTY",
            "VALUE"=>"12"
        ),     
    ),
    "PHONE"=>array(
        array(
            "TYPE"=>"PROPERTY",
            "VALUE"=>"14"
        ),    
    ),
    "EMAIL"=>array(
        array(
            "TYPE"=>"PROPERTY",
            "VALUE"=>"13"
        ),    
    ),
    "COMPANY_NAME"=>array(
        array(
            "TYPE"=>"PROPERTY",
            "VALUE"=>"8"
        ),    
    ),
    "JURIDICAL_ADDRESS"=>array(
        array(
            "TYPE"=>"PROPERTY",
            "VALUE"=>"9"
        ),    
    ),
    "ZIP"=>array(
        array(
            "TYPE"=>"PROPERTY",
            "VALUE"=>"16"
        ),    
    ),
    "LOCATION"=>array(
        array(
            "TYPE"=>"PROPERTY",
            "VALUE"=>"18"
        ),    
    ),
    "ADDRESS"=>array(
        array(
            "TYPE"=>"PROPERTY",
            "VALUE"=>"19"
        ),    
    ),
    "INN"=>array(
        array(
            "TYPE"=>"PROPERTY",
            "VALUE"=>"10"
        ),    
    ),
    "KPP"=>array(
        array(
            "TYPE"=>"PROPERTY",
            "VALUE"=>"11"
        ),    
    ),
);


$arOptFields = array('1' => $arOptsPhisical, '2' => $arOptsUridical);
$arAllowedDeliveries = array('boxberry:PVZ', 'boxberry:PVZ_COD', 'boxberry:KD', 'boxberry:KD_COD');

global $up_boxberrydelivery_default_option ;

$up_boxberrydelivery_default_option  = array(
    'BB_API_TOKEN' 				=> '',
    'SHOP_PVZ' 					=> '',
    'OPTIONS' 					=> $arOptFields,
    'EXCLUDE_STATUSES' 			=> '',
    'SET_ORDER_PARAMETERS' 		=> 'N',             
    'ALLOWED_DELIVERIES' 		=> $arAllowedDeliveries,             
    'ALLOW_SHIPPING_STATUS'	 	=> '',             
);
?>