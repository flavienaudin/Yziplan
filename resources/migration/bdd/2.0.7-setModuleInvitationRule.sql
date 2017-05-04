-- Initialise la valeur de invitation_rule de la table des EventModule
UPDATE event_module
SET invitation_rule = 'invitationrule.everyone';

-- Corrige les valeur de l'énumération "enum_invitation_rule" pour TEST (car le pdu a déjà été fait)
ALTER TABLE event_module
  MODIFY invitation_rule ENUM ('modulerule.everyone', 'modulerule.everyone_except', 'modulerule.none_except','invitationrule.everyone', 'invitationrule.none_except');

UPDATE event_module
  set invitation_rule = 'invitationrule.everyone' where invitation_rule = 'modulerule.everyone';
UPDATE event_module
set invitation_rule = 'invitationrule.everyone_except' where invitation_rule = 'modulerule.none_except';
UPDATE event_module
  set invitation_rule = 'invitationrule.none_except' where invitation_rule = 'modulerule.none_except';

ALTER TABLE event_module
  MODIFY invitation_rule ENUM ('invitationrule.everyone', 'invitationrule.none_except') COMMENT '(DC2Type:enum_invitation_rule)';


-- Change les valeurs de l'énumération ModuleInvitation.status
ALTER TABLE event_module_invitation
  ADD status ENUM ('moduleinvitationstatus.awaiting_answer','moduleinvitationstatus.valid','moduleinvitationstatus.cancelled','moduleinvitationstatus.invited', 'moduleinvitationstatus.not_invited', 'moduleinvitationstatus.excluded')
COMMENT '(DC2Type:enum_moduleinvitation_status)' NOT NULL;

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

ALTER TABLE event_module_invitation
  ADD status ENUM ('moduleinvitationstatus.invited', 'moduleinvitationstatus.not_invited', 'moduleinvitationstatus.excluded') COMMENT '(DC2Type:enum_moduleinvitation_status)' NOT NULL;