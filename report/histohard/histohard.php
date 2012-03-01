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

// Original Author of file: Benoit Machiavello
// Purpose of file:
// ----------------------------------------------------------------------

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE         = 1;
$DBCONNECTION_REQUIRED  = 1; // Really a big SQL request

define ('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");

includeLocales("histohard");

plugin_reports_checkRight('reports', "histohard","r");
$computer = new Computer();
$computer->checkGlobal('r');

Html::header($LANG['plugin_reports']['histohard'][1],$_SERVER['PHP_SELF'],"utils","report");

Report::title();

echo "<div class='center'>";
echo "<table class='tab_cadrehov'>\n";
echo "<tr class='tab_bg_1 center'><th colspan='5'>" . $LANG['plugin_reports']['histohard'][1] .
      "</th></tr>\n";

echo "<tr><th>". $LANG['plugin_reports']['histohard'][2] . "</th>" .
      "<th>". $LANG["common"][34] . "</th>".
      "<th>". $LANG["help"][25] . "</th>".
      "<th>". $LANG["event"][18] . "</th>".
      "<th>". $LANG['plugin_reports']['histohard'][3] . "</th></tr>\n";

$sql = "SELECT `glpi_logs`.`date_mod` AS dat, `linked_action`, `itemtype`, `itemtype_link`,
               `old_value`, `new_value`, `glpi_computers`.`id` AS cid, `name`, `user_name`
        FROM `glpi_logs`
        LEFT JOIN `glpi_computers` ON (`glpi_logs`.`items_id` = `glpi_computers`.`id`)
        WHERE `glpi_logs`.`date_mod` > DATE_SUB(Now(), INTERVAL 21 DAY)
              AND `itemtype` = 'Computer'
              AND `linked_action` IN (".Log::HISTORY_CONNECT_DEVICE.",
                                      ".Log::HISTORY_DISCONNECT_DEVICE.",
                                      ".Log::HISTORY_DELETE_DEVICE.", " .Log::HISTORY_UPDATE_DEVICE.",
                                      ".Log::HISTORY_ADD_DEVICE.")
              AND `glpi_computers`.`entities_id` = '" . $_SESSION["glpiactive_entity"] ."'
        ORDER BY `glpi_logs`.`id` DESC
        LIMIT 0,100";
$result = $DB->query($sql);

$prev = "";
$class = "tab_bg_2";
while ($data = $DB->fetch_array($result)) {
   if ($prev == $data["dat"].$data["name"]) {
      echo "</td></tr><tr class='" . $prevclass ." top'><td></td><td></td><td></td><td>";
   } else {
      if (!empty($prev)) {
         echo "</td></tr>\n";
      }
      $prev = $data["dat"].$data["name"];
      echo "<tr class='" . $class . " top'><td>". Html::convDateTime($data["dat"]) . "</td>" .
            "<td>". $data["user_name"] . "&nbsp;</td>".
            "<td><a href='". Toolbox::getItemTypeFormURL('Computer')."?id=" . $data["cid"] . "'>" .
            $data["name"] . "</a></td><td>";
      $prevclass = $class;
      $class = ($class=="tab_bg_2" ? "tab_bg_1" : "tab_bg_2");
   }
   $field = "";
   if ($data["linked_action"]) {
   // Yes it is an internal device
      switch ($data["linked_action"]) {
         case Log::HISTORY_ADD_DEVICE :
            $field = NOT_AVAILABLE;
            if ($item = getItemForItemtype($data["itemtype_link"])) {
               $field = $item->getTypeName();
            }
            $change = $LANG["devices"][25]."&nbsp;<strong>:</strong>&nbsp;'".$data[ "new_value"]."'";
            break;

         case Log::HISTORY_UPDATE_DEVICE :
               $field = NOT_AVAILABLE;
               $change = '';
               if ($item = getItemForItemtype($data["itemtype_link"])) {
                  $field  = $item->getTypeName();
                  $change = $item->getSpecifityLabel()."&nbsp;<strong>:</strong>&nbsp;''";
               }
            $change .= $data[ "old_value"]."'&nbsp;<strong>--></strong>&nbsp;'".$data[ "new_value"]."'";
            break;

         case Log::HISTORY_DELETE_DEVICE :
            $field = NOT_AVAILABLE;
            if ($item = getItemForItemtype($data["itemtype_link"])) {
               $field = $item->getTypeName();
            }
            $change = $LANG["devices"][26]."&nbsp;<strong>:</strong>&nbsp;'".$data["old_value"]."'";
            break;

         case Log::HISTORY_DISCONNECT_DEVICE :
            if (!($item = getItemForItemtype($data["itemtype_link"]))) {
               continue;
            }
            $field  = $item->getTypeName();
            $change = $LANG["central"][6]."&nbsp;<strong>:</strong>&nbsp;'".$data["old_value"]."'";
            break;

         case Log::HISTORY_CONNECT_DEVICE :
            if (!($item = getItemForItemtype($data["itemtype_link"]))) {
               continue;
            }
            $field  = $item->getTypeName();
            $change = $LANG["log"][55]."&nbsp;<strong>:</strong>&nbsp;'".$data["new_value"]."'";
            break;
      }//fin du switch
   }
   echo $field . "<td>" . $change;
}

if (!empty($prev)) {
   echo "</td></tr>\n";
}
echo "</table><p>".$LANG['plugin_reports']['histohard'][4]."</p></div>\n";

Html::footer();
?>