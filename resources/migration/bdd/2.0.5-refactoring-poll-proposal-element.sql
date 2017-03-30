
-- A faire en debut de mise a jour avant un eventuel doctrine update
-- Modifie la table pour ajouter les colonnes
ALTER TABLE `module_poll_proposal`
ADD COLUMN   `val_string` varchar(511) COLLATE utf8_unicode_ci DEFAULT NULL,
ADD COLUMN   `val_text` longtext COLLATE utf8_unicode_ci,
ADD COLUMN   `val_integer` int(11) DEFAULT NULL,
ADD COLUMN   `val_datetime` datetime DEFAULT NULL,
ADD COLUMN   `has_time` tinyint(1) NOT NULL,
ADD COLUMN   `val_enddatetime` datetime DEFAULT NULL,
ADD COLUMN   `has_end_date` tinyint(1) NOT NULL,
ADD COLUMN  `has_end_time` tinyint(1) NOT NULL,
ADD COLUMN  `val_google_place_id` varchar(511) COLLATE utf8_unicode_ci DEFAULT NULL,
ADD COLUMN  `picture_filename` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL;

-- Creation d'une procedure stockée pour mettre a jour les poll proposal element vers les poll proposal

update module_poll_proposal pp set pp.val_string = (select val_string from module_poll_proposal_element ppe where ppe.poll_proposal_id = pp.id and ppe.val_string is not null) where pp.val_string is null;
update module_poll_proposal pp set pp.val_text = (select val_text from module_poll_proposal_element ppe where ppe.poll_proposal_id = pp.id and ppe.val_text is not null) where pp.val_text is null;
update module_poll_proposal pp set pp.val_integer = (select val_integer from module_poll_proposal_element ppe where ppe.poll_proposal_id = pp.id and ppe.val_integer is not null) where pp.val_integer is null;
update module_poll_proposal pp set pp.val_datetime = (select val_datetime from module_poll_proposal_element ppe where ppe.poll_proposal_id = pp.id and ppe.val_datetime is not null) where pp.val_datetime is null;
update module_poll_proposal pp set pp.has_time = (select has_time from module_poll_proposal_element ppe where ppe.poll_proposal_id = pp.id and ppe.has_time is not null) where pp.has_time is null;
update module_poll_proposal pp set pp.val_enddatetime = (select val_enddatetime from module_poll_proposal_element ppe where ppe.poll_proposal_id = pp.id and ppe.val_enddatetime is not null) where pp.val_enddatetime is null;
update module_poll_proposal pp set pp.has_end_date = (select has_end_date from module_poll_proposal_element ppe where ppe.poll_proposal_id = pp.id and ppe.has_end_date is not null) where pp.has_end_date is null;
update module_poll_proposal pp set pp.has_end_time = (select has_end_time from module_poll_proposal_element ppe where ppe.poll_proposal_id = pp.id and ppe.has_end_time is not null) where pp.has_end_time is null;
update module_poll_proposal pp set pp.val_google_place_id = (select val_google_place_id from module_poll_proposal_element ppe where ppe.poll_proposal_id = pp.id and ppe.val_google_place_id is not null) where pp.val_google_place_id is null;
update module_poll_proposal pp set pp.picture_filename = (select picture_filename from module_poll_proposal_element ppe where ppe.poll_proposal_id = pp.id and ppe.picture_filename is not null) where pp.picture_filename is null;


-- Vérification :
select
pp.val_string,ppe.val_string,
pp.val_text,ppe.val_text,
pp.val_integer,ppe.val_integer,
pp.val_datetime,ppe.val_datetime,
pp.has_time,ppe.has_time,
pp.val_enddatetime,ppe.val_enddatetime,
pp.has_end_date,ppe.has_end_date,
pp.has_end_time,ppe.has_end_time,
pp.val_google_place_id,ppe.val_google_place_id,
pp.picture_filename,ppe.picture_filename
from module_poll_proposal pp join module_poll_proposal_element ppe on pp.id = ppe.poll_proposal_id;