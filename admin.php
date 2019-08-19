<?php
header("Access-Control-Allow-Origin: *");
include dirname( __FILE__ ) . '/includes/compatibility.php';
include dirname( __FILE__ ) . '/includes/functions.php';
include dirname( __FILE__ ) . '/includes/class.db-api.php';
include dirname( __FILE__ ) . '/config.php';

$query = $db_api->parse_query();

$db_api->set_db( $query['db'] );
$reqMethod = $_SERVER['REQUEST_METHOD'];
$sql = <<<EOT
INSERT INTO UserOrder (OrderId, Date, UserId, ProductId, Quantity, Total)
SELECT concat(a.Id, CURRENT_DATE() + 1), CURRENT_DATE() + interval 1 day, a.UserId, a.ProductId, a.Quantity, (a.Quantity * c.Amount)
FROM Subscription AS a, User AS b , Product AS c
WHERE a.UserId = b.Id AND a.ProductId = c.Id AND
(a.PausedFrom IS NULL OR a.PausedFrom < (NOW() + INTERVAL 1 DAY) OR a.PausedTo < (NOW() + INTERVAL 1 DAY)) AND
 ((a.Type = 'D') OR
(a.Type = 'W' AND FIND_IN_SET(ELT(DAYOFWEEK(CURDATE() + interval 1 day),'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat') , a.Details)) OR
(a.Type = 'M' AND (FIND_IN_SET('LD', a.Details) AND last_day(CURRENT_DATE()+ INTERVAL 1 day) = CURRENT_DATE() + interval 1 day) OR
(FIND_IN_SET('FD', a.Details) AND DATE_FORMAT(NOW() ,'%Y-%m-01') = CURRENT_DATE() + interval 1 day)))
EOT;
$results = $db_api->runSql( $query, $sql);

$renderer = 'render_' . $query['format'];
$db_api->$renderer( $results, $query );
