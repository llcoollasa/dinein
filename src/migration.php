<?php

/*
 * This file is part of the CRUD Admin Generator project.
 *
 * Author: Jon Segador <jonseg@gmail.com>
 * Web: http://crud-admin-generator.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\DBAL\Schema\Table;

$console = new Application('Data Migration', '1.0');

$console
	->register('migrate:migrate')
	->setDefinition(array())
	->setDescription("migrate migrate")
	->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {

		$getTablesQuery = "SHOW TABLES";
		$getTablesResult = $app['db']->fetchAll($getTablesQuery, array());

		$_dbTables = array();
		$dbTables = array();

		foreach($getTablesResult as $getTableResult){

			$_dbTables[] = reset($getTableResult);

			$dbTables[] = array(
				"name" => reset($getTableResult),
				"columns" => array()
			);
		}

		var_dump($_dbTables);


});

return $console;
