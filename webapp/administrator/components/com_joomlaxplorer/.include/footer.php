<?php
/** ensure this file is being included by a parent file */
defined( '_VALID_MOS' ) or die( 'Direct Access to this location is not allowed.' );
/*------------------------------------------------------------------------------
     The contents of this file are subject to the Mozilla Public License
     Version 1.1 (the "License"); you may not use this file except in
     compliance with the License. You may obtain a copy of the License at
     http://www.mozilla.org/MPL/

     Software distributed under the License is distributed on an "AS IS"
     basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
     License for the specific language governing rights and limitations
     under the License.

     The Original Code is footer.php, released on 2003-01-25.

     The Initial Developer of the Original Code is The QuiX project.

     Alternatively, the contents of this file may be used under the terms
     of the GNU General Public License Version 2 or later (the "GPL"), in
     which case the provisions of the GPL are applicable instead of
     those above. If you wish to allow use of your version of this file only
     under the terms of the GPL and not to allow others to use
     your version of this file under the MPL, indicate your decision by
     deleting  the provisions above and replace  them with the notice and
     other provisions required by the GPL.  If you do not delete
     the provisions above, a recipient may use your version of this file
     under either the MPL or the GPL."
------------------------------------------------------------------------------*/
/*------------------------------------------------------------------------------
Author: The QuiX project
	quix@free.fr
	http://www.quix.tk
	http://quixplorer.sourceforge.net

Comment:
	QuiXplorer Version 2.3
	Footer File
	
	Have Fun...
------------------------------------------------------------------------------*/
//------------------------------------------------------------------------------
function show_footer() {			// footer for html-page
	echo"\n<br style=\"clear:both;\"/>
	<small>
	<a class=\"title\" href=\"".$GLOBALS['jx_home']."\" target=\"_blank\">joomlaXplorer</a>
 (<a href=\"http://virtuemart.net/index2.php?option=com_versions&amp;catid=2&amp;myVersion=". $GLOBALS['jx_version'] ."\" onclick=\"javascript:void window.open('http://virtuemart.net/index2.php?option=com_versions&catid=2&myVersion=". $GLOBALS['jx_version'] ."', 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=580,directories=no,location=no'); return false;\" title=\"".$GLOBALS["messages"]["check_version"]."\">".$GLOBALS["messages"]["check_version"]."</a>)
	</small>
	</div>
	<hr/>";
}
//------------------------------------------------------------------------------
?>
