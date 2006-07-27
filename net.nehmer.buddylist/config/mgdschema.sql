CREATE TABLE net_nehmer_buddylist_entry_db
(
    id INT(11) NOT NULL auto_increment,
    sitegroup INT(11) NOT NULL DEFAULT 0,
    metadata_size INT(11) NOT NULL DEFAULT '0',

    account VARCHAR(255) NOT NULL DEFAULT '',
    buddy VARCHAR(255) NOT NULL DEFAULT '',

    added INT(11) NOT NULL DEFAULT 0,
    isapproved BOOL NOT NULL DEFAULT 0,
    blacklisted BOOL NOT NULL DEFAULT 0,

    PRIMARY KEY (id),
    INDEX net_nehmer_buddylist_entry_db_sitegroup_idx(sitegroup),
    INDEX net_nehmer_buddylist_entry_db_account_idx(account),
    INDEX net_nehmer_buddylist_entry_db_buddy_idx(buddy)
);
