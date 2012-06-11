/*
 * datetime.js
 * Copyright: Bryan Healey 2010, 2011, 2012 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Do date/time transformations
 */

var DateData = {
    /**
     *
     * function: dow
     * Get the textual representation of the day of the week
     * @access public
     * @param i
     * @return string
     */
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

    /**
     *
     * function: daysUntil
     * Get the number of days until a certain date
     * @access public
     * @param end
     * @return string
     */
    daysUntil: function(end) {
        var start = DateData.unixtime();
        var diff = end-start;

        return Math.floor(diff/(24*60*60));
    },

    /**
     *
     * function: hrsUntil
     * Get the number of hours until a certain date
     * @access public
     * @param end
     * @return string
     */
    hrsUntil: function(end) {
        var start = DateData.unixtime();
        var diff = end-start;

        return Math.floor(diff/(60*60));
    },

    /**
     *
     * function: minsUntil
     * Get the number of minutes until a certain date
     * @access public
     * @param end
     * @return string
     */
    minsUntil: function(end) {
        var start = DateData.unixtime();
        var diff = end-start;

        return Math.floor(diff/60);
    },

    /**
     *
     * function: secsUntil
     * Get the number of seconds until a certain date
     * @access public
     * @param end
     * @return string
     */
    secsUntil: function(end) {
        var start = DateData.unixtime();
        var diff = end-start;

        return diff;
    },

    /**
     *
     * function: measuresUntil
     * Get the various measures until a certain date
     * @access public
     * @param end
     * @return string
     */
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

    /**
     *
     * function: measuresUntil
     * Get the various measures until a certain date
     * @access public
     * @param end
     * @return string
     */
    measuresBetween: function(start,end) {
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

    /**
     *
     * function: unixtime
     * Get the current unix timestamp
     * @access public
     * @return string
     */
    unixtime: function() {
        var d = new Date;
        var unixtime_ms = d.getTime();
        return parseInt(unixtime_ms / 1000);
    },

    /**
     *
     * function: stayDuration
     * Get the amount of time the page has been open
     * @access public
     * @return string
     */
    stayDuration: function() {
        var endTime = DateData.unixtime();
        var startTime = sys.openTime;
        return DateData.measuresBetween(startTime,endTime);
    }
};
