$(function(){
	
	var mainElementID = "#homeCatalog"; //--\\
	var $self = $(mainElementID);
	var httpLock = false;

	var getProductByGroup = function(event){
		if(httpLock == false){
			if(offersProductParams != ""){
			
				var $this = $(this);
				var $parentThis = $this.parent();

				var page = $this.data("page");			
				var groupID = $this.data("group");
				var changeSheet = $this.data("sheet");

				
				if($parentThis.hasClass("selected") && changeSheet != "Y"){
					return false;
				}

				if(changeSheet != "Y"){
					var $captionEL = $self.find(".caption")
										.removeClass("selected");

				}

				var $ajaxContainer = $self.find(".ajaxContainer")
												.addClass("loading");

				$parentThis
					.addClass("loading");

				$this.data("sheet", "N");	// clear status 

				if(parseInt(groupID, 10) > 0 || groupID == "all"){
					
					httpLock = true;

					var sendDataObj = {
						params: offersProductParams,
						groupID: groupID,
						page: page
					}

					var jqxhr = $.get(ajaxDir + "/ajax.php", sendDataObj, function(http) {
						if(http){
							
							$ajaxContainer.html(http)
								.removeClass("loading");
							
							$parentThis
								.removeClass("loading");

							if(changeSheet != "Y"){		
								$this.parents(".caption")
									.addClass("selected");
							}
							httpLock = false;
						}
					});

				}else{
					console.error("check data group (data.group not found)");
				}
			
			}else{
				console.error("var type (json) not found (name offersProductParams)");
			}
		}
		return event.preventDefault();
		
	};

	var getProductNextPage = function(event){
	
		var $activeGroup = $self.find(".caption.selected a");
		var currentPage = parseInt($activeGroup.data("page"), 10);

		$activeGroup.data({
			"page": currentPage + 1,
			"sheet": "Y"
		});

		$activeGroup.trigger("click");

		return event.preventDefault();
	
	}

	$(document).on("click", ".getProductByGroup", getProductByGroup);
	$(document).on("click", ".product .showMore", getProductNextPage);

});