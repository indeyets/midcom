CREATE TABLE net_nehmer_branchenbuch_branche_db
(
    id INT(11) NOT NULL auto_increment,
    parent VARCHAR(255) NOT NULL DEFAULT '',
    name VARCHAR(255) NOT NULL DEFAULT '',
    type VARCHAR(255) NOT NULL DEFAULT '',
    itemcount INT(11) NOT NULL DEFAULT 0,
    sitegroup INT(11) NOT NULL DEFAULT 0,
    metadata_size INT(11) NOT NULL DEFAULT '0',

    PRIMARY KEY (id),
    INDEX net_nehmer_branchenbuch_branche_db_sitegroup_idx(sitegroup),
    INDEX net_nehmer_branchenbuch_branche_db_parent_idx(parent)
);

CREATE TABLE net_nehmer_branchenbuch_entry_db
(
    id INT(11) NOT NULL auto_increment,
    sitegroup INT(11) NOT NULL DEFAULT 0,
    branche VARCHAR(255) NOT NULL DEFAULT '',
    type VARCHAR(255) NOT NULL DEFAULT '',
    account VARCHAR(255) NOT NULL DEFAULT '',
    firstname VARCHAR(255) NOT NULL DEFAULT '',
    lastname VARCHAR(255) NOT NULL DEFAULT '',
    address VARCHAR(255) NOT NULL DEFAULT '',
    postcode VARCHAR(255) NOT NULL DEFAULT '',
    city VARCHAR(255) NOT NULL DEFAULT '',
    homepage VARCHAR(255) NOT NULL DEFAULT '',
    email VARCHAR(255) NOT NULL DEFAULT '',
    homephone VARCHAR(255) NOT NULL DEFAULT '',
    workphone VARCHAR(255) NOT NULL DEFAULT '',
    handphone VARCHAR(255) NOT NULL DEFAULT '',
    metadata_size INT(11) NOT NULL DEFAULT '0',

    PRIMARY KEY (id),
    INDEX net_nehmer_branchenbuch_entry_db_sitegroup_idx(sitegroup),
    INDEX net_nehmer_branchenbuch_entry_db_branche_idx(branche),
    INDEX net_nehmer_branchenbuch_entry_db_type_idx(type),
    INDEX net_nehmer_branchenbuch_entry_db_account_idx(account)
);

