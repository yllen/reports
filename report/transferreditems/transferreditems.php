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

// ----------------------------------------------------------------------
// Original Author of file: Stéphane SAVONA
// ----------------------------------------------------------------------
$USEDBREPLICATE= 1;
$DBCONNECION_REQUIRED= 0;

define('GLPI_ROOT', '../../../..');
include(GLPI_ROOT."/inc/includes.php");

// Instanciation
$report= new PluginReportsAutoReport();

// Search criterias
new PluginReportsDateIntervalCriteria($report, "`glpi_logs`.`date_mod`");

$types = array();
foreach (array('Computer', 'NetworkEquipment', 'Printer', 'Monitor','Peripheral',
               'Software', 'Phone','Software','SoftwareLicense') as $type) {
   $label = call_user_func(array($type,'getTypeName'));
   $types[$type] = $label;
}
ksort($types);
$typecritera = new PluginReportsItemTypeCriteria($report,"itemtype",$LANG['common'][17], $types);

$report->displayCriteriasForm();

// Declare columns
if($report->criteriasValidated()) {
   $itemtype = $_POST['itemtype'];
   $table = getTableForItemType($itemtype);

   $columns = array(new PluginReportsColumnLink('items_id',$LANG['common'][16],
                                                $itemtype, array('with_comment'=>1)),
                    new PluginReportsColumn('otherserial',$LANG['common'][20]),
                    new PluginReportsColumnDateTime('datemod',$LANG['plugin_reports']['transferreditems'][2]),
                    new PluginReportsColumn('old_value',$LANG['plugin_reports']['transferreditems'][3]),
                    new PluginReportsColumn('new_value',$LANG['plugin_reports']['transferreditems'][4]));
   $report->setColumnsNames($columns);
   $query= "SELECT `$table`.`id` as `items_id`,
                   `$table`.`name`,
                   `$table`.`otherserial`,
                   `$table`.`date_mod` as `date_mod`,
                   `glpi_logs`.`itemtype` as `itemtype`,
                   `glpi_logs`.`old_value`,
                   `glpi_logs`.`new_value`
            FROM `$table`, `glpi_logs` ";
   $query .= $report->addSqlCriteriasRestriction("WHERE");
   $query .= " AND `glpi_logs`.`items_id` = `$table`.`id`
                  AND `glpi_logs`.`itemtype` = '$itemtype'
                     AND `glpi_logs`.`id_search_option`='80'";
   //   $query.= " AND `glpi_logs`.`old_value` <> '".$LANG['entity'][2]."'";
   $query .= " ORDER BY `date_mod` ASC";
   $report->setSqlRequest($query);
   $report->execute();
}
?>