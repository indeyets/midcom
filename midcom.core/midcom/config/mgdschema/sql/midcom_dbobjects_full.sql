CREATE TABLE midcom_group_virtual
(
    id INT(11) NOT NULL auto_increment,
    component VARCHAR(100) NOT NULL DEFAULT '',
    identifier VARCHAR(200) NOT NULL DEFAULT '',
    name VARCHAR(255) NOT NULL DEFAULT '',
    sitegroup INT(11) NOT NULL DEFAULT 0,
    metadata_size INT(11) NOT NULL DEFAULT '0',

    PRIMARY KEY (id),
    UNIQUE INDEX midcom_virtual_group_idx(component, identifier),
    INDEX midcom_virtual_group_sitegroup_idx(sitegroup)
);

CREATE TABLE midcom_core_privilege
(
    id INT(11) NOT NULL auto_increment,
    objectguid TEXT NOT NULL DEFAULT '',
    name VARCHAR(255) NOT NULL DEFAULT '',
    assignee TEXT NOT NULL DEFAULT '',
    classname TEXT NOT NULL DEFAULT '',
    value INT(1) NOT NULL DEFAULT 1,
    sitegroup INT(11) NOT NULL DEFAULT 0,
    metadata_size INT(11) NOT NULL DEFAULT '0',

    PRIMARY KEY (id),
    INDEX midcom_core_privilege_name_idx(name),
    INDEX midcom_core_privilege_assingee_idx(assignee),
    INDEX midcom_core_privilege_objectguid_idx(objectguid)
);

CREATE TABLE midcom_core_login_session
(
    id INT(11) NOT NULL auto_increment,
    userid VARCHAR(255) NOT NULL DEFAULT '',
    username VARCHAR(255) NOT NULL DEFAULT '',
    password VARCHAR(255) NOT NULL DEFAULT '',
    clientip VARCHAR(15) NOT NULL DEFAULT '',
    timestamp INT(11) NOT NULL DEFAULT 0,
    sitegroup INT(11) NOT NULL DEFAULT 0,
    metadata_size INT(11) NOT NULL DEFAULT '0',

    PRIMARY KEY (id),
    INDEX midcom_core_login_session_userid_index(userid)
);

CREATE TABLE midcom_core_temporary_object_db
(
    id INT(11) NOT NULL auto_increment,
    timestamp INT(11) NOT NULL DEFAULT 0,

    name VARCHAR(255) NOT NULL DEFAULT '',
    text1 TEXT NOT NULL DEFAULT '',
    text2 TEXT NOT NULL DEFAULT '',
    text3 TEXT NOT NULL DEFAULT '',
    integer1 INT(11) NOT NULL DEFAULT 0,
    integer2 INT(11) NOT NULL DEFAULT 0,
    integer3 INT(11) NOT NULL DEFAULT 0,
    integer4 INT(11) NOT NULL DEFAULT 0,

    sitegroup INT(11) NOT NULL DEFAULT 0,
    metadata_size INT(11) NOT NULL DEFAULT '0',

    PRIMARY KEY (id),
    INDEX midcom_core_temporary_object_db_timestamp_index(timestamp)
);
