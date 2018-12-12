$(function(){

	//extented prices
	var reCalcPrice = function($qtyBox, currentQuantity){
		if(currentQuantity > 0){

			//price
			var $priceContainer = $("#catalogElement .mainTool .priceContainer .priceVal");

			//check for empty
			if($priceContainer.length > 0){
				var $priceContainerStr = $priceContainer.html().replace(/\d\.\d/g, '').replace(/[0-9]/g, '');
			}

			//discount
			var $discountContainer = $("#catalogElement .mainTool .price .discount");
			if($discountContainer.length > 0){
				var $discountContainerStr = $discountContainer.html().replace(/\d\.\d/g, '').replace(/[0-9]/g, '');
			}

			//economy
			var $economyContainer = $("#catalogElement .mainTool .price .economy");
			if($economyContainer.length > 0){
				var $economyContainerStr = $economyContainer.html().replace(/\d\.\d/g, '').replace(/[0-9]/g, '');
			}

			//get price object
			var obExtentedPrices = $qtyBox.data("extended-price");

			if(typeof obExtentedPrices != "undefined"){
				if(typeof obExtentedPrices == "string"){
					obExtentedPrices = $.parseJSON(obExtentedPrices);
				}
			}

			//check for empty object
			if(typeof obExtentedPrices == "object"){

				//each prices
				$.each(obExtentedPrices, function(index, nextValue){

					//check for empty quantity
					if(nextValue["QUANTITY_FROM"] != null || nextValue["QUANTITY_TO"] != null){

						//check for current quantity
						if((nextValue["QUANTITY_FROM"] == null || nextValue["QUANTITY_FROM"] != "" && currentQuantity >= nextValue["QUANTITY_FROM"]) && (nextValue["QUANTITY_TO"] == null || nextValue["QUANTITY_TO"] != "" && currentQuantity <= nextValue["QUANTITY_TO"])){
							
							//write price
							if(typeof nextValue["DISCOUNT_PRICE"] != "undefined"){
								$priceContainer.html(formatPrice(Number(nextValue["DISCOUNT_PRICE"]).toFixed(0)) + $priceContainerStr);
							}

							//write discount
							if(typeof nextValue["OLD_PRICE"] != "undefined"){
								$discountContainer.html(formatPrice(Number(nextValue["OLD_PRICE"]).toFixed(0)) + $discountContainerStr);
							}
							
							//write economy
							if(typeof nextValue["ECONOMY"] != "undefined"){
								$economyContainer.html(formatPrice(Number(nextValue["ECONOMY"]).toFixed(0)) + $economyContainerStr);
							}

						}
					}
				});
			}

		}
		return;
	};

	var addCartPlus = function(event){

		var $qtyBox = $("#catalogElement .secondTool .qtyBlock .qty");
		var $addCartBtn = $("#catalogElement .addCart.changeQty");

		var xCurrentQtyValue = Number($qtyBox.val());
		var xQtyStep = Number($qtyBox.data("step"));
		var xQtyExpression = Number((xCurrentQtyValue * 10 + xQtyStep * 10) / 10); //js magic .9999999

		var _enableTrace = $qtyBox.data("enable-trace");
		var _maxQuantity = Number($qtyBox.data("max-quantity"));

		var __qtyError = false;
		var xTmpExpression = 0;

		if(_enableTrace == "Y"){

			xTmpExpression = xQtyExpression;
			xQtyExpression = (xQtyExpression > _maxQuantity) ? _maxQuantity : xQtyExpression;

			if(xTmpExpression != xQtyExpression){
				__qtyError = true;
			}

		}

		$qtyBox.val(xQtyExpression);
		$addCartBtn.data("quantity", xQtyExpression);

		//extented prices
		reCalcPrice($qtyBox, xQtyExpression);

		//set or remove error
		__qtyError === true ? $qtyBox.addClass("error") : $qtyBox.removeClass("error");

		return event.preventDefault();

	};

	var addCartMinus = function(event){

		var $qtyBox = $("#catalogElement .secondTool .qtyBlock .qty");
		var $addCartBtn = $("#catalogElement .addCart.changeQty");

		var xCurrentQtyValue = Number($qtyBox.val());
		var xQtyStep = Number($qtyBox.data("step"));
		var xQtyExpression = Number((xCurrentQtyValue * 10 - xQtyStep * 10) / 10); //js magic .9999999

		var _enableTrace = $qtyBox.data("enable-trace");
		var _maxQuantity = Number($qtyBox.data("max-quantity"));

		var __qtyError = false;
		var xTmpExpression = 0;

		xQtyExpression = xQtyExpression > xQtyStep ? xQtyExpression : xQtyStep;

		if(_enableTrace == "Y"){

			xTmpExpression = xQtyExpression;
			xQtyExpression = (xQtyExpression > _maxQuantity) ? _maxQuantity : xQtyExpression;

			if(xTmpExpression != xQtyExpression){
				__qtyError = true;
			}

		}

		$qtyBox.val(xQtyExpression);
		$addCartBtn.data("quantity", xQtyExpression);

		//extented prices
		reCalcPrice($qtyBox, xQtyExpression);

		//set or remove error
		__qtyError === true ? $qtyBox.addClass("error") : $qtyBox.removeClass("error");

		return event.preventDefault();

	};

	var addCartChange = function(event){

		var $this = $(this);
		var $addCartBtn = $("#catalogElement .addCart.changeQty");

		var xCurrentQtyValue = $this.val();
		var xQtyStep = Number($this.data("step"));

		var _enableTrace = $this.data("enable-trace");
		var _maxQuantity = Number($this.data("max-quantity"));

		var __qtyError = false;
		var xTmpExpression = 0;

		if(xCurrentQtyValue.replace(/[^\d.]/gi, '') != xCurrentQtyValue){
			xCurrentQtyValue = xQtyStep;
		}else{
			xCurrentQtyValue = Number(xCurrentQtyValue);
		}

		xQtyExpression = Math.ceil(xCurrentQtyValue / xQtyStep) * xQtyStep;

		if(_enableTrace == "Y"){

			xTmpExpression = xQtyExpression;
			xQtyExpression = (xQtyExpression > _maxQuantity) ? _maxQuantity : xQtyExpression;

			if(xTmpExpression != xQtyExpression){
				__qtyError = true;
			}

		}

		$this.val(xQtyExpression);
		$addCartBtn.data("quantity", xQtyExpression);
		
		//extented prices
		reCalcPrice($this, xQtyExpression);

		//set or remove error
		__qtyError === true ? $this.addClass("error") : $this.removeClass("error");

	};

	$(document).on("click", "#catalogElement .qtyBlock .plus", addCartPlus);
	$(document).on("click", "#catalogElement .qtyBlock .minus", addCartMinus);
	$(document).on("change", "#catalogElement .qtyBlock .qty", addCartChange);

});