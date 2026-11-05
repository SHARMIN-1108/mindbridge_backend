-- =====================================================================
-- MindBridge Database Schema & Initial Real-time Seed Data
-- Database: mindbridge
-- Compatible with MySQL 5.7+ / 8.0+ / MariaDB
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `mindbridge` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `mindbridge`;

-- 1. USERS TABLE
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `preferred_name` VARCHAR(100) DEFAULT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('patient', 'caregiver') DEFAULT 'patient',
    `phone_number` VARCHAR(50) DEFAULT NULL,
    `profile_picture` VARCHAR(255) DEFAULT NULL,
    `api_token_hash` VARCHAR(64) DEFAULT NULL,
    `reset_token_hash` VARCHAR(64) DEFAULT NULL,
    `reset_token_expires` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_email` (`email`),
    INDEX `idx_users_token` (`api_token_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. DAILY SCHEDULES TABLE (Morning, Afternoon, Evening routines)
CREATE TABLE IF NOT EXISTS `daily_schedules` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `time` VARCHAR(50) NOT NULL,
    `period` ENUM('MORNING', 'AFTERNOON', 'EVENING') NOT NULL,
    `subtitle` VARCHAR(255) DEFAULT NULL,
    `is_completed` TINYINT(1) NOT NULL DEFAULT 0,
    `schedule_date` DATE NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_schedules_user_date` (`user_id`, `schedule_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. REMINDERS TABLE
CREATE TABLE IF NOT EXISTS `reminders` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `reminder_time` VARCHAR(50) NOT NULL,
    `reminder_date` VARCHAR(50) NOT NULL,
    `subtitle` VARCHAR(255) DEFAULT NULL,
    `status` VARCHAR(50) DEFAULT 'To do',
    `is_completed` TINYINT(1) NOT NULL DEFAULT 0,
    `is_today` TINYINT(1) NOT NULL DEFAULT 1,
    `category` VARCHAR(50) DEFAULT 'Routine',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_reminders_user` (`user_id`, `is_today`, `is_completed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. MEMORIES TABLE (Memory Journey & Details)
CREATE TABLE IF NOT EXISTS `memories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `memory_date` VARCHAR(100) NOT NULL,
    `subtitle` VARCHAR(255) DEFAULT NULL,
    `description` TEXT NOT NULL,
    `associated_people` VARCHAR(255) DEFAULT NULL,
    `media_status` VARCHAR(100) DEFAULT 'Photo and voice note attached',
    `voice_note_title` VARCHAR(150) DEFAULT NULL,
    `voice_note_subtitle` VARCHAR(100) DEFAULT '0:24 • Playback example',
    `voice_duration` VARCHAR(50) DEFAULT '0:24',
    `image_url` VARCHAR(255) DEFAULT NULL,
    `audio_url` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_memories_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. ALBUM PHOTOS TABLE
CREATE TABLE IF NOT EXISTS `album_photos` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `date_taken` VARCHAR(100) NOT NULL,
    `category` ENUM('MY_FAMILY', 'SPECIAL_DAYS', 'PLACES_I_LOVE') NOT NULL DEFAULT 'MY_FAMILY',
    `emoji_thumb` VARCHAR(50) DEFAULT '👥',
    `photo_url` VARCHAR(255) DEFAULT NULL,
    `is_selected` TINYINT(1) NOT NULL DEFAULT 0,
    `person_name` VARCHAR(100) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_album_user_category` (`user_id`, `category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. VOICE MEMORIES TABLE
CREATE TABLE IF NOT EXISTS `voice_memories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `author` VARCHAR(100) NOT NULL,
    `record_date` VARCHAR(100) NOT NULL,
    `duration` VARCHAR(50) NOT NULL DEFAULT '0:24',
    `audio_url` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. BRAIN GAMES TABLE
CREATE TABLE IF NOT EXISTS `games` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `description` TEXT NOT NULL,
    `thumbnail_emoji` VARCHAR(50) NOT NULL DEFAULT '🧩',
    `difficulty` ENUM('EASY', 'MEDIUM') NOT NULL DEFAULT 'EASY',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. GAME SESSIONS / SCORES
CREATE TABLE IF NOT EXISTS `game_sessions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `game_id` INT UNSIGNED NOT NULL,
    `score` INT NOT NULL DEFAULT 100,
    `duration_seconds` INT NOT NULL DEFAULT 60,
    `played_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`game_id`) REFERENCES `games`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. ACTIVITY PROGRESS METRICS TABLE
CREATE TABLE IF NOT EXISTS `activity_progress` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `date_range` VARCHAR(100) NOT NULL DEFAULT '5-11 October 2026',
    `banner_message` VARCHAR(255) NOT NULL DEFAULT 'You spent time on activities you enjoy this week.',
    `metric_title` VARCHAR(150) NOT NULL,
    `value_text` VARCHAR(50) NOT NULL,
    `subtitle` VARCHAR(255) NOT NULL,
    `progress` FLOAT NOT NULL DEFAULT 0.5,
    `icon_emoji` VARCHAR(50) NOT NULL DEFAULT '🌟',
    `footer_text` VARCHAR(255) DEFAULT 'Every moment counts. Enjoy activities at your own pace.',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. SAFE & FAMILIAR PLACES TABLE
CREATE TABLE IF NOT EXISTS `safe_places` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `address` VARCHAR(255) DEFAULT NULL,
    `icon_emoji` VARCHAR(50) NOT NULL DEFAULT '🏡',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. EMERGENCY SUPPORT & CONTACTS TABLE
CREATE TABLE IF NOT EXISTS `emergency_contacts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `relationship` VARCHAR(100) NOT NULL,
    `phone_number` VARCHAR(50) NOT NULL,
    `notes` VARCHAR(255) DEFAULT NULL,
    `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. CAREGIVER OVERVIEW & LINK TABLE
CREATE TABLE IF NOT EXISTS `caregiver_overview` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `member_name` VARCHAR(100) NOT NULL,
    `member_role` VARCHAR(100) NOT NULL DEFAULT 'Family member · Change',
    `sharing_status` VARCHAR(255) DEFAULT 'All sharing options active',
    `recent_updates` TEXT,
    `footer_text` VARCHAR(255) DEFAULT 'Example summary of app activity only. Review Margaret\'s sharing choices together.',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. AI CONVERSATIONS & CHAT HISTORY
CREATE TABLE IF NOT EXISTS `ai_conversations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `date_time` VARCHAR(100) NOT NULL,
    `snippet` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ai_messages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `conversation_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `sender` ENUM('user', 'assistant') NOT NULL,
    `content` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`conversation_id`) REFERENCES `ai_conversations`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. DAILY CHECK-IN (MOOD & NOTES) TABLE
CREATE TABLE IF NOT EXISTS `daily_checkins` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `mood_id` VARCHAR(50) NOT NULL,
    `mood_name` VARCHAR(50) NOT NULL,
    `mood_icon` VARCHAR(50) NOT NULL,
    `note` TEXT,
    `checkin_date` DATE NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_checkins_user_date` (`user_id`, `checkin_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. FAMILY UPDATES FEED TABLE
CREATE TABLE IF NOT EXISTS `family_updates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `update_type` ENUM('photo', 'audio', 'call') NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `sender_and_date` VARCHAR(150) NOT NULL,
    `details_or_message` TEXT NOT NULL,
    `icon` VARCHAR(50) DEFAULT '📷',
    `media_url` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. HELPFUL & MEDICAL INFORMATION TABLE
CREATE TABLE IF NOT EXISTS `helpful_information` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL UNIQUE,
    `please_call_me` VARCHAR(100) NOT NULL DEFAULT 'Margaret',
    `caregiver_contact` VARCHAR(150) NOT NULL DEFAULT 'Emma Wilson · Daughter',
    `phone_number` VARCHAR(50) NOT NULL DEFAULT '+1 (202) 555-0148',
    `communication_style` TEXT NOT NULL,
    `important_notes` TEXT NOT NULL,
    `notice_text` VARCHAR(255) DEFAULT 'Sharing is controlled by you or an authorized caregiver. Review the information before choosing to share it.',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. IMPORTANT PEOPLE TABLE
CREATE TABLE IF NOT EXISTS `important_people` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `relationship` VARCHAR(100) NOT NULL,
    `notes` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. USER PREFERENCES TABLE
CREATE TABLE IF NOT EXISTS `user_preferences` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `subtitle` VARCHAR(255) NOT NULL,
    `icon` VARCHAR(50) NOT NULL DEFAULT '🌟',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. APP SETTINGS TABLE
CREATE TABLE IF NOT EXISTS `app_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `setting_key` VARCHAR(50) NOT NULL,
    `title` VARCHAR(100) NOT NULL,
    `subtitle` VARCHAR(200) NOT NULL,
    `icon` VARCHAR(50) NOT NULL,
    `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `value` VARCHAR(100) DEFAULT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uniq_user_setting` (`user_id`, `setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- SEED DATA (Populating Realistic Initial Realtime Data)
-- Default password: password123 (hashed with PHP password_hash PASSWORD_DEFAULT)
-- =====================================================================

-- Default user: Margaret Wilson
INSERT INTO `users` (`id`, `name`, `preferred_name`, `email`, `password`, `role`, `phone_number`, `profile_picture`)
VALUES (
    1,
    'Margaret Wilson',
    'Margaret',
    'margaret.wilson@email.com',
    '$2y$10$wE9mHlV7q2x14P2eGZ8pNuR9xXo7/5cK5j0bZfK1I9bB8M7gK8b7u', -- bcrypt hash of 'password123'
    'patient',
    '+1 (202) 555-0148',
    'uploads/profiles/default_user.png'
) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- Caregiver user: Emma Wilson
INSERT INTO `users` (`id`, `name`, `preferred_name`, `email`, `password`, `role`, `phone_number`)
VALUES (
    2,
    'Emma Wilson',
    'Emma',
    'emma.wilson@email.com',
    '$2y$10$wE9mHlV7q2x14P2eGZ8pNuR9xXo7/5cK5j0bZfK1I9bB8M7gK8b7u',
    'caregiver',
    '+1 (202) 555-0148'
) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`);

-- Seed Daily Schedule for Margaret (User 1)
INSERT INTO `daily_schedules` (`user_id`, `title`, `time`, `period`, `subtitle`, `is_completed`, `schedule_date`) VALUES
(1, 'Breakfast', '8:00 AM', 'MORNING', 'Done', 1, CURDATE()),
(1, 'Morning Walk', '10:00 AM', 'MORNING', NULL, 0, CURDATE()),
(1, 'Lunch', '12:30 PM', 'AFTERNOON', NULL, 0, CURDATE()),
(1, 'Family Call', '4:00 PM', 'AFTERNOON', 'with Emma', 0, CURDATE()),
(1, 'Bedtime', '9:00 PM', 'EVENING', NULL, 0, CURDATE());

-- Seed Reminders for Margaret (User 1)
INSERT INTO `reminders` (`user_id`, `title`, `reminder_time`, `reminder_date`, `subtitle`, `status`, `is_completed`, `is_today`, `category`) VALUES
(1, 'Breakfast', '8:00 AM', 'Today', '8:00 AM · Completed', 'Completed', 1, 1, 'Meals'),
(1, 'Morning Walk', '10:00 AM', 'Today', '10:00 AM - To do', 'To do', 0, 1, 'Wellness'),
(1, 'Lunch', '12:30 PM', 'Today', '12:30 PM - To do', 'To do', 0, 1, 'Meals'),
(1, 'Call with Emma', '4:00 PM', 'Today', '4:00 PM - To do', 'To do', 0, 1, 'Family'),
(1, 'Community appointment', '11:00 AM', 'Wed, 14 Oct', 'Wed, 14 Oct - 11:00 AM - To do', 'To do', 0, 0, 'Medical'),
(1, 'Water the garden', '9:30 AM', 'Thu, 15 Oct', 'Thu, 15 Oct - 9:30 AM - To do', 'To do', 0, 0, 'Home');

-- Seed Memories for Margaret (User 1)
INSERT INTO `memories` (`user_id`, `title`, `memory_date`, `subtitle`, `description`, `associated_people`, `voice_note_title`, `voice_note_subtitle`, `voice_duration`) VALUES
(1, 'Family Picnic', '18 May 2006', 'Emma, Lily and me in the garden.', 'We shared tea and sandwiches in the sunshine. Lily helped choose flowers for the table.', 'Margaret, Emma and Lily', 'Emma\'s voice note', '0:24 · Playback example', '0:24'),
(1, 'My Birthday', '14 June 2020', 'Seventy-two candles, so much love.', 'Surrounded by family, delicious strawberry cake and lots of warm laughter.', 'Emma, David, Lily', 'Birthday laughter', '0:35 · Playback example', '0:35'),
(1, 'A Day by the Sea', '23 August 2020', 'A sunny afternoon with David.', 'Walking near the pier, breathing fresh salty sea air and watching seagulls.', 'David', 'Waves & David\'s voice', '0:42 · Playback example', '0:42');

-- Seed Album Photos
INSERT INTO `album_photos` (`user_id`, `title`, `date_taken`, `category`, `emoji_thumb`, `is_selected`, `person_name`) VALUES
(1, 'Margaret & Lily', '18 May 2006', 'MY_FAMILY', '👩‍👧', 1, 'Lily'),
(1, 'Emma', '18 May 2006', 'MY_FAMILY', '👩', 0, 'Emma'),
(1, 'David & Margaret', '23 Aug 2006', 'MY_FAMILY', '👫', 0, 'David'),
(1, 'Lily & Emma', '14 June 2006', 'MY_FAMILY', '👩‍👧', 0, 'Lily'),
(1, 'Garden Tea Party', '10 June 2021', 'SPECIAL_DAYS', '🎂', 0, 'Margaret'),
(1, 'Willow Lane Sunset', '05 Sept 2023', 'PLACES_I_LOVE', '🌅', 0, 'Margaret');

-- Seed Voice Memories
INSERT INTO `voice_memories` (`user_id`, `title`, `author`, `record_date`, `duration`) VALUES
(1, 'Family Picnic', 'Emma', '18 May 2006', '0:24'),
(1, 'A Day by the Sea', 'David', '23 August 2006', '0:42');

-- Seed Brain Games
INSERT INTO `games` (`id`, `title`, `description`, `thumbnail_emoji`, `difficulty`) VALUES
(1, 'Match the Pictures', 'Find two pictures that belong together.', '🖼️', 'EASY'),
(2, 'Remember the Objects', 'Look at familiar things, then recall them.', '🔍', 'EASY'),
(3, 'Complete the Pattern', 'Choose the shape that comes next.', '✨', 'MEDIUM')
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`);

-- Seed Activity Progress Metrics for Margaret
INSERT INTO `activity_progress` (`user_id`, `metric_title`, `value_text`, `subtitle`, `progress`, `icon_emoji`) VALUES
(1, 'Completed activities', '12', 'Walks, music and garden time', 0.8, '🌿'),
(1, 'Games played', '4', 'A few gentle brain games', 0.4, '🧩'),
(1, 'Memories added', '2', 'Stories to enjoy again', 0.25, '🖼️'),
(1, 'Routines followed', '5 days', 'Time for familiar daily moments', 0.6, '⏰');

-- Seed Safe Places for Margaret
INSERT INTO `safe_places` (`user_id`, `title`, `description`, `address`, `icon_emoji`) VALUES
(1, 'Home', 'Your home on Willow Lane', '12 Willow Lane', '🏡'),
(1, 'Family Home', 'Emma\'s home - Oak Street', '45 Oak Street', '🏠'),
(1, 'Local Park', 'Riverside Park - garden entrance', 'Riverside Park Main Rd', '🌳');

-- Seed Emergency Support Contacts
INSERT INTO `emergency_contacts` (`user_id`, `name`, `relationship`, `phone_number`, `notes`, `is_primary`) VALUES
(1, 'Emma Wilson - Daughter', 'Daughter', '+1 (202) 555-0148', 'Available day and night', 1),
(1, 'Dr. Roberts - Family Physician', 'Doctor', '+1 (202) 555-0199', 'Clinic hours 9 AM - 5 PM', 0);

-- Seed Caregiver Overview
INSERT INTO `caregiver_overview` (`user_id`, `member_name`, `member_role`, `sharing_status`, `recent_updates`) VALUES
(1, 'Margaret Wilson', 'Family member · Change', 'All sharing options active', 'Completed: Breakfast, Morning walk\nUpcoming: 4:00 PM Call with Emma\nNew: Family Picnic voice note added by Emma · 12 October');

-- Seed AI Conversations History
INSERT INTO `ai_conversations` (`user_id`, `title`, `date_time`, `snippet`) VALUES
(1, 'A lovely garden morning', '13 October · 9:20 AM', 'You told me about planting flowers with Emma.'),
(1, 'Songs we enjoy', '12 October · 3:10 PM', 'We talked about familiar songs and favorite music.'),
(1, 'Remembering the seaside', '11 October · 2:00 PM', 'A sunny afternoon with David by the sea.');

-- Seed Daily Check-in (Today)
INSERT INTO `daily_checkins` (`user_id`, `mood_id`, `mood_name`, `mood_icon`, `note`, `checkin_date`) VALUES
(1, '2', 'Calm', '😌', 'I enjoyed my tea in the garden.', CURDATE());

-- Seed Family Updates
INSERT INTO `family_updates` (`user_id`, `update_type`, `title`, `sender_and_date`, `details_or_message`, `icon`) VALUES
(1, 'photo', 'A photo from our picnic', 'Emma · 12 October 2026', '\"A lovely moment with you and Lily in the garden.\"', '📷'),
(1, 'audio', 'A saved voice memory', 'David · 11 Oct · Our seaside afternoon', '\"Listening to the waves and laughing together.\"', '🎙️'),
(1, 'call', 'Our next family call', 'Emma · 13 Oct · Today at 4:00 PM', 'Scheduled video call with Emma & Lily', '📞');

-- Seed Helpful Information
INSERT INTO `helpful_information` (`user_id`, `please_call_me`, `caregiver_contact`, `phone_number`, `communication_style`, `important_notes`) VALUES
(1, 'Margaret', 'Emma Wilson · Daughter', '+1 (202) 555-0148', 'Speak clearly and give me time to reply.', 'I enjoy a quiet space and familiar names.');

-- Seed Important People
INSERT INTO `important_people` (`user_id`, `name`, `relationship`, `notes`) VALUES
(1, 'Emma Wilson', 'Daughter', 'Our afternoon calls'),
(1, 'Oliver Wilson', 'Grandson', 'Loves the garden'),
(1, 'Rose Bennett', 'Close friend', 'Tea on Thursdays'),
(1, 'Sarah Miller', 'Caregiver', 'Helps with routines');

-- Seed User Preferences
INSERT INTO `user_preferences` (`user_id`, `title`, `subtitle`, `icon`) VALUES
(1, 'Favorite activities', 'Walking · Gentle brain games', '🚶'),
(1, 'Music', 'Familiar songs · Jazz', '🎵'),
(1, 'Foods', 'Tea · Apple pie', '☕'),
(1, 'Hobbies', 'Gardening · Reading', '🌱'),
(1, 'Familiar places', 'Home · Riverside Park', '🏡'),
(1, 'Conversation topics', 'Family · Garden · Seaside', '💬');

-- Seed App Settings
INSERT INTO `app_settings` (`user_id`, `setting_key`, `title`, `subtitle`, `icon`, `is_enabled`, `value`) VALUES
(1, 'text_size', 'Text Size', 'Large', 'T', 1, 'large'),
(1, 'sound_voice', 'Sound and Voice', 'Voice on · Gentle volume', '🔊', 1, 'gentle'),
(1, 'notifications', 'Notifications', 'Reminders and family updates on', '🔔', 1, 'enabled'),
(1, 'privacy', 'Privacy', 'Review example sharing choices', '🛡️', 1, 'family_only'),
(1, 'language', 'Language', 'English', '🌐', 1, 'en'),
(1, 'help', 'Help', 'Simple guides and support', '❓', 1, 'available');
