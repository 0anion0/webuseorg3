<?php
// Данный код создан и распространяется по лицензии GPL v3
// Разработчики:
// Грибов Павел,
// Сергей Солодягин (solodyagin@gmail.com)
// (добавляйте себя если что-то делали)
// http://грибовы.рф
include_once ("dbconfig.php");
include_once ("functions.php");
$uidview = GetDef('uidview');

function addCalendar($st, $et, $sub, $ade)
{
    global $sqlcn, $uidview;
    $ret = array();
    try {
        $sql = "insert into `jqcalendar` (`subject`, `starttime`, `endtime`, `isalldayevent`,uidview) values ('" . mysqli_real_escape_string($sqlcn->idsqlconnection, $sub) . "', '" . php2MySqlTime(js2PhpTime($st)) . "', '" . php2MySqlTime(js2PhpTime($et)) . "', '" . mysqli_real_escape_string($sqlcn->idsqlconnection, $ade) . "'," . $uidview . " )";
        // echo($sql);
        if ($sqlcn->ExecuteSQL($sql) == false) {
            
            $ret['IsSuccess'] = false;
            $ret['Msg'] = mysqli_error($sqlcn->idsqlconnection);
        } else {
            $ret['IsSuccess'] = true;
            $ret['Msg'] = 'add success';
            $ret['Data'] = mysqli_insert_id($sqlcn->idsqlconnection);
        }
    } catch (Exception $e) {
        $ret['IsSuccess'] = false;
        $ret['Msg'] = $e->getMessage();
    }
    return $ret;
}

function addDetailedCalendar($st, $et, $sub, $ade, $dscr, $loc, $color, $tz)
{
    global $sqlcn, $uidview;
    $ret = array();
    try {
        $sql = "insert into `jqcalendar` (`subject`, `starttime`, `endtime`, `isalldayevent`, `description`, `location`, `color`,uidview) values ('" . mysqli_real_escape_string($sqlcn->idsqlconnection, $sub) . "', '" . php2MySqlTime(js2PhpTime($st)) . "', '" . php2MySqlTime(js2PhpTime($et)) . "', '" . mysqli_real_escape_string($sqlcn->idsqlconnection, $ade) . "', '" . mysqli_real_escape_string($sqlcn->idsqlconnection, $dscr) . "', '" . mysqli_real_escape_string($sqlcn->idsqlconnection, $loc) . "', '" . mysqli_real_escape_string($sqlcn->idsqlconnection, $color) . "'," . $uidview . " )";
        // echo($sql);
        if ($sqlcn->ExecuteSQL($sql) == false) {
            $ret['IsSuccess'] = false;
            $ret['Msg'] = mysql_error();
        } else {
            $ret['IsSuccess'] = true;
            $ret['Msg'] = 'add success';
            $ret['Data'] = mysqli_insert_id($sqlcn->idsqlconnection);
        }
    } catch (Exception $e) {
        $ret['IsSuccess'] = false;
        $ret['Msg'] = $e->getMessage();
    }
    return $ret;
}

function listCalendarByRange($sd, $ed)
{
    global $sqlcn, $uidview;
    $ret = array();
    $ret['events'] = array();
    $ret["issort"] = true;
    $ret["start"] = php2JsTime($sd);
    $ret["end"] = php2JsTime($ed);
    $ret['error'] = null;
    try {
        $sql = "select * from jqcalendar where uidview=" . $uidview . " and `starttime` between '" . php2MySqlTime($sd) . "' and '" . php2MySqlTime($ed) . "'";
        // $handle = mysql_query($sql);
        // echo "!$sql!";
        $handle = $sqlcn->ExecuteSQL($sql) or die("Не могу сделать выборку событий из календаря!" . mysqli_error($sqlcn->idsqlconnection));
        
        while ($row = mysqli_fetch_array($handle)) {
            // $ret['events'][] = $row;
            // $attends = $row->AttendeeNames;
            // if($row->OtherAttendee){
            // $attends .= $row->OtherAttendee;
            // }
            // echo $row->StartTime;
            $ret['events'][] = array(
                $row["Id"],
                $row["Subject"],
                php2JsTime(mySql2PhpTime($row["StartTime"])),
                php2JsTime(mySql2PhpTime($row["EndTime"])),
                $row["IsAllDayEvent"],
                0, // more than one day event
                   // $row->InstanceType,
                0, // Recurring event,
                $row["Color"],
                1, // editable
                $row["Location"],
                '' // $attends
            );
        }
    } catch (Exception $e) {
        $ret['error'] = $e->getMessage();
    }
    return $ret;
}

function listCalendar($day, $type)
{
    $phpTime = js2PhpTime($day);
    // echo $phpTime . "+" . $type;
    switch ($type) {
        case "month":
            $st = mktime(0, 0, 0, date("m", $phpTime), 1, date("Y", $phpTime));
            $et = mktime(0, 0, - 1, date("m", $phpTime) + 1, 1, date("Y", $phpTime));
            break;
        case "week":
            // suppose first day of a week is monday
            $monday = date("d", $phpTime) - date('N', $phpTime) + 1;
            // echo date('N', $phpTime);
            $st = mktime(0, 0, 0, date("m", $phpTime), $monday, date("Y", $phpTime));
            $et = mktime(0, 0, - 1, date("m", $phpTime), $monday + 7, date("Y", $phpTime));
            break;
        case "day":
            $st = mktime(0, 0, 0, date("m", $phpTime), date("d", $phpTime), date("Y", $phpTime));
            $et = mktime(0, 0, - 1, date("m", $phpTime), date("d", $phpTime) + 1, date("Y", $phpTime));
            break;
    }
    // echo $st . "--" . $et;
    return listCalendarByRange($st, $et);
}

function updateCalendar($id, $st, $et)
{
    global $sqlcn;
    $ret = array();
    try {
        $sql = "update `jqcalendar` set" . " `starttime`='" . php2MySqlTime(js2PhpTime($st)) . "', " . " `endtime`='" . php2MySqlTime(js2PhpTime($et)) . "' " . "where `id`=" . $id;
        // echo $sql;
        
        if ($sqlcn->ExecuteSQL($sql) == false) {
            $ret['IsSuccess'] = false;
            $ret['Msg'] = mysql_error();
        } else {
            $ret['IsSuccess'] = true;
            $ret['Msg'] = 'Succefully';
        }
    } catch (Exception $e) {
        $ret['IsSuccess'] = false;
        $ret['Msg'] = $e->getMessage();
    }
    return $ret;
}

function updateDetailedCalendar($id, $st, $et, $sub, $ade, $dscr, $loc, $color, $tz)
{
    global $sqlcn;
    $ret = array();
    try {
        $sql = "update `jqcalendar` set" . " `starttime`='" . php2MySqlTime(js2PhpTime($st)) . "', " . " `endtime`='" . php2MySqlTime(js2PhpTime($et)) . "', " . " `subject`='" . mysqli_real_escape_string($sqlcn->idsqlconnection, $sub) . "', " . " `isalldayevent`='" . mysqli_real_escape_string($sqlcn->idsqlconnection, $ade) . "', " . " `description`='" . mysqli_real_escape_string($sqlcn->idsqlconnection, $dscr) . "', " . " `location`='" . mysqli_real_escape_string($sqlcn->idsqlconnection, $loc) . "', " . " `color`='" . mysqli_real_escape_string($sqlcn->idsqlconnection, $color) . "' " . "where `id`=" . $id;
        // echo $sql;
        if ($sqlcn->ExecuteSQL($sql) == false) {
            $ret['IsSuccess'] = false;
            $ret['Msg'] = mysql_error();
        } else {
            $ret['IsSuccess'] = true;
            $ret['Msg'] = 'Succefully';
        }
    } catch (Exception $e) {
        $ret['IsSuccess'] = false;
        $ret['Msg'] = $e->getMessage();
    }
    return $ret;
}

function removeCalendar($id)
{
    global $sqlcn;
    $ret = array();
    try {
        $sql = "delete from `jqcalendar` where `id`=" . $id;
        if ($sqlcn->ExecuteSQL($sql) == false) {
            $ret['IsSuccess'] = false;
            $ret['Msg'] = mysql_error();
        } else {
            $ret['IsSuccess'] = true;
            $ret['Msg'] = 'Succefully';
        }
    } catch (Exception $e) {
        $ret['IsSuccess'] = false;
        $ret['Msg'] = $e->getMessage();
    }
    return $ret;
}

header('Content-type:text/javascript;charset=UTF-8');
$method = $_GET["method"];
switch ($method) {
    case "add":
        $ret = addCalendar($_POST["CalendarStartTime"], $_POST["CalendarEndTime"], $_POST["CalendarTitle"], $_POST["IsAllDayEvent"]);
        break;
    case "list":
        $ret = listCalendar($_POST["showdate"], $_POST["viewtype"]);
        break;
    case "update":
        $ret = updateCalendar($_POST["calendarId"], $_POST["CalendarStartTime"], $_POST["CalendarEndTime"]);
        break;
    case "remove":
        $ret = removeCalendar($_POST["calendarId"]);
        break;
    case "adddetails":
        $st = $_POST["stpartdate"] . " " . $_POST["stparttime"];
        $et = $_POST["etpartdate"] . " " . $_POST["etparttime"];
        if (isset($_GET["id"])) {
            $ret = updateDetailedCalendar($_GET["id"], $st, $et, $_POST["Subject"], isset($_POST["IsAllDayEvent"]) ? 1 : 0, $_POST["Description"], $_POST["Location"], $_POST["colorvalue"], $_POST["timezone"]);
        } else {
            $ret = addDetailedCalendar($st, $et, $_POST["Subject"], isset($_POST["IsAllDayEvent"]) ? 1 : 0, $_POST["Description"], $_POST["Location"], $_POST["colorvalue"], $_POST["timezone"]);
        }
        break;
}
echo json_encode($ret);

?>