<?php namespace Bpez\Infuse\Commands;

// php artisan command:make InfuseDeploy --path=vendor/bpez/infuse/src/Bpez/Infuse/Commands --namespace=Bpez\\Infuse\\Commands
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class InfuseDeploy extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'infuse:deploy';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Deploy project with git.';

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
		$server = $this->option('server');
		$action = $this->argument('action');
		
		switch ($action) {
			case 'init':
				break;
			case 'sync-update':
				break;
			default:
				$action = "update";
				break;
		}

		// Choose between default connection or option server 
		$connection = \Config::has("remote.default");
		$connection = ($server)? $server : $connection;

		$commands = \Config::get("infuse_deploy::{$connection}.{$action}");
		
		if (!is_array($commands)) {
			$this->error("Commands must be in an array.");
			return false;
		}

		if (count($commands) > 0){
			$this->info("Infuse deployment running {$action} commands on {$connection} connection..");
			\SSH::into($connection)->run($commands);
			$this->info("Deployment {$action} finished.");
		}	else {
			$this->error("Commands must be in an array.");
			return false;
		}
		
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('action', InputArgument::OPTIONAL, 'Deployment action. (init, update, sync-update) ', false)
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
			array('server', 's', InputOption::VALUE_OPTIONAL, 'Target server to deploy to.', 'production')
		);
	}

}
