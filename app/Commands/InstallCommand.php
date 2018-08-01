<?php

namespace Eyewitness\Eye\Commands;

use Exception;
use Eyewitness\Eye\Eye;
use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The eye instance.
     *
     * @var \Eyewitness\Eye\Eye;
     */
    protected $eye;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'eyewitness:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Eyewitness.io - initial package installation and configuration';

    /**
     * Create the Install command.
     *
     * @param  \Eyewitness\Eye\Eye  $eye
     * @return void
     */
    public function __construct(Eye $eye)
    {
        parent::__construct();

        $this->eye = $eye;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->eye->checkConfig() && ! $this->handleUpgrade()) {
            $this->info('Aborted. No changes were made. If you need assistance please contact us anytime at: support@eyewitness.io');
            return;
        }

        $this->displayInitialInstallMessage();

        $this->callSilent('vendor:publish', ['--provider' => 'Eyewitness\Eye\EyeServiceProvider', '--force' => true]);
        $this->call('migrate');
        $this->call('eyewitness:regenerate', ['--force' => true]);

        try {
            $this->eye->scheduler()->install();
        } catch (Exception $e) {
            $this->error('The scheduling installation failed: '.$e->getMessage());
        }

        $this->displayPollingMessage();

        $this->call('eyewitness:poll', ['--force' => true]);

        $this->displayOutcome();
    }

    /**
     * Display the polling message.
     *
     * @return void
     */
    protected function displayPollingMessage()
    {
        $this->info('______________________________________');
        $this->info(' ');
        $this->info('Running initial checks. This includes some calls to external APIs so it might take a few moments...');
        $this->info(' ');
    }

    /**
     * Display the initial installation message.
     *
     * @return void
     */
    protected function displayInitialInstallMessage()
    {
        $this->info('______________________________________');
        $this->info(' ');
        $this->info('Installing and configuring Eyewitness.io package....');
        $this->info(' ');
    }

    /**
     * Display the outcome of the installation.
     *
     * @return void
     */
    protected function displayOutcome()
    {
        $this->info('______________________________________');
        $this->info(' ');
        $this->info('You can now visit: "'.config('app.url').'/'.config('eyewitness.base_uri').'" to see your dashboard (you will need your keys above to login).');
        $this->info(' ');
    }

    /**
     * Display the upgrade steps.
     *
     * @return bool
     */
    protected function handleUpgrade()
    {
        $this->error('______________________________________');
        $this->error('It appears that the Eyewitness package has already been installed. You only need to run the installer once per application.');
        $this->error('If you continue, this command will overwrite your eyewitness config file with the latest version, and you will lose all your current settings.');
        $this->error('You can run "artisan eyewitness:regenerate" if you just want to update to new keys.');
        $this->error('______________________________________');

        return $this->confirm('Do you wish to continue?');
    }
}
