<?php
/*
 * The script fillout all the campaingid field in trans_stats
 * with one matched in agent_site_mappings.
 * It takes no parameters at all.
 */
include 'zmysqlConn.class.php';

$zconn = new zmysqlConn;
$sql = "select * from agent_site_mappings where flag = 1";
$rs = mysql_query($sql, $zconn->dblink)
	or die ("Something wrong with: " . mysql_error());
$s = mysql_num_rows($rs);
$d = 0;
while ($r = mysql_fetch_assoc($rs)) {
	$sql = sprintf(
		"update trans_stats set campaignid = '%s'"
		. " where agentid = %d and siteid = %d"
		. " and typeid in (select id from trans_types)",
		$r["campaignid"], $r["agentid"], $r["siteid"]
	);
	mysql_query($sql, $zconn->dblink)
		or die ("Something wrong with: " . mysql_error());
	$d += mysql_affected_rows();
}
echo sprintf("%d/(%d) rows updated.\n", $d, $s);
?>