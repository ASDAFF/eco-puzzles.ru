window.Shiptor = window.Shiptor || {};
window.Shiptor.Map = {
    instance: null,
    arMarkers: null,
    init: function(params){
        this.arMarkers = [];
        params.elem.innerHTML = "";
        this.instance = new ymaps.Map(params.elem,
            {
                center: [parseFloat(params.latitude), parseFloat(params.longitude)],
                zoom: 14,
                controls: ['zoomControl', 'fullscreenControl']
            }, {
            searchControlProvider: 'yandex#search'
        });
    },
    setMarkers: function(arPvzList,callback){
        var iMapLength = arPvzList.length;
        for (var i = 0;i < iMapLength;i++){
            this.arMarkers[i] = new ymaps.Placemark(
                [parseFloat(arPvzList[i].gps_location.latitude), parseFloat(arPvzList[i].gps_location.longitude)],
                {
                    hintContent: arPvzList[i].address,
                    balloonContentHeader: arPvzList[i].address,
                    shiptorElemIndex:i
                }, {
                    preset: "islands#blueCircleDotIcon",
                    iconColor: '#2b7788',
                    balloonCloseButton: false,
                    hideIconOnBalloonOpen: false
                }
            );
            this.arMarkers[i].events.add('click', callback);

            this.instance.geoObjects.add(this.arMarkers[i]);
        }
    },
    changeMarker: function(index){
        var isMarkerLength = this.arMarkers.length;
        for(var i = 0; i < isMarkerLength;i++){
            this.arMarkers[i].options.set("preset", "islands#blueCircleDotIcon");
            this.arMarkers[i].options.set("iconColor", "#2b7788");
        }
        this.arMarkers[index].options.set("preset", "default#truckIcon");
        this.arMarkers[index].options.set("iconColor", "#ba0022");
        this.instance.setCenter(this.arMarkers[index].geometry.getCoordinates(), 14, {
            duration: 500,
            checkZoomRange: true
        });
    }
};
window.Shiptor.bxPopup = {
    instance: null,
    init: function(params){
        var clientSizes = this.getClientSizes();
        this.instance = BX.PopupWindowManager.create(params.id, null, {
            autoHide: true,
            offsetLeft: 0,
            offsetTop: 0,
            closeByEsc: true,
            titleBar: true,
            closeIcon: {top: '10px', right: '10px'},
            overlay: {backgroundColor: 'black', opacity: '80' },
            events:{
                onPopupClose: params.onPopupCloseCallback
            },
            buttons: [
                new BX.PopupWindowButton({
                    id:"saveBTN",
                    text: BX.message("SAVE"),
                    className: "popup-window-button-disable",
                    events:{
                        click: params.onPopupSaveCallback
                    }
             })]
        });
        this.setTitle(BX.message("POPUP_TITLE"));
        this.instance.setContent('<div class="row" style="width:'+clientSizes.width+'px;min-height:'+clientSizes.height+'px;"><div class="shiptor-pvz-list"><div class="form-group"><div class="scroll-container" id="containerPVZ"></div></div></div><div class="shiptor-map-container"><div id="map"><small>'+BX.message("LOAD")+'</small></div></div></div>');
        BX('map').style.height = clientSizes.height + "px";
        BX('containerPVZ').style.height = clientSizes.height + "px";
        var windowSizes = BX.GetWindowInnerSize();
        if(windowSizes.innerWidth < 992){
            BX('containerPVZ').style.height = (clientSizes.height - 52) + "px";
        }
        BX('ModalPVZ').style.height = windowSizes.innerHeight + "px";
    },
    setTitle: function(html){
        var title = BX.create('h3');
        title.className = "shiptor-h3-title";
        title.innerHTML = html;
        this.instance.setTitleBar({
            content: title
        });
    },
    getClientSizes: function(){
        var windowSizes = BX.GetWindowInnerSize(),
            clientSizes = {};
        if(windowSizes.innerWidth < 992){
            clientSizes.width = windowSizes.innerWidth * 1;
            clientSizes.height = windowSizes.innerHeight * 1;
        }else{
            clientSizes.width = windowSizes.innerWidth * 0.9;
            clientSizes.height = windowSizes.innerHeight * 0.85;
        }
        return clientSizes;
    },
    show: function(){
        this.instance.show();
        document.body.style.overflow = "hidden";
    }
};
window.Shiptor.Pvz = {
    oContainers: {
        PVZ_ID:"shd_pvz_pick",
        PVZ_INFO: "shd_pvz_info"
    },
    arConfig: null,
    oPVZNode: null,
    method: 0,
    payment: null,
    cod: null,
    kladr: null,
    limits: {},
    oUrls: {
        showPVZ:"/bitrix/tools/shiptor.delivery/ajax/ajaxShowPVZ.php",
        selectPVZ: "/bitrix/tools/shiptor.delivery/ajax/ajaxSelectPVZ.php",
        getLocation: "/bitrix/tools/shiptor.delivery/ajax/getLocation.php"
    },
    arPVZ: [],
    pvzId: 0,
    pvzIndex: -1,
    delieryId: null,
    addressPropId: null,
    init: function(mainContainer){
        if(!!mainContainer){
            var sJson = mainContainer.getAttribute('data-json'),
                oJson = (sJson.length > 0)?JSON.parse(sJson):null;
            this.deliveryId = oJson.delivery;
            this.method = oJson.method;
            this.cod = oJson.cod;
            this.payment = oJson.payment;
            this.kladr = oJson.kladr;
            this.pvzId = oJson.pvz?oJson.pvz:0;
            this.addressPropId = oJson.address_prop_id;
            this.limits = oJson.limits;
            console.info('Shiptor #'+this.deliveryId+' Init!');
            this.initPopupWindow();
        }
    },
    setPvzInfo: function(info){
        var pvzPicker = document.querySelector("#"+this.oContainers.PVZ_ID+"[data-delivery='"+this.deliveryId+"']");
        if(!!pvzPicker){
            this.oPVZNode = pvzPicker.previousElementSibling;
            this.oPVZNode.innerHTML = info;
        }
    },
    onPickerClick: function(button){
        this.init(button.parentNode);
        var sessid = BX.bitrix_sessid(),
            pvzParams = {sessid: sessid, kladr: this.kladr, deliveryId: this.deliveryId, id: this.method,
                limits: this.limits
            };
        BX.ajax.post(this.oUrls.showPVZ, pvzParams, BX.proxy(this.createPvzList,this));
        return false;
    },
    createPvzList: function(dataResult){
        dataResult = BX.parseJSON(dataResult);
        if (dataResult.success == true){
            this.arPVZ = dataResult.pvz;
            var pvz_id_tmp = this.pvzId;
            this.pvzId = 0;
            this.pvzIndex = -1;
            window.Shiptor.bxPopup.setTitle(BX.message("POPUP_TITLE"));
            BX.cleanNode(BX('containerPVZ'));
            for(var i in dataResult.pvz){
                if(dataResult.pvz.hasOwnProperty(i)){
                    var element = dataResult.pvz[i],
                        oRadio = BX.create("div",{props:{className:"rdio",id:"id_"+element.id},
                            html:'<label for="pvz_' + element.id + '" data-address="' + element.address + '" >\n\
<div class="sh_rdio_text"><input type="radio" onclick="window.Shiptor.Pvz.changeLabel(' + i + ')" name="delivery-option" id="pvz_' + element.id + '" value="' + element.id + '"></div></label>'});
                    oRadio = this.getRadio(element,oRadio);
                    BX('containerPVZ').appendChild(oRadio);
                    if (pvz_id_tmp == element.id) {
                        this.pvzId = element.id;
                        this.pvzIndex = i;
                    }
                }
            };
            window.Shiptor.bxPopup.show();
            ymaps.ready(BX.proxy(this.mapinit,this));
            return true;
        }else{
            alert(BX.message("ERROR") + dataResult.message);
        }
        return true;
    },
    getRadio: function(element,oRadio){
        var arBool2Text = {true: BX.message("DA_TEXT"),false:BX.message("NET_TEXT")},
            cardText = BX.message("CARD_TEXT") + arBool2Text[element.card],
            codText = BX.message("COD_TEXT") + arBool2Text[element.cod],
            labelText = BX.create("span"),
            divText = BX.create("div"),
            phones = null,iCard = null, rdio = null, iCash = null,
            additional1 = BX.create("p");

        labelText.innerHTML = element.address;
        labelText.style.paddingLeft = "5px";
        labelText.style.display = "table-cell";
        labelText.style.verticalAlign = "middle";

        divText.className = "text";
        if(element.phones.length > 0){
            phones = BX.create("p");
            phones.className = "additional phones";
            phones.innerHTML = BX.message("TEL_TEXT")+ element.phones.join(', ');
            divText.appendChild(phones);
        }

        additional1.className = "additional";
        additional1.innerHTML = element.work_schedule + (element.trip_description?'<br style="margin-bottom: 5px;"/>' + element.trip_description + '</p>':"");
        divText.appendChild(additional1);

        oRadio.querySelector("label div").appendChild(labelText);
        var icoHolder = BX.create('div');
        icoHolder.className = 'shd_ico_holder';
        if(element.card){
            iCard = BX.create("i");
            iCard.className = "fa fa-credit-card sh_pvz_pay_icon";
            iCard.setAttribute("aria-hidden","true");
            iCard.title = cardText;
            icoHolder.appendChild(iCard);
        }else{
            rdio = oRadio.querySelector("input[type=radio]");
            if(this.payment == "card" && !!rdio && this.cod){
                rdio.disabled = true;
                rdio.parentNode.setAttribute("title",BX.message("CARD_FAIL"));
                rdio.parentNode.style.opacity = "0.5";
            }
        }
        if(element.cod){
            iCash = BX.create("i");
            iCash.className = "fa fa-database sh_pvz_pay_icon";
            iCash.setAttribute("aria-hidden","true");
            iCash.title = codText;
            icoHolder.appendChild(iCash);
        }else{
            rdio = oRadio.querySelector("input[type=radio]");
            if(this.payment == "cash" && !!rdio && this.cod){
                rdio.disabled = true;
                rdio.parentNode.setAttribute("title",BX.message("COD_FAIL"));
                rdio.parentNode.style.opacity = "0.5";
            }
        }
        oRadio.querySelector("label").appendChild(icoHolder);
        oRadio.querySelector("label").appendChild(divText);
        return oRadio;
    },
    mapinit: function(){
        var mapParams = {
            elem: BX("map")
        };
        if (this.arPVZ.length > 0) {
            mapParams.latitude = this.arPVZ[0].gps_location.latitude;
            mapParams.longitude = this.arPVZ[0].gps_location.longitude;
            window.Shiptor.Map.init(mapParams);
            window.Shiptor.Map.setMarkers(this.arPVZ,BX.proxy(this.markerClick,this));
            setTimeout(BX.proxy(this.checkPicked,this), 600);
        }
    },
    markerClick: function(e){
        var properties = e.get('target').properties;
        if(!!properties){
            var i = properties.get("shiptorElemIndex"),
                v = this.arPVZ[i].id;
            this.changeLabel(i);
            this.scrollTo("id_"+v);
        }
    },
    checkPicked: function () {
        if (this.pvzId > 0 && this.pvzIndex >= 0) {
            this.changeLabel(this.pvzIndex);
            this.scrollTo("id_"+this.pvzId);
        } else {
            window.Shiptor.Map.instance.setBounds(window.Shiptor.Map.instance.geoObjects.getBounds(), {checkZoomRange: false, zoomMargin: 5});
        }
    },
    changeLabel: function(index){
        var currentPVZ = this.arPVZ[index];
        if(this.payment == "card" && currentPVZ.card == false && this.cod){
            window.Shiptor.bxPopup.setTitle('<strong class="error">'+BX.message("CARD_FAIL")+'</strong>');
            BX.addClass(BX('saveBTN'),"popup-window-button-disable");
            return false;
        }
        if(this.payment == "cash" && currentPVZ.cod == false && this.cod){
            window.Shiptor.bxPopup.setTitle('<strong class="error">'+BX.message("COD_FAIL")+'</strong>');
            BX.addClass(BX('saveBTN'),"popup-window-button-disable");
            return false;
        }
        this.saveChangePVZ(index);
        window.Shiptor.Map.changeMarker(index);
        this.setPvzInfo('<small title="'+currentPVZ.trip_description+'">#'+currentPVZ.id+' '+currentPVZ.address+'<br/>'+currentPVZ.work_schedule+'</small>');
        this.setAddressProp(currentPVZ.address);
    },
    saveChangePVZ: function(index){
        var currentPVZ = this.arPVZ[index],
            id = currentPVZ.id;
        if(this.payment == "card" && currentPVZ.card == false && this.cod){
            window.Shiptor.bxPopup.setTitle('<strong class="error">'+BX.message("CARD_FAIL")+'</strong>');
            BX.addClass(BX('saveBTN'),"popup-window-button-disable");
        }
        if(this.payment == "cash" && currentPVZ.cod == false && this.cod){
            window.Shiptor.bxPopup.setTitle('<strong class="error">'+BX.message("COD_FAIL")+'</strong>');
            BX.addClass(BX('saveBTN'),"popup-window-button-disable");
        }
        if (id > 0){
            var checkedRadio = BX("containerPVZ").querySelector(".rdio.checked input");
            if(checkedRadio != null){
                checkedRadio.checked = false;
                BX.removeClass(BX("containerPVZ").querySelector(".rdio.checked"),"checked");
            }
            BX.addClass(BX("id_" + id),"checked");
            BX("id_" + id).querySelector("input").checked = true;
            var address = BX("id_" + id).querySelector('label').getAttribute("data-address");
            if (typeof address !== "undefined" && address.length > 10) {
                window.Shiptor.bxPopup.setTitle(BX.message("CHOSEN_TITLE")+": "+'<strong class="success">' + address + '</strong>');
                BX.removeClass(BX('saveBTN'),"popup-window-button-disable");
            } else {
                window.Shiptor.bxPopup.setTitle('<strong class="error">'+BX.message("NCHOSEN_TITLE")+'</strong>');
                BX.addClass(BX('saveBTN'),"popup-window-button-disable");
                return false;
            }
            var sessid = BX.bitrix_sessid();
            BX.ajax.post(this.oUrls.selectPVZ, {sessid: sessid, id: id, currentPVZ: currentPVZ, deliveryId:this.deliveryId}, function (resPVZ_ID) {
                resPVZ_ID = BX.parseJSON(resPVZ_ID);
                if (resPVZ_ID.success == true){
                    console.info('resPVZ_ID.message = ' + resPVZ_ID.message);
                }else{
                    console.info('resPVZ_ID.message = ' + resPVZ_ID.message);
                }
            });
            this.pvzId = id;
        } else {
            window.Shiptor.bxPopup.setTitle('<strong class="error">'+BX.message("NCHOSEN_TITLE")+'</strong>');
            BX.addClass(BX('saveBTN'),"popup-window-button-disable");
            return false;
        }
    },
    setAddressProp: function(address){
        var addressProp = document.querySelector('[name="ORDER_PROP_'+this.addressPropId+'"]');
        if(!!addressProp){
            addressProp.value = address;
        }
    },
    initPopupWindow: function(){
        var popupParams = {
            id: "ModalPVZ",
            onPopupCloseCallback: BX.proxy(this.onPopupWindowClose,this),
            onPopupSaveCallback: BX.proxy(this.onPopupSave,this)
        };
        window.Shiptor.bxPopup.init(popupParams);
    },
    scrollTo: function(elementId){
        BX("containerPVZ").scrollTop = BX(elementId).offsetTop - 150;
    },
    onPopupWindowClose: function(){
        document.body.style.overflow = 'auto';
        var error = false;
        if (this.pvzId > 0) {
            var address = BX("id_" + this.pvzId).querySelector('label').getAttribute('data-address');
            if (typeof address !== "undefined" && address.length > 10){
                error = false;
            }else{
                error = true;
            }
        } else {
            error = true;
        }
        if (error === true) {
            if (confirm(BX.message("NCHOSEN_CONFIRM"))){

            }
        }else{
            this.pvzId = 0;
            this.pvzIndex = -1;
            BX.onCustomEvent('onDeliveryExtraServiceValueChange');
            return true;
        }
        return false;
    },
    onPopupSave: function(){
        window.Shiptor.bxPopup.instance.close();
    }
};
window.Shiptor.AdminPvz = {
    oForm: null,
    oContainers: {},
    arMethods: [],
    arPVZ: [],
    currentKLADR: 0,
    currentMethod: 0,
    limits: {},
    locationPropId: 0,
    currentLocationCode: 0,
    pvzId: 0,
    pvzIndex: -1,
    methodPropId: null,
    addrPropId: null,
    pvzPropId: null,
    kladrPropId: null,
    oMethodSelect: null,
    orderId: null,
    shipmentId: null,
    deliveryPrice: null,
    messages: {},
    config: null,
    deliveryId: null,
    paymentType: null,
    cod: true,
    init: function (params){
        console.info('Shiptor Admin Init!');
        this.methodPropId = params.METHOD_PROP_ID;
        this.pvzPropId = params.PVZ_PROP_ID;
        this.addrPropId = params.ADDR_PROP_ID;
        this.locationPropId = params.LOCATION_PROP_ID;
        this.arMethods = params.AVAILABLE_METHODS;
        this.orderId = params.ORDER_ID;
        this.shipmentId = params.SHIPMENT_ID;
        this.deliveryId = params.ID;
        this.config = params.CONFIG;
        this.currentMethod = params.METHOD;
        this.currentKLADR = params.KLADR;
        this.currentLocationCode = params.LOCATION_CODE;
        this.pvzId = params.CURRENT_PVZ;
        this.paymentType = params.PAYMENT_TYPE;
        this.cod = params.BCOD;
        this.limits = params.LIMITS;
        this.oForm = BX("sale_order_edit_form");
        this.setContainers();
        this.initPopupWindow();
        this.createFaCss();
    },
    createFaCss: function(){
        if(!!document.head){
            var link = BX.create("link");
            link.setAttribute("href","https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css");
            link.setAttribute("rel","stylesheet");
            link.setAttribute("type","text/css");
            document.head.appendChild(link);
        }
    },
    setContainers: function(){
        var pvzSelector = '[name="PROPERTIES['+this.pvzPropId+']"]';
        this.oContainers.pvzOld = this.oForm.querySelector(pvzSelector);
        if(this.oContainers.pvzOld === null){
            setTimeout(BX.proxy(this.setContainers,this),200);
        }else{
            this.oContainers.propLocation = this.oForm.querySelector('[name="PROPERTIES['+this.locationPropId+']"]');
            this.oContainers.propAddress = this.oForm.querySelector('[name="PROPERTIES['+this.addrPropId+']"]');
            this.processDefaultFields();
            this.createPvz();
            this.setLocationChanger();
        }
    },
    processDefaultFields: function(){
        BX.hide(this.oContainers.pvzOld);
    },
    createPvz: function(){
        var params = {sessid:BX.bitrix_sessid(), id: this.currentMethod,
            kladr: this.currentKLADR, deliveryId: this.deliveryId,
            limits: this.limits
        };
        BX.ajax.post(window.Shiptor.Pvz.oUrls.showPVZ, params, BX.proxy(this.pvzResult,this));
    },
    pvzResult: function(dataResult){
        dataResult = BX.parseJSON(dataResult);
        if (dataResult.success == true){
            var iPVZlength = dataResult.pvz.length;
            if(iPVZlength > 0){
                this.arPVZ = dataResult.pvz;
                for(var i in this.arPVZ){
                    if(this.arPVZ.hasOwnProperty(i)){
                        if(this.arPVZ[i].id == this.pvzId){
                            this.pvzIndex = i;
                            this.showInfo(this.arPVZ[i].address,{color: "green"});
                        }
                    }
                }
                this.createButton();
            }else{
                this.showError(BX.message("NO_PVZ"));
            }
        }else{
            this.showError(BX.message("NO_PVZ"));
        }
    },
    createButton: function(){
        var button = BX.create("button");
        button.innerHTML = BX.message("SHIPTOR_CHOOSE_PVZ");
        button.onclick = BX.proxy(this.openPicker,this);
        button.type = "button";
        button.className = "adm-btn";
        this.oContainers.pvzOld.parentNode.appendChild(button);
    },
    openPicker: function(){
        window.Shiptor.bxPopup.setTitle(BX.message("POPUP_TITLE"));
        BX.cleanNode(BX('containerPVZ'));
        for(var i in this.arPVZ){
            if(this.arPVZ.hasOwnProperty(i)){
                var element = this.arPVZ[i],
                    oRadio = BX.create("div",{props:{className:"rdio",id:"id_"+element.id},
                        html:'<label for="pvz_' + element.id + '" data-address="' + element.address + '" >\n\
<div class="sh_rdio_text"><input type="radio" onclick="window.Shiptor.AdminPvz.changeLabel(' + i + ')" name="delivery-option" id="pvz_' + element.id + '" value="' + element.id + '"></div></label>'});
                oRadio = this.getRadio(element,oRadio);
                BX('containerPVZ').appendChild(oRadio);
            }
        };
        window.Shiptor.bxPopup.show();
        ymaps.ready(BX.proxy(this.mapinit,this));
    },
    getRadio: function(element,oRadio){
        var arBool2Text = {true: BX.message("DA_TEXT"),false:BX.message("NET_TEXT")},
            cardText = BX.message("CARD_TEXT") + arBool2Text[element.card],
            codText = BX.message("COD_TEXT") + arBool2Text[element.cod],
            labelText = BX.create("span"),
            divText = BX.create("div"),
            phones = null,iCard = null, rdio = null, iCash = null,
            additional1 = BX.create("p");

        labelText.innerHTML = element.address;
        labelText.style.paddingLeft = "5px";
        labelText.style.display = "table-cell";
        labelText.style.verticalAlign = "middle";

        divText.className = "text";
        if(element.phones.length > 0){
            phones = BX.create("p");
            phones.className = "additional phones";
            phones.innerHTML = BX.message("TEL_TEXT")+ element.phones.join(', ');
            divText.appendChild(phones);
        }

        additional1.className = "additional";
        additional1.innerHTML = element.work_schedule + (element.trip_description?'<br style="margin-bottom: 5px;"/>' + element.trip_description:"");
        divText.appendChild(additional1);

        oRadio.querySelector("label div").appendChild(labelText);
        if(element.card){
            iCard = BX.create("i");
            iCard.className = "fa fa-credit-card sh_pvz_pay_icon";
            iCard.setAttribute("aria-hidden","true");
            iCard.title = cardText;
            oRadio.querySelector("label div").appendChild(iCard);
        }else{
            rdio = oRadio.querySelector("input[type=radio]");
            if(this.payment == "card" && !!rdio && this.cod){
                rdio.disabled = true;
                rdio.parentNode.setAttribute("title",BX.message("CARD_FAIL"));
                rdio.parentNode.style.opacity = "0.5";
            }
        }
        if(element.cod){
            iCash = BX.create("i");
            iCash.className = "fa fa-rub sh_pvz_pay_icon";
            iCash.setAttribute("aria-hidden","true");
            iCash.title = codText;
            oRadio.querySelector("label div").appendChild(iCash);
        }else{
            rdio = oRadio.querySelector("input[type=radio]");
            if(this.payment == "cash" && !!rdio && this.cod){
                rdio.disabled = true;
                rdio.parentNode.setAttribute("title",BX.message("COD_FAIL"));
                rdio.parentNode.style.opacity = "0.5";
            }
        }
        oRadio.querySelector("label").appendChild(divText);
        return oRadio;
    },
    mapinit: function(){
        var mapParams = {
            elem: BX("map")
        };
        if (this.arPVZ.length > 0) {
            mapParams.latitude = this.arPVZ[0].gps_location.latitude;
            mapParams.longitude = this.arPVZ[0].gps_location.longitude;
            window.Shiptor.Map.init(mapParams);
            window.Shiptor.Map.setMarkers(this.arPVZ,BX.proxy(this.markerClick,this));
            setTimeout(BX.proxy(this.checkPicked,this), 600);
        }
    },
    markerClick: function(e){
        var properties = e.get('target').properties;
        if(!!properties){
            var i = properties.get("shiptorElemIndex"),
                v = this.arPVZ[i].id;
            this.changeLabel(i);
            this.scrollTo("id_"+v);
        }
    },
    checkPicked: function () {
        if (this.pvzId > 0 && this.pvzIndex >= 0) {
            this.changeLabel(this.pvzIndex);
            this.scrollTo("id_"+this.pvzId);
        } else {
            window.Shiptor.Map.instance.setBounds(window.Shiptor.Map.instance.geoObjects.getBounds(), {checkZoomRange: false, zoomMargin: 5});
        }
    },
    changeLabel: function(index){
        var currentPVZ = this.arPVZ[index];
        if(this.payment == "card" && currentPVZ.card == false && this.cod){
            window.Shiptor.bxPopup.setTitle('<strong class="error">'+BX.message("CARD_FAIL")+'</strong>');
            BX.addClass(BX('saveBTN'),"popup-window-button-disable");
            return false;
        }
        if(this.payment == "cash" && currentPVZ.cod == false && this.cod){
            window.Shiptor.bxPopup.setTitle('<strong class="error">'+BX.message("COD_FAIL")+'</strong>');
            BX.addClass(BX('saveBTN'),"popup-window-button-disable");
            return false;
        }
        this.saveChangePVZ(index);
        window.Shiptor.Map.changeMarker(index);
    },
    saveChangePVZ: function(index){
        var currentPVZ = this.arPVZ[index],
            id = currentPVZ.id;
        if(this.payment == "card" && currentPVZ.card == false && this.cod){
            window.Shiptor.bxPopup.setTitle('<strong class="error">'+BX.message("CARD_FAIL")+'</strong>');
            BX.addClass(BX('saveBTN'),"popup-window-button-disable");
        }
        if(this.payment == "cash" && currentPVZ.cod == false && this.cod){
            window.Shiptor.bxPopup.setTitle('<strong class="error">'+BX.message("COD_FAIL")+'</strong>');
            BX.addClass(BX('saveBTN'),"popup-window-button-disable");
        }
        if (id > 0){
            var checkedRadio = BX("containerPVZ").querySelector(".rdio.checked input");
            if(checkedRadio != null){
                checkedRadio.checked = false;
                BX.removeClass(BX("containerPVZ").querySelector(".rdio.checked"),"checked");
            }
            BX.addClass(BX("id_" + id),"checked");
            BX("id_" + id).querySelector("input").checked = true;
            var address = BX("id_" + id).querySelector('label').getAttribute("data-address");
            if (typeof address !== "undefined" && address.length > 10) {
                window.Shiptor.bxPopup.setTitle(BX.message("CHOSEN_TITLE")+":"+'<strong class="success">' + address + '</strong>');
                BX.removeClass(BX('saveBTN'),"popup-window-button-disable");
            } else {
                window.Shiptor.bxPopup.setTitle('<strong class="error">'+BX.message("NCHOSEN_TITLE")+'</strong>');
                BX.addClass(BX('saveBTN'),"popup-window-button-disable");
                return false;
            }
            this.pvzId = id;
            this.pvzIndex = index;
        } else {
            window.Shiptor.bxPopup.setTitle('<strong class="error">'+BX.message("NCHOSEN_TITLE")+'</strong>');
            BX.addClass(BX('saveBTN'),"popup-window-button-disable");
            return false;
        }
    },
    scrollTo: function(elementId){
        BX("containerPVZ").scrollTop = BX(elementId).offsetTop - 150;
    },
    showError: function(text){
        this.showInfo(text,{color: "red"});
    },
    showInfo: function(text,styles){
        var infoNode = this.oContainers.pvzOld.parentNode.querySelector("#sh_info_node");
        if(!infoNode){
            infoNode = BX.create("span");
            infoNode.id = "sh_info_node";
            this.oContainers.pvzOld.parentNode.insertBefore(infoNode,this.oContainers.pvzOld.parentNode.querySelector("button"));
        }
        if(!!styles){
            for(var style in styles){
                if(styles.hasOwnProperty(style)){
                    infoNode.style[style] = styles[style];
                }
            }
        }
        infoNode.innerHTML = text;
    },
    setAddress: function(address){
        if(!!this.oContainers.propAddress){
            this.oContainers.propAddress.value = address;
        }
    },
    setLocationChanger: function(){
        BX.bind(this.oContainers.propLocation,"change",BX.proxy(this.onLocationChange,this));
    },
    onLocationChange: function(e){
        var currentCode = e.target.value;
        if(currentCode != this.currentLocationCode){
            BX.ajax.post(window.Shiptor.Pvz.oUrls.getLocation,{action: "get_location",locationCode:currentCode},BX.proxy(this.locationChangeResult,this));
        }
    },
    submitFormData: function(){
        this.oForm.querySelector('[name="apply"]').click();
    },
    locationChangeResult: function(d){
        var result = BX.parseJSON(d);
        if(result.success && !!result.location.CITY && !!result.location.KLADR){
            this.currentKLADR = result.location.KLADR;
            console.info(BX.message("SHIPTOR_NEED_RECALC"));
            this.oContainers.pvzOld.value = '';
            this.showInfo('');
            alert(BX.message("SHIPTOR_NEED_RECALC"));
        }else{
            console.warn(result.message);
        }
    },
    initPopupWindow: function(){
        var popupParams = {
            id: "ModalPVZ",
            onPopupCloseCallback: BX.proxy(this.onPopupClose,this),
            onPopupSaveCallback: BX.proxy(this.onPopupSave,this)
        };
        window.Shiptor.bxPopup.init(popupParams);
    },
    onPopupClose: function(){},
    onPopupSave: function(){
        this.oContainers.pvzOld.value = this.pvzId;
        this.showInfo(this.arPVZ[this.pvzIndex].address,{color: "green"});
        this.setAddress(this.arPVZ[this.pvzIndex].address);
        window.Shiptor.bxPopup.instance.close();
        setTimeout(BX.proxy(this.submitFormData,this),200);
    }
};
window.Shiptor.DirectPvz = {
    params: {},
    eMap: {},
    ePvzContainer: null,
    arPVZ: [],
    pvzId: 0,
    pvzIndex: -1,
    init:function(params){
        this.params = params;
        if(this.params.pvzCode){
            this.pvzId = this.params.pvzCode;
        }
        this.createMapDiv();
        this.bindHandlers();
        this.showPvz();
    },
    createMapDiv: function(){
        var mainDirectTable = BX("edit_DIRECT_edit_table"),
            tbody = mainDirectTable.querySelector("tbody"),
            tr = BX.create("tr"),
            tdPvzList = BX.create("td"),
            pvzListDiv = BX.create("div"),
            tdPvzMap = BX.create("td"),
            mapContainerDiv = BX.create("div");
        tr.style.backgroundColor = "white";
        tdPvzList.className = "adm-detail-valign-top adm-detail-content-cell-l";

        pvzListDiv.className = "shiptor-pvz-list";
        pvzListDiv.style.textAlign = "left";

        this.ePvzContainer = BX.create("div");
        this.ePvzContainer.className = "scroll-container";
        this.ePvzContainer.id = "containerPVZ";

        pvzListDiv.appendChild(this.ePvzContainer);
        tdPvzList.appendChild(pvzListDiv);
        tr.appendChild(tdPvzList);

        tdPvzMap.className = "adm-detail-valign-top adm-detail-content-cell-r";

        mapContainerDiv.className = "shiptor-map-container";

        this.eMap = BX.create("div");
        this.eMap.id = "map";
        this.eMap.innerHTML = '<small>'+BX.message("LOAD")+'</small>';

        mapContainerDiv.appendChild(this.eMap);
        tdPvzMap.appendChild(mapContainerDiv);
        tr.appendChild(tdPvzMap);
        tbody.appendChild(tr);
    },
    bindHandlers: function(){
        BX.bind(this.params.LOCATION_FIELD,'change',BX.proxy(this.newLocation,this));
    },
    showPvz: function(){
        if(!this.params.kladr){
            this.showError(BX.message("SHIPTOR_DIRECT_NO_KLADR"));
            this.eMap.style.display = "none";
            return false;
        }
        var params = {sessid: BX.bitrix_sessid(), kladr: this.params.kladr,
            deliveryId:this.params.deliveryId, id:this.params.method,
            selfPickup: true
        };
        BX.ajax.post(window.Shiptor.Pvz.oUrls.showPVZ, params, BX.proxy(this.initMap,this));
    },
    initMap: function(dataResult){
        var oResult = JSON.parse(dataResult);
        if(oResult.success){
            this.arPVZ = oResult.pvz;
            for(var i = 0; i < this.arPVZ.length; i++){
                var element = this.arPVZ[i],
                    oRadio = BX.create("div",{props:{className:"rdio",id:"id_"+element.id},
                        html:'<label for="pvz_' + element.id + '">\n\
                            <input type="radio" onclick="window.Shiptor.DirectPvz.changeLabel(' + i + ')" name="pvz_code" id="pvz_' + element.id + '" value="' + element.id + '"></label>'});
                oRadio = this.getRadio(element,oRadio);
                this.ePvzContainer.appendChild(oRadio);
                if (this.pvzId == element.id) {
                    this.pvzIndex = i;
                }
            }
            ymaps.ready(BX.proxy(this.mapinit,this));
        }else{
            this.showError(oResult.message);
            this.eMap.style.display = "none";
        }
    },
    getRadio: function(element,oRadio){
        var labelText = BX.create("span"),
            divText = BX.create("div"),
            phones = BX.create("p"),
            additional = BX.create("p");
        labelText.innerHTML = element.address;
        labelText.style.paddingLeft = "5px";

        divText.className = "text";

        phones.className = "additional phones";
        if(element.phones.length > 0){
            phones.innerHTML = BX.message("TEL_TEXT")+ element.phones.join(', ');
        }
        divText.appendChild(phones);

        additional.className = "additional";
        additional.innerHTML = element.work_schedule + (element.trip_description?'<br style="margin-bottom: 5px;"/>' + element.trip_description:"");
        divText.appendChild(additional);

        oRadio.querySelector("label").appendChild(labelText);
        oRadio.querySelector("label").appendChild(divText);
        return oRadio;
    },
    mapinit: function(){
        var mapParams = {
            elem: this.eMap
        };
        if (this.arPVZ.length > 0) {
            this.eMap.innerHTML = "";
            mapParams.latitude = this.arPVZ[0].gps_location.latitude;
            mapParams.longitude = this.arPVZ[0].gps_location.longitude;
            window.Shiptor.Map.init(mapParams);
            window.Shiptor.Map.setMarkers(this.arPVZ,BX.proxy(this.markerClick,this));

            setTimeout(BX.proxy(this.checkPicked,this), 600);
        }
    },
    checkPicked: function(){
        if (this.pvzId > 0 && this.pvzIndex >= 0) {
            this.changeLabel(this.pvzIndex);
            this.scrollTo("id_"+this.pvzId);
        } else {
            window.Shiptor.Map.instance.setBounds(window.Shiptor.Map.instance.geoObjects.getBounds(), {checkZoomRange: false, zoomMargin: 5});
        }
    },
    markerClick: function(e){
        var properties = e.get('target').properties;
        if(!!properties){
            var i = properties.get("shiptorElemIndex"),
                v = this.arPVZ[i].id;
            this.changeLabel(i);
            this.scrollTo("id_"+v);
        }
    },
    changeLabel: function(index){
        this.saveChangePVZ(index);
        window.Shiptor.Map.changeMarker(index);
    },
    saveChangePVZ: function(index){
        var id = this.arPVZ[index].id;
        if (id > 0){
            var checkedRadio = this.ePvzContainer.querySelector(".rdio.checked input");
            if(checkedRadio != null){
                checkedRadio.checked = false;
                BX.removeClass(this.ePvzContainer.querySelector(".rdio.checked"),"checked");
            }
            BX.addClass(BX("id_" + id),"checked");
            BX("id_" + id).querySelector("input").checked = true;
            this.pvzId = id;
            this.pvzIndex = index;
            this.params.PVZ_FIELD.value = id;
            this.params.PVZ_ADDR_FIELD.value = this.arPVZ[index].address;
        } else {
            this.showError(BX.message("NCHOSEN_TITLE"));
            return false;
        }
    },
    scrollTo: function(elementId){
        this.ePvzContainer.scrollTop = BX(elementId).offsetTop - 150;
    },
    showError: function(text){
        var eBlock = BX("shiptor_direct_error");
        if(!eBlock){
            eBlock = BX.create("div");
            eBlock.style.color = "red";
            this.params.PVZ_ADDR_FIELD.parentNode.insertBefore(eBlock,this.params.PVZ_FIELD.nextSibling);
        }
        eBlock.innerText = text;
    },
    newLocation: function(e){
        if(e.target.value.length > 0){
            this.pvzId = 0;
            this.params.PVZ_FIELD.value = "";
            this.params.PVZ_FIELD.form.submit();
        }
    }
};
window.Shiptor.checkPvz = function (result){
    switch(typeof result){
        case "object":
            var activeDelivery = null,
                ePicker = null,
                button = null,
                sJson = null,
                oJson = null,
                eActiveDeliveryRadio = null,
                pvzCode = null;
            if(!result.order){
                return true;
            }
            if(!result.order.DELIVERY){
                return true;
            }
            var arDeliveries = result.order.DELIVERY,
                iDeliveryLength = arDeliveries.length;
            if(iDeliveryLength <= 0){
                return true;
            }
            for(var i = 0; i < iDeliveryLength; i ++){
                if(!arDeliveries[i].hasOwnProperty('CHECKED')){
                    continue;
                }
                if(arDeliveries[i].CHECKED == 'Y'){
                    activeDelivery = arDeliveries[i].ID;
                }
            }
            if(!activeDelivery){
                return true;
            }
            eActiveDeliveryRadio = BX("ID_DELIVERY_ID_"+activeDelivery);
            if(!eActiveDeliveryRadio){
                return true;
            }
            if(!eActiveDeliveryRadio.checked){
                return true;
            }
            ePicker = document.querySelector('#shd_pvz_pick[data-delivery="'+activeDelivery+'"][data-force="1"]');
            if(!ePicker){
                return true;
            }
            button = ePicker.querySelector("button");
            if(!button){
                return true;
            }
            sJson = ePicker.getAttribute("data-json");
            oJson = JSON.parse(sJson);
            pvzCode = oJson.pvz;
            if(!pvzCode){
                window.Shiptor.Pvz.onPickerClick(button);
            }
            var addressProp = document.querySelector('[name="ORDER_PROP_'+oJson.address_prop_id+'"]');
            if(!!addressProp){
                addressProp.value = oJson.pvz_address;
            }
            break;
        case "string":default:
            /*if(result.indexOf("Shiptor") !== -1){
                console.log(result.search(/Shiptor\(\d+\)/i));
            }*/
            break;
    }
    return true;
};
BX.addCustomEvent('onAjaxSuccess', window.Shiptor.checkPvz);