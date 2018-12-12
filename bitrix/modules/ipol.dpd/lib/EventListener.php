<?php
namespace Ipolh\DPD;

use \Bitrix\Main\Config\Option;
use \Bitrix\Main\SystemException;
use \Ipolh\DPD\Delivery\DPD;

class EventListener
{
	/**
	 * Выполняет валидацию полей заявки перед сохранением заказа
	 *
	 * @param  int   $orderId
	 * @param  array $arOrder
	 * @return void
	 */
	public static function validateDeliveryInfo(\Bitrix\Main\Event $event)
	{
		if (defined('ADMIN_SECTION') && ADMIN_SECTION) {
			return;
		}

		$_REQUEST['IPOLH_DPD_ORDER']  = $_REQUEST['IPOLH_DPD_ORDER']  ?: $_SESSION['IPOLH_DPD_ORDER'];

		if (!$_REQUEST['IPOLH_DPD_ORDER']) {
			return;
		}

		$order = $event->getParameter('ENTITY');

		$deliveryId   = $order->getField('DELIVERY_ID');
		$deliveryCode = DPD::getDeliveryCode($deliveryId);
		$profile      = DPD::getDeliveryProfile($deliveryCode);

		if ($profile === false) {
			return;
		}

		
		$_REQUEST['IPOLH_DPD_TARIFF'] = $_REQUEST['IPOLH_DPD_TARIFF'] ?: $_SESSION['IPOLH_DPD_TARIFF'];

		$entity = new \Ipolh\DPD\DB\Order\Model();
		$entity->fillFromConfig();
		$entity->fillFromOrder($order);

		$entity->serviceCode          = $_REQUEST['IPOLH_DPD_TARIFF'][$profile];
		$entity->serviceVariant       = $profile;
		$entity->receiverTerminalCode = $_REQUEST['IPOLH_DPD_TERMINAL'][$profile] ?: null;

		$result = $entity->validate();

		if (!$result->isSuccess()) {
			$error = implode('<br>', array_map(function($error) {
				return 'ipol.dpd: '. $error;
			}, $result->getErrorMessages()));

			return new \Bitrix\Main\EventResult(
            	\Bitrix\Main\EventResult::ERROR,
            	new \Bitrix\Sale\ResultError($error, 'SALE_EVENT_WRONG_ORDER'),
            	'sale'
			);
		}
	}

	/**
	 * Сохраняем данные о доставке в заказе
	 *
	 * @param  int   $orderId
	 * @param  array $arOrder
	 * @return void
	 */
	public static function saveDeliveryInfo($orderId, $arOrder)
	{
		if (!$orderId) {
			return;
		}

		$deliveryCode = DPD::getDeliveryCode($arOrder['DELIVERY_ID']);
		$profile = DPD::getDeliveryProfile($deliveryCode);
		if ($profile === false) {
			return;
		}

		$orderId = \Ipolh\DPD\Utils::getOrderId($arOrder);
		$entity  = \Ipolh\DPD\DB\Order\Table::findByOrder($orderId, true);
		
		if ($entity->id) {
			return;
		}

		$_REQUEST['IPOLH_DPD_ORDER']  = $_REQUEST['IPOLH_DPD_ORDER']  ?: $_SESSION['IPOLH_DPD_ORDER'];
		$_REQUEST['IPOLH_DPD_TARIFF'] = $_REQUEST['IPOLH_DPD_TARIFF'] ?: $_SESSION['IPOLH_DPD_TARIFF'];

		$entity->serviceCode = $_REQUEST['IPOLH_DPD_TARIFF'][$profile];
		$entity->serviceVariant = $profile;
		$entity->receiverTerminalCode = $_REQUEST['IPOLH_DPD_TERMINAL'][$profile] ?: null;

		$result = $entity->save();
		if (!$result->isSuccess()) {
			$GLOBALS['APPLICATION']->ThrowException(implode('<br>', $result->getErrorMessages()));
			return false;
		}
	}

	/**
	 * Отрисовывает форму редактирования заказа
	 */
	public static function showAdminForm()
	{
		$userRights = \CMain::GetUserRight('sale');
		$depths = array('D' => 1, 'U' => 2, 'W' => 3);

		if ($depths['U'] > $depths[$userRights]) {
			return;
		}

		if (strpos($_SERVER['PHP_SELF'], "/bitrix/admin/sale_order_detail.php") !== false
			|| strpos($_SERVER['PHP_SELF'], "/bitrix/admin/sale_order_view.php") !== false
		) {
			require($_SERVER['DOCUMENT_ROOT'] .'/bitrix/modules/'. IPOLH_DPD_MODULE .'/admin/order_edit.php');
		}
	}
}