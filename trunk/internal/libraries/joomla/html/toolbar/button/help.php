<?php
/**
* @version		$Id: help.php 6220 2007-01-09 00:59:19Z hackwar $
* @package		Joomla.Framework
* @subpackage	HTML
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Renders a help popup window button
 *
 * @author 		Louis Landry <louis.landry@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage		HTML
 * @since		1.5
 */
class JButton_Help extends JButton
{
	/**
	 * Button type
	 *
	 * @access	protected
	 * @var		string
	 */
	var $_name = 'Help';

	function fetchButton( $type='Help', $ref = '', $com = false )
	{
		$text	= JText::_('Help');
		$class	= $this->fetchIconClass('help');
		$doTask	= $this->_getCommand($ref, $com);

		$html	= "<a href=\"#\" onclick=\"$doTask\" class=\"toolbar\">\n";
		$html .= "<span class=\"$class\" title=\"$text\">\n";
		$html .= "</span>\n";
 		$html	.= "$text\n";
		$html	.= "</a>\n";

		return $html;
	}

	/**
	 * Get the button id
	 *
	 * Redefined from JButton class
	 *
	 * @access		public
	 * @param		string	$name	Button name
	 * @return		string	Button CSS Id
	 * @since		1.5
	 */
	function fetchId($name)
	{
		return $this->_parent->_name.'-'."help";
	}

	/**
	 * Get the JavaScript command for the button
	 *
	 * @access	private
	 * @param	object	$definition	Button definition
	 * @return	string	JavaScript command string
	 * @since	1.5
	 */
	function _getCommand($ref, $com)
	{
		global $mainframe;

		// Get Help URL
		jimport('joomla.i18n.help');
		$url = JHelp::createURL($ref, $com);

		$cmd = "popupWindow('$url', '".JText::_('Help')."', 640, 480, 1)";

		return $cmd;
	}
}
?>