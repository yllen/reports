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

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE=1;
$DBCONNECTION_REQUIRED=0; // Really a big SQL request

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");

$report = new PluginReportsAutoReport();

//Report's search criterias
new PluginReportsDateIntervalCriteria($report, 'buy_date');
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
   $cols = array (new PluginReportsColumnType('itemtype', $LANG['common'][17]),
                  new PluginReportsColumnTypeLink('items_id', $LANG['common'][1], 'itemtype', array('with_comment'=>1)),
                  new PluginReportsColumnDate('buy_date', $LANG['financial'][14]),
                  new PluginReportsColumnDate('use_date', $LANG['financial'][76]),
                  new PluginReportsColumn('immo_number', $LANG['financial'][20]),
                  new PluginReportsColumn('order_number', $LANG['financial'][18]),
                  new PluginReportsColumn('delivery_number', $LANG['financial'][19]),
                  new PluginReportsColumnLink('budgets_id', $LANG['financial'][87], 'Budget')
                  );
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
   commonFooter();
}
?>