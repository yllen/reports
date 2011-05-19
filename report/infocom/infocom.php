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
 * 		Big UNION to have a report including all inventory
 *
 * ----------------------------------------------------------------------
 */

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE=1;
$DBCONNECTION_REQUIRED=0; // Really a big SQL request

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");


/*
 * TODO : add more criteria
 *
 * - num_immo not empry
 * - otherserial not empty
 * - etc
 *
 */

//logDebug($_REQUEST);
$report = new PluginReportsAutoReport();

$ignored = array('Software', 'CartridgeItem', 'ConsumableItem', 'Consumable', 'Cartridge');

$type = new PluginReportsItemTypeCriteria($report, '', '', 'infocom_types', $ignored);
$budg = new PluginReportsDropdownCriteria($report, '`glpi_infocoms`.`budgets_id`', 'glpi_budgets', $LANG['financial'][87]);

//Display criterias form is needed
$report->displayCriteriasForm();

//If criterias have been validated
if ($report->criteriasValidated()) {
   $report->setSubNameAuto();

   $cols = array(
      new PluginReportsColumnType('itemtype', $LANG["state"][6]),
      new PluginReportsColumn('manufacturer', $LANG["common"][5]),
      new PluginReportsColumn('type', $LANG["common"][17]),
      new PluginReportsColumn('model', $LANG["common"][22]),
      new PluginReportsColumnTypeLink('itemid', $LANG["common"][16], 'itemtype'),
      new PluginReportsColumn('serial', $LANG["common"][19]),
      new PluginReportsColumn('otherserial', $LANG["common"][20]),
      new PluginReportsColumn('location', $LANG["common"][15]),
      new PluginReportsColumn('building', $LANG["setup"][99]),
      new PluginReportsColumn('room', $LANG["setup"][100]),
      new PluginReportsColumnLink('groups_id', $LANG["common"][35], 'Group'),
      new PluginReportsColumn('state', $LANG["joblist"][0]),
      new PluginReportsColumn('immo_number', $LANG["financial"][20]),
      new PluginReportsColumnDate('buy_date', $LANG["financial"][14]),
      new PluginReportsColumnDate('use_date', $LANG["financial"][76]),
      new PluginReportsColumnDate('warranty_date', $LANG["financial"][29]),
      new PluginReportsColumnInteger('warranty_duration', $LANG["financial"][15]),
      new PluginReportsColumnInteger('warranty_info', $LANG["financial"][16]),
      new PluginReportsColumnLink('suppliers_id', $LANG["financial"][26], "Supplier"),
      new PluginReportsColumnDate('order_date', $LANG["financial"][28]),
      new PluginReportsColumn('order_number', $LANG["financial"][18]),
      new PluginReportsColumnDate('delivery_date', $LANG["financial"][27]),
      new PluginReportsColumn('delivery_number', $LANG["financial"][19]),
      new PluginReportsColumnFloat('value', $LANG["financial"][21]),
      new PluginReportsColumnFloat('warranty_value', $LANG["financial"][78]),
      new PluginReportsColumnInteger('sink_time', $LANG["financial"][23]),
      new PluginReportsColumnInteger('sink_type', $LANG["financial"][22]),
      new PluginReportsColumnFloat('sink_coeff', $LANG["financial"][77]),
      new PluginReportsColumn('bill', $LANG["financial"][82]),
      new PluginReportsColumn('budget', $LANG["financial"][87]),
      new PluginReportsColumnDate('inventory_date', $LANG["financial"][114]),
   );

   $report->setColumns($cols);
   $sel = $type->getParameterValue();
   if ($sel) {
      $types = array($sel);
   } else {
      $types = array_diff($CFG_GLPI['infocom_types'], $ignored);
   }

   $sql = '';
   foreach ($types as $itemtype) {
      $item = new $itemtype;
      $table = $item->getTable();

      $select = "SELECT '$itemtype' as itemtype,
                        `$table`.id AS itemid";

      $from = "FROM `$table` ";
      if ($itemtype == 'SoftwareLicense') {
         $select .= ", `glpi_manufacturers`.`name` AS manufacturer";
         $from .= "LEFT JOIN `glpi_softwares`
                        ON (`glpi_softwarelicenses`.`softwares_id` = `glpi_softwares`.`id`)
                   LEFT JOIN `glpi_manufacturers`
                        ON (`glpi_manufacturers`.`id` = `glpi_softwares`.`manufacturers_id`) ";
      } else if ($item->isField('manufacturers_id')) {
         $select .= ", `glpi_manufacturers`.`name` AS manufacturer";
         $from .= "LEFT JOIN `glpi_manufacturers`
                        ON (`glpi_manufacturers`.`id` = `$table`.`manufacturers_id`) ";
      } else {
         $select .= ", '' AS manufacturer";
      }

      $typeclass = $itemtype.'Type';
      $typetable = getTableForItemType($typeclass);
      if (TableExists($typetable)) {
         $typeitem  = new $typeclass;
         $typefkey  = $typeitem->getForeignKeyField();

         $select .= ", `$typetable`.`name` AS type";
         $from .= "LEFT JOIN `$typetable`
                        ON (`$typetable`.`id` = `$table`.`$typefkey`) ";
      } else {
         $select .= ", '' AS type";
      }

      $modelclass = $itemtype.'Model';
      $modeltable = getTableForItemType($modelclass);
      if ($itemtype == 'SoftwareLicense') {
         $select .= ", CONCAT(glpi_softwares.name,' ',buyversion.name) AS model";
         $from .= "LEFT JOIN `glpi_softwareversions` AS buyversion
                          ON (buyversion.`id` = `glpi_softwarelicenses`.`softwareversions_id_buy`) ";
      } else if (TableExists($modeltable)) {
         $modelitem  = new $modelclass;
         $modelitem  = $modelitem->getForeignKeyField();

         $select .= ", `$modeltable`.`name` AS model";
         $from .= "LEFT JOIN `$modeltable`
                        ON (`$modeltable`.`id` = `$table`.`$modelitem`) ";
      } else {
         $select .= ", '' AS model";
      }
      if ($item->isField('serial')) {
         $select .= ", `$table`.`serial`";
      } else {
         $select .= ", '' AS `serial`";
      }
      if ($item->isField('otherserial')) {
         $select .= ", `$table`.`otherserial`";
         $where = "WHERE (`$table`.`otherserial` != ''
                          OR `glpi_infocoms`.`immo_number` !='') ";
      } else {
         $select .= ", '' AS `otherserial`";
         $where = "WHERE 1 ";
      }
      if ($item->isField('groups_id')) {
         $select .= ", `$table`.`groups_id`";
      } else {
         $select .= ", 0 AS `groups_id`";
      }
      if ($item->isField('states_id')) {
         $select .= ", `glpi_states`.`name` AS state";
         $from .= "LEFT JOIN `glpi_states`
                         ON (`glpi_states`.`id` = `$table`.`states_id`)";
      } else {
         $select .= ", '' AS `state`";
      }
      if ($item->isField('locations_id')) {
         $select .= ", `glpi_locations`.`completename` AS location
                     , `glpi_locations`.`building`
                     , `glpi_locations`.`room`";
         $from .= "LEFT JOIN `glpi_locations`
                         ON (`glpi_locations`.`id` = `$table`.`locations_id`)";
      } else {
         $select .= ", '' AS location, '' AS building, '' AS room";
      }
      $select .= ", `glpi_infocoms`.*
                  , `glpi_infocoms`.`suppliers_id` AS supplier
                  , `glpi_budgets`.`name` AS budget";
      $from .= "LEFT JOIN `glpi_infocoms`
                      ON (`glpi_infocoms`.`itemtype` = '$itemtype'
                      AND `glpi_infocoms`.`items_id` = `$table`.`id`)
                LEFT JOIN `glpi_budgets`
                      ON (`glpi_budgets`.`id` = `glpi_infocoms`.`budgets_id`)";

      if ($item->maybeDeleted()) {
         $where .= " AND `$table`.`is_deleted` = 0 ";
      }
      if ($item->maybeTemplate()) {
         $where .= " AND `$table`.`is_template` = 0 ";
      }
      if ($item->isEntityAssign()) {
         $where .= getEntitiesRestrictRequest(" AND ", $table);
      }

      $where .= $budg->getSqlCriteriasRestriction();

      if ($sql) {
         $sql .= " UNION ";
      }
      $sql .= "($select $from $where)";
   }
   $report->setGroupBy('entity');
   $report->setSqlRequest($sql);
   $report->execute();

} else {
   commonFooter();
}
?>