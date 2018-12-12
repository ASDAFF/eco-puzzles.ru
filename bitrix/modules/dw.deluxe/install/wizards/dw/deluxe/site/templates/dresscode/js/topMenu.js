$(function(){
	
	var __menuSelector    = "#topMenu";
	var __menuBarClass    = "bar";
	var __menuSubClass    = "sub";
	var __menuActiveClass = "active";	

	var createMenu = function(event){

		// @event.data.reducedWidth
		// @event.data.maxMenuWidth

		var	$_self = $(__menuSelector),
			$_list = $_self.children("li:not(." + __menuBarClass + ")"),
			$_bar  = $_self.find("." + __menuBarClass),
			$_sub  = $_self.find("." + __menuSubClass),
			_enableBar = $_bar.length > 0 ? 1 : 0,
			_transElements = $_self.hasClass("transfered") ? 1 : 0;

		var $fn_createBar = function(){

			$_self.append(
				$("<li/>").addClass(__menuBarClass).append(
					$("<a/>").attr("href", "#").addClass("openEnder")
				).append(
					$("<ul/>").addClass(__menuSubClass)
				)
			);

			$_bar = $_self.find("." + __menuBarClass);
			$_sub = $_bar.find("." + __menuSubClass);

			return _enableBar++;
		};

		var $fn_elementsCopy = function(toggle){

			if(toggle === false){
				$_self.addClass("transfered").children("li").each(function(i){
					var $_this = $(this);
					if(!$_this.hasClass(__menuBarClass)){
						$_sub.append($_this.clone());
						$_this.remove();
					}
				});

				return _transElements++;

			}else{
				$_sub.children("li").each(function(i){
					var $_this = $(this);
					if(!$_this.hasClass(__menuBarClass)){
						$_self.append($_this.clone());
						$_this.remove();
					}
				});
				$_self.removeClass("transfered");
				$_bar.remove();
				
				$_list = $_self.children("li:not(." + __menuBarClass + ")");
			
				return _transElements-- - _enableBar--;
			}
		};

		if(document.body.clientWidth <= event.data.reducedWidth){
			!_enableBar && $fn_createBar();
			!_transElements && $fn_elementsCopy(false);
		}else{
			if(_transElements){
				$fn_elementsCopy(true);
			}

			if($_self.outerWidth() > event.data.maxMenuWidth){
				for (var i = $_list.length - 1; i >= 0; i--) {
					var $_this = $_list.eq(i);
					if($_self.outerWidth() <= event.data.maxMenuWidth){
						break;
					}else{
						!_enableBar && $fn_createBar();
						$_sub.prepend($_this.clone());
						$_this.remove();
					}
				};
			}
		}
	};

	var openMenu = function(event){
		$(this).closest("." + __menuBarClass).toggleClass(__menuActiveClass);
		event.preventDefault();
	};

	var closeMenu = function(event){
		$("#topMenu ." + __menuBarClass).removeClass(__menuActiveClass);
	};

	$(window).on("ready resize", {
		reducedWidth: 1250,
		maxMenuWidth: 800
	}, createMenu);
	
	$(document).on("click", "#topMenu ." + __menuBarClass, function(event){event.stopImmediatePropagation();});
	$(document).on("click", "#topMenu .openEnder", openMenu);
	$(document).on("click", "html", closeMenu);

});
