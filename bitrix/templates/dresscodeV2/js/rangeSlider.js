(function($) {
	jQuery.fn.rangeSlider = function(options) {
		var options = $.extend({
			step: 1,
		}, options);

		return this.each(function() {
			var mainObject = $(this);
			var handler = mainObject.find(".handler");
			var slider = mainObject.find(".slider");
			var mousePush = false;

			var offsetObject = {
				handler: handler.offset()
			};

			var widthObject = {
				main: slider.width()
			}

			var blackout = {
				left: mainObject.find(".blackoutLeft"),
				right: mainObject.find(".blackoutRight")
			};

			var buttons = {
				left: $(options["leftButton"]),
				right: $(options["rightButton"])
			}

			var input = {
				left: $(options["inputLeft"]),
				right: $(options["inputRight"])
			};

			var percent = {
				range: ((options["max"] - options["min"]) / 100),
				scroll: (widthObject.main / 100)
			};

			var reCalculate = function(part) {
				var append = part != "left" ? widthObject.main : 0;
				var shift = Math.abs((input[part].val() - options["min"]) * 100 / (options["max"] - options["min"]) * percent.scroll - append) + "px";
				blackout[part].width(shift);
			};

			var normalize = function(i) {
				return i >= 0 ? i : 0;
			}

			//set current position

			if (input.left.val() != options["min"]) {
				reCalculate("left");
			}

			if (input.right.val() != options["max"]) {
				reCalculate("right");
			}

			input.left.change(function(e) {
				if (parseInt(input.left.val()) >= parseInt(input.right.val())) {
					input.left.val(parseInt(input.right.val()) - 1);
				} else if (parseInt(input.left.val()) < options["min"]) {
					input.left.val(options["min"]);
				}
				reCalculate("left");
			});

			input.right.change(function(e) {
				if (parseInt(input.right.val()) <= parseInt(input.left.val())) {
					input.right.val(parseInt(input.left.val()) + 1);
				} else if (parseInt(input.right.val()) > options["max"]) {
					input.right.val(options["max"]);
				}
				reCalculate("right");
			});

			handler.on("mousedown", function(e) {
				if (mousePush == false) { // chrome ff click 
					var part = widthObject.main / 2 > e.pageX - offsetObject.handler.left ? "left" : "right";
					var move = part == "left" ? e.pageX - offsetObject.handler.left : widthObject.main - (e.pageX - offsetObject.handler.left);
					var inputVal = Math.round(Math.abs((part == "left" ? options["min"] : -options["max"]) + (normalize(move) / percent.scroll * percent.range)));
					blackout[part].addClass("trans").css("width", normalize(move));
					setTimeout(function() {
						blackout[part].removeClass("trans");
					}, 300)
					input[part].val(inputVal).trigger("change");
				} else {
					mousePush = false;
				}
			
			});

			buttons.left.mousedown(function(e) {
				buttons.left.active = true;
				buttons.left.clickStart = e.pageX;
				buttons.left.widthStart = parseInt(blackout["left"].width());
				e.stopImmediatePropagation();
				e.preventDefault();
			});

			buttons.right.mousedown(function(e) {
				buttons.right.active = true;
				buttons.right.clickStart = e.pageX;
				buttons.right.widthStart = parseInt(blackout["right"].width());
				e.stopImmediatePropagation();
				e.preventDefault();
			});

			$(document).on({
				mousemove: function(e) {
					if (buttons.right.active) {
						var mouseMove = buttons.right.widthStart + (buttons.right.clickStart - e.pageX);
						if (mouseMove > widthObject.main - blackout.left.width() || mouseMove < 0) return false;
						input.right.val(Math.round(options["max"] - (mouseMove / percent.scroll * percent.range))).trigger("change");
						blackout["right"].width(mouseMove);
						mousePush = true;
					} else if (buttons.left.active) {
						var mouseMove = buttons.left.widthStart - (buttons.left.clickStart - e.pageX);
						if (mouseMove > widthObject.main - blackout.right.width() || mouseMove < 0) return false;
						input.left.val(Math.round(options["min"] + (mouseMove / percent.scroll * percent.range))).trigger("change");
						blackout["left"].width(mouseMove);
						mousePush = true;
					}

				},
				mouseup: function(e) {
					buttons.left.active = buttons.right.active = mousePush = false;
				}
			});

			$(window).resize(function() {
				widthObject.main = slider.width();
				percent.scroll = widthObject.main / 100;
			});

		});

	};
})($);