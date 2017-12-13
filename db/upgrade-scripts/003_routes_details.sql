ALTER TABLE cantiga_edk_routes

ADD COLUMN `routePatron` varchar(100) CHARACTER SET utf8 COLLATE utf8_polish_ci DEFAULT NULL AFTER `name`,
ADD COLUMN `routeColor` varchar(100) CHARACTER SET utf8 COLLATE utf8_polish_ci DEFAULT NULL AFTER `routePatron`,
ADD COLUMN `routeAuthor` varchar(100) CHARACTER SET utf8 COLLATE utf8_polish_ci DEFAULT NULL AFTER `routeColor`,

ADD COLUMN `routeFromDetails` varchar(100) CHARACTER SET utf8 COLLATE utf8_polish_ci DEFAULT NULL AFTER `routeFrom`,
ADD COLUMN `routeToDetails` varchar(100) CHARACTER SET utf8 COLLATE utf8_polish_ci DEFAULT NULL AFTER `routeTo`,

ADD COLUMN `createdBy` int(11) DEFAULT NULL AFTER `createdAt`,
ADD COLUMN `updatedBy` int(11) DEFAULT NULL AFTER `updatedAt`,
ADD COLUMN `approvedAt` int(11) DEFAULT NULL AFTER `approved`,
ADD COLUMN `approvedBy` int(11) DEFAULT NULL AFTER `approvedAt`,

ADD COLUMN `gpsCreatedAt` int(11) DEFAULT NULL AFTER `gpsTrackFile`,
ADD COLUMN `gpsCreatedBy` int(11) DEFAULT NULL AFTER `gpsCreatedAt`,
ADD COLUMN `gpsUpdatedAt` int(11) DEFAULT NULL AFTER `gpsCreatedBy`,
ADD COLUMN `gpsUpdatedBy` int(11) DEFAULT NULL AFTER `gpsUpdatedAt`,
ADD COLUMN `gpsApproved` tinyint(4) NOT NULL DEFAULT '0' AFTER `gpsUpdatedBy`,
ADD COLUMN `gpsApprovedAt` int(11) DEFAULT NULL AFTER `gpsApproved`,
ADD COLUMN `gpsApprovedBy` int(11) DEFAULT NULL AFTER `gpsApprovedAt`,

ADD COLUMN `descriptionCreatedAt` int(11) DEFAULT NULL AFTER `descriptionFile`,
ADD COLUMN `descriptionCreatedBy` int(11) DEFAULT NULL AFTER `descriptionCreatedAt`,
ADD COLUMN `descriptionUpdatedAt` int(11) DEFAULT NULL AFTER `descriptionCreatedBy`,
ADD COLUMN `descriptionUpdatedBy` int(11) DEFAULT NULL AFTER `descriptionUpdatedAt`,
ADD COLUMN `descriptionApproved` tinyint(4) NOT NULL DEFAULT '0' AFTER `descriptionUpdatedBy`,
ADD COLUMN `descriptionApprovedAt` int(11) DEFAULT NULL AFTER `descriptionApproved`,
ADD COLUMN `descriptionApprovedBy` int(11) DEFAULT NULL AFTER `descriptionApprovedAt`,

ADD COLUMN `mapCreatedAt` int(11) DEFAULT NULL AFTER `mapFile`,
ADD COLUMN `mapCreatedBy` int(11) DEFAULT NULL AFTER `mapCreatedAt`,
ADD COLUMN `mapUpdatedAt` int(11) DEFAULT NULL AFTER `mapCreatedBy`,
ADD COLUMN `mapUpdatedBy` int(11) DEFAULT NULL AFTER `mapUpdatedAt`,
ADD COLUMN `mapApproved` tinyint(4) NOT NULL DEFAULT '0' AFTER `mapUpdatedBy`,
ADD COLUMN `mapApprovedAt` int(11) DEFAULT NULL AFTER `mapApproved`,
ADD COLUMN `mapApprovedBy` int(11) DEFAULT NULL AFTER `mapApprovedAt`;