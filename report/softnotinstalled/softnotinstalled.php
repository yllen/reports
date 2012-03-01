<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Softguard plugin for GLPI
 Copyright (C) 2003-2011 by the softguards Development Team.

 https://forge.indepnet.net/projects/softguard
 -------------------------------------------------------------------------

 LICENSE
		
 This file is part of softguard.

 Softguard is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Softguard is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Softguard. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE=1;
$DBCONNECTION_REQUIRED=0;

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");

$report = new PluginReportsAutoReport();

$soft = new PluginReportsTextCriteria($report, 'software', $LANG['help'][31]);
$soft->setSqlField("`glpi_softwares`.`name`");

$report->displayCriteriasForm();

// Form validate and only one software with license
if ($report->criteriasValidated()) {

   $report->setSubNameAuto();

   $report->setColumns(array(
      new PluginReportsColumnLink('computer', $LANG['help'][25],'Computer',
            array('sorton' => 'glpi_computers.name')),
      new PluginReportsColumn('operatingsystems', $LANG['computers'][9],
            array('sorton' => 'operatingsystems')),
      new PluginReportsColumn('state', $LANG['joblist'][0],
            array('sorton' => 'state')),
      new PluginReportsColumn('entity', $LANG['entity'][0],
            array('sorton' => 'entity,location')),
      new PluginReportsColumn('location', $LANG['common'][15]." - ".$LANG['help'][25],
            array('sorton' => 'location'))
   ));
   
   $query = "SELECT `glpi_computers`.`id` AS computer,
                    `glpi_states`.`name` AS state,
                    `glpi_operatingsystems`.`name` as operatingsystems,
                    `glpi_locations`.`completename` as location,
                    `glpi_entities`.`completename` as entity
             FROM `glpi_computers`
             LEFT JOIN `glpi_states`
                  ON (`glpi_states`.`id` = `glpi_computers`.`states_id`)
             LEFT JOIN `glpi_operatingsystems`
                  ON (`glpi_operatingsystems`.`id` = `glpi_computers`.`operatingsystems_id`)
             LEFT JOIN `glpi_locations`
                  ON (`glpi_locations`.`id` = `glpi_computers`.`locations_id`)
             LEFT JOIN `glpi_entities`
                  ON (`glpi_entities`.`id` = `glpi_computers`.`entities_id`) ".
             getEntitiesRestrictRequest('WHERE', 'glpi_computers');
   $query .= "AND `glpi_computers`.`is_template` = 0
             AND `glpi_computers`.`is_deleted` = 0
             AND `glpi_computers`.`id` NOT IN (
                SELECT `glpi_computers`.`id`
                FROM `glpi_softwares`
                INNER JOIN `glpi_softwareversions`
                     ON (`glpi_softwares`.`id` = `glpi_softwareversions`.`softwares_id`)
                INNER JOIN `glpi_computers_softwareversions`
                     ON (`glpi_computers_softwareversions`.`softwareversions_id` = `glpi_softwareversions`.`id`)
                INNER JOIN `glpi_computers`
                     ON (`glpi_computers_softwareversions`.`computers_id` = `glpi_computers`.`id`) ".
             getEntitiesRestrictRequest('WHERE', 'glpi_computers') .
             $report->addSqlCriteriasRestriction();
   $query .= ")".
             $report->getOrderby('computer', true);
   
   
   $report->setSqlRequest($query);
   $report->execute();
}

?>