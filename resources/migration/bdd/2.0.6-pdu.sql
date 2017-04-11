-- PDU : Creation table : event_invitation_preferences
CREATE TABLE event_invitation_preferences (id INT AUTO_INCREMENT NOT NULL, notif_new_comment TINYINT(1) NOT NULL, notif_new_comment_last_email_at DATETIME DEFAULT NULL, notif_new_module TINYINT(1) NOT NULL, notif_new_module_last_email_at DATETIME DEFAULT NULL, notif_new_pollpropsal TINYINT(1) NOT NULL, notif_new_pollproposal_last_email_at DATETIME DEFAULT NULL, notif_email_frequency ENUM('notification_frequency.never', 'notification_frequency.each_notification', 'notification_frequency.daily', 'notification_frequency.weekly') COMMENT '(DC2Type:enum_notification_frequency)' NOT NULL COMMENT '(DC2Type:enum_notification_frequency)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE event_event_invitation ADD event_invitation_preferences INT DEFAULT NULL;
ALTER TABLE event_event_invitation ADD CONSTRAINT FK_8FE35B451FD0E6EE FOREIGN KEY (event_invitation_preferences) REFERENCES event_invitation_preferences (id);
CREATE UNIQUE INDEX UNIQ_8FE35B451FD0E6EE ON event_event_invitation (event_invitation_preferences);

