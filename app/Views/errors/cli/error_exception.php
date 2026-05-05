<?php
echo "\nERROR:\n";
echo ($title ?? 'Exception') . "\n";
echo ($message ?? '') . "\n";
if (isset($exception)) {
    echo $exception->getFile() . ':' . $exception->getLine() . "\n";
}
