$(function(){

	//functions
	var getNextPage = function(event){

		var $this = $(this).addClass("loading");
		var $ajaxContainer = $(".skuOffersTableAjax");

		//current page
		var pager_num = $this.data("page-num");

		//next page
		pager_num++;

		//check empty var
		if (typeof catalogProductOffersParams != "undefined" 
			&& typeof catalogProductOffersAjaxDir != "undefined"
		){

			if($ajaxContainer.text() != ""){

				//check next page number
				if(typeof pager_num != "undefined" && pager_num != ""){

					//create ajax send object
					var sendDataObj = {
						params: catalogProductOffersParams,
						pager_num: pager_num
					}

					//get request
					var ajaxRequest = $.get(catalogProductOffersAjaxDir + "/ajax.php", sendDataObj, function(http_response){

						//success
						if(http_response){

							//push content
							$ajaxContainer.append(http_response);

							//remove loader
							$this.remove();

							//reload addCart button
							cartReload();
						}

						//failed
						else{
							//print errors
							console.error("ajax request failed");
							$this.removeClass("loading").addClass("error");
						}

					});

				}

			}

			else{
				//print errors
				console.error("ajax container not found");
				$this.removeClass("loading").addClass("error");
			}

		}else{
			//print errors
			console.error("var catalogProductOffersParams or var catalogProductOffersAjaxDir - undefined");
			$this.removeClass("loading").addClass("error");
			return false;
		}

		//
		return event.preventDefault();

	};

	//events
	$(document).on("click", ".catalogProductOffersNext", getNextPage);

});