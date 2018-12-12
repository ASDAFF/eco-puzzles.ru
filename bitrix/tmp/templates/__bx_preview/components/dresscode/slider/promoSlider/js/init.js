$(function() {

	$("#slider").dwSlider({
		afterResize: setSliderHeight, // callback
		afterLoad: setSliderHeight, // callback
		timeLine: false,
		responsive: true,
		delay: 5000,
		speed: 1000
	});

	//callBack functions
	function setSliderHeight(link) {

		function setHeight() {
			return false;
		}

		setHeight();

	}

	//
	$("#slider .sliderContent").removeClass("loading").show();

});