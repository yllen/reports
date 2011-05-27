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
 * Original Author of file: Benoit Machiavello
 *
 * Purpose of file:
 * 		Generate group members report
 * ----------------------------------------------------------------------
 */

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE=1;
$DBCONNECTION_REQUIRED=0; // Not really a big SQL request

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");
//include (GLPI_ROOT . "/plugins/reports/inc/function.php");

$report = new PluginReportsAutoReport();

//Report's search criterias
new PluginReportsDateIntervalCriteria($report, '`glpi_tickets`.`date`', $LANG["reports"][60]);

$timeInterval = new PluginReportsTimeIntervalCriteria($report, '`glpi_tickets`.`date`');

//Criterias default values
$timeInterval->setStartTime($CFG_GLPI['planning_end']);
$timeInterval->setEndtime($CFG_GLPI['planning_begin']);
logDebug($CFG_GLPI);
//Display criterias form is needed
$report->displayCriteriasForm();

//If criterias have been validated
if ($report->criteriasValidated()) {
   $report->setSubNameAuto();

   //Names of the columns to be displayed
   $report->setColumns(array(
      new PluginReportsColumnMap('priority', $LANG["joblist"][2], array(), array('sorton' => '`priority`,`date`')),
      new PluginReportsColumnDateTime('date', $LANG["reports"][60], array('sorton' => '`date`')),
      new PluginReportsColumn('id2', $LANG['common'][2]),
      new PluginReportsColumnLink('id', $LANG["common"][57], 'Ticket'),
      new PluginReportsColumn('groupname', $LANG["common"][35], array('sorton' => '`groups_id`,`date`'))
   ));

   $query = "SELECT `glpi_tickets`.`priority`, `glpi_tickets`.`date` , `glpi_tickets`.`id`,
                    `glpi_tickets`.`id` AS id2, `glpi_groups`.`name` as groupname
             FROM `glpi_tickets`
             LEFT JOIN `glpi_groups_tickets`
                  ON (`glpi_groups_tickets`.`tickets_id` = `glpi_tickets`.`id`
                      AND `glpi_groups_tickets`.`type` = '".Ticket::ASSIGN."')
             LEFT JOIN `glpi_groups` ON (`glpi_groups_tickets`.`groups_id` = `glpi_groups`.`id`)
             WHERE `glpi_tickets`.`status` NOT IN ('solved', 'closed') ".
                  $report->addSqlCriteriasRestriction() .
                  getEntitiesRestrictRequest(' AND ', 'glpi_tickets').
             $report->getOrderBy('priority');

   $report->setSqlRequest($query);
   $report->execute();
}

?>