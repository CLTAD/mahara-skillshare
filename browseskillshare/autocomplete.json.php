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
$field = param_alpha('field', '');
$term = param_variable('term', '');
if (isset($field) && isset($term)) {
    $querytype = $field;
    $queryterm = $term;
}

$result = array();
$localenrolments = get_records_sql_array("SELECT DISTINCT course FROM usr_enrolment WHERE course != 'none'", array());
$localcourseids = array();
if ($localenrolments) {
    foreach ($localenrolments as $row) {
        $allcourses = explode(',', $row->course);
        foreach ($allcourses as $course){
            if (!in_array($course, $localcourseids) && strlen($course)){
                $localcourseids[] = $course;
            }
        }
    }
}

switch ($querytype) {
    case 'course':
        
        try {
            $databasetype = 'mysql';
            $server = '';
            $user   = '';
            $password = '';
            $database = '';
            $dbext = ADONewConnection($databasetype);
            $dbext->debug = false;
            $dbext->Connect($server, $user, $password, $database);
            $rs = $dbext->Execute('SELECT EXTERNAL_COURSE_KEY as courseid, COURSE_NAME AS coursename
                                   FROM courses
                                   WHERE EXTERNAL_COURSE_KEY LIKE ?
                                   OR COURSE_NAME LIKE ?
                                   GROUP BY EXTERNAL_COURSE_KEY',
                                   array('%' . $queryterm . '%', '%' . $queryterm . '%') );
            $ids = array();
            while ($row = $rs->FetchNextObject()){
                $posname = strpos(strtolower($row->COURSENAME), strtolower($queryterm));
                $posid = strpos(strtolower($row->COURSEID), strtolower($queryterm));
                if ($posname !== false && in_array($row->COURSEID, $localcourseids)){
                    $result['courses'][] = $row->COURSENAME;
                }
                else if ($posid !== false && in_array($row->COURSEID, $localcourseids)){
                    $result['courses'][] = $row->COURSEID;
                }
            }
            
        } catch (Exception $e) {
            log_warn("Exception thrown trying to retrieve course and college in browseskillshare: " . $e);
        }
        
        $result['error'] = false;
        $result['message'] = false;
        break;
    case 'courseid':
        $courseids = array();        
        try {
            $databasetype = 'mysql';
            $server = '';
            $user   = '';
            $password = '';
            $database = '';
            $dbext = ADONewConnection($databasetype);
            $dbext->debug = false;
            $dbext->Connect($server, $user, $password, $database);
            $rs = $dbext->Execute('SELECT EXTERNAL_COURSE_KEY as courseid
                                   FROM courses
                                   WHERE EXTERNAL_COURSE_KEY = ?
                                   OR COURSE_NAME LIKE ?
                                   GROUP BY EXTERNAL_COURSE_KEY',
                                   array($queryterm, '%' . $queryterm . '%') );
            while ($row = $rs->FetchNextObject()){
                if (in_array($row->COURSEID, $localcourseids)){
                    $courseids[] = $row->COURSEID;
                }
            }
        
        } catch (Exception $e) {
            log_warn("Exception thrown trying to retrieve courseid in browseskillshare: " . $e);
        }

        $result['courseid'] = implode(",", $courseids);
        $result['error'] = false;
        $result['message'] = false;
        break;
}

json_headers();
echo json_encode($result);