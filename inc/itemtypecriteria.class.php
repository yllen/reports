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
 * Ticket status selection criteria
 */
class PluginReportsItemTypeCriteria extends PluginReportsDropdownCriteria {
   private $types = array();

   function __construct($report, $name='itemtype',$label='',$types=array()) {
      global $LANG;

      parent::__construct($report, $name, "no_table", ($label ? $label : $LANG['state'][6]));
      if (count($types)) {
         $this->types = $types;
      } else {
         $this->types = Ticket::getAllTypesForHelpdesk();
         $this->types[''] = $LANG['common'][66];
      }
   }


   function getSubName() {
      global $LANG;

      $itemtype = $this->getParameterValue();
      if ($itemtype && class_exists($itemtype)) {
         $item = new $itemtype();
         $name = $item->getTypeName();
      } else {
         //$name = $LANG['common'][66];
         // All
         return '';
      }
      return " " . $this->getCriteriaLabel() . " : " . $name;
   }


   public function displayDropdownCriteria() {
      Dropdown::showFromArray($this->getName(), $this->types, array('value'=>$this->getParameterValue()));
   }
}

?>