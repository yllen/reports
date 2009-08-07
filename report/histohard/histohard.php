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

// Original Author of file: Benoit Machiavello
// Purpose of file:
// ----------------------------------------------------------------------

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE=1;
$DBCONNECTION_REQUIRED=1; // Really a big SQL request

$NEEDED_ITEMS=array("search","computer","infocom","device");

define('GLPI_ROOT', '../../../..'); 
include (GLPI_ROOT . "/inc/includes.php"); 

includeLocales("histohard");

plugin_reports_checkRight("histohard","r");
checkSeveralRightsAnd(array(COMPUTER_TYPE=>"r"));
//plugin_reports_checkRight("computer","r");

commonHeader($LANG['plugin_reports']['histohard'][1],$_SERVER['PHP_SELF'],"utils","report");


echo "<div align='center'>";
echo "<table class='tab_cadre' cellpadding='5'>\n";
echo "<tr class='tab_bg_1' align='center'><th colspan='5'>" . $LANG['plugin_reports']['histohard'][1] . "</th></tr>\n";

echo "<tr class='tab_bg_1'><td>". $LANG['plugin_reports']['histohard'][2] . "</td>" .
		"<td>". $LANG["common"][34] . "</td>".
		"<td>". $LANG["help"][25] . "</td>".
		"<td>". $LANG["event"][18] . "</td>".
		"<td>". $LANG['plugin_reports']['histohard'][3] . "</td></tr>\n";
		
$sql = "SELECT glpi_history.date_mod AS dat, linked_action, device_type, device_internal_type, old_value, new_value, glpi_computers.ID AS cid, name, user_name FROM glpi_history  " . // FORCE INDEX (date_mod)
	" LEFT JOIN glpi_computers ON (glpi_history.FK_glpi_device=glpi_computers.ID)" .
	" WHERE glpi_history.date_mod > DATE_SUB(Now(), INTERVAL 21 DAY)".
	" AND device_type=" . COMPUTER_TYPE .
	" AND linked_action IN (" . HISTORY_CONNECT_DEVICE . 
	", " . HISTORY_DISCONNECT_DEVICE .
	", " . HISTORY_DELETE_DEVICE .
	", " . HISTORY_UPDATE_DEVICE .
	", " . HISTORY_ADD_DEVICE . ")" .
	" AND glpi_computers.FK_entities=" . $_SESSION["glpiactive_entity"] .
	" ORDER BY glpi_history.ID DESC LIMIT 0,100";
$result = $DB->query($sql);

$prev="";
$class="tab_bg_2";
while ($data = $DB->fetch_array($result)) {
	if ($prev == $data["dat"].$data["name"]) {
		echo "</td></tr><tr class='" . $prevclass ."' valign='top'><td></td><td></td><td></td><td>";
	}
	else {
		if (!empty($prev)) echo "</td></tr>\n";
		
		$prev = $data["dat"].$data["name"];
		echo "<tr class='" . $class . "' valign='top'><td>". convDateTime($data["dat"]) . "</td>" .
			"<td>". $data["user_name"] . "&nbsp;</td>".
			"<td><a href='". $_SESSION["glpiroot"] . "/" . $INFOFORM_PAGES[COMPUTER_TYPE] . 
			"?ID=" . $data["cid"] . "'>" . $data["name"] . "</a></td>".
			"<td>";
		$prevclass=$class;
		$class=($class=="tab_bg_2" ? "tab_bg_1" : "tab_bg_2");
	}
	$field="";
	if($data["linked_action"]){
	// Yes it is an internal device
		switch ($data["linked_action"]){
				case HISTORY_ADD_DEVICE :
					$field=getDictDeviceLabel($data["device_internal_type"]);
					$change = $LANG["devices"][25]."&nbsp;<strong>:</strong>&nbsp;\"".$data[ "new_value"]."\"";	
					break;

				case HISTORY_UPDATE_DEVICE :
					$field=getDictDeviceLabel($data["device_internal_type"]);
					$change = getDeviceSpecifityLabel($data["device_internal_type"])."&nbsp;:&nbsp;\"".$data[ "old_value"]."\"&nbsp;<strong>--></strong>&nbsp;\"".$data[ "new_value"]."\"";	
					break;

				case HISTORY_DELETE_DEVICE :
					$field=getDictDeviceLabel($data["device_internal_type"]);
					$change = $LANG["devices"][26]."&nbsp;<strong>:</strong>&nbsp;"."\"".$data["old_value"]."\"";	
					break;
				case HISTORY_DISCONNECT_DEVICE:
					$ci=new CommonItem();
					$ci->setType($data["device_internal_type"]);
					$field=$ci->getType();
					$change = $LANG["central"][6]."&nbsp;<strong>:</strong>&nbsp;"."\"".$data["old_value"]."\"";	
					break;	
				case HISTORY_CONNECT_DEVICE:
					$ci=new CommonItem();
					$ci->setType($data["device_internal_type"]);
					$field=$ci->getType();
					$change = $LANG["log"][55]."&nbsp;<strong>:</strong>&nbsp;"."\"".$data["new_value"]."\"";	
					break;	
		}//fin du switch
	}
	echo $field . "<td>" . $change;
}
if (!empty($prev)) echo "</td></tr>\n";
echo "</table><p>".$LANG['plugin_reports']['histohard'][4]."</p></div>\n";
	
commonFooter(); 
 
?>
