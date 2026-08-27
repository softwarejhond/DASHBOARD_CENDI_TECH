<?php
include __DIR__ . '/../moodle_api.php';

$courses = getCoursesB();

header('Content-Type: application/json');
echo json_encode($courses);