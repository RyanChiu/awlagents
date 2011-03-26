<?php
include 'extrakits.inc.php';

if (($argc - 1) != 1) {//if there is 1 parameter and it must mean a date like '2010-04-01,12:34:56'
	exit("Only 1 parameter needed like '2010-05-01,12:34:56'.\n");
}

/*
 * the following line will make the whole script exit if date string format is wrong
 */
$date = __get_remote_date($argv[1], "Europe/London", -1);
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

$start_date = $date;//'2011-02-13';
$end_date = $date;//'2011-02-15';

$url = 'http://www.loadedcash.com/api.php?response_type=xml&json={"key":"' .
	$key . '","key_d_t":"' . urlencode($key_d_t) .
	'","c":"affiliateStats","a":"trafficStats","params":{"aid":"' . $aid .
	'","start_date":"' . $start_date . '","end_date":"' . $end_date . '"}}';
/*
 * end of the block that given by loadedcash.com
 */
//echo "\n" . $url . "\n\n";//debug

/*
 * the following 3 lines are given by loadedcash.com
 */
//$response = file_get_contents($url);
//var_dump($response);
//$xml = simplexml_load_string($response);

/*
 * and we change and optimize the above 3 lines as the following block goes
 */
$response = file_get_contents($url);
if ($response === false) {
	exit(sprintf("\nFailed to get stats data.(%s)\n", $url));
}
//echo "var_dump\n";//for debug
//var_dump($response);//for debug
//echo "var_dump\n";//for debug
$xml = simplexml_load_string($response);

if ($xml === false) {
	exit(sprintf("\nFailed to get stats data.(%s)\n", $url));
}
foreach ($xml as $node => $values) {
	echo $node . " =>"
		. "\n" . $values->date 
		. "\n" . $values->campaign_label 
		. "\n" . $values->campaign_name
		. "\n" . $values->uniques
		. "\n" . $values->frees
		. "\n" . $values->signups
		. "\n";
}
?>