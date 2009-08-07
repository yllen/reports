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

// Original Author of file: Remi Collet
// Purpose of file: display the full rule's catalog (all rules, criterias and actions)
// ----------------------------------------------------------------------

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE=1;
$DBCONNECTION_REQUIRED=0;

$NEEDED_ITEMS=array("rulesengine","affectentity","rule.right","rule.ocs","rule.softwarecategories","rule.tracking");

define('GLPI_ROOT', '../../../..'); 
include (GLPI_ROOT . "/inc/includes.php"); 

includeLocales("rules");

plugin_reports_checkRight("rules","r");

function plugin_reports_rulelist ($rulecollection, $title) {
	global $LANG;
	
	checkRight($rulecollection->right,"r");
	
	$rulecollection->getCollectionDatas(true,true);
	echo "<div align='center'>";
	echo "<table class='tab_cadre' cellpadding='5'>\n";
	echo "<tr><th colspan='6'><a href='".$_SERVER["PHP_SELF"]."'>" . $LANG['plugin_reports']['rules'][1] . "</a> - " . $title . "</th></tr>";

	echo "<tr><th>".$LANG["common"][16]."</th>";
	echo "<th>".$LANG["joblist"][6]."</th>";
	echo "<th colspan='2'>".$LANG["rulesengine"][6]."</th>";
	echo "<th>".$LANG["rulesengine"][7]."</th>";
	echo "<th>".$LANG["common"][60]."</th></tr>\n";
	
	foreach($rulecollection->RuleList->list as $rule) {
		echo "<tr class='tab_bg_1'>";
		echo "<td>" . $rule->fields["name"] . "</td>";

		echo "<td>" . $rule->fields["description"] . "</td>";
		
		if ($rule->fields["match"]==AND_MATCHING) {
			echo "<td>".$LANG["rulesengine"][42]."</td>";
		} else {
			echo "<td>".$LANG["rulesengine"][43]."</td>";
		}

		echo "<td>";
		foreach ($rule->criterias as $criteria){
			echo $rule->getCriteriaName($criteria->fields["criteria"]) . " " .
				getConditionByID($criteria->fields["condition"]) . " " .
				$rule->getCriteriaDisplayPattern($criteria->fields["criteria"],$criteria->fields["condition"],$criteria->fields["pattern"]) . "<br>";
		}
		echo "</td>";
		echo "<td>";
		foreach ($rule->actions as $action){
			echo $rule->getActionName($action->fields["field"]) . " " .
				getActionByID($action->fields["action_type"]) . " " .
				stripslashes($rule->getActionValue($action->fields["field"],$action->fields["value"])) . "<br>";
		}
		echo "</td>";

		if ($rule->fields["active"]) {
			echo "<td>".$LANG["choice"][1]."</td>";
		} else {
			echo "<td>".$LANG["choice"][0]."</td>";
		}
		echo "</tr>\n";
	}
	echo "</table></div>\n";

}
commonHeader($LANG['plugin_reports']['rules'][1],$_SERVER['PHP_SELF'],"utils","report");

$type=(isset($_GET["type"]) ? $_GET["type"] : "");

if ($type=="ldap") {
	
	$rulecollection = new RightRuleCollection();
	plugin_reports_rulelist($rulecollection,$LANG["rulesengine"][19]);
	
} else if ($type=="ocs") {
	
	$rulecollection = new OcsRuleCollection(-1);
	plugin_reports_rulelist($rulecollection,$LANG["rulesengine"][18]);
	
} else if ($type=="track") {
	
	$rulecollection = new TrackingBusinessRuleCollection();
	plugin_reports_rulelist($rulecollection,$LANG["rulesengine"][28]);
	
} else if ($type=="soft") {
	
	$rulecollection = new SoftwareCategoriesRuleCollection();
	plugin_reports_rulelist($rulecollection,$LANG["rulesengine"][37]);
	
} else {
	
	echo "<div align='center'>";
	echo "<table class='tab_cadre' cellpadding='5'>\n";
	echo "<tr><th>" . $LANG['plugin_reports']['rules'][1] . " - " . $LANG["rulesengine"][24] . "</th></tr>";

	if ($CFG_GLPI["ocs_mode"] && haveRight("rule_ocs","r")){
		echo "<tr class='tab_bg_1'><td  align='center'><a href='".$_SERVER["PHP_SELF"]."?type=ocs'><strong>" . 
			$LANG["rulesengine"][18] . "</strong></a></td></tr>";
	}
	if (haveRight("rule_ldap","r")){
		echo "<tr class='tab_bg_1'><td align='center'><a href='".$_SERVER["PHP_SELF"]."?type=ldap'><strong>" .
			$LANG["rulesengine"][19] . "</strong></a></td> </tr>";
	}
	if (haveRight("rule_tracking","r")){
		echo "<tr class='tab_bg_1'><td  align='center'><a href='".$_SERVER["PHP_SELF"]."?type=track'><strong>" . 
			$LANG["rulesengine"][28] . "</strong></a></td></tr>";
	}
	if (haveRight("rule_softwarecategories","r")){
		echo "<tr class='tab_bg_1'><td  align='center'><a href='".$_SERVER["PHP_SELF"]."?type=soft'><strong>" . 
			$LANG["rulesengine"][37] . "</strong></a></td></tr>";
	}
	
	echo "</table></div>\n";
}
	
commonFooter(); 
 
?>
