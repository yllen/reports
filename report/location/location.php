<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 reports - Additional reports plugin for GLPI
 Copyright (C) 2003-2013 by the reports Development Team.

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

$USEDBREPLICATE        = 1;
$DBCONNECTION_REQUIRED = 0; // not really a big SQL request

include ("../../../../inc/includes.php");

//TRANS: The name of the report = Location tree
$report = new PluginReportsAutoReport(__('location_report_title', 'reports'));

$report->setColumns(array(new PluginReportsColumn('entity', __('Entity'),
                                                  array('sorton' => 'entity,location')),
                          new PluginReportsColumn('location', __('Location'),
                                                  array('sorton' => 'location')),
                          new PluginReportsColumnLink('link', _n('Link', 'Links', 2),'Location',
                                                  array('sorton' => '`glpi_locations`.`name`'))));

// SQL statement
$query = "SELECT `glpi_entities`.`completename` AS entity,
                 `glpi_locations`.`completename` AS location,
                 `glpi_locations`.`id` AS link
          FROM `glpi_locations`
          LEFT JOIN `glpi_entities` ON (`glpi_locations`.`entities_id` = `glpi_entities`.`id`)" .
          getEntitiesRestrictRequest(" WHERE ", "glpi_locations") .
          $report->getOrderBy('entity');

$report->setGroupBy('entity');
$report->setSqlRequest($query);
$report->execute();
?>