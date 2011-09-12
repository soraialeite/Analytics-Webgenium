<?php
error_reporting(E_ALL);
/**
 * Joomla! 1.5 component Analytics Webgenium
 *
 * @version $Id: analytics.php 2011-09-09 18:00:00 svn $
 * @author Luiz Felipe Weber
 * @author Kinshuk Kulshreshtha
 * @website webgenium.com.br
 * @package Joomla
 * @subpackage Analytics Webgenium
 * @license GNU/GPL
 *
 * Show Google Analytics in Joomla Backend 
 *
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

// Import Joomla! libraries
jimport('joomla.application.component.view');

class AnalyticsViewDefault extends JView {

	public $ga;
	public $profileId;
	public $webPropertyId;
	public $relatorios;
	public $connect;
	public $arquivo_cache;
	public $cache;
	public $resultado_grafico;	
	public $ecommerce_config;	
	public $params;	

    function __construct($config = array()) {
	
		// configuracao dos parametros do componente
		// recupera as informacoes de parametros de configuracao extra
		$this->params = AnalyticsHelper::getConfiguracaoAnalytics();
	
		$this->cache = & JFactory::getCache();
		// habilta o cache ( caso seja configurado )
		if ($this->params->get('cache_component')) {
			$this->cache->setCaching( 1 );
		}

		// configuracao do arquivo de cache
		$this->arquivo_cache = 'analytics'.date('dmY').'.tsv';

		// inicializa os dados do Analytics
		$this->connect = $this->getAnalyticsData();	
		
        // falsificando as configurações de visão do componente para ser chamado da área do site, uma task do admin
        $config = array(
            "name" => 'default',
            "models" => array(),
            "base_path" => JPATH_ADMINISTRATOR.DS.'components'.DS.'com_analytics',
            "defaultModel" => '',
            "layout" => "default",
            "layoutExt" => "php",
            "path" => array(
                "template" => array(
                    0 => JPATH_ADMINISTRATOR.DS.'templates/khepri/html/com_analytics/default/',
                    1 => JPATH_ADMINISTRATOR.DS.'components/com_analytics/views/default/tmpl/',
                ),
                "helper" => array(
                    0 => JPATH_ADMINISTRATOR.DS.'components/com_analytics/helpers/'
                )

            ),
            "helper_path" => JPATH_ADMINISTRATOR.DS.'components/com_analytics/helpers/',
            "template" => "",
            "output" => "",
            "escape" => "htmlspecialchars",
            "charset" => "UTF-8",
            "errors" => array(),
            "baseurl" => JURI::base(true).DS."administrator"
        );
	
		// monta os relatorios	de exibição das estatisticas
		$this->montaRelatorios();

        parent::__construct($config);
		
    }
	
    function display($tpl = null) {
        $doc = & JFactory::getDocument();
        $css = '.icon-48-chart-icon {background:url(../administrator/components/com_analytics/images/chart-icon.png) no-repeat;}';
        $doc->addStyleDeclaration($css);
		
		$js ="function submitform(pressbutton) {
			location.href='index.php?option=com_analytics&task='+pressbutton;
		}";
		$doc->addScriptDeclaration($js);

        JToolBarHelper::title(JText::_('Analytics'), 'chart-icon.png');

		// botão de enviar os emails ( cron ).
		JToolBarHelper::custom( 'cron', 'send', 'send', JText::_('SEND_STATS'), false, false );
        JToolBarHelper::preferences('com_analytics', 400);
		
		// grafico cacheado com as visitas do dia
		$this->montaGrafico();
		
		// configuracao do cache do grafico
		$grafico_array = array(
			'host'=> 'http://'.(AnalyticsHelper::getHost()),
			'cache' => $this->arquivo_cache
		);
		$this->assignRef('grafico_array', $grafico_array);
		
		$this->assignRef('theme',$this->params->get('theme'));
		$this->assignRef('export',$this->params->get('export'));
		$this->assignRef('ecommerce',$this->params->get('ecommerce_config'));
		$this->assignRef('mostrar_busca_interna',$this->params->get('mostrar_busca_interna'));		
        parent::display($tpl);
    }

    function cron($nome_cliente, $conteudo_email) {
		JRequest::setVar('tmpl','component');
        $this->assignRef('nome_cliente', $nome_cliente);
        $this->assignRef('conteudo_email', $conteudo_email);
    }

	function montaGrafico($resultado_somente=false) {
		$caminho_arquivo =JPATH_CACHE. DS .($this->arquivo_cache);
		if (file_exists($caminho_arquivo)) {
			return false;	
		}	
		
		$um_mes_atras = date('Y-m-d',strtotime('1 month ago'));
		$data_atual = date('Y-m-d');

		$mes_atras_relatorio  = date("D, F, Y",strtotime('1 month ago'));
		$data_relatorio  = date("D, F, Y");
	
		// maximo de 10 metricas
		$relatorio = $this->novoRelatorio (
								'grafico',
								array('date'),  // dimensions
								array('visits' ,'pageviews' ), // metrics
								'-date',	null, $um_mes_atras, $data_atual, 1, 31, false);
		
		// monta o arquivo
		$arquivo = "# ----------------------------------------\n".		
		(AnalyticsHelper::getHost())."\n".
		"Visitas Diarias\n".
		$mes_atras_relatorio." ".$data_relatorio."\n".
		"# ----------------------------------------\n".
		"\n".
		"# ----------------------------------------\n".
		"# Graph\n". 
		"# ----------------------------------------\n".
		"Day	Visitantes	Pageviews\n";

		$linha = '';
		foreach($relatorio as $graph) {
			$linha .=  (date("D, M d, Y",strtotime($graph->getDate()) ) ).";".
					   ($graph->getVisits()) .";".
					   ($graph->getPageviews()) ."\n";
		}
		//$this->assignRef('resultado_grafico',$relatorio);

		$arquivo .= $linha;
		$arquivo .= "# --------------------------------------------------------------------------------\n";

		try {
			// verifica se o arquivo de cache ja existe ou nao
			if (!file_exists($caminho_arquivo)) {
				jimport( 'joomla.filesystem.file' );
				JFile::write($caminho_arquivo,$arquivo);
			}
			// cache criado com sucesso
			AnalyticsHelper::noticeAnalytics("Cache created");
		} catch(Exception $e ) {
			AnalyticsHelper::errorAnalytics($e->getMessage());
		}
	}
	
	function montaRelatorios() {
		/*array('visitors','visits','newVisits','percentNewVisits','avgTimeOnPage','avgTimeOnSite','entrances','entranceRate','bounces','entranceBounceRate','visitBounceRate','pageviews','pageviewsPerVisit','uniquePageviews','avgTimeOnSite','exits','exitRate'), // metrics*/

		$um_mes_atras = date('Y-m-d',strtotime('1 month ago'));
		$data_atual = date('Y-m-d');
		
		// maximo de 10 metricas
		$this->novoRelatorio (
					'diario',
					array('day'),  // dimensions
					array('visits' ,'pageviews' ,'pageviewsPerVisit' ,'entranceBounceRate' ,'avgTimeOnPage' ,'percentNewVisits'), // metrics
					null,	null, $data_atual, $data_atual, 1,1 );

		// sumario ( um mes de estatisticas )
		$this->novoRelatorio (
					'sumario',
					array('month'),  // dimensions
					array('visits' ,'pageviews' ,'pageviewsPerVisit' ,'entranceBounceRate' ,'avgTimeOnPage' ,'percentNewVisits'), // metrics
					null,	null, $um_mes_atras, $data_atual, 1,1 );
					
		// mais visitados ( um mes de estatisticas )
		$this->novoRelatorio (
					'mais_visitados',
					array('pageTitle','pagePath'),  // dimensions
					array('visits' ,'pageviews'), // metrics
					'-visits',	null, $um_mes_atras, $data_atual, 1, 15 );	
		
		// mais buscados internamente ( um mes de estatisticas )
		$this->novoRelatorio (
					'busca_interna',
					array('searchKeyword'),  // dimensions
					array('searchVisits','visits','pageviews'), // metrics
					'-pageviews',	null, $um_mes_atras, $data_atual, 1, 15 );	
					
		// mais buscados 
		$this->novoRelatorio (
					'mais_buscados',
					array('keyword'),  // dimensions
					array('organicSearches','visits','pageviews'), // metrics
					'-visits',	null, $um_mes_atras, $data_atual, 1, 15 );

		// mais referencias de busca
		$this->novoRelatorio (
					'mais_referencias',
					array('source'),  // dimensions
					array('visits','pageviews'), // metrics
					'-visits',	null, $um_mes_atras, $data_atual, 1, 15 );						

		// mais referencias de busca
		$this->novoRelatorio (
					'mais_cidades',
					array('city'),  // dimensions
					array('visits'), // metrics
					'-visits',	null, $um_mes_atras, $data_atual, 1, 15 );						
					
		// comercio eletronico
		if ($this->params->get('ecommerce')) {
			$this->novoRelatorio (
						'transacao',
						array('date'),  // dimensions
						array('transactions','transactionsPerVisit','transactionRevenue','totalValue','revenuePerTransaction'), // metrics
						'-date',	null, $um_mes_atras, date('Y-m-d'), 1, 12 );			
		}
	}
	
	// novo relatorio com filtros
	function novoRelatorio($nome, $dimensions, $metrics, $sort_metric=null, $filter=null, $start_date=null, $end_date=null, $start_index=1, $max_results=30,$template=true) {
		// verifica se conectou
		$relatorio = null;
		if ($this->connect) {			
			try{			
				//$relatorio = $this->ga->requestReportData(ga_profile_id, $dimensions, $metrics, $sort_metric, $filter, $start_date, $end_date, $start_index, $max_results);
				// faz a consulta com cache
				$relatorio = $this->cache->call( array( $this->ga, 'requestReportData' ),  
					ga_profile_id, $dimensions, $metrics, $sort_metric, $filter, $start_date, $end_date, $start_index, $max_results
				);

				// verifica se adiciona ao template ou nao
				if ($template) {
					// ja adiciona ao template				
					$this->assignRef($nome,		$relatorio);
				} else {
					return $relatorio;
				}

			} catch(Exception $e ) {
				AnalyticsHelper::errorAnalytics($e->getMessage());
			}
			$relatorio = $this->cache->call( array( $this->ga, 'requestReportData' ),  
					ga_profile_id, $dimensions, $metrics, $sort_metric, $filter, $start_date, $end_date, $start_index, $max_results
			);

		}
	}
	
	function getAnalyticsData() {
		// incluindo a library do google analytics
		require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'gapi.class.php';

		// verifica se os parametros estão setados
		if (!$this->params->get('ga_email') or $this->params->get('ga_email') == '') {
			AnalyticsHelper::errorAnalytics('Email not found');
		}
		
		if (!$this->params->get('ga_pass') or $this->params->get('ga_pass') == '') {
			AnalyticsHelper::errorAnalytics('Password not found');
		}

		// testa a conexao
		try{
			$this->ga = new gapi(
				$this->params->get('ga_email'),
				$this->params->get('ga_pass')
			);
			
			if (!$this->params->get('ga_account_id')) {
		
				// procura o profile id
				$busca_site = $this->cache->call( array( $this, 'searchProfileId' ) );
				
				if ($busca_site){
					define('ga_profile_id',		str_replace('ga:','',$this->profileId));
					define('webPropertyId',	$this->webPropertyId);
					// seta usuario e senha
					define('ga_email',			$this->params->get('ga_email'));
					define('ga_password',		$this->params->get('ga_pass'));				
					
					// seta a configuracao no componente ( buscou direto no Analytics )
					AnalyticsHelper::setConfiguracaoAnalytics(array(
						'ga_email'		 	=> ga_email,
						'ga_pass' 			=> ga_password,
						'webPropertyId' 	=> webPropertyId,
						'ga_account_id' 	=> 'ga:'.ga_profile_id,
					));
					
					AnalyticsHelper::noticeAnalytics('Data updated');	
					
				} else {
					AnalyticsHelper::errorAnalytics('Profile ID not found');	
				}
			
			} else {
				if (!defined('ga_profile_id'))
					define('ga_profile_id',		str_replace('ga:','',$this->params->get('ga_account_id')));
				if (!defined('webPropertyId'))
					define('webPropertyId',	$this->params->get('webPropertyId'));
			}

		} catch(Exception $e ){
			AnalyticsHelper::errorAnalytics($e->getMessage());
			return false;
		}

		return true;
		
	}
	
	// faz a busca na lista de sites do Analytics
	function searchProfileId() {
		$lista_sites = $this->ga->requestAccountData(1,350);
		$host = AnalyticsHelper::getHost();
		
		foreach($lista_sites as $site) {
			// achou o site
			$properties = $site->getProperties();
			if ($properties['title'] == $host) {
				$this->profileId = $properties['profileId'];
				$this->webPropertyId = $properties['webPropertyId'];
				return true;
			}			
		}
	}
	
}