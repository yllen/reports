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
abstract class PluginReportsAutoCriteria {

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
      return $link . " `" . $this->getSqlField() . "`='" . $this->parameters[$this->getName()] . "' ";
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

?>