<?php
/**
* @version		$Id: link.php 6220 2007-01-09 00:59:19Z hackwar $
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
 * Renders a link button
 *
 * @author 		Louis Landry <louis.landry@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage		HTML
 * @since		1.5
 */
class JButton_Link extends JButton
{
	/**
	 * Button type
	 *
	 * @access	protected
	 * @var		string
	 */
	var $_name = 'Link';

	function fetchButton( $type='Link', $name = '', $text = '', $url = null )
	{
		$text	= JText::_($text);
		$class	= $this->fetchIconClass('back');
		$doTask	= $this->_getCommand($url);

		$html	= "<a href=\"$doTask\">\n";
		$html .= "<span class=\"$class\" title=\"$text\">\n";
		$html .= "</span>\n";
		$html	.= "$text\n";
		$html	.= "</a>\n";

		return $html;
	}

	/**
	 * Get the button CSS Id
	 *
	 * @access	public
	 * @return	string	Button CSS Id
	 * @since	1.5
	 */
	function fetchId($name)
	{
		return $this->_parent->_name.'-'.$name;
	}

	/**
	 * Get the JavaScript command for the button
	 *
	 * @access	private
	 * @param	object	$definition	Button definition
	 * @return	string	JavaScript command string
	 * @since	1.5
	 */
	function _getCommand($url) {
		return $url;
	}
}
?>
