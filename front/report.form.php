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

// Original Author of file: Balpe Dévi
// Purpose of file:
// ----------------------------------------------------------------------

define('GLPI_ROOT', '../../..');
include_once (GLPI_ROOT . "/inc/includes.php");

checkRight('profile', 'r');

Plugin::load('reports', true);

commonHeader($LANG['plugin_reports']['config'][1], $_SERVER['PHP_SELF'], 'config', 'plugins');

require_once "../inc/profile.class.php";

$report='';
if (isset($_POST['report'])) {
   $report=$_POST['report'];
}

$prof = new PluginReportsProfile();

if (isset($_POST['delete']) && $report) {
   checkRight('profile', 'w');
   $prof->deleteByCriteria(array('report' => $report));

} else  if (isset($_POST['update']) && $report) {
   checkRight('profile', 'w');
   PluginReportsProfile::updateForReport($_POST);
}

$tab = $prof->updatePluginRights();

echo "<form method='post' action=\"".$_SERVER["PHP_SELF"]."\">";
echo "<table class='tab_cadre'><tr><th colspan='2'><a href='config.form.php'>";
echo $LANG['plugin_reports']['config'][1]."</a><br>&nbsp;<br>";
echo $LANG['plugin_reports']['config'][8] . "</th></tr>\n";

echo "<tr class='tab_bg_1'><td>" . $LANG['plugin_reports']['config'][10] . "&nbsp;: ";
$query = "SELECT `id`, `name`
          FROM `glpi_profiles`
          ORDER BY `name`";
$result=$DB->query($query);

echo "<select name='report'>";
$plugname = array();
$rap = array();
foreach($tab as $key => $plug) {
   $mod = ($plug=='reports' ? $key : $plug.'_'.$key);
   if (!isset($plugname[$plug])) {
      // Retrieve the plugin name
      $function = "plugin_version_$plug";
      $tmp = $function();
      $plugname[$plug] = $tmp['name'];
   }
   $section = (isStat($mod)
                  ? $LANG['title'][24] . ' - ' . $LANG['Menu'][13]
                  : $LANG['Menu'][18] . ' - ' . $LANG['Menu'][6]);

   $rap[$plug][$section][$mod] = $LANG["plugin_$plug"][$key][1];
}
$tab = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
foreach ($rap as $plug => $tmp1) {
   echo '<optgroup label="'.$LANG['common'][29].' - '.$plugname[$plug].'">';
   foreach ($tmp1 as $section => $tmp2) {
      echo '<optgroup label="'.$tab."&raquo;&nbsp;".$section.'">';
      foreach ($tmp2 as $mod => $name) {
         echo "<option value='$mod' ".($report=="$mod"?"selected":"").">${tab}${tab}$name</option>\n";
      }
      echo "</optgroup>\n";
   }
   echo "</optgroup>\n";
}
echo "</select>";
echo "<td><input type='submit' value='".$LANG['buttons'][2]."' class='submit' ></td></tr>";
echo "</table></form>";

if ($report) {
   PluginReportsProfile::showForReport($report);
}

commonFooter();

?>