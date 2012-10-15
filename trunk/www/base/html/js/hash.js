/*
 * hash.js
 * Copyright: Bryan Healey 2010, 2011, 2012 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Manage all hash behavior
 */

var Hasher = {
    hash: {},

    /**
     *
     * function: init
     * Prepare hasher by incorporating all hash elements from the current URI
     * @access public
     * @return string
     */
    init: function() {
        if (window.location.hash.indexOf("#!/") > -1) {
            var hash = window.location.hash.replace("#!/","");
            if (hash.indexOf("|") > -1) hash = hash.split("|");
            else hash = Array(hash);

            hashCnt = hash.length;
            for(var i = 0; i < hashCnt; i++) {
                var subhash = hash[i].split(":");
                Hasher.hash[subhash[0]] = subhash[1];
            }
        }
    },

    /**
     *
     * function: render
     * Render a new hash based on custom behavior
     * @access public
     * @return string
     */
    render: function() {
        var hash = '#!/';
        var opts = [];
        for(var i in Hasher.hash) {
            if (typeof(i) == 'string' && typeof(Hasher.hash[i]) !== 'undefined') {
                if (i == el) opts.push(i+':'+val);
                else opts.push(i+':'+Hasher.hash[i]);
            }
        }

        if (opts.length > 0) hash += opts.join('|');
        else hash = '';

        window.location.hash = hash;
        Hasher.hash = {};
        Hasher.init();
    },

    /**
     *
     * function: set
     * Set a hash element
     * @access public
     * @param el
     * @param val
     * @return string
     */
    set: function(el,val) {
        var hash = '#!/';
        var opts = [];
        for(var i in Hasher.hash) {
            if (typeof(i) == 'string' && typeof(Hasher.hash[i]) !== 'undefined') {
                if (i == el) opts.push(i+':'+val);
                else opts.push(i+':'+Hasher.hash[i]);
            }
        }

        if (typeof(Hasher.hash[el]) == 'undefined') opts.push(el+':'+val);
        if (opts.length > 0) hash += opts.join('|');

        window.location.hash = hash;
        Hasher.hash = {};
        Hasher.init();
    },

    /**
     *
     * function: append
     * Append a hash element
     * @access public
     * @param el
     * @param val
     * @return string
     */
    append: function(el,val) {
        if (typeof(Hasher.hash[el]) !== 'undefined') Hasher.set(el,Hasher.hash[el]+'/'+val);
        else Hasher.set(el,val);
    }
};
