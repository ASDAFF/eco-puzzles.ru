$(function(){
	
	//vars jquery
	var $searchQuery = $("#searchQuery");
	var $searchResult = $("#searchResult");
	var $searchOverlap = $("#searchOverlap");

	//vars
	var searchTimeoutID;

	//functions
	var searchKeyPressed = function(event){
		if(event.keyCode !== 27){
			clearTimeout(searchTimeoutID);
			if($searchQuery.val().length > 1){
				searchTimeoutID = setTimeout(function(){
					getSearchProductList($searchQuery.val())
				}, 250);
			}else{
				$searchResult.empty().removeClass("visible");
				$searchOverlap.hide();
			}
		}
	};

	var getSearchProductList = function(keyword, page){

		var sectionPage = page != "" ? page : 0;

		$searchQuery.addClass("loading");

		var searchProductParamsObject = jQuery.parseJSON(searchProductParams);

		if(searchProductParamsObject["HIDE_NOT_AVAILABLE"] == undefined){
			searchProductParamsObject["HIDE_NOT_AVAILABLE"] = "N";
		}

		var getParamsObject = {
			"IBLOCK_TYPE": searchProductParamsObject["IBLOCK_TYPE"],
			"IBLOCK_ID": searchProductParamsObject["IBLOCK_ID"],
			"CONVERT_CASE": searchProductParamsObject["CONVERT_CASE"],
			"ELEMENT_SORT_FIELD": "sort",
			"ELEMENT_SORT_ORDER": "asc",
			"PROPERTY_CODE": searchProductParamsObject["PROPERTY_CODE"],
			"PAGE_ELEMENT_COUNT": 6,
			"PRICE_CODE": searchProductParamsObject["PRICE_CODE"],
			"PAGER_TEMPLATE": "round",
			"CONVERT_CURRENCY": searchProductParamsObject["CONVERT_CURRENCY"],
			"CURRENCY_ID": searchProductParamsObject["CURRENCY_ID"],
			"HIDE_NOT_AVAILABLE": searchProductParamsObject["HIDE_NOT_AVAILABLE"],
			"FILTER_NAME": "arrFilter",
			"ADD_SECTIONS_CHAIN": "N",
			"SHOW_ALL_WO_SECTION": "Y",
			"HIDE_MEASURES": searchProductParamsObject["HIDE_MEASURES"],
			"PAGEN_1": sectionPage,
			"SEARCH_QUERY": keyword,
			"SEARCH_PROPERTIES": searchProductParamsObject["SEARCH_PROPERTIES"]
		};

		var jqxhr = $.get(searchAjaxPath, getParamsObject, afterSearchGetProducts);
	
	};

	var afterSearchGetProducts = function(http){
		$searchQuery.removeClass("loading");
		$searchResult.html(http).addClass("visible");
		$searchOverlap.show();
	};
	
	var searchCloseWindow = function(event){
		$searchResult.empty().removeClass("visible");
		clearTimeout(searchTimeoutID);
		$searchOverlap.hide();
		return event.preventDefault();
	};

	var pageChangeProduct = function(event){
		
		var $this = $(this);
		var page = parseInt($this.data("page"));
		
		if(page > 0 || page == 0){
			getSearchProductList($searchQuery.val(), page);
		}
		
		return event.preventDefault();
	
	};

	//bind
	$searchQuery.on("keyup", searchKeyPressed);
	$(document).on("click", "#searchProductsClose", searchCloseWindow);
	$(document).on("click", "#searchResult .bx-pagination a", pageChangeProduct);

});