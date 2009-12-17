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
 * Original Author of file:
 *
 * Purpose of file:
 *
 * ----------------------------------------------------------------------
 */
//$NEEDED_ITEMS = array("enterprise");
//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE=1;
$DBCONNECTION_REQUIRED=0; // Really a big SQL request

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");

$report = new PluginReportsAutoReport("statticketsbypriority");

//Report's search criterias
new PluginReportsDateIntervalCriteria($report,"date");

//Display criterias form is needed
$report->displayCriteriasForm($_SERVER['PHP_SELF']);

//If criterias have been validated
if ($report->criteriasValidated()) {
   $report->setSubNameAuto();

   //Names of the columns to be displayed
   $colnumsnames = array ("priority"  => $LANG["joblist"][2],
                          "date"      => $LANG["reports"][60],
                          "id"        => $LANG['common'][2],
                          "tname"      => $LANG["common"][57],
                          "groupname" => $LANG["common"][35]);
   $report->setColumnsNames($colnumsnames);

   //Colunmns mappings if needed
   $columns_mappings = array("priority" => getPriorityLabelsArray());
   $report->setColumnsMappings($columns_mappings);

   $query = "SELECT `glpi_tickets`.`priority`, DATE(`glpi_tickets`.`date`) AS date,
                    `glpi_tickets`.`id`, `glpi_tickets`.`name` AS tname,
                    `glpi_groups`.`name` AS groupname
             FROM `glpi_tickets`
             LEFT JOIN `glpi_groups` ON (`glpi_tickets`.`groups_id_assign` = `glpi_groups`.`id`) ".
             $report->addSqlCriteriasRestriction("WHERE")."
                   AND `glpi_tickets`.`status` NOT IN ('closed', 'solved')
             ORDER BY priority DESC, date ASC";

   $report->setSqlRequest($query);
	$report->execute();

} else {
   commonFooter();
}

?>
