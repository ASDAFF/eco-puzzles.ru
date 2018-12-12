<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
use Bitrix\Main\Application,
Bitrix\Main\Web\Cookie;
\Bitrix\Main\Loader::IncludeModule("skyweb24.popuppro");
$popup=new popuppro;
$setting=$popup->getSetting($_REQUEST['idPopup']);
define("NO_KEEP_STATISTIC", true);
if($setting['row']['active']=='Y'){
	if(!check_bitrix_sessid()){
		die("ACCESS_DENIED");
	}else{
		$context = Application::getInstance()->getContext();
		$responce = $context->getResponse();
		//$testCookie=$context->getRequest()->getCookie('skyweb24Popup_'.$_REQUEST['idPopup']);
		//$testCookie=(int) $testCookie;
		////$cookieTime=time()-$testCookie;
		//if($cookieTime>300){
		//	echo 'error';
		//}else{
			$res='';
			$email = '';
			if(isset($_REQUEST['email']))
				$email=$_REQUEST['email'];
			if(isset($_REQUEST['email'])&&isset($_REQUEST['idPopup'])&&$_REQUEST['addtotable']=='Y'){
				if($_REQUEST['unique']=='Y'){
					if($popup->searchinMailList($email,$_REQUEST['idPopup'])){
						global $USER;
						if(!$USER->IsAuthorized()){
							$user = CUser::GetList(($by="id"),($order="desc"),array('EMAIL'=>$email));
							if($r=$user->Fetch()){
								$USER->Authorize($r['ID']);
							}else{
								$user = new CUser;
								$result=$USER->SimpleRegister($email);
								$fields = Array(
									"NAME"              => urldecode($_REQUEST['NAME']),
									"LAST_NAME"         => urldecode($_REQUEST['LAST_NAME']),
									"PERSONAL_PHONE"    => urldecode($_REQUEST['PHONE']),
								);
								$user->Update($USER->GetID(),$fields);
							}
						}
						$userID=$USER->GetID();
						CUser::SetUserGroup($userID, array_merge(CUser::GetUserGroup($userID), array($setting['view']['props']['USER_GROUP'])));
						$res = $popup->getCoupon($_REQUEST['id'],'infinite',$email,$_REQUEST['idPopup'],'',$_REQUEST['MASK']);
						$popup->insertToMailList($email,'',$_REQUEST['idPopup']);
						$cookie = new Cookie("skyweb24PopupFilling_".$_REQUEST['idPopup'], 'Y', time()+864000000);
						$cookie->setDomain($context->getServer()->getHttpHost());
						$cookie->setHttpOnly(false);
						$responce->addCookie($cookie);
						$context->getResponse()->flush("");
						echo $res;
					}else{
						echo 'not_unique';
					}
				}
			}
		
	}
}else{
	echo 'not active window';
}
die();
