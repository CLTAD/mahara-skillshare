<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage artefact-browseskillshare
 * @author     Mike Kelly UAL m.f.kelly@arts.ac.uk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 *
 */

define('INTERNAL', 1);
define('JSON', 1);
define('PUBLIC', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'browseskillshare');

$limit = param_integer('limit', 10);
$offset = param_integer('offset', 0);
$filters = array();

if ($keyword = param_variable('keyword', '')) {
    $filters['keyword'] = $keyword;
}
if ($sharetype = param_variable('sharetype', '')) {
    $filters['sharetype'] = $sharetype;
}
if ($college = param_variable('college', '')) {
    $filters['college'] = $college;
}
if ($yearofstudy = param_variable('yearofstudy', '')) {
    $filters['yearofstudy'] = $yearofstudy;
}
if ($course = param_variable('course', '')) {
    $filters['course'] = $course;
}
$items = ArtefactTypeBrowseSkillshare::get_browsable_items($filters, $offset, $limit);
ArtefactTypeBrowseSkillshare::build_browse_list_html($items);

json_reply(false, (object) array('message' => false, 'data' => $items));
