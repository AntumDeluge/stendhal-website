<?php

class FeaturePage extends Page {

	public function writeHtmlHeader() {
		echo '<title>Feature Requests'.STENDHAL_TITLE.'</title>';
	}

	function writeContent() {
		startBox("<h1>Feature Requests</h1>"); ?>
You can submit new feature requests with the following links:
<ul>
	<li><a href="https://github.com/arianne/stendhal/issues/new?labels=type%3A+feature+request&template=feature_request.md">Open feature request on GitHub</a></li>
	<li><a href="https://sourceforge.net/p/arianne/feature-requests/new/">Open feature request on SourceForge</a></li>
</ul>
		<?php endBox();
	}
}

$page = new FeaturePage();
