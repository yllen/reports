<?php

/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

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
 along with GLPI; if not, write to the Free Software Foundation, Inc.,
 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
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
    */
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
         return makeTextCriteria($this->getSqlField(), $param, false, $link);
      }
      return '';
   }
}
?>