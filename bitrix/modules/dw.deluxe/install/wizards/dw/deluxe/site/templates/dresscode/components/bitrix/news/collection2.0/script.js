$(function(){

	var openSmartFilterFlag = false;
	var changeSortParams = function(){
		window.location.href = $(this).val();
	};


	var openSmartFilter = function(event){

		var smartFilterOffset = 0;

		if($(".oFilter").length > 0){
			smartFilterOffset = $(".oFilter").offset().top;
		}

		// smartFilter block adaptive toggle
		if(!openSmartFilterFlag){
			$("#smartFilter").addClass("opened").css({"marginTop": (smartFilterOffset + 24) + "px", "top": "24px"});
			openSmartFilterFlag = true;
		}

		else{
			$("#smartFilter").removeClass("opened").removeAttr("style");
			openSmartFilterFlag = false;
		}

		return event.preventDefault();
	};

	var closeSmartFilter = function(event){
		if(openSmartFilterFlag){
			$("#smartFilter").removeClass("opened");
			openSmartFilterFlag = false;
		}
	};

	$(document).on("click", ".oSmartFilter", openSmartFilter);
    $(document).on("click", "#smartFilter, .oSmartFilter, .rangeSlider", function(event){
    	return event.stopImmediatePropagation();
    });

	$("#selectSortParams, #selectCountElements").on("change", changeSortParams);
	$(document).on("click", closeSmartFilter);
});