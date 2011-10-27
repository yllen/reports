<?php

/*
 * @version $Id$
 -------------------------------------------------------------------------
 reports - Additional reports plugin for GLPI
 Copyright (C) 2003-2011 by the reports Development Team.

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

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

/**
 * User titles selection criteria
 */
class PluginReportsTextCriteria extends PluginReportsDropdownCriteria {


   function __construct($report, $name='value', $label='') {
      global $LANG;

      parent::__construct($report, $name, NOT_AVAILABLE, ($label ? $label :$LANG['common'][16]));
   }


   function setDefaultValues() {
      $this->addParameter($this->getName(), '');
   }


   function displayCriteria() {
      global $LANG;

      $this->getReport()->startColumn();
      echo $this->getCriteriaLabel().'&nbsp;:';
      $this->getReport()->endColumn();

      $this->getReport()->startColumn();
      echo "<input type='text' name='".$this->getName()."' value='". $this->getParameterValue() ."'>";
      $this->getReport()->endColumn();
   }


   /**
    * Get criteria's subtitle
   **/
   public function getSubName() {

      $param = $this->getParameterValue();
      if ($param) {
         return $this->getCriteriaLabel().' : '.$this->getParameterValue();
      }
      return '';
   }


   function getSqlCriteriasRestriction($link = 'AND') {

      $param = $this->getParameterValue();
      if ($param) {
         return Search::makeTextCriteria($this->getSqlField(), $param, false, $link);
      }
      return '';
   }

}
?>