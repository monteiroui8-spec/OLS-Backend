-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 16, 2026 at 04:09 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `olsangola_local`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `log_name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `event` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `causer_type` varchar(255) DEFAULT NULL,
  `causer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `batch_uuid` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_profiles`
--

CREATE TABLE `admin_profiles` (
  `id` char(26) NOT NULL,
  `user_id` char(26) NOT NULL,
  `admin_code` varchar(255) NOT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]' CHECK (json_valid(`permissions`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_profiles`
--

INSERT INTO `admin_profiles` (`id`, `user_id`, `admin_code`, `permissions`, `created_at`, `updated_at`) VALUES
('01krgq1rbkqhgvt3zrvq58ck7d', '01krgq1rb3awk374qfnz3dbffw', 'ADM-001', '[\"full_access\",\"manage_billing\",\"manage_staff\"]', '2026-05-13 12:05:06', '2026-05-13 12:05:06');

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` char(36) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `course_id` char(26) DEFAULT NULL,
  `class_group_id` char(26) DEFAULT NULL,
  `teacher_id` char(26) NOT NULL,
  `due_date` timestamp NULL DEFAULT NULL,
  `points` int(11) NOT NULL DEFAULT 100,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `attachment_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`id`, `title`, `description`, `course_id`, `class_group_id`, `teacher_id`, `due_date`, `points`, `is_published`, `attachment_url`, `created_at`, `updated_at`) VALUES
('a1cb0195-b92d-4fac-8e6d-662d5df0ba11', 'dasdasd', 'dasdsadsadsa', NULL, '01krgq1s1jcrcempqrq6s45wkk', '01krgq1rpw3ev4kw64j893ng7k', '2026-12-11 23:00:00', 100, 1, NULL, '2026-05-16 10:59:30', '2026-05-16 10:59:30');

-- --------------------------------------------------------

--
-- Table structure for table `assignment_submissions`
--

CREATE TABLE `assignment_submissions` (
  `id` char(36) NOT NULL,
  `assignment_id` char(36) NOT NULL,
  `student_id` char(26) NOT NULL,
  `file_url` varchar(255) DEFAULT NULL,
  `student_notes` text DEFAULT NULL,
  `grade` decimal(5,2) DEFAULT NULL,
  `teacher_feedback` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','graded','late','missing') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assignment_submissions`
--

INSERT INTO `assignment_submissions` (`id`, `assignment_id`, `student_id`, `file_url`, `student_notes`, `grade`, `teacher_feedback`, `submitted_at`, `status`, `created_at`, `updated_at`) VALUES
('a1cb2185-7aab-45c4-b82d-3e2566c463a7', 'a1cb0195-b92d-4fac-8e6d-662d5df0ba11', '01krgq1s17jyax0h8rq329nyw7', 'http://localhost:8000/storage/assignment-submissions/Cmfj3H0S6ZAqrgVSk9Fqq17ARwd0XwmIg320gkx7.png', NULL, NULL, NULL, '2026-05-16 12:28:46', 'pending', '2026-05-16 12:28:48', '2026-05-16 12:28:48');

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `id` char(26) NOT NULL,
  `class_group_id` char(26) NOT NULL,
  `student_id` char(26) NOT NULL,
  `teacher_id` char(26) NOT NULL,
  `date` date NOT NULL,
  `status` enum('present','absent','late','justified') NOT NULL DEFAULT 'present',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendances`
--

INSERT INTO `attendances` (`id`, `class_group_id`, `student_id`, `teacher_id`, `date`, `status`, `notes`, `created_at`, `updated_at`) VALUES
('01krgq1s2rtrmgpas3npbv56s5', '01krgq1s1jcrcempqrq6s45wkk', '01krgq1s17jyax0h8rq329nyw7', '01krgq1rpw3ev4kw64j893ng7k', '2026-01-20', 'present', NULL, '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1s2w045qxsf101tfx7r4', '01krgq1s1jcrcempqrq6s45wkk', '01krgq1s17jyax0h8rq329nyw7', '01krgq1rpw3ev4kw64j893ng7k', '2026-01-22', 'present', NULL, '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1s2y7w3e33te65105f8g', '01krgq1s1jcrcempqrq6s45wkk', '01krgq1s17jyax0h8rq329nyw7', '01krgq1rpw3ev4kw64j893ng7k', '2026-01-24', 'absent', 'Falta não justificada.', '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1s31afr0ef56mxxv2g7h', '01krgq1s1jcrcempqrq6s45wkk', '01krgq1s17jyax0h8rq329nyw7', '01krgq1rpw3ev4kw64j893ng7k', '2026-01-27', 'present', NULL, '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1s33vxqrv1zmwf1mnhna', '01krgq1s1jcrcempqrq6s45wkk', '01krgq1s17jyax0h8rq329nyw7', '01krgq1rpw3ev4kw64j893ng7k', '2026-01-29', 'late', 'Chegou 15 minutos atrasado.', '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1s37nzjsnyk7bw36w93x', '01krgq1s1jcrcempqrq6s45wkk', '01krgq1s17jyax0h8rq329nyw7', '01krgq1rpw3ev4kw64j893ng7k', '2026-01-31', 'present', NULL, '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1s3ar51chjbpgf0z90sq', '01krgq1s1jcrcempqrq6s45wkk', '01krgq1s17jyax0h8rq329nyw7', '01krgq1rpw3ev4kw64j893ng7k', '2026-02-03', 'present', NULL, '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1s3dy7xw6jww0w29f9yf', '01krgq1s1jcrcempqrq6s45wkk', '01krgq1s17jyax0h8rq329nyw7', '01krgq1rpw3ev4kw64j893ng7k', '2026-02-05', 'justified', 'Atestado médico apresentado.', '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1s3ga807jxk5dt77d5nc', '01krgq1s1jcrcempqrq6s45wkk', '01krgq1s17jyax0h8rq329nyw7', '01krgq1rpw3ev4kw64j893ng7k', '2026-02-07', 'present', NULL, '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1s3jmy3d2zxk4rmr2k5d', '01krgq1s1jcrcempqrq6s45wkk', '01krgq1s17jyax0h8rq329nyw7', '01krgq1rpw3ev4kw64j893ng7k', '2026-02-10', 'present', NULL, '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1w41g7dakk0btahkmdkd', '01krgq1w1fzbht0kw8ra5975ym', '01krgq1t3s8vcbmwwtq85f1r98', '01krgq1sfx91pgs9pvfg8v8g90', '2026-01-21', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w44gvyyp22q0g3cf41f', '01krgq1w1fzbht0kw8ra5975ym', '01krgq1t3s8vcbmwwtq85f1r98', '01krgq1sfx91pgs9pvfg8v8g90', '2026-01-23', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w48wvq4wsggmse7qcv6', '01krgq1w1fzbht0kw8ra5975ym', '01krgq1t3s8vcbmwwtq85f1r98', '01krgq1sfx91pgs9pvfg8v8g90', '2026-01-28', 'absent', 'Falta não justificada.', '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w4c0j2gm1sg4mm7ebfy', '01krgq1w1fzbht0kw8ra5975ym', '01krgq1t3s8vcbmwwtq85f1r98', '01krgq1sfx91pgs9pvfg8v8g90', '2026-01-30', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w4fzx65aqv4n1mr9yjs', '01krgq1w1fzbht0kw8ra5975ym', '01krgq1t3s8vcbmwwtq85f1r98', '01krgq1sfx91pgs9pvfg8v8g90', '2026-02-04', 'late', 'Chegou 10 minutos atrasado.', '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w4kd58vj2vjy2act69n', '01krgq1w1fzbht0kw8ra5975ym', '01krgq1t3s8vcbmwwtq85f1r98', '01krgq1sfx91pgs9pvfg8v8g90', '2026-02-06', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w4p3c0v15hzbhf8gcmx', '01krgq1w1fzbht0kw8ra5975ym', '01krgq1t3s8vcbmwwtq85f1r98', '01krgq1sfx91pgs9pvfg8v8g90', '2026-02-11', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w4tnyy7ydkksrzev27x', '01krgq1w1fzbht0kw8ra5975ym', '01krgq1t3s8vcbmwwtq85f1r98', '01krgq1sfx91pgs9pvfg8v8g90', '2026-02-13', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w5w20dfyaf9wkadtcww', '01krgq1w1fzbht0kw8ra5975ym', '01krgq1v3bn468cgzyhr1h3ez8', '01krgq1sfx91pgs9pvfg8v8g90', '2026-01-21', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w60c131t02ckbh44egt', '01krgq1w1fzbht0kw8ra5975ym', '01krgq1v3bn468cgzyhr1h3ez8', '01krgq1sfx91pgs9pvfg8v8g90', '2026-01-23', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w63xqspby1rpcn454z7', '01krgq1w1fzbht0kw8ra5975ym', '01krgq1v3bn468cgzyhr1h3ez8', '01krgq1sfx91pgs9pvfg8v8g90', '2026-01-28', 'absent', 'Falta não justificada.', '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w66kv4wsfbfyg9ryhd9', '01krgq1w1fzbht0kw8ra5975ym', '01krgq1v3bn468cgzyhr1h3ez8', '01krgq1sfx91pgs9pvfg8v8g90', '2026-01-30', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w69nyqg4xs7pa1810dd', '01krgq1w1fzbht0kw8ra5975ym', '01krgq1v3bn468cgzyhr1h3ez8', '01krgq1sfx91pgs9pvfg8v8g90', '2026-02-04', 'late', 'Chegou 10 minutos atrasado.', '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w6cfh1r535cvbgbymx8', '01krgq1w1fzbht0kw8ra5975ym', '01krgq1v3bn468cgzyhr1h3ez8', '01krgq1sfx91pgs9pvfg8v8g90', '2026-02-06', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w6e5cb1nmrgr28r6jb2', '01krgq1w1fzbht0kw8ra5975ym', '01krgq1v3bn468cgzyhr1h3ez8', '01krgq1sfx91pgs9pvfg8v8g90', '2026-02-11', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w6h775beya26w7c84v5', '01krgq1w1fzbht0kw8ra5975ym', '01krgq1v3bn468cgzyhr1h3ez8', '01krgq1sfx91pgs9pvfg8v8g90', '2026-02-13', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w79a54fgfm632pmtryg', '01krgq1w1fzbht0kw8ra5975ym', '01krgq1w17j9h5gvrbfccskj15', '01krgq1sfx91pgs9pvfg8v8g90', '2026-01-21', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w7cwv8t95esfjnhenv3', '01krgq1w1fzbht0kw8ra5975ym', '01krgq1w17j9h5gvrbfccskj15', '01krgq1sfx91pgs9pvfg8v8g90', '2026-01-23', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w7ep93v68kb935wg93e', '01krgq1w1fzbht0kw8ra5975ym', '01krgq1w17j9h5gvrbfccskj15', '01krgq1sfx91pgs9pvfg8v8g90', '2026-01-28', 'absent', 'Falta não justificada.', '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w7hvs7apzed648g96cg', '01krgq1w1fzbht0kw8ra5975ym', '01krgq1w17j9h5gvrbfccskj15', '01krgq1sfx91pgs9pvfg8v8g90', '2026-01-30', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w7m8yzv168byhrppvtr', '01krgq1w1fzbht0kw8ra5975ym', '01krgq1w17j9h5gvrbfccskj15', '01krgq1sfx91pgs9pvfg8v8g90', '2026-02-04', 'late', 'Chegou 10 minutos atrasado.', '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w7qv4sk0yb6jem0k82t', '01krgq1w1fzbht0kw8ra5975ym', '01krgq1w17j9h5gvrbfccskj15', '01krgq1sfx91pgs9pvfg8v8g90', '2026-02-06', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w7s9nkt19vebnc92vch', '01krgq1w1fzbht0kw8ra5975ym', '01krgq1w17j9h5gvrbfccskj15', '01krgq1sfx91pgs9pvfg8v8g90', '2026-02-11', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w7v8nm5b7sk2xwqrxd8', '01krgq1w1fzbht0kw8ra5975ym', '01krgq1w17j9h5gvrbfccskj15', '01krgq1sfx91pgs9pvfg8v8g90', '2026-02-13', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w8fxthw9h7tm4yt60rb', '01krgq1w247s0jps6shzvez3k4', '01krgq1tgyb5tjvx7x8qvvdtct', '01krgq1sfx91pgs9pvfg8v8g90', '2026-01-21', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w8hn4ycst4g7wan7kzx', '01krgq1w247s0jps6shzvez3k4', '01krgq1tgyb5tjvx7x8qvvdtct', '01krgq1sfx91pgs9pvfg8v8g90', '2026-01-23', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w8jy3d2ykbh5cj2km79', '01krgq1w247s0jps6shzvez3k4', '01krgq1tgyb5tjvx7x8qvvdtct', '01krgq1sfx91pgs9pvfg8v8g90', '2026-01-28', 'absent', 'Falta não justificada.', '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w8mjn3jg4v45pk66hs6', '01krgq1w247s0jps6shzvez3k4', '01krgq1tgyb5tjvx7x8qvvdtct', '01krgq1sfx91pgs9pvfg8v8g90', '2026-01-30', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w8r0aa7daspnybgd8ay', '01krgq1w247s0jps6shzvez3k4', '01krgq1tgyb5tjvx7x8qvvdtct', '01krgq1sfx91pgs9pvfg8v8g90', '2026-02-04', 'late', 'Chegou 10 minutos atrasado.', '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w8tg8vaqw3awa9wd3yd', '01krgq1w247s0jps6shzvez3k4', '01krgq1tgyb5tjvx7x8qvvdtct', '01krgq1sfx91pgs9pvfg8v8g90', '2026-02-06', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w8wnabga1qm1kx11mjm', '01krgq1w247s0jps6shzvez3k4', '01krgq1tgyb5tjvx7x8qvvdtct', '01krgq1sfx91pgs9pvfg8v8g90', '2026-02-11', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w8yp2q7krxhh135kg90', '01krgq1w247s0jps6shzvez3k4', '01krgq1tgyb5tjvx7x8qvvdtct', '01krgq1sfx91pgs9pvfg8v8g90', '2026-02-13', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w9gk6jf0zx7nks0qpxf', '01krgq1w247s0jps6shzvez3k4', '01krgq1tsfwh9pt1pp95t0zm5p', '01krgq1sfx91pgs9pvfg8v8g90', '2026-01-21', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w9jx7pxyfjeh3jsdtv6', '01krgq1w247s0jps6shzvez3k4', '01krgq1tsfwh9pt1pp95t0zm5p', '01krgq1sfx91pgs9pvfg8v8g90', '2026-01-23', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w9myajaam2mg4vb2f5p', '01krgq1w247s0jps6shzvez3k4', '01krgq1tsfwh9pt1pp95t0zm5p', '01krgq1sfx91pgs9pvfg8v8g90', '2026-01-28', 'absent', 'Falta não justificada.', '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w9ptg2ezgnyr5bxwk7r', '01krgq1w247s0jps6shzvez3k4', '01krgq1tsfwh9pt1pp95t0zm5p', '01krgq1sfx91pgs9pvfg8v8g90', '2026-01-30', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w9rhfvb4091vzem47er', '01krgq1w247s0jps6shzvez3k4', '01krgq1tsfwh9pt1pp95t0zm5p', '01krgq1sfx91pgs9pvfg8v8g90', '2026-02-04', 'late', 'Chegou 10 minutos atrasado.', '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w9tm5b0217zmkx2g0xv', '01krgq1w247s0jps6shzvez3k4', '01krgq1tsfwh9pt1pp95t0zm5p', '01krgq1sfx91pgs9pvfg8v8g90', '2026-02-06', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w9ww23mhqjt04y5xz6j', '01krgq1w247s0jps6shzvez3k4', '01krgq1tsfwh9pt1pp95t0zm5p', '01krgq1sfx91pgs9pvfg8v8g90', '2026-02-11', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w9yn0n2wwxxjrxbfenz', '01krgq1w247s0jps6shzvez3k4', '01krgq1tsfwh9pt1pp95t0zm5p', '01krgq1sfx91pgs9pvfg8v8g90', '2026-02-13', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wafgrbcftm62xagj7x0', '01krgq1w247s0jps6shzvez3k4', '01krgq1vpjvw5xq819245m22vt', '01krgq1sfx91pgs9pvfg8v8g90', '2026-01-21', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wah0qdxmeb7y1k9hhwk', '01krgq1w247s0jps6shzvez3k4', '01krgq1vpjvw5xq819245m22vt', '01krgq1sfx91pgs9pvfg8v8g90', '2026-01-23', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wajy913jb03t62jzq56', '01krgq1w247s0jps6shzvez3k4', '01krgq1vpjvw5xq819245m22vt', '01krgq1sfx91pgs9pvfg8v8g90', '2026-01-28', 'absent', 'Falta não justificada.', '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wamvt7s9h7h0fpt45zh', '01krgq1w247s0jps6shzvez3k4', '01krgq1vpjvw5xq819245m22vt', '01krgq1sfx91pgs9pvfg8v8g90', '2026-01-30', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1waphqa80zhp3q3yv0k6', '01krgq1w247s0jps6shzvez3k4', '01krgq1vpjvw5xq819245m22vt', '01krgq1sfx91pgs9pvfg8v8g90', '2026-02-04', 'late', 'Chegou 10 minutos atrasado.', '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1waqj59whx0xv6q442sx', '01krgq1w247s0jps6shzvez3k4', '01krgq1vpjvw5xq819245m22vt', '01krgq1sfx91pgs9pvfg8v8g90', '2026-02-06', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1was7v1xz95raynxp95m', '01krgq1w247s0jps6shzvez3k4', '01krgq1vpjvw5xq819245m22vt', '01krgq1sfx91pgs9pvfg8v8g90', '2026-02-11', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wav58kv3r1p0es9t8es', '01krgq1w247s0jps6shzvez3k4', '01krgq1vpjvw5xq819245m22vt', '01krgq1sfx91pgs9pvfg8v8g90', '2026-02-13', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wb9c5zcb24phmtrmeck', '01krgq1w2mstgagh0cn0ymhenr', '01krgq1vd40e9p33q8gc0bmqd7', '01krgq1stkx4add98k5nrsvnx6', '2026-01-21', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wbb9v9745xw9n9nfmxm', '01krgq1w2mstgagh0cn0ymhenr', '01krgq1vd40e9p33q8gc0bmqd7', '01krgq1stkx4add98k5nrsvnx6', '2026-01-23', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wbcfszmjamrc3vfbhgz', '01krgq1w2mstgagh0cn0ymhenr', '01krgq1vd40e9p33q8gc0bmqd7', '01krgq1stkx4add98k5nrsvnx6', '2026-01-28', 'absent', 'Falta não justificada.', '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wbef10ymg880v9xr49p', '01krgq1w2mstgagh0cn0ymhenr', '01krgq1vd40e9p33q8gc0bmqd7', '01krgq1stkx4add98k5nrsvnx6', '2026-01-30', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wbgf7qsjwzahnzh94zj', '01krgq1w2mstgagh0cn0ymhenr', '01krgq1vd40e9p33q8gc0bmqd7', '01krgq1stkx4add98k5nrsvnx6', '2026-02-04', 'late', 'Chegou 10 minutos atrasado.', '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wbhbf5mt9288xaxga5w', '01krgq1w2mstgagh0cn0ymhenr', '01krgq1vd40e9p33q8gc0bmqd7', '01krgq1stkx4add98k5nrsvnx6', '2026-02-06', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wbk774mje7xrfc51r0e', '01krgq1w2mstgagh0cn0ymhenr', '01krgq1vd40e9p33q8gc0bmqd7', '01krgq1stkx4add98k5nrsvnx6', '2026-02-11', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wbmkk2s5gy9cweyhhy1', '01krgq1w2mstgagh0cn0ymhenr', '01krgq1vd40e9p33q8gc0bmqd7', '01krgq1stkx4add98k5nrsvnx6', '2026-02-13', 'present', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10');

-- --------------------------------------------------------

--
-- Table structure for table `blog_categories`
--

CREATE TABLE `blog_categories` (
  `id` char(26) NOT NULL,
  `name_pt` varchar(255) NOT NULL,
  `name_en` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blog_categories`
--

INSERT INTO `blog_categories` (`id`, `name_pt`, `name_en`, `slug`, `created_at`, `updated_at`) VALUES
('01krgq1wffwffk821w20559saa', 'Dicas de Estudo', 'Study Tips', 'dicas-de-estudo', '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wfjf8rqe66avc9q20s0', 'Notícias da Escola', 'School News', 'noticias-da-escola', '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wfknp79494n7tdgh394', 'Inglês para Negócios', 'Business English', 'ingles-para-negocios', '2026-05-13 12:05:10', '2026-05-13 12:05:10');

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` char(26) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `excerpt` text DEFAULT NULL,
  `category_id` char(26) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `author_id` char(26) NOT NULL,
  `status` enum('draft','published','scheduled') NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `meta_description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `blog_posts`
--

INSERT INTO `blog_posts` (`id`, `title`, `slug`, `content`, `excerpt`, `category_id`, `image_url`, `author_id`, `status`, `published_at`, `tags`, `meta_description`, `created_at`, `updated_at`) VALUES
('01krgq1wfqp33y2mybvz8m4a07', 'Como melhorar o seu Speaking em 30 dias', 'como-melhorar-o-seu-speaking-em-30-dias', '<h2>1. Pratique todos os dias</h2><p>A consistência é a chave. Fale inglês por pelo menos 15 minutos diariamente.</p><h2>2. Encontre um parceiro de conversação</h2><p>Ter alguém para praticar ajuda a perder o medo.</p>', 'Descubra técnicas simples e eficazes para ganhar confiança e fluência na fala.', '01krgq1wffwffk821w20559saa', 'assets/about-approach.jpg', '01krgq1rb3awk374qfnz3dbffw', 'published', '2026-05-05 12:05:10', NULL, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wft7pjehqnwy0kw2f54', 'Nova turma de Inglês para Negócios', 'nova-turma-de-ingles-para-negocios', '<p>Se você quer dar um salto na sua carreira internacional, a OLS preparou uma turma especial de Business English.</p><p>Vagas limitadas!</p>', 'Estamos a abrir novas inscrições para o curso de Business English. Saiba mais!', '01krgq1wfjf8rqe66avc9q20s0', 'assets/flyer-corporate.png', '01krgq1rb3awk374qfnz3dbffw', 'published', '2026-05-06 12:05:10', NULL, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wfw2g1rbqb2ntdfhsch', 'Expressões essenciais no mundo corporativo', 'expressoes-essenciais-no-mundo-corporativo', '<p>Expressões como \"Think outside the box\", \"Touch base\" e \"Get the ball rolling\" são fundamentais para entender o que os nativos dizem nas reuniões.</p>', 'Aprenda os idioms mais usados em reuniões de negócios em inglês.', '01krgq1wfknp79494n7tdgh394', 'assets/course-business.jpg', '01krgq1rb3awk374qfnz3dbffw', 'published', '2026-05-04 12:05:10', NULL, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_attachments`
--

CREATE TABLE `chat_attachments` (
  `id` char(26) NOT NULL,
  `uploader_id` char(26) NOT NULL,
  `url` varchar(500) NOT NULL,
  `name` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `size_bytes` bigint(20) UNSIGNED NOT NULL,
  `duration_seconds` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chat_attachments`
--

INSERT INTO `chat_attachments` (`id`, `uploader_id`, `url`, `name`, `mime_type`, `size_bytes`, `duration_seconds`, `created_at`, `updated_at`) VALUES
('01krgr20y9gz0aah5znpn0w7k4', '01krgq1rpbc47ckjsxm97x9e1w', '/storage/chat/attachments/rvQCbCFEqkEgpcWoWWc1BopDfFRSx09pdgPLaOL3.jpg', 'about-vision.jpg', 'image/jpeg', 66924, NULL, '2026-05-13 12:22:43', '2026-05-13 12:22:43'),
('01krgtg0ffb811ky0j40qsd67r', '01krgq1rpbc47ckjsxm97x9e1w', '/storage/chat/attachments/aYgXtb2pYY16QwrENpHav6kG1bePEO1CFPVMTMu3.jpg', 'Teste.jpg', 'image/jpeg', 66924, NULL, '2026-05-13 13:05:19', '2026-05-13 13:05:19'),
('01krrasnaf461019g8w03q50az', '01krgq1s0wfzpwk8tgywfvxp88', 'http://localhost:8000/storage/chat/attachments/9tWH4EF1gzuHOTyXiGcoohM5czbAoJThbZeMETXI.png', 'Designer_UI_Crie_um_logotipo_luxuoso_e_moderno_para_Tranas_da_Engrcia_c_b2476c48-98cc-4c59-8376-8dba49ab0905.png', 'image/png', 1546714, NULL, '2026-05-16 11:04:53', '2026-05-16 11:04:53'),
('01krrat9dneyar5mm94905hqzb', '01krgq1s0wfzpwk8tgywfvxp88', 'http://localhost:8000/storage/chat/attachments/PtSS1D64P5tr4nxiIlXRkb1jdMERP0QfMdAXdqrz.webm', 'audio.webm', 'video/webm', 191047, NULL, '2026-05-16 11:05:14', '2026-05-16 11:05:14'),
('01krrav3b7c0m9jc6cnqzdw8vh', '01krgq1s0wfzpwk8tgywfvxp88', 'http://localhost:8000/storage/chat/attachments/BOjneUaKr8nRa8knENtNouOQbC9Q2T1IHiGJAadI.webm', 'audio.webm', 'video/webm', 35207, NULL, '2026-05-16 11:05:40', '2026-05-16 11:05:40'),
('01krrc029rberpwzs5ywehc50k', '01krgq1s0wfzpwk8tgywfvxp88', 'http://localhost:8000/storage/chat/attachments/a3zCin30urrdZyCfR9prnJtWdcaqrFuwttDuIssU.webm', 'audio.webm', 'video/webm', 123849, NULL, '2026-05-16 11:25:52', '2026-05-16 11:25:52'),
('01krrc24g6whk8hm17jpc3ahxs', '01krgq1s0wfzpwk8tgywfvxp88', 'http://localhost:8000/storage/chat/attachments/exph73039Lv5dDLxPUolwZ3m0SUhf3sZwDb2igcm.png', 'Lucilene_Pereira_Crie_um_logotipo_moderno_elegante_e_memorvel_para_uma_marca_de_a6629b19-88df-4c16-8797-eb1465ebb9f1.png', 'image/png', 6639129, NULL, '2026-05-16 11:26:59', '2026-05-16 11:26:59');

-- --------------------------------------------------------

--
-- Table structure for table `chat_conversations`
--

CREATE TABLE `chat_conversations` (
  `id` char(26) NOT NULL,
  `type` enum('direct','group') NOT NULL DEFAULT 'direct',
  `name` varchar(150) DEFAULT NULL,
  `class_id` char(26) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chat_conversations`
--

INSERT INTO `chat_conversations` (`id`, `type`, `name`, `class_id`, `created_at`, `updated_at`) VALUES
('01krgr0g9tdvdw4dg1yakdcpax', 'direct', NULL, NULL, '2026-05-13 12:21:53', '2026-05-16 11:27:00'),
('01krh71c05kzsx0m3neaw70e8s', 'direct', NULL, NULL, '2026-05-13 16:44:30', '2026-05-13 16:48:15');

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` char(26) NOT NULL,
  `conversation_id` char(26) NOT NULL,
  `sender_id` char(26) NOT NULL,
  `type` enum('text','audio','file','image') NOT NULL DEFAULT 'text',
  `body` text DEFAULT NULL,
  `attachment_id` char(26) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `conversation_id`, `sender_id`, `type`, `body`, `attachment_id`, `created_at`, `updated_at`) VALUES
('01krgr1t1f65sh5mx7a167g772', '01krgr0g9tdvdw4dg1yakdcpax', '01krgq1rpbc47ckjsxm97x9e1w', 'text', 'Ola', NULL, '2026-05-13 12:22:36', '2026-05-13 12:22:36'),
('01krgr2193ywzb3f9gdwzvba2n', '01krgr0g9tdvdw4dg1yakdcpax', '01krgq1rpbc47ckjsxm97x9e1w', 'image', NULL, '01krgr20y9gz0aah5znpn0w7k4', '2026-05-13 12:22:44', '2026-05-13 12:22:44'),
('01krgtg0vcjw7kcq9b0t1qeb60', '01krgr0g9tdvdw4dg1yakdcpax', '01krgq1rpbc47ckjsxm97x9e1w', 'image', NULL, '01krgtg0ffb811ky0j40qsd67r', '2026-05-13 13:05:19', '2026-05-13 13:05:19'),
('01krh71h925xjzd2jf3wsesdk9', '01krh71c05kzsx0m3neaw70e8s', '01krgq1rpbc47ckjsxm97x9e1w', 'text', 'Oi', NULL, '2026-05-13 16:44:36', '2026-05-13 16:44:36'),
('01krh787n9jkhcctnejpetz9qz', '01krh71c05kzsx0m3neaw70e8s', '01krh6n1kn0h9kg2zw9gsm74ey', 'text', 'Sim', NULL, '2026-05-13 16:48:15', '2026-05-13 16:48:15'),
('01krrasp237rjf0hk62c7tj5gw', '01krgr0g9tdvdw4dg1yakdcpax', '01krgq1s0wfzpwk8tgywfvxp88', 'image', NULL, '01krrasnaf461019g8w03q50az', '2026-05-16 11:04:54', '2026-05-16 11:04:54'),
('01krrat9vz917120ma30tt72yz', '01krgr0g9tdvdw4dg1yakdcpax', '01krgq1s0wfzpwk8tgywfvxp88', 'audio', NULL, '01krrat9dneyar5mm94905hqzb', '2026-05-16 11:05:14', '2026-05-16 11:05:14'),
('01krrav3y586vf4xt4gzr38vny', '01krgr0g9tdvdw4dg1yakdcpax', '01krgq1s0wfzpwk8tgywfvxp88', 'audio', NULL, '01krrav3b7c0m9jc6cnqzdw8vh', '2026-05-16 11:05:41', '2026-05-16 11:05:41'),
('01krrc034exqv57n8g6q8b8j85', '01krgr0g9tdvdw4dg1yakdcpax', '01krgq1s0wfzpwk8tgywfvxp88', 'audio', NULL, '01krrc029rberpwzs5ywehc50k', '2026-05-16 11:25:53', '2026-05-16 11:25:53'),
('01krrc253s94gwtstz7nqqzese', '01krgr0g9tdvdw4dg1yakdcpax', '01krgq1s0wfzpwk8tgywfvxp88', 'image', NULL, '01krrc24g6whk8hm17jpc3ahxs', '2026-05-16 11:27:00', '2026-05-16 11:27:00');

-- --------------------------------------------------------

--
-- Table structure for table `chat_message_reads`
--

CREATE TABLE `chat_message_reads` (
  `message_id` char(26) NOT NULL,
  `user_id` char(26) NOT NULL,
  `read_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chat_message_reads`
--

INSERT INTO `chat_message_reads` (`message_id`, `user_id`, `read_at`) VALUES
('01krgr1t1f65sh5mx7a167g772', '01krgq1rpbc47ckjsxm97x9e1w', '2026-05-13 12:22:36'),
('01krgr1t1f65sh5mx7a167g772', '01krgq1s0wfzpwk8tgywfvxp88', '2026-05-16 11:04:42'),
('01krgr2193ywzb3f9gdwzvba2n', '01krgq1rpbc47ckjsxm97x9e1w', '2026-05-13 12:22:44'),
('01krgr2193ywzb3f9gdwzvba2n', '01krgq1s0wfzpwk8tgywfvxp88', '2026-05-16 11:04:42'),
('01krgtg0vcjw7kcq9b0t1qeb60', '01krgq1rpbc47ckjsxm97x9e1w', '2026-05-13 13:05:19'),
('01krgtg0vcjw7kcq9b0t1qeb60', '01krgq1s0wfzpwk8tgywfvxp88', '2026-05-16 11:04:42'),
('01krh71h925xjzd2jf3wsesdk9', '01krgq1rpbc47ckjsxm97x9e1w', '2026-05-13 16:44:36'),
('01krh71h925xjzd2jf3wsesdk9', '01krh6n1kn0h9kg2zw9gsm74ey', '2026-05-13 16:48:11'),
('01krh787n9jkhcctnejpetz9qz', '01krh6n1kn0h9kg2zw9gsm74ey', '2026-05-13 16:48:15'),
('01krrasp237rjf0hk62c7tj5gw', '01krgq1rpbc47ckjsxm97x9e1w', '2026-05-16 11:30:10'),
('01krrasp237rjf0hk62c7tj5gw', '01krgq1s0wfzpwk8tgywfvxp88', '2026-05-16 11:04:54'),
('01krrat9vz917120ma30tt72yz', '01krgq1rpbc47ckjsxm97x9e1w', '2026-05-16 11:30:10'),
('01krrat9vz917120ma30tt72yz', '01krgq1s0wfzpwk8tgywfvxp88', '2026-05-16 11:05:14'),
('01krrav3y586vf4xt4gzr38vny', '01krgq1rpbc47ckjsxm97x9e1w', '2026-05-16 11:30:10'),
('01krrav3y586vf4xt4gzr38vny', '01krgq1s0wfzpwk8tgywfvxp88', '2026-05-16 11:05:41'),
('01krrc034exqv57n8g6q8b8j85', '01krgq1rpbc47ckjsxm97x9e1w', '2026-05-16 11:30:10'),
('01krrc034exqv57n8g6q8b8j85', '01krgq1s0wfzpwk8tgywfvxp88', '2026-05-16 11:25:53'),
('01krrc253s94gwtstz7nqqzese', '01krgq1rpbc47ckjsxm97x9e1w', '2026-05-16 11:30:10'),
('01krrc253s94gwtstz7nqqzese', '01krgq1s0wfzpwk8tgywfvxp88', '2026-05-16 11:27:00');

-- --------------------------------------------------------

--
-- Table structure for table `chat_participants`
--

CREATE TABLE `chat_participants` (
  `conversation_id` char(26) NOT NULL,
  `user_id` char(26) NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chat_participants`
--

INSERT INTO `chat_participants` (`conversation_id`, `user_id`, `joined_at`) VALUES
('01krgr0g9tdvdw4dg1yakdcpax', '01krgq1rpbc47ckjsxm97x9e1w', '2026-05-13 12:21:53'),
('01krgr0g9tdvdw4dg1yakdcpax', '01krgq1s0wfzpwk8tgywfvxp88', '2026-05-13 12:21:53'),
('01krh71c05kzsx0m3neaw70e8s', '01krgq1rpbc47ckjsxm97x9e1w', '2026-05-13 16:44:30'),
('01krh71c05kzsx0m3neaw70e8s', '01krh6n1kn0h9kg2zw9gsm74ey', '2026-05-13 16:44:30');

-- --------------------------------------------------------

--
-- Table structure for table `class_groups`
--

CREATE TABLE `class_groups` (
  `id` char(26) NOT NULL,
  `name` varchar(255) NOT NULL,
  `course_id` char(26) NOT NULL,
  `teacher_id` char(26) NOT NULL,
  `year` year(4) NOT NULL,
  `capacity` smallint(5) UNSIGNED NOT NULL DEFAULT 6,
  `room` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `class_groups`
--

INSERT INTO `class_groups` (`id`, `name`, `course_id`, `teacher_id`, `year`, `capacity`, `room`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
('01krgq1s1jcrcempqrq6s45wkk', 'Turma B1 — Manhã 2026', '01krgq1qy96xw88238ktyrn971', '01krgq1rpw3ev4kw64j893ng7k', '2026', 8, 'Sala 02', 1, '2026-05-13 12:05:07', '2026-05-13 12:05:07', NULL),
('01krgq1w1fzbht0kw8ra5975ym', 'Turma A1 — Tarde 2026', '01krgq1qy4z2434tyem0yq66e0', '01krgq1sfx91pgs9pvfg8v8g90', '2026', 8, 'Sala 03', 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10', NULL),
('01krgq1w247s0jps6shzvez3k4', 'Turma Oil&Gas — Manhã 2026', '01krgq1qyg0jrr1cympad27n2x', '01krgq1sfx91pgs9pvfg8v8g90', '2026', 6, 'Sala 04', 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10', NULL),
('01krgq1w2mstgagh0cn0ymhenr', 'Turma Banca — Noite 2026', '01krgq1qymzzypdrw78mbex17n', '01krgq1stkx4add98k5nrsvnx6', '2026', 8, 'Sala 05', 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `class_schedules`
--

CREATE TABLE `class_schedules` (
  `id` char(26) NOT NULL,
  `class_group_id` char(26) NOT NULL,
  `day_of_week` tinyint(3) UNSIGNED NOT NULL,
  `start_time` varchar(5) NOT NULL,
  `end_time` varchar(5) NOT NULL,
  `room` varchar(255) DEFAULT NULL,
  `is_recurring` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `class_schedules`
--

INSERT INTO `class_schedules` (`id`, `class_group_id`, `day_of_week`, `start_time`, `end_time`, `room`, `is_recurring`, `created_at`, `updated_at`) VALUES
('01krgq1s1rjt76vt1x9wkjxyv2', '01krgq1s1jcrcempqrq6s45wkk', 1, '08:00', '10:00', 'Sala 02', 1, '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1s1vj5a8ggratymxq34q', '01krgq1s1jcrcempqrq6s45wkk', 3, '08:00', '10:00', 'Sala 02', 1, '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1s20t18jg4yrsn4xwmap', '01krgq1s1jcrcempqrq6s45wkk', 5, '08:00', '10:00', 'Sala 02', 1, '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1w1pbefqfjz8mw6sjyxa', '01krgq1w1fzbht0kw8ra5975ym', 2, '14:00', '16:00', 'Sala 03', 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w1xbk9kth0xr1y6k9te', '01krgq1w1fzbht0kw8ra5975ym', 4, '14:00', '16:00', 'Sala 03', 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w28vzvwm13w51ehk1p8', '01krgq1w247s0jps6shzvez3k4', 1, '09:00', '11:00', 'Sala 04', 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w2cfehjd3afr2fkq16m', '01krgq1w247s0jps6shzvez3k4', 3, '09:00', '11:00', 'Sala 04', 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w2s7m6yv5rmmbtzyett', '01krgq1w2mstgagh0cn0ymhenr', 2, '18:00', '20:00', 'Sala 05', 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w2wp1bwrc6j9d9j3tcs', '01krgq1w2mstgagh0cn0ymhenr', 4, '18:00', '20:00', 'Sala 05', 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10');

-- --------------------------------------------------------

--
-- Table structure for table `contact_form_submissions`
--

CREATE TABLE `contact_form_submissions` (
  `id` char(26) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `language` enum('pt','en') NOT NULL DEFAULT 'pt',
  `ip_address` varchar(255) DEFAULT NULL,
  `replied_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_form_submissions`
--

INSERT INTO `contact_form_submissions` (`id`, `first_name`, `last_name`, `email`, `phone`, `subject`, `message`, `language`, `ip_address`, `replied_at`, `created_at`, `updated_at`) VALUES
('01krgr6et9nvvz57025cbeg09y', 'Teste', 'Pereira', 'lucilenepereir123@gmail.com', '92787654', 'Inscrição', 'Quero me inscrever no curso de inglês', 'pt', '127.0.0.1', NULL, '2026-05-13 12:25:09', '2026-05-13 12:25:09');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` char(26) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `title_pt` varchar(255) NOT NULL,
  `title_en` varchar(255) NOT NULL,
  `description_pt` text NOT NULL,
  `description_en` text NOT NULL,
  `prerequisites` text DEFAULT NULL,
  `level` varchar(255) NOT NULL,
  `service_type` enum('group_daily_communication','individual','kanuca','oil_gas','banking_finance','corporate') NOT NULL,
  `duration` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `available_seats` smallint(5) UNSIGNED DEFAULT NULL,
  `responsible_teacher_id` char(26) DEFAULT NULL,
  `syllabus` longtext DEFAULT NULL,
  `required_material` longtext DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `flyer_url` varchar(255) DEFAULT NULL,
  `price_aoa` decimal(12,2) NOT NULL DEFAULT 0.00,
  `price_eur` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price_usd` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `visibility_status` enum('draft','published','scheduled','hidden') NOT NULL DEFAULT 'published',
  `published_at` timestamp NULL DEFAULT NULL,
  `meta_description` varchar(180) DEFAULT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]' CHECK (json_valid(`tags`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `slug`, `title_pt`, `title_en`, `description_pt`, `description_en`, `prerequisites`, `level`, `service_type`, `duration`, `start_date`, `available_seats`, `responsible_teacher_id`, `syllabus`, `required_material`, `image_url`, `flyer_url`, `price_aoa`, `price_eur`, `price_usd`, `is_active`, `visibility_status`, `published_at`, `meta_description`, `tags`, `created_at`, `updated_at`, `deleted_at`) VALUES
('01krgq1qy4z2434tyem0yq66e0', 'ingles-comunicacao-diaria-a1', 'Inglês — Comunicação Diária A1', 'English — Daily Communication A1', 'Curso introdutório para quem não tem qualquer conhecimento de inglês. Foco em vocabulário básico, saudações e frases do quotidiano.', 'Introductory course for absolute beginners. Focus on basic vocabulary, greetings and everyday phrases.', NULL, 'A1', 'group_daily_communication', '3 meses', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 35000.00, 35.00, 38.00, 1, 'published', NULL, NULL, '[\"iniciante\",\"grupo\",\"comunica\\u00e7\\u00e3o\"]', '2026-05-13 12:05:05', '2026-05-13 12:05:05', NULL),
('01krgq1qy96xw88238ktyrn971', 'ingles-comunicacao-diaria-b1', 'Inglês — Comunicação Diária B1', 'English — Daily Communication B1', 'Nível intermédio. O aluno aperfeiçoa a compreensão oral e escrita e adquire fluência para conversas do dia-a-dia.', 'Intermediate level. Students improve listening and reading comprehension and develop fluency for everyday conversations.', NULL, 'B1', 'group_daily_communication', '4 meses', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 45000.00, 45.00, 49.00, 1, 'published', NULL, NULL, '[\"interm\\u00e9dio\",\"grupo\",\"conversa\\u00e7\\u00e3o\"]', '2026-05-13 12:05:05', '2026-05-13 12:05:05', NULL),
('01krgq1qycwgyd6w6qwqs4zd32', 'ingles-individual-b2', 'Inglês Individual B2', 'Individual English B2', 'Aulas individuais personalizadas para nível B2. Ideal para quem precisa de evoluir rapidamente.', 'Personalised one-on-one lessons at B2 level. Ideal for students who need to progress quickly.', NULL, 'B2', 'individual', 'Flexível', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 70000.00, 70.00, 75.00, 1, 'published', NULL, NULL, '[\"individual\",\"avan\\u00e7ado\"]', '2026-05-13 12:05:05', '2026-05-13 12:05:05', NULL),
('01krgq1qyg0jrr1cympad27n2x', 'ingles-oil-gas', 'Inglês para Petróleo e Gás', 'English for Oil & Gas', 'Inglês técnico para profissionais do sector petrolífero. Vocabulário especializado, relatórios e comunicação offshore.', 'Technical English for oil and gas professionals. Specialised vocabulary, reports and offshore communication.', NULL, 'B2', 'oil_gas', '6 meses', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 120000.00, 120.00, 130.00, 1, 'published', NULL, NULL, '[\"oil&gas\",\"corporativo\",\"t\\u00e9cnico\"]', '2026-05-13 12:05:06', '2026-05-13 12:05:06', NULL),
('01krgq1qymzzypdrw78mbex17n', 'ingles-banca-financas', 'Inglês para Banca e Finanças', 'English for Banking & Finance', 'Curso especializado para profissionais do sector bancário e financeiro angolano.', 'Specialised course for banking and finance professionals in Angola.', NULL, 'B1', 'banking_finance', '5 meses', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 95000.00, 95.00, 103.00, 1, 'published', NULL, NULL, '[\"banca\",\"finan\\u00e7as\",\"corporativo\"]', '2026-05-13 12:05:06', '2026-05-13 12:05:06', NULL),
('01krgq1wesv7w8mzem3qvsfcw7', 'general-english-DAvyL', 'Inglês Geral', 'General English', 'Construa uma base forte em inglês com lições compreensivas cobrindo habilidades de fala, escuta, leitura e escrita.', 'Build a strong foundation in English with comprehensive lessons covering speaking, listening, reading, and writing skills.', 'Nenhum', 'beginner', 'group_daily_communication', '12 weeks', '2026-05-28', 10, '01krgq1rpw3ev4kw64j893ng7k', 'Conteúdo programático em desenvolvimento...', 'Caderno e Caneta', 'assets/course-beginner.jpg', NULL, 45000.00, 45.00, 50.00, 1, 'published', '2026-05-13 12:05:10', NULL, '[]', '2026-05-13 12:05:10', '2026-05-13 12:05:10', NULL),
('01krgq1wexggy84k45nnswgz9a', 'business-english-O1JIa', 'Inglês para Negócios', 'Business English', 'Domine o inglês profissional para o ambiente de trabalho, incluindo apresentações, negociações e correspondência comercial.', 'Master professional English for the workplace including presentations, negotiations, and business correspondence.', 'Nenhum', 'intermediate', 'corporate', '16 weeks', '2026-05-28', 10, '01krgq1rpw3ev4kw64j893ng7k', 'Conteúdo programático em desenvolvimento...', 'Caderno e Caneta', 'assets/course-business.jpg', 'assets/flyer-corporate.png', 85000.00, 85.00, 94.44, 1, 'published', '2026-05-13 12:05:10', NULL, '[]', '2026-05-13 12:05:10', '2026-05-13 12:05:10', NULL),
('01krgq1wf1p3r3c4k94t6p3rjq', 'conversational-english-c4Sy8', 'Inglês para Conversação', 'Conversational English', 'Melhore sua fluência e confiança nas conversas do dia a dia com instrutores falantes nativos.', 'Improve your fluency and confidence in everyday conversations with native-speaking instructors.', 'Nenhum', 'intermediate', 'group_daily_communication', '8 weeks', '2026-05-28', 10, '01krgq1rpw3ev4kw64j893ng7k', 'Conteúdo programático em desenvolvimento...', 'Caderno e Caneta', 'assets/course-conversation.jpg', NULL, 40000.00, 40.00, 44.44, 1, 'published', '2026-05-13 12:05:10', NULL, '[]', '2026-05-13 12:05:10', '2026-05-13 12:05:10', NULL),
('01krgq1wf4zmb1f2mgspenyt0y', 'ielts-preparation-3Xvfk', 'Preparação para IELTS', 'IELTS Preparation', 'Prepare-se para o exame IELTS com prática direcionada nas quatro habilidades e estratégias para a prova.', 'Prepare for the IELTS exam with targeted practice in all four skills and test-taking strategies.', 'Nenhum', 'advanced', 'individual', '12 weeks', '2026-05-28', 10, '01krgq1rpw3ev4kw64j893ng7k', 'Conteúdo programático em desenvolvimento...', 'Caderno e Caneta', 'assets/course-ielts.jpg', NULL, 95000.00, 95.00, 105.56, 1, 'published', '2026-05-13 12:05:10', NULL, '[]', '2026-05-13 12:05:10', '2026-05-13 12:05:10', NULL),
('01krgq1wf68tg666vew2h17pft', 'english-for-kids-wzMlF', 'Inglês para Crianças (Kanuca)', 'English for Kids', 'Lições divertidas e interativas de inglês projetadas especificamente para crianças de 6 a 12 anos.', 'Fun and interactive English lessons designed specifically for children aged 6-12 years.', 'Nenhum', 'beginner', 'kanuca', '20 weeks', '2026-05-28', 10, '01krgq1rpw3ev4kw64j893ng7k', 'Conteúdo programático em desenvolvimento...', 'Caderno e Caneta', 'assets/class-3.jpg', 'assets/flyer-kanuca.png', 35000.00, 35.00, 38.89, 1, 'published', '2026-05-13 12:05:10', NULL, '[]', '2026-05-13 12:05:10', '2026-05-13 12:05:10', NULL),
('01krgq1wf80hcv9f21qvyn7xsy', 'advanced-writing-tpFyN', 'Escrita Avançada', 'Advanced Writing', 'Aprimore suas habilidades de escrita para fins acadêmicos e profissionais com orientação especializada.', 'Enhance your writing skills for academic and professional purposes with expert guidance.', 'Nenhum', 'advanced', 'individual', '10 weeks', '2026-05-28', 10, '01krgq1rpw3ev4kw64j893ng7k', 'Conteúdo programático em desenvolvimento...', 'Caderno e Caneta', 'assets/course-advanced.jpg', NULL, 50000.00, 50.00, 55.56, 1, 'published', '2026-05-13 12:05:10', NULL, '[]', '2026-05-13 12:05:10', '2026-05-13 12:05:10', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` char(26) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('PDF','Video','Image','Document','Audio') NOT NULL,
  `mime_type` varchar(255) NOT NULL,
  `size_bytes` bigint(20) UNSIGNED NOT NULL,
  `storage_key` varchar(255) NOT NULL,
  `public_url` varchar(255) DEFAULT NULL,
  `course_id` char(26) DEFAULT NULL,
  `uploaded_by_id` char(26) NOT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `downloads` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `name`, `type`, `mime_type`, `size_bytes`, `storage_key`, `public_url`, `course_id`, `uploaded_by_id`, `is_public`, `downloads`, `created_at`, `updated_at`, `deleted_at`) VALUES
('01krgq1s5avh4vtwj2f5nt5cwa', 'Programa do Curso B1 2026', 'PDF', 'application/pdf', 204800, 'documents/b1-programa-2026.pdf', NULL, '01krgq1qy96xw88238ktyrn971', '01krgq1rpbc47ckjsxm97x9e1w', 0, 12, '2026-05-13 12:05:07', '2026-05-13 12:05:07', NULL),
('01krgq1s5c8hewf8adgyyyknxf', 'Guia de Estudo — Unidade 1', 'Document', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 98304, 'documents/b1-guia-unidade1.docx', NULL, '01krgq1qy96xw88238ktyrn971', '01krgq1rpbc47ckjsxm97x9e1w', 1, 7, '2026-05-13 12:05:07', '2026-05-13 12:05:07', NULL),
('01krgq1s5gq2gxnnjkecy5rb4s', 'Exercícios de Listening — Unidade 1', 'Audio', 'audio/mpeg', 5242880, 'documents/b1-listening-unidade1.mp3', NULL, '01krgq1qy96xw88238ktyrn971', '01krgq1rpbc47ckjsxm97x9e1w', 0, 5, '2026-05-13 12:05:07', '2026-05-13 12:05:07', NULL),
('01krgq1wec5zy69shc4w2jvbv1', 'Programa do Curso A1 2026', 'PDF', 'application/pdf', 204800, 'documents/a1-programa-2026.pdf', NULL, '01krgq1qy4z2434tyem0yq66e0', '01krgq1sffpwqrctrbe8gvwbh1', 0, 15, '2026-05-13 12:05:10', '2026-05-13 12:05:10', NULL),
('01krgq1weepkw9sjchn5y4vzya', 'Guia de Estudo A1 — Unidade 1', 'Document', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 98304, 'documents/a1-guia-unidade1.docx', NULL, '01krgq1qy4z2434tyem0yq66e0', '01krgq1sffpwqrctrbe8gvwbh1', 1, 8, '2026-05-13 12:05:10', '2026-05-13 12:05:10', NULL),
('01krgq1wegy2z0j28ytfen7d74', 'Programa do Curso B2 2026', 'PDF', 'application/pdf', 204800, 'documents/oil_gas-programa-2026.pdf', NULL, '01krgq1qyg0jrr1cympad27n2x', '01krgq1sffpwqrctrbe8gvwbh1', 0, 14, '2026-05-13 12:05:10', '2026-05-13 12:05:10', NULL),
('01krgq1weh6zr787awqggqrzag', 'Guia de Estudo B2 — Unidade 1', 'Document', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 98304, 'documents/oil_gas-guia-unidade1.docx', NULL, '01krgq1qyg0jrr1cympad27n2x', '01krgq1sffpwqrctrbe8gvwbh1', 1, 5, '2026-05-13 12:05:10', '2026-05-13 12:05:10', NULL),
('01krgq1wekz67c99xm8d1x8w6x', 'Programa do Curso B1 2026', 'PDF', 'application/pdf', 204800, 'documents/banking-programa-2026.pdf', NULL, '01krgq1qymzzypdrw78mbex17n', '01krgq1sta6t3bvpy0j58w6tmc', 0, 20, '2026-05-13 12:05:10', '2026-05-13 12:05:10', NULL),
('01krgq1wen1ax6qmzy6ee3xm63', 'Guia de Estudo B1 — Unidade 1', 'Document', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 98304, 'documents/banking-guia-unidade1.docx', NULL, '01krgq1qymzzypdrw78mbex17n', '01krgq1sta6t3bvpy0j58w6tmc', 1, 8, '2026-05-13 12:05:10', '2026-05-13 12:05:10', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` char(26) NOT NULL,
  `student_id` char(26) NOT NULL,
  `course_id` char(26) NOT NULL,
  `class_group_id` char(26) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','completed','suspended','cancelled') NOT NULL DEFAULT 'active',
  `progress_pct` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `payment_start_date` date DEFAULT NULL,
  `payment_frequency` enum('monthly','quarterly','semiannual','annual') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `course_id`, `class_group_id`, `start_date`, `end_date`, `status`, `progress_pct`, `notes`, `payment_start_date`, `payment_frequency`, `created_at`, `updated_at`) VALUES
('01krgq1s255qy48jax9d9jvyth', '01krgq1s17jyax0h8rq329nyw7', '01krgq1qy96xw88238ktyrn971', '01krgq1s1jcrcempqrq6s45wkk', '2026-01-20', '2026-05-20', 'active', 45, 'Inscrição confirmada. Pagamento parcial recebido.', NULL, NULL, '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1w30073dknzcmnxehm3d', '01krgq1t3s8vcbmwwtq85f1r98', '01krgq1qy4z2434tyem0yq66e0', '01krgq1w1fzbht0kw8ra5975ym', '2026-01-20', '2026-05-20', 'active', 25, NULL, NULL, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w4zppv8q7z77aewg5sf', '01krgq1v3bn468cgzyhr1h3ez8', '01krgq1qy4z2434tyem0yq66e0', '01krgq1w1fzbht0kw8ra5975ym', '2026-01-20', '2026-05-20', 'active', 42, NULL, NULL, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w6m2n167j5m62hg0wke', '01krgq1w17j9h5gvrbfccskj15', '01krgq1qy4z2434tyem0yq66e0', '01krgq1w1fzbht0kw8ra5975ym', '2026-01-20', '2026-05-20', 'active', 20, NULL, NULL, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w7y9djkhpz31x4gexp7', '01krgq1tgyb5tjvx7x8qvvdtct', '01krgq1qyg0jrr1cympad27n2x', '01krgq1w247s0jps6shzvez3k4', '2026-01-20', '2026-05-20', 'active', 24, NULL, NULL, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w91vh64ccy4swp64fx0', '01krgq1tsfwh9pt1pp95t0zm5p', '01krgq1qyg0jrr1cympad27n2x', '01krgq1w247s0jps6shzvez3k4', '2026-01-20', '2026-05-20', 'active', 69, NULL, NULL, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wa0w4fcfy6wad6t9qzw', '01krgq1vpjvw5xq819245m22vt', '01krgq1qyg0jrr1cympad27n2x', '01krgq1w247s0jps6shzvez3k4', '2026-01-20', '2026-05-20', 'active', 61, NULL, NULL, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1waxk1k1966qvsebqj5j', '01krgq1vd40e9p33q8gc0bmqd7', '01krgq1qymzzypdrw78mbex17n', '01krgq1w2mstgagh0cn0ymhenr', '2026-01-20', '2026-05-20', 'active', 46, NULL, NULL, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krh6r8hen4k0dpyc77v03ez1', '01krh6n1m6k88mfpfcq1m164zw', '01krgq1qy96xw88238ktyrn971', '01krgq1s1jcrcempqrq6s45wkk', '2026-05-13', NULL, 'active', 0, NULL, NULL, NULL, '2026-05-13 16:39:32', '2026-05-13 16:39:32');

-- --------------------------------------------------------

--
-- Table structure for table `enrollment_requests`
--

CREATE TABLE `enrollment_requests` (
  `id` char(26) NOT NULL,
  `protocol` varchar(255) NOT NULL,
  `user_id` char(26) NOT NULL,
  `course_id` varchar(255) DEFAULT NULL,
  `class_group_id` char(26) DEFAULT NULL,
  `class_schedule_id` char(26) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `responded_at` timestamp NULL DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `id` char(26) NOT NULL,
  `title` varchar(255) NOT NULL,
  `class_group_id` char(26) DEFAULT NULL,
  `course_id` char(26) DEFAULT NULL,
  `teacher_id` char(26) NOT NULL,
  `duration` smallint(5) UNSIGNED NOT NULL DEFAULT 60,
  `max_attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `status` enum('draft','published','closed') NOT NULL DEFAULT 'draft',
  `due_date` timestamp NULL DEFAULT NULL,
  `total_points` smallint(5) UNSIGNED NOT NULL DEFAULT 100,
  `pass_score` tinyint(3) UNSIGNED NOT NULL DEFAULT 50,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exams`
--

INSERT INTO `exams` (`id`, `title`, `class_group_id`, `course_id`, `teacher_id`, `duration`, `max_attempts`, `status`, `due_date`, `total_points`, `pass_score`, `created_at`, `updated_at`, `deleted_at`) VALUES
('01krgq1s3p01fndf6vadxdqrny', 'Teste de Avaliação B1 — Unidade 1', '01krgq1s1jcrcempqrq6s45wkk', '01krgq1qy96xw88238ktyrn971', '01krgq1rpw3ev4kw64j893ng7k', 60, 2, 'published', '2026-02-28 09:00:00', 100, 50, '2026-05-13 12:05:07', '2026-05-13 12:05:07', NULL),
('01krgq1wbpw6pqq4em3dtkg8kz', 'Teste Final A1 — Unidade 2', '01krgq1w1fzbht0kw8ra5975ym', '01krgq1qy4z2434tyem0yq66e0', '01krgq1sfx91pgs9pvfg8v8g90', 45, 2, 'published', '2026-03-15 13:00:00', 100, 50, '2026-05-13 12:05:10', '2026-05-13 12:05:10', NULL),
('01krgq1wd2fdak6pstek63yfsv', 'Avaliação Oil & Gas — Módulo 1', '01krgq1w247s0jps6shzvez3k4', '01krgq1qyg0jrr1cympad27n2x', '01krgq1sfx91pgs9pvfg8v8g90', 90, 1, 'published', '2026-03-20 08:00:00', 100, 60, '2026-05-13 12:05:10', '2026-05-13 12:05:10', NULL),
('01krh6x47z4f608d1se9s5m5wd', 'Novo Teste', NULL, NULL, '01krgq1rpw3ev4kw64j893ng7k', 60, 1, 'published', NULL, 20, 50, '2026-05-13 16:42:11', '2026-05-13 16:42:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `exam_answers`
--

CREATE TABLE `exam_answers` (
  `id` char(26) NOT NULL,
  `attempt_id` char(26) NOT NULL,
  `question_id` char(26) NOT NULL,
  `answer` int(11) NOT NULL DEFAULT -1,
  `is_correct` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exam_answers`
--

INSERT INTO `exam_answers` (`id`, `attempt_id`, `question_id`, `answer`, `is_correct`, `created_at`, `updated_at`) VALUES
('01krgq1s4fttt4h68aswxn1cdt', '01krgq1s4av86dfbembk55w9bn', '01krgq1s3v8nqy4bxgq6qkzs79', 2, 1, '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1s4j7rsbg4j04gfkxpt3', '01krgq1s4av86dfbembk55w9bn', '01krgq1s3yfppz02crvfw9dy3h', 1, 1, '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1s4nzvpqsdstea8326ha', '01krgq1s4av86dfbembk55w9bn', '01krgq1s413y7qvykg0xe9cxr7', 2, 1, '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1s4r0d4k6xhhtsjcgw1d', '01krgq1s4av86dfbembk55w9bn', '01krgq1s43kpyw7jwk0vntrkg4', 0, 1, '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1s4tyx5p55k93dt4qknk', '01krgq1s4av86dfbembk55w9bn', '01krgq1s469dnvtf9pp2451pz6', 0, NULL, '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1wc8fcetsqsh4ypjhks2', '01krgq1wc4dj6gr3fmnnf9a1f7', '01krgq1wbs0abc3eh8bzkvxb5s', 1, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wc9s2z9r4dayfpk4zpr', '01krgq1wc4dj6gr3fmnnf9a1f7', '01krgq1wbx9g79218tkt1zr820', 1, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wcbme268k2b5xgwaa5v', '01krgq1wc4dj6gr3fmnnf9a1f7', '01krgq1wby1ksrpy6ef0qvf70s', 2, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wcd3wpj8wdw24q8y2p5', '01krgq1wc4dj6gr3fmnnf9a1f7', '01krgq1wc0vx0jhy5rn5b5543g', 1, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wcenh7h0p47b80c8vg0', '01krgq1wc4dj6gr3fmnnf9a1f7', '01krgq1wc2cvcq5dpbkq0qx6kz', 0, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wchdr1tn7ep5gs6er9c', '01krgq1wcgnj1paz5agthjqhfs', '01krgq1wbs0abc3eh8bzkvxb5s', 1, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wckdjsq42553ss69pe2', '01krgq1wcgnj1paz5agthjqhfs', '01krgq1wbx9g79218tkt1zr820', 1, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wcmj66057rmarnjs4d8', '01krgq1wcgnj1paz5agthjqhfs', '01krgq1wby1ksrpy6ef0qvf70s', 2, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wcnp1430b5x8f8e93ke', '01krgq1wcgnj1paz5agthjqhfs', '01krgq1wc0vx0jhy5rn5b5543g', 1, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wcqhqxdsan60jrznpsc', '01krgq1wcgnj1paz5agthjqhfs', '01krgq1wc2cvcq5dpbkq0qx6kz', 0, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wctkbxdb221v7vxpd62', '01krgq1wcrmdd8j76ntp69kha2', '01krgq1wbs0abc3eh8bzkvxb5s', 1, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wcvm41zwyf91mqew46n', '01krgq1wcrmdd8j76ntp69kha2', '01krgq1wbx9g79218tkt1zr820', 1, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wcxxfmkjxb1by81mtas', '01krgq1wcrmdd8j76ntp69kha2', '01krgq1wby1ksrpy6ef0qvf70s', 2, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wcztk4gztqje30vv1v9', '01krgq1wcrmdd8j76ntp69kha2', '01krgq1wc0vx0jhy5rn5b5543g', 1, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wd0yx613rwt12p890bt', '01krgq1wcrmdd8j76ntp69kha2', '01krgq1wc2cvcq5dpbkq0qx6kz', 0, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wdgmzb63r9wtype7ebf', '01krgq1wdegjng7qb188wpz4kd', '01krgq1wd3spzczm1vz35mhdhz', 1, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wdhg5g4jkr2fb5zfjmr', '01krgq1wdegjng7qb188wpz4kd', '01krgq1wd52zczaw1b1d2dz96q', 0, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wdkrqy31fg6nvf2z7tq', '01krgq1wdegjng7qb188wpz4kd', '01krgq1wd64mse6ame08hakm2c', 1, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wdmt6s0jax74bjq0t2v', '01krgq1wdegjng7qb188wpz4kd', '01krgq1wd89pzmt3gw6148je14', 1, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wdpxmq5kfww71rwx8dk', '01krgq1wdegjng7qb188wpz4kd', '01krgq1wd91ajkt3eqtj8s9wzh', 0, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wdspbatqs82znb5dmse', '01krgq1wdqc0bkmx3hxvdhzn7z', '01krgq1wd3spzczm1vz35mhdhz', 1, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wdty01e2qx7bysk6xnp', '01krgq1wdqc0bkmx3hxvdhzn7z', '01krgq1wd52zczaw1b1d2dz96q', 0, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wdwm1vwft8tngp4n5x3', '01krgq1wdqc0bkmx3hxvdhzn7z', '01krgq1wd64mse6ame08hakm2c', 1, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wdzmc96d7jchmbs7h18', '01krgq1wdqc0bkmx3hxvdhzn7z', '01krgq1wd89pzmt3gw6148je14', 1, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1we1g8yvsf5skre4687g', '01krgq1wdqc0bkmx3hxvdhzn7z', '01krgq1wd91ajkt3eqtj8s9wzh', 0, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1we4t9jfkt0mt4emx6xf', '01krgq1we3mfcz6sg1ksrq0bxj', '01krgq1wd3spzczm1vz35mhdhz', 1, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1we6ypdp5sj676f0w931', '01krgq1we3mfcz6sg1ksrq0bxj', '01krgq1wd52zczaw1b1d2dz96q', 0, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1we7mq7vhp0d7vm10wh3', '01krgq1we3mfcz6sg1ksrq0bxj', '01krgq1wd64mse6ame08hakm2c', 1, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1we9cp7g4mgxaxanfzhf', '01krgq1we3mfcz6sg1ksrq0bxj', '01krgq1wd89pzmt3gw6148je14', 1, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1weam7aeqevsyvx9x8r8', '01krgq1we3mfcz6sg1ksrq0bxj', '01krgq1wd91ajkt3eqtj8s9wzh', 0, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10');

-- --------------------------------------------------------

--
-- Table structure for table `exam_assignments`
--

CREATE TABLE `exam_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `exam_id` char(26) NOT NULL,
  `assignable_type` varchar(255) NOT NULL,
  `assignable_id` char(26) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_attempts`
--

CREATE TABLE `exam_attempts` (
  `id` char(26) NOT NULL,
  `exam_id` char(26) NOT NULL,
  `student_id` char(26) NOT NULL,
  `status` enum('in_progress','completed','expired') NOT NULL DEFAULT 'in_progress',
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `submitted_at` timestamp NULL DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `passed` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exam_attempts`
--

INSERT INTO `exam_attempts` (`id`, `exam_id`, `student_id`, `status`, `started_at`, `submitted_at`, `score`, `passed`, `created_at`, `updated_at`) VALUES
('01krgq1s4av86dfbembk55w9bn', '01krgq1s3p01fndf6vadxdqrny', '01krgq1s17jyax0h8rq329nyw7', 'completed', '2026-05-13 13:05:07', '2026-02-25 08:55:00', 80.00, 1, '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1wc4dj6gr3fmnnf9a1f7', '01krgq1wbpw6pqq4em3dtkg8kz', '01krgq1t3s8vcbmwwtq85f1r98', 'completed', '2026-05-13 13:05:10', '2026-03-14 14:30:00', 81.00, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wcgnj1paz5agthjqhfs', '01krgq1wbpw6pqq4em3dtkg8kz', '01krgq1v3bn468cgzyhr1h3ez8', 'completed', '2026-05-13 13:05:10', '2026-03-14 14:30:00', 89.00, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wcrmdd8j76ntp69kha2', '01krgq1wbpw6pqq4em3dtkg8kz', '01krgq1w17j9h5gvrbfccskj15', 'completed', '2026-05-13 13:05:10', '2026-03-14 14:30:00', 58.00, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wdegjng7qb188wpz4kd', '01krgq1wd2fdak6pstek63yfsv', '01krgq1tgyb5tjvx7x8qvvdtct', 'completed', '2026-05-13 13:05:10', '2026-03-19 09:00:00', 77.00, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wdqc0bkmx3hxvdhzn7z', '01krgq1wd2fdak6pstek63yfsv', '01krgq1tsfwh9pt1pp95t0zm5p', 'completed', '2026-05-13 13:05:10', '2026-03-19 09:00:00', 81.00, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1we3mfcz6sg1ksrq0bxj', '01krgq1wd2fdak6pstek63yfsv', '01krgq1vpjvw5xq819245m22vt', 'completed', '2026-05-13 13:05:10', '2026-03-19 09:00:00', 69.00, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fouls`
--

CREATE TABLE `fouls` (
  `id` char(26) NOT NULL,
  `student_id` char(26) NOT NULL,
  `course_id` char(26) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` enum('AOA','EUR','USD') NOT NULL DEFAULT 'AOA',
  `status` enum('pending','overdue','paid') NOT NULL DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_by` char(26) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fouls`
--

INSERT INTO `fouls` (`id`, `student_id`, `course_id`, `description`, `amount`, `currency`, `status`, `paid_at`, `due_date`, `created_by`, `created_at`, `updated_at`) VALUES
('01krgq1s55w04gkz4h73s0wa5q', '01krgq1s17jyax0h8rq329nyw7', '01krgq1qy96xw88238ktyrn971', 'Falta não justificada — 24 de Janeiro de 2026', 2500.00, 'AOA', 'pending', NULL, '2026-02-05', '01krgq1rb3awk374qfnz3dbffw', '2026-05-13 12:05:07', '2026-05-13 12:05:07');

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` char(26) NOT NULL,
  `student_id` char(26) NOT NULL,
  `teacher_id` char(26) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'Exam',
  `course_id` char(26) DEFAULT NULL,
  `class_group_id` char(26) DEFAULT NULL,
  `grade` decimal(5,2) NOT NULL,
  `max_grade` decimal(5,2) NOT NULL DEFAULT 100.00,
  `date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `student_id`, `teacher_id`, `title`, `type`, `course_id`, `class_group_id`, `grade`, `max_grade`, `date`, `notes`, `created_at`, `updated_at`) VALUES
('01krgq1s4xw6tchyq2sht788dy', '01krgq1s17jyax0h8rq329nyw7', '01krgq1rpw3ev4kw64j893ng7k', 'Teste de Avaliação — Unidade 1', 'Exam', '01krgq1qy96xw88238ktyrn971', '01krgq1s1jcrcempqrq6s45wkk', 80.00, 100.00, '2026-02-25', 'Bom desempenho. Errou na questão de texto.', '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1s4z1p67dx8hddpqqtz2', '01krgq1s17jyax0h8rq329nyw7', '01krgq1rpw3ev4kw64j893ng7k', 'Participação Oral — Janeiro', 'Participation', '01krgq1qy96xw88238ktyrn971', '01krgq1s1jcrcempqrq6s45wkk', 85.00, 100.00, '2026-01-31', 'Boa participação. Vocabulário em expansão.', '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1s52twfvt6z8crc1dhpm', '01krgq1s17jyax0h8rq329nyw7', '01krgq1rpw3ev4kw64j893ng7k', 'Trabalho de Casa — Unidade 1', 'Homework', '01krgq1qy96xw88238ktyrn971', '01krgq1s1jcrcempqrq6s45wkk', 90.00, 100.00, '2026-02-10', 'Entregue no prazo. Excelente.', '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1w3px8vw3a38xpjzf78h', '01krgq1t3s8vcbmwwtq85f1r98', '01krgq1sfx91pgs9pvfg8v8g90', 'Teste de Avaliação — A1 Unidade 1', 'Exam', '01krgq1qy4z2434tyem0yq66e0', '01krgq1w1fzbht0kw8ra5975ym', 64.00, 100.00, '2026-02-25', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w3tmjnbk7v7j8g1bw6n', '01krgq1t3s8vcbmwwtq85f1r98', '01krgq1sfx91pgs9pvfg8v8g90', 'Participação Oral — A1 Janeiro', 'Participation', '01krgq1qy4z2434tyem0yq66e0', '01krgq1w1fzbht0kw8ra5975ym', 77.00, 100.00, '2026-01-31', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w3xkb2mpf6y7850yfrm', '01krgq1t3s8vcbmwwtq85f1r98', '01krgq1sfx91pgs9pvfg8v8g90', 'Trabalho de Casa — A1 Unidade 1', 'Homework', '01krgq1qy4z2434tyem0yq66e0', '01krgq1w1fzbht0kw8ra5975ym', 97.00, 100.00, '2026-02-10', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w5hqaz2p654v215xmay', '01krgq1v3bn468cgzyhr1h3ez8', '01krgq1sfx91pgs9pvfg8v8g90', 'Teste de Avaliação — A1 Unidade 1', 'Exam', '01krgq1qy4z2434tyem0yq66e0', '01krgq1w1fzbht0kw8ra5975ym', 70.00, 100.00, '2026-02-25', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w5n9bp15gg9vxx8dsde', '01krgq1v3bn468cgzyhr1h3ez8', '01krgq1sfx91pgs9pvfg8v8g90', 'Participação Oral — A1 Janeiro', 'Participation', '01krgq1qy4z2434tyem0yq66e0', '01krgq1w1fzbht0kw8ra5975ym', 62.00, 100.00, '2026-01-31', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w5r40mgqm2b0tarsra5', '01krgq1v3bn468cgzyhr1h3ez8', '01krgq1sfx91pgs9pvfg8v8g90', 'Trabalho de Casa — A1 Unidade 1', 'Homework', '01krgq1qy4z2434tyem0yq66e0', '01krgq1w1fzbht0kw8ra5975ym', 95.00, 100.00, '2026-02-10', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w71kze35frmxd6abm5g', '01krgq1w17j9h5gvrbfccskj15', '01krgq1sfx91pgs9pvfg8v8g90', 'Teste de Avaliação — A1 Unidade 1', 'Exam', '01krgq1qy4z2434tyem0yq66e0', '01krgq1w1fzbht0kw8ra5975ym', 92.00, 100.00, '2026-02-25', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w74gr58krjptj8azy8x', '01krgq1w17j9h5gvrbfccskj15', '01krgq1sfx91pgs9pvfg8v8g90', 'Participação Oral — A1 Janeiro', 'Participation', '01krgq1qy4z2434tyem0yq66e0', '01krgq1w1fzbht0kw8ra5975ym', 68.00, 100.00, '2026-01-31', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w77gptgaty1pjnwfcfw', '01krgq1w17j9h5gvrbfccskj15', '01krgq1sfx91pgs9pvfg8v8g90', 'Trabalho de Casa — A1 Unidade 1', 'Homework', '01krgq1qy4z2434tyem0yq66e0', '01krgq1w1fzbht0kw8ra5975ym', 79.00, 100.00, '2026-02-10', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w88w8bbzn5hm2t62vgn', '01krgq1tgyb5tjvx7x8qvvdtct', '01krgq1sfx91pgs9pvfg8v8g90', 'Teste de Avaliação — B2 Unidade 1', 'Exam', '01krgq1qyg0jrr1cympad27n2x', '01krgq1w247s0jps6shzvez3k4', 84.00, 100.00, '2026-02-25', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w8by2kpwzn7wrjmpzfd', '01krgq1tgyb5tjvx7x8qvvdtct', '01krgq1sfx91pgs9pvfg8v8g90', 'Participação Oral — B2 Janeiro', 'Participation', '01krgq1qyg0jrr1cympad27n2x', '01krgq1w247s0jps6shzvez3k4', 61.00, 100.00, '2026-01-31', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w8dq5kpfz9jp4fs1wbc', '01krgq1tgyb5tjvx7x8qvvdtct', '01krgq1sfx91pgs9pvfg8v8g90', 'Trabalho de Casa — B2 Unidade 1', 'Homework', '01krgq1qyg0jrr1cympad27n2x', '01krgq1w247s0jps6shzvez3k4', 86.00, 100.00, '2026-02-10', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w9adw85hrgysxch75d3', '01krgq1tsfwh9pt1pp95t0zm5p', '01krgq1sfx91pgs9pvfg8v8g90', 'Teste de Avaliação — B2 Unidade 1', 'Exam', '01krgq1qyg0jrr1cympad27n2x', '01krgq1w247s0jps6shzvez3k4', 60.00, 100.00, '2026-02-25', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w9c82c3a3dzgtwtan98', '01krgq1tsfwh9pt1pp95t0zm5p', '01krgq1sfx91pgs9pvfg8v8g90', 'Participação Oral — B2 Janeiro', 'Participation', '01krgq1qyg0jrr1cympad27n2x', '01krgq1w247s0jps6shzvez3k4', 95.00, 100.00, '2026-01-31', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w9emq7xbkvv0vjegs2h', '01krgq1tsfwh9pt1pp95t0zm5p', '01krgq1sfx91pgs9pvfg8v8g90', 'Trabalho de Casa — B2 Unidade 1', 'Homework', '01krgq1qyg0jrr1cympad27n2x', '01krgq1w247s0jps6shzvez3k4', 91.00, 100.00, '2026-02-10', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wa80wp163tk86ytz5yd', '01krgq1vpjvw5xq819245m22vt', '01krgq1sfx91pgs9pvfg8v8g90', 'Teste de Avaliação — B2 Unidade 1', 'Exam', '01krgq1qyg0jrr1cympad27n2x', '01krgq1w247s0jps6shzvez3k4', 83.00, 100.00, '2026-02-25', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wabkwwmmcvt3geh7zge', '01krgq1vpjvw5xq819245m22vt', '01krgq1sfx91pgs9pvfg8v8g90', 'Participação Oral — B2 Janeiro', 'Participation', '01krgq1qyg0jrr1cympad27n2x', '01krgq1w247s0jps6shzvez3k4', 76.00, 100.00, '2026-01-31', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wadh22jnqz01zd5jycq', '01krgq1vpjvw5xq819245m22vt', '01krgq1sfx91pgs9pvfg8v8g90', 'Trabalho de Casa — B2 Unidade 1', 'Homework', '01krgq1qyg0jrr1cympad27n2x', '01krgq1w247s0jps6shzvez3k4', 74.00, 100.00, '2026-02-10', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wb4qgwk0s157mjex56d', '01krgq1vd40e9p33q8gc0bmqd7', '01krgq1stkx4add98k5nrsvnx6', 'Teste de Avaliação — B1 Unidade 1', 'Exam', '01krgq1qymzzypdrw78mbex17n', '01krgq1w2mstgagh0cn0ymhenr', 70.00, 100.00, '2026-02-25', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wb54gwtv1sf0ngtnejn', '01krgq1vd40e9p33q8gc0bmqd7', '01krgq1stkx4add98k5nrsvnx6', 'Participação Oral — B1 Janeiro', 'Participation', '01krgq1qymzzypdrw78mbex17n', '01krgq1w2mstgagh0cn0ymhenr', 75.00, 100.00, '2026-01-31', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wb757w8db0e6t9z8k1e', '01krgq1vd40e9p33q8gc0bmqd7', '01krgq1stkx4add98k5nrsvnx6', 'Trabalho de Casa — B1 Unidade 1', 'Homework', '01krgq1qymzzypdrw78mbex17n', '01krgq1w2mstgagh0cn0ymhenr', 86.00, 100.00, '2026-02-10', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `id` char(26) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('PDF','Video','Image','Document','Audio','Link','Other') NOT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `size_bytes` bigint(20) UNSIGNED DEFAULT NULL,
  `storage_key` varchar(255) DEFAULT NULL,
  `external_url` varchar(255) DEFAULT NULL,
  `class_group_id` char(26) DEFAULT NULL,
  `course_id` char(26) DEFAULT NULL,
  `uploaded_by_id` char(26) NOT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `views` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `downloads` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`id`, `title`, `description`, `type`, `mime_type`, `size_bytes`, `storage_key`, `external_url`, `class_group_id`, `course_id`, `uploaded_by_id`, `is_published`, `views`, `downloads`, `created_at`, `updated_at`, `deleted_at`) VALUES
('01krgszpmf0xttpdrwkbvpgc4q', 'teste', 'dfhujniulgf', 'Image', 'image/jpeg', 34305, 'materials/404de88a-26fa-46b0-a904-fe9aaa753525/Salesroom Ui - Dmitriy Haraberush.jpg', NULL, '01krgq1s1jcrcempqrq6s45wkk', NULL, '01krgq1rpbc47ckjsxm97x9e1w', 1, 2, 1, '2026-05-13 12:56:24', '2026-05-16 11:01:55', NULL),
('01krpbtszmqzp4jgw5166gec3v', 'sss', 'cvcccc', 'PDF', 'application/pdf', 2559617, 'materials/64dc6918-4961-4c6d-b098-807c2eaf0a49/Manual_invoice_nduenga_v3.pdf', NULL, NULL, NULL, '01krgq1rpbc47ckjsxm97x9e1w', 1, 0, 1, '2026-05-15 16:44:30', '2026-05-15 16:44:49', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_01_01_000000_create_permission_tables', 1),
(5, '2026_01_01_000001_create_users_table', 1),
(6, '2026_01_01_000002_create_personal_access_tokens_table', 1),
(7, '2026_01_01_000003_create_student_profiles_table', 1),
(8, '2026_01_01_000004_create_teacher_profiles_table', 1),
(9, '2026_01_01_000005_create_admin_profiles_table', 1),
(10, '2026_01_01_000006_create_courses_table', 1),
(11, '2026_01_01_000007_create_class_groups_table', 1),
(12, '2026_01_01_000008_create_class_schedules_table', 1),
(13, '2026_01_01_000009_create_enrollments_table', 1),
(14, '2026_01_01_000010_create_attendances_table', 1),
(15, '2026_01_01_000011_create_exams_table', 1),
(16, '2026_01_01_000012_create_questions_table', 1),
(17, '2026_01_01_000013_create_exam_attempts_table', 1),
(18, '2026_01_01_000014_create_exam_answers_table', 1),
(19, '2026_01_01_000015_create_grades_table', 1),
(20, '2026_01_01_000016_create_payments_table', 1),
(21, '2026_01_01_000017_create_fouls_table', 1),
(22, '2026_01_01_000018_create_documents_table', 1),
(23, '2026_01_01_000019_create_notifications_table', 1),
(24, '2026_01_01_000020_create_system_settings_table', 1),
(25, '2026_01_01_000021_create_newsletter_subscribers_table', 1),
(26, '2026_01_01_000022_create_contact_form_submissions_table', 1),
(27, '2026_05_01_205140_create_activity_log_table', 1),
(28, '2026_05_01_205141_add_event_column_to_activity_log_table', 1),
(29, '2026_05_01_205142_add_batch_uuid_column_to_activity_log_table', 1),
(30, '2026_05_03_000001_create_chat_tables', 1),
(31, '2026_05_03_000001_create_materials_table', 1),
(32, '2026_05_11_000001_add_admin_fields_to_courses_table', 1),
(33, '2026_05_11_000002_add_username_and_cpf_to_users_table', 1),
(34, '2026_05_11_000003_create_enrollment_requests_table', 1),
(35, '2026_05_11_103441_create_blog_categories_table', 1),
(36, '2026_05_11_103449_create_blog_posts_table', 1),
(37, '2026_05_12_000001_make_enrollment_requests_course_nullable', 1),
(38, '2026_05_12_102249_add_payment_fields_to_enrollments_table', 1),
(39, '2026_05_12_124358_add_proof_url_to_payments_table', 1),
(40, '2026_05_12_130726_add_rejection_reason_to_payments_table', 1),
(41, '2026_05_12_130804_update_payment_status_enum', 1),
(42, '2026_05_13_120303_create_exam_assignments_table', 1),
(43, '2026_05_13_130317_create_assignments_table', 1),
(44, '2026_05_13_130326_create_assignment_submissions_table', 1),
(45, '2026_05_13_134359_create_testimonials_table', 2),
(46, '2026_05_16_000001_add_attachment_url_to_assignments_table', 3);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` char(26) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` varchar(26) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', '01krgq1rb3awk374qfnz3dbffw'),
(2, 'App\\Models\\User', '01krgq1rpbc47ckjsxm97x9e1w'),
(2, 'App\\Models\\User', '01krgq1sffpwqrctrbe8gvwbh1'),
(2, 'App\\Models\\User', '01krgq1sta6t3bvpy0j58w6tmc'),
(3, 'App\\Models\\User', '01krgq1s0wfzpwk8tgywfvxp88'),
(3, 'App\\Models\\User', '01krgq1t3a7rf9c9hd17bzxav5'),
(3, 'App\\Models\\User', '01krgq1tgr6ayzfa4fg3acqh21'),
(3, 'App\\Models\\User', '01krgq1ts6s3sdgppn589qwah0'),
(3, 'App\\Models\\User', '01krgq1v30trtqhqqgdwwxq56z'),
(3, 'App\\Models\\User', '01krgq1vcv1bdnw47mv8cnx5w9'),
(3, 'App\\Models\\User', '01krgq1vp7rhf8aah1mey6fgds'),
(3, 'App\\Models\\User', '01krgq1w0wn6rj9yh59rn9jek0'),
(3, 'App\\Models\\User', '01krh6a6gpjtx9nksb7p6emd3k'),
(3, 'App\\Models\\User', '01krh6n1kn0h9kg2zw9gsm74ey');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` char(26) NOT NULL,
  `email` varchar(255) NOT NULL,
  `language` enum('pt','en') NOT NULL DEFAULT 'pt',
  `country` varchar(2) DEFAULT NULL,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `unsubscribed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` char(26) NOT NULL,
  `student_id` char(26) NOT NULL,
  `course_id` char(26) DEFAULT NULL,
  `enrollment_id` char(26) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` enum('AOA','EUR','USD') NOT NULL DEFAULT 'AOA',
  `amount_aoa` decimal(12,2) DEFAULT NULL,
  `due_date` date NOT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `method` enum('credit_card','bank_transfer','mpesa','multicaixa','cash','other') DEFAULT NULL,
  `transaction_ref` varchar(255) DEFAULT NULL,
  `proof_url` varchar(255) DEFAULT NULL,
  `receipt_url` varchar(255) DEFAULT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `notes` text DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `student_id`, `course_id`, `enrollment_id`, `description`, `amount`, `currency`, `amount_aoa`, `due_date`, `paid_at`, `status`, `method`, `transaction_ref`, `proof_url`, `receipt_url`, `invoice_number`, `notes`, `rejection_reason`, `created_at`, `updated_at`) VALUES
('01krgq1s2b40dvamqtvmc1cg3e', '01krgq1s17jyax0h8rq329nyw7', '01krgq1qy96xw88238ktyrn971', '01krgq1s255qy48jax9d9jvyth', 'Mensalidade Janeiro 2026 — Inglês B1', 45000.00, 'AOA', 45000.00, '2026-01-20', '2026-01-18 09:30:00', 'paid', 'multicaixa', 'MCX-2026-0118-001', NULL, NULL, 'OLS-2026-0001', 'Pago antecipado.', NULL, '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1s2enbwx0pqksmzsaqh9', '01krgq1s17jyax0h8rq329nyw7', '01krgq1qy96xw88238ktyrn971', '01krgq1s255qy48jax9d9jvyth', 'Mensalidade Fevereiro 2026 — Inglês B1', 45000.00, 'AOA', 45000.00, '2026-02-20', '2026-02-19 08:15:00', 'paid', 'bank_transfer', 'BNK-2026-0219-002', NULL, NULL, 'OLS-2026-0002', NULL, NULL, '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1s2k7dg2rwbs2wyvzk4c', '01krgq1s17jyax0h8rq329nyw7', '01krgq1qy96xw88238ktyrn971', '01krgq1s255qy48jax9d9jvyth', 'Mensalidade Março 2026 — Inglês B1', 45000.00, 'AOA', 45000.00, '2026-03-20', '2026-05-12 23:00:00', 'paid', 'bank_transfer', NULL, NULL, NULL, 'OLS-2026-0003', 'Aguarda pagamento.', NULL, '2026-05-13 12:05:07', '2026-05-13 16:36:37'),
('01krgq1w37jq87qb46cveekkfx', '01krgq1t3s8vcbmwwtq85f1r98', '01krgq1qy4z2434tyem0yq66e0', '01krgq1w30073dknzcmnxehm3d', 'Mensalidade Janeiro 2026 — Inglês — Comunicação Diária A1', 35000.00, 'AOA', 35000.00, '2026-01-20', '2026-01-18 09:00:00', 'paid', 'multicaixa', 'MCX-2026-0004', NULL, NULL, 'OLS-2026-0004', NULL, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w3b10fjxsgfb85kc667', '01krgq1t3s8vcbmwwtq85f1r98', '01krgq1qy4z2434tyem0yq66e0', '01krgq1w30073dknzcmnxehm3d', 'Mensalidade Fevereiro 2026 — Inglês — Comunicação Diária A1', 35000.00, 'AOA', 35000.00, '2026-02-20', '2026-02-17 09:00:00', 'paid', 'multicaixa', 'MCX-2026-0005', NULL, NULL, 'OLS-2026-0005', NULL, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w3gy3sxhce2hhwtr2tm', '01krgq1t3s8vcbmwwtq85f1r98', '01krgq1qy4z2434tyem0yq66e0', '01krgq1w30073dknzcmnxehm3d', 'Mensalidade Março 2026 — Inglês — Comunicação Diária A1', 35000.00, 'AOA', 35000.00, '2026-03-20', '2026-03-18 09:00:00', 'paid', 'multicaixa', 'MCX-2026-0006', NULL, NULL, 'OLS-2026-0006', NULL, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w53h55fpk346nz56zde', '01krgq1v3bn468cgzyhr1h3ez8', '01krgq1qy4z2434tyem0yq66e0', '01krgq1w4zppv8q7z77aewg5sf', 'Mensalidade Janeiro 2026 — Inglês — Comunicação Diária A1', 35000.00, 'AOA', 35000.00, '2026-01-20', '2026-01-18 09:00:00', 'paid', 'multicaixa', 'MCX-2026-0007', NULL, NULL, 'OLS-2026-0007', NULL, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w58hweft1c0gk4mfbnn', '01krgq1v3bn468cgzyhr1h3ez8', '01krgq1qy4z2434tyem0yq66e0', '01krgq1w4zppv8q7z77aewg5sf', 'Mensalidade Fevereiro 2026 — Inglês — Comunicação Diária A1', 35000.00, 'AOA', 35000.00, '2026-02-20', NULL, 'overdue', NULL, NULL, NULL, NULL, 'OLS-2026-0008', 'Pagamento em atraso. Contactar aluno.', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w5c43gvty5c123zxype', '01krgq1v3bn468cgzyhr1h3ez8', '01krgq1qy4z2434tyem0yq66e0', '01krgq1w4zppv8q7z77aewg5sf', 'Mensalidade Março 2026 — Inglês — Comunicação Diária A1', 35000.00, 'AOA', 35000.00, '2026-03-20', NULL, 'overdue', NULL, NULL, NULL, NULL, 'OLS-2026-0009', 'Pagamento em atraso. Contactar aluno.', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w6q45bc73re1k6yjwpy', '01krgq1w17j9h5gvrbfccskj15', '01krgq1qy4z2434tyem0yq66e0', '01krgq1w6m2n167j5m62hg0wke', 'Mensalidade Janeiro 2026 — Inglês — Comunicação Diária A1', 35000.00, 'AOA', 35000.00, '2026-01-20', '2026-01-18 09:00:00', 'paid', 'multicaixa', 'MCX-2026-0010', NULL, NULL, 'OLS-2026-0010', NULL, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w6t9ajgw106mjhvxvk5', '01krgq1w17j9h5gvrbfccskj15', '01krgq1qy4z2434tyem0yq66e0', '01krgq1w6m2n167j5m62hg0wke', 'Mensalidade Fevereiro 2026 — Inglês — Comunicação Diária A1', 35000.00, 'AOA', 35000.00, '2026-02-20', '2026-02-17 09:00:00', 'paid', 'multicaixa', 'MCX-2026-0011', NULL, NULL, 'OLS-2026-0011', NULL, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w6y38f9xshsbc029ctj', '01krgq1w17j9h5gvrbfccskj15', '01krgq1qy4z2434tyem0yq66e0', '01krgq1w6m2n167j5m62hg0wke', 'Mensalidade Março 2026 — Inglês — Comunicação Diária A1', 35000.00, 'AOA', 35000.00, '2026-03-20', NULL, 'pending', NULL, NULL, NULL, NULL, 'OLS-2026-0012', NULL, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w81kxdd79rymst3r1qx', '01krgq1tgyb5tjvx7x8qvvdtct', '01krgq1qyg0jrr1cympad27n2x', '01krgq1w7y9djkhpz31x4gexp7', 'Mensalidade Janeiro 2026 — Inglês para Petróleo e Gás', 120000.00, 'AOA', 120000.00, '2026-01-20', '2026-01-18 09:00:00', 'paid', 'multicaixa', 'MCX-2026-0013', NULL, NULL, 'OLS-2026-0013', NULL, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w83w4c3krjkq7ezv0xc', '01krgq1tgyb5tjvx7x8qvvdtct', '01krgq1qyg0jrr1cympad27n2x', '01krgq1w7y9djkhpz31x4gexp7', 'Mensalidade Fevereiro 2026 — Inglês para Petróleo e Gás', 120000.00, 'AOA', 120000.00, '2026-02-20', '2026-02-17 09:00:00', 'paid', 'multicaixa', 'MCX-2026-0014', NULL, NULL, 'OLS-2026-0014', NULL, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w86pgf624rcazp4tw3m', '01krgq1tgyb5tjvx7x8qvvdtct', '01krgq1qyg0jrr1cympad27n2x', '01krgq1w7y9djkhpz31x4gexp7', 'Mensalidade Março 2026 — Inglês para Petróleo e Gás', 120000.00, 'AOA', 120000.00, '2026-03-20', NULL, 'pending', NULL, NULL, NULL, NULL, 'OLS-2026-0015', NULL, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w936gbkc471dapj7sas', '01krgq1tsfwh9pt1pp95t0zm5p', '01krgq1qyg0jrr1cympad27n2x', '01krgq1w91vh64ccy4swp64fx0', 'Mensalidade Janeiro 2026 — Inglês para Petróleo e Gás', 120000.00, 'AOA', 120000.00, '2026-01-20', '2026-01-18 09:00:00', 'paid', 'multicaixa', 'MCX-2026-0016', NULL, NULL, 'OLS-2026-0016', NULL, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w95x2sz35dcp7gbkajs', '01krgq1tsfwh9pt1pp95t0zm5p', '01krgq1qyg0jrr1cympad27n2x', '01krgq1w91vh64ccy4swp64fx0', 'Mensalidade Fevereiro 2026 — Inglês para Petróleo e Gás', 120000.00, 'AOA', 120000.00, '2026-02-20', NULL, 'overdue', NULL, NULL, NULL, NULL, 'OLS-2026-0017', 'Pagamento em atraso. Contactar aluno.', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1w9831yykh1qzssh7fzg', '01krgq1tsfwh9pt1pp95t0zm5p', '01krgq1qyg0jrr1cympad27n2x', '01krgq1w91vh64ccy4swp64fx0', 'Mensalidade Março 2026 — Inglês para Petróleo e Gás', 120000.00, 'AOA', 120000.00, '2026-03-20', NULL, 'overdue', NULL, NULL, NULL, NULL, 'OLS-2026-0018', 'Pagamento em atraso. Contactar aluno.', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wa2n6wsb1y6paexnckz', '01krgq1vpjvw5xq819245m22vt', '01krgq1qyg0jrr1cympad27n2x', '01krgq1wa0w4fcfy6wad6t9qzw', 'Mensalidade Janeiro 2026 — Inglês para Petróleo e Gás', 120000.00, 'AOA', 120000.00, '2026-01-20', '2026-01-18 09:00:00', 'paid', 'multicaixa', 'MCX-2026-0019', NULL, NULL, 'OLS-2026-0019', NULL, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wa4cze8a4vfds6vapr7', '01krgq1vpjvw5xq819245m22vt', '01krgq1qyg0jrr1cympad27n2x', '01krgq1wa0w4fcfy6wad6t9qzw', 'Mensalidade Fevereiro 2026 — Inglês para Petróleo e Gás', 120000.00, 'AOA', 120000.00, '2026-02-20', NULL, 'overdue', NULL, NULL, NULL, NULL, 'OLS-2026-0020', 'Pagamento em atraso. Contactar aluno.', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wa69ccda7cak8sww4ys', '01krgq1vpjvw5xq819245m22vt', '01krgq1qyg0jrr1cympad27n2x', '01krgq1wa0w4fcfy6wad6t9qzw', 'Mensalidade Março 2026 — Inglês para Petróleo e Gás', 120000.00, 'AOA', 120000.00, '2026-03-20', NULL, 'overdue', NULL, NULL, NULL, NULL, 'OLS-2026-0021', 'Pagamento em atraso. Contactar aluno.', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wazaygh8gzffrr434zd', '01krgq1vd40e9p33q8gc0bmqd7', '01krgq1qymzzypdrw78mbex17n', '01krgq1waxk1k1966qvsebqj5j', 'Mensalidade Janeiro 2026 — Inglês para Banca e Finanças', 95000.00, 'AOA', 95000.00, '2026-01-20', '2026-01-18 09:00:00', 'paid', 'multicaixa', 'MCX-2026-0022', NULL, NULL, 'OLS-2026-0022', NULL, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wb0ae9be74wef8zrqta', '01krgq1vd40e9p33q8gc0bmqd7', '01krgq1qymzzypdrw78mbex17n', '01krgq1waxk1k1966qvsebqj5j', 'Mensalidade Fevereiro 2026 — Inglês para Banca e Finanças', 95000.00, 'AOA', 95000.00, '2026-02-20', '2026-02-17 09:00:00', 'paid', 'multicaixa', 'MCX-2026-0023', NULL, NULL, 'OLS-2026-0023', NULL, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wb2f3vtjyanp6n7ebs0', '01krgq1vd40e9p33q8gc0bmqd7', '01krgq1qymzzypdrw78mbex17n', '01krgq1waxk1k1966qvsebqj5j', 'Mensalidade Março 2026 — Inglês para Banca e Finanças', 95000.00, 'AOA', 95000.00, '2026-03-20', '2026-03-18 09:00:00', 'paid', 'multicaixa', 'MCX-2026-0024', NULL, NULL, 'OLS-2026-0024', NULL, NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krh73rh20hcv73jgg1rpszwa', '01krh6n1m6k88mfpfcq1m164zw', '01krgq1qy96xw88238ktyrn971', '01krh6r8hen4k0dpyc77v03ez1', 'Mensalidade', 75000.00, 'AOA', NULL, '2026-05-13', '2026-05-12 23:00:00', 'paid', 'bank_transfer', NULL, NULL, NULL, 'INV-2026-0025', NULL, NULL, '2026-05-13 16:45:49', '2026-05-13 16:46:00');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'manage_users', 'web', '2026-05-13 12:05:05', '2026-05-13 12:05:05'),
(2, 'view_users', 'web', '2026-05-13 12:05:05', '2026-05-13 12:05:05'),
(3, 'manage_courses', 'web', '2026-05-13 12:05:05', '2026-05-13 12:05:05'),
(4, 'view_courses', 'web', '2026-05-13 12:05:05', '2026-05-13 12:05:05'),
(5, 'manage_classes', 'web', '2026-05-13 12:05:05', '2026-05-13 12:05:05'),
(6, 'view_classes', 'web', '2026-05-13 12:05:05', '2026-05-13 12:05:05'),
(7, 'manage_payments', 'web', '2026-05-13 12:05:05', '2026-05-13 12:05:05'),
(8, 'view_payments', 'web', '2026-05-13 12:05:05', '2026-05-13 12:05:05'),
(9, 'manage_exams', 'web', '2026-05-13 12:05:05', '2026-05-13 12:05:05'),
(10, 'view_exams', 'web', '2026-05-13 12:05:05', '2026-05-13 12:05:05'),
(11, 'manage_grades', 'web', '2026-05-13 12:05:05', '2026-05-13 12:05:05'),
(12, 'view_grades', 'web', '2026-05-13 12:05:05', '2026-05-13 12:05:05'),
(13, 'manage_documents', 'web', '2026-05-13 12:05:05', '2026-05-13 12:05:05'),
(14, 'view_documents', 'web', '2026-05-13 12:05:05', '2026-05-13 12:05:05'),
(15, 'manage_settings', 'web', '2026-05-13 12:05:05', '2026-05-13 12:05:05'),
(16, 'view_reports', 'web', '2026-05-13 12:05:05', '2026-05-13 12:05:05'),
(17, 'manage_fouls', 'web', '2026-05-13 12:05:05', '2026-05-13 12:05:05');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` char(26) NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', '01krgq1rpbc47ckjsxm97x9e1w', 'api-token', 'cad531d00a6e99e4d2a608141714a864f78d2f8e541821c2101bb10edb046eed', '[\"*\"]', NULL, '2026-06-12 12:18:47', '2026-05-13 12:18:47', '2026-05-13 12:18:47'),
(7, 'App\\Models\\User', '01krgq1rpbc47ckjsxm97x9e1w', 'api-token', 'f3ebb00fa22b011d45d6549aef915656c5a97394f189662ea3041a06a5fcf460', '[\"*\"]', '2026-05-13 15:15:50', '2026-06-12 14:08:58', '2026-05-13 14:08:58', '2026-05-13 15:15:50'),
(12, 'App\\Models\\User', '01krgq1rb3awk374qfnz3dbffw', 'api-token', 'ddf7b28e67d2502baee08f197ba5eade5c5118fae603dc6ec2e73836ce03e77f', '[\"*\"]', '2026-05-13 16:49:23', '2026-06-12 16:34:02', '2026-05-13 16:34:02', '2026-05-13 16:49:23'),
(14, 'App\\Models\\User', '01krh6n1kn0h9kg2zw9gsm74ey', 'api-token', 'af9c2a045e294613b619f902d63ac82a781bd3aa95b4f8aaf337f346d45796c2', '[\"*\"]', '2026-05-13 16:48:29', '2026-06-12 16:45:05', '2026-05-13 16:45:05', '2026-05-13 16:48:29'),
(15, 'App\\Models\\User', '01krgq1rpbc47ckjsxm97x9e1w', 'api-token', '635b2536fbf0c024144682c315dc2175ddc3925dc0595e5b999ce66747964e02', '[\"*\"]', '2026-05-16 10:26:20', '2026-06-14 16:40:01', '2026-05-15 16:40:01', '2026-05-16 10:26:20'),
(20, 'App\\Models\\User', '01krgq1rb3awk374qfnz3dbffw', 'api-token', '825303fa479f5dc098249397d987d665a24c487bcccc10d745dc94900c1b4f08', '[\"*\"]', '2026-05-16 12:27:57', '2026-06-15 11:30:58', '2026-05-16 11:30:58', '2026-05-16 12:27:57'),
(23, 'App\\Models\\User', '01krgq1rpbc47ckjsxm97x9e1w', 'api-token', '1f152c88f5b87e5df045a5091f96287de942061b4b0550fb8c5ecc25efd8180b', '[\"*\"]', '2026-05-16 12:31:13', '2026-06-15 12:29:31', '2026-05-16 12:29:31', '2026-05-16 12:31:13');

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` char(26) NOT NULL,
  `exam_id` char(26) NOT NULL,
  `text` text NOT NULL,
  `type` enum('multiple_choice','true_false','open_text') NOT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]' CHECK (json_valid(`options`)),
  `correct` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `points` tinyint(3) UNSIGNED NOT NULL DEFAULT 10,
  `order` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `exam_id`, `text`, `type`, `options`, `correct`, `points`, `order`, `created_at`, `updated_at`) VALUES
('01krgq1s3v8nqy4bxgq6qkzs79', '01krgq1s3p01fndf6vadxdqrny', 'Which sentence is grammatically correct?', 'multiple_choice', '[\"She don\'t like coffee.\",\"She doesn\'t likes coffee.\",\"She doesn\'t like coffee.\",\"She not like coffee.\"]', 2, 20, 1, '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1s3yfppz02crvfw9dy3h', '01krgq1s3p01fndf6vadxdqrny', 'English is the official language of Angola.', 'true_false', '[\"True\",\"False\"]', 1, 20, 2, '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1s413y7qvykg0xe9cxr7', '01krgq1s3p01fndf6vadxdqrny', 'What is the past tense of \"go\"?', 'multiple_choice', '[\"goed\",\"gone\",\"went\",\"going\"]', 2, 20, 3, '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1s43kpyw7jwk0vntrkg4', '01krgq1s3p01fndf6vadxdqrny', '\"I have been studying English for 2 years.\" — This sentence uses the Present Perfect Continuous tense.', 'true_false', '[\"True\",\"False\"]', 0, 20, 4, '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1s469dnvtf9pp2451pz6', '01krgq1s3p01fndf6vadxdqrny', 'Describe in 3–5 sentences your daily routine in English.', 'open_text', '[]', 0, 20, 5, '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1wbs0abc3eh8bzkvxb5s', '01krgq1wbpw6pqq4em3dtkg8kz', 'My name ___ Maria.', 'multiple_choice', '[\"am\",\"is\",\"are\",\"be\"]', 1, 20, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wbx9g79218tkt1zr820', '01krgq1wbpw6pqq4em3dtkg8kz', '\"Good morning\" is a greeting used in the evening.', 'true_false', '[\"True\",\"False\"]', 1, 20, 2, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wby1ksrpy6ef0qvf70s', '01krgq1wbpw6pqq4em3dtkg8kz', 'How do you say \"obrigado\" in English?', 'multiple_choice', '[\"please\",\"sorry\",\"thank you\",\"hello\"]', 2, 20, 3, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wc0vx0jhy5rn5b5543g', '01krgq1wbpw6pqq4em3dtkg8kz', 'The plural of \"child\" is \"childs\".', 'true_false', '[\"True\",\"False\"]', 1, 20, 4, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wc2cvcq5dpbkq0qx6kz', '01krgq1wbpw6pqq4em3dtkg8kz', 'Write 3 sentences about yourself in English.', 'open_text', '[]', 0, 20, 5, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wd3spzczm1vz35mhdhz', '01krgq1wd2fdak6pstek63yfsv', 'What does \"upstream\" mean in the oil & gas industry?', 'multiple_choice', '[\"Refining\",\"Exploration and production\",\"Distribution\",\"Retail sales\"]', 1, 20, 1, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wd52zczaw1b1d2dz96q', '01krgq1wd2fdak6pstek63yfsv', 'An HSE report focuses on Health, Safety and Environment.', 'true_false', '[\"True\",\"False\"]', 0, 20, 2, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wd64mse6ame08hakm2c', '01krgq1wd2fdak6pstek63yfsv', 'Which word means \"perfuração\"?', 'multiple_choice', '[\"flaring\",\"drilling\",\"piping\",\"refining\"]', 1, 20, 3, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wd89pzmt3gw6148je14', '01krgq1wd2fdak6pstek63yfsv', 'A \"wellhead\" is located offshore only.', 'true_false', '[\"True\",\"False\"]', 1, 20, 4, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krgq1wd91ajkt3eqtj8s9wzh', '01krgq1wd2fdak6pstek63yfsv', 'Write an email to your supervisor requesting a safety inspection of the rig.', 'open_text', '[]', 0, 20, 5, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krh6x4838rn66hns037vx4j7', '01krh6x47z4f608d1se9s5m5wd', 'Questão', 'multiple_choice', '[\"sss\",\"ddd\",\"ffff\",\"dddd\"]', 0, 10, 1, '2026-05-13 16:42:11', '2026-05-13 16:42:11'),
('01krh6x484rb74m6wqjdj07ffc', '01krh6x47z4f608d1se9s5m5wd', 'fggggg', 'multiple_choice', '[\"sss\",\"ddd\",\"ffff\",\"dddd\"]', 2, 10, 2, '2026-05-13 16:42:11', '2026-05-13 16:42:11');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'web', '2026-05-13 12:05:05', '2026-05-13 12:05:05'),
(2, 'teacher', 'web', '2026-05-13 12:05:05', '2026-05-13 12:05:05'),
(3, 'student', 'web', '2026-05-13 12:05:05', '2026-05-13 12:05:05');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(4, 3),
(5, 1),
(6, 1),
(6, 2),
(7, 1),
(8, 1),
(8, 3),
(9, 1),
(9, 2),
(10, 1),
(10, 2),
(10, 3),
(11, 1),
(11, 2),
(12, 1),
(12, 2),
(12, 3),
(13, 1),
(13, 2),
(14, 1),
(14, 2),
(14, 3),
(15, 1),
(16, 1),
(17, 1),
(17, 2);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_profiles`
--

CREATE TABLE `student_profiles` (
  `id` char(26) NOT NULL,
  `user_id` char(26) NOT NULL,
  `student_code` varchar(255) NOT NULL,
  `current_level` enum('A1','A2','B1','B2','C1') DEFAULT NULL,
  `enrollment_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_profiles`
--

INSERT INTO `student_profiles` (`id`, `user_id`, `student_code`, `current_level`, `enrollment_date`, `notes`, `created_at`, `updated_at`) VALUES
('01krgq1s17jyax0h8rq329nyw7', '01krgq1s0wfzpwk8tgywfvxp88', 'STU-001', 'B1', '2026-01-15', 'Aluno motivado. Tem dificuldades com tempos verbais no passado. Recomendado reforço em listening.', '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1t3s8vcbmwwtq85f1r98', '01krgq1t3a7rf9c9hd17bzxav5', 'STU-002', 'A1', '2026-01-20', NULL, '2026-05-13 12:05:08', '2026-05-13 12:05:08'),
('01krgq1tgyb5tjvx7x8qvvdtct', '01krgq1tgr6ayzfa4fg3acqh21', 'STU-003', 'B1', '2026-01-20', NULL, '2026-05-13 12:05:08', '2026-05-13 12:05:08'),
('01krgq1tsfwh9pt1pp95t0zm5p', '01krgq1ts6s3sdgppn589qwah0', 'STU-004', 'B2', '2026-01-20', NULL, '2026-05-13 12:05:08', '2026-05-13 12:05:08'),
('01krgq1v3bn468cgzyhr1h3ez8', '01krgq1v30trtqhqqgdwwxq56z', 'STU-005', 'A1', '2026-01-20', NULL, '2026-05-13 12:05:09', '2026-05-13 12:05:09'),
('01krgq1vd40e9p33q8gc0bmqd7', '01krgq1vcv1bdnw47mv8cnx5w9', 'STU-006', 'B1', '2026-01-20', NULL, '2026-05-13 12:05:09', '2026-05-13 12:05:09'),
('01krgq1vpjvw5xq819245m22vt', '01krgq1vp7rhf8aah1mey6fgds', 'STU-007', 'B2', '2026-01-20', NULL, '2026-05-13 12:05:09', '2026-05-13 12:05:09'),
('01krgq1w17j9h5gvrbfccskj15', '01krgq1w0wn6rj9yh59rn9jek0', 'STU-008', 'A1', '2026-01-20', NULL, '2026-05-13 12:05:10', '2026-05-13 12:05:10'),
('01krh6a6h4a2fkehghj6hqjdfs', '01krh6a6gpjtx9nksb7p6emd3k', 'OLS-2026-0009', NULL, '2026-05-13', NULL, '2026-05-13 16:31:51', '2026-05-13 16:31:51'),
('01krh6n1m6k88mfpfcq1m164zw', '01krh6n1kn0h9kg2zw9gsm74ey', 'OLS-2026-0010', NULL, '2026-05-13', NULL, '2026-05-13 16:37:47', '2026-05-13 16:37:47');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `key` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`key`, `value`, `description`, `updated_at`, `updated_by`) VALUES
('academic_year', '2025/2026', 'Ano lectivo actual', '2026-05-13 13:05:05', NULL),
('default_currency', 'AOA', 'Moeda padrão', '2026-05-13 13:05:05', NULL),
('default_language', 'pt', 'Idioma padrão', '2026-05-13 13:05:05', NULL),
('invoice_prefix', 'OLS', 'Prefixo das facturas', '2026-05-13 13:05:05', NULL),
('late_fee_pct', '5', 'Percentagem de multa por atraso (%)', '2026-05-13 13:05:05', NULL),
('max_class_capacity', '8', 'Capacidade máxima por turma', '2026-05-13 13:05:05', NULL),
('school_address', 'Rua Major Kanhangulo, 123, Luanda', 'Morada da escola', '2026-05-13 13:05:05', NULL),
('school_country', 'AO', 'País', '2026-05-13 13:05:05', NULL),
('school_email', 'geral@olsangola.ao', 'Email de contacto geral', '2026-05-13 13:05:05', NULL),
('school_name', 'OLS Angola', 'Nome oficial da escola', '2026-05-13 13:05:05', NULL),
('school_phone', '+244 923 456 789', 'Telefone principal', '2026-05-13 13:05:05', NULL),
('timezone', 'Africa/Luanda', 'Fuso horário', '2026-05-13 13:05:05', NULL),
('trial_lesson_enabled', 'true', 'Aula experimental habilitada', '2026-05-13 13:05:05', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `teacher_profiles`
--

CREATE TABLE `teacher_profiles` (
  `id` char(26) NOT NULL,
  `user_id` char(26) NOT NULL,
  `teacher_code` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL,
  `certifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]' CHECK (json_valid(`certifications`)),
  `specializations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '[]' CHECK (json_valid(`specializations`)),
  `hire_date` date NOT NULL,
  `salary` decimal(12,2) DEFAULT NULL,
  `salary_currency` enum('AOA','EUR','USD') NOT NULL DEFAULT 'AOA',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teacher_profiles`
--

INSERT INTO `teacher_profiles` (`id`, `user_id`, `teacher_code`, `bio`, `certifications`, `specializations`, `hire_date`, `salary`, `salary_currency`, `created_at`, `updated_at`) VALUES
('01krgq1rpw3ev4kw64j893ng7k', '01krgq1rpbc47ckjsxm97x9e1w', 'TCH-001', 'Professora de inglês com 8 anos de experiência no ensino de adultos em Angola. Certificada pelo Cambridge Assessment English.', '[\"CELTA\",\"Cambridge TKT\",\"IELTS 8.0\"]', '[\"Business English\",\"Oil & Gas English\",\"Daily Communication\"]', '2020-03-01', 250000.00, 'AOA', '2026-05-13 12:05:06', '2026-05-13 12:05:06'),
('01krgq1sfx91pgs9pvfg8v8g90', '01krgq1sffpwqrctrbe8gvwbh1', 'TCH-002', 'Professor de inglês técnico com experiência no sector petrolífero. 6 anos a lecionar inglês Oil & Gas em Luanda.', '[\"IELTS 7.5\",\"Oil & Gas Communication Certificate\"]', '[\"Oil & Gas English\",\"B2 Advanced\",\"Technical Writing\"]', '2021-06-01', 230000.00, 'AOA', '2026-05-13 12:05:07', '2026-05-13 12:05:07'),
('01krgq1stkx4add98k5nrsvnx6', '01krgq1sta6t3bvpy0j58w6tmc', 'TCH-003', 'Professora especializada em inglês para banca e finanças. Formação em Economia e certificação Cambridge.', '[\"CELTA\",\"Cambridge TKT\",\"Financial English Certificate\"]', '[\"Banking & Finance English\",\"A1 Beginner\",\"Business Writing\"]', '2022-09-01', 220000.00, 'AOA', '2026-05-13 12:05:07', '2026-05-13 12:05:07');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `rating` int(11) NOT NULL DEFAULT 5,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` char(26) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','teacher','student') NOT NULL DEFAULT 'student',
  `status` enum('active','inactive','pending','suspended') NOT NULL DEFAULT 'pending',
  `avatar_url` varchar(255) DEFAULT NULL,
  `country` varchar(2) NOT NULL DEFAULT 'AO',
  `preferred_language` enum('pt','en') NOT NULL DEFAULT 'pt',
  `preferred_currency` enum('AOA','EUR','USD') NOT NULL DEFAULT 'AOA',
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `email_verify_token` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `username`, `phone`, `cpf`, `password`, `role`, `status`, `avatar_url`, `country`, `preferred_language`, `preferred_currency`, `two_factor_enabled`, `two_factor_secret`, `email_verified_at`, `email_verify_token`, `remember_token`, `last_login_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
('01krgq1rb3awk374qfnz3dbffw', 'Carlos', 'Mendonça', 'admin@olsangola.ao', NULL, '+244 923 100 001', NULL, '$2y$12$KOmrJAy9ZHn1E5hk8C4bHubD6G7vUmp04Hue6wFYdvezLY067mN2q', 'admin', 'active', NULL, 'AO', 'pt', 'AOA', 0, NULL, '2026-05-13 12:05:06', NULL, NULL, '2026-05-16 11:30:58', '2026-05-13 12:05:06', '2026-05-16 11:30:58', NULL),
('01krgq1rpbc47ckjsxm97x9e1w', 'Ana', 'Ferreira', 'teacher@olsangola.ao', NULL, '+244 923 200 002', NULL, '$2y$12$O/qnDTj7CaqOFyPTs6ePY.vdAKrlWx6gdfc9lyzOum/9CQ5NTgSBm', 'teacher', 'active', NULL, 'AO', 'pt', 'AOA', 0, NULL, '2026-05-13 12:05:06', NULL, NULL, '2026-05-16 12:29:31', '2026-05-13 12:05:06', '2026-05-16 12:29:31', NULL),
('01krgq1s0wfzpwk8tgywfvxp88', 'João', 'Silva', 'student@olsangola.ao', NULL, '+244 923 300 003', NULL, '$2y$12$2qJW9ypHFgpOg2SGf7W8cezfK49Cxp51Y0rKt76i2d0yi1l1Q4aI.', 'student', 'active', NULL, 'AO', 'pt', 'AOA', 0, NULL, '2026-05-13 12:05:07', NULL, NULL, '2026-05-16 12:28:17', '2026-05-13 12:05:07', '2026-05-16 12:28:17', NULL),
('01krgq1sffpwqrctrbe8gvwbh1', 'Bruno', 'Santos', 'teacher2@olsangola.ao', NULL, '+244 923 200 022', NULL, '$2y$12$aSMWEikhQ/t4bnJCMK8fTuKfSbZ/.mxIgQaOJcw/diuBtLJjSkTmK', 'teacher', 'active', NULL, 'AO', 'pt', 'AOA', 0, NULL, '2026-05-13 12:05:07', NULL, NULL, '2026-05-11 12:05:07', '2026-05-13 12:05:07', '2026-05-13 12:05:07', NULL),
('01krgq1sta6t3bvpy0j58w6tmc', 'Maria', 'Neto', 'teacher3@olsangola.ao', NULL, '+244 923 200 033', NULL, '$2y$12$se0uxcAgtPZ1kFTeaHyRI.JtzRPGgc0xQIQ493ui0.DN57E7hNnyq', 'teacher', 'active', NULL, 'AO', 'pt', 'AOA', 0, NULL, '2026-05-13 12:05:07', NULL, NULL, '2026-05-12 12:05:07', '2026-05-13 12:05:07', '2026-05-13 12:05:07', NULL),
('01krgq1t3a7rf9c9hd17bzxav5', 'Esperança', 'Tchiami', 'aluno2@olsangola.ao', NULL, '+244 923 300 022', NULL, '$2y$12$eyrKa0cnwG5pCniMY5iLy.q2Rno/7Wotu1ZxSwR9mxjnDoIh80HwO', 'student', 'active', NULL, 'AO', 'pt', 'AOA', 0, NULL, '2026-05-13 12:05:08', NULL, NULL, '2026-05-06 12:05:08', '2026-05-13 12:05:08', '2026-05-13 12:05:08', NULL),
('01krgq1tgr6ayzfa4fg3acqh21', 'Ricardo', 'Nkosi', 'aluno3@olsangola.ao', NULL, '+244 923 300 033', NULL, '$2y$12$QIibEWmZwuBaElfaMCA53OkCbNPDQVUhxhp8dXzB0NgBufSW1q7W6', 'student', 'active', NULL, 'AO', 'pt', 'AOA', 0, NULL, '2026-05-13 12:05:08', NULL, NULL, '2026-05-06 12:05:08', '2026-05-13 12:05:08', '2026-05-13 12:05:08', NULL),
('01krgq1ts6s3sdgppn589qwah0', 'Fátima', 'Cardoso', 'aluno4@olsangola.ao', NULL, '+244 923 300 044', NULL, '$2y$12$vvo62pfb4BZq543i3oEUGOlcc55cSuGW3vYac8VVWd2jaD15fve5m', 'student', 'active', NULL, 'AO', 'pt', 'AOA', 0, NULL, '2026-05-13 12:05:08', NULL, NULL, '2026-05-08 12:05:08', '2026-05-13 12:05:08', '2026-05-13 12:05:08', NULL),
('01krgq1v30trtqhqqgdwwxq56z', 'David', 'Nunes', 'aluno5@olsangola.ao', NULL, '+244 923 300 055', NULL, '$2y$12$p7ukZV/cUGB/pZrw8GLyl.btt.1vg3zlCHOgcoh1eFf4P6PA97rH2', 'student', 'active', NULL, 'AO', 'pt', 'AOA', 0, NULL, '2026-05-13 12:05:09', NULL, NULL, '2026-05-09 12:05:09', '2026-05-13 12:05:09', '2026-05-13 12:05:09', NULL),
('01krgq1vcv1bdnw47mv8cnx5w9', 'Leonor', 'Augusto', 'aluno6@olsangola.ao', NULL, '+244 923 300 066', NULL, '$2y$12$2AsQUNCsnW8mV4Mupr5HsuG7xmKXLXX5Is4SWOnNqDeqIu0QzvtoK', 'student', 'active', NULL, 'AO', 'pt', 'AOA', 0, NULL, '2026-05-13 12:05:09', NULL, NULL, '2026-05-10 12:05:09', '2026-05-13 12:05:09', '2026-05-13 12:05:09', NULL),
('01krgq1vp7rhf8aah1mey6fgds', 'Paulo', 'Ferreira', 'aluno7@olsangola.ao', NULL, '+244 923 300 077', NULL, '$2y$12$U0P0aW7qOK9GCZHiu0EETeIO7C4EXz2epM.5scrBYgPzU31gjqckS', 'student', 'active', NULL, 'AO', 'pt', 'AOA', 0, NULL, '2026-05-13 12:05:09', NULL, NULL, '2026-05-08 12:05:09', '2026-05-13 12:05:09', '2026-05-13 12:05:09', NULL),
('01krgq1w0wn6rj9yh59rn9jek0', 'Inês', 'Rodrigues', 'aluno8@olsangola.ao', NULL, '+244 923 300 088', NULL, '$2y$12$Ec4BBiJECWtl1jJSc.5feeGZR49JWRYMilLt01ML8XTaH/J42gGtG', 'student', 'active', NULL, 'AO', 'pt', 'AOA', 0, NULL, '2026-05-13 12:05:10', NULL, NULL, '2026-05-08 12:05:10', '2026-05-13 12:05:10', '2026-05-13 12:05:10', NULL),
('01krh6a6gpjtx9nksb7p6emd3k', 'Lucilene', 'Pereira', 'lucilenepereir123@gmail.com', NULL, '927851163', NULL, '$2y$12$aK4cViN0sMAZfN/65gMsee23LiW/ou96l6CehlVKLBu7Zpg0IUaA.', 'student', 'active', NULL, 'AO', 'pt', 'AOA', 0, NULL, NULL, NULL, NULL, NULL, '2026-05-13 16:31:51', '2026-05-13 16:31:51', NULL),
('01krh6n1kn0h9kg2zw9gsm74ey', 'Walter', 'Monteiro', 'enzopereir223@gmail.com', NULL, '+244927851163', NULL, '$2y$12$NRgmoqr4JC2/DI5DIgkOI.M0CLZjWURUt0BB2rpx/stGPxZrr7cIa', 'student', 'active', NULL, 'AO', 'pt', 'AOA', 0, NULL, NULL, NULL, NULL, '2026-05-13 16:45:05', '2026-05-13 16:37:47', '2026-05-13 16:45:05', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject` (`subject_type`,`subject_id`),
  ADD KEY `causer` (`causer_type`,`causer_id`),
  ADD KEY `activity_log_log_name_index` (`log_name`);

--
-- Indexes for table `admin_profiles`
--
ALTER TABLE `admin_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_profiles_user_id_unique` (`user_id`),
  ADD UNIQUE KEY `admin_profiles_admin_code_unique` (`admin_code`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assignments_course_id_foreign` (`course_id`),
  ADD KEY `assignments_class_group_id_foreign` (`class_group_id`),
  ADD KEY `assignments_teacher_id_foreign` (`teacher_id`);

--
-- Indexes for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assignment_submissions_assignment_id_foreign` (`assignment_id`),
  ADD KEY `assignment_submissions_student_id_foreign` (`student_id`);

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attendances_class_group_id_student_id_date_unique` (`class_group_id`,`student_id`,`date`),
  ADD KEY `attendances_teacher_id_foreign` (`teacher_id`),
  ADD KEY `attendances_student_id_index` (`student_id`),
  ADD KEY `attendances_class_group_id_date_index` (`class_group_id`,`date`);

--
-- Indexes for table `blog_categories`
--
ALTER TABLE `blog_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `blog_categories_slug_unique` (`slug`);

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `blog_posts_slug_unique` (`slug`),
  ADD KEY `blog_posts_category_id_foreign` (`category_id`),
  ADD KEY `blog_posts_author_id_foreign` (`author_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `chat_attachments`
--
ALTER TABLE `chat_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_attachments_uploader_id_foreign` (`uploader_id`);

--
-- Indexes for table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_conversations_class_id_foreign` (`class_id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_messages_sender_id_foreign` (`sender_id`),
  ADD KEY `chat_messages_attachment_id_foreign` (`attachment_id`),
  ADD KEY `chat_messages_conversation_id_created_at_index` (`conversation_id`,`created_at`);

--
-- Indexes for table `chat_message_reads`
--
ALTER TABLE `chat_message_reads`
  ADD PRIMARY KEY (`message_id`,`user_id`),
  ADD KEY `chat_message_reads_user_id_foreign` (`user_id`);

--
-- Indexes for table `chat_participants`
--
ALTER TABLE `chat_participants`
  ADD PRIMARY KEY (`conversation_id`,`user_id`),
  ADD KEY `chat_participants_user_id_foreign` (`user_id`);

--
-- Indexes for table `class_groups`
--
ALTER TABLE `class_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_groups_course_id_year_index` (`course_id`,`year`),
  ADD KEY `class_groups_teacher_id_index` (`teacher_id`);

--
-- Indexes for table `class_schedules`
--
ALTER TABLE `class_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_schedules_class_group_id_index` (`class_group_id`);

--
-- Indexes for table `contact_form_submissions`
--
ALTER TABLE `contact_form_submissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `courses_slug_unique` (`slug`),
  ADD KEY `courses_service_type_is_active_index` (`service_type`,`is_active`),
  ADD KEY `courses_visibility_status_published_at_index` (`visibility_status`,`published_at`),
  ADD KEY `courses_responsible_teacher_id_index` (`responsible_teacher_id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documents_course_id_index` (`course_id`),
  ADD KEY `documents_uploaded_by_id_index` (`uploaded_by_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `enrollments_student_id_class_group_id_unique` (`student_id`,`class_group_id`),
  ADD KEY `enrollments_course_id_foreign` (`course_id`),
  ADD KEY `enrollments_student_id_index` (`student_id`),
  ADD KEY `enrollments_class_group_id_index` (`class_group_id`);

--
-- Indexes for table `enrollment_requests`
--
ALTER TABLE `enrollment_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `enrollment_requests_protocol_unique` (`protocol`),
  ADD KEY `enrollment_requests_user_id_foreign` (`user_id`),
  ADD KEY `enrollment_requests_class_group_id_foreign` (`class_group_id`),
  ADD KEY `enrollment_requests_class_schedule_id_foreign` (`class_schedule_id`),
  ADD KEY `enrollment_requests_status_created_at_index` (`status`,`created_at`),
  ADD KEY `enrollment_requests_course_id_index` (`course_id`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exams_course_id_foreign` (`course_id`),
  ADD KEY `exams_class_group_id_status_index` (`class_group_id`,`status`),
  ADD KEY `exams_teacher_id_index` (`teacher_id`);

--
-- Indexes for table `exam_answers`
--
ALTER TABLE `exam_answers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `exam_answers_attempt_id_question_id_unique` (`attempt_id`,`question_id`),
  ADD KEY `exam_answers_question_id_foreign` (`question_id`);

--
-- Indexes for table `exam_assignments`
--
ALTER TABLE `exam_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `exam_assign_unique` (`exam_id`,`assignable_id`,`assignable_type`),
  ADD KEY `exam_assignments_assignable_type_assignable_id_index` (`assignable_type`,`assignable_id`);

--
-- Indexes for table `exam_attempts`
--
ALTER TABLE `exam_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exam_attempts_student_id_index` (`student_id`),
  ADD KEY `exam_attempts_exam_id_index` (`exam_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `fouls`
--
ALTER TABLE `fouls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fouls_course_id_foreign` (`course_id`),
  ADD KEY `fouls_created_by_foreign` (`created_by`),
  ADD KEY `fouls_student_id_status_index` (`student_id`,`status`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `grades_teacher_id_foreign` (`teacher_id`),
  ADD KEY `grades_class_group_id_foreign` (`class_group_id`),
  ADD KEY `grades_student_id_index` (`student_id`),
  ADD KEY `grades_course_id_index` (`course_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `materials_class_group_id_index` (`class_group_id`),
  ADD KEY `materials_course_id_index` (`course_id`),
  ADD KEY `materials_uploaded_by_id_index` (`uploaded_by_id`),
  ADD KEY `materials_is_published_index` (`is_published`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `newsletter_subscribers_email_unique` (`email`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`),
  ADD KEY `notifications_notifiable_id_notifiable_type_read_at_index` (`notifiable_id`,`notifiable_type`,`read_at`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payments_invoice_number_unique` (`invoice_number`),
  ADD KEY `payments_course_id_foreign` (`course_id`),
  ADD KEY `payments_enrollment_id_foreign` (`enrollment_id`),
  ADD KEY `payments_student_id_status_index` (`student_id`,`status`),
  ADD KEY `payments_due_date_status_index` (`due_date`,`status`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `questions_exam_id_index` (`exam_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_profiles_user_id_unique` (`user_id`),
  ADD UNIQUE KEY `student_profiles_student_code_unique` (`student_code`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `teacher_profiles`
--
ALTER TABLE `teacher_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teacher_profiles_user_id_unique` (`user_id`),
  ADD UNIQUE KEY `teacher_profiles_teacher_code_unique` (`teacher_code`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_cpf_unique` (`cpf`),
  ADD KEY `users_role_status_index` (`role`,`status`),
  ADD KEY `users_email_index` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_assignments`
--
ALTER TABLE `exam_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_profiles`
--
ALTER TABLE `admin_profiles`
  ADD CONSTRAINT `admin_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_class_group_id_foreign` FOREIGN KEY (`class_group_id`) REFERENCES `class_groups` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `assignments_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `assignments_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teacher_profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assignment_submissions`
--
ALTER TABLE `assignment_submissions`
  ADD CONSTRAINT `assignment_submissions_assignment_id_foreign` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignment_submissions_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_class_group_id_foreign` FOREIGN KEY (`class_group_id`) REFERENCES `class_groups` (`id`),
  ADD CONSTRAINT `attendances_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`),
  ADD CONSTRAINT `attendances_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teacher_profiles` (`id`);

--
-- Constraints for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD CONSTRAINT `blog_posts_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `blog_posts_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `chat_attachments`
--
ALTER TABLE `chat_attachments`
  ADD CONSTRAINT `chat_attachments_uploader_id_foreign` FOREIGN KEY (`uploader_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  ADD CONSTRAINT `chat_conversations_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `class_groups` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_attachment_id_foreign` FOREIGN KEY (`attachment_id`) REFERENCES `chat_attachments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `chat_messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_message_reads`
--
ALTER TABLE `chat_message_reads`
  ADD CONSTRAINT `chat_message_reads_message_id_foreign` FOREIGN KEY (`message_id`) REFERENCES `chat_messages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_message_reads_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_participants`
--
ALTER TABLE `chat_participants`
  ADD CONSTRAINT `chat_participants_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_participants_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `class_groups`
--
ALTER TABLE `class_groups`
  ADD CONSTRAINT `class_groups_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `class_groups_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teacher_profiles` (`id`);

--
-- Constraints for table `class_schedules`
--
ALTER TABLE `class_schedules`
  ADD CONSTRAINT `class_schedules_class_group_id_foreign` FOREIGN KEY (`class_group_id`) REFERENCES `class_groups` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_responsible_teacher_id_foreign` FOREIGN KEY (`responsible_teacher_id`) REFERENCES `teacher_profiles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `documents_uploaded_by_id_foreign` FOREIGN KEY (`uploaded_by_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_class_group_id_foreign` FOREIGN KEY (`class_group_id`) REFERENCES `class_groups` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `enrollments_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `enrollments_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`);

--
-- Constraints for table `enrollment_requests`
--
ALTER TABLE `enrollment_requests`
  ADD CONSTRAINT `enrollment_requests_class_group_id_foreign` FOREIGN KEY (`class_group_id`) REFERENCES `class_groups` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `enrollment_requests_class_schedule_id_foreign` FOREIGN KEY (`class_schedule_id`) REFERENCES `class_schedules` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `enrollment_requests_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `enrollment_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exams`
--
ALTER TABLE `exams`
  ADD CONSTRAINT `exams_class_group_id_foreign` FOREIGN KEY (`class_group_id`) REFERENCES `class_groups` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `exams_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `exams_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teacher_profiles` (`id`);

--
-- Constraints for table `exam_answers`
--
ALTER TABLE `exam_answers`
  ADD CONSTRAINT `exam_answers_attempt_id_foreign` FOREIGN KEY (`attempt_id`) REFERENCES `exam_attempts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_answers_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_assignments`
--
ALTER TABLE `exam_assignments`
  ADD CONSTRAINT `exam_assignments_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_attempts`
--
ALTER TABLE `exam_attempts`
  ADD CONSTRAINT `exam_attempts_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`),
  ADD CONSTRAINT `exam_attempts_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`);

--
-- Constraints for table `fouls`
--
ALTER TABLE `fouls`
  ADD CONSTRAINT `fouls_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fouls_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fouls_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`);

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_class_group_id_foreign` FOREIGN KEY (`class_group_id`) REFERENCES `class_groups` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `grades_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `grades_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`),
  ADD CONSTRAINT `grades_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teacher_profiles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `materials_class_group_id_foreign` FOREIGN KEY (`class_group_id`) REFERENCES `class_groups` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `materials_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `materials_uploaded_by_id_foreign` FOREIGN KEY (`uploaded_by_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_enrollment_id_foreign` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `student_profiles` (`id`);

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD CONSTRAINT `student_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher_profiles`
--
ALTER TABLE `teacher_profiles`
  ADD CONSTRAINT `teacher_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
