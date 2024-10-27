<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2016 Stendhal

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

require_once '../../configuration.php';
require_once '../../scripts/imageprocessing.php';

$OUTFITS_BASE="../../data/sprites/outfit";

$params = [
	//background, offset,   x,   y
	['balduin',        1, 170, 190],
	['hayunn_naratha', 3, 280, 130],
	['semosdungeon',   3, 260, 155],
	['tempel',         0, 280, 140],
	['jenny',          3, 320, 200],
	['gnomes',         3, 420, 150],
	['ados',           1, 100, 150],
	['toweroutside',   0, 250, 230],
	['wildlife',       0, 215,  20],
	['wizzardtower',   0, 345, 195],
	['annie',          3, 110,  80],
	['hell',           0, 140, 160],
	['imorgen',        1, 220, 200],
	['imperial',       1, 130, 220],
	['nalworhut',      2, 225, 140],
	['onicastle',      2, 200,  40],
	['sally',          1, 150, 160]
];

$i = $_GET['i'];
$completeOutfit = $_GET['outfit'];

if (defined('STENDHAL_SECRET') && ($_GET['h'] != hash_hmac('sha256', $i.'_'.$completeOutfit, STENDHAL_SECRET))) {
	header('404 Page not found');
	echo 'Page not found';
	exit;
}

header('Content-Type: image/png');

$image = new Imagick('../../images/photos/'.$params[$i][0].'.png');

$offset = $params[$i][1];

$drawer = new OutfitDrawer();
$outfit = $drawer->create_outfit(explode('_', $completeOutfit), $offset);
$image->compositeImage($outfit, Imagick::COMPOSITE_OVER, $params[$i][2], $params[$i][3]);

$texture = new Imagick('../../images/photos/canvas.jpg');
$image->compositeImage($image->textureImage($texture), Imagick::COMPOSITE_SOFTLIGHT, 0, 0);

echo $image;
