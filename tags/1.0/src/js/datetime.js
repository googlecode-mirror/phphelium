var DateData = {
    dow: function(i) {
        switch(i) {
            case 0: return "Sunday"; break;
            case 1: return "Monday"; break;
            case 2: return "Tuesday"; break;
            case 3: return "Wednesday"; break;
            case 4: return "Thursday"; break;
            case 5: return "Friday"; break;
            case 6: return "Saturday"; break;
        }
    },
    
    daysUntil: function(end) {
        var start = DateData.unixtime();
        var diff = end-start;

        return Math.floor(diff/(24*60*60));
    },
	
    hrsUntil: function(end) {
        var start = DateData.unixtime();
        var diff = end-start;

        return Math.floor(diff/(60*60));
    },
	
    minsUntil: function(end) {
        var start = DateData.unixtime();
        var diff = end-start;

        return Math.floor(diff/60);
    },
	
    secsUntil: function(end) {
        var start = DateData.unixtime();
        var diff = end-start;

        return diff;
    },

    measuresUntil: function(end) {
        var start = DateData.unixtime();
        var diff = end-start;

        var days = Math.floor(diff/(24*60*60));

        diff = diff-(days*(24*60*60));
        var hours = Math.floor(diff/(60*60));

        diff = diff-(hours*(60*60));
        var mins = Math.floor(diff/60);

        diff = diff-(mins*(60));
        var secs = Math.floor(diff);

        return [days,hours,mins,secs];
    },
	
    unixtime: function() {
        var d = new Date;
        var unixtime_ms = d.getTime();
        return parseInt(unixtime_ms / 1000);
    }
};
