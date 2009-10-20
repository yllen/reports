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
// Original Author of file: Dévi Balpe
// Purpose of file:
// ----------------------------------------------------------------------

function defaultValueExists($report, $criteria) {
   global $DB;

   $query = "SELECT `id`
             FROM `glpi_plugin_reports_defaultvalues`
             WHERE `report` = '$report'
                   AND `criteria` = '$criteria'";
   $result = $DB->query($query);

   if ($DB->numrows() == 1) {
      return $DB->result($result,0,"id");
   }
   return 0;
}


function putDefaultValue($report, $criteria, $value) {

   $defaultvalueID = defaultValueExists($report,$criteria); 
   $defaultvalue = new ReportDefaultValue;

   $input["report"]=$report;
   $input["criteria"]=$criteria;
   $input["value"]=$value;

   if (!$defaultvalueID) {
      $defaultvalue->add($input);
   } else {
      $input["id"]=$defaultvalueID;
      $defaultvalue->update($input);
   }
}


function removeDefaultValue($report,$criteria) {

   $defaultvalueID = defaultValueExists($report,$criteria); 
   if ($defaultvalueID) {
      $defaultvalue = new ReportDefaultValue;
      $input["id"]=$defaultvalueID;
      $defaultvalue->delete($input);
   }
}


function getAllDefaultValuesByReport($resport) {

   $defaultValues = array();
   $results = getAllDatasFromTable($defaultvalue->table,"report='$report'");
   foreach ($results as $result) {
      $tmp = new ReportDefaultValue;
      $tmp->fields = $result;
      $defaultValues[] = $tmp;
   }
   return $defaultValues;
}

?>