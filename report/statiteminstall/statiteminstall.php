<?php
/*	----------------------------------------------------------------------
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

//	Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE=1;
$DBCONNECTION_REQUIRED=1;

// Initialization of the variables
define('GLPI_ROOT',  '../../../..');
include (GLPI_ROOT . "/inc/includes.php");

$report = new PluginReportsAutoReport($LANG['plugin_reports']['statiteminstall'][1]);

//Report's search criterias
$date = new PluginReportsDateIntervalCriteria($report, 'buy_date');
$type = new PluginReportsItemTypeCriteria($report, '', '', 'infocom_types');

//Display criterias form is needed
$report->displayCriteriasForm();

//If criterias have been validated
if ($report->criteriasValidated()) {
   $report->setSubNameAuto();
   echo "<div align='left'><pre>";

   $itemtype = $type->getParameterValue();
   if ($itemtype) {
      $types = array($itemtype);
   } else {
      $types = array();
      $sql = "SELECT DISTINCT `itemtype`
              FROM `glpi_infocoms` ".
              getEntitiesRestrictRequest('WHERE','glpi_infocoms').
              $date->getSqlCriteriasRestriction('AND');
      foreach ($DB->request($sql) as $data) {
         $types[] = $data['itemtype'];
      }
   }

   foreach ($types as $type) {
      $result[$type] = array();
      // Total of buy equipment
      $crit = "itemtype='$type'".
              getEntitiesRestrictRequest('AND','glpi_infocoms').
              $date->getSqlCriteriasRestriction('AND');

      $result[$type]['buy'] = countElementsInTable('glpi_infocoms', $crit);

      for ($deb=0 ; $deb<12 ; $deb=$fin) {
         $fin = $deb+2;
         $crit2 = $crit;
         if ($deb) {
            $crit2 .= " AND `use_date` >= DATE_ADD(`buy_date`, INTERVAL $deb MONTH) ";
         }
         if ($fin) {
            $crit2 .= " AND `use_date` < DATE_ADD(`buy_date`, INTERVAL $fin MONTH) ";
         }
         $result[$type]["$deb-$fin"] = countElementsInTable('glpi_infocoms', $crit2);
      }
      $crit2 = $crit;
      $crit2 .= " AND (`use_date` IS NULL OR `use_date` >= DATE_ADD(`buy_date`, INTERVAL 12 MONTH))";
      $result[$type]['12+'] = countElementsInTable('glpi_infocoms', $crit2);
   }

echo "RESULT:";   print_r($result);
   echo "</pre></div>";
/*
   //Names of the columns to be displayed
   $cols = array (new PluginReportsColumn('name', $LANG['entity'][0],
                                           array('sorton' => '`glpi_entities`.`completename`')),
                  new PluginReportsColumnInteger('nbusers', $LANG['plugin_reports']['ticketsbyentity'][5],
                                                 array('withtotal' => true,
                                                       'sorton'    => 'nbusers')),
                  new PluginReportsColumnInteger('number', $LANG['plugin_reports']['ticketsbyentity'][2],
                                                 array('withtotal' => true,
                                                       'sorton'    => 'number')),
                  new PluginReportsColumnDateTime('mindate', $LANG['plugin_reports']['ticketsbyentity'][3],
                                                  array('sorton' => 'mindate')),
                  new PluginReportsColumnDateTime('maxdate', $LANG['plugin_reports']['ticketsbyentity'][4],
                                                  array('sorton' => 'maxdate')));
   $report->setColumns($cols);

   $subcpt = "SELECT COUNT(*)
              FROM `glpi_profiles_users`
              WHERE `glpi_profiles_users`.`entities_id`=`glpi_entities`.`id` ".
              $prof->getSqlCriteriasRestriction();

   $query = "SELECT `glpi_entities`.`completename` AS name,
                    ($subcpt) as nbusers,
                    COUNT(`glpi_tickets`.`id`) AS number,
                    MIN(`glpi_tickets`.`date`) as mindate,
                    MAX(`glpi_tickets`.`date`) as maxdate
             FROM `glpi_entities`
             INNER JOIN `glpi_tickets` ON (`glpi_tickets`.`entities_id`=`glpi_entities`.`id`)".
             getEntitiesRestrictRequest(" WHERE ", "glpi_entities") .
            "GROUP BY `glpi_entities`.`id`".
            $report->getOrderBy('name');

   $report->setSqlRequest($query);
   $report->execute(array('withtotal'=>true));
*/
}
commonFooter();

?>
