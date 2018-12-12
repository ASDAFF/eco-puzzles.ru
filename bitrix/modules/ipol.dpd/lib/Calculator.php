<?php
namespace Ipolh\DPD;

use \Bitrix\Main\Config\Option;
use \Bitrix\Main\SystemException;

use \Ipolh\DPD\API\User;

class Calculator
{
	protected static $lastResult = false;

	protected $api;

	protected $shipment;

	/**
	 * Возвращает список всех тарифов которые могут быть использованы
	 *
	 * @return array
	 */
	public static function TariffList()
	{
		return array(
			"PCL" => "DPD Online Classic",
			// "CUR" => "DPD CLASSIC domestic",
			"CSM" => "DPD Online Express",
			"ECN" => "DPD ECONOMY",
			"ECU" => "DPD ECONOMY CU",
			// "NDY" => "DPD EXPRESS",
			// "TEN" => "DPD 10:00",
			// "DPT" => "DPD 13:00",
			// "BZP" => "DPD 18:00",
			"MXO" => "DPD Online Max",
		);
	}

	/**
	 * Возвращает список тарифов которые можно использовать
	 *
	 * @return array
	 */
	public static function AllowedTariffList()
	{
		$disableTariffs = (array) (\unserialize(Option::get(IPOLH_DPD_MODULE, 'TARIFF_OFF')) ?: []);
		return array_diff_key(static::TariffList(), array_flip($disableTariffs));
	}

	/**
	 * Возвращает последний расчет
	 * 
	 * @return array
	 */
	public static function getLastResult()
	{
		return static::$lastResult;
	}

	public function __construct(Shipment $shipment, User $api = null)
	{
		$this->shipment                  = $shipment;
		$this->api                       = $api ?: User::getInstance();
		$this->defaultTariffCode         = Option::get(IPOLH_DPD_MODULE, 'DEFAULT_TARIFF_CODE');
		$this->minCostWhichUsedDefTariff = Option::get(IPOLH_DPD_MODULE, 'DEFAULT_TARIFF_THRESHOLD', 0);
	}

	/**
	 * Устанавливает посылку для расчета стоимости
	 * 
	 * @param \Ipolh\DPD\Shipment $shipment
	 */
	public function setShipment(Shipment $shipment)
	{
		$this->shipment = $shipment;

		return $this;
	}

	/**
	 * Возвращает посыдку для расчета стоимости
	 * 
	 * @return \Ipolh\DPD\Shipment $shipment
	 */
	public function getShipment()
	{
		return $this->shipment;
	}

	/**
	 * Устанавливает тариф и порог мин. стоимости доставки
	 * при не достижении которого будет использован переданный тариф
	 * 
	 * @param string  $tariffCode
	 * @param float   $minCostWhichUsedTariff
	 */
	public function setDefaultTariff($tariffCode, $minCostWhichUsedTariff = 0)
	{
		$this->defaultTariffCode = $tariffCode;
		$this->minCostWhichUsedDefTariff = $minCostWhichUsedTariff;
	}

	/**
	 * Возвращает тариф по умолчанию
	 * 
	 * @return string
	 */
	public function getDefaultTariff()
	{
		return $this->defaultTariffCode;
	}

	/**
	 * Возвращает порог стоимости доставки при недостижении которого
	 * будет использован тариф по умолчанию
	 * 
	 * @return float
	 */
	public function getMinCostWhichUsedDefTariff()
	{
		return $this->minCostWhichUsedDefTariff;
	}

	/**
	 * Расчитывает стоимость доставки
	 * 
	 * @return array Оптимальный тариф доставки
	 */
	public function calculate($currency = false)
	{
		if (!$this->getShipment()->isPossibileDelivery()) {
			return false;
		}

		$calcByParcel = Option::get(IPOLH_DPD_MODULE, 'CALCULATE_BY_PARCEL', 0) > 0;

		$parms = $this->getServiceParmsArray($calcByParcel);
		$tariffs = $this->getListFromService($parms, $calcByParcel);

		if (empty($tariffs)) {
			return false;
		}

		$tariff = $this->getActualTariff($tariffs);
		$tariff = $this->adjustTariffWithCommission($tariff);
		$tariff = $this->convertCurrency($tariff, $currency);

		return self::$lastResult = $tariff;
	}

	/**
	 * Возвращает стоимость доставки для конкретного тарифа
	 * @param  string $tariffCode
	 * @return array
	 */
	public function calculateWithTariff($tariffCode, $currency = false)
	{
		if (!$this->getShipment()->isPossibileDelivery()) {
			return false;
		}

		$calcByParcel = Option::get(IPOLH_DPD_MODULE, 'CALCULATE_BY_PARCEL', 0) > 0;

		$parms = $this->getServiceParmsArray($calcByParcel);
		$tariffs = $this->getListFromService($parms, $calcByParcel);

		if (empty($tariffs)) {
			return false;
		}

		foreach($tariffs as $tariff) {
			if ($tariff['SERVICE_CODE'] == $tariffCode) {
				$tariff = $this->adjustTariffWithCommission($tariff);
				$tariff = $this->convertCurrency($tariff, $currency);

				return self::$lastResult = $tariff;
			}
		}

		return false;
	}

	/**
	 * Корректирует стоимость тарифа с учетом комиссии на наложенный платеж 
	 * 
	 * @param  array $tariff
	 * @param  int   $personTypeId
	 * @param  int   $paySystemId
	 * @return array
	 */
	public function adjustTariffWithCommission($tariff)
	{
		if (!$this->getShipment()->isPaymentOnDelivery()) {
			return $tariff;
		}

		$payment = $this->getShipment()->getPaymentMethod();

		$siteId            = ADMIN_SECTION ? 's1': SITE_ID;
		$useCommission     = Option::get(IPOLH_DPD_MODULE, 'COMMISSION_NPP_CHECK_'.   $payment['PERSON_TYPE_ID'] .'_'. $siteId);
		$commissionPercent = Option::get(IPOLH_DPD_MODULE, 'COMMISSION_NPP_PERCENT_'. $payment['PERSON_TYPE_ID'] .'_'. $siteId);
		$minCommission     = Option::get(IPOLH_DPD_MODULE, 'COMMISSION_NPP_MINSUM_'.  $payment['PERSON_TYPE_ID'] .'_'. $siteId);

		if (!$useCommission) {
			return $tariff;
		}

		$sum = ($this->getShipment()->getPrice() * $commissionPercent / 100);
		$tariff['COST'] += $sum < $minCommission ? $minCommission : $sum;

		return $tariff;
	}

	/**
	 * Возвращает параметры для запроса на основе данных отправки
	 * 
	 * @return array
	 */
	public function getServiceParmsArray($calcByParcel = false)
	{
		$ret = [
			'PICKUP'         => $this->getShipment()->getSender(),
			'DELIVERY'       => $this->getShipment()->getReceiver(),
			'SELF_PICKUP'    => $this->getShipment()->getSelfPickup()   ? 1 : 0,
			'SELF_DELIVERY'  => $this->getShipment()->getSelfDelivery() ? 1 : 0,
			'DECLARED_VALUE' => $this->getShipment()->getDeclaredValue() ? round($this->shipment->getPrice(), 2) : 0,
		];

		if ($calcByParcel) {
			$ret['PARCEL'] = [
				[
					'WEIGHT'   => $this->getShipment()->getWeight(),
					'WIDTH'    => $this->getShipment()->getWidth(),
					'HEIGHT'   => $this->getShipment()->getHeight(),
					'LENGTH'   => $this->getShipment()->getLength(),
					'QUANTITY' => 1,
				]
			];

			// foreach ($this->getShipment()->getItems() as $item) {
			// 	$ret['PARCEL'][] = [
			// 		'WEIGHT'   => $item['WEIGHT'],
			// 		'WIDTH'    => $item['DIMENSIONS']['WIDTH'],
			// 		'HEIGHT'   => $item['DIMENSIONS']['HEIGHT'],
			// 		'LENGTH'   => $item['DIMENSIONS']['LENGTH'],
			// 		'QUANTITY' => $item['QUANTITY'],
			// 	];
			// }
		} else {
			$ret['WEIGHT'] = $this->getShipment()->getWeight();
			$ret['VOLUME'] = $this->getShipment()->getVolume();
		}

		return $ret;
	}

	/**
	 * Получает список тарифов у внешнего сервиса
	 * с учетом разрешенных тарифов
	 * 
	 * @param  array $parms
	 * @return array
	 */
	public function getListFromService($parms, $calcByParcel = false)
	{
		if (isset($parms['VOLUME']) && $parms['VOLUME'] <= 0) {
			unset($parms['VOLUME']);
		}

		if ($calcByParcel) {
			$tariffs = $this->api->getService('calculator')->getServiceCostByParcels($parms);
		} else {
			$tariffs = $this->api->getService('calculator')->getServiceCost($parms);
		}

		if (!$tariffs) {
			return [];
		}

		return array_intersect_key($tariffs, static::AllowedTariffList());
	}

	/**
	 * Возвращает актуальный тариф с учетом мин. тарифа по умолчанию
	 * 
	 * @param  array $tariffs
	 * @return array
	 */
	protected function getActualTariff(array $tariffs)
	{
		$defaultTariff = false;
		$actualTariff = reset($tariffs);

		foreach($tariffs as $tariff) {
			if ($tariff['SERVICE_CODE'] == $this->getDefaultTariff()) {
				$defaultTariff = $tariff;
			}

			if ($tariff['COST'] < $actualTariff['COST']) {
				$actualTariff = $tariff;
			}
		}

		if ($defaultTariff
			&& $actualTariff['COST'] < $this->getMinCostWhichUsedDefTariff()
		) {
			return $defaultTariff;
		}

		return $actualTariff;
	}

	protected function convertCurrency($tariff, $currencyTo)
	{
		$currencyFrom = $this->api->getClientCurrency();
		$currencyTo   = $currencyTo ?: $currencyFrom;

		$tariff['COST']     = \CCurrencyRates::ConvertCurrency($tariff['COST'], $currencyFrom, $currencyTo);
		$tariff['CURRENCY'] = $currencyTo;

		return $tariff;
	}
}