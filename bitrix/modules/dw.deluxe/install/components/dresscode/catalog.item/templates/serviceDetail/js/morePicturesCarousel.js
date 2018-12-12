	
	var startMorePicturesElementCarousel;

	$(function(){

		startMorePicturesElementCarousel = function(){

			//settings
			var maxVisibleElements = 3;

			var $moreImagesCarousel = $("#moreImagesCarousel").addClass("show");
			var $moreImagesSlideBox = $moreImagesCarousel.find(".slideBox");
			var $moreImagesItems = $moreImagesSlideBox.find(".item");

			var elementsCount = $moreImagesItems.length;
			var maxPosition = $moreImagesItems.length - maxVisibleElements;
			var currentPosition = 0;
			var startPosition = 0;

			$moreImagesItems.eq(0).addClass("selected")
									.find("a").addClass("zoom");

			if(elementsCount <= maxVisibleElements){
				$("#moreImagesRightButton, #moreImagesLeftButton").hide();
				startPosition = 100 / maxVisibleElements * ((maxVisibleElements - elementsCount) /2);
			}else{
				$("#moreImagesRightButton, #moreImagesLeftButton").show();
			}

			$moreImagesSlideBox.css({
				width: elementsCount * 100 + "%",
				left: startPosition + "%"
			});

			$moreImagesItems.css({
				width: 100 / elementsCount / maxVisibleElements + "%"
			});

			var carouselMoving = function(to){
				$moreImagesSlideBox.finish().animate({
					left: "-" + 100 / maxVisibleElements * to + "%"
				}, 200);
			};

			var leftMoveCarousel = function(event){
				if(--currentPosition < 0){
					currentPosition = maxPosition;
				}				
				return event.preventDefault(carouselMoving(currentPosition));
			};

			var rightMoveCarousel = function(event){
				if(++currentPosition > maxPosition){
					currentPosition = 0;
				}				
				return event.preventDefault(carouselMoving(currentPosition));
			};

			$(document).on("click", "#moreImagesRightButton", rightMoveCarousel);
			$(document).on("click", "#moreImagesLeftButton", leftMoveCarousel);
		}

		startMorePicturesElementCarousel();

	});