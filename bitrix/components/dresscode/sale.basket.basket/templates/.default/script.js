
//global
var $main ="";
var flushTimeout = "";
var locTimeout = "";

$(window).bind('load', function(){ 
	$main = $("#personalCart").find(".productTable");
	$(window).width() < 1300 ? $("#looked").hide() : void 0;
});

$(window).resize(function(){
	$(window).width() < 1300  ? $("#looked").hide() : $("#looked").show();
});

$(document).on("click", "#allClear", function(e){ // clear
	$.get(ajaxDir + "/ajax.php?act=emp", function(data){
		data == 1 ? document.location.reload() : alert("error" + data);
	});
    e.preventDefault();
});

$(document).on("click", "#basketQty .minus", function(e){
	
	var $this = $(this);
	var $qtyBox = $this.siblings(".qty");
	var $sum = $this.closest("tr").find(".sum");
	var sumStr = $sum.html().replace(/\d\.\d/g, '').replace(/[0-9]/g, '');
	var sumCalc;

	if(parseInt($qtyBox.val()) - 1 > 0){
		$qtyBox.val(parseFloat($qtyBox.val()) -1);
		sumCalc = parseFloat(parseFloat($qtyBox.val()) * parseFloat($sum.data("price"))).toFixed(2).replace("\.00", '');
		$sum.html(formatPrice(sumCalc) + sumStr);
		clearTimeout(flushTimeout); 
		flushTimeout = setTimeout(function(){
			updateCart($this.data("id"), parseInt($qtyBox.val()))
		}, 500);
	}

	flushParams();
	e.preventDefault();

});

$(document).on("click", "#basketQty .plus", function(e){
	
	var $this = $(this);
	var $qtyBox = $this.siblings(".qty");
	var $sum = $this.closest("tr").find(".sum");
	var sumStr = $sum.html().replace(/\d\.\d/g, '').replace(/[0-9]/g, '');
	var sumCalc;
	
	if(!parseFloat($qtyBox.val())){
		$qtyBox.val(0);
	}
	
	$qtyBox.val(parseInt($qtyBox.val()) + 1);
	sumCalc = parseFloat(parseFloat($qtyBox.val()) * parseFloat($sum.data("price"))).toFixed(2).replace("\.00", '');
	$sum.html(formatPrice(sumCalc) + sumStr);
	
	clearTimeout(flushTimeout);
	flushTimeout = setTimeout(function(){
		updateCart($this.data("id"), parseFloat($qtyBox.val()))
	}, 500);
	
	flushParams();
	e.preventDefault();

});

$(document).on("click", ".delete", function(e){
	var $this = $(this);
	var $qtyBox = $this.closest("tr").find(".qty");
	var $sum = $this.closest("tr").find(".sum");
	if(!parseFloat($qtyBox.val())){
		$qtyBox.val(1);
	}
	if($this.data("id") !=""){
		$this.addClass("loaderBlue");
		$.get(ajaxDir + "/ajax.php?act=del&id=" + $this.data("id"), function(data){
			if(data == 1){
				$("#personalCart").find(".delete").length == 1 ? document.location.reload() : $this.closest("tr").remove();
				flushParams();
				cartReload();
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
		var $sum = $this.closest("tr").find(".sum");
		var sumStr = $sum.html().replace(/\d\.\d/g, '').replace(/[0-9]/g, '');
		var value = "";
		var sumCalc;
		
		if($this.val().replace(/[^\d]/gi, '') != $this.val()){
			value = 1;
		}else if(parseFloat($this.val()) > 0){
			value = parseFloat($this.val()); 
		}

		if(value){
			$this.val(value);
			sumCalc = parseFloat(value * parseFloat($sum.data("price"))).toFixed(2).replace("\.00", '');
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
			data == 0 ?	alert("error") : cartReload();
		});
	}
}

var flushParams = function(){
	var $mainQty     = $("#personalCart").find(".qty");
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
				$allDevi.text()
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
};

var personTypeSelect = function(event){
	var $this = $(this);
	var $orderProps = $(".orderProps");
	$orderProps.toggle().filter(
		'[data-id="person_' + $this.find("option:selected").data("id") + '"]'
	).css("display", "table");
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
	$("#deli_" + $selected.val()).show().find("input, textarea").prop("disabled", false);

	flushParams();
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

	//check props
	$activeform.find('[data-requied="Y"]').removeClass("error").each(function(i){
		var $nextElement = $(this);
		if($.trim($nextElement.val()) == "" && $nextElement.prop("disabled") !== true){

			if($firstError === ""){
				$firstError = $nextElement;
			}

			$nextElement.addClass("error");
		}

		if($nextElement.data("mail") === "Y"){
			email = $nextElement.val();
		}

		if($nextElement.data("location") !==""){
			deliveryLoc = $nextElement.data("location");
		}

	});

	if($firstError === ""){
		$this.addClass("orderLoad").text(LANG["ORDER"]);
		$.getJSON(ajaxDir + "/ajax.php?act=orderMake&" + $activeform.serialize() + "&email=" + email + "&location=" + deliveryLoc + "&SITE_ID=" + SITE_ID, function(jsonData){
			if(jsonData["ERROR"] === undefined){

				$cartContainer.empty(); //
				
				if(jsonData["NEW_WINDOW"] == "Y"){
					pay_win = open("","","toolbar=0,width=800,height=700");
					pay_win.document.open();
					pay_win.document.write(jsonData["PAYSYSTEM"]);
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


function messageClose(event){
	$("#elementError").hide();
	event.preventDefault();
};

$(document).on("keyup", ".location", getLocations);
$(document).on("click", ".locTip" , setLocation);
$(document).on("change", ".personSelect", personTypeSelect);
$(document).on("change", ".deliSelect", deliChange);
$(document).on("click", "#orderMake", orderMake);
$(document).on("click", "#elementError .close, #elementErrorClose", messageClose);
