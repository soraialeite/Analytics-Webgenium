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

jimport( 'joomla.application.component.controller' );
require_once( JPATH_COMPONENT.DS.'helpers'.DS.'helper.php' );

/**
 * Analytics Controller
 *
 * @package Joomla
 * @subpackage Analytics
 */
class AnalyticsController extends JController {
    /**
     * Constructor
     * @access private
     * @subpackage Analytics
     */
    function __construct() {
        //Get View
        if(JRequest::getCmd('view') == '') {
            JRequest::setVar('view', 'default');
            $this->padrao();
        }
        parent::__construct();
    }

    /**
     * Chama a função padrão para mostrar as estatísticas do site
     */
    function padrao() {
        $this->item_type = 'Default';
        $helper = new  AnalyticsHelper();
    }

    // método para enviar a cron do sistema
    function cron() {
        // buscar a informação das estatísticas
        require_once( JPATH_COMPONENT.DS.'views'.DS.'default'.DS.'view.html.php' );

        $view = new AnalyticsViewDefault();
	    $conteudo_email = $view->loadTemplate();		

        // carrega a configuração do Joomla
        $row = new JConfig();
        // carrega o template do email
        $view2 = new AnalyticsViewDefault();		
        $view2->setLayout('cron');
        // adiciona as variáveis para o template
        $view2->cron($row->fromname, $conteudo_email);
        $conteudo = $view2->loadTemplate();

        $url_pedido = JURI::base().'index.php?option=com_analytics';
        // envia o email para o administrador
        $enviar_email = AnalyticsHelper::sendMail( $row, $conteudo );
        if (!$enviar_email) {
            $msg = JText::_('Error sending email');
            $tipo = 'error';
        } else {
            $msg = JText::_('Email sent sucessfully!');
            $tipo = 'success';
        }
        $app = JFactory::getApplication();
        $app->redirect($url_pedido,$msg,$tipo);
        //parent::__construct();
    }
}
?>