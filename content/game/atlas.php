<?php
class AtlasPage extends Page {

	public function writeHtmlHeader() {
		echo '<title>Atlas'.STENDHAL_TITLE.'</title>';
	}

	function writeContent() {
startBox("World of Stendhal"); ?>
<img class="screenshot" src="http://arianne.sourceforge.net/screens/stendhal/worldsmall.png" alt="Miniature view of stendhal world map"/>

<div class="title">Extended info</div>
You can obtain more info and more detailed views at our Wiki at <a href="http://stendhalgame.org/wiki/StendhalAtlas">http://stendhalgame.org/wiki/StendhalAtlas</a>
<?php endBox();
	}
}
$page = new AtlasPage();
