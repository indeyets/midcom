-- Create the tables using midgard-schema
-- Then import this file into the database to speed up the queries.

CREATE INDEX net_nehmer_publications_entry_db_title_idx 
	ON net_nehmer_publications_entry_db(title);
CREATE INDEX net_nehmer_publications_entry_db_author_idx 
	ON net_nehmer_publications_entry_db(author);

CREATE UNIQUE INDEX net_nehmer_publications_categorymap_db_m_n_idx 
	ON net_nehmer_publications_categorymap_db(publication, category);