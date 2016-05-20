<?php 

namespace CodeProject\Services;

use CodeProject\Repositories\ProjectRepository;
use CodeProject\Validators\ProjectValidator;




use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Filesystem\Factory as Storage;


class ProjectService
{

	/**
	* @var ProjectRepository
	*/
	protected $repository;


	/**
	* @var ProjectValidator
	*/
	protected $validator;

	public function __construct(ProjectRepository $repository, ProjectValidator $validator , Filesystem $filesystem , Storage $storage)
	{
		$this->repository = $repository;
		$this->validator = $validator;
		$this->filesystem = $filesystem;
		$this->storage = $storage;
	}

	public function create(array $data)
	{

		try {
			$this->validator->with($data)->passesOrFail();
			return $this->repository->create($data);
		} catch(ValidatorException $e) {
			return [
				'error' => true,
				'message' => $e->getMessageBag()
			];
		}


		
	}

	public function update(array $data, $id)
	{

		try {
			$this->validator->with($data)->passesOrFail();
			return $this->repository->update($data, $id);
		} catch(ValidatorException $e) {
			return [
				'error' => true,
				'message' => $e->getMessageBag()
			];
		}
		
		
	}

	public function createFile(array $data)
	{
		$project = $this->repository->skipPresenter()->find($data['project_id']);
		
		$projectFile = $project->files()->create($data);

		$this->storage->put($projectFile->id.".".$data['extension'] , $this->filesystem->get($data['file']));


	}

}
