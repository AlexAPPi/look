<?php

namespace Look\Html;

use Look\Html\Page;

/**
 * Базовый класс страницы
 */
class PageIEFiendly extends Page
{
    /** {@inheritdoc} */
    protected function buildHTML(int $offset, int $tabSize, string $mainTabStr, string $tabStr) : ?string
    {
        $class = null;
        if($this->hasAttribute('class')) {
            $class = ' ' . $this->getAttribute('class');
            $this->unsetAttribute('class');
        }
        
        $attrs = $this->attributesToHTML();
        
        $html = "$mainTabStr<!DOCTYPE html>\n"
              . "$mainTabStr<!--[if IE 6]><html class=\"ie ie6 no-js{$class}\"{$attrs}><![endif]-->\n"
              . "$mainTabStr<!--[if IE 7]><html class=\"ie ie7 no-js{$class}\"{$attrs}><![endif]-->\n"
              . "$mainTabStr<!--[if IE 8]><html class=\"ie ie8 no-js{$class}\"{$attrs}><![endif]-->\n"
              . "$mainTabStr<!--[if IE 9]><html class=\"ie ie9 no-js{$class}\"{$attrs}><![endif]-->\n"
              . "$mainTabStr<!--[if !IE]><!-->\n"
              . "$mainTabStr<html class=\"not-ie no-js{$class}\"{$attrs}><!--<![endif]-->\n"
              . $this->head->__toHTML($offset + 1, $tabSize) . "\n"
              . $this->body->__toHTML($offset + 1, $tabSize) . "\n"
              . "$mainTabStr</html>";
        
        // convert utf-8 to page encoding
        $encoding = $this->getEncoding();
        if($encoding != Encoding::UTF8) {
            return iconv(Encoding::UTF8, $encoding, $html);
        }
        
        return $html;
    }
}
