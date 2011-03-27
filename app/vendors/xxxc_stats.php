<?php
include 'zmysqlConn.class.php';
include 'extrakits.inc.php';

if (($argc - 1) != 1) {//if there is 1 parameter and it must mean a date like '2010-04-01,12:34:56'
	exit("Only 1 parameter needed like '2010-05-01,12:34:56'.\n");
}

/*
 * the following line will make the whole script exit if date string format is wrong
 */
$date = __get_remote_date($argv[1], "Europe/London", -1);

/*get the abbreviation of the site*/
$abbr = __stats_get_abbr($argv[0]);

/*find out the typeids and siteid from db by "xxxc" which is the abbreviation of the site*/
$typeids = array();
$siteid = null;
$zconn = new zmysqlConn;
__stats_get_types_site($typeids, $siteid, $abbr, $zconn->dblink);
if (count($typeids) != 1) {
	exit(sprintf("The site with abbreviation \"%s\" should have 1 type at least.\n", $abbr));
}
if (empty($siteid)) {
	exit(sprintf("The site with abbreviation \"%s\" does not exist.\n", $abbr));
}
/*get all the campaign mappings of the site*/
$sql = sprintf("select * from view_mappings where siteid = %d", $siteid);
$rs = mysql_query($sql, $zconn->dblink)
	or die ("Something wrong with: " . mysql_error());
$agents = array();
while ($row = mysql_fetch_assoc($rs)) {
	$agents += array($row['campaignid'] => $row['agentid']);
}
if (empty($agents)) {
	exit(sprintf("The site with abbreviation \"%s\" does not have any campaign ids asigned for agents.\n", $abbr));
}

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
$retimes = 0;
$response = file_get_contents($url);
while ($response === false) {
	$retimes++;
	sleep(35);
	$response = file_get_contents($url);
	if ($retimes == 1) break;
}
if ($response === false) {
	$mailinfo = 
		__phpmail("maintainer.cci@gmail.com",
			"XCUP STATS GETTING ERROR, REPORT WITH DATE: " . date('Y-m-d H:i:s') . "(retried " . $retimes . " times)",
			"<b>FROM WEB02</b><br><b>--ERROR REPORT</b><br>"
		);
	exit(sprintf("Failed to read stats data.(%s)(%d times)\n", $mailinfo, $retimes));
}
//echo "var_dump\n";//for debug
//var_dump($response);//for debug
//echo "var_dump\n";//for debug
$xml = simplexml_load_string($response);

if ($xml === false) {
	exit(sprintf("\nFailed to parse stats data.\n"));
}

$i = $j = $m = 0;
foreach ($xml as $node => $values) {
	echo $node . " =>"
		. "\n" . $values->date 
		. "\n" . $values->campaign_label 
		. "\n" . $values->campaign_name
		. "\n" . $values->uniques
		. "\n" . $values->frees
		. "\n" . $values->signups
		. "\nfor debug\n";
		
	if (in_array($values->campaign_name, array_keys($agents))) {//compare campaign_name as campaignid
		echo $values->campaign_name . "," . $agents['' . $values->campaign_name] . ";\n"; continue;//for debug
		/*
		 * try to put stats data into db
		 * 0.see if there is any frauds data except 0 or null, if there is, remember it and save it back in step 2
		 * 1.delete the data already exist
		 * 2.insert the new data
		 */
		$frauds = 0;
		$conditions = sprintf('convert(trxtime, date) = "%s" and siteid = %d'
			. ' and typeid = %d and agentid = %d and campaignid = "%s"',
			$date, $siteid, $typeids[0], $agents['' . $values->campaign_name], '' . $values->campaign_name);
		$sql = 'select * from trans_stats where ' . $conditions;
		$result = mysql_query($sql, $zconn->dblink)
			or die ("Something wrong with: " . mysql_error());
		if (mysql_num_rows($result) != 0) {
			if (mysql_num_rows($result) != 1) {
				exit("It should be only 1 row data by day.\n");
			}
			$row = mysql_fetch_assoc($result);
			$frauds = empty($row['frauds']) ? 0 : $row['frauds'];
		}
		
		$sql = 'delete from trans_stats where ' . $conditions;
		mysql_query($sql, $zconn->dblink)
			or die ("Something wrong with: " . mysql_error());
		$m += mysql_affected_rows();
		
		$sql = sprintf(
			'insert into trans_stats'
			. ' (agentid, campaignid, siteid, typeid, raws, uniques, chargebacks, signups, frauds, sales_number, trxtime)'
			. ' values (%d, "%s", %d, %d, 0, %d, 0, %d, %d, %d, "%s")',
			$agents['' . $values->campaign_name], '' . $values->campaign_name, $siteid, $typeids[0],
			$values->uniques, $values->free, $frauds, $values->signups,
			$date
		);
		//echo $sql . "\n"; continue;//for debug
		mysql_query($sql, $zconn->dblink)
			or die ("Something wrong with: " . mysql_error());
		$j += mysql_affected_rows();
		$i++;
	}
}
if ($i == 0) {
	echo "No stats data exist by now.\n";
}
echo $m . " row(s) deleted.\n";
echo $j . "(/" . $i . ") row(s) inserted.\n";
echo "retried " . $retimes . " times.\n";
echo "Processing " . $date . " OK\n";
?>