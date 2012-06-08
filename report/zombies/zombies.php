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

$USEDBREPLICATE=0;
$DBCONNECTION_REQUIRED=0;

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");

$report = new PluginReportsAutoReport($LANG['plugin_reports']['zombies'][1]);

$name = new PluginReportsTextCriteria($report, 'name', $LANG['login'][6]);

$tab = array(0 => $LANG['choice'][0], 1 => $LANG['choice'][1]);
$filter = new PluginReportsArrayCriteria($report, 'tickets', $LANG['plugin_reports']['zombies'][2], $tab);

//Display criterias form is needed
$report->displayCriteriasForm();

//If criterias have been validated
if ($report->criteriasValidated()) {
   $report->setSubNameAuto();
   $report->delCriteria('tickets');

   $cols = array(
      new PluginReportsColumnItemCheckbox('id', 'User'),
      new PluginReportsColumnLink('id2', $LANG['common'][34], 'User', array('with_comment' => true, 'with_navigate' => true)),
      new PluginReportsColumn('name', $LANG['login'][6], array('sorton' => 'name')),
      new PluginReportsColumn('email', $LANG['mailing'][118]),
      new PluginReportsColumn('phone', $LANG['help'][35]),
      new PluginReportsColumn('location', $LANG['common'][15]),
      new PluginReportsColumnDate('last_login', $LANG['login'][0], array('sorton' => 'last_login')),
   );
   if (!$filter->getParameterValue()) {
      $cols[] = new PluginReportsColumnInteger('nb1', $LANG['common'][37],  array('with_zero' => false,
                                                                                  'sorton'    => 'nb1'));
      $cols[] = new PluginReportsColumnInteger('nb2', $LANG['job'][4],      array('with_zero' => false,
                                                                                  'sorton' => 'nb2'));
      $cols[] = new PluginReportsColumnInteger('nb3', $LANG['common'][104], array('with_zero' => false,
                                                                                  'sorton' => 'nb3'));
      $cols[] = new PluginReportsColumnInteger('nb4', $LANG['job'][6],      array('with_zero' => false,
                                                                                  'sorton' => 'nb4'));
   }

   $report->setColumns($cols);

   $query = "SELECT `glpi_users`.`id`, `glpi_users`.`id` AS id2, `glpi_users`.`name`, `last_login`,
                    (
                       SELECT COUNT(*)
                       FROM `glpi_tickets`
                       WHERE `glpi_users`.`id` = `glpi_tickets`.`users_id_recipient`
                    ) AS nb1,
                    (
                       SELECT COUNT(*)
                       FROM `glpi_tickets_users`
                       WHERE `glpi_users`.`id` = `glpi_tickets_users`.`users_id`
                             AND `glpi_tickets_users`.`type`=".Ticket::REQUESTER."
                    ) AS nb2,
                    (
                       SELECT COUNT(*)
                       FROM `glpi_tickets_users`
                       WHERE `glpi_users`.`id` = `glpi_tickets_users`.`users_id`
                             AND `glpi_tickets_users`.`type`=".Ticket::OBSERVER."
                    ) AS nb3,
                    (
                       SELECT COUNT(*)
                       FROM `glpi_tickets_users`
                       WHERE `glpi_users`.`id` = `glpi_tickets_users`.`users_id`
                             AND `glpi_tickets_users`.`type`=".Ticket::ASSIGN."
                    ) AS nb4,
                    `phone`, `glpi_locations`.`completename` as location,
                    `glpi_useremails`.`email`
             FROM `glpi_users`
             LEFT JOIN `glpi_locations`
                    ON `glpi_locations`.`id` = `glpi_users`.`locations_id`
             LEFT JOIN `glpi_useremails`
                    ON `glpi_useremails`.`users_id` = `glpi_users`.`id`
                   AND `glpi_useremails`.`is_default`
             WHERE `glpi_users`.`id` NOT IN (
                   SELECT distinct `users_id`
                   FROM `glpi_profiles_users`
                   )
             AND `glpi_users`.`is_deleted`=0 ".
             $report->addSqlCriteriasRestriction('AND');
   if ($filter->getParameterValue()) {
      $query .= " HAVING nb1=0 AND nb2=0 AND nb3=0  AND nb4=0 ";
   }
   $query .= $report->getOrderBy('name');

   $report->setSqlRequest($query);
   $report->execute(array('withmassiveaction' => 'User'));

} else {
   Html::Footer();
}
