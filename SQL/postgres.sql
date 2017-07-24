CREATE TABLE IF NOT EXISTS attachments (
  cache_id bigserial NOT NULL,
  cache_key varchar(128) NOT NULL,
  fname varchar(256) DEFAULT NULL,
  created timestamp with time zone NOT NULL DEFAULT '1000-01-01 00:00:00',
  data text NOT NULL,
  downloads integer NOT NULL DEFAULT 0,
  recipients text,
  user_id integer NOT NULL
	REFERENCES users (user_id) ON DELETE CASCADE ON UPDATE CASCADE,
  PRIMARY KEY (cache_id)
);
CREATE INDEX ix_attachments_created ON attachments (created);
CREATE INDEX ix_attachments_user_id_cache_key ON attachments (user_id, cache_key);

