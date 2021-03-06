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
require_once INCL_DIR.'html_functions.php';
require_once INCL_DIR.'bbcode_functions.php';
require_once INCL_DIR.'page_verify.php';

global $CURUSER;
if (!mkglobal("id"))
	die();

$id = 0 + $id;
if (!$id)
	die();

/** who is modding by pdq **/
if ((isset($_GET['unedit']) && $_GET['unedit'] == 1) && $CURUSER['class'] >= UC_MODERATOR)
{
$modfile = 'cache/details/'.$id.'_moddin.txt';
if (file_exists($modfile))
unlink($modfile);
$returl = "details.php?id=$id";
if (isset($_POST["returnto"]))
	$returl .= "&returnto=" . urlencode($_POST["returnto"]);
header("Refresh: 0; url=$returl");
exit();
}

dbconn();

loggedinorreturn();

    $lang = array_merge( load_language('global'), load_language('edit') );
    $newpage = new page_verify(); 
    $newpage->create('teit');
    $res = sql_query("SELECT * FROM torrents WHERE id = $id");
    $row = mysql_fetch_assoc($res);
    if (!$row)
      stderr($lang['edit_user_error'], $lang['edit_no_torrent']);


    
    if (!isset($CURUSER) || ($CURUSER["id"] != $row["owner"] && $CURUSER["class"] < UC_STAFF)) 
    {
      stderr($lang['edit_user_error'], sprintf($lang['edit_no_permission'], urlencode($_SERVER['REQUEST_URI'])));
    }


    $HTMLOUT = '';
    
    if ($CURUSER['class'] >= UC_MODERATOR)
    {    
    $expire = 300; // 5 minutes
    $modfile = 'cache/details/'.$id.'_moddin.txt';
    if (file_exists($modfile) && filemtime($modfile) > (time() - $expire)) {
    $modcache = fopen($modfile, "r");
    $ismoddin = fread($modcache, filesize($modfile));
    fclose($modcache);
    $HTMLOUT .= '<h1><font size="+1"><font color="#FF0000">'.$ismoddin.'</font> is currently editing this torrent!</font></h1>';
    }
    else
    {
    $modder = $CURUSER['username'];
    $fp = fopen($modfile, "w") or die('Couldn\'t open file for writing!');
    fwrite($fp, $modder) or die('Couldn\'t write values to file!');
    fclose($fp);
    }
    }
    $ismodd = '<tr><td align=\'center\' class=\'colhead\' colspan=\'2\'><b>Edit Torrent</b> '.(($CURUSER['class'] > UC_UPLOADER)?'<small><a href="edit.php?id='.$id.'&amp;unedit=1">Click here</a> to add temp edit notification while you edit this torrent</small>':'').'</td></tr>';
    $HTMLOUT  .= "<form name='compose' method='post' action='takeedit.php' enctype='multipart/form-data'>
    <input type='hidden' name='id' value='$id' />";
    
    if (isset($_GET["returnto"]))
    $HTMLOUT  .= "<input type='hidden' name='returnto' value='" . htmlspecialchars($_GET["returnto"]) . "' />\n";
    $HTMLOUT  .=  "<table border='1' cellspacing='0' cellpadding='10'>\n";
    $HTMLOUT  .= $ismodd;
    $HTMLOUT  .= tr("{$lang['edit_imdb_url']}", "<input type='text' name='url' size='80' value='".$row["url"]."' />", 1);
    $HTMLOUT .= tr($lang['edit_poster'], "<input type='text' name='poster' size='80' value='" . htmlspecialchars($row["poster"]) . "' /><br />{$lang['edit_poster1']}\n", 1);
    $HTMLOUT  .= tr($lang['edit_torrent_name'], "<input type='text' name='name' value='" . htmlspecialchars($row["name"]) . "' size='80' />", 1);
    $HTMLOUT  .= tr($lang['edit_nfo'], "<input type='radio' name='nfoaction' value='keep' checked='checked' />{$lang['edit_keep_current']}<br />".
	"<input type='radio' name='nfoaction' value='update' />{$lang['edit_update']}<br /><input type='file' name='nfo' size='80' />", 1);
    if ((strpos($row["ori_descr"], "<") === false) || (strpos($row["ori_descr"], "&lt;") !== false))
    {
      $c = "";
    }
    else
    {
      $c = " checked";
    }
    
    $HTMLOUT  .= tr($lang['edit_description'], "". textbbcode("compose","descr","".htmlspecialchars($row['ori_descr'])."")."<br />({$lang['edit_tags']})", 1);

    $s = "<select name='type'>\n";

    $cats = genrelist();
    
    foreach ($cats as $subrow) 
    {
      $s .= "<option value='" . $subrow["id"] . "'";
      if ($subrow["id"] == $row["category"])
        $s .= " selected='selected'";
      $s .= ">" . htmlspecialchars($subrow["name"]) . "</option>\n";
    }

    $s .= "</select>\n";
    $HTMLOUT  .= tr($lang['edit_type'], $s, 1);
    $HTMLOUT  .= tr($lang['edit_visible'], "<input type='checkbox' name='visible'" . (($row["visible"] == "yes") ? " checked='checked'" : "" ) . " value='1' /> {$lang['edit_visible_mainpage']}<br /><table border='0' cellspacing='0' cellpadding='0' width='420'><tr><td class='embedded'>{$lang['edit_visible_info']}</td></tr></table>", 1);

    if ($CURUSER['class'] >= UC_STAFF)
    {
    $HTMLOUT  .= tr($lang['edit_banned'], "<input type='checkbox' name='banned'" . (($row["banned"] == "yes") ? " checked='checked'" : "" ) . " value='1' /> {$lang['edit_banned']}", 1);
    }
    if ($CURUSER['class'] >= UC_VIP)
    $HTMLOUT .= tr("Nuked", "<input type='radio' name='nuked'" . ($row["nuked"] == "yes" ? " checked='checked'" : "") . " value='yes' />Yes <input type='radio' name='nuked'" . ($row["nuked"] == "no" ? " checked='checked'" : "") . " value='no' />No",1);
    $HTMLOUT .= tr("Nuke Reason", "<input type='text' name='nukereason' value='" . htmlspecialchars($row["nukereason"]) . "' size='80' />", 1);

    if ($CURUSER['class'] >= UC_STAFF)
    {
      $HTMLOUT  .= tr("Free Leech", ($row['free'] != 0 ? 
	  "<input type='checkbox' name='fl' value='1' /> Remove Freeleech" : "
    <select name='free_length'>
    <option value='0'>------</option>
    <option value='42'>Free for 1 day</option>
    <option value='1'>Free for 1 week</option>
    <option value='2'>Free for 2 weeks</option>
    <option value='4'>Free for 4 weeks</option>
    <option value='8'>Free for 8 weeks</option>
    <option value='255'>Unlimited</option>
    </select>"), 1);
    }
    if ($row['free'] != 0) {
    	 $HTMLOUT  .= tr("Free Leech Duration", 
		 ($row['free'] != 1 ? "Until ".get_date($row['free'],'DATE')." 
		 (".mkprettytime($row['free'] - time())." to go)" : 'Unlimited'), 1);
    }
    // ===09 Allow Comments
    if ($CURUSER['class'] >= UC_MODERATOR && $CURUSER['class'] <= UC_SYSOP) {
    if ($row["allow_comments"] == "yes")
    $messc = "&nbsp;Comments are allowed for everyone on this torrent!";
    else
    $messc = "&nbsp;Only staff members are able to comment on this torrent!";
    $HTMLOUT.="<tr>
    <td align='right'><font color='red'>&nbsp;*&nbsp;</font><b>&nbsp;{$lang['edit_comment']}</b></td>
    <td>
    <select name='allow_comments'>
    <option value='".htmlspecialchars($row["allow_comments"])."'>".htmlspecialchars($row["allow_comments"])."</option>
    <option value='yes'>Yes</option><option value='no'>No</option></select>{$messc}</td></tr>\n";
    }
    // ===end
    if($CURUSER['class'] >= UC_STAFF)
    $HTMLOUT .= tr("Sticky", "<input type='checkbox' name='sticky'" . (($row["sticky"] == "yes") ? " checked='checked'" : "" ) . " value='yes' />Sticky this torrent !", 1);
    $HTMLOUT .= tr($lang['edit_anonymous'], "<input type='checkbox' name='anonymous'" . (($row["anonymous"] == "yes") ? " checked='checked'" : "" ) . " value='1' />{$lang['edit_anonymous1']}", 1);
    $HTMLOUT  .= "<tr><td colspan='2' align='center'><input type='submit' value='{$lang['edit_submit']}' class='btn' /> <input type='reset' value='{$lang['edit_revert']}' class='btn' /></td></tr>
    </table>
    </form>
    <br />
    <form method='post' action='delete.php'>
    <table border='1' cellspacing='0' cellpadding='5'>
    <tr>
      <td class='embedded' style='background-color: #F5F4EA;padding-bottom: 5px' colspan='2'><b>{$lang['edit_delete_torrent']}.</b> {$lang['edit_reason']}</td>
    </tr>
    <tr>
      <td><input name='reasontype' type='radio' value='1' />&nbsp;{$lang['edit_dead']} </td><td> {$lang['edit_peers']}</td>
    </tr>
    <tr>
      <td><input name='reasontype' type='radio' value='2' />&nbsp;{$lang['edit_dupe']}</td><td><input type='text' size='40' name='reason[]' /></td>
    </tr>
    <tr>
      <td><input name='reasontype' type='radio' value='3' />&nbsp;{$lang['edit_nuked']}</td><td><input type='text' size='40' name='reason[]' /></td>
    </tr>
    <tr>
      <td><input name='reasontype' type='radio' value='4' />&nbsp;{$lang['edit_rules']}</td><td><input type='text' size='40' name='reason[]' />({$lang['edit_req']})</td>
    </tr>
    <tr>
      <td><input name='reasontype' type='radio' value='5' checked='checked' />&nbsp;{$lang['edit_other']}</td><td><input type='text' size='40' name='reason[]' />({$lang['edit_req']})<input type='hidden' name='id' value='$id' /></td>
    </tr>";
    
    if (isset($_GET["returnto"]))
    {
      $HTMLOUT  .= "<input type='hidden' name='returnto' value='" . htmlspecialchars($_GET["returnto"]) . "' />\n";
		}
    
    $HTMLOUT  .= "<tr><td colspan='2' align='center'><input type='submit' value='{$lang['edit_delete']}' class='btn' /></td>
    </tr>
    </table>
    </form>";


//////////////////////////// HTML OUTPIT ////////////////////////////////
    print stdhead("{$lang['edit_stdhead']} '{$row["name"]}'") . $HTMLOUT . stdfoot();

?>