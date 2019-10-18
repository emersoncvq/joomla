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
	return;
}

class JFormFieldS2sk2article extends K2Element
{
    protected $type = 's2sk2article';



    function fetchElement($name, $value, &$node, $control_name)
    {

        $params = JComponentHelper::getParams('com_k2');
        $document = JFactory::getDocument();
        if (version_compare(JVERSION, '1.6.0', 'ge'))
        {
            JHtml::_('behavior.framework');
        }
        else
        {
            JHTML::_('behavior.mootools');
        }
        //K2HelperHTML::loadjQuery();
        $mainframe = JFactory::getApplication();
        if (K2_JVERSION != '15')
        {
            $fieldName = $name;
            $attribute = K2_JVERSION == '25' ? $node->getAttribute('multiple') : $node->attributes()->multiple;
            if (!$attribute)
            {
                $fieldName .= '[]';
            }
            $image = JURI::root(true).'/administrator/templates/'.$mainframe->getTemplate().'/images/admin/publish_x.png';
        }
        else
        {
            $fieldName = $control_name.'['.$name.'][]';
            $image = JURI::root(true).'/administrator/images/publish_x.png';
        }

        $js = "
        function jSelectItem(id, title, object) {
            var exists = false;
            \$K2('#itemsList input').each(function(){
                    if(\$K2(this).val()==id){
                        alert('".JText::_('K2_THE_SELECTED_ITEM_IS_ALREADY_IN_THE_LIST')."');
                        exists = true;
                    }
            });
            if(!exists){
                var container = \$K2('<li/>').appendTo(\$K2('#itemsList'));
                var img = \$K2('<img/>',{'class':'remove', src:'".$image."'}).appendTo(container);
                img.click(function(){\$K2(this).parent().remove();});
                var span = \$K2('<span/>',{'class':'handle'}).html(title).appendTo(container);
                var input = \$K2('<input/>',{value:id, type:'hidden', name:'".$fieldName."'}).appendTo(container);
                var div = \$K2('<div/>',{style:'clear:both;'}).appendTo(container);
                \$K2('#itemsList').sortable('refresh');
                alert('".JText::_('K2_ITEM_ADDED_IN_THE_LIST', true)."');
            }
        }
        
        \$K2(document).ready(function(){
            \$K2('#itemsList').sortable({
                containment: '#itemsList',
                items: 'li',
                handle: 'span.handle'
            });
            \$K2('#itemsList .remove').click(function(){
                \$K2(this).parent().remove();
            });
        });
        ";

        $document->addScriptDeclaration($js);
        $document->addStyleSheet(JURI::root(true).'/media/k2/assets/css/k2.modules.css?v=2.7.1');

        $current = array();
        if (is_string($value) && !empty($value))
        {
            $current[] = $value;
        }
        if (is_array($value))
        {
            $current = $value;
        }

        JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'tables');
        $output = '<div style="clear:both"></div><ul id="itemsList">';
        foreach ($current as $id)
        {
            $row = JTable::getInstance('K2Item', 'Table');
            $row->load($id);
            $output .= '
            <li>
                <img class="remove" src="'.$image.'" alt="'.JText::_('K2_REMOVE_ENTRY_FROM_LIST').'" />
                <span class="handle">'.$row->title.'</span>
                <input type="hidden" value="'.$row->id.'" name="'.$fieldName.'" />
                <span style="clear:both;"></span>
            </li>
            ';
        }
        $output .= '</ul>';

        return $output;
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
