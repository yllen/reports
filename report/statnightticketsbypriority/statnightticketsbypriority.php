<?php
/*
 * @version $Id: HEADER 15930 2011-10-25 10:47:55Z jmd $
 -------------------------------------------------------------------------
 reports - Additional reports plugin for GLPI
 Copyright (C) 2003-2011 by the reports Development Team.

 https://forge.indepnet.net/projects/reports
 -------------------------------------------------------------------------

 LICENSE

 This file is part of reports.

 reports is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 reports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with reports. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/*
 * ----------------------------------------------------------------------
 * Original Author of file: Benoit Machiavello
 *
 * Purpose of file:
 *    Generate group members report
 * ----------------------------------------------------------------------
 */

$USEDBREPLICATE         = 1;
$DBCONNECTION_REQUIRED  = 0;

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");

$report = new PluginReportsAutoReport();

//Report's search criterias
new PluginReportsDateIntervalCriteria($report, '`glpi_tickets`.`date`', $LANG["reports"][60]);

$timeInterval = new PluginReportsTimeIntervalCriteria($report, '`glpi_tickets`.`date`');

//Criterias default values
$timeInterval->setStartTime($CFG_GLPI['planning_end']);
$timeInterval->setEndtime($CFG_GLPI['planning_begin']);

//Display criterias form is needed
$report->displayCriteriasForm();

//If criterias have been validated
if ($report->criteriasValidated()) {
   $report->setSubNameAuto();

   //Names of the columns to be displayed
   $report->setColumns(array(new PluginReportsColumnMap('priority', $LANG["joblist"][2], array(),
                                                        array('sorton' => '`priority`, `date`')),
                             new PluginReportsColumnDateTime('date', $LANG["reports"][60],
                                                             array('sorton' => '`date`')),
                             new PluginReportsColumn('id2', $LANG['common'][2]),
                             new PluginReportsColumnLink('id', $LANG["common"][57], 'Ticket'),
                             new PluginReportsColumn('groupname', $LANG["common"][35],
                                                     array('sorton' => '`groups_id`, `date`'))));

   $query = "SELECT `glpi_tickets`.`priority`, `glpi_tickets`.`date` , `glpi_tickets`.`id`,
                    `glpi_tickets`.`id` AS id2, `glpi_groups`.`name` as groupname
             FROM `glpi_tickets`
             LEFT JOIN `glpi_groups_tickets`
                  ON (`glpi_groups_tickets`.`tickets_id` = `glpi_tickets`.`id`
                      AND `glpi_groups_tickets`.`type` = '".Ticket::ASSIGN."')
             LEFT JOIN `glpi_groups` ON (`glpi_groups_tickets`.`groups_id` = `glpi_groups`.`id`)
             WHERE `glpi_tickets`.`status` NOT IN ('solved', 'closed')
                  AND NOT `glpi_tickets`.`is_deleted` ".
                  $report->addSqlCriteriasRestriction() .
                  getEntitiesRestrictRequest(' AND ', 'glpi_tickets').
             $report->getOrderBy('priority');

   $report->setSqlRequest($query);
   $report->execute();
}
?>