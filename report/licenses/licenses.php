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

/*
 * ----------------------------------------------------------------------
 * Original Author of file: Remi Collet
 * 
 * Purpose of file: 
 * 		Generate a detailed license report
 * ----------------------------------------------------------------------
 */ 

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE=1;
$DBCONNECTION_REQUIRED=0; 

define('GLPI_ROOT', '../../../..'); 
include (GLPI_ROOT . "/inc/includes.php"); 

$soft = (isset($_POST["soft"]) ? $_POST["soft"] : 0);

if (isset($_POST["display_type"])) {
	$output_type=$_POST["display_type"];
} else {
	$output_type=HTML_OUTPUT;
}

includeLocales("licenses");

plugin_reports_checkRight("licenses","r");
checkSeveralRightsAnd(array(COMPUTER_TYPE=>"r", SOFTWARE_TYPE=>"r"));

if ($output_type==HTML_OUTPUT || !$soft) {
	
	commonHeader($LANG['plugin_reports']['licenses'][1],$_SERVER['PHP_SELF'],"utils","report");
	
	echo "<div align='center'>";
	echo "<form action='".$_SERVER["PHP_SELF"]."' method='post'>";
	
	echo "<table class='tab_cadre' cellpadding='5'>\n";
	echo "<tr><th colspan='2'>" . $LANG['plugin_reports']['licenses'][1] . "</th></tr>\n";
	
	echo "<tr class='tab_bg_1' align='center'><td>" . $LANG["help"][31] . "</td>";
	echo "<td>";
	dropdownSoftwareWithLicense($soft);
	echo "</td></tr>\n";
	
	echo "<tr class='tab_bg_1' align='center'><td colspan='2'>";
	echo "<input type='submit' name='action' value='".$LANG["buttons"][2]."' class='submit' />";
	echo "</td></tr>\n";
	echo "</table>";
	
	echo "</form></div>";

}

if ($soft) {

	simpleReport(
		// Report Name
		"licenses", 
	
		// SQL statement
		"SELECT glpi_softwarelicenses.name AS license, 
			glpi_softwarelicenses.serial, 
			glpi_softwarelicenses.number AS nombre,
			glpi_dropdown_licensetypes.name AS type,
			buyversion.name AS buy,
			useversion.name AS used,
			glpi_softwarelicenses.expire, 
			glpi_softwarelicenses.comments,
			glpi_computers.name
		FROM glpi_softwarelicenses
			LEFT JOIN glpi_software ON (glpi_softwarelicenses.sID=glpi_software.ID)
			LEFT JOIN glpi_computers ON (glpi_computers.ID=glpi_softwarelicenses.FK_computers)
			LEFT JOIN glpi_softwareversions AS buyversion ON (buyversion.ID=glpi_softwarelicenses.buy_version)	
			LEFT JOIN glpi_softwareversions AS useversion ON (useversion.ID=glpi_softwarelicenses.use_version)	
			LEFT JOIN glpi_dropdown_licensetypes ON (glpi_dropdown_licensetypes.ID=glpi_softwarelicenses.type)
			LEFT JOIN glpi_entities ON (glpi_software.FK_entities = glpi_entities.ID)
		WHERE glpi_softwarelicenses.sID=$soft 
			AND glpi_software.deleted=0 
			AND glpi_software.is_template=0 " .
			getEntitiesRestrictRequest(' AND ', 'glpi_software') ."
		ORDER BY license",
		
		// Columns title (optional), from $LANG
		array (
			"license"=>$LANG["software"][11],
			"serial"=>$LANG["common"][19],
			"nombre"=>$LANG['tracking'][29],
			"type"=>$LANG['common'][17],
			"buy"=>$LANG['plugin_reports']['licenses'][2],
			"used"=>$LANG['plugin_reports']['licenses'][3],
			"expire"=>$LANG['financial'][98],
			"comments"=>$LANG["common"][25],
			"name"=>$LANG["help"][25],
			),
	
		// Sub title
		getDropdownName("glpi_software", $soft),
		
		// Group by
		array ("license")
		);
		
}

?>