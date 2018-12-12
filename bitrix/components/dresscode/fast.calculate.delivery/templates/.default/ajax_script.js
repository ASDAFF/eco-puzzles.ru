$(function(){

	//j vars
	var $deliveryModal = $(".delivery-modal");
	var $deliveryStyles = $(".fastDeliveryModalStyles");
	var $deliveryScripts = $(".fastDeliveryModalScript");
	var $deliveryItemsContainer = $deliveryModal.find(".delivery-items");
	var $qtyBox = $deliveryModal.find(".qty");
	var $calcCheckbox = $deliveryModal.find("#delivery-modal-calc-checkbox");

	//vars
	var timeoutId = null;
	var timeoutValue = 500;

	//functions
	var deliveryQKeyPress = function(event){

		//vars
		var $this = $(this);
		var gValue = $this.val();
		var qtyRatio = Number($this.data("step"));
		var wValue;

		//calc value
		if(gValue.replace(/[^\d]/gi, '') != gValue){
			wValue = qtyRatio;
		}else if(Number(gValue) > qtyRatio){
			wValue = Number(gValue); 
		}else{
			wValue = qtyRatio;
		}

		//round
		wValue = Math.ceil(wValue / qtyRatio) * qtyRatio;

		//max value
		if(wValue > 9999){
			wValue = 9999;
		}
		
		//write
		$this.val(wValue);

		//reload items data
		clearTimeout(timeoutId);
		timeoutId = setTimeout(deliveryReloadItems, timeoutValue);

		//block action
		return event.preventDefault();
	
	};

	var deliveryQMinus = function(event){

		//vars
		var $this = $(this);
		var gQuantity = Number($qtyBox.val());
		var qtyRatio = Number($qtyBox.data("step"));

		//write & calc value
		if(gQuantity > qtyRatio){

			//write val
			$qtyBox.val((gQuantity * 10 - qtyRatio * 10 ) / 10).removeClass("error");

			//reload items data
			clearTimeout(timeoutId);
			timeoutId = setTimeout(deliveryReloadItems, timeoutValue);
			
		}

		//block action
		return event.preventDefault();

	};

	var deliveryQPlus = function(event){

		//vars
		var $this = $(this);
		var gQuantity = Number($qtyBox.val());
		var qtyRatio = Number($qtyBox.data("step"));

		//calc value
		var finishValue = ((gQuantity * 10 + qtyRatio * 10) / 10);

		//max value
		if(finishValue > 9999){
			finishValue = 9999;
		}

		//write value
		$qtyBox.val(finishValue).removeClass("error");

		//reload items data
		clearTimeout(timeoutId);
		timeoutId = setTimeout(deliveryReloadItems, timeoutValue);

		//block action
		return event.preventDefault();

	};

	var deliveryCalcAll = function(event){
		//reload items data
		clearTimeout(timeoutId);
		timeoutId = setTimeout(deliveryReloadItems, timeoutValue);
	}

	//error ajax
	var sendProcessingError = function(jqXHR, exception){
		$deliveryModal.removeClass("loading").addClass("error");
		console.error(jqXHR);
		console.error(exception);
	};

	var sendProcessingResult = function(jsonData){

		//check exist
		if(typeof jsonData["HTML_DATA"] != "undefined"){
			//write data
			$deliveryItemsContainer.html(jsonData["HTML_DATA"]);
		}

		//remove loader
		$deliveryModal.removeClass("loading");

	};

	var deliveryReloadItems = function(){

		if(typeof fastCalcDeliveryAjaxDir != "undefined"){
			
			if(typeof fastCalcDeliveryParams != "undefined"){
				
				//add loader
				$deliveryModal.addClass("loading");

				//calc all basket items flag
				$calcAllProducts = $calcCheckbox.prop("checked") ? "Y" : "N";

				//service data
				var sendObject = {
					"act": "getCalculatedItems",
					"quantity": $qtyBox.val(),
					"calcAllItems": $calcAllProducts
				}

				//merge objects
				$.extend(sendObject, fastCalcDeliveryParams);

				//get jsonData
				$.ajax({
					url: fastCalcDeliveryAjaxDir + "/ajax.php",
					type: "POST",
					data: sendObject,
					dataType: "json",
					success: sendProcessingResult,
					error: sendProcessingError
				});
			}
			else{
				//show error
				console.error("fastCalcDeliveryParams not found");
			}

		}

		else{
			//show error
			console.error("fastCalcDeliveryAjaxDir not found");
		}

	};

	var deliveryClose = function(event){

		//unbind
		$(document).off("change", ".delivery-modal #delivery-modal-calc-checkbox", deliveryCalcAll);
		$(document).off("keyup", ".delivery-modal .qtyBlock .qty", deliveryQKeyPress);
		$(document).off("click", ".delivery-modal .qtyBlock .minus", deliveryQMinus);
		$(document).off("click", ".delivery-modal .qtyBlock .plus", deliveryQPlus);
		$(document).off("click", ".delivery-modal .delivery-modal-exit", deliveryClose);

		//remove modal
		$deliveryModal.remove();
		$deliveryStyles.remove();

		//block action
		return event.preventDefault();

	}

	//binds
	$(document).on("change", ".delivery-modal #delivery-modal-calc-checkbox", deliveryCalcAll);
	$(document).on("keyup", ".delivery-modal .qtyBlock .qty", deliveryQKeyPress);
	$(document).on("click", ".delivery-modal .qtyBlock .minus", deliveryQMinus);
	$(document).on("click", ".delivery-modal .qtyBlock .plus", deliveryQPlus);

	//close
	$(document).on("click", ".delivery-modal .delivery-modal-exit", deliveryClose);

});