<?
$MESS["SHIPTOR_PVZ_NAME"] = "Пункт выдачи Shiptor";
$MESS["SHIPTOR_MODULE_NAME"] = "Агрегатор служб доставки Shiptor";
$MESS["SHIPTOR_MODULE_DESC"] = "Работа с ведущими службами доставки через единый договор. Доставка по Москве на следующий день, курьерскими службами по РФ в любые ПВЗ и постаматы, Почтой России — в отдаленные уголки. Быстрые взаиморасчеты по наложенному платежу. Возьмем на себя возвраты и задолженности. Заберем заказы у ваших поставщиков, обработаем и передадим на доставку. (Предупреждение: код работает на версиях модуля sale 16.0 и выше!)";
$MESS["SHIPTOR_PARTNER_NAME"] = "Shiptor";
$MESS["SHIPTOR_PARTNER_URI"] = "http://shiptor.ru";

$MESS['WSD_STEP1_TITLE'] = 'Мастер быстрой активации модуля Агрегатор служб доставки Shiptor';
$MESS['WSD_STEP1_CONTENT'] = '<p>Для того чтобы модуль начал работать, нужно указать ключ API. Его можно взять в вашем <a href="https://shiptor.ru/account/settings/api" target="_blank">ЛК Shiptor</a></p>';
$MESS['WSD_STEP1_API_LABEL'] = 'Ваш ключ API: ';
$MESS['WSD_STEP1_WHERE'] = '<p>Если у вас он уже указан, переходите к следующему шагу.</p>';

$MESS['WSD_NEXT'] = 'Далее';
$MESS['WSD_FINISH'] = 'Завершить';

$MESS['WSD_STEP2_TITLE'] = 'Создание и настройка службы доставки';
$MESS['WSD_STEP2_ERROR_NO_API_KEY'] = '<span style="color:red;">Вы не указали ключ API</span>';
$MESS['WSD_STEP2_ERROR_WRONG_API_KEY'] = '<span style="color:red;">Ключ API неверен!</span>';
$MESS['WSD_STEP2_ERROR_CREATING'] = '<span style="color:red;">Не удалось создать родительскую службу доставки!</span>';
$MESS['WSD_STEP2_ERROR_SMTH'] = '<span style="color:red;">Не удалось получить ответ от API Shiptor</span>';
$MESS['WSD_STEP2_ALREADY_EXISTS'] = '<p>У вас есть родительская служба доставки Shiptor</p>';
$MESS['WSD_STEP2_CREATED'] = '<p>Создана родительская служба доставки Shiptor</p>';
$MESS['WSD_STEP2_PROFILES_CREATED'] = '<p>Созданы все доступные профили доставки Shiptor. Перейдите в <a href="/bitrix/admin/sale_delivery_service_edit.php?lang=ru&PARENT_ID=0&ID=#ID#" target="_blank">настройки службы доставки</a> во вкладку Профили и активируйте нужные вам.</p>';

$MESS['WSD_STEP2_DEFAULT_SIZE_PARAMS'] = 'Задайте габариты посылки по умолчанию';
$MESS['WSD_STEP2_DP_LENGTH'] = 'Длина, см';
$MESS['WSD_STEP2_DP_WIDTH'] = 'Ширина, см';
$MESS['WSD_STEP2_DP_HEIGHT'] = 'Высота, см';
$MESS['WSD_STEP2_DP_WEIGHT'] = 'Вес, кг';
$MESS['WSD_STEP2_DEFAULT_CALC_ALGO'] = 'Выберите алгоритм расчета габаритов для нескольких товаров';
$MESS['WSD_STEP2_DP_CALC_ALGORITM'] = 'Тип расчета';
$MESS['WSD_STEP2_DEFAULT_CALC_ALGO_SIMPLE'] = 'По умолчанию для посылки';
$MESS['WSD_STEP2_DEFAULT_CA_SIMPLE'] = 'В этом режиме габариты по умолчанию будут использоваться в качестве габаритов всей посылки, если у вашего товара в каталоге не заполнены габариты или его количество превышает 1';
$MESS['WSD_STEP2_DEFAULT_CALC_ALGO_COMPLEX'] = 'Автоматически для товаров';
$MESS['WSD_STEP2_DEFAULT_CA_COMPLEX'] = 'В этом режиме габариты по умолчанию будут использоваться если у какого-либо из товаров в заказе не заполнены габариты, и будут использованы в качестве габаритов такого товара. Итоговый габарит посылки будет расчитываться, как эффективная величина из суммарного объема товаров заказа.';
$MESS['WSD_STEP2_ADDRESS_PROPS'] = 'Настройте привязки свойств заказов';
$MESS['WSD_STEP2_ADDRESS_TYPE'] = 'Тип поля Адрес';
$MESS['WSD_STEP2_ADDRESS_TYPE_SIMPLE'] = 'Единое';
$MESS['WSD_STEP2_ADDRESS_TYPE_COMPLEX'] = 'Составное';
$MESS['WSD_STEP2_ADDRESS_PROP_ID'] = 'Свойство типа Адрес для <b>#PERSON_TYPE#</b>';
$MESS['WSD_STEP2_STREET_PROP_ID'] = 'Свойство Улица для <b>#PERSON_TYPE#</b>';
$MESS['WSD_STEP2_BLD_PROP_ID'] = 'Свойство Дом для <b>#PERSON_TYPE#</b>';
$MESS['WSD_STEP2_CORP_PROP_ID'] = 'Свойство Корпус для <b>#PERSON_TYPE#</b>';
$MESS['WSD_STEP2_FLAT_PROP_ID'] = 'Свойство Квартира для <b>#PERSON_TYPE#</b>';
$MESS['WSD_STEP2_PVZ_PROP_ID'] = 'Свойство типа Код ПВЗ для <b>#PERSON_TYPE#</b>';

$MESS['WSD_FINALSTEP_TITLE'] = 'Настройка завершена';
$MESS['WSD_FINALSTEP_CONTENT_HEAD'] = '<span style="color:green;">Базовые настройки модуля успешно завершены!</span>';
$MESS['WSD_FINALSTEP_CONTENT_FAST_DOC_LINK'] = 'Чтобы ознакомиться с документацией к модулю воспользуйтесь ссылкой <a href="http://bitrix.shiptor.ru/learning/course/?COURSE_ID=1&LESSON_ID=21&LESSON_PATH=1.21" target="_blank">Быстрый старт</a>';
$MESS['WSD_FINALSTEP_CONTENT_MODULE_LINK'] = 'Настройки модуля расположены <a href="/bitrix/admin/settings.php?lang=ru&mid=shiptor.delivery&mid_menu=1" target="_blank">здесь</a>';
$MESS['WSD_FINALSTEP_CONTENT_DELIVERY_LINK'] = 'Настройки службы доставки расположены <a href="/bitrix/admin/sale_delivery_service_edit.php?lang=ru&PARENT_ID=0&ID=#ID#" target="_blank">здесь</a>';