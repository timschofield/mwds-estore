<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the 
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 ********************************************************************************/
/*********************************************************************************
 * $Header:  vtiger_crm/sugarcrm/modules/Calls/ListView.php,v 1.3 2004/10/29 09:55:09 jack Exp $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('XTemplate/xtpl.php');
require_once("data/Tracker.php");
require_once('modules/Calls/Call.php');
require_once('themes/'.$theme.'/layout_utils.php');
require_once('include/logging.php');
require_once('include/ListView/ListView.php');

global $mod_strings;
global $app_strings;
global $app_list_strings;

global $list_max_entries_per_page;
global $urlPrefix;

$log = LoggerManager::getLogger('call_list');

global $currentModule;
global $current_language;
$current_module_strings = return_module_language($current_language, 'Calls');

global $image_path;
global $theme;

// focus_list is the means of passing data to a ListView.
global $focus_list;

if (isset($_REQUEST['current_user_only'])) $current_user_only = $_REQUEST['current_user_only'];

if (!isset($_REQUEST['search_form']) || $_REQUEST['search_form'] != 'false') {
	// Stick the form header out there.
	$search_form=new XTemplate ('modules/Calls/SearchForm.html');
	$search_form->assign("MOD", $mod_strings);
	$search_form->assign("APP", $app_strings);
	
	if(isset($_REQUEST['query'])) {
		if(isset($_REQUEST['name'])) $search_form->assign("NAME", $_REQUEST['name']);
		if(isset($_REQUEST['contact_name'])) $search_form->assign("CONTACT_NAME", $_REQUEST['contact_name']);
		if(isset($current_user_only)) $search_form->assign("CURRENT_USER_ONLY", "checked");
	}
	$search_form->parse("main");
	
	echo get_form_header($mod_strings['LBL_SEARCH_FORM_TITLE'], "", false);
	$search_form->out("main");
	echo get_form_footer();
	echo "\n<BR>\n";
}



$where = "";

$seedCall = new Call();
if(isset($_REQUEST['query']))
{
	// we have a query
	if(isset($_REQUEST['name'])) $name = $_REQUEST['name'];
	if(isset($_REQUEST['contact_name'])) $contact_name = $_REQUEST['contact_name'];
	if(isset($_REQUEST['date_start'])) $date_start = $_REQUEST['date_start'];
	if(isset($_REQUEST['location'])) $location = $_REQUEST['location'];

	$where_clauses = Array();
	if(isset($current_user_only) && $current_user_only != "") array_push($where_clauses, "calls.assigned_user_id='$current_user->id'");

	if(isset($name) && $name != '')
	{
		array_push($where_clauses, "calls.name like '".PearDatabase::quote($name)."%'");
	}
	if(isset($contact_name) && $contact_name != '')
	{
		$contact_names = explode(" ", $contact_name);
		foreach ($contact_names as $name) {
			array_push($where_clauses, "(contacts.first_name like '".PearDatabase::quote($name)."%' OR contacts.last_name like '".PearDatabase::quote($name)."%')");
		}
	}
	if(isset($date_start) && $date_start != '')
	{
		array_push($where_clauses, "calls.date_start like '".PearDatabase::quote($date_start)."%'");
	}

	$where = "";
	if (isset($where_clauses)) {
		foreach($where_clauses as $clause)
		{
			if($where != "")
			$where .= " and ";
			$where .= $clause;
		}
	}
	$log->info("Here is the where clause for the list view: $where");

}



global $call_title;
$display_title= $mod_strings['LBL_LIST_FORM_TITLE'];

$ListView = new ListView();
$ListView->initNewXTemplate( 'modules/Calls/ListView.html',$mod_strings);
$ListView->setHeaderTitle($display_title );
$ListView->setQuery($where, "", "date_start", "CALL");
$ListView->processListView($seedCall, "main", "CALL");


?>
