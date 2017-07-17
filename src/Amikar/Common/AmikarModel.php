<?php
/**
 * *
 *  * Copyright 2017 Amikar, Inc.
 *  *
 *  * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 *  * use, copy, modify, and distribute this software in source code or binary
 *  * form for use in connection with the web services and APIs provided by
 *  * Facebook.
 *  *
 *  * As with any software that integrates with the Amikar platform, your use
 *  * of this software is subject to the Amikar Developer Principles and
 *  * Policies [http://developers.amikar.com/policy]. This copyright notice
 *  * shall be included in all copies or substantial portions of the software.
 *  *
 *  * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 *  * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 *  * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 *  * DEALINGS IN THE SOFTWARE.
 *  *
 *
 */

namespace Amikar\Common;


class AmikarModel
{
    /**
     * Returns a list of Object from Array or Json String. It is generally used when your json
     * contains an array of this object
     *
     * @param mixed $data Array object or json string representation
     * @return array
     */
    public static function getList($data)
    {

    }

    /**
     * Converts Params to Array
     *
     * @param $param
     * @return array
     */
    private function _convertToArray($param)
    {
        $ret = array();
        foreach ($param as $k => $v) {
            if ($v instanceof AmikarModel) {
                $ret[$k] = $v->toArray();
            } elseif (sizeof($v) <= 0 && is_array($v)) {
                $ret[$k] = array();
            } elseif (is_array($v)) {
                $ret[$k] = $this->_convertToArray($v);
            } else {
                $ret[$k] = $v;
            }
        }
        // If the array is empty, which means an empty object,
        // we need to convert array to StdClass object to properly
        // represent JSON String
        if (sizeof($ret) <= 0) {
            $ret = new AmikarModel();
        }
        return $ret;
    }

    /**
     * Fills object value from Array list
     *
     * @param $arr
     * @return $this
     */
    public function fromArray($arr)
    {
        if (!empty($arr)) {
            // Iterate over each element in array
            foreach ($arr as $k => $v) {
                // If the value is an array, it means, it is an object after conversion
                if (is_array($v)) {
                    // Determine the class of the object
                    if (($clazz = ReflectionUtil::getPropertyClass(get_class($this), $k)) != null) {
                        // If the value is an associative array, it means, its an object. Just make recursive call to it.
                        if (empty($v)) {
                            if (ReflectionUtil::isPropertyClassArray(get_class($this), $k)) {
                                // It means, it is an array of objects.
                                $this->assignValue($k, array());
                                continue;
                            }
                            $o = new $clazz();
                            //$arr = array();
                            $this->assignValue($k, $o);
                        } elseif (ArrayUtil::isAssocArray($v)) {
                            /** @var self $o */
                            $o = new $clazz();
                            $o->fromArray($v);
                            $this->assignValue($k, $o);
                        } else {
                            // Else, value is an array of object/data
                            $arr = array();
                            // Iterate through each element in that array.
                            foreach ($v as $nk => $nv) {
                                if (is_array($nv)) {
                                    $o = new $clazz();
                                    $o->fromArray($nv);
                                    $arr[$nk] = $o;
                                } else {
                                    $arr[$nk] = $nv;
                                }
                            }
                            $this->assignValue($k, $arr);
                        }
                    } else {
                        $this->assignValue($k, $v);
                    }
                } else {
                    $this->assignValue($k, $v);
                }
            }
        }
        return $this;
    }

    private function assignValue($key, $value)
    {
        $setter = 'set'. $this->convertToCamelCase($key);
        // If we find the setter, use that, otherwise use magic method.
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } else {
            $this->__set($key, $value);
        }
    }

    /**
     * Converts the input key into a valid Setter Method Name
     *
     * @param $key
     * @return mixed
     */
    private function convertToCamelCase($key)
    {
        return str_replace(' ', '', ucwords(str_replace(array('_', '-'), ' ', $key)));
    }

}