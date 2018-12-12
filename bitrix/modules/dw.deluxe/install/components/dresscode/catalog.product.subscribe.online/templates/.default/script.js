var subscribeOnline;
var subscribeProductsNow;

$(function(){

	//update buttons
	var subscribeButtonReload = function(jsonData){
		if(typeof jsonData != "undefined"){
			$.each(jsonData, function(subscribeId, itemId){

				//vars
				var $subscribeButton = $('.subscribe[data-id="' + itemId + '"]');

				//each buttons
				$subscribeButton.each(function(i, nextButton){

					//vars
					var $nextButton = $(nextButton);
					var $nextButtonImage = $nextButton.find("img");
					//set labels, clases & id
					$nextButton.text(subscribeOnlineLang["delete"]).prepend($nextButtonImage.attr({
							src: TEMPLATE_PATH + "/images/subscribeDelete.png",
						})
					).data("subscribe-id", subscribeId).addClass("unSubscribe");
				});
				
			});

		}
	};

	//re buttons
	subscribeOnline = function(jsonData){

		if(typeof jsonData == "undefined" || jsonData == ""){
			if(typeof subscribeOnlineAjaxDir != "undefined" && subscribeOnlineAjaxDir != ""){
				$.getJSON(subscribeOnlineAjaxDir + "/ajax.php?act=getSubscribeItems&site_id=" + SITE_ID, function(jData){
					//update buttons
					subscribeButtonReload(jData);
				});
			}
		}

		//update buttons
		subscribeButtonReload(jsonData);

	};
});