<?php

namespace Siganurame\Repositories;

use Cache;
use Siganurame\Repositories\Events\RepoStore;
use Siganurame\Repositories\Events\RepoUpdate;
use Siganurame\Repositories\Events\RepoDestroy;
use Illuminate\Container\Container as Application;
use Siganurame\Repositories\Exceptions\RepositoryException;
use Siganurame\Repositories\Contracts\Repository as RepositoryContract;

abstract class Repository implements RepositoryContract
{
	/**
	 * Application instance
	 *
     * @var Application
     */
	protected $app;

    /**
     * Model instance
     *
     * @var Model
     */
	protected $model;

    /**
     * Fields repository that searchable
     *
     * @var array
     */
	protected $fieldSearchable = [];

    /**
     * Constructor
     *
     * @param Application $app
     */
	public function __construct(Application $app)
	{
		$this->app = $app;
		$this->model = $this->makeModel();
	}

	/**
	 * Specify Model class name
	 *
	 * @return Model
	 */
	abstract public function model();

	/**
	 * Make model by it's child
	 *
     * @return Model
     */
	public function makeModel()
	{
		$model = $this->app->make($this->model());

		if(! $model instanceof \Illuminate\Database\Eloquent\Model) {
			throw new RepositoryException("Class {$this->model()} must be instance of Illuminate\\Database\\Eloquent\\Model");
		}

		return $model;
	}

	/**
	 * Get fields that searchable
	 *
	 * @return array
	 */
	public function getFieldSearchable()
	{
		return $this->fieldSearchable;
	}

	/**
	 * Set fields that searchable
	 *
	 * @param array
	 */
	public function setFieldSearchable($fields)
	{
		$this->fieldSearchable = $fields;
	}

	/**
	 * Get all data child model
	 *
	 * @param array  $columns
	 *
	 * @return Collection
	 */
	public function all($columns = ['*'])
	{
		return $this->model->get($columns);
	}

	/**
	* Find a specific model by id given
	*
	* @param  integer  $id
	* @param  array  $columns
	*
	* @return  Collection
	*/
	public function find($id, $columns = ['*'])
	{
		return $this->model->findOrFail($id, $columns);
	}

    /**
     * Find specific model by field and value given
     *
     * @param  string  $field
     * @param  string  $value
     * @param  array  $columns
     *
     * @return Collection
     */
	public function where($field, $value, $columns = ['*'])
	{
		return $this->model->where($field, $value)->firstOrFail($columns);
	}

	/**
	 * Where in clause query
	 *
	 * @param  string  $field
	 * @param  array  $values
	 *
	 * @return Collection
	 */
	public function whereIn($field, array $values)
	{
		return $this->model->whereIn($field, $values)->get();
	}

	/**
	 * Where not in clause query
	 *
	 * @param  string  $field
	 * @param  array  $values
	 *
	 * @return Collection
	 */
	public function whereNotIn($field, array $values)
	{
		return $this->model->whereNotIn($field, $values)->get();
	}

   	/**
     * Get data with relations
     *
     * @param array|string $relations
     *
     * @return $this
     */
    public function with($relations)
    {
        $this->model = $this->model->with($relations);

        return $this;
    }

	/**
	 * Count the number of records in model table
	 *
	 * @return integer
	 */
	public function count()
	{
		$model = $this->model;

		return Cache::remember($this->model, 2000, function() use($model) {
			return $model->count();
		});
	}

	/**
	 * List of all field of repository
	 *
	 * @param string  $column
	 * @param string  $key
	 *
	 * @return Collection
	 */
	public function lists($column, $key = null)
	{
		return $this->model->lists($column, $key);
	}

	/**
	 * Paginate the given query into a simple paginator
	 *
	 * @param  int|null $perPage
	 * @param  array    $columns
	 * @param  string   $pageName
	 *
	 * @return collection
	 */
	public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page')
	{
		$perPage = $perPage ? $perpage : config('repository.pagination.perPage');

		return $this->model->simplePaginate($perPage, $columns, $pageName);
	}

	/**
	 * Paginate the given query
	 *
	 * @param  int|null $perPage
	 * @param  array    $columns
	 * @param  string   $pageName
	 * @param  null     $page
	 *
	 * @return collection
	 */
	public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
	{
		$perPage = $perPage ? $perpage : config('repository.pagination.perPage');

		return $this->model->paginate($perPage, $columns, $pageName, $page);
	}

	/**
	 * Make custom paginate based on params parameters
	 *
	 * @param  array|mixed  $params
	 *
	 * @return array
	 */
	public function customPaginate($params = [])
	{
		$page = isset($params['page']) ? $params['page'] : 1;
		$per_page = isset($params['per_page']) ? $params['per_page'] : config('repository.pagination.perPage');

		$results = $this->buildPaginateQuery($params, $page, $per_page);

		$last_page  = $results['last_page'];
		$total  = $results['total'];
		$data = $results['query']->get()->toArray();

		return compact('page', 'per_page', 'last_page', 'total', 'data');
	}

    /**
     * Builds paginate query with given parameters.
     *
     * @param  array   $params
     * @param  integer $page
     * @param  integer $per_page
     *
     * @return array
     */
    protected function buildPaginateQuery(array $params, $page = 1, $per_page = 10)
    {
		$query = $this->model;

		$query = $this->appendParams($params, $query);

		$table = $this->model->getTable();

		$total = Cache::remember('count_'. $table, 2000, function() use($query) {
			return $query->count();
		});

		$last_page = ceil($total / $per_page);

		$query = $query->skip($per_page * ($page - 1))->take($per_page);

		if(isset($params['order']) and $params['order']) {
			$query = $query->orderBy($params['order']);
		}

		if(config('cache.default') == 'memcached' or config('cache.default') == 'redis') {
			// cache tags tidak support untuk driver file atau database, hanya support memcached dan redis
			$query = Cache::tags([$table, 'pagination'])->remember($table, 2000, function($query) {
			   return $query;
			});
		}

		return compact('total', 'last_page', 'query');
    }

	/**
	 * Restrict query by given params.
	 *
	 * @param  array $params
	 * @param  Builder $query
	 *
	 * @return Builder
	 */
	protected function appendParams(array $params, $query)
	{
		if(isset($params['relations'])) {
			$query = $query->with($params['relations']);
		}

		if(isset($params['query'])) {
			$query = $this->searchField($query, $params['query']);
		}

		return $query;
	}

	/**
	 * Search query string on fields given by it's child
	 *
	 * @param  Builder $query
	 * @param  string $param
	 *
	 * @return Builder
	 */
	protected function searchField($query, $param)
	{
		$fields = $this->getFieldSearchable();
		
		return $query->where(function($query) use ($fields, $param) {
			foreach ($fields as $key => $field) {
				if($key == 0) {
					$query->where($field, 'like', '%'. $param .'%');
				}
				else {
					$query->orWhere($field, 'like', '%'. $param .'%');
				}
			}

			return $query->get();
		});
	}

	/**
	 * Store a new entity in repository
	 *
	 * @param  array  $request
	 *
	 * @return Boolean
	 */
	public function store(array $request)
	{
		if($instance = $this->model->create($request)) {
			event(new RepoStore($instance));

			return $instance;
		}
	}

	/**
	 * Update entity in repoitory by it's model
	 *
	 * @param  array  $request
	 * @param  Illuminate\Database\Eloquent\Model  $model
	 *
	 * @return Boolean
	 */
	public function update(array $request, $model)
	{
		if($model->fill($request)->save()) {
			event(new RepoUpdate($model));

			return true;
		}
	}

	/**
	 * Delete entity in repository by it's model
	 *
	 * @param  Illuminate\Database\Eloquent\Model $model
	 *
	 * @return Boolean
	 */
	public function delete($model)
	{
		if($model->delete()) {
			event(new RepoDestroy($model));

			return true;
		}
	}
}
