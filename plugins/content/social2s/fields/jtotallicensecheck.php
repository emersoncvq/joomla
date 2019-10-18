<?php
/**
 * @Copyright
 * @package     Field - license check
 * @author      anton {@link http://www.dibuxo.com}
 * @version     Joomla! 3 - 4.0.124
 * @date        Created on 09-02-2019
 * @link        Project Site {@link http://jtotal.org}
 *
 * @license GNU/GPL
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('JPATH_PLATFORM') or die;

class JFormFieldJtotalLicenseCheck extends JFormField
{
    protected $type = 'jtotallicensecheck';

    public $tiempo = '';
    public $error = array();

    protected function getLabel(){
        $mode = $this->element['mode'];
        if($mode == 'dashboard'){

            return "<strong>License</strong>";
        }
        //return "<strong>License</strong>";
    }

    protected function getInput()
    {

        $session = JFactory::getSession();

        $field_set = $this->form->getFieldset();
        $params = $this->form->getValue('params');
     
        $license_email = $field_set['jform_params_jtotal_email']->value;
        $license_key = $field_set['jform_params_jtotal_key']->value;



        //s2s v3 to v4
        $s2sv3_email = $field_set['jform_params_s2s_license_email']->value;
        
        if($license_email == ''){
            if($s2sv3_email != ''){
                $license_email = $s2sv3_email;
                $license_key = 'SOCIAL2SV3';
            } 
        }


        $valida_sesion = $session->get('jtotallicensecheck');

        $this->tiempo = '';
        $field_value = '';

        /*******test********/
        //$session->set('JTlicensecheck',0);
        /*******test********/

        $valida = $this->_checkLicense($license_email, $license_key);

        if($valida){
            /*PRO*/
            $field_value .= $this->pro();
         }else{
            /*LITE*/
            $field_value .= $this->lite();
        }


        //$field_set = $this->form->getFieldset();
        $field_value .= "";

        if(count($this->error)>=1){
            //process error
            $field_value .= '<div class="alert alert-danger">';

            foreach ($this->error as $key => $value) {
                echo '<br>';
                 $field_value .= '<i class="fa fa-exclamation-circle"></i> '.$value;
            }

            $field_value .= '</div>';
        }

        $mode = $this->element['mode'];
        if($mode == 'dashboard'){

            if(!$session->get('JTlicensecheck')){
                $session->set('JTlicensecheck',0);
            }
            if($session->get('JTlicensecheck')==1){
                return '<h6 class="text-success"><i class="fas fa-unlock"></i> '.JText::_('JTOTAL_LICENSE_OK_ENJOY').'</h6>';
            }else{

                $lite_dashboard = '<h6 class="text-warning"><i class="fas fa-lock"></i> ';

                $lite_dashboard .= 'Social2s '.JText::_('JTOTAL_LICENSE_LITE_H1');
                $lite_dashboard .= '</h6>';

                return $lite_dashboard;
            }
        }



        return $field_value;
    }


    private function _checkLicense($email, $key){

        $params = $this->form->getValue('params');

        $session = JFactory::getSession();

        if(!$session->get('JTkeycheck')){
            $session->set('JTkeycheck',$key);
        }
        if(!$session->get('JTemailcheck')){
            $session->set('JTemailcheck',$email);
        }
        if(!$session->get('JTlicensecheck')){
            $session->set('JTlicensecheck',0);
        }


        $valida = false;

        //CHECK EMPTY EMAIL AND JKEY
        if($email=='' || $key==''){
            $session->set('JTlicensecheck', 0);
            //$session->set('JTkeycheck',$key);
            $valida = false;
        }else{

            $key_session = $session->get('JTkeycheck');
            $email_session = $session->get('JTemailcheck');
            $license_check =  $session->get('JTlicensecheck');

            if($key_session == $key && $email_session ==$email){
                if($license_check == 1){
                    //EVERYTHINK IS OK...
                    return true;
                }
            }


            //KEY != SESSION
            if($key_session != $key || $email_session !=$email){
                $session->set('JTlicensecheck', 0);
                //$session->set('JTkeycheck',$key);

                if($this->_checkHome($email, $key)===true){
                    $session->set('JTlicensecheck', 1);
                    $key_session = $session->set('JTkeycheck',$key);
                    $email_session = $session->set('JTemailcheck',$email);
                    //$jt_tiempo = $session->set('JTtimeleft',$this->tiempo);

                    $valida = true;
                }else{
                    $session->set('JTlicensecheck', 0); 
                    $valida = false;
                }
            }else{

               //var_dump('</br>key_session == key ');
                if($session->get('JTlicensecheck') == 1){
                    //var_dump('</br>JTlicensecheck == 1 ');
                    $valida = true;
                }else{
                    //var_dump('</br>JTlicensecheck == 0 ');
                    if($this->_checkHome($email, $key)===true){

                        $session->set('JTlicensecheck', 1);
                        $key_session = $session->set('JTkeycheck',$key);
                        $email_session = $session->set('JTemailcheck',$email);
                        //$jt_tiempo = $session->set('JTtimeleft',$this->tiempo);

                        $valida = true;
                    }else{
                        $valida = false;
                    }
                }
            }
        }
        return $valida;
    }

    private function _checkHome($email, $key){
        
        //TEST
        //$url_check = 'http://localhost/online/users_jtotal/index.php?option=com_jtcontrol&tmpl=component&view=jtcheck&email='.$email.'&key='.$key.'&product=1';
        
        //REAL
        $url_check = 'https://users.jtotal.org/index.php?option=com_jtcontrol&tmpl=component&view=jtcheck&email='.$email.'&key='.$key.'&product=1';
        

        $session = JFactory::getSession();

        //CURL
        //var_dump(function_exists('curl_init'));
        if(function_exists('curl_init')){
            $session->set('jtlicense_validation:', 'CURL');
            $checkHomeCurl = $this->_checkHomeCurl($url_check);
            return $checkHomeCurl;
        }else{
        //CURL_FALLBACK
            $session->set('jtlicense_validation:', 'fsockopen');
            return $this->_checkHomeCurl_fallback($url_check);
        }

    }


    public function _checkHomeCurl($url_check){

        $curl_response = curl_init($url_check);
        curl_setopt($curl_response, CURLOPT_HEADER, 0);
        curl_setopt($curl_response, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_response, CURLOPT_NOSIGNAL, 1);
        curl_setopt($curl_response, CURLOPT_CONNECTTIMEOUT, 5);
        $response = curl_exec($curl_response);

        $session = JFactory::getSession();
        //echo "</br>la respuesta es:".$response;
        //
        $response = json_decode($response);

        //response == success
        if(isset($response->success)){
            if($response->data == "OK"){
                $session->set('jtotallicensecheck', 1);
                $this->tiempo = $response->message->tiempo;
                $this->error[] = 'Data OK';
                return true;
            }else{
                $session->set('jtotallicensecheck', 0);
                $this->error[] = 'Data error';
                return false;
            } 
        }else{
            $this->error[] = 'Error receiving data';
            return false;
        }
    }


    private function _checkHomeCurl_fallback($url){

        $session = JFactory::getSession();
        $response = $this->rest_helper($url);
        $response = json_decode($response);

        //response == success
        if(isset($response->success)){
            if($response->data == "OK"){
                $session->set('jtotallicensecheck', 1);
                $this->tiempo = $response->message->tiempo;
                return true;
            }else{
                $session->set('jtotallicensecheck', 0);
                return false;
            } 
        }else{
            $this->error[] = 'Error receiving data';
            return false;
        }
    }


    function rest_helper($url, $params = null, $verb = 'GET', $format = 'html')
    {
      $cparams = array(
        'http' => array(
          'method' => $verb,
          'ignore_errors' => true
        )
      );
      if ($params !== null) {
        $params = http_build_query($params);
        if ($verb == 'POST') {
          $cparams['http']['content'] = $params;
        } else {
          $url .= '?' . $params;
        }
      }

      $context = stream_context_create($cparams);
      $fp = fopen($url, 'rb', false, $context);
      if (!$fp) {
        $res = false;
      } else {
        $res = stream_get_contents($fp);
      }

      if ($res === false) {
        throw new Exception("$verb $url failed: $php_errormsg");
      }

      switch ($format) {
        case 'json':
          $r = json_decode($res);
          if ($r === null) {
            throw new Exception("failed to decode $res as json");
          }
          return $r;
        case 'html':
          $r = $res;
          if ($r === null) {
            throw new Exception("failed to decode $res as json");
          }
          return $r;
        case 'xml':
          $r = simplexml_load_string($res);
          if ($r === null) {
            throw new Exception("failed to decode $res as xml");
          }
          return $r;
      }
      return $res;
    }


    private function lite(){

        $module_id = $this->form->getValue('id');
        $params = $this->form->getValue('params');

        //v4
        $params->s2s_text_to_share = '0';
        $params->og_multi_img = '0';
        $params->og_add_dom_img = '0';
        $params->og_skip_intro_img = '0';

        //v3
        $params->social2s_base = '0';
        $params->s2s_stupid_cookie_on = '0';
       //$params->s2s_insert = '0';
        $params->s2s_virtuemart = '0';
        $params->s2s_takefromarticle = '1';
        $params->twitter_follow = '0';
        $params->tumblr_follow = '0';
        //$params->s2s_art_fixed = '0';
        //$params->s2s_cta_active = '0';
        $params->twitter_cards = '0';
        $params->opengraph_default_image_opt = '0';
        $params->s2s_jevents = '0';
        $params->s2s_og_debug = '0';
        $params->og_skip_min_img = '0';
        //$params->og_multi_img = '0';
        $params->twitter_fast_as_light = '0';
        $params->s2s_art_balloon_pos = '0';
        $params->s2s_k2 = '0';
        //$params->s2s_credits = '1';
        $params->facebook_like_count = '0';
        $params->facebook_share_count = '0';
        //$params->facebook_total_count = '0';
        $params->pinterest_count = '0';
        $params->linkedin_count = '0';
        $params->gplus_b_count = '0';
        $params->tumblr_count = '0';
        $params->s2s_vk_count = '0';
        $params->opengraph_metadescription = '0';
        $params->twitter_cards_summary = '0';

        if($params->mas_on==2){
            $params->mas_on = '0';
        }
        $params->mas_style='plus_q';

        //count
        if($params->s2s_art_fill==2){
            $params->s2s_art_fill = 0;
        }

        //consulta
        $reparams = $this->form->getValue('params');

        $encode_params = json_encode($reparams);
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        // Build the query
        $query->update($db->quoteName('#__extensions'));
        $query->set($db->quoteName('params') . ' = ' . $db->quote((string)$encode_params));
        $query->where($db->quoteName('element') . ' = ' . $db->quote("social2s"));

        // Execute the query
        $db->setQuery($query);
        
        $jversion = new JVersion();
        if($jversion->getShortVersion() >= "3.0"){
            //v3
            $db->query();
        }else{
            //v4
            $db->execute();
        }

        return $this->mensajeLITE();


    }

    private function pro(){

            $count_error = 0;
            $field_value='';

            $field_value.='
            <div class="jtotal_license_ok alert alert-success">';
                    $field_value.= '<a class="btn btn-success apply_cool_stuff" onclick="apply_cool_stuff();">'.JText::_('SOCIAL2S_APPLY_COOL_STUFF').'</a>';

                    $field_value.= '<div class="span9 col-sm-9 text-center">';
                    
                    $field_value.= '<br>';

                    $field_value.= '<h1 class="text-success"><i class="fas fa-unlock"></i> '.JText::_('JTOTAL_LICENSE_OK_ENJOY').'</h1>';
                    $field_value.= '<img src="'.JURI::root().'media/plg_social2s/assets/jtotal_portada.png" width="150"/>';
                    $field_value.= '<br><br>';

                    $field_value.= '<a class="btn btn-info" href="mailto:support@jtotal.org"><strong>'.JText::_('JTOTAL_LICENSE_SUPPORT').'</strong><br></a>  ';

                    $field_value.= '<a class="btn btn-success" href="https://extensions.joomla.org/extensions/extension/social-web/social-share/social2s/" target="_blank" rel="nofollow"><strong>'.JText::_('JTOTAL_LICENSE_REVIEW').'</strong><br></a>  ';

                    $field_value.= '<a class="btn btn-jtotal" href="https://users.jtotal.org" target="_blank" rel="nofollow"><strong>'.JText::_('JTOTAL_LICENSE_GOTO_USERS').'</strong><br></a>';

                    $field_value.='</div>';

                    $field_value.= '<div class="span3 col-sm-3">';

                    //var_dump($this->tiempo);
                    if($this->tiempo){
                        $field_value.= '<i class="fas fa-chess-knight fa-3x"></i>
                        '.self::datediff(date('Y-m-d h:i:s',time()), date('Y-m-d h:i:s', $this->tiempo)).' '.JText::_('JTOTAL_LICENSE_TIME').'
                        </div>
                        <div class="clearfix"></div>';
                    }

            $field_value.= '</div><div class="clearfix"></div>';

            //form activate
            $module_id = $this->form->getValue('id');

            //var_dump($module_id);

            //consulta
            $reparams = $this->form->getValue('params');

            $encode_params = json_encode($reparams);

            $field_value.= '<input type="hidden" class="jtotal_license_check" name="jtotal_license_check_js" value="1"/>';

            //bad idea
            //$field_value .= '<input type="text" name="jtotal_license_time" id="jtotal_license_time" value="'.$this->tiempo.'"/>';

           
            //OLD LICENSE
            if(strtolower($reparams->jtotal_key) == 'social2sv3'){
             


            }



            return $field_value;

    }

    private function mensajeLITE(){

        $mensaje='';

        $mensaje.= '<div class="alert alert-danger">

            <div class="col-sm-9 text-center">';

            $mensaje.= '<h1>'.JText::_('JTOTAL_LICENSE_LITE_H1').'</h1>';
            $mensaje.= '<h3>'.JText::_('JTOTAL_LICENSE_LITE_MODULE_HEADER').'</h3>';
            $mensaje.= '<h6>'.JText::_('JTOTAL_LICENSE_LITE_HELPUS').'</h6>';
            $mensaje.= '<i class="far fa-heart fa-2x fa-fw" aria-hidden="true"></i>';
            $mensaje.= '<i class="far fa-heart fa-2x fa-fw" aria-hidden="true"></i>';
            $mensaje.= '<i class="far fa-heart fa-2x fa-fw" aria-hidden="true"></i>';
            $mensaje.= '<br>';
            $mensaje.= '<div class="clearfix"></div>';
            $mensaje.= '<br>';


            $mensaje.= '  <a class="btn btn-success" href="http://sites.fastspring.com/dibuxo/product/social2sv4" target="_blank" rel="nofollow"><strong>'.JText::_('JTOTAL_LICENSE_BUY').'</strong><br>
                        <small>'.JText::_('JTOTAL_LICENSE_BUY_AND').'</small>
                    </a>';

            $mensaje.= '  <a class="btn btn-info" href="https://extensions.joomla.org/extensions/extension/social-web/social-share/social2s/" target="_blank" rel="nofollow"><strong>'.JText::_('JTOTAL_LICENSE_REVIEW').'</strong><br>
                        <small>'.JText::_('JTOTAL_LICENSE_REVIEW_AND').'</small>
                    </a>';


        $mensaje.= '</div>';
        $mensaje.= '<br>';
        $mensaje.= '<div class="col-sm-3 text-center">';
        $mensaje.= '<img src="'.JURI::root().'media/plg_social2s/assets/jtotal_portada.png" width="150"/>';
        $mensaje.= '</div>
                    <div class="clearfix"></div>
                    </div>';

        $mensaje.= ' <input type="hidden" class="jtotal_license_check" name="jtotal_license_check_js" value="0"/>';


        return $mensaje;
    }

    private function dateDiff($start, $end) { 
        $start_ts = strtotime($start);
        $end_ts = strtotime($end);
        $diff = $end_ts - $start_ts;
        return intval(round($diff / 3600)/24);
    }

}
