$(function(){
	var $authForm = $(".bx-auth-form");

	var authFormSubmit = function(event){
		var $formFields = $authForm.find("input").removeClass("error");
		var emptyFields = false;

		$formFields.each(function(i, nextElement){
			var $nextElement = $(nextElement);
			if($nextElement.val() == ""){
				$nextElement.addClass("error");
				emptyFields = true;
			}
		});

		if(emptyFields){
			return event.preventDefault();
		}

	};

	$authForm.on("submit", authFormSubmit);
});