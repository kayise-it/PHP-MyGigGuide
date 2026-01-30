-- Create venue_owners table
CREATE TABLE IF NOT EXISTS `venue_owners` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `venue_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role` enum('primary','co_owner','manager') NOT NULL DEFAULT 'co_owner',
  `added_by_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `added_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `venue_owners_venue_id_user_id_unique` (`venue_id`,`user_id`),
  KEY `venue_owners_venue_id_index` (`venue_id`),
  KEY `venue_owners_user_id_index` (`user_id`),
  KEY `venue_owners_added_by_user_id_foreign` (`added_by_user_id`),
  CONSTRAINT `venue_owners_venue_id_foreign` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`) ON DELETE CASCADE,
  CONSTRAINT `venue_owners_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `venue_owners_added_by_user_id_foreign` FOREIGN KEY (`added_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create venue_owner_requests table
CREATE TABLE IF NOT EXISTS `venue_owner_requests` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `venue_id` bigint(20) UNSIGNED NOT NULL,
  `requester_user_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `reason` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `proof_document_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_by_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_venue_requester` (`venue_id`,`requester_user_id`),
  KEY `venue_owner_requests_venue_id_index` (`venue_id`),
  KEY `venue_owner_requests_requester_user_id_index` (`requester_user_id`),
  KEY `venue_owner_requests_status_index` (`status`),
  KEY `venue_owner_requests_reviewed_by_user_id_foreign` (`reviewed_by_user_id`),
  CONSTRAINT `venue_owner_requests_venue_id_foreign` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`) ON DELETE CASCADE,
  CONSTRAINT `venue_owner_requests_requester_user_id_foreign` FOREIGN KEY (`requester_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `venue_owner_requests_reviewed_by_user_id_foreign` FOREIGN KEY (`reviewed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

