-- Initialise la valeur de invitation_rule de la table des EventModule
UPDATE event_module
SET invitation_rule = 'modulerule.everyone';

-- Change les valeurs de l'énumération ModuleInvitation.status
ALTER TABLE event_module_invitation
  DROP status;
ALTER TABLE event_module_invitation
  ADD status ENUM ('moduleinvitationstatus.invited', 'moduleinvitationstatus.not_invited', 'moduleinvitationstatus.excluded') COMMENT '(DC2Type:enum_moduleinvitation_status)' NOT NULL
COMMENT '(DC2Type:enum_moduleinvitation_status)';

-- Set ModuleInvitation.status = Invited si le module est en organisation
UPDATE event_module_invitation mi
SET status = 'moduleinvitationstatus.invited'
WHERE mi.module_id IN (SELECT m.id
                       FROM event_module m
                       WHERE m.status = 'modulestatus.in_organization');

-- Set ModuleInvitation.status = Not Invited si l'EventInvitation est annulée
UPDATE event_module_invitation mi
SET status = 'moduleinvitationstatus.not_invited'
WHERE mi.event_invitation_id IN (SELECT ei.id
                                 FROM event_event_invitation ei
                                 WHERE ei.status = 'status.cancelled');
