var __menuFirstOpenTimeoutID;
var __sectionMenuTimeoutID;
var __menuTimeoutID;

$(window).on("ready", function(event){

	//section menu vars
	var __sectionMenuActive = "activeDrop";
	var __sectionMenuMenuID = "menuCatalogSection";
	var __sectionMenuOpener = "menuSection";
	var __sectionMenuDrop	 = "drop";

	var $_sectionMenuSelf = $("#" + __sectionMenuMenuID);
	var $_sectionMenuEChild = $_sectionMenuSelf.children("." + __sectionMenuOpener);

	var sectionMenuOpenChild = function(){

		var $_this = $(this);

		__menuFirstOpenTimeoutID = setTimeout(function(){
			if($_this.is(":hover")){
				clearTimeout(__menuFirstOpenTimeoutID);
				$_sectionMenuEChild.removeClass(__sectionMenuActive).find("." + __sectionMenuDrop).hide();
				$_eChild.removeClass(__active).find("." + __drop).hide();
				$_this.addClass(__sectionMenuActive).find("." + __sectionMenuDrop).css("display", "table");
				return clearTimeout(__sectionMenuTimeoutID);
			}
		}, 300);

	}

	var sectionMenuCloseChild = function(){
		var $_captureThis = $(this);
		__sectionMenuTimeoutID = setTimeout(function(){
			$_captureThis.removeClass(__active).find("." + __drop).hide();
		}, 500);
	}

	$_sectionMenuEChild.hover(sectionMenuOpenChild, sectionMenuCloseChild);

	//main menu vars
	var __active = "activeDrop";
	var __menuID = "mainMenu";
	var __opener = "eChild";
	var __drop	 = "drop";

	var $_self = $("#" + __menuID);
	var $_eChild = $_self.children("." + __opener);

	var openChild = function(){

		var $_this = $(this);
		if(!$_this.hasClass("removed")){
		
			__menuFirstOpenTimeoutID = setTimeout(function(){
				if($_this.is(":hover")){
					clearTimeout(__menuFirstOpenTimeoutID);
					$_sectionMenuEChild.removeClass(__sectionMenuActive).find("." + __sectionMenuDrop).hide();
					$_eChild.removeClass(__active).find("." + __drop).hide();
					$_this.addClass(__active).find("." + __drop).css("display", "table");
					return clearTimeout(__menuTimeoutID);
				}
			}, 300);

		}

	}

	var closeChild = function(){
		var $_captureThis = $(this);
		__menuTimeoutID = setTimeout(function(){
			$_captureThis.removeClass(__active).find("." + __drop).hide();
		}, 500);
	}

	$_eChild.hover(openChild, closeChild);

	// slice menu func
	function sliceMainMenu(resize){
		var $mainMenu = $("#mainMenu");
		if(resize == true){
			$mainMenu.find(".removed").each(function(i, nextElement){
				var $nextElement = $(nextElement);
				$mainMenu.append(
					$nextElement.removeClass("removed")
				)
			});
			$mainMenu.find(".removedItemsLink").remove();
		}

		var $mainMenuItems = $mainMenu.children("li");
		var visibleMenuWidth = $mainMenu.width() - 100;
		var totalSumMenuWidth = 0;

		if(visibleMenuWidth > 700){

			$mainMenuItems.each(function(i, nextElement){
				var $nextElement = $(nextElement);
				totalSumMenuWidth += $nextElement.outerWidth(true);
				if(totalSumMenuWidth > visibleMenuWidth){
					$nextElement.addClass("removed");
				}
			});

			var $removedItems = $mainMenu.find(".removed");
			if($removedItems.length > 0){
				var $removedItemsList = $("<ul/>").addClass("removedItemsList");
				var $removedItemsLink = $("<li/>").addClass("removedItemsLink").append($("<a/>").attr("href", "#"));
				$removedItems.each(function(i, nextElement){
					var $nextElement = $(nextElement);
					$removedItemsList.append(
						$nextElement
					)
				});
				$mainMenu.append($removedItemsLink.append($removedItemsList));
				$removedItemsList.css({
					left: $removedItemsLink.offset().left + "px"
				});
			}
		}
	}

	// $("#mainMenu .removedItemsLink").hover(function(){})

	$(window).on("load", function(){
		sliceMainMenu(false);
	});

	$(window).on("resize", function(){
		sliceMainMenu(true);
	});

	var dropCatalog = function(event){
		$("#mainMenu").slideToggle();
		return event.preventDefault();
	};

	$(document).on("click", "#catalogSlideButton", dropCatalog);

});
