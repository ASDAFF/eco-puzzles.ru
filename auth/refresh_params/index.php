<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// Обработчик - автозабитие параметров товара из свойств
if (CModule::IncludeModule("iblock")):

$addProps = CIBlockElement::GetList (
	Array("ID" => "ASC"),
	Array("IBLOCK_ID" => 15),
		false,
		false,
			Array( //Получаем данные о товаре
			'ID',
			'PROPERTY_VES',
			'PROPERTY_DLINA',
			'PROPERTY_SHIRINA',
			'PROPERTY_VYSOTA'
			)
);
while($ar_fields = $addProps->GetNext())
{
	echo '<strong>Товару с ID-' . $ar_fields['ID'] .' установлены параметры</strong><br>';
	echo 'Вес:' . $ar_fields['PROPERTY_VES_VALUE'].' / ';
	echo 'Длина:' . $ar_fields['PROPERTY_DLINA_VALUE'].' / ';
	echo 'Ширина:' . $ar_fields['PROPERTY_SHIRINA_VALUE'].' / ';
	echo 'Высота:' . $ar_fields['PROPERTY_VYSOTA_VALUE'].'<br><br>';
	// Оппа
	Cmodule::IncludeModule('catalog');
	$PRODUCT_ID = $ar_fields['ID'];
	$arFields = array(
		'WEIGHT' => $ar_fields['PROPERTY_VES_VALUE'],
		'LENGTH' => $ar_fields['PROPERTY_DLINA_VALUE'],
		'WIDTH' => $ar_fields['PROPERTY_SHIRINA_VALUE'],
		'HEIGHT' => $ar_fields['PROPERTY_SHIRINA_VALUE']
	);
	CCatalogProduct::Update($PRODUCT_ID, $arFields); // Забиваем параметры из свойств
}
endif;

?>