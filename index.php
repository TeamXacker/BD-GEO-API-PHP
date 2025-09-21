<?php
header('Content-Type: application/json; charset=utf-8');

function load_data($filename) {
    return json_decode(file_get_contents($filename), true);
}

$divisions = load_data('divisions_data.json');
$districts = load_data('districts_data.json');
$upazilas  = load_data('upazilas_data.json');
$unions    = load_data('unions_data.json');

$uri = explode('/', trim($_SERVER['REQUEST_URI'], '/')); 
// e.g. /division/3/district/27 -> ["division","3","district","27"]

// /division
if ($uri[0] === 'division' && count($uri) === 1) {
    echo json_encode($divisions, JSON_UNESCAPED_UNICODE);
    exit;
}

// /division/{division_id}
if ($uri[0] === 'division' && isset($uri[1]) && count($uri) === 2) {
    $division_id = $uri[1];
    $filtered = array_values(array_filter($districts,
        fn($d) => $d['division_id'] === (string)$division_id));
    if (!$filtered) {
        http_response_code(404);
        echo json_encode(["error" => "Division not found or no districts available"]);
        exit;
    }
    echo json_encode($filtered, JSON_UNESCAPED_UNICODE);
    exit;
}

// /division/{division_id}/district/{district_id}
if ($uri[0] === 'division' && isset($uri[1], $uri[2], $uri[3]) && $uri[2] === 'district' && count($uri) === 4) {
    $district_id = $uri[3];
    $filtered = array_values(array_filter($upazilas,
        fn($u) => $u['district_id'] === (string)$district_id));
    if (!$filtered) {
        http_response_code(404);
        echo json_encode(["error" => "District not found or no upazilas available"]);
        exit;
    }
    echo json_encode($filtered, JSON_UNESCAPED_UNICODE);
    exit;
}

// /division/{division_id}/district/{district_id}/upazila/{upazila_id}
if ($uri[0] === 'division' && isset($uri[1], $uri[2], $uri[3], $uri[4], $uri[5]) 
    && $uri[2] === 'district' && $uri[4] === 'upazila' && count($uri) === 6) {

    $upazila_id = $uri[5];
    // note: JSON key is spelled "upazilla_id"
    $filtered = array_values(array_filter($unions,
        fn($u) => $u['upazilla_id'] === (string)$upazila_id));
    if (!$filtered) {
        http_response_code(404);
        echo json_encode(["error" => "Upazila not found or no unions available"]);
        exit;
    }
    echo json_encode($filtered, JSON_UNESCAPED_UNICODE);
    exit;
}

// If nothing matched
http_response_code(404);
echo json_encode(["error" => "Endpoint not found"]);
