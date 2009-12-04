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
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------


/**
 * Manage criterias from dropdown tables
 */
class PluginReportsDropdownCriteria extends PluginReportsAutoCriteria {

   //Drodown table
   private $table = "";

   //Should display dropdown's childrens value
   private $childrens = false;

   ///Use entity restriction in the dropdown ? (default is current entity)
   private $entity_restrict = -1;

   //Display dropdown comments
   private $displayComments = false;

   // search for zero if true, else treat zero as "all" (no criteria)
   private $searchzero = false;


   function __construct($report, $name, $table, $label = '') {
      parent :: __construct($report, $name);

      $this->table = $table;
      $this->addCriteriaLabel($name, $label);
   }


   /**
    * Get criteria's related table
    */
   public function getTable() {
      return $this->table;
   }


   /**
    * Will display dropdown childrens (in table in hierarchical)
    */
   public function setWithChildrens() {
      global $CFG_GLPI;

      if (in_array($this->getTable(), $CFG_GLPI["dropdowntree_tables"])) {
         $this->childrens = true;
      }
   }


   /**
    * Will display dropdown childrens (in table in hierarchical)
    */
   public function setSearchZero() {
      $this->searchzero = true;
   }


   /**
    * Set default criteria value to 0 and entity restriction to current entity only
    */
   public function setDefaultValues() {

      $this->addParameter($this->getName(), 0);
      $this->setEntityRestriction($_SESSION["glpiactive_entity"]);
      $this->setDisplayComments();
   }


   /**
    * Show dropdown comments (enable by defaults)
    */
   public function setDisplayComments() {
      $this->displayComments = true;
   }


   /**
    * Hide dropdown comments
    */
   public function setNoDisplayComments () {
      $this->displayComments = false;
   }


   /**
    * Get display comments status
    */
   public function getDisplayComments() {
      return $this->displayComments;
   }


   /**
    * Change criteria's label
    * @param label the new label to display
    * @param name the name of the criteria whose label should be changed (if no name is provided, the default criteria will be used)
    */
   public function setCriteriaLabel ($label,$name='') {

      if ($name == '') {
         $this->criterias_labels[$this->name] = $label;
      } else {
         $this->criterias_labels[$name] = $label;
      }
   }


   /**
    * Change entity restriction
    * Values are :
    * REPORTS_NO_ENTITY_RESTRICTION : no entity restriction (everything is displayed)
    * REPORTS_CURRENT_ENTITY : only values from the current entity
    * REPORTS_SUB_ENTITIES : values from the current entity + sub-entities
    */
   public function setEntityRestriction($restriction) {
      global $CFG_GLPI;

      switch ($restriction) {
         case REPORTS_NO_ENTITY_RESTRICTION :
            $this->entity_restrict = -1;
            break;

         case REPORTS_CURRENT_ENTITY :
            $this->entity_restrict = $_SESSION["glpiactive_entity"];
            break;

         case REPORTS_SUB_ENTITIES :
            $this->entity_restrict = getSonsOf('glpi_entities',$_SESSION["glpiactive_entity"]);
            break;
      }
   }


   /**
    * Get entity restrict status
    */
   public function getEntityRestrict() {
      return $this->entity_restrict;
   }


   /**
    * Get criteria's subtitle
    */
   public function getSubName() {

      return " " . $this->getCriteriaLabel() . " : " .
              getDropdownName($this->getTable(), $this->getParameterValue());
   }


   /**
    * Display criteria in the criteria's selection form
    */
   public function displayCriteria() {
      global $LANG;

      $this->getReport()->startColumn();
      echo $this->getCriteriaLabel();
      $this->getReport()->endColumn();

      $this->getReport()->startColumn();
      $this->displayDropdownCriteria();
      $this->getReport()->endColumn();
   }


   /**
    * Display dropdown
    */
   public function displayDropdownCriteria() {

      dropdownValue($this->getTable(), $this->getName(), $this->getParameterValue(), 
                    $this->getDisplayComments(), $this->getEntityRestrict());
   }


   /**
    * Get SQL code associated with the criteria
    */
   public function getSqlCriteriasRestriction($link = 'AND') {

      if ($this->getParameterValue() || $this->searchzero) {
         if (!$this->childrens) {
            return $link . " " . $this->getSqlField() . "=" . $this->getParameterValue() . " ";
         } else {
            return $link . " " . $this->getSqlField() .
                   " IN (" . implode(',', getSonsOfTreeItem($this->getTable(),
                                                            $this->getParameterValue())) . ") ";
         }
      }
      // Zero => means all => no criteria
      return '';
   }
}

?>