#Creating columns created for net.nemein.calendar
## net_nemein_calendar_event
ALTER TABLE event ADD COLUMN openregistration int(11) NOT NULL default '0';
ALTER TABLE event ADD COLUMN closeregistration int(11) NOT NULL default '0';
ALTER TABLE event ADD COLUMN location VARCHAR(255) NOT NULL default '';