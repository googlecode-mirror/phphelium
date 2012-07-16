/*
 * main.js
 * Copyright: Bryan Healey 2010, 2011, 2012 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: All collected base functionality
 */

__CARRIAGE = 13;

var sys = {
    openTime: false,

    init: function() {
        Hasher.init();
        $('#popup-container').draggable();
        sys.openTime = DateData.unixtime();
    },

    toggleLanguage: function(language) {
        ajax.onSuccess = function(e) {
            if (e['result'] == 'success') window.location = window.location.pathname;
            else alert('An error has occured');
        }

        ajax.request('/update-language/','POST',{'language':language});
    },
    
    getBrowser: function() {
        var browser = "unknown";
        var agent = navigator.userAgent.toLowerCase();
        var possibleMatches = new Array('safari','msie 6','msie','firefox','netscape','omniweb','avantbrowser','msn','konqueror','camino','chrome');

        for (var i=0;i<possibleMatches.length;i++) {
            if (agent.indexOf(possibleMatches[i]) > -1) {
                browser = possibleMatches[i];
                break;
            }
        }

        return browser;
    },

    setCookie: function(c_name,value,exdays) {
        var exdate=new Date();
        exdate.setDate(exdate.getDate() + exdays);
        var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
        document.cookie = c_name+"="+c_value;
    },

    getCookie: function(c_name) {
        var i,x,y,ARRcookies=document.cookie.split(";");
        for (i=0;i<ARRcookies.length;i++) {
            x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
            y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
            x=x.replace(/^\s+|\s+$/g,"");

            if (x == c_name) return unescape(y);
        }

        return false;
    }
};

var pos = {
    rightOf: function(pnode,obj) {
        var off = $(pnode).offset();
        $(obj).offset({ 'top': (off.top+1), 'left': (off.left+$(pnode).width()+30) });
    }
};

var navAssist = {
    navRegister: {},
    prepareNav: function(navId,defVal) {
        navAssist.navRegister[navId] = {};
        navAssist.navRegister[navId]['active'] = defVal;

        Hasher.init();
        if (Hasher.hash['tab']) navAssist.navRegister[navId]['active'] = Hasher.hash['tab'];

        $('#'+navId).children().each(function(){
            var kid = $(this).attr("id");
            var title = $(this).attr("makeTitle");
            var base = kid.replace("t","");

            if (base == Hasher.hash['tab']) {
                $('#t'+base).addClass('active');
                $('#c'+base).css("display","block");

                if (title !== undefined) document.title = title;
                navAssist.navRegister[navId]['active'] = base;
            } else {
                $('#t'+base).removeClass('active');
                $('#c'+base).css("display","none");
            }

            $('#t'+base).click(function() {
                if (title !== undefined) document.title = title;
                Hasher.set('tab',base);
                $('#t'+navAssist.navRegister[navId]['active']).removeClass('active');
                $('#c'+navAssist.navRegister[navId]['active']).css("display","none");

                $('#t'+base).addClass('active');
                $('#c'+base).css("display","block");

                navAssist.navRegister[navId]['active'] = base;
            });
        });

        $('#t'+navAssist.navRegister[navId]['active']).addClass('active');
        $('#c'+navAssist.navRegister[navId]['active']).css("display","block");
    }
};

var formAssist = {
    clear: function(formId) {
        var el = document.getElementById(formId);

        for(var i in el.elements) {
            switch(el.elements[i].type) {
                case "text": el.elements[i].value = ""; break;
                case "checkbox": el.elements[i].checked = false; break;
                case "radio": el.elements[i].checked = false; break;
                case "password": el.elements[i].value = ""; break;
            }
        }
    },

    disable: function(formId) {
        var el = document.getElementById(formId);

        for(var i in el.elements) {
            el.elements[i].disabled = true;
        }
    },

    enable: function(formId) {
        var el = document.getElementById(formId);

        for(var i in el.elements) {
            el.elements[i].disabled = false;
        }
    },

    clearList: function(list) {
        for (var i in list) {
            $('#'+list[i]).removeAttr("checked");
        }
    }
};

var popup = {
    closer: null,
    loader: null,

    load: function(top,title) {
        $('#popup-container').css('visibility','hidden');
        $('#popup-container').css('display','block');

        if (title) {
            $('#popup-title').css('display','block');
            $('#popup-title').html(title);
        }

        popup.correct();

        if (navigator.userAgent.toLowerCase().indexOf("msie") > -1) {
            $('#popup-container').css('visibility','visible');
            $('#popup-container').css('opacity',1);
        } else {
            $('#popup-container').css('opacity',0);
            $('#popup-container').css('visibility','visible');
            $('#popup-container').animate({
                opacity: 1
            },200);
        }
    },

    update: function(content) {
        $('#popup-content').html(content);
        popup.correct();
    },

    trigger: function(title,uri,method,appendParams) {
        if (!method) var method = 'GET';
        if (!appendParams) var appendParams = {};

        var params = {};
        params['params'] = appendParams;
        params['title'] = title;
        params['uri'] = uri;
        params['method'] = method;
        
        popup.prepare(params);
    },

    raw: function(title,data,method,appendParams) {
        if (!method) var method = 'GET';
        if (!appendParams) var appendParams = {};

        var params = {};
        params['params'] = appendParams;
        params['title'] = title;
        params['raw'] = data;
        params['method'] = method;

        popup.prepare(params);
    },
    
    prepare: function(params) {
        if ($('#popup-container').css('display') == 'none') {
            popup.process(params);
        } else {
            $('#popup-container').animate({
                opacity: 0
            },200,function() {
                popup.process(params);
            });
        }
    },

    process: function(params) {
        if (params.uri || params.raw) {
            if (!params.params) params.params = {};
            if (!params.method) params.method = 'GET';

            if (params.top) params.top = "'"+params.top+"'";
            else params.top = false;

            if (params.uri) {
                ajax.onSuccess = function() {
                    setTimeout(function() {
                        popup.load(params.top,params.title);
                    },200);

                    if (popup.loader) popup.loader();
                }

                ajax.request(params.uri,params.method,params.params,'popup-content');
            } else if (params.raw) {
                $('#popup-content').html(params.raw);

                setTimeout(function() {
                    popup.load(params.top,params.title);
                },200);
            }
        }
    },

    hide: function(bypassCloser) {
        if (!bypassCloser) var bypassCloser = false;

        var allowClose = true;
        if (!bypassCloser && popup.closer && !popup.closer()) allowClose = false;

        if (allowClose) {
            $('#popup-container').animate({
                opacity: 0
            },200);

            setTimeout(function() {
                $('#popup-container').css('display','none');
            },200);
        } else popup.closer();
    },

    correct: function() {
        $('#popup-container').center();
    }
};

jQuery.fn.center = function () {
    this.css("position","absolute");
    this.css("top", (($(window).height() - this.outerHeight()) / 2) + $(window).scrollTop() + "px");
    this.css("left", (($(window).width() - this.outerWidth()) / 2) + $(window).scrollLeft() + "px");
    return this;
}

String.prototype.isDate = function() {
    var txt = this;
    if (txt.length > 10 && txt.substr(10,1) == " ") txt = txt.substr(0,10);

    var objDate, mSeconds;

    if (txt.length != 10) return false;

    var year = txt.substr(0,4);
    var month = txt.substr(5,2);
    var day = txt.substr(8,2);

    if (txt.substr(4,1) != '/') return false;
    if (txt.substr(7,1) != '/') return false;

    if (year < 999 || year > 3000) return false;

    mSeconds = (new Date(year,month,day)).getTime();

    objDate = new Date();
    objDate.setTime(mSeconds);

    if (objDate.getFullYear() != year) return false;
    if (objDate.getMonth() != month) return false;
    if (objDate.getDate() != day) return false;

    return mSeconds;
};

String.prototype.strripos = function(needle, offset) {
    haystack = (this+'').toLowerCase();
    needle = (needle+'').toLowerCase();

    var i = -1;
    if (offset) {
        i = (haystack+'').slice(offset).lastIndexOf(needle); // strrpos' offset indicates starting point of range till end,
        // while lastIndexOf's optional 2nd argument indicates ending point of range from the beginning
        if (i !== -1) {
            i += offset;
        }
    }
    else {
        i = (haystack+'').lastIndexOf(needle);
    }
    return i >= 0 ? i : false;
};

function strip_tags(input,allowed) {
    allowed = (((allowed || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join(''); // making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)
    var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
        commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;

    return input.replace(commentsAndPhpTags, '').replace(tags, function($0, $1) {
        return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
    });
}

function replaceIt(input,search,sub) {
    while(input.indexOf(search) > -1) input = input.replace(search,sub);
    return input;
}

function addCommas(str) {
    var amount = new String(str);
    amount = amount.split("").reverse();

    var output = "";
    for (var i = 0; i <= amount.length-1; i++) {
        output = amount[i] + output;
        if ((i+1) % 3 == 0 && (amount.length-1) !== i) output = ',' + output;
    }

    return output;
}