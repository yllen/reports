<?php
/*
 * @version $Id$
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
 * Original Author of file: Nelly Lasson
 *
 * Purpose of file:
 *       Generate location report
 *       Illustrate use of simpleReport
 * ----------------------------------------------------------------------
 */

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE        = 1;
$DBCONNECTION_REQUIRED = 0;

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");

$report = new PluginReportsAutoReport();
$loc = new PluginReportsLocationCriteria($report);

$report->setColumns(array(new PluginReportsColumnType('itemtype', $LANG['common'][17]),
                          new PluginReportsColumnTypeLink('items_id', $LANG['common'][1],
                                                          'itemtype', array('with_comment' => 1)),
                          new PluginReportsColumn('serial', $LANG['common'][19]),
                          new PluginReportsColumn('otherserial', $LANG['common'][20]),
                          new PluginReportsColumnModelType('models_id', $LANG['common'][22],
                                                           'itemtype', array('with_comment' => 1)),
                          new PluginReportsColumnTypeType('types_id', $LANG['common'][17],
                                                          'itemtype', array('with_comment' => 1))));

//Display criterias form is needed
$report->displayCriteriasForm();

//If criterias have been validated
if ($report->criteriasValidated()) {
   $report->setSubNameAuto();

   $query = getSqlSubRequest("Computer",$loc,new Computer());
   foreach($CFG_GLPI["infocom_types"] as $itemtype) {
      $obj = new $itemtype;
      if ($obj->isField('locations_id')) {
         $query.= "UNION (".getSqlSubRequest($itemtype,$loc,$obj).")";
      }
   }
   $report->setGroupBy("entity","itemtype");
   $report->setSqlRequest($query);
   $report->execute();
}
else {
   Html::footer();
}


function getSqlSubRequest($itemtype,$loc,$obj) {

   $table     = getTableForItemType($itemtype);
   $models_id = getForeignKeyFieldForTable(getTableForItemType($itemtype.'Model'));
   $types_id  = getForeignKeyFieldForTable(getTableForItemType($itemtype.'Type'));
   $fields    = array('name'        => 'name',
                      'serial'      => 'serial',
                      'otherserial' => 'otherserial',
                      $models_id    => 'models_id',
                      $types_id     => 'types_id');

   $query_where = "SELECT '$itemtype' AS itemtype,
                          `$table`.`id` AS items_id,
                          `$table`.`locations_id`";

   foreach ($fields as $field => $alias) {
      if ($obj->isField($field)) {
         $query_where .= ", `$table`.`$field` AS $alias";
      } else {
         $query_where .= ", '' AS $alias";
      }
   }

   $query_where .= " FROM `$table` ";

   if ($obj->isEntityAssign()) {
      $query_where .= getEntitiesRestrictRequest('WHERE', "$table");
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
?>