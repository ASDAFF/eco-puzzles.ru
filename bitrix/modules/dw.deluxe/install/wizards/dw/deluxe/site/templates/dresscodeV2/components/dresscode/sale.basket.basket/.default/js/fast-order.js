$(function(){

	//vars
	$body = $("body");

	//functions
	var fastProcessingResult = function(htmlData){

		if(typeof htmlData != "undefined" && htmlData != ""){

			//remove old data
			$(".fast-basket").remove();

			//insert new data
			$body.append(htmlData);

		}

		else{
			//show error message
			console.error("empty html data");
		}

	}

	var fastOrderClick = function(event){

		//check ajax path
		if(typeof ajaxDir != "undefined" && typeof SITE_ID != "undefined"){

			var sendObject = {
				"act": "getFastBasketWindow",
				"site_id": SITE_ID
			}

			$.ajax({
				url: ajaxDir + "/ajax.php",
				type: "GET",
				data: sendObject,
				dataType: "html",
				success: fastProcessingResult
			});

		}

		else{
			//show error message
			console.error("check vars - ajaxDir | SITE_ID");
		}

		return event.preventDefault();

	};

	//bind
	$(document).on("click", "#fastBasketOrder", fastOrderClick);

});