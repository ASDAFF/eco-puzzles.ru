$(function(){
	var searchVisible;

	var $searchQuery = $("#searchQuery");
	var openSearch = function(event){
		$("#topSearch, #topSearch3").slideDown(150, function(){
			var tmpSearchKeyword = $searchQuery.val();
			searchVisible = true;
			$searchQuery.val("");
			$searchQuery.val(tmpSearchKeyword);
			$searchQuery.focus();
		});
		event.preventDefault();
	}

	var closeSearch = function(event){
		if(searchVisible == true){
			if(event.which == 1){
				$("#searchProductsClose").trigger("click");
				$("#topSearch, #topSearch3").slideUp(150);
				searchVisible = false;
				return event.preventDefault();
			}
		}
	}

	$(document).keydown(function(event) {
	    if(searchVisible == true && event.keyCode === 27 ) {
			$("#searchProductsClose").trigger("click");
			$("#topSearch, #topSearch3").slideUp(150);
			searchVisible = false;
	        return false;
	    }
	});


	$(document).on("click", "#headerTools, #topSearchForm, #searchResult", function(event){event.stopImmediatePropagation();});
	$(document).on("click", "#openSearch", openSearch);
	$(document).on("click", "#topSeachCloseForm", closeSearch);
	$(document).on("click", closeSearch);
});