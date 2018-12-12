<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
use Bitrix\Main\UserConsent\Internals\AgreementTable,
Bitrix\Main\UserConsent\Agreement;

if(!empty($_REQUEST['ID'])){
	$tmpAgree=new Agreement((int) $_REQUEST['ID']);
	$text=$tmpAgree->getText();
	echo $text;
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>