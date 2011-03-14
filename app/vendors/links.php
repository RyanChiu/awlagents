<?php
/*
 * it will call commdrv_links.php and import from each of every "abbr"_campaigns.txt
 * (like cam4_campaigns.txt etc.) files into the table agent_site_mappings.
 * it takes no param.
 */

include 'zmysqlConn.class.php';

$zconn = new zmysqlConn();

/*find each everyone abbr from trans_sites*/
$sql = sprintf('select id, abbr from trans_sites');
$rs = mysql_query($sql, $zconn->dblink)
	or die ("Something wrong with: " . mysql_error());
/*
 * and see if driver file 'abbr'_stats.php exists, if it does, then execute it one by
 * one with specified argvs of this file.
*/
$path_parts = pathinfo($argv[0]);
while ($row = mysql_fetch_assoc($rs)) {
	$fn = $row['abbr'] . '_campaigns.txt';
	echo "--==import " . $fn . " start.==--\n";
	$output = array();
	//echo "php " . $path_parts["dirname"] . "/commdrv_links.php " . $fn . " " . $row["id"] . "\n";
	exec("php " . $path_parts["dirname"] . "/commdrv_links.php " . $fn . " " . $row["id"], $output);
	echo implode("\n", $output) . "\n";
	echo "--==import " . $fn . " end.==--(" . date('Y-m-d H:i:s') . ")\n";
}
?>
