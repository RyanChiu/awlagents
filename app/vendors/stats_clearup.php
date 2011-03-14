<?php
/**
 * This driver could accept 2 parameters and clear up the trans_run_stats & trans_tmp_stats.
 * 1st one is a date, 2nd one is a digit means how many days before the date should be delete.
 * And it will check if the date is earlier than today, if not, it'll exit with warnings.
 */

include 'zmysqlConn.class.php';

$enddate = $argv[1];
$days = (int)$argv[2];
if (($argc - 1) != 2 || strtotime($enddate) == -1 || $days <= 0) {
	exit("It must take 2 params, date & days, like \"2010-05-01 3\". Please try again.\n");
}
$startdate = date("Y-m-d", strtotime("-" . ($days - 1) . " day", strtotime($enddate)));
if ($startdate >= date("Y-m-d")) {
	$errmsg = sprintf(
		"The starting date (%s) according to the params is not before today.\nPlease try again.\n",
		$startdate
	);
	exit($errmsg);
}

if ($enddate >= date("Y-m-d")) {
	$errmsg = sprintf(
		"Attention!!\nWe will only clearup from %s to yesterday.\n",
		$startdate
	);
	echo $errmsg;
}

/*dealing with the data*/
$zconn = new zmysqlConn();
$datecond = sprintf(" where convert(runtime, date) >= '%s' and convert(runtime, date) <= '%s'",
	$startdate, $enddate
);
$sql = sprintf(
	"delete from trans_tmp_stats"
	. " where runid in"
	. " (select id from trans_run_stats"
	. $datecond . ")"
);
//echo $sql . "\n";
$rs = mysql_query($sql, $zconn->dblink)
	or die ("Something wrong with: " . mysql_error());
echo mysql_affected_rows() . " records deleted.(tmp stats)\n";
$sql = sprintf(
	"delete from trans_run_stats"
	. $datecond
);
//echo $sql . "\n";
$rs = mysql_query($sql, $zconn->dblink)
	or die ("Something wrong with: " . mysql_error());
echo mysql_affected_rows() . " records deleted.(run ids)\n";
echo "Clearup done.(" . date("Y-m-d H:i:s") . ")\n";
?>