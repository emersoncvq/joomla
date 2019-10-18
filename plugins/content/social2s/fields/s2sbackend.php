<?php
/**
 * @Copyright
 * @package     Field - license check
 * @author      anton {@link http://www.dibuxo.com}
 * @version     Joomla! 3.0 - 4.0.124
 * @date        Created on 09-09-2018
 * @link        Project Site {@link http://store.dibuxo.com}
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

class JFormFieldS2sBackend extends JFormField
{
    protected $type = 's2sbackend';

    protected function getLabel(){
        //return "<strong>License</strong>";
    }

    protected function getInput()
    {
        $field_set = $this->form->getFieldset();

        //$field_value='<div class="alert alert-success">'.JText::_('SOCIAL2S_FIELD_LICENSE_OK').'</div>';
        $document = JFactory::getDocument();
        $plugin_path = JURI::root().'plugins/content/social2s';
        $media_path = JURI::root().'media/plg_social2s';

        $document->addStyleSheet($media_path.'/css/s2sfont.min.css', 'text/css');
    

        $version = new JVersion();
        $jversion = substr($version->getShortVersion(), 0, 1);

        $document->addScriptdeclaration('var jversion="' . $jversion . '";');

        $params  = $this->form->getValue('params');
        $debug = $params->s2s_debug;


        //JS
        if($debug){
            $document->addScript($media_path.'/js/s2sv4_backend.js');
        }else{
            $document->addScript($media_path.'/js/s2sv4_backend.min.js');
        }
        //CSS
        $document->addStyleSheet($media_path.'/css/s2sv4_backend.css', 'text/css');

        $document->addStyleSheet('https://use.fontawesome.com/releases/v5.7.0/css/all.css', 'text/css');

        
        //Specifics
        //V4.X
        if(version_compare($version->getShortVersion(), '4.0', 'ge')){

        //V3.X
        }elseif(version_compare($version->getShortVersion(), '3', 'ge')){
                    
        }

        $field_value = '';

        return $field_value;
    }

}
