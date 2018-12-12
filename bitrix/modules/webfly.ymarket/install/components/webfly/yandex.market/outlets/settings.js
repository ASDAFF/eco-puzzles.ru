function OnOutletsOptionsYMarket(arParams) {
    if (null != window.outletsFiller) {
        window.outletsFiller = null;
    }
    window.outletsFiller = new wfJCInputFillerOutlets(arParams);
}
/**
 * Main class
 * @param {object} arParams
 * @returns {JCInputFiller}
 */
function wfJCInputFillerOutlets(arParams) {
    this.options = arParams.data.split("|");
    this.max = this.options[0];
    this.current = 0;
    this.arParams = arParams;
    this.obButton = document.createElement('INPUT');
    this.obButton.type = "button";
    this.obButton.value = this.options[1];
    this.arParams.oCont.appendChild(this.obButton);
    this.obButton.onclick = BX.delegate(this.btnClick, this);
    this.saveData = BX.delegate(this.__saveData, this);
    if (this.arParams.oInput.value.length > 0) {
        var anchorList = this.arParams.oInput.value.split("|"),
                anchorListLength = anchorList.length;
        for (var i = 0; i < anchorListLength; i++) {
            var props = anchorList[i].split(","),
                    obAnchor = document.createElement('a');
            obAnchor.href = "#";
            obAnchor.style = "display:block;margin-bottom:10px;";
            obAnchor.setAttribute("dataoutletid", props[0]);
            obAnchor.setAttribute("dataoutletinstock", props[1]);
            obAnchor.setAttribute("dataoutletbooking", props[2]);
            obAnchor.onclick = BX.delegate(this.anchorClick, this);
            obAnchor.innerHTML = this.options[4];
            this.arParams.oCont.insertBefore(obAnchor, this.obButton);
            this.current++;
            if (this.current >= this.max)
                this.obButton.style = "display:none;";
        }
    }
}
/**
 * Click on anchor
 * @param {object} e event object
 * @returns {undefined}
 */
wfJCInputFillerOutlets.prototype.anchorClick = function (e) {
    var that = this,
            anchor = e.target,
            jcOutletId = anchor.getAttribute("dataoutletid"),
            jcOutletInstock = anchor.getAttribute("dataoutletinstock"),
            jcOutletBooking = anchor.getAttribute("dataoutletbooking"),
            btn_save = {
                title: BX.message('JS_CORE_WINDOW_SAVE'),
                id: 'savebtn',
                name: 'savebtn',
                className: BX.browser.IsIE() && BX.browser.IsDoctype() && !BX.browser.IsIE10() ? '' : 'adm-btn-save',
                action: function () {
                    var jcOutletId = BX("jc_OutletId").value,
                            jcOutletInstock = BX("jc_OutletInstock").value,
                            jcOutletBooking = BX("jc_OutletBooking").value,
                            obAnchor = anchor;
                    //if selects empty - take inputs
                    if (jcOutletId == "WF_YM_WRITE")
                        jcOutletId = BX("jc_OutletId_input").value;
                    if (jcOutletInstock == "WF_YM_WRITE")
                        jcOutletInstock = BX("jc_OutletInstock_input").value;
                    if (jcOutletBooking == "WF_YM_WRITE")
                        jcOutletBooking = BX("jc_OutletBooking_input").value;
                    obAnchor.setAttribute("dataoutletid", jcOutletId);
                    obAnchor.setAttribute("dataoutletinstock", jcOutletInstock);
                    obAnchor.setAttribute("dataoutletbooking", jcOutletBooking);
                    that.current++;
                    if (that.current >= that.max)
                        that.obButton.style = "display:none;";
                    that.saveData();
                    this.parentWindow.Close();
                }
            },
    btn_delete = {
        title: that.options[3],
        id: 'deletebtn',
        name: 'deletebtn',
        action: function () {
            that.current--;
            if (that.current < that.max)
                that.obButton.style = "display:block;";
            anchor.parentNode.removeChild(anchor);
            that.saveData();
            this.parentWindow.Close();
        }
    },
    paramsList = that.arParams.getElements(),
            result = paramsList.IBLOCK_ID_IN.childNodes;
    if (!result)
    {
        result = paramsList.IBLOCK_ID_IN[0].children;
    }
    //result = $(paramsList.IBLOCK_ID_IN).find("option"),
    var length = result.length,
            ibs = [];
    for (var i = 0; i < length; i++) {
        if (result[i].selected) {
            ibs.push(result[i].value);
        }
    }
    var strUrl = '/bitrix/components/webfly/yandex.market/outlets/settings.php',
            strUrlPost = 'iblock_id=' + ibs.join(",") + "&outletid=" + jcOutletId + "&outletinstock=" + jcOutletInstock + "&outletbooking=" + jcOutletBooking;
    if (null != window.jsPopupOptionsFiller) {
        window.jsPopupOptionsFiller.DIV.parentNode.removeChild(window.jsPopupOptionsFiller.DIV);
    }
    window.jsPopupOptionsFiller = new BX.CDialog({
        'content_url': strUrl,
        'content_post': strUrlPost,
        'width': 500,
        'height': 200,
        'resizable': true,
        'title': that.options[2],
        'buttons': [BX.CDialog.btnCancel, btn_save, btn_delete]
    });
    window.jsPopupOptionsFiller.Show();
    window.jsPopupOptionsFiller.PARAMS.content_url = '';
    e.preventDefault();
};
/**
 * Click on add
 * @returns {Boolean}
 */
wfJCInputFillerOutlets.prototype.btnClick = function () {
    var that = this;
    if (that.current < that.max) {
        var btn_save = {
            title: BX.message('JS_CORE_WINDOW_SAVE'),
            id: 'savebtn',
            name: 'savebtn',
            className: BX.browser.IsIE() && BX.browser.IsDoctype() && !BX.browser.IsIE10() ? '' : 'adm-btn-save',
            action: function () {
                var jcOutletId = BX("jc_OutletId").value,
                        jcOutletInstock = BX("jc_OutletInstock").value,
                        jcOutletBooking = BX("jc_OutletBooking").value,
                        obAnchor = document.createElement('a');
                //if selects empty - take inputs
                if (jcOutletId == "WF_YM_WRITE")
                    jcOutletId = BX("jc_OutletId_input").value;
                if (jcOutletInstock == "WF_YM_WRITE")
                    jcOutletInstock = BX("jc_OutletInstock_input").value;
                if (jcOutletBooking == "WF_YM_WRITE")
                    jcOutletBooking = BX("jc_OutletBooking_input").value;

                if (jcOutletId.length > 0) {
                    obAnchor.href = "#";
                    obAnchor.style = "display:block;margin-bottom:10px;";
                    obAnchor.setAttribute("dataoutletid", jcOutletId);
                    obAnchor.setAttribute("dataoutletinstock", jcOutletInstock);
                    obAnchor.setAttribute("dataoutletbooking", jcOutletBooking);
                    obAnchor.onclick = BX.delegate(that.anchorClick, that);
                    obAnchor.innerHTML = that.options[4];
                    that.arParams.oCont.insertBefore(obAnchor, that.obButton);
                    that.current++;
                    that.saveData();
                    if (that.current >= that.max)
                        that.obButton.style = "display:none;";
                }
                this.parentWindow.Close();
            }
        };
        var paramsList = that.arParams.getElements(),
                result = paramsList.IBLOCK_ID_IN.childNodes;
        if (!result)
        {
            result = paramsList.IBLOCK_ID_IN[0].children;
        }
        //result = $(paramsList.IBLOCK_ID_IN).find("option"),
        var length = result.length,
                ibs = [];
        for (var i = 0; i < length; i++) {
            if (result[i].selected) {
                ibs.push(result[i].value);
            }
        }
        var strUrl = '/bitrix/components/webfly/yandex.market/outlets/settings.php',
                strUrlPost = 'iblock_id=' + ibs.join(",");
        if (null != window.jsPopupOptionsFiller) {
            window.jsPopupOptionsFiller.DIV.parentNode.removeChild(window.jsPopupOptionsFiller.DIV);
        }
        window.jsPopupOptionsFiller = new BX.CDialog({
            'content_url': strUrl,
            'content_post': strUrlPost,
            'width': 500,
            'height': 200,
            'resizable': true,
            'title': that.options[2],
            'buttons': [BX.CDialog.btnCancel, btn_save]
        });
        window.jsPopupOptionsFiller.Show();
        window.jsPopupOptionsFiller.PARAMS.content_url = '';
    } else {
        that.obButton.style = "display:none;";
    }
    return false;
};
/**
 * Saves data
 * @returns {undefined}
 */
wfJCInputFillerOutlets.prototype.__saveData = function () {
    var result = [],
            anchors = BX.findChildren(BX(this.arParams.oCont), {"tag": "a"}),
            anhorsLength = anchors.length;
    for (var i = 0; i < anhorsLength; i++) {
        result.push(anchors[i].getAttribute("dataoutletid") + "," + anchors[i].getAttribute("dataoutletinstock") + "," + anchors[i].getAttribute("dataoutletbooking"));
    }
    this.arParams.oInput.value = result.join("|");
};