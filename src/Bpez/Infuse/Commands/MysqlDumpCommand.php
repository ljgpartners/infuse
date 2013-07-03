<?php namespace Bpez\Infuse\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

// php artisan infuse:mysqldump

class MysqlDumpCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'infuse:mysqldump';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = "This is a php version of linux's mysqldump in terminal";

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire() // https://packagist.org/packages/dg/mysql-dump http://bundles.laravel.com/bundle/mysqldump-php
	{ // http://net.tutsplus.com/tutorials/php/your-one-stop-guide-to-laravel-commands/
		$this->info("Mysql backup starting...");
		set_time_limit(0);
		ignore_user_abort(TRUE);

		$time = -microtime(TRUE);

		$conn = Config::get('database.connections.mysql');
		$dump = new MySQLDump(new mysqli($conn['host'], $conn['username'], $conn['password'], $conn['database']));
		$dump->save($conn['database'].'_'. date('Y-m-d H-i') . '.sql');

		$time += microtime(TRUE);

		$this->info("Mysql backup complete. FINISHED (in $time s)");

	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('example', InputArgument::REQUIRED), // 'example', InputArgument::REQUIRED, 'An example argument.'
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array(), // 'example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null
		);
	}

}