<?php

namespace Look\Page\Util;

use Look\Exceptions\SystemException;

class CssMinimizer
{
    public static function getIntegrity(string $css, bool $isSource = false, string $algo = 'sha512') : string
    {
        if(!$isSource) {
            $css = static::getCssFile($css);
        }
        
        $sum = base64_encode(openssl_digest($css, $algo, true));
        return "$algo-$sum";
    }
    
    public static function setFolderIfNotHas(string $file, string $folder) : string
    {
        $fixfolder = str_replace('/', DIRECTORY_SEPARATOR, $folder);
        $fixfile   = str_replace('/', DIRECTORY_SEPARATOR, $file);
        $clear     = substr($fixfile, 0, strlen($fixfolder));
        
        if($clear == $fixfolder) {
            return $fixfile;
        }
        
        if(substr($folder, -1) == DIRECTORY_SEPARATOR) {
            return $fixfolder . $fixfile;
        }
        return $fixfolder . DIRECTORY_SEPARATOR . $fixfile;
    }
    
    public static function getBaseFoler() : string
    {
        return PUBLIC_DIR;
    }

    public static function getCssFolder() : string
    {
        return static::getBaseFoler() . DIRECTORY_SEPARATOR . 'css';
    }
    
    public static function clearCssCache(string $namesHex = '') : int
    {
        $result   = 0;
        $dirName  = static::getCssFolder();
        $fileList = scandir($dirName);
        $checkVal = 'cache.' . $namesHex;
        $checkLen = strlen($checkVal);
        
        foreach($fileList as $tmpFile) {
            if(substr($tmpFile, 0, $checkLen) == $checkVal) {
                unlink($dirName . DIRECTORY_SEPARATOR . $tmpFile);
                $result++;
            }
        }
        
        return $result;
    }
    
    public static function minimizeCss($css) : string
    {
        $str = preg_replace( '#/\*(?:.(?!/)|[^\*](?=/)|(?<!\*)/)*\*/#s' , ' ' , $css);
        $str = str_replace("\t","", $str);
        $str = str_replace("  ","", $str);
        $str = str_replace(";\r","; ", $str);
        $str = str_replace("; \r","; ", $str);
        $str = str_replace("; \n","; ", $str);
        $str = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $str);
        $str = str_replace("  ","", $str);
        $str = str_replace("  ","", $str);
        $str = str_replace("{ ","{", $str);
        $str = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $str);
        $str = str_replace("{\r","{", $str);
        $str = str_replace("{\n","{", $str);
        $str = str_replace("\n}","}", $str);
        $str = str_replace("\r}","}", $str);
        $str = str_replace(" }","}", $str);
        $str = str_replace("\r ","\r", $str);
        $str = str_replace("\n ","\n", $str);
        $str = str_replace(",\n",", ", $str);
        $str = str_replace(",\r",", ", $str);
        $str = str_replace(": ",":", $str);
        $str = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $str);
        $str = str_replace("\r}","}", $str);
        $str = str_replace("\r{","{", $str);
        $str = str_replace("\g{","{", $str);
        $str = str_replace("\n{","{", $str);
        $str = str_replace("\g}","}", $str);
        $str = str_replace("\n}","}", $str);
        $str = str_replace("\\","", $str);
        
        $str = str_replace("\r","", $str);
        $str = str_replace("\n","", $str);
        return $str;
    }
    
    public static function getCssFile($cssfile) : string
    {
        $fixcssfile = static::setFolderIfNotHas($cssfile, static::getCssFolder());
        if(!file_exists($fixcssfile)) {
            throw new SystemException('css file not exists: ' . $fixcssfile);
        }
        return file_get_contents($fixcssfile);
    }
    
    public static function minimizeCssFile($cssfile) : string
    {
        return static::minimizeCss(static::getCssFile($cssfile));
    }
    
    public static function combineCssFilesToSource(string ...$file) : string
    {
        $css = '';
        $privateFolder = static::getBaseFoler();
        foreach($file as $tmp) {
            $path = str_replace($privateFolder, '', $tmp);
            $css .= PHP_EOL . '/* Start:' . $path . ' */' . PHP_EOL;
            $css .= static::getCssFile($tmp);
            $css .= PHP_EOL . '/* End */' . PHP_EOL;
        }
        return $css;
    }
    
    public static function combineMinimizeCssFilesToSource(string ...$file) : string
    {
        $css = '';
        foreach($file as $tmp) {
            $css .= static::minimizeCssFile($tmp);
        }
        return $css;
    }

    public static function getCombineFileNameHex(string ...$file)
    {
        $names   = '';
        foreach($file as $tmp) { $names .= $tmp . '|'; }
        return md5($names);
    }
    
    public static function saveCombineFile(string $hex, string $sources) : string
    {
        $dirName = static::getCssFolder();
        $outFile = 'cache.' . $hex . '.' . time() . '.css';
        $outFile = $dirName . DIRECTORY_SEPARATOR . $outFile;
        file_put_contents($outFile, $sources, LOCK_EX);
        // build Integrity
        $integrityFile = 'cache.' . $hex . '.' . time() . '.integrity';
        file_put_contents($outFile, 'sha512-' . hash('sha512', $sources), LOCK_EX);
        return $outFile;
    }
    
    public static function cacheFileExists(int $cacheTime, string ...$file) : ?string
    {
        $hex      = static::getCombineFileNameHex();
        $dirName  = static::getCssFolder();
        $fileList = scandir($dirName);
        
        foreach($fileList as $tmpFile) {
            $tmp = explode('.', $tmpFile);
            if(count($tmp) == 4 && $tmp[3] == 'css' && $tmp[1] == $hex) {
                // file exists and cache not expired
                if((int)$tmp[2] + $cacheTime > time()) {
                    return $dirName . DIRECTORY_SEPARATOR . $tmpFile;
                }
            }
        }
        
        return null;
    }
    
    public static function combineCssFiles(int $cacheTime, string ...$file) : string
    {
        $file = static::cacheFileExists($cacheTime, ...$file);
        if($file !== null) {
            return $file;
        }
        
        return static::saveCombineFile(
            static::getCombineFileNameHex(),
            static::combineCssFilesToSource(... $file)
        );
    }
    
    public static function getHrefForFile(string $path)
    {
        return str_replace(DIRECTORY_SEPARATOR, '/', str_replace(static::getBaseFoler(), '', $path));
    }

    public static function combineCssFilesAndGetHref(int $cacheTime, string ...$file) : string
    {
        $combineFile = static::combineCssFiles($cacheTime, ...$file);
        return static::getHrefForFile($combineFile);
    }
    
    public static function combineMinimizeCssFiles(int $cacheTime, string ...$file) : string
    {
        $file = static::cacheFileExists($cacheTime, ...$file);
        if($file !== null) {
            return $file;
        }
        
        return static::saveCombineFile(
            static::getCombineFileNameHex(),
            static::combineMinimizeCssFilesToSource(... $file)
        );
    }
    
    public static function combineMinimizeCssFilesAndGetHref(int $cacheTime, string ...$file) : string
    {
        $combineFile = static::combineMinimizeCssFiles($cacheTime, ...$file);
        return static::getHrefForFile($combineFile);
    }
}