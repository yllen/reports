<?php
/**
 * @version $Id$
 -------------------------------------------------------------------------
  LICENSE

 This file is part of Reports plugin for GLPI.

 Reports is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Reports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Reports. If not, see <http://www.gnu.org/licenses/>.

 @package   reports
 @authors    Nelly Mahu-Lasson, Remi Collet
 @copyright Copyright (c) 2009-2017 Reports plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.glpi-project.org/projects/reports
 @link      http://www.glpi-project.org/
 @since     2009
 --------------------------------------------------------------------------
*/


function plugin_reports_install() {
   global $DB;

   // config of doublon report is now in glpi_blacklists
   If ($DB->tableExists("glpi_plugin_reports_doublons_backlists")) {

      if ($result = $DB->request(['FROM' => 'glpi_plugin_reports_doublons_backlists'])) {
         if (count($result) > 0) {
            while ($data = $result->next()) {
               $data = toolbox::addslashes_deep($data);
               if ($data['type'] == 1) {
                  $type = 2;
               } else if ($data['type'] == 2) {
                  $type = 1;
               } else {
                  $type = $data['type'];
               }
               $query = "INSERT INTO `glpi_blacklists`
                             (`type`, `name`, `value`, `comment`)
                          VALUES (".$type.", '".$data['addr']."', '".$data['addr']."',
                                  '".$data['comment']."')";
               $DB->queryOrDie($query, "0.90 config doublon in blacklist");
            }
         }
      }
      $sql = "DROP TABLE `glpi_plugin_reports_doublons_backlists`";
      $DB->queryOrDie($query, "0.90 delete glpi_plugin_reports_doublons_backlists ");
   }

   // No autoload when plugin is not activated
   include_once (GLPI_ROOT."/plugins/reports/inc/profile.class.php");

   return PluginReportsProfile::install();
   }


function plugin_reports_uninstall() {
   global $DB;

   // No autoload when plugin is not activated (if dessactivation before uninstall)
   include_once (GLPI_ROOT."/plugins/reports/inc/profile.class.php");

   return PluginReportsProfile::uninstall();
}
