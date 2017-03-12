<?php
/**
 * Created by IntelliJ IDEA.
 * User: momo
 * Date: 2017/2/20
 * Time: 上午1:37
 */

if (is_file($_SERVER['DOCUMENT_ROOT'] . $_SERVER['REQUEST_URI'])) {
    return false;
} else {
    require(__DIR__ . '/index.php');
}