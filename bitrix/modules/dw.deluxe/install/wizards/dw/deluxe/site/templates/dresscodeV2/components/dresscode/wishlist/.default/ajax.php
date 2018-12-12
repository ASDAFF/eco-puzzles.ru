<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");?>
<?error_reporting(0);?>
<?
	if(!empty($_GET["act"])){
		
		if($_GET["act"] == "sendMail"){

			if(!empty($_GET["email"]) && !empty($_GET["siteID"])){

				//include modules
				\Bitrix\Main\Loader::includeModule("currency");

				//vars
				$SITE_NAME = "";
				$PRODUCT_LIST = "";

				//check wishlist products
				if(!empty($_SESSION["WISHLIST_LIST"]["ITEMS"])){

					//check email template
					$postMess = CEventMessage::GetList($by = "site_id", $order = "desc", array("SITE_ID" => $_GET["siteID"], "TYPE" => "SALE_DRESSCODE_WISHLIST_SEND"))->GetNext();

					//create new email template
					if(empty($postMess)){

						$MESSAGE = '<div style="background-color:#f3f3f3;padding-top:24px;padding-bottom:24px;margin:0;font-family:\'Arial\',sans-serif;">
							<div style="min-width:600px;max-width:600px;margin:auto;">
								<div style="padding-top:12px;padding-right:24px;padding-bottom:12px;padding-left:24px;margin-bottom:12px;background-color:#ffffff;">
									<table width="100%" cellpadding="0" cellspacing="0" border="0">
										<tbody>
											<tr>
												<td style="width:50%;text-align:left;">
													<a href="#SITE_URL#" target="_blank"><img src="#SITE_URL##SITE_TEMPLATE_PATH#/images/logo.png" style="max-width:100%;" alt=""></a>
												</td>
												<td style="width:50%;text-align:right;">
													<div><span style="font-weight:bold;font-family:\'Arial Bold\',Gadget,sans-serif;font-size:20px;color:#000000;">8 (800) 000-00-00</span></div>
													<div><a href="mailto:#SALE_EMAIL#" style="font-weight:bold;font-family:\'Arial Bold\',Gadget,sans-serif;font-size:14px;color:#000000;text-decoration:none;border-bottom:1px solid #000000;" target="_blank">#SALE_EMAIL#</a></div>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
								<div style="padding-top:18px;padding-right:24px;padding-bottom:24px;padding-left:24px;background-color:#ffffff;">
									<div style="margin-bottom:6px;font-weight:bold;font-family:\'Arial Bold\',Gadget,sans-serif;font-size:20px;line-height:24px;color:#000000;">Список избранных товаров на сайте #SITE_NAME#</div>
									<div style="margin-bottom:18px;font-weight:normal;font-family:\'Arial\',sans-serif;font-size:14px;line-height:18px;color:#000000;">Здравствуйте. Вы высылали запрос на отправку товаров, добавленных в избранное.</div>
									<div style="margin-bottom:8px;font-weight:bold;font-family:\'Arial Bold\',Gadget,sans-serif;font-size:14px;line-height:18px;color:#000000;">Вы выбрали:</div>
									#PRODUCT_LIST#
									<div style="margin-top:18px;text-align:center;">
										<a href="#SITE_URL#" style="display:inline-block;padding-top:12px;padding-right:18px;padding-bottom:14px;padding-left:18px;font-weight:normal;font-family:\'Arial\',sans-serif;font-size:14px;line-height:16px;background: #63c322;color:#fefefe;text-decoration:none;border-radius:2px;">Перейти на сайт магазина</a>
									</div>
								</div>
								<div style="padding-top:24px;padding-right:24px;padding-bottom:30px;padding-left:24px;margin-top:12px;background-color:#ffffff;">
									<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
										<tbody>
											<tr>
												<td style="width:50%;text-align:left;">
													<a href="#SITE_URL#" target="_blank"><img src="#SITE_URL##SITE_TEMPLATE_PATH#/images/logo.png" style="max-width:100%;" alt=""></a>
												</td>
												<td style="width:50%;text-align:right;">
													<div style="">С уважением, администрация магазина</div>
													<a href="#SITE_URL#" style="display:inline-block;margin-top:8px;padding-top:12px;padding-right:18px;padding-bottom:14px;padding-left:18px;font-weight:normal;font-family:\'Arial\',sans-serif;font-size:14px;line-height:16px;background: #63c322;color:#fefefe;text-decoration:none;border-radius:2px;">Перейти на сайт магазина</a>
												</td>
											</tr>
										</tbody>
									</table>
									<table width="100%" cellpadding="0" cellspacing="0" border="0">
										<tbody>
											<tr>
												<td style="width:50%;padding-top:18px;padding-bottom:18px;text-align:center;border-right:5px solid #fff;text-align:center;background:#f3f3f3;">
													<div style="margin-bottom:8px;font-weight:normal;font-family:\'Arial\',sans-serif;font-size:14px;line-height:18px;color:#000000;">Телефон магазина</div>
													<div style="font-weight:bold;font-family:\'Arial Bold\',Gadget,sans-serif;font-size:16px;line-height:18px;color:#000000;">8 (800) 000-00-00</div>
												</td>
												<td style="width:50%;padding-top:18px;padding-bottom:18px;text-align:center;border-left:5px solid #fff;text-align:center;background:#f3f3f3;">
													<div style="margin-bottom:8px;font-weight:normal;font-family:\'Arial\',sans-serif;font-size:14px;line-height:18px;color:#000000;">Email магазина</div>
													<a href="mailto:#SALE_EMAIL#" traget="_blank" style="font-weight:bold;font-family:\'Arial Bold\',Gadget,sans-serif;font-size:16px;line-height:18px;color:#000000;text-decoration:none;border-bottom:1px solid #000000;">#SALE_EMAIL#</a>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>';

						$FIELDS = "#SITE# \n #USER_EMAIL# \n #PRODUCT_LIST# \n #SITE_URL##SITE_TEMPLATE_PATH# \n #SALE_EMAIL# \n #SITE_URL# \n #SITE_NAME# \n";

						$et = new CEventType;
					    $et->Add(
					    	array(
						        "LID"           => "ru",
						        "EVENT_NAME"    => "SALE_DRESSCODE_WISHLIST_SEND",
						        "NAME"          => "Отправка избранных товаров на почту",
						        "DESCRIPTION"   => $FIELDS
					        )
					    );

						$arr["EMAIL_FROM"] = COption::GetOptionString("sale", "order_email");
						$arr["EVENT_NAME"] = "SALE_DRESSCODE_WISHLIST_SEND";
						$arr["SUBJECT"] = "Ваши избранные товары";
						$arr["EMAIL_TO"] = "#USER_EMAIL#";
						$arr["LID"] = $_GET["siteID"];
						$arr["BODY_TYPE"] = "html";
						$arr["MESSAGE"] = $MESSAGE;
						$arr["ACTIVE"] = "Y";
						$arr["BCC"] = "";

						$emess = new CEventMessage;
						$emess->Add($arr);

					}

					//create message
					$SITE_URL .= (CMain::IsHTTPS()) ? "https://" : "http://";
					$SITE_URL .= preg_replace("#/$#", "", $_SERVER["SERVER_NAME"]);

					$_GET["HIDE_NOT_AVAILABLE"] = !empty($_GET["HIDE_NOT_AVAILABLE"]) ? $_GET["HIDE_NOT_AVAILABLE"] : "N";

					foreach($_SESSION["WISHLIST_LIST"]["ITEMS"] as $arNextElementID){

						//parse product element
						ob_start();
						$APPLICATION->IncludeComponent(
							"dresscode:catalog.item", 
							"email", 
							array(
								"CACHE_TIME" => $_GET["CACHE_TIME"],
								"CACHE_TYPE" => $_GET["CACHE_TYPE"],
								"HIDE_MEASURES" => $_GET["HIDE_MEASURES"],
								"HIDE_NOT_AVAILABLE" => $_GET["HIDE_NOT_AVAILABLE"],
								"IBLOCK_ID" => $_GET["IBLOCK_ID"],
								"IBLOCK_TYPE" => $_GET["IBLOCK_TYPE"],
								"PRODUCT_ID" => $arNextElementID,
								"PICTURE_HEIGHT" => 200,
								"PICTURE_WIDTH" => 200,
								"CONVERT_CURRENCY" => $_GET["CONVERT_CURRENCY"],
								"CURRENCY_ID" => $_GET["CURRENCY_ID"],
								"PRODUCT_PRICE_CODE" => $_GET["PRICE_CODE"],
								"SITE_URL" => $SITE_URL
							),
							false
						);
						$PRODUCT_LIST .= ob_get_contents();
						ob_end_clean();

					}

					$FIELDS = "#SITE# \n #PRODUCT_LIST# \n #SITE_URL# \n #SITE_TEMPLATE_PATH#/ \n #SALE_EMAIL# \n #SITE_URL# \n #SITE_NAME# \n";

					//get site info
					$rsSites = CSite::GetList($by = "sort", $order = "desc", Array("ID" => $_GET["siteID"]));
					if($arSite = $rsSites->Fetch()){
						$SITE_NAME = $arSite["NAME"];
					}

					//write mail fields
					$arMessage = array(
						"SALE_EMAIL" => COption::GetOptionString("sale", "order_email"),
						"SITE_TEMPLATE_PATH" => SITE_TEMPLATE_PATH,
						"PRODUCT_LIST" => $PRODUCT_LIST,
						"USER_EMAIL" => $_GET["email"],
						"SITE" => SITE_SERVER_NAME,
						"SITE_NAME" => $SITE_NAME,
						"SITE_URL" => $SITE_URL
					);

					//send message
					CEvent::SendImmediate("SALE_DRESSCODE_WISHLIST_SEND", htmlspecialcharsbx($_GET["siteID"]), $arMessage, "Y", false);

					//return error
					echo \Bitrix\Main\Web\Json::encode(
						array("SUCCESS" => "Y")
					);

				}

				else{

					//return error
					echo \Bitrix\Main\Web\Json::encode(
						array("ERROR" => "Y")
					);

				}

			}

			else{

				//return error
				echo \Bitrix\Main\Web\Json::encode(
					array("ERROR" => "Y")
				);

			}

		}

	}
?>
