<?php
/*
 * @version $Id$
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
 * Original Author of file: Alexis CHARLES
 *
 * Purpose of file:
 *    Generate a detailed license report
 * ----------------------------------------------------------------------
 */

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE         = 1;
$DBCONNECTION_REQUIRED  = 0;

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");

$report = new PluginReportsAutoReport();

$softwarecategories = new PluginReportsSoftwareCategoriesCriteria($report, 'softwarecategories', $LANG['softwarecategories'][5]);
$softwarecategories->setSqlField("`glpi_softwarecategories`.`id`");

$software = new PluginReportsSoftwareCriteria($report, 'software', $LANG['plugin_reports']['applicationsbylocation'][2]);
$software->setSqlField("`glpi_softwares`.`id`");

$statecpt = new PluginReportsStatusCriteria($report, 'statecpt', $LANG['plugin_reports']['applicationsbylocation'][3]);
$statecpt->setSqlField("`glpi_computers`.`states_id`");

$location = new PluginReportsLocationCriteria($report, 'location', $LANG['plugin_reports']['applicationsbylocation'][4]);
$location->setSqlField("`glpi_computers`.`locations_id`");


$report->displayCriteriasForm();

// Form validate and only one software with license
if ($report->criteriasValidated()) {

   $report->setSubNameAuto();

   $report->setColumns(array(
      new PluginReportsColumnLink('soft', $LANG['help'][31], 'Software', array('sorton' => 'soft,version')),
      new PluginReportsColumnLink('locat', $LANG['common'][15], 'Location', array('sorton' => 'glpi_locations.name')),
      new PluginReportsColumnLink('computer', $LANG['help'][25],'Computer', array('sorton' => 'glpi_computers.name')),
      new PluginReportsColumn('statecpt', $LANG['joblist'][0]),
      new PluginReportsColumnLink('version', $LANG['rulesengine'][78], 'SoftwareVersion'),
      new PluginReportsColumnLink('user', $LANG['common'][34], 'User', array('sorton' => 'glpi_users.name'))
   ));

   $query = "SELECT `glpi_softwareversions`.`softwares_id` AS soft,
                    `glpi_softwareversions`.`name` AS software,
                    `glpi_locations`.`id` AS locat,
                    `glpi_computers`.`id` AS computer,
                    `state_ver`.`name` AS statever,
                    `state_cpt`.`name` AS statecpt,
                    `glpi_locations`.`name` as location,
                    `glpi_softwareversions`.`id` AS version,
                    `glpi_computers`.`users_id` AS user
             FROM `glpi_softwareversions`
             INNER JOIN `glpi_computers_softwareversions`
                   ON (`glpi_computers_softwareversions`.`softwareversions_id` = `glpi_softwareversions`.`id`)
             INNER JOIN `glpi_computers`
                   ON (`glpi_computers_softwareversions`.`computers_id` = `glpi_computers`.`id`)
             INNER JOIN `glpi_softwares`
                   ON (`glpi_softwares`.`id` = `glpi_softwareversions`.`softwares_id`)
             LEFT JOIN `glpi_softwarecategories`
                  ON (`glpi_softwares`.`softwarecategories_id` = `glpi_softwarecategories`.`id`)
             LEFT JOIN `glpi_locations`
                  ON (`glpi_locations`.`id` = `glpi_computers`.`locations_id`)
             LEFT JOIN `glpi_states` state_ver
                  ON (`state_ver`.`id` = `glpi_softwareversions`.`states_id`)
             LEFT JOIN `glpi_states` state_cpt
                  ON (`state_cpt`.`id` = `glpi_computers`.`states_id`) ".
             getEntitiesRestrictRequest('WHERE', 'glpi_softwareversions') .
             $report->addSqlCriteriasRestriction().
             "ORDER BY soft ASC, locat ASC";

   $report->setSqlRequest($query);
   $report->execute();
}
?>