<?php
/**
 * @Copyright
 * @package     Field - S2s first time
 * @author      anton {@link http://www.dibuxo.com}
 * @version     Joomla! 3.x - 4.0.124
 * @date        Created on 09-09-2013
 * @link        Project Site {@link https://jtotal.org}
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

JFormHelper::loadFieldClass('text');

class JFormFieldS2sfirsttime extends JFormFieldText
{
    protected $type = 's2sfirsttime';

	public function getLabel() {

		//return 'version';
	}

    protected function getInput()
    {

      //var_dump($this);

      return '<input type="text" name="'.$this->name.'" id="s2s_firsttime" value="'.$this->value.'"/>';
    }






}
