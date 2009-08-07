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

// Original Author of file: Remi Collet
// Purpose of file: Handle configuration for "doublons" report
// ----------------------------------------------------------------------

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE=1;
$DBCONNECTION_REQUIRED=0;

$NEEDED_ITEMS=array("search","computer","infocom");

define('GLPI_ROOT', '../../../..'); 
include (GLPI_ROOT . "/inc/includes.php"); 

includeLocales("doublons");

plugin_reports_checkRight("doublons","r");
checkSeveralRightsAnd(array(COMPUTER_TYPE=>"r"));

commonHeader($LANG['plugin_reports']['doublons'][1],$_SERVER['PHP_SELF'],"utils","report");

$crits = array(
	0=>"---", 
	1=>$LANG["common"][16],			// Name
	2=>$LANG["common"][22]." + ".$LANG["common"][19], 		// Model + Serial
	3=>$LANG["common"][16]." + ".$LANG["common"][22]." + ".$LANG["common"][19], // Name + Model + Serial
	4=>$LANG["device_iface"][2], 	// Mac Address
	5=>$LANG["networking"][14],		// IP Address
	6=>$LANG['common'][20]			// Otherserial
	);

if (isset($_GET["crit"]))
	$_POST = $_GET["crit"];
$crit = (isset($_POST["crit"]) ? $_POST["crit"] : 0); 

// ---------- Form ------------
echo "<div align='center'>";
echo "<form action='".$_SERVER["PHP_SELF"]."' method='post'>";
echo "<table class='tab_cadre' cellpadding='5'>\n";
echo "<tr class='tab_bg_1' align='center'><th colspan='3'>" . $LANG['plugin_reports']['doublons'][1] . "</th></tr>\n";

if (haveRight("config","r")) // Check only read as we probably use the replicate (no 'w' in this case)
{
	echo "<tr class='tab_bg_3' align='center'><td colspan='".($crit>0?'3':'2')."'><a href='./doublons.config.php'>" . $LANG['plugin_reports']['config'][11] . "</a></td></tr>\n";
}
echo "<tr class='tab_bg_1'><td align='right'>" . $LANG["rulesengine"][6] . "&nbsp;:&nbsp;</td><td>";
echo "<select name='crit'>";
foreach ($crits as $key => $val) {
	echo "<option value='$key'" . ($crit==$key ? "selected" : "") . ">$val</option>";	
}
echo "</select></td>";
if ($crit>0)
{
	echo "<td>";
	//Add parameters to uri to be saved as bookmarks
	$_SERVER["REQUEST_URI"] = buildBookmarkUrl($_SERVER["REQUEST_URI"],$crit);
	showSaveBookmarkButton(BOOKMARK_SEARCH,COMPUTER_TYPE);
	echo "</td>";
}
echo"</tr>\n";

echo "<tr class='tab_bg_1' align='center'><td colspan='".($crit>0?'3':'2')."'><input type='submit' value='Valider' class='submit' />";
echo "</td></tr>\n";
echo "</table>\n</form></div>\n";


if ($crit==5) { // Search Duplicate IP Address - From glpi_networking_ports
	
	$IPBlacklist="AA.ifaddr!='' AND AA.ifaddr!='0.0.0.0'";
	if (TableExists("glpi_plugin_reports_doublons_backlist")) {
		$res=$DB->query("SELECT addr FROM glpi_plugin_reports_doublons_backlist WHERE Type=2");
		while ($data = $DB->fetch_array($res)) {
			if (strpos($data["addr"], '%')){
				$IPBlacklist .= "  AND AA.ifaddr NOT LIKE '".addslashes($data["addr"])."'";
			} else {
				$IPBlacklist .= " AND AA.ifaddr!='".addslashes($data["addr"])."'";			
			}
		}
	}
	
	$Sql = "SELECT A.ID AS AID, A.name AS Aname, A.serial AS Aserial, A.model AS Amodel, A.FK_glpi_enterprise AS Amanu, AA.ifaddr AS Aaddr, A.FK_entities AS entity, A.otherserial as Aotherserial, ".
				  "B.ID AS BID, B.name AS Bname, B.serial AS Bserial, B.model AS Bmodel, B.FK_glpi_enterprise AS Bmanu, BB.ifaddr AS Baddr, B.otherserial as Botherserial " .
		"FROM glpi_computers A, glpi_computers B, glpi_networking_ports AA, glpi_networking_ports BB" . 
		getEntitiesRestrictRequest(" WHERE ", "A", "FK_entities") .
		" AND AA.device_type=".COMPUTER_TYPE." AND AA.on_device=A.ID " .
		" AND BB.device_type=".COMPUTER_TYPE." AND BB.on_device=B.ID " .
		" AND AA.ifaddr=BB.ifaddr AND ($IPBlacklist)" .
		" AND B.ID>A.ID AND A.FK_entities=B.FK_entities " .
		" AND A.is_template=0 AND B.is_template=0 AND A.deleted=0 AND B.deleted=0"; 

	$col=$LANG["networking"][14];		

} else if ($crit==4) { // Search Duplicate Mac Address - From glpi_computer_device
	
	$MacBlacklist="''";
	if (TableExists("glpi_plugin_reports_doublons_backlist")) {
		$res=$DB->query("SELECT addr FROM glpi_plugin_reports_doublons_backlist WHERE Type=1");
		while ($data = $DB->fetch_array($res)) {
			$MacBlacklist .= ",'".addslashes($data["addr"])."'";
		}
	}
	else {
		$MacBlacklist .= ",'44:45:53:54:42:00','BA:D0:BE:EF:FA:CE', '00:53:45:00:00:00', '80:00:60:0F:E8:00'";	
	}
	$Sql = "SELECT A.ID AS AID, A.name AS Aname, A.serial AS Aserial, A.model AS Amodel, A.FK_glpi_enterprise AS Amanu, AA.specificity AS Aaddr, A.FK_entities AS entity, A.otherserial as Aotherserial,".
				  "B.ID AS BID, B.name AS Bname, B.serial AS Bserial, B.model AS Bmodel, B.FK_glpi_enterprise AS Bmanu, BB.specificity AS Baddr, B.otherserial as Botherserial " .
		"FROM glpi_computers A, glpi_computers B, glpi_computer_device AA, glpi_computer_device BB" . 
		getEntitiesRestrictRequest(" WHERE ", "A", "FK_entities") .
		" AND AA.device_type=".NETWORK_DEVICE." AND AA.FK_computers=A.ID " .
		" AND BB.device_type=".NETWORK_DEVICE." AND BB.FK_computers=B.ID " .
		" AND AA.specificity=BB.specificity AND AA.specificity NOT IN ($MacBlacklist)" .
		" AND B.ID>A.ID AND A.FK_entities=B.FK_entities " .
		" AND A.is_template=0 AND B.is_template=0 AND A.deleted=0 AND B.deleted=0"; 

	$col=$LANG["networking"][15];		

} else if ($crit>0) { // Search Duplicate Name and/ord Serial or Otherserial - From glpi_computers

	$SerialBlacklist="''";
	if (TableExists("glpi_plugin_reports_doublons_backlist")) {
		$res=$DB->query("SELECT addr FROM glpi_plugin_reports_doublons_backlist WHERE Type=3");
		while ($data = $DB->fetch_array($res)) {
			$SerialBlacklist .= ",'".addslashes($data["addr"])."'";
		}
	}
	$Sql = "SELECT A.ID AS AID, A.name AS Aname, A.serial AS Aserial, A.model AS Amodel, A.FK_glpi_enterprise AS Amanu, A.FK_entities AS entity, A.otherserial AS Aotherserial, ".
				  "B.ID AS BID, B.name AS Bname, B.serial AS Bserial, B.model AS Bmodel, B.FK_glpi_enterprise AS Bmanu, B.otherserial AS Botherserial " .
		"FROM glpi_computers A, glpi_computers B " . getEntitiesRestrictRequest(" WHERE ", "A", "FK_entities") .
		" AND B.ID>A.ID AND A.FK_entities=B.FK_entities " .
		" AND A.is_template=0 AND B.is_template=0 AND A.deleted=0 AND B.deleted=0"; 

	if ($crit == 6) {
		$Sql .= " AND A.otherserial!='' AND A.otherserial=B.otherserial";
	}  
	else
	{
		if ($crit & 1) {
			$Sql .= " AND A.name!='' AND A.name=B.name";
		}  
		if ($crit & 2) {
			$Sql .= " AND A.serial NOT IN ($SerialBlacklist) AND A.serial=B.serial AND A.model=B.model";
		}
	}

	$col="";  
}	

if ($crit>0) { // Display result
	
	echo "<div align='center'><table class='tab_cadrehov' cellpadding='5'>" .
		 "<tr><th colspan='". ($col ? 7 : 6) ."'>" . $LANG['plugin_reports']['doublons'][2] . "</th>" .
		 "<th colspan='". ($col ? 7 : 6) ."'>" . $LANG['plugin_reports']['doublons'][3] . "</th></tr>\n" .
		 "<tr><th>" . $LANG["common"][2] . "</th>" .
		 "<th>" . $LANG["common"][16] . "</th>" .
		 "<th>" . $LANG["common"][5] . "</th>" .
		 "<th>" . $LANG["common"][22] . "</th>" .
		 "<th>" . $LANG["common"][19] . "</th>" .
		 "<th>".$LANG['common'][20]."</th>";
	if ($col) echo "<th>$col</th>";
	echo "<th>" . $LANG["common"][2] . "</th>" .
		 "<th>" . $LANG["common"][16] . "</th>" .
		 "<th>" . $LANG["common"][5] . "</th>" .
		 "<th>" . $LANG["common"][22] . "</th>" .
		 "<th>" . $LANG["common"][19] . "</th>".
		 "<th>".$LANG['common'][20]."</th>";
	if ($col) echo "<th>$col</th>";
	echo "</tr>\n";

	$result = $DB->query($Sql);
	for ($prev=-1, $i=0 ; $data = $DB->fetch_array($result) ; $i++) {
		if ($prev != $data["entity"]) {
			$prev = $data["entity"];
			echo "<tr class='tab_bg_4'><td align='center' colspan='". ($col ? 14 : 12) ."'>".
				getDropdownName("glpi_entities", $prev) . "</td></tr>\n"; 
		}
		echo "<tr class='tab_bg_2'>" .
			 "<td><a href='../../../../" .$INFOFORM_PAGES[COMPUTER_TYPE] . "?ID=".$data["AID"]."'>".$data["AID"]."</a></td>" .
			 "<td>".$data["Aname"]."</td><td>".getDropdownName("glpi_dropdown_manufacturer",$data["Amanu"])."</td>".
			 "<td>".getDropdownName("glpi_dropdown_model",$data["Amodel"])."</td><td>".$data["Aserial"]."</td>".
			 "<td>".$data["Aotherserial"]."</td>";
		if ($col) echo "<td>" .$data["Aaddr"]. "</td>";

		echo "<td><a href='../../../../" .$INFOFORM_PAGES[COMPUTER_TYPE] . "?ID=".$data["BID"]."'>".$data["BID"]."</a></td>" .
			 "<td>".$data["Bname"]."</td><td>".getDropdownName("glpi_dropdown_manufacturer",$data["Bmanu"])."</td>".
			 "<td>".getDropdownName("glpi_dropdown_model",$data["Bmodel"])."</td><td>".$data["Bserial"]."</td>".
			 "<td>".$data["Botherserial"]."</td>";
		if ($col) echo "<td>" .$data["Aaddr"]. "</td>";

		echo "</tr>\n";
	}
	if ($i) {
		echo "<tr class='tab_bg_4'><td align='center' colspan='". ($col ? 14 : 12) ."'>" . $LANG['plugin_reports']['doublons'][1] . " : $i</td></tr>\n";
	}
	echo "</table></div>";
}
commonFooter(); 
 
 
function buildBookmarkUrl($url,$crit)
{
	 return $url."?crit=".$crit;
}
 
?>