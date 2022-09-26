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

$USEDBREPLICATE        = 1;
$DBCONNECTION_REQUIRED = 0;

include ("../../../../inc/includes.php");


//TRANS: The name of the report = List of equipments by location
$report = new PluginReportsAutoReport(__('listequipmentbylocation_report_title', 'reports'));
$loc    = new PluginReportsLocationCriteria($report);


$ignored = ['Cartridge', 'CartridgeItem', 'Consumable', 'ConsumableItem', 'Software', 'Line',
            'Certificate', 'Appliance', 'Domain', 'Item_DeviceSimcard', 'SoftwareLicense'];

$report->setColumns([new PluginReportsColumnType('itemtype', __('Type'), $ignored),
                     new PluginReportsColumnTypeLink('items_id', __('Item'), 'itemtype',
                                                     ['with_comment' => 1]),
                     new PluginReportsColumn('statename', __('Status')),
                     new PluginReportsColumn('serial', __('Serial number')),
                     new PluginReportsColumn('otherserial', __('Inventory number')),
                     new PluginReportsColumnModelType('models_id', __('Model'), 'itemtype',
                                                      ['with_comment' => 1]),
                     new PluginReportsColumnTypeType('types_id', __('Type'), 'itemtype',
                                                     ['with_comment' => 1])]);

//Display criterias form is needed
$report->displayCriteriasForm();

//If criterias have been validated
if ($report->criteriasValidated() && ($loc->getParameterValue() != 0)) {
      $report->setSubNameAuto();

      $query = getSqlSubRequest("Computer", $loc, new Computer());
      foreach($CFG_GLPI["infocom_types"] as $itemtype) {
         $obj = new $itemtype;
         if ($obj->isField('locations_id') && ($itemtype != "Computer")) {
            $query.= "UNION (".getSqlSubRequest($itemtype,$loc,$obj).")";
         }
      }

      $report->setGroupBy("entity","itemtype");
      $report->setSqlRequest($query);
      $report->execute();

} else {
   echo "<p class='red center'>". __('Location not selected', 'reports')."</p>";
   Html::footer();
}



function getSqlSubRequest($itemtype,$loc,$obj) {

   $dbu = new DbUtils();

   $table     = $dbu->getTableForItemType($itemtype);
   $models_id = $dbu->getForeignKeyFieldForTable($dbu->getTableForItemType($itemtype.'Model'));
   $types_id  = $dbu->getForeignKeyFieldForTable($dbu->getTableForItemType($itemtype.'Type'));
   $fields    = ['name'        => 'name',
                 'serial'      => 'serial',
                 'otherserial' => 'otherserial',
                 'states_id'   => 'states_id',
                 $models_id    => 'models_id',
                 $types_id     => 'types_id'];

   $query_where = "SELECT '$itemtype' AS itemtype,
                          `$table`.`id` AS items_id,
                          `$table`.`locations_id`";

   $join = "";
   foreach ($fields as $field => $alias) {
      if ($obj->isField($field)) {
         if ($field == 'states_id') {
            $query_where .= ", `glpi_states`.`name` as statename";
            $join .= " LEFT JOIN `glpi_states`ON `glpi_states`.`id` = `$table`.`states_id` ";
         } else {
            $query_where .= ", `$table`.`$field` AS $alias";
         }
      } else {
         $query_where .= ", '' AS $alias";
      }
   }


   $query_where .= " FROM `$table`
                   $join ";

   if ($obj->isEntityAssign()) {
      $query_where .= $dbu->getEntitiesRestrictRequest('WHERE', "$table");
   } else {
      $query_where .= 'WHERE 1';
   }

   if ($obj->maybeTemplate()) {
      $query_where .= " AND `is_template`='0'";
   }

   if ($obj->maybeDeleted()) {
      $query_where .= " AND `is_deleted`='0'";
   }

   $query_where .= $loc->getSqlCriteriasRestriction();

   return $query_where;
}
