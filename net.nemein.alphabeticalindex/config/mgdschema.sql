CREATE TABLE net_nemein_alphabeticalindex_item_db
(
    id INT(11) NOT NULL auto_increment,
    title VARCHAR(200) NOT NULL DEFAULT '',
    url VARCHAR(200) NOT NULL DEFAULT '',
    description TEXT NOT NULL DEFAULT '',
    objectGuid VARCHAR(150) NOT NULL DEFAULT '',
    cachedUrl TEXT NOT NULL DEFAULT '',
    node int(11) NOT NULL default 0,
    modified tinyint(1) NOT NULL default 0,
        
    PRIMARY KEY (id),
    UNIQUE INDEX net_nemein_alphabeticalindex_item_db_idx(title, url),
    INDEX net_nemein_alphabeticalindex_item_object_idx(objectGuid),
    INDEX net_nemein_alphabeticalindex_item_node_idx (node)
);