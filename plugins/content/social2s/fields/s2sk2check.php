<?php
/**
 * @Copyright
 * @package     Field - S2s k2check
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

class JFormFieldS2sk2check extends JFormField
{
    protected $type = 's2sk2check';

	protected function getLabel(){}


    protected function getInput()
	{

		$db = JFactory::getDBO();
        $query = $db->getQuery(true);

		$query
		    ->select($db->quoteName(array('element', 'enabled')))
		    ->from($db->quoteName('#__extensions'))
		    ->where($db->quoteName('element') . ' = '. $db->quote('s2s_k2'));

		$db->setQuery($query);
		$results = $db->loadObjectList();

		if(count($results)>=1){
			//jevents exists
			if($results[0]->enabled == 1){
				//exists and enabled
				$field_value = '<div class="alert alert-success">'.JText::_('S2S_K2_PRESENT_ACTIVE').'</div>';
			}else{
				$field_value = '<div class="alert alert-warning">'.JText::_('S2S_K2_PRESENT_NOT_ACTIVE').'</div>';
			}
		}else{
			$field_value = '<div class="alert alert-error">'.JText::_('S2S_K2_NOTE').'</div>';
		}
		return $field_value;
	}




}
