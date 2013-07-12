<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2013 Faiumoni e. V.

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
class TradeFeedPage extends Page {

	public function writeHttpHeader() {
		header('Content-Type: application/atom+xml', true); // TODO
		$this->writeFeed();
		return false;
	}

	public function writeFeed() {
		$this->writeHeader();
		$news = getNews(' where news.active=1 ', 'created desc', 'limit 20');
		foreach($news as $entry) {
			$this->writeEntry($entry);
		}
		$this->writeFooter();
	}

	private function formatedDate($date) {
		$datetime = new DateTime($date);
		$datetime->setTimezone(new DateTimeZone('GMT'));
		echo $datetime->format('Y-m-d\TH:i:s\Z');
	}

	private function writeHeader() {
		?>
	 <feed xml:lang="en-US" xmlns="http://www.w3.org/2005/Atom"> 
     <title>Harold's Offers</title> 
     <subtitle>Stendhal Trades</subtitle>
     <link href="https://stendhalgame.org/trade" rel="self"/> 
     <updated><?php echo $this->formatedDate(date()); // TODO: Use date of last entry?></updated>
     <author> 
          <name>Harold</name>
          <email>harold@stendhalgame.org</email>
     </author>
     <id>tag:stendhalgame.org,2013:https://stendhalgame.org/trade</id> 
     <?php
     echo $datetime->format('Y-m-d\TH:i:s');
	}

	private function writeEntry() {
		echo '<entry>';
		echo '<id>'.rewriteURL('https://stendhalgame.org/trade/'.$id).'</id>';
		echo '<title>'.$title.'</title>';
		echo '<updated>'.$this->formatedDate($date).'</updated>';
		echo '<published>'.$this->formatedDate($date).'</published>';
		echo '<link>'.rewriteURL('https://stendhalgame.org/trade/'.$id.'.html').' rel="alternate"></link>';
		echo '<content>'.'</content>';
		echo "</entry>\r\n";
	}

	private function writeFooter() {
		echo '</feed>';
	}
}

$page = new TradeFeedPage();