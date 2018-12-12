<?php
namespace Ipolh\DPD\DB\Order;

use \Bitrix\Main;
use \Bitrix\Main\Entity;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

class Table extends Entity\DataManager 
{
	public static function getTableName()
	{
		return 'b_ipol_dpd_order';
	}
	
	public static function getMap()
    {
        return array(
			new Entity\IntegerField(
				'ID',
				array(
					'primary' => true,
					'autocomplete' => true
				)
			),						
			new Entity\StringField(
				'ORDER_ID',
				array(
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_ORDER_ID'),				
					'required' => true,
				)
			),
			new Entity\IntegerField(
				'SHIPMENT_ID',
				array(
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_SHIPMENT_ID'),			
				)
			),
			new Entity\DatetimeField(
				'ORDER_DATE',
				array(					
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_ORDER_DATE'),
					'validation' => array(__CLASS__, 'validateOrderDate'),
				)
			),
			new Entity\DatetimeField(
				'ORDER_DATE_CREATE',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_ORDER_DATE_CREATE'),
					'validation' => array(__CLASS__, 'validateOrderDateCreate'),
				)
			),
			new Entity\DatetimeField(
				'ORDER_DATETIME_CREATE',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_ORDER_DATE_CREATE'),
					'validation' => array(__CLASS__, 'validateOrderDateCreate'),
				)
			),
			new Entity\DatetimeField(
				'ORDER_DATE_CANCEL',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_ORDER_DATE_CANCEL'),
					'validation' => array(__CLASS__, 'validateOrderDateCancel'),
				)
			),
			new Entity\DatetimeField(
				'ORDER_DATE_STATUS',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_ORDER_DATE_STATUS'),
					'validation' => array(__CLASS__, 'validateOrderDateStatus'),
				)
			),
			new Entity\StringField(
				'ORDER_NUM',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_ORDER_NUM'),
					'validation' => array(__CLASS__, 'validateOrderNum'),
				)
			),
			new Entity\TextField(
				'ORDER_STATUS',
				array(
					'title'         => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_ORDER_STATUS'),
					'default_value' => 'NEW',
				)
			),
			new Entity\TextField(
				'ORDER_STATUS_CANCEL',
				array(
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_ORDER_STATUS_CANCEL'),
				)
			),			
			new Entity\TextField(
				'ORDER_ERROR',
				array(
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_ORDER_ERROR'),
				)
			),
			new Entity\StringField(
				'SERVICE_CODE',
				array(
					'required'   => true,
					'validation' => array(__CLASS__, 'validateServiceCode'),
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_SERVICE_CODE'),
				)
			),
			new Entity\StringField(
				'SERVICE_VARIANT',
				array(
					'required'   => true,
					'validation' => array(__CLASS__, 'validateServiceVariant'),
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_SERVICE_VARIANT'),
				)
			),
			new Entity\DateField(
				'PICKUP_DATE',
				array(
					'validation' => array(__CLASS__, 'validatePickupDate'),
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_PICKUP_DATE'),
				)
			),
			new Entity\StringField(
				'PICKUP_TIME_PERIOD',
				array(
					'required'   => true,
					'validation' => array(__CLASS__, 'validatePickupTimePeriod'),
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_PICKUP_TIME_PERIOD'),
				)
			),
			new Entity\StringField(
				'DELIVERY_TIME_PERIOD',
				array(
					'validation' => array(__CLASS__, 'validateDeliveryTimePeriod'),
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_DELIVERY_TIME_PERIOD'),
				)
			),
			new Entity\FloatField(
				'CARGO_WEIGHT',
				array(
					'required' => true,
					'default_value' => 0,
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_CARGO_WEIGHT'),
				)
			),
			new Entity\FloatField(
				'DIMENSION_WIDTH',
				array(
					'default_value' => 0,
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_DIMENSION_WIDTH'),
				)
			),
			new Entity\FloatField(
				'DIMENSION_HEIGHT',
				array(
					'default_value' => 0,
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_DIMENSION_WIDTH'),
				)
			),
			new Entity\FloatField(
				'DIMENSION_LENGTH',
				array(
					'default_value' => 0,
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_DIMENSION_WIDTH'),
				)
			),
			new Entity\FloatField(
				'CARGO_VOLUME',
				array(
					'default_value' => 0,
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_CARGO_VOLUME'),
				)
			),
			new Entity\FloatField(
				'CARGO_NUM_PACK',
				array(
					'required' => true,
					'title'    => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_CARGO_NUM_PACK'),				
				)
			),
			new Entity\StringField(
				'CARGO_CATEGORY',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_CARGO_CATEGORY'),
					'required'   => true,
					'validation' => array(__CLASS__, 'validateCargoCategory'),
				)
			),
			new Entity\StringField(
				'SENDER_FIO',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_FIO'),
					'required'   => true,
					'validation' => array(__CLASS__, 'validateFio'),
				)
			),			
			new Entity\StringField(
				'SENDER_NAME',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_NAME'),
					'required'   => true,
					'validation' => array(__CLASS__, 'validateName'),
				)
			),
			new Entity\StringField(
				'SENDER_PHONE',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_PHONE'),
					'validation' => array(__CLASS__, 'validatePhone'),
				)
			),
			new Entity\StringField(
				'SENDER_EMAIL',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_EMAIL'),
					'validation' => array(__CLASS__, 'validateEmail'),
				)
			),
			new Entity\BooleanField(
				'SENDER_NEED_PASS',
				array(
					'title'         => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_NEED_PASS'),
					'values'        => array('N', 'Y'),
					'default_value' => 'N',
				)
			),
			new Entity\StringField(
				'SENDER_LOCATION',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_SENDER_LOCATION'),
					'required'   => true,
					'validation' => array(__CLASS__, 'validateLocation'),
				)
			),
			new Entity\StringField(
				'SENDER_STREET',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_STREET'),
					'validation' => array(__CLASS__, 'validateStreet'),
				)
			),
			new Entity\StringField(
				'SENDER_STREETABBR',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_STREETABBR'),
					'validation' => array(__CLASS__, 'validateStreetAbbr'),
				)
			),
			new Entity\StringField(
				'SENDER_HOUSE',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_HOUSE'),
					'validation' => array(__CLASS__, 'validateHouse'),
				)
			),
			new Entity\StringField(
				'SENDER_KORPUS',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_KORPUS'),
					'validation' => array(__CLASS__, 'validateKorpus'),
				)
			),
			new Entity\StringField(
				'SENDER_STR',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_STR'),
					'validation' => array(__CLASS__, 'validateStr'),
				)
			),
			new Entity\StringField(
				'SENDER_VLAD',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_VLAD'),
					'validation' => array(__CLASS__, 'validateVlad'),
				)
			),
			new Entity\StringField(
				'SENDER_OFFICE',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_OFFICE'),
					'validation' => array(__CLASS__, 'validateOffice'),
				)
			),
			new Entity\StringField(
				'SENDER_FLAT',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_FLAT'),
					'validation' => array(__CLASS__, 'validateFlat'),
				)
			),
			new Entity\StringField(
				'SENDER_TERMINAL_CODE',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_TERMINAL_CODE'),
					'validation' => array(__CLASS__, 'validateTerminalCode'),
				)
			),
			new Entity\StringField(
				'RECEIVER_FIO',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_FIO'),
					'required'   => true,
					'validation' => array(__CLASS__, 'validateFio'),
				)
			),			
			new Entity\StringField(
				'RECEIVER_NAME',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_NAME'),
					'required'   => true,
					'validation' => array(__CLASS__, 'validateName'),
				)
			),
			new Entity\StringField(
				'RECEIVER_PHONE',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_PHONE'),
					'validation' => array(__CLASS__, 'validatePhone'),
				)
			),
			new Entity\StringField(
				'RECEIVER_EMAIL',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_EMAIL'),
					'validation' => array(__CLASS__, 'validateEmail'),
				)
			),
			new Entity\StringField(
				'RECEIVER_LOCATION',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_LOCATION'),
					'required'   => true,
					'validation' => array(__CLASS__, 'validateLocation'),
				)
			),
			new Entity\StringField(
				'RECEIVER_STREET',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_STREET'),
					'validation' => array(__CLASS__, 'validateStreet'),
				)
			),
			new Entity\StringField(
				'RECEIVER_STREETABBR',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_STREETABBR'),
					'validation' => array(__CLASS__, 'validateStreetAbbr'),
				)
			),
			new Entity\StringField(
				'RECEIVER_HOUSE',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_HOUSE'),
					'validation' => array(__CLASS__, 'validateHouse'),
				)
			),
			new Entity\StringField(
				'RECEIVER_KORPUS',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_KORPUS'),
					'validation' => array(__CLASS__, 'validateKorpus'),
				)
			),
			new Entity\StringField(
				'RECEIVER_STR',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_STR'),
					'validation' => array(__CLASS__, 'validateStr'),
				)
			),
			new Entity\StringField(
				'RECEIVER_VLAD',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_VLAD'),
					'validation' => array(__CLASS__, 'validateVlad'),
				)
			),
			new Entity\StringField(
				'RECEIVER_OFFICE',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_OFFICE'),
					'validation' => array(__CLASS__, 'validateOffice'),
				)
			),
			new Entity\StringField(
				'RECEIVER_FLAT',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_FLAT'),
					'validation' => array(__CLASS__, 'validateFlat'),
				)
			),
			new Entity\StringField(
				'RECEIVER_TERMINAL_CODE',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_TERMINAL_CODE'),
					'validation' => array(__CLASS__, 'validateTerminalCode'),
				)
			),
			new Entity\StringField(
				'RECEIVER_COMMENT',
				array(
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_COMMENT'),
				)
			),
			new Entity\BooleanField(
				'RECEIVER_NEED_PASS',
				array(
					'title'         => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_RECEIVER_NEED_PASS'),
					'values'        => array('N', 'Y'),
					'default_value' => 'N',
				)
			),
			new Entity\FloatField(
				'PRICE',
				array(
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_PRICE'),		
				)
			),
			new Entity\FloatField(
				'PRICE_DELIVERY',
				array(
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_PRICE_DELIVERY'),
				)
			),					
			new Entity\FloatField(
				'CARGO_VALUE',
				array(
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_CARGO_VALUE'),				
				)
			),
			new Entity\BooleanField(
				'NPP',
				array(
					'title'         => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_NPP'),		
					'values'        => array('N', 'Y'),
					'default_value' => 'N',
				)
			),
			new Entity\FloatField(
				'SUM_NPP',
				array(
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_SUM_NPP'),		
				)
			),
			new Entity\BooleanField(
				'CARGO_REGISTERED',
				array(
					'title'         => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_NPP'),			
					'values'        => array('N', 'Y'),
					'default_value' => 'N',
				)
			),			
			new Entity\StringField(
				'SMS',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_SMS'),
					'validation' => array(__CLASS__, 'validateSms'),
				)
			),
			new Entity\StringField(
				'EML',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_EML'),
					'validation' => array(__CLASS__, 'validateEml'),
				)
			),
			new Entity\StringField(
				'ESD',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_ESD'),
					'validation' => array(__CLASS__, 'validateEsd'),
				)
			),
			new Entity\StringField(
				'ESZ',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_ESZ'),
					'validation' => array(__CLASS__, 'validateEsz'),
				)
			),			
			new Entity\StringField(
				'OGD',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_OGD'),
					'validation' => array(__CLASS__, 'validateOgd'),
				)
			),
			new Entity\BooleanField(
				'DVD',
				array(
					'title'         => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_DVD'),
					'values'        => array('N', 'Y'),
					'default_value' => 'N',
				)
			),
			new Entity\BooleanField(
				'VDO',
				array(
					'title'         => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_VDO'),
					'values'        => array('N', 'Y'),
					'default_value' => 'N',
				)
			),
			new Entity\StringField(
				'POD',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_POD'),
					'validation' => array(__CLASS__, 'validatePOD'),
				)
			),
			new Entity\BooleanField(
				'PRD',
				array(
					'title'         => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_PRD'),
					'values'        => array('N', 'Y'),
					'default_value' => 'N',
				)
			),
			new Entity\BooleanField(
				'TRM',
				array(
					'title'         => Loc::getMessage('IPOLH_DPD_TABLE_ORDER_TRM'),
					'values'        => array('N', 'Y'),
					'default_value' => 'N',
				)
			),
			new Entity\StringField(
				'LABEL_FILE',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_LABEL_FILE'),
					'validation' => array(__CLASS__, 'validateLabelFile'),
				)
			),
			new Entity\StringField(
				'INVOICE_FILE',
				array(
					'title'      => Loc::getMessage('IPOLH_DPD_TABLE_INVOICE_FILE'),
					'validation' => array(__CLASS__, 'validateInvoiceFile'),
				)
			),
			new Entity\StringField(
				'PAYMENT_TYPE',
				array(
					'title' => Loc::getMessage('IPOLH_DPD_TABLE_PAYMENT_TYPE'),
				)
			),
        );
    }
	
	public static function validateOrderNum()
	{
		return array(
			new Main\Entity\Validator\Length(null, 15),
		);
	}
	
	public static function validateOrderDate()
	{
		return array(
			new Main\Entity\Validator\Length(null, 20),
		);
	}

	public static function validateOrderDateCreate()
	{
		return array(
			new Main\Entity\Validator\Length(null, 20),
		);
	}
	
	public static function validateOrderDateCancel()
	{
		return array(
			new Main\Entity\Validator\Length(null, 20),
		);
	}

	public static function validateOrderDateStatus()
	{
		return array(
			new Main\Entity\Validator\Length(null, 20),
		);
	}
	
	public static function validateServiceCode()
	{
		return array(
			new Main\Entity\Validator\Length(null, 3),
		);
	}
	
	public static function validateServiceVariant()
	{
		return array(
			new Main\Entity\Validator\Length(null, 2),
		);
	}
	
	public static function validateCargoCategory()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}

	public static function validatePickupDate()
	{
		return array(
			new Main\Entity\Validator\Length(null, 20),

			function($value, $primary, $row, $field) {
				if (!is_null($primary) && empty($value) && $row['ORDER_STATUS'] != 'Canceled') {
					return Loc::getMessage("IPOLH_DPD_TABLE_PICKUP_DATE_ERROR_EMPTY");
				}

				if ($value) {
					// if (isset($_REQUEST['IPOLH_DPD_ORDER']) && \MakeTimeStamp($value, "DD.MM.YYYY") < mktime(0, 0, 0)) {
					// 	return Loc::getMessage("IPOLH_DPD_TABLE_PICKUP_DATE_ERROR_LESS");
					// }
				}

				return true;
			}
		);
	}
	
	public static function validatePickupTimePeriod()
	{
		return array(
			new Main\Entity\Validator\Length(null, 5),
		);
	}
	
	public static function validateDeliveryTimePeriod()
	{
		return array(
			new Main\Entity\Validator\Length(null, 5),
		);
	}
	
	public static function validateTerminalCode()
	{
		return array(
			// new Main\Entity\Validator\Length(null, 4),

			function($value, $primary, $row, $field) {
				$model = new Model($row);

				if ($field->getName() == 'SENDER_TERMINAL_CODE'
					&& $model->isSelfPickup()
					&& empty($value)
				) {
					return Loc::getMessage('IPOLH_DPD_TABLE_ERROR_REQUIRED', ['#NAME#' => $field->getTitle()]);
				}

				if ($field->getName() == 'RECEIVER_TERMINAL_CODE'
					&& $model->isSelfDelivery()
					&& Option::get(IPOLH_DPD_MODULE, 'REQUIRED_IS_SELECT_PVZ') > 0
					&& empty($value)
				) {
					return Loc::getMessage('IPOLH_DPD_TABLE_ERROR_REQUIRED', ['#NAME#' => $field->getTitle()]);
				}

				return true;
			}
		);
	}
	
	public static function validateFio()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	
	public static function validateName()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	
	public static function validatePhone()
	{
		return array(
			new Main\Entity\Validator\Length(null, 20),
		);
	}

	public static function validateEmail()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),

			function($value, $primary, $row, $field) {
				$model = new Model($row);

				if ($field->getName() == 'SENDER_EMAIL'
					&& $row['SENDER_NEED_PASS'] == 'Y'
					&& empty($value)
				) {
					return Loc::getMessage('IPOLH_DPD_TABLE_ERROR_REQUIRED', ['#NAME#' => $field->getTitle()]);
				}

				if ($field->getName() == 'RECEIVER_EMAIL'
					&& $row['RECEIVER_NEED_PASS'] == 'Y'
					&& empty($value)
				) {
					return Loc::getMessage('IPOLH_DPD_TABLE_ERROR_REQUIRED', ['#NAME#' => $field->getTitle()]);
				}

				return true;
			}
		);
	}
      
	public static function validateLocation()
	{
		return array();
	}
	
	public static function validateStreet()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	
	public static function validateStreetAbbr()
	{
		return array(
			new Main\Entity\Validator\Length(null, 10),
		);
	}
	
	public static function validateHouse()
	{
		return array(
			new Main\Entity\Validator\Length(null, 10),
		);
	}
	
	public static function validateKorpus()
	{
		return array(
			new Main\Entity\Validator\Length(null, 10),
		);
	}
	
	public static function validateStr()
	{
		return array(
			new Main\Entity\Validator\Length(null, 10),
		);
	}
	
	public static function validateVlad()
	{
		return array(
			new Main\Entity\Validator\Length(null, 10),
		);
	}
	
	public static function validateOffice()
	{
		return array(
			new Main\Entity\Validator\Length(null, 10),
		);
	}
	
	public static function validateFlat()
	{
		return array(
			new Main\Entity\Validator\Length(null, 10),
		);
	}
	
	public static function validateSms()
	{
		return array(
			new Main\Entity\Validator\Length(null, 25),
		);
	}
	
	public static function validateEml()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
	
	public static function validateEsd()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
	
	public static function validateEsz()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
	
	public static function validateOgd()
	{
		return array(
			new Main\Entity\Validator\Length(null, 4),
		);
	}
		
	public static function validatePOD()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
	
	public static function validateLabelFile()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	
	public static function validateInvoiceFile()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}

	/**
	 * Возвращает одну запись по ID
	 * 
	 * @param  $orderId
	 * 
	 * @return \Ipolh\DPD\DB\OrderTableItem
	 */
	public static function findByOrder($orderId, $autoCreate = false)
	{
		$item = self::getList(array(
			'filter' => array('ORDER_ID' => $orderId)
		))->Fetch();

		if ($item) {
			return new Model($item);
		} elseif (!$autoCreate) {
			return false;
		}
		
		$item = new Model();
		$item->fillFromConfig();
		$item->fillFromOrder($orderId);

		return $item;
	}
}
?>