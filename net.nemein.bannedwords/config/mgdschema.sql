CREATE TABLE net_nemein_bannedwords_word (
  bannedWord varchar(255) NOT NULL default '',
  description varchar(255) NOT NULL default '',
  language varchar(255) NOT NULL default '',
  #
  id int(11) NOT NULL auto_increment,
  sitegroup int(11) NOT NULL default '0',
  guid varchar(80) NOT NULL default '',
  PRIMARY KEY (id),
  #
  KEY net_nemein_bannedwords_word_idx (`bannedWord`(30)),
  KEY net_nemein_description_word_idx (`description`(30)),
  KEY net_nemein_language_word_idx (`language`(30))
);
