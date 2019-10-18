<?php
/**
 * @Copyright
 * @package     Field - S2s Order
 * @author      anton {@link http://www.dibuxo.com}
 * @version     Joomla! 3.x - 1.0.24
 * @date        Created on 09-09-2013
 * @link        Project Site {@link http://dibuxo.com}
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

class JFormFieldS2sOrder extends JFormField
{
    protected $type = 's2sorder';


    protected function getInput()
    {
        $orden = strtolower($this->value);

        $field_set = $this->form->getFieldset();

        $field_value = '';

        $jversion = new JVersion();
        $jversion = substr($jversion->getShortVersion(), 0, 1);
       
        $field_value .= '<ul style="margin-left:0;" id="sortable" class="s2s_order btn-group">';    
              
        //number of characters to prevent errors
        if(strlen($orden) != 31){
            $orden='0,1,2,3,4,5,6,7,8,9,10,11,12,13';
        }

        //var_dump($orden);

        $order_array = explode(',',$orden);

        $new_order = array();

        foreach ($order_array as $key => $value) {
          
            if($value == '0'){
                $field_value .= '<li data-s2s="0" class="btn btn-small s2sfo fo-twitter"></li>';
            }elseif ($value == '1'){
                $field_value .= '<li data-s2s="1" class="btn btn-small s2sfo fo-facebook"></li>';
            }elseif ($value == '2'){
                $field_value .= '<li data-s2s="2" class="btn btn-small s2sfo fo-pinterest"></li>';
            }elseif ($value == '3'){
                $field_value .= '<li data-s2s="3" class="btn btn-small s2sfo fo-linkedin"></li>';
            }elseif ($value == '4'){
                $field_value .= '<li data-s2s="4" class="btn btn-small s2sfo fo-google-plus disabled"></li>';
            }elseif ($value == '5'){
                $field_value .= '<li data-s2s="5" class="btn btn-small s2sfo fo-whatsapp"></li>';
            }elseif ($value == '6'){
                $field_value .= '<li data-s2s="6" class="btn btn-small s2sfo fo-telegram"></li>';
            }elseif ($value == '7'){
                $field_value .= '<li data-s2s="7" class="btn btn-small s2sfo fo-flipb"></li>';
            }elseif ($value == '8'){
                $field_value .= '<li data-s2s="8" class="btn btn-small s2sfo fo-delio"></li>';
            }elseif ($value == '9'){
                $field_value .= '<li data-s2s="9" class="btn btn-small s2sfo fo-tumblr"></li>';
            }elseif ($value == '10'){
                $field_value .= '<li data-s2s="10" class="btn btn-small s2sfo fo-vk"></li>';
            }elseif ($value == '11'){
                $field_value .= '<li data-s2s="11" class="btn btn-small s2sfo fo-email"></li>';
            }elseif ($value == '12'){
                $field_value .= '<li data-s2s="12" class="btn btn-small s2sfo fo-reddit"></li>';
            }elseif ($value == '13'){
                $field_value .= '<li data-s2s="13" class="btn btn-small s2sfo fo-'.$field_set['jform_params_mas_style']->value.'"></li>';
            }
            $new_order[] = $value;
        }

        $field_value .= '</ul>';

        $field_value .= '</br>';

        if($jversion == "3"){
            JHtml::_('jquery.ui', array('core', 'sortable'));
        }elseif($jversion == "4"){
            //v4
            JHtml::_('jquery.ui', array('core', 'sortable'));
        }

        //var_dump($orden);
        
        $field_value .= '<input class="input_s2s_orden" type="hidden" name="' . $this->name . '" value="'.$orden.'" />';
    
        return $field_value;
    }




}
