<?php
/*
 * it will call each individualy stats php drivers.i.e., it will call cam4_stats.php,
 * swf_stats.php etc.when called "daily", it will call each individual stats and pull
 * that day's stats i.e.
 * stats.php daily 2010-05-19,12:34:56
 * or
 * stats.php biweekly 2010-05-19,12:34:56
 */

include 'zmysqlConn.class.php';
include 'extrakits.inc.php';

if (($argc - 1) == 2) {
	if (!in_array($argv[1], array('daily', 'biweekly'))) {
		exit("The 1st param is daily or biweekly, please try again.\n");
	}
} else {
	exit("It must take 2 parameters, please try again.\n");
}
$path_parts = pathinfo($argv[0]);
$date = __get_remote_date($argv[2]);
if ($date === false) {
	exit("The 2nd param must be like this: 2010-05-01, please try again.\n");
}
$arydt = explode(",", $argv[2]);
$ymd = explode("-", $arydt[0]);
$his = explode(":", $arydt[1]);

$dates = array();
if ($argv[1] == 'daily') {
	array_push($dates, $argv[2]);
} else if ($argv[1] == 'biweekly') {
	for ($i = 0; $i < 14; $i++) {
		$date = date('Y-m-d,H:i:s', mktime($his[0], $his[1], $his[2], $ymd[1], $ymd[2] - $i, $ymd[0]));
		array_push($dates, $date);
	}
}

$zconn = new zmysqlConn();

/*find each everyone abbr from trans_sites*/
$sql = sprintf('select abbr from trans_sites');
$rs = mysql_query($sql, $zconn->dblink)
	or die ("Something wrong with: " . mysql_error());
/*
 * and see if driver file 'abbr'_stats.php exists, if it does, then execute it one by
 * one with specified argvs of this file.
*/
while ($row = mysql_fetch_assoc($rs)) {
	$fn = $path_parts["dirname"] . "/" . $row['abbr'] . '_stats.php';
	if (file_exists($fn)) {
		/*we could execute the drivers here ...*/
		//echo $fn . "\n";
		//echo print_r($dates, true) . "\n";
		for ($i = 0; $i < count($dates); $i++) {
			echo "--==execute " . $fn . " start.==--(D" . $i . " with parameter: " . $dates[$i]. ")\n";
			$output = array();
			exec("php " . $fn . " " . $dates[$i], $output);
			echo implode("\n", $output) . "\n";
			echo "--==execute " . $fn . " end.==--(with server time: " . date('Y-m-d H:i:s') . ")\n";
		}
	} else {
		echo "File " . $fn . " does not exist.\n";
	}
}
?>