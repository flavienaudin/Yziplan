-- ***********************************************************
-- Mise à jour des valeurs de l'énumération "enum_notification_type"

ALTER TABLE event_event_invitation_notification
  CHANGE type type ENUM ('notification_type.post_comment', 'notification_type.add_module', 'notification_type.add_pollproposal', 'notification_type.change_pollmodule_votingtype') COMMENT '(DC2Type:enum_notification_type)' NOT NULL
COMMENT '(DC2Type:enum_notification_type)';
