/*
 * validate.js
 * Copyright: Bryan Healey 2010, 2011, 2012 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Data validation manager
 */

var Validate = {
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
                    }
                }

                toRet = pass;

                if (pass) pass = "/images/success.gif";
                else pass = "/images/error.png";

                if (document.getElementById('validResult_'+elem.name)) {
                    document.getElementById('validResult_'+elem.name).src = pass;
                } else {
                    var validResult = document.createElement('img');
                    validResult.src = pass;
                    validResult.style.height = '1em';
                    validResult.style.verticalAlign = 'middle';
                    validResult.id = 'validResult_'+elem.name;

                    elem.parentNode.appendChild(validResult);
                }
            }
        } else if (document.getElementById('validResult_'+elem.name)) {
            elem.parentNode.removeChild(document.getElementById('validResult_'+elem.name));
        }

        return toRet;
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
