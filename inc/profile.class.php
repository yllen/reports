<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 reports - Additional reports plugin for GLPI
 Copyright (C) 2003-2013 by the reports Development Team.

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

class PluginReportsProfile extends Profile {
   static $rightname = 'profile';

   /**
    * if profile cloned
    *
    * @param $prof   Profile  object
   **/
   static function cloneProfile(Profile $prof) {
      global $DB;

      $profile_right = new ProfileRight;
      $crit          = array('profiles_id' => $prof->input['_old_id'], 
                             "`name` LIKE 'plugin_reports_%'");
      $rights = array();
      foreach ($DB->request($profile_right->getTable(), $crit) as $data) {
         $rights[$data['name']] = $data['rights'];
      }
      unset($input['id']);
      $profile_right->updateProfileRights($prof->getID(), $rights);
   }

   /**
    * @param $prof   Profile object
   **/
   static function showForProfile(Profile $prof){
      global $DB, $LANG;

      $canedit = Session::haveRightsOr(self::$rightname, array(CREATE, UPDATE, PURGE));
      if ($canedit) {
         echo "<form method='post' action='".$prof->getFormURL()."'>";
      }

      $rights = self::getAllRights();
      $prof->displayRightsChoiceMatrix($rights, 
                                       array('canedit'       => $canedit,
                                             'default_class' => 'tab_bg_2',
                                             'title'         => __('Rights management by profil', 
                                                                   'reports')));
      if ($canedit) {
         echo "<div class='center'>";
         echo Html::hidden('id', array('value' => $prof->getField('id')));
         echo Html::submit(_sx('button', 'Save'), array('name' => 'update'));
         echo "</div>\n";
         Html::closeForm();
      }
      echo "</div>";

      $prof->showLegend();
   }


   /**
    * @param $report
   **/
   static function showForReport($report) {
      global $DB;

      if (empty($report) || !Session::haveRight('profile', READ)) {
         return false;
      }
      $current = self::getAllProfilesRights(array("name LIKE '%$report'"));
      $canedit = Session::haveRight('profile', UPDATE);

      if ($canedit) {
         echo "<form action='".$_SERVER['PHP_SELF']."' method='post'>\n";
      }

      echo "<table class='tab_cadre'>\n";
      echo "<tr><th colspan='2'>".__('Profils rights', 'reports')."</th></tr>\n";

      $query = "SELECT `id`, `name`
                FROM `glpi_profiles`
                ORDER BY `name`";

      foreach ($DB->request($query) as $data) {
         echo "<tr class='tab_bg_1'><td>" . $data['name'] . "&nbsp: </td><td>";
         if ((isStat($report) && Session::haveRight("statistic", READ))
             || (!isStat($report) && Session::haveRight("reports", READ))) {
            Profile::dropdownNoneReadWrite($data['id'], $current[$data['id']], 1, 1, 0);
         } else {
            // Can't access because missing right from GLPI core
            // Profile::dropdownNoneReadWrite($mod,'',1,0,0);
            echo "<input type='hidden' name='".$data['id']."' value='NULL'>".__('No access')." *";
         }
         echo "</td></tr>\n";
      }
      echo "<tr class='tab_bg_4'><td colspan='2'>* ";
      if (isStat($report)) {
         _e('No right on Assistance / Statistics', 'reports');
      } else {
         _e('No right on Tools / Reports', 'reports');
      }
      echo "</tr>";

      if ($canedit) {
         echo "<tr class='tab_bg_1'><td colspan='2' class='center'>";
         echo "<input type='hidden' name='report' value='$report'>";
         echo "<input type='submit' name='update' value='"._sx('button', 'Update')."' ".
                "class='submit'>&nbsp;&nbsp;&nbsp;";
         echo "<input type='submit' name='delete' value='"._sx('button', 'Delete permanently')."'
                class='submit'>";
         echo "</td></tr>\n";
         echo "</table>\n";
         Html::closeForm();
      } else {
         echo "</table>\n";
      }
   }

   /**
    * @param $input
   **/
   static function updateForReport($input) {
      $prof    = new Profile();
      $report  = $input['report'];

      foreach($input as $profiles_id => $right) {
         if (is_numeric($profiles_id)) {
            $prof->update(array('id'                       => $profiles_id,
                                '_plugin_reports_'.$report => $right));
            // TODO Check here with another plugin
         }
      }
   }


   /**
    * @param $reports
   **/
   function updateRights($reports) {
      global $DB;

      $profile_right = new ProfileRight;

      $rights = array();
      foreach ($reports as $report => $plug) {
         if ($plug == 'reports') {
            $rights["plugin_reports_$report"] = 1;
         } else {
            $rights["plugin_reports_${plug}_${report}"] = 1;
         }
      }

      $current_rights = array();
      $query = "SELECT DISTINCT `name`
                FROM `glpi_profilerights`
                WHERE `name` LIKE 'plugin_reports_%'";
      foreach ($DB->request($query) as $data) {
         $current_rights[$data['name']] = 1;
      }

      // Remove old reports
      foreach($current_rights as $right => $value) {
         if (!isset($rights[$right])) {
            // Delete the lines for old reports
            $profile_right->deleteByCriteria(array('name' => $right));
         } else {
            unset($rights[$right]);
         }
      }

      // Add new reports
      $rights_name = array_keys($rights);
      ProfileRight::addProfileRights($rights_name);
      if ($_SESSION['glpiactiveprofile']['id'] == 4) {
         $profile_right->updateProfileRights(4, $rights);
      }
   }


   /**
    * @param $crit
    * @param $full   (false by default)
   **/
   static function getAllProfilesRights($crit, $full=false) {
      global $DB;

      $tab = array();

      foreach ($DB->request('glpi_profilerights', $crit) as $data) {
         $tab[$data['profiles_id']] = ($full ? $data : $data['rights']);
      }
      return $tab;
   }

   static function getAllRights() {
      global $LANG;
      $rights = array();
      foreach(searchReport() as $key => $plug) {
         $mod = (($plug == 'reports') ? $key : "${plug}_${key}");
         if (!isset($plugname[$plug])) {
            // Retrieve the plugin name
            $function         = "plugin_version_$plug";
            $tmp              = $function();
            $plugname[$plug]  = $tmp['name'];
         }

         $field = 'plugin_reports_'.$key;
         if ($plug != 'reports') {
            $field = 'plugin_reports_'.$plug."_".$key;
         }
      
         $rights[] = array('itemtype' => 'PluginReportsReport',
                           'label'    => $plugname[$plug]." - ".$LANG["plugin_$plug"][$key], 
                           'field'    => $field);
      }
      return $rights;
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

      if (TableExists('glpi_plugin_reports_profiles')) { 
         if (!FieldExists('glpi_plugin_reports_profiles','profiles_id')) { // version < 1.5.0
            $create = "CREATE TABLE IF NOT EXISTS `glpi_plugin_reports_profiles` (
                          `id` int(11) NOT NULL auto_increment,
                          `profiles_id` int(11) NOT NULL DEFAULT '0',
                          `report` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                          `access` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
                        PRIMARY KEY (`id`),
                        KEY `report` (`report`),
                        KEY `profiles_id` (`profiles_id`))
                        ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

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
      }


      // -- SINCE 0.85 --
      //Add new rights in glpi_profilerights table
      $profile = new self();
      foreach ($profile->getAllRights() as $data) {
         if (countElementsInTable("glpi_profilerights", "`name` = '".$data['field']."'") == 0) {
            ProfileRight::addProfileRights(array($data['field']));
            $_SESSION['glpiactiveprofile'][$data['field']] = 0;
         }
      }
      
      //Migration old rights in new ones
      if (TableExists('glpi_plugin_reports_profiles')) {
         foreach ($DB->request("SELECT `id` FROM `glpi_profiles`") as $prof) {
            self::migrateOneProfile($prof['id']);
         }
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

      //delete profiles
      $profile_right = new ProfileRight;
      return $profile_right->deleteByCriteria(array("name LIKE 'plugin_reports_%'"));
   }


   /**
    * @see inc/CommonGLPI::getTabNameForItem()
   **/
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType() == 'Profile') {
         if ($item->getField('interface') == 'central') {
            return PluginReportsReport::getTypeName(2);
         }
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType() == 'Profile') {
         if ($item->getField('interface') == 'central') {
            $ID = $item->getField('id');

            $prof = new self();
            $prof->updatePluginRights();
            
            self::showForProfile($item);
         }
      }
      return true;
   }

   /**
    * @since 0.85
    * migrate a right value from old system to the new one
    * @param  [string] $old_right
    * @return [integer] new right
    * @see ../../config/define.php
    */
   static function translateARight($old_right) {
      switch ($old_right) {
         case '': 
            return 0;
         case 'r' :
            return READ;
         case 'w':
            return ALLSTANDARDRIGHT;
         case '0':
         case '1':
            return $old_right;
            
         default :
            return 0;
      }
   }
   
   /**
   * @since 0.85
   * Migration rights from old system to the new one for one profile
   * @param $profiles_id the profile ID
   */
   static function migrateOneProfile($profiles_id) {
      global $DB;

      $profile_right = new ProfileRight;
      $new_rights = array();
      
      foreach ($DB->request('glpi_plugin_reports_profiles', "`profiles_id`='$profiles_id'") as $old_profile_data) {
         $new_right = self::translateARight($old_profile_data['access']);
         $new_rights["plugin_reports_".$old_profile_data['report']] = $new_right;
      }
      $profile_right->updateProfileRights($profiles_id, $new_rights);
   }  
}
?>