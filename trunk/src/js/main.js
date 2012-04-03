/*
 * main.js
 * Copyright: Bryan Healey 2010, 2011, 2012 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: All collected base functionality
 */

__CARRIAGE = 13;

var navAssist = {
    navRegister: {},
    prepareNav: function(navId,defVal) {
        navAssist.navRegister[navId] = {};
        navAssist.navRegister[navId]['active'] = defVal;

        Hasher.init();
        if (Hasher.hash['tab']) navAssist.navRegister[navId]['active'] = Hasher.hash['tab'];

        $('#'+navId).children().each(function(){
            var kid = $(this).attr("id");
            var base = kid.replace("t","");

            if (base == Hasher.hash['tab']) {
                $('#t'+base).addClass('active');
                $('#c'+base).css("display","block");
                navAssist.navRegister[navId]['active'] = base;
            } else {
                $('#t'+base).removeClass('active');
                $('#c'+base).css("display","none");
            }

            $('#t'+base).click(function() {
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

var htmlAssist = {
    idRegisters: {},
    prepareCAC: function(el) {
        el = $('#'+el);
        htmlAssist.idRegisters[el.attr("id")] = {'defaultText':el.val()};

        if (el.type == "password") {
            el.type = "text";
            htmlAssist.idRegisters[el.id]['typeChange'] = 'password';
        }

        el.click(function(event) {
            if (this.value == htmlAssist.idRegisters[this.id]['defaultText']) {
                this.value = "";
                this.style.color = "black";
                if (htmlAssist.idRegisters[this.id]['typeChange'] !== null) this.type = htmlAssist.idRegisters[this.id]['typeChange'];
            }
        });

        el.blur(function(event) {
            if (this.value == "") {
                this.value = htmlAssist.idRegisters[this.id]['defaultText'];
                this.style.color = "gray";
                if (htmlAssist.idRegisters[this.id]['typeChange'] !== null) this.type = "text";
            }
        });
    },
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

        if (getBrowser() == "msie 6") {
            var selects = document.getElementsByTagName('select');
            for (i = 0; i < selects.length; i++) selects[i].css('visibility','hidden');
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

    trigger: function(title,uri,params) {
        if (!params) var params = {};
        params['uri'] = uri;
        params['title'] = title;
        
        popup.prepare(params);
    },
    
    raw: function(title,data,params) {
        if (!params) var params = {};
        params['raw'] = data;
        params['title'] = title;
        
        popup.prepare(params);
    },
    
    prepare: function(params) {
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
                if (!params.nc) {
                    $('#popup-content').html('');

                    rContainer = document.createElement('div');
                    rContainer.style.padding = '0.5em';
                    rContainer.innerHTML = params.raw;

                    document.getElementById('popup-content').appendChild(rContainer);
                } else $('#popup-content').html(params.raw);
                
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
            if (getBrowser() == "msie 6") {
                var selects = document.getElementsByTagName('select');
                for (i = 0; i < selects.length; i++) selects[i].css('visibility','visible');
            }

            $('#popup-container').animate({
                opacity: 0
            },200);

            setTimeout(function() {
                $('#popup-container').css('display','none');
            },200);
        }
    },

    correct: function() {
        $('#popup-backdrop').width(($('#popup').width()+28)+"px");
        $('#popup-backdrop').height(($('#popup').height()+22)+"px");

        $('#popup-container').center();
        $('#popup-backdrop').center();
    }
};

jQuery.fn.center = function () {
    var newTop = (($(window).height() - this.outerHeight()) / 2) + $(window).scrollTop() + "px";
    var newLeft = (($(window).width() - this.outerWidth()) / 2) + $(window).scrollLeft() + "px";

    this.css("position","absolute");
    this.offset({ top: newTop, left: newLeft });

    return this;
}

Array.prototype.nsort = function(sort_flags) {
    var valArr = [], keyArr = [], k = '', i = 0, sorter = false, that = this, populateArr = [];

    switch (sort_flags) {
        case 'SORT_STRING':
            sorter = function (a, b) {
                return that.strnatcmp(a, b);
            };

        break;

        case 'SORT_NUMERIC':
            sorter = function (a, b) {
                return (a - b);
            };

        break;

        case 'SORT_REGULAR':
        default:
            sorter = function (a, b) {
                if (a > b) return 1;
                if (a < b) return -1;
                return 0;
            };

        break;
    }

    for (k in this) if (this.hasOwnProperty) valArr.push(this[k]);

    valArr.sort(sorter);
    for (i = 0; i < valArr.length; i++) if (isNumeric(valArr[i])) populateArr[i] = valArr[i];
    return populateArr;
};

Array.prototype.rsort = function(sort_flags) {
    var inputArr = this;
    var valArr=[], keyArr=[], k, i, ret, sorter, that = this, strictForIn = false, populateArr = [];

    switch (sort_flags) {
        case 'SORT_STRING':
            sorter = function (a, b) {
            	return that.strnatcmp(b, a);
            };

        break;

        case 'SORT_NUMERIC':
            sorter = function (a, b) {
                return (a - b);
            };

        break;

        case 'SORT_REGULAR':
        default:
            sorter = function (a, b) {
            if (a > b) return 1;
                if (a < b) return -1;
                return 0;
            };

        break;
    }

    var bubbleSort = function (keyArr, inputArr) {
        var i, j, tempValue, tempKeyVal;
        for (i = inputArr.length-2; i >= 0; i--) {
            for (j = 0; j <= i; j++) {
            	ret = sorter(inputArr[j+1], inputArr[j]);
                if (ret > 0) {
                    tempValue = inputArr[j];
                    inputArr[j] = inputArr[j+1];
                    inputArr[j+1] = tempValue;
                    tempKeyVal = keyArr[j];
                    keyArr[j] = keyArr[j+1];
                    keyArr[j+1] = tempKeyVal;
                }
            }
        }
    };

    for (k in inputArr) {
        if (inputArr.hasOwnProperty) {
            valArr.push(inputArr[k]);
            keyArr.push(k);
        }
    }

    try { bubbleSort(keyArr, valArr); } catch (e) { return false; }

    return valArr;
};

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

function getBrowser() {
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
}

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