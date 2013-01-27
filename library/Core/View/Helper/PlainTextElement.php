<?php
/**
 * Copyright (c) 2012 by PHP Team of NIX Solutions Ltd
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Core_View_Helper_AElement
 *
 * @category   Core
 * @package    Core_View
 * @subpackage Helper
 * @author Dmitriy Savchenko <savchenko.d.v@nixsolutions.com>
 * @date: 22.06.12
 */
class Core_View_Helper_PlainTextElement extends Zend_View_Helper_FormElement
{
//    /**
//     * @var
//     */
//    public $view;
//
//    /**
//     * @param Zend_View_Interface $view
//     * @return void|Zend_View_Helper_Abstract|Zend_View_Helper_Interface
//     */
//    public function setView(Zend_View_Interface $view)
//    {
//        $this->view = $view;
//    }

    /**
     * Render element
     *
     * @param $name
     * @param null $value
     * @param null $attribs
     * @return null
     */
    public function PlainTextElement($name, $value = null, $attribs = null) {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable
        if (null === $value) {
            $value = $name;
        }

        return $value;
    }
}
