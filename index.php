<?php
include dirname( __FILE__ ) . '/includes/compatibility.php';
include dirname( __FILE__ ) . '/includes/functions.php';
include dirname( __FILE__ ) . '/includes/class.db-api.php';
include dirname( __FILE__ ) . '/config.php';

$query = $db_api->parse_query();

$db_api->set_db( $query['db'] );
$reqMethod = $_SERVER['REQUEST_METHOD'];
$results = '';
switch ($reqMethod) {
    case "GET":
        $results = $db_api->query( $query );
        break;
    case "POST":
	    $results = $db_api->post($query, $_POST);
        break;
    case "PUT":
        parse_str(file_get_contents('php://input'), $_PUT);
        $results = $db_api->put($query, $_PUT);
        break;
    case "DELETE":
	    $results = $db_api->delete($query);
        break;
}

$renderer = 'render_' . $query['format'];
$db_api->$renderer( $results, $query );
