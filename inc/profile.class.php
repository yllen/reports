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
 @authors    Nelly Mahu-Lasson, Remi Collet, Alexandre Delaunay
 @copyright Copyright (c) 2009-2021 Reports plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.glpi-project.org/projects/reports
 @link      http://www.glpi-project.org/
 @since     2009
 --------------------------------------------------------------------------
 */

class PluginReportsProfile extends Profile {
   static $rightname = 'profile';

   /**
    * @param $prof   Profile object
   **/
   static function showForProfile(Profile $prof){
      global $DB;

      $canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE]);

      if ($canedit) {
         echo "<form method='post' action='".$prof->getFormURL()."'>";
      }

      $rights = self::getAllRights();
      $prof->displayRightsChoiceMatrix($rights,
                                       ['canedit'       => $canedit,
                                        'default_class' => 'tab_bg_2',
                                        'title'         => __('Rights management by profil',
                                                                   'reports')]);
      if ($canedit) {
         echo "<div class='center'>";
         echo Html::hidden('id', ['value' => $prof->getField('id')]);
         echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
         echo "</div>\n";
         Html::closeForm();
      }
      echo "</div>";

   }


   /**
    * @param $report
   **/
   static function showForReport($report) {
      global $DB;

      /* call from front/config.form.php
       * $report = "bar" (from reports) or "foo_bar" (other plugins)
       */
      if (empty($report) || !Session::haveRight('profile', READ)) {
         return false;
      }
      $current = self::getAllProfilesRights(["name = 'plugin_reports_$report'"]);
      $canedit = Session::haveRight('profile', UPDATE);

      if ($canedit) {
         echo "<form action='".$_SERVER['PHP_SELF']."' method='post'>\n";
      }

      echo "<table class='tab_cadre'>\n";
      echo "<tr><th colspan='2'>".__('Profils rights', 'reports')."</th></tr>\n";

      foreach ($DB->request('glpi_profiles',['SELECT' => ['id', 'name'],
                                             'ORDER'  => 'name']) as $data) {
         echo "<tr class='tab_bg_1'><td>" . $data['name'] . "&nbsp: </td><td>";

         $profrights = ProfileRight::getProfileRights($data['id'], ['statistic', 'reports']);
         $canstat    = (isset($profrights['statistic']) && $profrights['statistic']);
         $canreport  = (isset($profrights['reports'])   && $profrights['reports']);

         if ((isStat($report) && $canstat)
             || (!isStat($report) && $canreport)) {
            Profile::dropdownRight($data['id'],
                                   ['value'    => (isset($current[$data['id']])
                                                         ? $current[$data['id']] : 0),
                                    'nonone'  => 0,
                                    'noread'  => 0,
                                    'nowrite' => 1]);
         } else {
            // Can't access because missing right from GLPI core
            echo "<input type='hidden' name='".$data['id']."' value='NULL'>".__('No access')." *";
         }
         echo "</td></tr>\n";
      }
      echo "<tr class='tab_bg_4'><td colspan='2'>* ";
      if (isStat($report)) {
         echo __('No right on Assistance / Statistics', 'reports');
      } else {
         echo __('No right on Tools / Reports', 'reports');
      }
      echo "</tr>";

      if ($canedit) {
         echo "<tr class='tab_bg_1'><td colspan='2' class='center'>";
         echo "<input type='hidden' name='report' value='$report'>";
         echo "<input type='submit' name='update' value='"._sx('button', 'Update')."' ".
                "class='submit'>";
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

      /* call from front/config.form.php
       * $report = "bar" (from reports) or "foo_bar" (other plugins)
       */
      $prof      = new ProfileRight();
      $report    = $input['report'];
      $rightname = "plugin_reports_$report";
      $current   = self::getAllProfilesRights(["name = '$rightname'"], true);

      foreach($input as $profiles_id => $right) {
         if ($right == 'NULL') {
            $right = 0;
         }
         if (is_numeric($profiles_id)) {
            if (isset($current[$profiles_id])) {
               $prof->update(['id'     => $current[$profiles_id]['id'],
                              'rights' => $right]);
            } else if ($right) {
               $prof->add(['profiles_id' => $profiles_id,
                           'name'        => $rightname,
                           'rights'      => $right]);
            }
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

      $rights = [];
      foreach ($reports as $report => $plug) {
         if ($plug == 'reports') {
            $rights["plugin_reports_$report"] = 1;
         } else {
            $rights["plugin_reports_${plug}_${report}"] = 1;
         }
      }

      $current_rights = [];
      foreach ($DB->request('glpi_profilerights',
                            ['SELECT'   => 'name',
                             'DISTINCT' => true,
                             'WHERE'    => ['name' => ['LIKE', 'plugin_reports_%']]]) as $data) {
         $current_rights[$data['name']] = 1;
      }

      // Remove old reports
      foreach($current_rights as $right => $value) {
         if (!isset($rights[$right])) {
            // Delete the lines for old reports
            $profile_right->deleteByCriteria(['name' => $right]);
         } else {
            unset($rights[$right]);
         }
      }
/*
      // Add new reports
      $rights_name = array_keys($rights);
      ProfileRight::addProfileRights($rights_name);
      if ($_SESSION['glpiactiveprofile']['id'] == 4) {
         $profile_right->updateProfileRights(4, $rights);
      }*/
   }


   /**
    * @param $crit
    * @param $full   (false by default)
   **/
   static function getAllProfilesRights($crit, $full=false) {
      global $DB;

      $tab = [];

      foreach ($DB->request('glpi_profilerights', $crit) as $data) {
         $tab[$data['profiles_id']] = ($full ? $data : $data['rights']);
      }
      return $tab;
   }


   static function getAllRights() {
      global $LANG;

      $rights = [];

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

         $rights[] = ['itemtype' => 'PluginReportsReport',
                      'label'    => $plugname[$plug]." - ".$LANG["plugin_$plug"][$key],
                      'field'    => $field];
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


   static function install(Migration $mig) {
      global $DB;


      if ($DB->tableExists('glpi_plugin_reports_profiles')) {
         if ($DB->fieldExists('glpi_plugin_reports_profiles','ID')) { // version installee < 1.4.0
            $query = "ALTER TABLE `glpi_plugin_reports_profiles`
                      CHANGE `ID` `id` int(11) NOT NULL auto_increment";
            $DB->queryOrDie($query, "CHANGE ID: ".$DB->error());
         }

         if (!$DB->fieldExists('glpi_plugin_reports_profiles','profiles_id')) { // version < 1.5.0
            $mig->renameTable('glpi_plugin_reports_profiles', 'glpi_plugin_reports_oldprofiles');
            $mig->executeMigration();

            $fields = $DB->list_fields('glpi_plugin_reports_oldprofiles');
            unset($fields['id']);
            unset($fields['profile']);
            foreach($fields as $field => $descr) {
               $query = "INSERT INTO `glpi_plugin_reports_profiles`
                                (`profiles_id`, `report`, `access`)
                          SELECT `id`, '$field', `$field`
                          FROM `glpi_plugin_reports_oldprofiles`
                          WHERE `$field` IS NOT NULL";
               $DB->queryOrDie($query, "LOAD TABLE profiles: ".$DB->error());
            }

            $mig->dropTable('glpi_plugin_reports_oldprofiles');
         }


         // -- SINCE 0.85 --
         //Add new rights in glpi_profilerights table
         $profileRight = new ProfileRight();

         foreach ($DB->request('glpi_plugin_reports_profiles') as $data) {
            $right['profiles_id']   = $data['profiles_id'];
            $right['name']          = "plugin_reports_".$data['report'];
            $droit                  = $data['access'];
            if ($droit == 'r') {
               $right['rights'] = 1;
               $profileRight->add($right);
            }
         }
         $mig->dropTable('glpi_plugin_reports_profiles');
      }

   }


   static function uninstall(Migration $mig) {
      global $DB;

      $tables = ['glpi_plugin_reports_profiles',
                 'glpi_plugin_reports_oldprofiles',
                 'glpi_plugin_reports_doublons_backlist',
                 'glpi_plugin_reports_doublons_backlists'];

      foreach ($tables as $table) {
         $mig->dropTable($table);
      }

      //Delete rights associated with the plugin
      $query = "DELETE
                FROM `glpi_profilerights`
                WHERE `name` LIKE 'plugin_reports_%'";
      $DB->queryOrDie($query, $DB->error());
   }


   /**
    * @see inc/CommonGLPI::getTabNameForItem()
   **/
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $DB;

      if ($item->getType() == 'Profile') {
         if ($item->getField('interface') == 'central') {
            $nb = 0;
            if (Session::haveRight('reports', READ)) {
               if ($_SESSION['glpishow_count_on_tabs']) {
                  // Set number of tasks
                  $data = $DB->request('glpi_profilerights',
                                      ['COUNT' => 'cpt',
                                       'WHERE' => ['profiles_id' => $_GET['id'],
                                                   'name'        => ['LIKE', 'plugin_reports_%'],
                                                   'rights'      => 1]])->next();

                  $nb = (isset($data['cpt']) && $data['cpt'] > 0) ? $data['cpt'] : 0;

               }
               return self::createTabEntry(PluginReportsReport::getTypeName($nb), $nb);
            }
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

  }
