<?php
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
if ( ! defined( 'IN_TBDEV_ADMIN' ) )
{
	$HTMLOUT='';
	$HTMLOUT .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
		\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
		<html xmlns='http://www.w3.org/1999/xhtml'>
		<head>
		<title>Error!</title>
		</head>
		<body>
	<div style='font-size:33px;color:white;background-color:red;text-align:center;'>Incorrect access<br />You cannot access this file directly.</div>
	</body></html>";
	print $HTMLOUT;
	exit();
}
require_once(INCL_DIR.'user_functions.php');
require_once(INCL_DIR.'bbcode_functions.php');
require_once(INCL_DIR.'html_functions.php');

$HTMLOUT="";

if (!min_class(UC_SYSOP)) // or just simply: if (!min_class(UC_STAFF))
header( "Location: {$TBDEV['baseurl']}/index.php");

function mysql_fetch_all($query, $default_value = Array())
{
    $r = @mysql_query($query);
    $result = Array();
    if ($err = mysql_error())return $err;
    if (@mysql_num_rows($r))
        while ($row = mysql_fetch_array($r))$result[] = $row;
    if (count($result) == 0)
        return $default_value;
    return $result;
}

function get_user_name($userid){
	
  $res = mysql_query("SELECT `username` , `id` , `class`, `chatpost`, `leechwarn`, `warned`, `pirate`, `king`, `donor`, `enabled` FROM `users` WHERE `id` = $userid LIMIT 1")  or sqlerr(__FILE__, __LINE__);
  $username = mysql_fetch_assoc($res);
  
  return $username;
}

$scheduled_events = mysql_fetch_all("SELECT * from `events` ORDER BY `startTime` DESC LIMIT 10;", array());

if (is_array($scheduled_events)){
foreach ($scheduled_events as $scheduled_event)
{
if (is_array($scheduled_event) && array_key_exists('startTime', $scheduled_event) &&
array_key_exists('endTime', $scheduled_event))
{
$startTime = 0;
$endTime = 0;
$overlayText = "";
$displayDates = true;

$startTime = $scheduled_event['startTime'];

$endTime = $scheduled_event['endTime'];

if (time() < $endTime && time() > $startTime)
{

if (array_key_exists('overlayText', $scheduled_event))
$overlayText = $scheduled_event['overlayText'];
if (!is_string($overlayText))
$overlayText = "";

if (array_key_exists('displayDates', $scheduled_event))
$displayDates = (bool)(int)$scheduled_event['displayDates'];
if (!is_bool($displayDates))
$displayDates = false;

if (array_key_exists('freeleechEnabled', $scheduled_event)) {
$freeleechEnabled = $scheduled_event['freeleechEnabled'];
}
if (!is_bool($freeleechEnabled)){
$freeleechEnabled = false;
}


if (array_key_exists('duploadEnabled', $scheduled_event)){
$duploadEnabled = $scheduled_event['duploadEnabled'];
}
if (!is_bool($duploadEnabled)) {
$duploadEnabled = false;
}

if (array_key_exists('hdownEnabled', $scheduled_event)) {
$hdownEnabled = $scheduled_event['hdownEnabled'];
}
if (!is_bool($hdownEnabled)) {
$hdownEnabled = false;
}

if ($freeleechEnabled){
$freeleechEnabled = true;
}
if ($duploadEnabled){
$duploadEnabled = true;
}
if ($hdownEnabled){
$hdownEnabled = true;
}

if ($displayDates) {
$overlay_text = "<span style=\"font-size: 90%\">$overlayText</span><br/><span style=\"font-size: 60%\">" .
get_date($startTime, 'DATE') . " - " . get_date($endTime, 'DATE') . "</span>\n";
} else {
$overlay_text = "<span style=\"font-size: 90%\">$overlayText</span>\n";
}
}
}
}
}

$HTMLOUT .="
<script type='text/javascript'>
/*<![CDATA[*/
function checkAllGood(event){
var result = confirm(\"Are you sure you want to remove '\" + event + \"' Event ?\");
if(result)
return true;
else
return false;
}
/*]]>*/
</script>";

if(!is_array($scheduled_events)){
$_POST = (isset($_POST) ? $_POST : '');
$HTMLOUT .="Error: Events not loaded.";
}else{
foreach($_POST as $key => $value){
if(gettype($pos = strpos($key, "_"))!= 'boolean'){
$id = (int)substr($key, $pos + 1);
if(gettype(strpos($key, "removeEvent_"))!= 'boolean'){
$sql = "DELETE FROM `events` WHERE `id` = $id LIMIT 1;";
$res = mysql_query($sql);
if(mysql_error()!=0)
$HTMLOUT .="<p>Error Deleting Event: " . mysql_error() . "<br /> Click <a class='altlink' href='{$TBDEV['baseurl']}/events.php'>Here</a> to go back.<br /></p>\n";
else{
if(mysql_affected_rows()==0)
$HTMLOUT .="<p>Error Deleting Event: " . mysql_error() . "<br /> Click <a class='altlink' href='{$TBDEV['baseurl']}/events.php'>Here</a> to go back.<br /></p>\n";
else{
$HTMLOUT .="<p>Deleted.</p>\n";
header("Refresh: 2; url=admin.php?action=events");
}
}
}
elseif(gettype(strpos($key, "saveEvent_"))!= 'boolean'){
$text = "";
$start = 0;
$end = 0;
$showDates = 0;


if(array_key_exists('userid', $_POST))
$userid = $_POST['userid'];
if(array_key_exists('editText', $_POST))
$text = $_POST['editText'];
if(array_key_exists('editStartTime', $_POST))
$start = strtotime(trim($_POST['editStartTime']));
if(array_key_exists('editEndTime', $_POST))
$end = strtotime(trim($_POST['editEndTime']));
 
    if(isset($_POST["editFreeleech"])){
	  $freeleech = 1;
	  }
		if(isset($_POST['editFreeleech']) == ''){
		$freeleech = 0;
		}
		
	  if(isset($_POST["editDoubleupload"])){
	  $doubleupload = 1;
	  }
		if(isset($_POST['editDoubleupload']) == ''){
		$doubleupload = 0;
		}
 
    if(isset($_POST["editHalfdownload"])){
	  $halfdownload = 1;
	  }
		if(isset($_POST['editHalfdownload']) == ''){
		$halfdownload = 0;
		}
 

if (array_key_exists('editShowDates', $_POST))
$showDates = 1;
if($id==-1)
$sql = "INSERT INTO `events`(`overlayText`, `startTime`, `endTime`, `displayDates`, `freeleechEnabled`, `duploadEnabled`, `hdownEnabled`, `userid`) VALUES ('$text', $start, $end, $showDates, $freeleech, $doubleupload, $halfdownload, $userid);";
else
$sql = "UPDATE `events` SET `overlayText` = '$text',`startTime` = $start, `endTime` = $end, `displayDates` = $showDates, `freeleechEnabled` = $freeleech, `duploadEnabled` = $doubleupload, `hdownEnabled` = $halfdownload, `userid` = $userid  WHERE `id` = $id;";

$res = mysql_query($sql);
if(mysql_error()!=0)
$HTMLOUT .="<p>Error Saving Event: " . mysql_error() . "<br /> Click <a class='altlink' href='{$TBDEV['baseurl']}/events.php'>Here</a> to go back.<br /></p>\n";
else{
if(mysql_affected_rows()==0)
$HTMLOUT .="<p>Possible Error Saving (No Changes)<br /> Click <a class='altlink' href='{$TBDEV['baseurl']}/events.php'>Here</a> to go back.<br /></p>\n";
else{
$HTMLOUT .="<p>Saved.</p>\n";
header("Refresh: 2; url=admin.php?action=events");
}
}
}
}
}

$HTMLOUT .="<p><strong> Events Schedular </strong> (eZERO) - <strong> <font color='red'>BETA</font> </strong> </p>
<form action='' method='post'>
<table width='80%' cellpadding='6'>
<tr><th>User</th><th>Text</th><th>Start</th><th>End</th><th>Freeleech?</th><th>DUpload?</th><th>halfdownload?</th><th>Show Dates?</th><th>&nbsp;</th></tr>";

foreach($scheduled_events as $scheduled_event){
$id = $scheduled_event['id'];
$username = get_user_name($scheduled_event['userid']);
$text = $scheduled_event['overlayText'];
$start = get_date((int)$scheduled_event['startTime'], 'DATE');
$end = get_date((int)$scheduled_event['endTime'], 'DATE');
$freeleech = (bool)(int)$scheduled_event['freeleechEnabled'];
$doubleUpload = (bool)(int)$scheduled_event['duploadEnabled'];
$halfdownload = (bool)(int)$scheduled_event['hdownEnabled'];

if($freeleech){
$freeleech = "<img src=\"{$TBDEV['pic_base_url']}on.gif\" alt=\"Freeleech Enabled\" title=\"Enabled\" />";
}else{
$freeleech = "<img src=\"{$TBDEV['pic_base_url']}off.gif\" alt=\"Freeleech Disabled\" title=\"Disabled\" />";
}
if($doubleUpload){
$doubleUpload = "<img src=\"{$TBDEV['pic_base_url']}on.gif\" alt=\"Double Upload Enabled\" title=\"Enabled\" />";
}else{
$doubleUpload = "<img src=\"{$TBDEV['pic_base_url']}off.gif\" alt=\"Double Upload Disabled\" title=\"Disabled\" />";
}

if($halfdownload){
$halfdownload = "<img src=\"{$TBDEV['pic_base_url']}on.gif\" alt=\"Halfdownload Enabled\" title=\"Enabled\" />";
}else{
$halfdownload = "<img src=\"{$TBDEV['pic_base_url']}off.gif\" alt=\"Halfdownload Disabled\" title=\"Disabled\" />";
}
$showdates = (bool)(int)$scheduled_event['displayDates'];
if($showdates){
$showdates = "<img src=\"{$TBDEV['pic_base_url']}on.gif\" alt=\"Showing of Dates Enabled\" title=\"Enabled\" />";
}else{
$showdates = "<img src=\"{$TBDEV['pic_base_url']}off.gif\" alt=\"Showing of Dates Disabled\" title=\"Disabled\" />";
}
$HTMLOUT .="<tr><td align=\"center\">".format_username($username)."</td><td align=\"center\">{$text}</td><td align=\"center\">{$start}</td><td align=\"center\">{$end}</td><td align=\"center\">{$freeleech}</td><td align=\"center\">{$doubleUpload}</td><td align=\"center\">{$halfdownload}</td><td align=\"center\">{$showdates}</td><td align=\"center\"><input type=\"submit\" name=\"editEvent_$id\" value=\"Edit\" /> <input type=\"submit\" onclick=\"return checkAllGood('$text')\" name=\"removeEvent_$id\" value=\"Remove\" /></td></tr>";
}
$HTMLOUT .="<tr><td colspan='9' align='right'><input type='submit' name='editEvent_-1' value='Add Event' /></td></tr></table>";

foreach($_POST as $key => $value){
if(gettype($pos = strpos($key, "_"))!= 'boolean'){
$id = (int)substr($key, $pos + 1);
if(gettype(strpos($key, "editEvent_"))!= 'boolean'){
if($id==-1){
$HTMLOUT .="<table>
<tr><th align='right'>Userid</th><td><input type='text' name='userid' value='{$CURUSER["id"]}' /></td></tr>
<tr><th align='right'>Text</th><td><input type='text' name='editText' /></td></tr>
<tr><th align='right'>Start Time</th><td><input type='text' name='editStartTime' /></td></tr>
<tr><th align='right'>End Time</th><td><input type='text' name='editEndTime' /></td></tr>
<tr><th align='right'>Freeleech</th><td><input type='checkbox' name='editFreeleech' /></td></tr>
<tr><th align='right'>DoubleUpload</th><td><input type='checkbox' name='editDoubleupload' /></td></tr>
<tr><th align='right'>halfdownload</th><td><input type='checkbox' name='editHalfdownload' /></td></tr>
<tr><th align='right'>Show Dates</th><td><input type='checkbox' name='editShowDates' /></td></tr>
<tr><td colspan='2' align='center'><input type='submit' name='saveEvent_-1' value='Save Changes' /></td></tr>
</table>";
}
else
foreach($scheduled_events as $scheduled_event){
if($id == $scheduled_event['id']){
$text = $scheduled_event['overlayText'];
$start = get_date((int)$scheduled_event['startTime'], 'DATE');
$end = get_date((int)$scheduled_event['endTime'], 'DATE');
$freeleech = (bool)(int)$scheduled_event['freeleechEnabled'];
if($freeleech){
$freeleech = "checked=\"checked\"";
}else{
$freeleech = "";
}

$doubleupload = (bool)(int)$scheduled_event['duploadEnabled'];
if($doubleupload){
$doubleupload = "checked=\"checked\"";
}else{
$doubleupload = "";
}

$halfdownload = (bool)(int)$scheduled_event['hdownEnabled'];
if($halfdownload){
$halfdownload = "checked=\"checked\"";
}else{
$halfdownload = "";
}

$showdates = (bool)(int)$scheduled_event['displayDates'];
if($showdates){
$showdates = "checked=\"checked\"";
}else{
$showdates = "";
}

$HTMLOUT .="<table>
<tr><th align='right'>Userid</th><td><input type='text' name='userid' value='{$CURUSER["id"]}' /></td></tr>
<tr><th align='right'>Text</th><td><input type='text' name='editText' value='{$text}' /></td></tr>
<tr><th align='right'>Start Time</th><td><input type='text' name='editStartTime' value='{$start}' /></td></tr>
<tr><th align='right'>End Time</th><td><input type='text' name='editEndTime' value='{$end}' /></td></tr>
<tr><th align='right'>Freeleech</th><td><input type='checkbox' name='editFreeleech' value='{$freeleech}' /></td></tr>
<tr><th align='right'>DoubleUpload</th><td><input type='checkbox' name='editDoubleupload' value='{$doubleUpload}' /></td></tr>
<tr><th align='right'>halfdownload</th><td><input type='checkbox' name='editHalfdownload' value='{$halfdownload}' /></td></tr>
<tr><th align='right'>Show Dates</th><td><input type='checkbox' name='editShowDates' value='{$showdates}' /></td></tr>
<tr><td colspan='2' align='center'><input type='submit' name='saveEvent_{$id}' value='Save Changes' /></td></tr>
</table>";
break;
}
}
}
}
}
$HTMLOUT .="</form>";
}
print stdhead('Events') . $HTMLOUT . stdfoot();
die;
?>