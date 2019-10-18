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

class JFormFieldS2spubli extends JFormField
{
    protected $type = 's2spubli';

    protected function getLabel(){
        return '<strong>'.JText::_('SOCIAL2S_PUBLI').'</strong>';
    }

    protected function getInput()
    {
        $field_set = $this->form->getFieldset();

        $field_value='


<div class="row-fluid">
            <ul class="thumbnails">
              <li class="span3">
                <div class="thumbnail">
                  <img alt="joomgram" src="../media/plg_social2s//assets/publi/photofeed_250.png">
                  <div class="caption">
                    <h3>Photofeed</h3>
                    <p>Photofeed (before known as JoomGram) is a Joomla! module to display your Instagram feed into your Joomla! website.</p>
                    <p>It uses bootstrap to support multi devices and load fast!</p>
                    <p><a class="label label-info pull-right">free and pro</a></p>
                    <p><a href="https://dibuxo.com/en/joomlacms/photofeed/photofeed" target="_blank" class="btn btn-success"><i class="fa fa-plus-circle"></i> info</a></p>
                  </div>
                </div>
              </li>
              <li class="span3">
                <div class="thumbnail">
                  <img alt="fbcoupon" src="../media/plg_social2s//assets/publi/fbcoupon.png">
                  <div class="caption">
                    <h3>Facebook Coupon</h3>
                    <p>Your users get a discount coupon or a download link after they follow you in your facebook page.</p>
                    <p>Increase your audicence with fbcoupon!</p>
                    <p><a class="label label-info pull-right">free and pro</a></p>
                    <p><a href="https://dibuxo.com/en/joomlacms/facebook-coupon/facebook-coupon-for-joomla" target="_blank" class="btn btn-success"><i class="fa fa-plus-circle"></i> info</a></p>
                  </div>
                </div>
              </li>
              <li class="span3">
                <div class="thumbnail">
                  <img alt="imgtopicture"  src="../media/plg_social2s//assets/publi/imgtopicture_big.png">
                    <div class="caption">
                    <h3>imageTOpicture</h3>
                    <p>It is a Joomla plugin to convert your images to the new amazing HTML5 super smart element called <i>picture</i>.  So, you can save bandwidth and get a faster web :)</p>
                    <p><a class="label label-info pull-right">free and pro</a></p>
                    <p><a href="https://dibuxo.com/en/joomlacms/imgtopicture/itp" target="_blank" class="btn btn-success"><i class="fa fa-plus-circle"></i> info</a></p>
                  </div>
                </div>
              </li>
              <li class="span3">
                <div class="thumbnail">
                  <img alt="dx fontawesome" src="../media/plg_social2s//assets/publi/dxfa_banner.png">
                    <div class="caption">
                    <h3>dx fontawesome</h3>
                    <p>It is a package that allows you to insert FontAwesome Icons into your Joomla! articles</p>
                    <p>Its easy and powerful!</p>                    <p><a class="label label-info pull-right">free</a></p>
                    <p><a href="https://dibuxo.com/en/joomlacms/dx-fa-button/dx-fa-button-2" target="_blank" class="btn btn-success"><i class="fa fa-plus-circle"></i> info</a>

                  </div>
                </div>
              </li>
            </ul>
          </div>

  <div class="clearfix"></div>

        ';
    

        //$field_set = $this->form->getFieldset();
        //$field_value = "";

        return $field_value;
    }

}
