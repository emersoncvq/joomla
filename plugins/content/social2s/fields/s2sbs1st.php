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

class JFormFieldS2sbs1st extends JFormField
{
    protected $type = 's2sbs1st';

	public function getLabel() {
		// code that returns HTML that will be shown as the label
		$initial = strtolower($this->element['initial']);
		$initial = '';
		return $initial;
	}

    protected function getInput()
    {

        $id = strtolower($this->element['id']);
        $mode = strtolower($this->element['mode']);

        $initial = strtolower($this->element['initial']);
        $label = strtolower($this->element['label']);
        $slide = strtolower($this->element['slide']);
        $acordeon = strtolower($this->element['acordeon']);

        $active = strtolower($this->element['active']);

        $jversion = new JVersion();


        //////////////
        //V4.X
        if(version_compare($jversion->getShortVersion(), '4.0', 'ge')){

            if($initial=='true'){
                return JHtml::_('bootstrap.startAccordion', $acordeon, array('active' => $active));

            }elseif($initial=='slide'){
                $slider = JHtml::_('bootstrap.addSlide', $acordeon, JText::_($label), $slide);
                return $slider;
                
            }elseif($initial=='end'){
                $end = JHtml::_('bootstrap.endSlide');
                return $end;

            }elseif($initial=='superend'){

                $superend = JHtml::_('bootstrap.endAccordion');
                return $superend;

            }

        ////
        //V3.X
        }elseif(version_compare($jversion->getShortVersion(), '3.0', 'ge')){

            if($initial=='true'){
                return JHtml::_('bootstrap.startAccordion', $acordeon, array('active' => $active));

            }elseif($initial=='slide'){
                $slider = JHtml::_('bootstrap.addSlide', $acordeon, JText::_($label), $slide);
                return $slider;
                
            }elseif($initial=='end'){
                $end = JHtml::_('bootstrap.endSlide');
                return $end;

            }elseif($initial=='superend'){

                $superend = JHtml::_('bootstrap.endAccordion');
                return $superend;

            }
        }


    }




}
