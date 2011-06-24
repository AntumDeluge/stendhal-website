<?php 
/*
    Stendhal website - a website to manage and ease playing of Stendhal game
    Copyright (C) 2008-2010  Miguel Angel Blanch Lardin, The Arianne Project

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

require_once('scripts/website.php');

if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == "on")) {
	$protocol = 'https';
	ini_set('session.cookie_secure', 1);
	session_start();
} else {
	if (!STENDHAL_SECURE_SESSION) {
		session_start();
	}
	$protocol = 'http';
}


/*
 * Open connection to both databases.
 */
connect();

/**
 * Scan the name module to load and reset it to safe default if something strange appears.
 *
 * @param string $url The name of the module to load without .php
 * @return string the name of the module to load.
 */
function decidePageToLoad($url) {
	if(strpos($url,".")!==false) {
		return null;
	}

	if(strpos($url,"//")!==false) {
		return null;
	}

	if(strpos($url,":")!==false) { // http://, https://, ftp://
		return null;
	}

	if(strpos($url,"/")==0) {
		return null;
	}

	if(strpos($url.'.php',".php")===false) {
		return null;
	}

	if(!file_exists($url.'.php')) {
		return null;
	}

	return $url;
}

require_once("content/frame/frame.php");
require_once(STENDHAL_FRAME);

/*
 * This code decides the page to load.
 */
$page_url = $frame->getDefaultPage();
if(isset($_REQUEST["id"])) {
	$page_url = decidePageToLoad($_REQUEST["id"]);

	// if the page does not exist, redirect to the main page
	if (!isset($page_url)) {
		header('Location: '.$protocol.'://'.STENDHAL_SERVER_NAME);
		return;
	}
}

if (!$frame->writeHttpHeader($page_url)) {
	exit(0);
}


require_once("content/page.php");
require_once($page_url.'.php');


// header("X-Content-Security-Policy: allow 'self'; default-src 'self'; img-src 'self' data: https://sflogo.sourceforge.net", true);
if ($page->writeHttpHeader()) {
header('Content-Type: text/html; charset=utf-8')
?>
<!DOCTYPE html>
<html>
	<head>
	<link rel="stylesheet" type="text/css" href="<?php echo STENDHAL_FOLDER; ?>/css/00000021.css">
	<!--[if lt IE 8]><link rel="stylesheet" type="text/css" href="<?php echo STENDHAL_FOLDER;?>/css/ie000010.css"><![endif]-->
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<?php
		/*
		 * Does another style sheet for this page exists?
		 * Yes? Load it.
		 */
		if(file_exists($page_url.'.css')) {
			?>
			<link rel="stylesheet" type="text/css" href="<?php echo STENDHAL_FOLDER.'/'.$page_url; ?>.css">
			<?php
		}
		$frame->writeHtmlHeader();
		$page->writeHtmlHeader();
		if (!defined("STENDHAL_WANT_ROBOTS") || !constant("STENDHAL_WANT_ROBOTS")) {
			echo '<meta name="robots" content="noindex">'."\n";
		}
	?>
	</head>
<?php
	$frame->renderFrame();
}
// Close connection to databases.
disconnect();
?>
