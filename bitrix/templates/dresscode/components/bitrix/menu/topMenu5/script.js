$(function(){

	var $sliceMenu = $("#subMenu");
	var $sliceMenuItems = $sliceMenu.children("li");

	var sliceMenuOpened = false;
	var resizeCacheWidth = 0;

	var sliceMenuOnline = function(resize){

		if(resize == true){

			$sliceMenu.find(".removed").each(function(i, nextElement){
				var $nextElement = $(nextElement);
				$sliceMenu.append(
					$nextElement.removeClass("removed")
				)
			});

			$sliceMenu.find(".removedItemsContainer").remove();

		}else{
			$sliceMenu.css("visibility", "visible");
		}

		var menuMaxWidth = $("#headerLine3 .headerLineMenu").width();
		var sumMenuItemsWidth = 70;

		if($(window).width() <= 1200){
			menuMaxWidth = 0;
		}

		$sliceMenuItems.each(function(i, nextElement){

			var $nextElement = $(nextElement);
				sumMenuItemsWidth += $nextElement.width();

			if(sumMenuItemsWidth >= menuMaxWidth){
				$nextElement.addClass("removed");
			}

		});

		//create drop down
		var $removedItems = $sliceMenu.find(".removed");

		if($removedItems.length > 0){

			var $removedItemsList = $("<ul/>").addClass("removedItemsList");
			var $removedItemsLink = $("<li/>").addClass("removedItemsContainer").append($("<a/>", {class: "removedItemsLink"}).attr("href", "#"));

			$removedItems.each(function(i, nextElement){
				var $nextElement = $(nextElement);
				$removedItemsList.append($nextElement);
			});

			$sliceMenu.append($removedItemsLink.append($removedItemsList));

		}

	}

	var openDropDownMenu = function(event){

		if(sliceMenuOpened == false){
			$("#subMenu .removedItemsList").show();
			sliceMenuOpened = true;
		}else{
			$("#subMenu .removedItemsList").hide();
			sliceMenuOpened = false;
		}

		return event.preventDefault();
	}

	var closeDropDownMenu = function(event){
		$("#subMenu .removedItemsList").hide();
		sliceMenuOpened = false;
	};

	$(document).on("click", ".removedItemsLink", openDropDownMenu);
    $(document).on("click", ".removedItemsList, .removedItemsLink", function(event){
    	return event.stopImmediatePropagation();
    });

	$(document).on("click", closeDropDownMenu);

	$(document).on("ready", function(){
		resizeCacheWidth = $(window).width();
		sliceMenuOnline(false);
	});

	$(window).on("resize", function(event){
		//fix mobile scroll/resize lag
		if(resizeCacheWidth != $(window).width()){
			resizeCacheWidth = $(window).width();
			sliceMenuOnline(true);
		}
	});

});