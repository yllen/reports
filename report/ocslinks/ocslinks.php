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
 * Original Author of file: Remi Collet
 *
 * Purpose of file:
 * 		Generate location report
 * 		Illustrate use of simpleReport
 * ----------------------------------------------------------------------
 */

$USEDBREPLICATE=1;
$DBCONNECTION_REQUIRED=0; // Not really a big SQL request

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");

$servers = array();
$crit = array('FIELDS'    => array('id', 'name'),
              'is_active' => 1);
foreach ($DB->request('glpi_ocsservers', $crit) as $data) {
   $servers[$data['id']] = $data['name'];
}
if (count($servers)<1) {
   displayErrorAndDie($LANG['ocsng'][27]);
}

// Instantiate Report with Name
$report   = new PluginReportsAutoReport();
//$critdate = new PluginReportsDateIntervalCriteria($report, 'last_update');
$critid   = new PluginReportsTextCriteria($report, 'ocsid', $LANG['ocsng'][45]);
$critdev  = new PluginReportsTextCriteria($report, 'ocs_deviceid', $LANG['plugin_reports']['ocslinks'][3]);
$critserv = new PluginReportsArrayCriteria($report, 'ocsservers_id', $LANG['ocsng'][29], $servers);

//Display criterias form is needed
$report->displayCriteriasForm();

//If criterias have been validated
if ($report->criteriasValidated()
    && isset($_REQUEST['ocsservers_id'])
    && $_REQUEST['ocsservers_id']
    && ($_REQUEST['ocsid'] || $_REQUEST['ocs_deviceid'] )) {

   $cols = array(
      new PluginReportsColumnInteger('ocsid', $LANG['ocsng'][45]),
      new PluginReportsColumnInteger('computers_id', $LANG['common'][2]),
      new PluginReportsColumnLink('cid', $LANG['common'][16], 'Computer'),
      new PluginReportsColumn('ocs_deviceid', $LANG['plugin_reports']['ocslinks'][3]),
      new PluginReportsColumnDateTime('last_update', $LANG['ocsng'][13]),
      new PluginReportsColumnDateTime('last_ocs_update', $LANG['ocsng'][14]),
      new PluginReportsColumn('ocs_agent_version', $LANG['ocsng'][49]),
   );
   $report->setColumns($cols);


   $report->setSubNameAuto();

   $id = $critid->getParameterValue();
   $dev = trim($critdev->getParameterValue());

   $report->delCriteria('ocsid');
   $report->delCriteria('ocs_deviceid');

   $restrict = $report->addSqlCriteriasRestriction('WHERE');
   if (intval($id)) {
      $restrict .= (empty($restrict) ? ' WHERE ' : ' AND ');
      $restrict .= "`ocsid`=".intval($id);

   } else if ($dev) {
      $restrict .= (empty($restrict) ? ' WHERE ' : ' AND ');
      $restrict .= "`ocs_deviceid` LIKE '$dev%'";
   }

   $query = "SELECT `glpi_ocslinks`.`ocsid`,
                    `glpi_ocslinks`.`computers_id`,
                    `glpi_ocslinks`.`computers_id` AS cid,
                    `glpi_ocslinks`.`ocs_deviceid`,
                    `glpi_ocslinks`.`last_update`,
                    `glpi_ocslinks`.`last_ocs_update`,
                    `glpi_ocslinks`.`ocs_agent_version`
             FROM `glpi_ocslinks`
             $restrict";

   $report->setSqlRequest($query);
   $report->execute(array('withmassiveaction' => 'User'));

} else {
   echo "<p class='center red b'>".$LANG['plugin_reports']['ocslinks'][2]."</p>";
   commonFooter();
}
