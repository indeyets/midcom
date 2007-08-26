CREATE TABLE `pl_olga_windguru_cache` (
`id` INT( 11 ) NOT NULL ,
`spot` INT( 11 ) NOT NULL ,
`model` INT( 11 ) NOT NULL ,
`lang` CHAR( 2 ) NOT NULL ,
`data` BLOB NOT NULL ,
`met` DATETIME NOT NULL ,
`wave` DATETIME NOT NULL ,
PRIMARY KEY ( `id` ) ,
INDEX ( `spot` , `model` , `lang` )
)

CREATE TABLE `pl_olga_windguru_status` (
`id` INT( 11 ) NOT NULL ,
`value` VARCHAR( 255 ) NOT NULL ,
`status` INT( 11 ) NOT NULL ,
PRIMARY KEY ( `id` ) ,
INDEX ( `value`,`status` )
)