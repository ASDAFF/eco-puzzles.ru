$(function(){

	var changeSortParams = function(){
		window.location.href = $(this).val();
	};

	$("#selectSortParams, #selectCountElements").on("change", changeSortParams);

 	var wishlistFormSend = function(event){
 		
 		//jquery vars
 		$emailField = $("#wishlist-form-email").removeClass("error").addClass("loading");
 		$this = $(this);

 		//vars
 		emailFieldValue = $emailField.val();

 		//check empty
 		if(emailFieldValue != "" && validateEmail(emailFieldValue)){

 			if(typeof wishListArParams == "object"){
	 			
	 			//shaping
	 			wishListArParams["act"] = "sendMail";
	 			wishListArParams["email"]  = emailFieldValue;
	 			wishListArParams["siteID"] = SITE_ID;

	 			//send request
		 		$.getJSON(wishListAjaxPath, wishListArParams).done(function(jData){
		 			
		 			//check data
					if(jData["SUCCESS"] == "Y"){
						$this.text(LANG["WISHLIST_SENDED"]).addClass("sended");
						$emailField.removeClass("loading");
					}

	 				//error
					else{
						$emailField.addClass("error").removeClass("loading");
					}

				}).fail(function(jqxhr, textStatus, error){
				    console.error(
				    	"Request Failed: " + textStatus + ", " + error
				    );
			    });

			}

		}

 		//error
 		else{
 			$emailField.addClass("error").removeClass("loading");
 		}

 		return event.preventDefault();
 	};

 	//binds
	$(document).on("click", "#wishlist-form-send", wishlistFormSend);


});