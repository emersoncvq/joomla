<?php
/**
 * @Copyright
 * @package     Field - S2s Version
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

class JFormFieldS2sversion extends JFormField
{
    protected $type = 's2sversion';

	public function getLabel() {

		//return 'version';
	}

    protected function getInput()
    {
      return '<div class="s2s_version"><span class="badge badge-success">'.$this->getVersion().'</span></div>';
      //return '<input type="text" name="s2s_db_version" id="s2s_db_version" value="'.$this->getVersion().'"/>';
    }

    private function getVersion() {
      $db = JFactory::getDBO();
      $query = $db->getQuery(true);
      $query
          ->select(array('*'))
          ->from($db->quoteName('#__extensions'))
          ->where($db->quoteName('type').' = '.$db->quote('plugin'))
          ->where($db->quoteName('element').' = '.$db->quote('social2s'))
          ->where($db->quoteName('folder').' = '.$db->quote('content'));
      $db->setQuery($query);
      $result = $db->loadObject();
      $manifest_cache = json_decode($result->manifest_cache);
      if (isset($manifest_cache->version)) {
        return $manifest_cache->version;
      }
      return;
    }




}
