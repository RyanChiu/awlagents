<?php
/*
 * start of the block that given by loadedcash.com
 */
/*
$aid = 'YOUR LOADEDCASH AFFILIATE ID HERE';
$username = 'YOUR LOADEDCASH USERNAME HERE';
$password = 'YOUR LOADEDCASH PASSWORD HERE';
*/
$aid = '43800';
$username = 'suzannebloch45';
$password = 'SUZANNE4545';

$key_d_t = gmdate("Y-m-d H:i:s"); // Greenwich Mean Date Time
$key = md5($username . $password . $key_d_t);

$start_date = '2011-02-13';
$end_date = '2011-02-15';

$url = 'http://www.loadedcash.com/api.php?response_type=xml&json={"key":"' .
	$key . '","key_d_t":"' . urlencode($key_d_t) .
	'","c":"affiliateStats","a":"trafficStats","params":{"aid":"' . $aid .
	'","start_date":"' . $start_date . '","end_date":"' . $end_date . '"}}';
/*
 * end of the block that given by loadedcash.com
 */
echo "\n" . $url . "\n\n";
$xml = simplexml_load_file($url);
if ($xml === false) {
	exit(sprintf("\nFailed to get stats data.(%s)\n", $url));
}
foreach ($xml as $key => $value) {
	echo $key . "=>" . $value . "\n";
}
?>