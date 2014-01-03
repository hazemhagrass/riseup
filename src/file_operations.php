<?php
function cmp_date($a, $b)
{	
	$date1 = intval($a['date']);
	$date2 = intval($b['date']);
	
	if ($date1 == $date2) {
        return 0;
    }
    return ($date1 < $date2) ? -1 : 1;
}
function getFilesSorted($root){
	$files = array();
	$dir = new DirectoryIterator($root);
	foreach ($dir as $fileinfo) {     
		if(!is_dir(realpath($root . $fileinfo->getFilename() . '/'))){
			array_push($files, array('date' => $fileinfo->getMTime(), 'path' => $fileinfo->getFilename()));
		}
	}
	
	return $files;
}
function getRandomFile($root){
	$files = getFilesSorted($root);

	$randomIndex = rand(0, count($files) - 1);
	$index = 0;
	foreach ($files as $file) {     
		if($index == $randomIndex)
			break;
		$index ++;
	}

	return $file;
}

function getOldestFile($root){
	$files = getFilesSorted($root);
	
	usort($files, 'cmp_date');

	//get random file from the oldest 100
	$maxChunkSize = 100;
	$randomIndex = rand(0, $maxChunkSize - 1);
	$file = $files[$randomIndex];

	return $file['path'];
}
?>