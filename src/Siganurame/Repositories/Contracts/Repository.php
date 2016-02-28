<?php

namespace Siganurame\Repositories\Contracts;

interface Repository
{
	/**
	 * Make model by it's child
	 *
    * @return Model
    */
	public function makeModel();

	/**
	 * Get fields that searchable
	 *
	 * @return array
	 */
	public function getFieldSearchable();

	/**
	 * Set fields that searchable
	 *
	 * @param array
	 */
	public function setFieldSearchable($fields);

	/**
	 * Get all data child model
	 *
	 * @param array  $columns
	 *
	 * @return Collection
	 */
	public function all($columns = ['*']);

	/**
	* Find a specific model by id given
	*
	* @param  integer  $id
	* @param  array  $columns
	*
	* @return  Collection
	*/
	public function find($id, $columns = ['*']);

   /**
    * Find specific model by field and value given
    *
    * @param  string  $field
    * @param  string  $value
    * @param  array  $columns
    *
    * @return Collection
    */
	public function where($field, $value, $columns = ['*']);

	/**
	 * Where in clause query
	 *
	 * @param  string  $field
	 * @param  array  $values
	 *
	 * @return Collection
	 */
	public function whereIn($field, array $values);

	/**
	 * Where not in clause query
	 *
	 * @param  string  $field
	 * @param  array  $values
	 *
	 * @return Collection
	 */
	public function whereNotIn($field, array $values);

   	/**
     * Get data with relations
     *
     * @param array|string $relations
     *
     * @return $this
     */
    public function with($relations);

	/**
	 * Count the number of records in model table
	 *
	 * @return integer
	 */
	public function count();

	/**
	 * List of all field of repository
	 *
	 * @param string  $column
	 * @param string  $key
	 *
	 * @return Collection
	 */
	public function lists($column, $key = null);

	/**
	 * Paginate the given query into a simple paginator
	 *
	 * @param  int|null $perPage
	 * @param  array    $columns
	 * @param  string   $pageName
	 *
	 * @return collection
	 */
	public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page');

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
	public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null);

	/**
	 * Make custom paginate based on params parameters
	 *
	 * @param mixed $param
	 * 
	 * @return array
	 */
	public function customPaginate($params);

	/**
	 * Store a new entity in repository
	 *
	 * @param  array  $request
	 *
	 * @return Boolean
	 */
	public function store(array $request);

	/**
	 * Update entity in repoitory by it's model
	 *
	 * @param  array  $request
	 * @param  Illuminate\Database\Eloquent\Model  $model
	 *
	 * @return Boolean
	 */
	public function update(array $request, $model);

	/**
	 * Delete entity in repository by it's model
	 *
	 * @param  Illuminate\Database\Eloquent\Model $model
	 *
	 * @return Boolean
	 */
	public function delete($model);
}
