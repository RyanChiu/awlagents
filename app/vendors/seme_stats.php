<?php
include 'zmysqlConn.class.php';
include 'extrakits.inc.php';

if (($argc - 1) != 1) {//if there is 1 parameter and it must mean a date like '2010-04-01,12:34:56'
	exit("Only 1 parameter needed like '2010-05-01,12:34:56'.\n");
}

/*
 * the following line will make the whole script exit if date string format is wrong
 */
$date = __get_remote_date($argv[1], "America/New_York", -1);
$date_l = __get_remote_date($argv[1], "America/New_York", -1, "America/New_York", true);

/*get the abbreviation of the site*/
$abbr = __stats_get_abbr($argv[0]);

/*find out the typeids and siteid from db by "mps" which is the abbreviation of the site*/
$typeids = array();
$siteid = null;
$zconn = new zmysqlConn;
__stats_get_types_site($typeids, $siteid, $abbr, $zconn->dblink);
if (empty($siteid)) {
	exit(sprintf("The site with abbreviation \"%s\" does not exist.\n", $abbr));
}
if (count($typeids) != 2) {
	exit(sprintf("The site with abbreviation \"%s\" should have 2 type at least.\n", $abbr));
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

/*try to read the stats from remote server*/
$url = sprintf(
	'http://www.morepesos.com/xml_stats/sales/parent/day?username=JADE02&password=aomangels&p=child&ds=%s',
	$date
);
//#debug echo $url . "\n";
$retimes = 0;
$xml = simplexml_load_file($url);
while ($xml === false) {
	$retimes++;
	sleep(30);
	$xml = simplexml_load_file($url);
	if ($retimes == 3) break;
}
	
if ($xml === false) {
	$mailinfo = 
	__phpmail("ray.october@gmail.com",
		"MPS STATS GETTING ERROR, REPORT WITH DATE: " . date('Y-m-d H:i:s') . "(retried " . $retimes . " times)",
		"<b>FROM WEB02</b><br><b>--ERROR REPORT</b><br>"
	);
	exit(sprintf("Failed to read stats data.(%s)(%d times)\n", $mailinfo, $retimes));
}
$ms = $is = $js = array(0, 0);
foreach ($xml->data->row as $xrow) {
	/*
	 * for debug
	 * begin
	 */
//	echo $xrow['user-id'] . ':' . $rxow['username'] . "\n";
//	foreach ($xrow->children() as $child) {
//		echo $child->getName() . ":" 
//			. intval($child->raw) . "," 
//			. intval($child->unique) . "," 
//			. intval($child->sales) 
//			. "\n";
//	}
//	echo "~~~~~~~\n";
	/*
	 * for debug
	 * end
	 */
	$campaignid = intval($xrow['user-id']);
	$xraws = $xuniques = $xsales = $xdenieds = $xpendings = $xrevokeds = array();
	foreach ($xrow->children() as $child) {
		array_push($xraws, intval($child->raw));
		array_push($xuniques, intval($child->unique));
		array_push($xsales, intval($child->sales));
		array_push($xdenieds, intval($child->denied));
		array_push($xpendings, intval($child->pengding));
		array_push($xrevokeds, intval($child->revoked));
	}
	//echo print_r($xraws, true) . "\n";//for debug
	//echo print_r($xuniques, true) . "\n";//for debug
	//echo print_r($xsales, true) . "\n";//for debug
	if (in_array($campaignid, array_keys($agents))) {
		//echo $campaignid . "," . $agents[$campaignid] . ";\n"; continue;//for debug
		/*
		* try to put stats data into db
		* 0.see if there is any frauds data except 0 or null, if there is, remember it and save it back in step 2
		* 1.delete the data already exist
		* 2.insert the new data
		*/
		for ($k = 0; $k < count($typeids); $k++) {
			$frauds = 0;
			$conditions = sprintf('convert(trxtime, date) = "%s" and siteid = %d'
				. ' and typeid = %d and agentid = %d and campaignid = "%s"',
				$date, $siteid, $typeids[$k], $agents[$campaignid], $campaignid);
			$sql = 'select * from trans_stats where ' . $conditions;
			//echo $sql . "\n"; continue;//for debug
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
			//echo $sql . "\n"; continue;//for debug
			mysql_query($sql, $zconn->dblink)
				or die ("Something wrong with: " . mysql_error());
			//echo mysql_affected_rows() . " $k\n";//for debug
			$ms[$k] += mysql_affected_rows();
			
			//only if not all the stats data are zero, we put them into our DB
			if ($xraws[$k] != 0 || $xuniques[$k] != 0 || $xsales[$k] != 0
				|| $xdenieds[$k] != 0 || $xpendings[$k] != 0 || $xrevokeds[$k] != 0) {
				/*
				 * we regard "chargebacks" as revoked, "signups" as pending, "frauds" as denied in here
				 */
				$sql = sprintf(
					'insert into trans_stats'
					. ' (agentid, campaignid, siteid, typeid, raws, uniques, chargebacks, signups, frauds, sales_number, trxtime)'
					. ' values (%d, "%s", %d, %d, %d, %d, %d, %d, %d, %d, "%s")',
					$agents[$campaignid], $campaignid, $siteid, $typeids[$k],
					$xraws[$k], $xuniques[$k], $xrevokeds[$k], $xpendings[$k], $xdenieds[$k], $xsales[$k],
					$date
				);
				//echo $sql . "\n"; continue;//for debug
				mysql_query($sql, $zconn->dblink)
					or die ("Something wrong with: " . mysql_error());
				//echo mysql_affected_rows() . " ~$k\n";//for debug
				$js[$k] += mysql_affected_rows();
				$is[$k]++;
			}
		}
	}
}
for ($k = 0; $k < count($typeids); $k++) {
	echo "type id " . $typeids[$k] . ($is[$k] == 0 ? "(no stats data exist by now)" : "") . ">>>>>>>>>>>>>>>>\n";
	echo $ms[$k] . " row(s) deleted.\n";
	echo $js[$k] . "(/" . $is[$k] . ") row(s) inserted.\n";
	echo "<<<<<<<<<<<<<<<<\n";
}
echo "retried " . $retimes . " time(s).\n";
echo "Just got the stats data from the remote server at '" . $date_l . " on the remote server'.\n";
?>