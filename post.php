
<?php
define("ROOT", 'posts/image/');
define("ROOT_TEXT", 'posts/text/');

define("INDEX_FILE", 'posts_index');
define("IMAGES_TILL_TEXT", 1000);

include_once 'src/facebook.php';
include_once 'src/config.php';
include_once 'src/file_operations.php';

function swap_access_token_for_page($access_token, $page_id)
{
    $accounts = file_get_contents('https://graph.facebook.com/me/accounts?access_token='.$access_token);
    $accounts = json_decode($accounts, true);
 
    if (isset($accounts['data'])) {
        foreach ($accounts['data'] as $account) {
            if ($page_id == $account['id']) {
                return $account['access_token'];
            }
        }
    }
}


	try{
		$fb = new Facebook(array('appId'=>$config['app_id'], 'secret'=>$config['app_secret']));
		echo 'Retrieving page data: {id:' . $config['page_id'].', name: ' . 'riseup'. '}';
		echo '</br>';


		//read index file
		$index = intval(file_get_contents(INDEX_FILE)) + 1;
		
		//post text every IMAGES_TILL_TEXT(=5) images
		$root = $index % IMAGES_TILL_TEXT == 0 ? ROOT_TEXT : ROOT;
		
		$page_info = $fb->api("https://graph.facebook.com/" . $config['page_id'] . "?fields=access_token");
		echo 'Post is prepared';
		echo '</br>';

        //scan images
		// $randomFile = getRandomFile($root);
		$randomFile = getOldestFile($root);
		$path = $root . $randomFile;
		$pathDone = $root . 'published/' . $randomFile;
		
		$text_post = $index % IMAGES_TILL_TEXT == 0 ? file_get_contents($path) : '';
		
		$new_access_token = swap_access_token_for_page($config['offline_access_token'], $config['page_id']);


		//post image
		$fb->setFileUploadSupport(true);
		$args = array('message' => $text_post, 'access_token' => $new_access_token);
		if($index % IMAGES_TILL_TEXT == 0){
			$args['picture'] = '';
			$args['caption'] = '';
			$args['description'] = '';
			$result = $fb->api('/' . $config['page_id'] . '/feed', 'POST', $args);
		}else{
		    $args['caption'] = '';
			$args['description'] = '';
			$args['picture'] = '@' . realpath($path);
			$result = $fb->api('/' . $config['page_id'] . '/photos', 'POST', $args);
		}
		

		if($result){
			echo 'Successfully posted to Facebook Wall... post_number: ' . $index;
			echo '</br>';
			file_put_contents(INDEX_FILE, $index);
			copy(realpath($path), $pathDone);
			unlink($path);
		}

	}catch(FacebookApiException $e){
		echo $e->getMessage();
	}

