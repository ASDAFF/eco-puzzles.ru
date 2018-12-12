<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<?
	if(isset($_GET["USER_PASSWORD"]) &&
	   isset($_GET["USER_PASSWORD_CONFIRM"]) &&
	   isset($_GET["USER_STREET"]) &&
	   isset($_GET["USER_MOBILE"]) &&
	   isset($_GET["USER_CITY"]) &&
	   isset($_GET["USER_ZIP"]) &&
	   isset($_GET["EMAIL"]) &&
	   isset($_GET["FIO"])
	){
		global $USER;
		$userID = $USER->GetID();
		if($userID){
			$NAME            = explode(" ", htmlspecialchars($_GET["FIO"]));
			$EMAIL           = htmlspecialchars($_GET["EMAIL"]);
			$PASSWORD        = addslashes($_GET["USER_PASSWORD"]);
			$REPASSWORD      = addslashes($_GET["USER_PASSWORD_CONFIRM"]);
			$PERSONAL_STREET = htmlspecialchars($_GET["USER_STREET"]);
			$PERSONAL_MOBILE = htmlspecialchars($_GET["USER_MOBILE"]);
			$PERSONAL_CITY   = htmlspecialchars($_GET["USER_CITY"]);
			$PERSONAL_ZIP    = htmlspecialchars($_GET["USER_ZIP"]);
			
			$user = new CUser;
			$fields = Array(
			  "NAME"              => BX_UTF === true ? $NAME[1] : iconv("UTF-8","windows-1251//IGNORE", $NAME[1]),
			  "SECOND_NAME"       => BX_UTF === true ? $NAME[2] : iconv("UTF-8","windows-1251//IGNORE", $NAME[2]),
			  "LAST_NAME"         => BX_UTF === true ? $NAME[0] : iconv("UTF-8","windows-1251//IGNORE", $NAME[0]),
			  "PERSONAL_STREET"   => BX_UTF === true ? $PERSONAL_STREET : iconv("UTF-8","windows-1251//IGNORE", $PERSONAL_STREET),
			  "PERSONAL_CITY"	  => BX_UTF === true ? $PERSONAL_CITY : iconv("UTF-8","windows-1251//IGNORE", $PERSONAL_CITY),
			  "PERSONAL_ZIP"      => BX_UTF === true ? $PERSONAL_ZIP : iconv("UTF-8","windows-1251//IGNORE", $PERSONAL_ZIP),
			  "PERSONAL_MOBILE"   => BX_UTF === true ? $PERSONAL_MOBILE : iconv("UTF-8","windows-1251//IGNORE", $PERSONAL_MOBILE),			  
			  "EMAIL"             => $EMAIL,
			  "PASSWORD"          => $PASSWORD,
			  "CONFIRM_PASSWORD"  => $REPASSWORD
			);

			if(empty($PASSWORD)){
				unset($fields["PASSWORD"]);
				unset($fields["REPASSWORD"]);
			}

			if(!$user->Update($userID, $fields)){
				$result = array(
					"message" => strip_tags($user->LAST_ERROR),
					"heading" => "������",
					"reload" => false
				);
			}else{
				$result = array(
					"message" => "���������� ������� ���������",
					"heading" => "���������",
					"reload" => true
				);
			}
		}else{
			$result = array(
				"message" => "��������� �����������",
				"heading" => "������",
				"reload" => false
			);
		}
	
	}else{
		$result = array(
			"message" => "������ �������� �����",
			"heading" => "������",
			"reload" => false
		);
	}

	echo \Bitrix\Main\Web\Json::encode($result);

?>

