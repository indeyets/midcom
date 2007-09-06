CREATE TABLE net_nemein_alphabeticalindex_item_db
(
    id INT(11) NOT NULL auto_increment,
    title VARCHAR(200) NOT NULL DEFAULT '',
    url VARCHAR(200) NOT NULL DEFAULT '',
    objectGuid VARCHAR(150) NOT NULL DEFAULT '',
    
    PRIMARY KEY (id),
    UNIQUE INDEX net_nemein_alphabeticalindex_item_db_idx(title, url),
    INDEX net_nemein_alphabeticalindex_item_object_idx(objectGuid)
);