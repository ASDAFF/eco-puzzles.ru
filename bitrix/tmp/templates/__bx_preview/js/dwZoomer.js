$(function(){
	
	var windowOpened = false;
	var openWindow = function(event){

		var $this = $(this);
		var picturePath = $this.data("path");

		if(picturePath){
		
			$("#appZoomWindow").remove();

			var $body = $("body");
			var $appWindow = $("<div />", {id: "appZoomWindow"});
			var $appZoomContainer = $("<div />", {class: "appZoomContainer"});
			var $appZoomContainerImage = $("<img />", {class: "appZoomContainerImage", src: picturePath});
			var $appZoomContainerExit = $("<a />", {class: "appZoomContainerExit", href: "#"});

		    $appWindow.css({
		    	background: "rgba(0, 0, 0, 0.4)",
		    	overflow: "auto",
			    position: "fixed",
			    height: "100%",
			    width: "100%",
			    zIndex: "40",
			    left: "0px",
			    top: "0px"
			});

		    $appZoomContainer.css({
			    boxShadow: "1px 1px 12px rgba(60, 60, 60, 0.3)",
			    transform: "translateY(-50%) translateX(-50%)",
			    backgroundColor: "#ffffff",
		    	display: "inline-block",
		    	position: "absolute",
			    textAlign: "center",
			    padding: "24px",
			    left: "50%",
			    top: "50%",
		    });

		    $appZoomContainerImage.css({
		    	verticalAlign: "middle",
		    	maxHeight: "85vh",
		    	maxWidth: "85vw",
		    });

		    $appZoomContainer.append($appZoomContainerExit).addClass("loading");
		    $appWindow.append($appZoomContainer)

	        if(picturePath){
	            var newImage = new Image();
	            newImage.src = picturePath;
	            $(newImage).one("load", function(){
	            	$appZoomContainer.append($appZoomContainerImage).removeClass("loading");
	            });
	        }

			$body.append($appWindow).on("keyup.zoomer", function(event){
			    if(event.keyCode == 27){
			    	$("#appZoomWindow").remove();
			    	$body.off("keyup.zoomer");
			    	windowOpened = false;
			    }
			});

			windowOpened = true;

		}else{
			console.error("picture path is empty");
		}

		return event.preventDefault();

	};

	var closeWindow = function(event){
		
		if(windowOpened){
			$("#appZoomWindow").remove();
			windowOpened = false;
			event.preventDefault();
		}

	};

	$(document).on("click", ".oZoomer", openWindow);
	$(document).on("click", ".appZoomContainerImage, .oZoomer", function(event){event.stopImmediatePropagation();});
	$(document).on("click", "html", closeWindow);

});