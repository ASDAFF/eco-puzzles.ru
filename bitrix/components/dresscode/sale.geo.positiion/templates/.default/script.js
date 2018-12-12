$(function(){

	//global vars
	var ajaxTimeOutId;

	//jquery vars
	$resContainer = $(".geo-location-window-search-values");

	if("getPositionIncludeApi" in window && getPositionIncludeApi === true){

		// fields
		// jsonObject.isHighAccuracy,
		// jsonObject.latitude,
		// jsonObject.longitude,
		// jsonObject.country,
		// jsonObject.region,
		// jsonObject.city,
		// jsonObject.zoom

		function yandex_init(){

		    var geolocation = ymaps.geolocation;

		    if(geolocation.city){
			    $.getJSON(geoPositionAjaxDir + "/ajax.php", {
			    	act: "userPosition",
			    	latitude: geolocation.latitude,
			    	longitude: geolocation.longitude,
					city: geolocation.city,
					country: geolocation.country,
					isHighAccuracy: geolocation.isHighAccuracy,
					region: geolocation.region,
					zoom: geolocation.zoom
			    }, function(jsonObject){
			    	if(jsonObject["ERROR"] != "Y"){
				    	//set values
				    	$('.geo-location-window-list-item-link[data-id="' + jsonObject.locationID + '"]').trigger("click");
				    	$(".user-geo-position-value-link, .geo-location-window-city-value").html(jsonObject.city);
				    	$(".geo-location-window-search-input").val(jsonObject.city);
				    }else{
				    	showLocationWindow();
				    }
			    });
			}else{
				showLocationWindow();
			}

		}

		function sypex_init(){
			$.getJSON("//api.sypexgeo.net/", function(json){
				if(typeof(json["city"]["name_ru"]) != "undefined"){
				    $.getJSON(geoPositionAjaxDir + "/ajax.php", {
				    	act: "userPosition",
				    	latitude: json["city"]["lat"],
				    	longitude: json["city"]["lon"],
						city: json["city"]["name_ru"],
						country: json["country"]["name_ru"],
						isHighAccuracy: false,
						region: json["region"]["name_ru"],
						zoom: false
				    }, function(jsonObject){
				    	if(jsonObject["ERROR"] != "Y"){
					    	//set values
					    	$('.geo-location-window-list-item-link[data-id="' + jsonObject.locationID + '"]').trigger("click");
					    	$(".user-geo-position-value-link, .geo-location-window-city-value").html(jsonObject.city);
					    	$(".geo-location-window-search-input").val(jsonObject.city);
					    }else{
					    	showLocationWindow();
					    }
				    });
				}else{
					showLocationWindow();
				}
			});
		}

		if(geoPositionEngine == "YANDEX"){

			//load yandex map script
			var yandexMapLoader = document.createElement("script");
			yandexMapLoader.src = "//api-maps.yandex.ru/2.0/?load=package.standard&lang=ru-RU";
			yandexMapLoader.className = "yaMapLoaderScript";
			document.body.appendChild(yandexMapLoader);
			yandexMapLoader.onload = function(){
				if(typeof ymaps == "object" && typeof ymaps.ready == "function"){
					ymaps.ready(yandex_init);
				}
			}

			//check 1 sec for load ya script
			setTimeout(function(){
				if(typeof ymaps != "object"){
					$(".yaMapLoaderScript").remove();
				}
			}, 1000);

		}

		else{
			sypex_init();
		}

	}

	var getSearchCity = function($input, query){

		//loader
		$input.addClass("loading");

		//clear container
		$resContainer.empty();

		//get location list
		$.getJSON(geoPositionAjaxDir + "/ajax.php?act=locSearch&query=" + encodeURI(query), function(jsonData){
			$input.removeClass("loading");
			if(jsonData["ERROR"] != "Y"){
				$.each(jsonData, function(i, arValues){
					$resContainer.append(
						$("<div />", {class: "geo-location-list-item"}).append(
							$("<a />",{class: "geo-location-list-item-link"}).html(arValues["COUNTRY_NAME"] + ", " + arValues["CITY_NAME"]).attr("href", "#").data("id", arValues["ID"]).data("parse-value", arValues["CITY_NAME"])
						)
					);
				});
			}
		});

	};

	var pressSearchField = function(event){

		var $this = $(this);
		var thisValue = $this.val();

		if(thisValue.length > 1 && !clearTimeout(ajaxTimeOutId)){
			ajaxTimeOutId = setTimeout(
				function(){
					getSearchCity($this, thisValue)
				}, 350
			);
		}

	};

	var selectLocationFromFastView = function(event){

		var $this = $(this);
		var thisID = $this.data("id");
		var thisValue = $this.data("parse-value");

		$(".geo-location-window-search-input").val(thisValue).data("id", thisID);
		$(".geo-location-window-city-value").html(thisValue);

		var $locationWindowList = $(".geo-location-window-list");
		var $locationWindowListLinks = $locationWindowList.find(".geo-location-window-list-item-link").removeClass("selected");

		$locationWindowListLinks.each(function(index, el) {
			var $nextElement = $(el);
			if($nextElement.data("id") == thisID){
				$nextElement.addClass("selected");
				return false;
			}
		});

		$(".geo-location-window-button").removeClass("disabled").addClass("modifed");

		$resContainer.empty();

		return event.preventDefault();

	};

	var setLocationFromServer = function(event){

		var $this = $(this).addClass("loading");

		$.getJSON(geoPositionAjaxDir + "/ajax.php", {
			act: "setLocation",
			locationID: $(".geo-location-window-search-input").data("id")
		}, function(jsonData){
			if(jsonData["SUCCESS"] == "Y"){
				window.location.reload();
			}
		});

		return event.preventDefault();

	};

	var showLocationWindow = function(){
		if(getCookie("locationWindowClose") != "Y"){
			$("#geo-location-window").removeClass("hidden");
		}
	};

	var openLocationWindow = function(event){
		$("#geo-location-window").removeClass("hidden").show();
		return event.preventDefault();
	};

	var closeLocationWindow = function(event){
		var currentDate = new Date(new Date().getTime() + 128000 * 1000);
		document.cookie = "locationWindowClose=Y; path=/; expires=" + currentDate.toUTCString();
		$("#geo-location-window").hide();
		return event.preventDefault();
	};

	function getCookie(name){

		//vars
		var cookie = " " + document.cookie;
		var search = " " + name + "=";
		var setStr = null;
		var offset = 0;
		var end = 0;

		if(cookie.length > 0){
			offset = cookie.indexOf(search);
			if (offset != -1) {
				offset += search.length;
				end = cookie.indexOf(";", offset)
				if (end == -1) {
					end = cookie.length;
				}
				setStr = unescape(cookie.substring(offset, end));
			}
		}

		return(setStr);

	}

	$(document).on("keyup", ".geo-location-window-search-input", pressSearchField);
	$(document).on("click", ".geo-location-list-item-link", selectLocationFromFastView);
	$(document).on("click", ".geo-location-window-list-item-link", selectLocationFromFastView);
	$(document).on("click", ".geo-location-window-button", setLocationFromServer);
	$(document).on("click", ".geo-location-window-exit", closeLocationWindow);
	$(document).on("click", ".user-geo-position-value-link", openLocationWindow);

});
