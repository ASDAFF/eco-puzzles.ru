$(function(){

	var skuLoading = false;

	var elementSelectSku = function(event, ignoreLoading){

		if(skuLoading == true && typeof ignoreLoading == "undefined"){
			return false;
		}

		var _params = "";
		var _props = "";
		var _highload= "";
		var _requestStoresParams = "";

		var $_this = $(this);

		if(!$_this.parent().hasClass("selected")){

			var $_mProduct = $_this.parents(".elementSku");
			var $_mProductContainer = $_this.parents(".item");
			var $_parentProp = $_this.parents(".elementSkuProperty");
			var $_propList = $_mProduct.find(".elementSkuProperty");
			var $_clickedProp = $_this.parents(".elementSkuPropertyValue");
			var $changeFastBack = $_mProduct.find(".fastBack").removeClass("disabled");

			var _level = $_parentProp.data("level");

			$_this.parents(".elementSkuPropertyList").find("li").removeClass("selected");
			$_clickedProp.addClass("selected loading");

			skuLoading = true; //block

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

			if(typeof elementStoresComponentParams != "undefined"){
				_requestStoresParams = JSON.stringify(elementStoresComponentParams);
			}

			var sendObject = {
				"act": "selectSku",
				"props": _props,
				"params": _params,
				"level": _level,
				"iblock_id": $_mProduct.data("iblock-id"),
				"prop_id": $_mProduct.data("prop-id"),
				"product_id": $_mProduct.data("product-id"),
				"highload": _highload,
				"price-code": $_mProductContainer.data("price-code"),
				"stores_params": _requestStoresParams
			}

			$.ajax({
				url: elementAjaxPath,
				type: "POST",
				async : false,
				data: sendObject,
				dataType: "json",
				success: skuProcessingResult
			});

			function skuProcessingResult(jsonData){
							
				$_propList.each(function(pI, pV){
		  			var $_sf = $(pV);
		  				$_sf.data("level") > _level && $_sf.find(".elementSkuPropertyValue").removeClass("selected").addClass("disabled");
		  		});
				
				$.each(jsonData[1]["PROPERTIES"], function(name, val){
				  	var $_gPropList = $_propList.filter(function(){ return ($(this).data("name") == name); });
				  	var $_gPropListValues = $_gPropList.find(".elementSkuPropertyValue");
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
				
				$(".changeName").html(jsonData[0]["PRODUCT"]["NAME"]);
				$(".cheaper-product-name").html(jsonData[0]["PRODUCT"]["NAME"]);

				$_mProduct.find(".changeID").data("id", jsonData[0]["PRODUCT"]["ID"]).attr("data-id", jsonData[0]["PRODUCT"]["ID"]);
				$_mProduct.find(".changePicture").html($("<img/>").attr("src", jsonData[0]["PRODUCT"]["IMAGES"][0]["MEDIUM_IMAGE"]["SRC"]));
				$_mProduct.find(".changePropertiesNoGroup").html(jsonData[0]["PRODUCT"]["RESULT_PROPERTIES_NO_GROUP"]);
				$_mProduct.find(".changePropertiesGroup").html(jsonData[0]["PRODUCT"]["RESULT_PROPERTIES_GROUP"]);

				var $changeCart = $_mProduct.find(".changeCart").removeClass("subscribe unSubscribe");

				$changeCart.find("img").remove();
				if(jsonData[0]["PRODUCT"]["PRICE"]["DISCOUNT_PRICE"]){
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

				//AVAILABLE
				var $changeAvailable = $_mProduct.find(".eChangeAvailable");

				$changeAvailable.removeClass("getStoresWindow");
				$changeAvailable.removeClass("outOfStock");
				$changeAvailable.removeClass("onOrder");
				$changeAvailable.removeClass("inStock");
				$changeAvailable.removeAttr("href");

				if(jsonData[0]["PRODUCT"]["PRICE"]["DISCOUNT_PRICE"]){
					var $productChangePrice = $_mProduct.find(".changePrice").empty().removeClass("getPricesWindow").removeClass("requestPrice").removeAttr("href").data("id", jsonData[0]["PRODUCT"]["ID"]);
					var $productPriceVal = $("<span/>", {class: "priceVal"}).html(jsonData[0]["PRODUCT"]["PRICE"]["DISCOUNT_PRICE"]);
					var $productPriceContainer = $("<span/>", {class: "priceContainer"}).html($productPriceVal);
				}else{
					var $productChangePrice = $_mProduct.find(".changePrice").empty().removeClass("getPricesWindow").removeAttr("href").addClass("requestPrice").addClass("disabled").data("id", jsonData[0]["PRODUCT"]["ID"]);
					var $productPriceVal = $("<span/>", {class: "priceVal"}).html(LANG["REQUEST_PRICE_LABEL"]);
					var $productPriceContainer = $("<span/>", {class: "priceContainer"}).html($productPriceVal);

					jsonData[0]["PRODUCT"]["CAN_BUY"] = "N";
				}

				//remove bonus container
				$productChangePrice.find(".purchaseBonus").remove();

				//write price container with price value
				$productChangePrice.html($productPriceContainer);

				if(jsonData[0]["PRODUCT"]["RESULT_PROPERTIES"]){
					$product.find(".changeProperties").html(jsonData[0]["PRODUCT"]["RESULT_PROPERTIES"]);
				}

				if(jsonData[0]["PRODUCT"]["COUNT_PRICES"] > 1){
					$productPriceContainer.prepend($("<span/>", {class: "priceIcon"}));
					$productChangePrice.addClass("getPricesWindow").attr("href", "#");
				}

				if(jsonData[0]["PRODUCT"]["CATALOG_QUANTITY"] > 0){
					if(jsonData[0]["PRODUCT"]["STORES_COUNT"] > 1){
						$changeAvailable.html($("<span/>").html(LANG["CATALOG_AVAILABLE"])).addClass("inStock").attr("href", "#").addClass("getStoresWindow").data("id", jsonData[0]["PRODUCT"]["ID"]);
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
					if(jsonData[0]["PRODUCT"]["CAN_BUY"] != "Y"){
						
						$changeAvailable.html(LANG["CATALOG_NO_AVAILABLE"]).addClass("outOfStock");
						$changeFastBack.addClass("disabled");
						
						if(jsonData[0]["PRODUCT"]["CATALOG_SUBSCRIBE"] == "Y" && jsonData[0]["PRODUCT"]["PRICE"]["DISCOUNT_PRICE"]){
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

				//QTY BOX

				//get qty box ()
				var $qtyBox = $_mProduct.find(".qtyBlock .qty");

				//write values
				$qtyBox.val(jsonData[0]["PRODUCT"]["BASKET_STEP"]).data("max-quantity", jsonData[0]["PRODUCT"]["CATALOG_QUANTITY"]).data("step", jsonData[0]["PRODUCT"]["BASKET_STEP"]).removeClass("error");
				$qtyBox.removeAttr("data-extended-price").removeData("extended-price");

				$changeCart.data("quantity", jsonData[0]["PRODUCT"]["BASKET_STEP"]);

				if(typeof jsonData[0]["PRODUCT"]["PRICE"]["EXTENDED_PRICES_JSON_DATA"] != "undefined"){
					if(jsonData[0]["PRODUCT"]["PRICE"]["EXTENDED_PRICES_JSON_DATA"] != ""){
						$qtyBox.data("extended-price", jsonData[0]["PRODUCT"]["PRICE"]["EXTENDED_PRICES_JSON_DATA"]);
					}
				}

				if(jsonData[0]["PRODUCT"]["CATALOG_QUANTITY_TRACE"] == "Y" && jsonData[0]["PRODUCT"]["CATALOG_CAN_BUY_ZERO"] == "N"){
					$qtyBox.data("enable-trace", "Y");
				}else{
					$qtyBox.data("enable-trace", "N");
				}

				//stores component

				//storesTab
				var $storesTab = $_mProduct.find('.changeTabs .tab[data-id="stores"]').removeClass("active disabled");

				if(typeof jsonData[0]["PRODUCT"]["STORES_COMPONENT"] != "undefined" && jsonData[0]["PRODUCT"]["STORES_COMPONENT"] != ""){
					if($("div").is("#storesContainer")){
						//vars
						var $storesContainer = $("#storesContainer");
						//insert component html
						$storesContainer.html(jsonData[0]["PRODUCT"]["STORES_COMPONENT"]);
						//calc element tabs
						if (typeof startElementTabs === "function") { 
						    startElementTabs();
						}
						
					}
				}

				else{

					//clear
					$("#storesContainer").html("");
					$storesTab.addClass("disabled");

					//calc element tabs
					if (typeof startElementTabs === "function") { 
					    startElementTabs();
					}

				}

				// pictures
				var countImages = 0;

				if(jsonData[0]["PRODUCT"]["IMAGES"]){

					for(var i in jsonData[0]["PRODUCT"]["IMAGES"]) {
						countImages = i;
					}

					// big slider vars
					var $pictureSlider = $("#pictureContainer .pictureSlider").empty();

					// small pictures slider
					var $moreImagesCarousel = $("#moreImagesCarousel").removeClass("hide");
					var $moreImagesCarouselSlideBox = $moreImagesCarousel.find(".slideBox");
					$moreImagesCarouselSlideBox.find(".item").remove();

					$.each(jsonData[0]["PRODUCT"]["IMAGES"], function(i, nextElement){

						//big slider
						$pictureSlider.append(
							$("<div />", {class: "item"}).append(
								$("<a/>", {class: "zoom", href: nextElement["LARGE_IMAGE"]["SRC"]}).data("large-picture", nextElement["LARGE_IMAGE"]["SRC"]).data("small-picture", nextElement["SMALL_IMAGE"]["SRC"]).append(
									$("<img />", {src: nextElement["MEDIUM_IMAGE"]["SRC"]})
								)
							)
						)

						if(countImages > 0){
							//small slider
							$moreImagesCarouselSlideBox.append(
								$("<div />", {class: "item"}).append(
									$("<a/>", {class: "zoom", href: nextElement["LARGE_IMAGE"]["SRC"]}).data("large-picture", nextElement["LARGE_IMAGE"]["SRC"]).append(
										$("<img />", {src: nextElement["SMALL_IMAGE"]["SRC"]})
									)
								)
							);
						}else{
							$moreImagesCarousel.addClass("hide");
						}
					});

					//addCart button reload
					changeAddCartButton(basketProductsNow);
					//subscribe button reload
					subscribeOnline();					

					//apps
					startPictureElementSlider();
					startMorePicturesElementCarousel();
					createZoomer();
				
				}

				//short description

				if(jsonData[0]["PRODUCT"]["PREVIEW_TEXT"]){
					$_mProduct.find(".changeShortDescription").html(jsonData[0]["PRODUCT"]["PREVIEW_TEXT"]);
				}else{
					if($_mProduct.find(".changeShortDescription").data("first-value")){
						$_mProduct.find(".changeShortDescription").html($_mProduct.find(".changeShortDescription").data("first-value"));
					}
				}

				// full description

				if(jsonData[0]["PRODUCT"]["DETAIL_TEXT"]){
					$_mProduct.find(".changeDescription").html(jsonData[0]["PRODUCT"]["DETAIL_TEXT"]);
				}else{
					if($_mProduct.find(".changeDescription").data("first-value")){
						$_mProduct.find(".changeDescription").html($_mProduct.find(".changeDescription").data("first-value"));
					}
				}

				//article

				if(typeof(jsonData[0]["PRODUCT"]["PROPERTIES"]["CML2_ARTICLE"]) != "undefined"){
					if(typeof(jsonData[0]["PRODUCT"]["PROPERTIES"]["CML2_ARTICLE"]["VALUE"]) != "undefined" && jsonData[0]["PRODUCT"]["PROPERTIES"]["CML2_ARTICLE"]["VALUE"] !=""){
						$_mProduct.find(".changeArticle").html(jsonData[0]["PRODUCT"]["PROPERTIES"]["CML2_ARTICLE"]["VALUE"]).parents(".article").removeClass("hidden");
					}else{
						if($_mProduct.find(".changeArticle").data("first-value")){
							$_mProduct.find(".changeArticle").html($_mProduct.find(".changeArticle").data("first-value"));
						}else{
							$_mProduct.find(".changeArticle").parents(".article").addClass("hidden");
						}
					}
				}


				if(jsonData[0]["PRODUCT"]["PRICE"]["DISCOUNT_PRICE"]){
					if($_mProduct.data("hide-measure") != "Y" && jsonData[0]["PRODUCT"]["MEASURE"] != undefined && jsonData[0]["PRODUCT"]["MEASURE"]["SYMBOL_RUS"] != ""){
						$productChangePrice.append(
							$("<span/>").addClass("measure").html(
								" / " + jsonData[0]["PRODUCT"]["MEASURE"]["SYMBOL_RUS"] + " "
							)
						);
					}
				}

				if(jsonData[0]["PRODUCT"]["PRICE"]["RESULT_PRICE"]["DISCOUNT"] > 0){
					
					$productPriceBlock = $("<span/>", {class: "priceBlock"});
					$productPriceBlock.append(
						$("<span/>").addClass("oldPriceLabel").html(
							$("<s/>").addClass("discount").html(
								jsonData[0]["PRODUCT"]["PRICE"]["RESULT_PRICE"]["BASE_PRICE"]
							)
						)
					);

					$productPriceBlock.append(
						$("<span/>").addClass("oldPriceLabel").html(" &nbsp; " + LANG["CATALOG_ECONOMY"]).append(
							$("<span/>").addClass("economy").html(
								jsonData[0]["PRODUCT"]["PRICE"]["DISCOUNT_PRINT"]
							)
						)
					);

					//write discount
					$productChangePrice.prepend($productPriceBlock);

				}

				//bonus
				if(typeof jsonData[0]["PRODUCT"]["PROPERTIES"]["BONUS"] != "undefined" && jsonData[0]["PRODUCT"]["PROPERTIES"]["BONUS"]["VALUE"] != ""){
					var $purchaseBonus = $("<span />", {class: "purchaseBonus"}).html(jsonData[0]["PRODUCT"]["PROPERTIES"]["BONUS"]["NAME"]);
					var $purchaseBonusValue = $("<span />", {class: "theme-color"}).html("+ " + jsonData[0]["PRODUCT"]["PROPERTIES"]["BONUS"]["VALUE"]);
					$purchaseBonus.prepend($purchaseBonusValue);
					$productChangePrice.append($purchaseBonus);
				}
				
				//catalog set (complect) block

				if($("div").is("#set")){

					var $changePriceSet = $(".changePriceSet").html(jsonData[0]["PRODUCT"]["PRICE"]["DISCOUNT_PRICE"]);
					if(jsonData[0]["PRODUCT"]["PRICE"]["RESULT_PRICE"]["DISCOUNT"] > 0){
						$changePriceSet.append(
							$("<s/>").addClass("discount").html(
								jsonData[0]["PRODUCT"]["PRICE"]["RESULT_PRICE"]["BASE_PRICE"]
							)
						);
					}

					//j vars
					var $setProductContainer = $("#set");
					var $setMainElements = $setProductContainer.find('.setMainElement:not(".disabled")');
					var $setPrice = $("#setPrice");
					var $setDisnt = $("#setDisnt");

					//n vars
					var setPriceValue = 0;
					var setPriceDiscountValue = 0;

					//str vars
					var tmpPriceLabel = $setPrice.html().replace(/[0-9]/g, '');
					var tmpDisntLabel = $setDisnt.html().replace(/[0-9]/g, '');

					var changePriceValue = parseInt(jsonData[0]["PRODUCT"]["PRICE"]["DISCOUNT_PRICE"].replace(/[^0-9]/g, ''));

					$setProductContainer.find(".general.setElement").data("price", changePriceValue).data("discount", jsonData[0]["PRODUCT"]["PRICE"]["RESULT_PRICE"]["DISCOUNT"]);

					$setMainElements.each(function(i, nextElement){
						var $nextElement = $(nextElement);
						setPriceValue += parseInt($nextElement.data("price"));
						setPriceDiscountValue += parseInt($nextElement.data("price")) + parseInt($nextElement.data("discount"));
					});

					$setPrice.html(
						formatPrice(setPriceValue) + tmpPriceLabel
					);

					$setDisnt.html(
						formatPrice(setPriceDiscountValue) + tmpDisntLabel
					);

					if(setPriceDiscountValue == 0){
						$setDisnt.hide();
					}else{
						$setDisnt.show();
					}

				}

				if($("div").is("#setWindow")){

					// catalog set window
					var $setWindowContainer = $("#setWindow");
					var $setWindowElements = $setWindowContainer.find('.setWindowElement:not(".disabled")');
					var $setWindowPrice = $("#setWPrice");
					var $setWindowDisnt = $("#setWDisnt");

					//n vars
					var setWindowPriceValue = 0;
					var setWindowPriceDiscountValue = 0;

					//str vars
					var tmpPriceWindowLabel = $setWindowPrice.html().replace(/[0-9]/g, '');
					var tmpDisntWindowLabel = $setWindowDisnt.html().replace(/[0-9]/g, '');

					var changePriceValue = parseInt(jsonData[0]["PRODUCT"]["PRICE"]["DISCOUNT_PRICE"].replace(/[^0-9]/g, ''));

					$setWindowContainer.find("#wProduct").data("price", changePriceValue).data("discount", jsonData[0]["PRODUCT"]["PRICE"]["RESULT_PRICE"]["DISCOUNT"]);

					$setWindowElements.each(function(i, nextElement){
						var $nextElement = $(nextElement);
						setWindowPriceValue += parseInt($nextElement.data("price"));
						setWindowPriceDiscountValue += parseInt($nextElement.data("price")) + parseInt($nextElement.data("discount"));
					});

					$setWindowPrice.html(
						formatPrice(setWindowPriceValue) + tmpPriceLabel
					);

					$setWindowDisnt.html(
						formatPrice(setWindowPriceDiscountValue) + tmpDisntLabel
					);

					if(setWindowPriceDiscountValue == 0){
						$setWindowDisnt.hide();
					}else{
						$setWindowDisnt.show();
					}
				}

				$_clickedProp.removeClass("loading");
				skuLoading = false;
			}
		  		
		}

		event.preventDefault();

	}

	var openSkuDropDown = function(event){

		//vars
		var $this = $(this);
		var $dropList = $this.siblings(".skuDropdownList");

		//show list
		$dropList.toggleClass("opened");

		//opened flag
		skuDropdownOpened = $dropList.hasClass("opened");

		return event.preventDefault();

	};

	var selectSkuDropDownValue = function(event){

		//vars
		var $this = $(this);
		var $dropList = $this.parents(".skuDropdownList");
		var $dropListItems = $dropList.find(".skuDropdownListItem").removeClass("selected");
		var $checkedItem = $dropList.siblings(".skuCheckedItem");

		//hide list
		$dropList.removeClass("opened");

		//opened flag
		skuDropdownOpened = false;

		//active
		$this.addClass("selected");

		//write value
		$checkedItem.html($this.text());

		//
		return event.preventDefault();

	};

	var closeSkuDropDown = function(event){

		//if opened
		if(skuDropdownOpened){

			//close
			$(".skuDropdownList").removeClass("opened");

			//opened flag
			skuDropdownOpened = false;
		}

	};

	//skuDropDown
	$(document).on("click", ".elementSkuDropDownProperty .skuCheckedItem", openSkuDropDown);
	$(document).on("click", ".elementSkuDropDownProperty .skuDropdownListItem", selectSkuDropDownValue);
	$(document).on("click", ".skuDropdown", function(event){event.stopImmediatePropagation()});
	$(document).on("click", closeSkuDropDown);

	//sku select
	$(document).on("click", ".elementSkuPropertyLink", elementSelectSku);

});