<?php if (!defined('BASEPATH')) exit('No direct script access allowed');


if (! function_exists('upload_to_s3')) {

	function upload_to_s3($uploadFilePath, $uploadFileName, $accessKey, $secretKey) {
		if(isset($uploadFilePath)){

		  	require(APPPATH.'libraries/S3.php');
		  	//configurations
		  	$BASE_URL= 'https://s3-ap-southeast-1.amazonaws.com/';
		  	$bucket = 'taxibook';

		  	$target = $uploadFilePath;

	  		// downsize image if it is too large
		  	$image_info_array = getimagesize($target);
		  	$width = 0; $height = 0;
		  	if (array_key_exists(0, $image_info_array)){
		  		$width = $image_info_array[0];
		  	}
		  	if (array_key_exists(1, $image_info_array)) {
		  		$height = $image_info_array[1];
		  	}
		  	$resize_flag = 0;
		  	if ($width >= 640) {
		  		$resize_flag = 1;
		  	}
		  	if ($height >= 640) {
		  		$resize_flag = 1;
		  	}

		  	if ($resize_flag) {
		  		$min_dimension = min($width, $height);

		  		$ratio = 640/$min_dimension;
		  		$newwidth = $width * $ratio;
				$newheight = $height * $ratio;

				// Load
				$thumb = imagecreatetruecolor($newwidth, $newheight);
				$image_type = $image_info_array[2];
				if ($image_type == IMAGETYPE_JPEG) {
					$source = imagecreatefromjpeg($target);	
				} else {
					$source = imagecreatefrompng($target);
				}
				

				// Resize
				imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

				// Save the source and rename the source
				$newTarget = getcwd().'/uploads/'.basename('resize_'. $uploadFileName);
				$image_type = $image_info_array[2];
				if ($image_type == IMAGETYPE_JPEG) {
					imagejpeg($thumb, $newTarget);
				} else { // assume it is a png
					imagepng($thumb, $newTarget);
				}
				imagedestroy($thumb);
				imagedestroy($source);
				unlink($target);
				$target = $newTarget;
		  	}


		  	//Create a S3 instance
			$s3 = new S3($accessKey, $secretKey);

			$bytes = openssl_random_pseudo_bytes(64, $cstrong);
			$file_name = bin2hex($bytes);

			$extension_array = explode('.', $uploadFilePath);
			$extension = '.' . $extension_array[count($extension_array) - 1];

			$file_name = md5($uploadFileName) . '_' .$file_name . $extension;

			try {
				S3::putObject(S3::inputFile($target,false), $bucket , $file_name, 
					S3::ACL_PUBLIC_READ, array(), array( 'Expect' => '100-continue', "Cache-Control" => "max-age=31536000", 
						"Expires" => gmdate("D, d M Y H:i:s T", strtotime("+1 years"))));	
			} catch (Exception $e) {
				// remove the file first
				unlink($target);
				error_log($e->get_message());
				return FALSE;
			}

			// $obj_info = S3::getObjectInfo('sensbeat_testing', $_FILES['file']['name']);
			unlink($target); // remove the file in the server upload folder
			//return object url
			return $BASE_URL . $bucket . '/' . $file_name;
		}
	}


}