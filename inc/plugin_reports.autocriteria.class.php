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
 * AutCriteria class manage a new search & filtering criteria
 * It manage display & sql code associated
 */
abstract class AutoCriteria {

   //Criteria's internal name
   private $name = "";

   //Label of the criteria (refers to an entry in the locale file)
   private $criterias_labels = array ();

   //Parameters are stored as name => value
   private $parameters = array ();

   //Field in the SQL request (can be table.field)
   private $sql_field = "";

   //Report in which the criteria will be added to
   private $report = null;


   /**
    * Contructor
    * @param report the report in which the criteria is added
    * @param name the criteria's name
    * @param sql_field the sql field associated with the criteria (can be set later with setSqlField).
    *          - Sql_field can be prefixed with table name
    *          - if sql_field=='' then sql_field=name
	 */
   function __construct($report, $name, $sql_field = '') {

      $this->setName($name);

      if ($sql_field == '') {
         $this->setSqlField($name);
      } else {
         $this->setSqlField($sql_field);
      }
      $this->setReport($report);
      $this->report->addCriteria($this);
      $this->setDefaultValues();
   }

   //-------------- Getters ------------------//

   /**
    * Get report object
    */
   function getReport() {
      return $this->report;
   }


   /**
    * Get all parameters associated with the criteria
    */
   function getParameterValue() {
      return $this->parameters[$this->name];
   }


   /**
    * Get sql_field associated with the criteria
    * @return the sql_field associated with the criteria
    */
   function getSqlField() {
      return $this->sql_field;
   }


   /**
    * Get a specific parameter
    * @param parameter the parameter's name
    * @return the parameter's value
    */
   function getParameter($parameter) {
      return $this->parameters[$parameter];
   }


   /**
    * Get the label associated with the criteria
    * @return label associated with the criteria
    */
   function getCriteriaLabel() {
      return $this->criterias_labels[$this->getName()];
   }


   /**
    * Get the criteria's title
    * @param criteria's title
    */
   function getSubName() {
      return "";
   }


   /**
    * Get criteria's name
    * @return criteria's name
    */
   function getName () {
      return $this->name;
   }


   /**
    * Get all the parameters associated with the criteria
    * @return the parameters
    */
   function getParameters() {
      return $this->parameters;
   }


   /**
    * Build Sql code associated with the criteria (to be included into the global report's sql query)
    * @return a where sql request
    */
   public function getSqlCriteriasRestriction($link = 'AND') {
      return $link . " " . $this->getSqlField() . "='" . $this->parameters[$this->getName()] . "' ";
   }


   /**
    * Get URL to be used by bookmarking system
    * @return the bookmark's url associated with the criteria
    */
   public function getBookmarkUrl() {

      $url = "";
      foreach ($this->parameters as $parameter => $value) {
         $url .= '&' .
         $parameter . '=' . $value;
      }
      return $url;
   }

   //-------------- Setters ------------------//

   /**
    * Set report
    * @report the report in which the criteria is put
    */
   function setReport($report) {
      $this->report = $report;
   }


   /**
    * Set criteria's parameters
    * @parameter the parameters
    */
   function setParameters($parameters) {
      $this->parameters = $parameters;
   }


   /**
    * Add a new parameter to the criteria
    * If parameter exists, it overwrites the existing values
    * @param name parameter's name
    * @value parameter's value
    */
   function addParameter($name, $value) {
      $this->parameters[$name] = $value;
   }


   /**
    * Set sql field associated with the criteria
    * @param sql_fiel sql field associated with the criteria
    */
   function setSqlField($sql_field) {
      $this->sql_field = $sql_field;
   }


   /**
    * Set criteria's name
    * @param criteria's name
    */
   function setName($name) {
      $this->name = $name;
   }


   /**
    * Add a label to the criteria
    * @param name criteria's name
    * @param label add criteria's label
    */
   function addCriteriaLabel($name, $label) {
      $this->criterias_labels[$name] = $label;
   }


   /**
    * Set criteria's default value()
    * This method is abstract ! Needs to be implemented in each criteria
    */
   abstract public function setDefaultValues();

   //-------------- Other ------------------//

   /**
    * Add GET & POST values in order to get pager & export working correctly
    * @param fields : criterias's values to be set
    */
   function managePostGetValues($field) {

      if (isset ($_GET[$field])) {
         $_POST[$field] = $this->parameters[$field] = $_GET[$field];
      } else {
         if (isset ($_POST[$field])) {
            $this->parameters[$field] = $_POST[$field];
         } else {
            $_POST[$field] = $this->parameters[$field];
         }
      }
   }


   /**
    * Display criteria in the criteria's selection form
    * This method is abstract : needs to be implemented by each criteria !
    */
   abstract public function displayCriteria();


   /**
    * Set parameter's values get the criteria working
    */
   public function manageCriteriaValues() {

      foreach ($this->parameters as $parameter => $value) {
         $this->managePostGetValues($parameter);
      }
   }

}


/**
 * Manage criterias from dropdown tables
 */
class GenericDropdownCriteria extends AutoCriteria {

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
            $this->entity_restrict = getEntitySons($_SESSION["glpiactive_entity"]);
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
      dropdownValue($this->getTable(), $this->getName(), $this->getParameterValue(), $this->getDisplayComments(), $this->getEntityRestrict());
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



/**
 * Location selection criteria
 */
class LocationCriteria extends GenericDropdownCriteria {

   function __construct($report) {
      global $LANG;
      parent :: __construct($report, "location", "glpi_locations", $LANG['common'][15]);
   }


   public function setDefaultLocation($location) {
      $this->addParameter('location', $location);
   }


   /**
    * Deprecated : replace by setWithChildrens
    */
   public function setLocationWithChildrens() {
      $this->setWithChildrens();
   }

}



/**
 * Group selection criteria
 */
class GroupCriteria extends GenericDropdownCriteria {

   function __construct($report) {
      global $LANG;
      parent :: __construct($report, "group", "glpi_groups", $LANG['common'][35]);
   }
}



/**
 * Criteria which allows to select a date interval
 */
class DateIntervalCriteria extends AutoCriteria {

   function __construct($report,$sql_field='') {
      parent :: __construct($report, "date-interval",$sql_field);
   }


   public function setStartDate($startdate) {
      $this->addParameter("startdate", $startdate);
   }


   function setEndDate($enddate) {
      $this->addParameter("enddate", $enddate);
   }


   public function setDefaultValues() {
      $this->setStartDate(date("Y-m-d"));
      $this->setEndDate(date("Y-m-d"));
   }


   public function displayCriteria() {
      global $LANG;

      $this->getReport()->startColumn();
      echo $LANG['search'][8];
      $this->getReport()->endColumn();

      $this->getReport()->startColumn();
      showDateFormItem("startdate", $this->getParameter("startdate"), false);
      $this->getReport()->endColumn();

      $this->getReport()->startColumn();
      echo $LANG['search'][9] . "</td>";
      $this->getReport()->endColumn();

      $this->getReport()->startColumn();
      showDateFormItem("enddate", $this->getParameter("enddate"), false);
      $this->getReport()->endColumn();
   }


   public function getSqlCriteriasRestriction($link = 'AND') {
      return $link . " " . 
             $this->getSqlField() . " >= '" . $this->getParameter("startdate") . " 00:00:00' AND " . 
             $this->getSqlField() . "<='" . $this->getParameter("enddate") . " 23:59:59' ";
   }


   function getSubName() {
      global $LANG;

      return (isset($LANG['plugin_reports']['subname'][$this->getName()]) 
              ? $LANG['plugin_reports']['subname'][$this->getName()] : '') .
             " (" . convDate($this->getParameter("startdate")) . "," . 
                convDate($this->getParameter("enddate")) . ")";
   }

}



/**
 * Criteria which allows to select a time interval
 */
class TimeIntervalCriteria extends AutoCriteria {

   function __construct($report,$sql_field='') {
      parent :: __construct($report, "time-interval",$sql_field);
   }


   public function setDefaultValues() {
      $this->setStartTime(date("Y-m-d"));
      $this->setEndTime(date("Y-m-d"));
   }


   function setStartTime($starttime) {
      $this->addParameter('starttime',$starttime);
   }


   function setEndtime($endtime) {
      $this->addParameter('endtime',$endtime);
   }


   function displayCriteria() {
      global $LANG;

      $this->getReport()->startColumn();
      echo $LANG['job'][21] . " " . $LANG['buttons'][33];
      $this->getReport()->endColumn();

      $this->getReport()->startColumn();
      dropdownHours("starttime", $this->getParameter('starttime'));
      $this->getReport()->endColumn();

      $this->getReport()->startColumn();
      echo $LANG['job'][21] . " " . $LANG['buttons'][32];
      $this->getReport()->endColumn();

      $this->getReport()->startColumn();
      dropdownHours("endtime", $this->getParameter('endtime'));
      $this->getReport()->endColumn();
   }


   function getSqlCriteriasRestriction($link = 'AND') {

      if ($this->getParameter("starttime") < $this->getParameter("endtime")) {
         // ex  08:00:00 <= time < 18:00:00
         return " $link TIME(".$this->getSqlField().") >= '".$this->getParameter('starttime'). ":00'
                 AND TIME(" .$this->getSqlField(). ") < '" .$this->getParameter('endtime'). ":00'";
      } else {
         // ex time < 08:00:00 or 18:00:00 <= time
         return " $link (TIME(". $this->getSqlField().") >= '".$this->getParameter('starttime').":00' 
                         OR TIME(".$this->getSqlField().") < '".$this->getParameter('endtime').":00')";
      }
   }


   function getSubName() {
      global $LANG;

      return " " . (isset ($LANG['plugin_reports']['subname'][$this->getName()]) 
                    ? $LANG['plugin_reports']['subname'][$this->getName()] : '') .
             " (" . $this->getParameter('starttime') . "," . $this->getParameter('endtime') . ")";
   }

}



/**
 * Priority selection criteria
 */
class PriorityCriteria extends AutoCriteria {

   function __construct($report,$sql_field='') {
      global $LANG;
      parent :: __construct($report, "priority",$sql_field);

      $this->addCriteriaLabel($this->getName(), $LANG['joblist'][2]);
   }


   public function setDefaultValues() {
      $this->addParameter($this->getName(), 1);
   }


   public function displayCriteria() {
      global $LANG;

      $this->getReport()->startColumn();
      echo $this->getCriteriaLabel();
      $this->getReport()->endColumn();

      $this->getReport()->startColumn();
      dropdownPriority($this->getName(), $this->getParameterValue(), 1);
      $this->getReport()->endColumn();
   }


   function getSubName() {
      global $LANG;

      if (!$this->getParameterValue()) {
         $priority = $LANG['common'][66];
      } else {
         if ($this->getParameterValue() < 0) {
            $priority = $LANG['search'][16] . getPriorityName(abs($this->getParameterValue()));
         } else {
            $priority = getPriorityName($this->getParameterValue());
         }
      }
      return " " . $this->getCriteriaLabel() . " : " . $priority;
   }


   function setDefaultPriorityValue($priority) {
      $this->addParameter($this->getName(), $priority);
   }


   public function getSqlCriteriasRestriction($link = 'AND') {
      //If value > 0 : a priority is selected
      //If value == 0 : no priority selected
      //If value < 0 : means "priority above the priority selected"

      if ($this->getParameterValue() > 0) {
         return $link . " " . $this->getSqlField() . " = '" . $this->getParameterValue() . "'";
      }
      if ($this->getParameterValue() < 0) {
         return $link . " " . $this->getSqlField() . " >= '" . abs($this->getParameterValue()) ."'";
      }
   }

}



/**
 * Ticket status selection criteria
 */
class TicketStatusCriteria extends GenericDropdownCriteria {

   function __construct($report) {
      global $LANG;
      parent :: __construct($report, "status", "no_table", $LANG['joblist'][0]);
   }


   function getSubName() {
      return " " . $this->getCriteriaLabel() . " : " . getStatusName($this->getParameterValue());
   }


   public function displayDropdownCriteria() {
      dropdownStatus($this->getName(), $this->getParameterValue());
   }

}



/**
 * Devices status selection criteria
 */
class StatusCriteria extends GenericDropdownCriteria {

   function __construct($report) {
      global $LANG;
      parent :: __construct($report, "state", "glpi_states", $LANG['joblist'][0]);
   }

}



/**
 * Ticket category selection criteria
 */
class TicketCategoryCriteria extends GenericDropdownCriteria {

   function __construct($report) {
      global $LANG;
      parent :: __construct($report, "category", "glpi_ticketscategories", $LANG['common'][36]);
   }

}



/**
 * User selection criteria
 */
class UserCriteria extends GenericDropdownCriteria {

   function __construct($report) {
      global $LANG;
      parent :: __construct($report, "user", "glpi_users", $LANG['common'][34]);
   }


   public function displayDropdownCriteria() {

      dropdownUsers($this->getName(),$this->getParameterValue(),'all',0,$this->getDisplayComments(),
                    $this->getEntityRestrict());
   }

}



/**
 * User types selection criteria
 */
class UserTypeCriteria extends GenericDropdownCriteria {

   function __construct($report) {
      global $LANG;
      parent :: __construct($report, "type", "glpi_userscategories", $LANG['common'][34]." : ".
                            $LANG['common'][17]);
   }

}



/**
 * User titles selection criteria
 */
class UserTitleCriteria extends GenericDropdownCriteria {

   function __construct($report) {
      global $LANG;
      parent :: __construct($report, "title", "glpi_userstitles", $LANG['common'][34]." : ".
                            $LANG['common'][57]);
   }

}



/**
 * User titles selection criteria
 */
class EnterpriseCriteria extends GenericDropdownCriteria {

   function __construct($report) {
      global $LANG;

      //Add enterprise includes
      include_once(GLPI_ROOT."/inc/enterprise.class.php");
      include_once(GLPI_ROOT."/inc/enterprise.function.php");

      parent :: __construct($report, "suppliers_id", "glpi_suppliers", $LANG['financial'][26]);
   }

}



/**
 * User titles selection criteria
 */
class RequestTypeCriteria extends GenericDropdownCriteria {

   function __construct($report) {
      global $LANG;

      //No need to specify a sql field, because priorities are not stored in DB
      parent :: __construct($report, "request_type", "", $LANG['job'][44]);
   }


   //Dropdown priorities is not a generic dropdown, so the function needs to be overwritten
   public function displayDropdownCriteria() {
      dropdownRequestType($this->getName(),$this->getParameterValue());
   }


   function getSubName() {

      if ($this->getParameterValue() > 0) {
         return " ".$this->getCriteriaLabel()." : ".getRequestTypeName($this->getParameterValue());
      }
   }


   public function setDefaultValues() {
      $this->addParameter($this->getName(), 1);
   }

}

?>