<?php

class FeaturePage extends Page {

	public function writeHtmlHeader() {
		echo '<title>Feature Requests'.STENDHAL_TITLE.'</title>';
	}

	function writeContent() {
		startBox("<h1>Feature Requests</h1>"); ?>
<p>You can submit a new feature request in our Codeberg project: <a href="https://codeberg.org/arianne/stendhal/issues/new/choose">Open feature request</a>.
<p>Or browse the <a href="https://codeberg.org/arianne/stendhal/issues">issue tracker</a>.
Or the old trackers at <a href="https://github.com/arianne/stendhal/issues">GitHub</a> or <a href="https://sourceforge.net/p/arianne/feature-requests/">SourceForge</a>

		<?php endBox();
	}
}

$page = new FeaturePage();
