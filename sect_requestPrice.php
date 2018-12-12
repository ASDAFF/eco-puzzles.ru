<div id="requestPrice" data-load="<?=SITE_TEMPLATE_PATH?>/images/picLoad.gif">
	<div id="requestPriceContainer">
		<div class="requestPriceHeading">Запросить стоимость товара <a href="#" class="close closeWindow"></a></div>
		<div class="requstProductContainer">
			<div class="productColumn">
				<div class="productImageBlock">
					<a href="#" class="requestPriceUrl" target="_blank"><img src="<?=SITE_TEMPLATE_PATH?>/images/picLoad.gif" alt="" class="requestPricePicture"></a>
				</div>
				<div class="productNameBlock">
					<a href="#" class="productUrl requestPriceUrl" target="_blank">
						<span class="middle">Загрузка товара</span>
					</a>
				</div>
			</div>
			<div class="formColumn">
				<div class="requestPriceFormHeading">Заполните данные для запроса цены</div>
				<form id="requestPriceForm" method="GET">
					<input type="text" name="name" value="" placeholder="Имя" id="requestPriceFormName">
					<input type="text" name="telephone" value="" data-required="Y" placeholder="Телефон*" id="requestPriceFormTelephone">
					<input type="hidden" name="productID" value="" id="requestPriceProductID">
					<input name="id" type="hidden" id="requestPriceFormId" value="">
					<input name="act" type="hidden" id="requestPriceFormAct" value="requestPrice">
					<input name="SITE_ID" type="hidden" id="requestPriceFormSiteId" value="<?=SITE_ID?>">
					<textarea name="message" placeholder="Сообщение"></textarea>
					<div class="personalInfoRequest"><input type="checkbox" name="personalInfoRequest" id="personalInfoRequest"><label for="personalInfoRequest">Я согласен на <a href="/personal-info/" class="pilink">обработку персональных данных.</a>*</label></div>
					<a href="#" id="requestPriceSubmit"><img src="<?=SITE_TEMPLATE_PATH?>/images/request.png" alt="Запросить цену"> Запросить цену</a>
				</form>
			</div>
		</div>
		<div id="requestPriceResult">
			<div id="requestPriceResultTitle"></div>
			<div id="requestPriceResultMessage"></div>
			<a href="" id="requestPriceResultClose" class="closeWindow">Закрыть окно</a>
        </div>
	</div>
</div>