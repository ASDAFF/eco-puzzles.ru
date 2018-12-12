"use strict";
var skyweb24PopupCookiePlaning=[];
var skyweb24AfterTimeSecondTimer=Math.round(new Date().getTime()/1000);
var isDelegateAjaxSucces = true;
var skyweb24Popups;
BX.ready(function(){
	
	if(typeof(skyweb24Popups)=='undefined'){
		BX.ajax({
			url: '/bitrix/components/skyweb24/popup.pro/ajax.php',
			data: {
				'type':'skyweb24Popups',
			},
			method:'POST',
			dataType:'json',
			async:false,
			onsuccess:function(data){
				skyweb24Popups=data;
			}
		});
	}
	BX.ajax({
		url: '/bitrix/components/skyweb24/popup.pro/ajax.php',
		data: {
			'type':'getPopups',
			'pageUrl':encodeURIComponent(location.href),
			'site':skyweb24Popups.site,
			'dateUser':Math.round(+new Date()/1000),
		},
		method: 'POST',
		dataType: 'json',
		timeout:300,
		async: true,
		/* scriptsRunFirst:true, */
		onsuccess: function(data){
			if(Object.keys(data).length>0){
				skyweb24Popups.popups=data;
				skyweb24Popups.tmpParams={};
				for(var key in data){
					skyweb24Popups.tmpParams[key]={};
				}
				skyweb24startConditions();
			} 
		},
		onfailure: function(data){
			console.log(data);
		}
	});
})
var checker = 0;
function skyweb24startConditions(){
	if(Object.keys(skyweb24Popups.popups).length>0){
		BX.ajax({
			url: '/bitrix/components/skyweb24/popup.pro/ajax.php',
			data: {
				'type':'getBasket'
			},
			method: 'POST',
			dataType: 'json',
			timeout:300,
			async: true,
			/* scriptsRunFirst:true, */
			onsuccess: function(data){
				if(!data.not_include){
                    skyweb24Popups.basket=data;
                    if(isDelegateAjaxSucces){
                        BX.addCustomEvent('onAjaxSuccess', function(a,b){
                            if(b && b.url && b.url.indexOf('basket')>-1 && b.url.indexOf('component_props.php') == -1){
                                BX.ajax({
                                    url: '/bitrix/components/skyweb24/popup.pro/ajax.php',
                                    data: {
                                        'type':'getBasket'
                                    },
                                    method: 'POST',
                                    dataType: 'json',
                                    timeout:300,
                                    async: true,
                                    onsuccess: function(data){
                                        if(!data.not_include){
                                            skyweb24startConditions();
                                        }
                                    },
                                    onfailure: function(data){
                                        console.log(data);
                                    }
                                });
                            }
                        });
                        isDelegateAjaxSucces = false;
                    }
					
				}
			},
			onfailure: function(data){
				console.log(data);
			}
		});
	}
	
	for(var key in skyweb24Popups.popups){
		var nextPopup=skyweb24Popups.popups[key][''];
		var resultCheck = 123;
		
		var resultCheck = skyweb24CheckGroup(nextPopup,key,'','','','');
		if(resultCheck===false){
			delete skyweb24Popups.popups[key];
		}
		if(resultCheck===true){
			skyweb24showPopup(key,'initial');
			//break;
		}
	}

	if(Object.keys(skyweb24Popups.popups).length>0){
		skyweb24uploadPopups();
	}

}

function skyweb24AfterTimeSecons(key,alreadyGoing){
	if(skyweb24Popups.popups[key]){
		var nextPopup=skyweb24Popups.popups[key][''];
		if(!!nextPopup){
			var resultCheck = skyweb24CheckGroup(nextPopup,key,alreadyGoing,'','','');
			if(resultCheck===false){
				delete skyweb24Popups.popups[key];
			}
			if(resultCheck===true){
				skyweb24showPopup(key,'timeSecnd');
			}
		}
	}
}

function skyweb24CheckInUserAgent(needle,useragent){
	var tmpNeedle = needle.split(' ');
	var counter = tmpNeedle.length;
	for(var i in tmpNeedle){
		if(tmpNeedle[i]=='phone'){
			if(useragent.indexOf('mobile')!==-1){
				counter--;
			}
		}
		if(useragent.indexOf(tmpNeedle[i])!==-1){
			counter--;
		}
	}
	if(counter == 0){
		return true;
	}else if (counter>0){
		return false;
	}
}

function skyweb24showScroll(){
	if(skyweb24Popups.popups[this.key])
		var nextPopup=skyweb24Popups.popups[this.key][''];
	if(!!nextPopup){
		if(document.body.scrollHeight/100*this.scroll>=window.pageYOffset){
			var resultCheck = skyweb24CheckGroup(nextPopup,this.key,'','','','scrolled');
			
			if(resultCheck===true){
				skyweb24showPopup(this.key);
			}
		}

	}
}

function skyweb24CheckGroup(tmpRule,popupTmpId,alreadyGoing,anchor,classLink,ScreenScroll,where){
	if(alreadyGoing == undefined){alreadyGoing=''}
	if(anchor == undefined){anchor=''}
	if(classLink == undefined){classLink=''}
	if(ScreenScroll == undefined){ScreenScroll=''}
	if(where == undefined){where='group'}
	var TmpAggregator = tmpRule.values.aggregator;
	var TmpLogic = tmpRule.values.value;
	if(TmpAggregator=='AND'){
		var TmpPopupRes = [];
		for(var i=0; i<tmpRule.children.length; i++){
				if(tmpRule.children[i]===false)
					TmpPopupRes.push(false);
				else if(tmpRule.children[i]===true){
					TmpPopupRes.push(tmpRule.children[i]);
				}
				else if(tmpRule.children[i].controlId=="CondGroup"){
					var tmplol = skyweb24CheckGroup(tmpRule.children[i],popupTmpId,alreadyGoing,anchor,classLink,ScreenScroll);
					if(tmplol===false||tmplol===true) TmpPopupRes.push(tmplol);
				}
				else if(tmpRule.children[i].controlId=="DAY"){
					var tmpDate = new Date();
					var tmpDay = tmpDate.getDay();
					var tmpLengthRes = TmpPopupRes.length;
					if(tmpRule.children[i].values.logic=='Equal'){
						for(var day in tmpRule.children[i].values.value){
							if(tmpDay==tmpRule.children[i].values.value[day]){
								TmpPopupRes.push(true);
								break;
							}
						}
						if(tmpLengthRes==TmpPopupRes.length){
							TmpPopupRes.push(false);
						}
					}else{
						for(var day in tmpRule.children[i].values.value){
							if(tmpDay==tmpRule.children[i].values.value[day]){
								TmpPopupRes.push(false);
								break;
							}
						}
						if(tmpLengthRes==TmpPopupRes.length){
							TmpPopupRes.push(true);
						}
					}
				}
				else if(tmpRule.children[i].controlId=="AFTER_TIME_SECOND"){
					var tmpUnixtime = Math.round(new Date().getTime()/1000);
					var tmpSeconds = tmpUnixtime-skyweb24AfterTimeSecondTimer;
					var tmpSeconds2 = tmpRule.children[i].values.value;
					var tmpLogic = tmpRule.children[i].values.logic;
					if(tmpLogic=='more'){
						if(tmpSeconds>=tmpSeconds2){
							TmpPopupRes.push(true);
						}else{
							//hack if setting alreadyGoing
							skyweb24Popups.popups[popupTmpId].timerDelay='Y';
						}
					}else{
						if(tmpSeconds<tmpSeconds2){
							TmpPopupRes.push(true);
							//hack if setting alreadyGoing
							//skyweb24Popups.popups[popupTmpId].timerDelay='Y';
						}else{
						}
					}
					if(tmpRule.children[i]!==false&&tmpLogic=="more"&&tmpSeconds2>0){
						setTimeout(function(){
                            //hack if setting alreadyGoing
                            if(skyweb24Popups.popups[popupTmpId] && skyweb24Popups.popups[popupTmpId].timerDelay){
                                skyweb24Popups.popups[popupTmpId].timerDelay='N';
                            }
							skyweb24AfterTimeSecons(popupTmpId,alreadyGoing);
						},
						parseInt(tmpSeconds2)*1000);
					}
				}
				else if(tmpRule.children[i].controlId=="AFTER_TIME_SECOND_PAGE"){
					var tmpUnixtime = Math.round(new Date().getTime()/1000);
					var tmpSeconds = tmpUnixtime-skyweb24AfterTimeSecondTimer;
					var tmpSeconds2 = tmpRule.children[i].values.value;
					var tmpLogic = tmpRule.children[i].values.logic;
					if(tmpLogic=='more'){
						if(tmpSeconds>=tmpSeconds2){
							TmpPopupRes.push(true);
						}else{
                            //hack if setting alreadyGoing
							skyweb24Popups.popups[popupTmpId].timerDelay='Y';
						}
					}else{
						if(tmpSeconds<tmpSeconds2){
                            TmpPopupRes.push(true);
                            //hack if setting alreadyGoing
							//skyweb24Popups.popups[popupTmpId].timerDelay='Y';
						}else{
						}
                    }
					if(tmpRule.children[i]!==false&&tmpLogic=="more"&&tmpSeconds2>0){
						setTimeout(function(){
                            //hack if setting alreadyGoing
							if(skyweb24Popups.popups[popupTmpId] && skyweb24Popups.popups[popupTmpId].timerDelay){
								skyweb24Popups.popups[popupTmpId].timerDelay='N';
							}
							skyweb24AfterTimeSecons(popupTmpId,alreadyGoing);
						},
						parseInt(tmpSeconds2)*1000);
					}
				}
				
				else if(tmpRule.children[i].controlId=='TIME_INTERVAL'){
					var tmpNowTime = new Date;
					tmpNowTime = tmpNowTime.getHours()*3600+tmpNowTime.getMinutes()*60;
					if(tmpRule.children[i].values.time_start!=''){
						var tmpStartTime = tmpRule.children[i].values.time_start.split(':');
						var tmpStartTime = tmpStartTime[0]*3600+tmpStartTime[1]*60;
					}else{
						var tmpStartTime=0;
					}
					if(tmpRule.children[i].values.time_end!=''){
						var tmpEndTime = tmpRule.children[i].values.time_end.split(':');
						var tmpEndTime = tmpEndTime[0]*3600+tmpEndTime[1]*60;
					}else{
						var tmpEndTime = 86400;
					}
					if(tmpNowTime>=tmpStartTime&&tmpNowTime<=tmpEndTime){
						tmpRule.children[i]=true;
						TmpPopupRes.push(true);
					}else{
						tmpRule.children[i]=false;
						TmpPopupRes.push(false);
					}
				}
				else if(tmpRule.children[i].controlId=='ALREADY_GOING'){
					if(!skyweb24Popups.popups[popupTmpId].timerDelay || (skyweb24Popups.popups[popupTmpId].timerDelay=='N')){//hack if setting timerDelay (AFTER_TIME_SECOND, AFTER_TIME_SECOND_PAGE)
						if(alreadyGoing==''){
							BX.bind(BX(document), 'mousemove', BX.delegate(skyweb24showAlreadyGoing, popupTmpId));
						}else{
							BX.unbind(BX(document), 'mousemove', BX.delegate(skyweb24showAlreadyGoing, popupTmpId));
							tmpRule.children[i]=true;
							TmpPopupRes.push(true);
						}
					}
				}
				else if(tmpRule.children[i].controlId=='PERCENT_PAGE'){
 					if(ScreenScroll===''){
						BX.bind(BX(document), 'scroll', BX.delegate(skyweb24showScroll,{key:popupTmpId,scroll:tmpRule.children[i].values.value}));
					}else{
						BX.unbind(BX(document), 'scroll', BX.delegate(skyweb24showScroll,{key:popupTmpId,scroll:tmpRule.children[i].values.value}));
						tmpRule.children[i]=true;
						TmpPopupRes.push(true);
					}
				}
				else if(tmpRule.children[i].controlId=='ANCHOR_VISIBLE'){
					if(anchor!=tmpRule.children[i].values.value){
						var tmpAnchor = {anchor:tmpRule.children[i].values.value,key:popupTmpId}
						BX.bind(BX(document), 'scroll', BX.delegate(skyweb24showAnchor,tmpAnchor));
						//fix if ANCHOR in view port
						skyweb24showAnchor.call(tmpAnchor)
					}else{
						tmpRule.children[i]=true;
						TmpPopupRes.push(true);
					}
				}
				else if(tmpRule.children[i].controlId=='ON_CLICK_CLASS_LINK'){
					if(classLink!=tmpRule.children[i].values.value){
						var tmpClassLink = tmpRule.children[i].values.value;
						var tmpLinkObj = {
							classLink:tmpClassLink,
							key:popupTmpId
						}
						BX.bind(BX(document),'click',BX.delegate(skyweb24openByClick,tmpLinkObj));
					}else{
						tmpRule.children[i]=true;
						TmpPopupRes.push(true);
					}
				}
				else if(tmpRule.children[i].controlId=='DEVICE_TYPE'){
					var userAgent = window.navigator.userAgent.toLowerCase();
					var tmpLogic = tmpRule.children[i].values.logic;
					if(tmpLogic=='Not'){
						var tmpDeviceCounter=tmpRule.children[i].values.value.length;
					}
					for(var k=0;k<tmpRule.children[i].values.value.length;k++){
						if(tmpLogic=='Equal'){
							if(skyweb24CheckInUserAgent(tmpRule.children[i].values.value[k],userAgent)){
								TmpPopupRes.push(true);
								break;
							}
						}else{
							if(!skyweb24CheckInUserAgent(tmpRule.children[i].values.value[k],userAgent)){
								tmpDeviceCounter--;
							}
						}
					}
					if(tmpLogic=='Not'){
						if(tmpDeviceCounter==0){
							TmpPopupRes.push(true);
						}
					}
				}
				else if(tmpRule.children[i].controlId=='OS'){
					var userAgent = window.navigator.userAgent.toLowerCase();
					var tmpLogic = tmpRule.children[i].values.logic;
					
					if(tmpLogic=='Not'){
						var tmpDeviceCounter=tmpRule.children[i].values.value.length;
					}
					for(var k=0;k<tmpRule.children[i].values.value.length;k++){
						if(tmpLogic=='Equal'){
							if(skyweb24CheckInUserAgent(tmpRule.children[i].values.value[k],userAgent)){
								TmpPopupRes.push(true);
								tmpRule.children[i]=true;
								break;
							}
						}else{
							if(!skyweb24CheckInUserAgent(tmpRule.children[i].values.value[k],userAgent)){
								tmpDeviceCounter--;
							}
						}
					}
					if(tmpLogic=='Not'){
						if(tmpDeviceCounter==0){
							TmpPopupRes.push(true);
							tmpRule.children[i]=true;
						}
					}
				}
				else if(tmpRule.children[i].controlId=='BROWSER'){
					var userAgent = window.navigator.userAgent.toLowerCase();
					var tmpLogic = tmpRule.children[i].values.logic;
					if(tmpLogic=='Not'){
						var tmpDeviceCounter=tmpRule.children[i].values.value.length;
					}
					for(var k=0;k<tmpRule.children[i].values.value.length;k++){
						if(tmpLogic=='Equal'){
							if(skyweb24CheckInUserAgent(tmpRule.children[i].values.value[k],userAgent)){
								TmpPopupRes.push(true);
								tmpRule.children[i]=true;
								break;
							}
						}else{
							if(!skyweb24CheckInUserAgent(tmpRule.children[i].values.value[k],userAgent)){
								tmpDeviceCounter--;
							}
						}
					}
					if(tmpLogic=='Not'){
						if(tmpDeviceCounter==0){
							TmpPopupRes.push(true);
							tmpRule.children[i]=true;
						}
					}
				}
				else if(tmpRule.children[i].controlId=='REPEAT_SHOW'){
					if(BX.getCookie('skwb24_popups_'+popupTmpId)==='Y'){
						TmpPopupRes.push(false);
					}else{
						var tmpCounter = tmpRule.children[i].values.repeat;
						var tmpRepeatType = tmpRule.children[i].values.type.toLowerCase();
						var type={
							hour:3600,
							day:86400,
							week:604800,
							month:2419200,
							year:31536000,
						};
						skyweb24PopupCookiePlaning[popupTmpId]=type[tmpRepeatType]*tmpCounter;
						//tmpRule.children[i]=true;
						TmpPopupRes.push(true);
					}
				}
				else if(tmpRule.children[i].controlId=='CART_SUMM'){
					var tmpLogic = tmpRule.children[i].values.logic;
					if(tmpLogic=='more'){
						if(tmpRule.children[i].values.value<=parseFloat(skyweb24Popups.basket.summ)){
							TmpPopupRes.push(true);	
						}else{
						}
					}else{
						if(tmpRule.children[i].values.value<=parseFloat(skyweb24Popups.basket.summ)){
						}else{
							TmpPopupRes.push(true);
						}
					}
				}
				else if(tmpRule.children[i].controlId=='CART_COUNT'){
					var tmpLogic = tmpRule.children[i].values.logic;
					if(tmpLogic=='more'){
						if(parseInt(skyweb24Popups.basket.count)>=parseInt(tmpRule.children[i].values.value)){
							TmpPopupRes.push(true);
						}
					}else{
						if(parseInt(skyweb24Popups.basket.count)<parseInt(tmpRule.children[i].values.value)){
							TmpPopupRes.push(true);
						}
					}
				}
				else if(tmpRule.children[i].controlId=='CART_PRODUCT'){
					var tmpLogic = tmpRule.children[i].values.logic;
					if(tmpLogic=='Equal'){
						if(skyweb24Popups.basket.products.indexOf(tmpRule.children[i].values.value)!=-1){
							TmpPopupRes.push(true);
						}
					}else{
						if(skyweb24Popups.basket.products.indexOf(tmpRule.children[i].values.value)==-1){
							TmpPopupRes.push(true);
						}
					}
				}
				else if(tmpRule.children[i].controlId=='CART_SECTION'){
					var tmpLogic = tmpRule.children[i].values.logic;
					if(tmpLogic=='Equal'){
						if(skyweb24Popups.basket.sections.indexOf(tmpRule.children[i].values.value)!=-1){
							TmpPopupRes.push(true);
						}
					}else{
						if(skyweb24Popups.basket.sections.indexOf(tmpRule.children[i].values.value)==-1){
							TmpPopupRes.push(true);
						}
					}
				}
		}

		if(TmpLogic=='True'){
			if(tmpRule.children.length==TmpPopupRes.length){
				if(TmpPopupRes.indexOf(false)<0){
					return true;
				}else{
					return false;
				}
			}
		}else{
			if(tmpRule.children.length==TmpPopupRes.length){
				if(TmpPopupRes.indexOf(true)<0){
					return true;
				}else{
					return false;
				}
			}
		}
	}else if(TmpAggregator=='OR'){
		var TmpPopupRes=[];
		for(var i=0; i<tmpRule.children.length; i++){
			if(TmpLogic=='True'){
				if(tmpRule.children[i]===true){
					return true;
				}
				else if(tmpRule.children[i]===false){
					TmpPopupRes.push(false);
				}
				else if(tmpRule.children[i].controlId=="CondGroup"){
					var tmplol = skyweb24CheckGroup(tmpRule.children[i],popupTmpId,alreadyGoing,anchor,classLink,ScreenScroll);
					if(tmplol===false||tmplol===true)	TmpPopupRes.push(tmplol);
				}
				else if(tmpRule.children[i].controlId=="DAY"){
					var tmpDate = new Date();
					var tmpDay = tmpDate.getDay();
					var tmpLengthRes = TmpPopupRes.length;
					if(tmpRule.children[i].values.logic=='Equal'){
						for(var day in tmpRule.children[i].values.value){
							if(tmpDay==tmpRule.children[i].values.value[day]){
								return true;
								break;
							}
						}
						if(tmpLengthRes==TmpPopupRes.length){
							TmpPopupRes.push(false);
						}
					}else{
						for(var day in tmpRule.children[i].values.value){
							if(tmpDay==tmpRule.children[i].values.value[day]){
								TmpPopupRes.push(false);
								break;
							}
						}
						if(tmpLengthRes==TmpPopupRes.length){
							return true;
						}
					}
				}
				else if(tmpRule.children[i].controlId=="AFTER_TIME_SECOND"){
					var tmpUnixtime = Math.round(new Date().getTime()/1000);
					var tmpSeconds = tmpUnixtime-skyweb24AfterTimeSecondTimer;
					var tmpSeconds2 = tmpRule.children[i].values.value;
					var tmpLogic = tmpRule.children[i].values.logic;
					if(tmpLogic =='more'){
						if(tmpSeconds>=tmpSeconds2){
							return true;
						}else{
							//hack if setting alreadyGoing
							//skyweb24Popups.popups[popupTmpId].timerDelay='Y';
						}
					}else{
						if(tmpSeconds<tmpSeconds2){
                            //hack if setting alreadyGoing
							//skyweb24Popups.popups[popupTmpId].timerDelay='Y';
							return true;
						}else{
							TmpPopupRes.push(false);
						}
					}
					if(tmpRule.children[i]!==false&&tmpLogic=='more'&&tmpSeconds2>0){
						setTimeout(function(){
                            //hack if setting alreadyGoing
							if(skyweb24Popups.popups[popupTmpId] && skyweb24Popups.popups[popupTmpId].timerDelay){
								skyweb24Popups.popups[popupTmpId].timerDelay='N';
							}
                            skyweb24AfterTimeSecons(popupTmpId,alreadyGoing)
                        },parseInt(tmpSeconds2)*1000);
					}
				}
				else if(tmpRule.children[i].controlId=="AFTER_TIME_SECOND_PAGE"){
					var tmpUnixtime = Math.round(new Date().getTime()/1000);
					var tmpSeconds = tmpUnixtime-skyweb24AfterTimeSecondTimer;
					var tmpSeconds2 = tmpRule.children[i].values.value;
					var tmpLogic = tmpRule.children[i].values.logic;
					if(tmpLogic =='more'){
						if(tmpSeconds>=tmpSeconds2){
							return true;
						}else{
							//hack if setting alreadyGoing
							//skyweb24Popups.popups[popupTmpId].timerDelay='Y';
						}
					}else{
						if(tmpSeconds<tmpSeconds2){
                            //hack if setting alreadyGoing
							//skyweb24Popups.popups[popupTmpId].timerDelay='Y';
							return true;
						}else{
							TmpPopupRes.push(false);
						}
					}
					if(tmpRule.children[i]!==false&&tmpLogic=='more'&&tmpSeconds2>0){
						setTimeout(function(){
                            //hack if setting alreadyGoing
							if(skyweb24Popups.popups[popupTmpId] && skyweb24Popups.popups[popupTmpId].timerDelay){
								skyweb24Popups.popups[popupTmpId].timerDelay='N';
							}
                            skyweb24AfterTimeSecons(popupTmpId,alreadyGoing)
                        },parseInt(tmpSeconds2)*1000);
					}
				}
				else if(tmpRule.children[i].controlId=='TIME_INTERVAL'){
					var tmpNowTime = new Date;

					tmpNowTime = tmpNowTime.getHours()*3600+tmpNowTime.getMinutes()*60;
					if(tmpRule.children[i].values.time_start!=''){
						var tmpStartTime = tmpRule.children[i].values.time_start.split(':');
						var tmpStartTime = tmpStartTime[0]*3600+tmpStartTime[1]*60;
					}else{
						var tmpStartTime=0;
					}
					if(tmpRule.children[i].values.time_end!=''){
						var tmpEndTime = tmpRule.children[i].values.time_end.split(':');
						var tmpEndTime = tmpEndTime[0]*3600+tmpEndTime[1]*60;
					}else{
						var tmpEndTime = 86400;
					}
					if(tmpNowTime>=tmpStartTime&&tmpNowTime<=tmpEndTime){
						tmpRule.children[i]=true;
						return true;
					}else{
						tmpRule.children[i]=false;
						TmpPopupRes.push(false);
					}
				}
				else if(tmpRule.children[i].controlId=='ALREADY_GOING'){
                    if(!skyweb24Popups.popups[popupTmpId].timerDelay || (skyweb24Popups.popups[popupTmpId].timerDelay=='N')){//hack if setting timerDelay (AFTER_TIME_SECOND, AFTER_TIME_SECOND_PAGE)
                        if(alreadyGoing===''){
                            BX.bind(BX(document), 'mousemove', BX.delegate(skyweb24showAlreadyGoing, popupTmpId));
                        }else{
                            BX.unbind(BX(document), 'mousemove', BX.delegate(skyweb24showAlreadyGoing, popupTmpId));
                            tmpRule.children[i]=true;
                            return true;
                        }
                    }
				}
				else if(tmpRule.children[i].controlId=='PERCENT_PAGE'){
 					if(ScreenScroll===''){
						BX.bind(BX(document), 'scroll', BX.delegate(skyweb24showScroll,{key:popupTmpId,scroll:tmpRule.children[i].values.value}));
					}else{
						BX.unbind(BX(document), 'scroll', BX.delegate(skyweb24showScroll,{key:popupTmpId,scroll:tmpRule.children[i].values.value}));
						tmpRule.children[i]=true;
						return true;
					}
				}
				else if(tmpRule.children[i].controlId=='ANCHOR_VISIBLE'){
					if(anchor!=tmpRule.children[i].values.value){
						var tmpAnchor = {anchor:tmpRule.children[i].values.value,key:popupTmpId}
						BX.bind(BX(document), 'scroll', BX.delegate(skyweb24showAnchor,tmpAnchor));
						skyweb24showAnchor.call(tmpAnchor)
					}else{
						tmpRule.children[i]=true;
						return true;
					}
				}
				else if(tmpRule.children[i].controlId=='ON_CLICK_CLASS_LINK'){
					if(classLink!=tmpRule.children[i].values.value){
						var tmpClassLink = tmpRule.children[i].values.value;
						var tmpLinkObj = {
							classLink:tmpClassLink,
							key:popupTmpId
						}
						BX.bind(BX(document),'click',BX.delegate(skyweb24openByClick,tmpLinkObj));
					}else{
						tmpRule.children[i]=true;
						return true;
					}
				}
				else if(tmpRule.children[i].controlId=='DEVICE_TYPE'){
					var userAgent = window.navigator.userAgent.toLowerCase();
					var tmpLogic = tmpRule.children[i].values.logic;
					if(tmpLogic=='Not'){
						var tmpDeviceCounter=tmpRule.children[i].values.value.length;
					}
					for(var k=0;k<tmpRule.children[i].values.value.length;k++){
						if(tmpLogic=='Equal'){
							if(skyweb24CheckInUserAgent(tmpRule.children[i].values.value[k],userAgent)){
								return true;
							}
						}else{
							if(!skyweb24CheckInUserAgent(tmpRule.children[i].values.value[k],userAgent)){
								tmpDeviceCounter--;
							}
						}
					}
					if(tmpLogic=='Not'){
						if(tmpDeviceCounter==0){
							return true;
						}
					}
				}
				else if(tmpRule.children[i].controlId=='OS'){
					var userAgent = window.navigator.userAgent.toLowerCase();
					var tmpLogic = tmpRule.children[i].values.logic;
					if(tmpLogic=='Not'){
						var tmpDeviceCounter=tmpRule.children[i].values.value.length;
					}
					for(var k=0;k<tmpRule.children[i].values.value.length;k++){
						if(tmpLogic=='Equal'){
							if(skyweb24CheckInUserAgent(tmpRule.children[i].values.value[k],userAgent)){
								return true;
							}
						}else{
							if(!skyweb24CheckInUserAgent(tmpRule.children[i].values.value[k],userAgent)){
								tmpDeviceCounter--;
							}
						}
					}
					if(tmpLogic=='Not'){
						if(tmpDeviceCounter==0){
							return true;
						}
					}
				}
				else if(tmpRule.children[i].controlId=='BROWSER'){
					var userAgent = window.navigator.userAgent.toLowerCase();
					var tmpLogic = tmpRule.children[i].values.logic;
					if(tmpLogic=='Not'){
						var tmpDeviceCounter=tmpRule.children[i].values.value.length;
					}
					for(var k=0;k<tmpRule.children[i].values.value.length;k++){
						if(tmpLogic=='Equal'){
							if(skyweb24CheckInUserAgent(tmpRule.children[i].values.value[k],userAgent)){
								return true;
							}
						}else{
							if(!skyweb24CheckInUserAgent(tmpRule.children[i].values.value[k],userAgent)){
								tmpDeviceCounter--;
							}
						}
					}
					if(tmpLogic=='Not'){
						if(tmpDeviceCounter==0){
							return true;
						}
					}
				}
				else if(tmpRule.children[i].controlId=='REPEAT_SHOW'){
					if(BX.getCookie('skwb24_popups_'+popupTmpId)==='Y'){
						TmpPopupRes.push(false);
					}else{
						var tmpCounter = tmpRule.children[i].values.repeat;
						var tmpRepeatType = tmpRule.children[i].values.type.toLowerCase();
						var type={
							hour:3600,
							day:86400,
							week:604800,
							month:2419200,
							year:31536000,
						};
						skyweb24PopupCookiePlaning[popupTmpId]=type[tmpRepeatType]*tmpCounter;
						return true;
					}
				}
				else if(tmpRule.children[i].controlId=='CART_SUMM'){
					var tmpLogic = tmpRule.children[i].values.logic;
					if(tmpLogic=='more'){
						if(tmpRule.children[i].values.value<=parseFloat(skyweb24Popups.basket.summ)){
							return true;
						}
					}else{
						if(tmpRule.children[i].values.value>parseFloat(skyweb24Popups.basket.summ)){
							return true;
						}
					}
				}
				else if(tmpRule.children[i].controlId=='CART_COUNT'){
					var tmpLogic = tmpRule.children[i].values.logic;
					if(tmpLogic=='more'){
						if(parseInt(skyweb24Popups.basket.count)>=parseInt(tmpRule.children[i].values.value)){
							return true;
						}
					}else{
						if(parseInt(skyweb24Popups.basket.count)<parseInt(tmpRule.children[i].values.value)){
							return true;
						}
					}
				}
				else if(tmpRule.children[i].controlId=='CART_PRODUCT'){
					var tmpLogic = tmpRule.children[i].values.logic;
					if(tmpLogic=='Equal'){
						if(skyweb24Popups.basket.products.indexOf(tmpRule.children[i].values.value)!=-1){
							return true;
						}
					}else{
						if(skyweb24Popups.basket.products.indexOf(tmpRule.children[i].values.value)==-1){
							return true;
						}
					}
				}
				else if(tmpRule.children[i].controlId=='CART_SECTION'){
					var tmpLogic = tmpRule.children[i].values.logic;
					if(tmpLogic=='Equal'){
						if(skyweb24Popups.basket.sections.indexOf(tmpRule.children[i].values.value)!=-1){
							return true;
						}
					}else{
						if(skyweb24Popups.basket.sections.indexOf(tmpRule.children[i].values.value)==-1){
							return true;
						}
					}
				}

			}else{
				if(tmpRule.children[i]===true){
					TmpPopupRes.push(true);
				}
				else if(tmpRule.children[i]===false){
					TmpPopupRes.push(false);
				}
				else if(tmpRule.children[i].controlId=="CondGroup"){
					var tmplol = skyweb24CheckGroup(tmpRule.children[i],popupTmpId,alreadyGoing,anchor,classLink,ScreenScroll);
					if(tmplol===false||tmplol===true)	TmpPopupRes.push(tmplol);
				}
				else if(tmpRule.children[i].controlId=="DAY"){
					var tmpDate = new Date();
					var tmpDay = tmpDate.getDay();
					var tmpLengthRes = TmpPopupRes.length;
					if(tmpRule.children[i].values.logic=='Equal'){
						for(var day in tmpRule.children[i].values.value){
							if(tmpDay==tmpRule.children[i].values.value[day]){
								TmpPopupRes.push(true);
								break;
							}
						}
						if(tmpLengthRes==TmpPopupRes.length){
							TmpPopupRes.push(false);
						}
					}else{
						for(var day in tmpRule.children[i].values.value){
							if(tmpDay==tmpRule.children[i].values.value[day]){
								TmpPopupRes.push(false);
								break;
							}
						}
						if(tmpLengthRes==TmpPopupRes.length){
							TmpPopupRes.push(true);
						}
					}
				}
				else if(tmpRule.children[i].controlId=="AFTER_TIME_SECOND"){
					var tmpUnixtime = Math.round(new Date().getTime()/1000);
					var tmpSeconds = tmpUnixtime-skyweb24AfterTimeSecondTimer;
					var tmpSeconds2 = tmpRule.children[i].values.value;
					var tmpLogic = tmpRule.children[i].values.logic;
					if(tmpLogic =='more'){
						if(tmpSeconds>=tmpSeconds2){
							TmpPopupRes.push(true);
						}else{
                            //hack if setting alreadyGoing
							skyweb24Popups.popups[popupTmpId].timerDelay='Y';
						}
					}else{
						if(tmpSeconds<tmpRule.children[i].values.value){
                            //hack if setting alreadyGoing
							//skyweb24Popups.popups[popupTmpId].timerDelay='Y';
							TmpPopupRes.push(true);
						}else{
							tmpRule.children[i]=false;
							TmpPopupRes.push(false);
						}
					}if(tmpRule.children[i]!==false&&tmpLogic=='more'&&tmpSeconds2>0){
					setTimeout(function(){
                        //hack if setting alreadyGoing
                        if(skyweb24Popups.popups[popupTmpId] && skyweb24Popups.popups[popupTmpId].timerDelay){
                            skyweb24Popups.popups[popupTmpId].timerDelay='N';
                        }
                        skyweb24AfterTimeSecons(popupTmpId,alreadyGoing)
                    },parseInt(tmpSeconds)*1000);}
				}
				else if(tmpRule.children[i].controlId=="AFTER_TIME_SECOND_PAGE"){
					var tmpUnixtime = Math.round(new Date().getTime()/1000);
					var tmpSeconds = tmpUnixtime-skyweb24AfterTimeSecondTimer;
					var tmpSeconds2 = tmpRule.children[i].values.value;
					var tmpLogic = tmpRule.children[i].values.logic;
					if(tmpLogic =='more'){
						if(tmpSeconds>=tmpSeconds2){
							TmpPopupRes.push(true);
						}else{
                            //hack if setting alreadyGoing
							skyweb24Popups.popups[popupTmpId].timerDelay='Y';
						}
					}else{
						if(tmpSeconds<tmpRule.children[i].values.value){
                            //hack if setting alreadyGoing
							//skyweb24Popups.popups[popupTmpId].timerDelay='Y';
							TmpPopupRes.push(true);
						}else{
							tmpRule.children[i]=false;
							TmpPopupRes.push(false);
                        }
					}if(tmpRule.children[i]!==false&&tmpLogic=='more'&&tmpSeconds2>0){
					setTimeout(function(){
                        //hack if setting alreadyGoing
                        if(skyweb24Popups.popups[popupTmpId] && skyweb24Popups.popups[popupTmpId].timerDelay){
                            skyweb24Popups.popups[popupTmpId].timerDelay='N';
                        }
                        skyweb24AfterTimeSecons(popupTmpId,alreadyGoing)
                    },parseInt(tmpSeconds)*1000);}
				}
				else if(tmpRule.children[i].controlId=='TIME_INTERVAL'){
					var tmpNowTime = new Date;
					tmpNowTime = tmpNowTime.getHours()*3600+tmpNowTime.getMinutes()*60;
					if(tmpRule.children[i].values.time_start!=''){
						var tmpStartTime = tmpRule.children[i].values.time_start.split(':');
						var tmpStartTime = tmpStartTime[0]*3600+tmpStartTime[1]*60;
					}else{
						var tmpStartTime=0;
					}
					if(tmpRule.children[i].values.time_end!=''){
						var tmpEndTime = tmpRule.children[i].values.time_end.split(':');
						var tmpEndTime = tmpEndTime[0]*3600+tmpEndTime[1]*60;
					}else{
						var tmpEndTime = 86400;
					}
					if(tmpNowTime>=tmpStartTime&&tmpNowTime<=tmpEndTime){
						tmpRule.children[i]=true;
						TmpPopupRes.push(true);
					}else{
						tmpRule.children[i]=false;
						TmpPopupRes.push(false);
					}
				}
				else if(tmpRule.children[i].controlId=='ALREADY_GOING'){
                    if(!skyweb24Popups.popups[popupTmpId].timerDelay || (skyweb24Popups.popups[popupTmpId].timerDelay=='N')){//hack if setting timerDelay (AFTER_TIME_SECOND, AFTER_TIME_SECOND_PAGE)
                        if(alreadyGoing==''){
                            BX.bind(BX(document), 'mousemove', BX.delegate(skyweb24showAlreadyGoing, popupTmpId));
                        }else{
                            BX.unbind(BX(document), 'mousemove', BX.delegate(skyweb24showAlreadyGoing, popupTmpId));
                            tmpRule.children[i]=true;
                            TmpPopupRes.push(true);
                        }
                    }
				}
				else if(tmpRule.children[i].controlId=='PERCENT_PAGE'){
 					if(ScreenScroll===''){
						BX.bind(BX(document), 'scroll', BX.delegate(skyweb24showScroll,{key:popupTmpId,scroll:tmpRule.children[i].values.value}));
					}else{
						BX.unbind(BX(document), 'scroll', BX.delegate(skyweb24showScroll,{key:popupTmpId,scroll:tmpRule.children[i].values.value}));
						tmpRule.children[i]=true;
						return true;
					}
				}
				else if(tmpRule.children[i].controlId=='ANCHOR_VISIBLE'){
					if(anchor!=tmpRule.children[i].values.value){
						var tmpAnchor = {anchor:tmpRule.children[i].values.value,key:popupTmpId}
						BX.bind(BX(document), 'scroll', BX.delegate(skyweb24showAnchor,tmpAnchor));
						skyweb24showAnchor.call(tmpAnchor)
					}else{
						tmpRule.children[i]=true;
						TmpPopupRes.push(true);
					}
				}
				else if(tmpRule.children[i].controlId=='ON_CLICK_CLASS_LINK'){
					if(classLink!=tmpRule.children[i].values.value){
						var tmpClassLink = tmpRule.children[i].values.value;
						var tmpLinkObj = {
							classLink:tmpClassLink,
							key:popupTmpId
						}
						BX.bind(BX(document),'click',BX.delegate(skyweb24openByClick,tmpLinkObj));
					}else{
						tmpRule.children[i]=true;
						TmpPopupRes.push(true);
					}
				}
				else if(tmpRule.children[i].controlId=='DEVICE_TYPE'){
					var userAgent = window.navigator.userAgent.toLowerCase();
					var tmpLogic = tmpRule.children[i].values.logic;
					if(tmpLogic=='Not'){
						var tmpDeviceCounter=tmpRule.children[i].values.value.length;
					}
					for(var k=0;k<tmpRule.children[i].values.value.length;k++){
						if(tmpLogic=='Equal'){
							if(skyweb24CheckInUserAgent(tmpRule.children[i].values.value[k],userAgent)){
								TmpPopupRes.push(true);
								break;
							}
						}else{
							if(!skyweb24CheckInUserAgent(tmpRule.children[i].values.value[k],userAgent)){
								tmpDeviceCounter--;
							}
						}
					}
					if(tmpLogic=='Not'){
						if(tmpDeviceCounter==0){
							TmpPopupRes.push(true);
						}
					}
				}
				else if(tmpRule.children[i].controlId=='OS'){
					var userAgent = window.navigator.userAgent.toLowerCase();
					var tmpLogic = tmpRule.children[i].values.logic;
					if(tmpLogic=='Not'){
						var tmpDeviceCounter=tmpRule.children[i].values.value.length;
					}
					for(var k=0;k<tmpRule.children[i].values.value.length;k++){
						if(tmpLogic=='Equal'){
							if(skyweb24CheckInUserAgent(tmpRule.children[i].values.value[k],userAgent)){
								TmpPopupRes.push(true);
								break;
							}
						}else{
							if(!skyweb24CheckInUserAgent(tmpRule.children[i].values.value[k],userAgent)){
								tmpDeviceCounter--;
							}
						}
					}
					if(tmpLogic=='Not'){
						if(tmpDeviceCounter==0){
							TmpPopupRes.push(true);
						}
					}
				}
				else if(tmpRule.children[i].controlId=='BROWSER'){
					var userAgent = window.navigator.userAgent.toLowerCase();
					var tmpLogic = tmpRule.children[i].values.logic;
					if(tmpLogic=='Not'){
						var tmpDeviceCounter=tmpRule.children[i].values.value.length;
					}
					for(var k=0;k<tmpRule.children[i].values.value.length;k++){
						if(tmpLogic=='Equal'){
							if(skyweb24CheckInUserAgent(tmpRule.children[i].values.value[k],userAgent)){
								TmpPopupRes.push(true);
								break;
							}
						}else{
							if(!skyweb24CheckInUserAgent(tmpRule.children[i].values.value[k],userAgent)){
								tmpDeviceCounter--;
							}
						}
					}
					if(tmpLogic=='Not'){
						if(tmpDeviceCounter==0){
							TmpPopupRes.push(true);
						}
					}
				}
				else if(tmpRule.children[i].controlId=='REPEAT_SHOW'){
					if(BX.getCookie('skwb24_popups_'+popupTmpId)==='Y'){
						TmpPopupRes.push(false);
					}else{
						var tmpCounter = tmpRule.children[i].values.repeat;
						var tmpRepeatType = tmpRule.children[i].values.type.toLowerCase();
						var type={
							hour:3600,
							day:86400,
							week:604800,
							month:2419200,
							year:31536000,
						};
						skyweb24PopupCookiePlaning[popupTmpId]=type[tmpRepeatType]*tmpCounter;
						//tmpRule.children[i]=true;
						TmpPopupRes.push(true);
					}
				}
				else if(tmpRule.children[i].controlId=='CART_SUMM'){
					var tmpLogic = tmpRule.children[i].values.logic;
					if(tmpLogic=='more'){
						if(tmpRule.children[i].values.value<=parseFloat(skyweb24Popups.basket.summ)){
							TmpPopupRes.push(true);
						}else{
							TmpPopupRes.push(false);
						}
					}else{
						if(tmpRule.children[i].values.value>parseFloat(skyweb24Popups.basket.summ)){
							TmpPopupRes.push(true);
						}else{
							TmpPopupRes.push(false);
						}
					}
				}
				else if(tmpRule.children[i].controlId=='CART_COUNT'){
					var tmpLogic = tmpRule.children[i].values.logic;
					if(tmpLogic=='more'){
						if(parseInt(skyweb24Popups.basket.count)>=parseInt(tmpRule.children[i].values.value)){
							TmpPopupRes.push(true);
						}else{
							TmpPopupRes.push(false);
						}
					}else{
						if(parseInt(skyweb24Popups.basket.count)<parseInt(tmpRule.children[i].values.value)){
							TmpPopupRes.push(true);
						}else{
							TmpPopupRes.push(false);
						}
					}
				}
				else if(tmpRule.children[i].controlId=='CART_PRODUCT'){
					var tmpLogic = tmpRule.children[i].values.logic;
					if(tmpLogic=='Equal'){
						if(skyweb24Popups.basket.products.indexOf(tmpRule.children[i].values.value)!=-1){
							TmpPopupRes.push(true);
						}else{
							TmpPopupRes.push(false);
						}
					}else{
						if(skyweb24Popups.basket.products.indexOf(tmpRule.children[i].values.value)==-1){
							TmpPopupRes.push(true);
						}else{
							TmpPopupRes.push(false);
						}
					}
				}
				else if(tmpRule.children[i].controlId=='CART_SECTION'){
					var tmpLogic = tmpRule.children[i].values.logic;
					if(tmpLogic=='Equal'){
						if(skyweb24Popups.basket.sections.indexOf(tmpRule.children[i].values.value)!=-1){
							TmpPopupRes.push(true);
						}
					}else{
						if(skyweb24Popups.basket.sections.indexOf(tmpRule.children[i].values.value)==-1){
							TmpPopupRes.push(true);
						}
					}
				}
			}
		}
		if(TmpLogic=='false'){
			if(tmpRule.children.length==TmpPopupRes.length){
				if(TmpPopupRes.indexOf(false)>=0){
					return true;
				}else{
					return false;
				}
			}
		}else{
			/*if(tmpRule.children.length==TmpPopupRes.length){
				if(TmpPopupRes.indexOf(true)>=0){
					return true;
				}else{
					return false;
				}
			}*/
			if(tmpRule.children.length>0){
				if(TmpPopupRes.indexOf(true)>=0){
					return true;
				}else if(tmpRule.children.length==TmpPopupRes.length){
					return false;
				}
			}
		}
		//console.log('ot_true');
	}
	return;
}

function skyweb24uploadPopups(){

	var tmpKey=[]
	for(var key in skyweb24Popups.popups){
		tmpKey.push(key);
	}
	if(tmpKey.length>0){
		BX.ajax({
			url: '/bitrix/components/skyweb24/popup.pro/ajax.php',
			data: {
				'type':'getTemplatePath',
				'popupIds':tmpKey
			},
			method: 'POST',
			dataType: 'json',
			timeout:300,
			async: true,
			/* scriptsRunFirst:true, */
			onsuccess: function(data){
                // var interval = setInterval(() =>{
                //     var popupContainer = document.getElementById('popup-message');
                //     if(!popupContainer){
                //         clearInterval(interval);
                //         includeAnimationJs(data);
                //         includeTimerScc(data);
                //         skyweb24Popups.popupdata=data;
                //         //uploadPopupsHTML();
                //         setTimeout(skyweb24uploadPopupsHTML, 2);
                //     }
                // },50);

				includeAnimationJs(data);
				includeTimerScc(data);
				skyweb24Popups.popupdata=data;
				//uploadPopupsHTML();
                setTimeout(skyweb24uploadPopupsHTML, 2);
			},
			onfailure: function(data){
				console.log(data);
			}
		});
	}
}

function includeTimerScc(data){
	for(var key in data){
		var nextPopup=data[key];
		if(nextPopup.TIMER_STYLE){
			var newStyle=document.createElement('link');
			newStyle.href = nextPopup.TIMER_STYLE;
			newStyle.rel = 'stylesheet';
			newStyle.type = 'text/css';
			document.getElementsByTagName('head')[0].appendChild(newStyle);
			break;
		}
	}
}

function includeAnimationJs(data){
	var isIncludeScripts=[];
	for(var key in data){
		var nextPopup=data[key];
		if(nextPopup.SHOW_ANIMATION && nextPopup.SHOW_ANIMATION!='none'){
			var fullName='/bitrix/js/skyweb24.popuppro/effect_show_'+nextPopup.SHOW_ANIMATION+'.js';
			if(!BX.util.in_array(fullName, isIncludeScripts)){
				var newScript=document.createElement('script');
				newScript.src = fullName;
				document.getElementsByTagName('head')[0].appendChild(newScript);
				isIncludeScripts.push(fullName);
			}
		}
		if(nextPopup.HIDE_ANIMATION && nextPopup.HIDE_ANIMATION!='none'){
			var fullName='/bitrix/js/skyweb24.popuppro/effect_hide_'+nextPopup.HIDE_ANIMATION+'.js';
			if(!BX.util.in_array(fullName, isIncludeScripts)){
				var newScript=document.createElement('script');
				newScript.src = fullName;
				document.getElementsByTagName('head')[0].appendChild(newScript);
				isIncludeScripts.push(fullName);
			}
		}
	}
}

function skyweb24getPrepolader(){
	if(!skyweb24Popups.preloadBlock){
		var preloadBlock=document.createElement('div');
			preloadBlock.style.position='absolute';
			preloadBlock.style.left='-1000000px';
			preloadBlock.style.top='-1000000px';
			preloadBlock.className='skyweb24PreloadBlock';
		document.body.appendChild(preloadBlock);
		skyweb24Popups.preloadBlock=preloadBlock;
	}
}

var isOpenPopup = false;
function skyweb24uploadPopupsHTML(){
	for(var key in skyweb24Popups.popups){
		(function(){
			var currentKey=key;
			let xhr = new XMLHttpRequest();
			xhr.open('POST', '/bitrix/components/skyweb24/popup.pro/ajax.php?type=getHTML&popupId='+key, true);
			xhr.send();
			xhr.onreadystatechange = function(){
				if (xhr.status == 200 && xhr.readyState==4){
					var intreval = setInterval(() =>{
						skyweb24Popups.popupdata[currentKey].DATA=xhr.responseText;
						skyweb24getPrepolader();
						var tmpNode=document.createElement('div');
						tmpNode.innerHTML=xhr.responseText;
						var tmpImgs=tmpNode.querySelectorAll('img');
						if(tmpImgs.length>0){
							for(var i=0; i<tmpImgs.length; i++){
								skyweb24Popups.preloadBlock.appendChild(tmpImgs[i]);
							}
						}
						clearInterval(intreval);
					}, 100);
				}else{
					//console.log(xhr.status + ': ' + xhr.statusText);
				}
			}
        })();
	}
}

function checkElement(selector) {
    if (document.querySelector(selector) !== null) {
        return rafAsync().then(() => checkElement(selector));
    } else {
		return Promise.resolve(true);
    }
}

function rafAsync() {
    return new Promise(resolve => {
        requestAnimationFrame(resolve); 
    });
}

function skyweb24showPopup(popupId,where){
    if(where == undefined){where=''}
	popupId=popupId.toString();
	if(skyweb24Popups.popupdata && skyweb24Popups.popupdata[popupId] && skyweb24Popups.popupdata[popupId].DATA){

        if(skyweb24Popups.currentPopup){
            //queue popups
            if(!skyweb24Popups.queue){
                skyweb24Popups.queue={};
            }
            skyweb24Popups.queue[popupId]=where;
            return;
        }

		BX.remove(BX('skyweb24_popup_style'));
		BX.remove(BX('skyweb24_popup_color'));
		var head = document.getElementsByTagName('head')[0];
		var s_tepmlate = BX.create('link', {'attrs':{
			'id':'skyweb24_popup_style',
			'type':'text/css',
			'rel':'stylesheet',
			'href':skyweb24Popups.popupdata[popupId].STYLE
		}});
		head.appendChild(s_tepmlate);

		if(skyweb24Popups.popupdata[popupId].THEME){
			var s_color = BX.create('link', {'attrs':{
				'id':'skyweb24_popup_color',
				'type':'text/css',
				'rel':'stylesheet',
				'href':skyweb24Popups.popupdata[popupId].THEME
			}});
			head.appendChild(s_color);
		}
		
		var backColor=(skyweb24Popups.popupdata[popupId].BACKGROUND_COLOR && skyweb24Popups.popupdata[popupId].BACKGROUND_COLOR!='')?skyweb24Popups.popupdata[popupId].BACKGROUND_COLOR:'#000';
		var backOpacity=(skyweb24Popups.popupdata[popupId].BACKGROUND_OPACITY && skyweb24Popups.popupdata[popupId].BACKGROUND_OPACITY!='')?skyweb24Popups.popupdata[popupId].BACKGROUND_OPACITY:50;
		backOpacity=backOpacity*1;
		var closeIcon=true;
		if(skyweb24Popups.popupdata[popupId].SHOW_CLOSEBUTTON && skyweb24Popups.popupdata[popupId].SHOW_CLOSEBUTTON!=''){
			closeIcon=(skyweb24Popups.popupdata[popupId].SHOW_CLOSEBUTTON=='Y')?true:false;
		}
		var autoHide=true;
		if(skyweb24Popups.popupdata[popupId].CLOSE_AUTOHIDE && skyweb24Popups.popupdata[popupId].CLOSE_AUTOHIDE!=''){
			autoHide=(skyweb24Popups.popupdata[popupId].CLOSE_AUTOHIDE=='Y')?true:false;
		}
		var popup = new BX.PopupWindow("popup-message", null, {
			content: "---",
			autoHide:autoHide,
			zIndex: 0,
			offsetTop : 1,
			offsetLeft : 0,
			className: 'sw24PopupPro',
			lightShadow : true,
			closeIcon : closeIcon,
			closeByEsc : true,
			onPopupClose: function(){
				//console.log('close');
			},
			overlay:{
				backgroundColor:backColor,
				opacity:backOpacity
			},
			events:{
				onAfterPopupShow: function(){
					if(skyweb24Popups.popupdata[popupId].VIDEO_AUTOPLAY){
						skyweb24PopupTargetAction();
					}
					skyweb24positionBanner(popup);
					
					let closeButton=this.contentContainer.querySelector(".sw24TextCloseButton"),
						self=this;
					if(closeButton){
						closeButton.addEventListener('click', function(){
							self.close();
						})
					}
                },
                onPopupDestroy:function(){
                    if(skyweb24Popups.queue){
                        for(var key in skyweb24Popups.queue){
                            var tmpKey=key, tmpWhere=skyweb24Popups.queue[key];
                            delete skyweb24Popups.queue[key];
                            skyweb24showPopup(tmpKey,tmpWhere);
                            break;
                        }
                    }
                },
				onPopupShow: function(){
					var tmpProps=skyweb24Popups.popupdata[popupId];
					if(tmpProps.SHOW_ANIMATION && tmpProps.SHOW_ANIMATION!='none'){
						var elems=BX("popup-message").childNodes;
						for(var i=0; i<elems.length; i++){
							elems[i].style.opacity=0;
						}
					}
				}
			}
		});

		popup.close=function(event){
			if(event !== undefined){
				isOpenPopup = false;
			}
			if (!this.isShown()){
				return;
			}

			if (event && !(BX.getEventButton(event) & BX.MSLEFT)){
				return true;
			}
 
			BX.onCustomEvent(this, "onPopupClose", [this, event]);

			//this.hideOverlay();
			//this.popupContainer.style.display = "none";

			if (this.isCloseByEscBinded)
			{
				BX.unbind(document, "keyup", BX.proxy(this._onKeyUp, this));
				this.isCloseByEscBinded = false;
			}

			if(skyweb24Popups.popupdata[popupId].HIDE_ANIMATION && skyweb24Popups.popupdata[popupId].HIDE_ANIMATION!='none'){
                startCustomAnimation(popup, skyweb24Popups.popupdata[popupId].HIDE_ANIMATION);
				return;
			}else{
				this.hideOverlay();
				this.popupContainer.style.display = "none";
				setTimeout(BX.proxy(this.destroy, this), 0);
            }
		}
	
		//inner scripts
		var tmpData=skyweb24Popups.popupdata[popupId].DATA;
		var result = tmpData.match(/<script[^>]*>([\s\S]*?)<\/script>/g);
		var tmpInnerJs=[], tmpOuterJs=[];
		if(result && result.length>0){
			for(var n = 0; n < result.length; n++){
				var nextRes=result[n].match(/<script[^>]*>([\s\S]*?)<\/script>/);
				if(nextRes){
					tmpInnerJs.push(nextRes[1]);
					tmpData.replace(nextRes[0], '');
					if(nextRes[1]==''){
						tmpOuterJs.push(nextRes[0]);
					}
				}
			}
		}
		
		popup.setContent(tmpData);

		/*if(skyweb24Popups.currentPopup){
			skyweb24Popups.currentPopup.close();
        }*/
		skyweb24Popups.currentPopup=popup;
		skyweb24Popups.currentPopupId=popupId;
		skyweb24Popups.currentPopupStartTime=Math.floor(Date.now()/1000);
		
		//inner scripts		
		/*var scripts = popup.getPopupContainer().getElementsByTagName('script');
		if(scripts){
			for (var n = 0; n < scripts.length; n++){
					eval(scripts[n].innerHTML);
			}
		}*/
		if(tmpOuterJs.length>0){
			for(var n = 0; n < tmpOuterJs.length; n++){
				var tmpEl=document.createElement('div');
				tmpEl.innerHTML=tmpOuterJs[n];
				var tmpS=tmpEl.querySelector("script");
				if(tmpS.src){
					var tmpOuterS=document.createElement('script');
					tmpOuterS.src=tmpS.src;
					popup.popupContainer.appendChild(tmpOuterS);
				}
			}
			setTimeout(function(){
				if(tmpInnerJs.length>0){
					for(var n = 0; n < tmpInnerJs.length; n++){
						if(tmpInnerJs[n]!=''){
							var nextScript=document.createElement('script');
							nextScript.innerHTML=tmpInnerJs[n];
							popup.popupContainer.appendChild(nextScript);
						}
					}
				}
			}, 500);
		}else if(tmpInnerJs.length>0){
			for(var n = 0; n < tmpInnerJs.length; n++){
				if(tmpInnerJs[n]!=''){
					var nextScript=document.createElement('script');
					nextScript.innerHTML=tmpInnerJs[n];
					popup.popupContainer.appendChild(nextScript);
				}
			}
		}
		
		popup.show();

		var targetsAction=popup.contentContainer.querySelectorAll(".sw24TargetAction");
		if(targetsAction.length>0){
			for(var i=0; i<targetsAction.length; i++){
				targetsAction[i].onclick=skyweb24PopupTargetAction;
			}
		}

		//statistic open
		BX.ajax({
			url: '/bitrix/components/skyweb24/popup.pro/ajax.php',
			data: {
				'type':'statisticShow',
				'popupId':popupId
			},
			method: 'POST',
			dataType: 'html',
			timeout:300,
			async: true,
			/* scriptsRunFirst:true, */
			onsuccess: function(data){
				//console.log(data);
			},
			onfailure: function(data){
				console.log(data);
			}
		});

		if(popup.contentContainer.querySelector('.skyweb24_popup_pro_timer .timer')!=null){
			function startTimer(){
				var timer=popup.contentContainer.querySelectorAll('.skyweb24_popup_pro_timer .timer .clock>span:not(.sep)');
				var d,h,m,s;
				try{
					d=timer.item(0).innerHTML;
					h=timer.item(1).innerHTML;
					m=timer.item(2).innerHTML;
					s=timer.item(3).innerHTML;
				}catch(error){
					return;
				}

				if(s==0){
					if(m==0){
						if(h==0){
							if(d==0){
								popup.close();
							}
							d--;
							h=24;
							if(d<10) d='0'+d;
						}
						h--;
						m=60;
						if(h<10) h='0'+h;
					}
					m--;
					if(m<10) m='0'+m;
					s=59;
				}
				else s--;
				if(s<10) s="0"+s;
				popup.contentContainer.querySelector('.skyweb24_popup_pro_timer .timer .clock').innerHTML="<span>"+d+"</span><span class='sep'>:</span><span>"+h+"</span><span class='sep'>:</span><span>"+m+"</span><span class='sep'>:</span><span>"+s+"</span>";
				setTimeout(startTimer,1000);
			}
			startTimer();
		}
		if(popup.contentContainer.querySelector('section.container')!=null){
			var count = paintRoulett();
			BX.bind(BX(popup.contentContainer.querySelector('button.roll_roulette')),'click',function(){roll_roulette_func(count);});
        }
        if(where != 'click'){
            delete skyweb24Popups.popups[popupId];
        }
		

		if(!!skyweb24PopupCookiePlaning[popupId]){
			BX.setCookie('skwb24_popups_'+popupId, 'Y', {expires: skyweb24PopupCookiePlaning[popupId], path:'/'});
		}
	}else{
		setTimeout(skyweb24showPopup, 300, popupId);
	}
}

function skyweb24checkPath(path,classLink){
	for(var i=0;i<path.length;i++){
		if(path[i].tagName=='A'){
			var tmpClass=" "+path[i].className+" ";
			if((tmpClass.indexOf(' '+classLink+' '))!=-1){
				return true;
			}
		}
	}
	return false;
}

function skyweb24openByClick(e){
	var path = e.path || (e.composedPath && e.composedPath()) || composedPath(e.target);
	if(skyweb24checkPath(path,this.classLink)){
		if(skyweb24Popups.popups[this.key])
			var nextPopup=skyweb24Popups.popups[this.key][''];
		if(!!nextPopup){
			var resultCheck = skyweb24CheckGroup(nextPopup,this.key,'','',this.classLink,'');
			if(resultCheck===true){
				e.preventDefault();
				skyweb24showPopup(this.key,'click');
				//delete skyweb24Popups.popups[this.key];
			}
		}
	}
}

function skyweb24showAlreadyGoing(e){
	if(skyweb24Popups.popups[this])
		var nextPopup=skyweb24Popups.popups[this][''];
	if(!!nextPopup){
        if(!skyweb24Popups.popups[this].timerDelay || skyweb24Popups.popups[this].timerDelay=='N'){
            if(e.clientY<50){
                var resultCheck = skyweb24CheckGroup(nextPopup,this,'already','','','');
                if(resultCheck===true){
                    skyweb24showPopup(this,'already');
                }
            }
        }
	}
}

function skyweb24showAnchor(){
	if(!this.key){
		return;
	}
	if(skyweb24Popups.popups[this.key])
		var nextPopup=skyweb24Popups.popups[this.key][''];
	var portHeight=window.innerHeight,
	obj = document.querySelector('[name="'+this.anchor+'"]');
	if(!!obj && nextPopup){
		var targetRect=obj.getBoundingClientRect();
		if(targetRect.bottom>0 && targetRect.top<=portHeight && this.key){
			var resultCheck = skyweb24CheckGroup(nextPopup,this.key,'',this.anchor,'','');
			if(resultCheck===true){
				skyweb24showPopup(this.key,'anchor');
			}
			delete this.key;
			delete skyweb24Popups.popups[this.key];
		}
	}
}

//This function defines whether the client gets to the set range and if isn't present - that how many remained to him
function skyweb24timeIntervalStatus(interval){
	interval=interval.split('#');

	var tmpD=new Date,
		currentDate=tmpD.getHours()*3600+tmpD.getMinutes()*60,
		retArr={inInterval:false};

	for(var i=0; i<2; i++){
		if(interval[i]!=''){
			var tmpTime=interval[i].split(':');
			interval[i]=tmpTime[0]*3600+tmpTime[1]*60;
		}else{
			interval[i]=0;
		}
	}
	if(interval[1]==0){
		interval[1]=86400;
	}
	if(interval[1]>interval[0]){
		if(currentDate>=interval[0] && currentDate<=interval[1]){
			retArr.inInterval=true;
		}else{
			retArr.beforeShow=interval[0]-currentDate;
			if(interval[1]<currentDate){
				retArr.beforeShow+=86400;
			}
		}
	}else{
		if((currentDate>=interval[0] && currentDate>=interval[1]) || (currentDate<=interval[0] && currentDate<=interval[1])){
			retArr.inInterval=true;
		}else{
			retArr.beforeShow=(interval[0]-currentDate)*1000;
		}
	}
	return retArr;
}

function skyweb24PopupClose(){
	//statistic close
	if(skyweb24Popups && skyweb24Popups.currentPopupId && skyweb24Popups.currentPopupStartTime){
		BX.ajax({
			url: '/bitrix/components/skyweb24/popup.pro/ajax.php',
			data: {
				'type':'statisticTime',
				'popupId':skyweb24Popups.currentPopupId.toString(),
				'popupTime':(Math.floor(Date.now()/1000) - skyweb24Popups.currentPopupStartTime)
			},
			method: 'POST',
			dataType: 'html',
			timeout:300,
			/* scriptsRunFirst:true, */
			async: true,
			onsuccess: function(data){
				delete skyweb24Popups.currentPopup;
				delete skyweb24Popups.currentPopupId;
				delete skyweb24Popups.currentPopupStartTime;
			},
			onfailure: function(data){
				console.log(data);
			}
		});
	}
}

function skyweb24PopupTargetAction(){
	//statistic close
	if(skyweb24Popups && skyweb24Popups.currentPopupId){
		BX.ajax({
			url: '/bitrix/components/skyweb24/popup.pro/ajax.php',
			data: {
				'type':'statisticAction',
				'popupId':skyweb24Popups.currentPopupId.toString()
			},
			method: 'POST',
			dataType: 'html',
			timeout:300,
			async: true,
			/* scriptsRunFirst:true, */
			onsuccess: function(data){
				//console.log(data);
			},
			onfailure: function(data){
				console.log(data);
			}
		});
	}
}

BX.addCustomEvent('onPopupClose', function(){
	skyweb24PopupClose();
});

BX.addCustomEvent('onPopupShow', function(){
 	if(this.params.className=='sw24PopupPro'){
		this.contentContainer.parentNode.style.opacity=0;

	}
});

function skyweb24getPosition(posObj){
	var retData=false;
	var positions={};
	if(posObj.POSITION_LEFT && posObj.POSITION_LEFT=='Y'){
		retData=true;
		positions.POSITION_LEFT=true;
	}
	if(posObj.POSITION_RIGHT && posObj.POSITION_RIGHT=='Y'){
		retData=true;
		positions.POSITION_RIGHT=true;
	}
	if(posObj.POSITION_TOP && posObj.POSITION_TOP=='Y'){
		retData=true;
		positions.POSITION_TOP=true;
	}
	if(posObj.POSITION_BOTTOM && posObj.POSITION_BOTTOM=='Y'){
		retData=true;
		positions.POSITION_BOTTOM=true;
	}
	if(posObj.POSITION_FIXED){
		positions.POSITION_FIXED=(posObj.POSITION_FIXED=='Y')?true:false;
	}
	if(retData){
		return positions;
	}
	return false;
}

function skyweb24setPosition(o, pos){
	skyweb24Popups.currentPopup.adjustPosition();
	let tmpProps=skyweb24Popups.popupdata[skyweb24Popups.currentPopupId];
	let isFixed=(tmpProps.POSITION_FIXED && tmpProps.POSITION_FIXED=='Y')?true:false;
	
	let topTimerHeigth = 0;
	let bottomTimerHeigth = 0;
	if(skyweb24Popups.currentPopup.contentContainer.querySelector('.skyweb24_popup_pro_timer.top')){
		topTimerHeigth=skyweb24Popups.currentPopup.contentContainer.querySelector('.skyweb24_popup_pro_timer').clientHeight;
	}
	if(skyweb24Popups.currentPopup.contentContainer.querySelector('.skyweb24_popup_pro_timer.bottom')){
		bottomTimerHeigth=skyweb24Popups.currentPopup.contentContainer.querySelector('.skyweb24_popup_pro_timer').clientHeight;
	}
	
	if(!isFixed && pos==false){
	}else if(isFixed && pos==false){
		let topTimerHeigth=(o.querySelector('.skyweb24_popup_pro_timer.top'))?o.querySelector('.skyweb24_popup_pro_timer').clientHeight:0,
			tmpTop=Math.round((document.documentElement.clientHeight-o.offsetHeight-topTimerHeigth)/2),
			tmpLeft=Math.round((document.documentElement.clientWidth-o.offsetWidth)/2);
			o.style.position='fixed';
			o.style.top=tmpTop+'px';
			o.style.left=tmpLeft+'px';
			if(pos.POSITION_BOTTOM){
				o.style.bottom= '0';
				o.style.top= '';
			}
			if(pos.POSITION_TOP){
				o.style.bottom= '';
				o.style.top= '0';
			}
			if(pos.POSITION_RIGHT){
				o.style.right='0';
				o.style.left='';
			}
			if(pos.POSITION_LEFT){
				o.style.right='';
				o.style.left='0';
			}
	}else{
		if(pos.POSITION_BOTTOM){
			if(pos.POSITION_FIXED){
				o.style.bottom=(0+bottomTimerHeigth*1)+'px';
				o.style.top= '';
			}else{
				var tmpStyle=getComputedStyle(o);
				o.style.bottom= bottomTimerHeigth + 'px';
				o.style.top= '';
				//fix body relative
				var tmpBodyStyle=getComputedStyle(document.body);
				if(tmpBodyStyle.position=='relative'){
					o.style.top=(document.documentElement.scrollTop+document.documentElement.clientHeight-parseInt(tmpStyle.height)-bottomTimerHeigth)+'px';
					o.style.bottom = '';
				}
			}
		}else if(pos.POSITION_TOP){
			if(pos.POSITION_FIXED){
				o.style.bottom='';
				o.style.top=(0+topTimerHeigth*1)+'px';
			}else{
				o.style.bottom='';
				o.style.top=document.documentElement.scrollTop+topTimerHeigth+'px';
			}
		}

		if(pos.POSITION_LEFT){
			o.style.right='';
			o.style.left='0';
		}else if(pos.POSITION_RIGHT){
			o.style.left='';
			o.style.right='0';
		}
		if(pos.POSITION_FIXED){
			o.style.position='fixed';
		}
	}
	
	//animation and opasity
	var youtube = skyweb24Popups.currentPopup.contentContainer.querySelectorAll('#skyweb24_video_youtube'); 
	var rollete = skyweb24Popups.currentPopup.contentContainer.querySelectorAll('#skyweb24_roulette');
	var discount = skyweb24Popups.currentPopup.contentContainer.querySelectorAll('#skyweb24_coupon_coupon');
	var popup = skyweb24Popups.currentPopup;
	if(tmpProps.SHOW_ANIMATION && tmpProps.SHOW_ANIMATION!='none'){
			
		//if youtube wait load
		if(youtube.length!=0){
			var iframe = skyweb24Popups.currentPopup.contentContainer.querySelector('iframe');
			iframe.onload = function(){
				//skyweb24Popups.currentPopup.adjustPosition();
				startCustomAnimation(popup, tmpProps.SHOW_ANIMATION);
			}
		} 
		else if(discount.length>0 || rollete.length>0){
			
			var styles = window.document.styleSheets;
			if(isStylesFound){
				startCustomAnimation(popup, tmpProps.SHOW_ANIMATION);
				isStylesFound = false; 
			}
			else{
				for(var i in styles){
					if(isNaN(i)){
						continue;
					}
					if(styles[i].href&&styles[i].href.indexOf(tmpProps.STYLE) !== -1){
						isStylesFound = true;
						break;
					}
				}
				//skyweb24positionBanner(popup);
				setTimeout(function(){skyweb24positionBanner(popup);}, 20);
				return;
			}
		}
		else{
			startCustomAnimation(popup, tmpProps.SHOW_ANIMATION);
		}
		
	}else{
		if(youtube.length!=0){
			var iframe = skyweb24Popups.currentPopup.contentContainer.querySelector('iframe');
			iframe.onload = function(){
				skyweb24Popups.currentPopup.adjustPosition();
				BX("popup-message").style.opacity = 1;
				var elems=BX("popup-message").childNodes;
				for(var i=0; i<elems.length; i++){
					elems[i].style.opacity=1;
				}
			}
		}
		else{
			BX("popup-message").style.opacity = 1;
			var elems=BX("popup-message").childNodes;
			for(var i=0; i<elems.length; i++){
				elems[i].style.opacity=1;
			}
		}	
	}
	
}

var isStylesFound = false;
function skyweb24positionBanner(popup){
    var _this=skyweb24Popups.currentPopup;
	var tmpRect=_this.popupContainer.getBoundingClientRect();
	if(tmpRect.width==0 || tmpRect.height==0){
		setTimeout(function(){
			skyweb24positionBanner(popup);
		}, 100);
	}else{
		setTimeout(function(popup){
			if(_this.params.className=='sw24PopupPro'){
				var currentPos=skyweb24getPosition(skyweb24Popups.popupdata[skyweb24Popups.currentPopupId]);
                var img = skyweb24Popups.currentPopup.contentContainer.querySelectorAll('img');

                var youtube = skyweb24Popups.currentPopup.contentContainer.querySelectorAll('#skyweb24_video_youtube'); 
                var rollete = skyweb24Popups.currentPopup.contentContainer.querySelectorAll('#skyweb24_roulette');
                
				if(img.length==0 && youtube.length==0){
					if(currentPos===false){
                        if(rollete.length > 0){
							setTimeout(function(){
								skyweb24setPosition(_this.contentContainer.parentNode, currentPos);
							}, 200);
                        }else{
							skyweb24setPosition(_this.contentContainer.parentNode, currentPos);
                        }
					}else{
						skyweb24setPosition(_this.contentContainer.parentNode, currentPos);
					}
				}else{
					if(currentPos===false){
                        if(img.length!=0){
                            var container=_this.contentContainer
                            var innerImgs=img.length;
                            for(var i = 0; i<img.length;i++){
								if(img[i].offsetHeight){
									innerImgs--;
								}else{
									setTimeout(function(){
										skyweb24positionBanner(popup);
									}, 100);
									return;
								}
                            }
							if(innerImgs<1){
								skyweb24setPosition(_this.contentContainer.parentNode, currentPos);
							}
                        }else{
							skyweb24setPosition(_this.contentContainer.parentNode, currentPos);
						}
						
					}else{
						skyweb24setPosition(_this.contentContainer.parentNode, currentPos);
					}
				}
			}
		}, 30, popup);
	}
}

function startCustomAnimation(obj, type){
	var tmpRect=obj.popupContainer.getBoundingClientRect();
	if(tmpRect.width==0){
		setTimeout(function(){
			startCustomAnimation(obj, type)
		}, 50);
	}else{
		setTimeout(function(){
			skyweb24_effects.show(obj, type);
		}, 100);
	}
}

window.onbeforeunload = function(){
	skyweb24PopupClose();
};

function composedPath (el) {

    var path = [];

    while (el) {

        path.push(el);

        if (el.tagName === 'HTML') {

            path.push(document);
            path.push(window);

            return path;
       }

       el = el.parentElement;
    }
}