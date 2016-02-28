<?php

namespace Siganurame\Repositories\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class RepoDestroy
{
	/**
	 * Model instance
	 *
	 * @var object
	 */
	protected $model;

	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct($model)
	{
		$this->model = $model;
	}

	/**
	 * Get the channels the event should be broadcast on.
	 *
	 * @return array
	 */
	public function broadcastOn()
	{
		return [];
	}
}
