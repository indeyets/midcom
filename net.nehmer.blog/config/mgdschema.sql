CREATE TABLE net_nehmer_blog_link
(
    id             INT(8)         AUTO_INCREMENT,
    topic          INT(8)         NOT NULL   DEFAULT 0,
    article        INT(8)         NOT NULL   DEFAULT 0,
    sitegroup      INT(8)         NOT NULL   DEFAULT 0,
    PRIMARY KEY (id)
);
