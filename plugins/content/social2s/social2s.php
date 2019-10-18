<?php
/**
* @package     Joomla.Plugin
* @subpackage  Content.social2s
*
* @copyright   Copyright (C) 2005 - 2017 dibuxo.com All rights reserved.
* @license     GNU General Public License version 2 or later; see LICENSE.txt
*/

defined('_JEXEC') or die;


/**
* social2s plugin.
*
* @package     Joomla.Plugin
* @subpackage  Content.social2s
* @since       1.5
*/
class PlgContentSocial2s extends JPlugin
{
	/**
	* Load the language file on instantiation.
	*
	* @var    boolean
	* @since  3.1
	*/
	protected $autoloadLanguage = true;

	//necessary for vk
	public static $article_num = '0';
	
	public static $s2s_debug = array();

	public static $s2s_og_debug = array();

	//s2s_obj
	public static $s2s_obj;

	//social obj
	public static $s2s_social;

	//social check
	public static $s2s_check;

	//social images
	public static $s2s_images;

	//social tmpl
	public static $s2s_tmpl;

	//opengraph
	public static $s2s_og;

	//TIMER
	public static $startmicro;
	
	

	public function __construct(&$subject, $config)
    {


    
		//$params = json_decode($config['params']);
    	
		$jinput = JFactory::getApplication()->input;

		$view = $jinput->get('view', '', 'WORD');
		$component = $jinput->get('option', '', 'WORD');
		$id = $jinput->get('id', '', 'INT');
		$catid = $jinput->get('catid', '', 'INT');
		$itemid = $jinput->get('Itemid', '', 'INT');


		if($this->params === NULL){
			$plugin = JPluginHelper::getPlugin('content', 'social2s');
			$params = new JRegistry($plugin->params);
		}else{
			$params = $this->params;
		}

		//TIMER
		self::$startmicro = microtime(TRUE);

		self::$s2s_obj = (object) array(
			'context'=>$component.'.'.$view,
			'params'=>$params,
			'view'=>$view, 
			'component'=>$component,
			'id'=>$id,
			'catid'=>$catid,
			'itemid'=>$itemid,
			'images_path' => JURI::base().'media/plg_social2s/assets/',
			's2sjs_file_loaded' => false,
			's2scss_file_loaded' => false
		);


		//its necessary??
		//self::$s2s_obj->params = $this->params;

		$this->loadLanguage();

		//check component
	    require_once('features/in_ex.php');
		self::$s2s_check = new s2s_checkvisibility;

		//first stop: component and view
		//if(!self::$s2s_check::check_component($component, $view)) return;
		
		require_once('features/s2s_images.php');
	    self::$s2s_images = new s2s_images;

	    require_once('features/tmpl.php');
	    self::$s2s_tmpl = new s2s_tmpl;

	    require_once('features/opengraph.php');
	    self::$s2s_og = new s2s_og;

		parent::__construct($subject, $config);

    }



    public static function updateS2sObject($obj){
		self::$s2s_obj = $obj;
    }

    //DEBUG
    public static function updateS2sDebug($label, $message = '', $style = 'default', $icon = 'debug', $time = true){
		$debug = '<td class="text-right">';
			$debug .= '<i class="fa fa-'.$icon.'" aria-hidden="true"></i> ';
			$debug .= '<strong>'.$label.': </strong>';
		$debug .= '</td><td>';
			$debug .= '<span class="text-'.$style.'">'.$message.'</span>';
		$debug .= '</td><td class="text-center">';
		if($time){
			$current_time = microtime(TRUE);
			$totaltime = ($current_time - PlgContentSocial2s::$startmicro)*100000;
			$i_ttime = (int) $totaltime/100;
			$debug .= ' <small>'.$i_ttime.'ms</small>';
		}

		$debug .= '</td>';

		self::$s2s_debug[] = $debug;
    }

    public static function getDebug(){
		$s2s_obj = self::$s2s_obj;

		$html = '';
		//v3
		//$param_s2s_debug = $params->get('social2s_debug', 0);
		//v4
		
		$param_s2s_debug = $s2s_obj->params->get('s2s_debug', '0');

		
		if($param_s2s_debug){
			
			$html .= '<div class="table-responsive"><table class="table table-striped table-condensed table-bordered "><thead><tr class="alert alert-success">
					<th class="text-center"><i class="fa fa-bug"></i></th>
					<th class="text-center">Debug</th>
					<th class="text-center"><i class="fa fa-clock-o" aria-hidden="true"></i></th></tr></thead>';
				foreach (self::$s2s_debug as $key => $value) {
					$html .= '<tr>'.$value.'</tr>';
				}
				
			$html .= '</table></div>';

			//vacío el array del debug
			//v4
			self::$s2s_debug=array();
			//v3
			//unset(self::$s2s_debug);
		}

		//OPENGRAPH
		$param_s2s_og_debug = $s2s_obj->params->get('s2s_og_debug', '0');

		if($param_s2s_og_debug){

			if(count(self::$s2s_og_debug)){
				$html .= '<div class="table-responsive"><table class="table table-striped table-condensed table-bordered "><thead><tr class="alert alert-info">
					<th class="text-center"><i class="fa fa-bug"></i></th>
					<th class="text-center">OpenGraph Debug</th>
					<th class="text-center"><i class="fa fa-clock-o" aria-hidden="true"></i></th></tr></thead>';
				foreach (self::$s2s_og_debug as $key => $value) {
					$html .= '<tr>'.$value.'</tr>';
				}
				
				$html .= '</table></div>';
			}


			//vacío el array del debug
			//v4
			self::$s2s_og_debug=array();
			//v3
			//unset(self::$s2s_debug);
		}

		return $html;


	}


    //OG DEBUG
    public static function updateS2sOgDebug($label, $message = '', $style = 'default', $icon = 'debug', $time = true){
		$debug = '<td class="text-right">';
			$debug .= '<i class="fa fa-'.$icon.'" aria-hidden="true"></i> ';
			$debug .= '<strong>'.$label.': </strong>';
		$debug .= '</td><td>';
			$debug .= '<span class="text-'.$style.'">'.$message.'</span>';
		$debug .= '</td><td class="text-center">';
		if($time){
			$current_time = microtime(TRUE);
			$totaltime = ($current_time - PlgContentSocial2s::$startmicro)*100000;
			$i_ttime = (int) $totaltime/100;
			$debug .= ' <small>'.$i_ttime.'ms</small>';
		}

		$debug .= '</td>';

		self::$s2s_og_debug[] = $debug;
    }

    /**
     * @param $row
     * @return string|void
     *
     * @since version
     */
	//MAIN FUNCTION AFTER OR BEFORE
	public function onJevents($row){

		//check if jevent option is checked
		if(!$this->params->get('s2s_jevents',0)) return false;

		self::$s2s_debug[] = '<strong>Context: </strong> <span class="label label-info">jEvents</span>';

		
		//$html = 'Jevents social2s v4';
		//$html = $this->formatTmpl($this->params,$row,false,'jevents');
		$html = self::$s2s_tmpl->init(self::$s2s_obj, $row);

		//OPENGRAPH
		self::$s2s_og->init(self::$s2s_obj, $row);

		//debug
		$html .= self::getDebug();

		return $html;

		//return "success!";
	}

    /**
     * @param $context
     * @param $row
     * @param $params
     * @param int $page
     * @param string $msg
     *
     * @return string|void
     *
     * @since version
     */
    public static function onK2($context, &$row, &$params, $page=0, $msg=""){


		self::updateS2sDebug('Function', 'onK2'.$msg, 'info', 'cube');

		self::$s2s_obj->context = $context;
		self::$s2s_obj->params = $params;

        $s2s_check = new s2s_checkvisibility;
	
		//CHECK ON
		//if(!self::$s2s_check::check_on_k2()) return self::getDebug();

        if(!$s2s_check::check_on_k2())return self::getDebug();

		self::updateS2sDebug('comp', 'k2 passed', 'success', 'puzzle-piece ');

		//CHECK CONTEXT
		//self::updateS2sDebug('Context', self::$s2s_obj->context, 'info', 'cube');
		//if(!self::$s2s_check::check_context()){ return self::getDebug();}
		$acceso = false;
		if($context=='com_k2.item' || $context=='com_k2.itemlist'){
			$acceso = true;
		}
		if(!$acceso){
			return;
		}
		
		//CHECK POSITION (before or after)
		//$s2s_pos = (integer) self::$s2s_check::check_position();
		//self::updateS2sDebug('Check position', 'BEFORE: '.$s2s_pos.'. PASSED', 'success', 'toggle-on');

		//CHECK VISIBILITY
		if(!$s2s_check::checkPages_v4($params,$row,$context)) return self::getDebug();

		//!!!!!!!!!!!!!TMPL!!!!!!!!!!!!!!
		//$html = $this->formatTmpl(self::$s2s_obj->params,$row,false,$context);
		$html = self::$s2s_tmpl->init(self::$s2s_obj, $row);

		//OPENGRAPH
		self::$s2s_og->init(self::$s2s_obj, $row);
		$html .= self::getDebug();

		return $html;

	}

	public function onSP($context, &$row, &$params, $page=0){

		self::$s2s_debug[] = '<strong>Context: </strong> <span class="label label-info">'.$context.'</span>';

		$acceso = false;
		if($context=='com_sppagebuilder.page'){
			$acceso = true;
		}

		if($context=='com_spsoccer.page'){
			$acceso = true;
		}

		if(!$acceso){
			return;
		}

		$html = $this->formatTmpl($this->params,$row,false,$context);

		return $html;

	}

	/**
	 * [onContentBeforeDisplay]
	 * @param  [type]  $context  [context]
	 * @param  [type]  &$row     [info about article]
	 * @param  [type]  &$jparams [joomla parameters]
	 * @param  integer $page     [pagination]
	 * @return [type]            [HTML]
	 */
	public function onContentBeforeDisplay($context, &$row, &$jparams, $page=0){

		self::updateS2sDebug('Function', 'onContentBeforeDisplay', 'info', 'cube');

		self::$s2s_obj->context = $context;
		$params = self::$s2s_obj->params;

        $s2s_check = new s2s_checkvisibility;

		//CHECK ON
		if(!$s2s_check::check_on()) return self::getDebug();

		//CHECK MODULE
		//modifies context
		if(!$s2s_check::check_module($jparams)) return self::getDebug();

		
		//CHECK SUPPORT o DO_MY_BEST
		//if(!self::$s2s_check::check_component_support()) return  self::getDebug();


		//CHECK CONTEXT
		self::updateS2sDebug('Context', self::$s2s_obj->context, 'info', 'cube');
		if(!$s2s_check::check_context()){ return self::getDebug();}


		//CHECK POSITION (before or after)
		$s2s_pos = (integer) $s2s_check::check_position();

		if($s2s_pos == 0 || $s2s_pos == 2){

			self::updateS2sDebug('Check position', 'BEFORE: '.$s2s_pos.'. PASSED', 'success', 'toggle-on');

			//v4 superseed
			if(!$s2s_check::checkPages_v4($params,$row,$context)) return self::getDebug();


			//!!!!!!!!!!!!!TMPL!!!!!!!!!!!!!!
			//$html = $this->formatTmpl(self::$s2s_obj->params,$row,false,$context);
			$html = self::$s2s_tmpl->init(self::$s2s_obj, $row);

			//!!!!!!!!!!!!!OPENGRAPH!!!!!!!!!!!!!!
			self::$s2s_og->init(self::$s2s_obj, $row);
			$html .= self::getDebug();

			return $html;

		}else{
			self::updateS2sDebug('Check position', 'BEFORE: '.$s2s_pos.'. NOT passed', 'danger', 'toggle-on');
		}

		echo self::getDebug();

	}


	public function onContentAfterDisplay($context, &$row, &$jparams, $page=0){
		
		self::updateS2sDebug('Function', 'onContentAfterDisplay', 'info', 'cube');

		//-->IMPROVE CHECK IF THINGS ARE LOADED
		self::$s2s_obj->context = $context;
		$params = self::$s2s_obj->params;

		 $s2s_check = new s2s_checkvisibility;
		
		//CHECK MODULE
		//modifies context
		if(!$s2s_check::check_module($jparams)) return self::getDebug();


		//CHECK ON
		if(!$s2s_check::check_on()) return self::getDebug();	

		self::updateS2sDebug('Context', self::$s2s_obj->context, 'info', 'cube');

		//CHECK CONTEXT
		if(!$s2s_check::check_context()){ return self::getDebug();}

		//CHECK POSITION (before or after)
		$s2s_pos = (integer) $s2s_check::check_position();

		if($s2s_pos == 1 || $s2s_pos == 2){

			self::updateS2sDebug('Check position', 'BEFORE: '.$s2s_pos.'. PASSED', 'success', 'toggle-on');

			//v4 superseed
			if(!$s2s_check::checkPages_v4($params,$row,$context)) return self::getDebug();

			//!!!!!!!!!!!!!TMPL!!!!!!!!!!!!!!
			//$html = $this->formatTmpl(self::$s2s_obj->params,$row,false,$context);
			$html = self::$s2s_tmpl->init(self::$s2s_obj, $row);

			//!!!!!!!!!!!!!OPENGRAPH!!!!!!!!!!!!!!
			self::$s2s_og->init(self::$s2s_obj, $row);
			$html .= self::getDebug();

			return $html;

		}else{
			self::updateS2sDebug('Check position', 'BEFORE: '.$s2s_pos.'. NOT passed', 'danger', 'toggle-on');
		}

		echo self::getDebug();

	}

	
}
