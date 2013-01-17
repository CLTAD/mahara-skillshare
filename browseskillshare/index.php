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
 * @author     Mike Kelly
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 *
 */


define('INTERNAL', 1);
define('PUBLIC', 1);
define('MENUITEM', 'dashboard/browseskillshare');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'browseskillshare');
define('SECTION_PAGE', 'index');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'browseskillshare');
global $THEME;

define('TITLE', get_string('browseskillshare','artefact.browseskillshare'));
$wwwroot = get_config('wwwroot');
// offset and limit for pagination
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 10);

//UAL-specific code
/*
$skillsharecolleges = get_records_assoc('college');
foreach ($skillsharecolleges as $key => $college ) {
    $optionscolleges[$key] = $college->abbrev;
}
*/
$filters = array();
$items = ArtefactTypeBrowseSkillshare::get_browsable_items($filters, $offset, $limit);
ArtefactTypeBrowseSkillshare::build_browse_list_html($items);

$smarty = smarty(array('jquery',
                        'artefact/browseskillshare/js/browseskillshare.js',
                        'artefact/browseskillshare/js/jquery.galleriffic.js',
                        'artefact/browseskillshare/js/jquery.opacityrollover.js',
                        'artefact/browseskillshare/js/jquery.history.js',
                        'artefact/browseskillshare/js/jquery-ui/jquery-ui-1.8.19.custom.min.js',
                        'artefact/browseskillshare/js/chosen/chosen.jquery.min.js'),
                 array('<link href="' . get_config('wwwroot') . 'artefact/browseskillshare/js/jquery-ui/css/jquery-ui-1.8.20.custom.css" type="text/css" rel="stylesheet">',
                       '<link href="' . get_config('wwwroot') . 'artefact/browseskillshare/js/chosen/css/chosen.css" type="text/css" rel="stylesheet">')
                 );
$smarty->assign_by_ref('items', $items);
$smarty->assign('wwwroot', $wwwroot);
$smarty->assign('PAGEHEADING', hsc(get_string("browseskillshare", "artefact.browseskillshare")));
//$smarty->assign('colleges', $optionscolleges);
$smarty->display('artefact:browseskillshare:index.tpl');
