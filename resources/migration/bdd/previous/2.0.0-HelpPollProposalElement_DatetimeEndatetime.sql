# retourne les événements ayant un PollModule de type Quand et des propositions avec une date de fin non stockée avec la date de début
SELECT e.id, e.token, e.name 
FROM event_event e 
WHERE e.id IN ( 
    SELECT em.event_id 
    FROM event_module em, module_poll_module pm 
    WHERE em.id = pm.module_id 
    AND pm.poll_module_type = "pollmoduletype.when" 
    AND em.status != "modulestatus.deleted" 
    AND pm.id in ( 
        SELECT mpp.poll_module_id 
        FROM module_poll_proposal mpp, module_poll_proposal_element mppe 
        WHERE mpp.poll_module_id = pm.id AND mpp.id = mppe.poll_proposal_id 
        AND mppe.val_enddatetime is not null and mppe.val_datetime is null
    ) 
    GROUP BY em.event_id 
) LIMIT 0, 500;

# 178    Uxcfhgxgi    Validation 1.0.1
# 188    n7ieLDinj    JDR Partie dématerialisé
# 193    hih7626yl    Rocket League!
# 198    4ThDDqWVE    Soirée à la maison
# 199    EqYKsjX7s    Soirée à 4


# retourne les couple de pollProposalElement datetime/endatetime : 
SELECT mppe.id, mppe.poll_proposal_id, mppe.val_datetime, val_enddatetime, mpe.type, e.id, e.token, e.name
FROM
  module_poll_proposal mpp,
  module_poll_proposal_element mppe,
  module_poll_element mpe,
  module_poll_module pm,
  event_module em,
  event_event e
WHERE
  mpp.id = mppe.poll_proposal_id AND mppe.poll_element_id = mpe.id AND pm.id = mpp.poll_module_id AND em.id = pm.module_id 
  AND e.id = em.event_id AND (mpe.type = 'datetime' or mpe.type = 'enddatetime')
  AND EXISTS ( 
      SELECT 1 
      FROM module_poll_proposal_element mppe1, module_poll_element mpe1
      WHERE mppe1.poll_proposal_id = mppe.poll_proposal_id and mppe1.poll_element_id = mpe1.id
      	AND mpe1.type = 'enddatetime'
  ) AND em.status != "modulestatus.deleted"
ORDER BY poll_proposal_id ASC, mppe.id ASC
LIMIT 0, 500


# Afficher les event_invitations
select e.id, e.token, e.name, ei.id, ei.token, ei.creator, ei.guest_name, ue.email
from event_event e, event_event_invitation ei, user_app_user_email ue
where e.id = ei.event_id and ei.application_user_id = ue.application_user_id
	and e.token in ("Uxcfhgxgi","n7ieLDinj","hih7626yl","4ThDDqWVE","EqYKsjX7s")