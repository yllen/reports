<?php
/*	----------------------------------------------------------------------
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

//	Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE=1;
$DBCONNECTION_REQUIRED=1;

// Initialization of the variables
define('GLPI_ROOT',  '../../../..');
include (GLPI_ROOT . "/inc/includes.php");

$report = new PluginReportsAutoReport($LANG['plugin_reports']['statticketsbyentity'][1]);

//Report's search criterias
$prof = new PluginReportsDropdownCriteria($report,'profiles_id','glpi_profiles',$LANG['profiles'][22]);

//Display criterias form is needed
$report->displayCriteriasForm();

//If criterias have been validated
if ($report->criteriasValidated()) {
   $report->setSubNameAuto();

   //Names of the columns to be displayed
   $cols = array (new PluginReportsColumn('name', $LANG['entity'][0],
                                           array('sorton' => '`glpi_entities`.`completename`')),
                  new PluginReportsColumnInteger('nbusers', $LANG['plugin_reports']['statticketsbyentity'][5],
                                                 array('withtotal' => true,
                                                       'sorton'    => 'nbusers')),
                  new PluginReportsColumnInteger('number', $LANG['plugin_reports']['statticketsbyentity'][2],
                                                 array('withtotal' => true,
                                                       'sorton'    => 'number')),
                  new PluginReportsColumnDateTime('mindate', $LANG['plugin_reports']['statticketsbyentity'][3],
                                                  array('sorton' => 'mindate')),
                  new PluginReportsColumnDateTime('maxdate', $LANG['plugin_reports']['statticketsbyentity'][4],
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

} else {
   commonFooter();
}

?>
