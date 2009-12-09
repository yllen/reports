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

   public $table = "glpi_plugin_reports_profiles";
   public $type  = 'PluginReportsProfile';


   //if profile deleted
   function cleanProfiles($id) {
      global $DB;

      $query = "DELETE 
                FROM `glpi_plugin_reports_profiles`
                WHERE `id` = '$id' ";
      $DB->query($query);
   }


   function showForm($target,$id){
      global $LANG,$DB;

      if ($id > 0){
         $this->check($id,'r');
      } else {
         $this->check(-1,'w');
         $this->getEmpty();
      }

      $canedit=$this->can($id,'w');

      echo "<form action='$target' method='post'>";
      echo "<table class='tab_cadre_fixe'>"; 
      echo "<tr><th colspan='3' class='center b'>".
             $LANG['plugin_reports']['config'][4]." ".$this->fields["profile"]."</th></tr>";

      $tab = searchReport(GLPI_ROOT."/plugins/reports/report");

      foreach($tab as $key => $value) {
         echo "<tr class='tab_bg_1'>";
         if (strpos($key,'stat') === false) {
            echo "<td>".$LANG['Menu'][6]."</td>";
         } else {
            echo "<td>".$LANG['Menu'][13]."</td>";	
         }
         echo "<td>".$LANG['plugin_reports'][$key][1]." :</td><td>";
         dropdownNoneReadWrite($key,(isset($this->fields[$key])?$this->fields[$key]:''),1,1,0);
         echo "</td></tr>";	
      }

      if ($canedit) {
         echo "<tr class='tab_bg_1'>";
         echo "<td class='center' colspan='3'>";
         echo "<input type='hidden' name='id' value=$id>";
         echo "<input type='submit' name='update_user_profile' value='".
                $LANG['buttons'][7]."' class='submit'>";
         echo "</td></tr>\n";
      }
      echo "</table></form>";
   }


   function updateRights($id,$rights) {
      global $DB;

      // Add missing profiles
      $DB->query("INSERT INTO 
                  `glpi_plugin_reports_profiles` (`id`, `profile`) 
                  (SELECT `id`, `name`
                   FROM `glpi_profiles`
                   WHERE `id` NOT IN (SELECT `id` 
                                      FROM `glpi_plugin_reports_profiles`))");

      $current_rights = $this->fields;
      unset($current_rights["id"]);
      unset($current_rights["profile"]);
      foreach($current_rights as $right => $value) {
         if (!isset($rights[$right])) {
            // Delete the columns for old reports
            $DB->query("ALTER TABLE 
                        `".$this->table."` 
                        DROP COLUMN `".$right."`");
         } else {
            unset($rights[$right]);
         }
      }

      foreach ($rights as $key=>$right) {
         // Add the column for new report
         $DB->query("ALTER TABLE 
                     `".$this->table."` 
                     ADD COLUMN `".$key."` char(1) DEFAULT NULL");
         // Add "read" write to Super-admin
         $DB->query("UPDATE 
                     `".$this->table."` 
                     SET `".$key."`='r' 
                     WHERE `id` = '4'");
      }

      // Delete unused profiles
      $DB->query("DELETE 
                  FROM `glpi_plugin_reports_profiles` 
                  WHERE `id` NOT IN (SELECT `id`
                                     FROM `glpi_profiles`)");
   }


   static function changeprofile() {

      $prof = new self();
      if ($prof->getFromDB($_SESSION['glpiactiveprofile']['id'])) {
         $_SESSION["glpi_plugin_reports_profile"]=$prof->fields;
      } else {
         unset($_SESSION["glpi_plugin_reports_profile"]);
      }
   }


   /**
    * Create access rights for an user
    * @param id the user id
    */
   function createaccess($id) {
      global $DB;

      $Profile = new Profile();
      $Profile->GetfromDB($id);
      $name = $Profile->fields["profil"];

      $query = "INSERT INTO 
                `glpi_plugin_reports_profiles` (`id`, `profile`) 
                VALUES ('$id', '$name');";
      $DB->query($query);
   }


   /**
    * Look for all the plugins, and update rights if necessary
    */
   function updatePluginRights($path) {

      $this->getEmpty();
      $tab = searchReport($path, 1);
      $this->updateRights(-1, $tab);
   
      return $tab;
   }


}

?>