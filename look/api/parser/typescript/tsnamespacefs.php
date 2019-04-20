<?php

namespace Look\API\Parser\TypeScript;

use Look\API\Parser\Exceptions\ParserException;

/**
 * Создает архитектуру namespace в виде папок и файлов
 */
class TSNamespaceFS extends TSNamespace
{
    const Type = 'ts';    
    
    protected function getRootDir(string $path1, string $path2) : ?string
    {
        $fixPath1 = str_replace(['\\', '/', '//'], DIRECTORY_SEPARATOR, $path1);
        $fixPath2 = str_replace(['\\', '/', '//'], DIRECTORY_SEPARATOR, $path2);
                
        $tmp1 = explode(DIRECTORY_SEPARATOR, $fixPath1);
        $tmp2 = explode(DIRECTORY_SEPARATOR, $fixPath2);
        
        if($tmp1[0] != $tmp2[0]) {
            return null;
        }
        
        $count1 = count($tmp1);
        $count2 = count($tmp2);
        
        if($count1 > $count2) {
            $buf  = $tmp1;
            $tmp1 = $tmp2;
            $tmp2 = $buf;
        }
        
        $index = -1;
        foreach($tmp1 as $item) {
            
            $index++;
            
            if($tmp2[$index] == $item) {
                continue;
            }
            break;
        }
        
        return implode(DIRECTORY_SEPARATOR, array_slice($tmp1, 0, $index));
    }
    
    protected function buildImportPath(string $absoluteThisFile, string $absoluteImportFile)
    {        
        $fixAbsoluteThisFile   = str_replace(['\\', '/', '//'], DIRECTORY_SEPARATOR, $absoluteThisFile);
        $fixAbsoluteImportFile = str_replace(['\\', '/', '//'], DIRECTORY_SEPARATOR, $absoluteImportFile);
                
        $importName = basename($fixAbsoluteImportFile);
        $importDir  = dirname($fixAbsoluteImportFile);
        $thisDir    = dirname($fixAbsoluteThisFile);

        // файл находится в той же папке, что и наш файл
        if($importDir == $thisDir) {
            return './' . $importName;
        }
        
        $root = $this->getRootDir($thisDir, $importDir);
        
        if($root == null) {
            throw new ParserException('path root error');
        }
                
        $relativeThisFile   = str_replace($root, '', $thisDir);
        $relativeImportFile = str_replace($root, '', $importDir);
        
        $offsets   = explode(DIRECTORY_SEPARATOR, $relativeThisFile);
        $prefix    = explode(DIRECTORY_SEPARATOR, $relativeImportFile);
        
        array_shift($offsets);
        array_shift($prefix);
        
        $offsetStr = '';
        foreach($offsets as $offset) {
            $offsetStr .= '../';
        }
        
        $prefix = implode('/', $prefix);
        if(!empty($prefix)) {
            $prefix .= '/';
        }
        
        return $offsetStr . $prefix . $importName;
    }
    
    protected function buildImport(string $baseDir, string $absoluteThisFile, array $import) : ?string
    {        
        $result = null;
        
        if(count($import) > 0) {
            
            $result  = '';
            foreach($import as $item) {
                $name = basename($item);
                $absoluteImportFile = $baseDir . DIRECTORY_SEPARATOR . $item;
                $importPath = $this->buildImportPath($absoluteThisFile, $absoluteImportFile);
                $result .= "import {{$name}} from '$importPath';\n";
            }
            
            if(!empty($result)) {
                $result .= "\n";
            }
        }
        
        return $result;
    }

    /** {@inheritdoc} */
    protected function buildTS(int $offset, int $tabSize, string $mainTabStr, string $tabStr) : string
    {
        $baseDir       = PUBLIC_DIR . DIRECTORY_SEPARATOR . self::Type;
        $namespacePath = $baseDir;
        $folders       = explode('\\', $this->name);
        foreach($folders as $folder) {
            $namespacePath .= DIRECTORY_SEPARATOR . $folder;
            if(!file_exists($namespacePath) && !mkdir($namespacePath)) {
                throw new ParserException("не удалось создать путь: $namespacePath");
            }
        }
        
        $enumsCount = count($this->enums);
        if($enumsCount > 0) {
            foreach($this->enums as $enum) {
                
                $name = $enum->name;
                $file = $namespacePath . DIRECTORY_SEPARATOR . $name . '.' . static::Type;
                
                $code  = $this->buildImport($baseDir, $file, $enum->getImportList());
                $code .= $enum->toTS(0, $tabSize);
                
                file_put_contents($file, $code);
            }
        }
        
        $interfacesCount = count($this->interfaces);
        if($interfacesCount > 0) {
            foreach($this->interfaces as $interface) {
                $name = $interface->name;
                $file = $namespacePath . DIRECTORY_SEPARATOR . $name . '.' . static::Type;
                
                $code  = $this->buildImport($baseDir, $file, $interface->getImportList());
                $code .= $interface->toTS(0, $tabSize);
                
                file_put_contents($file, $code);
            }
        }
        
        $classesCount = count($this->classes);
        if($classesCount > 0) {
            foreach($this->classes as $class) {
                $name = $class->name;
                $file = $namespacePath . DIRECTORY_SEPARATOR . $name . '.' . static::Type;
                
                $code  = $this->buildImport($baseDir, $file, $class->getImportList());
                $code .= $class->toTS(0, $tabSize);
                
                file_put_contents($file, $code);
            }
        }
        
        return "";
    }
}