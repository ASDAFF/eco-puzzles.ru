$(function(){

	var getNextPage = function(event){
		
		var $self =  $("#popSection");
		var page = $self.data("page") + 1;			


		var $ajaxContainer = $self.find(".ajaxContainer")
										.addClass("loading");

		var sendDataObj = {
			params: popSectionParams,
			page: page
		}

		var jqxhr = $.get(ajaxDirPopSection + "/ajax.php", sendDataObj, function(http){
			if(http){
				$ajaxContainer.html(http)
					.removeClass("loading");

				$self.data("page", page);
			}
		});

		return event.preventDefault();
	};

	$(document).on("click", "#popSection .showMore", getNextPage);
});