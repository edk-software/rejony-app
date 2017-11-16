ALTER TABLE cantiga_areas
ADD COLUMN `stationaryTraining` tinyint(4) NOT NULL DEFAULT 0 AFTER `visiblePublicly`,
ADD COLUMN `contract` tinyint(4) NOT NULL DEFAULT 0 AFTER `stationaryTraining`;