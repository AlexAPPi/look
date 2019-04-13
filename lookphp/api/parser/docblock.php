<?php

namespace LookPhp\API\Parser;

use LookPhp\API\Parser\DocBlock\ShortParam;
use LookPhp\API\Parser\DocBlock\ParamDocBlock;

class DocBlock
{
    // /** TEXT TEXT */
    const shortText = '/\/\*\*[ ]*(.*)\*\//';
    
    // /** @var type Value */
    const shortParam = '/\/\*\*[ ]*\@(\w*)[ ]*(\w*)[ ->]*(.*)/';
    
    // @param type Name -> Desc
    const paramExtract = '/\@(\w*)[ ]*(\w*)[ ]*\$(\w*)[ ->]*(.*)/';
    
    
    
    public $docblock,
           $description = null,
           $params      = [];
    
    /**
     * Parses a docblock;
     */
    function __construct(string $docblock) {
        $this->docblock = $docblock;
        $this->parseBlock();
    }
    
    /**
     * An alias to __call();
     * allows a better DSL
     *
     * @param string $paramName
     * @return mixed
     */
    public function __get($paramName) {
        return $this->$paramName();
    }
    
    /**
     * Checks if the param exists
     * 
     * @param string $paramName
     * @param string $paramSubName
     * @return mixed
     */
    public function __call($paramName, $paramSubName = null) {
        
        if($paramName == "description") {
            return $this->description;
        }
        
        if(isset($this->params[$paramName]))
        {
            $params = $this->params[$paramName];
            return $params;
        }
        
        return null;
    }
    
    /**
     * Parse each line in the docblock
     * and store the params in `$this->params`
     * and the rest in `$this->description`
     */
    private function parseBlock()
    {
        $lines = preg_split("/(\r?\n)/", $this->docblock);
                
        if(count($lines) == 1)
        {
            if(preg_match(static::shortParam, $lines[0], $matches)) {
                
                $paramName = $matches[1];
                $paramType = $matches[2];
                $paramDesc = $matches[3];
                
                $this->params[$paramName] = new ShortParam(
                    $paramName,
                    $paramType,
                    $paramDesc
                );
            }
            
            if(preg_match(static::shortText, $lines[0], $matches)) {
                $this->description = $matches[1];
            }
            
            $this->description = $lines[0];
            return;
        }
                        
        // split at each line
        foreach($lines as $line)
        {
            // if starts with an asterisk
            if(preg_match('/^(?=\s+?\*[^\/])(.+)/', $line, $matches)) {
                
                $info = $matches[1];
                
                // remove wrapping whitespace
                $info = trim($info);
                
                // Fix " * ?" => "*"
                if($info == '*') {
                    
                    // Добавляем слеш и пропускаем
                    if(!empty($this->description)) {
                        $this->description .= "\n";
                    }
                    
                    continue;
                }
                else {
                    // remove leading asterisk
                    $info = preg_replace('/^(\*\s+?)/', '', $info);
                }
                
                // if it doesn't start with an "@" symbol
                // then add to the description
                if($info[0] !== "@") {
                    
                    // Добавляем слеш
                    if(!empty($this->description)) {
                        $this->description .= "\n";
                    }
                    
                    $this->description .= $info;
                }
                else
                {
                    // get the name of the param
                    preg_match('/@(\w+)/', $info, $matches);
                    $paramName = $matches[1];
                    
                    // remove the param from the string
                    $value = str_replace("$paramName ", '', $info);
                    
                    // Описание параметра
                    if(preg_match('/\@(\w*)[ ]*(\w*)[ ]*\$(\w*)[ ->]*(.*)/', $info, $matches)) {
                        $argName = $matches[3];
                        $this->params[$paramName][$argName] = new ParamDocBlock($matches[2], $matches[3], $matches[4]);
                        continue;
                    }
                    
                    // if the param hasn't been added yet, create a key for it
                    if(!isset($this->params[$paramName])) {
                        $this->params[$paramName] = [];
                    }
                    
                    // push the param value into place
                    $this->params[$paramName][] = $value;
                }
            }
        }
    }
}