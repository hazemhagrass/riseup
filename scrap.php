<?php
define("ROOT", 'scrapper/');
define("PAGES", 'pages');

include_once 'src/facebook.php';
include_once 'src/config.php';


//read pages to scrap from pages file
$pagesFileContent = file_get_contents(PAGES);
$pages = explode("\n", $pagesFileContent);

$fb = new Facebook(array('appId'=>$config['app_id'], 'secret'=>$config['app_secret']));

function retrievePagePhotos ($fb, $page_access_token, $pageId){
	$photos = array();
	$args = array('access_token' => $page_access_token);
	$publicFeed = $fb->api('/' . $pageId . '/albums?fields=photos.limit(5).fields(picture,source)',
		$args);

	$recordsJson = $publicFeed['data'];
	foreach ($recordsJson as $recordJson){
		$photosJson = $recordJson['photos'];
		$photosDataJson = $photosJson['data'];
		foreach ($photosDataJson as $photoDataJson){
			$isFreshPost = (date('Ymd') == date('Ymd', strtotime($photoDataJson['created_time'])));
			if(!$isFreshPost)
				continue;
			array_push($photos, $photoDataJson['source']);
		}
	}
	return $photos;
}

function downloadPhoto($localPath, $url){

	$urlSplits = explode("/", $url);
	$photoName = $urlSplits[count($urlSplits) - 1];

	$photo = $localPath . $photoName;
	if(file_exists($photo))
		return;

	echo "new photo: " . $photoName;
	echo "</br>";

	file_put_contents($photo, file_get_contents($url));
}

$access_token = $config['app_access_token'];

try{
	foreach ($pages as $page){
		$pageUrl = ROOT . $page . '/';
		if (!file_exists($pageUrl)) 
			mkdir($pageUrl, 0777);

		echo "==================================";
		echo "</br>";
		echo "Page: " . $page;
		echo "</br>";
		echo "==================================";
		echo "</br>";
		$photos = retrievePagePhotos($fb, $access_token, $page);
		echo "number of photos: " . count($photos);
		echo "</br>";
		echo "----------------------------------";
		echo "</br>";

		foreach ($photos as $photo){
			downloadPhoto($pageUrl, $photo);
		}
		echo "</br>";
		echo "</br>";
		echo "</br>";
	}

}catch(FacebookApiException $e){
	echo $e->getMessage();
}


?>
