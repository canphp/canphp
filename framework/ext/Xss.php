<?php

/**
 * Xss过滤
 */

namespace framework\ext;

class Xss {

    /**
     * 允许的标签
     * 先剔除不允许的,再过滤允许的
     *
     * 以 a 标签为例:
     *   1. 允许a的所有 ['a'] => ''
     *   2. 不允许a的 style ['a'] => array('disallowed' => array('style'))
     *   3. 只允许a的 href rel ['a'] = array('allowed' => array('href', 'rel'))
     * 
     */
    protected $allowedTags = array();
    
    /**
     * 允许的style属性
     */
    protected $allowedStyleProperties = array();
    
    /**
     * 允许的style域名
     */
    protected $allowedStyleDomain = array();
    
    /**
     * 执行Xss filter
     * @param string $string   字符
     * @param array  $allowedTags  array('a'=>array()) 允许的标签
     * @param array  $allowedStyleProperties  array('font-size','font-weight') 允许的属性
     */
    public function filter($string, $allowedTags = array(), $allowedStyleProperties = array()) {
        //非UTF8编码直接置空
        if (!String::isUTF8($string)) {
            return '';
        }
        //设置tags
        $this->setAllowedTags($allowedTags);
        $this->setAllowedStyleProperties($allowedStyleProperties);
        //去除结尾符
        $string = str_replace(chr(0), '', $string);
        //去除Netscape JS
        $string = preg_replace('%&\s*\{[^}]*(\}\s*;?|$)%', '', $string);
        //转义&
        $string = str_replace('&', '&amp;', $string);
        //反转&
        $string = preg_replace('/&amp;#([0-9]+;)/', '&#\1', $string);
        $string = preg_replace('/&amp;#[Xx]0*((?:[0-9A-Fa-f]{2})+;)/', '&#x\1', $string);
        $string = preg_replace('/&amp;([A-Za-z][A-Za-z0-9]*;)/', '&\1', $string);
        //回调处理
        return preg_replace_callback('%
          (
          <(?=[^a-zA-Z!/])  # a lone <
          |                 # or
          <!--.*?-->        # a comment
          |                 # or
          <[^>]*(>|$)       # a string that starts with a <, up until the > or the end of the string
          |                 # or
          >                 # just a >
          )%x', array($this, 'split'), $string);        
    }
    
    /**
     * 分析标签
     */
    public function split($matches) {
        $string = $matches[1];
        //单个 < >
        if (substr($string, 0, 1) != '<') {
            return '&gt;';
        } elseif (strlen($string) == 1) {
            return '&lt;';
        }
        //匹配分析
        if (!preg_match('%^<\s*(/\s*)?([a-zA-Z0-9]+)([^>]*)>?|(<!--.*?-->)$%', $string, $matches)) {
            return '';
        }
        $slash    = trim($matches[1]);
        $elem     = &$matches[2];
        $attrlist = &$matches[3];
        $comment  = &$matches[4];
        $elem     = strtolower($elem);
        //注释头
        if ($comment) {
            $elem = '!--';
        }
        //不在允许标签范围
        if (!isset($this->allowedTags[$elem])) {
            return '';
        }
        //允许注释并且是注释就直接返回
        if ($comment) {
            return $comment;
        }
        //是闭合标签直接返回
        if ($slash != '') {
            return "</$elem>";
        }
        //自闭合标签
        $attrlist = preg_replace('%(\s?)/\s*$%', '\1', $attrlist, -1, $count);
        $xhtml_slash = $count ? ' /' : '';
        //清理属性
        if (($attr2 = $this->attributes($attrlist, $elem)) === false) {
            return '';
        }
        $attr2 = implode(' ', $attr2);
        $attr2 = preg_replace('/[<>]/', '', $attr2);
        $attr2 = strlen($attr2) ? ' ' . $attr2 : '';

        return "<$elem$attr2$xhtml_slash>";
    }
    
    /**
     * 清理属性
     */
    public function attributes($attributes, $elem = '') {
        $return = array();
        $mode = 0;
        $attrname = '';
        $skip = false;
        while (strlen($attributes) != 0) {
            $working = 0;
            switch ($mode) {
                //属性名
                case 0:
                    if (preg_match('/^([-a-zA-Z]+)/', $attributes, $match)) {
                        $working = 1;
                        $mode = 1;
                        $attrname = strtolower($match[1]);
                        $skip = substr($attrname, 0, 2) == 'on';
                        $attributes = preg_replace('/^[-a-zA-Z]+/', '', $attributes);
                    }
                    break;
                //单个的属性值
                case 1:
                    if (preg_match('/^\s*=\s*/', $attributes)) {
                        $working = 1;
                        $mode = 2;
                        $attributes = preg_replace('/^\s*=\s*/', '', $attributes);
                        break;
                    }
                    if (preg_match('/^\s+/', $attributes)) {
                        $working = 1;
                        $mode = 0;
                        if (!$skip) {
                            $return[$attrname] = array();
                        }
                        $attributes = preg_replace('/^\s+/', '', $attributes);
                    }
                    break;
                //属性值
                case 2:
                    if (preg_match('/^"([^"]*)"(\s+|$)/', $attributes, $match)) {
                        $working = 1;
                        $mode = 0;
                        if (!$skip) {
                            $return[$attrname] = array(
                                'value'     => $match[1],
                                'delimiter' => '"',
                            );
                        }
                        $attributes = preg_replace('/^"[^"]*"(\s+|$)/', '', $attributes);
                        break;
                    }
                    if (preg_match("/^'([^']*)'(\s+|$)/", $attributes, $match)) {
                        $working = 1;
                        $mode = 0;
                        if (!$skip) {
                            $return[$attrname] = array(
                                'value'     => $match[1],
                                'delimiter' => "'",
                            );
                        }
                        $attributes = preg_replace("/^'[^']*'(\s+|$)/", '', $attributes);
                        break;
                    }
                    if (preg_match("%^([^\s\"']+)(\s+|$)%", $attributes, $match)) {
                        $working = 1;
                        $mode = 0;
                        if (!$skip) {
                            $return[$attrname] = array(
                                'value'     => $match[1],
                                'delimiter' => '"',
                            );
                        }
                        $attributes = preg_replace("%^[^\s\"']+(\s+|$)%", '', $attributes);
                    }
                    break;
            }
            //没有匹配到的,直接过滤
            if ($working == 0) {
                $attributes = preg_replace('/
                  ^
                  (
                  "[^"]*("|$)     # - a string that starts with a double quote, up until the next double quote or the end of the string
                  |               # or
                  \'[^\']*(\'|$)| # - a string that starts with a quote, up until the next quote or the end of the string
                  |               # or
                  \S              # - a non-whitespace character
                  )*              # any number of the above three
                  \s*             # any number of whitespaces
                  /x', '', $attributes);
                $mode = 0;
            }
        }
        if ($mode == 1 && !$skip) {
            $return[$attrname] = array();
        }
        //执行属性的清理
        $tag = isset($this->allowedTags[$elem]) ? $this->allowedTags[$elem] : array();
        foreach ($return as $name => $info) {
            if (!isset($info['value'])) {
                continue;
            }
            //去掉不允许的
            if (isset($tag['disallowed']) && in_array($name, $tag['disallowed'])) {
                unset($return[$name]);
                continue;
            }
            //只留允许的
            if (isset($tag['allowed']) && !in_array($name, $tag['allowed'])) {
                unset($return[$name]);
                continue;
            }
            //对style深度清理
            if ($name == 'style') {
                $sanitized_properties = array();
                $properties = array_filter(array_map('trim', explode(';', String::decodeEntities($info['value']))));
                foreach ($properties as $property) {
                    if (!preg_match('#^([a-zA-Z][-a-zA-Z]*)\s*:\s*(.*)$#', $property, $property_matches)) {
                        continue;
                    }
                    $property_name  = strtolower($property_matches[1]);
                    $property_value = &$property_matches[2];
                    if (!isset($this->allowedStyleProperties[$property_name])) {
                        continue;
                    }
                    if (strpos($property_value, 'url(') !== false) {
                        if (!preg_match('`url\(\s*(([\'"]?)(?:[^)]|(?<=\\\\)\\))+[\'"]?)\s*\)`', $property_value, $url) || empty($url[1])) {
                            continue;
                        }
                        if (!empty($url[2])) {
                            if (substr($url[1], -1) != $url[2]) {
                                continue;
                            }
                            $url[1] = substr($url[1], 1, -1);
                        }
                        $url = preg_replace('`\\\\([(),\'"\s])`', '\1', $url[1]);
                        if (String::filterBadProtocol($url) != $url) {
                            continue;
                        }
                        if (!preg_match('`^/[^/]+`', $url)) {
                            $match = false;
                            foreach ($this->allowedStyleDomain as $reg) {
                                if (preg_match($reg, $url)) {
                                    $match = true;
                                    break;
                                }
                            }
                            if (!$match) {
                                continue;
                            }
                        }
                    }
                    $sanitized_properties[] = $property_name . ':' . String::checkPlain($property_value);
                }
                if (empty($sanitized_properties)) {
                    unset($return[$name]);
                    continue;
                }
                $info['value'] = implode('; ', $sanitized_properties);
            }
            else {
                $info['value'] = String::filterBadProtocol($info['value']);
            }
            
            $return[$name] = $name . '=' . $info['delimiter'] . $info['value'] . $info['delimiter'];
        }

        return $return;
    }
    
    //设置允许的标签
    public function setAllowedTags($tags) {
        foreach ($tags as $k => $tag) {
            if (is_int($k) && is_string($tag)) {
                unset($tags[$k]);
                $tags[$tag] = array();
            }
        }
        $this->allowedTags = $tags;
    }
    
    //设置允许的style属性
    public function setAllowedStyleProperties($properties) {
        $this->allowedStyleProperties = array_flip($properties);
    }
    
    //设置允许的style domain
    public function setAllowedStyleDomain($domain) {
        if (is_string($domain)) {
            $this->allowedStyleDomain[] = '`^(https?://|//)' . $domain . '`i';
        } elseif (is_array($domain)) {
            foreach ($domain as $d) {
                $this->allowedStyleDomain[] = '`^(https?://|//)' . $d . '`i';
            }
        }
        return $this;
    }

}
