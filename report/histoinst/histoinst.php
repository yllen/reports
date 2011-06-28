<?php
/*
 * @version $Id: HEADER 14684 2011-06-11 06:32:40Z remi $
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

// Original Author of file: Remi Collet
// Purpose of file:
// ----------------------------------------------------------------------

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE = 1;
$DBCONNECTION_REQUIRED = 1; // Really a big SQL request

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");

includeLocales("histoinst");

plugin_reports_checkRight('reports', "histoinst","r");
$computer = new Computer();
$computer->checkGlobal('r');
$software = new Software();
$software->checkGlobal('r');

commonHeader($LANG['plugin_reports']['histoinst'][1],$_SERVER['PHP_SELF'],"utils","report");

echo "<div class='center'>";
echo "<table class='tab_cadrehov' cellpadding='5'>\n";
echo "<tr class='tab_bg_1 center'><th colspan='4'>" . $LANG['plugin_reports']['histoinst'][1] .
      "</th></tr>\n";

echo "<tr class='tab_bg_2'><th>". $LANG['plugin_reports']['histoinst'][2] . "</th>" .
      "<th>". $LANG['plugin_reports']['histoinst'][3] . "</th>".
      "<th>". $LANG['plugin_reports']['histoinst'][4] . "</th>".
      "<th>". $LANG['plugin_reports']['histoinst'][5] . "</th></tr>\n";

$sql = "SELECT a.`date_mod` AS dat, a.`new_value`, `glpi_computers`.`id` AS cid, `name`,
               a.`user_name`
        FROM (SELECT `date_mod`, `new_value`, `user_name`, `items_id`, `id`
              FROM `glpi_logs`
              WHERE `glpi_logs`.`date_mod` > DATE_SUB(Now(), INTERVAL 21 DAY)
                    AND `linked_action` = '" .HISTORY_INSTALL_SOFTWARE ."'
                    AND `itemtype` = 'Computer') a
        LEFT JOIN `glpi_computers` ON (a.`items_id` = `glpi_computers`.`id`)
        WHERE `glpi_computers`.`entities_id` = '" . $_SESSION["glpiactive_entity"] ."'
        ORDER BY a.`id` DESC
        LIMIT 0,200";
$result = $DB->query($sql);

$prev = "";
$class = "tab_bg_2";
while ($data = $DB->fetch_array($result)) {
   if ($prev == $data["dat"].$data["name"]) {
      echo "<br />";
   } else {
      if (!empty($prev)) {
         echo "</td></tr>\n";
      }
      $prev = $data["dat"].$data["name"];
      echo "<tr class='" . $class . " top'><td class='center'>". convDateTime($data["dat"]) . "</td>" .
            "<td>". $data["user_name"] . "&nbsp;</td>".
            "<td><a href='". getItemTypeFormURL('Computer') . "?id=" . $data["cid"] . "'>" .
            $data["name"] . "</a></td>".
            "<td>";
      $class = ($class=="tab_bg_2" ? "tab_bg_1" : "tab_bg_2");
   }
   echo $data["new_value"];
}

if (!empty($prev)) {
   echo "</td></tr>\n";
}
echo "</table><p>". $LANG['plugin_reports']['histoinst'][6]."</p></div>\n";

commonFooter();

?>