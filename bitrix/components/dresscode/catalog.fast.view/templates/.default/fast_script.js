var fastViewInitPictureCarousel;
var fastViewInitPictureSlider;
var createFastView;

var initFastViewApp = function(){

	//vars
	var $appFastView = $("#appFastView");
	var $appFastViewPictureLoupe = false;
	var $pictureCarouselParentItems;
	var $pictureCarouselItem;
	var imageSliderCurrentPosition = 0;
	var appFastViewPictureLoupeWidth = 0;
	var appFastViewPictureLoupeHeight = 0;
	var appFastViewPictureLoupeOffset = 0;

	var appFastViewPictureLoupeImage;
	var appFastViewPictureLoupeImageWidth;
	var appFastViewPictureLoupeImageHeight;
	var appFastViewPictureLoupeImageLoaded;

	//functions
	createFastView = function(){
		fastViewInitPictureSlider();
		fastViewInitPictureCarousel();
	};

	fastViewInitPictureSlider = function(){
		
		//vars
		var $this = $appFastView.find(".appFastViewPictureSlider");
		var $thisContainer = $this.find(".appFastViewPictureSliderItems");
		var $thisContainerItems = $this.find(".appFastViewPictureSliderItem");

		var _thisItemsCount = $thisContainerItems.length;
		
		imageSliderCurrentPosition = 0;

		//set styles
		$thisContainer.css({
			"width" : _thisItemsCount * 100  + "%"
		});

		$thisContainerItems.css({
			"width" : 100 / _thisItemsCount + "%"
		});

	};

	fastViewInitPictureCarousel = function(){
		
		//vars
		var $this = $appFastView.find(".appFastViewPictureCarousel");
		var $thisContainer = $this.find(".appFastViewPictureCarouselItems");
		var $thisContainerItems = $this.find(".appFastViewPictureCarouselItem");
		var $thisLeftControlButton = $this.find(".appFastViewPictureCarouselLeftButton").show();
		var $thisRightControlButton = $this.find(".appFastViewPictureCarouselRightButton").show();

		var _thisItemsCount = $thisContainerItems.length;

		$pictureCarouselItem = $thisContainerItems;
		$pictureCarouselParentItems = $thisContainer;

		if(_thisItemsCount > 0){

			//set styles
			$thisContainer.css({
				"width" : _thisItemsCount * 100  + "%"
			});

			$thisContainerItems.css({
				"width" : (100 / _thisItemsCount) / 3 + "%" // 3 = count visible elements 
			});

			$thisContainerItems.eq(0).find("a").addClass("active");
 
			if(_thisItemsCount <= 3){
				$thisLeftControlButton.hide();
				$thisRightControlButton.hide();
			}

		}

	};

	var changeActiveCarouselPicture = function(event){
		
		var $this = $(this);
		var $thisLink = $this.find("a");
		
		if(!$thisLink.hasClass("active")){
			
			var $appFastViewSliderItems = $appFastView.find(".appFastViewPictureSliderItems");
			var $appFastViewCarouselItemsLinks = $appFastView.find(".appFastViewPictureCarouselItemLink").removeClass("active");
			
			$appFastViewSliderItems.css({
				"left": "-" + $this.index() * 100 + "%"
 			});

 			imageSliderCurrentPosition = $this.index();
 			$thisLink.addClass("active");

		}

		event.preventDefault();
	};

	var carouselControlHandlerLeft = function(event){
		
		var carouselMovePosition = 0;
		var repeatCarousel = false;

		if(--imageSliderCurrentPosition < 0){
			imageSliderCurrentPosition = $pictureCarouselItem.length -1;
			carouselMovePosition = imageSliderCurrentPosition -2;
			repeatCarousel = true;

		}else{
			carouselMovePosition = imageSliderCurrentPosition;
			repeatCarousel = false;
		}

		if(imageSliderCurrentPosition + 1 < $pictureCarouselItem.length -1 || repeatCarousel == true){
			$pictureCarouselParentItems.css({
				"left": "-" + carouselMovePosition * 100 / 3 + "%"
			});
		}

		$pictureCarouselItem.eq(imageSliderCurrentPosition).trigger("click");

		event.preventDefault();
	
	};

	var carouselControlHandlerRight = function(event){
		
		var carouselMovePosition = 0;

		if(++imageSliderCurrentPosition > $pictureCarouselItem.length -1){
			imageSliderCurrentPosition = 0;
		}else{
			if($pictureCarouselItem.length -1 - imageSliderCurrentPosition < 2){
				carouselMovePosition = $pictureCarouselItem.length -3;
			}else{
				carouselMovePosition = imageSliderCurrentPosition;
			}
		}

		$pictureCarouselParentItems.css({
			"left": "-" + carouselMovePosition * 100 / 3 + "%"
		});	
		
		$pictureCarouselItem.eq(imageSliderCurrentPosition).trigger("click");
		
		event.preventDefault();
	
	};

	var closeFastView = function(event){
		$("#foundation").removeClass("blurred");
		$appFastView.hide();
		event.preventDefault();
	};

	var fastViewLoupeInit = function(event){
		
		var $this = $(this);
		var $thisPicture = $this.find(".appFastViewPictureSliderItemPicture");
		
		if($this.data("loupe-picture")){

			appFastViewPictureLoupeImage = new Image();  
			appFastViewPictureLoupeImage.src = $this.data("loupe-picture");   
			
			appFastViewPictureLoupeImage.onload = function(){
				
				if(!$appFastViewPictureLoupe){
					$appFastViewPictureLoupe = $("<div />", {class: "appFastViewPictureLoupe"});
					$this.append($appFastViewPictureLoupe);
				}

				$appFastViewPictureLoupe.css({
					"background-image": "url(" + $this.data("loupe-picture") + ")"
				}); 

				appFastViewPictureLoupeImageWidth = this.width;
				appFastViewPictureLoupeImageHeight = this.height;
				appFastViewPictureLoupeWidth = $thisPicture.innerWidth();
				appFastViewPictureLoupeHeight = $thisPicture.innerHeight();
				appFastViewPictureLoupeOffset = $thisPicture.offset();

				appFastViewPictureLoupeImageLoaded = true;

			} 

		}else{
			console.error("data loupe picture is empty!");
		}

	};		

	var fastViewLoupeMove = function(event){

		if(appFastViewPictureLoupeImageLoaded || $appFastViewPictureLoupe == false){

			var $this = $(this);

			var mouseMathPositionX = event.pageX - appFastViewPictureLoupeOffset.left;
			var mouseMathPositionY = event.pageY - appFastViewPictureLoupeOffset.top;

			if($appFastViewPictureLoupe != false && 
			    mouseMathPositionX < 0 || mouseMathPositionX > appFastViewPictureLoupeWidth || 
			    mouseMathPositionY < 0 || mouseMathPositionY > appFastViewPictureLoupeHeight
			){
				fastViewLoupeClose();
			}else{
				
				if($appFastViewPictureLoupe == false){
					$this.trigger("mouseover");
				}else{

					var offsetPercentX = mouseMathPositionX * 100 / appFastViewPictureLoupeWidth;
					var offsetPercentY = mouseMathPositionY * 100 / appFastViewPictureLoupeHeight;

					var backgroundPositionX = appFastViewPictureLoupeImageWidth * offsetPercentX / 100 - 75;
					var backgroundPositionY = appFastViewPictureLoupeImageHeight * offsetPercentY / 100 - 75;

					backgroundPositionX = backgroundPositionX >= 0 ? backgroundPositionX : 0;
					backgroundPositionY = backgroundPositionY >= 0 ? backgroundPositionY : 0;

					backgroundPositionX = backgroundPositionX > appFastViewPictureLoupeImageWidth - 150 ? appFastViewPictureLoupeImageWidth - 150 : backgroundPositionX;
					backgroundPositionY = backgroundPositionY > appFastViewPictureLoupeImageHeight - 150 ? appFastViewPictureLoupeImageHeight - 150 : backgroundPositionY;

					$appFastViewPictureLoupe.css({
						backgroundPosition: "-" + backgroundPositionX + "px " + "-" + backgroundPositionY + "px",
						left: offsetPercentX + "%",
						top: offsetPercentY + "%"
					});

				}

			}
		}

	};

	var fastViewLoupeClose = function(){
		if($appFastViewPictureLoupe !== false){
			$appFastViewPictureLoupe.remove();
			$appFastViewPictureLoupe = false;

			appFastViewPictureLoupeImage = false;
			appFastViewPictureLoupeImageWidth = false;
			appFastViewPictureLoupeImageHeight = false;
			appFastViewPictureLoupeImageLoaded = false;
		}
	}

	//binds
	$(document).on("click", "#appFastView .appFastViewExit", closeFastView);
	$(document).on("click", "#appFastView .appFastViewPictureCarouselItem", changeActiveCarouselPicture);
	$(document).on("click", "#appFastView .appFastViewPictureCarouselLeftButton", carouselControlHandlerLeft);
	$(document).on("click", "#appFastView .appFastViewPictureCarouselRightButton", carouselControlHandlerRight);
	$(document).on("mousemove", "#appFastView .appFastViewPictureSliderItemLink", fastViewLoupeMove);
	$(document).on("mouseover", "#appFastView .appFastViewPictureSliderItemLink", fastViewLoupeInit);
	$(document).on("mouseleave", "#appFastView .appFastViewPictureSliderItemLink", fastViewLoupeClose);
	
	//start
	createFastView();

};