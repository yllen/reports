<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 reports - Additional reports plugin for GLPI
 Copyright (C) 2003-2011 by the reports Development Team.

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

// Original Author of file: Remi Collet
// Purpose of file: Generate "doublons" report
// ----------------------------------------------------------------------

define('GLPI_ROOT', '../../../..');

include (GLPI_ROOT . "/inc/includes.php");

Plugin::load('reports');

Session::checkRight("profile","w");

Html::header($LANG['plugin_reports']['doublons'][1], $_SERVER['PHP_SELF'], "config", "plugins");

$types = array(1 => $LANG["networking"][15], // Mac
               2 => $LANG["networking"][14], // IP
               3 => $LANG["common"][19]);    // Serial

if (isset($_GET["delete"])) {
   $query = "DELETE
             FROM `glpi_plugin_reports_doublons_backlists`
             WHERE `id` = '".$_GET["delete"]."'";
   $DB->query($query);

} else if (isset($_POST["add"])
           && isset($_POST["type"])
           && isset($_POST["addr"])
           && strlen($_POST["addr"])) {

   $query = "INSERT INTO `glpi_plugin_reports_doublons_backlists`
             SET `type` = '".$_POST["type"]."',
                 `addr` = '".trim($_POST["addr"])."',
                 `comment` = '".trim($_POST["comment"])."'";
   $DB->query($query);
}

// Initial creation
$migration = new Migration(160);
if (TableExists("glpi_plugin_reports_doublons_backlist")) {
   $migration->renameTable("glpi_plugin_reports_doublons_backlist",
                           "glpi_plugin_reports_doublons_backlists");

   $migration->changeField("glpi_plugin_reports_doublons_backlists", "ID", "id", 'autoincrement');

   $migration->executeMigration();

} else if (!TableExists("glpi_plugin_reports_doublons_backlists")) {
   $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_reports_doublons_backlists` (
               `id` int(11) NOT NULL AUTO_INCREMENT,
               `type` int(11) NOT NULL DEFAULT '0',
               `addr` varchar(255) DEFAULT NULL,
               `comment` varchar(255) DEFAULT NULL,
               PRIMARY KEY (`id`)
             ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
   $DB->query($query) or die($DB->error());

   $query = "INSERT INTO`glpi_plugin_reports_doublons_backlists`
                    (`type`, `addr`, `comment`)
             VALUES (1, '44:45:53:54:42:00', 'Nortel IPSECSHM Adapter'),
                    (1, 'BA:D0:BE:EF:FA:CE', 'GlobeTrotter Module 3G+ Network Card'),
                    (1, '00:53:45:00:00:00', 'WAN (PPP/SLIP) Interface'),
                    (1, '80:00:60:0F:E8:00', 'Windows Mobile-based'),
                    (2, '127.0.0.1', 'loopback'),
                    (3, 'INVALID', 'from OCSNG'),
                    (3, 'XxXxXxX', 'from IBM')";
   $DB->query($query);
}

// ---------- Form ------------
echo "<div class='center'><table class='tab_cadre' cellpadding='5'>\n";
echo "<tr class='tab_bg_1 center'><th><a href='".GLPI_ROOT."/plugins/reports/front/config.form.php'>".
      $LANG['plugin_reports']['config'][1] . "</a><br />&nbsp;<br />" .
      $LANG['plugin_reports']['config'][11] . " : " . $LANG['plugin_reports']['doublons'][1] .
      "</th></tr>\n";

$plug = new Plugin();
if ($plug->isActivated('reports')) {
   echo "<tr class='tab_bg_1 center'><td>";
   echo "<a href='./doublons.php'>" .$LANG['plugin_reports']['config'][10] . " - " .
         $LANG['plugin_reports']['doublons'][1] . "</a></td></tr>\n";
}

echo "</table>\n";

echo "<form action='".$_SERVER["PHP_SELF"]."' method='post'><br />" .
   "<table class='tab_cadre' cellpadding='5'>\n" .
   "<tr class='tab_bg_1 center'><th colspan='4'>" . $LANG['plugin_reports']['doublons'][4] .
   "</th></tr>\n" .
   "<tr class='tab_bg_1 center'><th>" . $LANG["common"][17] . "</th><th>" .
      $LANG["networking"][14]."/".$LANG["networking"][15] . "</th>" .
   "<th>" . $LANG["common"][25] . "</th><th>&nbsp;</th></tr>\n";

$query = "SELECT *
          FROM `glpi_plugin_reports_doublons_backlists`
          ORDER BY `type`, `addr`";
$res = $DB->query($query);

while ($data = $DB->fetch_array($res)) {
   echo "<tr class='tab_bg_1 center'><td>" . $types[$data["type"]] . "</td>" .
      "<td>" . $data["addr"] . "</td><td>" . $data["comment"] . "</td>" .
      "<td><a href='".$_SERVER["PHP_SELF"]."?delete=".$data["id"]."'>".$LANG["buttons"][6].
      "</a></td></tr>\n";
}

echo "<tr class='tab_bg_1 center'><td>";
Dropdown::showFromArray("type", $types);
echo "</td><td><input type='text' name='addr' size='20'></td><td>".
   "<input type='text' name='comment' size='40'></td>" .
   "<td><input type='submit' name='add' value='".$LANG["buttons"][8]."' class='submit' ></td></tr>\n";

echo "</table>\n</form>\n</div>";

Html::footer();
?>