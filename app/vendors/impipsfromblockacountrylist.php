<?php
/*
* This php file save the ips into the db
* 
* This driver need to be passed through a parameter named 'country' 
* which should be a abbr name of country or area, such as
* 'US' means 'United States' or 'GB' means 'United Kingdom',
* which following the ISO standard.
* And also it needs a text file at the same directory named
* with the country name the way amentioned above, such as
* 'US.txt' and so on.
* And each line of the text file should be like this:
* 202.101.0.6/24
* A calling sample is: ./impipsfromblockacountrylist.php?country=US
*/
include 'zmysqlConn.class.php';

if (isset($_GET['country'])) {
	$fn = $_GET['country'];
	if (file_exists($fn . '.txt')) {
		$handle = fopen($fn . '.txt', 'r');
		if ($handle) {
			/*delete all records that countryabbr is $fn in DB*/
			$zconn = new zmysqlConn();
			mysql_select_db("zcakephp",$zconn->dblink)
				or die ("Something wrong with: " . mysql_error());
			$sql = 'delete from trans_ipranges where countryabbr = "' . $fn . '"';
			mysql_query($sql)
				or die ("Something wrong with:\n" . mysql_error());
			//prepare to insert all the new records
			$sql = 'insert into trans_ipranges (startip, endip, countryabbr) values ';
				
			$i = 0;
			$values = array();
			$value = array();
			while (!feof($handle)) {
				$buf = fgets($handle);
				$subnet = explode('/', $buf);
				if (count($subnet) == 2) {
					$ip = ip2long($subnet[0]); 
					$nm = 0xffffffff << (32 - $subnet[1]); 
					$nw = ($ip & $nm); 
					$bc = $nw | (~$nm);
					$i++;
					
					/*start to save data in db with fields startip, endip and country*/
					array_push($value, sprintf('(%s, %s, "%s")', ip2long(long2ip($nw + 1)), ip2long(long2ip($bc - 1)), $fn));
					if ($i % 50 == 0) {
						array_push($values, $value);
						$value = array();
					}
				}
			}
			array_push($values, $value);
			foreach ($values as $value) {
				if (!empty($value)) {
					mysql_query($sql . implode(',', $value))
						or die ("Something wrong with:\n" . mysql_error());
				}
			}
			echo 'Ips inserted.';
		} else {
			echo 'File "' . $fn . '.txt" cannot be opened.';
		}
	} else {
		echo 'File "' . $fn . '.txt" doesn\'t exist.';
	}
} else {
	echo 'Please send a country, such as US (means United States) or GB (means United Kingdom) following the ISO standard.';
}
?>
