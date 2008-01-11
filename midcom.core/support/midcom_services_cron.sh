#!/bin/bash
# MidCOM Cron executer script by Eero 'Rambo' af Heurlin <rambo@nemein.com>
#
# USAGE:
#   Fill the sites list below then have the system cron call this script as
#   often as needed, recommended calls:
#
#   */2 *  * * *   root    /usr/local/bin/midcom_services_cron.sh
#   30  *  * * *   root    /usr/local/bin/midcom_services_cron.sh hour
#   15  5  * * *   root    /usr/local/bin/midcom_services_cron.sh day
#
# Space separated list of sites to run MidCOM cron for,
# those that do not start with "http" or "https" get prefixed with "http://".
# If the Midgard host has a prefix add that without the trailing slash
SITES="example.com/test https://secure.example.com"
# alternatively use elinks, you also specify other options here
# like "lynx -auth=username:password" for HTTP basic-auth
LYNX="lynx" 
STAT="stat -c %Z" # how to get file timestamp
DATE="date +%s" # how to get system timestamp
LIFETIME=3600 # Max pidfile age in seconds before it's considered stale

if [ "$1" == "" ]
then
    URL_SUFFIX=""
    PIDFILE="/var/run/midcom_services_cron_everymin.pid"
    PIDFILE2=""
else
    URL_SUFFIX="?type=$1"
    PIDFILE="/var/run/midcom_services_cron_$1.pid"
    PIDFILE2="/var/run/midcom_services_cron_everymin.pid"
fi  

function check_pidfile
{
    if [ -f $1 ]
    then
        MODIFIED=`$STAT $1`
        NOW=`$DATE`
        AGE=`expr $NOW - $MODIFIED`
        #echo "MODIFIED=$MODIFIED NOW=$NOW AGE=$AGE LIFETIME=$LIFETIME"
        if [ "$AGE" -lt "$LIFETIME" ]
        then
            return 1
        fi
        echo "Lock $1 is stale, removing"
        # PONDER: Should we kill the process ??, the line below should do the trick
        # kill -9 `cat $1`
        rm -f $1
        return 0
    fi
    return 0
}

# Make sure we only have one copy of the type running at a given time
check_pidfile $PIDFILE
PIDSTAT=$?
if [  "$PIDSTAT" != "0" ]
then
    exit 1
fi
echo $$ > $PIDFILE
# Wait untill everymin script has completed if it's running
if [ "$PIDFILE2" != "" ]
then
    check_pidfile $PIDFILE2
    PIDSTAT2=$?
    while [  "$PIDSTAT2" != "0" ]
    do
        sleep 10
        check_pidfile $PIDFILE2
        PIDSTAT2=$?
    done
fi

for SITE in $SITES
do
    if [ `expr match "$SITE" 'https*'` -eq 0 ]
    then
        URL="http://$SITE/midcom-exec-midcom/cron.php$URL_SUFFIX"
    else
        URL="$SITE/midcom-exec-midcom/cron.php$URL_SUFFIX"
    fi
    $LYNX -dump $URL | sed '/^$/d'
done

rm -f $PIDFILE
