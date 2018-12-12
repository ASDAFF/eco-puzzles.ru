$(function(){

    var ValidateEmail = function(email) {
        var expr = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
        return expr.test(email);
    };

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
					if($input.data("email") == "yes"){
						if(!ValidateEmail($input.val())){
							//add error class
							$input.addClass("error");
							errorFlag = true;							
						}
					}
					else{
						if($input.val() == ""){
							//add error class
							$input.addClass("error");
							errorFlag = true;
						}
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
		$("#bind__catalog-subscribe-form").trigger("submit");
		return event.preventDefault();
	};

	var sendProcessingResult = function(jsonData){

		//vars
		var $submitButton = $("#bind__catalog-subscribe-submit").removeClass("error");
		var $subscribeButton = $('.subscribe[data-id="' + jsonData["ITEM_ID"] + '"]');
		var $subscribeButtonImage = $subscribeButton.find("img");

		//check vars
		if(typeof jsonData != "undefined"){

			if(jsonData["SUCCESS"] == "Y"){

				//add clases
				$(".catalog-subscribe-container").addClass("hidden");
				$(".catalog-subscribe-success").addClass("open");
				
				//each buttons
				$subscribeButton.each(function(i, nextButton){

					var $nextButton = $(nextButton);
					var $nextButtonImage = $nextButton.find("img");

					$nextButton.text(LANG["REMOVE_SUBSCRIBE_LABEL"]).prepend($nextButtonImage.attr({
							src: TEMPLATE_PATH + "/images/subscribeDelete.png",
						})
					).addClass("unSubscribe").data("subscribe-id", jsonData["SUBSCRIBE_ID"]);

				});

			}

			else{
				//print error
				$(".catalog-subscribe-container").addClass("hidden");
				$(".catalog-subscribe-error").addClass("open");
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
		var $submitButton = $thisForm.find("#bind__catalog-subscribe-submit");

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
				if(typeof subscribeAjaxDir != "undefined" && subscribeAjaxDir != ""){

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
							"act": "sendSubscribeForm",
							"site_id": SITE_ID
						}

						//merge objects
						$.extend(sendObject, formDataObject);

						$.ajax({
							url: subscribeAjaxDir + "/ajax.php",
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

	var subscribeRemove = function(event){

		//vars

		var $this = $(this);

		//unbind last events
		$(document).off("change", "#bind__catalog-subscribe-form input");
		$(document).off("click", "#bind__catalog-subscribe-submit");
		$(document).off("submit", "#bind__catalog-subscribe-form");

		//remove component
		$(".catalog-subscribe").remove();
		
		//remove styles
		$(".subscribeStyles").remove();

		if($this.data("reload") == "yes"){
			window.location.reload();
		}

		//block action
		return event.preventDefault();
	};

	$(document).on("change", "#bind__catalog-subscribe-form input", inputChangeProcessing);
	$(document).on("click", "#bind__catalog-subscribe-submit", buttonSubmitProcessing);
	$(document).on("submit", "#bind__catalog-subscribe-form", formSubmitProcessing);

	//remove
	$(document).on("click", ".catalog-subscribe-container__exit", subscribeRemove);
	$(document).on("click", ".subscribe-success-close__button", subscribeRemove);
	$(document).on("click", ".subscribe-error-close__button", subscribeRemove);

});