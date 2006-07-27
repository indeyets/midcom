CREATE TABLE net_nehmer_jobmarket_entry_db
(
    id INT(11) NOT NULL auto_increment,
    sitegroup INT(11) NOT NULL DEFAULT 0,
    metadata_size INT(11) NOT NULL DEFAULT '0',

    offer BOOL NOT NULL DEFAULT 0,
    type VARCHAR(255) NOT NULL DEFAULT '',
    sector VARCHAR(255) NOT NULL DEFAULT '',
    location VARCHAR(255) NOT NULL DEFAULT '',
    account VARCHAR(255) NOT NULL DEFAULT '',
    published INT(11) NOT NULL DEFAULT 0,

    title VARCHAR(255) NOT NULL DEFAULT '',
    abstract TEXT NOT NULL DEFAULT '',
    description TEXT NOT NULL DEFAULT '',

    contact VARCHAR(255) NOT NULL DEFAULT '',
    company VARCHAR(255) NOT NULL DEFAULT '',
    address VARCHAR(255) NOT NULL DEFAULT '',
    postcode VARCHAR(255) NOT NULL DEFAULT '',
    city VARCHAR(255) NOT NULL DEFAULT '',
    homepage VARCHAR(255) NOT NULL DEFAULT '',
    email VARCHAR(255) NOT NULL DEFAULT '',
    workphone VARCHAR(255) NOT NULL DEFAULT '',
    mobilephone VARCHAR(255) NOT NULL DEFAULT '',

    PRIMARY KEY (id),
    INDEX net_nehmer_jobmarket_entry_db_sitegroup_idx(sitegroup),
    INDEX net_nehmer_jobmarket_entry_db_sector_idx(sector),
    INDEX net_nehmer_jobmarket_entry_db_type_idx(type),
    INDEX net_nehmer_jobmarket_entry_db_location_idx(location),
    INDEX net_nehmer_jobmarket_entry_db_title_idx(title),
    INDEX net_nehmer_jobmarket_entry_db_account_idx(account)
);
