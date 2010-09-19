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

   function __construct($report, $name='value', $label='', $signe='', $min=0, $max=100, $coef=1, $unit='') {
      global $LANG;

      parent :: __construct($report,$name,'no_table');
      $this->setOptions($signe,$min,$max,$coef,$unit);
      $this->addCriteriaLabel($name, ($label ? $label :$LANG['financial'][21]));
   }

   function setDefaultValues() {
      $this->addParameter($this->getName(),0);
      $this->addParameter($this->getName().'_sign','<=');
   }

   function setOptions($signe='', $min=0, $max=100, $coef=1, $unit='') {
      $this->signe = $signe;
      $this->min   = $min;
      $this->max   = $max;
      $this->coef  = $coef;
      $this->unit  = $unit;
   }

   function displayCriteria() {
      global $LANG;
      $this->getReport()->startColumn();
      echo $this->getCriteriaLabel().'&nbsp;:';
      $this->getReport()->endColumn();

      $this->getReport()->startColumn();
      if (empty($this->signe)) {
         Dropdown::showFromArray($this->getName()."_sign",
                                 array('<=' => '<=', '>=' => '>='),
                                 array('value' => unclean_cross_side_scripting_deep($this->getParameter($this->getName()."_sign"))));
         echo "&nbsp;";
      }
      Dropdown::showInteger($this->getName(),$this->getParameterValue(),
                            $this->min, $this->max, 1);
      echo '&nbsp; '.$this->unit;

      $this->getReport()->endColumn();
   }

   /**
    * Get criteria's subtitle
    */
   public function getSubName() {
      global $LANG;

      $value = $this->getParameterValue();
      return $this->getCriteriaLabel().' '.$this->getSign()." $value ".$this->unit;
   }

   function getSign() {
      if (empty($this->signe)) {
         return unclean_cross_side_scripting_deep($this->getParameter($this->getName()."_sign"));
      }
      return $this->signe;
   }

   function getSqlCriteriasRestriction($link = 'AND') {
      $param = $this->getParameterValue();
      return $link." `".$this->getSqlField()."`".$this->getSign()."'".($param*$this->coef)."' ";
   }
}
?>