<?php namespace Bpez\Infuse\Commands;

// php artisan command:make InfusePublish --path=vendor/bpez/infuse/src/Bpez/Infuse/Commands --namespace=Bpez\\Infuse\\Commands
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use File;

class InfusePublish extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'infuse:publish';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Publish files from package to project.';

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
		$folder = $this->option('folder');

		if ($folder == "models") {
			$this->info("Infuse publishing models folder..");
			File::copyDirectory(
	      __DIR__.'/../../../models',
	      app_path().'/models'
      );
      $this->info("Finished coping models to app/mmodels.");
		} else {
			$this->error("Folder option (-f) {$folder} is not supported.");
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
		return array();
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			array('folder', 'f', InputOption::VALUE_OPTIONAL, 'Choose folder to publish.', 'models')
		);
	}

}
