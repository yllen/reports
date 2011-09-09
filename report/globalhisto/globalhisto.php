<?php
/*
 * @version $Id: HEADER 14684 2011-06-11 06:32:40Z remi $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

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
 along with GLPI; if not, write to the Free Software Foundation, Inc.,
 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 --------------------------------------------------------------------------
 */

/*
 * ----------------------------------------------------------------------
 * Original Author of file: Remi Collet
 *
 * Purpose of file:
 *    Generate location report
 *    Illustrate use of simpleReport
 * ----------------------------------------------------------------------
 */

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE         = 1;
$DBCONNECTION_REQUIRED  = 0; // not really a big SQL request

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");

$report = new PluginReportsAutoReport();

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

   //Names of the columns to be displayed
   $report->setColumnsNames(array('id'            => $LANG['common'][2],
                                  'date_mod'      => $LANG['common'][27],
                                  'user_name'     => $LANG['common'][34],
                                  'linked_action' => $LANG['event'][19]));

   //Colunmns mappings if needed
   $columns_mappings
      = array('linked_action' => array(Log::HISTORY_DELETE_ITEM        => $LANG['log'][22],
                                       Log::HISTORY_RESTORE_ITEM       => $LANG['log'][23],
                                       Log::HISTORY_ADD_DEVICE         => $LANG['devices'][25],
                                       Log::HISTORY_UPDATE_DEVICE      => $LANG['log'][28],
                                       Log::HISTORY_DELETE_DEVICE      => $LANG['devices'][26],
                                       Log::HISTORY_INSTALL_SOFTWARE   => $LANG['software'][44],
                                       Log::HISTORY_UNINSTALL_SOFTWARE => $LANG['software'][45],
                                       Log::HISTORY_DISCONNECT_DEVICE  => $LANG['central'][6],
                                       Log::HISTORY_CONNECT_DEVICE     => $LANG['log'][55],
                                       Log::HISTORY_OCS_IMPORT         => $LANG['ocsng'][7],
                                       Log::HISTORY_OCS_DELETE         => $LANG['ocsng'][46],
                                       Log::HISTORY_OCS_LINK           => $LANG['ocsng'][47],
                                       Log::HISTORY_OCS_IDCHANGED      => $LANG['ocsng'][48],
                                       Log::HISTORY_LOG_SIMPLE_MESSAGE => ""));

   $report->setColumnsMappings($columns_mappings);

   $query = "SELECT `id`, `date_mod`, `user_name`, `linked_action`
             FROM `glpi_logs` ".
             $report->addSqlCriteriasRestriction("WHERE")."
             ORDER BY `date_mod`";

   $report->setSqlRequest($query);
   $report->execute();
}

Html::footer();
?>