/**
 * Projects time calculator
 * Henri Bergius, henri.bergius@iki.fi
 *
 * Based on fi.protie.costcounter by Arttu Manninen, arttu.manninen@protie.fi
 */
var workingOnCalculator = Class.create();

workingOnCalculator.prototype = {

    initialize: function(elementId, timebase)
    {
        // This is the constructor
        this.element = $(elementId);
        this.startTime = this.element.name;

        // Difference between user and server clocks
        var timebase = timebase;
        var currentTime = new Date();
        this.timeDifference = Math.floor(currentTime.getTime() / 1000 - timebase);

        // Start the counter
        this.counter();
    },

    /**
     * Calculate time elapsed since page was loaded
     */
    counter: function()
    {
        currentTime = new Date();

        timeElapsed = Math.floor(currentTime.getTime() / 1000 - this.startTime - this.timeDifference);
        this.element.value = this.formatTime(timeElapsed);

        // FIXME: This makes FF 2 freeze setInterval(this.counter.bind(this), 1000);
        setTimeout('timeCounter.counter()', 1000);
    },

    /**
     * Format elapsed time into user-readable format
     */
    formatTime: function(rawtime)
    {
        var seconds = rawtime % 60;
        var minutes = Math.floor(rawtime / 60) % 60;
        var hours   = Math.floor(rawtime / 3600) % 3600;

        if (seconds < 10)
        {
            if (seconds == 0)
            {
                var outputsec = '00';
            }
            else
            {
                var outputsec = '0' + seconds;
            }
        }
        else
        {
            var outputsec = seconds;
        }

        if (minutes < 10)
        {
            if (minutes == 0)
            {
                var outputmin = '00';
            }
            else
            {
                var outputmin = '0' + minutes;
            }
        }
        else
        {
            outputmin = minutes;
        }

        return hours + ':' + outputmin + ':' + outputsec;
    }
};