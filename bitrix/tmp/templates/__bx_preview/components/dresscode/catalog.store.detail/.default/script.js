$(function(){
	
	var scrollToMap = function(event){

		var $storeDetailMap = $(".storeDetailMap");

		if($storeDetailMap){
			$("html, body").animate({
				"scrollTop": $storeDetailMap.offset().top - 150
			}, 250);
		}

		return event.preventDefault();

	};

	$(document).on("click", "#storeDetail .showByMapLink", scrollToMap);

});