<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Как стать оптовиком");?><h1>Как стать оптовиком</h1>
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
<div class="global-block-container">
	<div class="global-content-block">
		<img src="<?=SITE_TEMPLATE_PATH?>/images/optBanner.png" class="pagePicture" alt="">
		<h2 class="mediumText">Мы работаем с оптовыми покупателями (как с физическими, так и с юридическими лицами).</h2>
		<h3>Возможные варианты сотрудничества:</h3>
		<div class="priceTableContainer">
			<table class="priceTableStyle80">
				<thead>
					<tr>
						<th>Тип цены</th>
						<th>Как получить статус </th>
						<th>Скидка</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>Розничная цена</td>
						<td>Базовая цена для всех незарегистрированных пользователей</td>
						<td>Базовая цена</td>
					</tr>
					<tr>
						<td>Постоянный клиент</td>
						<td>Статус присваевается при покупке более чем на 15 000 рублей</td>
						<td>Скидка: 5%</td>
					</tr>
					<tr>
						<td>Мелкий опт</td>
						<td>Статус присваевается при покупке более чем на 45 000 рублей</td>
						<td>Скидка: 10%</td>
					</tr>
					<tr>
						<td>Крупный опт</td>
						<td>Статус присваевается при покупке более чем на100 000 рублей ЕЖЕМЕСЯЧНО</td>
						<td>Скидка: 15%</td>
					</tr>
				</tbody>
			</table>
		</div>

		<h3 class="mediumText">Пять причин работать с нами!</h3>
		<ul>
			<li>Выгодные цены.</li>
			<li>Гибкая система скидок.</li>
			<li>Удобная система оповещения.</li>
			<li>Оперативная отгрузка товара.</li>
			<li>Быстрая доставка товара до клиента.</li>
		</ul>

		<h3 class="mediumText">Стать оптовиком легко! - просто напишите нам!</h3>
		<p>В письме необходимо указать:</p>
		<ul>
			<li>ФИО контактного лица</li>
			<li>Электронная почта </li>
			<li>Полное наименование предприятия</li>
			<li>Наименование бренда</li>
			<li>Контактный телефон</li>
		</ul>
		<h4 class="mediumText">Адрес элекронной почты для связи: <a href="mailto:<?=COption::GetOptionString("sale", "order_email")?>"><?=COption::GetOptionString("sale", "order_email")?></a></h4>
		Мы будем рады взаимовыгодному сотрудничеству!
	</div>
	<div class="global-information-block">
		<?$APPLICATION->IncludeComponent(
			"bitrix:main.include", 
			".default", 
			array(
				"COMPONENT_TEMPLATE" => ".default",
				"AREA_FILE_SHOW" => "sect",
				"AREA_FILE_SUFFIX" => "information_block",
				"AREA_FILE_RECURSIVE" => "Y",
				"EDIT_TEMPLATE" => ""
			),
			false
		);?>
	</div>
</div>
<br><br><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>