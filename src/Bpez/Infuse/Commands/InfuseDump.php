<?php namespace Bpez\Infuse\Commands;

//php artisan command:make InfuseDump --path=vendor/bpez/infuse/src/Bpez/Infuse/Commands --namespace=Bpez\\Infuse\\Commands
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Clouddueling\Mysqldump\Mysqldump;

class InfuseDump extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'infuse:dump';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Dumps the mysql database into the root of the site.';

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
	 * @return mixed
	 */
	public function fire()
	{
		$this->info("Infuse mysql dump started...");
		$dumpSettings = array(
		  'include-tables' => array(),
		  'exclude-tables' => array(),
		  'compress' => 'GZIP',
		  'no-data' => false,
		  'add-drop-database' => false,
		  'add-drop-table' => false,
		  'single-transaction' => true,
		  'lock-tables' => false,
		  'add-locks' => true,
		  'extended-insert' => true,
		  'disable-foreign-keys-check' => false
		);

		$config = \Config::get("database.connections.mysql");
		$dump = new Mysqldump($config['database'], $config['username'], $config['password'], $config['host'], 'mysql', $dumpSettings);
    $dump->start(base_path().$config['database'].'_dump.sql');
    $this->info("Infuse mysql dump finished");
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
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
		);
	}

}
