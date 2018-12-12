var __menuTimeoutID;

$(window).on("ready", function(event){

	var __active = "activeDrop";
	var __menuID = "leftMenu";
	var __opener = "eChild";
	var __drop	 = "drop";

	var $_self = $("#" + __menuID);
	var $_eChild = $_self.children("." + __opener);

	var openChild = function(){

		var $_this = $(this);

		$_eChild.removeClass(__active).find("." + __drop).hide();
		$_this.addClass(__active).find("." + __drop).css("display", "table");

		return clearTimeout(__menuTimeoutID);

	}

	var closeChild = function(){
		var $_captureThis = $(this);
		__menuTimeoutID = setTimeout(function(){
			$_captureThis.removeClass(__active).find("." + __drop).hide();
		}, 500);
	}

	$_eChild.hover(openChild, closeChild);

});