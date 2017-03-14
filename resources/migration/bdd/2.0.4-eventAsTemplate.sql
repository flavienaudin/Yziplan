ALTER TABLE event_event ADD template TINYINT(1) NOT NULL;
update event_event set template = true where duplication_enabled = true;