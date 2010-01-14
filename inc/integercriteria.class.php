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
class PluginReportsIntegerCriteria extends PluginReportsDropdownCriteria {

   private $signe = '=';
   private $min   = 0;
   private $max   = 100;
   private $coef  = 1;

   function __construct($report, $name='value', $label='', $signe='=', $min=0, $max=100, $coef=1) {
      global $LANG;

      parent :: __construct($report,$name,'no_table');
      $this->setOptions($signe,$min,$max,$coef);
      $this->addCriteriaLabel($name, ($label ? $label :$LANG['financial'][21]));
   }

   function setDefaultValues() {
      $this->addParameter($this->getName(),0);
   }

   function setOptions($signe='=', $min=0, $max=100, $coef=1) {
      $this->signe = $signe;
      $this->min   = $min;
      $this->max   = $max;
      $this->coef  = $coef;
   }

   function displayCriteria() {
      global $LANG;
      $this->getReport()->startColumn();
      echo $this->getCriteriaLabel();
      $this->getReport()->endColumn();

      $this->getReport()->startColumn();
      Dropdown::showInteger($this->getName(),$this->getParameterValue(),
                            $this->min, $this->max, 1);
      $this->getReport()->endColumn();
   }

   function getSqlCriteriasRestriction($link = 'AND') {
      $param = $this->getParameterValue();
      return $link." ".$this->getSqlField().$this->signe."'".($param*$this->coef)."' ";
   }
}
?>