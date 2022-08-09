<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2008  Miguel Angel Blanch Lardin
 Copyright (C) 2008-2016  The Arianne Project

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

/**
  * A class representing a news item without comments.
  */
class News {
	public $id;

	/** Title of the news item */
	public $title;

	/** Date in ISO format YYYY-MM-DD HH:mm */
	public $date;

	/** One line description of the news item. */
	public $oneLineDescription;

	/** Extended description of the news item that follow the one line one. */
	public $extendedDescription;

	/** Images of the news item */
	public $images;

	/** description for detail page */
	public $detailedDescription;

	/** active */
	public $active;

	/** id of type */
	public $typeId;

	/** name of type */
	public $typeTitle;

	/** image of type */
	public $typeImage;

	/** counts the number of updates */
	public $updateCount;

	function __construct($id, $title, $date, $shortDesc, $longDesc, $images, $detailedDescription, $active, $typeId, $typeTitle, $typeImage, $updateCount) {
		$this->id=$id;
		$this->title=$title;
		$this->date=$date;
		$this->oneLineDescription=$shortDesc;
		$this->extendedDescription=$longDesc;
		$this->images=$images;
		$this->detailedDescription = $detailedDescription;
		$this->active = $active;
		$this->typeId = $typeId;
		$this->typeTitle = $typeTitle;
		$this->typeImage = $typeImage;
		$this->updateCount = $updateCount;
	}

	function show($detail=false) {
		// link the title unless we are in detail view
		$heading = '<div class="newsDate">'.$this->date.'</div><div class="newsTitle">';
		if (!$detail) {
			$heading .= '<h1><a href="'.rewriteURL('/news/'.$this->getNiceURL()).'">'.$this->title.'</a></h1>';
		} else {
			$heading .= '<h1>'.$this->title.'</h1>';
		}
		$heading .= '</div>';

		startBox($heading);

		// image for type of news and social network buttons
		if (isset($this->typeImage) && strlen($this->typeImage) > 0) {
			echo '<div class="newsIcons">';
			echo '<div class="newsIcon newsIcon'.$this->typeId.'"></div>';
			echo '</div>';
		}

		// render one line description
		if (isset($this->oneLineDescription) && strlen($this->oneLineDescription) > 0) {
			echo '<div class="newsContent">'.$this->oneLineDescription.'</div>';
		}

		// render news posting (add more link if there is a detail version)
		echo '<div class="newsContent newsTeaser">'.$this->extendedDescription;
		if (!$detail) {
			if (isset($this->detailedDescription) && (trim($this->detailedDescription) != '')) {
				echo ' <a href="'.rewriteURL('/news/'.$this->getNiceURL()).'" title="Read More...">(read more)</a>';
			}
		}
		echo '</div>';

		// in detail view, include the details
		if ($detail) {
			echo '<div class="newsContent newsDetail">'.$this->detailedDescription.'</div>';
		}

		endBox();
		/* END NOTE */
	}

	/**
	 * gets a nice url
	 *
	 * @return string nice url
	 */
	function getNiceURL() {
		$res = strtolower($this->title.'-'.$this->id);
		$res = preg_replace('/[ _,;.:<>|!?\'"()\/*] /', ' ', $res);
		$res = preg_replace('/[ _,;.:<>|!?\'"()\/*]/', '-', $res);
		return urlencode($res.'.html');
	}

};



/**
  * Returns a list of news. Note: All parameters need to be SQL escaped.
  */
function getNews($where='', $sortby='created desc', $cond='limit 3') {
	$sql = 'SELECT news.id As news_id, news.title As title, news.created As created, '
		.'news.shortDescription As shortDescription, '
		.'news.extendedDescription As extendedDescription, '
		.'news.detailedDescription As detailedDescription, news.active As active, '
		.'news_type.id As type_id, news_type.title As type_title, news_type.image_url As image_url, '
		.'news.updateCount As updateCount '
		.'FROM news LEFT JOIN news_type ON news.news_type_id=news_type.id '.$where.' order by '.$sortby.' '.$cond;

	$result = DB::web()->query($sql);
	$list=array();

	foreach($result as $row) {
		$images=array();

		$list[]=new News(
			$row['news_id'],
			$row['title'],
			$row['created'],
			$row['shortDescription'],
			$row['extendedDescription'],
			$images,
			$row['detailedDescription'],
			$row['active'],
			$row['type_id'],
			$row['type_title'],
			$row['image_url'],
			$row['updateCount']
		);
	}

	return $list;
}


function addNews($title, $oneline, $body, $images, $details, $type) {
	try {
		$stmt = DB::web()->prepare("insert into news (title, shortDescription, extendedDescription, active, detailedDescription, news_type_id) values "
			."(:title, :shortDescription, :extendedDescription, :active, :detailedDescription, :news_type_id)");
		$stmt->execute(array(
			':title' => $title,
			':shortDescription' => $oneline,
			':extendedDescription' => $body,
			':active' => 1,
			':detailedDescription' => $details,
			':news_type_id' => $type
		));
	} catch(PDOException $e) {
		echo '<span class="error">There has been a problem while inserting news</span>';
		error_log('ERROR addNews: ' . $e->getMessage());
	}
}


function deleteNews($id) {
	try {
		$stmt = DB::web()->prepare('delete from news where id=:id');
		$stmt->execute(array(
			':id' => $id
		));
	} catch(PDOException $e) {
		echo '<span class="error">There has been a problem while delteting news</span>';
		error_log('ERROR deleteNews: ' . $e->getMessage());
	}
}

function updateNews($id, $title, $oneline, $body, $images, $details, $type, $incUpdateCount = true) {
	$update = '';
	if ($incUpdateCount) {
		$update = ', updateCount=updateCount+1';
	}
	try {
		$query="UPDATE news SET title=:title, shortDescription=:shortDescription, "
			. " extendedDescription=:extendedDescription, detailedDescription=:detailedDescription, "
			. " news_type_id=:news_type_id ".$update." WHERE id=:id";
		$stmt = DB::web()->prepare($query);
		$stmt->execute(array(
				':title' => $title,
				':shortDescription' => $oneline,
				':extendedDescription' => $body,
				':detailedDescription' => $details,
				':news_type_id' => $type,
				':id' => $id
		));
	} catch(PDOException $e) {
		echo '<span class="error">There has been a problem while updating news</span>';
		error_log('ERROR updateNews: ' . $e->getMessage());
	}
}

/**
  * Returns a list of news between adate and bdate both inclusive
  */
function getNewsBetween($adate, $bdate) {
  return getNews('where date between '.mysql_real_escape_string($adate).' and '.mysql_real_escape_string($bdate));
}


function getNewsTypes() {
	$sql = 'SELECT id, title FROM news_type ORDER BY title';
	$stmt = DB::web()->query($sql);
	return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


/**
 * A class representing a trade offers.
 */
class TradeOffer {

	public $id;

	/** name of item */
	public $itemname;

	/** quantity offered */
	public $quantity;

	/** price */
	public $price;

	/** stats of the item */
	public $stats;

	/** time of  the offer */
	public $timedate;

	function __construct($id, $itemname, $quantity, $price, $stats, $timedate) {
		$this->id=$id;
		$this->itemname=$itemname;
		$this->quantity=$quantity;
		$this->price=$price;
		$this->stats=$stats;
		$this->timedate = $timedate;
	}

	/**
	 * Returns a list of trade offers.
	 */
	public static function getTradeOffers() {
		$sql = 'SELECT id, itemname, quantity, price, stats, timedate'
				. ' FROM trade WHERE timedate > subtime(now(), \'72:00:00\')'
						. ' ORDER BY timedate DESC LIMIT 100';
		return TradeOffer::readOffers($sql);
	}

	/**
	 * Returns a specific trade offer
	 *
	 * @param int tradeId
	 */
	public static function getTradeOffer($tradeId) {
		$sql = 'SELECT id, itemname, quantity, price, stats, timedate'
				. ' FROM trade WHERE id='.intval($tradeId). ';';
		return TradeOffer::readOffers($sql);
	}

	/**
 	 * Returns a list of trade offers for an SQL statement
 	 */
	private static function readOffers($sql) {
		$rows = DB::game()->query($sql);
		$list = array();
		foreach($rows as $row) {
			$list[] = new TradeOffer(
					$row['id'],
					$row['itemname'],
					$row['quantity'],
					$row['price'],
					$row['stats'],
					$row['timedate']
			);
		}
		return $list;
	}
}
