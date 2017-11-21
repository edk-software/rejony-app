<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171119234001 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE `cantiga_users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `login` varchar(40) COLLATE utf8_polish_ci NOT NULL,
            `name` varchar(60) COLLATE utf8_polish_ci NOT NULL,
            `password` varchar(100) COLLATE utf8_polish_ci NOT NULL,
            `salt` varchar(40) COLLATE utf8_polish_ci NOT NULL,
            `email` varchar(100) COLLATE utf8_polish_ci NOT NULL,
            `active` int(11) NOT NULL DEFAULT "1",
            `removed` int(11) NOT NULL DEFAULT "0",
            `admin` tinyint(4) NOT NULL DEFAULT "0",
            `lastVisit` int(11) DEFAULT NULL,
            `avatar` varchar(40) COLLATE utf8_polish_ci DEFAULT NULL,
            `registeredAt` int(11) NOT NULL,
            `placeNum` int(11) NOT NULL DEFAULT "0",
            PRIMARY KEY (`id`),
            UNIQUE KEY `login` (`login`),
            KEY `active_users` (`active`,`login`),
            KEY `user_name` (`name`),
            KEY `removed` (`removed`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci');
        $this->addSql('CREATE TABLE `cantiga_user_registrations` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `login` varchar(40) NOT NULL,
            `name` varchar(100) NOT NULL,
            `password` varchar(100) NOT NULL,
            `salt` varchar(40) NOT NULL,
            `email` varchar(100) NOT NULL,
            `languageId` int(11) NOT NULL,
            `provisionKey` varchar(40) NOT NULL,
            `requestIp` bigint(20) NOT NULL,
            `requestTime` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `magicKey` (`provisionKey`),
            KEY `language` (`languageId`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_languages` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(20) NOT NULL,
            `locale` varchar(10) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_mail` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `place` varchar(40) NOT NULL,
            `locale` varchar(10) NOT NULL,
            `subject` varchar(100) NOT NULL,
            `lastUpdate` int(11) NOT NULL,
            `content` text NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `name_locale_pair` (`place`,`locale`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_places` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `slug` varchar(12) DEFAULT NULL,
            `type` varchar(30) NOT NULL,
            `removedAt` int(11) DEFAULT NULL,
            `memberNum` int(11) DEFAULT "0",
            `rootPlaceId` int(11) DEFAULT NULL,
            `archived` tinyint(1) NOT NULL DEFAULT "0",
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`),
            KEY `rootPlaceId` (`rootPlaceId`),
            KEY `archived` (`archived`) USING HASH,
            CONSTRAINT `cantiga_places_ibfk_1` FOREIGN KEY (`rootPlaceId`) REFERENCES `cantiga_places` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_projects` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(30) NOT NULL,
            `slug` varchar(12) NOT NULL,
            `description` varchar(1000) NOT NULL,
            `parentProjectId` int(11) DEFAULT NULL,
            `modules` varchar(1000) NOT NULL,
            `areasAllowed` tinyint(4) NOT NULL,
            `areaRegistrationAllowed` tinyint(4) NOT NULL,
            `archived` tinyint(4) NOT NULL,
            `createdAt` int(11) NOT NULL,
            `archivedAt` int(11) DEFAULT NULL,
            `placeId` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`),
            UNIQUE KEY `placeId` (`placeId`) USING BTREE,
            KEY `parentProjectId` (`parentProjectId`),
            CONSTRAINT `cantiga_projects_fk1` FOREIGN KEY (`placeId`) REFERENCES `cantiga_places` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_place_members` (
            `placeId` int(11) NOT NULL,
            `userId` int(11) NOT NULL,
            `role` int(11) NOT NULL,
            `showDownstreamContactData` tinyint(1) NOT NULL DEFAULT "0",
            `note` varchar(30) DEFAULT NULL,
            PRIMARY KEY (`placeId`,`userId`),
            KEY `userId` (`userId`),
            CONSTRAINT `cantiga_place_members_ibfk_1` FOREIGN KEY (`placeId`) REFERENCES `cantiga_places` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `cantiga_place_members_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `cantiga_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_territories` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `projectId` int(11) NOT NULL,
            `name` varchar(100) NOT NULL,
            `locale` varchar(10) DEFAULT NULL,
            `areaNum` int(11) NOT NULL DEFAULT "0",
            `requestNum` int(11) NOT NULL DEFAULT "0",
            PRIMARY KEY (`id`),
            KEY `name` (`name`),
            KEY `projectId` (`projectId`),
            CONSTRAINT `cantiga_territories_ibfk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_group_categories` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `projectId` int(11) NOT NULL,
            `name` varchar(40) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `projectId` (`projectId`),
            CONSTRAINT `cantiga_group_categories_fk1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_groups` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(50) NOT NULL,
            `categoryId` int(11) DEFAULT NULL,
            `notes` varchar(500) DEFAULT "",
            `slug` varchar(12) NOT NULL,
            `projectId` int(11) NOT NULL,
            `areaNum` int(11) NOT NULL,
            `placeId` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `placeId` (`placeId`) USING BTREE,
            KEY `projectId` (`projectId`),
            KEY `categoryId` (`categoryId`),
            CONSTRAINT `cantiga_groups_fk2` FOREIGN KEY (`categoryId`) REFERENCES `cantiga_group_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
            CONSTRAINT `cantiga_groups_fk3` FOREIGN KEY (`placeId`) REFERENCES `cantiga_places` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
            CONSTRAINT `cantiga_groups_ibfk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_area_statuses` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(30) NOT NULL,
            `label` varchar(30) NOT NULL,
            `isDefault` tinyint(4) NOT NULL DEFAULT "0",
            `areaNum` int(11) NOT NULL DEFAULT "0",
            `projectId` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `projectId` (`projectId`),
            CONSTRAINT `cantiga_area_statuses_ibfk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_areas` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `slug` varchar(12) NOT NULL,
            `projectId` int(11) NOT NULL,
            `groupId` int(11) DEFAULT NULL,
            `groupName` varchar(50) DEFAULT NULL,
            `statusId` int(11) NOT NULL,
            `territoryId` int(11) NOT NULL,
            `reporterId` int(11) DEFAULT NULL,
            `createdAt` int(11) NOT NULL,
            `lastUpdatedAt` int(11) NOT NULL,
            `percentCompleteness` int(11) NOT NULL,
            `visiblePublicly` int(11) NOT NULL,
            `stationaryTraining` tinyint(4) NOT NULL DEFAULT "0",
            `contract` tinyint(4) NOT NULL DEFAULT "0",
            `customData` text NOT NULL,
            `placeId` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`),
            UNIQUE KEY `placeId` (`placeId`) USING BTREE,
            KEY `projectId` (`projectId`),
            KEY `groupId` (`groupId`),
            KEY `statusId` (`statusId`),
            KEY `territoryId` (`territoryId`),
            CONSTRAINT `cantiga_areas_fk5` FOREIGN KEY (`placeId`) REFERENCES `cantiga_places` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
            CONSTRAINT `cantiga_areas_ibfk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
            CONSTRAINT `cantiga_areas_ibfk_2` FOREIGN KEY (`groupId`) REFERENCES `cantiga_groups` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
            CONSTRAINT `cantiga_areas_ibfk_3` FOREIGN KEY (`statusId`) REFERENCES `cantiga_area_statuses` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
            CONSTRAINT `cantiga_areas_ibfk_4` FOREIGN KEY (`territoryId`) REFERENCES `cantiga_territories` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_area_requests` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `projectId` int(11) NOT NULL,
            `name` varchar(100) NOT NULL,
            `requestorId` int(11) NOT NULL,
            `verifierId` int(11) DEFAULT NULL,
            `territoryId` int(11) NOT NULL,
            `createdAt` int(11) NOT NULL,
            `lastUpdatedAt` int(11) NOT NULL,
            `lat` decimal(18,9) DEFAULT NULL,
            `lng` decimal(18,9) DEFAULT NULL,
            `customData` text NOT NULL,
            `status` tinyint(4) NOT NULL DEFAULT "0",
            `commentNum` int(11) NOT NULL DEFAULT "0",
            `areaId` int(11) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `reporterId` (`requestorId`),
            KEY `verifierId` (`verifierId`),
            KEY `areaId` (`areaId`),
            KEY `territoryId` (`territoryId`),
            CONSTRAINT `cantiga_area_requests_ibfk_2` FOREIGN KEY (`requestorId`) REFERENCES `cantiga_users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
            CONSTRAINT `cantiga_area_requests_ibfk_3` FOREIGN KEY (`verifierId`) REFERENCES `cantiga_users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
            CONSTRAINT `cantiga_area_requests_ibfk_4` FOREIGN KEY (`areaId`) REFERENCES `cantiga_areas` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
            CONSTRAINT `cantiga_area_requests_ibfk_5` FOREIGN KEY (`territoryId`) REFERENCES `cantiga_territories` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_area_request_comments` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `requestId` int(11) NOT NULL,
            `userId` int(11) NOT NULL,
            `createdAt` int(11) NOT NULL,
            `message` varchar(500) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `requestId` (`requestId`),
            KEY `userId` (`userId`),
            CONSTRAINT `cantiga_area_request_comments_ibfk_1` FOREIGN KEY (`requestId`) REFERENCES `cantiga_area_requests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `cantiga_area_request_comments_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `cantiga_users` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_contacts` (
            `userId` int(11) NOT NULL,
            `placeId` int(11) NOT NULL,
            `email` varchar(100) DEFAULT NULL,
            `telephone` varchar(30) DEFAULT NULL,
            `notes` varchar(250) DEFAULT NULL,
            PRIMARY KEY (`userId`,`placeId`),
            KEY `placeId` (`placeId`),
            CONSTRAINT `cantiga_contacts_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `cantiga_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `cantiga_contacts_ibfk_2` FOREIGN KEY (`placeId`) REFERENCES `cantiga_places` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_courses` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `projectId` int(11) NOT NULL,
            `name` varchar(100) NOT NULL,
            `description` varchar(255) DEFAULT NULL,
            `authorName` varchar(100) NOT NULL,
            `authorEmail` varchar(100) NOT NULL,
            `lastUpdated` int(11) NOT NULL,
            `presentationLink` varchar(255) NOT NULL,
            `deadline` int(11) DEFAULT NULL,
            `isPublished` int(11) NOT NULL DEFAULT "0",
            `displayOrder` int(11) NOT NULL,
            `notes` varchar(255) DEFAULT "",
            PRIMARY KEY (`id`),
            KEY `inProject` (`projectId`,`displayOrder`),
            CONSTRAINT `cantiga_courses_fk1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_course_results` (
            `userId` int(11) NOT NULL,
            `courseId` int(11) NOT NULL,
            `trialNumber` int(11) NOT NULL,
            `startedAt` int(11) NOT NULL,
            `completedAt` int(11) DEFAULT NULL,
            `result` tinyint(4) NOT NULL,
            `totalQuestions` int(11) NOT NULL,
            `passedQuestions` int(11) NOT NULL,
            PRIMARY KEY (`userId`,`courseId`),
            KEY `userId` (`userId`),
            KEY `courseId` (`courseId`),
            CONSTRAINT `cantiga_course_results_fk1` FOREIGN KEY (`userId`) REFERENCES `cantiga_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `cantiga_course_results_fk2` FOREIGN KEY (`courseId`) REFERENCES `cantiga_courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_course_area_results` (
            `areaId` int(11) NOT NULL,
            `courseId` int(11) NOT NULL,
            `userId` int(11) NOT NULL,
            PRIMARY KEY (`areaId`,`courseId`),
            KEY `userId` (`userId`),
            KEY `cantiga_course_area_results_fk2` (`userId`,`courseId`),
            CONSTRAINT `cantiga_course_area_results_fk1` FOREIGN KEY (`areaId`) REFERENCES `cantiga_areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `cantiga_course_area_results_fk2` FOREIGN KEY (`userId`, `courseId`) REFERENCES `cantiga_course_results` (`userId`, `courseId`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_course_progress` (
            `areaId` int(11) NOT NULL,
            `mandatoryCourseNum` int(11) NOT NULL DEFAULT "0",
            `passedCourseNum` int(11) NOT NULL DEFAULT "0",
            `failedCourseNum` int(11) NOT NULL DEFAULT "0",
            PRIMARY KEY (`areaId`),
            KEY `passedCourseNum` (`passedCourseNum`),
            CONSTRAINT `cantiga_course_progress_fk1` FOREIGN KEY (`areaId`) REFERENCES `cantiga_areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=latin2');
        $this->addSql('CREATE TABLE `cantiga_course_tests` (
            `courseId` int(11) NOT NULL,
            `testStructure` text NOT NULL,
            PRIMARY KEY (`courseId`),
            CONSTRAINT `cantiga_course_tests_fk1` FOREIGN KEY (`courseId`) REFERENCES `cantiga_courses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_credential_changes` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `userId` int(11) NOT NULL,
            `provisionKey` varchar(40) NOT NULL,
            `password` varchar(100) DEFAULT NULL,
            `salt` varchar(40) DEFAULT NULL,
            `email` varchar(100) DEFAULT NULL,
            `requestIp` bigint(20) NOT NULL,
            `requestTime` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `userId` (`userId`),
            CONSTRAINT `cantiga_credential_changes_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `cantiga_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_data_export` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(40) NOT NULL,
            `projectId` int(11) NOT NULL,
            `areaStatusId` int(11) DEFAULT NULL,
            `url` varchar(100) NOT NULL,
            `encryptionKey` varchar(128) NOT NULL,
            `active` tinyint(1) NOT NULL,
            `notes` text,
            `lastExportedAt` int(11) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `projectId` (`projectId`),
            KEY `areaStatusId` (`areaStatusId`),
            CONSTRAINT `cantiga_data_export_fk1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `cantiga_data_export_fk2` FOREIGN KEY (`areaStatusId`) REFERENCES `cantiga_area_statuses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_discussion_channels` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `projectId` int(11) NOT NULL,
            `name` varchar(50) NOT NULL,
            `description` varchar(250) NOT NULL DEFAULT "",
            `color` varchar(30) NOT NULL DEFAULT "green",
            `icon` varchar(30) NOT NULL,
            `projectVisible` tinyint(1) NOT NULL,
            `groupVisible` tinyint(1) NOT NULL,
            `areaVisible` tinyint(1) NOT NULL,
            `projectPosting` int(11) NOT NULL,
            `groupPosting` int(11) NOT NULL,
            `areaPosting` int(11) NOT NULL,
            `discussionGrouping` tinyint(2) NOT NULL,
            `enabled` tinyint(1) NOT NULL DEFAULT "1",
            PRIMARY KEY (`id`),
            KEY `projectId` (`projectId`),
            CONSTRAINT `cantiga_discussion_channels_ibfk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_discussion_subchannels` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `channelId` int(11) NOT NULL,
            `entityId` int(11) NOT NULL,
            `lastPostTime` int(11) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `channelId` (`channelId`),
            KEY `entityId` (`entityId`),
            CONSTRAINT `cantiga_discussion_subchannels_ibfk_1` FOREIGN KEY (`channelId`) REFERENCES `cantiga_discussion_channels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `cantiga_discussion_subchannels_ibfk_2` FOREIGN KEY (`entityId`) REFERENCES `cantiga_places` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_discussion_posts` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `subchannelId` int(11) NOT NULL,
            `authorId` int(11) NOT NULL,
            `createdAt` int(11) NOT NULL,
            `content` text,
            PRIMARY KEY (`id`),
            KEY `channelId` (`subchannelId`),
            KEY `authorId` (`authorId`),
            CONSTRAINT `cantiga_discussion_posts_ibfk_1` FOREIGN KEY (`subchannelId`) REFERENCES `cantiga_discussion_subchannels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `cantiga_discussion_posts_ibfk_2` FOREIGN KEY (`authorId`) REFERENCES `cantiga_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_invitations` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `email` varchar(100) NOT NULL,
            `userId` int(11) DEFAULT NULL,
            `role` int(11) NOT NULL,
            `note` varchar(30) NOT NULL,
            `showDownstreamContactData` tinyint(1) NOT NULL DEFAULT "0",
            `placeId` int(11) NOT NULL,
            `inviterId` int(11) NOT NULL,
            `createdAt` int(11) NOT NULL,
            `assignmentKey` varchar(100) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniqueCombination` (`email`,`placeId`) USING BTREE,
            KEY `inviterId` (`inviterId`),
            KEY `placeId` (`placeId`),
            CONSTRAINT `cantiga_invitations_ibfk_1` FOREIGN KEY (`placeId`) REFERENCES `cantiga_places` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_links` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `url` varchar(100) NOT NULL,
            `projectId` int(11) DEFAULT NULL,
            `presentedTo` tinyint(4) NOT NULL DEFAULT "0",
            `listOrder` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `projectId` (`projectId`),
            CONSTRAINT `cantiga_links_ibfk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_milestones` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `projectId` int(11) NOT NULL,
            `name` varchar(60) NOT NULL,
            `description` text,
            `displayOrder` int(11) NOT NULL DEFAULT "1",
            `type` tinyint(4) NOT NULL DEFAULT "0",
            `entityType` varchar(30) NOT NULL,
            `deadline` int(11) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `projectId` (`projectId`),
            KEY `presentation` (`projectId`,`displayOrder`),
            KEY `entityType` (`entityType`),
            CONSTRAINT `cantiga_milestones_fk1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_milestone_progress` (
            `entityId` int(11) NOT NULL,
            `completedNum` int(11) NOT NULL DEFAULT "0",
            PRIMARY KEY (`entityId`),
            CONSTRAINT `cantiga_milestone_progress_fk1` FOREIGN KEY (`entityId`) REFERENCES `cantiga_places` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_milestone_rules` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `projectId` int(11) NOT NULL,
            `milestoneId` int(11) NOT NULL,
            `name` varchar(80) NOT NULL,
            `activator` varchar(80) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `milestoneId` (`milestoneId`),
            KEY `projectId` (`projectId`),
            CONSTRAINT `cantiga_milestone_rules_fk_1` FOREIGN KEY (`milestoneId`) REFERENCES `cantiga_milestones` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `cantiga_milestone_rules_fk_2` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_milestone_status` (
            `entityId` int(11) NOT NULL,
            `milestoneId` int(11) NOT NULL,
            `progress` int(11) NOT NULL,
            `completedAt` int(11) DEFAULT NULL,
            PRIMARY KEY (`entityId`,`milestoneId`),
            KEY `cantiga_milestone_status_fk2` (`milestoneId`),
            CONSTRAINT `cantiga_milestone_status_fk1` FOREIGN KEY (`entityId`) REFERENCES `cantiga_places` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `cantiga_milestone_status_fk2` FOREIGN KEY (`milestoneId`) REFERENCES `cantiga_milestones` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_milestone_status_rules` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `projectId` int(11) NOT NULL,
            `name` varchar(80) NOT NULL,
            `newStatusId` int(11) NOT NULL,
            `prevStatusId` int(11) NOT NULL,
            `milestoneMap` text,
            `activationOrder` int(11) NOT NULL,
            `lastUpdatedAt` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `projectId` (`projectId`),
            KEY `projectActivation` (`projectId`,`activationOrder`),
            KEY `cantiga_milestone_status_rules_fk2` (`newStatusId`),
            KEY `cantiga_milestone_status_rules_fk3` (`prevStatusId`),
            CONSTRAINT `cantiga_milestone_status_rules_fk1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `cantiga_milestone_status_rules_fk2` FOREIGN KEY (`newStatusId`) REFERENCES `cantiga_area_statuses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `cantiga_milestone_status_rules_fk3` FOREIGN KEY (`prevStatusId`) REFERENCES `cantiga_area_statuses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_password_recovery` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `userId` int(11) NOT NULL,
            `provisionKey` varchar(40) NOT NULL,
            `requestIp` bigint(20) NOT NULL,
            `requestTime` int(11) NOT NULL,
            `status` tinyint(4) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `userId` (`userId`,`provisionKey`),
            CONSTRAINT `cantiga_password_recovery_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `cantiga_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_project_settings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `projectId` int(11) NOT NULL,
            `module` varchar(30) NOT NULL,
            `name` varchar(70) NOT NULL,
            `key` varchar(250) NOT NULL,
            `value` varchar(250) NOT NULL,
            `type` tinyint(4) NOT NULL,
            `extensionPoint` varchar(40) DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_project_pair` (`projectId`,`key`),
            CONSTRAINT `cantiga_project_settings_ibfk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_stat_arq_time` (
            `projectId` int(11) NOT NULL,
            `datePoint` date NOT NULL,
            `requestsNew` int(11) NOT NULL,
            `requestsVerification` int(11) NOT NULL,
            `requestsApproved` int(11) NOT NULL,
            `requestsRejected` int(11) NOT NULL,
            PRIMARY KEY (`projectId`,`datePoint`),
            CONSTRAINT `cantiga_stat_arq_time_ibfk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_stat_courses` (
            `projectId` int(11) NOT NULL,
            `datePoint` date NOT NULL,
            `areasWithCompletedCourses` int(11) NOT NULL,
            `avgCompletedCourses` double NOT NULL,
            PRIMARY KEY (`projectId`,`datePoint`),
            CONSTRAINT `cantiga_stat_courses_fk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_texts` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `place` varchar(30) NOT NULL,
            `projectId` int(11) DEFAULT NULL,
            `title` varchar(100) NOT NULL,
            `content` text NOT NULL,
            `locale` varchar(10) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `key` (`place`),
            KEY `projectId` (`projectId`),
            CONSTRAINT `cantiga_texts_ibfk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_user_profiles` (
            `userId` int(11) NOT NULL,
            `location` varchar(100) DEFAULT NULL,
            `settingsLanguageId` int(11) NOT NULL,
            `settingsTimezone` varchar(30) NOT NULL DEFAULT "UTC",
            `afterLogin` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`userId`),
            CONSTRAINT `cantiga_user_profiles_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `cantiga_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_stat_edk_participants` (
            `projectId` int(11) NOT NULL,
            `datePoint` date NOT NULL,
            `participantNum` int(11) NOT NULL,
            PRIMARY KEY (`projectId`,`datePoint`),
            CONSTRAINT `cantiga_stat_edk_participants_fk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_stat_edk_area_participants` (
            `projectId` int(11) NOT NULL,
            `areaId` int(11) NOT NULL,
            `datePoint` date NOT NULL,
            `participantNum` int(11) NOT NULL,
            PRIMARY KEY (`projectId`,`areaId`,`datePoint`),
            KEY `cantiga_stat_edk_area_participants_fk_2` (`areaId`),
            CONSTRAINT `cantiga_stat_edk_area_participants_fk_1` FOREIGN KEY (`projectId`) REFERENCES `cantiga_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `cantiga_stat_edk_area_participants_fk_2` FOREIGN KEY (`areaId`) REFERENCES `cantiga_areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_edk_area_notes` (
            `areaId` int(11) NOT NULL,
            `noteType` tinyint(4) NOT NULL,
            `content` text NOT NULL,
            `lastUpdatedAt` int(11) DEFAULT NULL,
            PRIMARY KEY (`areaId`,`noteType`),
            CONSTRAINT `cantiga_edk_area_notes_fk1` FOREIGN KEY (`areaId`) REFERENCES `cantiga_areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_edk_messages` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `areaId` int(11) NOT NULL,
            `subject` varchar(100) NOT NULL,
            `content` text NOT NULL,
            `authorName` varchar(50) NOT NULL,
            `authorEmail` varchar(100) DEFAULT NULL,
            `authorPhone` varchar(30) DEFAULT NULL,
            `createdAt` int(11) NOT NULL,
            `answeredAt` int(11) DEFAULT NULL,
            `completedAt` int(11) DEFAULT NULL,
            `status` tinyint(1) NOT NULL DEFAULT "0",
            `responderId` int(11) DEFAULT NULL,
            `duplicate` tinyint(1) NOT NULL DEFAULT "0",
            `ipAddress` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `areaId` (`areaId`),
            KEY `responderId` (`responderId`),
            KEY `lastUpdate` (`ipAddress`,`createdAt`),
            CONSTRAINT `cantiga_edk_messages_fk1` FOREIGN KEY (`areaId`) REFERENCES `cantiga_areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `cantiga_edk_messages_fk2` FOREIGN KEY (`responderId`) REFERENCES `cantiga_users` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_edk_routes` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `areaId` int(11) NOT NULL,
            `routeType` tinyint(1) NOT NULL,
            `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL,
            `routeFrom` varchar(50) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL,
            `routeTo` varchar(50) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL,
            `routeCourse` varchar(500) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL,
            `routeLength` int(11) NOT NULL,
            `routeAscent` int(11) NOT NULL,
            `routeObstacles` varchar(100) DEFAULT NULL,
            `createdAt` int(11) NOT NULL,
            `updatedAt` int(11) NOT NULL,
            `approved` tinyint(4) NOT NULL DEFAULT "0",
            `descriptionFile` varchar(60) DEFAULT NULL,
            `mapFile` varchar(60) DEFAULT NULL,
            `gpsTrackFile` varchar(60) NOT NULL,
            `publicAccessSlug` varchar(40) NOT NULL,
            `commentNum` int(11) NOT NULL DEFAULT "0",
            `importedFrom` int(11) DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `publicAccessSlug` (`publicAccessSlug`),
            KEY `approved` (`approved`),
            KEY `areaId` (`areaId`),
            KEY `routeFrom` (`routeFrom`),
            KEY `routeTo` (`routeTo`),
            KEY `routeLength` (`routeLength`),
            CONSTRAINT `cantiga_edk_routes_fk_1` FOREIGN KEY (`areaId`) REFERENCES `cantiga_areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_edk_participants` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `accessKey` varchar(40) NOT NULL,
            `routeId` int(11) NOT NULL,
            `areaId` int(11) NOT NULL,
            `firstName` varchar(30) NOT NULL,
            `lastName` varchar(40) NOT NULL,
            `sex` tinyint(1) NOT NULL,
            `age` tinyint(4) DEFAULT NULL,
            `email` varchar(100) DEFAULT NULL,
            `peopleNum` tinyint(1) NOT NULL DEFAULT "1",
            `customAnswer` varchar(250) DEFAULT NULL,
            `howManyTimes` tinyint(4) NOT NULL,
            `whyParticipate` varchar(200) DEFAULT NULL,
            `whereLearnt` tinyint(4) NOT NULL,
            `whereLearntOther` varchar(40) DEFAULT NULL,
            `terms1Accepted` tinyint(4) NOT NULL,
            `terms2Accepted` tinyint(4) NOT NULL,
            `terms3Accepted` tinyint(4) NOT NULL,
            `createdAt` int(11) NOT NULL,
            `ipAddress` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `accessKey` (`accessKey`),
            KEY `routeId` (`routeId`),
            KEY `areaId` (`areaId`),
            KEY `ipAddress` (`ipAddress`),
            KEY `byLastName` (`areaId`,`lastName`),
            CONSTRAINT `cantiga_edk_participants_fk1` FOREIGN KEY (`areaId`) REFERENCES `cantiga_areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `cantiga_edk_participants_fk2` FOREIGN KEY (`routeId`) REFERENCES `cantiga_edk_routes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_edk_registration_settings` (
            `routeId` int(11) NOT NULL,
            `areaId` int(11) NOT NULL,
            `registrationType` tinyint(4) NOT NULL,
            `startTime` int(11) DEFAULT NULL,
            `endTime` int(11) DEFAULT NULL,
            `externalRegistrationUrl` varchar(100) DEFAULT NULL,
            `participantLimit` int(11) DEFAULT NULL,
            `maxPeoplePerRecord` int(11) DEFAULT NULL,
            `allowLimitExceed` tinyint(4) DEFAULT NULL,
            `participantNum` int(11) NOT NULL DEFAULT "0",
            `externalParticipantNum` int(11) NOT NULL DEFAULT "0",
            `customQuestion` varchar(200) DEFAULT NULL,
            PRIMARY KEY (`routeId`),
            KEY `areaId` (`areaId`),
            CONSTRAINT `cantiga_edk_registration_settings_fk1` FOREIGN KEY (`routeId`) REFERENCES `cantiga_edk_routes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `cantiga_edk_registration_settings_fk2` FOREIGN KEY (`areaId`) REFERENCES `cantiga_areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_edk_removed_participants` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `areaId` int(11) NOT NULL,
            `participantId` int(11) NOT NULL,
            `email` varchar(100) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL,
            `reason` varchar(150) CHARACTER SET utf8 COLLATE utf8_polish_ci NOT NULL,
            `removedAt` int(11) NOT NULL,
            `removedById` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `participantId` (`participantId`),
            KEY `areaId` (`areaId`),
            KEY `removedById` (`removedById`),
            CONSTRAINT `cantiga_edk_removed_participants_fk1` FOREIGN KEY (`areaId`) REFERENCES `cantiga_areas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `cantiga_edk_removed_participants_fk2` FOREIGN KEY (`removedById`) REFERENCES `cantiga_users` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_edk_route_comments` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `routeId` int(11) NOT NULL,
            `userId` int(11) NOT NULL,
            `createdAt` int(11) NOT NULL,
            `message` varchar(500) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `routeId` (`routeId`),
            KEY `userId` (`userId`),
            CONSTRAINT `cantiga_edk_route_comments_ibfk_1` FOREIGN KEY (`routeId`) REFERENCES `cantiga_edk_routes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `cantiga_edk_route_comments_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `cantiga_users` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        $this->addSql('CREATE TABLE `cantiga_edk_route_notes` (
            `routeId` int(11) NOT NULL,
            `noteType` tinyint(4) NOT NULL,
            `content` text NOT NULL,
            `lastUpdatedAt` int(11) DEFAULT NULL,
            PRIMARY KEY (`routeId`,`noteType`),
            CONSTRAINT `cantiga_edk_route_notes_ibfk_1` FOREIGN KEY (`routeId`) REFERENCES `cantiga_edk_routes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addSql('INSERT INTO `cantiga_languages` VALUES(1, "English", "en")');
        $this->addSql('INSERT INTO `cantiga_languages` VALUES(2, "Polski", "pl")');

        $this->addSql('INSERT INTO `cantiga_mail` VALUES(1, "cantiga:user-registration", "en", "[Cantiga] New account created", 1450001229, "<html>\r\n<body>\r\n <p>Welcome to Cantiga! You have created a new account in the system:</p>\r\n <ul>\r\n  <li><strong>Your login:</strong> {{ login }}</li>\r\n  <li><strong>Your name:</strong> {{ name }}</li>\r\n </ul>\r\n<p>Before you can use the system, you must activate the account.</p>\r\n <p><a href=\"{{ path(\'cantiga_auth_activate\', {id: id, provisionKey: key}) }}\">Click here to activate the account</a></p>\r\n</body>\r\n</html>")');
        $this->addSql('INSERT INTO `cantiga_mail` VALUES(2, "cantiga:password-recovery", "en", "[Cantiga] Password recovery request", 1450001417, "<html>\r\n<body>\r\n <p>We have received a request to recover a password for your account in Cantiga. If the request has been sent by you, please follow the instructions to reset the password. Otherwise, <strong>please ignore this message</strong>.</p>\r\n <ul>\r\n  <li><strong>Your login:</strong> {{ login }}</li>\r\n  <li><strong>Request from IP:</strong> {{ ip }}</li>\r\n  <li><strong>Request time:</strong> {{ format_time(TimeFormatter.FORMAT_LONG, time) }}</li>\r\n </ul>\r\n <p><a href=\"{{ path(\'cantiga_auth_recovery_complete\', {id: id, provisionKey: key}) }}\">Click here to set the new password for your account</a></p>\r\n</body>\r\n</html>")');
        $this->addSql('INSERT INTO `cantiga_mail` VALUES(3, "cantiga:password-recovery-completed", "en", "[Cantiga] Password recovery successful", 1450001574, "<html>\r\n<body>\r\n <p>The password recovery for your account <strong>{{ login }}</strong> in Cantiga has been completed. <a href=\"{{path(\'cantiga_auth_login\') }}\">You can now login with your new password</a></p>\r\n</body>\r\n</html>")');
        $this->addSql('INSERT INTO `cantiga_mail` VALUES(4, "cantiga:credential-change", "en", "[Cantiga] Credential changes", 1450001670, "<html>\r\n<body>\r\n <p>You have requested to change your e-mail or password to your account <strong>{{ login }}</strong> in Cantiga. To confirm the change and activate the new credentials, please click the following link:</p>\r\n <p><a href=\"{{ path(\'user_profile_confirm_credential_change\', {id: id, provisionKey: key}) }}\">Confirm credential change</a></p>\r\n\r\n <p>If you have not requested any change, please ignore this message, as it will expire in a short time. However, this means that someone else has an access to your account.</p>\r\n</body>\r\n</html>")');
        $this->addSql('INSERT INTO `cantiga_mail` VALUES(5, "cantiga:invitation-member", "en", "[Cantiga] Invitation to join", 0, "<html>\r\n  <body>\r\n   <h2>Hello, {{ user.name }}</h2>\r\n  <p>{{ inviter.name }} has invited you to join the {{ invitation.resourceType~\'Nominative: 0\' | trans([invitation.resourceName]) }} in Cantiga. Because you have already an account in the system, you can just login to your profile and click the <strong>Invitations</strong> option to confirm it.</p>\r\n  </body>\r\n</html>")');
        $this->addSql('INSERT INTO `cantiga_mail` VALUES(6, "cantiga:invitation-anonymous", "en", "[Cantiga] Invitation to join", 0, "<html>\r\n  <body>\r\n   <h2>Hello!</h2>\r\n  <p>{{ inviter.name }} has invited you to join the {{ invitation.resourceType~\'Nominative: 0\' | trans([invitation.resourceName]) }} in Cantiga. To join, you must first <a href=\"{{ path(\'cantiga_auth_register\', { \'fromMail\': invitation.email, \'_locale\': \'en\'}) }}\">create an account</a> using the e-mail address where you have received the invitation. Once registered, please follow to the <strong>Invitations</strong> page and confirm it.</p>\r\n  <p>If you wish to register an account under a different e-mail address (or you DO HAVE an account with a different e-mail), you can still accept this invitation. To do so, please visit the <strong>Invitations</strong> page and in the option <strong>Find invitation</strong>, enter the following invitation code:</p>\r\n <p><strong>{{ invitation.assignmentKey }}</strong></p>\r\n <p>The invitation will be added to your account and you will be able to confirm it.</p>\r\n  </body>\r\n</html>")');
        $this->addSql('INSERT INTO `cantiga_mail` VALUES(7, "cantiga:area-request:verification", "en", "[Cantiga] Area verification started", 0, "<html>\r\n <body>\r\n   <p>We have started the verification of your request to create a new area \"{{ request.name }}\" in project {{ request.project.name }}. You can follow the status of your request in Cantiga, after logging in. Please note that there is a box called \"Feedback\", where the verifier can ask you some questions. Please check it from time to time and answer them. It will help us in the verification process. You will be informed via e-mail whether your request has been approved or revoked.</p>\r\n <p>This message has been generated automatically. Please do not answer it.</p>\r\n </body>\r\n</html>")');
        $this->addSql('INSERT INTO `cantiga_mail` VALUES(8, "cantiga:area-request:revoked", "en", "[Cantiga] Area request revoked", 0, "<html>\r\n <body>\r\n   <p>Your request for creating the area \"{{ request.name }}\" in project {{ request.project.name }} has been revoked. The detailed information can be found on the feedback page of your request.</p>\r\n\r\n<p>This message has been generated automatically. Please do not answer it.</p>\r\n </body>\r\n</html>")');
        $this->addSql('INSERT INTO `cantiga_mail` VALUES(9, "cantiga:area-request:approved", "en", "[Cantiga] Area request approved", 0, "<html>\r\n <body>\r\n   <p>Thank you, your request for creating an area \"{{ request.name }}\" in project {{ request.project.name }} has been approved. Please visit Cantiga to manage your area. You can find it by clicking on the field with two arrows on the top bar.</p>\r\n\r\n <p>What\'s next?</p>\r\n <ol>\r\n   <li>invite other members of your team to create an account an join the area,</li>\r\n   <li>fill in the area profile,</li>\r\n   <li>follow the instructions provided by the project coordinators.</li>\r\n </ol>\r\n\r\n  <p>This message has been generated automatically. Please do not answer it.</p>\r\n </body>\r\n</html>")');
        $this->addSql('INSERT INTO `cantiga_mail` VALUES(10, "cantiga:invitation-member", "pl", "[Cantiga] Zaproszenie do dołączenia", 1450002416, "<html>\r\n  <body>\r\n   <h2>Witaj, {{ user.name }}</h2>\r\n  <p>{{ inviter.name }} zaprosił Cię do dołączenia do {{ (invitation.resourceType~\'Genitive\') | trans }} \"{{ invitation.resourceName }}\" w systemie Cantiga. Ponieważ posiadasz już konto w tym systemie, wystarczy że zalogujesz się na swój profil i przejdziesz do podstrony <strong>Zaproszenia</strong>.</p>\r\n  </body>\r\n</html>")');
        $this->addSql('INSERT INTO `cantiga_mail` VALUES(11, "cantiga:invitation-anonymous", "pl", "[Cantiga] Zaproszenie do dołączenia", 1450002434, "<html>\r\n  <body>\r\n   <h2>Witaj!</h2>\r\n  <p>{{ inviter.name }} zaprosił Cię do dołączenia do {{ (invitation.resourceType~\'Genitive\') | trans }} \"{{ invitation.resourceName }}\" w systemie Cantiga.  Aby dołączyć, <a href=\"{{ path(\'cantiga_auth_register\', { \'fromMail\': invitation.email, \'_locale\': \'pl\'}) }}\">zarejestruj się</a> przy pomocy tego samego adresu e-mail, na który otrzymałeś zaproszenie, a następnie po pierwszym zalogowaniu przejdź do podstrony <strong>Zaproszenia</strong>.</p>\r\n  <p>Jeśli chciałbyś założyć konto pod innym adresem e-mail, wciąż możesz skorzystać z tego zaproszenia. Aby to zrobić, po założeniu konta wejdź w zakładkę <strong>Zaproszenia</strong>, wybierz opcję <strong>Znajdź zaproszenie</strong> i podaj następujący kod tego zaproszenia:</p>\r\n <p><strong>{{ invitation.assignmentKey }}</strong></p>\r\n  </body>\r\n</html>")');
        $this->addSql('INSERT INTO `cantiga_mail` VALUES(12, "cantiga:user-registration", "pl", "[Cantiga] Założenie nowego konta", 1450001335, "<html>\r\n<body>\r\n <p>Witaj w Cantiga. Założyłeś właśnie nowe konto w naszym systemie:</p>\r\n <ul>\r\n  <li><strong>Twój login:</strong> {{ login }}</li>\r\n  <li><strong>Twoje imię i nazwisko:</strong> {{ name }}</li>\r\n </ul>\r\n<p>Zanim zaczniesz używać systemu, musisz aktywować swoje konto.</p>\r\n <p><a href=\"{{ path(\'cantiga_auth_activate\', {id: id, provisionKey: key, \'_locale\': \'pl\' }) }}\">Kliknij tutaj, aby aktywować konto.</a></p>\r\n</body>\r\n</html>")');
        $this->addSql('INSERT INTO `cantiga_mail` VALUES(13, "cantiga:area-request:verification", "pl", "[Cantiga] Weryfikacja zgłoszenia rejonu rozpoczęta", 1450002219, "<html>\r\n <body>\r\n   <p>Rozpoczęła się weryfikacja Twojego zgłoszenia rejonu \"{{ request.name }}\" w projekcie {{ request.project.name }}. Stan Twojego zgłoszenia możesz na bieżąco śledzić w systemie po zalogowaniu się, zaś o przyjęciu bądź odrzuceniu zostaniesz dodatkowo powiadomiony e-mailem. W podglądzie zgłoszenia w systemie znajduje się okno \"Informacja zwrotna\", w którym osoba weryfikująca może zamieścić dla Ciebie dodatkowe pytania - zaglądaj tam od czasu do czasu oraz udziel wszystkich potrzebnych informacji. Pomoże to w weryfikacji zgłoszenia.</p>\r\n\r\n<p>Ta wiadomość została wygenerowana automatycznie. Prosimy na nią nie odpowiadać.</p>\r\n </body>\r\n</html>")');
        $this->addSql('INSERT INTO `cantiga_mail` VALUES(14, "cantiga:area-request:revoked", "pl", "[Cantiga] Zgłoszenie rejonu zostało odrzucone", 1450002452, "<html>\r\n <body>\r\n   <p>Twoje zgłoszenie rejonu \"{{ request.name }}\" w projekcie {{ request.project.name }} zostało odrzucone. Szczegółowe informacje znajdziesz w podglądzie Twojego zgłoszenia w systemie w oknie \"Informacja zwrotna\".</p>\r\n\r\n<p>Ta wiadomość została wygenerowana automatycznie. Prosimy na nią nie odpowiadać.</p>\r\n </body>\r\n</html>")');
        $this->addSql('INSERT INTO `cantiga_mail` VALUES(15, "cantiga:area-request:approved", "pl", "[Cantiga] Zgłoszenie rejonu zostało przyjęte", 1450002532, "<html>\r\n <body>\r\n   <p>Dziękujemy, Twoje zgłoszenie rejonu \"{{ request.name }}\" w projekcie {{ request.project.name }} zostało przyjęte. Zaloguj się do systemu, aby móc zarządzać swoim rejonem. Możesz się do niego dostać, klikając na rozwijaną listę oznaczoną strzałkami na górnej belce nawigacyjnej.</p>\r\n\r\n <p>Co teraz?</p>\r\n <ol>\r\n   <li>zaproś pozostałych liderów do założenia konta w systemie i dołączenia do Twojego rejonu,</li>\r\n   <li>uzupełnij profil,</li>\r\n   <li>postępuj zgodnie z dalszymi informacjami zamieszczonymi w systemie.</li>\r\n </ol>\r\n\r\n  <p>Ta wiadomość została wygenerowana automatycznie. Prosimy na nią nie odpowiadać.</p>\r\n </body>\r\n</html>")');
        $this->addSql('INSERT INTO `cantiga_mail` VALUES(16, "cantiga:password-recovery", "pl", "[Cantiga] Odzyskiwanie hasła", 0, "<html>\r\n<body>\r\n <p>Otrzymaliśmy zgłoszenie chęci odzyskania zapomnianego hasła do Twojego konta w Cantiga. Jeśli faktycznie wystawiłeś takie zgłoszenie, prosimy postępować zgodnie z dalszymi instrukcjami w celu ustawienia nowego hasła. W przeciwnym wypadku prosimy zignorować tę wiadomość.</p>\r\n <ul>\r\n  <li><strong>Twój login:</strong> {{ login }}</li>\r\n  <li><strong>Zgłoszenie wysłane z adresu IP:</strong> {{ ip }}</li>\r\n  <li><strong>Czas przyjęcia zgłoszenia:</strong> {{ format_time(TimeFormatter.FORMAT_LONG, time) }}</li>\r\n </ul>\r\n <p><a href=\"{{ path(\'cantiga_auth_recovery_complete\', {id: id, provisionKey: key, \'_locale\': \'pl\'}) }}\">Kliknij tutaj, aby ustawić nowe hasło do swojego konta</a></p>\r\n</body>\r\n</html>")');
        $this->addSql('INSERT INTO `cantiga_mail` VALUES(17, "cantiga:password-recovery-completed", "pl", "[Cantiga] Odzyskiwanie hasła zakończone", 0, "<html>\r\n<body>\r\n <p>Odzyskiwanie hasła dla Twojego konta <strong>{{ login }}</strong> w Panelu Rejonów zostało zakończone. <a href=\"{{path(\'cantiga_auth_login\', {\'_locale\': \'pl\'}) }}\">Możesz teraz zalogować się z użyciem nowego hasła</a></p>\r\n</body>\r\n</html>")');
        $this->addSql('INSERT INTO `cantiga_mail` VALUES(18, "cantiga:credential-change", "pl", "[Cantiga] Zmiana danych logowania", 0, "<html>\r\n<body>\r\n <p>Zgłosiłeś chęć zmiany swojego adresu e-mail bądź hasła do konta <strong>{{ login }}</strong> w Cantiga. Aby potwierdzić zmianę i aktywować nowe dane, prosimy kliknąć w poniższy odnośnik:</p>\r\n <p><a href=\"{{ path(\'user_profile_confirm_credential_change\', {id: id, provisionKey: key, \'_locale\': \'pl\'}) }}\">Potwierdź zmianę danych</a></p>\r\n\r\n <p>Jeśli nie zmieniałeś nic, koniecznie zignoruj tę wiadomość, a wygaśnie ona w krótkim czasie. Jednakże, oznacza to że ktoś niepowołany posiada dostęp do Twojego konta!</p>\r\n</body>\r\n</html>")');

        $this->addSql('INSERT INTO `cantiga_texts` VALUES(1, "cantiga:login", NULL, "Welcome to Cantiga", "<p>Welcome to Cantiga. Please sign in to access the system and configure it.</p>", "en")');
        $this->addSql('INSERT INTO `cantiga_texts` VALUES(2, "cantiga:login", NULL, "Witaj w systemie Cantiga", "<p>Witaj w systemie Cantiga. Zaloguj się, aby uzyskać dostęp do systemu i skonfigurować go.</p>", "pl")');

        $this->addSql('INSERT INTO `cantiga_users` VALUES(1, "administrator", "Administrator", "c0vl2Nq/4I6gOwFbH32Qe/UadsPSG5VVaVyyh4/6duQ=", "ffda6bdf3973428d3d5d0c6521a336cb55ab7369", "admin@example.com", 1, 0, 1, 1, NULL, UNIX_TIMESTAMP(), 0)');
        $this->addSql('INSERT INTO `cantiga_user_profiles` VALUES(1, "", 1, "Europe/Warsaw", NULL)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE IF EXISTS `cantiga_edk_route_notes`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_edk_route_comments`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_edk_removed_participants`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_edk_registration_settings`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_edk_participants`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_edk_routes`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_edk_messages`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_edk_area_notes`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_stat_edk_area_participants`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_stat_edk_participants`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_user_profiles`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_texts`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_stat_courses`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_stat_arq_time`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_project_settings`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_password_recovery`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_milestone_status_rules`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_milestone_status`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_milestone_rules`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_milestone_progress`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_milestones`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_links`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_invitations`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_discussion_posts`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_discussion_subchannels`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_discussion_channels`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_data_export`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_credential_changes`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_course_tests`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_course_progress`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_course_area_results`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_course_results`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_courses`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_contacts`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_area_request_comments`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_area_requests`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_areas`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_area_statuses`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_groups`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_group_categories`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_territories`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_place_members`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_projects`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_places`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_mail`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_languages`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_user_registrations`');
        $this->addSql('DROP TABLE IF EXISTS `cantiga_users`');
    }
}
