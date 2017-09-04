-- ***********************************************************
-- Mise à jour des valeurs de l'énumération "enum_notification_type"

ALTER TABLE event_event_invitation_notification
  CHANGE type type ENUM ('notification_type.post_comment', 'notification_type.add_module', 'notification_type.add_pollproposal', 'notification_type.change_pollmodule_votingtype') COMMENT '(DC2Type:enum_notification_type)' NOT NULL
COMMENT '(DC2Type:enum_notification_type)';

-- Mise à jour de la table Event Invitation Preference pour ajouter la préférence de notification
ALTER TABLE event_invitation_preferences ADD notif_change_poll_module_voting_type TINYINT(1) NOT NULL, ADD notif_change_poll_module_voting_type_last_email_at DATETIME DEFAULT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL;
