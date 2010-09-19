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
 * Original Author of file: Nelly Lasson
 *
 * Purpose of file:
 *       Generate location report
 *       Illustrate use of simpleReport
 * ----------------------------------------------------------------------
 */

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE=1;
$DBCONNECTION_REQUIRED=0; // Really a big SQL request

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");

$report = new PluginReportsAutoReport();
$loc = new PluginReportsLocationCriteria($report);

$report->setColumns(array(new PluginReportsColumnType('itemtype', $LANG['common'][17]),
                          new PluginReportsColumnTypeLink('items_id', $LANG['common'][1], 'itemtype', array('with_comment'=>1)),
                          new PluginReportsColumn('serial',$LANG['common'][19]),
                          new PluginReportsColumn('otherserial', $LANG['common'][20]),
                          ));

//Display criterias form is needed
$report->displayCriteriasForm();

//If criterias have been validated
if ($report->criteriasValidated()) {
   $report->setSubNameAuto();

   $query= getSqlSubRequest("Computer",$loc);
   foreach(array('Monitor','Printer','Peripheral','NetworkEquipment','Phone') as $itemtype) {
      $query.= "UNION (".getSqlSubRequest($itemtype,$loc).")";
   }
   $report->setGroupBy("entity");
   $report->setSqlRequest($query);
   $report->execute();
}
else {
   commonFooter();
}

function getSqlSubRequest($itemtype,$loc) {
   $query_where = "";
   $table = getTableForItemType($itemtype);
   $models_id = getForeignKeyFieldForTable(getTableForItemType($itemtype.'Model'));
   $types_id = getForeignKeyFieldForTable(getTableForItemType($itemtype.'Type'));
   $query_where.="SELECT '$itemtype' as itemtype, `$table`.`id` as items_id, `$table`.`locations_id`,
                           `$table`.`name` AS name,`$table`.`serial` AS serial,
                            `$table`.`$models_id` AS models_id,
                               `$table`.`$types_id` AS types_id,
                              `$table`.`otherserial` AS otherserial
                  FROM `$table`
                  WHERE `is_deleted`='0' AND `is_template`='0' ";
   $query_where.= $loc->getSqlCriteriasRestriction();
   return $query_where;
}
?>