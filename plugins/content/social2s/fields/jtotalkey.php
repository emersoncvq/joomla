<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\FormHelper;


/**
 * Form Field class for the Joomla Platform.
 * Provides and input field for e-mail addresses
 *
 * @link   http://www.w3.org/TR/html-markup/input.email.html#input.email
 * @see    JFormRuleEmail
 * @since  1.7.0
 */
class JFormFieldJtotalkey extends JFormFieldText
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  11.1
     */
    protected $type = 'jtotalkey';

    /**
     * Method to get the field input markup for e-mail addresses.
     *
     * @return  string  The field input markup.
     *
     * @since   11.1
     */
    protected function getInput()
    {

        // Translate placeholder text
        $hint = $this->translateHint ? JText::_($this->hint) : $this->hint;

        // Initialize some field attributes.
        $size         = !empty($this->size) ? ' size="' . $this->size . '"' : '';
        $maxLength    = !empty($this->maxLength) ? ' maxlength="' . $this->maxLength . '"' : '';
        $class        = !empty($this->class) ? ' class="inputbox ' . $this->class . '"' : ' class="inputbox"';
        $readonly     = $this->readonly ? ' readonly' : '';
        $disabled     = $this->disabled ? ' disabled' : '';
        $required     = $this->required ? ' required aria-required="true"' : '';
        $hint         = $hint ? ' placeholder="' . $hint . '"' : '';
        $autocomplete = !$this->autocomplete ? ' autocomplete="off"' : ' autocomplete="' . $this->autocomplete . '"';
        $autocomplete = $autocomplete == ' autocomplete="on"' ? '' : $autocomplete;
        $autofocus    = $this->autofocus ? ' autofocus' : '';
        $multiple     = $this->multiple ? ' multiple' : '';
        $spellcheck   = $this->spellcheck ? '' : ' spellcheck="false"';

        // Initialize JavaScript field attributes.
        $onchange = $this->onchange ? ' onchange="' . $this->onchange . '"' : '';

        $jversion = new JVersion();

        if($jversion->getShortVersion() >= "4.0"){

            //J4
            return '
            <div class="form-row align-items-center">

                <div class="col-auto">
                    <label class="sr-only" for="inlineFormInputGroup">Username</label>
                    <div class="input-group mb-2">
                        <input type="text" class="form-control" placeholder="" name="' . $this->name . '"' . $class . ' id="' . $this->id . '" value="'
                            . htmlspecialchars(JStringPunycode::emailToUTF8($this->value), ENT_COMPAT, 'UTF-8') . '"' . $spellcheck . $size . $disabled . $readonly
                            . $onchange . $autocomplete . $multiple . $maxLength . $hint . $required . $autofocus . ' />

                        <div class="input-group-append">
                          <button class="btn" type="button" onclick="check_license()">'.JText::_('JTOTAL_LICENSE_EMAIL_GO').'</button>
                        </div>

                    </div>
                </div>

            </div>';

        }else{
            //J3
            return '<div class="input-append"><input type="text" name="' . $this->name . '"' . $class . ' id="' . $this->id . '" value="'
            . htmlspecialchars(JStringPunycode::emailToUTF8($this->value), ENT_COMPAT, 'UTF-8') . '"' . $spellcheck . $size . $disabled . $readonly
            . $onchange . $autocomplete . $multiple . $maxLength . $hint . $required . $autofocus . ' /><button class="btn" type="button" onclick="check_license()">'.JText::_('JTOTAL_LICENSE_EMAIL_GO').'</button></div><br><small><a class="s2s_have_v3_license" href="#" onclick="check_license(\'v3\')">'.JText::_('JTOTAL_LICENSE_V3_GO').'</a></small>';

        }


        
    }
}
