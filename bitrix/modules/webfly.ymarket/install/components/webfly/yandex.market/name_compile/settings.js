function OnNameCompileYMarket(arParams) {
    if (null != window.nameCompileFiller) {
        window.nameCompileFiller = null;
    }
    window.nameCompileFiller = new wfJCInputFillerNameC(arParams);
}
/**
 * Main class
 * @param {object} arParams
 * @returns {JCInputFiller}
 */
function wfJCInputFillerNameC(arParams) {
    this.options = arParams.data.split("|");
    this.arParams = arParams;
    this.obButton = document.createElement('INPUT');
    this.obButton.type = "button";
    this.obButton.value = this.options[0];
    this.arParams.oCont.appendChild(this.obButton);
    this.obButton.onclick = BX.delegate(this.btnClick, this);
    this.saveData = BX.delegate(this.__saveData, this);
    if (this.arParams.oInput.value.length > 0) {//скрытый инпут
        var params = this.arParams.oInput.value.split("|");
        var obAnchor = document.createElement('a');
        obAnchor.href = "#";
        obAnchor.style = "display:block;margin-bottom:10px;";
        obAnchor.onclick = BX.delegate(this.anchorClick, this);
        obAnchor.innerHTML = this.__parseParams(params[0].split(","), params[1].split(","));
        obAnchor.setAttribute("data-selects", params[0].split(","));
        obAnchor.setAttribute("data-inputs", params[1].split(","));
        this.arParams.oCont.insertBefore(obAnchor, this.obButton);
        this.obButton.style = "display:none;";
    }
}
/**
 * Click on anchor
 * @param {object} e event object
 * @returns {undefined}
 */
wfJCInputFillerNameC.prototype.anchorClick = function (e) {
    var that = this,
            anchor = e.target,
            jcSelects = anchor.getAttribute("data-selects"),
            jcInputs = anchor.getAttribute("data-inputs"),
            btn_save = {
                title: BX.message('JS_CORE_WINDOW_SAVE'),
                id: 'savebtn',
                name: 'savebtn',
                className: BX.browser.IsIE() && BX.browser.IsDoctype() && !BX.browser.IsIE10() ? '' : 'adm-btn-save',
                action: function () {
                    var count = document.getElementsByClassName("jc_holder").length, //количество дивов
                            jcNameSels = [],
                            jcNameSelsData,
                            jcNameInps = [],
                            jcNameInpsData;
                    for (var i = 0; i < count; i++) {
                        var childs = document.getElementsByClassName("jc_holder")[i].children,
                                childsLength = childs.length;
                        for (var j = 0; j < childsLength; j++) {
                            switch (childs[j].tagName) {
                                case "SELECT":
                                    jcNameSels.push(childs[j].value);
                                    break;
                                case "INPUT":
                                    if (childs[j].type == "text") {
                                        jcNameInps.push(childs[j].value);
                                    }
                                    break;
                                default://br
                            }
                        }
                    }
                    jcNameSelsData = jcNameSels.join(",");
                    jcNameInpsData = jcNameInps.join(",");

                    if (jcNameSelsData.length > 0) {
                        anchor.style = "display:block;margin-bottom:10px;";
                        anchor.setAttribute("data-selects", jcNameSelsData);
                        anchor.setAttribute("data-inputs", jcNameInpsData);
                        anchor.onclick = BX.delegate(that.anchorClick, that);
                        var anchorName = that.__parseParams(jcNameSels, jcNameInps);
                        anchor.innerHTML = anchorName;
                        that.saveData();
                        that.obButton.style = "display:none;";
                    }
                    this.parentWindow.Close();
                }
            },
    btn_delete = {
        title: that.options[1],
        id: 'deletebtn',
        name: 'deletebtn',
        action: function () {
            that.obButton.style = "display:block;";
            anchor.parentNode.removeChild(anchor);
            that.saveData();
            this.parentWindow.Close();
        }
    },
    paramsList = that.arParams.getElements(),
            result = paramsList.IBLOCK_ID_IN.children,
            length = result.length,
            ibs = [];
    for (var i = 0; i < length; i++) {
        if (result[i].selected) {
            ibs.push(result[i].value);
        }
    }
    var strUrl = '/bitrix/components/webfly/yandex.market/name_compile/settings.php',
            strUrlPost = 'iblock_id=' + ibs.join(",") + "&selects=" + jcSelects + "&inputs=" + jcInputs;
    if (null != window.jsPopupOptionsFiller) {
        window.jsPopupOptionsFiller.DIV.parentNode.removeChild(window.jsPopupOptionsFiller.DIV);
    }
    window.jsPopupOptionsFiller = new BX.CDialog({
        'content_url': strUrl,
        'content_post': strUrlPost,
        'width': 520,
        'height': 500,
        'resizable': false,
        'title': that.options[0],
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
wfJCInputFillerNameC.prototype.btnClick = function () {
    var that = this;
    var btn_save = {
        title: BX.message('JS_CORE_WINDOW_SAVE'),
        id: 'savebtn',
        name: 'savebtn',
        className: BX.browser.IsIE() && BX.browser.IsDoctype() && !BX.browser.IsIE10() ? '' : 'adm-btn-save',
        action: function () {
            var count = document.getElementsByClassName("jc_holder").length, //количество дивов
                    jcNameSels = [],
                    jcNameSelsData,
                    jcNameInps = [],
                    jcNameInpsData;
            for (var i = 0; i < count; i++) {
                var childs = document.getElementsByClassName("jc_holder")[i].children,
                        childsLength = childs.length;
                for (var j = 0; j < childsLength; j++) {
                    switch (childs[j].tagName) {
                        case "SELECT":
                            jcNameSels.push(childs[j].value);
                            break;
                        case "INPUT":
                            if (childs[j].type == "text") {
                                jcNameInps.push(childs[j].value);
                            }
                            break;
                        default://br
                    }
                }
            }
            jcNameSelsData = jcNameSels.join(",");
            jcNameInpsData = jcNameInps.join(",");

            var obAnchor = document.createElement('a');
            if (jcNameSelsData.length > 0) {
                obAnchor.href = "#";
                obAnchor.style = "display:block;margin-bottom:10px;";
                obAnchor.setAttribute("data-selects", jcNameSelsData);
                obAnchor.setAttribute("data-inputs", jcNameInpsData);
                obAnchor.onclick = BX.delegate(that.anchorClick, that);
                var anchorName = that.__parseParams(jcNameSels, jcNameInps);
                obAnchor.innerHTML = anchorName;
                that.arParams.oCont.insertBefore(obAnchor, that.obButton);
                that.saveData();
                that.obButton.style = "display:none;";
            }
            this.parentWindow.Close();
        }
    };
    var paramsList = that.arParams.getElements(),
            result = paramsList.IBLOCK_ID_IN.childNodes;
    //result = $(paramsList.IBLOCK_ID_IN).find("option"),
    if (!result)
    {
        result = paramsList.IBLOCK_ID_IN[0].children;
    }
    var length = result.length,
            ibs = [];
    for (var i = 0; i < length; i++) {
        if (result[i].selected) {
            ibs.push(result[i].value);
        }
    }
    var strUrl = '/bitrix/components/webfly/yandex.market/name_compile/settings.php',
            strUrlPost = 'iblock_id=' + ibs.join(",");
    if (null != window.jsPopupOptionsFiller) {
        window.jsPopupOptionsFiller.DIV.parentNode.removeChild(window.jsPopupOptionsFiller.DIV);
    }
    window.jsPopupOptionsFiller = new BX.CDialog({
        'content_url': strUrl,
        'content_post': strUrlPost,
        'width': 520,
        'height': 500,
        'resizable': true,
        'title': that.options[2],
        'buttons': [BX.CDialog.btnCancel, btn_save]
    });
    window.jsPopupOptionsFiller.Show();
    window.jsPopupOptionsFiller.PARAMS.content_url = '';
    return false;
};
/**
 * Saves data
 * @returns {undefined}
 */
wfJCInputFillerNameC.prototype.__saveData = function () {
    var childs = this.arParams.oCont.children,
            childsLength = childs.length;
    for (var i = 0; i < childsLength; i++) {
        if (childs[i].tagName === "A") {
            this.arParams.oInput.value = childs[i].getAttribute("data-selects") + "|" + childs[i].getAttribute("data-inputs");
            return true;
        }
    }
    this.arParams.oInput.value = "";
};
/**
 * Parse selects and inputs
 * @returns {sAnchorName}
 */
wfJCInputFillerNameC.prototype.__parseParams = function (selects, inputs) {
    var paramsCount = selects.length,
        arAnchorName = [],
        sAnchorName = null,
        compParams = this.arParams.data.split("|");
    for (var i = 0; i < paramsCount; i++) {
        if (selects[i].length > 0 && selects[i] != "WF_YM_WRITE")
        {
            arAnchorName.push("[" + selects[i] + "]");
        }
        else
        {
            arAnchorName.push(BX.util.trim(inputs[i]));
        }
    }
    if(arAnchorName.length > 0 && arAnchorName[0].length > 0) sAnchorName = arAnchorName.join(" ");
    else sAnchorName = compParams[2];
    return sAnchorName;
};

