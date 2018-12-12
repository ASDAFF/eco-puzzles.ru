var developTools = function (){
	if($("#dw_develop_tools").length > 0) {
	 	re_create_dw_tools();
	}else{
		create_dw_tools();
	}
}


var create_dw_tools = function(){
	$("body")
		.append(
			$('<div />', {
				id : "dw_develop_tools"
			}).css({
				"position" : "fixed",
				"bottom" : "240px",
				"right" : 0,
				"background-color" : "#000",
				"width" : "120px",
				"height" : "80px",
				"z-index": "99999"
			}).append(
				$("<span />", {
					id: "dw_develop_tools_w"
				}).html(
					" screen w: " + window.innerWidth + " px"
				).css({
					"margin-top" : "12px",
					"padding-left" : "4px",
					"color" : "#fff",
					"display" : "block"
				})
			).append(
				$("<span />", {
					id: "dw_develop_tools_h"
				}).html(
					" screen h: " + window.innerHeight + " px"
				).css({
					"padding-left" : "4px",
					"color" : "#fff",
					"display" : "block",
					"margin-top" : "0px",
				})
			).append(
				$("<span />", {
					id: "dw_develop_tools_s"
				}).html(
					" scroll Y: 0px"
				).css({
					"padding-left" : "4px",
					"color" : "#fff",
					"display" : "block",
					"margin-bottom" : "12px",
				})
			)
		);
}

var re_create_dw_tools = function(){
	var $dw_develop_tools_w = $("#dw_develop_tools_w");
	var $dw_develop_tools_h = $("#dw_develop_tools_h");
	$dw_develop_tools_w.html(
		" screen w: " + window.innerWidth + " px"
	);
	$dw_develop_tools_h.html(
		" screen h: " + window.innerHeight + " px"
	);
}


var developToolsScroll = function(event){
	$("#dw_develop_tools_s").html(" scroll Y: " + event.currentTarget.scrollY + "px");
};


$(window).on("resize ready", developTools);
$(window).on("scroll", developToolsScroll)