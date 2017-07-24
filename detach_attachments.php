<?php

# 
# This file is part of Roundcube "detach_attachments" plugin.
# 

if (class_exists('filesystem_attachments', false) && !defined('TESTS_DIR')) {
    die("Configuration issue. There can be only one enabled plugin for attachments handling");
}

require_once INSTALL_PATH . 'plugins/filesystem_attachments/filesystem_attachments.php';

class detach_attachments extends filesystem_attachments {

	var $task='login|mail|settings';

    public $debug = false;
    private $rcmail;
	private $dir;
	private $lifetime;
    private $detach = 5;

	static private $plugin = 'detach_attachments';
	static private $author = 'wisekaa03@gmail.com';
	static private $authors_comments = '';
	static private $download = 'wisekaa.ru';
	static private $version = '1.0.1';
	static private $date = '2017-03-22';
	static private $licence = 'All Rights reserved';
	static private $requirements = array('Roundcube'=>'1.0');
	static private $prefs = null;
	static private $config_dist = 'config.inc.php.dist';
	static private $tables = array('attachments');
	static private $db_version = array('initial');

    function init() {

		$this->rcmail = rcmail::get_instance();

		$this->load_config();

		$this->debug = $this->rcmail->config->get('detach_attachments_debug', true);
		$this->dir = slashify($this->rcmail->config->get('detach_attachments_dir', 'plugins/detach_attachments/dl'));
		$this->lifetime = $this->rcmail->config->get('detach_attachments_expires', 30);
		$this->detach = $this->rcmail->config->get('detach_attachments_upload', 5);

		$this->add_hook('startup', array($this, 'startup'));
		$this->add_hook('attachment_upload', array($this, 'attachment_upload'));
		$this->add_hook('attachment_delete', array($this, 'attachment_delete'));
		$this->add_hook('attachment_save', array($this, 'attachment_save'));
		$this->add_hook('message_compose', array($this, 'message_compose'));
        /*
		if ($this->rcmail->task == 'mail' && $this->rcmail->action == 'get') {
			$this->add_hook('render_page',array($this, 'render_page'));
		}
        */

		//$this->register_action('plugin.dta_import',array($this,'import'));
		//$this->register_action('plugin.dta_counts',array($this,'counts'));
		//$this->register_action('plugin.dla_delete',array($this,'delete'));

        /*
		$dont_override = $this->rcmail->config->get('dont_override', array());
		if (! in_array('detach_attachments_upload', $dont_override)) {
			$this->add_hook('preferences_list', array($this, 'preferences_list'));
			$this->add_hook('preferences_save', array($this, 'preferences_save'));
        }
        */

		$this->gc($this->lifetime);

	}


	function gc($lifetime) {

        $date = (new DateTime(date("Y-m-d H:i:s")))->sub(new DateInterval('P'. (int)$lifetime .'D'));
		$result = $this->rcmail->db->query("SELECT * FROM `". rcmail::get_instance()->db->table_name('attachments') ."` WHERE `created` < ?", $date->format('Y-m-d H:i:s'));
		while ($result_query = $this->rcmail->db->fetch_assoc($result) ) {
            if ($this->debug) {
                rcube::write_log('detach_attachment', 'gc. lifetime='. print_r($lifetime,true) .', created < '. $date->format('Y-m-d H:i:s') .', result_query='. print_r($result_query,true));
            }
            @unlink($this->dir . '/' . $result_query['cache_key']);
			$this->rcmail->db->query("DELETE FROM `".rcmail::get_instance()->db->table_name('attachments')."` WHERE `cache_id`=?", $result_query['cache_id']);
		}

	}


	function render_page($args) {
		if ($args['template'] == 'messagepart') {
			// $this->include_script('detach_attachments.js');
			$this->add_texts('localization/',true);
			// $this->rcmail->output->set_env('user_hash',md5($_SESSION['user_id']));
		}
	}


	function attachment_delete($args) {

        if ($this->debug) {
            rcube::write_log('detach_attachment', 'attachment_delete. pre. args=' . print_r($args,true));
        }
    	$filesize = filesize($args['path']);
    	$F = rcube_utils::get_input_value('_id',RCUBE_INPUT_GPC);
    	if (isset($_SESSION['attachments_total'][$F])) {
    		$J = $_SESSION['attachments_total'][$F];
    	}
    	@unlink($args['path']);
        $tt = false;
        $result = $this->rcmail->db->query("SELECT `cache_key` FROM `". rcmail::get_instance()->db->table_name('attachments') ."` WHERE `user_id`=? AND `cache_key` LIKE '". $args['id'] ."%'", $_SESSION['user_id']);
  	    while ($result_query = $this->rcmail->db->fetch_assoc($result)) {
            $tt = true;
            $file = $this->dir . '/' . $result_query['cache_key'];
            $filesize = filesize($file);
            @unlink($file);
            $_SESSION['attachments_total'][$F] -= $filesize;
            if ($this->debug) {
                rcube::write_log('detach_attachment', 'attachment_delete. result total='.$_SESSION['attachments_total'][$F].' file='.$file.' filesize='.$filesize.' args=' . print_r($args,true));
            }
            if ($result = $this->rcmail->db->query("DELETE FROM `". rcmail::get_instance()->db->table_name('attachments') ."` WHERE `user_id`=? AND `cache_key`=?", $_SESSION['user_id'], $result_query['cache_key'])) {
                if ($this->debug) {
                    rcube::write_log('detach_attachment', 'attachment_delete. user_id='. $_SESSION['user_id'] .', result_query='. print_r($result_query,true));
                }
            }
        }
        if (!$tt) {
	        $_SESSION['attachments_total'][$F] -= $filesize;
            if ($this->debug) {
                rcube::write_log('detach_attachment', 'attachment_delete. total='.$_SESSION['attachments_total'][$F].' file='.$file.' filesize='.$filesize.' args=' . print_r($args,true));
            }
        }
        $args['status'] = true;
        if ($this->debug) {
            rcube::write_log('detach_attachment', 'attachment_delete. post. args=' . print_r($args,true));
        }

        return $args;
	}


	function startup() {

        if ($this->debug) {
            rcube::write_log('detach_attachment', 'startup. action=' . $_GET['_action']);
        }

		if (isset($_GET['_action']) && $_GET['_action'] == 'dta') {
			$this->rcmail = rcmail::get_instance();
			if(isset($_GET['_dta']) && $B = $_GET['_dta']) {
				$B = urldecode($B);
				$E = $this->rcmail->db->query("SELECT `downloads`, `user_id`, `fname` FROM `".rcmail::get_instance()->db->table_name('attachments')."` WHERE `cache_key`=? LIMIT 1",$B);
				if($D = $this->rcmail->db->fetch_assoc($E)) {
					if(isset($_GET['delete']) && $q = $_GET['_delete']) {
						if($q == md5($_SESSION['user_id'])) {
							if(unlink($this->dir.$B)) {
								$E=$this->rcmail->db->query("DELETE FROM `".rcmail::get_instance()->db->table_name('attachments')."` WHERE `cache_key`=?",$B);
							}
						}
					} else { // if($_SESSION['user_id']!=$D['user_id']) {
						/*
						$w=$D['downloads']+1;
						$E=$this->rcmail->db->query("UPDATE ".get_table_name('attachments')." SET downloads=".$w." WHERE cache_key=?",$B);
						*/
						if($D['fname']){
							$i = $D['fname'];
						} else {
							$i = $B;
						}
						$o = new rcube_browser;
						rcmail::get_instance()->output->nocacheing_headers();
						header("Content-Type: application/octet-stream");
						if($o->ie)
							header("Content-Type: application/force-download");
						@set_time_limit(0);
						header("Content-Disposition: attachment; filename=\"".$i."\"");
						header("Content-length: ".filesize($this->dir.$B));
						ob_clean();
						flush();
						readfile($this->dir.$B);
					}
				} else {
					$this->add_texts('localization',false);
					$this->rcmail->output->send('detach_attachments.error');
				}
				exit;
			}
		}

	}


    function attachment_save($args) {
        if ($this->debug) {
            rcube::write_log('detach_attachment', 'attachment_save. args=' . print_r($args, true));
        }

		if ($this->detach == -1) {
            $args['status'] = true;
			return $args;
		}

		$J = 0;
		$F = rcube_utils::get_input_value('_id',RCUBE_INPUT_GPC);
		if (isset($_SESSION['attachments_total'][$F])) {
			$J = $_SESSION['attachments_total'][$F];
		}
		$G = $args['size'];
		$J = $J + $G;
		$_SESSION['attachments_total'][$F] = $J;
		if (0 && $J >= ((int)$this->detach * 1024 * 1024)) {
    		$args['id'] = md5(session_id().microtime().$_SESSION['user_id']);
    		$args['status'] = true;

            if ($this->debug) {
                rcube::write_log('detach_attachment', 'attachment_save. post. total=' . sprintf("%d", $J / 1024 / 1024) . ' Mb. Max=' . ((int)$this->detach) .' Mb. args=' . print_r($args,true));
            }
        } else {
            $args = parent::save($args);

            if ($this->debug) {
                rcube::write_log('detach_attachment', 'attachment_save. post. total=' . sprintf("%d", $J / 1024 / 1024) . ' Mb. Misses. Max=' . ((int)$this->detach) .' Mb. args=' . print_r($args,true));
            }
        }

        return $args;
    }


	function message_compose($args) {

        if ($this->debug) {
            rcube::write_log('detach_attachment', 'message_compose. args=' . print_r($args,true));
        }

		// rcmail::get_instance()->session->remove('attachments_total');

		return $args;
	}


	function attachment_upload($args) {
        if ($this->debug) {
            rcube::write_log('detach_attachment', 'attachment_upload. pre. args=' . print_r($args,true));
        }
		$path = $args['path'];
		if ($this->detach == -1) {
            $args['status'] = true;
			return $args;
		}

		$J = 0;
		$F = rcube_utils::get_input_value('_id',RCUBE_INPUT_GPC);
		if (isset($_SESSION['attachments_total'][$F])) {
			$J = $_SESSION['attachments_total'][$F];
		}
		$G = filesize($path);
		$J = $J + $G;
		$_SESSION['attachments_total'][$F] = $J;
		if ($J >= ((int)$this->detach * 1024 * 1024)) {
    		$args['id'] = md5(session_id().microtime().$_SESSION['user_id']);
			$mimetype = strtolower($args['mimetype']);
			if ($mimetype == 'text/calendar' || $mimetype == 'text/ical' || $mimetype == 'application/ics') {
				$args['status'] = true;
				return $args;
			}
			$j = substr( strtolower(urlencode($args['id'].'_'.asciiwords($args['name'],false,'_'))), 0, 128 );

				$this->rcmail->db->query("INSERT INTO `".rcmail::get_instance()->db->table_name('attachments')."` (`created`, `user_id`, `cache_key`, `fname`, `data`) VALUES (".$this->rcmail->db->now().", ?, ?, ?, ?)", $_SESSION['user_id'], $j, $args['name'], '');
				move_uploaded_file($path, $this->dir.$j);
				if( isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS']=='1' || strtolower($_SERVER['HTTPS'])=='on') ) {
					$H="s";
				} else {
					$H="";
                }
				$url = 'http'.$H. '://' . $_SERVER['HTTP_HOST'] . $_SERVER['DOCUMENT_URI'] . '?_action=dta&_dta=' . $j;

				if($G > 1024*1024) {
                    $filebytes = round($G/1024/1024,2)." MBytes";
                } else if($G > 1024) {
                    $filebytes = round($G/1024,2)." KBytes";
                } else {
                    $filebytes = round($G,2)." Bytes";
                }

				$O = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN">'."\r\n" .
				     '<html>' .
                     '<head>' .
                     '<style type="text/css">body{font-family: "Lucida Grande", Verdana, Arial, Helvetica, sans-serif;font-size: 12px;}</style>' .
                     '<meta http-equiv="refresh" content="0;URL='.$url.'">' .
                     '</head>' .
                     '<body><div>' .
				     '<a id="' . md5($_SESSION['user_id']) . '" download onclick="location=\'' . $url . '\';" href="' . $url . '">' .
                        (htmlentities($args['name'],ENT_COMPAT,'UTF-8')) .
                     '</a> (' . $filebytes . ') ' .
				     '[' . date($this->rcmail->config->get('date_format','d.m.Y') . ' ' . $this->rcmail->config->get('time_format','H:i'), time()+86400*(int)$this->rcmail->config->get('dl_expires',30)) . ']' .
				     '</div></body></html>'
                ;
				file_put_contents($path . '.dta', $O);
				$args['size'] = filesize($path . '.dta');
				$args['path'] = $path . '.dta';
				$args['name'] = $args['name'] . '.html';
				$args['mimetype'] = 'text/html';
				$args['status'] = true;
                if ($this->debug) {
                    rcube::write_log('detach_attachment', 'attachment_upload. post. total=' . sprintf("%d", $J / 1024 / 1024) . ' Mb. Max='. ((int)$this->detach) .' Mb. args=' . print_r($args,true));
                }

        } else {

            $args = parent::upload($args);

            if ($this->debug) {
                rcube::write_log('detach_attachment', 'attachment_upload. post. total=' . sprintf("%d", $J / 1024 / 1024) . ' Mb. Misses. Max=' . ((int)$this->detach) .' Mb. args=' . print_r($args,true));
            }
        }

		return $args;
	}



	function preferences_list($C) {
		if($C['section']=='compose'){
			$this->add_texts('localization',false);
			$this->rcmail=rcmail::get_instance();
			$u='rcmfd_detach_attachments';
			$P=new html_select(array('name'=>'_cum_upload_detach','id'=>$u));
			$P->add($this->gettext('never'),-1);
			$P->add($this->gettext('always'),0);
			$L=$this->rcmail->config->get('cum_upload_limit_resolution',0.1);
			$R=5;
			if($L<1){
				$R=50;
			}
			for($M=$L;$M<=$this->rcmail->config->get('cum_upload_detach_max_limit',$L*$R);$M+=$L){
				if($M==0.6){
					$M=1;$L=$L*10;$R=$R/10;
				}
				$P->add($this->gettext('detach_attachments.cumsize').' > '.$M.' MB',str_replace(',','.',$M));
			}
			$H=$this->rcmail->config->get('cum_upload_detach','5');
			$H=str_replace(',','.',$H);
			if($H==0){
				$H=(int)$H;
			}
			$C['blocks']['main']['options']['detachattachments']['title']=Q($this->gettext('storeattachments'));
			$C['blocks']['main']['options']['detachattachments']['content']=$P->show($H);
		}
		return $C;
	}


	function preferences_save($C) {
		if($C['section']=='compose') {
			$this->rcmail=rcmail::get_instance();
			$G=rcube_utils::get_input_value('_cum_upload_detach',RCUBE_INPUT_POST);
			$C['prefs']['cum_upload_detach']=str_replace(',','.',$G);
			return $C;
		}
	}



	function counts() {
		$this->rcmail=rcmail::get_instance();
		$y=rcube_utils::get_input_value('_hash',RCUBE_INPUT_POST);
		$B=urldecode(rcube_utils::get_input_value('_file',RCUBE_INPUT_POST));
		if(md5($_SESSION['user_id'])==$y){
			$E=$this->rcmail->db->query("SELECT `downloads`, `user_id` FROM `".rcmail::get_instance()->db->table_name('attachments')."` WHERE `cache_key`=?",$B);
			if($D=$this->rcmail->db->fetch_assoc($E)){
				$Z=$D['downloads'];
			}
			else{
				$Z='?';
			}
		}
		else{
			$Z='?';
		}
		$this->rcmail->output->command('plugin.dta_counts',$Z);
	}

}
