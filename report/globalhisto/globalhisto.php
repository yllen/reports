<?php
/**
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
 @copyright Copyright (c) 2009-2022 Reports plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.glpi-project.org/projects/reports
 @link      http://www.glpi-project.org/
 @since     2009
 --------------------------------------------------------------------------
 */

$USEDBREPLICATE         = 1;
$DBCONNECTION_REQUIRED  = 0; // not really a big SQL request

include ("../../../../inc/includes.php");
//TRANS: The name of the report = Global History (for Test / example only)
$report = new PluginReportsAutoReport(__('globalhisto_report_title', 'reports'));

//Report's search criterias
//Possible current values are :
// - date-interval
// - time-interval
// - group
new PluginReportsDateIntervalCriteria($report, "date_mod");

//Display criterias form is needed
$report->displayCriteriasForm();

//If criterias have been validated
if ($report->criteriasValidated()) {
   $report->setSubNameAuto();

   //Colunmns mappings if needed
   $columns_mappings =
      ['0'                               => '',
       Log::HISTORY_ADD_DEVICE           => Log::getLinkedActionLabel(Log::HISTORY_ADD_DEVICE),
       Log::HISTORY_UPDATE_DEVICE        => Log::getLinkedActionLabel(Log::HISTORY_UPDATE_DEVICE),
       Log::HISTORY_DELETE_DEVICE        => Log::getLinkedActionLabel(Log::HISTORY_DELETE_DEVICE),
       Log::HISTORY_INSTALL_SOFTWARE     => Log::getLinkedActionLabel(Log::HISTORY_INSTALL_SOFTWARE),
       Log::HISTORY_UNINSTALL_SOFTWARE   => Log::getLinkedActionLabel(Log::HISTORY_UNINSTALL_SOFTWARE),
       Log::HISTORY_DISCONNECT_DEVICE    => Log::getLinkedActionLabel(Log::HISTORY_DISCONNECT_DEVICE),
       Log::HISTORY_CONNECT_DEVICE       => Log::getLinkedActionLabel(Log::HISTORY_CONNECT_DEVICE),
       Log::HISTORY_LOCK_DEVICE          => Log::getLinkedActionLabel(Log::HISTORY_LOCK_DEVICE),
       Log::HISTORY_UNLOCK_DEVICE        => Log::getLinkedActionLabel(Log::HISTORY_UNLOCK_DEVICE),
       Log::HISTORY_LOG_SIMPLE_MESSAGE   => Log::getLinkedActionLabel(Log::HISTORY_LOG_SIMPLE_MESSAGE),
       Log::HISTORY_DELETE_ITEM          => Log::getLinkedActionLabel(Log::HISTORY_DELETE_ITEM),
       Log::HISTORY_RESTORE_ITEM         => Log::getLinkedActionLabel(Log::HISTORY_RESTORE_ITEM),
       Log::HISTORY_ADD_RELATION         => Log::getLinkedActionLabel(Log::HISTORY_ADD_RELATION),
       Log::HISTORY_DEL_RELATION         => Log::getLinkedActionLabel(Log::HISTORY_DEL_RELATION),
       Log::HISTORY_CREATE_ITEM          => Log::getLinkedActionLabel(Log::HISTORY_CREATE_ITEM)];

   //Names of the columns to be displayed
   $report->setColumns([new PluginReportsColumn('id', __('ID')),
                        new PluginReportsColumnDate('date_mod', __('Date')),
                        new PluginReportsColumn('user_name', __('User')),
                        new PluginReportsColumnMap('linked_action', _x('noun','Update'),
                                                   $columns_mappings)]);


   $query = "SELECT `id`, `date_mod`, `user_name`, `linked_action`
             FROM `glpi_logs` ".
             $report->addSqlCriteriasRestriction("WHERE")."
             ORDER BY `date_mod`";

   $report->setSqlRequest($query);
   $report->execute();
} else {
   Html::footer();
}
