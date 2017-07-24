CREATE TABLE IF NOT EXISTS 'attachments' (
  'cache_id' INTEGER NOT NULL PRIMARY KEY ASC,
  'cache_key' VARCHAR(128),
  'fname' VARCHAR(256),
  'created' datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
  'data' text NOT NULL,
  'downloads' int(10) NOT NULL DEFAULT '0',
  'recipients` text',
  'user_id' int(10) NOT NULL
);

CREATE TABLE IF NOT EXISTS 'system' (
  name varchar(64) NOT NULL PRIMARY KEY,
  value text NOT NULL
);

INSERT INTO system (name, value) VALUES ('myrc_detach_attachments', 'initial');

CREATE INDEX attachments_cache_id ON 'attachments'('cache_id');
CREATE INDEX attachments_cache_key ON 'attachments'('cache_key');