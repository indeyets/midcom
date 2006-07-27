CREATE TABLE net_nehmer_mail_mailbox_db
(
    id INT(11) NOT NULL auto_increment,
    owner VARCHAR(200) NOT NULL DEFAULT '',
    name VARCHAR(200) NOT NULL DEFAULT '',
    quota INT(11) NOT NULL DEFAULT 0,
    sitegroup INT(11) NOT NULL DEFAULT 0,
    metadata_size INT(11) NOT NULL DEFAULT '0',

    PRIMARY KEY (id),
    UNIQUE INDEX net_nehmer_mail_mailbox_db_idx(owner, name),
    INDEX net_nehmer_mail_mailbox_db_sitegroup_idx(sitegroup)
);

CREATE TABLE net_nehmer_mail_mail_db
(
    id INT(11) NOT NULL auto_increment,
    mailbox VARCHAR(255) NOT NULL DEFAULT '',
    sender VARCHAR(255) NOT NULL DEFAULT '',
    subject VARCHAR(255) NOT NULL DEFAULT '',
    body TEXT NOT NULL DEFAULT '',
    received INT(11) NOT NULL DEFAULT 0,
    isread BOOL NOT NULL DEFAULT 0,
    isreplied BOOL NOT NULL DEFAULT 0,
    sitegroup INT(11) NOT NULL DEFAULT 0,
    metadata_size INT(11) NOT NULL DEFAULT '0',

    PRIMARY KEY (id),
    INDEX net_nehmer_mail_mail_db_mailbox_idx(mailbox),
    INDEX net_nehmer_mail_mail_db_received_idx(received),
    INDEX net_nehmer_mail_mail_db_sitegroup_idx(sitegroup)
);