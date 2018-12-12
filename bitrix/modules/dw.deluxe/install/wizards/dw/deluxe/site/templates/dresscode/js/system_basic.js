var appOpen = false;
var timeOutID;
var intervalID;
var flushTimeout;
var appBasketChangeTimeout;
var lastAddCartText;
var skuLoading = false;
var fastBuyOpen = false;
var fastViewOpen = false;
var fastViewStoresOpen = false;
var priceVariantOpen = false;
var requestPriceOpen = false;
var specialBlockMoved = false;
var basketProductsNow = false;
var oSkuDropdownOpened = false;

var changeAddCartButton = function(jsonData){

	//search addCart buttons
	if(typeof jsonData["CATEGORIES"] != "undefined"){
		if(typeof jsonData["CATEGORIES"]["READY"] != "undefined"){
			//each basket elements
			$.each(jsonData["CATEGORIES"]["READY"], function(index, nextElement){
				if(typeof nextElement["PRODUCT_ID"] != "undefined"){
					var $currentButton = $('.addCart[data-id="' + nextElement["PRODUCT_ID"] + '"]');
					if(typeof $currentButton != "undefined"){
						$currentButton.each(function(ii, nextButton){
							updateAddCartButton($(nextButton));
						});
					}
				}
			});
			//save current values
			basketProductsNow = jsonData;
		}
	}

};

var updateAddCartButton = function($currentElement){
	var $imageAfterLoad = $currentElement.find("img");
	$currentElement.text(LANG["ADDED_CART_SMALL"])
		.attr("href", SITE_DIR + "personal/cart/")
		.prepend($imageAfterLoad.attr("src", TEMPLATE_PATH + "/images/added.png"))
		.addClass("added");
};

var flushCart = function(id, q) {
	$.getJSON(ajaxPath + "?act=upd&id=" + id + "&q=" + q + "&site_id=" + SITE_ID, function(data) {
		if(data["success"] == "Y"){

			var $appBasket = $("#appBasket");
			var $price = $appBasket.find(".price").html(data["PRICE"]).data({"price": data["~PRICE"], "discount": data["OLD_PRICE"]});
			var $allSum = $appBasket.find(".allSum").html(data["SUM"]);

			if(data["MEASURE_SYMBOL_RUS"] != undefined && data["MEASURE_SYMBOL_RUS"] != ""){
				$price.append(
					$("<span/>").addClass("measure").html(
						" / " + data["MEASURE_SYMBOL_RUS"] + " "
					)
				);
			}

			if(parseInt(data["DISCOUNT_PRICE"], 10) > 0){
				$price.append(
					$("<s>")
						.addClass("discount")
							.html(data["DISCOUNT_PRICE"])
				);
			}

			if(parseInt(data["DISCOUNT_SUM"], 10) > 0){
				$allSum.append(
					$("<s>")
						.addClass("discount")
							.html(" " + data["DISCOUNT_SUM"])
				);
			}

			cartReload();

		}else{
			$basketQty = $("#appBasket .qty").addClass("error");
			if(data["error"] == "quantityError"){

				$basketQty.val(data["currentQuantityValue"]);

				//calc
				var $appBasket = $("#appBasket");
				var $price = $appBasket.find(".price");
				var $sum = $appBasket.find(".allSum");
				var gStrSum = $sum.html().replace(/\d\.\d/g, '').replace(/[0-9]/g, '');

				$sum.html(
					formatPrice(
						$price.data("price") * $basketQty.val()
					) + gStrSum
				);

				if($price.data("discount") > 0){

					var $sumDiscount = $sum.find(".discount");
					var gstrSumDiscount = $sumDiscount.html().replace(/\d\.\d/g, '').replace(/[0-9]/g, '');

					$sumDiscount.html(
						formatPrice(
							$price.data("discount") * $basketQty.val()
						) + gstrSumDiscount
					);
				}

			}
			console.error("flushCart: " + data["error"]);
		}

	});
}

var cartReload = function(){

	if(typeof(window.topCartTemplate) == "undefined"){
		window.topCartTemplate = "topCart";
	}

	if(typeof(window.wishListTemplate) == "undefined"){
		window.wishListTemplate = ".default";
	}

	if(typeof(window.compareTemplate) == "undefined"){
		window.compareTemplate = ".default";
	}

	$.get(ajaxPath + "?act=flushCart&topCartTemplate=" + window.topCartTemplate + "&wishListTemplate=" + window.wishListTemplate + "&compareTemplate=" + window.compareTemplate, function(data){

		var $items = $(data).find(".dl");

		$("#flushTopCart").html($items.eq(0).html());
		$("#flushFooterCart").html($items.eq(1).html());
		$("#flushTopwishlist").html($items.eq(2).html());
		$("#flushTopCompare").html($items.eq(3).html());

	});
}

$(function(){
	
	$(".questions-answers-list .question").click(function(){
		var par = $(this).parents(".question-answer-wrap");
		par.toggleClass("active").find(".answer").slideToggle();
	});

	$(".banner-animated").addClass("banner-image-load");

	if($("#footerTabs .tab").size() == 0){
		$("#footerTabs, #footerTabsCaption").remove();
	}else{
		$("#footerTabsCaption .item").eq(0).find("a").addClass("selected");
		$("#footerTabs .tab").eq(0).addClass("selected");
	}

	if($("#infoTabs .tab").size() == 0){
		$("#infoTabs, #infoTabsCaption").remove();
	}else{
		$("#infoTabsCaption .item").eq(0).find("a").addClass("selected");
		$("#infoTabs .tab").eq(0).addClass("selected");
	}

	var $upButton = $("#upButton");

	$(window).on("ready scroll", function(event){
		var curScrollValueY = (event.currentTarget.scrollY) ? event.currentTarget.scrollY : $(window).scrollTop()
		if(curScrollValueY > 0){
			$upButton.addClass("enb");
		}else{
			$upButton.removeClass("enb");
		}

	});

	$upButton.on("click", function(event){

		$("html,body").animate({
			scrollTop: 0
		}, 250);

		return event.preventDefault();

	});

});

$(window).on("ready", function(event){

	var $body = $("body").removeClass("loading"); // cache body

	if($("div").is(".global-block-container") && $("div").is(".global-information-block") && $("div").is(".global-information-block-cn")){

		var $globalBlockContainer = $body.find(".global-block-container");
		var $globalInformationBlock = $globalBlockContainer.find(".global-information-block");
		var $globalInformationBlockCntr = $globalInformationBlock.find(".global-information-block-cn");

		$globalBlockContainer.css("min-height", $globalInformationBlock.height());

		if(!$globalInformationBlock.hasClass("no-fixed")){
			var informBlockOffset = $globalInformationBlock.offset();
			var maxScrollHeight = $globalBlockContainer.height() + informBlockOffset.top - ($globalInformationBlockCntr.height() + 24); //24 padding top
		}

		var gbScrollCtr = function(event){

			var $this = $(this);
			var currentScrollValue = $this.scrollTop();

			if(currentScrollValue >= informBlockOffset.top){
				if(currentScrollValue >= maxScrollHeight){
					$globalInformationBlock.addClass("max-scroll");
				}else{
					$globalInformationBlock.removeClass("max-scroll");
				}
				$globalInformationBlock.addClass("fixed");
			}else{
				$globalInformationBlock.removeClass("fixed");
			}

		};

		var reCalcGbParams = function(){
			informBlockOffset = $globalInformationBlock.offset();
			maxScrollHeight = $globalBlockContainer.height() + informBlockOffset.top - ($globalInformationBlockCntr.height() + 24); //24 padding top
		}

		$(window).on("scroll", gbScrollCtr);
		$(window).on("resize", reCalcGbParams);

	}

	var moveBlockToContainer = function(blockID, moveBlockID){

		//set j vars
		var $blockID = $(blockID);
		var $moveBlockID = $(moveBlockID);

		//move
		$moveBlockID.append($blockID);

		//set global flag var
		return specialBlockMoved = true;

	};

	var setSpecialBlockPosition = function(){

		if($("div").is("#specialBlock")){
			if($(window).width() <= 1850){
				moveBlockToContainer("#specialBlock", "#specialBlockMoveContainer");
			}else if(specialBlockMoved === true && $(window).width() > 1600){
				moveBlockToContainer("#specialBlock", "#promoBlock");
			}
		}
	};

	//start form load
	setSpecialBlockPosition();

	//resize events
	$(window).on("resize", setSpecialBlockPosition);

	var getRequestPrice = function(event){

		var $this = $(this);
		var $requestPrice = $("#requestPrice");
		var $foundation = $("#foundation").addClass("blurred");

		$("#requestPrice, #requestPrice .requstProductContainer").show();
		$("#requestPriceResult").hide();

		//clear form
		$("#requestPriceForm").find('input[type="text"], textarea').val("");
		$requestPrice.find(".requestPricePicture").attr("src", $requestPrice.data("load"));

		var productID = $this.data("id");

		$this.addClass("loading");

		var gObj = {
			id: productID,
			act: "getRequestPrice"
		};

		$.getJSON(ajaxPath, gObj).done(function(jData){

			$this.removeClass("loading");
			$requestPrice.find(".requestPriceUrl").attr("href", jData[0]["DETAIL_PAGE_URL"]);
			$requestPrice.find(".productNameBlock .middle").html(jData[0]["NAME"]);
			$requestPrice.find("#requestPriceProductID").val(jData[0]["ID"]);
			$requestPrice.find(".markerContainer").remove();

			if(jData[0]["MARKER"] != undefined){

				$requestPrice.find("#fastBuyPicture").prepend(
					$("<div>").addClass("markerContainer")
						.append(
							jData[0]["MARKER"]
						)

				);
			}

			$requestPrice.show();

			loadingPictureControl(jData[0]["PICTURE"]["src"], function(){
				$requestPrice.find(".requestPricePicture").attr("src", jData[0]["PICTURE"]["src"]);
			});

			requestPriceOpen = true;

		}).fail(function(jqxhr, textStatus, error){

			$.get(ajaxPath, gObj).done(function(Data){
				console.log(Data)
			});

			$this.removeClass("loading")
						.addClass("error");

		    console.error(
		    	"Request Failed: " + textStatus + ", " + error
		    );

		});

		return event.preventDefault();
	};

	var sendRequestPrice = function(event){

		var $this = $(this).addClass("loading");
		var $requestPriceForm = $("#requestPriceForm");
		var $requestPriceFormTelephone = $requestPriceForm.find("#requestPriceFormTelephone").removeClass("error");

		if($requestPriceFormTelephone.val() == ""){
			$requestPriceFormTelephone.addClass("error");
		}

		var $personalInfo = $requestPriceForm.find("#personalInfoRequest");
		if(!$personalInfo.prop("checked")){
			$personalInfo.addClass("error");
		}

		if($requestPriceFormTelephone.val() !="" && $personalInfo.prop("checked")){

			$.getJSON(ajaxPath + "?" + $requestPriceForm.serialize()).done(function(jData){

				$("#requestPriceResultTitle").html(jData["heading"]);
				$("#requestPriceResultMessage").html(jData["message"]);

				$("#requestPrice .requstProductContainer").hide();
				$("#requestPriceResult").show();

				$this.removeClass("loading");

			}).fail(function(jqxhr, textStatus, error){

				$this.removeClass("loading").addClass("error");

			    console.error(
			    	"Request Failed: " + textStatus + ", " + error
			    );

			});

		}else{
			$this.removeClass("loading");
		}

		return event.preventDefault();
	};


	var closeRequestPrice = function(event){
		var $appFastBuy = $("#requestPrice").hide();
		var $foundation = $("#foundation").removeClass("blurred");
		requestPriceOpen = false;
		return event.preventDefault();
	};

	var getFastView = function(event){

		var $this = $(this).addClass("loading");
		var $productContainer = $this.parents(".item");
		var productID = $this.data("id");

		if(productID){
			$.ajax({
				url: ajaxPath + "?act=getFastView&product_id=" + productID + "&product_currency_id=" + $productContainer.data("currency-id") + "&product_convert_currency=" + $productContainer.data("convert-currency") + "&product_price_code=" + $productContainer.data("price-code") + "&product_hide_measures=" + $productContainer.data("hide-measure") + "&product_hide_not_available=" + $productContainer.data("hide-not-available"),
				success: function(http){

					//clear carousel cache vars
					delete fastViewInitPictureCarousel;
					delete fastViewInitPictureSlider;
					delete initFastViewApp;
					delete createFastView;

					//remove fastview window
					$("#appFastView").remove();

					//append to body
					$body.append(http);
					$this.removeClass("loading");

					//unbind last events
					$(document).off("click", "#appFastView .appFastViewExit");
					$(document).off("click", "#appFastView .appFastViewPictureCarouselItem");
					$(document).off("click", "#appFastView .appFastViewPictureCarouselLeftButton");
					$(document).off("click", "#appFastView .appFastViewPictureCarouselRightButton");
					$(document).off("mousemove", "#appFastView .appFastViewPictureSliderItemLink");
					$(document).off("mouseover", "#appFastView .appFastViewPictureSliderItemLink");
					$(document).off("mouseleave", "#appFastView .appFastViewPictureSliderItemLink");

					//start fastView scrips
					initFastViewApp();
					//reload addCart button
					cartReload();
					//subscribe button reload
					subscribeOnline();

				},
				cache: false,
				async: false
			});
			fastViewOpen = true;
		}

		return event.preventDefault();
	}

	var getStoresWindow = function(event){
	
		var $this = $(this).addClass("loading");
		var productID = $this.data("id");

		if(productID){
			$.get(ajaxPath + "?act=getAvailableWindow&product_id=" + productID, function(http){
				$("#fastViewStores").remove();
				$body.append(http);
				$this.removeClass("loading");
				fastViewStoresOpen = true;
			});
		}
	
		return event.preventDefault();

	};

	var closeStoresWindow = function(event){
		$("#fastViewStores").remove();
		fastViewStoresOpen = false;
		return event.preventDefault();
	};

	var getPricesWindow = function(event){

		var $this = $(this).addClass("loading");
		var $thisContainer = $this.parents(".item");
		var productID = $this.data("id");

		if(productID){
			$.get(ajaxPath + "?act=getPricesWindow&product_id=" + productID + "&product_price_code=" + encodeURIComponent($thisContainer.data("price-code")) + "&product_currency=" + encodeURIComponent($thisContainer.data("currency")), function(http){

				$("#appProductPriceVariant").remove();
				$this.removeClass("loading");
				$body.append(http);

				var thisOffsetLeft = $this.offset().left;
				var thisOffsetTop = $this.offset().top;

				if(thisOffsetLeft + 320 > $(window).width()){
					thisOffsetLeft = $(window).width() - 334;
				}

				if($this.data("fixed") == "Y"){
					$("#appProductPriceVariant").css({
						left: thisOffsetLeft,
						top: thisOffsetTop - $(window).scrollTop(),
						position: "fixed"
					});
				}else{
					$("#appProductPriceVariant").css({
						left: thisOffsetLeft,
						top: thisOffsetTop
					});
				}
				priceVariantOpen = true;
			});
		}

		return event.preventDefault();

	};

	var closePricesWindow = function(event){
		$("#appProductPriceVariant, .priceVariantStyles").remove();
		priceVariantOpen = false;
		return event.preventDefault();
	};

	var giftView = function($product, http){
		var $namer = $product.find(".name");
		var $nameMiddler = $namer.find(".middle");
		var $elPicture = $product.find(".picture");
		var $changeFastBack = $product.find(".fastBack").removeClass("disabled");

		if($nameMiddler){
			$namer.attr("href", http[0]["PRODUCT"]["DETAIL_PAGE_URL"]);
			$nameMiddler.html(http[0]["PRODUCT"]["NAME"]);
		}else{
			$namer.attr("href", http[0]["PRODUCT"]["DETAIL_PAGE_URL"]).html(http[0]["PRODUCT"]["NAME"]);
		}

		$elPicture.attr("href", http[0]["PRODUCT"]["DETAIL_PAGE_URL"]);
		$elPicture.html($("<img/>").attr("src", http[0]["PRODUCT"]["PICTURE"]));
		$elPicture.append($("<span />", {class: "getFastView"}).data("id", http[0]["PRODUCT"]["ID"]).html(LANG["FAST_VIEW_PRODUCT_LABEL"]));

		$product.find(".addCart, .fastBack, .addCompare").data("id", http[0]["PRODUCT"]["ID"]);

		if(http[0]["PRODUCT"]["PRICE"]["DISCOUNT_PRICE"]){
			$product.find(".price").html(LANG["GIFT_PRICE_LABEL"] + " ").removeClass("getPricesWindow");
			$product.find(".price").append(
				$("<s/>").addClass("discount").html(
					http[0]["PRODUCT"]["PRICE"]["DISCOUNT_PRICE"]
				)
			);
		}else{
			$product.find(".price").html(LANG["REQUEST_PRICE_LABEL"]).removeClass("getPricesWindow");
		}

		var $changeCart = $product.find(".addCart");

		$changeCart.find("img").remove();
		if(http[0]["PRODUCT"]["PRICE"]["DISCOUNT_PRICE"]){
			if($changeCart.hasClass("added")){
				$changeCart.removeClass("disabled").removeClass("requestPrice")
				.html($changeCart.data("cart-label"))
				.prepend($("<img />").attr({src: TEMPLATE_PATH + "/images/added.png", class: "icon"}));
			}else{
				$changeCart.removeClass("disabled").removeClass("requestPrice")
				.html($product.data("cart-label"))
				.prepend($("<img />").attr({src: TEMPLATE_PATH + "/images/addGift.png", class: "icon"}))
				.attr("href", "#");
			}
		}else{
			$changeFastBack.addClass("disabled");
			$changeCart.addClass("disabled").addClass("requestPrice")
				.html(LANG["REQUEST_PRICE_BUTTON_LABEL"])
				.prepend($("<img />").attr({src: TEMPLATE_PATH + "/images/request.png", class: "icon"}))
				.attr("href", "#");
			http[0]["PRODUCT"]["CAN_BUY"] = "N";
		}

		if(http[0]["PRODUCT"]["PRICE"]["DISCOUNT_PRICE"]){
			if($product.data("hide-measure") != "Y" && http[0]["PRODUCT"]["MEASURE"] != undefined && http[0]["PRODUCT"]["MEASURE"]["SYMBOL_RUS"] != ""){
				$product.find(".price").find(".discount").append(
					$("<span/>").addClass("measure").html(
						" / " + http[0]["PRODUCT"]["MEASURE"]["SYMBOL_RUS"] + " "
					)
				);
			}
		}

		var $changeAvailable = $product.find(".changeAvailable");

		$changeAvailable.removeClass("getStoresWindow");
		$changeAvailable.removeClass("outOfStock");
		$changeAvailable.removeClass("onOrder");
		$changeAvailable.removeClass("inStock");
		$changeAvailable.removeAttr("href");


		if(http[0]["PRODUCT"]["CATALOG_QUANTITY"] > 0){
			if(http[0]["PRODUCT"]["STORES_COUNT"] > 1){
				$changeAvailable.html($("<span/>").html(LANG["CATALOG_AVAILABLE"])).addClass("inStock").attr("href", "#").addClass("getStoresWindow").data("id", http[0]["PRODUCT"]["ID"]);
				$changeAvailable.prepend(
					$("<img/>").addClass("icon").attr("src", TEMPLATE_PATH + "/images/inStock.png")
				);
			}else{
				$changeAvailable.html(LANG["CATALOG_AVAILABLE"]).addClass("inStock");
				$changeAvailable.prepend(
					$("<img/>").addClass("icon").attr("src", TEMPLATE_PATH + "/images/inStock.png")
				);
			}
		}else{
			if(http[0]["PRODUCT"]["CAN_BUY"] != "Y"){
				$changeAvailable.html(LANG["CATALOG_NO_AVAILABLE"]).addClass("outOfStock");
				$changeFastBack.addClass("disabled");
				$changeCart.addClass("disabled");
				$changeAvailable.prepend(
					$("<img/>").addClass("icon").attr("src", TEMPLATE_PATH + "/images/outOfStock.png")
				);
			}else{
				$changeAvailable.html(LANG["CATALOG_ON_ORDER"]).addClass("onOrder");
				$changeAvailable.prepend(
					$("<img/>").addClass("icon").attr("src", TEMPLATE_PATH + "/images/onOrder.png")
				);
			}
		}
	};

	var fastViewSku = function($product, http){
		var $namer = $product.find(".appFastViewProductHeadingLink");
		var $elPicture = $product.find(".picture");
		var $changeFastBack = $product.find(".fastBack").removeClass("disabled");

		$namer.attr("href", http[0]["PRODUCT"]["DETAIL_PAGE_URL"]).html(http[0]["PRODUCT"]["NAME"]);

		$elPicture.attr("href", http[0]["PRODUCT"]["DETAIL_PAGE_URL"]);
		$elPicture.html($("<img/>").attr("src", http[0]["PRODUCT"]["PICTURE"]));

		$product.find(".addCart, .fastBack, .addCompare").data("id", http[0]["PRODUCT"]["ID"]).attr("data-id", http[0]["PRODUCT"]["ID"]);
		if(http[0]["PRODUCT"]["PRICE"]["DISCOUNT_PRICE"]){
			$product.find(".price").html($("<span />", {class: "priceVal"}).html(http[0]["PRODUCT"]["PRICE"]["DISCOUNT_PRICE"] + " ")).removeClass("getPricesWindow");
		}else{
			$product.find(".price").html($("<span />", {class: "priceVal"}).html(LANG["REQUEST_PRICE_LABEL"])).removeClass("getPricesWindow").removeAttr("href");
			http[0]["PRODUCT"]["CAN_BUY"] = "N";
		}
		if(http[0]["PRODUCT"]["RESULT_PROPERTIES"]){
			$product.find(".changeProperties").html(http[0]["PRODUCT"]["RESULT_PROPERTIES"]);
		}

		var $changeCart = $product.find(".addCart").removeClass("subscribe unSubscribe");

		$changeCart.find("img").remove();
		if(http[0]["PRODUCT"]["PRICE"]["DISCOUNT_PRICE"]){
			$changeCart.removeClass("added").removeClass("disabled").removeClass("requestPrice")
				.html(LANG["ADD_BASKET_DEFAULT_LABEL"])
				.prepend($("<img />").attr({src: TEMPLATE_PATH + "/images/incart.png", class: "icon"}))
				.attr("href", "#");
		}else{
			$changeFastBack.addClass("disabled");
			$changeCart.removeClass("added").addClass("disabled").addClass("requestPrice")
				.html(LANG["REQUEST_PRICE_BUTTON_LABEL"])
				.prepend($("<img />").attr({src: TEMPLATE_PATH + "/images/request.png", class: "icon"}))
				.attr("href", "#");
		}

		if(http[0]["PRODUCT"]["PRICE"]["DISCOUNT_PRICE"]){
			if($product.data("hide-measure") != "Y" && http[0]["PRODUCT"]["MEASURE"] != undefined && http[0]["PRODUCT"]["MEASURE"]["SYMBOL_RUS"] != ""){
				$product.find(".price").append(
					$("<span/>").addClass("measure").html(
						" / " + http[0]["PRODUCT"]["MEASURE"]["SYMBOL_RUS"] + " "
					)
				);
			}
		}

		if(http[0]["PRODUCT"]["PRICE"]["RESULT_PRICE"]["DISCOUNT"] > 0){
			$product.find(".price").append(
				$("<span/>").addClass("oldPriceLabel").html(CATALOG_LANG["FAST_VIEW_OLD_PRICE_LABEL"]).append(
					$("<s/>").addClass("discount").html(
						http[0]["PRODUCT"]["PRICE"]["RESULT_PRICE"]["BASE_PRICE"]
					)
				)
			);
		}

		if(http[0]["PRODUCT"]["COUNT_PRICES"] > 1){
			$product.find(".price").addClass("getPricesWindow").data("id", http[0]["PRODUCT"]["ID"]).prepend($("<span/>", {class: "priceIcon"})).attr("href", "#");
		}else{
			$product.find(".price").removeAttr("href").find(".priceIcon").remove();
		}

		var $changeAvailable = $product.find(".changeAvailable");

		$changeAvailable.removeClass("getStoresWindow");
		$changeAvailable.removeClass("outOfStock");
		$changeAvailable.removeClass("onOrder");
		$changeAvailable.removeClass("inStock");
		$changeAvailable.removeAttr("href");


		if(http[0]["PRODUCT"]["CATALOG_QUANTITY"] > 0){
			if(http[0]["PRODUCT"]["STORES_COUNT"] > 1){
				$changeAvailable.html($("<span/>").html(LANG["CATALOG_AVAILABLE"])).addClass("inStock").attr("href", "#").addClass("getStoresWindow").data("id", http[0]["PRODUCT"]["ID"]);
				$changeAvailable.prepend(
					$("<img/>").addClass("icon").attr("src", TEMPLATE_PATH + "/images/inStock.png")
				);
			}else{
				$changeAvailable.html(LANG["CATALOG_AVAILABLE"]).addClass("inStock");
				$changeAvailable.prepend(
					$("<img/>").addClass("icon").attr("src", TEMPLATE_PATH + "/images/inStock.png")
				);
			}
		}else{
			if(http[0]["PRODUCT"]["CAN_BUY"] != "Y"){
				$changeAvailable.html(LANG["CATALOG_NO_AVAILABLE"]).addClass("outOfStock");
				$changeFastBack.addClass("disabled");

				if(http[0]["PRODUCT"]["CATALOG_SUBSCRIBE"] == "Y" && http[0]["PRODUCT"]["PRICE"]["DISCOUNT_PRICE"]){
					$changeCart.html(LANG["ADD_SUBSCRIBE_LABEL"])
						.prepend($("<img />").attr({src: TEMPLATE_PATH + "/images/subscribe.png", class: "icon"}))
						.attr("href", "#").addClass("subscribe");
				}

				else{
					$changeCart.addClass("disabled");
				}

				$changeAvailable.prepend(
					$("<img/>").addClass("icon").attr("src", TEMPLATE_PATH + "/images/outOfStock.png")
				);
			}else{
				$changeAvailable.html(LANG["CATALOG_ON_ORDER"]).addClass("onOrder");
				$changeAvailable.prepend(
					$("<img/>").addClass("icon").attr("src", TEMPLATE_PATH + "/images/onOrder.png")
				);				
			}
		}

		//article

		if(typeof(http[0]["PRODUCT"]["PROPERTIES"]["CML2_ARTICLE"]) != "undefined"){
			if(typeof(http[0]["PRODUCT"]["PROPERTIES"]["CML2_ARTICLE"]["VALUE"]) != "undefined" && http[0]["PRODUCT"]["PROPERTIES"]["CML2_ARTICLE"]["VALUE"] !=""){
				$product.find(".changeArticle").html(http[0]["PRODUCT"]["PROPERTIES"]["CML2_ARTICLE"]["VALUE"]).parents(".article").removeClass("hidden");
			}else{
				if($product.find(".changeArticle").data("first-value")){
					$product.find(".changeArticle").html($product.find(".changeArticle").data("first-value"));
				}else{
					$product.find(".changeArticle").parents(".article").addClass("hidden");
				}
			}
		}

		//desc
		var $productDescription = $product.find(".appFastViewDescription");
		var $productDescriptionText = $productDescription.find(".appFastViewDescriptionText");
		if(http[0]["PRODUCT"]["PREVIEW_TEXT"]){
			$productDescription.addClass("visible");
			$productDescriptionText.html(http[0]["PRODUCT"]["PREVIEW_TEXT"]);
		}else{
			$productDescription.removeClass("visible");
		}


		//QTY BOX

		//get qty box ()
		var $qtyBox = $product.find(".catalogQtyBlock .catalogQty");
		$qtyBox.removeAttr("data-extended-price").removeData("extended-price");

		//write values
		$qtyBox.val(http[0]["PRODUCT"]["BASKET_STEP"]).data("max-quantity", http[0]["PRODUCT"]["CATALOG_QUANTITY"]).data("step", http[0]["PRODUCT"]["BASKET_STEP"]).removeClass("error");
		$changeCart.data("quantity", http[0]["PRODUCT"]["BASKET_STEP"]);

		if(typeof http[0]["PRODUCT"]["PRICE"]["EXTENDED_PRICES_JSON_DATA"] != "undefined"){
			if(http[0]["PRODUCT"]["PRICE"]["EXTENDED_PRICES_JSON_DATA"] != ""){
				$qtyBox.data("extended-price", http[0]["PRODUCT"]["PRICE"]["EXTENDED_PRICES_JSON_DATA"]);
			}
		}

		if(http[0]["PRODUCT"]["CATALOG_QUANTITY_TRACE"] == "Y" && http[0]["PRODUCT"]["CATALOG_CAN_BUY_ZERO"] == "N"){
			$qtyBox.data("enable-trace", "Y");
		}else{
			$qtyBox.data("enable-trace", "N");
		}

		if(http[0]["PRODUCT"]["IMAGES"]){
			
			var $appFastViewPictureSliderItems = $product.find(".appFastViewPictureSliderItems").css({left: 0});
			var $appFastViewPictureCarouselItems = $product.find(".appFastViewPictureCarouselItems").css({left: 0});
			
			$appFastViewPictureSliderItems.empty();
			$appFastViewPictureCarouselItems.empty();

			$.each(http[0]["PRODUCT"]["IMAGES"], function(i, nextElement){
				$appFastViewPictureSliderItems.append(
					$("<div />", {class: "appFastViewPictureSliderItem"}).append(
						$("<div />", {class: "appFastViewPictureSliderItemLayout"}).append(
							$("<a />", {class: "appFastViewPictureSliderItemLink", href: http[0]["PRODUCT"]["DETAIL_PAGE_URL"]}).data("loupe-picture", nextElement["SUPER_LARGE_PICTURE"]["src"]).append(
								$("<img />", {class: "appFastViewPictureSliderItemPicture", src: nextElement["LARGE_PICTURE"]["src"]})
							)
						)
					)
				);

				$appFastViewPictureCarouselItems.append(
					$("<div />", {class: "appFastViewPictureCarouselItem"}).append(
						$("<a />", {class: "appFastViewPictureCarouselItemLink", href: "#"}).append(
							$("<img />", {class: "appFastViewPictureCarouselItemPicture", src: nextElement["SMALL_PICTURE"]["src"]})
						)
					)
				);

			});

			// //addCart button reload
			// changeAddCartButton(basketProductsNow);
			// //subscribe button reload
			// subscribeOnline();

			//sliders
			fastViewInitPictureSlider();
			fastViewInitPictureCarousel();

		}
	};

	var selectSku = function(event){

		if(skuLoading == true){
			return false;
		}

		var _params = "";
		var _props = "";
		var _highload= "";
		var _product_width = 200;
		var _product_height = 180;

		var $_this = $(this);
		var $_mProductContainer = $_this.parents(".item");
		var $_mProduct = $_this.parents(".sku");
		var $_parentProp = $_this.parents(".skuProperty");
		var $_propList = $_mProduct.find(".skuProperty");
		var $_clickedProp = $_this.parents(".skuPropertyValue");

		var _level = $_parentProp.data("level");

		$_this.parents(".skuPropertyList").find("li").removeClass("selected");
		$_clickedProp.addClass("selected loading");

		skuLoading = true; //block

		// set product image paramets
		if($_mProduct.data("product-width") != undefined){
			_product_width = $_mProduct.data("product-width");
		}

		if($_mProduct.data("product-height") != undefined){
			_product_height = $_mProduct.data("product-height");
		}

		$_propList.each(function(i, prop){

			var $_nextProp  = $(prop);
			var $_nextPropList = $_nextProp.find("li");

			var propName = $_nextProp.data("name");
			var _used = false;

			if($_nextProp.data("highload") == "Y"){
				_highload = _highload + propName + ";"
			}

			$_nextPropList.each(function(io, obj){
				var $_currentObj = $(obj);
				_props = _props + propName + ":" + $_currentObj.data("value") + ";";
				if($_currentObj.hasClass("selected")){
					_params = _params + propName + ":" + $_currentObj.data("value") + ";";
					return _used = true;
				}
			});

			if(!_used){
				_params = _params + propName + ":-forse;";
			}

		});

		$.getJSON(ajaxPath + "?act=selectSku&props=" + encodeURIComponent(_props) + "&params=" + encodeURIComponent(_params) + "&level=" + _level + "&iblock_id=" + $_mProduct.data("iblock-id") + "&prop_id=" + $_mProduct.data("prop-id") + "&product_id=" + $_mProduct.data("product-id") + "&highload=" + encodeURIComponent(_highload) + "&product_width=" + _product_width + "&product_height=" + _product_height + "&product-change-prop=" + $_mProduct.data("change-prop") + "&product-more-pictures=" + $_mProduct.data("more-pictures") + "&price-code=" + encodeURIComponent($_mProductContainer.data("price-code")))
		  .done(function(http){
	  		$_propList.each(function(pI, pV){
	  			var $_sf = $(pV);
	  				$_sf.data("level") > _level && $_sf.find(".skuPropertyValue").removeClass("selected").addClass("disabled");
	  		});
			$.each(http[1]["PROPERTIES"], function(name, val){
			  	var $_gPropList = $_propList.filter(function(){ return ($(this).data("name") == name); });
			  	var $_gPropListValues = $_gPropList.find(".skuPropertyValue");
				$_gPropListValues.each(function(il, element){
					var $nextElement = $(element);
					$.each(val, function(pVal, _selected){
						if(pVal == $nextElement.data("value") && _selected != "D"){
							(_selected == "Y") ? $nextElement.addClass("selected").removeClass("disabled").trigger("click") : $nextElement.removeClass("disabled");
							return false;
						}
					});
				});
			});
			
			if($_mProduct.data("cast-func")){ 
				eval($_mProduct.data("castFunc"))($_mProduct,  http); // callback function for cast sku change.
			}else{

				var $namer = $_mProduct.find(".name");
				var $nameMiddler = $namer.find(".middle");
				var $elPicture = $_mProduct.find(".picture");
				var $changeFastBack = $_mProduct.find(".fastBack").removeClass("disabled");

				if($nameMiddler){
					$namer.attr("href", http[0]["PRODUCT"]["DETAIL_PAGE_URL"]);
					$nameMiddler.html(http[0]["PRODUCT"]["NAME"]);
				}else{
					$namer.attr("href", http[0]["PRODUCT"]["DETAIL_PAGE_URL"]).html(http[0]["PRODUCT"]["NAME"]);
				}

				$elPicture.attr("href", http[0]["PRODUCT"]["DETAIL_PAGE_URL"]);
				$elPicture.html($("<img/>").attr("src", http[0]["PRODUCT"]["PICTURE"]));
				$elPicture.append($("<span />", {class: "getFastView"}).data("id", http[0]["PRODUCT"]["ID"]).html(LANG["FAST_VIEW_PRODUCT_LABEL"]));

				$_mProduct.find(".addCart, .fastBack, .addCompare").data("id", http[0]["PRODUCT"]["ID"]).attr("data-id", http[0]["PRODUCT"]["ID"]);

				if(http[0]["PRODUCT"]["PRICE"]["DISCOUNT_PRICE"]){
					$_mProduct.find(".price").html(http[0]["PRODUCT"]["PRICE"]["DISCOUNT_PRICE"] + " ").removeClass("getPricesWindow");
				}else{
					$_mProduct.find(".price").html(LANG["REQUEST_PRICE_LABEL"]).removeClass("getPricesWindow");
				}

				var $changeCart = $_mProduct.find(".addCart").removeClass("subscribe unSubscribe");

				$changeCart.find("img").remove();
				if(http[0]["PRODUCT"]["PRICE"]["DISCOUNT_PRICE"]){
					$changeCart.removeClass("added").removeClass("disabled").removeClass("requestPrice")
						.html(LANG["ADD_BASKET_DEFAULT_LABEL"])
						.prepend($("<img />").attr({src: TEMPLATE_PATH + "/images/incart.png", class: "icon"}))
						.attr("href", "#");
				}else{
					$changeFastBack.addClass("disabled");
					$changeCart.removeClass("added").addClass("disabled").addClass("requestPrice")
						.html(LANG["REQUEST_PRICE_BUTTON_LABEL"])
						.prepend($("<img />").attr({src: TEMPLATE_PATH + "/images/request.png", class: "icon"}))
						.attr("href", "#");
					http[0]["PRODUCT"]["CAN_BUY"] = "N";
				}

				if(http[0]["PRODUCT"]["PRICE"]["DISCOUNT_PRICE"]){
					if($_mProduct.data("hide-measure") != "Y" && http[0]["PRODUCT"]["MEASURE"] != undefined && http[0]["PRODUCT"]["MEASURE"]["SYMBOL_RUS"] != ""){
						$_mProduct.find(".price").append(
							$("<span/>").addClass("measure").html(
								" / " + http[0]["PRODUCT"]["MEASURE"]["SYMBOL_RUS"] + " "
							)
						);
					}
				}

				if(http[0]["PRODUCT"]["PRICE"]["RESULT_PRICE"]["DISCOUNT"] > 0){
					$_mProduct.find(".price").append(
						$("<s/>").addClass("discount").html(
							http[0]["PRODUCT"]["PRICE"]["RESULT_PRICE"]["BASE_PRICE"]
						)
					);
				}

				if(http[0]["PRODUCT"]["COUNT_PRICES"] > 1){
					$_mProduct.find(".price").addClass("getPricesWindow").data("id", http[0]["PRODUCT"]["ID"]).prepend($("<span/>", {class: "priceIcon"})).attr("href", "#");
				}else{
					$_mProduct.find(".price").find(".priceIcon").remove();
				}
				
				var $changeAvailable = $_mProduct.find(".changeAvailable");

				$changeAvailable.removeClass("getStoresWindow");
				$changeAvailable.removeClass("outOfStock");
				$changeAvailable.removeClass("onOrder");
				$changeAvailable.removeClass("inStock");
				$changeAvailable.removeAttr("href");


				if(http[0]["PRODUCT"]["CATALOG_QUANTITY"] > 0){
					if(http[0]["PRODUCT"]["STORES_COUNT"] > 1){
						$changeAvailable.html($("<span/>").html(LANG["CATALOG_AVAILABLE"])).addClass("inStock").attr("href", "#").addClass("getStoresWindow").data("id", http[0]["PRODUCT"]["ID"]);
						$changeAvailable.prepend(
							$("<img/>").addClass("icon").attr("src", TEMPLATE_PATH + "/images/inStock.png")
						);
					}else{
						$changeAvailable.html(LANG["CATALOG_AVAILABLE"]).addClass("inStock");
						$changeAvailable.prepend(
							$("<img/>").addClass("icon").attr("src", TEMPLATE_PATH + "/images/inStock.png")
						);
					}
				}else{
					if(http[0]["PRODUCT"]["CAN_BUY"] != "Y"){

						$changeAvailable.html(LANG["CATALOG_NO_AVAILABLE"]).addClass("outOfStock");
						$changeFastBack.addClass("disabled");

						if(http[0]["PRODUCT"]["CATALOG_SUBSCRIBE"] == "Y" && http[0]["PRODUCT"]["PRICE"]["DISCOUNT_PRICE"]){
							$changeCart.html(LANG["ADD_SUBSCRIBE_LABEL"])
								.prepend($("<img />").attr({src: TEMPLATE_PATH + "/images/subscribe.png", class: "icon"}))
								.attr("href", "#").addClass("subscribe");
						}

						else{
							$changeCart.addClass("disabled");
						}

						$changeAvailable.prepend(
							$("<img/>").addClass("icon").attr("src", TEMPLATE_PATH + "/images/outOfStock.png")
						);

					}else{
						$changeAvailable.html(LANG["CATALOG_ON_ORDER"]).addClass("onOrder");
						$changeAvailable.prepend(
							$("<img/>").addClass("icon").attr("src", TEMPLATE_PATH + "/images/onOrder.png")
						);
					}
				}
			}

			//addCart button reload
			changeAddCartButton(basketProductsNow);
			//subscribe button reload
			subscribeOnline();

			$_clickedProp.removeClass("loading");
			skuLoading = false;

		  }).fail(function(jqxhr, textStatus, error){
		  	$_clickedProp.removeClass("loading");
		  	skuLoading = false;
		    alert("Request Failed: " + textStatus + ", " + error);
		});

		event.preventDefault();

	}

	var addSubscribe = function(event){
		
		//j vars
		$body = $("body");
		$this = $(this);

		//vars
		productId = $this.data("id");

		//check id
		if(productId != ""){

			//loader
			$this.addClass("loading");

			//get subscribe window
			$.getJSON(ajaxPath + "?act=addSubscribe&id=" + productId + "&site_id=" + SITE_ID, function(jsonData){

				if(jsonData["SUCCESS"] == "Y"){
					//show form
					if(jsonData["SUBSCRIBE_FORM"] != ""){
						$body.append(jsonData["SUBSCRIBE_FORM"]);
						$this.removeClass("loading");
					}
				}

				else{
					console.error(jsonData);
				}

			});

		}

		else{
			//show error
			console.error("product id not found");

		}

		//block action
		return event.preventDefault();

	};

	var unSubscribe = function(event){

		//j vars
		$this = $(this);
		$thisImage = $this.find("img");

		//vars
		subscribeId = $this.data("subscribe-id");

		//check id
		if(subscribeId != ""){

			//loader
			$this.addClass("loading");

			//get subscribe window
			$.getJSON(ajaxPath + "?act=unSubscribe&subscribeId=" + subscribeId + "&site_id=" + SITE_ID, function(jsonData){

				if(jsonData["SUCCESS"] == "Y"){
					$this.data("subscribe-id", "").text(LANG["ADD_SUBSCRIBE_LABEL"]).prepend($thisImage.attr({
						src: TEMPLATE_PATH + "/images/subscribe.png",
					})).removeClass("unSubscribe");
				}

				else{
					console.error(jsonData);
				}

			});

		}

		else{
			//show error
			console.error("product id not found");

		}
		return event.preventDefault();
	};

	var addCart = function(event){
		
		var $this = $(this);
		var productID = $this.data("id");
		var quantity = $this.data("quantity");
		var windowDisplay = $this.data("display-window");
		var refreshPage = $this.data("refresh-page");
		var addedLabel = $this.data("cart-label");

		var _arID = [];

		if(!$this.hasClass("disabled") && !$this.hasClass("subscribe")){

			if($this.attr("href") === "#"){
				if($this.hasClass("multi")){
					if($this.data("selector") != "" && $this.attr("href") === "#"){
						$this.addClass("loading").text(LANG["ADD_CART_LOADING"]);
						var $addElements = $($this.data("selector")).filter(":not(.disabled)");
						var elementsQuantity = "";
						if($addElements.length > 0){
							$addElements.each(function(x, elx){
								var $elx = $(elx);
								if($elx.data("id") != ""){
									_arID[x] = $elx.data("id");
									if(parseFloat($elx.data("quantity")) != ""){
										elementsQuantity += $elx.data("id") + ":" + parseFloat($elx.data("quantity")) + ";";
									}
								}
							});

							if(_arID != ""){
								$.getJSON(ajaxPath + "?act=addCart&id=" + _arID.join(";") + "&q="+ elementsQuantity +"&multi=1&site_id=" + SITE_ID, function(data) {
									var $imageAfterLoad = $this.find("img");
									$this.text(LANG["ADDED_CART_SMALL"])
										.attr("href", SITE_DIR + "personal/cart/")
										.prepend($imageAfterLoad.attr("src", TEMPLATE_PATH + "/images/added.png"))
										.removeClass("loading")
										.addClass("added");
									cartReload();
								});
							}else{
								alert("error (5)");
							}
						}else{
							alert("error(6)");
						}
						event.preventDefault();
					}
				}else{

					if(parseInt(productID, 10) > 0){

						$this.addClass("loading");

						var gObj = {
							act: "addCart",
							id: productID,
							site_id: SITE_ID
						};

						if(quantity > 0){
							gObj["q"] = quantity;
						}

						$.getJSON(ajaxPath, gObj).done(function(jData){

							var reloadCart = cartReload();

							//show display window
							if(typeof(windowDisplay) == "undefined" || typeof(windowDisplay) != "undefined" && windowDisplay == "Y"){
								var cartWindow = displayWindow(jData);
							}

							//change add cart label
							LANG["BASKET_ADDED"] = typeof(addedLabel) == "undefined" ? LANG["BASKET_ADDED"] : addedLabel;

							var $imageAfterLoad = $this.find("img");

							$(".bwOpened").removeClass("bwOpened");
							lastAddCartText = $this.html();

							$this.removeClass("loading")
								.addClass("added")
								.addClass("bwOpened")
								.html(LANG["BASKET_ADDED"])
								.prepend($imageAfterLoad.attr("src", TEMPLATE_PATH + "/images/added.png"))
								.attr("href", SITE_DIR + "personal/cart/");


							//reload page after add cart
							if(typeof(refreshPage) != "undefined" && refreshPage == "Y"){
								document.location.reload();
								window.scrollTo(0, 0); 
							}

						}).fail(function(jqxhr, textStatus, error){

							$.get(ajaxPath, gObj).done(function(Data){
								console.log(Data);
							});

							$this.removeClass("loading")
										.addClass("error");

						    console.error(
						    	"Request Failed: " + textStatus + ", " + error
						    );

						});

					}
				}
			}else{
				return true;
			}
		}

		return event.preventDefault();

	}

	var displayWindow = function(jData){
		if($(window).width() > 700){
			var $appBasket = $("#appBasket").data("id", jData["ID"]).show();
			var $container = $appBasket.find(".container");
			var $foundation = $("#foundation").addClass("blurred");
			var $changeAvailable = $appBasket.find(".availability");
			var $moreLink = $appBasket.find(".moreLink").attr("href", jData["DETAIL_PAGE_URL"]);
			var $image = $appBasket.find(".image").attr("src", $appBasket.data("load"));
			var $wishlist = $appBasket.find(".addWishlist").data("id", jData["ID"]);
			var $compare = $appBasket.find(".addCompare").data("id", jData["ID"]);
			var $picture = $appBasket.find(".picture");
			var $delete = $appBasket.find(".delete").data("id", jData["CART_ID"]);
			var $price = $appBasket.find(".price").html(jData["PRICE"]).data({"price": jData["~PRICE"], "discount": jData["OLD_PRICE"]});
			var $allSum = $appBasket.find(".allSum").html(jData["SUM"]);
			var $name = $appBasket.find(".name").text(jData["NAME"])
			var $qty = $appBasket.find(".qty").val(jData["QUANTITY"]).data("id", jData["ID"]).data("ratio", jData["ADDBASKET_QUANTITY_RATIO"]).removeClass("error");
			var $minus = $appBasket.find(".minus").data("id", jData["ID"]);
			var $plus = $appBasket.find(".plus").data("id", jData["ID"]);

			$changeAvailable.removeClass("outOfStock");
			$changeAvailable.removeClass("onOrder");
			$changeAvailable.removeClass("inStock");


			if(jData["CATALOG_QUANTITY"] > 0){
				$changeAvailable.addClass("inStock");
			}else{
				if(jData["CAN_BUY"] != true){
					$changeAvailable.addClass("outOfStock");
				}else{
					$changeAvailable.addClass("onOrder");		
				}
			}

			if(jData["MEASURE_SYMBOL_RUS"] != undefined && jData["MEASURE_SYMBOL_RUS"] != ""){
				$price.append(
					$("<span/>").addClass("measure").html(
						" / " + jData["MEASURE_SYMBOL_RUS"] + " "
					)
				);
			}

			if(parseInt(jData["DISCOUNT_PRICE"], 10) > 0){
				$price.append(
					$("<s>")
						.addClass("discount")
							.html(jData["DISCOUNT_PRICE"])
				);
			}

			if(parseInt(jData["DISCOUNT_SUM"], 10) > 0){
				$allSum.append(
					$("<s>")
						.addClass("discount")
							.html(jData["DISCOUNT_SUM"])
				);
			}

			if(jData["RATING"] != undefined){
				
				$container.prepend(
					$("<div>").addClass("rating")
						.append(
							$("<i>")
								.addClass("m")
									.css("width", (jData["RATING"] * 100 / 5) + "%")
						)
							.append(
								$("<i>")
									.addClass("h")
							)

				);
			}

			$picture.find(".markerContainer")
										.remove();

			if(jData["MARKER"] != undefined){
				
				$picture.prepend(
					$("<div>").addClass("markerContainer")
						.append(
							jData["MARKER"]
						)

				);
			}

			loadingPictureControl(jData["DETAIL_PICTURE"], function(){
				$image.attr("src", jData["DETAIL_PICTURE"]);
			});

			appOpen = true;  //global flag
		}

	};

	var appBasketDelete = function(event){

		var $this = $(this)
						.addClass("loading");

		var gObj = {
			id: $this.data("id"),
			act: "del"
		};

		$.get(ajaxPath, gObj).done(function(hData){

			if(hData != ""){
				var $savedItems = $(".bwOpened").removeClass("added").attr("href", "#");
				$("#appBasket .closeWindow").trigger("click");
				$this.removeClass("loading");
				$savedItems.html(lastAddCartText);
				cartReload();
			}else{
				$this.removeClass("loading")
							.addClass("error");
			}

		}).fail(function(jqxhr, textStatus, error){
			
			$this.removeClass("loading")
						.addClass("error");
		   
		    console.error(
		    	"Request Failed: " + textStatus + ", " + error
		    );

		});

		return event.preventDefault();
	};

	var appBasketClose = function(event){

		var $appBasket = $("#appBasket").hide();
		var $foundation = $("#foundation").removeClass("blurred");
		
		appOpen = false;  //global flag
		
		return event.preventDefault();
	};

	var appBasketMinus = function(event){
		var $this = $(event.currentTarget);
		var $qty = $this.siblings(".qty").removeClass("error");
		var gQuantity = Number($qty.val());
		var qtyRatio = Number($qty.data("ratio"));

		if(gQuantity > qtyRatio){
			$qty.val((gQuantity * 10 - qtyRatio * 10 ) / 10);
		}
		
		appBasketCalc($qty, $this.data("id"));
		return event.preventDefault();
	};

	var appBasketPlus = function(event){

		var $this = $(event.currentTarget);
		var $qty = $this.siblings(".qty").removeClass("error");
		var gQuantity = Number($qty.val());
		var qtyRatio = Number($qty.data("ratio"));

		var finishValue = ((gQuantity * 10 + qtyRatio * 10) / 10);
		finishValue = finishValue > 9999 ? 9999 : finishValue;

		$qty.val(finishValue);
		appBasketCalc($qty, $this.data("id"));

		return event.preventDefault();
	};

	var appBasketChange = function(event){
		
		var $this = $(this);
		var gValue = $this.val();
		var qtyRatio = Number($this.data("ratio"));
		var wValue;

		if(gValue.replace(/[^\d]/gi, '') != gValue){
			wValue = qtyRatio;
		}else if(Number(gValue) > qtyRatio){
			wValue = Number(gValue); 
		}else{
			wValue = qtyRatio;
		}

		wValue = Math.ceil(wValue / qtyRatio) * qtyRatio;
		wValue = wValue > 9999 ? 9999 : wValue;

		var tmpWValue = $this.val(wValue);
		var tmpWId = $this.data("id");
		
		clearTimeout(appBasketChangeTimeout);
		appBasketChangeTimeout = setTimeout(function() {
			appBasketCalc(tmpWValue, tmpWId);
		}, 600);

		return event.preventDefault();
	};

	var appBasketCalc = function($qty, productID){

		var $appBasket = $("#appBasket");
		var $price = $appBasket.find(".price");
		var $sum = $appBasket.find(".allSum");
		var gStrSum = $sum.html().replace(/\d\.\d/g, '').replace(/[0-9]/g, '');
		$qty.removeClass("error");

		var tmpPriceSum = $price.data("price") * $qty.val();

		$sum.html(
			formatPrice(
				tmpPriceSum.toFixed(0)
			) + gStrSum
		);
	
		if($price.data("discount") > 0){

			var $sumDiscount = $sum.find(".discount");
			var gstrSumDiscount = $sumDiscount.html().replace(/\d\.\d/g, '').replace(/[0-9]/g, '');
			var tmpDiscPrice = $price.data("discount") * $qty.val();

			$sumDiscount.html(
				formatPrice(
					tmpDiscPrice.toFixed(0)
				) + gstrSumDiscount
			);
		}

		clearTimeout(flushTimeout);
		flushTimeout = setTimeout(function(){
			flushCart(productID, $qty.val())
		}, 500);

	};

	var appBasketPlusHold = function(event){
		intervalID = setInterval(function() {
	        appBasketPlus(event);
	    }, 150);
	};	

	var appBasketPlusHoldUp = function(){
		clearInterval(intervalID);
	};

	var appBasketMinusHold = function(event){
		intervalID = setInterval(function() {
	        appBasketMinus(event);
	    }, 150);
	};	

	var appBasketMinusHoldUp = function(){
		clearInterval(intervalID);
	};

	var addCompare = function(event){

		var $this = $(event.currentTarget);
		var $icon = $this.find("img");
		var productID = $this.data("id");

		if($this.attr("href") == "#"){
			if(parseInt(productID, 10) > 0 && !$this.hasClass("added")){
				
				$this.addClass("loading");

				var gObj = {
					id: productID,
					act: "addCompare"
				};

				$.get(ajaxPath, gObj).done(function(hData){
					if(hData != ""){
						var reloadCart = cartReload();
						if($this.data("no-label") == "Y"){
							$this.removeClass("loading")
										.addClass("added")
											.attr("href", SITE_DIR + "compare/");
						}else{
							$this.removeClass("loading")
										.addClass("added")
											.html(LANG["ADD_COMPARE_ADDED"])
												.prepend($icon)
													.attr("href", SITE_DIR + "compare/");
						}
					}else{
						$this.removeClass("loading")
								.addClass("error");
					}
				}).fail(function(jqxhr, textStatus, error){
					
					$this.removeClass("loading")
								.addClass("error");
				   
				    console.error(
				    	"Request Failed: " + textStatus + ", " + error
				    );

				});
			}

			return event.preventDefault();
		}
	};

	var addWishlist = function(event){
		
		var $this = $(event.currentTarget);
		var $icon = $this.find("img");
		var productID = $this.data("id");

		if($this.attr("href") == "#"){
			if(parseInt(productID, 10) > 0 && !$this.hasClass("added")){
				
				$this.addClass("loading");

				var gObj = {
					id: productID,
					act: "addWishlist"
				};

				$.get(ajaxPath, gObj).done(function(hData){
					if(hData != ""){
						var reloadCart = cartReload();
						if($this.data("no-label") == "Y"){
							$this.removeClass("loading")
										.addClass("added")
											.attr("href", SITE_DIR + "wishlist/");
						}else{
							$this.removeClass("loading")
										.addClass("added")
											.html(LANG["WISHLIST_ADDED"])
												.prepend($icon)
													.attr("href", SITE_DIR + "wishlist/");
						}
					}else{
						$this.removeClass("loading")
									.addClass("error");
					}
				}).fail(function(jqxhr, textStatus, error){
					
					$this.removeClass("loading")
								.addClass("error");
				   
				    console.error(
				    	"Request Failed: " + textStatus + ", " + error
				    );

				});
			}

			return event.preventDefault();
		}
	};

	var openFastBack = function(event){

		var $this = $(this);

		if(!$this.hasClass("disabled")){

			var $appFastBuy = $("#appFastBuy");
			var $foundation = $("#foundation").addClass("blurred");

			$("#fastBuyOpenContainer").show();
			$("#fastBuyResult").hide();

			$("#fastBuyForm").find('input[type="text"], textarea').val("");

			var productID = $this.data("id");
			
			$this.addClass("loading");

			var gObj = {
				id: productID,
				act: "getFastBuy"
			};

			$.getJSON(ajaxPath, gObj).done(function(jData){
				
				$this.removeClass("loading");
				$appFastBuy.find("#fastBuyPicture .url, #fastBuyName .url").attr("href", jData[0]["DETAIL_PAGE_URL"]);
				$appFastBuy.find("#fastBuyPicture .picture").attr("src", $appFastBuy.data("load"));
				$appFastBuy.find("#fastBuyPrice").html(jData[0]["PRICE"]["PRICE_FORMATED"]);
				$appFastBuy.find("#fastBuyName .middle").html(jData[0]["NAME"]);	
				$appFastBuy.find("#fastBuyFormId").val(jData[0]["ID"]);
				$appFastBuy.find(".markerContainer").remove();

				if(jData[0]["MARKER"] != undefined){
					
					$appFastBuy.find("#fastBuyPicture").prepend(
						$("<div>").addClass("markerContainer")
							.append(
								jData[0]["MARKER"]
							)

					);
				}

				$appFastBuy.show();	

				loadingPictureControl(jData[0]["PICTURE"]["src"], function(){
					$appFastBuy.find("#fastBuyPicture .picture").attr("src", jData[0]["PICTURE"]["src"]);
				});

			}).fail(function(jqxhr, textStatus, error){
				
				$.get(ajaxPath, gObj).done(function(Data){
					console.log(Data)
				});

				$this.removeClass("loading")
							.addClass("error");
			   
			    console.error(
			    	"Request Failed: " + textStatus + ", " + error
			    );

			});

			fastBuyOpen = true;
		}

		return event.preventDefault();
	};

	var sendFastBack = function(event){
		
		var $this = $(this).addClass("loading");
		var $fastBuyForm = $("#fastBuyForm");
		var $fastBuyFormName = $fastBuyForm.find("#fastBuyFormName").removeClass("error");
		var $fastBuyFormTelephone = $fastBuyForm.find("#fastBuyFormTelephone").removeClass("error");

		if($fastBuyFormName.val() == ""){
			$fastBuyFormName.addClass("error");
		}

		if($fastBuyFormTelephone.val() == ""){
			$fastBuyFormTelephone.addClass("error");
		}

		var $personalInfo = $fastBuyForm.find("#personalInfoFastBuy");
		if(!$personalInfo.prop("checked")){
			$personalInfo.addClass("error");
		}

		if($fastBuyFormName.val() != "" && $fastBuyFormTelephone.val() !="" && $personalInfo.prop("checked")){

			$.getJSON(ajaxPath + "?" + $fastBuyForm.serialize()).done(function(jData){
				
				$("#fastBuyResultTitle").html(jData["heading"]);
				$("#fastBuyResultMessage").html(jData["message"]);

				$("#fastBuyOpenContainer").hide();
				$("#fastBuyResult").show();
				
				$this.removeClass("loading");

			}).fail(function(jqxhr, textStatus, error){
				
				$this.removeClass("loading").addClass("error");
			   
			    console.error(
			    	"Request Failed: " + textStatus + ", " + error
			    );

			});

		}else{
			$this.removeClass("loading");
		}

		return event.preventDefault();
	};

	var closeFastBack = function(event){
		var $appFastBuy = $("#appFastBuy").hide();
		var $foundation = $("#foundation").removeClass("blurred");
		return event.preventDefault();
	};

	var removeFromWishlist = function(event){
		
		var $this = $(this);
		var $wishlist = $("#wishlist");
		var $parentThis = $(this).parents(".item");
		var productID = $this.data("id");
				$this.addClass("loading");

		var gObj = {
			id: productID,
			act: "removeWishlist"
		};

		$.get(ajaxPath, gObj).done(function(hData){
			if(hData != ""){
				if($wishlist.find(".product, .itemRow").length == 1){
					window.location.reload();
				}else{
					reloadCart = cartReload();
					$parentThis.remove();
				}
			}else{
				$this.removeClass("loading")
							.addClass("error");
			}
		}).fail(function(jqxhr, textStatus, error){
			
			$this.removeClass("loading")
						.addClass("error");
		   
		    console.error(
		    	"Request Failed: " + textStatus + ", " + error
		    );

		});

		return event.preventDefault();
	
	};

    var slideCollapsedBlock = function(event){
    	var $collapsed =  $("#left").children(".collapsed");
		if(!$collapsed.is(":visible") || $collapsed.hasClass("toggled")){
	    	$collapsed.stop().slideToggle().addClass("toggled");
	    	return event.preventDefault();
	    }
    };

    var openSmartFiler = function(event){
    	$smartFilterForm = $("#smartFilterForm");
    	if($smartFilterForm.is(":visible")){
    		$smartFilterForm.stop().slideUp("fast");
    	}else{
    		$smartFilterForm.stop().slideDown("fast");
    	}
    };

    var openSmartSections = function(event){
    	$smartSections = $("#nextSection ul");
    	if($smartSections.is(":visible")){
    		$smartSections.stop().slideUp("fast");
    	}else{
    		$smartSections.stop().slideDown("fast");
    	}
    };

	var formatPrice = function(data) {
		var price = String(data).split('.');
		var strLen = price[0].length;
		var str = "";

		for (var i = strLen; i > 0; i--) {
			str = str + ((!(i % 3) ? " " : "") + price[0][strLen - i]);
		}

		return str + (price[1] != undefined ? "." + price[1] : "");
	}

    var loadingPictureControl = function(imagePath, callBack){
    
        if(imagePath){
            var newImage = new Image();
            $(newImage).one("load", callBack);
            newImage.src = imagePath;
        }
   
    };

	//extented prices
	var catalogReCalcPrice = function($qtyBox, currentQuantity){
		if(currentQuantity > 0){

			//price
			var $priceContainer = $qtyBox.parents(".item").find(".price");
			var $priceValContainer = $priceContainer.find(".priceVal");

			//check for empty
			if($priceValContainer.length > 0){
				var $priceContainerStr = $priceValContainer.html().replace(/\d\.\d/g, '').replace(/[0-9]/g, '');
			}

			//discount
			var $discountContainer = $priceContainer.find(".discount");
			if($discountContainer.length > 0){
				var $discountContainerStr = $discountContainer.html().replace(/\d\.\d/g, '').replace(/[0-9]/g, '');
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
								$priceValContainer.html(formatPrice(Number(nextValue["DISCOUNT_PRICE"]).toFixed(0)) + $priceContainerStr);
							}

							//write discount
							if(typeof nextValue["OLD_PRICE"] != "undefined"){
								$discountContainer.html(formatPrice(Number(nextValue["OLD_PRICE"]).toFixed(0)) + $discountContainerStr);
							}	

						}
					}
				});
			}

		}
		return;
	};

	var catalogAddCartPlus = function(event){

		var $this = $(this);
		var $qtyBox = $this.siblings(".catalogQtyBlock .catalogQty");
		var $addCartBtn = $this.parent().siblings(".addCart");

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
		catalogReCalcPrice($qtyBox, xQtyExpression);

		//set or remove error
		__qtyError === true ? $qtyBox.addClass("error") : $qtyBox.removeClass("error");

		return event.preventDefault();

	};

	var catalogAddCartMinus = function(event){

		var $this = $(this);
		var $qtyBox = $this.siblings(".catalogQtyBlock .catalogQty");
		var $addCartBtn = $this.parent().siblings(".addCart");

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
		catalogReCalcPrice($qtyBox, xQtyExpression);

		//set or remove error
		__qtyError === true ? $qtyBox.addClass("error") : $qtyBox.removeClass("error");

		return event.preventDefault();

	};

	var catalogAddCartChange = function(event){

		var $this = $(this);
		var $addCartBtn = $this.parent().siblings(".addCart");

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
		catalogReCalcPrice($qtyBox, xQtyExpression);

		//set or remove error
		__qtyError === true ? $this.addClass("error") : $this.removeClass("error");

	};

	var closeElementsAfterClick = function(event){

		if(appOpen === true){
			appBasketClose(event);
		}

		if(fastBuyOpen === true){
			$("#appFastBuy").hide();
			$("#foundation").removeClass("blurred");
			fastBuyOpen = false;
		}

		if(fastViewOpen === true){
			$("#appFastView").remove();
			fastViewOpen = false;
		}

		if(fastViewStoresOpen === true){
			$("#fastViewStores").remove();
			fastViewStoresOpen = false;
		}

		if(priceVariantOpen === true){
			$("#appProductPriceVariant").remove();
			priceVariantOpen = false;
		}

		if(requestPriceOpen === true){
			$("#foundation").removeClass("blurred");
			$("#requestPrice").hide();
			requestPriceOpen = false;
		}

	};

	$(document).on("click", "#footerTabsCaption .item", function(event){
		$(this).find("a").addClass("selected");
		$(this).siblings(".item").find("a").removeClass("selected");
		$("#footerTabs").find(".tab").hide().eq($(this).index()).show();
		event.stopImmediatePropagation();
		return event.preventDefault();
	});

	$(document).on("click", "#infoTabsCaption .item", function(event){
		$(this).find("a").addClass("selected");
		$(this).siblings(".item").find("a").removeClass("selected");
		$("#infoTabs").find(".tab").hide().eq($(this).index()).show();
		return event.preventDefault();
	});

	//check checkbox by class name on label

	$(".label-class").on("click", function(){

		var $this = $(this);
		var $cTarget = $this.attr("for");
		var $parentForm = $this.parents("form");
		var $cCheckBox = $parentForm.find("." + $cTarget);

		if($cCheckBox.prop("checked")){
			$cCheckBox.prop("checked", false).focus();
		}

		else{
			$cCheckBox.prop("checked", "checked").focus();
		}

		return event.preventDefault();

	});


	var openSkuDropDown = function(event){

		//vars
		var $this = $(this);
		var $dropList = $this.siblings(".oSkuDropdownList");

		//show list
		$dropList.toggleClass("opened");

		//opened flag
		oSkuDropdownOpened = $dropList.hasClass("opened");

		return event.preventDefault();

	};

	var selectSkuDropDownValue = function(event){

		//vars
		var $this = $(this);
		var $dropList = $this.parents(".oSkuDropdownList");
		var $dropListItems = $dropList.find(".oSkuDropdownListItem").removeClass("selected");
		var $checkedItem = $dropList.siblings(".oSkuCheckedItem");

		if(!$checkedItem.hasClass("noHideChecked")){

			//hide list
			$dropList.removeClass("opened");

			//opened flag
			oSkuDropdownOpened = false;

		}

		//active
		$this.addClass("selected");

		//write value
		$checkedItem.html($this.text());

		//
		return event.preventDefault();

	};

	var closeSkuDropDown = function(event){

		//if opened
		if(oSkuDropdownOpened){
			//block trigger events
			if(typeof event.isTrigger == "undefined"){
				//close
				$(".oSkuDropdownList").removeClass("opened");

				//opened flag
				oSkuDropdownOpened = false;
			}
		}

	};

	//skuDropDown
	$(document).on("click", ".oSkuDropDownProperty .oSkuCheckedItem", openSkuDropDown);
	$(document).on("click", ".oSkuDropDownProperty .oSkuDropdownListItem", selectSkuDropDownValue);
	$(document).on("click", ".oSkuDropdown", function(event){event.stopImmediatePropagation()});
	$(document).on("click", closeSkuDropDown);

	$(document).on("click", ".catalogQtyBlock .catalogPlus", catalogAddCartPlus);
	$(document).on("click", ".catalogQtyBlock .catalogMinus", catalogAddCartMinus);
	$(document).on("change", ".catalogQtyBlock .catalogQty", catalogAddCartChange);

    $(document).on("click", "#appBasket .closeWindow", appBasketClose);
    $(document).on("click", "#appBasket .delete", appBasketDelete);
    $(document).on("click", "#appBasket .minus", appBasketMinus);
    $(document).on("click", "#appBasket .plus", appBasketPlus);
    $(document).on("keyup", "#appBasket .qty", appBasketChange);
	
	$(document).on("click", ".skuPropertyLink", selectSku);
	$(document).on("click", ".subscribe:not(.unSubscribe)", addSubscribe);
	$(document).on("click", ".unSubscribe", unSubscribe);
	$(document).on("click", ".addCart", addCart);

	$(document).on("click", ".addWishlist", addWishlist);
	$(document).on("click", ".addCompare", addCompare);
	$(document).on("click", ".fastBack", openFastBack);
	$(document).on("click", ".requestPrice", getRequestPrice);
	$(document).on("click", "#requestPriceSubmit", sendRequestPrice);
	$(document).on("click", "#fastBuyFormSubmit", sendFastBack);
	$(document).on("click", "#appFastBuy .closeWindow", closeFastBack);
	$(document).on("click", "#requestPrice .closeWindow", closeRequestPrice);
	$(document).on("click", ".removeFromWishlist", removeFromWishlist);

	$(document).on("mouseout",  "#appBasket .plus", appBasketPlusHoldUp);
	$(document).on("mouseup",   "#appBasket .plus", appBasketPlusHoldUp);
	$(document).on("mousedown", "#appBasket .plus", appBasketPlusHold);

	$(document).on("mousedown", "#appBasket .minus", appBasketMinusHold);
	$(document).on("mouseout", "#appBasket .minus", appBasketMinusHoldUp);
	$(document).on("mouseup", "#appBasket .minus", appBasketMinusHoldUp);

	$(document).on("click", "#fastViewStores .fastViewStoresExit", closeStoresWindow);
	$(document).on("click", ".getStoresWindow", getStoresWindow);

	$(document).on("click", "#appProductPriceVariant .appPriceVariantExit", closePricesWindow);
	$(document).on("click", ".getPricesWindow", getPricesWindow);

	$(document).on("click", ".getFastView", getFastView);

    $(document).on("click", "#catalogMenuHeading", slideCollapsedBlock);
    $(document).on("click", "#smartFilter .heading", openSmartFiler);
    $(document).on("click", "#nextSection .title", openSmartSections);

    $(document).on("click", "#appBasketContainer, #appFastView .appFastViewContainer, #fastViewStores .fastViewStoresContainer, #appProductPriceVariant, #appFastBuyContainer, .getFastView, .getPricesWindow, .fastBack, .addCart, #requestPriceContainer, .requestPrice", function(event){
    	return event.stopImmediatePropagation();
    });

    //close elements after document click
    $(document).on("click", closeElementsAfterClick);

	// ajax all error;

	$(document).ajaxError(function( event, request, settings ) {
		console.error("Error requesting page " + settings.url);
	});

});


var formatPrice = function(data) {
	var price = String(data).split('.');
	var strLen = price[0].length;
	var str = "";

	for (var i = strLen; i > 0; i--) {
		str = str + ((!(i % 3) ? " " : "") + price[0][strLen - i]);
	}

	return str + (price[1] != undefined ? "." + price[1] : "");
}

function validateEmail(sEmail){

	//vars
    var filter = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;

    //check
    if (filter.test(sEmail)) {
        return true;
    }

    else{
        return false;
    }
    
}