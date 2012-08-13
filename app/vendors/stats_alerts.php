<?php
/**
 * The script will report (mail out and log at the same time) when find
 * out that there are some abnormal acts from table "dup_stats", which means
 * that if any raws or uniques or signups or frauds or sales_numbers is bigger
 * than @threshold, it'll report.
 * @datetime = argv[1]
 * @threshold = 15 = argv[2]
 */
include 'zmysqlConn.class.php';
include 'extrakits.inc.php';

if (($argc - 1) < 1) {//if there is 1 parameter and it must mean a date like '2010-04-01,12:34:56'
	exit("At least 1 parameter needed like '2010-05-01,12:34:56'.\n");
}

$threshold = 15;
if (($argc - 1) >= 2) {
	$threshold = $argv[2];
	if (! is_numeric($threshold) || $threshold < 1) {
		exit("Threshold must be an integer and bigger than 0.\n");
	}
}

/*
 * the following line will make the whole script exit if date string format is wrong
*/
$date = __get_remote_date($argv[1], "America/New_York", -5);
$date_l = __get_remote_date($argv[1], "America/New_York", -5, "America/New_York", true);

$zconn = new zmysqlConn;
$sql = sprintf(
	"select * from dup_stats where convert(trxtime, date) = '%s'"
	. " and (raws >= %d or uniques >= %d or chargebacks >= %d or signups >= %d or frauds >= %d or sales_number >= %d)"
	. " order by trxtime desc",
	$date, $threshold, $threshold, $threshold, $threshold, $threshold, $threshold
);
//echo $sql . "\n"; //for debug

$rs = mysql_query($sql, $zconn->dblink)
	or die ("Something wrong with: " . mysql_error());
if (mysql_num_rows($rs) != 0) {
	$lines = '';
	while ($r = mysql_fetch_assoc($rs)) {
		foreach ($r as $k => $v) {
			$lines .= ($k . ":" . $v . " | ");
		}
		$lines .= "\n";
	}
	
	$mailinfo =
		__phpmail("agents.maintainer@gmail.com",
			"AWL STATS ALERTS ($date_l, threshold: $threshold, row(s): " . mysql_num_rows($rs) . ")",
			$lines
		);
	exit($mailinfo . "\n");
} else {
	echo "No alerts.\n";
}
?>