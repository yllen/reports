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
 * Criteria which allows to select a date interval
 */
class PluginReportsDateIntervalCriteria extends PluginReportsAutoCriteria {

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

?>