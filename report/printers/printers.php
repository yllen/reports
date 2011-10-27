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
 * Original Author of file: Remi Collet
 *
 * Purpose of file:
 *    Big UNION to have a report including all inventory
 *
 * ----------------------------------------------------------------------
 */

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE         = 1;
$DBCONNECTION_REQUIRED  = 0;

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");

$report = new PluginReportsAutoReport();

// Definition of the criteria
$grpcrit = new PluginReportsGroupCriteria($report, 'glpi_printers.groups_id', '', 'is_requester');
$loccrit = new PluginReportsLocationCriteria($report, 'glpi_printers.locations_id');

//Display criterias form is needed
$report->displayCriteriasForm();

//If criterias have been validated
if ($report->criteriasValidated()) {
   $report->setSubNameAuto();

   $cols = array(
      new PluginReportsColumnLink('id', $LANG['common'][16], 'Printer',
                                  array('with_navigate' => true,
                                        'sorton'        => 'glpi_printers.name')),
      new PluginReportsColumn('state', $LANG['state'][0]),
      new PluginReportsColumn('manu', $LANG['common'][5]),
      new PluginReportsColumn('model', $LANG['common'][22],
                              array('sorton' => 'glpi_manufacturers.name, glpi_printermodels.name')),
      new PluginReportsColumn('serial', $LANG['common'][19]),
      new PluginReportsColumn('otherserial', $LANG['common'][20]),
      new PluginReportsColumn('immo_number', $LANG['financial'][20]),
      new PluginReportsColumnDate('buy_date', $LANG['financial'][14],
                                  array('sorton' => 'glpi_infocoms.buy_date')),
      new PluginReportsColumnDate('use_date', $LANG['financial'][76],
                                  array('sorton' => 'glpi_infocoms.use_date')),
      new PluginReportsColumnInteger('last_pages_counter', $LANG['printers'][31]),
      new PluginReportsColumnLink('user', $LANG['common'][34], 'User'),
      new PluginReportsColumnLink('groupe', $LANG['common'][35], 'Group',
                                  array('sorton' => 'glpi_groups.name')),
      new PluginReportsColumnInteger('compgrp', $LANG['plugin_reports']['printers'][2]),
      new PluginReportsColumnInteger('usergrp', $LANG['plugin_reports']['printers'][3]),
      new PluginReportsColumnLink('location', $LANG['common'][15], 'Location',
                                  array('sorton' => 'glpi_locations.completename')),
      new PluginReportsColumnInteger('comploc', $LANG['plugin_reports']['printers'][4]),
      new PluginReportsColumnInteger('userloc', $LANG['plugin_reports']['printers'][5]),
   );

   $report->setColumns($cols);

   $compgrp = "SELECT COUNT(*)
               FROM `glpi_computers`
               WHERE `glpi_computers`.`groups_id`>0
                     AND `glpi_computers`.`groups_id`=`glpi_printers`.`groups_id`";

   $usergrp = "SELECT COUNT(*)
               FROM `glpi_groups_users`
               WHERE `glpi_groups_users`.`groups_id`>0
                     AND `glpi_groups_users`.`groups_id`=`glpi_printers`.`groups_id`";

   $comploc = "SELECT COUNT(*)
               FROM `glpi_computers`
               WHERE `glpi_computers`.`locations_id`>0
                     AND `glpi_computers`.`locations_id`=`glpi_printers`.`locations_id`";

   $userloc = "SELECT COUNT(*)
               FROM `glpi_users`
               WHERE `glpi_users`.`locations_id`>0
                     AND `glpi_users`.`locations_id`=`glpi_printers`.`locations_id`";

   $sql = "SELECT `glpi_printers`.`id`, `glpi_printers`.`serial`, `glpi_printers`.`otherserial`,
                  `glpi_printers`.`last_pages_counter`,
                  `glpi_printermodels`.`name` AS model,
                  `glpi_manufacturers`.`name` AS manu,
                  `glpi_printers`.`users_id` AS user,
                  `glpi_printers`.`groups_id` AS groupe, ($compgrp) AS compgrp, ($usergrp) AS usergrp,
                  `glpi_locations`.`id` AS location, ($comploc) AS comploc, ($userloc) AS userloc,
                  `glpi_infocoms`.`immo_number`, `glpi_infocoms`.`buy_date`, `glpi_infocoms`.`use_date`,
                  `glpi_states`.`name` AS state
           FROM `glpi_printers`
           LEFT JOIN `glpi_printermodels` ON (`glpi_printermodels`.`id`=`glpi_printers`.`printermodels_id`)
           LEFT JOIN `glpi_manufacturers` ON (`glpi_manufacturers`.`id`=`glpi_printers`.`manufacturers_id`)
           LEFT JOIN `glpi_states` ON (`glpi_states`.`id`=`glpi_printers`.`states_id`)
           LEFT JOIN `glpi_infocoms` ON (`glpi_infocoms`.`itemtype`='Printer' AND `glpi_infocoms`.`items_id`=`glpi_printers`.`id`)
           LEFT JOIN `glpi_locations` ON (`glpi_locations`.`id`=`glpi_printers`.`locations_id`)
           LEFT JOIN `glpi_groups` ON (`glpi_groups`.`id`=`glpi_printers`.`groups_id`)
           ".
           getEntitiesRestrictRequest('WHERE', 'glpi_printers').
           $report->addSqlCriteriasRestriction().
           $report->getOrderBy('groupe');

   $report->setSqlRequest($sql);
   $report->execute();

} else {
   Html::footer();
}
