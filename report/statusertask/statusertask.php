<?php
/*    ----------------------------------------------------------------------
* @version $Id: yllen $
-------------------------------------------------------------------------
  LICENSE

 This file is part of Reports plugin for GLPI.

 Reports is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Reports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Reports. If not, see <http://www.gnu.org/licenses/>.

 @package   reports
 @authors    Nelly Mahu-Lasson, Remi Collet
 @copyright Copyright (c) 2009-2021 Reports plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.glpi-project.org/projects/reports
 @link      http://www.glpi-project.org/
 @since     2009
 --------------------------------------------------------------------------
*/

$USEDBREPLICATE         = 1;
$DBCONNECTION_REQUIRED  = 1;

include ("../../../../inc/includes.php");

//titre du rapport dans la liste de selection,  soit en dur ici, soit mettre à jour la variable dans les fichiers de traduction;
$report = new PluginReportsAutoReport(__('statusertask_report_title', 'reports'));

//critère de selection;
$date = new PluginReportsDateIntervalCriteria($report, '`glpi_tickettasks`.`date`', __('Tasks created', 'reports'));

$report->displayCriteriasForm();

$display_type = Search::HTML_OUTPUT;

if ($report->criteriasValidated()) {
 //  $report->setSubNameAuto();
//   $title    = $report->getFullTitle();

   $cols = [new PluginReportsColumn('realname', __('User')),
            new PluginReportsColumn('date', __('Date')),
            new PluginReportsColumn('ticketid', __('Ticket task id')),
            new PluginReportsColumn('duree', __('Duration')),
            new PluginReportsColumn('nbretask', __('Number created tasks', 'reports')),
            new PluginReportsColumn('total', __('Total duration'))];

   $report->setColumns($cols);


   $query = "SELECT DATE_FORMAT(`glpi_tickettasks`.`date`, '%d/%m/%Y') AS date,
                    `glpi_users`.`realname`,
                    `glpi_tickettasks`.`id` AS ticketid,
                    SEC_TO_TIME( sum( glpi_tickettasks.actiontime ) )  AS duree,
                    count(`glpi_tickettasks`.`tickets_id` ) AS nbretask,
                    (SELECT SEC_TO_TIME(sum(glpi_tickettasks.actiontime ))
                     FROM `glpi_tickettasks`
                     INNER JOIN  `glpi_users` ON (`glpi_tickettasks`.`users_id` = `glpi_users`.`id`)
                     WHERE `glpi_users`.`id` =".Session::getLoginUserID(false) ." ".
                           $date->getSqlCriteriasRestriction()." ) as total
              FROM `glpi_tickettasks`
              INNER JOIN  `glpi_users` ON (`glpi_tickettasks`.`users_id` = `glpi_users`.`id`)
              WHERE `glpi_users`.`id` = ".Session::getLoginUserID(false) ." ".
                    $date->getSqlCriteriasRestriction()."
              GROUP BY date, realname, ticketid";

   $report->setSqlRequest($query);
   $report->setGroupBy('RTOTAL');

   $report->execute();
}
else {
   Html::footer();
}

