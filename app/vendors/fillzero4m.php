<?php
include 'zmysqlConn.class.php';

function fillu($str, $forelen = 24, $afterlen = 24) {
	$str0 = $str;
	$str1 = "";
	for ($i = 0; $i < strlen($str); $i++) {
		if ($str{$i} >= '0' && $str{$i} <= '9') {
			$str0 = substr($str, 0, $i);
			$str1 = substr($str, $i, strlen($str) - $i);
			break;
		}
	}
	
	$str0 = $str0 . str_repeat("0", $forelen - strlen($str0));
	$str1 = str_repeat("0", $afterlen - strlen($str1)) . $str1;
	
	$str = substr($str0, 0, $forelen) . substr($str1, 0, $afterlen);
	
	return $str;
}

$zconn = new zmysqlConn();
mysql_select_db("zcleancake", $zconn->dblink)
	or die ("Something wrong with: " . mysql_error());
$sql = sprintf("select id, username from trans_accounts");
$rs = mysql_query($sql, $zconn->dblink)
	or die("Something wrong with: " . mysql_error());
while ($row = mysql_fetch_assoc($rs)) {
	$sql = sprintf("update trans_accounts set username4m = '%s' where id = '%s';",
		fillu($row["username"]), $row["id"]
	);
	mysql_query($sql, $zconn->dblink)
		or die("Something wrong with: " . mysql_error());
	echo "/";
}
?>
