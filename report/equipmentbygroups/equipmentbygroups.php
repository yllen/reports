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
 * Original Author of file: Walid Nouh
 * 
 * Purpose of file: 
 * 		
 * ----------------------------------------------------------------------
 */

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE = 1;
$DBCONNECTION_REQUIRED = 0; // Not really a big SQL request

$NEEDED_ITEMS = array (
	"search",
	"user",
	"group"
);
define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");

includeLocales("equipmentbygroups");

commonHeader($LANG['plugin_reports']['equipmentbygroups'][1], $_SERVER['PHP_SELF'], "utils", "report");

if (isset ($_GET["reset_search"]))
	resetSearch();

$_GET = getValues($_GET, $_POST);
displaySearchForm();

$sql = "SELECT ID as group_id, name as group_name FROM glpi_groups WHERE FK_entities=" . $_SESSION["glpiactive_entity"] . ($_GET["group"] ? " AND ID=" . $_GET["group"] : "") . " ORDER BY name";
$result = $DB->query($sql);

$last_group_id = -1;

echo "<div class='center'>";

while ($datas = $DB->fetch_array($result)) {
	if ($last_group_id != $datas["group_id"]) {
		echo "<table class='tab_cadre' cellpadding='5'>";
		echo "<tr class='tab_bg_1' align='left'>";
		echo "<th>" . $LANG["common"][35] . " : " . $datas["group_name"] . "</th></th>";
		echo "</tr>";
		$last_group_id = $datas["group_id"];
		echo "</table>";
	}

	getObjectsByGroupAndEntity($datas["group_id"], $_SESSION["glpiactive_entity"]);
}

echo "</div>";

commonFooter();

/**
 * Display group form
 */
function displaySearchForm() {
	global $_SERVER, $_GET, $LANG, $CFG_GLPI;

	echo "<form action='" . $_SERVER["PHP_SELF"] . "' method='post'>";
	echo "<table class='tab_cadre' cellpadding='5'>";
	echo "<tr class='tab_bg_1' align='center'>";
	echo "<td>";
	echo $LANG["common"][35] . " :";
	dropdownValue("glpi_groups", "group", $_GET["group"], 1, $_SESSION["glpiactive_entity"]);
	echo "</td>";

	// Display Reset search
	echo "<td>";
	echo "<a href='" . $CFG_GLPI["root_doc"] . "/plugins/reports/report/plugin_reports.equipmentbygroups.php?reset_search=reset_search' ><img title=\"" . $LANG["buttons"][16] . "\" alt=\"" . $LANG["buttons"][16] . "\" src='" . $CFG_GLPI["root_doc"] . "/pics/reset.png' class='calendrier'></a>";
	echo "</td>";

	echo "<td>";
	echo "<input type='submit' value='Valider' class='submit' />";
	echo "</td>";

	echo "</tr>";
	echo "</table>";
	echo "</form>";
}

function getValues($get, $post) {
	$get = array_merge($get, $post);

	if (!isset ($get["group"]))
		$get["group"] = 0;

	return $get;
}

/**
 * Reset search
 */
function resetSearch() {
	$_GET["group"] = 0;
}

/**
 * Get all the users in a group
 * @group_id the ID of the group
 * @entity the current entity
 * @return an array with user's ID an name
 */
function getAllUsersInGroup($group_id, $entity) {
	global $DB, $LANG;
	$sql = "SELECT gu.ID as user_id, gu.name as user_name FROM glpi_users as gu, glpi_users_groups as gug, glpi_groups as gg WHERE gg.FK_entities=" . $entity . " AND gg.ID=gug.FK_groups AND gug.FK_users=gu.ID AND gg.ID=$group_id ORDER BY gu.name";
	$result = $DB->query($sql);

	$users = array ();
	while ($datas = $DB->fetch_array($result))
		$users[$datas["user_id"]] = $datas["user_name"];

	//Add element 0, which means all devices not linked to a user
	$users[0] = $LANG["common"][49];

	return $users;
}

/**
 * Display all devices by group
 * @group_id the group ID
 * @entity the current entity
 */
function getObjectsByGroupAndEntity($group_id, $entity) {
	global $DB, $CFG_GLPI, $LANG, $LINK_ID_TABLE;

	$display_header = false;

	$types = array (
			COMPUTER_TYPE,
			MONITOR_TYPE,
			PRINTER_TYPE,
			PHONE_TYPE,
			NETWORKING_TYPE			
		);

	foreach ($types as $type) {

		$query = "SELECT main.ID as ID, name, FK_groups, serial, otherserial, FK_glpi_enterprise, buy_date " .
				"FROM " . $LINK_ID_TABLE[$type] . " as main " .
				"LEFT JOIN glpi_infocoms as infocoms ON (main.ID=infocoms.FK_device AND device_type=$type) " .
				"WHERE FK_groups=" .$group_id . " AND FK_entities=$entity";

		if (in_array($LINK_ID_TABLE[$type], $CFG_GLPI["template_tables"])) {
			$query .= " AND is_template=0 ";
		}
		if (in_array($LINK_ID_TABLE[$type], $CFG_GLPI["deleted_tables"])) {
			$query .= " AND deleted=0 ";
		}

		$result = $DB->query($query);
		if ($DB->numrows($result) > 0) {
			if (!$display_header) {
				echo "<br><table class='tab_cadre_fixe'>";
				echo "<tr><th>" . $LANG["common"][17] . "</th><th>" . $LANG["common"][16] . "</th>";
				echo "<th>" . $LANG["common"][19] . "</th><th>" . $LANG["common"][20] . "</th>";
				echo "<th>" . $LANG["financial"][26] . "</th><th>" . $LANG["financial"][14] . "</th>";
				echo "</tr>";
				$display_header = true;
			}
			displayUserDevices($type, $result);
		}

	}
	echo "</table>";
}

/**
 * Display all device for a group
 * @user_id the user ID
 * @user_name the user name
 * @type the objet type
 * @result the resultset of all the devices found
 */
function displayUserDevices($type, $result) {
	global $DB, $CFG_GLPI, $LANG, $LINK_ID_TABLE, $INFOFORM_PAGES;

	$ci = new CommonItem();

	$ci->setType($type);
	$type_name = $ci->getType();
	$cansee = haveTypeRight($type, "r");
	while ($data = $DB->fetch_array($result)) {
		$link = $data["name"];
		$link = "<a href='" . $CFG_GLPI["root_doc"] . "/" . $INFOFORM_PAGES[$type] . "?ID=" . $data["ID"] . "'>" . $link . (($CFG_GLPI["view_ID"] || empty ($link)) ? " (" . $data["ID"] . ")" : "") . "</a>";
		$linktype = "";
		if (isset ($groups[$data["FK_groups"]])) {
			$linktype = $LANG["common"][35] . " " . $groups[$data["FK_groups"]];
		}

		echo "<tr class='tab_bg_1'><td class='center'>$type_name</td><td class='center'>$link</td>";

		echo "<td class='center'>";
		if (isset ($data["serial"]) && !empty ($data["serial"])) {
			echo $data["serial"];
		} else
			echo '&nbsp;';

		echo "</td><td class='center'>";
		if (isset ($data["otherserial"]) && !empty ($data["otherserial"])) {
			echo $data["otherserial"];
		} else
			echo '&nbsp;';

		echo "</td><td class='center'>";
		if (isset ($data["FK_glpi_enterprise"]) && !empty ($data["FK_glpi_enterprise"])) {
			echo getDropdownName("glpi_enterprises", $data["FK_glpi_enterprise"]);
		} else
			echo '&nbsp;';

		echo "</td><td class='center'>";
		if (isset ($data["buy_date"]) && !empty ($data["buy_date"])) {
			echo convDate($data["buy_date"]);
		} else
			echo '&nbsp;';
		echo "</td>";
		echo "</tr>";
	}
}
?>
