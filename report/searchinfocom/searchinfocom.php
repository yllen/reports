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

$USEDBREPLICATE         = 1;
$DBCONNECTION_REQUIRED  = 0;

include ("../../../../inc/includes.php");

$report = new PluginReportsAutoReport(__('searchinfocom_report_title'));

//Report's search criterias
new PluginReportsDateIntervalCriteria($report, 'order_date', __('Order date'));
new PluginReportsDateIntervalCriteria($report, 'buy_date', __('Date of purchase'));
new PluginReportsDateIntervalCriteria($report, 'delivery_date', __('Delivery date'));
new PluginReportsDateIntervalCriteria($report, 'use_date', __('Startup date'));
new PluginReportsDateIntervalCriteria($report, 'inventory_date', __('Date of last physical inventory'));
new PluginReportsTextCriteria($report, 'immo_number', __('Immobilization number'));
new PluginReportsTextCriteria($report, 'order_number', __('Order number'));
new PluginReportsTextCriteria($report, 'delivery_number', __('Delivery form'));
new PluginReportsDropdownCriteria($report, 'budgets_id', 'glpi_budgets', __('Budget'));

//Display criterias form is needed
$report->displayCriteriasForm();

//If criterias have been validated
if ($report->criteriasValidated()) {

   // Report title
   $report->setSubNameAuto();

   // Report Columns
   $cols = array(new PluginReportsColumnType('itemtype', __('Type')),
                 new PluginReportsColumnTypeLink('items_id', __('Item'), 'itemtype',
                                                 array('with_comment' => 1)),
                 new PluginReportsColumnDate('order_date', __('Order date')),
                 new PluginReportsColumn('order_number', __('Order number')),
                 new PluginReportsColumnDate('buy_date', __('Date of purchase')),
                 new PluginReportsColumn('delivery_date', __('Delivery date')),
                 new PluginReportsColumn('delivery_number', __('Delivery form')),
                 new PluginReportsColumn('immo_number', __('Immobilization number')),
                 new PluginReportsColumnDate('use_date', __('Startup date')),
                 new PluginReportsColumnDate('inventory_date', __('Date of last physical inventory')),
                 new PluginReportsColumnLink('budgets_id', __('Budget'), 'Budget'));

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