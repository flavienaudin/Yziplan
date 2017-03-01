CREATE OR REPLACE
 ALGORITHM = UNDEFINED
 VIEW `EventInvitaitonDetails`
 AS SELECT ei.id, ei.event_id, e.name, ei.token as "invit_token", ei.guest_name, ei.application_user_id, ae.email, au.account_user_id
FROM event_event e, `event_event_invitation` ei
LEFT JOIN user_application_user au ON ei.application_user_id = au.id 
LEFT JOIN user_app_user_email ae on au.id = ae.application_user_id 
where ei.event_id = e.id
ORDER BY `event_id` ASC