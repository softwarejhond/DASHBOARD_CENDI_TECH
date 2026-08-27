<?php
$api_url = "https://campus.cenditech.com.co/webservice/rest/server.php";
$token   = "c4bc5a8ef9d02d713c1e5283da17c29f";
$format  = "json";

function callMoodleAPIB($function, $params = []) {
    global $api_url, $token, $format;
    $params['wstoken'] = $token;
    $params['wsfunction'] = $function;
    $params['moodlewsrestformat'] = $format;
    $url = $api_url . '?' . http_build_query($params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        curl_close($ch);
        return ['error' => 'Error al conectar con Moodle: ' . curl_error($ch)];
    }
    curl_close($ch);
    return json_decode($response, true);
}

function getCoursesB() {
    return callMoodleAPIB('core_course_get_courses');
}
