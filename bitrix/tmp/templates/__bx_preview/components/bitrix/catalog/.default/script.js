$(function(){

	//global vars
	var openSmartFilterFlag = false;
	var changeSortParams = function(){
		window.location.href = $(this).val();
	};

	$("#selectSortParams, #selectCountElements").on("change", changeSortParams);


	var openSmartFilter = function(event){

		// smartFilter block adaptive toggle
		if(!openSmartFilterFlag){
			$("#smartFilter").addClass("opened").css('marginTop', ($('.oSmartFilter').offset().top - $('#nextSection').offset().top - $('#nextSection').height() +25));
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
	$(document).on("click", closeSmartFilter);

});