<?php
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php');
require_once(INCL_DIR.'user_functions.php');
dbconn();

$pkey = isset($_GET['passkey']) && strlen($_GET['passkey']) == 32 ? $_GET['passkey'] : '';
if(!empty($pkey)) {
	$q0 = mysql_query("SELECT * FROM users where passkey = ".sqlesc($pkey)) or sqlerr(__FILE__, __LINE__);
	if(mysql_num_rows($q0) == 0)
		die($lang['downlod_passkey']);
	else
		$CURUSER = mysql_fetch_assoc($q0);
}else
	loggedinorreturn();

  $lang =  array_merge( load_language('global'),load_language('download'));
  
 
  $id = isset($_GET['torrent']) ? intval($_GET['torrent']) : 0;

  if ( !is_valid_id($id) )
  stderr("{$lang['download_user_error']}", "{$lang['download_no_id']}");
  
  
  if($TBDEV['coins'])
  if($CURUSER['coins'] < 200)
  stderr("Error!","You do not have enough coins to download this torrent");
  coin(200, false);
  
  $res = @sql_query("SELECT name, owner, category, filename FROM torrents WHERE id = $id") or sqlerr(__FILE__, __LINE__);
  $row = mysql_fetch_assoc($res);
  
  $fn = "{$TBDEV['torrent_dir']}/$id.torrent";
  if (!$row || !is_file($fn) || !is_readable($fn))
   httperr();
   
  if ( happyHour( "check" ) && happyCheck( "checkid", $row["category"] ) ) {
  $multiplier = happyHour( "multiplier" );
  $time = time();
  happyLog( $CURUSER["id"], $id, $multiplier );
  @sql_query( "INSERT INTO happyhour (userid, torrentid, multiplier ) VALUES (" . sqlesc( $CURUSER["id"] ) . " , " . sqlesc( $id ) . ", " . sqlesc( $multiplier ) . ")" );
  }
   
  if (!($CURUSER["id"] == $row["owner"])) {
  if ($CURUSER["downloadpos"] == 0 || $CURUSER["downloadpos"] > 1 )
  stderr("Error","Your download rights have been disabled.");
  }

  @sql_query("UPDATE torrents SET hits = hits + 1 WHERE id = $id");
  /** free mod for TBDev 09 by pdq **/
  require_once(MODS_DIR.'freeslots_inc.php');
  /** end **/
  require_once(INCL_DIR.'benc.php');
  
  if (!isset($CURUSER['passkey']) || strlen($CURUSER['passkey']) != 32) 
  {
  $CURUSER['passkey'] = md5($CURUSER['username'].time().$CURUSER['passhash']);
  @sql_query("UPDATE users SET passkey='{$CURUSER['passkey']}' WHERE id={$CURUSER['id']}");
  }
  $dict = bdec_file($fn, filesize($fn));
  $dict['value']['announce']['value'] = "{$TBDEV['announce_urls'][0]}?passkey={$CURUSER['passkey']}";
  $dict['value']['announce']['string'] = strlen($dict['value']['announce']['value']).":".$dict['value']['announce']['value'];
  $dict['value']['announce']['strlen'] = strlen($dict['value']['announce']['string']);
  $dict['value']['created by']=bdec(benc_str( "".$CURUSER['username'].""));
  
	header('Content-Disposition: attachment; filename="['.$TBDEV['site_name'].']'.$row['filename'].'"');
  header("Content-Type: application/x-bittorrent"); 
	print(benc($dict));
	
?>