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

if (!defined('GLPI_ROOT')) {
   define('GLPI_ROOT', '../../..');
}

include_once (GLPI_ROOT . "/inc/includes.php");
Plugin::load('reports');

Session::checkSeveralRightsOr(array("config" => "w", "profile" => "w"));
Html::header($LANG['common'][12], $_SERVER['PHP_SELF'], "config", "plugins");

echo "<div class='center'>";
echo "<table class='tab_cadre'>";
echo "<tr><th>".$LANG['plugin_reports']['config'][1]."</th></tr>";

if (Session::haveRight("profile","w")) {
   echo "<tr class='tab_bg_1 center'><td>";
   echo "<a href='report.form.php'>".$LANG['plugin_reports']['config'][8]."</a>";
   echo "</td/></tr>\n";
}

if (Session::haveRight("config","w")) {
   foreach (searchReport() as $report => $plug) {
      if (is_file($url=getReportConfigPage($plug,$report))) {
         echo "<tr class='tab_bg_1 center'><td>";
         echo "<a href='$url'>".
               $LANG['plugin_reports']['config'][11] . " : " . $LANG['plugin_reports'][$report][1];
         echo "</a></td/></tr>";
      }
   }
}

echo "</table></div>";

Html::footer();
?>