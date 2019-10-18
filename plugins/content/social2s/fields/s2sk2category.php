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

if (file_exists(JPATH_ADMINISTRATOR.'/components/com_k2/elements/base.php')) {
	require_once (JPATH_ADMINISTRATOR.'/components/com_k2/elements/base.php');
}else{
	//TODO mejorar el return con info
	return false;
}

class JFormFieldS2sk2category extends K2Element
{
    protected $type = 's2sk2category';



   function fetchElement($name, $value, &$node, $control_name)
    {
        $db = JFactory::getDBO();

        $query = 'SELECT m.* FROM #__k2_categories m WHERE trash = 0 ORDER BY parent, ordering';
        $db->setQuery($query);
        $mitems = $db->loadObjectList();
        $children = array();
        if ($mitems)
        {
            foreach ($mitems as $v)
            {
                if (K2_JVERSION != '15')
                {
                    $v->title = $v->name;
                    $v->parent_id = $v->parent;
                }
                $pt = $v->parent;
                $list = @$children[$pt] ? $children[$pt] : array();
                array_push($list, $v);
                $children[$pt] = $list;
            }
        }
        $list = JHTML::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);
        $mitems = array();
        $mitems[] = JHTML::_('select.option', '0', JText::_('K2_NONE_ONSELECTLISTS'));

        foreach ($list as $item)
        {
            $item->treename = JString::str_ireplace('&#160;', ' -', $item->treename);
            $mitems[] = JHTML::_('select.option', $item->id, $item->treename);
        }

        $attributes = 'class="inputbox"';
        if (K2_JVERSION != '15')
        {
            $attribute = K2_JVERSION == '25' ? $node->getAttribute('multiple') : $node->attributes()->multiple;
            if ($attribute)
            {
                $attributes .= ' multiple="multiple" size="10"';
            }
        }
        else
        {
            if ($node->attributes('multiple'))
            {
                $attributes .= ' multiple="multiple" size="10"';
            }
        }

        if (K2_JVERSION != '15')
        {
            $fieldName = $name;
        }
        else
        {
            $fieldName = $control_name.'['.$name.']';
            if ($node->attributes('multiple'))
            {
                $fieldName .= '[]';
            }
        }

        return JHTML::_('select.genericlist', $mitems, 'jform[params][s2s_k2_categories][]', $attributes, 'value', 'text', $value);
    }

}
/*
class JFormFieldCategories extends K2ElementCategories
{
    var $type = 'categories';
}

class JElementCategories extends K2ElementCategories
{
    var $_name = 'categories';
}
*/
