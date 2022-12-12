<?php

class SourceLogPage extends Page {

	public function writeHtmlHeader() {
		if (!isset($_GET['month'])) {
			echo '<title>Source Code Changes'.STENDHAL_TITLE.'</title>';
		} else {
			$month = $_GET['month'];
			if (preg_match("/^\d\d\d\d-\d\d$/", $month)) {
				echo '<title>Source Code Changes in '.$month.STENDHAL_TITLE.'</title>';
			} else {
				echo '<title>Source Code Changes '.STENDHAL_TITLE.'</title>';
				echo '<meta name="robots" content="noindex">'."\n";
			}
		}
	}

	function writeContent() {
startBox("<h1>Source Code</h1>"); ?>
<p>The Arianne project is hosted on
<a href="https://github.com/arianne/">GitHub</a> and
<a href="https://sourceforge.net/projects/arianne/">Sourceforge</a> and
uses Git to manage changes to our source code.</p>

<p>You can use a Git client to download our Stendhal or Marauroa source
code.</p>

<ul>
	<li><a href="https://github.com/arianne/stendhal/">Stendhal source on GitHub</a></li>
	<li><a href="https://sourceforge.net/p/arianne/stendhal/ci/master/tree/">Stendhal source on SourceForge</a></li>
	<li><a href="https://github.com/arianne/marauroa/">Marauroa source on GitHub</a></li>
	<li><a href="https://sourceforge.net/p/arianne/marauroa/ci/master/tree/">Marauroa source on SourceForge</a></li>
</ul>

<p>For more information check out the <a href="/wiki/Arianne_Source_Code_Repositories">Source Code Repositories wiki page</a>.</p>

<p>Recent changes are listed in our <a href="https://arianne-project.org/postsai/query.html?repository=arianne%2F.*&amp;repositorytype=regexp&amp;date=month">commit database</a>.</p>
}
$page = new SourceLogPage();
