$(function(){

	//global vars
	var $txSwitcher = $(".txSwitcher");
	var $txSwitcherSettings = $(".txSwitcherSettings");
	var $txSwitcherSave = $txSwitcher.find(".switcherSave");

	//functions
	var toggleSwitcher = function(event){

		//create cookie
		var date = new Date(new Date().getTime() + 3660 * 1000);

		//open switcher
		if(!$txSwitcher.hasClass("opened")){
			$txSwitcher.addClass("opened");
			$txSwitcherSettings.addClass("active");
			document.cookie = "switcherOpened=Y; path=/; expires=" + date.toUTCString();
		}

		//close
		else{
			$txSwitcher.removeClass("opened");
			$txSwitcherSettings.removeClass("active");
			document.cookie = "switcherOpened=N; path=/; expires=" + date.toUTCString();
		}

		//block action
		return event.preventDefault();

	};

	var switchByLink = function(event){

		//vars

		//this button
		var $this = $(this);

		//parent container
		var $thisContainer = $this.parents(".switchByLink");

		//set clases
		$thisContainer.find(".switchByLinkItem").removeClass("selected");
		$this.parents(".switchByLinkItem").addClass("selected");

		//hide blocks by value
		checkDependenceBlocks();

		//block action
		return event.preventDefault();

	}

	//save settings
	var switcherSave = function(event){

		//j vars
		$switcherItems = $txSwitcher.find(".switchByLink, .switcherFile, .switcherSelect, .switcherCheckboxItems, .switcherInputText, .switcherInputTextB64");

		//vars
		ajaxFormData = new FormData();

		//each items
		$switcherItems.each(function(ix, nextItem){

			//get query item
			var $nextItem = $(nextItem);
			var nextItemSettingId = $nextItem.data("id").toString();
			var nextItemSettingValue = "";
			var nextItemIsFile = false;

			//check id
			if(typeof nextItemSettingId != "undefined" && nextItemSettingId != ""){

				//[check setting type]

				//links
				if($nextItem.hasClass("switchByLink")){
					nextItemSettingValue = $nextItem.find(".switchByLinkItem.selected a").data("value");
				}

				//select
				else if($nextItem.hasClass("switcherSelect")){
					nextItemSettingValue = $nextItem.val();
				}

				//multi checkbox
				else if($nextItem.hasClass("switcherCheckboxItems")){
					var $checkBoxCheckedItems = $nextItem.find(".switcherMultiCheckItem:checked");
					if($checkBoxCheckedItems.length > 0){
						nextItemSettingId = nextItemSettingId + "[]";
						nextItemSettingValue = new Array();
						$checkBoxCheckedItems.each(function(inx, nextElement){
							nextItemSettingValue.push($(nextElement).val());
						});
					}
				}

				//file
				else if($nextItem.hasClass("switcherFile")){

					//vars
					var $fileInput = $nextItem.find('input[type="file"]');
					var fileDataValue = $fileInput.data("value");

					//get file data
					if(checkVar($fileInput.val())){
						nextItemSettingValue = $fileInput.prop("files")[0];
						nextItemIsFile = true;
					}

					//save old value
					else if(checkVar(fileDataValue)){
						nextItemSettingValue = fileDataValue;
					}

				}

				//text input
				else if($nextItem.hasClass("switcherInputText")){
					var $textInput = $nextItem.find("input");
					if(checkVar($textInput.val())){
						nextItemSettingValue = $textInput.val();
					}
				}

				//hex
				else if($nextItem.hasClass("switcherInputTextB64")){

					//jquery vars
					var $textInput = $nextItem.find("input");

					//other vars
					var inputValue = $textInput.val();
					var base64String = "";

					//check empty
					if(checkVar(inputValue)){

						//convert to base64
						base64String = btoa(unescape(encodeURIComponent(inputValue)));

						//set value
						nextItemSettingValue = base64String;

					}

				}
			}

			//append to form data
			if(nextItemSettingValue != "" && nextItemSettingId != ""){

				//object append
				if(!nextItemIsFile && typeof nextItemSettingValue == "object"){
					$.each(nextItemSettingValue, function(inx, nextValue){
						ajaxFormData.append(nextItemSettingId, nextValue);
					});
				}

				//other strings
				else{
					ajaxFormData.append(nextItemSettingId, nextItemSettingValue);
				}

			}

		});

		if(!$.isEmptyObject(ajaxFormData)){
			sendSettings(ajaxFormData);
		}

		//block action
		return event.preventDefault();

	};

	//send request
	var sendSettings = function(ajaxFormData){

		//check vars
		if(!$.isEmptyObject(ajaxFormData)){

			//check component ajax path
			if(checkVar(settingsAjaxDir)){

				//append act
				ajaxFormData.append("act", "saveSettings");

				//add loader
				$txSwitcherSave.addClass("loading");

				//send data
				$.ajax({
					type: "POST",
					url: settingsAjaxDir + "/ajax.php",
					enctype: "multipart/form-data",
					data: ajaxFormData,
					processData: false,
					contentType: false,
					dataType: "json",
					async: false,
					cache: false,
					success: function(jsonData){
						if(checkVar(jsonData["SUCCESS"]) && jsonData["SUCCESS"] == "Y"){
							console.log("settings successfully saved");
							window.location.reload();
						}
						else{
							$txSwitcherSave.addClass("error").removeClass("loading");
							console.error("sendSettings: error");
							console.error(jsonData);
						}
					},
					error: function(jsonData){
						$txSwitcherSave.addClass("error").removeClass("loading");
						console.error(jsonData);
					}
				});

			}

		}

	};

	//show more checkbox
	var openAllCheckItems = function(event){

		//vars
		var $this = $(this);
		var $thisContainer = $this.parents(".switcherCheckboxItems");
		var $hideAllbutton = $thisContainer.find(".switcherHideAll");
		var $thisContainerItems = $thisContainer.find(".switcherCheckboxItem");

		//show hiddenitems
		$thisContainerItems.removeClass("hidden");
		$hideAllbutton.removeClass("hidden");
		$this.addClass("hidden");

		//block actions
		return event.preventDefault();

	};

	//hide more checkbox
	var closeAllCheckItems = function(event){

		//vars
		var $this = $(this);
		var $thisContainer = $this.parents(".switcherCheckboxItems");
		var $showAllbutton = $thisContainer.find(".switcherShowAll");
		var $thisContainerItems = $thisContainer.find(".switcherCheckboxItem");

		//show hiddenitems
		$thisContainerItems.slice(10).addClass("hidden");
		$showAllbutton.removeClass("hidden");
		$this.addClass("hidden");

		//block actions
		return event.preventDefault();

	};

	var reloadPropertyAjax = function($ajaxContainer, properties, propSort, paramName, iblockId){

		//check objects
		if(checkObject($ajaxContainer) && checkObject(properties)){

			//check vars
			if(checkVar(propSort) && checkVar(paramName) && checkVar(iblockId)){

				//vars
				var propIndex = 0;

				//clear select options
				$ajaxContainer.empty();

				//create options
				$.each(properties, function(i, nextItem){

					//item attributes
					var itemParams =  {
						class: "switcherCheckboxItem"
					}

					//item container
					var $appendItem = $("<div/>", itemParams);

					//hide others
					if(++propIndex > 10){
						$appendItem.addClass("hidden");
					}

					//checkbox attributes
					var checkboxParams = {
						class: "switcherMultiCheckItem",
						name: paramName,
						type: "checkbox",
						id: "multiCheck_" + nextItem["ID"]
					}

					if(nextItem["SORT"] <= propSort){
						checkboxParams["checked"] = "checked";
					}

					//create checkbox
					var $appendCheckbox = $("<input/>", checkboxParams).val(nextItem["ID"]);

					var labelParams = {
						for: "multiCheck_" + nextItem["ID"]
					}

					//create label
					var $appendLabel = $("<label/>", labelParams).text(nextItem["NAME"] + " [" + nextItem["ID"] + "]");

					//create edit button
					editParams = {
						target: "_blank",
						class: "settingsEdit",
						href: "/bitrix/admin/iblock_edit_property.php?ID=" + nextItem["ID"] + "&IBLOCK_ID=" + iblockId,
					}

					if(checkVar(settingsLang)){
						editParams["title"] = settingsLang["SETTINGS_PROPERTY_EDIT"];
					}

					var $appendEdit = $("<a/>", editParams);

					//join elements
					$appendItem.append($appendCheckbox).append($appendLabel).append($appendEdit);				

					//write
					$ajaxContainer.append($appendItem);

				});

				if(propIndex > 10){

					if(checkVar(settingsLang)){

						//buttons
						var $showAllbutton = $("<a/>", {class: "switcherShowAll", href: "#"}).text(settingsLang["SETTINGS_PROPERTY_SHOW_ALL"]);
						var $hideAllbutton = $("<a/>", {class: "switcherHideAll hidden", href: "#"}).text(settingsLang["SETTINGS_PROPERTY_HIDE_ALL"]);

						//write
						$ajaxContainer.append($showAllbutton).append($hideAllbutton);

					}

					//error
					else{
						console.error("reloadPropertyAjax: check component lang vars");
					}

				}

			}

			else{
				console.error("reloadPropertyAjax: check property vars");
			}

		}

		else{
			console.error("reloadPropertyAjax: check objects");
		}

	};

	var iblockSelectAjax = function($ajaxContainer, iblockId, propertySort, settingName){

		//check vars
		if(checkObject($ajaxContainer) && checkVar(iblockId) && checkVar(propertySort) && checkVar(settingName)){

			//remove old state
			$ajaxContainer.parents(".switcherAjaxContainer").removeClass("hidden");

			var ajaxObject = {
				"iblockId": iblockId,
				"act": "getPropertiesByIblock"
			};

			//check component ajax path
			if(checkVar(settingsAjaxDir)){

				//send data
				$.getJSON(settingsAjaxDir + "/ajax.php", ajaxObject, function(jsonData){
					
					if(checkVar(jsonData["SUCCESS"]) && jsonData["SUCCESS"] == "Y"){
						
						//check items
						if(checkObject(jsonData["PROPERTIES"])){
							reloadPropertyAjax($ajaxContainer, jsonData["PROPERTIES"], propertySort, settingName, iblockId);
						}

						else{
							console.log("iblockSelectAjax: properties is empty (iblockId: " + iblockId + ")");
						}

					}

					//hide block
					else if(checkVar(jsonData["HIDE_BLOCK"]) && jsonData["HIDE_BLOCK"] == "Y"){
						$ajaxContainer.parents(".switcherAjaxContainer").addClass("hidden");
					}

					else{
						console.error(jsonData);
					}

				});
			}

		}

		else{
			console.error("iblockSelectAjax: check vars");
		}

	};

	var afterIblockSelect = function(event, settingName, iblockId){

		//vars
		var $createProductPropertiesLink = $(".switcherCreateProductProperties");
		var $createSkuPropertiesLink = $(".switcherCreateSkuProperties");

		//check parameters
		if(checkVar(settingName) && checkVar(iblockId)){

			//change product data iblock id for create properties button
			if(settingName == "TEMPLATE_PRODUCT_PROPERTIES[]"){
				$createProductPropertiesLink.data("iblock-id", iblockId);
			}

			//change sku data iblock id for create properties button
			else if(settingName == "TEMPLATE_SKU_PROPERTIES[]"){
				$createSkuPropertiesLink.data("iblock-id", iblockId);
			}

		}

	};

	var iblockSelectHandler = function(event){

		//jquery vars
		var $this = $(this);
		var $ajaxContainerItems = $(event.data.containerClass + " .switcherCheckboxItems");

		//other
		var iblockId = $this.val();

		//reload property container
		iblockSelectAjax($ajaxContainerItems, iblockId, event.data.propertySort, event.data.settingName);

		//dependencies
		afterIblockSelect(event, event.data.settingName, iblockId);

	};

	var switcherChangeTab = function(event){

		//jquery vars
		var $this = $(this);
		var $switcherTabs = $(".switcherTabs");
		var $switcherTabItems = $switcherTabs.find(".switcherTab");
		var $switcherChangeTabsLinks = $switcherTabs.find(".switcherChangeTabItem a");

		//other vars
		var currentIndex = $this.parents(".switcherChangeTabItem").index();

		//remove clases
		$switcherTabItems.removeClass("active");
		$switcherChangeTabsLinks.removeClass("active");

		//add clases
		$this.addClass("active");
		$switcherTabItems.eq(currentIndex).addClass("active");

		//create cookie
		var date = new Date(new Date().getTime() + 3660 * 1000);
		document.cookie = "switcherActiveTabIndex=" + currentIndex + "; path=/; expires=" + date.toUTCString();

		//block actions
		return event.preventDefault();

	};

	var createWindowTableColumns = function(propertyResult, $containerToAppend){

		//create report 
		$.each(propertyResult, function(i, nextItem){

			//create columns array
			var arColumns = new Array();

			//push 1 column
			if(checkVar(nextItem["PROPERTY_NAME"])){
				arColumns.push({
					text: nextItem["PROPERTY_NAME"],
					title: nextItem["PROPERTY_NAME"],
					class: "switcherResultTableCell"
				});
			}

			//push 2 column
			if(checkVar(nextItem["PROPERTY_CODE"])){
				arColumns.push({
					text: nextItem["PROPERTY_CODE"],
					title: nextItem["PROPERTY_CODE"],
					class: "switcherResultTableCell"
				});
			}

			//push 3 column
			if(checkVar(nextItem["ERROR"]) &&  nextItem["ERROR"] == "Y"){
				arColumns.push({
					text: (checkVar(nextItem["PROPERTY_ALLREADY_CREATED"]) ? settingsLang["SETTINGS_PROPERTY_ALLREADY_CREATED"] : (checkVar($nextItem["ERROR_DATA"]) ? $nextItem["ERROR_DATA"] : settingsLang["SETTINGS_PROPERTY_ERROR"])),
					title: "",
					class: "switcherResultTableCell error"
				});
			}

			else if(checkVar(nextItem["SUCCESS"]) && nextItem["SUCCESS"] == "Y"){
				arColumns.push({
					text: settingsLang["SETTINGS_PROPERTY_SUCCESS_CREATED"],
					title: "",
					class: "switcherResultTableCell success"
				});							
			}

			if(checkObject(arColumns)){
				
				//create table row item
				var $newTableRow = $("<div/>", {class: "switcherResultTableRow"});

				//append created columns
				$.each(arColumns, function(ix, nextColumn){
					$newTableRow.append(
						$("<div/>", {class: nextColumn["class"], title: nextColumn["title"]}).text(nextColumn["text"])
					)
				});

				$containerToAppend.append($newTableRow);

			}

		});	

	}

	var createProductProperties = function(event){

		//jquery vars
		var $this = $(this);
		var $createWindow = $this.parents(".txSwitcherWindow");
		var $startContainer = $createWindow.find(".switcherStartContainer");
		var $errorContainer = $createWindow.find(".switcherErrorContainer");
		var $resultContainer = $createWindow.find(".switcherResultContainer");

		//other vars
		var iblockId = $createWindow.data("iblock-id");

		//
		if(checkVar(iblockId)){

			//add loader
			$createWindow.addClass("opened loading");

			//object to send
			var ajaxObject = {
				"iblockId": iblockId,
				"act": "createProductProperties"
			};

			//check component ajax path
			if(checkVar(settingsAjaxDir)){

				//send data
				$.getJSON(settingsAjaxDir + "/ajax.php", ajaxObject, function(jsonData){
					
				    //check success
					if(checkVar(jsonData["PRODUCT_PROPERTIES"]["SUCCESS"]) && jsonData["PRODUCT_PROPERTIES"]["SUCCESS"] == "Y" || checkVar(jsonData["SECTION_PROPERTIES"]["SUCCESS"]) && jsonData["SECTION_PROPERTIES"]["SUCCESS"] == "Y"){
						displayResult(jsonData);
					}

					else{
						displayError(jsonData);
					}

				});

			}

			else{
				displayError("createProductProperties: var settingsAjaxDir not set");
			}

		}

		else{
			displayError("createProductProperties: var iblockId not set");
		}

		//createProductProperties util functions
		function displayError(consoleStr){

			//clases job
			$errorContainer.removeClass("hidden");
			$createWindow.removeClass("loading");
			$startContainer.addClass("hidden");

			//print error to console
			if(checkVar(consoleStr)){
				console.error(consoleStr);
			}

		}

		function displayResult(jsonData){

			//clases job
			$resultContainer.removeClass("hidden");
			$createWindow.removeClass("loading");
			$startContainer.addClass("hidden");

			if(checkVar(jsonData["PRODUCT_PROPERTIES"]["SUCCESS"]) && jsonData["PRODUCT_PROPERTIES"]["SUCCESS"] == "Y"){
				if(checkObject(jsonData["PRODUCT_PROPERTIES"]["PROPERTY_RESULT"])){

					//vars
					var $productResultContainer = $(".switcherProductResult").removeClass("hidden");
					var $productResultTable = $productResultContainer.find(".switcherResultTable");

					createWindowTableColumns(jsonData["PRODUCT_PROPERTIES"]["PROPERTY_RESULT"], $productResultTable);

				}

			}

			//section
			if(checkVar(jsonData["SECTION_PROPERTIES"]["SUCCESS"]) && jsonData["SECTION_PROPERTIES"]["SUCCESS"] == "Y"){
				if(checkObject(jsonData["SECTION_PROPERTIES"]["PROPERTY_RESULT"])){

					//vars
					var $sectionResultContainer = $(".switcherSectionResult").removeClass("hidden");
					var $sectionResultTable = $sectionResultContainer.find(".switcherResultTable");

					createWindowTableColumns(jsonData["SECTION_PROPERTIES"]["PROPERTY_RESULT"], $sectionResultTable);

				}
			}

		}

		//block actions
		return event.preventDefault();

	};

	var createSkuProperties = function(event){
		
		//jquery vars
		var $this = $(this);
		var $createWindow = $this.parents(".txSwitcherWindow");
		var $startContainer = $createWindow.find(".switcherStartContainer");
		var $errorContainer = $createWindow.find(".switcherErrorContainer");
		var $resultContainer = $createWindow.find(".switcherResultContainer");

		//other vars
		var iblockId = $createWindow.data("iblock-id");

		//
		if(checkVar(iblockId)){

			//add loader
			$createWindow.addClass("opened loading");

			var ajaxObject = {
				"iblockId": iblockId,
				"act": "createSkuProperties"
			};

			//check component ajax path
			if(checkVar(settingsAjaxDir)){

				//send data
				$.getJSON(settingsAjaxDir + "/ajax.php", ajaxObject, function(jsonData){
					
				    //check success
					if(checkVar(jsonData["SUCCESS"]) && jsonData["SUCCESS"] == "Y"){
						displayResult(jsonData);
					}

					else{
						displayError(jsonData);
					}

				});

			}

		}

		//createProductProperties util functions
		function displayError(consoleStr){

			//clases job
			$errorContainer.removeClass("hidden");
			$createWindow.removeClass("loading");
			$startContainer.addClass("hidden");

			//print error to console
			if(checkVar(consoleStr)){
				console.error(consoleStr);
			}

		}

		function displayResult(jsonData){

			//clases job
			$resultContainer.removeClass("hidden");
			$createWindow.removeClass("loading");
			$startContainer.addClass("hidden");

			if(checkVar(jsonData["SUCCESS"]) && jsonData["SUCCESS"] == "Y"){
				if(checkObject(jsonData["PROPERTY_RESULT"])){

					//vars
					var $skuResultContainer = $(".switcherSkuResult").removeClass("hidden");
					var $skuResultTable = $skuResultContainer.find(".switcherResultTable");

					createWindowTableColumns(jsonData["PROPERTY_RESULT"], $skuResultTable);

				}

			}

		}

		//block actions
		return event.preventDefault();
	};

	var openProductPropertiesWindow = function(event){

		//jquery vars
		var $this = $(this);
		var $createWindow = $(".productCreatePropertiesWindow");
		var $iblockArea = $createWindow.find(".switcherWindowIblockData");

		//other vars
		var iblockId = $this.data("iblock-id");

		//add info
		$createWindow.data("iblock-id", iblockId).addClass("opened");
		$iblockArea.text(iblockId);


		//block actions
		return event.preventDefault();

	};

	var openSkuPropertiesWindow = function(event){

		//vars
		var $this = $(this);
		var $createWindow = $(".skuCreatePropertiesWindow");
		var $iblockArea = $createWindow.find(".switcherWindowIblockData");

		//other vars
		var iblockId = $this.data("iblock-id");

		//add info
		$createWindow.data("iblock-id", iblockId).addClass("opened");
		$iblockArea.text(iblockId);

		//block actions
		return event.preventDefault();

	};

	//
	var selectActiveTabFromStart = function(){
		//check cookie
		if(checkVar(getCookie("switcherActiveTabIndex"))){
			//vars
			var currentIndex = getCookie("switcherActiveTabIndex");
			//change tab
			$(".switcherTabs .switcherChangeTab").eq(currentIndex).trigger("click");
		}
	};

	var closeSwitcherWindow = function(event){

		//hide window
		$(".txSwitcherWindow").removeClass("opened");

		//reload page
		window.location.reload();

		//block actions
		return event.preventDefault();	

	};

	var checkDependenceBlocks = function(){

		//vars
		var $depBlocks = $txSwitcher.find('[data-dependence]');

		//check find items
		if(checkObject($depBlocks)){

			//each items
			$depBlocks.each(function(i, nextItem){

				//vars
				var $nextItem = $(nextItem);
				var dependenceValue = $nextItem.data("dependence");

				//flag vars
				var hideCurrentItem = false;

				//check multi dependence
				if(typeof dependenceValue == "object" && checkObject(dependenceValue)){

					//all items
					$.each(dependenceValue, function(nextId, nextValue){

						//vars
						var $dependenceItem = $txSwitcher.find('[data-id="' + nextId + '"]');
						var dependenceItemValue = "";

						//get current item type

						//select type
						if($dependenceItem.hasClass("switcherSelect")){
							dependenceItemValue = $dependenceItem.val();
						}

						//bool button type
						else if($dependenceItem.hasClass("switcherBool")){
							dependenceItemValue = $dependenceItem.find(".selected a").data("value");
						}

						//check value
						if(checkVar(dependenceItemValue) && dependenceItemValue != nextValue){
							hideCurrentItem = true;
							return false;
						}

					});

					//check for need hide current item
					hideCurrentItem ? $nextItem.addClass("hidden") : $nextItem.removeClass("hidden");

				}

				//check single dependence
				else{
					//get value from dep item
					if(checkVar(dependenceValue)){
						
						var $dependenceItem = $txSwitcher.find('[data-id="' + dependenceValue + '"]');
						var dependenceItemValue = $dependenceItem.find(".selected a").data("value");

						//check for need hide current item
						if(checkVar(dependenceItemValue)){
							dependenceItemValue == "Y" ? $nextItem.removeClass("hidden") : $nextItem.addClass("hidden");
						}

					}
				}

			});
		}

	};

	var switcherAfterChangeValues = function(event){
		checkDependenceBlocks();
	};

	//util functions
	var checkVar = function(cVar){
		return typeof cVar != "undefined" && cVar != "";		
	}

	var checkObject = function(cObject){
		return typeof cObject != "undefined" && !$.isEmptyObject(cObject);
	};

	var getCookie = function(name){

		//vars
		var matches = document.cookie.match(new RegExp(
			"(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
		));

		return matches ? decodeURIComponent(matches[1]) : undefined;

	}

	//[binds]

	//click binds
	$(document).on("click", ".txSwitcherWindowExit, .switcherWindowExit", closeSwitcherWindow);
	$(document).on("click", ".txSwitcherSettings, .switcherClose", toggleSwitcher);
	$(document).on("click", ".switcherCreateProductProperties", openProductPropertiesWindow);
	$(document).on("click", ".switcherCreateSkuProperties", openSkuPropertiesWindow);
	$(document).on("click", ".startCreateProductProperties", createProductProperties);
	$(document).on("click", ".startCreateSkuProperties", createSkuProperties);
	$(document).on("click", ".switcherHideAll", closeAllCheckItems);
	$(document).on("click", ".switcherShowAll", openAllCheckItems);
	$(document).on("click", ".switchByLink a", switchByLink);
	$(document).on("click", ".switcherSave", switcherSave);
	
	//change binds
	$(document).on("change", ".productIblockSelect", {containerClass: ".productPropertyAjax", propertySort: 5000, settingName: "TEMPLATE_PRODUCT_PROPERTIES[]"}, iblockSelectHandler);
	$(document).on("change", ".skuIblockSelect", {containerClass: ".skuPropertyAjax", propertySort: 100, settingName: "TEMPLATE_SKU_PROPERTIES[]"}, iblockSelectHandler);
	$(document).on("change", ".switcherSelect", switcherAfterChangeValues);

	//switcher tabs
	$(document).on("click", ".switcherTabs .switcherChangeTab", switcherChangeTab);

	//start functions
	selectActiveTabFromStart();
	checkDependenceBlocks();

});