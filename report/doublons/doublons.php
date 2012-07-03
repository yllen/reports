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
// Purpose of file: Handle configuration for "doublons" report
// ----------------------------------------------------------------------

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");

includeLocales("doublons");

plugin_reports_checkRight('reports', "doublons","r");
$computer = new Computer();
$computer->checkGlobal('r');

Html::header($LANG['plugin_reports']['doublons'][1], $_SERVER['PHP_SELF'], "utils", "report");

Report::title();

$crits = array(0 => Dropdown::EMPTY_VALUE,
               1 => $LANG["common"][16],        // Name
               2 => $LANG["common"][22]." + ".$LANG["common"][19],   // Model + Serial
               3 => $LANG["common"][16]." + ".$LANG["common"][22]." + ".$LANG["common"][19], // Name + Model + Serial
               4 => $LANG["device_iface"][2],   // Mac Address
               5 => $LANG["networking"][14],    // IP Address
               6 => $LANG['common'][20]);

if (isset($_GET["crit"])) {
   $crit = $_GET["crit"];

} else if (isset($_POST["crit"])) {
   $crit = $_POST["crit"];

} else if (isset($_SESSION['plugin_reports_doublons_crit'])) {
   $crit = $_SESSION['plugin_reports_doublons_crit'];

} else {
   $crit = 0;
}

// ---------- Form ------------
echo "<form action='".$_SERVER["PHP_SELF"]."' method='post'>";
echo "<table class='tab_cadre' cellpadding='5'>\n";
echo "<tr class='tab_bg_1 center'><th colspan='3'>" . $LANG['plugin_reports']['doublons'][1] .
      "</th></tr>\n";

if (Session::haveRight("config","r")) { // Check only read as we probably use the replicate (no 'w' in this case)
   echo "<tr class='tab_bg_3 center'><td colspan='".($crit>0?'3':'2')."'>";
   echo "<a href='./doublons.config.php'>" . $LANG['plugin_reports']['config'][11]."</a></td></tr>\n";
}

echo "<tr class='tab_bg_1'><td class='right'>" . $LANG["rulesengine"][6] . "&nbsp;:&nbsp;</td><td>";
echo "<select name='crit'>";

foreach ($crits as $key => $val) {
   echo "<option value='$key'" . ($crit==$key ? "selected" : "") . ">$val</option>";
}
echo "</select></td>";

if ($crit > 0) {
   echo "<td>";
   //Add parameters to uri to be saved as bookmarks
   $_SERVER["REQUEST_URI"] = buildBookmarkUrl($_SERVER["REQUEST_URI"],$crit);
   Bookmark::showSaveButton(Bookmark::SEARCH,'Computer');
   echo "</td>";
}
echo"</tr>\n";

echo "<tr class='tab_bg_1 center'><td colspan='".($crit>0?'3':'2')."'>";
echo "<input type='submit' value='valider' class='submit'/>";
echo "</td></tr>\n";
echo "</table>\n</form>\n";

if ($crit==5) { // Search Duplicate IP Address - From glpi_networking_ports
   $IPBlacklist = "AA.`ip` != ''
                   AND AA.`ip` != '0.0.0.0'";
   if (TableExists("glpi_plugin_reports_doublons_backlists")) {
      $res  =$DB->query("SELECT `addr`
                         FROM `glpi_plugin_reports_doublons_backlists`
                         WHERE `type` = '2'");

      while ($data = $DB->fetch_array($res)) {
         if (strpos($data["addr"], '%')) {
            $IPBlacklist .= " AND AA.`ip` NOT LIKE '".addslashes($data["addr"])."'";
         } else {
            $IPBlacklist .= " AND AA.`ip` != '".addslashes($data["addr"])."'";
         }
      }
   }

   $Sql = "SELECT A.`id` AS AID, A.`name` AS Aname,
                  AA.`ip` AS Aaddr, A.`entities_id` AS entity,
                  B.`id` AS BID, B.`name` AS Bname,
                  BB.`ip` AS Baddr
           FROM `glpi_computers` A,
                `glpi_computers` B,
                `glpi_networkports` AA,
                `glpi_networkports` BB " .
           getEntitiesRestrictRequest(" WHERE ", "A", "entities_id") ."
                 AND AA.`itemtype` = 'Computer'
                 AND AA.`items_id` = A.`id`
                 AND BB.`itemtype` = 'Computer'
                 AND BB.`items_id` = B.`id`
                 AND AA.`ip` = BB.`ip`
                 AND ($IPBlacklist)
                 AND B.`id` > A.`id`
                 AND A.`entities_id` = B.`entities_id`
                 AND A.`is_template` = '0'
                 AND B.`is_template` = '0'
                 AND A.`is_deleted` = '0'
                 AND B.`is_deleted` = '0'";

   $col = $LANG["networking"][14];

} else if ($crit==4) { // Search Duplicate Mac Address - From glpi_computer_device
   $MacBlacklist = "''";
   if (TableExists("glpi_plugin_reports_doublons_backlists")) {
      $res = $DB->query("SELECT `addr`
                        FROM `glpi_plugin_reports_doublons_backlists`
                        WHERE `type` = '1'");
      while ($data = $DB->fetch_array($res)) {
         $MacBlacklist .= ",'".addslashes($data["addr"])."'";
      }
   } else {
      $MacBlacklist .= ",'44:45:53:54:42:00', 'BA:D0:BE:EF:FA:CE', '00:53:45:00:00:00',
                         '80:00:60:0F:E8:00'";
   }
   $Sql = "SELECT A.`id` AS AID, A.`name` AS Aname,
                  AA.`specificity` AS Aaddr, A.`entities_id` AS entity,
                  B.`id` AS BID, B.`name` AS Bname,
                  BB.`specificity` AS Baddr
           FROM `glpi_computers` A,
                `glpi_computers` B,
                `glpi_computers_devicenetworkcards` AA,
                `glpi_computers_devicenetworkcards` BB ".
           getEntitiesRestrictRequest(" WHERE ", "A", "entities_id") ."
                 AND AA.`computers_id` = A.`id`
                 AND BB.`computers_id` = B.`id`
                 AND AA.`specificity` = BB.`specificity`
                 AND AA.`specificity` NOT IN ($MacBlacklist)
                 AND B.`id` > A.`id`
                 AND A.`entities_id` = B.`entities_id`
                 AND A.`is_template` = '0'
                 AND B.`is_template` = '0'
                 AND A.`is_deleted` = '0'
                 AND B.`is_deleted` = '0'";

   $col = $LANG["networking"][15];

} else if ($crit>0) { // Search Duplicate Name and/ord Serial or Otherserial - From glpi_computers
   $SerialBlacklist = "''";
   if (TableExists("glpi_plugin_reports_doublons_backlists")) {
      $res = $DB->query("SELECT `addr`
                         FROM `glpi_plugin_reports_doublons_backlists`
                         WHERE `type` = '3'");
      while ($data = $DB->fetch_array($res)) {
         $SerialBlacklist .= ",'".addslashes($data["addr"])."'";
      }
   }
   $Sql = "SELECT A.`id` AS AID, A.`name` AS Aname,
                  A.`entities_id` AS entity,
                  B.`id` AS BID, B.`name` AS Bname
           FROM `glpi_computers` A,
                `glpi_computers` B " .
           getEntitiesRestrictRequest(" WHERE ", "A", "entities_id") ."
                 AND B.`id` > A.`id`
                 AND A.`entities_id` = B.`entities_id`
                 AND A.`is_template` = '0'
                 AND B.`is_template` = '0'
                 AND A.`is_deleted` = '0'
                 AND B.`is_deleted` = '0'";

   if ($crit == 6) {
      $Sql .= " AND A.`otherserial` != ''
                AND A.`otherserial` = B.`otherserial`";
   } else {
      if ($crit & 1) {
         $Sql .= " AND A.`name` != ''
                   AND A.`name` = B.`name`";
      }
      if ($crit & 2) {
         $Sql .= " AND A.`serial` NOT IN ($SerialBlacklist)
                   AND A.`serial` = B.`serial`
                   AND A.`computermodels_id` = B.`computermodels_id`";
      }
   }
   $col = "";
}


if ($crit>0) { // Display result
   $canedit = $computer->canUpdate();
   $colspan = ($col ? 8 : 7) + ($canedit ? 1 : 0);

   // save crit for massive action
   $_SESSION['plugin_reports_doublons_crit'] = $crit;

   if ($canedit) {
      echo "<form method='post' name='massiveaction_form' id='massiveaction_form' action=\"".
            $CFG_GLPI["root_doc"]."/front/massiveaction.php\">";
   }
   echo "<table class='tab_cadrehov' cellpadding='5'>" .
      "<tr><th colspan='$colspan'>" . $LANG['plugin_reports']['doublons'][2] . "</th>" .
      "<th class='blue' colspan='$colspan'>" . $LANG['plugin_reports']['doublons'][3]."</th></tr>\n" .
      "<tr>";
   $colspan *= 2;

   if ($canedit) {
      echo "<th>&nbsp;</th>";
   }
   echo "<th>" . $LANG["common"][2] . "</th>" .
      "<th>" . $LANG["common"][16] . "</th>" .
      "<th>" . $LANG["common"][5] . "</th>" .
      "<th>" . $LANG["common"][22] . "</th>" .
      "<th>" . $LANG["common"][19] . "</th>" .
      "<th>" . $LANG['common'][20] . "</th>";
   if ($col) {
      echo "<th>$col</th>";
   }
   echo "<th>".$LANG['ocsng'][14]."</th>";

   if ($canedit) {
      echo "<th>&nbsp;</th>";
   }

   echo "<th class='blue'>" . $LANG["common"][2] . "</th>" .
      "<th class='blue'>" . $LANG["common"][16] . "</th>" .
      "<th class='blue'>" . $LANG["common"][5] . "</th>" .
      "<th class='blue'>" . $LANG["common"][22] . "</th>" .
      "<th class='blue'>" . $LANG["common"][19] . "</th>".
      "<th class='blue'>".$LANG['common'][20]."</th>";
   if ($col) {
      echo "<th class='blue'>$col</th>";
   }
   echo "<th class='blue'>".$LANG['ocsng'][14]."</th>";

   echo "</tr>\n";


   if (method_exists('DBConnection', 'getReadConnection')) { // In 0.80
      $DBread = DBConnection::getReadConnection();
   } else {
      $DBread = $DB;
   }

   $comp = new Computer();
   $result = $DBread->query($Sql);
   for ($prev=-1, $i=0 ; $data = $DBread->fetch_array($result) ; $i++) {
      if ($prev != $data["entity"]) {
         $prev = $data["entity"];
         echo "<tr class='tab_bg_4'><td class='center' colspan='$colspan'>".
            Dropdown::getDropdownName("glpi_entities", $prev) . "</td></tr>\n";
      }
      echo "<tr class='tab_bg_2'>";
      if ($canedit) {
         echo "<td><input type='checkbox' name='item[".$data["AID"]."]' value='1'></td>";
      }
      echo "<td class='b'>".$data["AID"]."</td>";
      if ($comp->getFromDB($data["AID"])) {
         echo "<td>";
         echo $comp->getLink(true);
         echo "</td><td>";
         echo Dropdown::getDropdownName("glpi_manufacturers", $comp->getField('manufacturers_id'));
         echo "</td><td>";
         echo Dropdown::getDropdownName("glpi_computermodels", $comp->getField('computermodels_id'));
         echo "</td><td>".$comp->getField('serial');
         echo "</td><td>".$comp->getField('otherserial')."</td>";
      } else {
         echo "<td colspan='5'>".$data["Aname"]."</td>";
      }
      if ($col) {
         echo "<td>" .$data["Aaddr"]. "</td>";
      }
      echo "<td>" .getLastOcsUpdate($data['AID']). "</td>";

      if ($canedit) {
         echo "<td><input type='checkbox' name='item[".$data["BID"]."]' value='1'></td>";
      }
      echo "<td class='b blue'>".$data["BID"]."</td>";
      if ($comp->getFromDB($data["BID"])) {
         echo "<td class='blue'>";
         echo $comp->getLink(true);
         echo "</td><td class='blue'>";
         echo Dropdown::getDropdownName("glpi_manufacturers", $comp->getField('manufacturers_id'));
         echo "</td><td class='blue'>";
         echo Dropdown::getDropdownName("glpi_computermodels", $comp->getField('computermodels_id'));
         echo "</td><td class='blue'>".$comp->getField('serial');
         echo "</td><td class='blue'>".$comp->getField('otherserial')."</td>";
      } else {
         echo "<td colspan='5' class='blue'>".$data["Aname"]."</td>";
      }
      if ($col) {
         echo "<td class='blue'>" .$data["Baddr"]. "</td>";
      }
      echo "<td class='blue'>" .getLastOcsUpdate($data['BID']). "</td>";

   echo "</tr>\n";
   }
   echo "<tr class='tab_bg_4'><td class='center' colspan='$colspan'>";
   if ($i) {
      echo $LANG['plugin_reports']['doublons'][1] . " : $i";
   } else {
      echo $LANG['search'][15];
   }
   echo "</td></tr>\n";
   echo "</table>";
   if ($canedit) {
      if ($i) {
         Html::openArrowMassives("massiveaction_form");
         Dropdown::showForMassiveAction('Computer');
         $options = array();
         Html::closeArrowMassives($options);
      }
      echo "</form>";
   }
}
Html::footer();


function buildBookmarkUrl($url,$crit) {
   return $url."?crit=".$crit;
}


function getLastOcsUpdate($computers_id) {
   global $DB;

   $query = "SELECT `last_ocs_update`
             FROM `glpi_ocslinks`
             WHERE `computers_id` = '$computers_id'";
   $results = $DB->query($query);

   if ($DB->numrows($results) > 0) {
      return $DB->result($results,0,'last_ocs_update');
   }
   return '';
}
?>