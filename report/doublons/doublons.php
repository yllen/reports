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

// Original Author of file: Remi Collet
// Purpose of file: Handle configuration for "doublons" report
// ----------------------------------------------------------------------

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE = 1;
$DBCONNECTION_REQUIRED = 0;

define('GLPI_ROOT', '../../../..'); 
include (GLPI_ROOT . "/inc/includes.php"); 

includeLocales("doublons");

plugin_reports_checkRight("doublons","r");
checkSeveralRightsAnd(array('computer' => "r"));

commonHeader($LANG['plugin_reports']['doublons'][1],$_SERVER['PHP_SELF'],"utils","report");

$crits = array(0 => "-----", 
               1 => $LANG["common"][16],        // Name
               2 => $LANG["common"][22]." + ".$LANG["common"][19],   // Model + Serial
               3 => $LANG["common"][16]." + ".$LANG["common"][22]." + ".$LANG["common"][19], // Name + Model + Serial
               4 => $LANG["device_iface"][2],   // Mac Address
               5 => $LANG["networking"][14],    // IP Address
               6 => $LANG['common'][20]);       // Otherserial

if (isset($_GET["crit"])) {
   $_POST = $_GET["crit"];
}
$crit = (isset($_POST["crit"]) ? $_POST["crit"] : 0); 

// ---------- Form ------------
echo "<div class='center'>";
echo "<form action='".$_SERVER["PHP_SELF"]."' method='post'>";
echo "<table class='tab_cadre' cellpadding='5'>\n";
echo "<tr class='tab_bg_1 center'><th colspan='3'>" . $LANG['plugin_reports']['doublons'][1] . 
      "</th></tr>\n";

if (haveRight("config","r")) { // Check only read as we probably use the replicate (no 'w' in this case)
   echo "<tr class='tab_bg_3 center'><td colspan='".($crit>0?'3':'2')."'>";
   echo "<a href='./doublons.config.php'>" . $LANG['plugin_reports']['config'][11] . "</a></td></tr>\n";
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
   Bookmark::showSaveButton(BOOKMARK_SEARCH,'Computer');
   echo "</td>";
}
echo"</tr>\n";

echo "<tr class='tab_bg_1 center'><td colspan='".($crit>0?'3':'2')."'>";
echo "<input type='submit' value='valider' class='submit'/>";
echo "</td></tr>\n";
echo "</table>\n</form></div>\n";

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

   $Sql = "SELECT A.`id` AS AID, A.`name` AS Aname, A.`serial` AS Aserial, 
                  A.`computermodels_id` AS Amodel, 
                  A.`manufacturers_id` AS Amanu, AA.`ip` AS Aaddr, A.`entities_id` AS entity, 
                  A.`otherserial` AS Aotherserial,
                  B.`id` AS BID, B.`name` AS Bname, B.`serial` AS Bserial, 
                  B.`computermodels_id` AS Bmodel, 
                  B.`manufacturers_id` AS Bmanu, BB.`ip` AS Baddr, B.`otherserial` AS Botherserial
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

   $col=$LANG["networking"][14];

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
      $MacBlacklist .= ",'44:45:53:54:42:00','BA:D0:BE:EF:FA:CE', '00:53:45:00:00:00', '80:00:60:0F:E8:00'";
   }
   $Sql = "SELECT A.`id` AS AID, A.`name` AS Aname, A.`serial` AS Aserial, 
                  A.`computermodels_id` AS Amodel, 
                  A.`manufacturers_id` AS Amanu, AA.`specificity` AS Aaddr, A.`entities_id` AS entity, 
                  A.`otherserial` AS Aotherserial,
                  B.`id` AS BID, B.`name` AS Bname, B.`serial` AS Bserial, 
                  B.`computermodels_id` AS Bmodel, 
                  B.`manufacturers_id` AS Bmanu, BB.`specificity` AS Baddr, B.`otherserial` as Botherserial
           FROM `glpi_computers` A, 
                `glpi_computers` B, 
                `glpi_computers_devices` AA, 
                `glpi_computers_devices` BB" . 
           getEntitiesRestrictRequest(" WHERE ", "A", "entities_id") ."
                 AND AA.`devicetype` = '".NETWORK_DEVICE."' 
                 AND AA.`computers_id` = AID
                 AND BB.`devicetype` = '".NETWORK_DEVICE."'
                 AND BB.`computers_id` = BID 
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
   $Sql = "SELECT A.`id` AS AID, A.`name` AS Aname, A.`serial` AS Aserial, 
                  A.`computermodels_id` AS Amodel, 
                  A.`manufacturers_id` AS Amanu, A.`entities_id` AS entity, A.`otherserial` AS Aotherserial,
                  B.`id` AS BID, B.`name` AS Bname, B.`serial` AS Bserial, 
                  B.`computermodels_id` AS Bmodel, 
                  B.`manufacturers_id` AS Bmanu, B.`otherserial` AS Botherserial 
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
   echo "<div class='center'><table class='tab_cadrehov' cellpadding='5'>" .
      "<tr><th colspan='". ($col ? 7 : 6) ."'>" . $LANG['plugin_reports']['doublons'][2] . "</th>" .
      "<th class='blue' colspan='". ($col ? 7 : 6) ."'>" . $LANG['plugin_reports']['doublons'][3] . "</th></tr>\n" .
      "<tr><th>" . $LANG["common"][2] . "</th>" .
      "<th>" . $LANG["common"][16] . "</th>" .
      "<th>" . $LANG["common"][5] . "</th>" .
      "<th>" . $LANG["common"][22] . "</th>" .
      "<th>" . $LANG["common"][19] . "</th>" .
      "<th>".$LANG['common'][20]."</th>";
   if ($col) {
      echo "<th>$col</th>";
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
   echo "</tr>\n";

   $result = $DB->query($Sql);
   for ($prev=-1, $i=0 ; $data = $DB->fetch_array($result) ; $i++) {
      if ($prev != $data["entity"]) {
         $prev = $data["entity"];
         echo "<tr class='tab_bg_4'><td class='center' colspan='". ($col ? 14 : 12) ."'>".
            Dropdown::getDropdownName("glpi_entities", $prev) . "</td></tr>\n"; 
      }
      echo "<tr class='tab_bg_2'>" .
         "<td><a href='".getItemTypeFormURL('Computer')."?id=".$data["AID"]."'>".$data["AID"]."</a>".
         "</td>" .
         "<td>".$data["Aname"]."</td><td>".Dropdown::getDropdownName("glpi_manufacturers",$data["Amanu"]).
         "</td>".
         "<td>".Dropdown::getDropdownName("glpi_computermodels",$data["Amodel"])."</td><".
         "td>".$data["Aserial"]."</td><td>".$data["Aotherserial"]."</td>";

      if ($col) {
         echo "<td>" .$data["Aaddr"]. "</td>";
      }

      echo "<td><a href='".getItemTypeFormURL('Computer')."?id=".$data["BID"]."'>".
         $data["BID"]."</a></td>" .
         "<td class='blue'>".$data["Bname"]."</td><".
         "td class='blue'>".Dropdown::getDropdownName("glpi_manufacturers",$data["Bmanu"]).
         "</td>".
         "<td class='blue'>".Dropdown::getDropdownName("glpi_computermodels",$data["Bmodel"])."</td>".
         "<td class='blue'>".$data["Bserial"]."</td><td class='blue'>".$data["Botherserial"]."</td>";

      if ($col) {
         echo "<td>" .$data["Aaddr"]. "</td>";
      }
   echo "</tr>\n";
   }
   if ($i) {
      echo "<tr class='tab_bg_4'><td class='center' colspan='". ($col ? 14 : 12) ."'>" . 
      $LANG['plugin_reports']['doublons'][1] . " : $i</td></tr>\n";
   }
   echo "</table></div>";
}
commonFooter(); 
 
 
function buildBookmarkUrl($url,$crit) {
   return $url."?crit=".$crit;
}
 
?>