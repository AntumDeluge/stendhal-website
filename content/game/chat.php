<?php

class ChatPage extends Page {
	public function writeHtmlHeader() {
		echo '<title>Chat'.STENDHAL_TITLE.'</title>';
		echo '<meta name="robots" content="noindex">'."\n";
		$this->includeJs();
	}

	function writeContent() {
		$this->writeIntroduction();
		if (isset($_GET['date']) && preg_match("/^\d\d\d\d-\d\d-\d\d$/", $_GET['date'])) {
			$this->writeLog();
		} else {
			$this->writeListing();
		}
		$this->writeChatFooter();
	}


	function writeIntroduction() {
		startBox("<h1>Chat to other users and developers</h1>");
		?>

We have a Discord server and channels on the Libera IRC network. Both services are mirrored, so people on either side can chat which each other.

<ul>
<li>
	<a href="https://discord.gg/sw7kKgu"><img src="/images/buttons/discord_button.png" style="height:1.5em; vertical-align: text-top;"></a>
	<a href="https://discord.gg/sw7kKgu"><b>Stendhal Discord</b></a>
<li> <b><span style="font-size: 120%; font-weight: bolder; padding:0 .4em 0 .4em">#</span>irc.libera.chat</b>
    <ul>
    <li><a href="https://web.libera.chat/#arianne,#arianne-chat">#arianne</a> (for ideas, contributions and support)</li>
    <li><a href="https://web.libera.chat/#arianne,#arianne-chat">#arianne-chat</a> (for off topic chat not related to Arianne/Stendhal)</li>
    </ul>
</ul>


<p>If you are new to IRC it is well worth reading this <a href="http://www.irchelp.org/irchelp/new2irc.html">short guide</a> before you join. In particular the section on talking, and entering commands, and the section 'Some advice' may be helpful.
		<?php
		endBox();
	}

	function writeLog() {
		startBox('<h2>'.MAIN_CHANNEL . ' IRC log</h2>');
		echo '<a name="log" id="log"></a>';
		$directory = MAIN_LOG_DIRECTORY;

	$date = $_GET['date'];
?>
	<p><a href="<?php echo rewriteURL("/chat/");?>">Index of logs</a></p>

	<h2><?php echo(MAIN_CHANNEL); ?> IRC Log for <?php echo($date); ?></h2>
	<p>Timestamps are in server time. <span id="irclog-toggle-ircstatus-span" style="display:none"><input id="irclog-toggle-ircstatus" type="checkbox" value=""><label for="irclog-toggle-ircstatus">Show join/quit messages</label></span></p>

		<?php
		$filename = $directory.$date . ".log";
		if (!file_exists($filename)) {
			$filename = $directory.substr($date, 0, 4).'/'.$date.'.log';
		}

		$lines = explode("\n", file_get_contents($filename));
		echo '<div class="chattable">';
		for ($i = 0; $i < count($lines); $i++) {
			$line = $lines[$i];

			## make it pretty, yes this code is ugly.
			$class = "irctext";
			if (substr($line, 5, 5) == ' -!- ') {
				$class = "ircstatus";
			} else {
				if (substr($line, 5, 16) == ' < postman-bot> ') {
					if (substr($line, 21, 22) == 'Administrator SHOUTS: ') {
						$class = "ircshout";
					} else if (strpos($line, 'rented a sign saying') > 10) {
						$class = "ircsign";
					}
				}
			}
			preg_match('/(..:..) *(<.([^>]*)>|\*|-!-) (.*)/', $line, $matches);
			if (count($matches) >= 4) {
				$time = $matches[1];
				$nick = $matches[2];
				if ($matches[3] != '') {
					$nick = $matches[3];
				}
				$line = $matches[4];

				$line = htmlspecialchars($line);
				$line = preg_replace('/@/', '&lt;(a)&gt;', $line);
				$line = preg_replace(
						'!(http|https)://(|www\.)(faiumoni.de|stendhalgame.org|arianne.sf.net|arianne-project.org|arianne.sourceforge.net|sourceforge.net|sf.net|download.oracle.com|libregamewiki.org|freesound.org|opengameart.org|openclipart.org|github.io|github.com|arianne.github.io|postsai.github.io)(/[^ ]*)?!',
						'<a href="$1://$2$3$4$5">$1://$2$3$4$5</a>', $line);

				if ($line != '') {
					echo '<div class="chatrow '.$class.'"><span class="chatcell c1">'
						.htmlspecialchars($time).'</span><span class="chatcell c2">'
						.htmlspecialchars($nick).'</span><span class="chatcell c3">'
						.$line.'</span></div>'."\n";
				}
			}
		}


		echo '<p><a href="'.rewriteURL('/chat/'.date_format(date_sub(date_create($date), date_interval_create_from_date_string('1 day')), 'Y-m-d').'.html#log');
		echo '">Older</a> &nbsp;&nbsp;&nbsp; * &nbsp;&nbsp;&nbsp; <a href="'.rewriteURL('/chat/'.date_format(date_add(date_create($date), date_interval_create_from_date_string('1 day')), 'Y-m-d').'.html#log').'">Newer</a>';

		echo '</div>';
		endBox();
	}

	function writeListing() {
		startBox(MAIN_CHANNEL . ' IRC log');
		echo '<a name="log" id="log"></a>';
		$directory = MAIN_LOG_DIRECTORY;

		$startYear = 2006;
		$endYear = date('Y');
		for ($year = $endYear; $year >= $startYear; $year--) {
			$startMonth = 1;
			$startDay = 1;
			if ($year == $startYear) {
				$startMonth = 9;
				$startDay = 1;
			}
			$endMonth = 12;
			$endDay = 31;
			if ($year == $endYear) {
				$endMonth = date('n');
				$endDay = date('j');
			}
			$this->renderYear($year, $startMonth, $startDay, $endMonth, $endDay);
		}
		endBox();
}

	function writeChatFooter() {
		?>
		<p>
		These logs of  <?php echo(MAIN_CHANNEL); ?> were automatically created on
		<a href="irc://<?php echo(MAIN_SERVER . "/" . substr(MAIN_CHANNEL, 1)); ?>"><?php echo(MAIN_SERVER); ?></a>
		</p>
		<?php
	}

	function renderYear($year, $startMonth, $startDay, $endMonth, $endDay) {
		echo '<h2>'.htmlspecialchars($year).'</h2>';
		echo '<table>';
		for ($month = $endMonth; $month >= $startMonth; $month--) {
			$time = mktime(0, 0, 0, $month, 1, $year);
			echo '<tr><td style="vertical-align: top">'.date('F', $time).'</td><td>';
			$myMonth = $month;
			if ($month < 10) {
				$myMonth = '0'.$month;
			}
			$myStartDay = 1;
			if ($month == $startMonth) {
				$myStartDay = $startDay;
			}
			$myEndDay = date('t', $time);
			if ($month == $endMonth) {
				$myEndDay = $endDay;
			}
			for ($day = $myStartDay; $day <= $myEndDay; $day++) {
				$myDay = $day;
				if ($day < 10) {
					$myDay = '0'.$day;
				}
				echo '&nbsp;<a href="'.rewriteURL('/chat/'.$year.'-'.$myMonth.'-'.$myDay.'.html').'">'.$myDay.'</a> ';
				if ($day == 15) {
					echo '<br>';
				}
			}
			echo '</td></tr>';
		}
		echo '</table>';
	}
}
$page = new ChatPage();
