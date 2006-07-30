#!/bin/bash
# MidCOM Cron executer script by Eero 'Rambo' af Heurlin <rambo@nemein.com>
#
# USAGE:
#   Fill the sites list below then have the system cron call this script as
#   often as needed.
#
# Space separated list of sites to run MidCOM cron for,
# those that do not start with "http" or "https" get prefixed with "http://".
# If the Midgard host has a prefix add that without the trailing slash
SITES="example.com/test https://secure.example.com"

# Make sure we only have one copy running at a given time
test -f /var/run/midcom_services_cron.pid && exit 1
echo $$ > /var/run/midcom_services_cron.pid

for SITE in $SITES; do
    if [ `expr match "$SITE" 'https*'` -eq 0 ]; then
        URL="http://$SITE/midcom-exec-midcom/cron.php"
    else
        URL="$SITE/midcom-exec-midcom/cron.php"
    fi
    #echo $URL
    lynx -dump $URL | sed '/^$/d'
done

rm -f /var/run/midcom_services_cron.pid
