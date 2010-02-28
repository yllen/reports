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
// Purpose of file:
// ----------------------------------------------------------------------

function cmpStat ($a, $b) {
   return $a["tot"] - $b["tot"];
}
function doStatBis ($table, $entities, $header) {
   global $DB, $LANG;

   // Compute stat
   $counts = array();
   foreach ($entities as $entity) {
      // Count for this entity
      $sql = "SELECT `states_id`, count(*) AS cpt
              FROM `$table`
              WHERE `is_deleted` = '0'
                    AND `is_template` = '0'
                    AND `entities_id` = '$entity'
              GROUP BY `states_id`";

      $result = $DB->query($sql);
      $counts[$entity] = array();
      while ($data = $DB->fetch_array($result)) {
         $counts[$entity][$data["state"]] = $data["cpt"];
      }

      $counts[$entity]["tot"] = 0;
      foreach ($header as $id => $name) {
         if (isset($counts[$entity][$id])) {
            $counts[$entity]["tot"] += $counts[$entity][$id];
         } else {
            $counts[$entity][$id] = 0;
         }
      }
   }

   // Sort result
   uasort($counts,"cmpStat");

   // Display result
   $total["tot"] = 0;
   foreach ($header as $id => $name) {
      $total[$id] = 0;
   }
   foreach ($counts as $entity => $count) {
      if ($count["tot"]) {
         $Ent = new Entity();
         $Ent->getFromDB($entity);

         echo "<tr class='tab_bg_2'><td class='left'>";
         if ($entity) {
            echo $Ent->fields["name"] . "</td>";
         } else {
            echo $LANG["entity"][2] . "</td>";
         }
         echo "<td class='right'>" . $count["tot"] . "</td>";
         $total["tot"] += $count["tot"];
         foreach ($header as $id => $name) {
            echo "<td class='right'>" . $count[$id] . "</td>";
            $total[$id] += $count[$id];
         }
      }
      echo "</tr>\n";
   }

   // Display total
   if (count($entities) >1) {
      echo "<tr class='tab_bg_1'><td class='left'>".$LANG['plugin_reports']['pcsbyentity'][3]."</td>";
      echo "<td class='right'>" . $total["tot"] . "</td>";
      foreach ($header as $id => $name) {
         echo "<td class='right'>" . $total[$id] . "</td>";
      }
      echo "</tr>\n";
   }
}


function doStat ($table, $entity, $header, $level=0) {
   global $DB, $LANG;

   $Ent = new Entity();
   $Ent->getFromDB($entity);

   // Count for this entity
   $sql = "SELECT `states_id`, count(*) AS cpt
           FROM `$table`
           WHERE `is_deleted` = '0'
                 AND `is_template` = '0'
                 AND `entities_id` = '$entity'
           GROUP BY `states_id`";

   $result = $DB->query($sql);
   $count = array();
   while ($data = $DB->fetch_array($result)) {
      $count[$data["states_id"]] = $data["cpt"];
   }

   $count["tot"] = 0;
   foreach ($header as $id => $name) {
      if (isset($count[$id])) {
         $count["tot"] += $count[$id];
      } else {
         $count[$id] = 0;
      }
   }

   // Display counters for this entity
   if ($count["tot"] >0) {
      echo "<tr class='tab_bg_2'><td class='left'>";
      for ($i=0 ; $i<$level ; $i++) {
         echo "&nbsp;&nbsp;&nbsp;";
      }
      if ($entity) {
         echo $Ent->fields["name"] . "</td>";
      }else {
         echo $LANG["entity"][2] . "</td>";
      }
      echo "<td class='right'>" . $count["tot"] . "</td>";
      foreach ($header as $id => $name) {
         echo "<td class='right'>" . $count[$id] . "</td>";
      }
      echo "</tr>\n";
   }

   // Call for Childs
   $save = $count["tot"];
   doStatChilds($table,$entity, $header, $count, $level+1);

   // Display total (Current+Childs)
   if ($save != $count["tot"]) {
      echo "<tr class='tab_bg_1'><td class='left'>";
      for ($i=0 ; $i<$level ; $i++) {
         echo "&nbsp;&nbsp;&nbsp;";
      }
      echo $LANG['plugin_reports']['pcsbyentity'][3] . " ";

      if ($entity) {
         echo $Ent->fields["name"] . "</td>";
      } else {
         echo $LANG["entity"][2] . "</td>";
      }
      echo "<td class='right'>" . $count["tot"] . "</td>";
      foreach ($header as $id => $name) {
         echo "<td class='right'>" . $count[$id] . "</td>";
      }
      echo "</tr>\n";
   }
   return $count;
}


function doStatChilds($table, $entity, $header, &$total, $level) {
   global $DB, $LANG;

   // Search child entities
   $sql = "SELECT `id`
           FROM `glpi_entities`
           WHERE `entities_id` = '$entity'
           ORDER BY `name`";
   $result = $DB->query($sql);

   while ($data = $DB->fetch_array($result)) {
      $fille = doStat($table, $data["id"], $header, $level);
      foreach ($header as $id => $name) {
         $total[$id] += $fille[$id];
      }
      $total["tot"] += $fille["tot"];
   }
}

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE = 1;
$DBCONNECTION_REQUIRED = 0;

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");

includeLocales("pcsbyentity");
plugin_reports_checkRight('reports', "pcsbyentity","r");
commonHeader($LANG['plugin_reports']['pcsbyentity'][1],$_SERVER['PHP_SELF'],"utils","report");

echo "<div class='center'>";

// ---------- Form ------------
echo "<form action='".$_SERVER["PHP_SELF"]."' method='post'>";
echo "<table class='tab_cadre' cellpadding='5'>\n";
echo "<tr class='tab_bg_1 center'><th colspan='2'>" . $LANG['plugin_reports']['pcsbyentity'][1] .
      "</th></tr>\n";
echo "<tr class='tab_bg_1'><td class='right'>" . $LANG['plugin_reports']['pcsbyentity'][2] .
      "&nbsp;:&nbsp;</td>";
echo "<td><select name='type'><option value=''>-----</option>";

$choix = array('Computer'         => $LANG["Menu"][0],
               'Monitor'          => $LANG["Menu"][3],
               'Printer'          => $LANG["Menu"][2],
               'NetworkEquipment' => $LANG["title"][6],
               'Phone'            => $LANG["help"][35]);

foreach ($choix as $id => $name) {
   $item = new $id();
   if ($item->canView()) {
      echo "<option value='" . $id;
      if (isset($_POST["type"]) && $_POST["type"]==$id) {
         echo "' selected='selected'>";
      } else {
         echo "'>";
      }
      echo $name . "</option>";
   }
}
echo "</select></td></tr>\n";

if (count($_SESSION["glpiactiveentities"]) > 1) {
   echo "<tr class='tab_bg_1'><td class='right'>" . $LANG['plugin_reports']['pcsbyentity'][5] .
         "&nbsp;:&nbsp;</td>";
   echo "<td><select name='sort'><option value='0'>".$LANG['plugin_reports']['pcsbyentity'][6].
         "</option>";
   echo "<option value='1'>".$LANG['plugin_reports']['pcsbyentity'][7]."</option></select></td></tr>\n";
}

echo "<tr class='tab_bg_1 center'><td colspan='2'><input type='submit' value='valider' class='submit'/>";
echo "</td></tr>\n";
echo "</table>\n</form></div>\n";

// --------------- Result -------------
if (isset($_POST["type"]) && $_POST["type"] != '') {
   echo "<table class='tab_cadre'>\n";

   echo "<tr><th>".$LANG["entity"][0]. "</th>" .
         "<th>&nbsp;" . $LANG['plugin_reports']['pcsbyentity'][3] . "&nbsp;</th>" .
         "<th>&nbsp;" . $LANG['plugin_reports']['pcsbyentity'][4] . "&nbsp;</th>";

   $sql = "SELECT `id`, `name`
           FROM `glpi_states`
           ORDER BY `id`";
   $result = $DB->query($sql);

   $header[0] = $LANG['plugin_reports']['pcsbyentity'][4];
   while ($data = $DB->fetch_array($result)) {
      $header[$data["id"]] = $data["name"];
      echo "<th>&nbsp;" . $data["name"] . "&nbsp;</th>";
   }
   echo "</tr>\n";

   if (isset($_POST["sort"]) && $_POST["sort"] >0) {
      doStatBis(getTableForItemType($_POST["type"]), $_SESSION["glpiactiveentities"], $header);
   } else {
      doStat(getTableForItemType($_POST["type"]), $_SESSION["glpiactive_entity"], $header);
   }
   echo "</table></div>";
}

commonFooter();

?>