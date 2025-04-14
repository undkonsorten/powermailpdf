<?php

use Undkonsorten\Powermailpdf\ViewHelpers\Misc\VariablesViewHelper;

if (!defined('TYPO3')) {
    die ('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][VariablesViewHelper::class] = [
    'className' => VariablesViewHelper::class
];
