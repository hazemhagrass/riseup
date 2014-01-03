<?php
define("ROOT", 'scrapper/');
define("PAGES", 'pages');

include_once 'src/facebook.php';

$appId = '238816542959619';
$secret = '59d334831df66a763c29e94197350122';
$returnurl = 'http://www.facebook.com/riseupquote';
$permissions = 'manage_pages,offline_access,publish_stream';

//read pages to scrap from pages file
$pagesFileContent = file_get_contents(PAGES);
$pages = explode("\n", $pagesFileContent);

$fb = new Facebook(array('appId'=>$appId, 'secret'=>$secret));

function retrievePagePhotos ($fb, $access_token, $pageId){
	$photos = array();
	$args = array('access_token' => $access_token);
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

$fbuser = $fb->getUser();
$access_token = $fb->getAccessToken();
if($fbuser){
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

}else{
	$fbloginurl = $fb->getLoginUrl(array('redirect-uri'=>$returnurl,
		'scope'=>$permissions, 'fbconnect' => 1));
	echo '<a id="login" href="'.$fbloginurl.'">Login with Facebook</a>';
}

?>

<script>
var el = document.getElementById('login');
if(el != null)
	el.click();
</script>