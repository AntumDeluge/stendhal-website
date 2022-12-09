<?php

class PatchPage extends Page {

	public function writeHtmlHeader() {
		echo '<title>Patch Submission'.STENDHAL_TITLE.'</title>';
	}

	function writeContent() {
		startBox("<h1>Patches</h1>"); ?>
Patches can be submitted via the following methods:
<ul>
	<li><a href="https://github.com/arianne/stendhal/issues/new?labels=type%3A+patch">GitHub issues tracker</a></li>
	<li><a href="https://sourceforge.net/p/arianne/patches/new/">SourceForge patch tracker</a></li>
	<li>Fork Stendhal's Git repository at
	<a href="https://github.com/arianne/stendhal">GitHub</a> or
	<a href="https://sourceforge.net/p/arianne/stendhal/ci/master/tree/">SourceForge</a>
	and submit a merge/pull request:</li>
	<ul>
		<li><a href="https://docs.github.com/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/creating-a-pull-request">GitHub instructions</a></li>
		<li><a href="https://sourceforge.net/p/forge/documentation/Git/#what-is-git">SourceForge instructions</a>
		(see video tutorial link)</li>
	</ul>
</ul>
		<?php endBox();
	}
}

$page = new PatchPage();
