<?php
$case = $_GET['case']?$_GET['case']:'';
switch($case)
{
	case 'getdeleted':
			//echo $_SERVER['SCRIPT_FILENAME'];
			$_SERVER['SCRIPT_FILENAME'] = str_replace('/civisync/','/civicrm/',$_SERVER['SCRIPT_FILENAME']);
			chdir('../../civicrm/extern');
		 	session_start( );

			require_once '../civicrm.config.php';
			require_once 'CRM/Core/Config.php';
			
			require_once 'CRM/Utils/REST.php';
			$rest = new CRM_Utils_REST();
			
			$config = CRM_Core_Config::singleton();
			
			if ( isset( $_GET['json'] ) &&
			     $_GET['json'] ) {
			    header( 'Content-Type: text/javascript' );
			} else {
			    header( 'Content-Type: text/xml' );
			}
			
			$result = $rest->handle( $config );
			$db = parse_url(CIVICRM_UF_DSN);
			$link = mysql_connect($db['host'], $db['user'], $db['pass']);
			if ($link)
			{
				mysql_select_db(substr($db['path'],1), $link);
				//echo'<pre>';print_r($result);echo'</pre>';
				foreach($result as $record)
				{
					
					$sql = 'SELECT is_deleted from civicrm_contact where id="'.$record['contact_id'].'"';
					$results = mysql_query($sql);
					$is_deleted  = mysql_result($results,0,0);
					//$is_deleted  = mysql_fetch_row($results);
					$result[$record['contact_id']]['isdeleted'] = $is_deleted;
				}
				
			}
			echo $rest->output( $config, $result );
		break;
	default:
			chdir('../../civicrm/extern');
			session_start( );
			require_once '../civicrm.config.php';
			require_once 'CRM/Core/Config.php';
			
			require_once 'CRM/Utils/REST.php';
			
			$db = parse_url(CIVICRM_UF_DSN);
			$link = mysql_connect($db['host'], $db['user'], $db['pass']);
			$xml = "<?xml version=\"1.0\"?>\n<ResultSet xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">\n";
			// check if this is a single element result (contact_get etc)
			// or multi element
			$xml .= "<Result>\n";
			if ($link)
			{
				mysql_select_db(substr($db['path'],1), $link);
				$sql = 'SELECT uid from users where name="'.addslashes($_GET['name']).'"';
				$result = mysql_query($sql);
				$uid  = mysql_fetch_row($result);
				header( 'Content-Type: text/xml' );
				
				if(count($uid)>0)
				{
					if($uid[0]>0)
					{
						$db1 = parse_url(CIVICRM_DSN);
						$link1 = mysql_connect($db1['host'], $db1['user'], $db1['pass']);
						if ($link1)
						{
							mysql_select_db(substr($db1['path'],1), $link1);
							$sql = 'SELECT contact_id,uf_name from civicrm_uf_match where uf_id="'.$uid[0].'"';
							$result = mysql_query($sql);
							$user_details  = mysql_fetch_row($result);
							if(count($uid)>0)
							{
								$xml .= "<email>".$user_details[1]."</email>\n";
						        $xml .= "<contact_id>".$user_details[0]."</contact_id>\n";
						       	$xml .= "<server_time>".date('m/d/Y h:i:s A',$_SERVER['REQUEST_TIME'])."</server_time>\n";
							}
							else
							{
								$xml .= '<is_error>No Result Found</is_error>\n';
							}
						}
						else
						{
							$xml .= '<is_error>Unable to connect Civicrm Database</is_error>\n';
						}
					}
					else
					{
						$xml .= '<is_error>No Result Found</is_error>\n';
					}
				}
				else
				{
					$xml .= '<is_error>No Result Found</is_error>\n';
				}
			}
			else
			{
				$xml .= '<is_error>Unable to connect Drupal Database</is_error>\n';
			}
			
			$xml .= "</Result>\n";
			$xml .= "</ResultSet>\n";
			echo  $xml;
}