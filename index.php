<?php


set_time_limit(10000);

$siteUrl = "http://koa.com";

include("PHPCrawl/libs/PHPCrawler.class.php");
include_once('PHPCrawl/lib/simple_html_dom.php');


function get_data($url) {

	$header[0] = "Accept: text/xml,application/xml,application/xhtml+xml, text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
	$header[] = "Cache-Control: max-age=0";
	$header[] = "Connection: keep-alive";
	$header[] = "Keep-Alive: 300";
	$header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
	$header[] = "Accept-Language: en-us,en;q=0.5";

	$ch = curl_init();
	$timeout = 5;

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; U; Linux x86_64; en-US) AppleWebKit/534.3 (KHTML, like Gecko) Ubuntu/10.04 Chromium/6.0.472.53 Chrome/6.0.472.53 Safari/534.3');
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$data = curl_exec($ch);
	return $data;
}
$url = 'links.html';
//$returned_content = get_data('http://koa.com/states-provinces/');
$html = file_get_html($url);


//file_put_contents($file, $returned_content);
//echo $html;

	$getUrls = $html->find('div.page-content div.col-md-9 div.col-md-12 ul li a');



$mysqli = mysqli_connect('localhost', 'root', '', 'craw3');

if (mysqli_connect_errno()) {
	printf("Подключение к серверу MySQL невозможно. Код ошибки: %s\n", mysqli_connect_error());
	exit;
}

foreach ($getUrls as $getUrl) {
	$stateUrl = $siteUrl.$getUrl->attr['href'];
	$stateTitle = addslashes($getUrl->attr['title']);
	$html2 = get_data($stateUrl);
	$htmlObject = str_get_html($html2);
	$getUrls2 = $htmlObject->find('div.state-listings-container div.col-sm-7 div.row div.campground-listing a.btn');

	foreach($getUrls2 as  $getUrl2) {
		$campUrl = $siteUrl.$getUrl2->attr['href'];
		$html3 = get_data($campUrl);
		$htmlObject2 = str_get_html($html3);
		$region = addslashes($htmlObject2->find('div.campground-header h1')[0]->nodes[0]->_[4]);
		$reserve_pn = addslashes($htmlObject2->find('div.campground-header-info div.col-sm-6 p')[1]->nodes[1]->_[4]);
		$info_pn = addslashes($htmlObject2->find('div.campground-header-info div.col-sm-6 p')[2]->nodes[1]->_[4]);
		$email = $htmlObject2->find('div.campground-header-info div.col-sm-6 p')[3]->nodes[0]->nodes[1]->parent->attr['href'];
		$email = addslashes(substr($email,7));
		$address = addslashes($htmlObject2->find('div.campground-header-info div.col-sm-6 p')[5]->nodes[0]->_[4]);
		$short_desc = addslashes($htmlObject2->find('div#clp-welcome-message')[0]->plaintext);
		$short_desc = trim($short_desc);
		$their_website = addslashes($htmlObject2->find('div.clp-body div.col-md-8 p a')[0]->href);

		echo $stateTitle. "<br />\n", $stateUrl. "<br />\n" ;
		echo $region. "<br />\n",$reserve_pn. "<br />\n", $info_pn. "<br />\n", $address. "<br />\n", $short_desc. "<br />\n", $their_website. "<br />\n", $email. "<br />\n";
		$query = "INSERT INTO data (state, region, reserve_pn, info_pn, address,	short_desc, their_website, email) VALUES ('".$stateTitle."','".$region."','".$reserve_pn."','".$info_pn."','".$address."','".$short_desc."','".$their_website."','".$email."')";

		mysqli_query($mysqli, $query);
	}
}
mysqli_close($mysqli);
