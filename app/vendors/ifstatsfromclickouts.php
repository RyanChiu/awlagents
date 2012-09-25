<?php
/**
 * The script takes 3 parameters: username, site id and date.
 * And if username is 'all' and site id is '0', then they means
 * that we should check all the abnormal possibilities on that date.
 * It'll analyze trans_clickouts and trans_stats and try to figure
 * out that if all the stats data are from the click outs redirected
 * by our site. Suggest that if the count for clicouts is bigger
 * than the sum for stats, we assume that it's normal, otherwise
 * it'll be regarded as abnormal.
 */
include "zmysqlConn.class.php";
include "extrakits.inc.php";

if (($argc - 1) < 3) {
	exit("3 parameters needed like 'AA01 3 2010-05-01'.\nThe 1st one is username, the 2nd one is site id, and the 3rd one is date you wanna check.\n");
}

$zconn = new zmysqlConn;

$date = $argv[3];
$agent_sites = array($argv[1] => $argv[2]);
if ($argv[1] == 'all' && $argv[2] == '0') {
	$agent_sites = array();
	$sql = sprintf(
		"select distinct username, siteid from trans_view_stats"
			. " where convert(trxtime, date) = '%s'"
		, $date
	);
	//echo $sql . "\n"; //for debug
	$rs = mysql_query($sql, $zconn->dblink)
		or die ("Something wrong with: " . mysql_error());
	while ($r = mysql_fetch_assoc($rs)) {
		$agent_sites[$r['username']] = $r['siteid'];
	}
}
//var_dump($agent_sites); echo "\n"; exit();//for debug

/*
 * $flag
 * = 0, which is the default velue, means "show the counts anyway"
 * = 1, means "show the abnormal counts only"
 * = 2, means "show the abnormal counts and the details"
 */
$flag = $argc - 1 > 3 ? $argv[4] : 0;

$abn = 0;//means abnormal counts
foreach ($agent_sites as $username => $siteid) {
	$sql = sprintf( 
		"select *, addtime( clicktime, '-12:00:00.000000' ) AS ny_clicktime"
		. " from trans_view_clickouts"
		. " where convert(addtime( clicktime, '-12:00:00.000000') , date) = '%s'"
		. "  and username = '%s' and siteid = %d",
		$date, $username, $siteid
	);
	//echo $sql . "\n"; //for debug
	$rs_clickouts = mysql_query($sql, $zconn->dblink)
		or die ("Something wrong with: " . mysql_error());
	$num_clickouts = mysql_num_rows($rs_clickouts);
	
	$sql = sprintf(
		"select * from trans_view_stats"
		. " where convert(trxtime, date) = '%s'"
		. " and username = '%s' and siteid = %d",
		$date, $username, $siteid
	);
	//echo $sql . "\n"; //for debug
	$rs_stats = mysql_query($sql, $zconn->dblink)
		or die ("Something wrong with: " . mysql_error());
	$num_clicks = -1;
	if (mysql_num_rows($rs_stats) != 0) {
		$r = mysql_fetch_assoc($rs_stats);
		$num_clicks = $r['raws'] + $r['uniques'] + $r['signups'] + $r['sales_number'];
	}
	mysql_data_seek($rs_stats, 0);
	
	switch ($flag) {
		case 0:
			echo sprintf(
				"%d clickout(s) there. %d click(s) in stats there.\n"
				, $num_clickouts, $num_clicks
			);
			break;
		case 1:
			if ($num_clickouts < $num_clicks) {
				$abn++;
				echo sprintf(
					"abnormal: %d clickout(s) there. %d click(s) in stats there.\n"
					, $num_clickouts, $num_clicks
				);
			}
			break;
		case 2:
			if ($num_clickouts < $num_clicks) {
				$abn++;
				echo sprintf(
						"abnormal: %d clickout(s) there. %d click(s) in stats there.\n"
						, $num_clickouts, $num_clicks
				);
				/*
				 * then we show the deatils below
				 */
				include_once 'datagrid.class.php';
				$grid_clickouts = new dataGrid(
					$rs_clickouts, "",
					array(
						0 => "companyid", 1 => "officename", 2 => "agentid",
						3 => "username", 4 => "clicktime", 5 => "ny_clicktime",
						6 => "fromip", 7 => "referer", 8 => "siteid", 9 => "sitename",
						10 => "typeid", 11 => "typename", 12 => "url"
					),
					'', '', '#cccddd', 600
				);
				$grid_clicks = new dataGrid(
					$rs_stats, "",
					array(
						0 => "trxtime", 1 => "companyid", 2 => "officename",
						3 => "agentid", 4 => "username", 5 => "siteid",
						6 => "sitename", 7 => "typeid", 8 => "typename",
						9 => "price", 10 => "earning", 11 => "raws",
						12 => "uniques", 13 => "chargebacks", 14 => "signups",
						15 => "frauds", 16 => "sales_number", 17 => "net",
						18 => "payouts", 19 => "earnings"
					),
					'', '', '#dddeee', 800
				);
				$grid_clickouts->create();
				$grid_clicks->create();
				echo "\n";
			}
			break;
	}
}
echo "(abnormal)" . $abn . "/(total)" . count($agent_sites) . "\n";
?>