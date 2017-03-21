ALTER TABLE event_event ADD duplication_enabled TINYINT(1) NOT NULL;
UPDATE event_event set duplication_enabled = (token_duplication is not NULL);