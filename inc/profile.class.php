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

// ----------------------------------------------------------------------
// Original Author of file: Dévi Balpe
// Purpose of file:
// ----------------------------------------------------------------------

class PluginReportsProfile extends CommonDBTM {


   //if profile deleted
   static function cleanProfiles(Profile $prof) {
      $plugprof = new self();
      $plugprof->deleteByCriteria(array('profiles_id' => $prof->getField("id")));
   }

   function canCreate() {
      return haveRight('profile', 'w');
   }

   function canView() {
      return haveRight('profile', 'r');
   }


   function showForm($id, $options=array()){
      global $LANG,$DB;

      $target = $this->getFormURL();
      if (isset($options['target'])) {
        $target = $options['target'];
      }

      if ($id > 0){
         $this->check($id,'r');
      } else {
         $this->check(-1,'w');
      }

      $canedit=$this->can($id,'w');

      echo "<form action='".$target."' method='post'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='4' class='center b'>".
             $LANG['plugin_reports']['config'][4]." ".$this->fields["profile"]."</th></tr>";

      $plugname = array();
      foreach(searchReport() as $key => $plug) {
         $mod = ($plug=='reports' ? $key : "${plug}_${key}");
         echo "<tr class='tab_bg_1'>";
         if (!isset($plugname[$plug])) {
            // Retrieve the plugin name
            $function = "plugin_version_$plug";
            $tmp = $function();
            $plugname[$plug] = $tmp['name'];
         }
         echo "<td>".$plugname[$plug]."</td>";
         if (strpos($key,'stat') === false) {
            echo "<td>".$LANG['Menu'][6]."</td>";
         } else {
            echo "<td>".$LANG['Menu'][13]."</td>";
         }
         echo "<td>".$LANG["plugin_$plug"][$key][1]." :</td><td>";
         Profile::dropdownNoneReadWrite($mod,(isset($this->fields[$mod])?$this->fields[$mod]:''),1,1,0);
         echo "</td></tr>";
      }

      if ($canedit) {
         echo "<tr class='tab_bg_1'>";
         echo "<td class='center' colspan='4'>";
         echo "<input type='hidden' name='id' value=$id>";
         echo "<input type='submit' name='update_user_profile' value='".
                $LANG['buttons'][7]."' class='submit'>";
         echo "</td></tr>\n";
      }
      echo "</table></form>";
   }


   function updateRights($reports) {
      global $DB;

      $rights = array();
      foreach($reports as $report => $plug) {
         if ($plug =='reports') {
            $rights[$report]=1;
         } else {
            $rights["${plug}_${report}"]=1;
         }
      }

      $current_rights = array();
      $query = "SELECT DISTINCT `report`
                FROM `glpi_plugin_reports_profiles`";
      foreach ($DB->request($query) as $data) {
         $current_rights[$data['report']] = 1;
      }

      // Removed report
      foreach($current_rights as $right => $value) {
         if (!isset($rights[$right])) {
            // Delete the lines for old reports
            $this->deleteByCriteria(array('report' => $right));
         } else {
            unset($rights[$right]);
         }
      }

      // Added report
      foreach ($rights as $right => $val) {
         $DB->query("INSERT INTO
                     `".$this->getTable()."` (`profiles_id`, `report`, `access`)
                     VALUE (4, '$right', 'r')");

         // For immediate availability
         if ($_SESSION['glpiactiveprofile']['id']==4) {
            $_SESSION['glpi_plugin_reports_profile'][$right] = 'r';
         }
      }
   }


   static function changeprofile() {
      global $DB;

      $_SESSION['glpi_plugin_reports_profile'] = array();

      $crit = array('profiles_id' => $_SESSION['glpiactiveprofile']['id']);

      foreach ($DB->request('glpi_plugin_reports_profiles', $crit) as $data) {
         $_SESSION['glpi_plugin_reports_profile'][$data['report']] = $data['access'];
      }
   }


   /**
    * Look for all the plugins, and update rights if necessary
    */
   function updatePluginRights() {

      $tab = searchReport();
      $this->updateRights($tab);

      return $tab;
   }

   static function install() {
      global $DB;

      $create = "CREATE TABLE IF NOT EXISTS `glpi_plugin_reports_profiles` (
                    `id` int(11) NOT NULL auto_increment,
                    `profiles_id` int(11) NOT NULL DEFAULT '0',
                    `report` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `access` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `report` (`report`),
                  KEY `profiles_id` (`profiles_id`))
                  ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

      if (TableExists('glpi_plugin_reports_profiles')) { //1.1 ou 1.2

         if (FieldExists('glpi_plugin_reports_profiles','ID')) { // version installee < 1.4.0
            $query = "ALTER TABLE `glpi_plugin_reports_profiles`
                      CHANGE `ID` `id` int(11) NOT NULL auto_increment";
            $DB->query($query) or die("CHANGE ID: ".$DB->error());
         }

         if (!FieldExists('glpi_plugin_reports_profiles','profiles_id')) { // version < 1.5.0
            $query = "RENAME TABLE `glpi_plugin_reports_profiles`
                                TO `glpi_plugin_reports_oldprofiles`";
            $DB->query($query) or die("SAVE TABLE profiles: ".$DB->error());
            $DB->query($create) or die("CREATE TABLE profiles: ".$DB->error());

            $fields = $DB->list_fields('glpi_plugin_reports_oldprofiles');
            unset($fields['id']);
            unset($fields['profile']);
            foreach($fields as $field => $descr) {
               $query = "INSERT INTO `glpi_plugin_reports_profiles`
                                     (`profiles_id`, `report`, `access`)
                                SELECT `id`, '$field', `$field`
                                FROM `glpi_plugin_reports_oldprofiles`
                                WHERE `$field` IS NOT NULL";
               $DB->query($query) or die("LOAD TABLE profiles: ".$DB->error());
            }

            $query = "DROP TABLE `glpi_plugin_reports_oldprofiles`";
            $DB->query($query) or die("DROP TABLE oldprofiles: ".$DB->error());
         }
      } else {
         $DB->query($create) or die("CREATE TABLE profiles: ".$DB->error());
      }

      return true;
   }


   static function uninstall() {
      global $DB;

      $tables = array('glpi_plugin_reports_profiles',
                      'glpi_plugin_reports_oldprofiles',
                      'glpi_plugin_reports_doublons_backlist',
                      'glpi_plugin_reports_doublons_backlists');

      foreach ($tables as $table) {
         $query = "DROP TABLE IF EXISTS `$table`";
         $DB->query($query) or die($DB->error());
      }

      return true;
   }
}

?>