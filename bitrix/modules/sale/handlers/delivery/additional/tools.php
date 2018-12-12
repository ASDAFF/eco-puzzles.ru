<?
/**
 * Bitrix Framework
 * @global CMain $APPLICATION
 */

define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NO_AGENT_CHECK", true);
define("NOT_CHECK_PERMISSIONS", true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

$result = array("ERROR" => "");

if (!\Bitrix\Main\Loader::includeModule('sale'))
	$result["ERROR"] = "Error! Can't include module \"Sale\"";

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if(strlen($result["ERROR"]) <= 0 && $saleModulePermissions >= "U" && check_bitrix_sessid())
{
	$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']): '';
	\Bitrix\Sale\Delivery\Services\Manager::getHandlersList();

	switch ($action)
	{
		case "get_ruspost_shipping_points_list":

			$result = '';
			$options = '<option value="">'.\Bitrix\Main\Localization\Loc::getMessage('SALE_DLVRS_ADDT_SP_NOT_SELECTED').'</option>';
			$deliveryId = isset($_REQUEST['deliveryId']) ? (int)$_REQUEST['deliveryId'] : 0;
			$spSelected = isset($_REQUEST['spSelected']) ? trim($_REQUEST['spSelected']) : '';
			$shippingPoints = \Sale\Handlers\Delivery\Additional\RusPost\Helper::getEnabledShippingPointsList($deliveryId);

			if(!empty($shippingPoints))
			{
				foreach($shippingPoints as $sPoint)
				{
					if($sPoint['enabled'] == 1)
					{
						$options .= '<option value="'.$sPoint['operator-postcode'].'"'.
							($spSelected == $sPoint['operator-postcode'] ? ' selected ' : '').'>'.
							$sPoint['operator-postcode'].' '.$sPoint['ops-address'].
							'</option>';
					}
				}
			}

			$result = '<select style="width: 450px;" id="sale-delivery-ruspost-shipment-points">'.$options.'</select>';

			die($result);
			break;

		case "locations_compare":
			$stage = isset($_REQUEST['stage']) ? trim($_REQUEST['stage']): 'start';
			$step = isset($_REQUEST['step']) ? trim($_REQUEST['step']): '';
			$timeout = isset($_REQUEST['timeout']) ? trim($_REQUEST['timeout']): 30;
			$progress = isset($_REQUEST['progress']) ? trim($_REQUEST['progress']): 0;

			if(strlen($stage) <= 0)
			{
				$result["ERROR"] = "Error! Wrong stage!";
				break;
			}

			$documentRoot = \Bitrix\Main\Application::getDocumentRoot();
			require_once($documentRoot.'/bitrix/modules/sale/handlers/delivery/additional/handler.php');
			$res = \Sale\Handlers\Delivery\Additional\Location::compare($stage, $step, $progress, $timeout);

			if($res->isSuccess())
			{
				$data = $res->getData();
				$result['action'] = $action;
				$result['stage'] = $data['STAGE'];

				if(!empty($data['STEP']))
					$result['step'] = $data['STEP'];

				if(!empty($data['MESSAGE']))
					$result['message'] = $data['MESSAGE'];

				if(!empty($data['PROGRESS']))
					$result['progress'] = $data['PROGRESS'];
			}
			else
			{
				$result["ERROR"] = implode(',<br>\n', $res->getErrorMessages());
			}

			break;
		default:
			$result["ERROR"] = "Error! Wrong action!";
			break;
	}
}
else
{
	if(strlen($result["ERROR"]) <= 0)
		$result["ERROR"] = "Error! Access denied";
}

if(strlen($result["ERROR"]) > 0)
	$result["RESULT"] = "ERROR";
else
	$result["RESULT"] = "OK";

if(strtolower(SITE_CHARSET) != 'utf-8')
	$result = \Bitrix\Main\Text\Encoding::convertEncoding($result, SITE_CHARSET, 'utf-8');

$result = json_encode($result);
header('Content-Type: application/json');
die($result);