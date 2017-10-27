ALTER TABLE cantiga_area_requests
ADD COLUMN `lat` DECIMAL(18,9) NULL AFTER `lastUpdatedAt`,
ADD COLUMN `lng` DECIMAL(18,9) NULL AFTER `lat`;