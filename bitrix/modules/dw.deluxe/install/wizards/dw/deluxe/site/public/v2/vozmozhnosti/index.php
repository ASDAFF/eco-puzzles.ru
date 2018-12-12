<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Возможности");?>
<h1 class="ff-medium"> Возможности </h1>

<div class="detail-text-wrap flex">
	<div class="flex-item">
		<div class="h1">Заголовок H1</div>
		<div class="h1 ff-bold">Заголовок H1 жирный</div>
		<br>
		<div class="h2">Заголовок H2</div>
		<div class="h2 ff-medium">Заголовок H2 (medium)</div>
		<br>
		<div class="h3">Заголовок H3</div>
		<div class="h3 ff-bold">Заголовок H3 жирный</div>
		<br>
	</div>
	<div class="flex-item">
		<h3 class="h3 ff-bold">Маркированный список</h3>
		<ul>
			<li>2 варианта шаблона в одном решении! </li>
			<li>Вы можете развернуть  2 сайта,</li>
			<li>Бесплатная установка на хостинг</li>
		</ul>
		<br>
		<h3 class="h3 ff-bold">Нумерованный список</h3>
		<ol>
			<li>2 варианта шаблона в одном решении! </li>
			<li>Вы можете развернуть  2 сайта,</li>
			<li>Бесплатная установка на хостинг</li>
		</ol>
		<br>
	</div>
</div>




<br/><br/><br/>
<div class="h2 ff-bold">Кнопки</div>





<div class="btn-wrap">
	<div class="btn-simple">Основная кнопка</div>

	<div class="btn-simple btn-border">Кнопка с обводкой</div>

	<div class="btn-simple btn-black">Черная кнопка</div>

	<div class="btn-simple btn-black-border">Кнопка с обводкой</div>
</div>

<div class="btn-wrap">
	<div class="btn-simple btn-medium">Средняя кнопка</div>

	<div class="btn-simple btn-medium btn-border">Средняя кнопка</div>

	<div class="btn-simple btn-medium btn-black">Средняя кнопка</div>

	<div class="btn-simple btn-medium btn-black-border">Средняя кнопка</div>
</div>

<div class="btn-wrap">
	<div class="btn-simple btn-small">Малая кнопка</div>

	<div class="btn-simple btn-small btn-border">Малая кнопка</div>

	<div class="btn-simple btn-small btn-black">Малая кнопка</div>

	<div class="btn-simple btn-small btn-black-border">Малая кнопка</div>
</div>

<div class="btn-wrap">
	<div class="btn-simple btn-micro">Микро кнопка</div>

	<div class="btn-simple btn-micro btn-border">Микро кнопка</div>

	<div class="btn-simple btn-micro btn-black">Микро кнопка</div>

	<div class="btn-simple btn-micro btn-black-border">Микро кнопка</div>
</div>

<div class="btn-wrap">
	<div class="btn-simple add-cart">В корзину</div>
</div>


<div class="btn-wrap">
	<a href="#" class="active-link">Активная ссылка</a><br/>
	<a href="#" class="inactive-link">Неактивная ссылка</a>
</div>

<div class="btn-wrap">
	<a href="#" class="big-text-link">Подробнее</a>
</div>

<div class="btn-wrap">
	<a href="#" class="theme-link-dashed">Подробнее</a>
</div>

<div class="btn-wrap">
	<a href="#" class="link-dashed">Подробнее</a>
</div>



<br/><br/><br/>
<div class="h2 ff-bold">Таблица</div>



<div class="detail-text-wrap">
	<div class="table-simple-wrap">
		<table class="table-simple">
			<tr>
				<th>Город доставки</th>
				<th>Условия доставки</th>
				<th>Стоимость </th>
			</tr>
			<tr>
				<td>Курьером по Санкт-Петербургу</td>
				<td>Бесплатно при заказе от 3 000 рублей</td>
				<td>350 руб</td>
			</tr>
			<tr>
				<td>Курьером по Москве</td>
				<td>Бесплатно при заказе от 5 000 рублей</td>
				<td>500 руб</td>
			</tr>
			<tr>
				<td>Доставка по России транспортной компанией</td>
				<td>Согласно тарифам Транспортной компании. До склада ТК (Деловые линии и СДЭК) бесплатно</td>
				<td>350 руб</td>
			</tr>
		</table>
	</div>
</div>






<br/><br/><br/>
<div class="h2 ff-bold">Веб форма в 2 ряда</div>







<div class="detail-text-wrap">
	<?$APPLICATION->IncludeComponent(
	"bitrix:form.result.new", 
	"twoColumns", 
	array(
		"CACHE_TIME" => "360000",
		"CACHE_TYPE" => "Y",
		"CHAIN_ITEM_LINK" => "",
		"CHAIN_ITEM_TEXT" => "",
		"COMPONENT_TEMPLATE" => "twoColumns",
		"EDIT_URL" => "",
		"IGNORE_CUSTOM_TEMPLATE" => "N",
		"LIST_URL" => "",
		"SEF_MODE" => "N",
		"SUCCESS_URL" => "",
		"USE_EXTENDED_ERRORS" => "Y",
		"WEB_FORM_ID" => "2",
		"VARIABLE_ALIASES" => array(
			"WEB_FORM_ID" => "WEB_FORM_ID",
			"RESULT_ID" => "RESULT_ID",
		)
	),
	false
);?>
</div>





<br/><br/><br/>
<div class="h2 ff-bold">Оформление преимуществ</div>






<div class="list-advantages">
	<div class="list-item">
		<div class="tb">
			<div class="tc image">
				<img src="/bitrix/templates/dresscode/images/advantages-img1.png" alt="">
			</div>
			<div class="tc text-wrap">
				<div class="name">Собственная служба доставки</div>
				<div class="descr">По Москве и Санкт-Петербургу доставка в день покупки</div>
			</div>
		</div>
	</div>
	<div class="list-item">
		<div class="tb">
			<div class="tc image">
				<img src="/bitrix/templates/dresscode/images/advantages-img2.png" alt="">
			</div>
			<div class="tc text-wrap">
				<div class="name">Отправим заказ в любой город</div>
				<div class="descr">Отправим заказ любой удобной курьерской службой</div>
			</div>
		</div>
	</div>
	<div class="list-item">
		<div class="tb">
			<div class="tc image">
				<img src="/bitrix/templates/dresscode/images/advantages-img3.png" alt="">
			</div>
			<div class="tc text-wrap">
				<div class="name">Собственная служба доставки</div>
				<div class="descr">По Москве и Санкт-Петербургу доставка в день покупки</div>
			</div>
		</div>
	</div>
	<div class="list-item">
		<div class="tb">
			<div class="tc image">
				<img src=/bitrix/templates/dresscode/images/advantages-img2.png" alt="">
			</div>
			<div class="tc text-wrap">
				<div class="name">Отправим заказ в любой город</div>
				<div class="descr">Отправим заказ любой удобной курьерской службой</div>
			</div>
		</div>
	</div>
</div>







<br/><br/><br/>
<div class="h2 ff-bold">Выделение текста</div>


	<div class="error-wrap">ВНИМАНИЕ! Неправильно указанный номер телефона, неточный или неполный адрес могут привести к дополнительной задержке! Пожалуйста, внимательно проверяйте ваши персональные данные при регистрации и оформлении заказа.Конфиденциальность ваших регистрационных данных гарантируется.</div>





<br/><br/><br/>
<div class="h2 ff-bold">Цитата</div>




	<div class="blockquote-wrap">
		<blockquote>Благодаря своей зоркости, своей удивительной способности беспристрастно воспринимать мир, Пелевин первым увидел, что восторжествовала несвобода, потому что свобода – это примета сложной системы, разветвленной, гибкой, имеющей множество внутренних укрытий и лабиринтов, множество непредсказуемых вариантов развития.</blockquote>
		<p>- Дмитрий Быков о Пелевине. "Путь вниз". Лекция первая</p>
	</div>




<br/><br/><br/>
<div class="h2 ff-bold">Выделение текста</div>






<div class="blockquote-wrap">
                <p>Полноценный функционал посадочных страниц, отлично продуманный для СЕО. Можно создать произвольный баннер с 2мя картинками, отдельной произвольной текстовой областью в баннере, возможностью создать любой текст сверху страницы, а также любой текст и различные блоки внизу страницы. Функционал отлично подходит для создания посадочных страниц с применением умного фильтра, а также можно применять такую структуру на любой текстовой странице</p>
      </div>



<br/><br/><br/>
<div class="h2 ff-bold">Заказать консультацию</div>






	<div class="consultation-wrap">
		<div class="tb">
			<div class="tc">
				<div class="tb">
					<div class="tc image">
						<img src="/bitrix/templates/dresscode/images/consultation-img.png" alt="">
					</div>
					<div class="tc">
						<div class="consultation-heading">Заказать консультацию</div>
						<div class="text">Оставьте Ваши контактные данные и мы свяжемся с Вами в ближайшее время</div>
					</div>
				</div>
			</div>
			<div class="tc consultation-btn-wrap">
				<div class="btn-simple btn-medium consultation-btn">Обратная связь</div>
			</div>
		</div>
	</div>






<br/><br/><br/>
<div class="h2 ff-bold">Табы 1</div>






	<div class="tabs-wrap">
		<div class="tabs-links">
			<div class="tab-link tab-btn-link active">Активный таб</div>
			<div class="tab-link tab-btn-link">Неактивный таб</div>
			<div class="tab-link tab-btn-link">Неактивный таб</div>
		</div>
		<div class="tabs-content">
			<div class="tab-content active">
				Благодаря своей зоркости, своей удивительной способности беспристрастно воспринимать мир, Пелевин первым увидел, что восторжествовала несвобода, потому что свобода – это примета сложной системы, разветвленной, гибкой, имеющей множество внутренних укрытий и лабиринтов, множество непредсказуемых вариантов развития.
			</div>
			<div class="tab-content">
				Неблагодаря своей зоркости, своей удивительной способности беспристрастно воспринимать мир, Пелевин первым увидел, что восторжествовала несвобода, потому что свобода – это примета сложной системы, разветвленной, гибкой, имеющей множество внутренних укрытий и лабиринтов, множество непредсказуемых вариантов развития.
			</div>
			<div class="tab-content">
				Благодаря не своей зоркости, своей удивительной способности беспристрастно воспринимать мир, Пелевин первым увидел, что восторжествовала несвобода, потому что свобода – это примета сложной системы, разветвленной, гибкой, имеющей множество внутренних укрытий и лабиринтов, множество непредсказуемых вариантов развития.
			</div>
		</div>
	</div>




<br/><br/><br/>
<div class="h2 ff-bold">Табы 2</div>



<div class="detail-text-wrap">
	<div class="tabs-wrap">
		<div class="tabs-links">
			<div class="tab-link tab-dashed-link active">Активный таб</div>
			<div class="tab-link tab-dashed-link">Неактивный таб</div>
		</div>
		<div class="tabs-content">
			<div class="tab-content active">
				Благодаря своей зоркости, своей удивительной способности беспристрастно воспринимать мир, Пелевин первым увидел, что восторжествовала несвобода, потому что свобода – это примета сложной системы, разветвленной, гибкой, имеющей множество внутренних укрытий и лабиринтов, множество непредсказуемых вариантов развития.
			</div>
			<div class="tab-content">
				Своей удивительной способности беспристрастно воспринимать мир, Пелевин первым увидел, что восторжествовала несвобода, потому что свобода – это примета сложной системы, разветвленной, гибкой, имеющей множество внутренних укрытий и лабиринтов, множество непредсказуемых вариантов развития.
			</div>
		</div>
	</div>
</div>



<br/><br/><br/>
<div class="h2 ff-bold">Вопрос ответ (вариант без инфоблока)</div>
<br/>





				<div class="questions-answers-list">
					<div class="question-answer-wrap">
						<div class="question">Доставка по Санкт-Петербургу и Ленинградской области
							<div class="open-answer"><span class="hide-answer-text">Скрыть</span><span class="open-answer-text">Подробнее</span><div class="open-answer-btn"></div></div>
						</div>
						<div class="answer">По своей сути рыбатекст является альтернативой традиционному lorem ipsum, который вызывает у некторых людей недоумение при попытках прочитать рыбу текст. В отличии от lorem ipsum, текст рыба на русском языке наполнит любой макет непонятным смыслом и придаст неповторимый колорит советских времен.</div>
					</div>
					<div class="question-answer-wrap">
						<div class="question">Доставка заказов по Москве
							<div class="open-answer"><span class="hide-answer-text">Скрыть</span><span class="open-answer-text">Подробнее</span><div class="open-answer-btn"></div></div>
						</div>
						<div class="answer">По своей сути рыбатекст является альтернативой традиционному lorem ipsum, который вызывает у некторых людей недоумение при попытках прочитать рыбу текст. В отличии от lorem ipsum, текст рыба на русском языке наполнит любой макет непонятным смыслом и придаст неповторимый колорит советских времен.</div>
					</div>
					<div class="question-answer-wrap">
						<div class="question">Доставка заказов в Регионы
							<div class="open-answer"><span class="hide-answer-text">Скрыть</span><span class="open-answer-text">Подробнее</span><div class="open-answer-btn"></div></div>
						</div>
						<div class="answer">По своей сути рыбатекст является альтернативой традиционному lorem ipsum, который вызывает у некторых людей недоумение при попытках прочитать рыбу текст. В отличии от lorem ipsum, текст рыба на русском языке наполнит любой макет непонятным смыслом и придаст неповторимый колорит советских времен.</div>
					</div>
					<div class="question-answer-wrap">
						<div class="question">Способы оплаты
							<div class="open-answer"><span class="hide-answer-text">Скрыть</span><span class="open-answer-text">Подробнее</span><div class="open-answer-btn"></div></div>
						</div>
						<div class="answer">По своей сути рыбатекст является альтернативой традиционному lorem ipsum, который вызывает у некторых людей недоумение при попытках прочитать рыбу текст. В отличии от lorem ipsum, текст рыба на русском языке наполнит любой макет непонятным смыслом и придаст неповторимый колорит советских времен.</div>
					</div>
				</div>
<br/><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>