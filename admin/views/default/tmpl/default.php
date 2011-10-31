<?php
/**
 * Joomla! 1.5 component Analytics Webgenium
 *
 * @version $Id: controller.php 2011-09-09 18:00:00 svn $
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
defined('_JEXEC') or die('Restricted access');

try{
// tratamento de erros
if (!isset($this->sumario)) {
	return false;
}

$user 		=& JFactory::getUser();//user
JHTML::_('behavior.tooltip');
$task 		= JRequest::getVar('task');

// configuracoes do relatorio
$sumario 				= $this->sumario[0];
$diario 					= $this->diario[0];
$mais_visitados 		= $this->mais_visitados;
$mais_buscados 		= $this->mais_buscados;
$busca_interna		= $this->busca_interna;
$mais_referencias 	= $this->mais_referencias;
$mais_cidades 		= $this->mais_cidades;
$transacao 				= (isset($this->transacao)?$this->transacao:'');
// config ecommerce
$ecommerce 			= @$this->ecommerce;
$mostrar_busca_interna		= @$this->mostrar_busca_interna;

// configuracao do grafico ( tema e modulos )
$export 			= isset($this->export)?$this->export:'';
$theme			= isset($this->theme)?$this->theme:'';
?>
  <div style="text-align: center;" align="center">

  <div style="padding-bottom: 5px;" align="center">
	<?php
	if ($this->logo_component != '') {
				echo "<div style='float:right; position: relative'>".$this->logo_component."</div>";
			}
	?>
	<div style="text-align:center; width: 70%" align="center">
        <?php
        if ($task!='cron') {
			$component 				= 'components'. DS .(JRequest::getVar('option')). DS .'views'. DS .'default'. DS .'tmpl'. DS .'grafico';		
			$grafico 				= $component. DS .'highcharts.js';
			$adapter_grafico 		= $component. DS .'adapters'. DS .'mootools-adapter.js';
			$exportacao_grafico		= $component. DS .'modules'. DS .'exporting.js';
			$config_grafico 		= $component. DS .'grafico.js';

			?>			
			<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js" type="text/javascript"></script>
			<script type="text/javascript">jQuery.noConflict();</script>
			<script type="text/javascript">
			var grafico_url = '<?=$this->grafico_array['host']?>';
			var grafico_cache = grafico_url+'/administrator/cache/<?=$this->grafico_array['cache']?>';
			var grafico_source = 'Origem: Google Analytics';
			</script>

			<script type="text/javascript" src="<?=$grafico?>"></script>
			<?php if ($export==1){ ?>
			<script type="text/javascript" src="<?=$exportacao_grafico?>"></script>
			<?php }?>
			<script type="text/javascript" src="<?=$config_grafico?>"></script>
			<?php
			if ($theme != 'default'){
				echo '<script type="text/javascript" src="'.($component. DS .'themes'. DS .$theme).'"></script>';
			}			
			
			?>			

			<div id="grafico" align="center" style="text-align:center"></div>
        <?php 
		} else {
            // imprime o grafico do google	para exibir no email
            echo AnalyticsHelper::google_charts();			
        } ?>
	</div>
	<br style="clear:both" />
	
	<div style="float:left; width: 50%">
		<h4 style="background-color: #fff; padding-left: 5px; padding-right: 5px;"><?=JText::_('Base Stats');?> </h4>
		<hr style="border: solid #eee 1px"/><br/>
		<div>
			<div id="base-stats">
			<div style="text-align: left;">
			  <div style="width: 50%; float: left;">				
				<table>
				  <tr><td align="right"><b><?php echo $sumario->getVisits(); ?>&nbsp;</b></td><td></td><td><?=JText::_('Visits');?></td></tr>
				  <tr><td align="right"><b><?php echo $sumario->getPageviews(); ?>&nbsp;</b></td><td></td><td><?=JText::_('Pageviews');?></td></tr>
				  <tr><td align="right"><b><?php echo number_format($sumario->getPageviewsPerVisit(),2); ?>&nbsp;</b></td><td></td><td><?=JText::_('Pages/Visit');?></td></tr>
				</table>
			  </div>
			  <div style="width: 50%; float: right;">
				<table>
				  <tr><td align="right"><b><?php echo number_format($sumario->getEntranceBounceRate(),2); ?>%&nbsp;</b></td><td></td><td><?=JText::_('Bounce Rate');?></td></tr>
				  <tr><td align="right"><b><?php echo AnalyticsHelper::sec2hms($sumario->getAvgTimeOnPage()); ?>&nbsp;</b></td><td></td><td><?=JText::_('Avg. Time on Site');?></td></tr>
				  <tr><td align="right"><b><?php echo number_format($sumario->getPercentNewVisits(),2); ?>%&nbsp;</b></td><td></td><td><?=JText::_('% New Visits');?></td></tr>
				</table>
			  </div>
			  <br style="clear: both"/>
			</div>
			</div>
		</div>

	</div>

	<div style="float:left; width: 50%">
		<h4 style="background-color: #fff; padding-left: 5px; padding-right: 5px;"><?=JText::_('DAILY_STATISTICS');?> </h4>
		<hr style="border: solid #eee 1px"/><br/>
		<div>
			<div id="base-stats">
			<div style="text-align: left;">
			  <div style="width: 50%; float: left;">				
				<table>
				  <tr><td align="right"><b><?php echo $diario->getVisits(); ?>&nbsp;</b></td><td></td><td><?=JText::_('Visits');?></td></tr>
				  <tr><td align="right"><b><?php echo $diario->getPageviews(); ?>&nbsp;</b></td><td></td><td><?=JText::_('Pageviews');?></td></tr>
				  <tr><td align="right"><b><?php echo number_format($diario->getPageviewsPerVisit(),2); ?>&nbsp;</b></td><td></td><td><?=JText::_('Pages/Visit');?></td></tr>
				</table>
			  </div>
			  <div style="width: 50%; float: right;">
				<table>
				  <tr><td align="right"><b><?php echo number_format($diario->getEntranceBounceRate(),2); ?>%&nbsp;</b></td><td></td><td><?=JText::_('Bounce Rate');?></td></tr>
				  <tr><td align="right"><b><?php echo AnalyticsHelper::sec2hms($diario->getAvgTimeOnPage()); ?>&nbsp;</b></td><td></td><td><?=JText::_('Avg. Time on Site');?></td></tr>
				  <tr><td align="right"><b><?php echo number_format($diario->getPercentNewVisits(),2); ?>%&nbsp;</b></td><td></td><td><?=JText::_('% New Visits');?></td></tr>
				</table>
			  </div>
			  <br style="clear: both"/>
			</div>
			</div>
		</div>

	</div>

  </div>

  <br style="clear: both"/>


  <div style="position: relative; padding-top: 5px;" class="ie_layout">
    <h4 style="position: absolute; top: 6px; left: 10px; background-color: #fff; padding-left: 5px; padding-right: 5px;"><?=JText::_('Extended Stats')?></h4>
    <hr style="border: solid #eee 1px"/><br/>
  </div>

  <div>
    <div id="extended-stats" style="padding-left: 10px; padding-right: 10px;">
      <div style="text-align: left; font-size: 90%;">
        <div style="width: 100%; ">

          <h4 class="heading"><?php echo JText::_('Top Pages'); ?></h4>

          <div style="padding-top: 5px;">
			<?php
			$host = 'http://'.(AnalyticsHelper::getHost());
			foreach($mais_visitados as $result)	{
				echo '<div style="float:left; width:46%; padding: 5px;">';
				echo '<div><a href="' .$host. DS .$result->getPagePath().'">'.$result->getPageTitle().'</a></div>';				
				echo '<div style="color: #666; width: 150px; padding-left: 10px;"><b>'.$result->getVisits().'</b> '.JText::_('PAGE VIEWS').' </div>';
				echo '</div>';
			}
			?>
          </div>
        </div>
		<br style="clear:both" />
		<hr style="border: solid #eee 1px"/><br/>
		
		  <?php if (count($busca_interna) > 0) { ?>
          <div style="float:left; width: 25%; border-right: 1px solid #ccc;">
          <h4 class="heading"><?php echo JText::_('Top Internal Searches') ; ?></h4>

			<div style="padding: 10px;">
            <table width="100%">
			<?php
			foreach($busca_interna as $result)	{
				echo '<tr>';
				echo '<td><b>'.$result->getPageviews().'</b></td>';
				echo '<td>'.$result->getSearchKeyword().'</td>';
				echo '</tr>';
			}
			?>			
            </table>
          </div>
		  
		  </div>
		  <?php } ?>
		  
		  <?php if (!empty($transacao)) { ?>
          <div style="float:left; width: 25%">
          <h4 class="heading"><?php echo JText::_('Transactions Ecommerce') ; ?></h4>

			<div style="padding: 10px; border-right: 1px solid #ccc; ">
            <table width="100%">
			<tr>
				<td><em>Data</em></td>
				<td align="center"><em><?=JText::_('TRANSACTIONS')?></em></td>
				<td align="right"><em><?=JText::_('TRANSACTIONS PER VISIT')?></em></td>
				<td align="right"><em><?=JText::_('TOTAL')?></em></td>
			</tr>			
			<?php						
			$total_pedidos = 0;
			$total_valores  = 0;
			$ticket_medio  = 0;
			$total_itens = 0;
			foreach($transacao as $result)	{
				$data = AnalyticsHelper::reformataData($result->getDate());
				echo '<tr>';
				echo '<td width="10%"><b>'.$data.'</b></td>';
				echo '<td align="center">'.$result->getTransactions().'</td>';
				echo '<td align="right">'.number_format($result->getTransactionsPerVisit(),2).' %</td>';
				echo '<td align="right">'.number_format($result->getTotalValue(),2,',','.').'</td>';
				echo '</tr>';
				$total_valores += $result->getTotalValue();
				$ticket_medio += $result->getRevenuePerTransaction();
				$total_itens +=  $result->getTransactions();
				$total_pedidos++;
			}
			echo '<tr><td colspan="3" align="right"><b>'.JText::_('Total Orders').':</b></td><td align="right"> '.$total_itens.'</td></tr>';
			echo '<tr><td colspan="3" align="right"><b>'.JText::_('Total Value').':</b></td><td align="right"> R$ '.number_format($total_valores,2,',','.').'</td></tr>';
			echo '<tr><td colspan="3" align="right"><b>'.JText::_('Transaction Revenue').':</b></td><td  align="right"> R$ '.number_format($ticket_medio/$total_pedidos,2,',','.').'</td></tr>';
			?>			
            </table>
          </div>
		  
		  </div>
		  <?php } ?>
		  
		  
		  <div style="float: left; width: 25%">
		  
		   <h4 class="heading"><?php echo JText::_('Top Searches') ; ?></h4>

          <div style="padding: 10px; border-right: 1px solid #ccc;">
            <table width="100%">
			<?php
			foreach($mais_buscados as $result)	{
				echo '<tr>';
				echo '<td><b>'.$result->getVisits().'</b></td>';
				echo '<td>'.$result->getKeyword().'</td>';
				echo '</tr>';
			}
			?>			
            </table>
          </div>
		  
		  </div>
		  <div style="float: left; width: 25%">

          <h4 class="heading"><?php echo JText::_('Top Referers'); ?></h4>

          <div style="padding: 10px; border-right: 1px solid #ccc;">
            <table width="100%">
			<?php
			foreach($mais_referencias as $result)	{
				echo '<tr>';
				echo '<td><b>'.$result->getVisits().'</b></td>';
				echo '<td>'.$result->getSource().'</td>';
				echo '</tr>';
			}
			?>			
            </table>
          </div>
		  </div>

		  <div style="float: left;width: 25%">		  
		  
		   <h4 class="heading"><?php echo JText::_('Top City Referers'); ?></h4>

          <div style="padding: 10px; ">
            <table width="100%">
			<?php
			foreach($mais_cidades as $result)	{
				echo '<tr>';
				echo '<td><b>'.$result->getVisits().'</b></td>';
				echo '<td>'.$result->getCity().'</td>';
				echo '</tr>';
			}
			?>			
            </table>
          </div>		  		  
			</div>

        <br style="clear: both"/>
      </div>
    </div>

  </div>

  </div>
  
  <div align="center"><?=$this->logo?></div>
 <?php


	} catch(Exception $e ) {
			//$this->errorAnalytics($e->getMessage());
			echo $e->getMessage();
	}
 
 ?>