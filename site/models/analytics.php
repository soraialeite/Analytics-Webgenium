<?php
/*------------------------------------------------------------------------
# com_analytics - Webgenium Analytics
# ------------------------------------------------------------------------
# author    Luiz Felipe Weber - Webgenium System
# copyright Copyright (C) 2011 webgenium.com.br. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://loja.weber.eti.br / http://webgenium.com.br
# Technical Support:  Forum - https://github.com/webgenium/Analytics-Webgenium
-------------------------------------------------------------------------*/

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * Analytics Component Analytics Model
 *
 * @author      notwebdesign
 * @package		Joomla
 * @subpackage	Analytics
 * @since 1.5
 */
class AnalyticsModelAnalytics extends JModel {
    /**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
    }
}
?>