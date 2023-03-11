<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2008  Miguel Angel Blanch Lardin
 Copyright (C) 2008-2016  The Arianne Project

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


 /*
 * This file is the PHP code that generate each of the website sections.
 */

require_once('configuration.php');
require_once('configuration.default.php');
require_once('scripts/mysql.php');

require_once('scripts/account.php');
require_once('scripts/achievement.php');
require_once('scripts/cache.php');
require_once('scripts/cms.php');
require_once('scripts/events.php');
require_once('scripts/grammar.php');
require_once('scripts/inspect.php');
require_once('scripts/items.php');
require_once('scripts/itemlog.php');
require_once('scripts/monsters.php');
require_once('scripts/netstats.php');
require_once('scripts/news.php');
require_once('scripts/npcs.php');
require_once('scripts/playerhistory.php');
require_once('scripts/players.php');
require_once('scripts/search.php');
require_once('scripts/shops.php');
require_once('scripts/statistics.php');
require_once('scripts/urlrewrite.php');
require_once('scripts/xml.php');
require_once('scripts/zones.php');


function startBox($title, $id=null) {
	echo '<div class="box">';
	$idAttr = '';
	if (isset($id)) {
		$idAttr = ' id="'.htmlspecialchars($id).'"';
	}
	echo '<div class="boxTitle"'.$idAttr.'>'.$title.'</div>';
	echo '<div class="boxContent">';
}

function endBox() {
	echo '</div></div>';
}

/**
 * gets the code to put into the a-tag of an overlib popup
 *
 * @param string $html
 */
function getOverlibCode($html) {
	return ' onmouseover="return overlib(\''.rawurlencode($html).'\', FGCOLOR, \'#000\', BGCOLOR, \'#FFF\','
		. 'DECODE, FULLHTML'
		. ');" onmouseout="return nd();"';
}

/**
 * creates a random string
 */
function createRandomString() {
	$characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	$res = '';
	for ($i = 0; $i < 20; $i++) {
		$res .= $characters[mt_rand(0, strlen($characters) - 1)];
	}
	return $res;
}

/**
 * queries the database for an array result, using the cache
 *
 * @param string $query query to execute
 * @param int    $ttl cache time, use 0 to disable cache
 */
function queryWithCache($query, $ttl, $db) {
	global $cache;
	$list = $cache->fetchAsArray('stendhal_query_'.$query);
	if (!isset($list) || !is_array($list)) {
		$list=array();
		$rows = $db->query($query);
		foreach($rows as $row) {
			$list[] = $row;
		}
		if ($ttl && $ttl > 0 && count($list) > 0) {
			$cache->store('stendhal_query_'.$query, new ArrayObject($list), $ttl);
		}
	}
	return $list;
}

/**
 * Format the number using (hard-coded) English locale with
 * the given number of digits. Terminating zeros and a possibly
 * terminating decimal point are removed as well.
 *
 * @param value float | integer
 * @param digits integer
 *
 * @return string
 */

function formatNumber($value, $digits = 6) {
	$decimalSeparator =  '.';
	$thousandsSeparator = ',';

	$sNumber = number_format($value, $digits, $decimalSeparator, $thousandsSeparator);

	// $sNumber could possibly contain trailing zeros, e.g. '10,000.000000'.
	// Remove the trailing zero, and the decimal point, but no any more zeros.

	list($sBefore, $sAfter) = explode($decimalSeparator, $sNumber);

	if (($sAfter = rtrim($sAfter, '0')) === '') {

		// We have no fraction.

		return $sBefore;
	}

	return $sBefore . $decimalSeparator . $sAfter;
}

function profilePoint($name) {
	if (isset($_REQUEST['_profiler'])) {
		global $profilerReferenceTime;
		echo "\n".'<!--'. number_format(microtime(true) - $profilerReferenceTime, 3). ': '.htmlspecialchars($name).'-->';
		$profilerReferenceTime = microtime(true);
	}
}


/**
 * read pages from the Stendal wiki to embed them into the Stendhal Website.
 *
 * @author hendrik
 */
class Wiki {
	private $url;
	private $page;
	private $breadcrumbs;

	public function __construct($url) {
		$this->url = $url;
	}

	/**
	 * gets the content of the wiki page
	 * @param string $page page name
	 * @return string content
	 */
	private function get($page) {

		// check file cache
		$md5 = md5($page);
		$path = '/var/www/stendhal/w/images/cache/'.$md5[0].'/'.$md5[0].$md5[1]
			.'/'.str_replace('/', '%2F', urlencode($page)).'.html';
		$content = @file_get_contents($path);

		if ($content === false) {
			// do not use ?action=render because that does not write file cache
			$url = 'https://stendhalgame.org/wiki/'.surlencode($page);
			$content = file_get_contents($url);
		}
		return $content;
	}

	/**
	 * encodes an url acording to wiki rules
	 *
	 * @param  string $title
	 * @return string encoded url
	 */
	private function wikiUrlEncode($title) {
		return str_replace('%2F', '/', urlencode($title));
	}

	public function findPage() {
		if (defined("STENDHAL_WIKI_CONNECTION") && constant("STENDHAL_WIKI_CONNECTION")) {
			$sql = "SELECT page_id, page_title As title, p2.pp_value As displaytitle FROM a1111_wiki.page, a1111_wiki.page_props as p1, a1111_wiki.page_props as p2 "
			." WHERE p1.pp_propname='externalcanonical' AND p1.pp_value = :url"
			." AND page.page_namespace=0 AND page.page_id=p1.pp_page"
			." AND p2.pp_propname='externaltitle' AND page.page_id=p2.pp_page";

			$stmt = DB::game()->prepare($sql);
			$stmt->execute(array(':url' => $this->url));
			$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
			if (count($res) > 0) {
				$this->page = $res[0];
				return $res[0];
			}
		}

		return null;
	}

	/**
	 * removes unwanted html code
	 *
	 * @param string $content
	 * @return string
	 */
	private function clean($content) {
		// remove wiki navigation
		$start = strpos($content, '<div id="mw-content-text" lang="en" dir="ltr" class="mw-content-ltr">');
		$end = strrpos($content, '<div class="printfooter">');
		$content = '<div id="mw-content-text">'.substr($content, $start, $end - $start);

		// remove content which was marked as to be removed
		while (true) {
			$start = strpos($content, '<span class="skip-start"></span>');
			if ($start === false) {
				break;
			}
			$end = strpos($content, '<span class="skip-end"></span>');
			$content = substr($content, 0, $start).substr($content, $end + 30);
		}

		// removed breadcrumb navigation (categories have already been removed because they are outside "bodyconent")
		$start = strpos($content, '<div class="breadcrumbs" ');
		if ($start !== false) {
			$end = strpos($content, '</div>', $start) + 6;
			$content = substr($content, 0, $start).substr($content, $end);
		}

		return $content;
	}

	private function rewriteLinks($pageId, $content) {
		if (defined("STENDHAL_WIKI_CONNECTION") && constant("STENDHAL_WIKI_CONNECTION")) {
			$sql = "SELECT page_title, pp_value FROM a1111_wiki.page_props, a1111_wiki.page, a1111_wiki.pagelinks"
				." WHERE pp_propname='externalcanonical' AND page.page_namespace=0 AND page.page_id=page_props.pp_page"
				." AND pl_namespace=0 AND page_title=pl_title AND pl_from=".intval($pageId);
			$rows = DB::game()->query($sql);

			$prefix = '<a href="';
			foreach ($rows as $row) {
				$content = str_replace($prefix.'/wiki/'.$this->wikiUrlEncode($row['page_title']).'"',
					$prefix.$this->wikiUrlEncode($row['pp_value']).'"', $content);
			}
		}

		return $content;
	}

	public static function rewriteImageLinks($content) {
		// <a href="/wiki/File:Semos.png" class="image"><img alt="Semos.png" src="/wiki/images/thumb/f/f0/Semos.png/300px-Semos.png" height="266" width="300"></a>
		// <a href="/wiki/File:[^"]* class="image"><img alt="([^"]*)" src="/wiki/images/thumb/(./..)/([^/]*)/"
		// <a href="/wiki/images/$2/$3* class="image fancybox"><img alt="$1" src="/wiki/images/thumb/$2/$3/"
		$content = preg_replace(
			'|<a href="/wiki/File:[^"]*" class="image"><img alt="([^"]*)" src="/wiki/images/(thumb/)?(./..)/([^/]*)/|',
			'<a href="/wiki/images/$3/$4" class="image fancybox"><img alt="$1" src="/wiki/images/$2$3/$4/',
			$content);
		return $content;
	}

	/**
	 * renders a wiki page in the Stendhal website
	 *
	 * @param  string $page page name
	 * @return string prepared content
	 */
	function render() {
		if (!isset($this->page)) {
			return;
		}
		$content = $this->get($this->page['title']);
		$content = $this->clean($content);
		$content = $this->rewriteLinks($this->page['page_id'], $content);
		$content = $this->rewriteImageLinks($content);

		return $content;
	}

	function getCategories() {
		$res = array();
		if (defined("STENDHAL_WIKI_CONNECTION") && constant("STENDHAL_WIKI_CONNECTION")) {
			$sql =  "SELECT cl_to FROM a1111_wiki.categorylinks WHERE cl_from=" . intval($this->page['page_id']);
			$rows = DB::game()->query($sql);
			foreach($rows as $row) {
				$res[] = $row['cl_to'];
			}
		}

		return $res;
	}

	/**
	 * gets a list of related pages
	 *
	 * @param string $propName name of property to look for
	 * @param string $category name of page category
	 */
	public static function findRelatedPages($propName, $category) {
		if (defined("STENDHAL_WIKI_CONNECTION") && constant("STENDHAL_WIKI_CONNECTION")) {
			$sql = "SELECT stendhal_category_search.entitytype, pp_title.pp_value As title, stendhal_category_search.category As category, pp_url.pp_value As path
					FROM a1111_wiki.categorylinks, a1111_wiki.stendhal_category_search,
							 a1111_wiki.page_props As pp_url, a1111_wiki.page_props As pp_title, a1111_wiki.page_props As pp_keyword
					WHERE pp_url.pp_propname='externalcanonical'
					AND pp_keyword.pp_page=pp_title.pp_page AND pp_title.pp_propname='externaltitle'
					AND pp_url.pp_page=pp_keyword.pp_page AND pp_keyword.pp_propname=:pp_propname
					AND categorylinks.cl_from=pp_keyword.pp_page AND stendhal_category_search.category=categorylinks.cl_to
					AND categorylinks.cl_to = :cl_to
					LIMIT 100";
			$stmt = DB::game()->prepare($sql);
			$stmt->execute(array(
				':pp_propname' => $propName,
				':cl_to' => $category
			));
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}

		return array();
	}
}

function requestJson($url) {
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$response = curl_exec($curl);
	return json_decode($response, true);
}
