ALTER TABLE person ADD COLUMN created datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE person ADD COLUMN creator int(11) NOT NULL default '0';
#These confuse old-api
#ALTER TABLE person ADD COLUMN revisor int(11) NOT NULL default '0';
#ALTER TABLE person ADD COLUMN revised datetime NOT NULL default '0000-00-00 00:00:00';
#ALTER TABLE person ADD COLUMN revision int(11) NOT NULL default '0';
#
ALTER TABLE grp ADD COLUMN created datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE grp ADD COLUMN creator int(11) NOT NULL default '0';
ALTER TABLE grp ADD COLUMN revisor int(11) NOT NULL default '0';
ALTER TABLE grp ADD COLUMN revised datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE grp ADD COLUMN revision int(11) NOT NULL default '0';
#
ALTER TABLE org_openpsa_document ADD COLUMN created datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_document ADD COLUMN creator int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_document ADD COLUMN revisor int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_document ADD COLUMN revised datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_document ADD COLUMN revision int(11) NOT NULL default '0';
#
ALTER TABLE event ADD COLUMN created datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE event ADD COLUMN creator int(11) NOT NULL default '0';
ALTER TABLE event ADD COLUMN revisor int(11) NOT NULL default '0';
ALTER TABLE event ADD COLUMN revised datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE event ADD COLUMN revision int(11) NOT NULL default '0';
#
ALTER TABLE eventmember ADD COLUMN created datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE eventmember ADD COLUMN creator int(11) NOT NULL default '0';
ALTER TABLE eventmember ADD COLUMN revisor int(11) NOT NULL default '0';
ALTER TABLE eventmember ADD COLUMN revised datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE eventmember ADD COLUMN revision int(11) NOT NULL default '0';
#
ALTER TABLE org_openpsa_task ADD COLUMN created datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_task ADD COLUMN creator int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_task ADD COLUMN revisor int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_task ADD COLUMN revised datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_task ADD COLUMN revision int(11) NOT NULL default '0';
#
ALTER TABLE org_openpsa_hour_report ADD COLUMN created datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_hour_report ADD COLUMN creator int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_hour_report ADD COLUMN revisor int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_hour_report ADD COLUMN revised datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_hour_report ADD COLUMN revision int(11) NOT NULL default '0';
#
ALTER TABLE org_openpsa_hour_expense ADD COLUMN created datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_hour_expense ADD COLUMN creator int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_hour_expense ADD COLUMN revisor int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_hour_expense ADD COLUMN revised datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_hour_expense ADD COLUMN revision int(11) NOT NULL default '0';
#
ALTER TABLE org_openpsa_task_resource ADD COLUMN created datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_task_resource ADD COLUMN creator int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_task_resource ADD COLUMN revisor int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_task_resource ADD COLUMN revised datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_task_resource ADD COLUMN revision int(11) NOT NULL default '0';
#
ALTER TABLE org_openpsa_query ADD COLUMN created datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_query ADD COLUMN creator int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_query ADD COLUMN revisor int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_query ADD COLUMN revised datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_query ADD COLUMN revision int(11) NOT NULL default '0';
#
ALTER TABLE org_openpsa_campaign ADD COLUMN created datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_campaign ADD COLUMN creator int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_campaign ADD COLUMN revisor int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_campaign ADD COLUMN revised datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_campaign ADD COLUMN revision int(11) NOT NULL default '0';
#
ALTER TABLE org_openpsa_campaign_member ADD COLUMN created datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_campaign_member ADD COLUMN creator int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_campaign_member ADD COLUMN revisor int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_campaign_member ADD COLUMN revised datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_campaign_member ADD COLUMN revision int(11) NOT NULL default '0';
#
ALTER TABLE org_openpsa_campaign_message ADD COLUMN created datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_campaign_message ADD COLUMN creator int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_campaign_message ADD COLUMN revisor int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_campaign_message ADD COLUMN revised datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_campaign_message ADD COLUMN revision int(11) NOT NULL default '0';
#
ALTER TABLE org_openpsa_campaign_message_receipt ADD COLUMN created datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_campaign_message_receipt ADD COLUMN creator int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_campaign_message_receipt ADD COLUMN revisor int(11) NOT NULL default '0';
ALTER TABLE org_openpsa_campaign_message_receipt ADD COLUMN revised datetime NOT NULL default '0000-00-00 00:00:00';
ALTER TABLE org_openpsa_campaign_message_receipt ADD COLUMN revision int(11) NOT NULL default '0';



