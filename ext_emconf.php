<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "powermailpdf".
 *
 * Auto generated 22-06-2017 13:58
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
	'title' => 'Powermail PDF Form',
	'description' => 'Add the possibility to download a pdf with data just entered.',
	'category' => 'fe',
	'author' => 'Eike Starkmann',
	'author_email' => 'starkmann@undkonsorten.com',
	'author_company' => 'undkonsorten Gbr',
	'state' => 'stable',
	'version' => '5.1.0',
    'autoload' =>
        array(
            'psr-4' => array('Undkonsorten\\Powermailpdf\\' => 'Classes')
        ),
	'constraints' =>
	array (
		'depends' =>
		array (
            'typo3' => '12.4.0-13.4.99',
			'powermail' => '12.0.0-13.99.99',
		),
		'conflicts' =>
		array (
		),
		'suggests' =>
		array (
		),
	),
);

