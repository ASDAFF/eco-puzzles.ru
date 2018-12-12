	
	// global function var
	var startPictureElementSlider;
	
	$(function(){

		startPictureElementSlider = function(){

			var $pictureContainer = $("#pictureContainer");
			var $pictureSlider = $pictureContainer.find(".pictureSlider");
			var $pictureSliderElements = $pictureSlider.find(".item");

			var $moreImagesCarousel = $("#moreImagesCarousel");
			var $itemClickToEvent = $moreImagesCarousel.find(".item");

			var elementsCount = $pictureSliderElements.length;
			var currentPosition = 0;

			// add styles
			$pictureContainer.css({
				overflow: "hidden",
				width: "100%",
			});

			$pictureSlider.css({
				width: elementsCount * 100 + "%",
				position: "relative",
				overflow: "hidden",
				display: "table",
				left: "0px"
			});

			$pictureSliderElements.css({
				width: 100 / elementsCount + "%",
				display: "table-cell",
				position: "relative",
				textAlign: "center"
			});


			var slideCalcToMove = function(event){
							
				$this = $(this);
				
				if(!$this.hasClass("selected")){
					$this.siblings(".item").removeClass("selected").find("a").removeClass("zoom");
					$this.addClass("selected").find("a").addClass("zoom");
					event.stopImmediatePropagation();
				}
				
				return event.preventDefault(slideMove($this.index()));
			
			}

			var slideMove = function(to){
				
				$pictureSlider.animate({
					left: "-" + to * 100 + "%"
				}, 250);

				return true;
			
			};

			$itemClickToEvent.on("click", slideCalcToMove);
		}

		startPictureElementSlider(); // start slider =)

	});