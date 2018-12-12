<?
use \Bitrix\Main\Application,
	Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\UserConsent\Internals\AgreementTable,
	Bitrix\Main\UserConsent\Agreement;
	
Loc::loadMessages(__FILE__);
class popupproEdit extends popuppro{
	
	private function getPostTemplate(){
		$tmpSettings=$this->getSetting($this->idPopup);
		$rsET = CEventType::GetByID("SKYWEB24_POPUPPRO_SENDER", LANGUAGE_ID);
		if(!$arET = $rsET->Fetch()){
			$et = new CEventType;
			$et->Add(array(
				"LID" => 'ru',
				"EVENT_NAME" => "SKYWEB24_POPUPPRO_SENDER",
				"NAME" => GetMessage("skyweb24.popuppro_EVENT_TYPE_NAME"),
				"DESCRIPTION" => 
					'#EMAIL# - mail
					#NAME# - name
					#PHONE# - phone
					#DESCRIPTION# - description
					#EMAIL_TITLE# - email_title
					#NAME_TITLE# - name_title
					#PHONE_TITLE# - phone_title
					#DESCRIPTION_TITLE# - description_title'
			));
			$tmpSettings['row']['cont_posttemplate']='';
		}
		if(empty($tmpSettings['row']['cont_posttemplate'])){
			$rsSites = CSite::GetList($by="sort", $order="desc");
			$lids=array();
			while ($arSite = $rsSites->Fetch()){
				$lids[]=$arSite['LID'];
			}
			$emess = new CEventMessage;
			$tmpSettings['row']['cont_posttemplate']=$emess->Add(array(
				'EVENT_NAME'=>'SKYWEB24_POPUPPRO_SENDER',
				'ACTIVE'=>'Y',
				'LID'=>$lids,
				'EMAIL_FROM'=>'#DEFAULT_EMAIL_FROM#',
				'EMAIL_TO'=>'#DEFAULT_EMAIL_FROM#',
				'SUBJECT'=>GetMessage("skyweb24.popuppro_POST_SEND").' #NAME_FORM#',
				'BODY_TYPE'=>'text',
				'MESSAGE'=>'
#EMAIL_TITLE# - #EMAIL#
#NAME_TITLE# - #NAME# 
#PHONE_TITLE# - #PHONE#
#DESCRIPTION_TITLE# - #DESCRIPTION#
'
			));
		}
		return $tmpSettings['row']['cont_posttemplate'];
	}
	
	public function editPopup($request){		
		$settings=array('view'=>array(), 'condition'=>array());
		$viewStructure=$this->getTemplates();
		$viewStructure=$viewStructure[$request->getPost("type")];
		$settings['view']['type']=$request->getPost("type");
		$settings['view']['color_style']=$request->getPost("color_style");
		$settings['view']['template']=$request->getPost("template");
		foreach($viewStructure as $nextViewEl){
			if($nextViewEl['template']==$settings['view']['template']){
				break;
			}
		}
		$viewStructure=$nextViewEl['props'];
		foreach($viewStructure as $keyNextViewEl=>$nextViewEl){
			$tmpPost=$request->getPost($keyNextViewEl);
			if($keyNextViewEl=='EMAIL_TEMPLATE'){
				if($request->getPost('id_popup')=='new'){
					$obTemplate = new CEventMessage;
					$template['LID']=array();
					$sites=Bitrix\Main\SiteTable::getList();
					while($s=$sites->Fetch())
						$template['LID'][]=$s['LID'];
					$template['EVENT_NAME']="SKYWEB24_POPUPPRO_SEND_COUPON";
					$template['ACTIVE']='Y';
					$template['EMAIL_FROM']='#DEFAULT_EMAIL_FROM#';
					$template['EMAIL_TO']='#EMAIL#';
					$template['SUBJECT']=GetMessage("skyweb24.popuppro_TEMPLATE_SUBJECT");
					$template['BODY_TYPE']='html';
					$template['MESSAGE']=GetMessage("skyweb24.popuppro_TEMPLATE_MESSAGE");
					$tmpPost=$obTemplate->Add($template);
				}
			}
			
			if($keyNextViewEl=='MAIL_TEMPLATE'){
				if($request->getPost('id_popup')=='new'){					
					$keyPost='SKYWEB24_POPUPPRO_ROULETTE_SEND';
					$rsET = CEventType::GetList(array('EVENT_NAME'=>$keyPost));
					if(!$arET = $rsET->Fetch()){
						$obEventType = new CEventType;
						$obEventType->Add(array(
							'LID'=>'ru',
							'EVENT_NAME'=>$keyPost,
							"NAME"=>GetMessage("skyweb24.popuppro_EVENT_NAME_ROULETTE"),
							"DESCRIPTION"=>str_ireplace("\t", '',GetMessage("skyweb24.popuppro_EVENT_DESCRIPTION_ROULETTE"))
						));
					}	
					$obTemplate = new CEventMessage;
					$template['LID']=array();
					$sites=Bitrix\Main\SiteTable::getList();
					while($s=$sites->Fetch())
						$template['LID'][]=$s['LID'];
					$template['EVENT_NAME']=$keyPost;
					$template['ACTIVE']='Y';
					$template['EMAIL_FROM']='#DEFAULT_EMAIL_FROM#';
					$template['EMAIL_TO']='#EMAIL#';
					$template['SUBJECT']=GetMessage("skyweb24.popuppro_TEMPLATE_SUBJECT_ROULETTE");
					$template['BODY_TYPE']='html';
					$template['MESSAGE']=GetMessage("skyweb24.popuppro_TEMPLATE_MESSAGE_ROULETTE");
					$tmpPost=$obTemplate->Add($template);
				}
			}
			if($keyNextViewEl=='EMAIL_TEMPLATE_D'){
				if($request->getPost('id_popup')=='new'){					
					$keyPost='SKYWEB24_POPUPPRO_DISCOUNT_SEND';
					$rsET = CEventType::GetList(array('EVENT_NAME'=>$keyPost));
					if(!$arET = $rsET->Fetch()){
						$obEventType = new CEventType;
						$obEventType->Add(array(
							'LID'=>'ru',
							'EVENT_NAME'=>$keyPost,
							"NAME"=>GetMessage("skyweb24.popuppro_EVENT_NAME_DISCOUNT"),
							"DESCRIPTION"=>str_ireplace("\t", '',GetMessage("skyweb24.popuppro_EVENT_DESCRIPTION_DISCOUNT"))
						));
					}	
					$obTemplate = new CEventMessage;
					$template['LID']=array();
					$sites=Bitrix\Main\SiteTable::getList();
					while($s=$sites->Fetch())
						$template['LID'][]=$s['LID'];
					$template['EVENT_NAME']=$keyPost;
					$template['ACTIVE']='Y';
					$template['EMAIL_FROM']='#DEFAULT_EMAIL_FROM#';
					$template['EMAIL_TO']='#EMAIL#';
					$template['SUBJECT']=GetMessage("skyweb24.popuppro_TEMPLATE_SUBJECT_DISCOUNT");
					$template['BODY_TYPE']='html';
					$template['MESSAGE']=GetMessage("skyweb24.popuppro_TEMPLATE_MESSAGE_DISCOUNT");
					$tmpPost=$obTemplate->Add($template);
				}
			}
			$currentVal=(!isset($tmpPost))?$nextViewEl:$tmpPost;
			$settings['view']['props'][$keyNextViewEl]=$currentVal;
		}

		$conditionSet=$this->getConditions();
		foreach($conditionSet as $keyNextCond=>$nextCond){
			if($keyNextCond=='timeInterval'){
				$settings['condition']['timeInterval']='';
				$tmpPeriodFrom=$request->getPost('period_from');
				$tmpPeriodTo=$request->getPost('period_to');
				if(!empty($tmpPeriodFrom) || !empty($tmpPeriodTo)){
					$settings['condition']['timeInterval']=$tmpPeriodFrom.'#'.$tmpPeriodTo;
				}
			}elseif($keyNextCond=='dateStart' || $keyNextCond=='dateFinish'){
				$settings['condition'][$keyNextCond]= MakeTimeStamp($request->getPost($keyNextCond), CSite::GetDateFormat("FULL"));
			}elseif($keyNextCond=='saleIDProdInBasket'){
				$tmpProds=array();
				$tmpProducts=$request->getPost('saleIDProdInBasket');
				if(!empty($tmpProducts)){
					foreach($request->getPost('saleIDProdInBasket') as $nextProd){
						if(!empty($nextProd)){
							$tmpProds[]=$nextProd;
						}
					}
				}
				$settings['condition'][$keyNextCond]=(count($tmpProds)==0)?'':$tmpProds;
			}elseif($keyNextCond=='rule'){
				$settings['condition'][$keyNextCond]=$this->ConvertRequest($request->getPost($keyNextCond));
			}else{
				$settings['condition'][$keyNextCond]=$request->getPost($keyNextCond);
			}
		}
		
		//contactsSetting
		if($settings['view']['type']=='contact'){
			$contactSendMail=$request->getPost("contact_send_mail");
			$contactSaveToIblock=$request->getPost("contact_save_to_iblock");
			$contactIblock=$request->getPost("contact_iblock");
			$contactSaveToList=$request->getPost("contact_save_to_list");
			$contactRegister=$request->getPost("contact_register");
			if(!empty($contactSendMail) || (!empty($contactSaveToIblock) && !empty($contactIblock)) || !empty($contactSaveToList) || !empty($contactRegister)){
				if(!empty($contactSaveToList)){
					$settings['contact']['emailList']='Y';
				}
				if(!empty($contactSaveToIblock) && !empty($contactIblock)){
					$settings['contact']['iblock']=$contactIblock;
				}
				if(!empty($contactSendMail)){
					$settings['contact']['posttemplate']=$this->getPostTemplate();
				}
				if(!empty($contactRegister)){
					$settings['contact']['register']=$contactRegister;
				}
			}
			$contactGroup=$request->getPost("contact_groupmail");
			if(!empty($contactSaveToList) && !empty($contactGroup)){
				$settings['contact']['groupmail']=$contactGroup;
			}
		}
		$timer_array=array('banner','video','action','contact','html','coupon','roulette','discount');
		if(in_array($settings['view']['type'],$timer_array)){
			$settings['timer']=array();
			$timerEnabled=$request->getPost("timer_enable");
			$timerTime=$request->getPost("timer_date");
			$timerText=$request->getPost("timer_text");
			$timerLeft=$request->getPost("timer_left");
			$timerRight=$request->getPost("timer_right");
			$timerTop=$request->getPost("timer_top");
			$timerBottom=$request->getPost("timer_bottom");
			$settings['timer']['enabled']=$timerEnabled;
			if(strlen($timerTime)==10){
				$timerTime = $timerTime.' 00:00:00';
			}
			$settings['timer']['date']=$timerTime;
			$settings['timer']['text']=$timerText;
			$settings['timer']['left']=$timerLeft;
			$settings['timer']['right']=$timerRight;
			$settings['timer']['top']=$timerTop;
			$settings['timer']['bottom']=$timerBottom;
		}
		if($settings['view']['type']=='roulette'){
			$countRow = $request->getPost("roulette_element_count");
			for($i=1;$i<=$countRow;$i++){
				$settings['roulett'][$i]['text']=$request->getPost("roulette_".$i."_text");
				$settings['roulett'][$i]['color']=$request->getPost("roulette_".$i."_color");
				$settings['roulett'][$i]['rule']=$request->getPost("roulette_".$i."_rule");
				$settings['roulett'][$i]['chance']=$request->getPost("roulette_".$i."_chance");
				$settings['roulett'][$i]['gravity']=$request->getPost("roulette_".$i."_gravity");
			}
			$settings['roulett']['count']=$countRow;
		}
		global $DB;
		$serviceName=$request->getPost("service_name");
		$serviceName=(!empty($serviceName))?$serviceName:GetMessage("skyweb24.popuppro_TABCOND_SERVICE_NAME").'_'.$this->idPopup;
		$arFieldsInsert=array(
			'sort'=>$DB->ForSql($request->getPost("sort")),
			'active'=>"'".$DB->ForSql($request->getPost("active"))."'",
			'name'=>"'".$DB->ForSql($serviceName)."'",
			'type'=>"'".$DB->ForSql($request->getPost("type"))."'",
			'settings'=>"'".$DB->ForSql(serialize($settings))."'"
		);
		if(!empty($settings['contact']['posttemplate'])){
			$arFieldsInsert['cont_posttemplate']=$settings['contact']['posttemplate'];
		}
		if(!empty($settings['contact']['iblock'])){
			$arFieldsInsert['cont_iblock']=$settings['contact']['iblock'];
		}
		if($request->getPost("id_popup")=='new'){
			$ID = $DB->Insert($this->tableSetting, $arFieldsInsert, $err_mess.__LINE__);
		}else{
			$DB->Update($this->tableSetting, $arFieldsInsert,  "WHERE id=".$request->getPost("id_popup"), $err_mess.__LINE__);
			$ID = $request->getPost("id_popup");
		}
		if(strlen($strError)>0){
			return array('status'=>'error', 'data'=>$strError);
		}
		return array('status'=>'success', 'data'=>$ID);
	}
	
	public function editFromTableList($id, $arFields){
		global $DB;
		$res = $DB->Query('select * from '.$this->tableSetting.' where id='.$id.' limit 1;');
		 if($row = $res->Fetch()){
			 $tmpSetting=unserialize($row['settings']);
			 $updFields=array();
			 $updFields['active']="'N'";
			 $tmpSetting['condition']['active']='N';
			 if(!empty($arFields['active'])){
				 $updFields['active']="'".$arFields['active']."'";
				 $tmpSetting['condition']['active']=$arFields['active'];
			 }
			 if(!empty($arFields['SORT'])){
				 $updFields['sort']=intval($arFields['SORT']);
				 $tmpSetting['condition']['sort']=intval($arFields['SORT']);
			 }
			 if(!empty($arFields['NAME'])){
				 $updFields['name']="'".$DB->ForSql($arFields['NAME'])."'";
				 $tmpSetting['condition']['service_name']=$arFields['NAME'];
			 }
			 $updFields['settings']="'".serialize($tmpSetting)."'";
			 $DB->Update($this->tableSetting, $updFields,  "WHERE id=".$id, $err_mess.__LINE__);
		 }
	}
	
	public function getAllPopups(){
		global $DB;
		$res = $DB->Query('select * from '.$this->tableSetting.' order by id;');
		$retArr=array();
		while($row = $res->Fetch()){
			$retArr[$row['id']]=unserialize($row['settings']);
		}
		return $retArr;
	}
	
	public function convertToUtf(&$item, $key){
		$item=iconv(LANG_CHARSET, "UTF-8", $item);
	}
	
	public function setTemplate($type, $template, $newname){
		global $DB;
		if(LANG_CHARSET=='windows-1251'){
			$newname=iconv("UTF-8", LANG_CHARSET, $newname);
		}
		$oldPath='/bitrix/components/skyweb24/popup.pro/templates/'.$type.'_'.$template;
		$newPath='/bitrix/components/skyweb24/popup.pro/templates/'.$type.'_custom_';
		if(file_exists($_SERVER["DOCUMENT_ROOT"].$oldPath)){
			$templates=$this->getTemplatesPreset();
			foreach($templates[$type] as $nextTemplate){
				if($nextTemplate['template']==$template){
					$copyTemplate=$nextTemplate;
					$copyTemplate['name']=$newname;
					$copyTemplate['active']=false;
					$ID = $DB->Insert($this->tableTemplates, array(
						'type'=>"'".$DB->ForSql($type)."'",
						'name'=>"'".$DB->ForSql($newname)."'",
						'template'=>"'start'",
					), $err_mess.__LINE__);
					$copyTemplate['template']='custom_'.$ID;
					$DB->Update($this->tableTemplates, array(
						'template'=>"'".serialize($copyTemplate)."'",
					),  "WHERE id=".$ID, $err_mess.__LINE__);
					CopyDirFiles($_SERVER["DOCUMENT_ROOT"].$oldPath, $_SERVER["DOCUMENT_ROOT"].$newPath.$ID, true, true, false);
					if(LANG_CHARSET=='windows-1251'){
						array_walk_recursive($copyTemplate, array($this, 'convertToUtf'));
					}
					return array(
						'popup'=>$copyTemplate,
						'newPath'=>$newPath.$ID.'/template.php',
						'code'=>"custom_".$ID,
						'id'=>$ID
					);
				}
			}
		}else{
			return false;
		}
	}
	
	public function setColorTheme($type, $template, $colorstyle, $newname){
		$retArr=array('status'=>'error update');
		if(LANG_CHARSET=='windows-1251'){
			$newname=iconv("UTF-8", LANG_CHARSET, $newname);
		}
		$oldPath='/bitrix/components/skyweb24/popup.pro/templates/'.$type.'_'.$template.'/themes/'.$colorstyle.'.css';
		if(file_exists($_SERVER["DOCUMENT_ROOT"].$oldPath)){
			global $DB;
			$ID = $DB->Insert($this->tableColorThemes, array(
				'template'=>'"'.$type.'_'.$template.'"',
				'name'=>"'".$DB->ForSql($newname)."'"
			), $err_mess.__LINE__);
			$newPath=str_replace($colorstyle, 'custom_'.$ID, $oldPath);
			copy($_SERVER["DOCUMENT_ROOT"].$oldPath , $_SERVER["DOCUMENT_ROOT"].$newPath);

			$retArr['status']='success';
			$retArr['newPath']=$newPath;
			$retArr['code']='custom_'.$ID;
			$retArr['id']=$ID;
		}
		return $retArr;
	}
		
	public function CopyPopup($id){
		global $DB;
		$res = $DB->Query('select sort,active,name,type,settings,cont_iblock,cont_posttemplate from '.$this->tableSetting.' where id='.$id);
		$tmpFields=array();
		if($r=$res->Fetch()){
			foreach($r as $code=>$value)
				$tmpFields[$code]="'".$DB->ForSql($value)."'";
			$res=$DB->Insert($this->tableSetting,$tmpFields,$err_mess.__LINE__);
			return $res;
		}else
			return false;
	}
}

?>