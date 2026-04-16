-- =============================================================================
-- Frowear Productions — Platform Schema V2
-- =============================================================================
-- Built on top of platform_schema.sql (V1).
-- Run V1 first, then run this file.
--
-- Sections:
--   1. Users & Auth
--   2. Profile Extensions  (ALTER existing V1 tables)
--   3. Media Uploads
--   4. Social Graph
--   5. Feed & Posts
--   6. Messaging
--   7. Bidding & Contracts
--   8. Events
--   9. Notifications
--  10. V1 Index Additions
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- =============================================================================
-- SECTION 1: USERS & AUTH
-- =============================================================================

CREATE TABLE IF NOT EXISTS `users` (
  `id`               BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `email`            VARCHAR(190)     NOT NULL,
  `password_hash`    VARCHAR(255)     NOT NULL,
  `role`             ENUM('talent','company_owner','admin') NOT NULL DEFAULT 'talent',
  `email_verified`   TINYINT(1)       NOT NULL DEFAULT 0,
  `avatar_url`       VARCHAR(500)     DEFAULT NULL,
  `display_name`     VARCHAR(160)     DEFAULT NULL,
  `created_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`       TIMESTAMP        NULL     DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `idx_users_role`       (`role`),
  KEY `idx_users_deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id`           BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `user_id`      BIGINT UNSIGNED  NOT NULL,
  `token_hash`   VARCHAR(64)      NOT NULL,
  `ip_address`   VARCHAR(45)      DEFAULT NULL,
  `user_agent`   TEXT             DEFAULT NULL,
  `expires_at`   TIMESTAMP        NOT NULL,
  `created_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_sessions_token_hash` (`token_hash`),
  KEY `idx_user_sessions_user_id`    (`user_id`),
  KEY `idx_user_sessions_expires_at` (`expires_at`),
  CONSTRAINT `fk_user_sessions_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `email_verification_tokens` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     BIGINT UNSIGNED NOT NULL,
  `token_hash`  VARCHAR(64)     NOT NULL,
  `expires_at`  TIMESTAMP       NOT NULL,
  `used_at`     TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email_verification_token` (`token_hash`),
  KEY `idx_email_verification_user_id` (`user_id`),
  CONSTRAINT `fk_email_verification_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     BIGINT UNSIGNED NOT NULL,
  `token_hash`  VARCHAR(64)     NOT NULL,
  `expires_at`  TIMESTAMP       NOT NULL,
  `used_at`     TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_password_reset_token` (`token_hash`),
  KEY `idx_password_reset_user_id` (`user_id`),
  CONSTRAINT `fk_password_reset_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- SECTION 2: PROFILE EXTENSIONS (ALTER V1 TABLES)
-- =============================================================================

-- talent_profiles --------------------------------------------------------
ALTER TABLE `talent_profiles`
  ADD COLUMN IF NOT EXISTS `user_id`       BIGINT UNSIGNED  DEFAULT NULL AFTER `id`,
  ADD COLUMN IF NOT EXISTS `avatar_url`    VARCHAR(500)     DEFAULT NULL AFTER `email`,
  ADD COLUMN IF NOT EXISTS `banner_url`    VARCHAR(500)     DEFAULT NULL AFTER `avatar_url`,
  ADD COLUMN IF NOT EXISTS `city`          VARCHAR(120)     DEFAULT NULL AFTER `banner_url`,
  ADD COLUMN IF NOT EXISTS `website_url`   VARCHAR(255)     DEFAULT NULL AFTER `city`,
  ADD COLUMN IF NOT EXISTS `linkedin_url`  VARCHAR(255)     DEFAULT NULL AFTER `website_url`,
  ADD COLUMN IF NOT EXISTS `github_url`    VARCHAR(255)     DEFAULT NULL AFTER `linkedin_url`,
  ADD COLUMN IF NOT EXISTS `visibility`    ENUM('public','connections','private') NOT NULL DEFAULT 'public' AFTER `github_url`,
  ADD COLUMN IF NOT EXISTS `resume_html`   MEDIUMTEXT       DEFAULT NULL AFTER `visibility`,
  ADD COLUMN IF NOT EXISTS `deleted_at`    TIMESTAMP        NULL DEFAULT NULL AFTER `updated_at`;

ALTER TABLE `talent_profiles`
  ADD CONSTRAINT `fk_talent_profiles_user`
    FOREIGN KEY IF NOT EXISTS (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `talent_profiles`
  ADD KEY IF NOT EXISTS `idx_talent_profiles_user_id`   (`user_id`),
  ADD KEY IF NOT EXISTS `idx_talent_profiles_visibility` (`visibility`),
  ADD KEY IF NOT EXISTS `idx_talent_profiles_deleted_at` (`deleted_at`);

-- companies --------------------------------------------------------------
ALTER TABLE `companies`
  ADD COLUMN IF NOT EXISTS `owner_user_id`       BIGINT UNSIGNED  DEFAULT NULL AFTER `id`,
  ADD COLUMN IF NOT EXISTS `avatar_url`          VARCHAR(500)     DEFAULT NULL AFTER `website_url`,
  ADD COLUMN IF NOT EXISTS `banner_url`          VARCHAR(500)     DEFAULT NULL AFTER `avatar_url`,
  ADD COLUMN IF NOT EXISTS `tagline`             VARCHAR(255)     DEFAULT NULL AFTER `banner_url`,
  ADD COLUMN IF NOT EXISTS `employee_count_label` VARCHAR(60)     DEFAULT NULL AFTER `tagline`,
  ADD COLUMN IF NOT EXISTS `founded_year`        YEAR             DEFAULT NULL AFTER `employee_count_label`,
  ADD COLUMN IF NOT EXISTS `verified`            TINYINT(1)       NOT NULL DEFAULT 0 AFTER `founded_year`,
  ADD COLUMN IF NOT EXISTS `deleted_at`          TIMESTAMP        NULL DEFAULT NULL AFTER `updated_at`;

ALTER TABLE `companies`
  ADD CONSTRAINT `fk_companies_owner_user`
    FOREIGN KEY IF NOT EXISTS (`owner_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `companies`
  ADD KEY IF NOT EXISTS `idx_companies_owner_user_id` (`owner_user_id`),
  ADD KEY IF NOT EXISTS `idx_companies_verified`      (`verified`),
  ADD KEY IF NOT EXISTS `idx_companies_deleted_at`    (`deleted_at`);

-- opportunities ----------------------------------------------------------
ALTER TABLE `opportunities`
  ADD COLUMN IF NOT EXISTS `budget_min`      DECIMAL(12,2)    DEFAULT NULL AFTER `status`,
  ADD COLUMN IF NOT EXISTS `budget_max`      DECIMAL(12,2)    DEFAULT NULL AFTER `budget_min`,
  ADD COLUMN IF NOT EXISTS `deadline_at`     TIMESTAMP        NULL DEFAULT NULL AFTER `budget_max`,
  ADD COLUMN IF NOT EXISTS `remote_ok`       TINYINT(1)       NOT NULL DEFAULT 1 AFTER `deadline_at`,
  ADD COLUMN IF NOT EXISTS `location_label`  VARCHAR(160)     DEFAULT NULL AFTER `remote_ok`,
  ADD COLUMN IF NOT EXISTS `visibility`      ENUM('public','connections','private') NOT NULL DEFAULT 'public' AFTER `location_label`,
  ADD COLUMN IF NOT EXISTS `deleted_at`      TIMESTAMP        NULL DEFAULT NULL AFTER `updated_at`;

ALTER TABLE `opportunities`
  ADD KEY IF NOT EXISTS `idx_opportunities_status`      (`status`),
  ADD KEY IF NOT EXISTS `idx_opportunities_deadline_at` (`deadline_at`),
  ADD KEY IF NOT EXISTS `idx_opportunities_deleted_at`  (`deleted_at`),
  ADD KEY IF NOT EXISTS `idx_opportunities_visibility`  (`visibility`);

-- projects ---------------------------------------------------------------
ALTER TABLE `projects`
  ADD COLUMN IF NOT EXISTS `budget_label`    VARCHAR(120)     DEFAULT NULL AFTER `image_alt`,
  ADD COLUMN IF NOT EXISTS `timeline_label`  VARCHAR(120)     DEFAULT NULL AFTER `budget_label`,
  ADD COLUMN IF NOT EXISTS `visibility`      ENUM('public','connections','private') NOT NULL DEFAULT 'public' AFTER `timeline_label`,
  ADD COLUMN IF NOT EXISTS `deleted_at`      TIMESTAMP        NULL DEFAULT NULL AFTER `updated_at`;

ALTER TABLE `projects`
  ADD KEY IF NOT EXISTS `idx_projects_visibility` (`visibility`),
  ADD KEY IF NOT EXISTS `idx_projects_deleted_at` (`deleted_at`);


-- =============================================================================
-- SECTION 3: MEDIA UPLOADS
-- =============================================================================

CREATE TABLE IF NOT EXISTS `media_uploads` (
  `id`             BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `user_id`        BIGINT UNSIGNED  DEFAULT NULL,
  `filename`       VARCHAR(255)     NOT NULL,
  `original_name`  VARCHAR(255)     NOT NULL,
  `mime_type`      VARCHAR(120)     NOT NULL,
  `file_size`      INT UNSIGNED     NOT NULL COMMENT 'Size in bytes',
  `width`          SMALLINT UNSIGNED DEFAULT NULL,
  `height`         SMALLINT UNSIGNED DEFAULT NULL,
  `url`            VARCHAR(500)     NOT NULL,
  `context`        VARCHAR(60)      DEFAULT NULL COMMENT 'e.g. project_image, post_media, avatar',
  `created_at`     TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_media_uploads_user_id`  (`user_id`),
  KEY `idx_media_uploads_context`  (`context`),
  KEY `idx_media_uploads_created_at` (`created_at`),
  CONSTRAINT `fk_media_uploads_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- SECTION 4: SOCIAL GRAPH
-- =============================================================================

CREATE TABLE IF NOT EXISTS `follows` (
  `id`                  BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `follower_user_id`    BIGINT UNSIGNED  NOT NULL,
  `followed_user_id`    BIGINT UNSIGNED  DEFAULT NULL,
  `followed_company_id` BIGINT UNSIGNED  DEFAULT NULL,
  `created_at`          TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  -- A user can follow a given user only once
  UNIQUE KEY `uq_follows_user`    (`follower_user_id`, `followed_user_id`),
  -- A user can follow a given company only once
  UNIQUE KEY `uq_follows_company` (`follower_user_id`, `followed_company_id`),
  KEY `idx_follows_followed_user_id`    (`followed_user_id`),
  KEY `idx_follows_followed_company_id` (`followed_company_id`),
  CONSTRAINT `fk_follows_follower`
    FOREIGN KEY (`follower_user_id`)    REFERENCES `users`     (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_follows_followed_user`
    FOREIGN KEY (`followed_user_id`)    REFERENCES `users`     (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_follows_followed_company`
    FOREIGN KEY (`followed_company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- SECTION 5: FEED & POSTS
-- =============================================================================

CREATE TABLE IF NOT EXISTS `feed_posts` (
  `id`              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `author_user_id`  BIGINT UNSIGNED  NOT NULL,
  `company_id`      BIGINT UNSIGNED  DEFAULT NULL,
  `post_type`       ENUM('update','opportunity','project','event','collaboration',
                         'skill_share','news','celebration','achievement')
                    NOT NULL DEFAULT 'update',
  `body`            TEXT             DEFAULT NULL,
  `ref_id`          BIGINT           DEFAULT NULL COMMENT 'FK to entity referenced by post_type',
  `ref_type`        VARCHAR(40)      DEFAULT NULL COMMENT 'e.g. project, opportunity, event',
  `visibility`      ENUM('public','connections','private') NOT NULL DEFAULT 'public',
  `is_pinned`       TINYINT(1)       NOT NULL DEFAULT 0,
  `reaction_count`  INT UNSIGNED     NOT NULL DEFAULT 0,
  `comment_count`   INT UNSIGNED     NOT NULL DEFAULT 0,
  `view_count`      INT UNSIGNED     NOT NULL DEFAULT 0,
  `created_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`      TIMESTAMP        NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_feed_posts_author`     (`author_user_id`),
  KEY `idx_feed_posts_company`    (`company_id`),
  KEY `idx_feed_posts_type`       (`post_type`),
  KEY `idx_feed_posts_created_at` (`created_at`),
  KEY `idx_feed_posts_deleted_at` (`deleted_at`),
  KEY `idx_feed_posts_visibility` (`visibility`),
  CONSTRAINT `fk_feed_posts_author`
    FOREIGN KEY (`author_user_id`) REFERENCES `users`     (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_feed_posts_company`
    FOREIGN KEY (`company_id`)     REFERENCES `companies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `post_media` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_id`     BIGINT UNSIGNED NOT NULL,
  `media_id`    BIGINT UNSIGNED NOT NULL,
  `sort_order`  TINYINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_post_media_post_id`  (`post_id`),
  KEY `idx_post_media_media_id` (`media_id`),
  CONSTRAINT `fk_post_media_post`
    FOREIGN KEY (`post_id`)  REFERENCES `feed_posts`    (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_post_media_media`
    FOREIGN KEY (`media_id`) REFERENCES `media_uploads` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `post_reactions` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_id`    BIGINT UNSIGNED NOT NULL,
  `user_id`    BIGINT UNSIGNED NOT NULL,
  `reaction`   VARCHAR(20)     NOT NULL DEFAULT 'like',
  `created_at` TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_post_reactions_post_user` (`post_id`, `user_id`),
  KEY `idx_post_reactions_user_id` (`user_id`),
  CONSTRAINT `fk_post_reactions_post`
    FOREIGN KEY (`post_id`) REFERENCES `feed_posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_post_reactions_user`
    FOREIGN KEY (`user_id`) REFERENCES `users`      (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `post_comments` (
  `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_id`           BIGINT UNSIGNED NOT NULL,
  `user_id`           BIGINT UNSIGNED NOT NULL,
  `parent_comment_id` BIGINT UNSIGNED DEFAULT NULL,
  `body`              TEXT            NOT NULL,
  `reaction_count`    INT UNSIGNED    NOT NULL DEFAULT 0,
  `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`        TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_post_comments_post_id`    (`post_id`),
  KEY `idx_post_comments_user_id`    (`user_id`),
  KEY `idx_post_comments_parent_id`  (`parent_comment_id`),
  KEY `idx_post_comments_deleted_at` (`deleted_at`),
  CONSTRAINT `fk_post_comments_post`
    FOREIGN KEY (`post_id`)           REFERENCES `feed_posts`    (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_post_comments_user`
    FOREIGN KEY (`user_id`)           REFERENCES `users`         (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_post_comments_parent`
    FOREIGN KEY (`parent_comment_id`) REFERENCES `post_comments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- SECTION 6: MESSAGING
-- =============================================================================

CREATE TABLE IF NOT EXISTS `conversations` (
  `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type`                ENUM('direct','group') NOT NULL DEFAULT 'direct',
  `title`               VARCHAR(255)    DEFAULT NULL,
  `last_message_at`     TIMESTAMP       NULL DEFAULT NULL,
  `created_by_user_id`  BIGINT UNSIGNED NOT NULL,
  `created_at`          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at`          TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_conversations_created_by`    (`created_by_user_id`),
  KEY `idx_conversations_last_message`  (`last_message_at`),
  KEY `idx_conversations_deleted_at`    (`deleted_at`),
  CONSTRAINT `fk_conversations_creator`
    FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `conversation_participants` (
  `id`                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `conversation_id`       BIGINT UNSIGNED NOT NULL,
  `user_id`               BIGINT UNSIGNED NOT NULL,
  `role`                  ENUM('member','admin') NOT NULL DEFAULT 'member',
  `last_read_message_id`  BIGINT UNSIGNED DEFAULT NULL,
  `joined_at`             TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `muted_until`           TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_conv_participants` (`conversation_id`, `user_id`),
  KEY `idx_conv_participants_user_id` (`user_id`),
  CONSTRAINT `fk_conv_participants_conversation`
    FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_conv_participants_user`
    FOREIGN KEY (`user_id`)         REFERENCES `users`         (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `messages` (
  `id`                   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `conversation_id`      BIGINT UNSIGNED NOT NULL,
  `sender_user_id`       BIGINT UNSIGNED NOT NULL,
  `body`                 TEXT            DEFAULT NULL,
  `message_type`         ENUM('text','image','file','system') NOT NULL DEFAULT 'text',
  `media_id`             BIGINT UNSIGNED DEFAULT NULL,
  `reply_to_message_id`  BIGINT UNSIGNED DEFAULT NULL,
  `read_count`           SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`           TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `edited_at`            TIMESTAMP       NULL DEFAULT NULL,
  `deleted_at`           TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_messages_conversation_created` (`conversation_id`, `created_at`),
  KEY `idx_messages_sender`               (`sender_user_id`),
  KEY `idx_messages_deleted_at`           (`deleted_at`),
  CONSTRAINT `fk_messages_conversation`
    FOREIGN KEY (`conversation_id`)     REFERENCES `conversations`  (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_messages_sender`
    FOREIGN KEY (`sender_user_id`)      REFERENCES `users`          (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_messages_media`
    FOREIGN KEY (`media_id`)            REFERENCES `media_uploads`  (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_messages_reply_to`
    FOREIGN KEY (`reply_to_message_id`) REFERENCES `messages`       (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `message_reads` (
  `message_id`  BIGINT UNSIGNED NOT NULL,
  `user_id`     BIGINT UNSIGNED NOT NULL,
  `read_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`, `user_id`),
  KEY `idx_message_reads_user_id` (`user_id`),
  CONSTRAINT `fk_message_reads_message`
    FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_message_reads_user`
    FOREIGN KEY (`user_id`)    REFERENCES `users`    (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- SECTION 7: BIDDING & CONTRACTS
-- =============================================================================

CREATE TABLE IF NOT EXISTS `bids` (
  `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `bidder_user_id`    BIGINT UNSIGNED NOT NULL,
  `target_type`       ENUM('project','opportunity') NOT NULL,
  `target_id`         BIGINT UNSIGNED NOT NULL,
  `proposed_rate`     DECIMAL(12,2)   DEFAULT NULL,
  `rate_unit`         ENUM('fixed','hourly','daily','monthly') NOT NULL DEFAULT 'fixed',
  `proposed_timeline` VARCHAR(160)    DEFAULT NULL,
  `cover_note`        TEXT            DEFAULT NULL,
  `status`            ENUM('pending','shortlisted','accepted','declined','withdrawn')
                      NOT NULL DEFAULT 'pending',
  `viewed_at`         TIMESTAMP       NULL DEFAULT NULL,
  `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  -- One bid per user per target entity
  UNIQUE KEY `uq_bids_bidder_target` (`bidder_user_id`, `target_type`, `target_id`),
  KEY `idx_bids_target`      (`target_type`, `target_id`),
  KEY `idx_bids_bidder`      (`bidder_user_id`),
  KEY `idx_bids_status`      (`status`),
  KEY `idx_bids_created_at`  (`created_at`),
  CONSTRAINT `fk_bids_bidder`
    FOREIGN KEY (`bidder_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `contracts` (
  `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `bid_id`              BIGINT UNSIGNED DEFAULT NULL,
  `project_id`          BIGINT UNSIGNED DEFAULT NULL,
  `opportunity_id`      BIGINT UNSIGNED DEFAULT NULL,
  `client_user_id`      BIGINT UNSIGNED NOT NULL,
  `contractor_user_id`  BIGINT UNSIGNED NOT NULL,
  `title`               VARCHAR(255)    NOT NULL,
  `scope_summary`       TEXT            DEFAULT NULL,
  `total_value`         DECIMAL(12,2)   DEFAULT NULL,
  `currency_code`       CHAR(3)         NOT NULL DEFAULT 'USD',
  `start_date`          DATE            DEFAULT NULL,
  `end_date`            DATE            DEFAULT NULL,
  `status`              ENUM('draft','sent','signed','active','completed','cancelled','disputed')
                        NOT NULL DEFAULT 'draft',
  `signed_at`           TIMESTAMP       NULL DEFAULT NULL,
  `created_at`          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_contracts_bid_id`             (`bid_id`),
  KEY `idx_contracts_project_id`         (`project_id`),
  KEY `idx_contracts_opportunity_id`     (`opportunity_id`),
  KEY `idx_contracts_client_user_id`     (`client_user_id`),
  KEY `idx_contracts_contractor_user_id` (`contractor_user_id`),
  KEY `idx_contracts_status`             (`status`),
  CONSTRAINT `fk_contracts_bid`
    FOREIGN KEY (`bid_id`)             REFERENCES `bids`          (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_contracts_project`
    FOREIGN KEY (`project_id`)         REFERENCES `projects`      (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_contracts_opportunity`
    FOREIGN KEY (`opportunity_id`)     REFERENCES `opportunities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_contracts_client`
    FOREIGN KEY (`client_user_id`)     REFERENCES `users`         (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_contracts_contractor`
    FOREIGN KEY (`contractor_user_id`) REFERENCES `users`         (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `contract_milestones` (
  `id`            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `contract_id`   BIGINT UNSIGNED  NOT NULL,
  `title`         VARCHAR(255)     NOT NULL,
  `description`   TEXT             DEFAULT NULL,
  `amount`        DECIMAL(12,2)    DEFAULT NULL,
  `due_date`      DATE             DEFAULT NULL,
  `status`        ENUM('pending','submitted','approved','paid') NOT NULL DEFAULT 'pending',
  `sort_order`    TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `submitted_at`  TIMESTAMP        NULL DEFAULT NULL,
  `approved_at`   TIMESTAMP        NULL DEFAULT NULL,
  `paid_at`       TIMESTAMP        NULL DEFAULT NULL,
  `created_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_contract_milestones_contract_id` (`contract_id`),
  KEY `idx_contract_milestones_status`      (`status`),
  CONSTRAINT `fk_contract_milestones_contract`
    FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- SECTION 8: EVENTS
-- =============================================================================

CREATE TABLE IF NOT EXISTS `events` (
  `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organizer_user_id` BIGINT UNSIGNED NOT NULL,
  `company_id`        BIGINT UNSIGNED DEFAULT NULL,
  `title`             VARCHAR(255)    NOT NULL,
  `slug`              VARCHAR(220)    NOT NULL,
  `description`       TEXT            DEFAULT NULL,
  `event_type`        ENUM('meetup','workshop','launch','webinar','conference','social')
                      NOT NULL DEFAULT 'meetup',
  `format`            ENUM('online','in_person','hybrid') NOT NULL DEFAULT 'online',
  `location_label`    VARCHAR(255)    DEFAULT NULL,
  `meeting_url`       VARCHAR(500)    DEFAULT NULL,
  `starts_at`         TIMESTAMP       NOT NULL,
  `ends_at`           TIMESTAMP       NULL DEFAULT NULL,
  `capacity`          SMALLINT UNSIGNED DEFAULT NULL,
  `banner_media_id`   BIGINT UNSIGNED DEFAULT NULL,
  `status`            ENUM('draft','published','cancelled') NOT NULL DEFAULT 'draft',
  `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at`        TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_events_slug` (`slug`),
  KEY `idx_events_organizer_user_id` (`organizer_user_id`),
  KEY `idx_events_company_id`        (`company_id`),
  KEY `idx_events_starts_at`         (`starts_at`),
  KEY `idx_events_status`            (`status`),
  KEY `idx_events_deleted_at`        (`deleted_at`),
  CONSTRAINT `fk_events_organizer`
    FOREIGN KEY (`organizer_user_id`) REFERENCES `users`          (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_events_company`
    FOREIGN KEY (`company_id`)        REFERENCES `companies`      (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_events_banner_media`
    FOREIGN KEY (`banner_media_id`)   REFERENCES `media_uploads`  (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `event_attendees` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id`      BIGINT UNSIGNED NOT NULL,
  `user_id`       BIGINT UNSIGNED NOT NULL,
  `rsvp_status`   ENUM('going','maybe','not_going') NOT NULL DEFAULT 'going',
  `checked_in_at` TIMESTAMP       NULL DEFAULT NULL,
  `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_event_attendees` (`event_id`, `user_id`),
  KEY `idx_event_attendees_user_id`     (`user_id`),
  KEY `idx_event_attendees_rsvp_status` (`rsvp_status`),
  CONSTRAINT `fk_event_attendees_event`
    FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_event_attendees_user`
    FOREIGN KEY (`user_id`)  REFERENCES `users`  (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- SECTION 9: NOTIFICATIONS
-- =============================================================================

CREATE TABLE IF NOT EXISTS `notifications` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`         BIGINT UNSIGNED NOT NULL,
  `type`            VARCHAR(60)     NOT NULL
                    COMMENT 'new_message | bid_received | bid_accepted | bid_declined | post_reaction | post_comment | application_update | follow | event_reminder | contract_update | milestone_approved',
  `actor_user_id`   BIGINT UNSIGNED DEFAULT NULL,
  `ref_type`        VARCHAR(40)     NOT NULL COMMENT 'e.g. message, bid, post, event, contract',
  `ref_id`          BIGINT UNSIGNED NOT NULL,
  `title`           VARCHAR(255)    NOT NULL,
  `body`            TEXT            DEFAULT NULL,
  `is_read`         TINYINT(1)      NOT NULL DEFAULT 0,
  `email_sent`      TINYINT(1)      NOT NULL DEFAULT 0,
  `push_sent`       TINYINT(1)      NOT NULL DEFAULT 0,
  `created_at`      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read_at`         TIMESTAMP       NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_notifications_user_unread`   (`user_id`, `is_read`),
  KEY `idx_notifications_created_at`    (`created_at`),
  KEY `idx_notifications_email_queue`   (`email_sent`, `created_at`),
  KEY `idx_notifications_actor_user_id` (`actor_user_id`),
  CONSTRAINT `fk_notifications_user`
    FOREIGN KEY (`user_id`)       REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notifications_actor`
    FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================================
-- SECTION 10: V1 INDEX ADDITIONS
-- =============================================================================

-- skills.category
ALTER TABLE `skills`
  ADD KEY IF NOT EXISTS `idx_skills_category` (`category`);

-- project_highlights.project_id
ALTER TABLE `project_highlights`
  ADD KEY IF NOT EXISTS `idx_project_highlights_project_id` (`project_id`);

-- project_assignments
ALTER TABLE `project_assignments`
  ADD KEY IF NOT EXISTS `idx_project_assignments_project_id` (`project_id`),
  ADD KEY IF NOT EXISTS `idx_project_assignments_talent_id`  (`talent_id`),
  ADD KEY IF NOT EXISTS `idx_project_assignments_status`     (`status`);

-- opportunity_applications
ALTER TABLE `opportunity_applications`
  ADD KEY IF NOT EXISTS `idx_opportunity_applications_opportunity_id` (`opportunity_id`),
  ADD KEY IF NOT EXISTS `idx_opportunity_applications_talent_id`      (`talent_id`),
  ADD KEY IF NOT EXISTS `idx_opportunity_applications_status`         (`status`);

-- =============================================================================

SET FOREIGN_KEY_CHECKS = 1;
