INSERT IGNORE INTO `configurations` (`id`, `id_name`, `group_name`, `preferences`, `title`, `value`, `description`, `created_at`, `updated_at`) VALUES
-- INSERT IGNORE INTO `login_screen_image` (`id`, `preferences`, `image`, `created_at`, `updated_at`) VALUES
-- INSERT IGNORE INTO `payment_gateway` (`id`, `title`, `key`, `secret`, `webhook_secret_key`, `is_active`, `created_at`, `updated_at`) VALUES
-- INSERT IGNORE INTO `permission` (`id`, `group_id`, `name`, `created_at`, `updated_at`) VALUES
-- INSERT IGNORE INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
-- INSERT IGNORE INTO `role_permission` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES
-- INSERT IGNORE INTO `social_media` (`id`, `title`, `image`, `url`, `created_at`, `updated_at`) VALUES
-- INSERT IGNORE INTO `user_notification` (`id`, `appointment_id`, `txn_id`, `prescription_id`, `file_id`, `user_id`, `title`, `body`, `image`, `type`, `created_at`, `updated_at`) VALUES
INSERT IGNORE INTO `user_notification` (`id`, `appointment_id`, `txn_id`, `prescription_id`, `file_id`, `user_id`, `title`, `body`, `image`, `type`, `created_at`, `updated_at`) VALUES
INSERT IGNORE INTO `web_pages` (`id`, `page_id`, `title`, `body`, `created_at`, `updated_at`) VALUES
