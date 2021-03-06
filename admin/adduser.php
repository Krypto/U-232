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
require_once(INCL_DIR.'password_functions.php');

$lang = array_merge( $lang, load_language('ad_adduser') );
/** new way **/
if (!min_class(UC_ADMINISTRATOR)) // or just simply: if (!min_class(UC_STAFF))
header( "Location: {$TBDEV['baseurl']}/index.php");

      
    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
      if ($_POST["username"] == "" || $_POST["password"] == "" || $_POST["email"] == "")
        stderr("{$lang['stderr_error']}", "{$lang['text_missing']}");
      if ($_POST["password"] != $_POST["password2"])
        stderr("{$lang['stderr_error']}", "{$lang['text_passwd']}");
      if (!validemail($_POST['email']))
        stderr("{$lang['stderr_error']}", "{$lang['text_email']}");
      
      $username = sqlesc($_POST["username"]);
      $password = $_POST["password"];
      $email = sqlesc($_POST["email"]);
      $secret = mksecret();
      $passhash = sqlesc( make_passhash( $secret, md5($password) ) );
      $secret = sqlesc($secret);
      $time_now = time();
      
      @mysql_query("INSERT INTO users (added, last_access, secret, username, passhash, status, email) VALUES($time_now, $time_now, $secret, $username, $passhash, 'confirmed', $email)") or sqlerr(__FILE__, __LINE__);
      $res = @mysql_query("SELECT id FROM users WHERE username=$username");
      $arr = mysql_fetch_row($res);
      if (!$arr)
        stderr("{$lang['stderr_error']}", "{$lang['text_username']}");
      header("Location: {$TBDEV['baseurl']}/userdetails.php?id=$arr[0]");
      die;
    }
    

    $HTMLOUT = '';
    
    $HTMLOUT .= "<h1>{$lang['text_adduser']}</h1>
    <br />
    <form method='post' action='admin.php?action=adduser'>
    <table border='1' cellspacing='0' cellpadding='5'>
    <tr><td class='rowhead'>{$lang['table_username']}</td><td><input type='text' name='username' size='40' /></td></tr>
    <tr><td class='rowhead'>{$lang['table_password']}</td><td><input type='password' name='password' size='40' /></td></tr>
    <tr><td class='rowhead'>{$lang['table_repasswd']}</td><td><input type='password' name='password2' size='40' /></td></tr>
    <tr><td class='rowhead'>{$lang['table_email']}</td><td><input type='text' name='email' size='40' /></td></tr>
    <tr><td colspan='2' align='center'><input type='submit' value='{$lang['btn_okay']}' class='btn' /></td></tr>
    </table>
    </form>";
    
    print stdhead("{$lang['stdhead_adduser']}") . $HTMLOUT . stdfoot(); 
?>