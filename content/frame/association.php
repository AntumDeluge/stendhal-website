<?php
/*
 Copyright (C) 2011-2018 Faiumoni e. V.

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


$dict = array();
if (isset($_REQUEST['lang']) && ($_REQUEST['lang'] == 'en' || $_REQUEST['lang'] == 'de')) {
	$lang = $_REQUEST['lang'];
}
if (!isset($lang) && !isset($_REQUEST['id'])) {
	if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && (strpos($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'de') === 0)) {
		header("Location: https://".$_SERVER['HTTP_HOST'].rewriteURL('/de/start.html'));
		exit();
	} else {
		$lang = 'en';
	}
}
if ($lang == 'de') {
	require_once('content/association/de.php');
	loadLanguage();
}

if (isset($_REQUEST['title'])) {
	$internalTitle = $_REQUEST['title'];
}
if (!isset($internalTitle) || $internalTitle == '') {
	$internalTitle = 'start';
}

$lang = urlencode($lang);

function t($msgid) {
	global $dict;
	if (isset($dict[$msgid])) {
		return $dict[$msgid];
	}
	return $msgid;
}

class AssociationFrame extends PageFrame {

	/**
	 * gets the default page in case none is specified.
	 *
	 * @return string name of default page
	 */
	function getDefaultPage() {
		return 'content/association/main';
	}

	/**
	 * this method can write additional http headers, for example for cache control.
	 *
	 * @return true, to continue the rendering, false to not render the normal content
	 */
	function writeHttpHeader($page_url) {
		global $protocol;
		if ((strpos($page_url, 'content/association/') !== 0) && (strpos($page_url, 'content/scripts/cspreport') !== 0)) {
			header('Location: '.$protocol.'://'.STENDHAL_SERVER_NAME);
			return false;
		}
		return true;
	}

	/**
	 * this method can write additional html headers.
	 */
	function writeHtmlHeader() {
		echo '<link rel="icon" type="image/x-icon" href="'.STENDHAL_FOLDER.'/images/association/favicon.ico">';
	?>
<style type="text/css">
body {
	background-color:#FFF;
	background-image:none;
	text-align: left;
}
#header {
	padding: 10px 0 0 8px;
	background-image: none;
}
#bodycontainer {
	background-color: #ccd9dc;
	background-image: url("/images/association/eye_background.jpg");
	background-repeat: no-repeat;
	width:100%;
	height:100%;
	position:fixed;
	top:0px;
	left:0;
	z-index:0
}
#container {
	background-image: none;
	border: none;
	height: 100%;
}
#leftArea {
	margin: 0 5px 0 0;
	height: 100%;
	overflow: auto;
}
#rightArea {
	margin: 0 0 0 5px;
}
.box {
	background-image: url("/images/semi_transparent.png");
	background-color: transparent;
	border-radius: 15px;
	-moz-border-radius: 15px;
	border: 0px;
}
.boxTitle {
	border-radius: 15px;
	-moz-border-radius: 15px;
	padding-left: 1em;
	background-image:none;
	background-color:#86979b;
	border: outset 2px grey;
}
#footerArea {
	border-top: none;
	text-align:left;
	padding-left: 1em;
}

.versionInformation {
	font-size:60%;
	text-align:right
}

.changehistory li {
	margin-bottom: 0.5em;
}
#contentArea {
	position:relative;
	top: 10px;
	z-index: 1;
	width:590px
}
#contentArea tr {
	vertical-align: top;
}
.menu a {background-color: transparent}
#rightArea {
    margin: 0 0 0 800px;
}
@media print {
	#bodycontainer {
		display: none;
	}
	#contentArea {
		margin: 0;
	}
	a:after, a:link:after  {
		color: #000000;
		background-color:transparent;
		content: " [" attr(href) "] ";
	}
	a:visited:after {
		color:#000000;
		background-color:transparent;
		content: " [Link " attr(href) "] ";
	}
}
</style>
		<?php
	}

	/**
	 * renders the frame
	 */
	function renderFrame() {
		global $page, $lang, $internalTitle;
?>
<body>
<div id="contentArea">
	<?php
		// The central area of the website.
		$page->writeContent();
	?>
	<div id="footerArea">
		<span>&copy; 2011-2018 Faiumoni e. V.</span>
	</div>
</div>
<div id="bodycontainer">
<div id="container" style="position:fixed; top:0px; left:0; z-index:0">

	<div id="leftArea">
		<div id="header">
		<?php
			$websiteRoot = STENDHAL_FOLDER.rewriteURL('/'.$lang.'/start.html');
			if (
					( !isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ||
						(strpos($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'de') !== 0)
					)
					&& ($lang == 'en')) {
				$websiteRoot = STENDHAL_FOLDER.'/';
			}
			echo '<a href="'.$websiteRoot.'">';
			echo '<img style="border: 0;" src="'.STENDHAL_FOLDER.'/images/association/logo.png" alt=""></a>';
		?>
		</div>
	<?php
		startBox(t('Association'));
		echo '<ul id="associationmenu" class="menu">';
			echo '<li><a id="menuAssociationAbout" href="'.rewriteURL('/'.$lang.'/about.html').'">'.t('Faiumoni e. V.').'</a></li>'."\n";
			echo '<li><a id="menuAssociationNews" href="'.rewriteURL('/'.$lang.'/news.html').'">'.t('News').'</a></li>'."\n";
			echo '<li><a id="menuAssociationStatue" href="'.rewriteURL('/'.$lang.'/statute.html').'">'.t('Statute').'</a></li>'."\n";
			// echo '<li><a id="menuAssociationMembers" href="'.rewriteURL('/'.$lang.'/members.html').'">'.t('Members').'</a></li>'."\n";
			echo '<li><a id="menuAssociationContact" href="'.rewriteURL('/'.$lang.'/legal-contact.html').'">'.t('Legal contact').'</a></li>'."\n";
			echo '<li><a id="menuAssociationDonations" href="'.rewriteURL('/'.$lang.'/donate.html').'">'.t('Donate').'</a></li>'."\n";
		echo '</ul>';
		endBox();

		startBox(t('Resources')); ?>
		<ul id="resourcemenu" class="menu">
			<?php
			echo '<li><a id="menuResourceConcept" href="'.rewriteURL('/'.$lang.'/concept.html').'">'.t('Concept').'</a></li>'."\n";
			echo '<li><a id="menuResourceProjects" href="'.rewriteURL('/'.$lang.'/projects/index.html').'">'.t('Projects').'</a></li>'."\n";
			echo '<li><a id="menuResourceMaterial" href="'.rewriteURL('/'.$lang.'/material.html').'">'.t('Material').'</a></li>'."\n";
			echo '<li><a id="menuResourceChat" href="'.rewriteURL('/'.$lang.'/chat.html').'">'.t('Chat').'</a></li>'."\n";
			echo '<li><a id="menuResourceEvents" href="'.rewriteURL('/'.$lang.'/meetings.html').'">'.t('Meetings').'</a></li>'."\n";
			?>
		</ul>
		<?php endBox() ?>

	</div>
</div>

	<div id="rightArea">
		<?php
		startBox(t('Language'));
		?>
		<ul id="languagemenu" class="menu">
			<?php
			echo '<li><a id="menuLangDe" href="'.rewriteURL('/de/'.surlencode($internalTitle).'.html').'">Deutsch</a></li>'."\n";
			echo '<li><a id="menuLangEn" href="'.rewriteURL('/en/'.surlencode($internalTitle).'.html').'">English</a></li>'."\n";
			?>
		</ul>
		<?php
		endBox();

		if (isset($_SESSION) && isset($_SESSION['account'])) {
			startBox(t('Account'));
			echo '<ul id="accountmenu" class="menu">';
			echo '<li><a id="menuAcccountDocuments" href="/?lang='.$lang.'&amp;id=content/association/documents">'.t('Documents').'</a></li>'."\n";
			echo '<li><a id="menuAcccountRecentChanges" href="/?lang='.$lang.'&amp;id=content/association/history">'.t('Recent changes').'</a></li>'."\n";
				if ($_REQUEST['id'] == '') {
					echo '<li><a id="menuAcccountEdit" href="/?id=content/association/edit&amp;lang='.surlencode($lang).'&amp;title='.urlencode($internalTitle).'">'.t('Edit').'</a></li>'."\n";
					echo '<li><a id="menuAcccountPageHistory" href="/?id=content/association/history&amp;lang='.surlencode($lang).'&amp;title='.urlencode($internalTitle).'">'.t('Page history').'</a></li>'."\n";
				}
			echo '</ul>';
			endBox();
		}
		?>
	</div>
</div>
<?php $this->includeJs();?>
</body>
</html>

<?php
	}
}
$frame = new AssociationFrame();
