$(function(){

	var getNextPage = function(event){
		
		var $self =  $("#brandList");
		var page = $self.data("page") + 1;			


		var $ajaxContainer = $self.find(".ajaxContainer")
										.addClass("loading");

		var sendDataObj = {
			params: brandListParams,
			page: page
		}

		var jqxhr = $.get(ajaxDirBrandList + "/ajax.php", sendDataObj, function(http){
			if(http){
				$ajaxContainer.html(http)
					.removeClass("loading");

				$self.data("page", page);
			}
		});

		return event.preventDefault();
	};

	$(document).on("click", "#brandList .showMore", getNextPage);
});