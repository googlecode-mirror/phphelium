/*
 * ajax.js
 * Copyright: Bryan Healey 2010, 2011, 2012 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Handle all AJAX functionality
 */

var ajaxQueue = {
    list: [],
    fired: false,
    timer: false,
    noQueue: false,

    begin: function() {
        ajaxQueue.timer = setTimeout("ajaxQueue.fireNext();",1000);
    },

    ensureSingularity: function(container) {
        for(var i in ajaxQueue.list) {
            if ($.isNumeric(i)) if (ajaxQueue.list[i]['container'] == container) return false;
        }

        return true;
    },

    submit: function(formId,container) {
        if (ajaxQueue.ensureSingularity(container)) {
            if (!ajaxQueue.noQueue || ajaxQueue.list.length == 0) ajaxQueue.list.push({'form':form,'container':container});
            else alert("You must wait");
        }
    },

    request: function(url,method,params,container,onSuccess,onError,onTimeout) {
        if (!onSuccess) var onSuccess = false;
        if (ajaxQueue.ensureSingularity(container)) {
            if (!ajaxQueue.noQueue || ajaxQueue.list.length == 0) ajaxQueue.list.push({'url':url,'method':method,'params':params,'container':container,'onSuccess':onSuccess,'onError':onError,'onTimeout':onTimeout});
            else alert("You must wait");
        }
    },

    fireNext: function() {
        clearTimeout(ajaxQueue.timer);

        if (ajaxQueue.list.length > 0 && ajaxQueue.fired == false) {
            ajaxQueue.fired = true;
            var el = ajaxQueue.list.shift();
            var a = new ajaxSingular();

            if (el.form) {
                a.submit(el.form,el.container);
            } else {
                a.onSuccess = el.onSuccess;
                a.onError = el.onError;
                a.onTimeout = el.onTimeout;

                a.request(el.url,el.method,el.params,el.container);
            }
        }

        ajaxQueue.timer = setTimeout("ajaxQueue.fireNext();",500);
    }
};

ajax = {
    onSuccess: null,
    onFail: null,
    onTimeout: null,

    submit: function(formId,container,params,sFunc,fFunc,tFunc) {
        if (!formId) console.log('You must supply a formId to submit');
        else {
            if (sFunc) ajax.onSuccess = sFunc;
            if (fFunc) ajax.onFail = fFunc;
            if (tFunc) ajax.onTimeout = tFunc;

            ta = new ajaxSingular();
            if (ajax.onSuccess) ta.onSuccess = ajax.onSuccess;
            if (ajax.onFail) ta.onFail = ajax.onFail;
            if (ajax.onTimeout) ta.onTimeout = ajax.onTimeout;
            var a = ta.submit(formId,container,params);

            ajax.onSuccess = null;
            ajax.onFail = null;

            return a;
        }
    },

    request: function(url,method,params,container,sFunc,fFunc,tFunc) {
        if (sFunc) ajax.onSuccess = sFunc;
        if (fFunc) ajax.onFail = fFunc;
        if (tFunc) ajax.onTimeout = tFunc;

        ta = new ajaxSingular();
        if (ajax.onSuccess) ta.onSuccess = ajax.onSuccess;
        if (ajax.onFail) ta.onFail = ajax.onFail;
        if (ajax.onTimeout) ta.onTimeout = ajax.onTimeout;
        var a = ta.request(url,method,params,container);

        ajax.onSuccess = null;
        ajax.onFail = null;

        return a;
    }
};

function ajaxSingular() {
    this.onSuccess = null;
    this.onFail = null;
    this.onTimeout = null;
    this.loader = false;

    this.submit = function(formId,container,params) {
        if ($('#'+formId)) var form = $('#'+formId);
        else return false;

        if (!params) params = {};

        params = params+'&'+form.serialize(true);
        return this.request(form.attr('action'),form.attr('method'),params,container);
    };

    this.request = function(uri,method,params,container) {
        if (!params) var params = {};
        params['AJAX'] = "true";

        var success = this.onSuccess;
        var fail = this.onFail;
        var timo = this.onTimeout;
        var loader = this.loader;
        var ret = false;

        this.onSuccess = null;
        this.onFail = null;
        this.onTimeout = null;

        if (loader) {
            if ($('#'+container)) {
                var loaderImg = document.createElement('img');
                loaderImg.src = '/img/loading.gif';

                var loaderDiv = document.createElement('div');
                loaderDiv.setAttribute('width','100%');
                loaderDiv.style.textAlign = 'center';
                loaderDiv.style.paddingTop = '0.75em';
                loaderDiv.appendChild(loaderImg);

                $('#'+container).update(loaderDiv);
            } else if ($('loader-container')) {
                $('#loader-container').style.display = "block";
            }
        }

        var a = $.ajax({
            type: method,
            url: uri,
            data: params,

            success: function(responseText) {
                try {
                    responseText = $.parseJSON(responseText);
                } catch (e) {
                    if ($('#'+container)) $('#'+container).html(responseText);
                }

                if (success) success(responseText);
                ret = true;

                ajaxQueue.fired = false;

                if ($('#loader-container') !== null) $('#loader-container').css('display','none');
            },

            error: function(responseText,err) {
                if (err == 'timeout') timo(responseText);
                else {
                    if (fail) fail(responseText);
                    ret = false;

                    if ($('#loader-container') !== null) $('#loader-container').css('display','none');
                }

                ajaxQueue.fired = false;
            }
        });

        return a;
    };
};