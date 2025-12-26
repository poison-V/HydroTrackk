<?php
/**
 * HydroTrack · Database Configuration
 *
 * NOTE:
 *  - Palitan ang values sa ibaba ayon sa database mo sa XAMPP/phpMyAdmin.
 *  - Siguraduhing nakagawa ka ng database at `users` table bago mag-signup/login.
 */

$DB_HOST = 'localhost';    // karaniwan: localhost
$DB_NAME = 'hydrotrack_db'; // PALITAN: pangalan ng database mo
$DB_USER = 'root';         // karaniwan: root
$DB_PASS = '';             // karaniwan: empty password sa XAMPP

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (Exception $e) {
    // Simple but clear error kapag hindi pa tama ang config o DB
    die('Database connection failed. Please check config.php settings and make sure the database exists.');
}

/**
 * EXPECTED users TABLE STRUCTURE (sample):
 *
 * CREATE TABLE `users` (
 *   `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
 *   `full_name` VARCHAR(255) NOT NULL,
 *   `email` VARCHAR(255) NOT NULL UNIQUE,
 *   `password_hash` VARCHAR(255) NOT NULL,
 *   `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 *   PRIMARY KEY (`id`)
 * );
 */


