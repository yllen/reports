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

// Original Author of file: BALPE Dévi
// Purpose of file:
// ----------------------------------------------------------------------

/**
 * Search for reports in a directory
 * @param rep : the directory to check for new reports
 * @param substr
 * @return tab : an array which contains all the reports found in the directory
 */
function searchReport($rep, $substr = 0) {
	global $CFG_GLPI;
	$tab = array ();

	if (file_exists($rep)) {
		if (is_dir($rep)) {
			$id_dossier = opendir($rep);
			while ($element = readdir($id_dossier)) {
				if (substr($element, 0, 1) != ".") {
					includeLocales($element);
					$tab[$element] = $element;
				}
			}
			closedir($id_dossier);
		}
	}

	return $tab;
}

/**
 * Include locales for a specific report
 * @param report_name the name of the report to use
 */
function includeLocales($report_name) {
	global $CFG_GLPI, $LANG;
	
	$prefix = GLPI_ROOT . "/plugins/reports/report/". $report_name ."/" . $report_name;
	 
	if (isset ($_SESSION["glpilanguage"]) 
		&& file_exists($prefix . "." . $CFG_GLPI["languages"][$_SESSION["glpilanguage"]][1])) {
		include_once  ($prefix . "." . $CFG_GLPI["languages"][$_SESSION["glpilanguage"]][1]);

	} else if (file_exists($prefix . ".en_GB.php")) {
			include_once  ($prefix . ".en_GB.php");

	} else if (file_exists($prefix . ".fr_FR.php")) {
			include_once  ($prefix . ".fr_FR.php");

	} else {
		logInFile('php-errors', "includeLocales($report_name) => not found\n");
	}
}

/**
 * Manage display and export of an sql query
 * @param name name of the report
 * @param sql the sql query to execute
 * @param cols the columns and their name to display
 * @param subname second level of name to display 
 * @param group an array which contains all the fields to use in GROUP BY sql instruction
 */
function simpleReport($name, $sql, $cols = array (), $subname = "", $group = array ()) {
	global $DB, $LANG, $CFG_GLPI;

	$report = new AutoReport($name);

	if (count($cols)) {
		$report->setColumnsNames($cols);
	}
	if (!empty($subname)) {
		$report->setSubName($subname);
	}
	if (count($group)) {
		$report->setGroupBy($group);
	}
	$report->setSqlRequest($sql);
	$report->execute();
}

/**
 * Create access rights for an user
 * @param ID the user ID
 */
function plugin_reports_createaccess($ID) {

	global $DB;

	$Profile = new Profile();
	$Profile->GetfromDB($ID);
	$name = $Profile->fields["profil"];

	$query = "INSERT INTO `glpi_plugin_reports_profiles` ( `ID`, `profile`) 
					VALUES ('$ID', '$name');";

	$DB->query($query);

}

/**
 * Look for all the plugins, and update rights if necessary
 */
function plugin_reports_updatePluginRights($path) {
	$prof = new ReportProfile();
	$prof->getEmpty();
	$tab = searchReport($path, 1);
	$prof->updateRights(-1, $tab);
	return $tab;
}

function dropdownSoftwareWithLicense($soft) {
	global $DB, $LANG;

	$query = "SELECT glpi_software.name,glpi_software.ID 
					FROM glpi_softwarelicenses 
						LEFT JOIN glpi_software on glpi_softwarelicenses.sID=glpi_software.ID 
						LEFT JOIN glpi_entities ON (glpi_software.FK_entities = glpi_entities.ID)
					WHERE glpi_softwarelicenses.FK_entities IN(" . $_SESSION['glpiactiveentities_string'] . ")
				GROUP BY glpi_software.name";
	$result = $DB->query($query);
	if ($DB->numrows($result)) {
		echo "<select name='soft'>";
		while ($data = $DB->fetch_array($result)) {
			echo "<option value='" . $data["ID"] . "'";
				if ($data["ID"]==$soft){
					echo " selected='selected'";
				}			
				echo ">" . $data["name"];
			echo "</option>";
		}
		echo "</select>";
	}
}

function getPriorityLabelsArray() {
	return array (
		"1" => getPriorityName(1),
		"2" => getPriorityName(2),
		"3" => getPriorityName(3),
		"4" => getPriorityName(4),
		"5" => getPriorityName(5)
	);
}

/**
 * This function should be in the core
 */
function displayOutputFormat() {
	global $LANG,$CFG_GLPI;
	echo "<select name='display_type'>";
	echo "<option value='" . PDF_OUTPUT_LANDSCAPE . "'>" . $LANG["buttons"][27] . " " . $LANG["common"][68] . "</option>";
	echo "<option value='" . PDF_OUTPUT_PORTRAIT . "'>" . $LANG["buttons"][27] . " " . $LANG["common"][69] . "</option>";
	echo "<option value='" . SYLK_OUTPUT . "'>" . $LANG["buttons"][28] . "</option>";
	echo "<option value='" . CSV_OUTPUT . "'>" . $LANG["buttons"][44] . "</option>";
	echo "<option value='-" . PDF_OUTPUT_LANDSCAPE . "'>" . $LANG["buttons"][29] . " " . $LANG["common"][68] . "</option>";
	echo "<option value='-" . PDF_OUTPUT_PORTRAIT . "'>" . $LANG["buttons"][29] . " " . $LANG["common"][69] . "</option>";
	echo "<option value='-" . SYLK_OUTPUT . "'>" . $LANG["buttons"][30] . "</option>";
	echo "<option value='-" . CSV_OUTPUT . "'>" . $LANG["buttons"][45] . "</option>";
	echo "</select>";
	echo "&nbsp;<input type='image' name='export'  src='" . $CFG_GLPI["root_doc"] . "/pics/greenbutton.png' title='" . $LANG["buttons"][31] . "' value='" . $LANG["buttons"][31] . "'>";
}

function getReportConfigPage($path,$report_name)
{
	return $path."/report/$report_name/".$report_name.".config".".php";	
}
?>
