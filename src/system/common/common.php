<?php
/**
 * 用于调试输出
 * @param mixed $msg:调试信息
 */
function dump($msg, $return = false)
{
    ob_start();
    if (is_array($msg) || is_resource($msg) || is_object($msg)) {
        var_dump($msg);
    } else {
        if (is_null($msg)) {
            $msg = 'NULL';
        } else if(is_bool($msg)) {
            $msg = $msg ? 'TRUE' : 'FALSE';
        }
        echo $msg;
    }
    $output = ob_get_clean();
    $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
    if (!extension_loaded('xdebug')) {
        $output = htmlspecialchars($output, ENT_SUBSTITUTE);
    }
    $output = '<pre>' . $output . '</pre>';
    $output = '<div style="position: relative;padding: 15px 15px 15px 55px;margin-bottom: 20px;font-size: 14px;background-color: #fafafa;border: solid 1px #d8d8d8;border-radius: 3px;">'.$output.'</div>';
    if ($return) {
        return $output;
    } else {
        echo($output);
    }
}

function error_page($code, $message)
{
    if (DEBUG) {
        dump($message);
    } else {

    }
}