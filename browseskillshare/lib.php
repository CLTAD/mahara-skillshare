<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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

defined('INTERNAL') || die();

class PluginArtefactBrowseSkillshare extends PluginArtefact {

    public static function get_artefact_types() {
        return array('browseskillshare');
    }

    public static function get_block_types() {
        return array();
    }

    public static function get_plugin_name() {
        return 'browseskillshare';
    }

    public static function menu_items() {
        return array(
            'dashboard/browseskillshare' => array (
                'path' => 'dashboard/browseskillshare',
                'url'  => 'artefact/browseskillshare',
                'title' => get_string('browseskillshare', 'artefact.browseskillshare'),
                'weight' => 50,
            ),
        );
    }

}

class ArtefactTypeBrowseSkillshare extends ArtefactType {

    public function __construct($id = 0, $data = null) {
        parent::__construct($id, $data);
    }

    public static function get_links($id) {
        return array();
    }

    public function delete() {
        return;
    }

    public static function get_icon($options=null) {
    }

    public static function is_singular() {
        return true;
    }

    /**
     * This function returns a list of browsable items.
     *
     * @param limit how many items to display per page
     * @param offset current page to display
     * @return array (count: integer, data: array)
     */

    public static function get_browsable_items($filters, $offset=0, $limit=10) {
        global $USER;
        $contents = array();
        $wwwroot = get_config('wwwroot');

        /*
         * Example query
        SELECT a.id, a.owner, a.mtime, s.artefact, s.statement, s.offered, s.wanted, s.statementtitle, s.externalwebsite, s.externalwebsiterole, s.publishskills
        FROM artefact a
        JOIN artefact_skillshare s ON a.id = s.artefact
        WHERE s.publishskills = 1
        GROUP BY a.id
        ORDER BY a.mtime DESC
        LIMIT 10 
         */
        $selectclause =     'SELECT a.id, a.owner, a.mtime, s.artefact, s.statement, s.offered, s.wanted, s.statementtitle, s.externalwebsite, s.externalwebsiterole, s.publishskills';
        $fromclause =       ' FROM {artefact} a';
        $joinclause =       ' JOIN {artefact_skillshare} s ON a.id = s.artefact';
        $join2clause =      '';
        $whereclause =      " WHERE a.artefacttype = 'skillshare'";
        $andclause =        ' AND s.publishskills = 1';

        $onetimeclause = false;

        foreach ($filters as $filterkey => $filterval){
            
            switch ($filterkey){

                case 'keyword':
                    $join2clause = ' LEFT OUTER JOIN {artefact_tag} t ON t.artefact = a.id';
                    $keywords = explode(",", $filterval);
                    if (count($keywords) == 1 ){
                        $andclause .= " AND (
                                    LOWER(s.statementtitle) LIKE LOWER('%$filterval%') 
                                    OR LOWER(s.statement) LIKE LOWER('%$filterval%')
                                    OR (LOWER(t.tag) LIKE LOWER('%$filterval%') AND t.artefact = a.id)
                                    )";
                    } else {
                        foreach($keywords as $key => $word){
                            if ($key == 0){
                                $andclause .= " AND (
                                    (
                                    LOWER(s.statementtitle) LIKE LOWER('%$word%') 
                                    OR LOWER(s.statement) LIKE LOWER('%$word%')
                                    OR (LOWER(t.tag) LIKE LOWER('%$word%') AND t.artefact = a.id)
                                    )";
                            } else {
                                $andclause .= " AND 
                                    (
                                    LOWER(s.statementtitle) LIKE LOWER('%$word%') 
                                    OR LOWER(s.statement) LIKE LOWER('%$word%')
                                    OR (LOWER(t.tag) LIKE LOWER('%$word%') AND t.artefact = a.id)
                                    )";
                            }
                            if ($key == count($keywords)-1){
                                $andclause .= ')';
                            }
                        }
                    }
                    
                    break;
                case 'sharetype':
                    // skill categories. 1 == 'None'
                    if ($filterval=='1'){
                        $andclause .= " AND s.offered > 0";
                    } else if ($filterval=='2'){
                        $andclause .= " AND s.wanted > 0";
                    }
                    break;
                case 'college' :
                    if (!empty($filterval) && !$onetimeclause) {
                        $join2clause .= ' JOIN {usr_enrolment} e ON e.usr = a.owner';
                        $selectclause .= ', e.college, e.course';
                        $ontimeclause = true;
                    }
                    $andclause .= " AND e.college IN ($filterval)";
                    break;
                case 'course' :
                    if (!empty($filterval) && !$onetimeclause) {
                        $join2clause .= ' JOIN {usr_enrolment} e ON e.usr = a.owner';
                        $selectclause .= ', e.college, e.course';
                        $ontimeclause = true;
                    }
                    $courseidgroups = explode(";", $filterval);
                    if (count($courseidgroups) == 1){
                        // one course submitted, could have multiple csv ids if selected by name
                        $courseids = explode(",", $courseidgroups[0]);
                        if (count($courseids) == 1 ){
                            $andclause .= " AND (e.course LIKE '%$courseids[0]%' AND e.usr = a.owner)";
                        } else if (count($courseids) > 1 ){
                            foreach($courseids as $key => $id){
                                if ($key == 0){
                                    $andclause .= " AND (e.course LIKE '%$id%'";
                                } else {
                                    $andclause .= " OR e.course LIKE '%$id%'";
                                }
                                if ($key == count($courseids)-1){
                                    $andclause .= ')';
                                }
                            }
                        }
                    } else if (count($courseidgroups) > 1) {
                        // more than one course submitted
                        foreach($courseidgroups as $key => $coursegroup){
                            
                            if ($key == 0){
                                $courseids = explode(",", $coursegroup);
                                if (count($courseids) == 1 ){
                                    $andclause .= " AND ((e.course LIKE '%$courseids[0]%')";
                                } else if (count($courseids) > 1 ){
                                    foreach($courseids as $key => $id){
                                        if ($key == 0){
                                            $andclause .= " AND ((e.course LIKE '%$id%'";
                                        } else {
                                            $andclause .= " OR e.course LIKE '%$id%'";
                                        }
                                        if ($key == count($courseids)-1){
                                            $andclause .= ')';
                                        }
                                    }
                                }
                                
                            } else {
                                // key != 0
                                $courseids = explode(",", $coursegroup);
                                if (count($courseids) == 1 ){
                                    $andclause .= " AND (e.course LIKE '%$courseids[0]%')";
                                } else if (count($courseids) > 1 ){
                                    foreach($courseids as $key => $id){
                                        if ($key == 0){
                                            $andclause .= " AND (e.course LIKE '%$id%'";
                                        } else {
                                            $andclause .= " OR e.course LIKE '%$id%'";
                                        }
                                        if ($key == count($courseids)-1){
                                            $andclause .= ')';
                                        }
                                    }
                                }
                            }
                        }
                    }
                    break;
            }
        }

        /**
        * The query checks for skillshare artefacts.
        */
        $skillshareitems = get_records_sql_array("
                        $selectclause 
                        $fromclause
                        $joinclause
                        $join2clause
                        $whereclause
                        $andclause
                        GROUP BY a.id
                        ORDER BY a.mtime DESC
                        ", array(), $offset, $limit);

        // UAL-specific code
        /*
        $skillsharecolleges = get_records_assoc('college');
        $optionscolleges = array();
        foreach ($skillsharecolleges as $key => $coll ) {
            $optionscolleges[$key] = $coll->college;
        }
        */
        
        if ($skillshareitems) {
            foreach ($skillshareitems as $key => $item) {
                
                if (!isset($item->statement)) {
                    continue;
                }
                $images = get_records_sql_array('SELECT a.id, a.title, a.description, a.note
                                                FROM {artefact} a
                                                WHERE artefacttype = ?
                                                AND a.owner = ?
                                                ORDER BY a.note, a.id', array('skillshareimage', $item->owner)
                                                );
                $tagsarray = get_column('artefact_tag', 'tag', 'artefact', $item->id);
                $tags = '';
                if ($tagsarray) {
                    $tags = implode(", ", $tagsarray);
                }

                $exampleimages = array();
                if ($images){
                    foreach ($images as $image){
                        $exampleimages[] = array( 
                            'link' => $wwwroot . 'artefact/skillshare/image.php?type=skillshareimage&id=' . $image->id, 
                            'source' => $wwwroot . 'artefact/skillshare/image.php?type=skillshareimage&maxsize=80&id=' . $image->id,
                            'title' => $image->description
                            );
                    }
                }

                // UAL-specific code
                // Disable call to db
                $courseandcollege = null; //get_record('usr_enrolment', 'usr', $item->owner);
                $collegeid = empty($courseandcollege->college)? 0 : $courseandcollege->college;
                $courseid = empty($courseandcollege->course)? 'none' : $courseandcollege->course;
                $coursename = array($courseid);
                $allcourseids = explode(',', $courseid);
                
                /*
                if ($courseid != 'none') {
                    try {
                        $databasetype = 'mysql';
                        $server = '';
                        $user   = '';
                        $password = '';
                        $database = '';
                        $dbext = ADONewConnection($databasetype);
                        $dbext->debug = false;
                        $dbext->Connect($server, $user, $password, $database);
                        foreach ($allcourseids as $key => $singlecourseid){
                            $rs = $dbext->Execute('SELECT COURSE_NAME AS coursename
                                    FROM courses
                                    WHERE EXTERNAL_COURSE_KEY = ?
                                    GROUP BY EXTERNAL_COURSE_KEY',
                                    array($singlecourseid) );
                           while ($row = $rs->FetchNextObject()){
                                if ($key == 0){
                                    $coursename = array($row->COURSENAME);
                                } else {
                                    $coursename[] = $row->COURSENAME;
                                }
                            }
                        }                 
                    } catch (Exception $e) {
                        log_warn("Exception thrown trying to retrieve course and college in browseskillshare: " . $e);
                    }
                }
                */

                $statement = str_shorten_html($item->statement, 300, true);
                // compensate for over-enthusiastic setting of <br>s in str_shorten_html
                $pattern = "/<br \/>\s<br \/>\s/s";
                preg_match($pattern, $statement, $matches);
                if (count($matches)){
                    $statement = str_replace($matches[0], '', $statement);
                }
                
                $userobj = new User();
                $userobj->find_by_id($item->owner);
                $profileurl = profile_url($userobj);
                $contents[] = array("id"             => $item->id,
                                    "statementtitle" => isset($item->statementtitle)? $item->statementtitle : get_string('statementtitle', 'artefact.browseskillshare'),
                                    "owner"            => display_name($item->owner),
                                    "profilepage"    => $profileurl,
                                    "messagelink"    => $wwwroot . 'user/sendmessage.php?id=' . $item->owner . '&returnto=skillshare',
                                    "images"         =>  (count($exampleimages))? $exampleimages : array(),
                                    "statement"     => $statement,
                                    "project"         => isset($item->project)? $item->project : '',
                                    "tags"            => $tags,
                                    "college"        => ($collegeid > 0)? $optionscolleges[$collegeid] : 0,
                                    "course"        => $coursename,
                                    "wanted"        => isset($item->wanted)? $item->wanted: 0,
                                    "offered"        => isset($item->offered)? $item->offered : 0,
                                    "yearofstudy"    => isset($yos)? $yos : get_string('notspecified', 'blocktype.skillshare/skillshare'),
                                    "externalwebsite" => isset($item->externalwebsite)? $item->externalwebsite : '',
                                    "externalwebsiterole" => isset($item->externalwebsiterole)? $item->externalwebsiterole : ''
                                    );
            } // foreach
        }

        $count = count_records_sql("
                                SELECT COUNT(distinct a.id)
                                $fromclause
                                $joinclause
                                $join2clause
                                $whereclause
                                $andclause
                                ", array());

        $items = array(
                'count'  => $count,
                'data'   => $contents,
                'offset' => $offset,
                'limit'  => $limit,
        );
        return $items;
    }

    public static function get_fullscreen_item($id, $imageindex) {
        global $USER;
        $wwwroot = get_config('wwwroot');

        /**
        * The query checks for skillshare artefacts.
        */
        $skillshareitem = get_record_sql("
                            SELECT a.id, a.owner, s.*
                            FROM {artefact} a, {artefact_skillshare} s 
                            WHERE a.id = s.artefact
                            AND a.artefacttype = 'skillshare'
                            AND s.publishskills = 1
                            AND a.id = ?
                            ", array($id));
        
        // UAL specific code
        /*
        $skillsharecolleges = get_records_assoc('college');
        $optionscolleges = array();
        foreach ($skillsharecolleges as $key => $college ) {
            $optionscolleges[$key] = $college->college;
        }
        */
        
        if ($skillshareitem) {

                if (!isset($skillshareitem->statement)) {
                    continue;
                }
                $images = get_records_sql_array('SELECT a.id, a.title, a.description, a.note
                                                    FROM {artefact} a
                                                    WHERE artefacttype = \'skillshareimage\'
                                                    AND a.owner = ?
                                                    ORDER BY a.note, a.id', array($skillshareitem->owner)
                );
                $where = 'artefact = ?';
                $tagsarray = get_records_select_array('artefact_tag', $where, array($skillshareitem->id));
                if ($tagsarray) {
                    foreach ($tagsarray as $t) {
                        $tagsonly[] = $t->tag;
                    }
                    $tags = implode(",", $tagsonly);
                } else {
                    $tags = '';
                }

                if ($images){
                    foreach ($images as $image){
                        $exampleimages[] = array(
                                'link' => $wwwroot . 'artefact/skillshare/image.php?type=skillshareimage&id=' . $image->id,
                                'thumb' => $wwwroot . 'artefact/skillshare/image.php?type=skillshareimage&maxsize=80&id=' . $image->id,
                                'source' => $wwwroot . 'artefact/skillshare/image.php?type=skillshareimage&id=' . $image->id,
                                'title' => $image->description
                        );
                    }
                } else {
                    $exampleimages = array();
                }

                // UAL-specific code
                // disable call to database
                $courseandcollege = null; //get_record('usr_enrolment', 'usr', $skillshareitem->owner);
                $collegeid = empty($courseandcollege->college)? 0 : $courseandcollege->college;
                $courseid = empty($courseandcollege->course)? 'none' : $courseandcollege->course;
                $coursename = array($courseid);
                $allcourseids = explode(',', $courseid);
                
                /*
                if ($courseid != 'none') {
                    try {
                        $databasetype = 'mysql';
                        $server = '';
                        $user   = '';
                        $password = '';
                        $database = '';
                        $dbext = ADONewConnection($databasetype);
                        $dbext->debug = false;
                        $dbext->Connect($server, $user, $password, $database);
                        foreach ($allcourseids as $key => $singlecourseid){
                            $rs = $dbext->Execute('SELECT COURSE_NAME AS coursename
                                                    FROM courses
                                                    WHERE EXTERNAL_COURSE_KEY = ?
                                                    GROUP BY EXTERNAL_COURSE_KEY',
                                                    array($singlecourseid) );
                            while ($row = $rs->FetchNextObject()){
                                if ($key == 0){
                                    $coursename = array($row->COURSENAME);
                                } else {
                                    $coursename[] = $row->COURSENAME;
                                }
                            }
                        }

                    } catch (Exception $e) {
                        log_warn("Exception thrown trying to retrieve course and college in browseskillshare: " . $e);
                    }
                }
                */

                $statement = strip_tags($skillshareitem->statement, '<p><a><br><strong><em>');

                if (count($exampleimages)==0){
                    $exampleimages[] = array (
                                    'link' => $wwwroot . 'artefact/browseskillshare/theme/raw/static/images/noimagesuploaded.png',
                                    'thumb' => $wwwroot . 'artefact/browseskillshare/theme/raw/static/images/noimagesuploadedthumb.png',
                                    'source' => '#',
                                    'title' => 'No images uploaded'
                                     );
                }

                $userobj = new User();
                $userobj->find_by_id($skillshareitem->owner);
                $profileurl = profile_url($userobj);    
                $contents = array(        "id"             => $skillshareitem->id,
                                        "shareurl"        => $wwwroot . '#public/skillshare/listing-' . $skillshareitem->id,
                                        "statementtitle" => isset($skillshareitem->statementtitle)? $skillshareitem->statementtitle : get_string('statementtitle', 'artefact.browseskillshare'),
                                        "owner"            => display_name($skillshareitem->owner),
                                        "profilepage"    => $profileurl,
                                        "messagelink"    => $wwwroot . 'user/sendmessage.php?id=' . $skillshareitem->owner . '&returnto=skillshare',
                                        "images"         => $exampleimages,
                                        "statement"     => $statement,
                                        "project"         => isset($skillshareitem->project)? $skillshareitem->project : '',
                                        "tags"            => $tags,
                                        "college"        => 0 /*($collegeid > 0)? $optionscolleges[$collegeid] : 0*/,
                                        "course"        => $coursename,
                                        "wanted"        => isset($skillshareitem->wanted)? $skillshareitem->wanted: 0,
                                        "offered"        => isset($skillshareitem->offered)? $skillshareitem->offered : 0,
                                        "yearofstudy"    => isset($yos)? $yos : get_string('notspecified', 'blocktype.skillshare/skillshare'),
                                        "externalwebsite" => isset($skillshareitem->externalwebsite)? $skillshareitem->externalwebsite : '',
                                        "externalwebsiterole" => isset($skillshareitem->externalwebsiterole)? $skillshareitem->externalwebsiterole : ''
                );

        }

        $item = array(
                    'data'   => $contents,
                    'imageindex' => $imageindex,
        );
        return $item;
    }

    /**
     * Builds the browse display
     *
     * @param items (reference)
     */
    public static function build_browse_list_html(&$items) {
        $smarty = smarty_core();
        $smarty->assign_by_ref('items', $items);
        $smarty->assign('cellwidth', 120);
        // compensate for table padding, which has no effect on bg images
        $padding = 8;
        $smarty->assign('padding', $padding);
        $items['tablerows'] = $smarty->fetch('artefact:browseskillshare:browselist.tpl'); // the 'tablerows' naming is required for pagination script
        // we set url later to call a javascript function
        $pagination = build_browseskillshare_pagination(array(
            'id' => 'browseskillsharelist_pagination',
            'jsonscript' => 'artefact/browseskillshare/browseskillshare.json.php',
            'datatable' => 'browselist', // the pagination script expects a table with this id
            'count' => $items['count'],
            'setlimit' => false,
            'limit' => $items['limit'],
            'offset' => $items['offset'],
            'url' => 'artefact/browseskillshare',
            'firsttext' => '',
            'previoustext' => '',
            'nexttext' => '',
            'lasttext' => '',
            'numbersincludefirstlast' => false,
            'resultcounttextsingular' => 'Item', 
            'resultcounttextplural' => 'Items',
        ));
        $items['pagination'] = $pagination['html'];
        $items['pagination_js'] = $pagination['javascript'];
        unset($items['data']); // not needed now we have tablerows. Remove to reduce size of transaction
    }

    public static function build_fullscreen_html(&$item) {
        $smarty = smarty_core();
        $smarty->assign_by_ref('item', $item);
        $smarty->assign('cellwidth', 120);
        // compensate for table padding, which has no effect on bg images
        $padding = 8;
        $smarty->assign('padding', $padding);
        $item['html'] = $smarty->fetch('artefact:browseskillshare:fullscreen.tpl');
        unset($item['data']); // not needed now we have tablerows. Remove to reduce size of transaction
    }
}

function build_browseskillshare_pagination($params) {
    $limitoptions = array(10, 20, 50, 100, 500);
    // Bail if the required attributes are not present
    $required = array('url', 'count', 'limit', 'offset');
    foreach ($required as $option) {
        if (!isset($params[$option])) {
            throw new ParameterException('You must supply option "' . $option . '" to build_pagination');
        }
    }

    if (isset($params['setlimit']) && $params['setlimit']) {
        if (!in_array($params['limit'], $limitoptions)) {
            $params['limit'] = 10;
        }
        if (!isset($params['limittext'])) {
            $params['limittext'] = get_string('maxitemsperpage');
        }
    }
    else {
        $params['setlimit'] = false;
    }

    // Work out default values for parameters
    if (!isset($params['id'])) {
        $params['id'] = substr(md5(microtime()), 0, 4);
    }

    $params['offsetname'] = (isset($params['offsetname'])) ? $params['offsetname'] : 'offset';
    if (isset($params['forceoffset']) && !is_null($params['forceoffset'])) {
        $params['offset'] = (int) $params['forceoffset'];
    }
    else if (!isset($params['offset'])) {
        $params['offset'] = param_integer($params['offsetname'], 0);
    }

    // Correct for odd offsets
    if ($params['limit']) {
        $params['offset'] -= $params['offset'] % $params['limit'];
    }

    $params['firsttext'] = (isset($params['firsttext'])) ? $params['firsttext'] : get_string('first');
    $params['previoustext'] = (isset($params['previoustext'])) ? $params['previoustext'] : get_string('previous');
    $params['nexttext']  = (isset($params['nexttext']))  ? $params['nexttext'] : get_string('next');
    $params['lasttext']  = (isset($params['lasttext']))  ? $params['lasttext'] : get_string('last');
    $params['resultcounttextsingular'] = (isset($params['resultcounttextsingular'])) ? $params['resultcounttextsingular'] : get_string('result');
    $params['resultcounttextplural'] = (isset($params['resultcounttextplural'])) ? $params['resultcounttextplural'] : get_string('results');

    if (!isset($params['numbersincludefirstlast'])) {
        $params['numbersincludefirstlast'] = true;
    }
    if (!isset($params['numbersincludeprevnext'])) {
        $params['numbersincludeprevnext'] = 1;
    }
    else {
        $params['numbersincludeprevnext'] = (int) $params['numbersincludeprevnext'];
    }

    if (!isset($params['extradata'])) {
        $params['extradata'] = null;
    }

    // Begin building the output
    $output = '<div id="' . $params['id'] . '" class="pagination fr';
    if (isset($params['class'])) {
        $output .= ' ' . hsc($params['class']);
    }
    $output .= '">';

    if ($params['limit'] && ($params['limit'] < $params['count'])) {
        $pages = ceil($params['count'] / $params['limit']);
        $page = $params['offset'] / $params['limit'];

        $last = $pages - 1;
        if (!empty($params['lastpage'])) {
            $page = $last;
        }
        $prev = max(0, $page - 1);
        $next = min($last, $page + 1);

        // Build a list of what pagenumbers will be put between the previous/next links
        $pagenumbers = array();

        // First page
        if ($params['numbersincludefirstlast']) {
            $pagenumbers[] = 0;
        }

        $maxjumplinks = isset($params['jumplinks']) ? (int) $params['jumplinks'] : 0;

        // Jump pages between first page and current page
        $betweencount = $page;
        $jumplinks = $pages ? round($maxjumplinks * ($betweencount / $pages)) : 0;
        $jumpcount = $jumplinks ? round($betweencount / ($jumplinks + 1)) : 0;
        $gapcount = 1;
        if ($jumpcount > 1) {
            for ($bc = 1; $bc < $betweencount; $bc++) {
                if ($gapcount > $jumpcount) {
                    $pagenumbers[] = $bc;
                    $gapcount = 1;
                }
                $gapcount++;
            }
        }

        // Current page with adjacent prev and next pages
        if ($params['numbersincludeprevnext'] > 0) {
            for ($i=$params['numbersincludeprevnext']; $i > 0; $i--) {
                $prevlink = $page - $i;
                if ($prevlink < 0) {
                    break;
                }
                $pagenumbers[] = $prevlink;
            }
            unset($prevlink);
        }
        $pagenumbers[] = $page;
        if ($params['numbersincludeprevnext'] > 0) {
            for ($i = 1; $i <= $params['numbersincludeprevnext']; $i++) {
                $nextlink = $page + $i;
                if ($nextlink > $last) {
                    break;
                }
                $pagenumbers[] = $nextlink;
            }
        }

        // Jump pages between current and last
        $betweencount = $pages - $page;
        $jumplinks = $pages ? round($maxjumplinks * ($betweencount / $pages)) : 0;
        $jumpcount = $jumplinks ? round($betweencount / ($jumplinks + 1)) : 0;
        $gapcount = 1;
        if ($jumpcount > 1) {
            for ($bc = $page; $bc < $last; $bc++) {
                if ($gapcount > $jumpcount) {
                    $pagenumbers[] = $bc;
                    $gapcount = 1;
                }
                $gapcount++;
            }
        }

        // Last page
        if ($params['numbersincludefirstlast']) {
            $pagenumbers[] = $last;
        }
        $pagenumbers = array_unique($pagenumbers);
        sort($pagenumbers);

        // Build the first/previous links
        $isfirst = $page == 0;
        $output .= build_skillshare_pagination_pagelink('first', $params['url'], $params['setlimit'], $params['limit'], 0, '&laquo; ' . $params['firsttext'], get_string('firstpage'), $isfirst, $params['offsetname']);
        $output .= build_skillshare_pagination_pagelink('prev', $params['url'], $params['setlimit'], $params['limit'], $params['limit'] * $prev, '&larr; ' . $params['previoustext'], get_string('prevpage'), $isfirst, $params['offsetname']);

        // Build the pagenumbers in the middle
        foreach ($pagenumbers as $k => $i) {
            if ($k != 0 && $prevpagenum < $i - 1) {
                $output .= '…';
            }
            if ($i == $page) {
                $output .= '<span class="selected">' . ($i + 1) . '</span>';
            }
            else {
                $output .= build_skillshare_pagination_pagelink('', $params['url'], $params['setlimit'], $params['limit'],
                        $params['limit'] * $i, $i + 1, '', false, $params['offsetname']);
            }
            $prevpagenum = $i;
        }

        // Build the next/last links
        $islast = $page == $last;
        $output .= build_skillshare_pagination_pagelink('next', $params['url'], $params['setlimit'], $params['limit'], $params['limit'] * $next,
                $params['nexttext'] . ' &rarr;', get_string('nextpage'), $islast, $params['offsetname']);
        $output .= build_skillshare_pagination_pagelink('last', $params['url'], $params['setlimit'], $params['limit'], $params['limit'] * $last,
                $params['lasttext'] . ' &raquo;', get_string('lastpage'), $islast, $params['offsetname']);
    }

    // Build limitoptions dropbox
    if ($params['setlimit']) {
        $strlimitoptions = array();
        $limit = $params['limit'];
        for ($i = 0; $i < count($limitoptions); $i++) {
            if ($limit == $limitoptions[$i]) {
                $strlimitoptions[] = "<option value = '$limit' selected='selected'> $limit </option>";
            }
            else {
                $strlimitoptions[] = "<option value = '$limitoptions[$i]'> $limitoptions[$i] </option>";
            }
        }
        $output .= '<form class="pagination" action="' . $params['url'] . '" method="POST">
        <span class="pagination"> ' . $params['limittext'] . '</span>' .
        '<select id="setlimitselect" class="pagination" name="limit"> '.
        join(' ', $strlimitoptions) .
        '</select>
        <input class="pagination js-hidden" type="submit" name="submit" value="' . get_string('change') . '"/>
        </form>';
    }

    // Work out what javascript we need for the paginator
    $js = '';
    $id = json_encode($params['id']);
    if (isset($params['jsonscript']) && isset($params['datatable'])) {
        $paginator_js = hsc(get_config('wwwroot') . 'js/paginator.js');
        $datatable    = json_encode($params['datatable']);
        $jsonscript   = json_encode($params['jsonscript']);
        $extradata    = json_encode($params['extradata']);
        $js .= "new Paginator($id, $datatable, $jsonscript, $extradata);";
    }
    else {
        $js .= "new Paginator($id, null, null, null);";
    }

    // Output the count of results
    $resultsstr = ($params['count'] == 1) ? $params['resultcounttextsingular'] : $params['resultcounttextplural'];
    $output .= '<div class="results">' . $params['count'] . ' ' . $resultsstr . '</div>';

    // Close the container div
    $output .= '</div>';

    return array('html' => $output, 'javascript' => $js);

}

function build_skillshare_pagination_pagelink($class, $url, $setlimit, $limit, $offset, $text, $title, $disabled=false, $offsetname='offset') {
    $return = '<span class="pagination';
    $return .= ($class) ? " $class" : '';

    $url = "javascript:BrowseManager.filter_content('skillshare'," . $offset . ");";

    if ($disabled) {
        $return .= ' disabled">' . $text . '</span>';
    }
    else {
        $return .= '">'
        . '<a href="' . $url . '" title="' . $title
        . '">' . $text . '</a></span>';
    }

    return $return;
}