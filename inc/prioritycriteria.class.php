<?php

/*
 * @version $Id: HEADER 14684 2011-06-11 06:32:40Z remi $
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
 * Priority selection criteria
 */
class PluginReportsPriorityCriteria extends PluginReportsAutoCriteria {

   function __construct($report, $name = 'priority', $label='') {
      global $LANG;

      parent::__construct($report, $name, $name, ($label ? $label :$LANG['joblist'][2]));
   }


   public function setDefaultValues() {
      $this->addParameter($this->getName(), 1);
   }


   public function displayCriteria() {
      global $LANG;

      $this->getReport()->startColumn();
      echo $this->getCriteriaLabel().'&nbsp;:';
      $this->getReport()->endColumn();

      $this->getReport()->startColumn();
      Ticket::dropdownPriority($this->getName(), $this->getParameterValue(), 1);
      $this->getReport()->endColumn();
   }


   function getSubName() {
      global $LANG;

      if (!$this->getParameterValue()) {
         $priority = $LANG['common'][66];
      } else {
         if ($this->getParameterValue() < 0) {
            $priority = $LANG['search'][16] . Ticket::getPriorityName(abs($this->getParameterValue()));
         } else {
            $priority = Ticket::getPriorityName($this->getParameterValue());
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
         return $link . " " . $this->getSqlField() . "= '" . $this->getParameterValue() . "'";
      }
      if ($this->getParameterValue() < 0) {
         return $link . " " . $this->getSqlField() . ">= '" . abs($this->getParameterValue()) ."'";
      }
   }

}

?>