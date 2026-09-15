-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: db:3306
-- Tiempo de generación: 15-09-2026 a las 20:44:52
-- Versión del servidor: 10.6.27-MariaDB-ubu2204
-- Versión de PHP: 8.3.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `cendi_tech`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `absence_log`
--

CREATE TABLE `absence_log` (
  `id` int(11) NOT NULL,
  `number_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `id_advisor` int(11) NOT NULL,
  `class_date` date NOT NULL,
  `contact_established` tinyint(1) NOT NULL COMMENT '0 = No, 1 = Sí',
  `compromiso` varchar(255) DEFAULT NULL,
  `seguimiento_compromiso` varchar(255) DEFAULT NULL,
  `retiro` varchar(255) DEFAULT NULL,
  `motivo_retiro` varchar(255) DEFAULT NULL,
  `observacion` mediumtext DEFAULT NULL,
  `creation_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acudientes`
--

CREATE TABLE `acudientes` (
  `id` int(11) NOT NULL,
  `number_id` bigint(20) NOT NULL COMMENT 'Documento del estudiante (user_register.number_id)',
  `guardian_full_name` varchar(255) NOT NULL,
  `guardian_document` varchar(50) NOT NULL,
  `guardian_phone` varchar(20) NOT NULL,
  `guardian_email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `advisors`
--

CREATE TABLE `advisors` (
  `id` int(11) NOT NULL,
  `idAdvisor` varchar(15) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` varchar(100) DEFAULT 'Asesor',
  `notes` text DEFAULT NULL,
  `status` enum('Activo','Inactivo') DEFAULT 'Activo',
  `registration_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencias_masterclass`
--

CREATE TABLE `asistencias_masterclass` (
  `id` int(11) NOT NULL,
  `number_id` bigint(20) NOT NULL,
  `code` varchar(50) NOT NULL,
  `fecha` date NOT NULL,
  `clases_nivelacion` int(11) DEFAULT 0,
  `creation_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `update_date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencias_mentorias`
--

CREATE TABLE `asistencias_mentorias` (
  `id` int(11) NOT NULL,
  `number_id` bigint(20) NOT NULL,
  `code` varchar(255) NOT NULL,
  `fecha` date NOT NULL,
  `clases_nivelacion` int(11) DEFAULT 0,
  `creation_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia_empleabilidad`
--

CREATE TABLE `asistencia_empleabilidad` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `activity_type` enum('Taller','Networking','Feria de empleabilidad','Acompañamiento personalizado') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `attendance_records`
--

CREATE TABLE `attendance_records` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `course_id` int(11) NOT NULL,
  `modality` varchar(50) NOT NULL,
  `sede` varchar(50) NOT NULL,
  `class_date` date NOT NULL,
  `recorded_hours` int(2) NOT NULL,
  `attendance_status` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `barrios`
--

CREATE TABLE `barrios` (
  `id` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(200) DEFAULT NULL,
  `identificacion` varchar(100) DEFAULT NULL,
  `limite_comuna_corregimiento_id` varchar(20) DEFAULT NULL,
  `limite_municipio_id` varchar(20) DEFAULT NULL,
  `subtipo_barriovereda` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carnet_records`
--

CREATE TABLE `carnet_records` (
  `id` int(11) NOT NULL,
  `number_id` varchar(50) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `generated_by` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cedulas_pdf`
--

CREATE TABLE `cedulas_pdf` (
  `id` int(11) NOT NULL,
  `number_id` varchar(30) NOT NULL,
  `pdf_path` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `certificados_emitidos`
--

CREATE TABLE `certificados_emitidos` (
  `id` int(11) NOT NULL,
  `number_id` varchar(30) NOT NULL,
  `serie_certificado` varchar(50) NOT NULL,
  `emitido_por` varchar(100) NOT NULL,
  `fecha_emision` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `certificados_senatics`
--

CREATE TABLE `certificados_senatics` (
  `id` int(11) NOT NULL,
  `number_id` varchar(20) NOT NULL,
  `archivo_certificado` varchar(255) NOT NULL,
  `fecha_subida` datetime DEFAULT current_timestamp(),
  `usuario_subida` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `certificates`
--

CREATE TABLE `certificates` (
  `id` int(11) NOT NULL,
  `number_id` int(11) NOT NULL,
  `link` text NOT NULL,
  `creation_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `certification_previous`
--

CREATE TABLE `certification_previous` (
  `id` int(11) NOT NULL,
  `number_id` varchar(20) NOT NULL,
  `has_certification` enum('SI','NO') NOT NULL,
  `program_certified` varchar(100) DEFAULT NULL,
  `anio_certificacion` varchar(4) DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `change_history`
--

CREATE TABLE `change_history` (
  `student_id` int(11) NOT NULL,
  `user_change` int(11) NOT NULL,
  `change_made` text NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `classrooms`
--

CREATE TABLE `classrooms` (
  `id` int(11) NOT NULL,
  `headquarters` varchar(100) NOT NULL,
  `classroom_name` varchar(100) NOT NULL,
  `bootcamp_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `class_observations`
--

CREATE TABLE `class_observations` (
  `id` int(11) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `course_id` int(11) NOT NULL,
  `class_date` date NOT NULL,
  `observation_type` varchar(50) NOT NULL,
  `observation_text` text DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cohorts`
--

CREATE TABLE `cohorts` (
  `id` int(11) NOT NULL,
  `cohort_number` int(2) NOT NULL,
  `start_date` date NOT NULL,
  `finish_date` date NOT NULL,
  `state` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `company`
--

CREATE TABLE `company` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `nit` varchar(15) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `email` varchar(266) NOT NULL,
  `ciudad` varchar(255) NOT NULL,
  `web` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comunas_corregimientos`
--

CREATE TABLE `comunas_corregimientos` (
  `id` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(200) DEFAULT NULL,
  `identificacion` varchar(100) DEFAULT NULL,
  `limite_municipio_id` varchar(20) DEFAULT NULL,
  `subtipo_comunacorregimiento` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `constancias_emitidas`
--

CREATE TABLE `constancias_emitidas` (
  `id` int(11) NOT NULL,
  `number_id` varchar(30) NOT NULL,
  `serie_constancia` varchar(50) NOT NULL,
  `emitido_por` varchar(100) NOT NULL,
  `fecha_emision` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contact_log`
--

CREATE TABLE `contact_log` (
  `id` int(11) NOT NULL,
  `idAdvisor` varchar(15) NOT NULL,
  `number_id` int(15) NOT NULL,
  `contact_established` tinyint(1) NOT NULL,
  `contact_date` datetime DEFAULT current_timestamp(),
  `details` mediumtext NOT NULL,
  `continues_interested` tinyint(1) NOT NULL,
  `observation` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `code` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `real_hours` int(3) NOT NULL,
  `teacher` int(11) NOT NULL,
  `mentor` int(11) NOT NULL,
  `monitor` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `cohort` int(1) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `monday_hours` int(2) NOT NULL,
  `tuesday_hours` int(2) NOT NULL,
  `wednesday_hours` int(2) NOT NULL,
  `thursday_hours` int(2) NOT NULL,
  `friday_hours` int(2) NOT NULL,
  `saturday_hours` int(2) NOT NULL,
  `sunday_hours` int(2) NOT NULL,
  `notes_limit` date NOT NULL,
  `creation_date` datetime DEFAULT current_timestamp(),
  `update_date` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `user_update` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `course_approvals`
--

CREATE TABLE `course_approvals` (
  `id` int(11) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `student_number_id` varchar(20) NOT NULL,
  `approved_by` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `final_grade` decimal(5,2) DEFAULT NULL,
  `grade_1` decimal(5,2) DEFAULT NULL,
  `grade_2` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `course_assignments`
--

CREATE TABLE `course_assignments` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `bootcamp_id` varchar(20) NOT NULL,
  `bootcamp_name` varchar(255) NOT NULL,
  `leveling_english_id` varchar(20) NOT NULL,
  `leveling_english_name` varchar(255) NOT NULL,
  `english_code_id` varchar(20) NOT NULL,
  `english_code_name` varchar(255) NOT NULL,
  `skills_id` varchar(20) NOT NULL,
  `skills_name` varchar(255) NOT NULL,
  `assigned_by` varchar(50) NOT NULL,
  `assigned_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `course_periods`
--

CREATE TABLE `course_periods` (
  `id` int(11) NOT NULL,
  `period_name` varchar(255) NOT NULL,
  `cohort` int(3) NOT NULL,
  `payment_number` int(11) DEFAULT 0,
  `bootcamp_code` varchar(50) DEFAULT NULL,
  `bootcamp_name` varchar(255) DEFAULT NULL,
  `leveling_english_code` varchar(50) DEFAULT NULL,
  `leveling_english_name` varchar(255) DEFAULT NULL,
  `english_code_code` varchar(50) DEFAULT NULL,
  `english_code_name` varchar(255) DEFAULT NULL,
  `skills_code` varchar(50) DEFAULT NULL,
  `skills_name` varchar(255) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_by` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos`
--

CREATE TABLE `cursos` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL COMMENT 'ID del curso en Moodle',
  `course_code` varchar(50) NOT NULL COMMENT 'Shortname del curso (ej: BLO-1, ING-BLO-1)',
  `course_name` varchar(255) NOT NULL COMMENT 'Fullname del curso',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departamentos`
--

CREATE TABLE `departamentos` (
  `id_departamento` int(10) UNSIGNED NOT NULL,
  `departamento` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `document_verifications`
--

CREATE TABLE `document_verifications` (
  `id` int(11) NOT NULL,
  `number_id` varchar(20) NOT NULL,
  `name_verified` tinyint(1) DEFAULT 0,
  `document_number_verified` tinyint(1) DEFAULT 0,
  `birth_date_verified` tinyint(1) DEFAULT 0,
  `document_type_verified` tinyint(1) DEFAULT 0,
  `verified_by` varchar(100) NOT NULL,
  `verification_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `email_history`
--

CREATE TABLE `email_history` (
  `id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `recipients_count` int(11) NOT NULL,
  `successful_count` int(11) NOT NULL,
  `failed_count` int(11) NOT NULL,
  `sent_by` varchar(100) NOT NULL,
  `sent_from` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `email_recipients`
--

CREATE TABLE `email_recipients` (
  `id` int(11) NOT NULL,
  `email_id` int(11) NOT NULL,
  `recipient_email` varchar(255) NOT NULL,
  `recipient_name` varchar(255) NOT NULL,
  `status` enum('success','failed') NOT NULL,
  `error_message` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `email_templates`
--

CREATE TABLE `email_templates` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` mediumtext NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `employability`
--

CREATE TABLE `employability` (
  `id` int(11) NOT NULL,
  `typeID` varchar(10) NOT NULL,
  `number_id` int(11) NOT NULL,
  `lote` tinyint(4) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `second_name` varchar(50) DEFAULT NULL,
  `first_last` varchar(50) NOT NULL,
  `second_last` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `interest` varchar(50) NOT NULL,
  `start_training_date` date NOT NULL,
  `personal_description` varchar(255) DEFAULT NULL,
  `localidad` varchar(50) NOT NULL,
  `nivel_educativo` varchar(50) NOT NULL,
  `gender` varchar(20) NOT NULL,
  `work_experience` varchar(255) DEFAULT NULL,
  `current_employment_status` varchar(20) NOT NULL,
  `tech_experience` varchar(5) NOT NULL,
  `job_profile` varchar(255) DEFAULT NULL,
  `tech_experience_years` int(11) NOT NULL,
  `last_tech_role` varchar(100) NOT NULL,
  `skills_knowledge` varchar(255) DEFAULT NULL,
  `digital_skills` text DEFAULT NULL,
  `soft_skills` text DEFAULT NULL,
  `professional_networks` text DEFAULT NULL,
  `desired_role` varchar(100) NOT NULL,
  `accept_requirements` tinyint(1) NOT NULL,
  `accept_data_policies` tinyint(1) NOT NULL,
  `fecha_registro` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `employability_close`
--

CREATE TABLE `employability_close` (
  `id` int(11) NOT NULL,
  `typeID` varchar(10) NOT NULL,
  `number_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `second_name` varchar(50) DEFAULT NULL,
  `first_last` varchar(50) NOT NULL,
  `second_last` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `interest` varchar(50) NOT NULL,
  `start_training_date` date NOT NULL,
  `grupos_poblacionales` varchar(100) NOT NULL,
  `nivel_educativo` varchar(50) NOT NULL,
  `gender` varchar(20) NOT NULL,
  `current_employment_status` varchar(20) NOT NULL,
  `current_tech_job` varchar(5) NOT NULL,
  `employment_obtained_by` varchar(100) NOT NULL,
  `contract_type` varchar(50) NOT NULL,
  `income_level` varchar(30) NOT NULL,
  `current_job_role` varchar(100) NOT NULL,
  `employment_route_spaces` text DEFAULT NULL,
  `content_usefulness` tinyint(4) DEFAULT NULL,
  `employment_support` tinyint(4) DEFAULT NULL,
  `general_satisfaction` varchar(30) DEFAULT NULL,
  `improvement_action` varchar(150) DEFAULT NULL,
  `accept_requirements` tinyint(1) NOT NULL DEFAULT 0,
  `accept_data_policies` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuestas_laborales`
--

CREATE TABLE `encuestas_laborales` (
  `id` int(11) NOT NULL,
  `nombreCompleto` varchar(255) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `situacionLaboral` varchar(50) NOT NULL,
  `tiempoDesempleo` varchar(255) DEFAULT NULL,
  `trabajoDesempena` varchar(255) DEFAULT NULL,
  `tipoContrato` varchar(100) NOT NULL,
  `rangoSalarial` varchar(20) NOT NULL,
  `hojaVida` varchar(3) NOT NULL,
  `contactoEmpleadores` varchar(3) NOT NULL,
  `talleresAsistidos` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `type_id` varchar(10) NOT NULL,
  `number_id` bigint(20) NOT NULL COMMENT 'Cedula del estudiante',
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL COMMENT 'Correo personal',
  `institutional_email` varchar(255) NOT NULL COMMENT 'Correo institucional generado',
  `username` varchar(100) NOT NULL COMMENT 'Usuario Moodle (cedula)',
  `password` varchar(255) NOT NULL COMMENT 'Contrasena inicial',
  `program` varchar(50) NOT NULL COMMENT 'Codigo del area tecnica (CIBER, IA, ...)',
  `program_name` varchar(255) NOT NULL COMMENT 'Nombre del tecnico',
  `moodle_user_id` int(11) NOT NULL COMMENT 'ID del usuario creado en Moodle',
  `set_id` int(11) NOT NULL COMMENT 'ID del set en sets_cursos',
  `course_tecnico_id` int(11) NOT NULL,
  `course_ingles_id` int(11) NOT NULL,
  `course_habilidades_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'enrolled' COMMENT 'enrolled | pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `enrollment_history`
--

CREATE TABLE `enrollment_history` (
  `id` int(11) NOT NULL,
  `type_id` varchar(10) NOT NULL,
  `number_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `institutional_email` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `headquarters` varchar(255) NOT NULL,
  `program` varchar(255) NOT NULL,
  `mode` varchar(255) NOT NULL,
  `id_bootcamp` int(11) DEFAULT NULL,
  `bootcamp_name` varchar(255) DEFAULT NULL,
  `id_leveling_english` int(11) DEFAULT NULL,
  `leveling_english_name` varchar(255) DEFAULT NULL,
  `id_english_code` int(11) DEFAULT NULL,
  `english_code_name` varchar(255) DEFAULT NULL,
  `id_skills` int(11) DEFAULT NULL,
  `skills_name` varchar(255) DEFAULT NULL,
  `enrollment_date` datetime NOT NULL COMMENT 'Fecha original de matrícula',
  `unenrollment_date` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha de desmatrícula',
  `unenrolled_by` varchar(100) NOT NULL COMMENT 'Usuario que realizó la desmatrícula'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Historial de matrículas eliminadas';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados`
--

CREATE TABLE `estados` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `executor_headquarters`
--

CREATE TABLE `executor_headquarters` (
  `id` int(11) NOT NULL,
  `username` int(11) NOT NULL,
  `headquarter` varchar(255) NOT NULL,
  `creation_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `filing_assignments`
--

CREATE TABLE `filing_assignments` (
  `id` int(11) NOT NULL,
  `username` int(11) NOT NULL,
  `filing_number` varchar(255) NOT NULL,
  `filing_date` date NOT NULL,
  `contract_role` varchar(255) NOT NULL,
  `creation_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `formularios`
--

CREATE TABLE `formularios` (
  `id` int(11) NOT NULL,
  `formulario` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gestiones_reportes`
--

CREATE TABLE `gestiones_reportes` (
  `id` int(11) NOT NULL,
  `id_reporte` int(11) NOT NULL,
  `responsable` varchar(100) NOT NULL,
  `gestion_a_realizar` text NOT NULL,
  `resultado_gestion` text NOT NULL,
  `status` varchar(20) NOT NULL,
  `fecha_gestion` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `groups`
--

CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `type_id` varchar(10) NOT NULL,
  `number_id` bigint(20) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `institutional_email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `headquarters` varchar(255) NOT NULL,
  `program` varchar(255) NOT NULL,
  `mode` varchar(255) NOT NULL,
  `bootcamp_teacher_id` int(11) NOT NULL,
  `bootcamp_mentor_id` int(11) NOT NULL,
  `bootcamp_monitor_id` int(11) NOT NULL,
  `id_bootcamp` int(11) DEFAULT NULL,
  `bootcamp_name` varchar(255) DEFAULT NULL,
  `b_intensity` int(3) NOT NULL,
  `b_reals` int(3) NOT NULL,
  `le_teacher_id` int(11) NOT NULL,
  `le_mentor_id` int(11) NOT NULL,
  `le_monitor_id` int(11) NOT NULL,
  `id_leveling_english` int(11) DEFAULT NULL,
  `leveling_english_name` varchar(255) DEFAULT NULL,
  `le_intensity` int(2) NOT NULL,
  `le_reals` int(2) NOT NULL,
  `ec_teacher_id` int(11) NOT NULL,
  `ec_mentor_id` int(11) NOT NULL,
  `ec_monitor_id` int(11) NOT NULL,
  `id_english_code` int(11) DEFAULT NULL,
  `ec_intensity` int(2) NOT NULL,
  `ec_reals` int(2) NOT NULL,
  `english_code_name` varchar(255) DEFAULT NULL,
  `skills_teacher_id` int(11) NOT NULL,
  `skills_mentor_id` int(11) NOT NULL,
  `skills_monitor_id` int(11) NOT NULL,
  `id_skills` int(11) DEFAULT NULL,
  `skills_name` varchar(255) DEFAULT NULL,
  `s_intensity` int(2) NOT NULL,
  `s_reals` int(2) NOT NULL,
  `cohort` int(2) NOT NULL,
  `creation_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `headquarters`
--

CREATE TABLE `headquarters` (
  `id` int(100) NOT NULL,
  `name` varchar(50) NOT NULL,
  `mode` text NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(100) NOT NULL DEFAULT 'Sistema',
  `photo` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `headquarters_attendance`
--

CREATE TABLE `headquarters_attendance` (
  `id` int(100) NOT NULL,
  `name` varchar(50) NOT NULL,
  `mode` text NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `headquarters_classrooms`
--

CREATE TABLE `headquarters_classrooms` (
  `id` int(11) NOT NULL,
  `headquarters` varchar(255) NOT NULL,
  `classrooms_count` int(11) DEFAULT NULL,
  `creation_date` datetime NOT NULL DEFAULT current_timestamp(),
  `update_date` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `headquarters_registrations`
--

CREATE TABLE `headquarters_registrations` (
  `id` int(100) NOT NULL,
  `name` varchar(50) NOT NULL,
  `mode` text NOT NULL,
  `programs` text NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(100) NOT NULL,
  `photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_correos`
--

CREATE TABLE `historial_correos` (
  `id` int(11) NOT NULL,
  `destinatario` varchar(255) NOT NULL,
  `cc` varchar(255) DEFAULT NULL,
  `asunto` varchar(255) NOT NULL,
  `mensaje` text NOT NULL,
  `estado` varchar(50) NOT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mentors`
--

CREATE TABLE `mentors` (
  `id` int(11) NOT NULL,
  `number_id` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `course_id` int(11) NOT NULL,
  `creation_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `monitors`
--

CREATE TABLE `monitors` (
  `id` int(11) NOT NULL,
  `number_id` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `course_id` int(11) NOT NULL,
  `creation_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `municipios`
--

CREATE TABLE `municipios` (
  `id` int(11) NOT NULL,
  `cod_departamento` int(11) DEFAULT NULL,
  `cod_municipio` int(11) DEFAULT NULL,
  `nom_municipio` varchar(512) DEFAULT NULL,
  `tipo` varchar(512) DEFAULT NULL,
  `longitud` varchar(512) DEFAULT NULL,
  `Latitud` varchar(512) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notas_estudiantes`
--

CREATE TABLE `notas_estudiantes` (
  `id` int(11) NOT NULL,
  `number_id` varchar(20) NOT NULL,
  `code` int(11) NOT NULL,
  `nota1` decimal(5,2) DEFAULT 0.00,
  `nota2` decimal(5,2) DEFAULT 0.00,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `opciones`
--

CREATE TABLE `opciones` (
  `id` int(11) NOT NULL,
  `id_pregunta` int(11) NOT NULL,
  `opcion` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `participantes`
--

CREATE TABLE `participantes` (
  `id` int(11) NOT NULL,
  `tipo_documento` varchar(255) DEFAULT NULL,
  `numero_documento` int(11) DEFAULT NULL,
  `primer_nombre` varchar(255) NOT NULL,
  `segundo_nombre` varchar(255) DEFAULT NULL,
  `primer_apellido` varchar(255) NOT NULL,
  `segundo_apellido` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `payment_number` int(11) NOT NULL,
  `lote` int(11) NOT NULL,
  `mode` varchar(50) NOT NULL,
  `goal` int(11) NOT NULL,
  `is_counterpart` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plantillas_correos`
--

CREATE TABLE `plantillas_correos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `asunto` varchar(255) NOT NULL,
  `mensaje` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pqr`
--

CREATE TABLE `pqr` (
  `id` int(11) NOT NULL,
  `tipo` enum('Petición','Queja','Reclamo','Sugerencia') NOT NULL,
  `asunto` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `fecha_registro` date NOT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `cedula` varchar(15) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telefono1` varchar(20) DEFAULT NULL,
  `telefono2` varchar(20) DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_resolucion` datetime DEFAULT NULL,
  `respuesta` text DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `numero_radicado` varchar(255) DEFAULT NULL,
  `estado` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas`
--

CREATE TABLE `preguntas` (
  `id` int(11) NOT NULL,
  `id_formulario` int(11) NOT NULL,
  `pregunta` text NOT NULL,
  `respuesta_correcta` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pre_registrations`
--

CREATE TABLE `pre_registrations` (
  `id` int(11) NOT NULL,
  `type_id` varchar(10) NOT NULL,
  `number_id` varchar(15) NOT NULL,
  `number_id_very` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `email_very` varchar(100) NOT NULL,
  `email2` varchar(100) NOT NULL,
  `email2_very` varchar(100) NOT NULL,
  `phone1` varchar(15) NOT NULL,
  `phone2` varchar(15) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `second_name` varchar(50) DEFAULT NULL,
  `first_last` varchar(50) NOT NULL,
  `second_last` varchar(50) NOT NULL,
  `sede_id` int(11) NOT NULL,
  `sede_name` varchar(100) NOT NULL,
  `programa` varchar(100) NOT NULL,
  `horario` varchar(100) DEFAULT NULL,
  `accept_requirements` tinyint(1) NOT NULL DEFAULT 0,
  `accept_data_policies` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `qr_codes`
--

CREATE TABLE `qr_codes` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `url` varchar(500) NOT NULL,
  `image_filename` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `qr_masterclass`
--

CREATE TABLE `qr_masterclass` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `url` varchar(500) NOT NULL,
  `image_filename` varchar(255) NOT NULL,
  `clases_equivalentes` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `qr_mentorias`
--

CREATE TABLE `qr_mentorias` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `url` text NOT NULL,
  `date` datetime NOT NULL,
  `image_filename` varchar(255) NOT NULL,
  `clases_equivalentes` int(11) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `authorized` tinyint(1) DEFAULT 0,
  `authorized_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuestas`
--

CREATE TABLE `respuestas` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_pregunta` int(11) NOT NULL,
  `respuesta` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `schedule` varchar(255) NOT NULL,
  `program` varchar(100) NOT NULL,
  `mode` varchar(255) NOT NULL,
  `headquarters` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `available` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `schedules_registrations`
--

CREATE TABLE `schedules_registrations` (
  `id` int(11) NOT NULL,
  `schedule` varchar(100) NOT NULL,
  `program` varchar(100) NOT NULL,
  `mode` varchar(255) NOT NULL,
  `headquarters` varchar(100) DEFAULT NULL,
  `department` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sets_cursos`
--

CREATE TABLE `sets_cursos` (
  `id` int(11) NOT NULL,
  `codigo_tecnico` varchar(20) NOT NULL COMMENT 'Código del área técnica (ej: BLO, IA, DT)',
  `serie` int(11) NOT NULL COMMENT 'Número de serie del set',
  `curso_tecnico_id` int(11) NOT NULL COMMENT 'course_id del curso técnico',
  `curso_ingles_id` int(11) NOT NULL COMMENT 'course_id del curso de Inglés',
  `curso_habilidades_id` int(11) NOT NULL COMMENT 'course_id del curso de Habilidades Blandas',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sms_logs`
--

CREATE TABLE `sms_logs` (
  `id` int(11) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `message` varchar(160) NOT NULL,
  `sender` varchar(100) NOT NULL,
  `sent_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `smtpConfig`
--

CREATE TABLE `smtpConfig` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `host` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `port` int(11) NOT NULL,
  `dependence` mediumtext NOT NULL,
  `Subject` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `student_attendance_management`
--

CREATE TABLE `student_attendance_management` (
  `id` int(11) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `course_id` int(11) NOT NULL,
  `requires_intervention` varchar(2) DEFAULT NULL,
  `responsible_username` varchar(100) DEFAULT NULL,
  `intervention_observation` text DEFAULT NULL,
  `is_resolved` varchar(2) DEFAULT NULL,
  `requires_additional_strategy` varchar(2) DEFAULT NULL,
  `strategy_observation` text DEFAULT NULL,
  `strategy_fulfilled` varchar(2) DEFAULT NULL,
  `withdrawal_reason` varchar(255) DEFAULT NULL,
  `withdrawal_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `student_reports`
--

CREATE TABLE `student_reports` (
  `id` int(11) NOT NULL,
  `number_id` int(50) NOT NULL,
  `code` varchar(50) NOT NULL,
  `grupo` varchar(255) NOT NULL,
  `gestion` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'PENDIENTE',
  `responsable` varchar(100) NOT NULL,
  `fecha_registro` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `number_id` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `course_id` int(11) NOT NULL,
  `creation_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `team_assignments`
--

CREATE TABLE `team_assignments` (
  `id` int(11) NOT NULL,
  `code` int(11) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `teacher` varchar(100) DEFAULT NULL COMMENT 'Username del profesor',
  `mentor` varchar(100) DEFAULT NULL COMMENT 'Username del mentor',
  `monitor` varchar(100) DEFAULT NULL COMMENT 'Username del monitor',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tutoriales`
--

CREATE TABLE `tutoriales` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `modulo` varchar(255) NOT NULL,
  `link` text NOT NULL,
  `descripcion` text NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` int(11) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre` mediumtext NOT NULL,
  `rol` int(2) NOT NULL,
  `rol_informativo` int(11) NOT NULL,
  `extra_rol` int(2) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `orden` int(11) NOT NULL,
  `fechaCreacionUser` varchar(15) NOT NULL,
  `email` varchar(255) NOT NULL,
  `genero` mediumtext NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `edad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_register`
--

CREATE TABLE `user_register` (
  `id` int(11) NOT NULL,
  `typeID` varchar(30) NOT NULL,
  `number_id` bigint(50) NOT NULL,
  `number_id_very` varchar(15) NOT NULL,
  `first_name` varchar(25) NOT NULL,
  `second_name` varchar(25) NOT NULL,
  `first_last` varchar(25) NOT NULL,
  `second_last` varchar(25) NOT NULL,
  `birthdate` date NOT NULL,
  `expedition_date` date NOT NULL,
  `gender` mediumtext NOT NULL,
  `marital_status` mediumtext NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_very` varchar(255) NOT NULL,
  `first_phone` varchar(15) NOT NULL,
  `second_phone` varchar(15) NOT NULL,
  `token` varchar(255) NOT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `emergency_contact_name` varchar(150) NOT NULL,
  `emergency_contact_number` varchar(15) NOT NULL,
  `nationality` mediumtext NOT NULL,
  `department` varchar(50) NOT NULL,
  `municipality` varchar(50) NOT NULL,
  `address` varchar(255) NOT NULL,
  `latitud` varchar(50) NOT NULL,
  `longitud` varchar(50) NOT NULL,
  `comuna_corregimiento` varchar(150) DEFAULT NULL,
  `barrio` varchar(200) DEFAULT NULL,
  `people_charge` int(2) NOT NULL,
  `vulnerable_population` varchar(2) NOT NULL,
  `vulnerable_type` varchar(50) NOT NULL,
  `ethnic_group` varchar(255) NOT NULL,
  `stratum` int(1) NOT NULL,
  `residence_area` mediumtext NOT NULL,
  `training_level` varchar(50) NOT NULL,
  `occupation` mediumtext NOT NULL,
  `time_obligations` varchar(50) NOT NULL,
  `motivations_belong_program` varchar(255) NOT NULL,
  `current_situation` varchar(255) NOT NULL,
  `impediment_complete_course` varchar(70) NOT NULL,
  `availability` mediumtext NOT NULL,
  `mode` varchar(50) NOT NULL,
  `headquarters` varchar(255) NOT NULL,
  `program` varchar(50) NOT NULL,
  `schedules` varchar(255) NOT NULL,
  `schedules_alternative` varchar(255) NOT NULL,
  `prior_knowledge` varchar(2) NOT NULL,
  `level` varchar(50) NOT NULL,
  `languages` varchar(25) NOT NULL,
  `languages_level` varchar(25) NOT NULL,
  `medical_condition` varchar(2) NOT NULL,
  `disability` varchar(2) NOT NULL,
  `type_disability` varchar(120) NOT NULL,
  `pregnancy` varchar(2) NOT NULL,
  `country_person` varchar(2) NOT NULL,
  `technologies` varchar(25) NOT NULL,
  `internet` varchar(2) NOT NULL,
  `knowledge_program` varchar(255) NOT NULL,
  `accept_requirements` varchar(2) NOT NULL,
  `accepts_tech_talent` varchar(2) NOT NULL,
  `accept_data_policies` varchar(2) NOT NULL,
  `file_front_id` varchar(255) NOT NULL,
  `file_back_id` varchar(255) NOT NULL,
  `status` int(1) NOT NULL,
  `statusAdmin` int(1) NOT NULL,
  `lote` int(1) NOT NULL,
  `directed_base` int(1) NOT NULL,
  `idCourse` int(5) NOT NULL,
  `contactMedium` mediumtext NOT NULL,
  `institution` varchar(255) NOT NULL,
  `creationDate` datetime NOT NULL,
  `dayUpdate` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `primer_nombre` varchar(50) NOT NULL,
  `segundo_nombre` varchar(50) NOT NULL,
  `primer_apellido` varchar(50) NOT NULL,
  `segundo_apellido` varchar(50) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `id_formulario` int(1) NOT NULL,
  `nivel` varchar(50) NOT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `absence_log`
--
ALTER TABLE `absence_log`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `acudientes`
--
ALTER TABLE `acudientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_acudientes_number` (`number_id`);

--
-- Indices de la tabla `advisors`
--
ALTER TABLE `advisors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `idAdvisor` (`idAdvisor`);

--
-- Indices de la tabla `asistencias_masterclass`
--
ALTER TABLE `asistencias_masterclass`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `asistencias_mentorias`
--
ALTER TABLE `asistencias_mentorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `asistencia_empleabilidad`
--
ALTER TABLE `asistencia_empleabilidad`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attendance` (`student_id`,`course_id`,`modality`,`sede`,`class_date`),
  ADD KEY `idx_attendance_student_course_date_status` (`student_id`,`course_id`,`class_date`,`attendance_status`);

--
-- Indices de la tabla `barrios`
--
ALTER TABLE `barrios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_barrio_comuna` (`limite_comuna_corregimiento_id`);

--
-- Indices de la tabla `carnet_records`
--
ALTER TABLE `carnet_records`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cedulas_pdf`
--
ALTER TABLE `cedulas_pdf`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_number_id` (`number_id`);

--
-- Indices de la tabla `certificados_emitidos`
--
ALTER TABLE `certificados_emitidos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `serie_certificado` (`serie_certificado`);

--
-- Indices de la tabla `certificados_senatics`
--
ALTER TABLE `certificados_senatics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_number_id` (`number_id`);

--
-- Indices de la tabla `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `certification_previous`
--
ALTER TABLE `certification_previous`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `classrooms`
--
ALTER TABLE `classrooms`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `class_observations`
--
ALTER TABLE `class_observations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_observation` (`student_id`,`course_id`,`class_date`),
  ADD KEY `idx_course_date` (`course_id`,`class_date`),
  ADD KEY `idx_student_id` (`student_id`);

--
-- Indices de la tabla `cohorts`
--
ALTER TABLE `cohorts`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `comunas_corregimientos`
--
ALTER TABLE `comunas_corregimientos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_comuna_codigo` (`codigo`);

--
-- Indices de la tabla `constancias_emitidas`
--
ALTER TABLE `constancias_emitidas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `serie_constancia` (`serie_constancia`);

--
-- Indices de la tabla `contact_log`
--
ALTER TABLE `contact_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_advisor` (`idAdvisor`),
  ADD KEY `fk_user` (`number_id`),
  ADD KEY `idx_contact_log_established_number` (`contact_established`,`number_id`);

--
-- Indices de la tabla `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_courses_code` (`code`);

--
-- Indices de la tabla `course_approvals`
--
ALTER TABLE `course_approvals`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `course_assignments`
--
ALTER TABLE `course_assignments`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `course_periods`
--
ALTER TABLE `course_periods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cohort` (`cohort`),
  ADD KEY `idx_period_dates` (`start_date`,`end_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indices de la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_course_id` (`course_id`),
  ADD UNIQUE KEY `uk_course_code` (`course_code`);

--
-- Indices de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  ADD PRIMARY KEY (`id_departamento`);

--
-- Indices de la tabla `document_verifications`
--
ALTER TABLE `document_verifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_number_id` (`number_id`),
  ADD KEY `idx_number_id` (`number_id`),
  ADD KEY `idx_verification_date` (`verification_date`);

--
-- Indices de la tabla `email_history`
--
ALTER TABLE `email_history`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `email_recipients`
--
ALTER TABLE `email_recipients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_id` (`email_id`);

--
-- Indices de la tabla `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `employability`
--
ALTER TABLE `employability`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `employability_close`
--
ALTER TABLE `employability_close`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `encuestas_laborales`
--
ALTER TABLE `encuestas_laborales`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_number_set` (`number_id`,`set_id`),
  ADD KEY `idx_enrollments_number` (`number_id`),
  ADD KEY `idx_enrollments_moodle_user` (`moodle_user_id`),
  ADD KEY `idx_enrollments_set` (`set_id`);

--
-- Indices de la tabla `enrollment_history`
--
ALTER TABLE `enrollment_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_number_id` (`number_id`),
  ADD KEY `idx_unenrollment_date` (`unenrollment_date`);

--
-- Indices de la tabla `estados`
--
ALTER TABLE `estados`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `executor_headquarters`
--
ALTER TABLE `executor_headquarters`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `filing_assignments`
--
ALTER TABLE `filing_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_username` (`username`);

--
-- Indices de la tabla `formularios`
--
ALTER TABLE `formularios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `gestiones_reportes`
--
ALTER TABLE `gestiones_reportes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `institutional_email` (`institutional_email`),
  ADD KEY `idx_groups_number` (`number_id`);

--
-- Indices de la tabla `headquarters`
--
ALTER TABLE `headquarters`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `headquarters_attendance`
--
ALTER TABLE `headquarters_attendance`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `headquarters_classrooms`
--
ALTER TABLE `headquarters_classrooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `headquarters` (`headquarters`);

--
-- Indices de la tabla `headquarters_registrations`
--
ALTER TABLE `headquarters_registrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `historial_correos`
--
ALTER TABLE `historial_correos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `mentors`
--
ALTER TABLE `mentors`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `monitors`
--
ALTER TABLE `monitors`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `municipios`
--
ALTER TABLE `municipios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `notas_estudiantes`
--
ALTER TABLE `notas_estudiantes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number_id` (`number_id`);

--
-- Indices de la tabla `opciones`
--
ALTER TABLE `opciones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `participantes`
--
ALTER TABLE `participantes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_numero_documento` (`numero_documento`);

--
-- Indices de la tabla `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `plantillas_correos`
--
ALTER TABLE `plantillas_correos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pqr`
--
ALTER TABLE `pqr`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `estado` (`estado`);

--
-- Indices de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pregunta_formulario` (`id_formulario`);

--
-- Indices de la tabla `pre_registrations`
--
ALTER TABLE `pre_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_number_id` (`number_id`),
  ADD UNIQUE KEY `unique_email` (`email`);

--
-- Indices de la tabla `qr_codes`
--
ALTER TABLE `qr_codes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `qr_masterclass`
--
ALTER TABLE `qr_masterclass`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `qr_mentorias`
--
ALTER TABLE `qr_mentorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `respuestas`
--
ALTER TABLE `respuestas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `schedules_registrations`
--
ALTER TABLE `schedules_registrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `sets_cursos`
--
ALTER TABLE `sets_cursos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_codigo_serie` (`codigo_tecnico`,`serie`),
  ADD KEY `fk_sets_tecnico` (`curso_tecnico_id`),
  ADD KEY `fk_sets_ingles` (`curso_ingles_id`),
  ADD KEY `fk_sets_habilidades` (`curso_habilidades_id`);

--
-- Indices de la tabla `sms_logs`
--
ALTER TABLE `sms_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `smtpConfig`
--
ALTER TABLE `smtpConfig`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `student_attendance_management`
--
ALTER TABLE `student_attendance_management`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student_course` (`student_id`,`course_id`);

--
-- Indices de la tabla `student_reports`
--
ALTER TABLE `student_reports`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tutoriales`
--
ALTER TABLE `tutoriales`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`username`),
  ADD KEY `id` (`id`);

--
-- Indices de la tabla `user_register`
--
ALTER TABLE `user_register`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_register_lote_number_headquarters` (`lote`,`number_id`,`headquarters`),
  ADD KEY `idx_user_register_institution` (`institution`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `absence_log`
--
ALTER TABLE `absence_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `acudientes`
--
ALTER TABLE `acudientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `advisors`
--
ALTER TABLE `advisors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asistencias_masterclass`
--
ALTER TABLE `asistencias_masterclass`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asistencias_mentorias`
--
ALTER TABLE `asistencias_mentorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asistencia_empleabilidad`
--
ALTER TABLE `asistencia_empleabilidad`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `attendance_records`
--
ALTER TABLE `attendance_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `barrios`
--
ALTER TABLE `barrios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `carnet_records`
--
ALTER TABLE `carnet_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cedulas_pdf`
--
ALTER TABLE `cedulas_pdf`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `certificados_emitidos`
--
ALTER TABLE `certificados_emitidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `certificados_senatics`
--
ALTER TABLE `certificados_senatics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `certification_previous`
--
ALTER TABLE `certification_previous`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `classrooms`
--
ALTER TABLE `classrooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `class_observations`
--
ALTER TABLE `class_observations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cohorts`
--
ALTER TABLE `cohorts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `company`
--
ALTER TABLE `company`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `comunas_corregimientos`
--
ALTER TABLE `comunas_corregimientos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `constancias_emitidas`
--
ALTER TABLE `constancias_emitidas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `contact_log`
--
ALTER TABLE `contact_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `course_approvals`
--
ALTER TABLE `course_approvals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `course_assignments`
--
ALTER TABLE `course_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `course_periods`
--
ALTER TABLE `course_periods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  MODIFY `id_departamento` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `document_verifications`
--
ALTER TABLE `document_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `email_history`
--
ALTER TABLE `email_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `email_recipients`
--
ALTER TABLE `email_recipients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `employability`
--
ALTER TABLE `employability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `employability_close`
--
ALTER TABLE `employability_close`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `encuestas_laborales`
--
ALTER TABLE `encuestas_laborales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `enrollment_history`
--
ALTER TABLE `enrollment_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estados`
--
ALTER TABLE `estados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `executor_headquarters`
--
ALTER TABLE `executor_headquarters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `filing_assignments`
--
ALTER TABLE `filing_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `formularios`
--
ALTER TABLE `formularios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `gestiones_reportes`
--
ALTER TABLE `gestiones_reportes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `headquarters`
--
ALTER TABLE `headquarters`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `headquarters_attendance`
--
ALTER TABLE `headquarters_attendance`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `headquarters_classrooms`
--
ALTER TABLE `headquarters_classrooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `headquarters_registrations`
--
ALTER TABLE `headquarters_registrations`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historial_correos`
--
ALTER TABLE `historial_correos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mentors`
--
ALTER TABLE `mentors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `monitors`
--
ALTER TABLE `monitors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `municipios`
--
ALTER TABLE `municipios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notas_estudiantes`
--
ALTER TABLE `notas_estudiantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `opciones`
--
ALTER TABLE `opciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `participantes`
--
ALTER TABLE `participantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `plantillas_correos`
--
ALTER TABLE `plantillas_correos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pqr`
--
ALTER TABLE `pqr`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pre_registrations`
--
ALTER TABLE `pre_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `qr_codes`
--
ALTER TABLE `qr_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `qr_masterclass`
--
ALTER TABLE `qr_masterclass`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `qr_mentorias`
--
ALTER TABLE `qr_mentorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `respuestas`
--
ALTER TABLE `respuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `schedules_registrations`
--
ALTER TABLE `schedules_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sets_cursos`
--
ALTER TABLE `sets_cursos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sms_logs`
--
ALTER TABLE `sms_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `smtpConfig`
--
ALTER TABLE `smtpConfig`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `student_attendance_management`
--
ALTER TABLE `student_attendance_management`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `student_reports`
--
ALTER TABLE `student_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tutoriales`
--
ALTER TABLE `tutoriales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `user_register`
--
ALTER TABLE `user_register`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `email_recipients`
--
ALTER TABLE `email_recipients`
  ADD CONSTRAINT `email_recipients_ibfk_1` FOREIGN KEY (`email_id`) REFERENCES `email_history` (`id`);

--
-- Filtros para la tabla `sets_cursos`
--
ALTER TABLE `sets_cursos`
  ADD CONSTRAINT `fk_sets_habilidades` FOREIGN KEY (`curso_habilidades_id`) REFERENCES `cursos` (`course_id`),
  ADD CONSTRAINT `fk_sets_ingles` FOREIGN KEY (`curso_ingles_id`) REFERENCES `cursos` (`course_id`),
  ADD CONSTRAINT `fk_sets_tecnico` FOREIGN KEY (`curso_tecnico_id`) REFERENCES `cursos` (`course_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
