<?
use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Application,
	Bitrix\Main\Web\Cookie,
	Bitrix\Main\Context,
	Bitrix\Main\UserConsent\Internals\AgreementTable,
	Bitrix\Main\UserConsent\Agreement;

\Bitrix\Main\Loader::IncludeModule("iblock");
\Bitrix\Main\Loader::IncludeModule("skyweb24.popuppro");

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
Loc::loadMessages(__FILE__);

class Skyweb24PopupPro extends \CBitrixComponent{

	public function onPrepareComponentParams($params){
		if(empty($params['ID_POPUP'])){
			$params['ID_POPUP']=1;
		}
		$params['REFERER']=$_SERVER['HTTP_REFERER'];
		return $params;
	}
	
	private function setPersonalize($res){
		$tmpReplace=Skyweb24\Popuppro\Tools::getPersonalizationValues();
		$newRes=[];
		foreach($res as $keyRow=>$nextRow){
			foreach($tmpReplace as $keyRep=>$nextRep){
				//if(!empty($nextRep)){
					$nextRow=str_replace('#'.$keyRep.'#', $nextRep, $nextRow);
				//}
			}
			$newRes[$keyRow]=$nextRow;
		}
		return $newRes;
	}

	public function executeComponent(){
		$context = Application::getInstance()->getContext();
		$responce = $context->getResponse();
		$request = $context->getRequest();
			
		global $APPLICATION;
		if(empty($this->arParams["ID_POPUP"])){
			return 'error id popup!';
		}
		if(!empty($this->arParams['MODE']) && $this->arParams['MODE']=='GET_PATH'){
			echo $this->GetPath().'/templates/'.$this->getTemplateName();
			die();
		}
		$this->arResult=array(
			'TITLE'=>'',
			'SUBTITLE'=>'',
			'CONTENT'=>'',
			'LINK_TEXT'=>'',
			'LINK_HREF'=>'http://skyweb24.ru',
			'IMG_1_SRC'=>'/bitrix/themes/.default/skyweb24.popuppro/preload/gift_1.jpg',
			'IMG_2_SRC'=>'/bitrix/themes/.default/skyweb24.popuppro/preload/gift_1.jpg',
			'LINK_VIDEO'=>'0KCGXCyM_K8',
			'VIDEO_SIMILAR'=>'VIDEO_SIMILAR',
			'VIDEO_AUTOPLAY'=>'VIDEO_AUTOPLAY',
			'BUTTON_TEXT_Y'=>'BUTTON_TEXT_Y',
			'BUTTON_TEXT_N'=>'BUTTON_TEXT_N',
			'COLOR_BG'=>'COLOR_BG',
			'ID_VK'=>'ID_VK',
			'ID_FB'=>'ID_FB',
			'ID_INST'=>'ID_INST',
			'ID_ODNKL'=>'ID_ODNKL',
			'TYPE_VIEW'=>'TYPE_VIEW',
			'BUTTON_TEXT'=>'BUTTON_TEXT',
			'VK'=>'VK',
			'FB'=>'FB',
			'TEXTAREA'=>'TEXTAREA',
			'DISCOUNT_MASK'=>'',
			'EMAIL_SHOW'=>'Y',
			'EMAIL_REQUIRED'=>'Y',
			'EMAIL_TITLE'=>'',
			'EMAIL_PLACEHOLDER'=>'',
			'GOOGLE_FONT'=>'',
			'NAME_SHOW'=>'Y',
			'NAME_REQUIRED'=>'Y',
			'NAME_TITLE'=>'',
			'NAME_PLACEHOLDER'=>'',
			
			'LASTNAME_SHOW'=>'Y',
			'LASTNAME_REQUIRED'=>'Y',
			'LASTNAME_TITLE'=>'',
			'LASTNAME_PLACEHOLDER'=>'',

			'PHONE_SHOW'=>'Y',
			'PHONE_REQUIRED'=>'Y',
			'PHONE_TITLE'=>'',
			'PHONE_PLACEHOLDER'=>'',

			'DESCRIPTION_SHOW'=>'Y',
			'DESCRIPTION_REQUIRED'=>'Y',
			'DESCRIPTION_TITLE'=>'',
			'DESCRIPTION_PLACEHOLDER'=>'',
			'CLOSE_TEXTAREA'=>'',
			'CLOSE_TEXTBOX'=>''
		);

		if(!empty($this->arParams['MODE']) && $this->arParams['MODE']=='TEMPLATE'){
			$this->arResult=array(
				'TITLE'=>'#TITLE#',
				'SUBTITLE'=>'#SUBTITLE#',
				'CONTENT'=>'#CONTENT#',
				'LINK_TEXT'=>'#LINK_TEXT#',
				'LINK_HREF'=>'#LINK_HREF#',
				'IMG_1_SRC'=>'#IMG_1_SRC#',
				'IMG_2_SRC'=>'#IMG_2_SRC#',
				'LINK_VIDEO'=>'#LINK_VIDEO#',
				'VIDEO_SIMILAR'=>'#VIDEO_SIMILAR#',
				'VIDEO_AUTOPLAY'=>'#VIDEO_AUTOPLAY#',
				'COLOR_BG'=>'#COLOR_BG#',
				'ID_VK'=>'#ID_VK#',
				'ID_FB'=>'#ID_FB#',
				'ID_INST'=>'#ID_INST#',
				'ID_ODNKL'=>'#ID_ODNKL#',
				'TYPE_VIEW'=>'#TYPE_VIEW#',
				'BUTTON_TEXT'=>'#BUTTON_TEXT#',
				'COLLECT'=>'#COLLECT#',
				'VK'=>'#VK#',
				'FB'=>'#FB#',
				'TEXTAREA'=>'#TEXTAREA#',
				'EMAIL_SHOW'=>'#EMAIL_SHOW#',
				'EMAIL_REQUIRED'=>'#EMAIL_REQUIRED#',
				'EMAIL_TITLE'=>'#EMAIL_TITLE#',
				'EMAIL_PLACEHOLDER'=>'#EMAIL_PLACEHOLDER#',
				'GOOGLE_FONT'=>'#GOOGLE_FONT#',
				'NAME_SHOW'=>'#NAME_SHOW#',
				'NAME_REQUIRED'=>'#NAME_REQUIRED#',
				'NAME_TITLE'=>'#NAME_TITLE#',
				'NAME_PLACEHOLDER'=>'#NAME_PLACEHOLDER#',
				
				'LASTNAME_SHOW'=>'#LASTNAME_SHOW#',
				'LASTNAME_REQUIRED'=>'#LASTNAME_REQUIRED#',
				'LASTNAME_TITLE'=>'#LASTNAME_TITLE#',
				'LASTNAME_PLACEHOLDER'=>'#LASTNAME_PLACEHOLDER#',

				'PHONE_SHOW'=>'#PHONE_SHOW#',
				'PHONE_REQUIRED'=>'#PHONE_REQUIRED#',
				'PHONE_TITLE'=>'#PHONE_TITLE#',
				'PHONE_PLACEHOLDER'=>'#PHONE_PLACEHOLDER#',

				'USE_CONSENT_SHOW'=>'#USE_CONSENT_SHOW#',
				'CONSENT_LIST'=>'#CONSENT_LIST#',

				'BUTTON_TEXT_Y'=>'#BUTTON_TEXT_Y#',
				'BUTTON_TEXT_N'=>'#BUTTON_TEXT_N#',
				'DISCOUNT_MASK'=>'#DISCOUNT_MASK#',
				'DESCRIPTION_SHOW'=>'#DESCRIPTION_SHOW#',
				'DESCRIPTION_REQUIRED'=>'#DESCRIPTION_REQUIRED#',
				'DESCRIPTION_TITLE'=>'#DESCRIPTION_TITLE#',
				'DESCRIPTION_PLACEHOLDER'=>'#DESCRIPTION_PLACEHOLDER#',
				'SOC_VK'=>'#SOC_VK#',
				'SOC_OD'=>'#SOC_OD#',
				'SOC_FB'=>'#SOC_FB#',
				'SOC_TW'=>'#SOC_TW#',
				'SOC_GP'=>'#SOC_GP#',
				'SOC_MR'=>'#SOC_MR#',
				
				'TIMER'=>'#timer_enable#',
				'TIMER_TEXT'=>'#timer_text#',
				'TIMER_DATE'=>'#timer_date#',
				'TIMER_LEFT'=>'#timer_left#',
				'TIMER_RIGHT'=>'#timer_right#',
				'TIMER_TOP'=>'#timer_top#',
				'TIMER_BOTTOM'=>'#timer_bottom#',
				'CLOSE_TEXTAREA'=>'#CLOSE_TEXTAREA#',
				'CLOSE_TEXTBOX'=>'Y'
			);
		}else{
			$popup=new popuppro;
			$tmpRes=$popup->getComponentResult($this->arParams['ID_POPUP']);
			$tmpRes=$this->setPersonalize($tmpRes);
			$popupSetting=$popup->getSetting($this->arParams['ID_POPUP']);
			if($popupSetting['view']['type']=='contact'||$popupSetting['view']['type']=='discount'){
				$contactArr=array('NAME'=>'', 'EMAIL'=>'','LASTNAME'=>'', 'PHONE'=>'', 'DESCRIPTION'=>'');
				global $USER;
				if($USER->IsAuthorized()){
					$rsUser = CUser::GetByID($USER->GetID());
					$arUser = $rsUser->Fetch();
					$contactArr['EMAIL']=$arUser['EMAIL'];
					$contactArr['NAME']=$arUser['NAME'];
					$contactArr['LASTNAME']=$arUser['LAST_NAME'];
					$contactArr['PHONE']=$arUser['PERSONAL_PHONE'];
				}
				$errors=array();
				$cenderData=false;
				$forSendDataArr=array('NAME_FORM'=>$popupSetting['row']['name']);
				foreach($contactArr as $keyContact=>$nextContact){
					if(!empty($tmpRes[$keyContact.'_SHOW']) && $tmpRes[$keyContact.'_SHOW']=='Y'){
						$tmpRes[$keyContact]=$nextContact;
						$reqValue = $request->get($keyContact);
						if(isset($reqValue)){
							$cenderData=true;
							$tmpRes[$keyContact]=$reqValue;
							if(empty($reqValue) && $tmpRes[$keyContact.'_REQUIRED']=='Y'){
								$errors[$keyContact]=$tmpRes[$keyContact.'_TITLE'];
								$tmpRes[$keyContact]='';
							}else{
								if($keyContact=='EMAIL' && !filter_var($reqValue, FILTER_VALIDATE_EMAIL)){
									$errors[$keyContact]=$tmpRes[$keyContact.'_TITLE'];
								}else{
									if(LANG_CHARSET=='windows-1251'){
										$reqValue=mb_convert_encoding($reqValue, "windows-1251", "utf-8");
										$tmpRes[$keyContact]=$reqValue;
									}
									$forSendDataArr[$keyContact]=$reqValue;
								}
							}
						}
					}
				}
				if(count($errors)>0){
					$tmpRes['ERRORS']=$errors;
				}elseif($cenderData){
					//send email, write to iblock, add to marketing list
					if(!empty($popupSetting['contact'])){

						$postTitle=array('NAME_TITLE', 'EMAIL_TITLE', 'PHONE_TITLE', 'DESCRIPTION_TITLE','LASTNAME_TITLE');
						foreach($postTitle as $nextTitle){
							$forSendDataArr[$nextTitle]='';
							if(!empty($popupSetting['view']['props'][$nextTitle])){
								$forSendDataArr[$nextTitle]=$popupSetting['view']['props'][$nextTitle];
							}
						}
						
						if(!empty($popupSetting['row']['cont_posttemplate'])){
							CEvent::Send("SKYWEB24_POPUPPRO_SENDER", SITE_ID, $forSendDataArr, 'Y', $popupSetting['row']['cont_posttemplate']);
						}
						if(!empty($popupSetting['contact']['emailList']) && $popupSetting['contact']['emailList']=='Y'){
							if(!empty($forSendDataArr['EMAIL'])){
								$popup->insertToMailList($forSendDataArr['EMAIL'], $forSendDataArr['NAME'], $this->arParams['ID_POPUP']);
							}
							if(!empty($forSendDataArr['PHONE'])){
								$popup->insertToMailList(preg_replace("/[^0-9]/", '', $forSendDataArr['PHONE']), $forSendDataArr['NAME'], $this->arParams['ID_POPUP']);
							}
						}
						if(!empty($popupSetting['contact']['iblock'])){
							$insertArr=array(
								'IBLOCK_ID'=>$popupSetting['contact']['iblock'],
								'NAME'=>array_shift($forSendDataArr),
								'PREVIEW_TEXT'=>$tmpInfo
							);
							foreach($forSendDataArr as $keyData=>$nextData){
								if(!empty($tmpRes[$keyData.'_TITLE'])){
									$insertArr['PREVIEW_TEXT'].=$tmpRes[$keyData.'_TITLE'].' - '.$nextData.PHP_EOL;
									$propId=Skyweb24\Popuppro\Tools::returnPropId($popupSetting['contact']['iblock'], $keyData, $tmpRes[$keyData.'_TITLE']);
									$insertArr['PROPERTY_VALUES'][$propId]=$nextData;
								}
							}
							$el = new CIBlockElement;
							$el->Add($insertArr);
						}
						if($tmpRes['USE_CONSENT_SHOW']=='Y'){
							$dataArr=array();
							if(!empty($this->arParams['REFERER'])){
								$dataArr['URL']=$this->arParams['REFERER'];
							}
							if (class_exists('Bitrix\Main\UserConsent\Agreement')){
								\Bitrix\Main\UserConsent\Consent::addByContext(
									$popupSetting['view']['props']['CONSENT_LIST'],
									'skyweb24/popuppro',
									$popupSetting['row']['id'],
									$dataArr
								);
							}
						}
						global $USER;
						
						if(!empty($popupSetting['contact']['register'])&&$popupSetting['contact']['register']=='Y'&&!empty($forSendDataArr['EMAIL'])&&!$USER->IsAuthorized()){
							$tmpUsers=CUser::GetList(($by="ID"),($order="desc"),array('EMAIL'=>$forSendDataArr['EMAIL']));
							$tmpRes['REG_RES'][]=!$tmpUsers->Fetch();
							if(!$tmpUsers->Fetch()){
								$res=$USER->SimpleRegister($forSendDataArr['EMAIL']);
							}
						}
					}
					$popup->setStatistic($popupSetting['row']['id'], 1, 'stat_action');
					$tmpRes['SUCCESS']='Y';

					//set cookies so as not to show the banner to users who filled out the form
					/*$cookie = new Cookie("skyweb24PopupFilling_".$this->arParams['ID_POPUP'], 'Y', time()+864000000);
					$cookie->setDomain($context->getServer()->getHttpHost());
					$cookie->setHttpOnly(false);
					$responce->addCookie($cookie);
					$context->getResponse()->flush("");*/
					$APPLICATION->set_cookie("skyweb24PopupFilling_".$this->arParams['ID_POPUP'], 'Y', time()+864000000, "/");
					
				}
			}elseif($popupSetting['view']['type']=='coupon'){
				
				foreach($popupSetting['view']['props'] as $key=>$prop){
					if($key!='IMG_1_SRC'&&$key!='EMAIL_PLACEHOLDER')
					$tmpRes[$key]=$prop;
				}
			}elseif($popupSetting['view']['type']=='age'){
				$reqValue = $request->get('checked');
				if(isset($reqValue)&&$reqValue=='Y'){
					/*$cookie = new Cookie("skyweb24PopupFilling_".$this->arParams['ID_POPUP'], 'Y', time()+864000000);
					$cookie->setDomain($context->getServer()->getHttpHost());
					$cookie->setHttpOnly(false);
					$responce->addCookie($cookie);
					$context->getResponse()->flush("");*/
					$APPLICATION->set_cookie("skyweb24PopupFilling_".$this->arParams['ID_POPUP'], 'Y', time()+864000000, "/");
					die();
				}
			}
			$this->arResult=$tmpRes;
		}
		$timer_array=array('banner','video','action','contact','html','coupon','roulette','discount');
			if(in_array($popupSetting['view']['type'],$timer_array) && !empty($popupSetting['timer']['date'])){
				$this->arResult['TIMER']=$popupSetting['timer']['enabled'];
				$this->arResult['TIMER_TEXT']=$popupSetting['timer']['text'];
				if($this->arResult['TIMER']=='Y'){
				$format = 'd.m.Y H:i:s';
				$unixtime=DateTime::createFromFormat($format, $popupSetting['timer']['date']);
				$nowtime=time();
				$nowtime=date_create();
				$unixtime=$nowtime->diff($unixtime);
				$unixtime=$unixtime->format('%a:%H:%I:%S');
				$this->arResult['TIMER_DATE']=$unixtime;
				$this->arResult['TIMER_LEFT']=$popupSetting['timer']['left'];
				$this->arResult['TIMER_RIGHT']=$popupSetting['timer']['right'];
				$this->arResult['TIMER_TOP']=$popupSetting['timer']['top'];
				$this->arResult['TIMER_BOTTOM']=$popupSetting['timer']['bottom'];
				}
			}
		if($popupSetting['view']['type']=='roulette'){
			$this->arResult['ELEMENTS']=$popupSetting['roulett'];
			unset($this->arResult['ELEMENTS']['count']);
			$this->arResult['ELEMENTS_COUNT']=$popupSetting['roulett']['count'];
		}
		//consent for
		if((!empty($popupSetting['view']['type']) && ($popupSetting['view']['type']=='contact'||$popupSetting['view']['type']=='discount')) || $this->arParams['MODE']=='TEMPLATE'){
			if(!empty($popup)){
				$agreements=$popup->getAgreements(array('button_caption'=>$popupSetting['view']['props']['BUTTON_TEXT']));
				$this->arResult['AGREEMENTS']=$agreements;
				if(!empty($popupSetting['view']['props']['CONSENT_LIST'])){
					$this->arResult['CONSENT_LIST']=$agreements[$popupSetting['view']['props']['CONSENT_LIST']];
					$this->arResult['CONSENT_ID']=$popupSetting['view']['props']['CONSENT_LIST'];
				}
			}else{
				$popup=new popuppro;
				$agreements=$popup->getAgreements(array('button_caption'=>'#BUTTON_TEXT#'));
				$this->arResult['AGREEMENTS']=$agreements;
				$this->arResult['CONSENT_ID']=1;
			}
		}
		//e. o. consent for
		
		//antispam
		$_SESSION['skyweb24_popup'.$this->arParams['ID_POPUP']]=time();
		$cookie = new Cookie("skyweb24Popup_".$this->arParams['ID_POPUP'], time(), time()+300);
		$cookie->setDomain($context->getServer()->getHttpHost());
		$cookie->setHttpOnly(false);
		$responce->addCookie($cookie);
		$context->getResponse()->flush("");
		
		$this->IncludeComponentTemplate($componentPage);
	}
}
