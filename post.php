
<?php
define("ROOT", 'posts/image');
define("ROOT_TEXT", 'posts/text');

define("INDEX_FILE", 'posts_index');
define("IMAGES_TILL_TEXT", 1000);

include_once 'src/facebook.php';
include_once 'src/file_operations.php';

$appId = '238816542959619';
$secret = '59d334831df66a763c29e94197350122';
$returnurl = 'http://www.facebook.com/riseupquote';
$permissions = 'manage_pages,offline_access,publish_stream';

$fb = new Facebook(array('appId'=>$appId, 'secret'=>$secret));

$fbuser = $fb->getUser();
$access_token = $fb->getAccessToken();
if($fbuser){

	try{
		$qry = 'select page_id, name from page where page_id in (select page_id from page_admin where uid ='.$fbuser.')';
		$pages = $fb->api(array('method' => 'fql.query','query' => $qry));

		if(empty($pages)){
			echo 'The user does not have any pages.';
		}else{
			echo 'You have ' . count($pages) . ' pages';
			echo '</br>';
			
			foreach($pages as $page){
				if($page['name'] != 'Rise up')
					continue;

				echo 'You will post to page: {id:' . $page['page_id'].', name: ' . $page['name']. '}';
				echo '</br>';

				$page_info = $fb->api("/" . $page['page_id'] . "?fields=access_token");

				//read index file
				$index = intval(file_get_contents(INDEX_FILE)) + 1;
				
				//post text every IMAGES_TILL_TEXT(=5) images
				$root = $index % IMAGES_TILL_TEXT == 0 ? ROOT_TEXT : ROOT;
				
           //scan images
				// $randomFile = getRandomFile($root);
				$randomFile = getOldestFile($root);
				$path = $root . $randomFile;
				$pathDone = $root . 'published/' . $randomFile;
				
				$text_post = $index % IMAGES_TILL_TEXT == 0 ? file_get_contents($path) : '';
				
        	//post image
				$fb->setFileUploadSupport(true);
				$args = array('message' => $text_post, 'access_token' => $page_info['access_token']);
				if($index % IMAGES_TILL_TEXT == 0){
					$args['picture'] = '';
					$args['caption'] = '';
					$args['description'] = '';
					$result = $fb->api('/' . $page['page_id'] . '/feed', 'POST', $args);
				}else{
				    $args['caption'] = '';
					$args['description'] = '';
					$args['picture'] = '@' . realpath($path);
					$result = $fb->api('/' . $page['page_id'] . '/photos', 'POST', $args);
				}
				

				if($result){
					echo 'Successfully posted to Facebook Wall... post_number: ' . $index;
					echo '</br>';
					file_put_contents(INDEX_FILE, $index);
					copy(realpath($path), $pathDone);
					unlink($path);
				}
			}

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
