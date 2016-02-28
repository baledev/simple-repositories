<?php

namespace Siganurame\Repositories\Support;

use Illuminate\Filesystem\Filesystem;

class RepositoryGenerator
{
    /**
     * Filesystem instance
     * 
     * @var Illuminate\FileSystem\Filesystem
     */
    protected $files;

    /**
     * Repository name
     * 
     * @var string
     */
    protected $repository;

    /**
     * Model name
     * 
     * @var string
     */
    protected $model;

    /**
     * Contructor
     * 
     * @param Illuminate\Filesystem\Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Get the repository attribute
     * 
     * @return string
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Set the repository attribute
     * 
     * @param string $repository
     *
     * @return void
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get the model attribute
     * 
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set the model attribute
     * 
     * @param string $model
     *
     * @return void
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * Create the repository.
     *
     * @param $repository
     * @param $model
     *
     * @return mixed
     */
    public function create($repository, $model, $override = false)
    {
        if(class_exists(config('repository.namespace') . '\\' . $repository) and !$override) {
            return false;
        }

        $this->setRepository($repository);

        $this->setModel($model);

        $this->createDirectory();

        return $this->createClass();
    }

    /**
     * Create directory if not exists
     *
     * @return void
     */
    protected function createDirectory()
    {
        foreach ($this->getDirectory() as $key => $directory) {
            if(!$this->files->isDirectory($directory)) {
                $this->files->makeDirectory($directory, 0755, true);
            }
        }
    }

    /**
     * Get the repository directory.
     *
     * @return mixed
     */
    protected function getDirectory()
    {
        return [
            'repository'    => config('repository.path.repository'),
            'contract'      => config('repository.path.contract')
        ];
    }

    /**
     * Get the repository name.
     *
     * @return mixed|string
     */
    protected function getRepositoryName()
    {
        // Get the repository.
        $repository_name = $this->getRepository();

        // Check if the repository ends with 'Repository'.
        if(!strpos($repository_name, 'Repository') !== false)
        {
            // Append 'Repository' if not.
            $repository_name .= 'Repository';
        }

        return $repository_name;
    }

    /**
     * Get the model name.
     *
     * @return string
     */
    protected function getModelName()
    {
        // Set model.
        $model = $this->getModel();

        // Check if the model isset.
        if(isset($model) && !empty($model)) {
            // Set the model name from the model option.
            $model_name = $model;
        }
        else {
            // Set the model name by the stripped repository name.
            $model_name = str_singular($this->stripRepositoryName());
        }

        return ucfirst($model_name);
    }

    /**
     * Get the stripped repository name.
     *
     * @return string
     */
    protected function stripRepositoryName()
    {
        // Lowercase the repository.
        $repository = strtolower($this->getRepository());

        return str_replace("repository", "", $repository);
    }

    /**
     * Get the populate data.
     *
     * @return array
     */
    protected function getPopulateData()
    {
        $repository_namespace = config('repository.namespace');
        $repository_class = $this->getRepositoryName();

        $contract_namespace = config('repository.contract');
        $contract_class = $repository_class;

        $model_path = config('repository.model');
        $model_name = $this->getModelName();
        
        return compact(
            'repository_namespace', 
            'repository_class', 
            'contract_namespace',
            'contract_class',
            'model_path',
            'model_name'
        );
    }

    /**
     * Get the path of repository will be create
     *
     * @return string
     */
    protected function getRepositoryPath()
    {
        $directory = $this->getDirectory()['repository'];
        $name = $this->getRepositoryName();

        return $directory . DIRECTORY_SEPARATOR . $name . '.php';
    }

    /**
     * Get the path of contract repository will be create
     *
     * @return string
     */
    protected function getContractPath()
    {
        $directory = $this->getDirectory()['contract'];
        $name = $this->getRepositoryName();

        return $directory . DIRECTORY_SEPARATOR . $name . '.php';
    }

    /**
     * Get the repository stub.
     *
     * @return string
     */
    protected function getRepositoryStub()
    {
        return $this->files->get($this->getStubPath() . "repository.stub");
    }

    /**
     * Get the contract stub.
     *
     * @return string
     */
    protected function getContractStub()
    {
        return $this->files->get($this->getStubPath() . "contract.stub");
    }

    /**
     * Get the stub path.
     *
     * @return string
     */
    protected function getStubPath()
    {
        $stub_path = __DIR__ . '/../../../resources/stubs/';

        return $stub_path;
    }

    /**
     * Populate the stub.
     *
     * @return mixed
     */
    protected function populateStub($stub)
    {
        $populate_data = $this->getPopulateData();
        
        // Loop through the populate data.
        foreach ($populate_data as $key => $value)
        {
            // Populate the stub.
            $stub = str_replace($key, $value, $stub);
        }

        return $stub;
    }

    /**
     * Create repository file that generated with stub
     *
     * @return string
     */
    protected function createClass()
    {
        $this->files->put($this->getRepositoryPath(), $this->populateStub($this->getRepositoryStub()));
        $this->files->put($this->getContractPath(), $this->populateStub($this->getContractStub()));

        return true;
    }
}