<?php
/*
 Copyright (C) 2011 Faiumoni e. V.

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

class TalkPage extends Page {
	private $path;

	/**
	 * dispatch to methods specific to the requested frame.
	 */
	// we use writeHttpHeader in order to surpress the normal navigation menu.
	public function writeHttpHeader() {
		$this->path = $_REQUEST['path'];
		if (!preg_match('|^[A-Za-z0-9_\-/]*$|', $this->path)) {
			die('Invalid path to presentation');
		}

		if ($_REQUEST['frame'] == 'frameset') {
			$this->renderFrameset();
		} else if ($_REQUEST['frame'] == 'rightframe') {
			$this->renderRightFrameset();
		} else if ($_REQUEST['frame'] == 'nav') {
			$this->renderOutline();
		} else if ($_REQUEST['frame'] == 'navnotes') {
			$this->renderBottomFrame();
		}
		return false;
	}


	/**
	 * renders the outer frameset
	 */
	private function renderFrameset() {
		$no = 0;
		if (isset($_REQUEST['no'])) {
			$no = intval($_REQUEST['no'], 10);
		}
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">';
		echo '<html><head><meta HTTP-EQUIV=CONTENT-TYPE CONTENT="text/html; charset=utf-8">';
		echo '<title>Talk</title></head>';
		echo '<frameset cols="*,656">';
		echo '<frame src="/?id=content/association/talk&amp;frame=nav&amp;path='.urlencode($this->path).'" name="nav">';
		echo '<frame src="/?id=content/association/talk&amp;frame=rightframe&amp;path='.urlencode($this->path).'&amp;no='.$no.'" name="rightframe">';
		echo '<noframes><body>';
		echo '<a href="/talk-files/'.$this->path.'/text'.$no.'.html">Text version of talk</a>';
		echo '</body></noframes>';
		echo '</frameset></html>';
	}


	/**
	 * renders the frameset on the right side
	 */
	private function renderRightFrameset() {
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">';
		echo '<html><head><meta HTTP-EQUIV=CONTENT-TYPE CONTENT="text/html; charset=utf-8">';
		echo '<title>ignored</title></head>';

		echo '<frameset rows="496,*">';
		echo '<frame src="/talk-files/'.$this->path.'/img'.urlencode($_REQUEST['no']).'.png" name="img">';
		echo '<frame src="/?id=content/association/talk&amp;frame=navnotes&amp;path='.urlencode($this->path).'&amp;no='.urlencode($_REQUEST['no']).'" name="navnote">';
		echo '</html>';
	}


	/**
	 * renders the navigation outline
	 */
	private function renderOutline() {
		$content = file_get_contents(STENDHAL_TALK_DIRECTORY.'/'.$this->path.'/outline0.html');
		echo '<!DOCTYPE HTML>';
		echo '<html><head><title>nav</title><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head><body><ul>';

		$slides = array();
		preg_match_all('|<a href="JavaScript:parent.NavigateAbs.[0-9]+.">([^<]*)</a>|', $content, $slides);

		$i = 0;
		foreach ($slides[1] as $slide) {
			echo '<li><a href="/?id=content/association/talk&amp;frame=rightframe&amp;no='.($i).'&amp;path='.urlencode($this->path);
			echo '" target="rightframe">'.htmlspecialchars($slide).'</a></li>';
			$i++;
		}
		echo '</ul></body></html>';
	}

	/**
	 * find the number of the last slide
	 */
	private function findNumberOfLastSlide() {
		$i = 0;
		while (file_exists(STENDHAL_TALK_DIRECTORY.'/'.$this->path.'/img'.$i.'.png')) {
			$i++;
		}
		return $i - 1;
	}

	/**
	 * renders the audio control, forward and backward navigation and note texts
	 */
	private function renderBottomFrame() {
		echo '<!DOCTYPE HTML>';
		echo '<html><head><title>nav</title><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head><body><div style="float:right">';

		$this->renderNavigation();
		$this->renderAudio();
		$this->renderNotes();

		echo '</body></html>';
	}


	/**
	 * render forward and backward navigation
	 */
	private function renderNavigation() {
		echo '<a href="/" target="_top"><img src="/images/impress/home.png" border=0 alt="Home"></a>';
		if ($_REQUEST['no'] == 0) {
			echo '<img src="/images/impress/first-inactive.png" border=0 alt=" ">';
			echo '<img src="/images/impress/left-inactive.png" border=0 alt=" ">';
		} else {
			echo '<a href="/?id=content/association/talk&amp;frame=rightframe&amp;no=0&amp;path='.urlencode($this->path).'" target="rightframe"><img src="/images/impress/first.png" border=0 alt="Start"></a>';
			echo '<a href="/?id=content/association/talk&amp;frame=rightframe&amp;no='.(intval($_REQUEST['no'], 10) - 1).'&amp;path='.urlencode($this->path).'" target="rightframe"><img src="/images/impress/left.png" border=0 alt="Back"></a>';
		}
		$last = $this->findNumberOfLastSlide();
		if ($_REQUEST['no'] == $last) {
			echo '<img src="/images/impress/right-inactive.png" border=0 alt=" ">';
			echo '<img src="/images/impress/last-inactive.png" border=0 alt=" ">';
		} else {
			echo '<a href="/?id=content/association/talk&amp;frame=rightframe&amp;no='.(intval($_REQUEST['no'], 10) + 1).'&amp;path='.urlencode($this->path).'" target="rightframe"><img src="/images/impress/right.png" border=0 alt="Next"></a>';
			echo '<a href="/?id=content/association/talk&amp;frame=rightframe&amp;no='.$last.'&amp;path='.urlencode($this->path).'" target="rightframe"><img src="/images/impress/last.png" border=0 alt="End"></a>';
		}
		echo '<a href="/talk-files/'.$this->path.'/text'.intval($_REQUEST['no'], 10).'.html" target="_top"><img src="/images/impress/text.png" border=0 alt="Text"></a>';
		echo '</div>';
	}

	/**
	 *  render audio controls, if an audio file exists
	 */
	private function renderAudio() {
		if (file_exists(STENDHAL_TALK_DIRECTORY.'/'.$this->path.'/'.intval($_REQUEST['no'], 10).'.ogg')) {
			echo '<div><audio controls="controls" autoplay="autoplay">
					<source src="/talk-files/'.$this->path.'/'.urlencode($_REQUEST['no']).'.ogg" type="audio/ogg" />
					<source src="/talk-files/'.$this->path.'/'.urlencode($_REQUEST['no']).'.mp3" type="audio/mp3" />
				</audio></div>';
		}
	}

	/**
	 *  Notes, if a notes file exists
	 */
	private function renderNotes() {
		if (file_exists(STENDHAL_TALK_DIRECTORY.'/'.$this->path.'/note'.intval($_REQUEST['no'], 10).'.html')) {
			echo '<div style="clear:both; margin-top:2em">';
			$notes = file_get_contents(STENDHAL_TALK_DIRECTORY.'/'.$this->path.'/note'.intval($_REQUEST['no'], 10).'.html');
			$pos1 = strpos($notes, '<body>');
			$pos2 = strpos($notes, '</body>');
			echo substr($notes, $pos1 + 6, $pos2 - $pos1 - 7);
			echo '</div>';
		}
	}
}

$page = new TalkPage();
