<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2008-2016 Stendhal
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


$OUTFITS_BASE="data/sprites/outfit";

class OutfitDrawer {

	// Imagick takes 'mixed' for colors, but ints didn't work. This seems to.
	function color_name($color) {
		// Drop alpha
		$name = dechex($color & 0xffffff);
		while (strlen($name) < 6) {
			$name = '0' . $name;
		}
		return '#' . $name;
	}

	function formatNumber3($i) {
		if ($i < 10) {
			return "00" . $i;
		}
		if ($i < 100) {
			return "0" . $i;
		}
		return "" . $i;

	}

	/**
	 * Color an image roughly like Stendhal Blend. TrueColor does.
	 *
	 * @param Imagick image
	 * @param int color
	 */
	function color_image($image, $color) {
		// Ensure the target image does not have 0 saturation. First grayscale
		// it, and then recolour it with known saturation.
		$image->modulateImage(100, 0, 100); // grayscale
		$overlay = new Imagick();
		$overlay->newImage($image->getImageWidth(),
			$image->getImageHeight(), 'red', 'png');
		$clone = $image->clone();
		// keep alpha
		$clone->compositeImage($overlay, imagick::COMPOSITE_SRCIN, 0, 0);
		$image->compositeImage($clone, imagick::COMPOSITE_OVERLAY, 0, 0);
		$clone->destroy();

		// color layer
		$overlay->newImage($image->getImageWidth(),
			$image->getImageHeight(), $this->color_name($color), 'png');
		// Color mask of the outfit part. Would not be needed if
		// colorize blend didn't handle alpha in an incompatible way.
		$clone = $image->clone();
		$clone->compositeImage($overlay, imagick::COMPOSITE_SRCIN, 0, 0);
		$overlay->destroy();

		// this is otherwise the usual hue blend, except that it
		// overwrites alpha, sigh
		$image->compositeImage($clone, imagick::COMPOSITE_HUE, 0, 0);
		$clone->destroy();

		// Imagick saturation filter is broken for low saturations.
		// Calculate adjustment.
		$r = (($color >> 16) & 0xff) / 255.0;
		$g = (($color >> 8) & 0xff) / 255.0;
		$b = ($color & 0xff) / 255.0;
		$max_color = max($r, $g, $b);
		$min_color = min($r, $g, $b);
		$lightness = ($max_color + $min_color) / 2;
		$diff = $max_color - $min_color;
		if ($diff < 0.001) {
			$saturation = 0;
		} else {
			if ($lightness < 0.5) {
				$saturation = $diff / ($max_color + $min_color);
			} else {
				$saturation = $diff / (2 - $max_color - $min_color);
			}
		}
		// Red colored image (like the adjusted base image) has saturation 1;
		// adjust it according to the saturation of our painting color.
		$adj_sat = 100 * $saturation;
		// Adjusting brightness does not work exactly as TrueColor does it.
		// TrueColor does a parabolic bend in the lightness curve; Imagick does
		// some other nonlinear adjustment. Hopefully this is close enough.
		$adj_bright = 50 + 100 * $lightness;
		$image->modulateImage($adj_bright, $adj_sat, 100); // LSH
	}

	/**
	 * Load a part of an outfit.
	 *
	 * @param part_name
	 *     Basename of part, "body", "head", "dress", etc.
	 * @param index
	 *     File index.
	 * @param offset
	 * @param suffix
	 *     Optional special case suffix to append to filename (default="").
	 */
	function load_part($part_name, $index, $offset, $suffix='') {
		global $OUTFITS_BASE;

		$location = $OUTFITS_BASE . '/' . $part_name . '/' . $index . $suffix . '.png';
		if (!file_exists($location)) {
    		$location = $OUTFITS_BASE . '/' . $part_name . '/' . $index . '.png';

    		// there are some heads with non existing numbers (e. g. 984)
    		if (!file_exists($location)) {
    		    $location = $OUTFITS_BASE . '/' . $part_name . '/000' . $suffix . '.png';
    		    if (!file_exists($location)) {
    		        $location = $OUTFITS_BASE . '/' . $part_name . '/000.png';
    		    }
    		}
		}

		// A workaround for imagick crashing when the file does not
		// exist.
		if (file_exists($location)) {
			$image = new Imagick($location);
			$w = $image->getImageWidth() / 3;
			$x_pos = $w; // use center frame
			$image->cropImage(48, 64, $x_pos, $offset * 64);
			return $image;
		}
		return 0;
	}

	/**
	 * Paint a colored image over outfit
	 */
	function composite_with_color($outfit, $overlay, $color) {
		if ($overlay) {
			if ($color) {
				$this->color_image($overlay, $color);
			}
			$outfit->compositeImage($overlay, imagick::COMPOSITE_OVER, 0, 0);
		}
	}

	/**
	 * Create an outfit image.
	 */
	function create_outfit_old($completeOutfit, $offset) {
	    // outfit code
	    $code = $completeOutfit[0];
	    // The client won't let select pure black, so 0 works for no color.
	    $detailColor = 0;
	    $hairColor = 0;
	    $dressColor = 0;
	    if (count($completeOutfit) > 1) {
	        $detailColor = hexdec($completeOutfit[1]);
	        $hairColor = hexdec($completeOutfit[2]);
	        $dressColor = hexdec($completeOutfit[4]);
	    }

	    // body:
	    $safe_suffix = '-nonude';
	    $index = $code % 100;
	    $bodyIndex = $index;
	    $outfit = $this->load_part('body', $this->formatNumber3($index), $offset, $safe_suffix);
	    if (!$outfit) {
	        // ensure we have something to draw on
	        $outfit = new Imagick();
	        $outfit->newImage(48, 64, 'transparent', 'png');
	    }

	    // all layers other than "body" can use the standard "-safe" suffix
	    $safe_suffix = '-safe';

	    // dress
	    $code /= 100;
	    $index = $code % 100;
	    if (($index == 0) && ($bodyIndex < 50)) {
	        $index = 91;
	    }
	    if ($index) {
	        $tmp = $this->load_part('dress', $this->formatNumber3($index), $offset, $safe_suffix);
	    } else {
	        $tmp = 0;
	    }
	    $this->composite_with_color($outfit, $tmp, $dressColor);

	    // head
	    $code /= 100;
	    $index = $code % 100;
	    $tmp = $this->load_part('head', $this->formatNumber3($index), $offset, $safe_suffix);
	    if ($tmp) {
	        $outfit->compositeImage($tmp, imagick::COMPOSITE_OVER, 0, 0);
	    }

	    // hair
	    $code /= 100;
	    $index = $code % 100;
	    if ($index) {
	        $tmp = $this->load_part('hair', $this->formatNumber3($index), $offset, $safe_suffix);
	    } else {
	        $tmp = 0;
	    }
	    $this->composite_with_color($outfit, $tmp, $hairColor);

	    // detail
	    $code /= 100;
	    $index = $code % 100;
	    if ($index) {
	        $tmp = $this->load_part('detail', $this->formatNumber3($index), $offset, $safe_suffix);
	    } else {
	        $tmp = 0;
	    }
	    $this->composite_with_color($outfit, $tmp, $detailColor);

	    return $outfit;
	}
	/**
	 * Create an outfit image.
	 */
	function create_outfit($layers, $offset) {
        $outfit = new Imagick();
        $outfit->newImage(48, 64, 'transparent', 'png');

        foreach ($layers as $layer) {
            $l = explode('-', $layer);
            if (count($l) > 3 && $l[1] === '') {
                // head--1-fff5dbc8 means that the head is -1, but "explode" gets confused about the extra "-".
                // Therefore we need this special code to detect -1 in order to skip the layer without drawing the
                // sprites at index 0
                continue;
            }

            $part_name = $1[0];
            $safe_suffix = '-safe';
            if ($part_name == 'body') {
            	$safe_suffix = '-nonude';
            }

    	    $image = $this->load_part($part_name, $this->formatNumber3($l[1]), $offset, $safe_suffix);
    	    $color = hexdec($l[2]);
    	    $this->composite_with_color($outfit, $image, $color);
        }

	    return $outfit;
	}

	/**
	 * tries to load an outfit from the file cache, creates and stores it otherwise
	 *
	 *
	 * @param string $completeOutfit the outfit string, needs to be validated before
	 * @param int $offset direction, needs to be validated before
	 */
	function loadOrCreate($completeOutfit, $offset) {
		$tmp_dir = getenv("TEMP");
		if (is_null($tmp_dir) || $tmp_dir == '') {
			$tmp_dir = getenv("TMP");
		}
		if (is_null($tmp_dir) || $tmp_dir == '') {
			$tmp_dir = "/tmp";
		}

		$cacheIdentifier = $tmp_dir.'/outfits/'.$completeOutfit.'-'.$offset.'.png';

		if (file_exists($cacheIdentifier)) {
			readfile($cacheIdentifier);
 			return;
		}

		if (strpos($completeOutfit, '-') > 0) {
		    $data = $this->create_outfit(explode('_', $completeOutfit), $offset);
		} else {
		    $data = $this->create_outfit_old(explode('_', $completeOutfit), $offset);
		}

		if (!file_exists($tmp_dir.'/outfits')) {
			mkdir($tmp_dir.'/outfits', 0755);
		}
		$fp = fopen($cacheIdentifier, 'xb');
		fwrite($fp, $data);
		fclose($fp);
		echo $data;
	}

	/**
	 * validates the input parameter "outfit"
	 *
	 * @param $outfit
	 * @return boolean
	 */
	function validateInput($outfit) {
		return preg_match('/^[a-z0-9_\\-]+$/', $outfit);
	}
}


class NPCAndCreatureDrawer {

	function createImageData($url) {
		$size = getimagesize($url);
		$x_loc = 0;
		$y_loc = 0;
		$w = $size[0];
		$h = $size[1];

		if (strpos($url, "/ent/") !== false) {
			// Ent images are tiles of 1x2 so we choose a single tile.
			$h = $h / 2;
		} else if (strpos($url, "/alternative/") === false) {
			// Images are tiles of 3x4 so we choose a single tile.
			$w = $w / 3;
			$h = $h / 4;
			$x_loc = $w; // use center frame
			$y_loc = $h * 2; // use south-facing frame
		}

		$result = imagecreatetruecolor($w, $h);
		// preserve alpha
		imagealphablending($result, false);
		imagesavealpha($result, true);

		$white = imagecolorallocatealpha($result, 255, 255, 255, 127);
		imagefilledrectangle($result, 0, 0, $w, $h, $white);

		$baseIm=imagecreatefrompng($url);
		imagecopy($result, $baseIm, 0, 0, $x_loc, $y_loc, $w, $h);
		return $result;
	}
}
