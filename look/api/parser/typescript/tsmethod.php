<?php

namespace Look\API\Parser\TypeScript;

use Look\API\Parser\DocBlock;
use Look\API\Parser\DocBlock\ParamDocBlock;
use Look\API\Parser\TypeScript\TSExporter;
use Look\API\Parser\TypeScript\TSArgumentList;

class TSMethod extends TSExporter
{
    const NoPrefix       = 0;
    const AbstractMethod = 1;
    const StaticMethod   = 2;
    
    const PublicAccess    = 256;
    const ProtectedAccess = 257;
    const PrivateAccess   = 258;
    
    public $prefix;
    public $access;
    public $name;
    
    /** @var TSArgumentList */
    public $arguments;
    
    /**
     * @param string        $name      -> Название
     * @param TSArgument    $arguments -> Аргументы
     * @param int           $access    -> Public | Protected | Private
     * @param int           $prefix    -> None | Abstract | Static
     * @param DocBlock|null $desc      -> Блок описания
     */
    public function __construct(string $name, TSArgumentList $arguments, int $access = TSMethod::PublicAccess, int $prefix = TSMethod::NoPrefix, ?DocBlock $desc = null)
    {
        $this->name      = $name;
        $this->arguments = $arguments;
        $this->prefix    = $prefix;
        $this->access    = $access;
        $this->desc      = $desc;
    }
    
    /** {@inheritdoc} */
    public function buildDesc(string $mainTabStr, string $tabStr = ''): ?string
    {
        if($this->desc)
        {
            $desc = $this->desc->description();
            
            if($desc)
            {
                $tmp  = '';
                // {MAIN_OFFSET}{CLASS_OFFSET} * {LINE}
                foreach(preg_split("/(\r?\n)/", $desc) as $line) {
                    $tmp .= $mainTabStr . $tabStr . " * " . $line . "\n";
                }

                $tmp .= $this->arguments->buildDesc($mainTabStr, $tabStr);
                
                // /**
                //  * {DESC} line 1
                //  * {DESC} line 2
                //  */
                $desc = $mainTabStr . $tabStr . "/**\n" .
                        $tmp .
                        $mainTabStr . $tabStr . " */\n";

                return $desc;
            }
        }
        
        return null;
    }
    
    /** {@inheritdoc} */
    public function buildTS(int $offset, int $tabSize, string $mainTabStr, string $tabStr): string
    {
        $fixName = $this->name;
        $desc    = $this->buildDesc($mainTabStr);
        
        $prefix = null;
        switch($this->prefix) {
            case static::AbstractMethod: $prefix = 'abstract '; break;
            case static::StaticMethod:   $prefix = 'static '; break;
            default: break;
        }
        
        $access = null;
        switch($this->access) {
            case static::ProtectedAccess: $access = 'protected '; break;
            case static::PrivateAccess:   $access = 'private '; break;
            case static::PublicAccess:    $access = 'public '; break;
            default: break;
        }
        
        $arguments = $this->arguments->toTS();
        
        return
        $desc .
        $mainTabStr . "$access$prefix$fixName($arguments) {}\n";
    }
}