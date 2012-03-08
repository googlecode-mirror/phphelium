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
            if (Page.isNumeric(i)) if (ajaxQueue.list[i]['container'] == container) return false;
        }

        return true;
    },

    submit: function(formId,container) {
        if (ajaxQueue.ensureSingularity(container)) {
            if (!ajaxQueue.noQueue || ajaxQueue.list.length == 0) ajaxQueue.list.push({'form':form,'container':container});
            else alert("You must wait");
        }
    },

    request: function(url,method,params,container,successFunc) {
        if (!successFunc) var successFunc = false;
        if (ajaxQueue.ensureSingularity(container)) {
            if (!ajaxQueue.noQueue || ajaxQueue.list.length == 0) ajaxQueue.list.push({'url':url,'method':method,'params':params,'container':container,'successFunc':successFunc});
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
                a.successFunc = el.successFunc;
                a.request(el.url,el.method,el.params,el.container);
            }
        }

        ajaxQueue.timer = setTimeout("ajaxQueue.fireNext();",500);
    }
};

ajax = {
    successFunc: null,
    failFunc: null,

    submit: function(formId,container,params,sFunc,fFunc) {
        if (sFunc) ajax.successFunc = sFunc;
        if (fFunc) ajax.failFunc = fFunc;

        ta = new ajaxSingular();
        if (ajax.successFunc) ta.successFunc = ajax.successFunc;
        if (ajax.failFunc) ta.failFunc = ajax.failFunc;
        var a = ta.submit(formId,container,params);

        ajax.successFunc = null;
        ajax.failFunc = null;

        return a;
    },

    request: function(url,method,params,container,sFunc,fFunc) {
        if (sFunc) ajax.successFunc = sFunc;
        if (fFunc) ajax.failFunc = fFunc;

        ta = new ajaxSingular();
        if (ajax.successFunc) ta.successFunc = ajax.successFunc;
        if (ajax.failFunc) ta.failFunc = ajax.failFunc;
        var a = ta.request(url,method,params,container);

        ajax.successFunc = null;
        ajax.failFunc = null;

        return a;
    }
};

function ajaxSingular() {
    this.successFunc = null;
    this.failFunc = null;
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

        var success = this.successFunc;
        var fail = this.failFunc;
        var loader = this.loader;
        var ret = false;

        this.successFunc = null;
        this.failFunc = null;

        if (loader) {
            if ($('#'+container)) {
                var loaderDiv = document.createElement('div');
                loaderDiv.setAttribute('width','100%');
                loaderDiv.style.textAlign = 'center';
                loaderDiv.style.paddingTop = '0.75em';
                loaderDiv.innerHTML = "<img src='/img/loading.gif' />";

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

            error: function(responseText) {
                if (fail) fail(responseText);
                ret = false;

                ajaxQueue.fired = false;

                if ($('#loader-container') !== null) $('#loader-container').css('display','none');
            }
        });

        return a;
    };
};