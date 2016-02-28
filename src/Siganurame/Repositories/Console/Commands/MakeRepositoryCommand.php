<?php

namespace Siganurame\Repositories\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Composer;
use Siganurame\Repositories\Support\RepositoryGenerator;

class MakeRepositoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:repository {repository : The name of repository} {--model= : The name of model existed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new repository class';

    /**
     * Repository Generator instance
     * 
     * @var Siganurame\Support\RepositoryGenerator
     */
    protected $generator;

    /**
     * Composer instance
     * 
     * @var Illuminate\Support\Composer
     */
    protected $composer;

    /**
     * Constructor
     * 
     * @param Siganurame\Repositories\Support\RepositoryGenerator  $generator
     * @param Illuminate\Support\Composer  $composer
     */
    public function __construct(RepositoryGenerator $generator, Composer $composer)
    {
        parent::__construct();

        $this->generator = $generator;

        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Get the arguments.
        $argument = $this->argument('repository');

        // Get the options.
        $option = $this->option('model');
        
        // Create repository.
        $this->createRepository($argument, $option);

        // Dump autoload.
        $this->composer->dumpAutoloads();
    }

    /**
     * Create repository
     * 
     * @param string  $repository
     * @param string  $model
     *
     * @return void
     */
    protected function createRepository($repository, $model = null)
    {
        if($this->generator->create($repository, $model)) {
            $this->info("Successfully created the $repository class");
        }

        if($this->confirm("Class $repository existed. Do You wish to override?")) {
            if($this->generator->create($repository, $model, true)) {
                $this->info("Successfully override the $repository class");
            }
        }
    }
}
