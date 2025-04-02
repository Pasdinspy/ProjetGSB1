-- Suppression des tables si elles existent
DROP TABLE IF EXISTS `ligne_frais_hors_forfait`;
DROP TABLE IF EXISTS `ligne_frais_forfait`;
DROP TABLE IF EXISTS `fiche_frais`;
DROP TABLE IF EXISTS `frais_forfait`;
DROP TABLE IF EXISTS `etat`;
DROP TABLE IF EXISTS `visiteur`;
DROP TABLE IF EXISTS `user`;

-- Création des tables
CREATE TABLE `etat` (
  `ETA_ID` char(2) NOT NULL,
  `ETA_LIB` varchar(30) NOT NULL,
  PRIMARY KEY (`ETA_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `visiteur` (
  `VIS_ID` int(11) NOT NULL AUTO_INCREMENT,
  `VIS_NOM` varchar(60) NOT NULL,
  `VIS_PRENOM` varchar(60) NOT NULL,
  `VIS_ADRESSE` varchar(100) NOT NULL,
  `VIS_CP` char(5) NOT NULL,
  `VIS_VILLE` varchar(60) NOT NULL,
  `VIS_DATE_EMBAUCHE` date NOT NULL,
  PRIMARY KEY (`VIS_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `VIS_ID` int(11) DEFAULT NULL,
  `username` varchar(16) NOT NULL,
  `password` varchar(255) NOT NULL,
  `dteConnexion` timestamp NULL DEFAULT current_timestamp(),
  `role` enum('VISITEUR_MEDICAL','COMPTABLE','ADMINISTRATEUR') NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`VIS_ID`) REFERENCES `visiteur` (`VIS_ID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `fiche_frais` (
  `FFR_ID` int(11) NOT NULL AUTO_INCREMENT,
  `ETA_ID` char(2) NOT NULL,
  `VIS_ID` int(11) NOT NULL,
  `FFR_ANNEE` char(4) NOT NULL,
  `FFR_MOIS` enum('JANVIER','FEVRIER','MARS','AVRIL','MAI','JUIN','JUILLET','AOUT','SEPTEMBRE','OCTOBRE','NOVEMBRE','DECEMBRE') NOT NULL,
  `FFR_MONTANT_VALIDE` decimal(10,2) NOT NULL,
  `FFR_NB_JUSTIFICATIFS` int(11) NOT NULL,
  `FFR_DATE_MODIF` date NOT NULL,
  PRIMARY KEY (`FFR_ID`),
  FOREIGN KEY (`ETA_ID`) REFERENCES `etat` (`ETA_ID`),
  FOREIGN KEY (`VIS_ID`) REFERENCES `visiteur` (`VIS_ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `frais_forfait` (
  `FOR_ID` char(3) NOT NULL,
  `FOR_LIB` varchar(20) NOT NULL,
  `FOR_MONTANT` decimal(5,2) NOT NULL,
  PRIMARY KEY (`FOR_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `ligne_frais_forfait` (
  `FFR_ID` int(11) NOT NULL,
  `FOR_ID` char(3) NOT NULL,
  `LIG_QTE` int(11) NOT NULL,
  PRIMARY KEY (`FFR_ID`, `FOR_ID`),
  FOREIGN KEY (`FFR_ID`) REFERENCES `fiche_frais` (`FFR_ID`) ON DELETE CASCADE,
  FOREIGN KEY (`FOR_ID`) REFERENCES `frais_forfait` (`FOR_ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Insertion des données
INSERT INTO `etat` (`ETA_ID`, `ETA_LIB`) VALUES
('CL', 'Saisie clôturée'),
('CR', 'Fiche créée, saisie en cours'),
('RE', 'Remboursée');

INSERT INTO `frais_forfait` (`FOR_ID`, `FOR_LIB`, `FOR_MONTANT`) VALUES
('ETP', 'etape', 2.00),
('KM', 'kilomètre', 0.70),
('NUI', 'nuitée', 70.00),
('REP', 'repas', 27.00);

INSERT INTO `visiteur` (`VIS_ID`, `VIS_NOM`, `VIS_PRENOM`, `VIS_ADRESSE`, `VIS_CP`, `VIS_VILLE`, `VIS_DATE_EMBAUCHE`) VALUES
(31, 'Thomas', 'Lorenzeo', '6546546', '64565', 'uiojiuouioiu', '2025-03-07');

INSERT INTO `user` (`id`, `VIS_ID`, `username`, `password`, `dteConnexion`, `role`) VALUES
(8, 31, 'thomas', 'Iroise29', '2025-03-08 15:50:33', 'VISITEUR_MEDICAL'),
(9, NULL, 'louna', 'Iroise29', '2025-03-09 19:42:34', 'COMPTABLE'),
(14, NULL, 'thomasA', 'Iroise29', '2025-03-10 13:10:26', 'ADMINISTRATEUR');

COMMIT;
