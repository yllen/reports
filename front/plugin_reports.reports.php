<?php


/*
   ----------------------------------------------------------------------
   GLPI - Gestionnaire Libre de Parc Informatique
   Copyright (C) 2003-2008 by the INDEPNET Development Team.

   http://indepnet.net/   http://glpi-project.org/
   ----------------------------------------------------------------------

   LICENSE

   This file is part of GLPI.

   GLPI is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 2 of the License, or
   (at your option) any later version.

   GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with GLPI; if not, write to the Free Software
   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
   ------------------------------------------------------------------------
 */

// Original Author of file: Balpe Dévi
// Purpose of file:
// ----------------------------------------------------------------------

$NEEDED_ITEMS=array("profile");

if(!defined('GLPI_ROOT')){
	define('GLPI_ROOT', '../../..'); 
}
include_once (GLPI_ROOT . "/inc/includes.php");
checkRight("profile","r");

usePlugin('reports',true);

commonHeader($LANG['plugin_reports']['config'][1], $_SERVER["PHP_SELF"],"config","plugins");

$tab = plugin_reports_updatePluginRights("../report");

if(!isset($_POST["report"])) $report='';	
else $report=$_POST["report"];

if (isset($_POST["delete"])){
	checkRight("profile","w");

	$DB->query("UPDATE glpi_plugin_reports_profiles SET $report=NULL");
}
else  if (isset($_POST["update"])){
	checkRight("profile","w");
	
	$prof = new ReportProfile();
	foreach ($_POST as $key => $value) if (is_numeric($key))
		$prof->update(array("ID"=>$key, $report=>$value));
}

echo "<div align='center'><form method='post' action=\"".$_SERVER["PHP_SELF"]."\">";
echo "<table class='tab_cadre' cellpadding='5'><tr><th colspan='2'><a href='plugin_reports.config.form.php'>";
echo $LANG['plugin_reports']['config'][1]."</a><br />&nbsp;<br />" . $LANG['plugin_reports']['config'][8] . "</th></tr>\n";

echo "<tr class='tab_bg_1'><td>" . $LANG['plugin_reports']['config'][10] . "&nbsp;: ";
$query="SELECT ID, name FROM glpi_profiles ORDER BY name";
$result=$DB->query($query);

echo "<select name='report'>";
foreach($tab as $key => $value) {
	echo "<option value='".$key."' ".($report==$key?"selected":"").">".$LANG['plugin_reports'][$key][1]."</option>";
}
echo "</select>";
echo "<td><input type='submit' value=\"".$LANG["buttons"][2]."\" class='submit' ></td></tr>";
echo "</table></form></div>";

if ($report){
		echo "<div align='center'>";
		echo "<form action='".$_SERVER['PHP_SELF']."' method='post'>\n";
		echo "<table class='tab_cadre' cellpadding='5'>\n"; 
		echo "<tr><th colspan='2'>".$LANG['plugin_reports']['config'][9].": </th></tr>\n";

		$query="SELECT ID,profile,$report FROM glpi_plugin_reports_profiles";
		$result=$DB->query($query);
		while ($data=$DB->fetch_assoc($result)){
				echo "<tr class='tab_bg_1'><td>" . $data['profile'] . "&nbsp: </td><td>";
				dropdownNoneReadWrite($data['ID'],$data[$report],1,1,0);
				echo "</td></tr>\n";
		}
		
		if (haveRight("profile","w")){
			echo "<tr class='tab_bg_1'><td colspan='2' align='center'>";
			echo "<input type='hidden' name='report' value=$report>";
			echo "<input type='submit' name='update' value=\"".$LANG["buttons"][7]."\" class='submit'>&nbsp;";
			echo "<input type='submit' name='delete' value=\"".$LANG["buttons"][6]."\" class='submit'>";
			echo "</td></tr>\n";
		}

		echo "</table></form></div>\n";	

}

commonFooter();
?>

