<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Задайте вопрос");
?><h1>Контактная информация</h1>
 <?$APPLICATION->IncludeComponent(
	"bitrix:menu",
	"personal",
	Array(
		"ALLOW_MULTI_SELECT" => "N",
		"CHILD_MENU_TYPE" => "",
		"COMPONENT_TEMPLATE" => "personal",
		"DELAY" => "N",
		"MAX_LEVEL" => "1",
		"MENU_CACHE_GET_VARS" => array(),
		"MENU_CACHE_TIME" => "3600000",
		"MENU_CACHE_TYPE" => "A",
		"MENU_CACHE_USE_GROUPS" => "Y",
		"ROOT_MENU_TYPE" => "about",
		"USE_EXT" => "N"
	)
);?>

	<ul class="contactList">
		<li>
		<table>
		<tbody>
		<tr>
			<td>
 <img alt="cont1.png" src="<?=SITE_TEMPLATE_PATH?>/images/cont1.png" title="cont1.png">
			</td>
			<td>
				 +7 (800) 505-40-38<br>
				 +7 (812) 244-71-27<br>
			</td>
		</tr>
		</tbody>
		</table>
 </li>
		<li>
		<table>
		<tbody>
		<tr>
			<td>
 <img alt="cont2.png" src="<?=SITE_TEMPLATE_PATH?>/images/cont2.png" title="cont2.png">
			</td>
			<td>
 <a href="mailto:info@dw24.su">info@dw24.su</a><br>
 <a href="mailto:support@dw24.su">support@dw24.su</a><br>
			</td>
		</tr>
		</tbody>
		</table>
 </li>
		<li>
		<table>
		<tbody>
		<tr>
			<td>
 <img alt="cont3.png" src="<?=SITE_TEMPLATE_PATH?>/images/cont3.png" title="cont3.png">
			</td>
			<td>
				 г. Санкт-Петербург<br>
				 улица Пушкина, д.53     
			</td>
		</tr>
		</tbody>
		</table>
 </li>
		<li>
		<table>
		<tbody>
		<tr>
			<td>
 <img alt="cont4.png" src="<?=SITE_TEMPLATE_PATH?>/images/cont4.png" title="cont4.png">
			</td>
			<td>
				 Пн-Пт : с 10:00 до 20:00<br>
				 Сб, Вс : выходной<br>
			</td>
		</tr>
		</tbody>
		</table>
 </li>
	</ul>
	 <?$APPLICATION->IncludeComponent(
	"bitrix:map.yandex.view", 
	".default", 
	array(
		"COMPONENT_TEMPLATE" => ".default",
		"CONTROLS" => array(
			0 => "SMALL_ZOOM_CONTROL",
			1 => "TYPECONTROL",
			2 => "SCALELINE",
		),
		"INIT_MAP_TYPE" => "ROADMAP",
		"MAP_DATA" => "a:4:{s:10:\"yandex_lat\";d:55.757043820610185;s:10:\"yandex_lon\";d:37.60695961914063;s:12:\"yandex_scale\";i:12;s:10:\"PLACEMARKS\";a:3:{i:0;a:3:{s:4:\"TEXT\";s:7:\"Точка 1\";s:3:\"LON\";d:37.620620727539;s:3:\"LAT\";d:55.731749899652;}i:1;a:3:{s:4:\"TEXT\";s:7:\"Точка 2\";s:3:\"LON\";d:37.58337020874;s:3:\"LAT\";d:55.752718847644;}i:2;a:3:{s:4:\"TEXT\";s:7:\"Магазин\";s:3:\"LON\";d:37.633838653564;s:3:\"LAT\";d:55.770200458426;}}}",
		"MAP_HEIGHT" => "500",
		"MAP_ID" => "",
		"MAP_WIDTH" => "100%",
		"OPTIONS" => array(
			0 => "ENABLE_DBLCLICK_ZOOM",
			1 => "ENABLE_DRAGGING",
			2 => "ENABLE_KEYBOARD",
		)
	),
	false
);?><br>
<br><br>
		<?$APPLICATION->IncludeComponent(
			"bitrix:form.result.new", 
			"twoColumns", 
			array(
				"CACHE_TIME" => "360000",
				"CACHE_TYPE" => "Y",
				"CHAIN_ITEM_LINK" => "",
				"CHAIN_ITEM_TEXT" => "",
				"EDIT_URL" => "",
				"IGNORE_CUSTOM_TEMPLATE" => "N",
				"LIST_URL" => "",
				"SEF_MODE" => "N",
				"SUCCESS_URL" => "",
				"USE_EXTENDED_ERRORS" => "Y",
				"WEB_FORM_ID" => "2",
				"COMPONENT_TEMPLATE" => ".default",
				"VARIABLE_ALIASES" => array(
					"WEB_FORM_ID" => "WEB_FORM_ID",
					"RESULT_ID" => "RESULT_ID",
				)
			),
			false
		);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php")?>