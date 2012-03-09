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
        else if (elem.getAttribute("required") == null && elem.value.strReplace(" ","") == "") check = false;

        if (check) {
            var pass = false;
            var validate = elem.getAttribute("validate");
            if (validate !== null) {
                if (elem.value.strReplace(" ","") !== "") {
                    switch(validate) {
                        case "filled":
                            if (elem.value.strReplace(' ','') == '') pass = false;
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
            } else pass = true;

            toRet = pass;

            if (pass) pass = "/images/success.gif";
            else pass = "/images/error.png";

            if ($('#validResult_'+elem.name)) {
                $('#validResult_'+elem.name).src = pass;
            } else {
                var validResult = document.createElement('img');
                validResult.src = pass;
                validResult.css('height','1em');
                validResult.css('verticalAlign','middle');
                validResult.id = 'validResult_'+elem.name;

                elem.up('div').appendChild(validResult);
            }
        } else if ($('#validResult_'+elem.name)) {
            elem.up('div').removeChild($('#validResult_'+elem.name));
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
        var el = $('#'+frm);

        for(var i in el.elements) {
            if (isNumeric(i)) {
                if (!Validate.checkElement(el.elements[i])) passAll = false;
            }
        }

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
        var el = $('#'+frm);

        for(var i in el.elements) {
            if (isNumeric(i) && el.elements[i].type !== "button") {
                elem = el.elements[i];
                elem.blur(function(event) { Validate.checkElement(this); });
                elem.change(function(event) { Validate.checkElement(this); });
            }
        }
    }
};
