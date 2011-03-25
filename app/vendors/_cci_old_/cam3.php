<?php
require('magpierss/rss_fetch.inc');
$url='http://webmasters.cams4pleasure.com/custom/campaigns.php?username=mac2010&password=riverview1';
$scrape_ch = curl_init();
curl_setopt($scrape_ch, CURLOPT_URL, $url);
curl_setopt($scrape_ch, CURLOPT_RETURNTRANSFER, true); 

$scrape = curl_exec( $scrape_ch );
$scrape = str_replace( "&", "&#x26;", $scrape );	# & encoding
curl_close($scrape_ch);
$rss = @new MagpieRSS($scrape);
echo "Site: ", $rss->channel['title'], "<br>";
foreach ($rss->items as $item ) {
        $name = $item[name];
        $link1   = $item[link_type1];
        echo "<a href=$link1>$name->$link1</a></li><br>";
}

?>
