<?
use \Bitrix\Main\Application,
	Bitrix\Main,
	Bitrix\Main\Web\Cookie,
	Bitrix\Main\Context,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Page\Asset,
	Bitrix\Main\UserConsent\Internals\AgreementTable,
	Bitrix\Main\UserConsent\Agreement;
use Bitrix\Main\Mail\Event;
use Bitrix\Sale\Internals;
\Bitrix\Main\Loader::IncludeModule('sale');
\Bitrix\Main\Loader::IncludeModule('catalog');
Loc::loadMessages(__FILE__);
class popuppro{

	protected $tableSetting;
	protected $tableColorThemes;
	protected $tableTemplates;				   
	protected $idPopup;
	protected $consentList;
	protected $site_id;
	const idModule='skyweb24.popuppro';

	function __construct($id='new'){
		$this->tableSetting='skyweb24_popuppro';
		$this->tableColorThemes='skyweb24_popuppro_add_colors';
		$this->tableTemplates='skyweb24_popuppro_add_templates';
		$this->idPopup=$id;
		$this->consentList='none';
		$this->site_id=SITE_ID;
	}

	public function getId(){
		return $this->idPopup;
	}
	
	public function getConsentList(){
		if($this->consentList=='none'){
			if (class_exists('Bitrix\Main\UserConsent\Agreement')){
				$tmpList=array();
				$list = AgreementTable::getList(array(
					'select' => array('ID', 'DATE_INSERT', 'ACTIVE', 'NAME', 'TYPE'),
					'filter' => array('ACTIVE' => 'Y'),
					'order' => array('ID' => 'ASC')
				));
				foreach($list as $item){
					$tmpList[$item['ID']]=$item['NAME'];
				}
				if(count($tmpList)>0){
					$this->consentList=$tmpList;
				}
			}
		}
		return ($this->consentList=='none')?array():$this->consentList;
	}

	public function getAgreements($agrArr=array()){
		$retArr=array();
		if (class_exists('Bitrix\Main\UserConsent\Agreement')){
			$agreements=new Agreement(1);
			$agreements=$agreements::getActiveList();
			if(count($agreements)>0){
				foreach($agreements as $key=>$agreement){
					$tmpAgreement=new Agreement($key, $agrArr);
					$retArr[$key] = $tmpAgreement->getLabelText();
				}
			}
		}
		return $retArr;
	}

	public function setPopupId($id){
		$this->idPopup=$id;
	}

	public function getTableSetting(){
		return $this->tableSetting;
	}

	private function getTypesPreset(){
		$type=array(
			/* 1. Èçîáðàæåíèå (Áàííåð) */
			'banner'=>array(
				'code'=>'banner',
				'name'=>GetMessage("skyweb24.popuppro_TYPE_NAME_BANNER"),
				'description'=>GetMessage("skyweb24.popuppro_TYPE_NAME_BANNER_DESCRIPTION"),
				'target'=>GetMessage("skyweb24.popuppro_TYPE_NAME_BANNER_TARGET"),
				'active'=>true,
				'props'=>array(
					'IMG_1_SRC'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_BANNER_CONTENT_IMG_1_SRC"), 'hint'=>GetMessage("skyweb24.popuppro_IMG_1_SRC_HINT")),
					'LINK_HREF'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_BANNER_CONTENT_LINK_HREF"), 'hint'=>GetMessage("skyweb24.popuppro_LINK_HREF_HINT")),
					'HREF_TARGET'=>array(
						'type'=>'service',
						'tag'=>'select',
						'name'=>GetMessage("skyweb24.popuppro_HREF_TARGET"),
						'list'=>array('_blank'=>GetMessage("skyweb24.popuppro_HREF_TARGET_BLANK"), '_self'=>GetMessage("skyweb24.popuppro_HREF_TARGET_SELF")),
						'hint'=>GetMessage("skyweb24.popuppro_HREF_TARGET_HINT")
					)
				)
			),

			/* 2. Âèäåî */
			'video'=>array(
				'code'=>'video',
				'name'=>GetMessage("skyweb24.popuppro_TYPE_NAME_VIDEO"),
				'description'=>GetMessage("skyweb24.popuppro_TYPE_NAME_VIDEO_DESCRIPTION"),
				'target'=>GetMessage("skyweb24.popuppro_TYPE_NAME_VIDEO_TARGET"),
				'props'=>array(
					'LINK_VIDEO'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_VIDEO_CONTENT_LINK_VIDEO"), 'hint'=>GetMessage("skyweb24.popuppro_VIDEO_CONTENT_LINK_VIDEO_HINT")),
					'VIDEO_SIMILAR'=>array(
						'type'=>'service',
						'tag'=>'select',
						'list'=>array(0=>GetMessage("skyweb24.popuppro_NO"), 1=>GetMessage("skyweb24.popuppro_YES")),
						'name'=>GetMessage("skyweb24.popuppro_VIDEO_SERVICE_VIDEO_SIMILAR"),
						'hint'=>GetMessage("skyweb24.popuppro_VIDEO_SERVICE_VIDEO_SIMILAR_HINT")
					),
					'VIDEO_AUTOPLAY'=>array(
						'type'=>'service',
						'name'=>GetMessage("skyweb24.popuppro_VIDEO_SERVICE_VIDEO_AUTOPLAY"),
						'tag'=>'select',
						'list'=>array(0=>GetMessage("skyweb24.popuppro_NO"), 1=>GetMessage("skyweb24.popuppro_YES")),
						'hint'=>GetMessage("skyweb24.popuppro_VIDEO_SERVICE_VIDEO_AUTOPLAY_HINT")
					)
				)
			),

			/* 3. Àêöèè */
			'action'=>array(
				'code'=>'action',
				'name'=>GetMessage("skyweb24.popuppro_TYPE_NAME_ACTION"),
				'description'=>GetMessage("skyweb24.popuppro_TYPE_NAME_ACTION_DESCRIPTION"),
				'target'=>GetMessage("skyweb24.popuppro_TYPE_NAME_ACTION_TARGET"),
				'color_style'=>array(
					'orange'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_ORANGE"),
					'green'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_GREEN"),
					'greensea'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_GREENSEA"),
					'red'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_RED"),
					'midnightblue'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_MIDNIGHTBLUE"),
					'asbestos'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_ASBESTOS"),
					'dark'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_DARK"),
					'blue'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_BLUE"),
					'pumpkin'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_PUMPKIN"),
					'wisteria'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_WISTERIA"),
					
					'au_GreenlandGreen'=>GetMessage("skyweb24.popuppro_au_GreenlandGreen"),
					'au_Turbo'=>GetMessage("skyweb24.popuppro_au_Turbo"),
					'au_PureApple'=>GetMessage("skyweb24.popuppro_au_PureApple"),
					'au_CarminePink'=>GetMessage("skyweb24.popuppro_au_CarminePink"),
					'au_HintOfIcePack'=>GetMessage("skyweb24.popuppro_au_HintOfIcePack"),
					'au_QuinceJelly'=>GetMessage("skyweb24.popuppro_au_QuinceJelly"),
					'au_WizardGrey'=>GetMessage("skyweb24.popuppro_au_WizardGrey"),
					'au_Blurple'=>GetMessage("skyweb24.popuppro_au_Blurple"),
					'au_DeepCove'=>GetMessage("skyweb24.popuppro_au_DeepCove"),
					'au_SteelPink'=>GetMessage("skyweb24.popuppro_au_SteelPink"),
					
					'ca_Cyanite'=>GetMessage("skyweb24.popuppro_ca_Cyanite"),
					'ca_DarkMountainMeadow'=>GetMessage("skyweb24.popuppro_ca_DarkMountainMeadow"),
					'ca_Amour'=>GetMessage("skyweb24.popuppro_ca_Amour"),
					'ca_AquaVelvet'=>GetMessage("skyweb24.popuppro_ca_AquaVelvet"),
					'ca_DoubleDragonSkin'=>GetMessage("skyweb24.popuppro_ca_DoubleDragonSkin"),
					'ca_LianHongLotusPink'=>GetMessage("skyweb24.popuppro_ca_LianHongLotusPink"),
					'ca_BleuDeFrance'=>GetMessage("skyweb24.popuppro_ca_BleuDeFrance"),
					'ca_StormPetrel'=>GetMessage("skyweb24.popuppro_ca_StormPetrel"),
					'ca_Bluebell'=>GetMessage("skyweb24.popuppro_ca_Bluebell"),
					'ca_ImperialPrimer'=>GetMessage("skyweb24.popuppro_ca_ImperialPrimer"),
					
					'ru_BlueCuracao'=>GetMessage("skyweb24.popuppro_ru_BlueCuracao"),
					'ru_Summertime'=>GetMessage("skyweb24.popuppro_ru_Summertime"),
					'ru_PorcelainRose'=>GetMessage("skyweb24.popuppro_ru_PorcelainRose"),
					'ru_DeepRose'=>GetMessage("skyweb24.popuppro_ru_DeepRose"),
					'ru_Tigerlily'=>GetMessage("skyweb24.popuppro_ru_Tigerlily"),
					'ru_SawtoothAak'=>GetMessage("skyweb24.popuppro_ru_SawtoothAak"),
					'ru_FlamingoPink'=>GetMessage("skyweb24.popuppro_ru_FlamingoPink"),
					'ru_Cornflower'=>GetMessage("skyweb24.popuppro_ru_Cornflower"),
					'ru_Biscay'=>GetMessage("skyweb24.popuppro_ru_Biscay"),
					'ru_PurpleCorallite'=>GetMessage("skyweb24.popuppro_ru_PurpleCorallite"),
				),
				'props'=>array(
					
					'IMG_1_SRC'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_ACTION_CONTENT_IMG_1_SRC"), 'hint'=>GetMessage("skyweb24.popuppro_IMG_1_SRC_HINT")),
					'TITLE'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_ACTION_CONTENT_TITLE"), 'hint'=>GetMessage("skyweb24.popuppro_CONTENT_TITLE_HINT"), 'PERSONALISATION'=>'Y'),
					'SUBTITLE'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_ACTION_CONTENT_SUBTITLE"), 'hint'=>GetMessage("skyweb24.popuppro_CONTENT_TITLE_HINT"), 'PERSONALISATION'=>'Y'),
					'CONTENT'=>array('type'=>'content', 'tag'=>'textarea', 'name'=>GetMessage("skyweb24.popuppro_ACTION_CONTENT_CONTENT"), 'hint'=>GetMessage("skyweb24.popuppro_CONTENT_TITLE_HINT"), 'PERSONALISATION'=>'Y'),
					'LINK_TEXT'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_ACTION_CONTENT_LINK_TEXT"), 'hint'=>GetMessage("skyweb24.popuppro_CONTENT_TITLE_HINT"), 'PERSONALISATION'=>'Y'),
					'LINK_HREF'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_ACTION_CONTENT_LINK_HREF"), 'hint'=>GetMessage("skyweb24.popuppro_ACTION_CONTENT_LINK_HREF_HINT")),
					
					'HREF_TARGET'=>array(
						'type'=>'service',
						'tag'=>'select',
						'name'=>GetMessage("skyweb24.popuppro_HREF_TARGET"),
						'list'=>array('_blank'=>GetMessage("skyweb24.popuppro_HREF_TARGET_BLANK"), '_self'=>GetMessage("skyweb24.popuppro_HREF_TARGET_SELF")),
						'hint'=>GetMessage('skyweb24.popuppro_HREF_TARGET_HINT')
					),
					'GOOGLE_FONT'=>array(
						'type'=>'service',
						'name'=>GetMessage('skyweb24.popuppro_GOOGLE_FONT_NAME'),
						'hint'=>GetMessage('skyweb24.popuppro_GOOGLE_FONT_HINT')
					),
					'BUTTON_METRIC'=>array('type'=>'service','tag'=>'textarea','name'=>GetMessage('skyweb24.popuppro_BUTTON_METRIC'), 'hint'=>GetMessage('skyweb24.popuppro_BUTTON_METRIC_HINT'))
				)
			),

			/* 4. Ñîöèàëüíûå ñåòè */
			'social'=>array(
				'code'=>'social',
				'name'=>GetMessage("skyweb24.popuppro_TYPE_NAME_SOCIAL"),
				'description'=>GetMessage("skyweb24.popuppro_TYPE_NAME_SOCIAL_DESCRIPTION"),
				'target'=>GetMessage("skyweb24.popuppro_TYPE_NAME_SOCIAL_TARGET"),
				'props'=>array(
					'TITLE'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_VIDEO_CONTENT_TITLE"), 'hint'=>GetMessage("skyweb24.popuppro_CONTENT_TITLE_HINT"), 'PERSONALISATION'=>'Y'), /*Óáðàòü*/
					'ID_VK'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_SOCIAL_SERVICE_ID_VK"), 'hint'=>GetMessage("skyweb24.popuppro_SOCIAL_SERVICE_ID_VK_HINT")),
					'ID_INST'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_SOCIAL_SERVICE_ID_INST"), 'hint'=>GetMessage("skyweb24.popuppro_SOCIAL_SERVICE_ID_INST_HINT")),
					'ID_ODNKL'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_SOCIAL_SERVICE_ID_ODNKL"), 'hint'=>GetMessage("skyweb24.popuppro_SOCIAL_SERVICE_ID_ODNKL_HINT")),
					
					'HREF_TARGET'=>array(
						'type'=>'service',
						'tag'=>'select',
						'name'=>GetMessage("skyweb24.popuppro_HREF_TARGET"),
						'list'=>array('_blank'=>GetMessage("skyweb24.popuppro_HREF_TARGET_BLANK"), '_self'=>GetMessage("skyweb24.popuppro_HREF_TARGET_SELF")),
					),					
					'GOOGLE_FONT'=>array(
						'type'=>'service',
						'name'=>GetMessage('skyweb24.popuppro_GOOGLE_FONT_NAME'),
						'hint'=>GetMessage('skyweb24.popuppro_GOOGLE_FONT_HINT')
					),					
				)
			),

			/* 5. Ñáîðùèê êîíòàêòîâ */
			'contact'=>array(
				'code'=>'contact',
				'name'=>GetMessage("skyweb24.popuppro_TYPE_NAME_CONTACT"),
				'description'=>GetMessage("skyweb24.popuppro_TYPE_NAME_CONTACT_DESCRIPTION"),
				'target'=>GetMessage("skyweb24.popuppro_TYPE_NAME_CONTACT_TARGET"),
				'color_style'=>array(
					'grad_greensea-green'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_GRAD_GREENSEA-GREEN"),
					'grad_greensea-blue'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_GRAD_GREENSEA-BLUE"),
					'grad_green-blue'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_GRAD_GREEN-BLUE"),
					'grad_red-orange'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_GRAD_RED-ORANGE"),
					'grad_blue-wisteria'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_GRAD_BLUE-WISTERIA"),
					'grad_wisteria-red'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_GRAD_WISTERIA-RED"),
					
					'orange'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_ORANGE"),
					'green'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_GREEN"),
					'greensea'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_GREENSEA"),
					'red'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_RED"),
					'blue'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_BLUE"),
					'pumpkin'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_PUMPKIN"),
					'wisteria'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_WISTERIA"),
					
					'au_GreenlandGreen'=>GetMessage("skyweb24.popuppro_au_GreenlandGreen"),
					'au_Turbo'=>GetMessage("skyweb24.popuppro_au_Turbo"),
					'au_PureApple'=>GetMessage("skyweb24.popuppro_au_PureApple"),
					'au_CarminePink'=>GetMessage("skyweb24.popuppro_au_CarminePink"),
					'au_HintOfIcePack'=>GetMessage("skyweb24.popuppro_au_HintOfIcePack"),
					'au_QuinceJelly'=>GetMessage("skyweb24.popuppro_au_QuinceJelly"),
					'au_WizardGrey'=>GetMessage("skyweb24.popuppro_au_WizardGrey"),
					'au_Blurple'=>GetMessage("skyweb24.popuppro_au_Blurple"),
					'au_DeepCove'=>GetMessage("skyweb24.popuppro_au_DeepCove"),
					'au_SteelPink'=>GetMessage("skyweb24.popuppro_au_SteelPink"),
					
					'ca_Cyanite'=>GetMessage("skyweb24.popuppro_ca_Cyanite"),
					'ca_DarkMountainMeadow'=>GetMessage("skyweb24.popuppro_ca_DarkMountainMeadow"),
					'ca_Amour'=>GetMessage("skyweb24.popuppro_ca_Amour"),
					'ca_AquaVelvet'=>GetMessage("skyweb24.popuppro_ca_AquaVelvet"),
					'ca_DoubleDragonSkin'=>GetMessage("skyweb24.popuppro_ca_DoubleDragonSkin"),
					'ca_LianHongLotusPink'=>GetMessage("skyweb24.popuppro_ca_LianHongLotusPink"),
					'ca_BleuDeFrance'=>GetMessage("skyweb24.popuppro_ca_BleuDeFrance"),
					'ca_StormPetrel'=>GetMessage("skyweb24.popuppro_ca_StormPetrel"),
					'ca_Bluebell'=>GetMessage("skyweb24.popuppro_ca_Bluebell"),
					'ca_ImperialPrimer'=>GetMessage("skyweb24.popuppro_ca_ImperialPrimer"),
					
					'ru_BlueCuracao'=>GetMessage("skyweb24.popuppro_ru_BlueCuracao"),
					'ru_Summertime'=>GetMessage("skyweb24.popuppro_ru_Summertime"),
					'ru_PorcelainRose'=>GetMessage("skyweb24.popuppro_ru_PorcelainRose"),
					'ru_DeepRose'=>GetMessage("skyweb24.popuppro_ru_DeepRose"),
					'ru_Tigerlily'=>GetMessage("skyweb24.popuppro_ru_Tigerlily"),
					'ru_SawtoothAak'=>GetMessage("skyweb24.popuppro_ru_SawtoothAak"),
					'ru_FlamingoPink'=>GetMessage("skyweb24.popuppro_ru_FlamingoPink"),
					'ru_Cornflower'=>GetMessage("skyweb24.popuppro_ru_Cornflower"),
					'ru_Biscay'=>GetMessage("skyweb24.popuppro_ru_Biscay"),
					'ru_PurpleCorallite'=>GetMessage("skyweb24.popuppro_ru_PurpleCorallite"),
				),
				'props'=>array(
					'IMG_1_SRC'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_CONTENT_MAIN_IMG"), 'hint'=>GetMessage('skyweb24.popuppro_IMG_1_SRC_HINT')),
					'TITLE'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_CONTENT_TITLE"), 'hint'=>GetMessage('skyweb24.popuppro_CONTENT_TITLE_HINT'), 'PERSONALISATION'=>'Y'),
					'SUBTITLE'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_CONTENT_SUBTITLE"), 'hint'=>GetMessage('skyweb24.popuppro_CONTENT_TITLE_HINT'), 'PERSONALISATION'=>'Y'),
					'BUTTON_TEXT'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_CONTENT_BUTTON_TEXT"), 'hint'=>GetMessage('skyweb24.popuppro_CONTENT_TITLE_HINT'), 'PERSONALISATION'=>'Y'),
					
					'EMAIL_SHOW'=>array('type'=>'content', 'tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_EMAIL_SHOW"),'block'=>'start'),
					'EMAIL_REQUIRED'=>array('type'=>'content', 'tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_EMAIL_REQUIRED"), 'hint'=>GetMessage('skyweb24.popuppro_INPUT_REQUIRED_HINT')),
					'EMAIL_TITLE'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_TITLE"), 'hint'=>GetMessage('skyweb24.popuppro_INPUT_NAME_HINT')),
					'EMAIL_PLACEHOLDER'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_PLACEHOLDER"), 'hint'=>GetMessage('skyweb24.popuppro_INPUT_PLECAHOLDER_HINT'), 'block'=>'end'),

					'NAME_SHOW'=>array('type'=>'content', 'tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_NAME_SHOW"),'block'=>'start'),
					'NAME_REQUIRED'=>array('type'=>'content', 'tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_NAME_REQUIRED"), 'hint'=>GetMessage('skyweb24.popuppro_INPUT_REQUIRED_HINT')),
					'NAME_TITLE'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_TITLE"), 'hint'=>GetMessage('skyweb24.popuppro_INPUT_NAME_HINT')),
					'NAME_PLACEHOLDER'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_PLACEHOLDER"), 'hint'=>GetMessage('skyweb24.popuppro_INPUT_PLECAHOLDER_HINT'),'block'=>'end'),

					'PHONE_SHOW'=>array('type'=>'content', 'tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_PHONE_SHOW"),'block'=>'start'),
					'PHONE_REQUIRED'=>array('type'=>'content', 'tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_PHONE_REQUIRED"), 'hint'=>GetMessage('skyweb24.popuppro_INPUT_REQUIRED_HINT')),
					'PHONE_TITLE'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_TITLE"), 'hint'=>GetMessage('skyweb24.popuppro_INPUT_NAME_HINT')),
					'PHONE_PLACEHOLDER'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_PLACEHOLDER"), 'hint'=>GetMessage('skyweb24.popuppro_INPUT_PLECAHOLDER_HINT'),'block'=>'end'),

					'DESCRIPTION_SHOW'=>array('type'=>'content', 'tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_DESCRIPTION_SHOW"),'block'=>'start'),
					'DESCRIPTION_REQUIRED'=>array('type'=>'content', 'tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_DESCRIPTION_REQUIRED"), 'hint'=>GetMessage('skyweb24.popuppro_INPUT_REQUIRED_HINT')),
					'DESCRIPTION_TITLE'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_TITLE"), 'hint'=>GetMessage('skyweb24.popuppro_INPUT_NAME_HINT')),
					'DESCRIPTION_PLACEHOLDER'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_PLACEHOLDER"), 'hint'=>GetMessage('skyweb24.popuppro_INPUT_PLECAHOLDER_HINT'),'block'=>'end'),

					'USE_CONSENT_SHOW'=>array('type'=>'content',  'tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_CONSENT"),'block'=>'start'),
					'CONSENT_LIST'=>array('type'=>'content',  'tag'=>'select', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_CONSENT_LIST"), 'hint'=>GetMessage('skyweb24.popuppro_INPUT_CONSENT_HINT'), 'list'=>$this->getConsentList(), 'block'=>'end'),

					'HREF_TARGET'=>array(
						'type'=>'service',
						'tag'=>'select',
						'name'=>GetMessage("skyweb24.popuppro_HREF_TARGET"),
						'list'=>array('_blank'=>GetMessage("skyweb24.popuppro_HREF_TARGET_BLANK"), '_self'=>GetMessage("skyweb24.popuppro_HREF_TARGET_SELF")),
					),
					'BUTTON_METRIC'=>array('type'=>'service','tag'=>'textarea','name'=>GetMessage('skyweb24.popuppro_BUTTON_METRIC'),'hint'=>GetMessage('skyweb24.popuppro_BUTTON_METRIC_HINT')),
					'GOOGLE_FONT'=>array(
						'type'=>'service',
						'name'=>GetMessage('skyweb24.popuppro_GOOGLE_FONT_NAME'),
						'hint'=>GetMessage('skyweb24.popuppro_GOOGLE_FONT_HINT')
					),
					
				)
			),

			/* 6. Ïîäåëèòüñÿ â ñîö ñåòè */
			'share'=>array(
				'code'=>'share',
				'name'=>GetMessage("skyweb24.popuppro_TYPE_NAME_SHARE"),
				'description'=>GetMessage("skyweb24.popuppro_TYPE_NAME_SHARE_DESCRIPTION"),
				'target'=>GetMessage("skyweb24.popuppro_TYPE_NAME_SHARE_TARGET"),
				'props'=>array(
					'TITLE'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_SHARE_CONTENT_TITLE"), 'hint'=>GetMessage('skyweb24.popuppro_CONTENT_TITLE_HINT'), 'PERSONALISATION'=>'Y'),
					'SUBTITLE'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_SHARE_CONTENT_SUBTITLE"), 'hint'=>GetMessage('skyweb24.popuppro_CONTENT_TITLE_HINT'), 'PERSONALISATION'=>'Y'),
					'SOC_VK'=>array('type'=>'content','tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_SHARE_CONTENT_VK")),
					'SOC_FB'=>array('type'=>'content','tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_SHARE_SERVICE_FB")),
					'SOC_OD'=>array('type'=>'content','tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_SHARE_SERVICE_OD")),
					'SOC_TW'=>array('type'=>'content','tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_SHARE_SERVICE_TW")),
					'SOC_GP'=>array('type'=>'content','tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_SHARE_SERVICE_GP")),
					'SOC_MR'=>array('type'=>'content','tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_SHARE_SERVICE_MR")),
					
					'HREF_TARGET'=>array(
						'type'=>'service',
						'tag'=>'select',
						'name'=>GetMessage("skyweb24.popuppro_HREF_TARGET"),
						'list'=>array('_blank'=>GetMessage("skyweb24.popuppro_HREF_TARGET_BLANK"), '_self'=>GetMessage("skyweb24.popuppro_HREF_TARGET_SELF")),
						'hint'=>GetMessage('skyweb24.popuppro_HREF_TARGET_HINT')
					),
					'GOOGLE_FONT'=>array(
						'type'=>'service',
						'name'=>GetMessage('skyweb24.popuppro_GOOGLE_FONT_NAME'),
						'hint'=>GetMessage('skyweb24.popuppro_GOOGLE_FONT_HINT')
					)
				)
			),

			/* 7. HTML */
			'html'=>array(
				'code'=>'html',
				'name'=>GetMessage("skyweb24.popuppro_TYPE_NAME_HTML"),
				'description'=>GetMessage("skyweb24.popuppro_TYPE_NAME_HTML_DESCRIPTION"),
				'target'=>GetMessage("skyweb24.popuppro_TYPE_NAME_HTML_TARGET"),
				'props'=>array(
					//'TITLE'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_SHARE_CONTENT_TITLE")),
					'TEXTAREA'=>array('type'=>'content', 'tag'=>'textarea', 'name'=>GetMessage("skyweb24.popuppro_HTML_CONTENT_TEXTAREA"),'row'=>'10', 'hint'=>GetMessage('skyweb24.popuppro_HTML_CONTENT_TEXTAREA_HINT'), 'PERSONALISATION'=>'Y'),
					'HREF_TARGET'=>array(
							'type'=>'service',
							'tag'=>'select',
							'name'=>GetMessage("skyweb24.popuppro_HREF_TARGET"),
							'list'=>array('_blank'=>GetMessage("skyweb24.popuppro_HREF_TARGET_BLANK"), '_self'=>GetMessage("skyweb24.popuppro_HREF_TARGET_SELF")),
						),
					
				)
			),
			/*8. Îêíî 18+ */
			'age'=>array(
				'code'=>'age',
				'name'=>GetMessage('skyweb24.popuppro_TYPE_NAME_AGE'),
				'description'=>GetMessage("skyweb24.popuppro_TYPE_NAME_AGE_DESCRIPTION"),
				'target'=>GetMessage("skyweb24.popuppro_TYPE_NAME_AGE_TARGET"),
				'props'=>array(
					'IMG_1_SRC'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_AGE_CONTENT_MAIN_IMG"), 'hint'=>GetMessage('skyweb24.popuppro_IMG_1_SRC_HINT')),
					'TITLE'=>array('type'=>'content','name'=>GetMessage('skyweb24.popuppro_AGE_CONTENT_TITLE'), 'hint'=>GetMessage('skyweb24.popuppro_CONTENT_TITLE_HINT')),
					'BUTTON_TEXT_Y'=>array('type'=>'content','name'=>GetMessage('skyweb24.popuppro_AGE_CONTENT_BUTTON_Y'), 'hint'=>GetMessage('skyweb24.popuppro_AGE_CONTENT_BUTTON_Y_HINT')),
					'BUTTON_TEXT_N'=>array('type'=>'content','name'=>GetMessage('skyweb24.popuppro_AGE_CONTENT_BUTTON_N'), 'hint'=>GetMessage('skyweb24.popuppro_AGE_CONTENT_BUTTON_N_HINT')),
					'HREF_LINK'=>array('type'=>'content','name'=>GetMessage('skyweb24.popuppro_AGE_CONTENT_HREF_LINK'), 'hint'=>GetMessage('skyweb24.popuppro_AGE_CONTENT_HREF_LINK_HINT')),
					
					'GOOGLE_FONT'=>array(
						'type'=>'service',
						'name'=>GetMessage('skyweb24.popuppro_GOOGLE_FONT_NAME'),
						'hint'=>GetMessage('skyweb24.popuppro_GOOGLE_FONT_HINT')
					)
				)
			)
		);
		if(\Bitrix\Main\Loader::IncludeModule('sale')){
			$type['coupon']=array(
				'code'=>'coupon',
				'name'=>GetMessage("skyweb24.popuppro_TYPE_NAME_COUPON"),
				'description'=>GetMessage("skyweb24.popuppro_TYPE_NAME_COUPON_DESCRIPTION"),
				'target'=>GetMessage("skyweb24.popuppro_TYPE_NAME_COUPON_TARGET"),
				'color_style'=>array(
					'green'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_GREEN"),
					'red'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_RED"),
					'blue'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_BLUE"),
					'wisteria'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_WISTERIA"),
					'orange'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_ORANGE"),
					'pumpkin'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_PUMPKIN"),
					'greensea'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_GREENSEA"),
					'midnightblue'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_MIDNIGHTBLUE"),
					'dark'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_DARK"),
					'asbestos'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_ASBESTOS")
				),
				'props'=>array(
					'IMG_1_SRC'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_COUPON_CONTENT_MAIN_IMG"), 'hint'=>GetMessage('skyweb24.popuppro_IMG_1_SRC_HINT')),
					'TITLE'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_COUPON_CONTENT_TITLE"), 'hint'=>GetMessage('skyweb24.popuppro_CONTENT_TITLE_HINT'), 'PERSONALISATION'=>'Y'),
					'SUBTITLE'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_COUPON_CONTENT_SUBTITLE"), 'hint'=>GetMessage('skyweb24.popuppro_CONTENT_TITLE_HINT'), 'PERSONALISATION'=>'Y'),
					'BUTTON_TEXT'=>array('type'=>'content','name'=>GetMessage("skyweb24.popuppro_COUPON_CONTENT_BUTTON_TEXT"), 'hint'=>GetMessage('skyweb24.popuppro_CONTENT_TITLE_HINT'), 'PERSONALISATION'=>'Y'),
					'EMAIL_SHOW'=>array('type'=>'content', 'tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_EMAIL_SHOW"),'block'=>'start'),
					'EMAIL_PLACEHOLDER'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_PLACEHOLDER"), 'hint'=>GetMessage('skyweb24.popuppro_INPUT_PLECAHOLDER_HINT')),
					'EMAIL_NOT_NEW'=>array('type'=>'content','tag'=>'checkbox','name'=>GetMessage('skyweb24.popuppro_CONTACT_UNIQUE'),'hint'=>GetMessage('skyweb24.popuppro_CONTACT_UNIQUE_HINT')),
					'EMAIL_NOT_NEW_TEXT'=>array('type'=>'content', 'name'=>GetMessage('skyweb24.popuppro_COUPON_CONTENT_EMAIL_NOT_NEW'),'block'=>'end','hint'=>GetMessage('skyweb24.popuppro_COUPON_CONTENT_EMAIL_NOT_NEW_HINT')),
					
					'RULE_ID'=>array(
						'type'=>'service',
						'tag'=>'select',
						'list'=>Skyweb24\Popuppro\Tools::getBasketRules(),
						'name'=>GetMessage("skyweb24.popuppro_COUPON_CONTENT_MAIN_RULE_ID"),
						'hint'=>GetMessage('skyweb24.popuppro_COUPON_CONTENT_MAIN_RULE_ID_HINT')
					),
					'TIMING'=>array('type'=>'service','name'=>GetMessage("skyweb24.popuppro_COUPON_CONTENT_MAIN_TIMING"), 'hint'=>GetMessage('skyweb24.popuppro_COUPON_CONTENT_MAIN_TIMING_HINT')),
					'GOOGLE_FONT'=>array(
						'type'=>'service',
						'name'=>GetMessage('skyweb24.popuppro_GOOGLE_FONT_NAME'),
						'hint'=>GetMessage('skyweb24.popuppro_GOOGLE_FONT_HINT')
					),
					'BUTTON_METRIC'=>array('type'=>'service','tag'=>'textarea','name'=>GetMessage('skyweb24.popuppro_BUTTON_METRIC'),'hint'=>GetMessage('skyweb24.popuppro_BUTTON_METRIC_HINT')),
					
					
					'EMAIL_ADD2BASE'=>array('type'=>'service','tag'=>'checkbox','name'=>GetMessage('skyweb24.popuppro_CONTACT_ADD'),'hint'=>GetMessage('skyweb24.popuppro_CONTACT_ADD_HINT')),
					'EMAIL_EMAIL_TO'=>array('type'=>'service','tag'=>'checkbox','name'=>GetMessage('skyweb24.popuppro_CONTACT_EMAIL_TO'),'hint'=>GetMessage('skyweb24.popuppro_CONTACT_EMAIL_TO_HINT')),
					
					'EMAIL_TEMPLATE'=>array(
						'type'=>'service',
						//'tag'=>'select',
						'tag'=>'posttemplate',
						'name'=>GetMessage('skyweb24.popuppro_CONTACT_TEMPLATE'),
						'hint'=>GetMessage('skyweb24.popuppro_CONTACT_TEMPLATE_HINT'),
						'list'=>Skyweb24\Popuppro\Tools::getMailTemplates()
					),
					
					
					
				)
			);
		}
		
		$type['roulette']=array(
				'code'=>'roulette',
				'name'=>GetMessage("skyweb24.popuppro_TYPE_NAME_ROULETTE"),
				'description'=>GetMessage("skyweb24.popuppro_TYPE_NAME_ROULETTE_DESCRIPTION"),
				'target'=>GetMessage("skyweb24.popuppro_TYPE_NAME_ROULETTE_TARGET"),
				'color_style'=>array(
					'green'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_GREEN"),
					'red'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_RED"),
					'blue'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_BLUE"),
					'wisteria'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_WISTERIA"),
					'orange'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_ORANGE"),
					'pumpkin'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_PUMPKIN"),
					'greensea'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_GREENSEA"),
				),
				'props'=>array(
					'TITLE'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_ROULETTE_CONTENT_TITLE"), 'hint'=>GetMessage('skyweb24.popuppro_CONTENT_TITLE_HINT'), 'PERSONALISATION'=>'Y'),
					'SUBTITLE'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_ROULETTE_CONTENT_SUBTITLE"), 'hint'=>GetMessage('skyweb24.popuppro_CONTENT_TITLE_HINT'), 'PERSONALISATION'=>'Y'),
					'BUTTON_TEXT'=>array('type'=>'content','name'=>GetMessage("skyweb24.popuppro_ROULETTE_CONTENT_BUTTON_TEXT"), 'hint'=>GetMessage('skyweb24.popuppro_CONTENT_TITLE_HINT'), 'PERSONALISATION'=>'Y'),
					'RESULT_TEXT'=>array('type'=>'content','name'=>GetMessage("skyweb24.popuppro_ROULETTE_RESULT_TITLE"), 'hint'=>GetMessage('skyweb24.popuppro_CONTENT_TITLE_HINT'),'hint'=>GetMessage('skyweb24.popuppro_ROULETTE_RESULT_HINT'), 'PERSONALISATION'=>'Y'),
					'NOTHING_TEXT'=>array('type'=>'content','name'=>GetMessage("skyweb24.popuppro_ROULETTE_NOTHING_TITLE"), 'hint'=>GetMessage('skyweb24.popuppro_CONTENT_TITLE_HINT'),'hint'=>GetMessage('skyweb24.popuppro_ROULETTE_NOTHING_HINT'), 'PERSONALISATION'=>'Y'),
					'EMAIL_SHOW'=>array('type'=>'content', 'tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_EMAIL_SHOW"),'block'=>'start'),
					'EMAIL_PLACEHOLDER'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_PLACEHOLDER"), 'hint'=>GetMessage('skyweb24.popuppro_INPUT_PLECAHOLDER_HINT')),
					'EMAIL_NOT_NEW'=>array('type'=>'content','tag'=>'checkbox','name'=>GetMessage('skyweb24.popuppro_CONTACT_UNIQUE'),'hint'=>GetMessage('skyweb24.popuppro_CONTACT_UNIQUE_HINT')),
					'EMAIL_NOT_NEW_TEXT'=>array('type'=>'content','name'=>GetMessage('skyweb24.popuppro_ROULETTE_CONTENT_EMAIL_NOT_NEW'),'block'=>'end','hint'=>GetMessage('skyweb24.popuppro_ROULETTE_CONTENT_EMAIL_NOT_NEW_HINT')),
				)
			);
		if(\Bitrix\Main\Loader::IncludeModule('sale')){
			$type['roulette']['props']['TIMING']=array('type'=>'service','name'=>GetMessage("skyweb24.popuppro_COUPON_CONTENT_MAIN_TIMING"),'hint'=>GetMessage('skyweb24.popuppro_COUPON_CONTENT_MAIN_TIMING_HINT'));
		}
		$type['roulette']['props']['MAIL_TEMPLATE']=array(
			'type'=>'service',
			//'tag'=>'select',
			'tag'=>'posttemplate',
			'name'=>GetMessage('skyweb24.popuppro_ROULETTE_TEMPLATE'),
			'hint'=>GetMessage('skyweb24.popuppro_ROULETTE_TEMPLATE_HINT'),
			'list'=>Skyweb24\Popuppro\Tools::getMailTemplates('SKYWEB24_POPUPPRO_ROULETTE_SEND')
		);
		$type['roulette']['props']['BUTTON_METRIC']=array('type'=>'service','tag'=>'textarea','name'=>GetMessage('skyweb24.popuppro_BUTTON_METRIC'),'hint'=>GetMessage('skyweb24.popuppro_BUTTON_METRIC_HINT'));
		$type['roulette']['props']['GOOGLE_FONT']=array(
			'type'=>'service',
			'name'=>GetMessage('skyweb24.popuppro_GOOGLE_FONT_NAME'),
			'hint'=>GetMessage('skyweb24.popuppro_GOOGLE_FONT_HINT')
		);
		$type['roulette']['props']['EMAIL_ADD2BASE']=array('type'=>'service','tag'=>'checkbox','name'=>GetMessage('skyweb24.popuppro_CONTACT_ADD'),'hint'=>GetMessage('skyweb24.popuppro_CONTACT_ADD_HINT'));
					
		
		
		if(\Bitrix\Main\Loader::IncludeModule('sale')){
			$type['discount']=array(
				'code'=>'discount',
				'name'=>GetMessage("skyweb24.popuppro_TYPE_NAME_DISCOUNT"),
				'description'=>GetMessage("skyweb24.popuppro_TYPE_NAME_DISCOUNT_DESCRIPTION"),
				'target'=>GetMessage("skyweb24.popuppro_TYPE_NAME_DISCOUNT_TARGET"),
				'color_style'=>array(
					'green'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_GREEN"),
					'red'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_RED"),
					'blue'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_BLUE"),
					'wisteria'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_WISTERIA"),
					'orange'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_ORANGE"),
					'pumpkin'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_PUMPKIN"),
					'greensea'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_GREENSEA"),
				),
				'props'=>array(
					'IMG_1_SRC'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_DISCOUNT_IMG_1"), 'hint'=>GetMessage('skyweb24.popuppro_IMG_1_SRC_HINT')),
					'IMG_2_SRC'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_DISCOUNT_IMG_2"), 'hint'=>GetMessage('skyweb24.popuppro_IMG_1_SRC_HINT')),
					'TITLE'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_ROULETTE_CONTENT_TITLE"), 'hint'=>GetMessage('skyweb24.popuppro_CONTENT_TITLE_HINT'), 'PERSONALISATION'=>'Y'),
					'SUBTITLE'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_ROULETTE_CONTENT_SUBTITLE"), 'hint'=>GetMessage('skyweb24.popuppro_CONTENT_TITLE_HINT'), 'PERSONALISATION'=>'Y'),
					'BUTTON_TEXT'=>array('type'=>'content','name'=>GetMessage("skyweb24.popuppro_ROULETTE_CONTENT_BUTTON_TEXT"), 'hint'=>GetMessage('skyweb24.popuppro_CONTENT_TITLE_HINT'), 'PERSONALISATION'=>'Y'),
					
					'NAME_SHOW'=>array('type'=>'content', 'tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_NAME_SHOW"),'block'=>'start'),
					'NAME_REQUIRED'=>array('type'=>'content', 'tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_NAME_REQUIRED"), 'hint'=>GetMessage('skyweb24.popuppro_INPUT_REQUIRED_HINT')),
					'NAME_TITLE'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_TITLE"), 'hint'=>GetMessage('skyweb24.popuppro_INPUT_NAME_HINT'),'block'=>'end'),
					
					'LASTNAME_SHOW'=>array('type'=>'content', 'tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_DISCOUNT_LASTNAME_SHOW"),'block'=>'start'),
					'LASTNAME_REQUIRED'=>array('type'=>'content', 'tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_DISCOUNT_LASTNAME_REQUIRED"), 'hint'=>GetMessage('skyweb24.popuppro_INPUT_REQUIRED_HINT')),
					'LASTNAME_TITLE'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_TITLE"), 'hint'=>GetMessage('skyweb24.popuppro_INPUT_NAME_HINT'),'block'=>'end'),

					'PHONE_SHOW'=>array('type'=>'content', 'tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_PHONE_SHOW"),'block'=>'start'),
					'PHONE_REQUIRED'=>array('type'=>'content', 'tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_PHONE_REQUIRED"), 'hint'=>GetMessage('skyweb24.popuppro_INPUT_REQUIRED_HINT')),
					'PHONE_TITLE'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_TITLE"), 'hint'=>GetMessage('skyweb24.popuppro_INPUT_NAME_HINT'),'block'=>'end'),
					
					'EMAIL_SHOW'=>array('type'=>'content', 'tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_EMAIL_SHOW"),'block'=>'start'),
					'EMAIL_TITLE'=>array('type'=>'content', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_TITLE"), 'hint'=>GetMessage('skyweb24.popuppro_INPUT_NAME_HINT')),
					'EMAIL_ADD2BASE'=>array('type'=>'content','tag'=>'checkbox','name'=>GetMessage('skyweb24.popuppro_CONTACT_ADD'),'hint'=>GetMessage('skyweb24.popuppro_CONTACT_ADD_HINT')),
					'EMAIL_NOT_NEW'=>array('type'=>'content','tag'=>'checkbox','name'=>GetMessage('skyweb24.popuppro_CONTACT_UNIQUE'),'hint'=>GetMessage('skyweb24.popuppro_CONTACT_UNIQUE_HINT')),
					'EMAIL_NOT_NEW_TEXT'=>array('type'=>'content','name'=>GetMessage('skyweb24.popuppro_ROULETTE_CONTENT_EMAIL_NOT_NEW'),'block'=>'end','hint'=>GetMessage('skyweb24.popuppro_ROULETTE_CONTENT_EMAIL_NOT_NEW_HINT')),
					
					'USE_CONSENT_SHOW'=>array('type'=>'content',  'tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_CONSENT"),'block'=>'start'),
					'CONSENT_LIST'=>array('type'=>'content',  'tag'=>'select', 'name'=>GetMessage("skyweb24.popuppro_CONTACT_CONSENT_LIST"), 'hint'=>GetMessage('skyweb24.popuppro_INPUT_CONSENT_HINT'), 'list'=>$this->getConsentList(), 'block'=>'end'),
					
					'RULE_ID'=>array(
						'type'=>'service',
						'tag'=>'select',
						'list'=>Skyweb24\Popuppro\Tools::getBasketRules(),
						'name'=>GetMessage("skyweb24.popuppro_COUPON_CONTENT_MAIN_RULE_ID"),
						'hint'=>GetMessage('skyweb24.popuppro_COUPON_CONTENT_MAIN_RULE_ID_HINT')
					),
					'DISCOUNT_MASK'=>array('type'=>'service','name'=>GetMessage('skyweb24.popuppro_DISCOUNT_MASK_TITLE'),'hint'=>GetMessage('skyweb24.popuppro_DISCOUNT_MASK_TITLE_HINT')),
					'USER_GROUP'=>array(
						'type'=>'service',
						'tag'=>'select',
						'list'=>Skyweb24\Popuppro\Tools::getUserGroup(),
						'name'=>GetMessage('skyweb24.popuppro_DISCOUNT_USERGROUP_TITLE'),
						'hint'=>GetMessage('skyweb24.popuppro_DISCOUNT_USERGROUP_TITLE_HINT')
					),
					'EMAIL_TEMPLATE_D'=>array(
						'type'=>'service',
						'tag'=>'posttemplate',
						//'tag'=>'select',
						'name'=>GetMessage('skyweb24.popuppro_ROULETTE_TEMPLATE'),
						'hint'=>GetMessage('skyweb24.popuppro_ROULETTE_TEMPLATE_HINT'),
						'list'=>Skyweb24\Popuppro\Tools::getMailTemplates('SKYWEB24_POPUPPRO_DISCOUNT_SEND')
					),
					'GOOGLE_FONT'=>array(
						'type'=>'service',
						'name'=>GetMessage('skyweb24.popuppro_GOOGLE_FONT_NAME'),
						'hint'=>GetMessage('skyweb24.popuppro_GOOGLE_FONT_HINT')
					),
					'BUTTON_METRIC'=>array('type'=>'service','tag'=>'textarea','name'=>GetMessage('skyweb24.popuppro_BUTTON_METRIC'),'hint'=>GetMessage('skyweb24.popuppro_BUTTON_METRIC_HINT')),
					
					
					
				)
			);
		}
		
		foreach($type as &$nextType){
			$nextType['props']['SHOW_CLOSEBUTTON']=array('type'=>'effects', 'tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_EFFECTS_SHOW_CLOSEBUTTON"), 'hint'=>GetMessage("skyweb24.popuppro_EFFECTS_SHOW_CLOSEBUTTON_HINT"));
			$nextType['props']['CLOSE_AUTOHIDE']=array('type'=>'effects', 'tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_EFFECTS_CLOSE_AUTOHIDE"), 'hint'=>GetMessage("skyweb24.popuppro_EFFECTS_CLOSE_AUTOHIDE_HINT"));
			$nextType['props']['CLOSE_TEXTBOX']=array('type'=>'effects', 'tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_EFFECTS_CLOSE_TEXTBOX"), 'hint'=>GetMessage("skyweb24.popuppro_EFFECTS_CLOSE_TEXTBOX_HINT"));
			$nextType['props']['CLOSE_TEXTAREA']=array('type'=>'effects', 'name'=>GetMessage("skyweb24.popuppro_EFFECTS_CLOSE_TEXTAREA"), 'hint'=>GetMessage("skyweb24.popuppro_EFFECTS_CLOSE_TEXTAREA_HINT"));
			
			$nextType['props']['SHOW_ANIMATION']=array(
				'type'=>'effects',
				'tag'=>'select',
				'name'=>GetMessage("skyweb24.popuppro_EFFECTS_SHOW"),
				'list'=>array(
					'none'=>GetMessage("skyweb24.popuppro_EFFECT_show_none"),
					'fromBottom'=>GetMessage("skyweb24.popuppro_EFFECT_show_fromBottom"),
					'fromUp'=>GetMessage("skyweb24.popuppro_EFFECT_show_fromUp"),
					'fromLeft'=>GetMessage("skyweb24.popuppro_EFFECT_show_fromLeft"),
					'fromRight'=>GetMessage("skyweb24.popuppro_EFFECT_show_fromRight"),
					'zoom'=>'Zoom',
					'bounce'=>'Bounce'
				),
				'hint'=>GetMessage("skyweb24.popuppro_EFFECTS_SHOW_HINT")
			);
			
			$nextType['props']['HIDE_ANIMATION']=array(
				'type'=>'effects',
				'tag'=>'select',
				'name'=>GetMessage("skyweb24.popuppro_EFFECTS_HIDE"),
				'list'=>array(
					'none'=>GetMessage("skyweb24.popuppro_EFFECT_hide_none"),
					'attenuation'=>GetMessage("skyweb24.popuppro_EFFECT_hide_attenuation"),
					'bounce_hide'=>'Bounce',
					'zoom_hide'=>'Zoom',
				),
				'hint'=>GetMessage("skyweb24.popuppro_EFFECTS_HIDE_HINT")
			);
			
			$nextType['props']['BACKGROUND_COLOR']=array(
				'type'=>'effects',
				'tag'=>'color',
				'id'=>'effects_color',
				'default'=>'#000',
				'name'=>GetMessage("skyweb24.popuppro_EFFECT_BACKGROUND_COLOR"),
				'hint'=>GetMessage("skyweb24.popuppro_EFFECT_BACKGROUND_COLOR_HINT")
			);
			
			$nextType['props']['BACKGROUND_OPACITY']=array(
				'type'=>'effects',
				//'tag'=>'number',
				'tag'=>'range',
				'min'=>0,
				'max'=>100,
				'step'=>1,
				'name'=>GetMessage("skyweb24.popuppro_EFFECT_BACKGROUND_OPACITY"),
				'hint'=>GetMessage("skyweb24.popuppro_EFFECT_BACKGROUND_OPACITY_HINT")
			);
			
			$nextType['props']['POSITION_LEFT']=array('type'=>'positionpopup', 'tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_POSITION_LEFT"));
			$nextType['props']['POSITION_RIGHT']=array('type'=>'positionpopup', 'tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_POSITION_RIGHT"));
			$nextType['props']['POSITION_TOP']=array('type'=>'positionpopup', 'tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_POSITION_TOP"));
			$nextType['props']['POSITION_BOTTOM']=array('type'=>'positionpopup', 'tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_POSITION_BOTTOM"));
			$nextType['props']['POSITION_FIXED']=array('type'=>'positionpopup', 'tag'=>'checkbox', 'name'=>GetMessage("skyweb24.popuppro_POSITION_fixed"), 'hint'=>GetMessage("skyweb24.popuppro_POSITION_fixed_HINT"));
		}
		return $type;
	}

	public function getTypes(){
		$type=$this->getTypesPreset();
		if($this->idPopup!='new'){
			$settings=$this->getSetting($this->idPopup);
			foreach($type as $keyType=>&$nextType){
				if($keyType==$settings['view']['type']){
					$nextType['active']=true;
				}else{
					$nextType['active']=false;
				}
			}
		}
		return $type;
	}

	protected function getTemplatesPreset(){
		$templates=array(
			/* 1. Èçîáðàæåíèå (Áàííåð) */
			'banner'=>array(
				array(
					'template'=>'default',
					'name'=>GetMessage("skyweb24.popuppro_TYPE_NAME_BANNER_T1"),
					'active'=>true,
					'props'=>array(
						'IMG_1_SRC'=>'/bitrix/themes/.default/skyweb24.popuppro/preload/banner_1.jpg',
						'LINK_HREF'=>"https://skyweb24.ru",
						'HREF_TARGET'=>'_blank',
						
					)
				)
			),

			/* 2. Âèäåî */
			'video'=>array(
				array(
					'template'=>'youtube',
					'name'=>GetMessage("skyweb24.popuppro_TYPE_NAME_VIDEO_T1"),
					'active'=>true,
					'props'=>array(
						'LINK_VIDEO'=>'EHQqQENOEps',
						'VIDEO_SIMILAR'=>'0',
						'VIDEO_AUTOPLAY'=>'0',
					)
				)
			),

			/* 3. Àêöèè */
			'action'=>array(
				array(
					'template'=>'leftimg',
					'name'=>GetMessage("skyweb24.popuppro_TYPE_NAME_ACTION_T1"),
					'active'=>true,
					'color_style'=>'au_Blurple',
					'props'=>array(
						'IMG_1_SRC'=>'/bitrix/themes/.default/skyweb24.popuppro/preload/gift_1.jpg',
						'TITLE'=>GetMessage("skyweb24.popuppro_ACTION_TITLE"),
						'SUBTITLE'=>GetMessage("skyweb24.popuppro_ACTION_SUBTITLE"),
						'CONTENT'=>GetMessage("skyweb24.popuppro_ACTION_CONTENT"),
						'LINK_TEXT'=>GetMessage("skyweb24.popuppro_ACTION_LINK_TEXT"),
						'LINK_HREF'=>'https://skyweb24.ru',
						
						'HREF_TARGET'=>'_blank',
						'GOOGLE_FONT'=>'',
						'BUTTON_METRIC'=>''
					)
				),
				array(
					'template'=>'rightimg',
					'name'=>GetMessage("skyweb24.popuppro_TYPE_NAME_ACTION_T2"),
					'color_style'=>'ca_Amour',
					'props'=>array(
						'IMG_1_SRC'=>'/bitrix/themes/.default/skyweb24.popuppro/preload/gift_1.jpg',
						'TITLE'=>GetMessage("skyweb24.popuppro_ACTION_TITLE"),
						'SUBTITLE'=>GetMessage("skyweb24.popuppro_ACTION_SUBTITLE"),
						'CONTENT'=>GetMessage("skyweb24.popuppro_ACTION_CONTENT"),
						'LINK_TEXT'=>GetMessage("skyweb24.popuppro_ACTION_LINK_TEXT"),
						'LINK_HREF'=>'https://skyweb24.ru',
						
						'HREF_TARGET'=>'_blank',
						'GOOGLE_FONT'=>'',
						'BUTTON_METRIC'=>''
					)
				),
				array(
					'template'=>'top',
					'name'=>GetMessage("skyweb24.popuppro_TYPE_NAME_ACTION_T3"),
					'color_style'=>'ru_DeepRose',
					'props'=>array(
						'IMG_1_SRC'=>'/bitrix/themes/.default/skyweb24.popuppro/preload/gift_1.jpg',
						'TITLE'=>GetMessage("skyweb24.popuppro_ACTION_TITLE"),
						'SUBTITLE'=>GetMessage("skyweb24.popuppro_ACTION_SUBTITLE"),
						'CONTENT'=>GetMessage("skyweb24.popuppro_ACTION_CONTENT"),
						'LINK_TEXT'=>GetMessage("skyweb24.popuppro_ACTION_LINK_TEXT"),
						'LINK_HREF'=>'https://skyweb24.ru',
						
						'HREF_TARGET'=>'_blank',
						'GOOGLE_FONT'=>'',
						'BUTTON_METRIC'=>''
					)
				)
			),

			/* 4. Ñîöèàëüíûå ñåòè */
			'social'=>array(
				array(
					'template'=>'one',
					'name'=>GetMessage("skyweb24.popuppro_TYPE_NAME_SOCIAL_T1"),
					'active'=>true,
					'props'=>array(
						'TITLE'=>GetMessage("skyweb24.popuppro_SOCIAL_TITLE"),
						'ID_VK'=>'89371159',
						'ID_INST'=>'cats_funny_inst',
						'ID_ODNKL'=>'50582132228315',
						
						'GOOGLE_FONT'=>''
					)
				),
				array(
					'template'=>'all',
					'name'=>GetMessage("skyweb24.popuppro_TYPE_NAME_SOCIAL_T2"),
					'props'=>array(
						'TITLE'=>GetMessage("skyweb24.popuppro_SOCIAL_TITLE"),
						'ID_VK'=>'89371159',
						'ID_INST'=>'cats_funny_inst',
						'ID_ODNKL'=>'50582132228315',
						
						'TYPE_VIEW'=>'123',
						'GOOGLE_FONT'=>''
					)
				)
			),

			/* 5. Ñáîðùèê êîíòàêòîâ */
			'contact'=>array(
				array(
					'template'=>'type1',
					'name'=>GetMessage("skyweb24.popuppro_TYPE_NAME_CONTACT_T1"),
					'active'=>true,
					'color_style'=>'grad_wisteria-red',
					'props'=>array(
						'IMG_1_SRC'=>'/bitrix/themes/.default/skyweb24.popuppro/preload/black_friday.png',
						'TITLE'=>GetMessage("skyweb24.popuppro_CONTACT_TITLE1"),
						'SUBTITLE'=>GetMessage("skyweb24.popuppro_CONTACT_SUBTITLE"),
						'BUTTON_TEXT'=>GetMessage("skyweb24.popuppro_CONTACT_SEND_BUTTON"),
						
						'EMAIL_SHOW'=>'Y',
						'EMAIL_REQUIRED'=>'Y',
						'EMAIL_TITLE'=>'email',
						'EMAIL_PLACEHOLDER'=>GetMessage("skyweb24.popuppro_CONTACT_EMAIL_PLACEHOLDER"),

						'NAME_SHOW'=>'Y',
						'NAME_REQUIRED'=>'Y',
						'NAME_TITLE'=>GetMessage("skyweb24.popuppro_CONTACT_NAME_TITLE"),
						'NAME_PLACEHOLDER'=>GetMessage("skyweb24.popuppro_CONTACT_NAME_PLACEHOLDER"),

						'PHONE_SHOW'=>'Y',
						'PHONE_REQUIRED'=>'Y',
						'PHONE_TITLE'=>GetMessage("skyweb24.popuppro_CONTACT_PHONE_TITLE"),
						'PHONE_PLACEHOLDER'=>GetMessage("skyweb24.popuppro_CONTACT_PHONE_PLACEHOLDER"),

						'DESCRIPTION_SHOW'=>'Y',
						'DESCRIPTION_REQUIRED'=>'Y',
						'DESCRIPTION_TITLE'=>GetMessage("skyweb24.popuppro_CONTACT_DESCRIPTION_TITLE"),
						'DESCRIPTION_PLACEHOLDER'=>GetMessage("skyweb24.popuppro_CONTACT_DESCRIPTION_PLACEHOLDER"),

						'USE_CONSENT_SHOW'=>'Y',
						'CONSENT_LIST'=>'1',
						
						'BUTTON_METRIC'=>'',
						'GOOGLE_FONT'=>'',
					)
				),
				array(
					'template'=>'type2',
					'name'=>GetMessage("skyweb24.popuppro_TYPE_NAME_CONTACT_T2"),
					'color_style'=>'grad_blue-wisteria',
					'color_styles'=>array(
						'grad_greensea-green'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_GRAD_GREENSEA-GREEN"),
						'grad_greensea-blue'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_GRAD_GREENSEA-BLUE"),
						'grad_green-blue'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_GRAD_GREEN-BLUE"),
						'grad_red-orange'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_GRAD_RED-ORANGE"),
						'grad_blue-wisteria'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_GRAD_BLUE-WISTERIA"),
						'grad_wisteria-red'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_GRAD_WISTERIA-RED"),
					),
					'props'=>array(
						'TITLE'=>GetMessage("skyweb24.popuppro_CONTACT_TITLE2"),
						'SUBTITLE'=>GetMessage("skyweb24.popuppro_CONTACT_SUBTITLE2"),
						'BUTTON_TEXT'=>GetMessage("skyweb24.popuppro_CONTACT_SEND_BUTTON2"),
						
						'EMAIL_SHOW'=>'Y',
						'EMAIL_REQUIRED'=>'Y',
						'EMAIL_TITLE'=>'email',
						'EMAIL_PLACEHOLDER'=>GetMessage("skyweb24.popuppro_CONTACT_EMAIL_PLACEHOLDER"),

						'NAME_SHOW'=>'Y',
						'NAME_REQUIRED'=>'Y',
						'NAME_TITLE'=>GetMessage("skyweb24.popuppro_CONTACT_NAME_TITLE"),
						'NAME_PLACEHOLDER'=>GetMessage("skyweb24.popuppro_CONTACT_NAME_PLACEHOLDER"),

						'PHONE_SHOW'=>'Y',
						'PHONE_REQUIRED'=>'Y',
						'PHONE_TITLE'=>GetMessage("skyweb24.popuppro_CONTACT_PHONE_TITLE"),
						'PHONE_PLACEHOLDER'=>GetMessage("skyweb24.popuppro_CONTACT_PHONE_PLACEHOLDER"),

						'DESCRIPTION_SHOW'=>'Y',
						'DESCRIPTION_REQUIRED'=>'Y',
						'DESCRIPTION_TITLE'=>GetMessage("skyweb24.popuppro_CONTACT_DESCRIPTION_TITLE"),
						'DESCRIPTION_PLACEHOLDER'=>GetMessage("skyweb24.popuppro_CONTACT_DESCRIPTION_PLACEHOLDER"),

						'USE_CONSENT_SHOW'=>'Y',
						'CONSENT_LIST'=>'1',
						
						'BUTTON_METRIC'=>'',
						'GOOGLE_FONT'=>'',
					)
				),
				array(
					'template'=>'type3',
					'name'=>GetMessage("skyweb24.popuppro_TYPE_NAME_CONTACT_T3"),
					'color_style'=>'ca_BleuDeFrance',
					'color_styles'=>array(
						'orange'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_ORANGE"),
						'green'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_GREEN"),
						'greensea'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_GREENSEA"),
						'red'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_RED"),
						'blue'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_BLUE"),
						'pumpkin'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_PUMPKIN"),
						'wisteria'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_WISTERIA"),
						
						'au_GreenlandGreen'=>GetMessage("skyweb24.popuppro_au_GreenlandGreen"),
						'au_Turbo'=>GetMessage("skyweb24.popuppro_au_Turbo"),
						'au_PureApple'=>GetMessage("skyweb24.popuppro_au_PureApple"),
						'au_CarminePink'=>GetMessage("skyweb24.popuppro_au_CarminePink"),
						'au_HintOfIcePack'=>GetMessage("skyweb24.popuppro_au_HintOfIcePack"),
						'au_QuinceJelly'=>GetMessage("skyweb24.popuppro_au_QuinceJelly"),
						'au_WizardGrey'=>GetMessage("skyweb24.popuppro_au_WizardGrey"),
						'au_Blurple'=>GetMessage("skyweb24.popuppro_au_Blurple"),
						'au_DeepCove'=>GetMessage("skyweb24.popuppro_au_DeepCove"),
						'au_SteelPink'=>GetMessage("skyweb24.popuppro_au_SteelPink"),
						
						'ca_Cyanite'=>GetMessage("skyweb24.popuppro_ca_Cyanite"),
						'ca_DarkMountainMeadow'=>GetMessage("skyweb24.popuppro_ca_DarkMountainMeadow"),
						'ca_Amour'=>GetMessage("skyweb24.popuppro_ca_Amour"),
						'ca_AquaVelvet'=>GetMessage("skyweb24.popuppro_ca_AquaVelvet"),
						'ca_DoubleDragonSkin'=>GetMessage("skyweb24.popuppro_ca_DoubleDragonSkin"),
						'ca_LianHongLotusPink'=>GetMessage("skyweb24.popuppro_ca_LianHongLotusPink"),
						'ca_BleuDeFrance'=>GetMessage("skyweb24.popuppro_ca_BleuDeFrance"),
						'ca_StormPetrel'=>GetMessage("skyweb24.popuppro_ca_StormPetrel"),
						'ca_Bluebell'=>GetMessage("skyweb24.popuppro_ca_Bluebell"),
						'ca_ImperialPrimer'=>GetMessage("skyweb24.popuppro_ca_ImperialPrimer"),
						
						'ru_BlueCuracao'=>GetMessage("skyweb24.popuppro_ru_BlueCuracao"),
						'ru_Summertime'=>GetMessage("skyweb24.popuppro_ru_Summertime"),
						'ru_PorcelainRose'=>GetMessage("skyweb24.popuppro_ru_PorcelainRose"),
						'ru_DeepRose'=>GetMessage("skyweb24.popuppro_ru_DeepRose"),
						'ru_Tigerlily'=>GetMessage("skyweb24.popuppro_ru_Tigerlily"),
						'ru_SawtoothAak'=>GetMessage("skyweb24.popuppro_ru_SawtoothAak"),
						'ru_FlamingoPink'=>GetMessage("skyweb24.popuppro_ru_FlamingoPink"),
						'ru_Cornflower'=>GetMessage("skyweb24.popuppro_ru_Cornflower"),
						'ru_Biscay'=>GetMessage("skyweb24.popuppro_ru_Biscay"),
						'ru_PurpleCorallite'=>GetMessage("skyweb24.popuppro_ru_PurpleCorallite"),
					),
					'props'=>array(
						'IMG_1_SRC'=>'/bitrix/themes/.default/skyweb24.popuppro/preload/bisnesplan.png',
						'TITLE'=>GetMessage("skyweb24.popuppro_CONTACT_TITLE3"),
						'SUBTITLE'=>GetMessage("skyweb24.popuppro_CONTACT_SUBTITLE3"),
						'BUTTON_TEXT'=>GetMessage("skyweb24.popuppro_CONTACT_SEND_BUTTON3"),
						
						'EMAIL_SHOW'=>'Y',
						'EMAIL_REQUIRED'=>'Y',
						'EMAIL_TITLE'=>'email',
						'EMAIL_PLACEHOLDER'=>GetMessage("skyweb24.popuppro_CONTACT_EMAIL_PLACEHOLDER"),

						'NAME_SHOW'=>'Y',
						'NAME_REQUIRED'=>'Y',
						'NAME_TITLE'=>GetMessage("skyweb24.popuppro_CONTACT_NAME_TITLE"),
						'NAME_PLACEHOLDER'=>GetMessage("skyweb24.popuppro_CONTACT_NAME_PLACEHOLDER"),

						'PHONE_SHOW'=>'Y',
						'PHONE_REQUIRED'=>'Y',
						'PHONE_TITLE'=>GetMessage("skyweb24.popuppro_CONTACT_PHONE_TITLE"),
						'PHONE_PLACEHOLDER'=>GetMessage("skyweb24.popuppro_CONTACT_PHONE_PLACEHOLDER"),

						'DESCRIPTION_SHOW'=>'Y',
						'DESCRIPTION_REQUIRED'=>'Y',
						'DESCRIPTION_TITLE'=>GetMessage("skyweb24.popuppro_CONTACT_DESCRIPTION_TITLE3"),
						'DESCRIPTION_PLACEHOLDER'=>GetMessage("skyweb24.popuppro_CONTACT_DESCRIPTION_PLACEHOLDER3"),

						'USE_CONSENT_SHOW'=>'Y',
						'CONSENT_LIST'=>'1',
						
						'BUTTON_METRIC'=>'',
						'GOOGLE_FONT'=>'',
					)
				),
				array(
					'template'=>'type4',
					'name'=>GetMessage("skyweb24.popuppro_TYPE_NAME_CONTACT_T4"),
					'color_style'=>'ru_BlueCuracao',
					'color_styles'=>array(
						'orange'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_ORANGE"),
						'green'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_GREEN"),
						'greensea'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_GREENSEA"),
						'red'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_RED"),
						'blue'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_BLUE"),
						'pumpkin'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_PUMPKIN"),
						'wisteria'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_WISTERIA"),
						
						'au_GreenlandGreen'=>GetMessage("skyweb24.popuppro_au_GreenlandGreen"),
						'au_Turbo'=>GetMessage("skyweb24.popuppro_au_Turbo"),
						'au_PureApple'=>GetMessage("skyweb24.popuppro_au_PureApple"),
						'au_CarminePink'=>GetMessage("skyweb24.popuppro_au_CarminePink"),
						'au_HintOfIcePack'=>GetMessage("skyweb24.popuppro_au_HintOfIcePack"),
						'au_QuinceJelly'=>GetMessage("skyweb24.popuppro_au_QuinceJelly"),
						'au_WizardGrey'=>GetMessage("skyweb24.popuppro_au_WizardGrey"),
						'au_Blurple'=>GetMessage("skyweb24.popuppro_au_Blurple"),
						'au_DeepCove'=>GetMessage("skyweb24.popuppro_au_DeepCove"),
						'au_SteelPink'=>GetMessage("skyweb24.popuppro_au_SteelPink"),
						
						'ca_Cyanite'=>GetMessage("skyweb24.popuppro_ca_Cyanite"),
						'ca_DarkMountainMeadow'=>GetMessage("skyweb24.popuppro_ca_DarkMountainMeadow"),
						'ca_Amour'=>GetMessage("skyweb24.popuppro_ca_Amour"),
						'ca_AquaVelvet'=>GetMessage("skyweb24.popuppro_ca_AquaVelvet"),
						'ca_DoubleDragonSkin'=>GetMessage("skyweb24.popuppro_ca_DoubleDragonSkin"),
						'ca_LianHongLotusPink'=>GetMessage("skyweb24.popuppro_ca_LianHongLotusPink"),
						'ca_BleuDeFrance'=>GetMessage("skyweb24.popuppro_ca_BleuDeFrance"),
						'ca_StormPetrel'=>GetMessage("skyweb24.popuppro_ca_StormPetrel"),
						'ca_Bluebell'=>GetMessage("skyweb24.popuppro_ca_Bluebell"),
						'ca_ImperialPrimer'=>GetMessage("skyweb24.popuppro_ca_ImperialPrimer"),
						
						'ru_BlueCuracao'=>GetMessage("skyweb24.popuppro_ru_BlueCuracao"),
						'ru_Summertime'=>GetMessage("skyweb24.popuppro_ru_Summertime"),
						'ru_PorcelainRose'=>GetMessage("skyweb24.popuppro_ru_PorcelainRose"),
						'ru_DeepRose'=>GetMessage("skyweb24.popuppro_ru_DeepRose"),
						'ru_Tigerlily'=>GetMessage("skyweb24.popuppro_ru_Tigerlily"),
						'ru_SawtoothAak'=>GetMessage("skyweb24.popuppro_ru_SawtoothAak"),
						'ru_FlamingoPink'=>GetMessage("skyweb24.popuppro_ru_FlamingoPink"),
						'ru_Cornflower'=>GetMessage("skyweb24.popuppro_ru_Cornflower"),
						'ru_Biscay'=>GetMessage("skyweb24.popuppro_ru_Biscay"),
						'ru_PurpleCorallite'=>GetMessage("skyweb24.popuppro_ru_PurpleCorallite"),
					),
					'props'=>array(
						'IMG_1_SRC'=>'/bitrix/themes/.default/skyweb24.popuppro/preload/cotntact_type4.jpg',
						'TITLE'=>GetMessage("skyweb24.popuppro_CONTACT_TITLE4"),
						'SUBTITLE'=>GetMessage("skyweb24.popuppro_CONTACT_SUBTITLE4"),
						'BUTTON_TEXT'=>GetMessage("skyweb24.popuppro_CONTACT_SEND_BUTTON4"),
						
						'EMAIL_SHOW'=>'Y',
						'EMAIL_REQUIRED'=>'Y',
						'EMAIL_TITLE'=>'email',
						'EMAIL_PLACEHOLDER'=>GetMessage("skyweb24.popuppro_CONTACT_EMAIL_PLACEHOLDER"),

						'NAME_SHOW'=>'Y',
						'NAME_REQUIRED'=>'Y',
						'NAME_TITLE'=>GetMessage("skyweb24.popuppro_CONTACT_NAME_TITLE"),
						'NAME_PLACEHOLDER'=>GetMessage("skyweb24.popuppro_CONTACT_NAME_PLACEHOLDER"),

						'PHONE_SHOW'=>'Y',
						'PHONE_REQUIRED'=>'Y',
						'PHONE_TITLE'=>GetMessage("skyweb24.popuppro_CONTACT_PHONE_TITLE"),
						'PHONE_PLACEHOLDER'=>GetMessage("skyweb24.popuppro_CONTACT_PHONE_PLACEHOLDER"),

						'DESCRIPTION_SHOW'=>'Y',
						'DESCRIPTION_REQUIRED'=>'Y',
						'DESCRIPTION_TITLE'=>GetMessage("skyweb24.popuppro_CONTACT_DESCRIPTION_TITLE4"),
						'DESCRIPTION_PLACEHOLDER'=>GetMessage("skyweb24.popuppro_CONTACT_DESCRIPTION_PLACEHOLDER3"),

						'USE_CONSENT_SHOW'=>'Y',
						'CONSENT_LIST'=>'1',
						
						'BUTTON_METRIC'=>'',
						'GOOGLE_FONT'=>'',
					)
				)

			),

			/* 6. Ïîäåëèòüñÿ â ñîö ñåòè */
			'share'=>array(
				array(
					'template'=>'default',
					'name'=>GetMessage("skyweb24.popuppro_TYPE_NAME_SHARE_T1"),
					'active'=>true,
					'props'=>array(
						'TITLE'=>GetMessage("skyweb24.popuppro_SHARE_TITLE"),
						'SUBTITLE'=>GetMessage("skyweb24.popuppro_SHARE_SUBTITLE"),
						'SOC_VK'=>'Y',
						'SOC_FB'=>'Y',
						'SOC_OD'=>'Y',
						'SOC_TW'=>'Y',
						'SOC_GP'=>'Y',
						'SOC_MR'=>'Y',
						'HREF_TARGET'=>'_blank',
						
						'GOOGLE_FONT'=>''
					)
				)
			),

			/* 7. HTML */
			'html'=>array(
				array(
					'template'=>'default',
					'name'=>GetMessage("skyweb24.popuppro_TYPE_NAME_HTML_T1"),
					'active'=>true,
					'props'=>array(
						'TEXTAREA'=>'<div style="text-align:center; padding:10px; background:#16a085"><h1 style="text-align:center; padding:10px; margin:0; background:#e67e22">'.GetMessage("skyweb24.popuppro_TYPE_NAME_HTML_SOMECODE").'</h1></div>',
						

					)
				)
			),
			/* 8. Îêíî 18+ */
			'age'=>array(
				array(
					'template'=>'default',
					'name'=>GetMessage('skyweb24.popuppro_TYPE_NAME_AGE_T1'),
					'active'=>true,
					'props'=>array(
						'IMG_1_SRC'=>'/bitrix/themes/.default/skyweb24.popuppro/preload/age.png',
						'TITLE'=>GetMessage('skyweb24.popuppro_AGE_CONTENT_TITLE_DEF'),
						'BUTTON_TEXT_Y'=>GetMessage('skyweb24.popuppro_AGE_CONTENT_BUTTON_Y_DEF'),
						'BUTTON_TEXT_N'=>GetMessage('skyweb24.popuppro_AGE_CONTENT_BUTTON_N_DEF'),
						'HREF_LINK'=>'http://disney.ru/',
						
						'GOOGLE_FONT'=>''
					)
				)
			)
		);
		if (\Bitrix\Main\Loader::IncludeModule('sale')){
			/* 9.  Êóïîí íà ñêèäêó*/
			/*$template_message=CEventMessage::GetList($by="site_id", $order="desc", array('TYPE_ID'=>'SKYWEB24_POPUPPRO_SEND_COUPON'));
			$serviceMessage=array();
			while($t_m=$template_message->Fetch()){$serviceMessage[]=$t_m;}*/
			
			$serviceMessage=Skyweb24\Popuppro\Tools::getMailTemplates('SKYWEB24_POPUPPRO_SEND_COUPON');
			
			 $templates['coupon']=array(
				array(
					'template'=>'type1',
					'name'=>GetMessage("skyweb24.popuppro_TYPE_NAME_COUPON_T1"),
					'active'=>true,
					'color_style'=>'ru_PurpleCorallite',
					'color_styles'=>array(
						'orange'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_ORANGE"),
						'green'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_GREEN"),
						'greensea'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_GREENSEA"),
						'red'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_RED"),
						'blue'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_BLUE"),
						'pumpkin'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_PUMPKIN"),
						'wisteria'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_WISTERIA"),
						
						'au_GreenlandGreen'=>GetMessage("skyweb24.popuppro_au_GreenlandGreen"),
						'au_Turbo'=>GetMessage("skyweb24.popuppro_au_Turbo"),
						'au_PureApple'=>GetMessage("skyweb24.popuppro_au_PureApple"),
						'au_CarminePink'=>GetMessage("skyweb24.popuppro_au_CarminePink"),
						'au_HintOfIcePack'=>GetMessage("skyweb24.popuppro_au_HintOfIcePack"),
						'au_QuinceJelly'=>GetMessage("skyweb24.popuppro_au_QuinceJelly"),
						'au_WizardGrey'=>GetMessage("skyweb24.popuppro_au_WizardGrey"),
						'au_Blurple'=>GetMessage("skyweb24.popuppro_au_Blurple"),
						'au_DeepCove'=>GetMessage("skyweb24.popuppro_au_DeepCove"),
						'au_SteelPink'=>GetMessage("skyweb24.popuppro_au_SteelPink"),
						
						'ca_Cyanite'=>GetMessage("skyweb24.popuppro_ca_Cyanite"),
						'ca_DarkMountainMeadow'=>GetMessage("skyweb24.popuppro_ca_DarkMountainMeadow"),
						'ca_Amour'=>GetMessage("skyweb24.popuppro_ca_Amour"),
						'ca_AquaVelvet'=>GetMessage("skyweb24.popuppro_ca_AquaVelvet"),
						'ca_DoubleDragonSkin'=>GetMessage("skyweb24.popuppro_ca_DoubleDragonSkin"),
						'ca_LianHongLotusPink'=>GetMessage("skyweb24.popuppro_ca_LianHongLotusPink"),
						'ca_BleuDeFrance'=>GetMessage("skyweb24.popuppro_ca_BleuDeFrance"),
						'ca_StormPetrel'=>GetMessage("skyweb24.popuppro_ca_StormPetrel"),
						'ca_Bluebell'=>GetMessage("skyweb24.popuppro_ca_Bluebell"),
						'ca_ImperialPrimer'=>GetMessage("skyweb24.popuppro_ca_ImperialPrimer"),
						
						'ru_BlueCuracao'=>GetMessage("skyweb24.popuppro_ru_BlueCuracao"),
						'ru_Summertime'=>GetMessage("skyweb24.popuppro_ru_Summertime"),
						'ru_PorcelainRose'=>GetMessage("skyweb24.popuppro_ru_PorcelainRose"),
						'ru_DeepRose'=>GetMessage("skyweb24.popuppro_ru_DeepRose"),
						'ru_Tigerlily'=>GetMessage("skyweb24.popuppro_ru_Tigerlily"),
						'ru_SawtoothAak'=>GetMessage("skyweb24.popuppro_ru_SawtoothAak"),
						'ru_FlamingoPink'=>GetMessage("skyweb24.popuppro_ru_FlamingoPink"),
						'ru_Cornflower'=>GetMessage("skyweb24.popuppro_ru_Cornflower"),
						'ru_Biscay'=>GetMessage("skyweb24.popuppro_ru_Biscay"),
						'ru_PurpleCorallite'=>GetMessage("skyweb24.popuppro_ru_PurpleCorallite"),
					),
					'props'=>array(
						'IMG_1_SRC'=>'/bitrix/themes/.default/skyweb24.popuppro/preload/coupon_1.jpg',
						'TITLE'=>GetMessage("skyweb24.popuppro_COUPON_CONTENT_TITLE_DEFAULT"),
						'SUBTITLE'=>GetMessage("skyweb24.popuppro_COUPON_CONTENT_SUBTITLE_DEFAULT"),
						'BUTTON_TEXT'=>GetMessage("skyweb24.popuppro_COUPON_CONTENT_BUTTON_TEXT_DEFAULT"),
						'EMAIL_SHOW'=>'Y',
						'EMAIL_PLACEHOLDER'=>GetMessage("skyweb24.popuppro_CONTACT_EMAIL_PLACEHOLDER"),
						'EMAIL_NOT_NEW'=>'N',
						'EMAIL_NOT_NEW_TEXT'=>GetMessage('skyweb24.popuppro_COUPON_CONTENT_EMAIL_NOT_NEW_DEFAULT'),
						
						'RULE_ID'=>GetMessage("skyweb24.popuppro_COUPON_CONTENT_MAIN_RULE_ID_DEFAULT"),
						'TIMING'=>GetMessage("skyweb24.popuppro_COUPON_CONTENT_MAIN_TIMING_DEFAULT"),
						
						'GOOGLE_FONT'=>'',
						'BUTTON_METRIC'=>'',
						
						
						'EMAIL_ADD2BASE'=>'N',
						
						'EMAIL_EMAIL_TO'=>'N',
						'EMAIL_TEMPLATE'=>$serviceMessage[0]['ID'],
						
					)
				),
			);

			$template_message_discount=CEventMessage::GetList($by="site_id", $order="desc", array('TYPE_ID'=>'SKYWEB24_POPUPPRO_DISCOUNT_SEND'));
			$serviceMessageDiscount=array();
			while($t_m_d=$template_message_discount->Fetch()){$serviceMessageDiscount[]=$t_m_d;}
			$templates['discount']=array(array(
				'template'=>'default',
					'name'=>GetMessage("skyweb24.popuppro_TYPE_NAME_DISCOUNT_T1"),
					'active'=>true,
					
					'color_style'=>'ca_Cyanite',
					'color_styles'=>array(
						''=>'Default',
						'au_GreenlandGreen'=>GetMessage("skyweb24.popuppro_au_GreenlandGreen"),
						'au_Turbo'=>GetMessage("skyweb24.popuppro_au_Turbo"),
						'au_PureApple'=>GetMessage("skyweb24.popuppro_au_PureApple"),
						'au_CarminePink'=>GetMessage("skyweb24.popuppro_au_CarminePink"),
						'au_HintOfIcePack'=>GetMessage("skyweb24.popuppro_au_HintOfIcePack"),
						'au_QuinceJelly'=>GetMessage("skyweb24.popuppro_au_QuinceJelly"),
						'au_WizardGrey'=>GetMessage("skyweb24.popuppro_au_WizardGrey"),
						'au_Blurple'=>GetMessage("skyweb24.popuppro_au_Blurple"),
						'au_DeepCove'=>GetMessage("skyweb24.popuppro_au_DeepCove"),
						'au_SteelPink'=>GetMessage("skyweb24.popuppro_au_SteelPink"),
						
						'ca_Cyanite'=>GetMessage("skyweb24.popuppro_ca_Cyanite"),
						'ca_DarkMountainMeadow'=>GetMessage("skyweb24.popuppro_ca_DarkMountainMeadow"),
						'ca_Amour'=>GetMessage("skyweb24.popuppro_ca_Amour"),
						'ca_AquaVelvet'=>GetMessage("skyweb24.popuppro_ca_AquaVelvet"),
						'ca_DoubleDragonSkin'=>GetMessage("skyweb24.popuppro_ca_DoubleDragonSkin"),
						'ca_LianHongLotusPink'=>GetMessage("skyweb24.popuppro_ca_LianHongLotusPink"),
						'ca_BleuDeFrance'=>GetMessage("skyweb24.popuppro_ca_BleuDeFrance"),
						'ca_StormPetrel'=>GetMessage("skyweb24.popuppro_ca_StormPetrel"),
						'ca_Bluebell'=>GetMessage("skyweb24.popuppro_ca_Bluebell"),
						'ca_ImperialPrimer'=>GetMessage("skyweb24.popuppro_ca_ImperialPrimer"),
						
						'ru_BlueCuracao'=>GetMessage("skyweb24.popuppro_ru_BlueCuracao"),
						'ru_Summertime'=>GetMessage("skyweb24.popuppro_ru_Summertime"),
						'ru_PorcelainRose'=>GetMessage("skyweb24.popuppro_ru_PorcelainRose"),
						'ru_DeepRose'=>GetMessage("skyweb24.popuppro_ru_DeepRose"),
						'ru_Tigerlily'=>GetMessage("skyweb24.popuppro_ru_Tigerlily"),
						'ru_SawtoothAak'=>GetMessage("skyweb24.popuppro_ru_SawtoothAak"),
						'ru_FlamingoPink'=>GetMessage("skyweb24.popuppro_ru_FlamingoPink"),
						'ru_Cornflower'=>GetMessage("skyweb24.popuppro_ru_Cornflower"),
						'ru_Biscay'=>GetMessage("skyweb24.popuppro_ru_Biscay"),
						'ru_PurpleCorallite'=>GetMessage("skyweb24.popuppro_ru_PurpleCorallite"),
					),
					'props'=>array(
						'IMG_1_SRC'=>'/bitrix/themes/.default/skyweb24.popuppro/preload/discount_logo.png',
						'IMG_2_SRC'=>'/bitrix/themes/.default/skyweb24.popuppro/preload/discount_girl.png',
						'TITLE'=>GetMessage("skyweb24.popuppro_TYPE_DISCOUNT_TITLE_DEFAULT"),
						'SUBTITLE'=>GetMessage("skyweb24.popuppro_TYPE_DISCOUNT_SUBTITLE_DEFAULT"),
						'BUTTON_TEXT'=>GetMessage("skyweb24.popuppro_TYPE_DISCOUNT_BUTTON_DEFAULT"),
						
						'NAME_SHOW'=>'Y',
						'NAME_REQUIRED'=>'Y',
						'NAME_TITLE'=>GetMessage("skyweb24.popuppro_DISCOUNT_NAME_TITLE"),
						
						'LASTNAME_SHOW'=>'Y',
						'LASTNAME_REQUIRED'=>'Y',
						'LASTNAME_TITLE'=>GetMessage("skyweb24.popuppro_DISCOUNT_LASTNAME_TITLE"),

						'PHONE_SHOW'=>'Y',
						'PHONE_REQUIRED'=>'Y',
						'PHONE_TITLE'=>GetMessage("skyweb24.popuppro_DISCOUNT_PHONE_TITLE"),
						
						'EMAIL_SHOW'=>'Y',
						'EMAIL_REQUIRED'=>'Y',
						'EMAIL_TITLE'=>GetMessage("skyweb24.popuppro_DISCOUNT_EMAIL_TITLE"),
						'EMAIL_ADD2BASE'=>'Y',
						'EMAIL_EMAIL_TO'=>'Y',
						
						'EMAIL_NOT_NEW'=>'Y',
						'EMAIL_NOT_NEW_TEXT'=>GetMessage("skyweb24.popuppro_DISCOUNT_EMAIL_NOT_NEW"),
						
						'USE_CONSENT_SHOW'=>'Y',
						'CONSENT_LIST'=>'1',						
						
						'RULE_ID'=>'',
						'DISCOUNT_MASK'=>'0000#####',
						'USER_GROUP'=>'',
						'EMAIL_TEMPLATE_D'=>$serviceMessageDiscount[0]['ID'],						
						'GOOGLE_FONT'=>'',
						'BUTTON_METRIC'=>'',
					)
				)
			);
		}
		
		$template_message_roulette=CEventMessage::GetList($by="site_id", $order="desc", array('TYPE_ID'=>'SKYWEB24_POPUPPRO_ROULETTE_SEND'));
		$serviceMessageRoulette=array();
		while($t_m_r=$template_message_roulette->Fetch()){$serviceMessageRoulette[]=$t_m_r;}
		$templates['roulette']=array(
				array(
					'template'=>'default',
					'name'=>GetMessage("skyweb24.popuppro_TYPE_NAME_ROULETTE_T1"),
					'active'=>true,
					'color_style'=>'au_PureApple',
					'color_styles'=>array(
						'orange'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_ORANGE"),
						'green'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_GREEN"),
						'greensea'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_GREENSEA"),
						'red'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_RED"),
						'blue'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_BLUE"),
						'pumpkin'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_PUMPKIN"),
						'wisteria'=>GetMessage("skyweb24.popuppro_ACTION_COLOR_WISTERIA"),
						
						'au_GreenlandGreen'=>GetMessage("skyweb24.popuppro_au_GreenlandGreen"),
						'au_Turbo'=>GetMessage("skyweb24.popuppro_au_Turbo"),
						'au_PureApple'=>GetMessage("skyweb24.popuppro_au_PureApple"),
						'au_CarminePink'=>GetMessage("skyweb24.popuppro_au_CarminePink"),
						'au_HintOfIcePack'=>GetMessage("skyweb24.popuppro_au_HintOfIcePack"),
						'au_QuinceJelly'=>GetMessage("skyweb24.popuppro_au_QuinceJelly"),
						'au_WizardGrey'=>GetMessage("skyweb24.popuppro_au_WizardGrey"),
						'au_Blurple'=>GetMessage("skyweb24.popuppro_au_Blurple"),
						'au_DeepCove'=>GetMessage("skyweb24.popuppro_au_DeepCove"),
						'au_SteelPink'=>GetMessage("skyweb24.popuppro_au_SteelPink"),
						
						'ca_Cyanite'=>GetMessage("skyweb24.popuppro_ca_Cyanite"),
						'ca_DarkMountainMeadow'=>GetMessage("skyweb24.popuppro_ca_DarkMountainMeadow"),
						'ca_Amour'=>GetMessage("skyweb24.popuppro_ca_Amour"),
						'ca_AquaVelvet'=>GetMessage("skyweb24.popuppro_ca_AquaVelvet"),
						'ca_DoubleDragonSkin'=>GetMessage("skyweb24.popuppro_ca_DoubleDragonSkin"),
						'ca_LianHongLotusPink'=>GetMessage("skyweb24.popuppro_ca_LianHongLotusPink"),
						'ca_BleuDeFrance'=>GetMessage("skyweb24.popuppro_ca_BleuDeFrance"),
						'ca_StormPetrel'=>GetMessage("skyweb24.popuppro_ca_StormPetrel"),
						'ca_Bluebell'=>GetMessage("skyweb24.popuppro_ca_Bluebell"),
						'ca_ImperialPrimer'=>GetMessage("skyweb24.popuppro_ca_ImperialPrimer"),
						
						'ru_BlueCuracao'=>GetMessage("skyweb24.popuppro_ru_BlueCuracao"),
						'ru_Summertime'=>GetMessage("skyweb24.popuppro_ru_Summertime"),
						'ru_PorcelainRose'=>GetMessage("skyweb24.popuppro_ru_PorcelainRose"),
						'ru_DeepRose'=>GetMessage("skyweb24.popuppro_ru_DeepRose"),
						'ru_Tigerlily'=>GetMessage("skyweb24.popuppro_ru_Tigerlily"),
						'ru_SawtoothAak'=>GetMessage("skyweb24.popuppro_ru_SawtoothAak"),
						'ru_FlamingoPink'=>GetMessage("skyweb24.popuppro_ru_FlamingoPink"),
						'ru_Cornflower'=>GetMessage("skyweb24.popuppro_ru_Cornflower"),
						'ru_Biscay'=>GetMessage("skyweb24.popuppro_ru_Biscay"),
						'ru_PurpleCorallite'=>GetMessage("skyweb24.popuppro_ru_PurpleCorallite"),				
					),
					'props'=>array(
						'TITLE'=>GetMessage("skyweb24.popuppro_TYPE_ROULETTE_TITLE_DEFAULT"),
						'SUBTITLE'=>GetMessage("skyweb24.popuppro_TYPE_ROULETTE_SUBTITLE_DEFAULT"),
						'BUTTON_TEXT'=>GetMessage("skyweb24.popuppro_TYPE_ROULETTE_BUTTON_DEFAULT"),
						'RESULT_TEXT'=>GetMessage("skyweb24.popuppro_TYPE_ROULETTE_RESULT_DEFAULT"),
						'NOTHING_TEXT'=>GetMessage("skyweb24.popuppro_TYPE_ROULETTE_NOTHING_DEFAULT"),
						'EMAIL_SHOW'=>'Y',
						'EMAIL_PLACEHOLDER'=>GetMessage("skyweb24.popuppro_TYPE_ROULETTE_PLACEHOLDER_DEFAULT"),
						'EMAIL_NOT_NEW'=>'N',
						'EMAIL_NOT_NEW_TEXT'=>GetMessage('skyweb24.popuppro_COUPON_CONTENT_EMAIL_NOT_NEW_DEFAULT'),
					)
				)
			 );
		if(\Bitrix\Main\Loader::IncludeModule('sale')){
			$templates['roulette'][0]['props']['TIMING']=GetMessage("skyweb24.popuppro_COUPON_CONTENT_MAIN_TIMING_DEFAULT");
		}
		$templates['roulette'][0]['props']['GOOGLE_FONT']='';
		$templates['roulette'][0]['props']['BUTTON_METRIC']='';
		
		$templates['roulette'][0]['props']['EMAIL_ADD2BASE']='N';
		$templates['roulette'][0]['props']['MAIL_TEMPLATE']=$serviceMessageRoulette[0]['ID'];
		
		
		
		
		$customTemplates=$this->getCustomTemplates();
		foreach($templates as $nextKey=>&$nextTemplate){
			foreach($nextTemplate as &$typeTemplate){
				$typeTemplate['props']['SHOW_CLOSEBUTTON']='Y';
				$typeTemplate['props']['CLOSE_AUTOHIDE']='Y';
				$typeTemplate['props']['CLOSE_TEXTBOX']='N';
				$typeTemplate['props']['CLOSE_TEXTAREA']='';
				$typeTemplate['props']['SHOW_ANIMATION']='none';
				$typeTemplate['props']['HIDE_ANIMATION']='none';
				$typeTemplate['props']['BACKGROUND_COLOR']='#000000';
				$typeTemplate['props']['BACKGROUND_OPACITY']='50';
				$typeTemplate['props']['POSITION_LEFT']='';
				$typeTemplate['props']['POSITION_RIGHT']='';
				$typeTemplate['props']['POSITION_TOP']='';
				$typeTemplate['props']['POSITION_BOTTOM']='';
				$typeTemplate['props']['POSITION_FIXED']='';
			}
			if(!empty($customTemplates[$nextKey])){
				$nextTemplate=array_merge($nextTemplate, $customTemplates[$nextKey]);
			}
		}
		return $templates;
	}

	private function getCustomPreset(){
		$templates=$this->getTemplatesPreset();
		$templates=$this->getCustomColors($templates);
		//$templates=$this->getCustomTemplates($templates);
		return $templates;
	}

	private function getCustomTemplates(){
		global $DB;
		$retArr=array();
		$res = $DB->Query('select * from '.$this->tableTemplates.' order by id;');
		while($row = $res->Fetch()){
			$additionalColorThemes[$row['template']]['custom_'.$row['id']]=$row['name'].' ['.$row['id'].']';
			$retArr[$row['type']][]=unserialize($row['template']);
		}
		return $retArr;
	}

	private function getCustomColors($templates){
		global $DB;
		$additionalColorThemes=array();
		$res = $DB->Query('select * from '.$this->tableColorThemes.' order by template, id;');
		while($row = $res->Fetch()){
			$additionalColorThemes[$row['template']]['custom_'.$row['id']]=$row['name'].' ['.$row['id'].']';
		}
		$types=$this->getTypesPreset();
		foreach($templates as $keyType=>&$nextType){
			foreach($nextType as $keyTemplate=>&$nextTemplate){
				if(empty($nextTemplate['color_styles']) && !empty($types[$keyType]['color_style'])){
					$nextTemplate['color_styles']=$types[$keyType]['color_style'];
				}
				if(!empty($nextTemplate['color_styles']) && !empty($additionalColorThemes[$keyType.'_'.$nextTemplate['template']])){
					$nextTemplate['color_styles']=array_merge($nextTemplate['color_styles'], $additionalColorThemes[$keyType.'_'.$nextTemplate['template']]);
				}
			}
		}
		return $templates;
	}

	public function getTemplates(){
		//$templates=$this->getTemplatesPreset();
		$templates=$this->getCustomPreset();
		if($this->idPopup!='new'){
			$settings=$this->getSetting($this->idPopup);

			foreach($templates[$settings['view']['type']] as &$nextTemplate){
				if($nextTemplate['template']==$settings['view']['template']){
					$nextTemplate['active']=true;
					$nextTemplate['color_style']=$settings['view']['color_style'];
					//$nextTemplate['props']=array();
					foreach($settings['view']['props'] as $keyProp=>$valProp){
						if(strpos($keyProp, 'IMG_')!==false && strpos($keyProp, '_id')!==false){
							continue;
						}
						if(strpos($keyProp, 'IMG_')!==false && strpos($keyProp, '_id')===false && intval($valProp)>0){
							$nextTemplate['props'][$keyProp.'_id']=$valProp;
							$valProp=CFile::GetPath($valProp);
						}
						if(strpos($keyProp, 'IMG_')!==false && strpos($keyProp, '_id')===false && empty($valProp)){
							$valProp=$nextTemplate['props'][$keyProp];
						}

						$nextTemplate['props'][$keyProp]=$valProp;
						/*if($keyProp=='HREF_TARGET'){
							$nextTemplate['props'][$keyProp]='123';
						}*/
					}
				}else{
					$nextTemplate['active']=false;
				}
			}
		}

		return $templates;
	}

	private function getConditionsPreset(){
		$conditionArr=array(
			'active'=>false,
			'sort'=>500,
			//'dateStart'=>ConvertTimeStamp(time(), "FULL", SITE_ID),
			//'dateFinish'=>ConvertTimeStamp(strtotime("+30 day"), "FULL", SITE_ID),
			//'dateStart'=>'',
			//'dateFinish'=>'',
			//'sites'=>array(
			//	array('active'=>true, 'id'=>'all', 'name'=>GetMessage("skyweb24.popuppro_CONDITIONS_SITEALL"))
			//),
			//'groups'=>array(
			//	array('active'=>false, 'id'=>'unregister', 'name'=>GetMessage("skyweb24.popuppro_CONDITIONS_GROUPSUNREGISTER"))
			//),
			//'showOnlyPath'=>'',
			//'hideOnlyPath'=>'',
			//'maskPriority'=>'SHOW',
			//'afterShowCountPages'=>0,
			//'afterTimeSecond'=>0,
			//'timeInterval'=>'',//12:40#15:50
			//'anchorVisible'=>'',//<a name="#anchorVisible#"></a>
			//'onClickClassLink'=>'',
			//'alreadygoing'=>false,
			//'repeatTime'=>0, //time of repeat
			//'repeatTime_type'=>'day',//type of repeat
			'rule'=>array()
		);

		if(\Bitrix\Main\Loader::IncludeModule("statistic")){
			$conditionArr['groups'][]=array('active'=>false, 'id'=>'firstvisit', 'name'=>GetMessage("skyweb24.popuppro_CONDITIONS_GROUPSFIRSTVISIT"));
		}

		$rsSites = CSite::GetList($by="sort", $order="desc");
		while ($arSite = $rsSites->Fetch()){
			$conditionArr['sites'][]=array('active'=>false, 'id'=>$arSite['LID'], 'name'=>$arSite['NAME']);
		}
		$rsGroups = CGroup::GetList ($by = "c_sort", $order = "asc", Array ());
		while($arGroup=$rsGroups->Fetch()){
			$conditionArr['groups'][]=array('active'=>false, 'id'=>$arGroup['ID'], 'name'=>$arGroup['NAME']);
		}
		if(\Bitrix\Main\Loader::includeModule('sale')){
			$conditionArr['saleCountProduct']=0;
			$conditionArr['saleSummBasket']=0;
			$conditionArr['saleIDProdInBasket']=0;
		}
		return $conditionArr;
	}

	public function getConditions(){
		$conditionArr=$this->getConditionsPreset();
		if($this->idPopup!='new'){
			$settings=$this->getSetting($this->idPopup);
			foreach($conditionArr as $keyCond=>&$valCond){
				if($keyCond=='active' || $keyCond=='alreadygoing'){
					$valCond=($settings['condition'][$keyCond]=='Y')?true:false;
				}elseif($keyCond=='sites' || $keyCond=='groups'){
					foreach($valCond as $key=>$val){
						if(!empty($settings['condition'][$keyCond]) && in_array($val['id'], $settings['condition'][$keyCond])){
							$valCond[$key]['active']=true;
						}else{
							$valCond[$key]['active']=false;
						}
					}
				}else{
					$valCond=$settings['condition'][$keyCond];
				}
			}
			if(!empty($settings['contact'])){
				$conditionArr['contact']=$settings['contact'];
			}
			if(!empty($settings['timer'])){
				$conditionArr['timer']=$settings['timer'];
			}
			if(!empty($settings['roulett'])){
				$conditionArr['roulett']=$settings['roulett'];
			}
			$conditionArr['service_name']=$settings['service_name'];
		}
		return $conditionArr;
	}
	
	public function searchinMailList($mail,$id=0){
		$id=(int)$id;
		if(!empty($mail)){
			$connection = \Bitrix\Main\Application::getConnection();
			$conHelper = $connection->getSqlHelper();
			
			$tmpMail = $conHelper->forSql($mail);
			
			$filter = array(
				'filter'=>array('CODE'=>$tmpMail)
			);
			if($id>0){
				$groupList=\Bitrix\Sender\ListTable::GetList(array(
					'filter'=>array('CODE'=>'skyweb24PopupPro_'.$id)
				));
				try {$emailList=\Bitrix\Sender\ContactTable::GetList($filter);}
				catch(Exception $e){
					$filter = array(
						'filter'=>array('EMAIL'=>$tmpMail)
					);
					$emailList = \Bitrix\Sender\ContactTable::GetList($filter);
				}
				$filter=array();
				if($row=$emailList->fetch()){
					$filter['filter']['CONTACT_ID']=$row['ID'];
				}else{
					return true;
				}
				if(!$row=$groupList->fetch()){
					$rowPopup=$this->getSetting($id);
					$listAddDb = \Bitrix\Sender\ListTable::add(array(
						'NAME' => $rowPopup['service_name'],
						'CODE' => 'skyweb24PopupPro_'.$id,
					));
					if($listAddDb->isSuccess())
						$listId = $listAddDb->getId();
				}else{
					$listId = $row['ID'];
				}
					
				$filter['filter']['LIST_ID']=$listId;
			}
			$emailList=\Bitrix\Sender\ContactListTable::GetList($filter);
			if(!$row=$emailList->fetch()){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	public function insertToMailList($mail, $name, $idPopup=0){
		$tmpPopup=0;
		if((int) $idPopup>0){
			$tmpPopup=(int) $idPopup;
		}elseif((int) $this->idPopup>0){
			$tmpPopup=(int) $this->idPopup;
		}
		if($tmpPopup>0){
			$connection = \Bitrix\Main\Application::getConnection();
			$conHelper = $connection->getSqlHelper();
			$curDateFunc = new \Bitrix\Main\Type\DateTime;

			$rowPopup=$this->getSetting($tmpPopup);

			//groupId
			$filter=['CODE'=>'skyweb24PopupPro_'.$tmpPopup];
			if(!empty($rowPopup['contact']['groupmail'])){
				$filter=['ID'=>$rowPopup['contact']['groupmail']];
			}
			$groupList=\Bitrix\Sender\ListTable::GetList(array(
				'filter'=>$filter
			));
			if(!$row=$groupList->fetch()){
				$listAddDb = \Bitrix\Sender\ListTable::add(array(
					'NAME' => $rowPopup['service_name'],
					'CODE' => 'skyweb24PopupPro_'.$tmpPopup,
				));
				if($listAddDb->isSuccess()){
					$listId = $listAddDb->getId();
				}
			}else{
				$listId = $row['ID'];
			}
			//mailId
			$tmpMail = $conHelper->forSql($mail);
			try{
			$emailList=\Bitrix\Sender\ContactTable::GetList(array(
				'filter'=>array('CODE'=>$tmpMail)
			));
			}catch(Exception $e){
				$emailList=\Bitrix\Sender\ContactTable::GetList(array(
				'filter'=>array('EMAIL'=>$tmpMail)
			));	
			}
			if(!$row=$emailList->fetch()){
				try{
				$typeId = \Bitrix\Sender\Recipient\Type::detect($mail);
				$listAddDb = \Bitrix\Sender\ContactTable::add(array(
					'NAME' => $conHelper->forSql($name),
					'CODE' => $tmpMail,
					'TYPE_ID'=>$typeId,
					'DATE_INSERT' => $curDateFunc,
					'DATE_UPDATE' => $curDateFunc
				));
				}catch(Exception $e){
					$listAddDb = \Bitrix\Sender\ContactTable::add(array(
					'NAME' => $conHelper->forSql($name),
					'EMAIL' => $tmpMail,
					'DATE_INSERT' => $curDateFunc,
					'DATE_UPDATE' => $curDateFunc
				));	
				}
				if($listAddDb->isSuccess()){
					$mailId = $listAddDb->getId();
				}else{
					//error
				}
			}else{
				$mailId = $row['ID'];
			}

			//add group to mail
			$unionList=\Bitrix\Sender\ContactListTable::GetList(array(
				'filter'=>array('LIST_ID'=>$listId, 'CONTACT_ID'=>$mailId)
			));
			if(!$row=$unionList->fetch()){
				$listAddDb = \Bitrix\Sender\ContactListTable::add(array(
					'LIST_ID' => $listId,
					'CONTACT_ID' => $mailId
				));
				if($listAddDb->isSuccess()){
					return true;
				}
			}else{
				return true;
			}
		}
		return false;
	}

	public function getSetting($id=0){
		if($id==0){return false;}
		global $DB;
		 $res = $DB->Query('select * from '.$this->tableSetting.' where id='.$id.' limit 1;');
		 if($row = $res->Fetch()){
			 $retArr=unserialize($row['settings']);
			 $retArr['service_name']=$row['name'];
			 $retArr['row']=$row;
			 //var_dump($retArr['view']['type']);
			 if($retArr['view']['type']=='coupon'){
				CModule::IncludeModule("sale");
				$res=CSaleDiscount::GetByID($retArr['view']['props']['RULE_ID']);
				$retArr['view']['props']['PERCENT']=$res;
				//if($res['DISCOUNT_TYPE']=='P'){
					$retArr['view']['props']['PERCENT']=explode('=>',$res['APPLICATION']);//['DISCOUNT_VALUE'].'%';
					$type=explode(',',$retArr['view']['props']['PERCENT'][2]);
					$type=$type[0];
					$retArr['view']['props']['PERCENT']=explode(',',$retArr['view']['props']['PERCENT'][1]);
					$retArr['view']['props']['PERCENT']=(float)$retArr['view']['props']['PERCENT'][0]*(-1);
					if($type[2]=='P'){
						$retArr['view']['props']['PERCENT']=$retArr['view']['props']['PERCENT'].'%';
					}elseif($type[2]=='S'||$type[2]=='F'){
						$retArr['view']['props']['PERCENT']=CurrencyFormat($retArr['view']['props']['PERCENT'],$res['CURRENCY']);
					}
				//}
			 }
			 
			 return $retArr;
		 }
		 return false;
	}
	
	public function getCoupon($id,$avaliable,$email='',$popup_id=0,$result_text='',$mask=''){
		if(empty($id)||$id==0){return false;}
		if($id!='win'){
			$COUPON='';
			$cTime=time();
			$startTime=new Bitrix\Main\Type\DateTime(ConvertTimeStamp($cTime, "FULL"));
			$endTime=false;
			if($avaliable!=''&&$avaliable!='infinite'){
				$endTime = $endTime=AddToTimeStamp(array('DD'=>$avaliable), $cTime);
				$endTime=new Bitrix\Main\Type\DateTime(ConvertTimeStamp($endTime, "FULL"));
			}
			$fields = array(
				'DISCOUNT_ID'=>$id,
				'ACTIVE'=>'Y',
				'COUPON'=>$COUPON,
				'DATE_APPLY'=>false,
				'ACTIVE_TO'=>$endTime,
				'ACTIVE_FROM'=>$startTime,
				'DESCRIPTION'=>$email,
			);
			if($avaliable==='infinite'){
				global $USER;
				$fields['TYPE']=Bitrix\Sale\Internals\DiscountCouponTable::TYPE_MULTI_ORDER;
				$fields['MAX_USE']='';
				$fields['USER_ID']=$USER->GetID();
				$res=\Bitrix\Sale\Internals\DiscountCouponTable::GetList(array('filter'=>array('DISCOUNT_ID'=>$id),'order'=>array('ID'=>'desc')));
				$prevCoupon=str_replace('%23','0',$mask);
				$prevCoupon=str_replace('#','0',$prevCoupon);
				$mask_length=strlen($prevCoupon);
				if($r=$res->fetch()){
					$prevCoupon=$r['COUPON'];
					if($mask_length>strlen($prevCoupon)){
						$len=strlen($prevCoupon);
						$prevCoupon=substr($mask,($mask_length-$len)).$prevCoupon;
					}
				}
				$mask=str_replace('%23','',$mask);
				$mask=str_replace('#','',$mask);
				$mask_length_=strlen($mask);
				$prevCoupon=substr($prevCoupon,$mask_length_);
				$newCoupon=(int)$prevCoupon;
				$newCoupon++;
				$couponLen=strlen($newCoupon);
				$prevCouponLen=strlen($prevCoupon);
				while($prevCouponLen>$couponLen){
					$newCoupon='0'.$newCoupon;
					$couponLen++;
				}
				$COUPON = $mask.$newCoupon;
			}else{
				$COUPON = Bitrix\Sale\Internals\DiscountCouponTable::generateCoupon(true);
				$fields['TYPE']=Bitrix\Sale\Internals\DiscountCouponTable::TYPE_ONE_ORDER;
				$fields['MAX_USE']='1';
			}
			$fields['COUPON']=$COUPON;
			if($popup_id!=0){
				$settings=$this->getSetting($popup_id);
				if($settings['view']['type']=='coupon'){
					if(!empty($settings['view']['props']['EMAIL_TEMPLATE'])){
						if($email!=''){
						Event::send(array(
								"EVENT_NAME" => "SKYWEB24_POPUPPRO_SEND_COUPON",
								"LID" => $this->site_id,
								"C_FIELDS" => array(
									"EMAIL" => $email,
									"COUPON" => $COUPON,
								),
								'MESSAGE_ID'=>$settings['view']['props']['EMAIL_TEMPLATE']
							));
						}
					}
				}elseif($settings['view']['type']=='roulette'){
					if(!empty($settings['view']['props']['MAIL_TEMPLATE'])){
						if($email!=''){
							Event::send(array(
								"EVENT_NAME" => "SKYWEB24_POPUPPRO_ROULETTE_SEND",
								"LID" => $this->site_id,
								"C_FIELDS" => array(
									"EMAIL" => $email,
									"COUPON" => $COUPON,
									"RESULT_TEXT"=>$result_text,
								),
								'MESSAGE_ID'=>$settings['view']['props']['MAIL_TEMPLATE']
							));
						}
					}
				}elseif($settings['view']['type']=='discount'){
					if(!empty($settings['view']['props']['EMAIL_TEMPLATE_D'])){
						if($email!=''){
							Event::send(array(
								"EVENT_NAME" => "SKYWEB24_POPUPPRO_DISCOUNT_SEND",
								"LID" => $this->site_id,
								"C_FIELDS" => array(
									"EMAIL" => $email,
									"COUPON" => $COUPON,
									"NAME"=>$USER->GetFirstName(),
									"LAST_NAME"=>$USER->GetLastName(),
								),
								'MESSAGE_ID'=>$settings['view']['props']['EMAIL_TEMPLATE_D']
							));
						}
					}
				}
			}
			$couponsResult = \Bitrix\Sale\Internals\DiscountCouponTable::add($fields);
			return $COUPON;
		}elseif($id=='win'){
			$COUPON=='';
			if($popup_id!=0){
				$settings=$this->getSetting($popup_id);
				if(!empty($settings['view']['props']['MAIL_TEMPLATE'])){
					if($email!=''){
						Event::send(array(
							"EVENT_NAME" => "SKYWEB24_POPUPPRO_ROULETTE_SEND",
							"LID" => $this->site_id,
							"C_FIELDS" => array(
								"EMAIL" => $email,
								"COUPON" => $COUPON,
								"RESULT_TEXT"=>$result_text,
							),
							'MESSAGE_ID'=>$settings['view']['props']['MAIL_TEMPLATE']
						));
					}
				}
			}
		}
	}
	
	public function getSimilarProps($id){
		$numbers_values = array('AFTER_SHOW_COUNT_PAGES','AFTER_TIME_SECOND','CART_COUNT','CART_SUMM');
		$props=array();
		if(!empty($id) && $id!='new'){
			$selectRow=$this->getSetting($id);
			return $selectRow['condition']['rule'];
		}
		if(empty($props))
			$props=array('id'=>'0','controlId'=>'CondGroup','values'=>array('All'=>'AND','True'=>'True'),'children'=>array());
		return $props;
	}
	
	public function getAvaliableProps(){
		$avaliableProps=array();
		$avaliableProps[]=array(
			'controlId'=>'CondGroup',
			'group'=>true,
			'label'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_GROUP"),
			'defaultText'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_GROUP_DEFAULT"),
			'showIn'=>array('CondGroup'),
			'visual'=> array(
				'controls'=>array('All', 'True'),
				'values'=>array(
							array(
								'All'=> 'AND', 'True'=> 'True'
							),array(
								'All'=> 'AND', 'True'=> 'False'
							),array(
								'All'=> 'OR', 'True'=> 'True'
							),array(
								'All'=> 'OR', 'True'=> 'False'
							)
						),
				'logic'=>array(
							array(
								'style'=> 'condition-logic-and',
								'message'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_LOGIC_AND")
							),array(
								'style'=> 'condition-logic-and',
								'message'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_LOGIC_AND_NOT")
							),array(
								'style'=> 'condition-logic-or', 'message'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_LOGIC_OR")
							),array(
								'style'=> 'condition-logic-or', 'message'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_LOGIC_OR_NOT")
							)
						)            
			),
			 'control'=>array(
                    array(
                        'id'=>'All',
                        'name'=>'aggregator',
                        'type'=>'select',
                        'values'=>array(
                            'AND'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_ALL"),
                            'OR'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_OR")
                        ),
                        'defaultText'=>'...',
                        'defaultValue'=>'AND',
                        'first_option'=>'...'
                    ),array(
                        'id'=>'True',
                        'name'=>'value',
                        'type'=>'select',
                        'values'=> array(
                                    'True'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_TRUE"),
                                    'False'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_FALSE")
                                ),
                        'defaultText'=>'...',
                        'defaultValue'=>'True',
                        'first_option'=>'...'
                    )
			)
		);
		$sites = array('all'=>GetMessage("skyweb24.popuppro_SITES_ALL_NAME"));
	    $tmpSites=CSite::GetList($by="sort", $order="desc");
	    while($tmpSite=$tmpSites->Fetch()){
	        $sites[$tmpSite['ID']]=$tmpSite['NAME'];
	    }
		$groups = array('unregister'=>GetMessage("skyweb24.popuppro_CONDITIONS_GROUPSUNREGISTER"),'firstvisit'=>GetMessage("skyweb24.popuppro_CONDITIONS_GROUPSFIRSTVISIT"));
		$tmpGroups=CGroup::GetList($by="sort", $order="desc");
		while($tmpGroup=$tmpGroups->Fetch()){
			$groups[$tmpGroup['ID']]=$tmpGroup['NAME'];
		}
		$avaliableProps[]=array(
			'controlgroup'=>true,
			'group'=>false,
			'label'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_GROUP_BASIC"),
			'showIn'=>array('CondGroup'),
			'children'=>array(
					array(
                        'controlId'=>'SITES',
						'description'=>GetMessage("skyweb24.popuppro_TABCOND_SITE_HINT"),
                        'group'=>false,
                        'label'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_SITES_LABEL"),
                        'showIn'=>array('CondGroup'),
                        'control'=>array(
                                    array(
                                        'id'=> 'prefix',
                                        'type'=> 'prefix',
                                        'text'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_SITES_TEXT")
                                    ),array(
                                        'id'=>'logic',
                                        'name'=>'logic',
                                        'type'=>'select',
                                        'values'=>array('Equal'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_LOGIC_EQUAL"),'Not'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_LOGIC_NOT")),
                                        'defaultText'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_LOGIC_EQUAL"),
                                        'defaultValue'=>'Equal'
                                    ),array(
                                        'type'=>'select',
                                        'multiple'=>'Y',
                                        'values'=>$sites,
                                        'param_id'=>'n',
                                        'show_value'=>'Y',
                                        'id'=>'value',
                                        'name'=>'value'
                                    )
                        )
                    ),
					array(
						'controlId'=>'SHOW_PAGE',
						'group'=>false,
						'label'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_SHOW_PAGE_LABEL"),
						'showIn'=>array('CondGroup'),
						'description'=>GetMessage("skyweb24.popuppro_TABCOND_SHOWONLYPATH_HINT"),
						'control'=>array(
							array(
								'id'=> 'prefix',
								'type'=> 'prefix',
								'text'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_SHOW_PAGE_TEXT")
							),array(
								'id'=>'logic',
								'name'=>'logic',
								'type'=>'select',
								'values'=>
									array(
										'Equal'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_LOGIC_EQUAL"),
										'Not'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_LOGIC_NOT")
									),
								'defaultText'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_LOGIC_EQUAL"),
								'defaultValue'=>'Equal'
							),array(
								'id'=> 'value',
								'name'=> 'value',
								'type'=> 'input',
								'param_id'=>'n',
								'show_value'=>'Y',
							)
						)
					),array(
                        'controlId'=>'DATE',
                        'group'=>false,
                        'label'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_DATE_LABEL"),
						'description'=>GetMessage("skyweb24.popuppro_TABCOND_ACTIVE_DATE_PERIOD_HINT"),
                        'showIn'=>array('CondGroup'),
                        'control'=>array(
									array(
										'id'=>'prefix',
										'type'=>'prefix',
										'text'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_DATE_TEXT_1")
									), GetMessage("skyweb24.popuppro_PROP_CONDITION_DATE_TEXT_2"),
                                    array(
                                     'id'=> 'ValueStart',
                                     'name'=> 'ValueStart',
                                     'type'=> 'datetime',
                                     'format'=> 'date'
                                     )
                                 , GetMessage("skyweb24.popuppro_PROP_CONDITION_DATE_TEXT_3"), array(
                                     'id'=>'ValueEnd',
                                     'name'=> 'ValueEnd',
                                     'type'=> 'datetime',
                                     'format'=> 'date'
                                 )
                             )
                    ),array(
                        'controlId'=>'USER_GROUP',
                        'group'=>false,
                        'label'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_USER_GROUP_LABEL"),
						'description'=>GetMessage("skyweb24.popuppro_TABCOND_GROUPS_HINT"),
                        'showIn'=>array('CondGroup'),
                        'control'=>array(
                            array(
                                'id'=> 'prefix',
                                'type'=> 'prefix',
                                'text'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_USER_GROUP_TEXT")
                            ),array(
                                'id'=>'logic',
                                'name'=>'logic',
                                'type'=>'select',
                                'values'=>array('Equal'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_LOGIC_EQUAL"),'Not'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_LOGIC_NOT")),
                                'defaultText'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_LOGIC_EQUAL"),
                                'defaultValue'=>'Equal'
                            ),array(
                                'type'=>'select',
                                'multiple'=>'Y',
                                'values'=> $groups,
                                'param_id'=>'n',
                                'show_value'=>'Y',
                                'id'=>'value',
                                'name'=>'value'
                            )
                        )
                    )
			)
		);
		
		$avaliableProps[]=array(
        'controlgroup'=>true,
        'group'=>false,
        'label'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_GROUP_SPECIAL"),
        'showIn'=>array('CondGroup'),
        'children'=>array(
				array(
					'controlId'=>'REPEAT_SHOW',
					'group'=>false,
					'label'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_REPEAT_SHOW_LABEL"),
					'description'=>GetMessage("skyweb24.popuppro_TABCOND_REPEATTIME_HINT"),
					'showIn'=>array('CondGroup'),
					'control'=>array(
						array(
							'id'=>'prefix',
							'type'=>'prefix',
							'text'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_REPEAT_SHOW_TEXT_1")
						),
						array(
							'type'=>'input',
							'id'=>'repeat',
							'name'=>'repeat',
							'param_id'=>'n',
							'show_value'=>'Y',
							'defaultValue'=>'1'
						),array(
							'type'=>'select',
							'multiple'=>'N',
							'values'=> array(
								'HOUR'=>GetMessage("skyweb24.popuppro_HOUR"),
								'DAY'=>GetMessage("skyweb24.popuppro_DAY"),
								'WEEK'=>GetMessage("skyweb24.popuppro_WEEK"),
								'MONTH'=>GetMessage("skyweb24.popuppro_MONTH"),
								'YEAR'=>GetMessage("skyweb24.popuppro_YEAR"),
							),
							'param_id'=>'n',
							'show_value'=>'Y',
							'id'=>'type',
							'name'=>'type',
							'defaultText'=>GetMessage("skyweb24.popuppro_DAY"),
							'defaultValue'=>'DAY'
						)
					)
				)
			)
		);
		
		$avaliableProps[]=array(
        'controlgroup'=>true,
        'group'=>false,
        'label'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_GROUP_ADDITIONAL"),
        'showIn'=>array('CondGroup'),
        'children'=>array(
				array(
					'controlId'=>'AFTER_SHOW_COUNT_PAGES',
					'description'=>GetMessage("skyweb24.popuppro_TABCOND_AFTERSHOWCOUNTPAGES_HINT"),
					'group'=>false,
					'label'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_AFTER_SHOW_COUNT_PAGES_LABEL"),
					'showIn'=>array('CondGroup'),
					'control'=>array(
						array(
							'id'=>'prefix',
							'type'=>'prefix',
							'text'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_AFTER_SHOW_COUNT_PAGES_TEXT_1")
						),array(
								'id'=>'logic',
								'name'=>'logic',
								'type'=>'select',
								'values'=>array(
									'more'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_CART_SUMM_TEXT_2"),
									'less'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_CART_SUMM_TEXT_3"),
								),
								'defaultText'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_CART_SUMM_TEXT_2"),
								'defaultValue'=>'more'
							),array(
								'id'=> 'value',
								'name'=> 'value',
								'type'=> 'input',
								'format'=>'number',
								'param_id'=>'n',
								'show_value'=>'Y',
								'defaultValue'=>'1'
							), GetMessage("skyweb24.popuppro_PROP_CONDITION_AFTER_SHOW_COUNT_PAGES_TEXT_2")
					)
				),array(
					'controlId'=>'AFTER_TIME_SECOND',
					'group'=>false,
					'label'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_AFTER_TIME_SECOND_LABEL"),
					'description'=>GetMessage("skyweb24.popuppro_TABCOND_AFTERTIMESECOND_HINT"),
					'showIn'=>array('CondGroup'),
					'control'=>array(
						array(
							'id'=>'prefix',
							'type'=>'prefix',
							'text'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_AFTER_TIME_SECOND_TEXT_1")
						),array(
								'id'=>'logic',
								'name'=>'logic',
								'type'=>'select',
								'values'=>array(
									'more'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_CART_SUMM_TEXT_2"),
									'less'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_CART_SUMM_TEXT_3"),
								),
								'defaultText'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_CART_SUMM_TEXT_2"),
								'defaultValue'=>'more'
							),array(
								'id'=> 'value',
								'name'=> 'value',
								'type'=> 'input',
								'format'=>'number',
								'param_id'=>'n',
								'show_value'=>'Y',
								'defaultValue'=>'10'
							), GetMessage("skyweb24.popuppro_PROP_CONDITION_AFTER_TIME_SECOND_TEXT_2")
					)
				),array(
					'controlId'=>'AFTER_TIME_SECOND_PAGE',
					'group'=>false,
					'label'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_AFTER_TIME_SECOND_PAGE_LABEL"),
					'description'=>GetMessage("skyweb24.popuppro_TABCOND_AFTERTIMESECOND_PAGE_HINT"),
					'showIn'=>array('CondGroup'),
					'control'=>array(
						array(
							'id'=>'prefix',
							'type'=>'prefix',
							'text'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_AFTER_TIME_SECOND_PAGE_TEXT_1")
						),array(
								'id'=>'logic',
								'name'=>'logic',
								'type'=>'select',
								'values'=>array(
									'more'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_CART_SUMM_TEXT_2"),
									'less'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_CART_SUMM_TEXT_3"),
								),
								'defaultText'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_CART_SUMM_TEXT_2"),
								'defaultValue'=>'more'
							),array(
								'id'=> 'value',
								'name'=> 'value',
								'type'=> 'input',
								'format'=>'number',
								'param_id'=>'n',
								'show_value'=>'Y',
								'defaultValue'=>'10'
							), GetMessage("skyweb24.popuppro_PROP_CONDITION_AFTER_TIME_SECOND_PAGE_TEXT_2")
					)
				),array(
					'controlId'=>'TIME_INTERVAL',
					'group'=>false,
					'label'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_TIME_INTERVAL_LABEL"),
					'description'=>GetMessage("skyweb24.popuppro_TABCOND_TIMEINTERVAL_HINT"),
					'showIn'=>array('CondGroup'),
					'control'=>array(
						array(
							'id'=>'prefix',
							'type'=>'prefix',
							'text'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_TIME_INTERVAL_TEXT_1")
						), GetMessage("skyweb24.popuppro_PROP_CONDITION_TIME_INTERVAL_TEXT_2"),
						array(
							'id'=> 'time_start',
							'name'=> 'time_start',
							'type'=>'datetime',
							'param_id'=>'n',
							'show_value'=>'Y',
						)
						, GetMessage("skyweb24.popuppro_PROP_CONDITION_TIME_INTERVAL_TEXT_3"), array(
							'id'=>'time_end',
							'name'=> 'time_end',
							'type'=>'datetime',
							'param_id'=>'n',
							'show_value'=>'Y',
						)
					)
				),array(
					'controlId'=>'ANCHOR_VISIBLE',
					'group'=>false,
					'label'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_ANCHOR_VISIBLE_LABEL"),
					'description'=>GetMessage("skyweb24.popuppro_TABCOND_ANCHORVISIBLE_HINT"),
					'showIn'=>array('CondGroup'),
					'control'=>array(
						array(
							'id'=>'prefix',
							'type'=>'prefix',
							'text'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_ANCHOR_VISIBLE_TEXT_1")
						),array(
							'id'=>'value',
							'name'=>'value',
							'type'=>'input'
						)
					)
				),array(
					'controlId'=>'PERCENT_PAGE',
					'group'=>false,
					'label'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_PREVENT_PAGE_LABEL"),
					'description'=>GetMessage("skyweb24.popuppro_TABCOND_PREVENT_PAGE_HINT"),
					'showIn'=>array('CondGroup'),
					'control'=>array(
						array(
							'id'=>'prefix',
							'type'=>'prefix',
							'text'=>GetMessage('skyweb24.popuppro_PROP_CONDITION_PRECENT_PAGE')
						),GetMessage("skyweb24.popuppro_PROP_CONDITION_LOGIC_EQUAL"),array(
							'id'=>'value',
							'name'=>'value',
							'type'=>'input',
							'param_id'=>'n',
							'show_value'=>'Y',
						),'%'
					)
				),array(
					'controlId'=>'ON_CLICK_CLASS_LINK',
					'group'=>false,
					'label'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_ON_CLICK_CLASS_LINK_LABEL"),
					'description'=>GetMessage("skyweb24.popuppro_TABCOND_ONCLICKCLASSLINK_HINT"),
					'showIn'=>array('CondGroup'),
					'control'=>array(
						array(
							'id'=>'prefix',
							'type'=>'prefix',
							'text'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_ON_CLICK_CLASS_LINK_TEXT_1")
						),array(
							'id'=>'value',
							'name'=>'value',
							'type'=>'input',
							'param_id'=>'n',
							'show_value'=>'Y',
						)
					)
				),array(
					'controlId'=>'ALREADY_GOING',
					'group'=>false,
					'label'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_ALREADY_GOING_LABEL"),
					'description'=>GetMessage("skyweb24.popuppro_TABCOND_ALREADYGOING_HINT"),
					'showIn'=>array('CondGroup'),
					'control'=>array(
						array(
							'id'=>'prefix',
							'type'=>'prefix',
							'text'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_ALREADY_GOING_TEXT_1")
						),
						array(
							
							'type'=>'select',
							'multiple'=>'N',
							'values'=> array(
								'Y'=>'',
							),
							'param_id'=>'n',
							'show_value'=>'Y',
							'id'=>'value',
							'name'=>'value',
							'defaultText'=>'',
							'defaultValue'=>'Y'
						)
					)
				),/*array(
					'controlId'=>'REPEAT_SHOW',
					'group'=>false,
					'label'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_REPEAT_SHOW_LABEL"),
					'description'=>GetMessage("skyweb24.popuppro_TABCOND_REPEATTIME_HINT"),
					'showIn'=>array('CondGroup'),
					'control'=>array(
						array(
							'id'=>'prefix',
							'type'=>'prefix',
							'text'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_REPEAT_SHOW_TEXT_1")
						),
						array(
							'type'=>'input',
							'id'=>'repeat',
							'name'=>'repeat',
							'param_id'=>'n',
							'show_value'=>'Y',
							'defaultValue'=>'1'
						),array(
							'type'=>'select',
							'multiple'=>'N',
							'values'=> array(
								'HOUR'=>GetMessage("skyweb24.popuppro_HOUR"),
								'DAY'=>GetMessage("skyweb24.popuppro_DAY"),
								'WEEK'=>GetMessage("skyweb24.popuppro_WEEK"),
								'MONTH'=>GetMessage("skyweb24.popuppro_MONTH"),
								'YEAR'=>GetMessage("skyweb24.popuppro_YEAR"),
							),
							'param_id'=>'n',
							'show_value'=>'Y',
							'id'=>'type',
							'name'=>'type',
							'defaultText'=>GetMessage("skyweb24.popuppro_DAY"),
							'defaultValue'=>'DAY'
						)
					)
				),*/array(
					'controlId'=>'DEVICE_TYPE',
					'group'=>false,
					'label'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_DEVICE_TYPE_LABEL"),
					'description'=>GetMessage("skyweb24.popuppro_TABCOND_DEVICE_HINT"),
					'showIn'=>array('CondGroup'),
					'control'=>array(
						array(
							'id'=>'prefix',
							'type'=>'prefix',
							'text'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_DEVICE_TYPE_LABEL")
						),array(
							'id'=>'logic',
							'name'=>'logic',
							'type'=>'select',
							'values'=>
								array(
									'Equal'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_LOGIC_EQUAL"),
									'Not'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_LOGIC_NOT")
								),
							'defaultText'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_LOGIC_EQUAL"),
							'defaultValue'=>'Equal'
						),array(
							'type'=>'select',
							'multiple'=>'Y',
							'values'=> array(
								'ipad'=>'iPad',
								'iphone'=>'iPhone',
								'android'=>'Android',
								'windows phone'=>'Windows Phone',
							),
							'param_id'=>'n',
							'show_value'=>'Y',
							'id'=>'value',
							'name'=>'value'
						)
					)
				),
				array(
					'controlId'=>'OS',
					'group'=>false,
					'label'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_OS_TYPE_LABEL"),
					'description'=>GetMessage("skyweb24.popuppro_TABCOND_OS_HINT"),
					'showIn'=>array('CondGroup'),
					'control'=>array(
						array(
							'id'=>'prefix',
							'type'=>'prefix',
							'text'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_OS_TYPE_LABEL")
						),array(
							'id'=>'logic',
							'name'=>'logic',
							'type'=>'select',
							'values'=>
								array(
									'Equal'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_LOGIC_EQUAL"),
									'Not'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_LOGIC_NOT")
								),
							'defaultText'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_LOGIC_EQUAL"),
							'defaultValue'=>'Equal'
						),array(
							'type'=>'select',
							'multiple'=>'Y',
							'values'=> array(
								'win'=>'Windows',
								'mac'=>'Mac OS',
								'linux'=>'Linux',
							),
							'param_id'=>'n',
							'show_value'=>'Y',
							'id'=>'value',
							'name'=>'value'
						)
					)
				),
				array(
					'controlId'=>'BROWSER',
					'group'=>false,
					'label'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_BROWSER_TYPE_LABEL"),
					'description'=>GetMessage("skyweb24.popuppro_TABCOND_BROWSER_HINT"),
					'showIn'=>array('CondGroup'),
					'control'=>array(
						array(
							'id'=>'prefix',
							'type'=>'prefix',
							'text'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_BROWSER_TYPE_LABEL")
						),array(
							'id'=>'logic',
							'name'=>'logic',
							'type'=>'select',
							'values'=>
								array(
									'Equal'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_LOGIC_EQUAL"),
									'Not'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_LOGIC_NOT")
								),
							'defaultText'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_LOGIC_EQUAL"),
							'defaultValue'=>'Equal'
						),array(
							'type'=>'select',
							'multiple'=>'Y',
							'values'=> array(
								'chrome'=>'Google Chrome',
								'firefox'=>'Firefox',
								'opera'=>'Opera',
								'apple'=>'Safari',
								'msie'=>'Internet Explorer',
								'edge'=>'Edge'
							),
							'param_id'=>'n',
							'show_value'=>'Y',
							'id'=>'value',
							'name'=>'value'
						)
					)
				),
				array(
					'controlId'=>'DAY',
					'group'=>false,
					'label'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_DAY_LABEL"),
					'description'=>GetMessage("skyweb24.popuppro_TABCOND_DAYWEEK_HINT"),
					'showIn'=>array('CondGroup'),
					'control'=>array(
						array(
							'id'=>'prefix',
							'type'=>'prefix',
							'text'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_DAY_LABEL")
						),array(
							'id'=>'logic',
							'name'=>'logic',
							'type'=>'select',
							'values'=>
								array(
									'Equal'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_LOGIC_EQUAL"),
									'Not'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_LOGIC_NOT")
								),
							'defaultText'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_LOGIC_EQUAL"),
							'defaultValue'=>'Equal'
						),array(
							'type'=>'select',
							'multiple'=>'Y',
							'values'=> array(
								'1'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_DAY_TEXT_1"),
								'2'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_DAY_TEXT_2"),
								'3'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_DAY_TEXT_3"),
								'4'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_DAY_TEXT_4"),
								'5'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_DAY_TEXT_5"),
								'6'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_DAY_TEXT_6"),
								'7'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_DAY_TEXT_7")
							),
							'param_id'=>'n',
							'show_value'=>'Y',
							'id'=>'value',
							'name'=>'value'
						)
					)
				)
			)
		);

		if(CModule::IncludeModule("sale")){
			$currency='';
			if(CModule::IncludeModule("currency")){
				$currency=CCurrency::GetBaseCurrency();
			}else{
				$currency='RUB';
			}
			$avaliableProps[]=array(
				'controlgroup'=>true,
				'group'=>false,
				'label'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_GROUP_SALE"),
				'showIn'=>array('CondGroup'),
				'children'=>array(
					array(
						'controlId'=>'CART_COUNT',
						'group'=>false,
						'label'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_CART_COUNT_LABEL"),
						'description'=>GetMessage("skyweb24.popuppro_TABCOND_SALECOUNTPRODUCT_HINT"),
						'showIn'=>array('CondGroup'),
						'control'=>array(
							array(
								'id'=>'prefix',
								'type'=>'prefix',
								'text'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_CART_COUNT_TEXT_1")
							),array(
								'id'=>'logic',
								'name'=>'logic',
								'type'=>'select',
								'values'=>array(
									'more'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_CART_COUNT_TEXT_3"),
									'less'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_CART_COUNT_TEXT_4"),
								),
								'defaultText'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_CART_SUMM_TEXT_2"),
								'defaultValue'=>'more'
							),
							array(
								'type'=>'input',
								'id'=>'value',
								'name'=>'value',
								'param_id'=>'n',
								'show_value'=>'Y',
								'defaultValue'=>'1'
							),GetMessage("skyweb24.popuppro_PROP_CONDITION_CART_COUNT_TEXT_2")
						)
					),array(
						'controlId'=>'CART_SUMM',
						'group'=>false,
						'label'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_CART_SUMM_LABEL"),
						'description'=>GetMessage("skyweb24.popuppro_TABCOND_SALESUMMBASKET_HINT"),
						'showIn'=>array('CondGroup'),
						'control'=>array(
							array(
								'id'=>'prefix',
								'type'=>'prefix',
								'text'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_CART_SUMM_TEXT_1")
							),
							array(
								'id'=>'logic',
								'name'=>'logic',
								'type'=>'select',
								'values'=>array(
									'more'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_CART_SUMM_TEXT_2"),
									'less'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_CART_SUMM_TEXT_3"),
								),
								'defaultText'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_CART_SUMM_TEXT_2"),
								'defaultValue'=>'more'
							),
							array(
								'type'=>'input',
								'id'=>'value',
								'name'=>'value',
								'param_id'=>'n',
								'show_value'=>'Y',
								'defaultValue'=>'100'
							),$currency
						)
					),array(
						'controlId'=>'CART_PRODUCT',
						'group'=>false,
						'label'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_CART_PRODUCT_LABEL"),
						'description'=>GetMessage("skyweb24.popuppro_TABCOND_SALEIDPRODINBASKET_HINT"),
						'showIn'=>array('CondGroup'),
						'control'=>array(
							array(
								'id'=>'prefix',
								'type'=>'prefix',
								'text'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_CART_PRODUCT_TEXT_1")
							),
							array(
								'id'=>'logic',
								'name'=>'logic',
								'type'=>'select',
								'values'=>
									array(
										'Equal'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_LOGIC_EQUAL"),
										'Not'=> GetMessage("skyweb24.popuppro_PROP_CONDITION_LOGIC_NOT")
									),
								'defaultText'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_LOGIC_EQUAL"),
								'defaultValue'=>'Equal'
							),
							array(
								'type'=>'dialog',
								'popup_url'=>'/bitrix/tools/sale/product_search_dialog.php',
								'popup_params'=> array(
									'lang'=> 'ru',
									'caller'=> 'discount_rules'
								),
								'param_id'=>'n',
								'show_value'=>'Y',
								'id'=>'value',
								'name'=>'value'
							)
						)
					),array(
						'controlId'=>'CART_SECTION',
                        'group'=>false,
                        'label'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_SECTION_LABEL"),
						'description'=>GetMessage("skyweb24.popuppro_TABCOND_SALEIDPRODINSECTION_HINT"),
                        'showIn'=>array('CondGroup'),
                        'control'=>array(
                                    array(
                                        'id'=>'prefix',
                                        'type'=>'prefix',
                                        'text'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_SECTION_TEXT_1")
                                    ),array(
                                        'id'=>'logic',
                                        'name'=>'logic',
                                        'type'=>'select',
                                        'values'=>array(
                                                'Equal'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_SECTION_TEXT_2"),
                                                'Not'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_SECTION_TEXT_3")
                                        ),
                                        'defaultText'=>GetMessage("skyweb24.popuppro_PROP_CONDITION_SECTION_TEXT_3"),
                                        'defaultValue'=>'Equal'
                                    ),array(
                                        'type'=>'popup',
                                        'popup_url'=>'/bitrix/admin/iblock_section_search.php',
                                        'popup_params'=>array(
                                            'lang'=>'ru',
                                            'discount'=>'Y',
                                            'simplename'=>'Y'
                                        ),
                                        'param_id'=>'n',
                                        'show_value'=>'Y',
                                        'id'=>'value',
                                        'name'=>'value'
                                    )
						   )
					)
				)
			);
		}
		return $avaliableProps;
	}
	
	public function ConvertRequest($request){
		$result=array();
		foreach($request as $keyProp=>&$nextProp){
			$arKeys = $this->__ConvertKey($keyProp);
			$tmpKey='';
			foreach($arKeys as $arKey)
				$tmpKey=$arKey;
				
			$tmp_item=array(
				'id'=>$tmpKey,
				'controlId'=>$nextProp['controlId'],
			);
			$tmp_item['values']=array();
			if(!empty($nextProp['aggregator'])){
				$nextProp['All']=array($nextProp['aggregator']);
				$nextProp['True']=array($nextProp['value']);
			}
			foreach($nextProp as $value_key=>$value)
				if($value_key!='controlId')
					$tmp_item['values'][$value_key]=$value;
						
			if($nextProp['controlId']=='CondGroup')
				$tmp_item['children']=array();
				
			if($nextProp['controlId']=='CART_PRODUCT')
				if(CModule::IncludeModule("iblock")){
					$tmp_label=CIBlockElement::GetList(array(),array('ID'=>$nextProp['value']),false,false,array('NAME'));
					if($tmp_label=$tmp_label->Fetch()){$tmp_item['labels']=array('value'=>array($tmp_label['NAME']));}
					else{unset($nextProp['values']['value']);}
				}
			if($nextProp['controlId']=='CART_SECTION'){
				if(CModule::IncludeModule("iblock")){
					
					$tmp_label=CIBlockSection::GetList(array(),array('ID'=>$nextProp['value']),false,false,array('NAME'));
					if($tmp_label=$tmp_label->Fetch()){$tmp_item['labels']=array('value'=>array($tmp_label['NAME']));}
					else{unset($nextProp['values']['value']);}
				}
			}
			$this->__SetCondition($result, $arKeys, 0, $tmp_item);
		}
		$result = $this->__formatIndex($result);
		return $result;
	}
	
	public function __ConvertKey($strKey){
		if('' !== $strKey){
			$arKeys=explode('__', $strKey);
			if (is_array($arKeys)){
				foreach ($arKeys as &$intOneKey){$intOneKey = (int)$intOneKey;}
			}
			return $arKeys;
		}else{
			return false;
		}
	}
	
	private function __formatIndex($level){
		if(isset($level['children'])){
			foreach($level['children'] as &$child){
				if($child['controlId']=='CondGroup'){
					$child=popuppro::__formatIndex($child);
				}
			}
			unset($child);
			$level['children']=array_values($level['children']);
			foreach($level['children'] as $key=>&$child){
				$child['id']=$key;
			}
		}
		return $level;
	}
	
	public function __SetCondition(&$arResult, $arKeys, $intIndex, $arOneCondition){
		if (0==$intIndex){
			if (1==sizeof($arKeys)){$arResult=$arOneCondition;return true;}
			else{return $this->__SetCondition($arResult, $arKeys, $intIndex + 1, $arOneCondition);}
		}else{
			if (!isset($arResult['children'])){$arResult['children'] = array();}
			if (!isset($arResult['children'][$arKeys[$intIndex]])){$arResult['children'][$arKeys[$intIndex]] = array();}
			if(($intIndex+1)<sizeof($arKeys)){
				return $this->__SetCondition($arResult['children'][$arKeys[$intIndex]],$arKeys,$intIndex+1,$arOneCondition);
			}else{
				if(!empty($arResult['children'][$arKeys[$intIndex]])){return false;}
				else{$arResult['children'][$arKeys[$intIndex]] = $arOneCondition;return true;}
			}
		}
	}
	
	//PUBLIC PART

	public static function convertTimeFromSecond($tm){
		$tmStr='';
		if($tm>86400){
			$tmStr.=floor($tm/86400).' '.GetMessage("skyweb24.popuppro_TIME_DAYS").' ';
			$tm=$tm%86400;
		}
		if($tm>3600){
			$tmStr.=floor($tm/3600).' '.GetMessage("skyweb24.popuppro_TIME_HOURS").' ';
			$tm=$tm%3600;
		}
		if($tm>60){
			$tmStr.=floor($tm/60).' '.GetMessage("skyweb24.popuppro_TIME_MINUTES").' ';
			$tm=$tm%60;
		}
		if($tm>0){
			$tmStr.=$tm.' '.GetMessage("skyweb24.popuppro_TIME_SECONDS");
		}
		return $tmStr;
	}

	/**
	* insertPopups function for show popups in public part
	*/
	public static function insertPopups(){
		$tmpActive = Bitrix\Main\Config\Option::get('skyweb24.popuppro', 'popup_active','Y');
		if(!defined('ADMIN_SECTION') && empty($_SERVER['HTTP_X_REQUESTED_WITH'])&&$tmpActive=='Y'){

			CJSCore::Init(array("ajax", "popup", "fx"));
			
			global $APPLICATION;
			$APPLICATION->AddHeadScript('/bitrix/js/'.self::idModule.'/script_public.js');
			$APPLICATION->AddHeadScript('/bitrix/js/'.self::idModule.'/effects.js');
			$APPLICATION->SetAdditionalCSS('/bitrix/js/main/core/css/core_popup.css');
			$APPLICATION->SetAdditionalCSS('/bitrix/themes/.default/skyweb24.popuppro_public.css');
			//$APPLICATION->AddHeadString('<script> var skyweb24Popups='.json_encode($skyweb24Popups).'; </script>');
			
			/*Asset::getInstance()->addJs('/bitrix/js/'.self::idModule.'/script_public.js');
			Asset::getInstance()->addCss('/bitrix/js/main/core/css/core_popup.css');
			Asset::getInstance()->addCss('/bitrix/themes/.default/skyweb24.popuppro_public.css');
			Asset::getInstance()->addString('<script> var skyweb24Popups='.json_encode($skyweb24Popups).'; </script>');*/
		}
	}

	public static function GetBasketInfo(){
		if(CModule::IncludeModule('sale')){
			CModule::IncludeModule('iblock');
			$basket=array('products'=>array(), 'summ'=>0,'sections'=>array(),'count'=>0);
			$basketNum=CSaleBasket::GetBasketUserID(true);
			$tmpOffers=array();
			if(!empty($basketNum)){
				$dbBasketItems = CSaleBasket::GetList([],
					["FUSER_ID" =>$basketNum, "LID" => SITE_ID, "ORDER_ID" => "NULL"],
					false,
					false,
					["ID", "TYPE", "CALLBACK_FUNC",'PRODUCT_XML_ID', "MODULE", "PRODUCT_ID", "QUANTITY", "DELAY", "CAN_BUY", "PRICE", "WEIGHT", "DISCOUNT_PRICE"]);
				$arBasketItems=[];
				$complects=[];
				while($arItems = $dbBasketItems->Fetch()){
					if($arItems["TYPE"] == 1){
						$complects[] = $arItems;
					}
					else{
						$arBasketItems[] = $arItems;
					}
				}
				//while($arItems = $dbBasketItems->Fetch()){
				//	$basket['products'][]=$arItems['PRODUCT_ID'];
				//	$basket['summ']+=$arItems['PRICE']*$arItems['QUANTITY'];
				//	$basket['count']++;
				//	if($arItems['PRODUCT_XML_ID']!=$arItems['PRODUCT_ID']){
				//		$tmpOffers[]=$arItems['PRODUCT_ID'];
				//	}
				//}
				if(!empty($complects)){
					foreach($complects as $complect){
						$arSets = CCatalogProductSet::getAllSetsByProduct($complect['PRODUCT_ID'], CCatalogProductSet::TYPE_SET);
					    $arSet = array_shift($arSets);
						foreach($arSet["ITEMS"] as $productAsComplect){
							foreach($arBasketItems as $key => $product){
					            if($product["PRODUCT_ID"] == $productAsComplect['ITEM_ID'] 
					            && $productAsComplect["QUANTITY"] * $complect["QUANTITY"] == $product["QUANTITY"]
					            && floatval($product["DISCOUNT_PRICE"] == 0)){
					                unset($arBasketItems[$key]);
					                break;
								}
					        }
						}
					}
					$arBasketItems = array_merge($arBasketItems, $complects);
				}
				foreach($arBasketItems as $item){
					
					$basket['products'][]=$item['PRODUCT_ID'];
					$basket['summ']+=(double)$item['PRICE']*(double)$item['QUANTITY'];
					$basket['count']++;
					if($items['PRODUCT_XML_ID']!=$item['PRODUCT_ID'] && $item['TYPE']!=1){
						$tmpOffers[]=$item['PRODUCT_ID'];
					}
				}

				if(count($tmpOffers)>0){
					$prods=CCatalogSKU::getProductList($tmpOffers);
					foreach($prods as $nextProduct){
						$basket['products'][]=$nextProduct['ID'];
					}
				}

				$db_sections = CIBlockElement::GetElementGroups($basket['products'],true);
				while($ar_group = $db_sections->Fetch()){
					$basket['sections'][]=$ar_group['ID'];
				}
				$basket['sections']=array_unique($basket['sections']);
			}
			return $basket;
		}else{
			return array('not_include'=>'Y');
		}
	}

	public function getAvailablePopups($options){

		global $DB;
		$res = $DB->Query('select * from '.$this->tableSetting.' where active="Y" order by sort;');
		$retArr=array();
		if(empty($_SESSION['skwb24_popuppro_afterTimeSecond'])){
			$_SESSION['skwb24_popuppro_afterTimeSecond']=time();
		}
		//if(empty($_COOKIE['skwb24_popuppro_afterTimeSecond']))
		//	setcookie('skwb24_popuppro_afterTimeSecond',time());
		$context = Application::getInstance()->getContext();
		$request = $context->getRequest();
		
		$httpType=($request->isHttps())?'https://':'http://';
		$tmp = $_SERVER['HTTP_HOST'];
		$tmp = explode(':',$tmp);
		$tmp = $tmp[0];
		$serverBase=$httpType.$tmp;
		$options['pageUrl'] = str_replace($serverBase, "", $options['pageUrl']);
		while($row = $res->Fetch()){
			$settings=unserialize($row['settings']);

			//do not to show the banner to users who filled out the form
			$cCookie=$request->getCookie("skyweb24PopupFilling_".$row['id']);
			if(!empty($cCookie) &&($settings["view"]["type"]=='contact'||$settings["view"]["type"]=='age'||$settings["view"]["type"]=='roulette'||$settings["view"]["type"]=='discount')){
				continue;
			}
			if(!empty($settings['timer']['enabled'])&&$settings['timer']['enabled']=="Y"&&!empty($settings['timer']['date'])){
				$format = 'd.m.Y H:i:s';
				$unixtime=DateTime::createFromFormat($format, $settings['timer']['date']);
				if(time()>$unixtime->getTimestamp()){
					continue;
				}
			}
			$settings['condition']['rule'] = $this->resultGroup($settings['condition']['rule'],$options);
			$retArr[$row['id']][$nextCond]=$settings['condition']['rule'];
		}
		return $retArr;
	}
	
	public function resultGroup($group,$option){
		//var_dump($group);
		foreach($group['children'] as &$child){
			if($child['controlId']=='CondGroup') $child = $this->resultGroup($child,$option);
			elseif($child['controlId']=='SITES'){
				$sitesRes=false;
				if(in_array('all',$child['values']['value'])||in_array($option['site'],$child['values']['value'])){
					$sitesRes=true;	
				}
				if($child['values']['logic']=='Equal'){
					$result[]=$sitesRes;
					$child=$sitesRes;
				}else{
					$child[]=!$sitesRes;
				}
			}elseif($child['controlId']=='AFTER_SHOW_COUNT_PAGES'){
				if($child['values']['logic']=='more'){
					if($child['values']['value']<=$option['countPages']){
						$child = true;
					}else{
						$child = false;
					}
				}else{
					if($child['values']['value']<=$option['countPages']){
						$child = false;
					}else{
						$child = true;
					}
				}
			}elseif($child['controlId']=='AFTER_TIME_SECOND'){
				$child['values']['value']=$child['values']['value']-(time()-$_SESSION['skwb24_popuppro_afterTimeSecond']);
			}
			elseif($child['controlId']=='USER_GROUP'){
				$userAccess=false;
				global $USER;
				if(!$USER->IsAuthorized() && in_array('unregister', $child['values']['value'])){
					$userAccess=true;
				}elseif(in_array('firstvisit', $child['values']['value']) && !empty($_SESSION["SESS_GUEST_NEW"]) && $_SESSION["SESS_GUEST_NEW"]=='Y'){
					$userAccess=true;
				}else{
					$cuserGroup=$USER->GetUserGroupArray();
					$tmpIntersect=array_intersect($cuserGroup, $child['values']['value']);
					if(count($tmpIntersect)>0){
						$userAccess=true;
					}
				}
				if($child['values']['logic']=='Equal'){
					$child=$userAccess;
				}else{
					$child=!$userAccess;
				}
			}
			elseif($child['controlId']=='DATE'){
				$tmpDateRes=true;
				$dateStart=0;
				$dateEnd=0;
				if(!empty($child['values']['ValueStart'])){
					$dateStart=DateTime::createFromFormat('d.m.Y H:i:s',$child['values']['ValueStart'].' 00:00:00');
					$dateStart = $dateStart->getTimestamp();
				}
				if(!empty($child['values']['ValueEnd'])){
					$dateEnd=DateTime::createFromFormat('d.m.Y H:i:s',$child['values']['ValueEnd'].' 23:59:59');
					$dateEnd = $dateEnd->getTimestamp();
				}
				if($dateStart!=0 && $dateStart>=$option['dateUser']){
					$tmpDateRes=false;
				}
				if($dateEnd!=0 && $dateEnd<=$option['dateUser']){
					$tmpDateRes=false;
				}
				$child=$tmpDateRes;
			}
			elseif($child['controlId']=='SHOW_PAGE'){
				if($child['values']['logic']=='Equal'){
					$showOnlyPath=false;
					$tmpShowOnly=trim($child['values']['value']);
					if(!empty($tmpShowOnly)){
						if(strpos($tmpShowOnly, '*')!==false){
							$pattern = '|^'.str_replace(array('*', '?'), array('(.*)', '\?'), $tmpShowOnly).'|';
							if(preg_match($pattern, $option['pageUrl'], $matches)==1){
								$showOnlyPath=true;
							}
						}elseif($option['pageUrl']==$tmpShowOnly){
							$showOnlyPath=true;
						}
					}
					
					$child=$showOnlyPath;
				}elseif($child['values']['logic']=='Not'){
					$hideOnlyPath=false;
					$tmpHideOnly=trim($child['values']['value']);
					if(!empty($tmpHideOnly)){
						if(strpos($tmpHideOnly,  '*')!==false){
							$pattern = '|^'.str_replace(array('*', '?'), array('(.*)', '\?'), $tmpHideOnly).'|';
							if(preg_match($pattern, $option['pageUrl'], $matches)==1){
								$hideOnlyPath=true;
							}
						}elseif($option['pageUrl']==$tmpHideOnly){
							$hideOnlyPath=true;
						}
					}
					$child=!$hideOnlyPath;
				}
			}
		}
		
		return $group;
	}
	/**
	* getComponentResult create array for components
	*/
	public function getComponentResult($idPopup){
		if($idPopup==0){return false;}
		global $DB;
		$res = $DB->Query('select * from '.$this->tableSetting.' where id='.$idPopup.' limit 1;');
		if($row = $res->Fetch()){
			$settings=unserialize($row['settings']);
			$settings['view']['props']['THEME']= $settings['view']['color_style'];
			$settings['view']['props']['TEMPLATE_NAME']= $settings['view']['type'].'_'.$settings['view']['template'];
			foreach($settings['view']['props'] as $keyProp=>$nextProp){
				if(strpos($keyProp, 'IMG_')!==false && intval($nextProp)>0){
					$settings['view']['props'][$keyProp]=CFile::GetPath($nextProp);
				}elseif(strpos($keyProp, 'IMG_')!==false && empty($nextProp)){
					$tmpTemplates=$this->getTemplates();
					foreach($tmpTemplates[$settings['view']['type']] as $nextTemplate){
						if($nextTemplate['template']==$settings['view']['template']){
							$settings['view']['props'][$keyProp]=$nextTemplate['props'][$keyProp];
							break;
						}
					}
				}
			}

			return  $settings['view']['props'];
		}
	}

	public function getHTMLByPopup($idPopup){
		$settings=$this->getSetting($idPopup);
		global $APPLICATION;
		$APPLICATION->IncludeComponent(
			"skyweb24:popup.pro", $settings['view']['type'].'_'.$settings['view']['template'],
			Array(
				"ID_POPUP" => $idPopup
			)
		);
	}

	public function getComponentPath($idPopups){
		if(count($idPopups)==0){return false;}
		if(!is_array($idPopups)){$idPopups=[$idPopups];}
		foreach($idPopups as $nextPopup){
			$settings=$this->getComponentResult($nextPopup);
			$tmpComponent = new CBitrixComponent();
			$tmpComponent->InitComponent('skyweb24:popup.pro', $settings['TEMPLATE_NAME']);
			$tmpComponent->initComponentTemplate();
			$tmpPath=$tmpComponent->__template->GetFolder();

			$retArr[$nextPopup]=array(
				'TEMPLATE'=>$tmpPath,
				'STYLE'=>$tmpPath.'/style.css',
				'TEMPLATE_NAME'=>$settings['TEMPLATE_NAME']
			);
			$settingsPos=$this->getSetting($nextPopup);
			$positions=array('POSITION_BOTTOM', 'POSITION_LEFT', 'POSITION_RIGHT', 'POSITION_TOP', 'VIDEO_AUTOPLAY', 'SHOW_ANIMATION', 'HIDE_ANIMATION', 'POSITION_FIXED', 'BACKGROUND_COLOR', 'BACKGROUND_OPACITY', 'SHOW_CLOSEBUTTON', 'CLOSE_AUTOHIDE', 'CLOSE_TEXTBOX', 'CLOSE_TEXTAREA');
			foreach($positions as $nextPosition){
				if(!empty($settingsPos['view']['props'][$nextPosition])){
					$retArr[$nextPopup][$nextPosition]=$settingsPos['view']['props'][$nextPosition];
				}
			}
			if(!empty($settings['THEME'])){
				$retArr[$nextPopup]['THEME']=$tmpPath.'/themes/'.$settings['THEME'].'.css';
			}
			
			if(!empty($settingsPos['timer']['enabled']) && $settingsPos['timer']['enabled']=='Y'){
				$retArr[$nextPopup]['TIMER']=$settingsPos['timer']['enabled'];
				$tmpComponent->InitComponent('skyweb24:popup.pro.timer', '');
				$tmpComponent->initComponentTemplate();
				$tmpPath=$tmpComponent->__template->GetFolder();
				$retArr[$nextPopup]['TIMER_STYLE']=$tmpPath.'/style.css';
			}
		}
		return $retArr;
	}

	public function setStatistic($idPopup, $value, $field){
		if(!empty($field) && in_array($field, array('stat_show','stat_time','stat_action'))){
			global $DB;
			$res = $DB->Query('select * from '.$this->tableSetting.' where id='.$idPopup.' limit 1;');
			if($row = $res->Fetch()){
				$DB->Query('update '.$this->tableSetting.' set '.$field.'="'.($row[$field]+$value).'" where id='.$idPopup.' limit 1;');
				return true;
			}
		}
		return false;
	}
}
?>
