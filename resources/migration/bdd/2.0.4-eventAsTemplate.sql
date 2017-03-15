# Add template attribute to table "Event"
ALTER TABLE event_event ADD template TINYINT(1) NOT NULL;

# Add eventParent attribute to table "Event"
ALTER TABLE event_event ADD event_parent_id INT DEFAULT NULL;
ALTER TABLE event_event ADD CONSTRAINT FK_7AB5BB8BF4EFF44D FOREIGN KEY (event_parent_id) REFERENCES event_event (id);
CREATE INDEX IDX_7AB5BB8BF4EFF44D ON event_event (event_parent_id);
