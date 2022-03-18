<?php
$cbr = download("http://www.cbr.ru/scripts/XML_daily.asp", false);
if (strpos($cbr, '<ValCurs Date')!==false)
{
	file_put_contents(__DIR__ ."/logs/cbr_rates.xml", $cbr);
	$cbrxml = new SimpleXMLElement($cbr);
	foreach ($cbrxml->Valute as $valute)
	{
		if ($valute->NumCode==840)
		{
			//echo "<pre>"; print_r($valute); echo "</pre>";
			$usdrate = str_replace(',', '.', $valute->Value);
			$usdrate += 0.0;
		}
	}
	echo "CBR RUB / USD RATE = ".$usdrate." <br>\r\n";	
	file_put_contents(__DIR__."/inc/configs/cbr.txt", $usdrate);
}


function download($url, $post)
{
	$c = curl_init();
	curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 5.1; rv:52.0) Gecko/20100101 Firefox/52.0");
	curl_setopt($c, CURLOPT_URL, $url);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($c, CURLOPT_HTTPHEADER, array(
		'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
	));
	if ($post)
	{
		curl_setopt($c, CURLOPT_POST, TRUE);
		curl_setopt($c, CURLOPT_POSTFIELDS, $post);
	}
	$b = curl_exec($c);
	$e = curl_error($c);
	if ($e) echo $e."<br>\r\n";
	curl_close($c);
	return $b;
}
?>