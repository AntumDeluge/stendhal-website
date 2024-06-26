<?php

class DownloadPage extends Page {
	public function writeHtmlHeader() {
		echo '<title>Downloads'.STENDHAL_TITLE.'</title>';
		echo '<meta name="robots" content="noindex">'."\n";
	}

	function writeContent() {
		startBox("<h1>Download</h1>");
		echo '<p>Stendhal is completely open source. Both client and server are licensed under the GNU General Public License.</p>';
		endBox();

		startBox('<h2>For players</h2>');
		echo '<p>You most likely want one of these downloads:</p>';
		echo '<ul>';
		echo '<li>Classic Java client.</li>';
		echo '<ul>';
		echo '<li><b><a href="https://arianne-project.org/download/stendhal.zip">stendhal.zip</a></b> <img src="/images/buttons/star.png"><br>Download this file to play on desktop.</li>';
		echo '</ul>';
		echo '<li>Android client.</li>';
		echo '<ul>';
		echo '<li><b><a href="https://arianne-project.org/download/org.stendhalgame.client.apk">org.stendhalgame.client.apk</a></b> <img src="/images/buttons/star.png"><br>Download this file to play on Android mobile devices or ...</li>';
		echo '<li>Get it on <b><a href="https://f-droid.org/packages/org.stendhalgame.client/">F-Droid</a></b></li>';
		echo '</ul></ul>';
		echo '<p>The Stendhal desktop client works on Windows, Mac and Linux. It requires a <a href="https://java.com">Java runtime</a>.</p>';
		echo '<p>&nbsp;</p>';
		endBox();

		startBox('<h2><a name="development"></a>For developers</h2>');
		echo '<p>If you are a developer, you may be interested in these files. Please have a look at the <a href="/development.html">development corner</a>.</p>';
		echo '<ul>';
		echo '<li><a href="https://arianne-project.org/download/stendhal-server.zip">stendhal-server.zip</a><br>This file contains the stendhal server files. It is not needed to play Stendhal.</li>';
		echo '<li><a href="https://arianne-project.org/download/stendhal-src.tar.gz">stendhal-src.tar.gz</a><br>This file is for developers. It contains the source code for both the client and the server.</li>';
		echo '</ul>';
		echo '<p>We use <a href="http://www.mapeditor.org/download.html">Tiled</a> to edit Stendhal maps.</p>';
		endBox();

		startBox('<h2><a name="pre-release"></a>Pre-release testing</h2>', 'testing');
		echo '<p>Please see <a href="http://stendhalgame.org/wiki/Stendhal_Testing">Stendhal Testing</a>, if you want to help us test the next release.</p>';
		echo '<p>Use this beta version with care as it will contain bugs. Please note: It will not update itself.</p>';

		echo '<div style="margin-left: 3em">';

		if (is_dir('download')) {
			$dir = opendir('download');
			if (!is_bool($dir)) {
				while (false !== ($file = readdir($dir))) {
					if (strpos($file, '.') !== 0) {
						echo '<li><a href="/download/'.htmlspecialchars($file).'">'.htmlspecialchars($file).'</a></li>';
					}
				}
				closedir($dir);
			}
		}
		echo '</div>';
		endBox();
	}
}

$page = new DownloadPage();
