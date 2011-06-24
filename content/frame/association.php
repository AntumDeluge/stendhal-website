<?php
/*
 Copyright (C) 2011 Faiumoni

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
$lang = 'en';
if ($_REQUEST['lang'] == 'de') {
	$lang = 'de';
	require_once('content/association/de.php');
	loadLanguage();
}﻿;

// TODO: guess German
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
	 * @return name of default page
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
		if (strpos($page_url, 'content/association/') !==0) {
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
}
#header {
	padding: 20px 0 20px 10px;
}
#bodycontainer {
	background-color: #ccd9dc;
	background-image: url("/images/association/eye_background.jpg");
	background-repeat: no-repeat;
}
#container {
	background-image: none;
	border: none;
}
#leftArea {
	margin: 0 5px 0 0;
}
#rightArea {
	margin: -80px 0 0 5px;
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
	border-top: none
}

.versionInformation {
	font-size:60%;
	text-align:right
}

.changehistory li {
	margin-bottom: 0.5em;
}
</style>
		<?php
	}

	/**
	 * renders the frame
	 */
	function renderFrame() {
		global $page, $lang;
?>
<body>
<div id="contentArea" style="position:relative; top: 34px; z-index: 1; width:590px">
	<?php
		// The central area of the website.
		$page->writeContent();
	?>
</div>
<div id="bodycontainer" style="width:100%; height:100%; position:fixed; top:0px; z-index:0">
<div id="container" style="position:fixed; top:0px; z-index:0">
	<div id="header">
		<a href="<?php echo STENDHAL_FOLDER;?>/"><img style="border: 0;" src="<?php echo STENDHAL_FOLDER;?>/images/association/logo.png" alt=""></a>
	</div>

	<div id="leftArea">
	<?php 
		startBox(t('Association'));
		echo '<ul id="associationmenu" class="menu">';
			echo '<li><a id="menuAssociationAbout" href="'.rewriteURL('/'.$lang.'/about.html').'">'.t('Faiumoni e. V.').'</a></li>'."\n";
			echo '<li><a id="menuAssociationNews" href="'.rewriteURL('/'.$lang.'/news.html').'">'.t('News').'</a></li>'."\n";
			echo '<li><a id="menuAssociationStatue" href="'.rewriteURL('/'.$lang.'/statute.html').'">'.t('Statute').'</a></li>'."\n";
			echo '<li><a id="menuAssociationMembers" href="'.rewriteURL('/'.$lang.'/members.html').'">'.t('Members').'</a></li>'."\n";
			echo '<li><a id="menuAssociationContact" href="'.rewriteURL('/'.$lang.'/legal-contact.html').'">'.t('Legal contact').'</a></li>'."\n";
			echo '<li><a id="menuAssociationDonations" href="'.rewriteURL('/'.$lang.'/donate.html').'">'.t('Donate').'</a></li>'."\n";
		echo '</ul>';
		endBox();

		startBox(t('Resources')); ?>
		<ul id="resourcemenu" class="menu">
			<?php
			echo '<li><a id="menuResourceConcept" href="'.rewriteURL('/'.$lang.'/concept.html').'">'.t('Concept').'</a></li>'."\n";
			echo '<li><a id="menuResourceProjects" href="'.rewriteURL('/'.$lang.'/projects/2011.html').'">'.t('Projects').'</a></li>'."\n";
			echo '<li><a id="menuResourceModules" href="'.rewriteURL('/'.$lang.'/modules.html').'">'.t('Modules/Material').'</a></li>'."\n";
			echo '<li><a id="menuResourceChat" href="'.rewriteURL('/'.$lang.'/chat.html').'">'.t('Chat').'</a></li>'."\n";
			echo '<li><a id="menuResourceEvents" href="'.rewriteURL('/'.$lang.'/meetings.html').'">'.t('Meetings').'</a></li>'."\n";
			?>
		</ul>
		<?php endBox() ?>

	</div>

	<div id="rightArea">
		<?php
		startBox(t('Language'));
		?>
		<ul id="languagemenu" class="menu">
			<?php
			echo '<li><a id="menuLangDe" href="'.rewriteURL('/de/'.surlencode($_REQUEST['title']).'.html').'">Deutsch</a></li>'."\n";
			echo '<li><a id="menuLangEn" href="'.rewriteURL('/en/'.surlencode($_REQUEST['title']).'.html').'">English</a></li>'."\n";
			?>
		</ul>
		<?php
		endBox();

		if (isset($_SESSION) && isset($_SESSION['account'])) {
			startBox(t('Account'));
			echo '<ul id="accountmenu" class="menu">';
				echo '<li><a id="menuAcccountRecentChanges" href="/?lang='.$lang.'&amp;id=content/association/history">'.t('Recent changes').'</a></li>'."\n";
				echo '<li><a id="menuAcccountDocuments" href="/?lang='.$lang.'&amp;id=content/association/documents">'.t('Documents').'</a></li>'."\n";
			echo '</ul>';
			endBox();
		}
			
			/* TODO: implement me
			startBox(t('Share'));
			echo '<ul id="sharemenu" class="menu">';
			echo '<li><a id="menuShareFacebook" href="TODO">'.t('Facebook').'</a></li>'."\n";
			echo '<li><a id="menuShareTwitter" href="TODO">'.t('Twitter').'</a></li>'."\n";
			echo '<li><a id="menuShareEMail" href="'.rewriteURL('/'.$lang.'/email.html').'">'.t('eMail').'</a></li>'."\n";
			echo '</ul>';
			endBox();
			*/
		?>
	</div>

	<div id="footerArea">
		<span>&copy; 1999-2011 <a href="http://arianne.sourceforge.net">Arianne Project</a>, 2011 Faiumoni e. V.</span>
	</div>
</div>
</div>
</body>
</html>

<?php 
	}
}
$frame = new AssociationFrame();
