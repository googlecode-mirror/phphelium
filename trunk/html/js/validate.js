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
            var pass = false;
            var validate = elem.getAttribute("validate");
            if (validate !== null) {
                if (elem.value.replace(" ","") !== "") {
                    switch(validate) {
                        case "filled":
                            if (elem.value.replace(' ','') == '') pass = false;
                            else pass = true;

                        break;

                        case "datetime":
                            var rule = /^(\d{4})-(\d{2})-(\d{2})$/;
                            if (rule.test(elem.value)) pass = true;

                        break;

                        case "email":
                            var rule = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                            if (rule.test(elem.value)) pass = true;

                        break;

                        case "phone":
                            var rule = /^\(?(\d{3})\)?[- ]?(\d{3})[- ]?(\d{4})$/;
                            if (rule.test(elem.value)) pass = true;

                        break;

                        case "mimic":
                            var mimic = elem.getAttribute("mimic");
                            if (document.getElementById(mimic).value == elem.value) pass = true;

                        break;
                    }
                }

                toRet = pass;

                if (pass) {
                    if (elem.getAttribute("onSuccess") == null) msg = 'Correct!';
                    else msg = elem.getAttribute("onSuccess");

                    pass = "/images/success.gif";
                } else {
                    if (elem.getAttribute("onError") == null) msg = 'An error has occured';
                    else msg = elem.getAttribute("onError");

                    pass = "/images/error.png";
                }

                if (document.getElementById('validResult_'+elem.name)) {
                    document.getElementById('validResult_'+elem.name).src = pass;
                    
                    if (Validate.bubbles == false) document.getElementById('validResult_'+elem.name).title = msg;
                    else {
                        if (toRet == false) Validate.createBubble(elem.parentNode,elem.name,msg);
                        else Validate.hideBubble(elem.parentNode,elem.name);
                    }
                } else {
                    var validResult = document.createElement('img');
                    validResult.src = pass;
                    validResult.style.height = '1em';
                    validResult.style.verticalAlign = 'middle';
                    validResult.id = 'validResult_'+elem.name;
                    if (Validate.bubbles == false) validResult.title = msg;

                    elem.parentNode.appendChild(validResult);

                    if (Validate.bubbles == true && toRet == false) Validate.createBubble(elem.parentNode,elem.name,msg);
                    else Validate.hideBubble(elem.parentNode,elem.name);
                }
            }
        } else if (document.getElementById('validResult_'+elem.name)) {
            elem.parentNode.removeChild(document.getElementById('validResult_'+elem.name));
            Validate.hideBubble(elem.name);
        }

        return toRet;
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
    createBubble: function(obj,pname,msg) {
        if (document.getElementById('validResultBubble_'+pname)) {
            document.getElementById('validResultBubble_'+pname).innerHTML = msg;
        } else {
            var bubbleResult = document.createElement('div');
            bubbleResult.className = 'bubble';
            bubbleResult.id = 'validResultBubble_'+pname;
            bubbleResult.innerHTML = msg;
            bubbleResult.style.left = obj.style.width;
            
            obj.appendChild(bubbleResult);
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
            if (!Validate.checkElement(this)) passAll = false;
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
                this.onblur = function() { Validate.checkElement(this); };
                this.onchange = function() { Validate.checkElement(this); };
            }
        });
    }
};
