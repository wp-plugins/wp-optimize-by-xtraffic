<?php


require_once(WPOPTIMIZEBYXTRAFFIC_PATH.'inc/class/WPOptimizeByxTraffic_OptimizeTraffic.php');


if ( !class_exists('WPOptimizeByxTraffic_OptimizeDatabases') ) :


class WPOptimizeByxTraffic_OptimizeDatabases extends WPOptimizeByxTraffic_OptimizeTraffic 
{
	
	
	
	function __construct() 
	{
	
		parent::__construct();
		
		
		
	}
	
	
	
	public function optimize_databases_check_system_ready() 
	{
		
		$resultData = array();
		$resultData['notice']['error'] = array();
		$resultData['notice']['error_no'] = array();
		
		
		$rsTemp = $this->base_check_system_ready();
		$resultData = PepVN_Data::mergeArrays(array(
			$resultData
			,$rsTemp
		));
		
		$resultData['notice']['error'] = array_unique($resultData['notice']['error']);
		$resultData['notice']['error_no'] = array_unique($resultData['notice']['error_no']);
		
		
		return $resultData;
		
	}
	
	
	public function optimize_databases_actions_run_once($input_parameters) 
	{
		$resultData = array();
		
		
		$resultData = $this->optimize_databases_run_actions($input_parameters['optimize_databases_actions_run_once']);
		
		//echo '<h2> Dump on : ',__FILE__,' at line : ',__LINE__,' </h2><pre>',var_dump($input_parameters,$resultData),'</pre>';exit(); 
		$this->base_clear_data(',all,');
		
		return $resultData;
	}
	
	
	
	
	
	public function optimize_databases_do_scheduled_actions() 
	{
		$resultData = array();
		
		$options = $this->get_options(array(
			'cache_status' => 0
		));
		
		$options['optimize_databases_scheduled_actions_days'] = abs((int)$options['optimize_databases_scheduled_actions_days']);
		
		$doScheduledActionsStatus = false;
		
		if(isset($options['optimize_databases_scheduled_actions_enable']) && $options['optimize_databases_scheduled_actions_enable']) {
			if($options['optimize_databases_scheduled_actions_days'] > 0) {
				
				$secondsScheduled = $options['optimize_databases_scheduled_actions_days'] * 86400;
				
				$doScheduledActionsStatus = true;
				
				if(isset($options['optimize_databases_scheduled_actions_lasttime_run_data']['timestamp'])) {
					$options['optimize_databases_scheduled_actions_lasttime_run_data']['timestamp'] = abs((int)$options['optimize_databases_scheduled_actions_lasttime_run_data']['timestamp']);
					if($options['optimize_databases_scheduled_actions_lasttime_run_data']['timestamp']>0) {
						if($options['optimize_databases_scheduled_actions_lasttime_run_data']['timestamp'] <= ( time() - $secondsScheduled)) {	//is timeout
						} else {
							$doScheduledActionsStatus = false;
						}
					}
				}
			}
		}
		
		if($doScheduledActionsStatus) {
			$options['optimize_databases_scheduled_actions_lasttime_run_data']['timestamp'] = time();
			update_option($this->wpOptimizeByxTraffic_DB_option, $options);
			
			$options['optimize_databases_scheduled_actions_lasttime_run_data']['result'] = $this->optimize_databases_run_actions();
			update_option($this->wpOptimizeByxTraffic_DB_option, $options);
			
		}
		
		return $resultData;
		
	}
	
	public function optimize_databases_run_actions($options = false) 
	{
		global $wpdb;
		
		$resultData = array();
		$resultData['notice']['success'] = array();
		
		if(false === $options) {
			$options = $this->get_options(array(
				'cache_status' => 1
			));
		}
		
		$options = $this->base_fix_options($options);
		
		
		
		
		$queryString = '';
		
		$keep_last_data_days = 0;
		if (isset($options['optimize_databases_keep_last_data_enable']) && $options['optimize_databases_keep_last_data_enable']) {
			if (isset($options['optimize_databases_keep_last_data_days']) && $options['optimize_databases_keep_last_data_days']) {
				$options['optimize_databases_keep_last_data_days'] = (int)$options['optimize_databases_keep_last_data_days'];
				if($options['optimize_databases_keep_last_data_days']>0) {
					$keep_last_data_days = $options['optimize_databases_keep_last_data_days'];
				}
			}
		}
		
		
		if(isset($options['optimize_databases_actions_enable']) && $options['optimize_databases_actions_enable']) {
			$options['optimize_databases_actions_enable'] = (array)$options['optimize_databases_actions_enable'];
			
			
			
			if(in_array('clean_posts_revisions',$options['optimize_databases_actions_enable'])) {
				$actionQuery = 'DELETE FROM `'.$wpdb->posts.'` WHERE ( post_type = "revision" ) AND ( post_status = "inherit" ) ';
                if ($keep_last_data_days > 0) {
                    $actionQuery .= ' AND ( post_modified < ( NOW() - INTERVAL ' .  $keep_last_data_days . ' DAY ) )';
                }
                $actionQuery .= ';';
    			$numberEffect = $wpdb->query( $actionQuery );
				$numberEffect = (int)$numberEffect;
				$resultData['notice']['success'][] = $numberEffect.' '.__('post revisions deleted',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'!';
				
			}
			
			if(in_array('clean_auto_draft',$options['optimize_databases_actions_enable'])) {
				$actionQuery = 'DELETE FROM `'.$wpdb->posts.'` WHERE ( post_type = "revision" ) AND ( post_status = "auto-draft" ) ';
                if ($keep_last_data_days > 0) {
                    $actionQuery .= ' AND ( post_modified < ( NOW() - INTERVAL ' .  $keep_last_data_days . ' DAY ) )';
                }
                $actionQuery .= ';';
				
    			$numberEffect = $wpdb->query( $actionQuery );
				$numberEffect = (int)$numberEffect;
				$resultData['notice']['success'][] = $numberEffect.' '.__('auto drafts deleted',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'!';
				
			}
			
			if(in_array('clean_posts_in_trash',$options['optimize_databases_actions_enable'])) {
				$actionQuery = 'DELETE FROM `'.$wpdb->posts.'` WHERE ( post_status = "trash" ) ';
                if ($keep_last_data_days > 0) {
                    $actionQuery .= ' AND ( post_modified < ( NOW() - INTERVAL ' .  $keep_last_data_days . ' DAY ) )';
                }
                $actionQuery .= ';';
    			$numberEffect = $wpdb->query( $actionQuery );
				$numberEffect = (int)$numberEffect;
				$resultData['notice']['success'][] = $numberEffect.' '.__('items removed from Trash',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'!';
				
			}
			
			if(in_array('clean_comments_spam_trash',$options['optimize_databases_actions_enable'])) {
				$actionQuery = 'DELETE FROM `'.$wpdb->comments.'` WHERE ( comment_approved = "spam" ) ';
                if ($keep_last_data_days > 0) {
                    $actionQuery .= ' AND ( comment_date < ( NOW() - INTERVAL ' .  $keep_last_data_days . ' DAY ) )';
                }
                $actionQuery .= ';';
    			$numberEffect = $wpdb->query( $actionQuery );
				$numberEffect = (int)$numberEffect;
				$resultData['notice']['success'][] = $numberEffect.' '.__('spam comments deleted',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'!';
				
				
				$actionQuery = 'DELETE FROM `'.$wpdb->comments.'` WHERE ( (comment_approved = "trash") OR (comment_approved = "post-trashed") OR (comment_approved = "post-trash") ) ';
                if ($keep_last_data_days > 0) {
                    $actionQuery .= ' AND ( comment_date < ( NOW() - INTERVAL ' .  $keep_last_data_days . ' DAY ) )';
                }
                $actionQuery .= ';';
    			$numberEffect = $wpdb->query( $actionQuery );
				$numberEffect = (int)$numberEffect;
				$resultData['notice']['success'][] = $numberEffect.' '.__('comments removed from Trash',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'!';
				
				
				$actionQuery = 'DELETE FROM `'.$wpdb->commentmeta.'` WHERE comment_id NOT IN (SELECT comment_id FROM `'.$wpdb->comments.'`) ';
                $actionQuery .= ';';
    			$numberEffect = $wpdb->query( $actionQuery );
				$numberEffect = (int)$numberEffect;
				$resultData['notice']['success'][] = $numberEffect.' '.__('unused comment metadata items removed',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'!';
				
				$actionQuery = 'DELETE FROM `'.$wpdb->commentmeta.'` WHERE meta_key LIKE "%akismet%"';
				$actionQuery .= ';';
				$numberEffect = $wpdb->query( $actionQuery );
				$numberEffect = (int)$numberEffect;
				$resultData['notice']['success'][] = $numberEffect.' '.__('unused akismet comment metadata items removed',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'!';
				
				
			}
			
			if(in_array('repair_optimize_databases',$options['optimize_databases_actions_enable'])) {
				$rsGetDbInfo = $this->optimize_databases_get_db_info();
				
				$actionQuery = '';
				
				$tablesRepair = array();
				$tablesOptimize = array();
				
				$tablesAnalyzedOptimized = array();
				
				foreach($rsGetDbInfo['mysql_tables'] as $valueOne) {
					//$wpdb->query('OPTIMIZE TABLE `'.$t[0].'`';); 
					if (in_array($valueOne->Engine, array('MyISAM','ARCHIVE'))) {
						$tablesRepair[] = $valueOne->Name;
						$tablesAnalyzedOptimized[] = $valueOne->Name;
					}
					
					if (in_array($valueOne->Engine, array('MyISAM','ARCHIVE','InnoDB'))) {
						$tablesOptimize[] = $valueOne->Name;
						$tablesAnalyzedOptimized[] = $valueOne->Name;
						
					}
				}
				
				$tablesRepair = array_unique($tablesRepair);
				if(count($tablesRepair)>0) {
					$actionQuery = 'REPAIR TABLE '.implode(',',$tablesRepair).';';
					$wpdb->query($actionQuery);
					//$resultData['notice']['success'][] = 'Repaired tables : '.implode(', ',$tablesRepair);
					
				}
				
				$tablesOptimize = array_unique($tablesOptimize);
				if(count($tablesOptimize)>0) {
					$actionQuery = 'OPTIMIZE TABLE '.implode(',',$tablesOptimize).';';
					$wpdb->query($actionQuery);
					//$resultData['notice']['success'][] = 'Optimized tables : '.implode(', ',$tablesOptimize); 
				}
				
				$tablesAnalyzedOptimized = array_unique($tablesAnalyzedOptimized);
				if(count($tablesAnalyzedOptimized)>0) {
					$resultData['notice']['success'][] = ''.__('Analyzed & Optimized tables',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' : '.implode(', ',$tablesAnalyzedOptimized); 
				}
				
			}
			
		}
		
		if(count($resultData['notice']['success']) < 1) {
			$resultData['notice']['error'][] = ''.__('No actions are executed',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'!';
		}
		
		return $resultData; 
	}
	
	
	
	
	
	
	public function optimize_databases_get_db_max_allowed_packet()
	{
		global $wpdb;
		
		
		
		$keyCache1 = PepVN_Data::createKey(array(
			__METHOD__
			,'optimize_databases_get_db_max_allowed_packet'
		));
		
		if(isset($this->baseCacheData[$keyCache1])) {
			$resultData = $this->baseCacheData[$keyCache1];
		} else {
			
			$resultData = 0;
			$rsOne = $wpdb->get_results('SHOW VARIABLES LIKE "max_allowed_packet";', ARRAY_N);
			if(isset($rsOne[0][1]) && $rsOne[0][1]) {
				$rsOne[0][1] = (int)$rsOne[0][1];
				if($rsOne[0][1] > 0) {
					$resultData = $rsOne[0][1];
				}
			}
			
			$resultData = (int)$resultData;
			$this->baseCacheData[$keyCache1] = $resultData;
		}
		
		return $resultData;
		
	}
	
	
	
	
	
	
	
	public function optimize_databases_get_db_info()
	{
		global $wpdb;
		
		
		
		$keyCache1 = PepVN_Data::createKey(array(
			__METHOD__
			,'optimize_databases_get_db_info'
		));
		
		$resultData = PepVN_Data::$cacheObject->get_cache($keyCache1);
		if(!$resultData) {
		
			$resultData = array();
			
			$resultData['mysql_version'] = $wpdb->get_var('SELECT VERSION() AS version');
		
			
			$resultData['posts']['revisions']['totals'] = $wpdb->get_var('SELECT COUNT(*) AS countNumber FROM '.$wpdb->posts.' WHERE ((post_type="revision") AND (post_status="inherit"))');
			$resultData['posts']['auto_draft']['totals'] = $wpdb->get_var('SELECT COUNT(*) AS countNumber FROM '.$wpdb->posts.' WHERE ((post_type="revision") AND (post_status="auto-draft"))');
			$resultData['posts']['trash']['totals'] = $wpdb->get_var('SELECT COUNT(*) AS countNumber FROM '.$wpdb->posts.' WHERE (post_status="trash")');
			
			$resultData['comments']['spam']['totals'] = $wpdb->get_var('SELECT COUNT(*) AS countNumber FROM '.$wpdb->comments.' WHERE (comment_approved="spam")');
			$resultData['comments']['trash']['totals'] = $wpdb->get_var('SELECT COUNT(*) AS countNumber FROM '.$wpdb->comments.' WHERE (comment_approved="trash")');
			
			
			
			$resultData['mysql_tables'] = array();
			
			$tablesStatus = $wpdb->get_results('SHOW TABLE STATUS', OBJECT);
			foreach($tablesStatus as  $valueOne) {
				
				if ($valueOne->Engine == 'InnoDB') {
					$valueTemp1 = $wpdb->get_var('SELECT COUNT(*) AS countNumber FROM `'.$valueOne->Name.'` ');
					if($valueTemp1) {
						$valueTemp1 = (int)$valueTemp1;
						if($valueTemp1>0) {
							$valueOne->Rows = $valueTemp1;
						}
					}
				}
				
				$resultData['mysql_tables'][] = $valueOne;
			}
			
			PepVN_Data::$cacheObject->set_cache($keyCache1, $resultData);
		}
		
		return $resultData;
	}
	

	public function optimize_databases_handle_options()
	{
		
		$rsOne = $this->handle_options();
		$options = $rsOne['options']; $rsOne = false;
		
		$action_url = $_SERVER['REQUEST_URI'];	
		$nonce = wp_create_nonce( WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG);
		
		$rsTemp = $this->optimize_databases_check_system_ready();
		if(!PepVN_Data::isEmptyArray($rsTemp['notice']['error'])) {
			echo implode(' ',$rsTemp['notice']['error']);
		}
		
		$rsGetDbInfo = $this->optimize_databases_get_db_info();
		
		
		$optimize_databases_keep_last_data_enable = $options['optimize_databases_keep_last_data_enable'] == 'on' ? 'checked':'';
		$optimize_databases_keep_last_data_days = abs((int)$options['optimize_databases_keep_last_data_days']);
		$optimize_databases_actions_enable = (array)$options['optimize_databases_actions_enable'];
		
		$optimize_databases_scheduled_actions_enable = $options['optimize_databases_scheduled_actions_enable'] == 'on' ? 'checked':'';
		$optimize_databases_scheduled_actions_days = abs((int)$options['optimize_databases_scheduled_actions_days']);
		
		
		$htmlDbTableInfo = '
<table class="widefat" style="min-width:900px;">
	<thead>
		<tr class="thead">
			<th style="text-align: center;font-weight: 900;">No</th>
			<th style="text-align: center;font-weight: 900;">Tables</th>
			<th style="text-align: center;font-weight: 900;">Records</th>
			<th style="text-align: center;font-weight: 900;">Data Size</th>
			<th style="text-align: center;font-weight: 900;">Index Size</th>
			<th style="text-align: center;font-weight: 900;">Engine</th>
			<th style="text-align: center;font-weight: 900;">Overhead</th>
		</tr>
	</thead>
	
	<tbody id="the-list">';
	
		$iNumber = 0;
		$dbTotalGain = 0;
		$dbRowUsage = 0;
		$dbDataUsage = 0;
		$dbIndexUsage = 0;
		$dbOverheadUsage = 0;
		
		foreach($rsGetDbInfo['mysql_tables'] as $valueOne) {
			
			if(($iNumber % 2) == 0) {
				$style = '';
			} else {
				$style = ' class="alternate"';
			}
			$iNumber++;
			
			$htmlDbTableInfo .= '
		<tr '.$style.' >
			<td style="text-align: center;">'.number_format((float)$iNumber,0,null,',').'</td>
			<td>'.$valueOne->Name.'</td>
			<td>'.number_format((float)$valueOne->Rows,0,null,',').'</td>
			<td>'.PepVN_Data::formatFileSize($valueOne->Data_length/1024).'</td>
			<td>'.PepVN_Data::formatFileSize($valueOne->Index_length/1024).'</td>
			<td>'.$valueOne->Engine.'</td>';
			
			
			if ($valueOne->Engine != 'InnoDB') {
				$dbOverheadUsage += (float)$valueOne->Data_free;
				$htmlDbTableInfo .= '
			<td>'.PepVN_Data::formatFileSize(((float)$valueOne->Data_free)/1024).'</td>';
			} else {
				$htmlDbTableInfo .= '
			<td>-</td>';
			}
			
			
			
			
			$dbRowUsage += $valueOne->Rows;
			$dbDataUsage += $valueOne->Data_length;
			$dbIndexUsage +=  $valueOne->Index_length;
			
			$htmlDbTableInfo .= '
		</tr>';
		
			
		}
		
		$htmlDbTableInfo .= '
		<tr class="thead">
			<th style="text-align: center;font-weight: 900;">Total :</th>
			<th style="text-align: left;font-weight: 900;">'.number_format((float)$iNumber,0,null,',').' Tables</th>
			<th style="text-align: left;font-weight: 900;">'.number_format((float)$dbRowUsage,0,null,',').' Records</th>
			<th style="text-align: left;font-weight: 900;">'.PepVN_Data::formatFileSize($dbDataUsage/1024).'</th>
			<th style="text-align: left;font-weight: 900;">'.PepVN_Data::formatFileSize($dbIndexUsage/1024).'</th>
			<th style="text-align: center;font-weight: 900;">-</th>
			<th style="text-align: left;font-weight: 900;">'.PepVN_Data::formatFileSize($dbOverheadUsage/1024).'</th>
		</tr>
	</tbody>
</table>';
		//ob_clean();echo '<pre>',var_dump($rsGetDbInfo),'</pre>';exit();
		
		
		$htmlDbScheduledInfo = '';
		if(
			isset($options['optimize_databases_scheduled_actions_lasttime_run_data']['timestamp'])
			&& $options['optimize_databases_scheduled_actions_lasttime_run_data']['timestamp']
		) {
			if(
				isset($options['optimize_databases_scheduled_actions_lasttime_run_data']['result'])
				&& $options['optimize_databases_scheduled_actions_lasttime_run_data']['result']
			) {
				$htmlDbScheduledInfo .= '<ul style="margin-top: 3%;margin-left: 0;word-break: break-word;">';
				$htmlDbScheduledInfo .= '	<li><b>'.__('Last time run scheduled',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' "<u>'.date('d-m-Y H:m:s',$options['optimize_databases_scheduled_actions_lasttime_run_data']['timestamp']).'</u>", '.__('with result',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' : </b></li>';
				
				if(
					isset($options['optimize_databases_scheduled_actions_lasttime_run_data']['result']['notice']['success'])
					&& $options['optimize_databases_scheduled_actions_lasttime_run_data']['result']['notice']['success']
				) {
					foreach($options['optimize_databases_scheduled_actions_lasttime_run_data']['result']['notice']['success'] as $valueOne) {
						$htmlDbScheduledInfo .= '<li class="wpoptxtr_success">'.$valueOne.'</li>';
						
					}
				}
				
				if(
					isset($options['optimize_databases_scheduled_actions_lasttime_run_data']['result']['notice']['error'])
					&& $options['optimize_databases_scheduled_actions_lasttime_run_data']['result']['notice']['error']
				) {
					foreach($options['optimize_databases_scheduled_actions_lasttime_run_data']['result']['notice']['error'] as $valueOne) {
						$htmlDbScheduledInfo .= '<li class="wpoptxtr_error">'.$valueOne.'</li>';
						
					}
				}
				
				
				$htmlDbScheduledInfo .= '</ul>';
				
				
				
			}
		}
		
		
		
		echo '
<script language="javascript" type="text/javascript">
	var wpOptimizeByxTraffic_Request_Nonce = "',$nonce,'";
</script>

<div class="wrap wpoptimizebyxtraffic_admin" style="">
	<h2>WP Optimize By xTraffic (Optimize Databases)</h2>
				
	<div id="poststuff" style="margin-top:10px;">
		
		<div id="mainblock" style="">

		
			<form name="WPOptimizeByxTraffic" class="wpoptimizebyxtraffic_form_optimize_databases" action="',$action_url,'" method="post">
					
					<input type="hidden" id="_wpnonce" name="_wpnonce" value="',$nonce,'" />
					
					<input type="hidden" name="submitted" value="1" /> 
					<input type="hidden" name="optimize_databases_submitted" value="1" /> 
					
						
						
					<div class="xtraffic_tabs_nav">
						<a href="#xtraffic_tabs_content1" class="active">'.__('Optimize Database',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</a>
						<a href="#xtraffic_tabs_content2" class="">'.__('Database Information',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</a>
					</div>
					
					
					
					
					
					
					
					
					
					<div id="xtraffic_tabs_content1" class="xtraffic_tabs_contents">

						<h3>'.__('Optimize Database',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</h3>
						
						
						<div class="wpoptxtr_col wpoptxtr_span_1_of_2">
							<div class="postbox">
								<div class="inside">
									<h4>'.__('Actions',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'Actions</h4>
									<ul class="list-actions-notice">
									</ul>
									<ul class="list-actions">
										<li>
											<input type="checkbox" name="optimize_databases_actions_enable[]" class="" value="clean_posts_revisions" '.((in_array('clean_posts_revisions',$optimize_databases_actions_enable)) ? ' checked="checked" ' : '').' />&nbsp;
											<span style="display: inline-block;vertical-align: top;max-width:90%;">
												<b>'.__('Clean posts revisions',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</b>
												<br />(<i>'.__('You have',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' <u>"'.$rsGetDbInfo['posts']['revisions']['totals'].' '.__('revisions',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'"</u> '.__('in your databases',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</i>)
											</span>
										</li>
										
										<li>
											<input type="checkbox" name="optimize_databases_actions_enable[]" class="" value="clean_auto_draft" '.((in_array('clean_auto_draft',$optimize_databases_actions_enable)) ? ' checked="checked" ' : '').' />&nbsp;
											<span style="display: inline-block;vertical-align: top;max-width:90%;">
												<b>'.__('Clean auto-draft',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</b>
												<br />(<i>'.__('You have',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' <u>"'.$rsGetDbInfo['posts']['auto_draft']['totals'].' auto-draft"</u>'.__('in your databases',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</i>)
											</span>
										</li>
										
										<li>
											<input type="checkbox" name="optimize_databases_actions_enable[]" class="" value="clean_posts_in_trash" '.((in_array('clean_posts_in_trash',$optimize_databases_actions_enable)) ? ' checked="checked" ' : '').' />&nbsp;
											<span style="display: inline-block;vertical-align: top; max-width:90%;">
												<b>'.__('Clean Posts in the Trash',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</b>
												<br />(<i>'.__('You have',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' <u>"'.$rsGetDbInfo['posts']['trash']['totals'].' '.__('Posts in the Trash',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'"</u> '.__('in your databases',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</i>)
											</span>
										</li>
										
										<li>
											<input type="checkbox" name="optimize_databases_actions_enable[]" class="" value="clean_comments_spam_trash" '.((in_array('clean_comments_spam_trash',$optimize_databases_actions_enable)) ? ' checked="checked" ' : '').' />&nbsp;
											<span style="display: inline-block;vertical-align: top; max-width:90%;">
												<b>'.__('Clean spam comments and comments in trash',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</b>
												<br />(<i>'.__('You have',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).' <u>"'.$rsGetDbInfo['comments']['spam']['totals'].' '.__('spam comments',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'"</u> & <u>"'.$rsGetDbInfo['comments']['trash']['totals'].' '.__('comments in trash',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'"</u> '.__('in your databases',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</i>)
											</span>
										</li>
										
										
										<li>
											<input type="checkbox" name="optimize_databases_actions_enable[]" class="" value="repair_optimize_databases" '.((in_array('repair_optimize_databases',$optimize_databases_actions_enable)) ? ' checked="checked" ' : '').' />&nbsp;
											<span style="display: inline-block;vertical-align: top; max-width:90%;">
												<b>'.__('Repair & Optimize your Database',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</b>
											</span>
										</li>
										
										<li>
											<b><input type="checkbox" name="optimize_databases_keep_last_data_enable" class="" '.$optimize_databases_keep_last_data_enable.' />&nbsp;Keep last&nbsp;<input type="text" name="optimize_databases_keep_last_data_days" class="" value="'.$optimize_databases_keep_last_data_days.'" style="width: 60px;" />&nbsp;days</b>
											<br />'.__('This option will retain the last selected days data and remove any garbage data before that period. This option will also affect "Scheduled Actions" process',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'
										</li>
										
										<li style="margin-top:2%;">
											<input type="button" name="optimize_databases_actions_run_once" value="'.__('Run Once',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'" class="button-primary">
										</li>
										
									</ul>
									
									<div class="wpoptxtr_ajax_loading_container">'.WPOPTIMIZEBYXTRAFFIC_AJAX_LOADING_TAG.'</div>
									
								</div>
							</div>
						</div>
						
						<div class="wpoptxtr_col wpoptxtr_span_1_of_2">
							<div class="postbox">
								<div class="inside">
									<h4>'.__('Scheduled Actions',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</h4>
									
									<ul class="list-actions">
										<li>
											<input type="checkbox" name="optimize_databases_scheduled_actions_enable" class="wpoptimizebyxtraffic_show_hide_trigger" data-target="#optimize_databases_scheduled_actions_days_container" '.$optimize_databases_scheduled_actions_enable.' />&nbsp;
											<span style="display: inline-block;vertical-align: top;max-width:90%;">
												<b>'.__('Enable scheduled actions',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</b>
											</span>
										</li>
										
										<ul class="list-actions wpoptimizebyxtraffic_show_hide_container" style="margin-left:28px;" id="optimize_databases_scheduled_actions_days_container">
											<li>
												<b>'.__('Run every',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'&nbsp;<input type="text" name="optimize_databases_scheduled_actions_days" class="" value="'.$optimize_databases_scheduled_actions_days.'" style="width:60px;" />&nbsp;'.__('days',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</b> 
											</li>
											
											
											'.$htmlDbScheduledInfo.'
										</ul>
										
									</ul>
									
									
								</div>
							</div>
						</div>
						
						
						
					</div><!-- //xtraffic_tabs_contents -->  
					
					
					
					
					<div id="xtraffic_tabs_content2" class="xtraffic_tabs_contents">

						<h3>'.__('Database Information',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG).'</h3>
						
						
						<div class="wpoptxtr_height_eq_window" style="display:block;width:100%;height:auto;overflow: auto;">',$htmlDbTableInfo,'</div>
						
					</div><!-- //xtraffic_tabs_contents -->  
					
					
					<div class="">
						<div class="submit"><input type="submit" name="submit" value="',__('Update Options',WPOPTIMIZEBYXTRAFFIC_PLUGIN_SLUG),'" class="button-primary" /></div>
					</div>
					
					
			</form>
			
			
		</div>
		
		',$this->base_get_sponsorsblock('vertical_01'),'

	</div>
	
</div>

'; 
		
		
	}
	
	


}//class WPOptimizeByxTraffic

endif; //if ( !class_exists('WPOptimizeByxTraffic') )







