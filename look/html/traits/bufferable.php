<?php

namespace Look\Html\Traits;

/**
 * Позволяет объекту использовать буфер данных
 */
trait Bufferable
{
    /** @var string Буфер обмена */
    protected $insertBuffer;
    
    /**
     * Добавляет указанный фрагмент в конец буфера
     * @param string $fragment -> Фрагмент данных
     * @return void
     */
    public function append(string $fragment) : void
    {
        $this->appendAfter($fragment);
    }
    
    /**
     * Добавляет указанный фрагмент в конец буфера
     * @param string $fragment -> Фрагмент данных
     * @return void
     */
    public function appendAfter(string $fragment) : void
    {
        $this->insertBuffer .= $fragment . "\n";
    }
    
    /**
     * Добавляет указанный фрагмент в начало буфера
     * @param string $fragment -> Фрагмент данных
     * @return void
     */
    public function appendBefore(string $fragment) : void
    {
        $this->insertBuffer = $fragment . "\n" . $this->insertBuffer;
    }
    
    /**
     * Turn on output buffering
     * <p>This function will turn output buffering on. While output buffering is active no output is sent from the script (other than headers), instead the output is stored in an internal buffer.</p><p>The contents of this internal buffer may be copied into a string variable using <code>ob_get_contents()</code>. To output what is stored in the internal buffer, use <code>ob_end_flush()</code>. Alternatively, <code>ob_end_clean()</code> will silently discard the buffer contents.</p><p>Some web servers (e.g. Apache) change the working directory of a script when calling the callback function. You can change it back by e.g. <i>chdir(dirname($_SERVER['SCRIPT_FILENAME']))</i> in the callback function.</p><p>Output buffers are stackable, that is, you may call <b>ob_start()</b> while another <b>ob_start()</b> is active. Just make sure that you call <code>ob_end_flush()</code> the appropriate number of times. If multiple output callback functions are active, output is being filtered sequentially through each of them in nesting order.</p>
     * @param callable $output_callback <p>An optional <code>output_callback</code> function may be specified. This function takes a string as a parameter and should return a string. The function will be called when the output buffer is flushed (sent) or cleaned (with <code>ob_flush()</code>, <code>ob_clean()</code> or similar function) or when the output buffer is flushed to the browser at the end of the request. When <code>output_callback</code> is called, it will receive the contents of the output buffer as its parameter and is expected to return a new output buffer as a result, which will be sent to the browser. If the <code>output_callback</code> is not a callable function, this function will return <b><code>FALSE</code></b>. This is the callback signature:</p> <p></p> string handler ( string <code>$buffer</code> [, int <code>$phase</code> ] )   <code>buffer</code>   Contents of the output buffer.    <code>phase</code>   Bitmask of <b><code>PHP_OUTPUT_HANDLER_&#42;</code></b> constants.    <p>If <code>output_callback</code> returns <b><code>FALSE</code></b> original input is sent to the browser.</p> <p>The <code>output_callback</code> parameter may be bypassed by passing a <b><code>NULL</code></b> value.</p> <p><code>ob_end_clean()</code>, <code>ob_end_flush()</code>, <code>ob_clean()</code>, <code>ob_flush()</code> and <b>ob_start()</b> may not be called from a callback function. If you call them from callback function, the behavior is undefined. If you would like to delete the contents of a buffer, return "" (a null string) from callback function. You can't even call functions using the output buffering functions like <i>print_r($expression, true)</i> or <i>highlight_file($filename, true)</i> from a callback function.</p> <p><b>Note</b>:</p><p><code>ob_gzhandler()</code> function exists to facilitate sending gz-encoded data to web browsers that support compressed web pages. <code>ob_gzhandler()</code> determines what type of content encoding the browser will accept and will return its output accordingly.</p>
     * @param int $chunk_size <p>If the optional parameter <code>chunk_size</code> is passed, the buffer will be flushed after any output call which causes the buffer's length to equal or exceed <code>chunk_size</code>. The default value <i>0</i> means that the output function will only be called when the output buffer is closed.</p> <p>Prior to PHP 5.4.0, the value <i>1</i> was a special case value that set the chunk size to 4096 bytes.</p>
     * @param int $flags <p>The <code>flags</code> parameter is a bitmask that controls the operations that can be performed on the output buffer. The default is to allow output buffers to be cleaned, flushed and removed, which can be set explicitly via <b><code>PHP_OUTPUT_HANDLER_CLEANABLE</code></b> | <b><code>PHP_OUTPUT_HANDLER_FLUSHABLE</code></b> | <b><code>PHP_OUTPUT_HANDLER_REMOVABLE</code></b>, or <b><code>PHP_OUTPUT_HANDLER_STDFLAGS</code></b> as shorthand.</p> <p>Each flag controls access to a set of functions, as described below:</p>   Constant Functions     <b><code>PHP_OUTPUT_HANDLER_CLEANABLE</code></b>  <code>ob_clean()</code>, <code>ob_end_clean()</code>, and <code>ob_get_clean()</code>.    <b><code>PHP_OUTPUT_HANDLER_FLUSHABLE</code></b>  <code>ob_end_flush()</code>, <code>ob_flush()</code>, and <code>ob_get_flush()</code>.    <b><code>PHP_OUTPUT_HANDLER_REMOVABLE</code></b>  <code>ob_end_clean()</code>, <code>ob_end_flush()</code>, and <code>ob_get_flush()</code>.
     * @return bool <p>Returns <b><code>TRUE</code></b> on success or <b><code>FALSE</code></b> on failure.</p>
     * @link http://php.net/manual/en/function.ob-start.php
     * @see ob_get_contents(), ob_end_clean(), ob_end_flush(), ob_implicit_flush(), ob_gzhandler(), ob_iconv_handler(), mb_output_handler(), ob_tidyhandler()
     * @since PHP 4, PHP 5, PHP 7
     */
    public function startInsert(callable $output_callback = null, int $chunk_size = 0, int $flags = PHP_OUTPUT_HANDLER_STDFLAGS) : bool
    {
        return ob_start($output_callback, $chunk_size, $flags);
    }
    
    /**
     * Append and clean (erase) the output buffer and turn off output buffering
     * <p>This function discards the contents of the topmost output buffer and turns off this output buffering. If you want to further process the buffer's contents you have to call <code>ob_get_contents()</code> before <b>ob_end_clean()</b> as the buffer contents are discarded when <b>ob_end_clean()</b> is called.</p><p>The output buffer must be started by <code>ob_start()</code> with PHP_OUTPUT_HANDLER_CLEANABLE and PHP_OUTPUT_HANDLER_REMOVABLE flags. Otherwise <b>ob_end_clean()</b> will not work.</p>
     * @return bool <p>Returns <b><code>TRUE</code></b> on success or <b><code>FALSE</code></b> on failure. Reasons for failure are first that you called the function without an active buffer or that for some reason a buffer could not be deleted (possible for special buffer).</p>
     * @link http://php.net/manual/en/function.ob-end-clean.php
     * @see ob_start(), ob_get_contents(), ob_flush()
     * @since PHP 4, PHP 5, PHP 7
     */
    public function endInsertAndAppendBefore() : bool
    {
        $this->appendBefore(ob_get_contents());
        return ob_end_clean();
    }
    
    /**
     * Append and clean (erase) the output buffer and turn off output buffering
     * <p>This function discards the contents of the topmost output buffer and turns off this output buffering. If you want to further process the buffer's contents you have to call <code>ob_get_contents()</code> before <b>ob_end_clean()</b> as the buffer contents are discarded when <b>ob_end_clean()</b> is called.</p><p>The output buffer must be started by <code>ob_start()</code> with PHP_OUTPUT_HANDLER_CLEANABLE and PHP_OUTPUT_HANDLER_REMOVABLE flags. Otherwise <b>ob_end_clean()</b> will not work.</p>
     * @return bool <p>Returns <b><code>TRUE</code></b> on success or <b><code>FALSE</code></b> on failure. Reasons for failure are first that you called the function without an active buffer or that for some reason a buffer could not be deleted (possible for special buffer).</p>
     * @link http://php.net/manual/en/function.ob-end-clean.php
     * @see ob_start(), ob_get_contents(), ob_flush()
     * @since PHP 4, PHP 5, PHP 7
     */
    public function endInsertAndAppendAfter() : bool
    {
        $this->appendAfter(ob_get_contents());
        return ob_end_clean();
    }
}
