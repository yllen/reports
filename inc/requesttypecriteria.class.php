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
 * User titles selection criteria
 */
class PluginReportsRequestTypeCriteria extends PluginReportsDropdownCriteria {

   function __construct($report) {
      global $LANG;

      //No need to specify a sql field, because priorities are not stored in DB
      parent :: __construct($report, "request_type", "", $LANG['job'][44]);
   }


   //Dropdown priorities is not a generic dropdown, so the function needs to be overwritten
   public function displayDropdownCriteria() {
      Dropdown::dropdownValue('glpi_requesttypes', $this->getName(),$this->getParameterValue());
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