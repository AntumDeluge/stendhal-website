<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2008  Miguel Angel Blanch Lardin

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

	/** Date in ISO format YYYY/MM/DD HH:mm */
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

	/** name of type */
	public $typeTitle;

	/** image of type */
	public $typeImage;

	function __construct($id, $title, $date, $shortDesc, $longDesc, $images, $detailedDescription, $active, $typeTitle, $typeImage) {
		$this->id=$id;
		$this->title=$title;
		$this->date=$date;
		$this->oneLineDescription=$shortDesc;
		$this->extendedDescription=$longDesc;
		$this->images=$images;
		$this->detailedDescription = $detailedDescription;
		$this->active = $active;
		$this->typeTitle = $typeTitle;
		$this->typeImage = $typeImage;
	}

	function show($detail=false) {
		// link the title unless we are in detail view
		$heading = '<div class="newsDate">'.$this->date.'</div><div class="newsTitle">';
		if (!$detail) {
			$heading .= '<a style="newsTitle" href="'.$this->getNiceURL().'">'.$this->title.'</a>';
		} else {
			$heading .= $this->title;
		}
		$heading .= '</div>';
		
		startBox($heading);
		echo '<div class="newsContent">'.$this->oneLineDescription.'</div>';

		echo '<div class="newsContent newsTeaser">'.$this->extendedDescription;
		if (!$detail) {
			if (isset($this->detailedDescription) && (trim($this->detailedDescription) != '')) {
				echo ' <a href="'.rewriteURL('/news/'.$this->getNiceURL()).'" title="Read More...">...</a>';
			}
		}
		echo '</div>';

		if ($detail) {
			echo '<div class="newsContent newsDetail">'.$this->detailedDescription.'</div>';
		}
		endBox();
		/* END NOTE */
	}

	/**
	 * gets a nice url
	 *
	 * @return nice url
	 */
	function getNiceURL() {
		$res = strtolower($this->title.'-'.$this->id);
		$res = preg_replace('/[ _,;.:<>|] /', ' ', $res);
		$res = preg_replace('/[ _,;.:<>|]/', '-', $res);
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
		.'news_type.title As type_title, news_type.image_url As image_url ' 
		.'FROM news LEFT JOIN news_type ON news.news_type_id=news_type.id '.$where.' order by '.$sortby.' '.$cond;

	$result = mysql_query($sql, getWebsiteDB());
	$list=array();

	while($row=mysql_fetch_assoc($result)) {
		$resultimages = mysql_query('select * from news_images where news_id="'.$row['id'].'" order by created desc', getWebsiteDB());
		$images=array();

		while($rowimages=mysql_fetch_assoc($resultimages)) {      
			$images[]=$rowimages['url'];
		}
		mysql_free_result($resultimages);

		$list[]=new News(
			$row['news_id'],
			$row['title'],
			$row['created'],
			$row['shortDescription'],
			$row['extendedDescription'],
			$images,
			$row['detailedDescription'],
			$row['active'],
			$row['type_title'],
			$row['image_url']
		);
	}

	mysql_free_result($result);
	
	return $list;
}


function addNews($title, $oneline, $body, $images, $approved=false) {
    $title=mysql_real_escape_string($title);
    $oneline=mysql_real_escape_string($oneline);
    $body=mysql_real_escape_string($body);

    $query='insert into news (title, shortDescription, extendedDescription, active) values ("'.$title.'","'.$oneline.'","'.$body.'", 1)';
    mysql_query($query, getWebsiteDB());
    if(mysql_affected_rows()!=1) {
        echo '<span class="error">There has been a problem while inserting news.</span>';
        echo '<span class="error_cause">'.$query.'</span>';
        return;
    }

    $result=mysql_query('select LAST_INSERT_ID() As lastid from news;', getWebsiteDB());
    while($rowimages=mysql_fetch_assoc($result)) {      
        $newsid=$rowimages['lastid'];
    }
    mysql_free_result($result);

    foreach(explode("\n",$images) as $image) {
      mysql_query('insert into news_images values(null,'.$newsid.',"'.mysql_real_escape_string($image).'",null, null', getWebsiteDB());
    }
    
}

function deleteNews($id) {
    $id=mysql_real_escape_string($id);
    
	$query='delete from news where id="'.mysql_real_escape_string($id).'"';
    mysql_query($query, getWebsiteDB());
    if(mysql_affected_rows()!=1) {
        echo '<span class="error">There has been a problem while deleting news.</span>';
        echo '<span class="error_cause">'.$query.'</span>';
        return;
    }
}

function updateNews($id, $title, $oneline, $body, $images, $approved=false) {
    $id=mysql_real_escape_string($id);
    $title=mysql_real_escape_string($title);
    $oneline=mysql_real_escape_string($oneline);
    $body=mysql_real_escape_string($body);

    $query='update news set title="'.$title.'", shortDescription="'.$oneline.'",extendedDescription="'.$body.'" where id="'.$id.'"';
    mysql_query($query, getWebsiteDB());
    if(mysql_affected_rows()!=1) {
        echo '<span class="error">There has been a problem while updating news.</span>';
        echo '<span class="error_cause">'.$query.'</span>';
        return;
    }
}
/**
  * Returns a list of news between adate and bdate both inclusive
  */
function getNewsBetween($adate, $bdate) {
  return getNews('where date between '.mysql_real_escape_string($adate).' and '.mysql_real_escape_string($bdate));
  }

?>