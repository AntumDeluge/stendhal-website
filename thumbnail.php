<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2008  Miguel Angel Blanch Lardin

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once 'configuration.php';

function open_image ($file) {
	// Get extension
	$extension = strrchr($file, '.');
	$extension = strtolower($extension);

	switch($extension) {
		case '.jpg':
		case '.jpeg':
			$im = imagecreatefromjpeg($file);
			break;
		case '.gif':
			$im = imagecreatefromgif($file);
			break;
		case '.png':
			$im = imagecreatefrompng($file);
			break;
		default:
			$im = false;
			break;
	}

	return $im;
}


function createImage($url) {
	// Load image
	$image = open_image($url);
	if ($image === false) {
		die ('Unable to open image');
	}

	// Get original width and height
	$width = imagesx($image);
	$height = imagesy($image);

	// New width and height
	$new_width = 158;
	$new_height = $height * ($new_width/$width);

	// Resample
	$image_resized = imagecreatetruecolor($new_width, $new_height);
	imagecopyresampled($image_resized, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
	return $image_resized;
}
$url = $_GET['img'];
if ((strpos($url, '..') !== false) || (strpos($url, 'screenshots/') !== 0) || (strpos($url, '.') < strlen($url) - 4)) {
	header('HTTP/1.1 404 Not Found', true, 404);
	die("Access denied.");
}

$etag = STENDHAL_VERSION.'-'.sha1($url);
if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
	$requestedEtag = $_SERVER['HTTP_IF_NONE_MATCH'];
}
header("Content-type: image/jpeg");
header("Cache-Control: max-age=3888000, public"); // 45 * 24 * 60 * 60
header('Pragma: cache');
header('Etag: "'.$etag.'"');

if (isset($requestedEtag) && (($requestedEtag == $etag) || ($requestedEtag == '"'.$etag.'"'))) {
	header('HTTP/1.0 304 Not modified');
} else {
	imagejpeg(createImage($url));
}
