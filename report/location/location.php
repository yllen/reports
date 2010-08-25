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
 * 		Generate location report
 * 		Illustrate use of simpleReport
 * ----------------------------------------------------------------------
 */

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE=1;
$DBCONNECTION_REQUIRED=0; // Really a big SQL request

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");

// Instantiate Report with Name
$report = new PluginReportsAutoReport();

// Columns title (optional), from $LANG
$report->setColumns(array(new PluginReportsColumn('entity', $LANG["entity"][0],
                                                  array('sorton' => 'entity,location')),
                          new PluginReportsColumn('location', $LANG["common"][15],
                                                  array('sorton' => 'location')),
                          new PluginReportsColumnLink('link', $LANG['title'][34],'Location',
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