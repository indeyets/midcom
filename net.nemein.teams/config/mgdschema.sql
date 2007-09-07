CREATE TABLE net_nemein_teams_log (
  id int(11) NOT NULL auto_increment,
  message varchar(255) NOT NULL default '',
  teamguid varchar(255) NOT NULL default '',
  PRIMARY KEY (id),
  KEY net_nemein_teams_log_message_idx(`message`(30)),
  KEY net_nemein_teams_log_teamguid_idx(`teamguid`(30))
);

CREATE TABLE net_nemein_teams_team (
  id int(11) NOT NULL auto_increment,
  topicguid varchar(255) NOT NULL default '',
  groupguid varchar(255) NOT NULL default '',
  managerguid varchar(255) NOT NULL default '',
  PRIMARY KEY (id),
  KEY net_nemein_teams_team_topicguid_idx(`topicguid`(30)),
  KEY net_nemein_teams_team_groupguid_idx(`groupguid`(30)),
  KEY net_nemein_teams_team_managerguid_idx(`managerguid`(30))
);

CREATE TABLE net_nemein_teams_pending (
  id int(11) NOT NULL auto_increment,
  playerguid varchar(255) NOT NULL default '',
  groupguid varchar(255) NOT NULL default '',
  managerguid varchar(255) NOT NULL default '',
  message varchar(255) NOT NULL default '',
  PRIMARY KEY (id),
  KEY net_nemein_teams_team_playerguid_idx(`playerguid`(30)),
  KEY net_nemein_teams_team_groupguid_idx(`groupguid`(30)),
  KEY net_nemein_teams_team_message_idx(`message`(30)),
  KEY net_nemein_teams_team_managerguid_idx(`managerguid`(30))
);

