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

if [ "$1" == "" ]; then
    URL_SUFFIX=""
    PIDFILE="/var/run/midcom_services_cron_everymin.pid"
    PIDFILE2=""
else
    URL_SUFFIX="?type=$1"
    PIDFILE="/var/run/midcom_services_cron_$1.pid"
    PIDFILE2="/var/run/midcom_services_cron_everymin.pid"
fi  

# TODO: figure out a locking mechanism that ensures only one instance
# is running at a time, without missing any valid runs

# Make sure we only have one copy of the type running at a given time
test -f $PIDFILE && exit 1
echo $$ > $PIDFILE
# Wait untill everymin script has completed if it's running
if [ "$PIDFILE2" != "" ]; then
    if [ -f $PIDFILE2 ]; then
        while [ -f $PIDFILE2 ]; do
            sleep 10
        done
    fi
fi

for SITE in $SITES; do
    if [ `expr match "$SITE" 'https*'` -eq 0 ]; then
        URL="http://$SITE/midcom-exec-midcom/cron.php$URL_SUFFIX"
    else
        URL="$SITE/midcom-exec-midcom/cron.php$URL_SUFFIX"
    fi
    $LYNX -dump $URL | sed '/^$/d'
done

rm -f $PIDFILE
