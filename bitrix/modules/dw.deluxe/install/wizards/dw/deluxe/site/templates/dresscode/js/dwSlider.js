(function($) {
    jQuery.fn.dwSlider = function(options) {
        var options = $.extend({
            rightButton: ".sliderBtnRight",
            leftButton: ".sliderBtnLeft",
            afterResize: false,
            responsive: false,
            secondDots: false,
            afterLoad: false,
            timeLine: false,
            autoMove: true,
            delay: 3500,
            severity: 8,
            touch: true,
            speed: 500,
        }, options);

        var link = $(this).css({"overflow" : "hidden", "position" : "relative"});
        var slideBox = link.find(".slideBox").css({"position" : "relative", "left": "0px", "padding" : "0px", "cursor" : options["touch"] ? "move" : "default", "margin" : "0px"});
        
        //check for extra div       
        if(slideBox.children("div").length > 0){
            var slideElements = slideBox.children("div").children("li");  
        }else{
            var slideElements = slideBox.children("li");
        }
        
        var leftButton = $(options["leftButton"]);
        var rightButton = $(options["rightButton"]);
        var currentPosition = 0;
        var moveValueFx = 0;
        var timeoutID = 0;
        var moveValue = 0;

        if(options["secondDots"] != false){
            var secondDots = $(options["secondDots"]).children("li");
        }

        slideElements.css({
            "position" : "relative",
            "list-style" : "none",
            "float" : "left",
            "width" : "100%"
        });

        //check length
        options["touch"] = slideElements.length > 1 &&  options["touch"] == true ? true : false;

        if(options["touch"]){
            var slideElementWidth = slideElements.width();
            var slideBoxWidth = ((slideElements.length - 1) * slideElementWidth);
            var slideStartPosition = false;
            var clickPositionX = false;
            var clickPositionY = false;
            var clickControl = false;
        }

        if(options["touch"]){

            slideBox.on("mousedown touchstart", function(e){
                timeoutID && clearTimeout(timeoutID);
                slideElementWidth = slideElements.width();
                slideBoxWidth = ((slideElements.length - 1) * slideElementWidth);
                slideStartPosition = parseInt(slideBox.css("left"), 10);
                clickPositionX = e.type == "touchstart" ? e.originalEvent.touches[0].pageX : e.pageX;
                clickPositionY = e.type == "touchstart" ? e.originalEvent.touches[0].pageY : e.pageY;
                if(e.type == "mousedown"){
                    e.preventDefault();
                }
                return clickControl = true;
            });

            $(document).on("mousemove touchmove", function(e) {
                if(clickControl == true){
                    var curTouchPosX = e.type == "touchmove" ? e.originalEvent.touches[0].pageX : e.pageX;
                    var curTouchPosY = e.type == "touchmove" ? e.originalEvent.touches[0].pageY : e.pageY;
                    if(Math.abs(clickPositionY - curTouchPosY) < 450){
                        var touchExpression = clickPositionX - curTouchPosX;
                        if(Math.abs(touchExpression) == touchExpression){

                            var touchMoveEx = slideStartPosition - Math.abs(touchExpression);
                            touchMoveEx = slideBoxWidth > Math.abs(touchMoveEx) ? touchMoveEx : -(slideBoxWidth + ((Math.abs(touchMoveEx) - slideBoxWidth) / 6));
                            console.log("touchExpression" + touchExpression);
                            console.log("touchMoveEx" + touchMoveEx);
                            slideBox.finish().css({
                                left: touchMoveEx + "px"
                            });

                        }else{
                            var touchMoveEx = slideStartPosition + Math.abs(touchExpression);
                            touchMoveEx = 0 > touchMoveEx ? touchMoveEx : touchMoveEx / 6;
                            slideBox.finish().css({
                                left: touchMoveEx + "px"
                            });
                        }
                    }
                }
            });

            $(document).on("mouseup touchend", function(e) {
                if(clickControl == true){

                    clickControl = false;

                    var boxPosition = parseInt(slideBox.css("left"), 10);

                    if(Math.abs(Math.abs(slideStartPosition) - Math.abs(boxPosition)) < 10){
                        slideBox.finish().animate({
                            left: slideStartPosition + "px"
                        }, 200);

                    }else{
                        var touchMoveEx = (slideStartPosition - boxPosition > 0) ? -(Math.ceil(Math.abs(boxPosition) / slideElements.width()) * 100) : -(Math.floor(Math.abs(boxPosition) / slideElements.width()) * 100);
                        if((Math.abs(touchMoveEx) / 100) > (slideElements.length - 1)){
                            currentPosition = touchMoveEx = 0;
                        }else if(boxPosition > 0){
                            touchMoveEx = -((slideElements.length - 1) * 100);
                            currentPosition = (slideElements.length - 1);
                        }else{
                            currentPosition = (Math.abs(touchMoveEx) / 100);
                        }

                        slideBox.finish().animate({
                            left: touchMoveEx + "%"
                        }, 200);

                        if(slideElements.length > 1){
                            slideDotElements.removeAttr("class").eq(currentPosition).addClass("selected");
                            if(options["secondDots"] != false){
                                secondDots.removeAttr("class").eq(currentPosition).addClass("selected");
                            }
                        }

                    }

                }
            });
        }

        if(options.responsive === true){
            var slidePictures = slideBox.find("span");
        }

        slideBox.width(slideElements.length * 100 + "%");
        slideElements.width(100 / slideElements.length + "%");

        if(slideElements.length > 1){

            link.append(
                $("<ol>").addClass("pager").append(
                    function() {
                        var str = "";
                        for (var i = 1; i <= slideElements.length; i++) {
                            if (i == 1) {
                                str = str + '<li class="selected"></li>';
                            } else {
                                str = str + '<li></li>';
                            }
                        }
                        return str;
                    }
                )
            );

            if(options["timeLine"] === true){
               
                var timeLine = $("<ins/>").addClass("timeLine").css({
                      "position": "absolute",
                      "display": "block",
                      "height": "2px",
                      "width": "0px",
                      "left": "0px",
                      "top": "0px"
                    });
               
                link.append(
                    timeLine
                );
            
            }

            var slideDotBox = link.find(".pager");
            var slideDotElements = slideDotBox.children("li");
            slideDotBox.css("margin-left", "-" + (slideElements.length * 22 / 2) + "px");

        }else{
            leftButton.hide();
            rightButton.hide();
        }

        var slideAuto = function() {

            nextTimeLine(true);          
            slideMove(false);
            clearTimeout(timeoutID);
            timeoutID = setTimeout(function() {
                slideAuto();
            }, options.delay);
       
        }

        var slideMove = function(left) {

            if (left == true) {
                if (-1 == --currentPosition) {
                    currentPosition = slideElements.length - 1;
                }
                moveValue = "-" + currentPosition * 100 + "%";
            } else {
                if (slideElements.length == ++currentPosition) {
                    currentPosition = 0;
                    moveValue = 0;
                } else {
                    moveValue = "-" + (currentPosition * 100) + "%"
                }
            }
            slideAnimate(moveValue, moveValueFx);
        }

        var slideAnimate = function(value, fxValue) {
         
            slideBox.finish().animate({
                "left": value
            }, 550, "easeInCubic", function() {
                if(slideElements.length > 1){
                    slideDotElements.removeAttr("class").eq(currentPosition).addClass("selected");
                    if(options["secondDots"] != false){
                        secondDots.removeAttr("class").eq(currentPosition).addClass("selected");
                    }
                }
            });

        }

        var makeSlider = function(event){
           
            if(event.type == "ready"){
                nextTimeLine(true);
            }

            if(options.responsive === true){
                var wW = $(window).outerWidth();
                var ch = (wW > 1350) ? "large" : "normal";
                slidePictures.each(function(i, el){
                    $(el).css({
                        "background-image" : "url('" + $(el).data(ch) + "')"
                    });
                });
            }
        
            //callBack after resize

            if(event.type == "resize"){
                if(options["afterResize"] && typeof(options["afterResize"]) === "function"){
                    options["afterResize"](link);
                }
            }

        };


        var nextTimeLine = function(enableAnimate){
           
            if(timeLine != undefined){
               
                timeLine.finish().css({
                    "width" : "0%"
                });
               
                if(enableAnimate == true){
                    timeLine.animate({
                        "width" : "100%"
                    }, options.delay);
                }
           
            }
        
        };

        leftButton.on("click", function(e) {
            clearTimeout(timeoutID);
            slideMove(true);
            e.preventDefault();
        });

        rightButton.on("click", function(e) {
            clearTimeout(timeoutID);
            slideMove(false);
            e.preventDefault();
        });

        if(slideElements.length > 1){
            slideDotElements.on("click", function(e) {
                clearTimeout(timeoutID);
                currentPosition = $(this).index() - 1;
                slideMove(false);
            });
            if(options["secondDots"] != false){
                secondDots.on("click", function(e) {
                    clearTimeout(timeoutID);
                    currentPosition = $(this).index() - 1;
                    slideMove(false);
                    e.preventDefault();
                });
            }
        }

        timeoutID = setTimeout(function() {
            slideAuto();
        }, options.delay);

        link.hover(function(){
            clearTimeout(timeoutID);
            nextTimeLine(false);
        }, function(){
            timeoutID = setTimeout(function() {
                slideAuto();
            }, options.delay);
            nextTimeLine(true);
        });

        $(window).on("ready resize", makeSlider);
        
        //callBack after load

        if(options["afterLoad"] && typeof(options["afterLoad"]) === "function"){
            if(slideElements.length > 0){
                options["afterLoad"](link);
            }
        }

    };
})(jQuery);