/*
 * validate.js
 * Copyright: Bryan Healey 2010, 2011, 2012 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Data validation manager
 */

var Validate = {
    bubbles: true,

    /**
     *
     * function: checkElement
     * Validate a specific element
     * @access public
     * @param elem
     * @return string
     */
    checkElement: function(elem) {
        var toRet = true;
        var check = true;

        if (elem.type == "button") check = false;
        else if (elem.getAttribute("required") == null && elem.value.replace(" ","") == "") check = false;

        if (check) {
            var validResult = {};
            var validate = elem.getAttribute("validate");

            if (validate !== null) {
                var msgOptions = {};
                validate = validate.split(' ');
                for(var vi in validate) {
                    var ruleId = validate[vi];
                    msgOptions[ruleId] = {'error':'An error has occured',
                                          'success':'Correct!'};

                    switch(ruleId) {
                        case "filled":
                            if (elem.value.replace(' ','') == '') validResult[ruleId] = false;
                            else validResult[ruleId] = true;

                        break;

                        case "datetime":
                            var rule = /^(\d{4})-(\d{2})-(\d{2})$/;
                            if (rule.test(elem.value)) validResult[ruleId] = true;
                            else validResult[ruleId] = false;

                        break;

                        case "email":
                            var rule = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                            if (rule.test(elem.value)) validResult[ruleId] = true;
                            else validResult[ruleId] = false;

                        break;

                        case "phone":
                            var rule = /^\(?(\d{3})\)?[- ]?(\d{3})[- ]?(\d{4})$/;
                            if (rule.test(elem.value)) validResult[ruleId] = true;
                            else validResult[ruleId] = false;

                        break;

                        case "mimic":
                            var mimic = elem.getAttribute("mimic");
                            if (document.getElementById(mimic).value == elem.value) validResult[ruleId] = true;
                            else validResult[ruleId] = false;

                        break;

                        case "minLength":
                            var charMin = elem.getAttribute("charMin");
                            if (elem.value.length >= charMin) validResult[ruleId] = true;
                            else validResult[ruleId] = false;

                        break;

                        case "maxLength":
                            var charMax = elem.getAttribute("charMax");
                            if (elem.value.length <= charMax) validResult[ruleId] = true;
                            else validResult[ruleId] = false;

                        break;
                    }
                }

                if (elem.getAttribute("onError")) {
                    var onErrorMsg = elem.getAttribute("onError");
                    onErrorMsg = onErrorMsg.split('|');
                    for(oemi in onErrorMsg) {
                        var msgOpt = onErrorMsg[oemi].split('=');
                        msgOptions[msgOpt[0]]['error'] = msgOpt[1];
                    }
                }

                if (elem.getAttribute("onSuccess")) {
                    var onSuccessMsg = elem.getAttribute("onSuccess");
                    onSuccessMsg = onSuccessMsg.split('|');
                    for(osmi in onSuccessMsg) {
                        var msgOpt = onSuccessMsg[osmi].split('=');
                        msgOptions[msgOpt[0]]['success'] = msgOpt[1];
                    }
                }

                var pass = true;
                var errorMessaging = [];
                var successMessaging = [];
                for(var vri in validResult) {
                    if (validResult[vri] == false) {
                        pass = false;
                        errorMessaging.push(msgOptions[vri]['error']);
                    } else successMessaging.push(msgOptions[vri]['success']);
                }

                errorMessaging = errorMessaging.join('<br />');
                successMessaging = successMessaging.join('<br />');

                var imgSrc = false;
                if (pass == true) {
                    if (successMessaging == '') msg = 'Correct!';
                    else msg = successMessaging;

                    imgSrc = "/images/success.gif";
                } else {
                    if (errorMessaging == '') msg = 'An error has occured';
                    else msg = errorMessaging;

                    imgSrc = "/images/error.png";
                }

                if (document.getElementById('validResult_'+elem.name)) {
					document.getElementById('validResult_'+elem.name).style.visibility = "visible";
                    if (imgSrc !== false) document.getElementById('validResult_'+elem.name).src = imgSrc;

                    if (Validate.bubbles == false) document.getElementById('validResult_'+elem.name).title = msg;
                    else {
                        if (pass == false) Validate.createBubble(elem.parentNode,elem.name,msg);
                        else Validate.hideBubble(elem.parentNode,elem.name);
                    }
                } else {
                    Validate.createIcon(elem,imgSrc);

                    if (Validate.bubbles == true && pass == false) Validate.createBubble(elem.parentNode,elem.name,msg);
                    else Validate.hideBubble(elem.parentNode,elem.name);
                }
            }
        } else if (document.getElementById('validResult_'+elem.name)) {
            elem.parentNode.removeChild(document.getElementById('validResult_'+elem.name));
            Validate.hideBubble(elem.name);
        }

        return pass;
    },

    createIcon: function(elem,ico,preHide) {
		if (!ico) var ico = "/images/success.gif";
		if (!preHide) var preHide = false;

		var validResult = document.createElement('img');
	    validResult.src = ico;
	    validResult.style.height = '1em';
	    validResult.style.verticalAlign = 'middle';
	    validResult.id = 'validResult_'+elem.name;
	    if (Validate.bubbles == false) validResult.title = msg;

	    elem.parentNode.appendChild(validResult);

	    if (preHide == true) document.getElementById('validResult_'+elem.name).style.visibility = "hidden";
	},

    /**
     *
     * function: createBubble
     * Create a warning bubble
     * @access public
     * @param obj
     * @param pname
     * @param msg
     * @return string
     */
    createBubble: function(obj,pname,msg,preHide) {
		if (document.getElementById('validResultBubble_'+pname)) {
            document.getElementById('validResultBubbleText_'+pname).innerHTML = msg;
        } else {
            var bubbleResult = document.createElement('div');
            bubbleResult.id = 'validResultBubble_'+pname;
            bubbleResult.style.left = obj.style.width;
            bubbleResult.style.display = 'none';

            bubblePoint = document.createElement('img');
            bubblePoint.className = 'bubblePoint';
            bubblePoint.id = 'validResultBubblePoint_'+pname;
            bubblePoint.src = '/images/left-red.png';
            bubblePoint.style.verticalAlign = 'middle';

            bubbleResult.appendChild(bubblePoint);

            var bubbleText = document.createElement('span');
            bubbleText.className = 'bubble';
            bubbleText.id = 'validResultBubbleText_'+pname;
            bubbleText.innerHTML = msg;

            bubbleResult.appendChild(bubbleText);
            obj.appendChild(bubbleResult);

            document.getElementById('validResultBubble_'+pname).style.display = 'block';
			pos.rightOf(obj,$('#validResultBubble_'+pname));
        }
    },

    /**
     *
     * function: hideBubble
     * Remove a warning bubble
     * @access public
     * @param pname
     * @return string
     */
    hideBubble: function(pnode,pname) {
        if (document.getElementById('validResultBubble_'+pname)) {
            pnode.removeChild(document.getElementById('validResultBubble_'+pname));
        }
    },

    /**
     *
     * function: checkAll
     * Validate an entire form
     * @access public
     * @param frm
     * @return string
     */
    checkAll: function(frm) {
        var passAll = true;
        $('form#'+frm+' :input').each(function(i) {
            if (Validate.checkElement(this) == false) passAll = false;
        });

        return passAll;
    },

    /**
     *
     * function: clearAll
     * Clear all validation from an entire form
     * @access public
     * @param frm
     * @return string
     */
    clearAll: function(frm) {
        var passAll = true;
        $('form#'+frm+' :input').each(function(i) {
            if (document.getElementById('validResult_'+this.name)) {
                this.parentNode.removeChild(document.getElementById('validResult_'+this.name));
            }
        });

        return passAll;
    },

    /**
     *
     * function: autoValidate
     * Auto-validate an entire form, prepared on page load
     * @access public
     * @param frm
     * @return string
     */
    autoValidate: function(frm) {
        $('form#'+frm+' :input').each(function(i) {
            if (this.type == 'text' || this.type == 'password' || this.type == 'textarea') {
				Validate.createIcon(this,false,true);

                this.onblur = function() {
                    if (this.value.replace(' ','') !== '') Validate.checkElement(this);
                };

                this.onchange = function() {
                    if (this.value.replace(' ','') !== '') Validate.checkElement(this);
                };
            }
        });
    }
};
