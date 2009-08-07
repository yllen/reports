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

// ----------------------------------------------------------------------
// Original Author of file: Dévi Balpe
// Purpose of file:
// ----------------------------------------------------------------------

class ReportProfile extends CommonDBTM {
	
	function __construct()
	{
		$this->table="glpi_plugin_reports_profiles";
    	$this->type=-1;
	}

	
	//if profile deleted
	function cleanProfiles($ID) {
	
		global $DB;
		$query = "DELETE FROM glpi_plugin_reports_profiles WHERE ID='$ID' ";
		$DB->query($query);
	}

	function showForm($target,$ID){
		global $LANG,$DB;

		if (!haveRight("profile","r")) return false;
		
		$canedit=haveRight("profile","w");

		if ($ID){
			$this->getFromDB($ID);
		} 
		else {
			$this->getEmpty();
		}
		
		echo "<form action='".$target."' method='post'>";
		echo "<table class='tab_cadre_fixe' cellpadding='5'>"; 
		echo "<tr><th colspan='3' align='center'><strong>".$LANG['plugin_reports']['config'][4]." ".$this->fields["profile"]."</strong></th></tr>";
			
		$tab = searchReport(GLPI_ROOT."/plugins/reports/report");
		
		foreach($tab as $key => $value)
		{
			echo "<tr class='tab_bg_1'>";
			if (strpos($key,'stat') === false)
				echo "<td>".$LANG['Menu'][6]."</td>";
			else
				echo "<td>".$LANG['Menu'][13]."</td>";	
				
			echo "<td>".$LANG['plugin_reports'][$key][1]." :</td><td>";
			dropdownNoneReadWrite($key,(isset($this->fields[$key])?$this->fields[$key]:''),1,1,0);
			echo "</td></tr>";	
		}

		if ($canedit){
			echo "<tr class='tab_bg_1'>";
			echo "<td  align='center' colspan='3'>";
			echo "<input type='hidden' name='ID' value=$ID>";
			echo "<input type='submit' name='update_user_profile' value=\"".$LANG["buttons"][7]."\" class='submit'>";
			echo "</td></tr>\n";
		}
		echo "</table></form>";
	}
	
	function updateRights($ID,$rights)
	{
		global $DB;

		// Add missing profiles
		$DB->query("INSERT INTO `glpi_plugin_reports_profiles` ( `ID`, `profile`) ".
			"SELECT ID,name FROM `glpi_profiles` " .
			"WHERE ID NOT IN(SELECT ID FROM glpi_plugin_reports_profiles)");

		$current_rights = $this->fields;
		unset($current_rights["ID"]);
		unset($current_rights["profile"]);
		foreach($current_rights as $right => $value)
		{
			if (!isset($rights[$right])) {
				// Delete the columns for old reports
				$DB->query("ALTER TABLE `".$this->table."` DROP COLUMN `".$right."`");
			}	
			else
				unset($rights[$right]);
		}
		
		foreach ($rights as $key=>$right) {	
			// Add the column for new report
			$DB->query("ALTER TABLE `".$this->table."` ADD COLUMN `".$key."` char(1) DEFAULT NULL");
			// Add "read" write to Super-admin
			$DB->query("UPDATE `".$this->table."` SET `".$key."`='r' WHERE ID=4");				
		}
					
		// Delete unused profiles
		$DB->query("DELETE FROM `glpi_plugin_reports_profiles` ".
			"WHERE ID NOT IN (SELECT ID FROM glpi_profiles)"); 		
	}
}
	
?>
