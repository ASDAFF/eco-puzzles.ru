<?
$MESS["SHIPTOR_TITLE"] = "Доставки Shiptor";
$MESS["SHIPTOR_DESCRIPTION"] = "Доставки Shiptor";
$MESS["SHIPTOR_ORDERS"] = "Заказы Shiptor";
$MESS["SHIPTOR_COLLECTOR"] = "Заборы посылок";
$MESS["SHIPTOR_UNLOAD"] = "Выгрузка данных";
$MESS["SHIPTOR_TRACKING"] = "Отслеживание";
$MESS["SHIPTOR_SEND_TO"] = "Отправить в Shiptor";
$MESS["SHIPTOR_UPDATE_STATUS"] = "Обновить статус";
$MESS["SHIPTOR_SEND_FULLFILL"] = "Отправить в Shiptor";
$MESS["SHIPTOR_DELETE"] = "Удалить посылку из Shiptor";
$MESS["SHIPTOR_ADD_SERVICE_NAME"] = "Услуга доставки посылки";
$MESS["SHIPTOR_CARRIAGE_STATUS"] = "Статус посылки в Shiptor";
$MESS["SHIPTOR_ERROR_CANT_UPLOAD_NALOZH_PLATEZH_PS"] = "Не удалось выгрузить не оплаченный заказ, услуга «Наложенный платеж» не подключена для платежной системы заказа!";
$MESS["SHIPTOR_ERROR_CANT_UPLOAD_NO_NALOZH_PLATEZH"] = "Не удалось выгрузить не оплаченный заказ, услуга «Наложенный платеж» не подключена!";
$MESS["SHIPTOR_FATAL_ERROR_NO_PVZ"] = "Ошбика! В заказе отсутствует ПВЗ!";
$MESS["SHIPTOR_FATAL_ERROR_SETTINGS"] = "Ошибка! Не удалось получить настройки Агрегатора служб доставки Shiptor!";
$MESS["SHIPTOR_ERROR_STATUS"] = "Посылка #ID# заказа №#SHIPMENT_NUM# имеет статус «Не отгружено»!";
$MESS["SHIPTOR_ERROR_GET_STATUS"] = "Не удалось получить статус посылки #ID# заказа №#SHIPMENT_NUM#: #SHIPTOR_MESSAGE#";
$MESS["SHIPTOR_ERROR_TRACK_NO"] = "У посылки #ID# заказа №#SHIPMENT_NUM# нет трек-номера!";
$MESS["SHIPTOR_ERROR_TRACK_ALREADY"] = "Посылка #ID# заказа №#SHIPMENT_NUM# уже имеет Трек-номер: #TRACK_NUM#";
$MESS["SHIPTOR_ERROR_SENT_ALREADY"] = "Посылка #ID# заказа №#SHIPMENT_NUM# уже имеет статус «Отгружено» #TRACK_NUM#";
$MESS["SHIPTOR_ERROR_SEND_NOT_ALLOWED"] = "Отправка посылки #ID# заказа №#SHIPMENT_NUM# не разрешена!";
$MESS["SHIPTOR_ERROR_SENT_WRONG"] = "Не удалось отправить посылку #ID# заказа №#SHIPMENT_NUM#: #SHIPTOR_MESSAGE#";
$MESS["SHIPTOR_ERROR_PRODUCT_ADD_WRONG"] = "Не удалось передать товар #NAME# посылки #ID# заказа №#SHIPMENT_NUM#: #SHIPTOR_MESSAGE#";
$MESS["SHIPTOR_ERROR_PRODUCT_ADD_WRONG_USUAL_SENT"] = "Не удалось передать товары посылки #ID# заказа №#SHIPMENT_NUM#, посылка передана без товаров!";
$MESS["SHIPTOR_REMOVE_SUCCESS"] = "Заказ #ORDER# успешно удален из Shiptor";
$MESS["SHIPTOR_REMOVE_ERROR"] = "Не удалось удалить #ORDER#: #SHIPTOR_MESSAGE#";
$MESS["SHIPTOR_SEND_SUCCESS"] = "Заказ #ORDER# успешно выгружен в Shiptor";
$MESS["SHIPTOR_SEND_SUCCESS_MULTY"] = "Успешно выгружены в Shiptor заказы: #ORDER_IDS#";
$MESS["SHIPTOR_SEND_ERROR_MULTY"] = "Не были выгружны в Shiptor заказы: #ORDER_IDS#";

$MESS["SHIPTOR_BTN_SETTINGS"] = "Настройки";
$MESS["SHIPTOR_BTN_SETTINGS_TEXT"] = "Перейти в настройки модуля";

$MESS["SHIPTOR_BTN_CREATE_AGENT"] = "Автоматическая проверка и отправка";
$MESS["SHIPTOR_BTN_CREATE_AGENT_TEXT"] = "Создать агента для автоматической проверки статусов и отправки заказов";
$MESS["SHIPTOR_WAIT"] = "Пожалуйста подождите";
$MESS["SHIPTOR_WRONG"] = "Что-то пошло не так!";
$MESS["SHIPTOR_AGENT_CREATED"] = "Агент успешно создан! <a href='/bitrix/admin/agent_edit.php?ID=#ID#&lang=ru' target='_blank'>Перейти к агенту</a>";
$MESS["SHIPTOR_AGENT_ALREADY_WORKING"] = "Агент уже создан! <a href='/bitrix/admin/agent_edit.php?ID=#ID#&lang=ru' target='_blank'>Перейти к агенту</a>";
$MESS["SHIPTOR_AGENT_INACTIVE"] = "Агент создан, но не активен! <a href='/bitrix/admin/agent_edit.php?ID=#ID#&lang=ru' target='_blank'>Перейти к агенту</a>";

$MESS["SHIPTOR_BTN_SEND_WARES"] = "Передать товары";
$MESS["SHIPTOR_BTN_SEND_WARES_TEXT"] = "Передать все товары в ЛК Shiptor";
$MESS["SHIPTOR_SALE_ORDER_MARKED"] = "С маркировкой";

$MESS["SHIPTOR_DELIVERY_TYPE"] = "Тип доставки";
$MESS["SHIPTOR_DELIVERY_TYPE_COMMON"] = "Обычные профили";
$MESS["SHIPTOR_DELIVERY_TYPE_DIRECT"] = "Сквозные профили";