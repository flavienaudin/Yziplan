-- ***********************************************************
-- Mise à jour des valeurs de l'énumération "enum_pollmodule_votingtype"

-- Vérification des données :
SELECT
  poll_module_type,
  voting_type,
  count(*)
FROM module_poll_module
GROUP BY poll_module_type, voting_type;

ALTER TABLE module_poll_module
  CHANGE voting_type voting_type ENUM ('yes_no', 'yes_no_maybe', 'notation', 'ranking', 'amount', 'pollmodule_votingtype.yes_no', 'pollmodule_votingtype.yes_no_maybe', 'pollmodule_votingtype.ranking', 'pollmodule_votingtype.amount') COMMENT '(DC2Type:enum_pollmodule_votingtype)' NOT NULL
COMMENT '(DC2Type:enum_pollmodule_votingtype)';

UPDATE module_poll_module
SET voting_type = 'pollmodule_votingtype.yes_no'
WHERE voting_type = 'yes_no';
UPDATE module_poll_module
SET voting_type = 'pollmodule_votingtype.yes_no_maybe'
WHERE voting_type = 'yes_no_maybe';
UPDATE module_poll_module
SET voting_type = 'pollmodule_votingtype.ranking'
WHERE voting_type = 'notation';
UPDATE module_poll_module
SET voting_type = 'pollmodule_votingtype.ranking'
WHERE voting_type = 'ranking';
UPDATE module_poll_module
SET voting_type = 'pollmodule_votingtype.amount'
WHERE voting_type = 'amount';

-- Vérification des données :
SELECT
  poll_module_type,
  voting_type,
  count(*)
FROM module_poll_module
GROUP BY poll_module_type, voting_type;

-- valeurs finales de l'énumération
ALTER TABLE module_poll_module
  CHANGE voting_type voting_type ENUM ('pollmodule_votingtype.yes_no', 'pollmodule_votingtype.yes_no_maybe', 'pollmodule_votingtype.ranking', 'pollmodule_votingtype.amount') COMMENT '(DC2Type:enum_pollmodule_votingtype)' NOT NULL
COMMENT '(DC2Type:enum_pollmodule_votingtype)';