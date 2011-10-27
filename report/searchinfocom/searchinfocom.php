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

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE         = 1;
$DBCONNECTION_REQUIRED  = 0;

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");

$report = new PluginReportsAutoReport();

//Report's search criterias
new PluginReportsDateIntervalCriteria($report, 'order_date', $LANG["financial"][28]);
new PluginReportsDateIntervalCriteria($report, 'buy_date', $LANG["financial"][14]);
new PluginReportsDateIntervalCriteria($report, 'delivery_date', $LANG["financial"][27]);
new PluginReportsDateIntervalCriteria($report, 'use_date', $LANG["financial"][76]);
new PluginReportsDateIntervalCriteria($report, 'inventory_date', $LANG["financial"][114]);
new PluginReportsTextCriteria($report, 'immo_number', $LANG['financial'][20]);
new PluginReportsTextCriteria($report, 'order_number', $LANG['financial'][18]);
new PluginReportsTextCriteria($report, 'delivery_number', $LANG['financial'][19]);
new PluginReportsDropdownCriteria($report, 'budgets_id', 'glpi_budgets', $LANG['financial'][87]);

//Display criterias form is needed
$report->displayCriteriasForm();

//If criterias have been validated
if ($report->criteriasValidated()) {

   // Report title
   $report->setSubNameAuto();

   // Report Columns
   $cols = array(new PluginReportsColumnType('itemtype', $LANG['common'][17]),
                 new PluginReportsColumnTypeLink('items_id', $LANG['common'][1], 'itemtype',
                                                 array('with_comment' => 1)),
                 new PluginReportsColumnDate('order_date', $LANG['financial'][28]),
                 new PluginReportsColumn('order_number', $LANG['financial'][18]),
                 new PluginReportsColumnDate('buy_date', $LANG['financial'][14]),
                 new PluginReportsColumn('delivery_date', $LANG['financial'][27]),
                 new PluginReportsColumn('delivery_number', $LANG['financial'][19]),
                 new PluginReportsColumn('immo_number', $LANG['financial'][20]),
                 new PluginReportsColumnDate('use_date', $LANG['financial'][76]),
                 new PluginReportsColumnDate('inventory_date', $LANG['financial'][114]),
                 new PluginReportsColumnLink('budgets_id', $LANG['financial'][87], 'Budget'));

   $report->setColumns($cols);

   // Build SQL request
   $sql = "SELECT *
           FROM `glpi_infocoms`
           WHERE `itemtype` NOT IN ('Software', 'CartridgeItem', 'ConsumableItem')".
           $report->addSqlCriteriasRestriction().
           getEntitiesRestrictRequest('AND', 'glpi_infocoms').
          "ORDER BY `itemtype`";

   $report->setGroupBy('itemtype');
   $report->setSqlRequest($sql);
   $report->execute();

} else {
   Html::footer();
}
?>