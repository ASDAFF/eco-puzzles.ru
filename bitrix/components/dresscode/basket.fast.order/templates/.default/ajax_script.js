$(function(){

	var checkInputValues = function($input){

		//vars
		errorFlag = false;

		//check vars
		if(typeof $input != "undefined"){

			//remove clases before check
			$input.removeClass("error");

			//check required
			if($input.data("required") == "yes"){
				//check input text
				if($input.attr("type") == "text"){
					if($input.val() == ""){
						//add error class
						$input.addClass("error");
						errorFlag = true;
					}
				}

				//check checkbox value
				else if($input.attr("type") == "checkbox"){
					if(!$input.prop("checked")){
						//add error class
						$input.addClass("error");
						errorFlag = true;
					}
				}
			}
		}

		return errorFlag;

	};

	var inputChangeProcessing = function(event){

		//vars
		var $this = $(this).removeClass("error");

		//check values (required)
		checkInputValues($this);

	};

	var buttonSubmitProcessing = function(event){
		$("#bind__fast-basket-form").trigger("submit");
		return event.preventDefault();
	};

	var sendProcessingResult = function(jsonData){

		//vars
		var $submitButton = $("#bind__fast-basket-submit").removeClass("error");

		//check vars
		if(typeof jsonData != "undefined"){

			if(jsonData["SUCCESS"] == "Y"){
				//metrica
				if(typeof globalSettings != "undefined" && typeof globalSettings["TEMPLATE_METRICA_FAST_CART"] != "undefined" && typeof globalSettings["TEMPLATE_METRICA_ID"] != "undefined"){
					window["yaCounter" + globalSettings["TEMPLATE_METRICA_ID"]].reachGoal(globalSettings["TEMPLATE_METRICA_FAST_CART"]);
				}
				$(".fast-basket-container").addClass("hidden");
				$(".fast-basket-success").addClass("open");
			}

			else{
				//print error
				$(".fast-basket-container").addClass("hidden");
				$(".fast-basket-error").addClass("open");
				$submitButton.addClass("error");
				console.error(jsonData);
			}

		}

		//remove loader
		$submitButton.removeClass(".loading");

	};

	var formSubmitProcessing = function(event){

		//vars
		var $thisForm = $(this);
		var $formFields = $thisForm.find("input");
		var $submitButton = $thisForm.find("#bind__fast-basket-submit");

		//error memory
		var errorFlag = false;

		//check errors
		if($formFields.length > 0){

			//each fileds
			$formFields.each(function(i, nextField){

				//vars
				var $nextField = $(nextField);

				//check values (required)
				fieldError = checkInputValues($nextField);

				//set error flag
				if(fieldError){
					errorFlag = true;
				}

			});

			//if no errors
			if(!errorFlag){

				//check ajax dir
				if(typeof fastBasketAjaxDir != "undefined" && fastBasketAjaxDir != ""){

					//check SITE_ID
					if(typeof SITE_ID != "undefined" && SITE_ID != ""){

						//get form data
						var formDataArray = $thisForm.serializeArray();
						var formDataObject = {};

						//convert array to object
						$.each(formDataArray, function(i, nextValue){
							formDataObject[nextValue["name"]] = nextValue["value"];
						});

						//service data
						var sendObject = {
							"act": "sendFastForm",
							"site_id": SITE_ID
						}

						//merge objects
						$.extend(sendObject, formDataObject);

						$.ajax({
							url: fastBasketAjaxDir + "/ajax.php",
							type: "POST",
							data: sendObject,
							dataType: "json",
							success: sendProcessingResult
						});

						$submitButton.addClass("loading");

					}

					else{
						//show error message
						console.error("check vars - SITE_ID");
					}

				}

				else{
					//show error message
					console.error("check vars - ajaxDir");
				}

			}

		}

		//block action
		return event.preventDefault();
	};

	var fastBasketRemove = function(event){

		//vars

		var $this = $(this);

		//unbind last events
		$(document).off("change", "#bind__fast-basket-form input");
		$(document).off("click", "#bind__fast-basket-submit");
		$(document).off("submit", "#bind__fast-basket-form");

		//remove component
		$(".fast-basket").remove();
		
		//remove styles
		$(".fastBasketStyles").remove();

		if($this.data("reload") == "yes"){
			window.location.reload();
		}

		//block action
		return event.preventDefault();
	};

	$(document).on("change", "#bind__fast-basket-form input", inputChangeProcessing);
	$(document).on("click", "#bind__fast-basket-submit", buttonSubmitProcessing);
	$(document).on("submit", "#bind__fast-basket-form", formSubmitProcessing);

	//remove
	$(document).on("click", ".fast-basket-container__exit", fastBasketRemove);
	$(document).on("click", ".basket-success-close__button", fastBasketRemove);
	$(document).on("click", ".basket-error-close__button", fastBasketRemove);

});