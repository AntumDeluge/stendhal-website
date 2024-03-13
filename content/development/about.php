<?php
class AboutPage extends Page {

	public function writeHtmlHeader() {
		echo '<title>About'.STENDHAL_TITLE.'</title>';
	}

	function writeContent() {

startBox("<h1>About Stendhal</h1>"); ?>
<p> Stendhal is an open source project, written and released under the <a href="https://www.gnu.org/licenses/agpl-3.0.html">GNU AGPL license</a> by the <a href="https://arianne-project.org">Arianne project</a>.
<p> Do you want to contribute? Have a look at <a href="<?php echo surlencode('/development/introduction.html')?>">Development section</a> to join the <a href="https://github.com/arianne/stendhal/blob/master/doc/contributors.md">List of contributors</a>.
<?php endBox();
	}
}
$page = new AboutPage();
