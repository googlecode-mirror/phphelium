var Hasher = {
    hash: {},

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

    append: function(el,val) {
        if (typeof(Hasher.hash[el]) !== 'undefined') Hasher.set(el,Hasher.hash[el]+'/'+val);
        else Hasher.set(el,val);
    }
};
