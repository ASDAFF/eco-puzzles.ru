<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("up.boxberrydelivery");
if (isset($_POST['change_pvz_id']) && !empty($_POST['change_pvz_id']))
{
	CBoxberry::updatePVZ($_POST['change_pvz_id'], $_POST['order_id'], (isset($_POST['address']) ? $_POST['address'] : NULL));
}
if (isset($_POST['save_admin_pvz_id']) && !empty($_POST['save_admin_pvz_id']))
{
	CBoxberry::saveadminPVZ($_POST['save_admin_pvz_id'],$_POST['order_id'],$_POST['address']);
	echo true;
}
if (isset($_POST['save_pvz_id']) && !empty($_POST['save_pvz_id']))
{
	session_start();
	
	CBoxberry::savePVZ($_POST['save_pvz_id'], (isset($_POST['change_location']) ? $_POST['change_location'] : NULL));
	echo true;
}
if (isset($_POST['check_pvz']) && !empty($_POST['check_pvz']))
{
	echo json_encode(CBoxberry::checkPVZ());
}
if (isset($_POST['remove_pvz']) && !empty($_POST['remove_pvz']))
{
	CBoxberry::removePVZ();
	echo true;
}
if (isset($_POST['get_link']))
{
	echo CDeliveryBoxberry::getLink_params();
}
?>
