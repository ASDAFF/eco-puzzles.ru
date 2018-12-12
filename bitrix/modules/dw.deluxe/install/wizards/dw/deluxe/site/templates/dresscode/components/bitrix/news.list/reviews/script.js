$(function(){

	var checkReviewForm = function(event){

		var $thisForm = $(this);
		var $formFields = $thisForm.find('select, input[type="text"], textarea').removeClass("error");
		var $formSubmitButton = $thisForm.find(".shop-review-form-submit").addClass("loading");

		var formError = false;

		$formFields.each(function(index, nextElement) {
			var $nextElement = $(nextElement);
			if($nextElement.val() == "" || $nextElement.val() == 0){
				$nextElement.addClass("error");
				formError = true;
			}
		});

		if(!formError){
			$.getJSON(reviewFormAjaxDir + "/ajax.php?act=newReview&" + $thisForm.serialize(), function(jsonData){
				if(jsonData["ERROR"] == "Y"){
					showMessageWindow(reviewFormLang["errorHeading"], reviewFormLang["errorTypeMessage_" + jsonData["ERROR_TYPE"]]);
				}else{
					//metrica
					if(typeof globalSettings != "undefined" && typeof globalSettings["TEMPLATE_METRICA_REVIEW_MAGAZINE"] != "undefined" && typeof globalSettings["TEMPLATE_METRICA_ID"] != "undefined"){
						window["yaCounter" + globalSettings["TEMPLATE_METRICA_ID"]].reachGoal(globalSettings["TEMPLATE_METRICA_REVIEW_MAGAZINE"]);
					}
					showMessageWindow(reviewFormLang["successHeading"], reviewFormLang["successMessage"]);
					$thisForm[0].reset();
				}
				$formSubmitButton.removeClass("loading");
			});
		}else{
			$formSubmitButton.removeClass("loading");
		}

		return event.preventDefault();

	};

	var focusReviewFields = function(event){
		$(this).removeClass("error");
	};

	var showMessageWindow  = function(messageHeading, messageText){
		$(".shop-review-message-window-heading .window-heading-text").html(messageHeading);
		$(".shop-review-message-window-message").html(messageText);
		$(".shop-review-message-window").addClass("visible");
	};

	var closeReviewWindow = function(event){
		$(".shop-review-message-window").removeClass("visible");
		return event.preventDefault();
	}

	var scrolltoReviewForm = function(event){
		var $this = $(this);
		if(!$this.hasClass("no-auth")){
			$("html, body").animate({
				scrollTop: $(".shop-review-form").offset().top - 150
			}, 300);
			return event.preventDefault();
		}
	};

	var reviewUtileBad = function(event){
		var $this = $(this);
		$.getJSON(reviewFormAjaxDir + "/ajax.php?act=utileBad&id=" + $this.data("id") + "&iblock_id=" + $this.data("iblock-id"), function(jsonData){
			if(jsonData["ERROR"] == "Y"){
				showMessageWindow(reviewFormLang["errorHeading"], reviewFormLang["errorTypeMessage_" + jsonData["ERROR_TYPE"]]);
			}else{
				$this.find("span").html(jsonData["VOTE_COUNT"]);
			}
		});
		return event.preventDefault();
	};

	var reviewUtileGood = function(event){
		var $this = $(this);
		$.getJSON(reviewFormAjaxDir + "/ajax.php?act=utileGood&id=" + $this.data("id") + "&iblock_id=" + $this.data("iblock-id"), function(jsonData){
			if(jsonData["ERROR"] == "Y"){
				showMessageWindow(reviewFormLang["errorHeading"], reviewFormLang["errorTypeMessage_" + jsonData["ERROR_TYPE"]]);
			}else{
				$this.find("span").html(jsonData["VOTE_COUNT"]);
			}
		});
		return event.preventDefault();
	};

	$(document).on("submit", ".shop-review-form", checkReviewForm);
	$(document).on("click", ".shop-review-item-utile-bad", reviewUtileBad);
	$(document).on("click", ".shop-review-item-utile-good", reviewUtileGood);
	$(document).on("click", ".shop-review-top-new-button", scrolltoReviewForm);
	$(document).on("focus", 'select, input[type="text"], textarea', focusReviewFields);
	$(document).on("click", ".shop-review-message-window-exit, .shop-review-message-window-close", closeReviewWindow);

});