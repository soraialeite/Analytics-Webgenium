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

jimport('joomla.application.component.controller');

/**
 * Analytics Component Controller
 */
class AnalyticsController extends JController {
	function display() {

            $caminho_admin = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_analytics';

            require_once( $caminho_admin.DS.'helpers'.DS.'helper.php' );
            require_once( $caminho_admin.DS.'views'.DS.'default'.DS.'view.html.php' );
            $helper = new AnalyticsHelper();
            $view = new AnalyticsViewDefault();

            $conteudo_email = $view->loadTemplate();
            // carrega a configuração do Joomla
            $row = new JConfig();
            // carrega o template do email
            $view2 = new AnalyticsViewDefault();
            $view2->setLayout('cron');
            // adiciona as variáveis para o template
            $view2->cron($row->fromname, utf8_decode($conteudo_email));
            $conteudo = $view2->loadTemplate();

            $url_pedido = JURI::base().'index.php?option=com_analytics';
            // envia o email para o administrador
            $enviar_email = AnalyticsHelper::sendMail( $row, $conteudo );
            if (!$enviar_email) {
                $msg = 'Erro ao enviar email';
                $tipo = 'error';
            } else {
                $msg = 'Email enviado com sucesso!';
                $tipo = 'success';
            }
            /*
            $app = JFactory::getApplication();
            $app->redirect($url_pedido,$msg,$tipo);
            //parent::__construct();
            */
            // Make sure we have a default view
           /* if( !JRequest::getVar( 'view' )) {
		JRequest::setVar('view', 'analytics' );
                JRequest::setVar('tmpl', 'component' );
            }
	    parent::display();
            */
	}
      
}
?>