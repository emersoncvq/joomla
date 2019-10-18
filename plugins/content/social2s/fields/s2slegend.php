<?php
/**
 * @Copyright
 * @package     Field - license check
 * @author      anton {@link http://www.dibuxo.com}
 * @version     Joomla! 2.5 - 1.0.24
 * @date        Created on 09-09-2013
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

class JFormFieldS2sLegend extends JFormField
{
    protected $type = 's2slegend';

    protected function getLabel(){
        $jversion = new JVersion();
        if($jversion->getShortVersion() >= "3.0"){
            //var_dump($jversion->getShortVersion());
            return '<strong>'.JText::_('SOCIAL2S_FIELD_LEGEND').'</strong>';
        }
    }

    protected function getInput()
    {
        $field_set = $this->form->getFieldset();

        $field_value='<div class="alert alert-info">

            <p><span class="fa fa-2x fa-exclamation-triangle"></span> '.JText::_('SOCIAL2S_FIELD_LEGEND_WARNING').'</p>
            <p><span class="far fa-2x fa fa-chess-queen"></span> '.JText::_('SOCIAL2S_FIELD_LEGEND_PRO').'</p>
            <p><span class="far fa-2x fa-thumbs-up"></span> '.JText::_('SOCIAL2S_FIELD_LEGEND_RECOMMENDED').'</p>

        </div>';
    

        $jversion = new JVersion();
        if($jversion->getShortVersion() >= "3.0"){
            return $field_value;
        }else{
            return '';
        }


    }

}
