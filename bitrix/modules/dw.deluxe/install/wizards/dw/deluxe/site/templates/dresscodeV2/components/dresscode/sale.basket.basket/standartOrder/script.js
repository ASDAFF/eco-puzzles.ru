//global
var $main ="";
var flushTimeout = "";
var locTimeout = "";

var $elementError = $("#elementError");

var cartShowError = function(type){
	
	if(type == "max-quantity"){
		$elementError.find(".message").html(personalCartLANG["max-quantity"]);
	}

	return $elementError.show();

};

$(window).bind('load', function(){ 
	$main = $("#personalCart");
	$(window).width() < 1300 ? $("#looked").hide() : void 0;
});

$(window).resize(function(){
	$(window).width() < 1300  ? $("#looked").hide() : $("#looked").show();
});

$(document).on("click", "#allClear", function(e){ // clear
	$.get(ajaxDir + "/ajax.php?act=emp&SITE_ID=" + SITE_ID, function(data){
		data == 1 ? document.location.reload() : alert("error" + data);
	});
    e.preventDefault();
});

$(document).on("click", "#scrollToOrder", function(event){
	$("html, body").animate({
		scrollTop: $("#order").offset().top
	}, 250);
	event.preventDefault();
});

$(document).on("click", ".basketQty .minus", function(e){
	
	var $this = $(this);
	var $qtyBox = $this.siblings(".qty").removeClass("error");
	var $sum = $this.closest(".parent").find(".sum");
	var sumStr = $sum.html().replace(/\d\.\d/g, '').replace(/[0-9]/g, '');
	var qtyRatio = Number($qtyBox.data("ratio"));
	var sumCalc;

	if(Number($qtyBox.val()) - qtyRatio > 0){
		$qtyBox.val((Number($qtyBox.val()) * 10 - qtyRatio * 10) / 10);
		sumCalc = Number(Number($qtyBox.val()) * Number($sum.data("price"))).toFixed(0).replace("\.00", '');
		$sum.html(formatPrice(sumCalc) + sumStr);
		clearTimeout(flushTimeout); 
		flushTimeout = setTimeout(function(){
			updateCart($this.data("id"), parseInt($qtyBox.val()))
		}, 500);
	}

	flushParams();
	e.preventDefault();

});

$(document).on("click", ".basketQty .plus", function(e){
	
	var $this = $(this);
	var $qtyBox = $this.siblings(".qty").removeClass("error");
	var $sum = $this.closest(".parent").find(".sum");
	var sumStr = $sum.html().replace(/\d\.\d/g, '').replace(/[0-9]/g, '');
	var qtyRatio = Number($qtyBox.data("ratio"));
	var sumCalc;
	
	if(!parseFloat($qtyBox.val())){
		$qtyBox.val(0);
	}

	if($qtyBox.data("max-quantity")){
		if($qtyBox.data("max-quantity") < Number($qtyBox.val()) + qtyRatio){
			cartShowError("max-quantity");
			$qtyBox.addClass("error");
			return false;
		}
	}

	$qtyBox.val((Number($qtyBox.val()) * 10 + qtyRatio * 10) / 10);
	sumCalc = Number(Number($qtyBox.val()) * Number($sum.data("price"))).toFixed(0).replace("\.00", '');
	$sum.html(formatPrice(sumCalc) + sumStr);
	
	clearTimeout(flushTimeout);
	flushTimeout = setTimeout(function(){
		updateCart($this.data("id"), Number($qtyBox.val()))
	}, 500);
	
	flushParams();
	e.preventDefault();

});

$(document).on("click", ".delete", function(e){
	var $this = $(this);
	var $qtyBox = $this.closest(".parent").find(".qty");
	var $sum = $this.closest(".parent").find(".sum");
	if(!parseFloat($qtyBox.val())){
		$qtyBox.val(1);
	}
	if($this.data("id") !=""){
		$this.addClass("loading");
		$.get(ajaxDir + "/ajax.php?act=del&id=" + $this.data("id"), function(data){
			if(data == 1){
				$("#personalCart").find(".delete").length == 1 ? document.location.reload() : $this.closest(".parent").remove();
				flushParams();
				cartReload();
				reCalcDelivery(getProductPricesInfo());
			}
		});
	}else{
		alert("error; [data-id] not found!")
	}
	e.preventDefault();
});

$(function(){
	$(".qty").on("keyup", function(e){
		var $this = $(this);
		var $sum = $this.closest(".parent").find(".sum");
		var sumStr = $sum.html().replace(/\d\.\d/g, '').replace(/[0-9]/g, '');
		var qtyRatio = Number($this.data("ratio"));
		var value = "";
		var sumCalc;
		
		if($this.val().replace(/[^\d]/gi, '') != $this.val()){
			value = 1;
		}else if(Number($this.val()) > 0){
			value = Number($this.val()); 
		}else{
			value = qtyRatio;
		}

		value = Math.ceil(value / qtyRatio) * qtyRatio;

		if($this.data("max-quantity")){
			if($this.data("max-quantity") < value){
				cartShowError("max-quantity");
				$this.addClass("error");
				value = $this.data("max-quantity");
			}
		}

		if(value){
			$this.val(value);
			sumCalc = Number(value * Number($sum.data("price"))).toFixed(0).replace("\.00", '');
			$sum.html(formatPrice(sumCalc) + sumStr);
			clearTimeout(flushTimeout);
			flushTimeout = setTimeout(function(){
				updateCart($this.data("id"), $this.val());
			}, 500);
			flushParams();
		}
	});
});

var updateCart = function(id, q){
	if(q){
		$.get(ajaxDir + "/ajax.php?act=upd&id=" + id + "&q=" + q, function(data){
			data != true ?	console.error(data) : cartReload(reCalcDelivery(getProductPricesInfo()));
		});
	}
}

var flushParams = function(){
	var $mainQty     = $("#personalCart").find(".qty").removeClass("error");
	var $allSum      = $("#allSum, #allOrderSum");
	var $itemsCount  = $("#countItems, #countOrderItems");
	var $sum         = $main.find(".sum");
	var $allDevi     = $("#allDevilerySum");
	var $allordSum   = $("#allOrderSum");
	var allStr       = $allSum.html().replace(/\d\.\d/g, '').replace(/[0-9]/g, '');
	var orderSum     = 0;
	var totalQty     = 0;

	$sum.each(function(index){
		orderSum += parseFloat($(this).text().replace(/\s+/g, ''));
	});

	$mainQty.each(function(){
		totalQty += parseFloat($(this).val());
	});

	$allSum.html(formatPrice(orderSum) + allStr);
	$itemsCount.text(totalQty);
	$allordSum.html(
		formatPrice(
			orderSum + parseFloat(
				$allDevi.text().replace(" ", "")
			)
		) + allStr
	);
}

var getLocations = function(event){
	var $this = $(this);
	var items = [];
	clearTimeout(locTimeout);
	locTimeout = setTimeout(function(){
		if($this.val() !=""){
			$.getJSON(ajaxDir + "/ajax.php?act=location&q=" + encodeURI($this.val()), function(jData){
				if(!$.isEmptyObject(jData)){
					$(".locDesk").remove();
					$.each(jData, function(key, val) {
						items.push( "<li class='locTip' id='" + key + "' data-id='" + $this.attr("id") + "'>" + val + "</li>" );
					});
					$this.after(
						$( "<ul/>", { "class": "locDesk", html: items.join( "" ) } )
					);
				}else{
					$(".locDesk").remove();
				}
			});
		}else{
			$(".locDesk").remove();
		}
	}, 200);

};

var setLocation = function(event){
	
	var $this = $(this);
	
	$("#" + $this.data("id")).val($this.text()).data("location", $this.attr("id"));
	$(".locDesk").remove();
	
	reCalcDelivery(getProductPricesInfo());

};

var clearLocations = function(event){
	$this = $(this);
	$this.data("location", false);
	$this.val("");
};

var personTypeSelect = function(event){
	var $this = $(this);
	var $orderProps = $(".orderProps");
	$orderProps.toggle().filter(
		'[data-id="person_' + $this.find("option:selected").data("id") + '"]'
	).css("display", "table");
	return reCalcDelivery(getProductPricesInfo());
};

var deliChange = function(event){
	var $this        = $(this);
	var $selected    = $this.find(":selected");
	var $allDevi     = $("#allDevilerySum");
	var allStr       = $allDevi.html().replace(/[0-9]/g, '');

	$allDevi.html(
		formatPrice(
			$selected.data("price")
		) + allStr
	);

	$(".deliProps").hide().find("input, textarea").prop("disabled", true);
	$('[data-id="deli_' + $selected.val() +'"]').show().find("input, textarea").prop("disabled", false);

	getProductPricesInfo(flushParams());

};


var payChange = function(event){
	return reCalcDelivery(getProductPricesInfo());
};

var orderMake = function(event){
	var $this = $(this);
	var $activePerson = $("#personSelect").find("option:selected");
	var $activeform =  $("#orderForm_" + $activePerson.data("id"));
	var $cartContainer = $("#personalCart");
	var $orderMessage = $("#elementError");
	var $orderSuccess = $("#orderSuccess");
	var $firstError = "";
	var orderText = $this.text();
	var email = "";
	var deliveryLoc = "";
	var user_name = "";
	var user_mobile = "";
	var user_address = "";

	//check props
	$activeform.find('[data-requied="Y"]').removeClass("error").each(function(i){ 
		var $nextElement = $(this);
		if($.trim($nextElement.val()) == "" && $nextElement.prop("disabled") != true){

			if($firstError === ""){
				$firstError = $nextElement;
			}

			$nextElement.addClass("error");
		}

		if($nextElement.data("mail") == "Y"){
			email = $nextElement.val();
		}

		if($nextElement.data("location") != undefined){
			if($nextElement.data("location") != false){
				deliveryLoc = $nextElement.data("location");
			}else{

				if($firstError === ""){
					$firstError = $nextElement;
				}

				$nextElement.addClass("error");
			}
		}

	});

	$activeform.find("input, textarea").each(function(i){ 
		
		var $nextElement = $(this);
		
		if($nextElement.data("payer") == "Y"){
			user_name = $nextElement.val();
		}

		if($nextElement.data("mobile") == "Y"){
			user_mobile = $nextElement.val();
		}
		
		if($nextElement.is(":visible") && $nextElement.data("address") == "Y"){
			user_address = $nextElement.val();
		}

	});

	if($firstError === ""){
		$this.addClass("orderLoad").text(LANG["ORDER"]);
		$.getJSON(ajaxDir + "/ajax.php?act=orderMake&" + $activeform.serialize() + "&email=" + email + "&location=" + deliveryLoc + "&SITE_ID=" + SITE_ID + "&USER_NAME=" + user_name + "&PERSONAL_MOBILE=" + user_mobile + "&PERSONAL_ADDRESS=" + user_address, function(jsonData){
			if(jsonData["ERROR"] === undefined){

				$cartContainer.empty(); //
				
				if(jsonData["NEW_WINDOW"] == "Y"){
					openNewModalWindow(jsonData["PAYSYSTEM"], "toolbar=0,width=1000,height=1000");
					$cartContainer.html(jsonData["PAYSYSTEM"]);
				}else{
					$cartContainer.html(jsonData["PAYSYSTEM"]);
				}
				
				$orderSuccess.show().find("#orderID").text(jsonData["ORDER_ID"]);

				$("body, html").animate({
					scrollTop : $cartContainer.offset().top - 150
				}, 300);

			}else{
				$orderMessage.show().find(".message").html(jsonData["ERROR"]);
			}
			
			$this.removeClass("orderLoad").text(orderText);
	
		});

	}else{

		$("body, html").animate({
			scrollTop : ($firstError.offset().top - 100)
		}, 300);

	}

	event.preventDefault();
};

var openNewModalWindow = function(str, params){
	var pay_win = open("","", params);
		pay_win.document.open();
		pay_win.document.write(str);
};

var reCalcDelivery = function(){
	beforeCalcDelivery();	
};

var beforeCalcDelivery = function(){
	
	// get DOM elements values
	var $personSelect = $("#personSelect");
	var $personOptionSelected = $personSelect.find("option:selected");

	var gPersonActiveIndex = $personOptionSelected.index();

	var $paySelect = $(".paySelect").eq(gPersonActiveIndex);
	var $deliSelect = $(".deliSelect").eq(gPersonActiveIndex);
	var $location = $(".location").eq(gPersonActiveIndex);
	var $orderContainer = $(".orderContainer");

	var gPaySelectValue = $paySelect.val();
	var gDeliSelectValue = $deliSelect.val();
	var gLocationValue = $location.data("location");
	var gPersonSelectValue = $personSelect.val();

	if(parseInt(gPaySelectValue, 10) 
		&& parseInt(gLocationValue, 10) 
		&& parseInt(gDeliSelectValue, 10)
		&& parseInt(gPersonSelectValue, 10)
	){
		
		$orderContainer.addClass("wait");
		clearTimeout(getForCalcDeliveryTimeout);

		var getForCalcDeliveryTimeout = setTimeout(function(){
			getForCalcDelivery({
				act: "reCalcDelivery",
				SITE_ID: SITE_ID,
				DELISYSTEM_ID: gDeliSelectValue, 
				PAYSYSTEM_ID: gPaySelectValue,
				LOCATION_ID: gLocationValue,
				PERSON_TYPE: gPersonSelectValue
			}, gPersonActiveIndex); // always false
		}, 25);

	}else{
		console.error("CHECK PAYSYSTEM_ID or LOCATION_ID or PERSON_TYPE_ID");
		console.log({
			PAYSYSTEM_ID: gPaySelectValue,
			DELISYSTEM_ID: gDeliSelectValue,
			LOCATION_ID: gLocationValue,
			PERSON_TYPE: gPersonSelectValue
		});
	}

};

var getForCalcDelivery = function(gObj, activeIndex){

	$.getJSON(ajaxDir + "/ajax.php", gObj, function(jsonData){

		if(jsonData["ERROR"]){
			console.error(jsonData["ERROR"]);
			console.log(jsonData);
		}else{
			deliveryJsonResultHandle(jsonData, activeIndex); // 
		}
	})

	.fail(	
		function(jqxhr, textStatus, error){
			
			$.get(ajaxDir + "/ajax.php", gObj).done(function(http){
				console.log(http);
			});
			
			jsonError(jqxhr, textStatus, error);
		
		}
	)

	return false;

};

var deliveryJsonResultHandle = function(jsonData, activeIndex){

	var $orderContainer = $(".orderContainer");
	var $deliSelect = $(".deliSelect").eq(activeIndex);

	$orderContainer.removeClass("wait");
	$deliSelect.find("option").remove();

	$.each(jsonData, function(I, EL){
		$deliSelect.append(
			$("<option/>", {
				"data-price": EL.PRICE,
				"value": EL.ID
			}).html(
				EL.NAME + " " + EL.PRICE_FORMATED
			)
		)
	});

	$deliSelect.trigger("change");

};

var flushGift = function(componentResult){
	$("#giftContainer").html(componentResult);
};

var getProductPricesInfo = function(){

	$productTable = $("#basketProductList").addClass("wait");
	$personSelect = $("#personSelect");

	var $personOptionSelected = $personSelect.find("option:selected");
	var gPersonActiveIndex = $personOptionSelected.index();
	var $paySelect = $(".paySelect").eq(gPersonActiveIndex);
	var $deliSelect = $(".deliSelect").eq(gPersonActiveIndex);

	var gObj = {
		act: "getProductPrices",
		SITE_ID: SITE_ID,
		PERSON_TYPE_ID: $personSelect.val(),
		PAY_SYSTEM_ID: $paySelect.val(),
		DELIVERY_ID: $deliSelect.val()
	};

	if(typeof(giftParams) != "undefined"){
		gObj.GIFT_PARAMS = giftParams;
	}
	
	$.getJSON(ajaxDir + "/ajax.php", gObj, function(jsonData){
		$.each(jsonData["ITEMS"], function(i, nextElement){
			
			var $nextElement = $productTable.find('[data-cart-id="' + nextElement["ID"] + '"]');
			var priceResult = nextElement["PRICE_FORMATED"];

			if(nextElement["MEASURE_SYMBOL_RUS"] != undefined && nextElement["MEASURE_SYMBOL_RUS"] !=""){
				priceResult = priceResult + " <span class=\"measure\"> / " + nextElement["MEASURE_SYMBOL_RUS"] + "</span>";
			}

			if(nextElement["~DISCOUNT_PRICE"] > 0 || nextElement["~BASE_PRICE"] != nextElement["~PRICE"]){
				priceResult = priceResult + " <s class=\"discount\">" + nextElement["BASE_PRICE"] + "</s>";
			}

			var $nextElementPrice = $nextElement.find(".price");

			$nextElementPrice.html(priceResult);
			$nextElement.find(".sum").data("price", nextElement["~PRICE"]).html(nextElement["SUM"]);
			
			if($nextElementPrice.hasClass("getPricesWindow")){
				$nextElementPrice.prepend($("<span/>", {class: "priceIcon"}));
			}

			flushParams();

			//push gift component
			if(jsonData["GIFTS"] != ""){
				flushGift(jsonData["GIFTS"]);
			}

		});
	
		$productTable.removeClass("wait");

	});

};

jsonError = function(jqxhr, textStatus, error){
   	console.error( "JSON -> Request Failed: " + textStatus + ", " + error);
   	return false;
};


function messageClose(event){
	$("#elementError").hide();
	event.preventDefault();
};

var checkCoupon = function(event){
	
	$this = $(this);
	$field = $this.find(".couponField")
						.addClass("loading");

	$button = $this.find(".couponActivate")
						.addClass("loading");

	if($field.val() != ""){

		var gObj = {
			act: "coupon",
			value: $field.val()
		};

		$.get(ajaxDir + "/ajax.php", gObj, function(httpData){
			if(httpData == true){
				window.location.reload();
			}else{
				$button.removeClass("loading");
				$field.removeClass("loading")
								.addClass("error");
			}
		});

	}else{
		$button.removeClass("loading");
		$field.removeClass("loading")
						.addClass("error");
	}

	return event.preventDefault();

};

$(document).on("keyup", ".location", getLocations);
$(document).on("focus", ".location", clearLocations);
$(document).on("click", ".locTip" , setLocation);
$(document).on("change", ".personSelect", personTypeSelect);
$(document).on("change", ".deliSelect", deliChange);
$(document).on("change", ".paySelect", payChange);
$(document).on("click", "#orderMake", orderMake);
$(document).on("click", "#elementError .close, #elementErrorClose", messageClose);
$(document).on("submit", "#coupon", checkCoupon);
