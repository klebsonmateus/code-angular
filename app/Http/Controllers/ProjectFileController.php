<?php

namespace CodeProject\Http\Controllers;

use Illuminate\Http\Request;
use CodeProject\Repositories\ProjectRepository;
use CodeProject\Services\ProjectService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;


class ProjectFileController extends Controller
{
    /**
     * @var ProjectRepository
     */
    private $repository;

    /**
     * @var ProjectService
     */
    private $service;

    /**
     * @param ProjectRepository $repository
     * @param ProjectService $service
     */
    public function __construct(ProjectRepository $repository, ProjectService $service)
    {
        $this->repository = $repository;
        $this->service = $service;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        return $this->repository->with(['owner', 'client'])->findWhere(['owner_id' => \Authorizer::getResourceOwnerId()]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $file = $request->file('file');

        $extension = $file->getClientOriginalExtension();

        $data['file'] =$file;
        $data['extension'] = $extension;
        $data['name'] = $request->name;

        $this->service->createFile($data);

        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if($this->checkProjectPermissions($id)==false) {
            return ['error' => 'Access Forbidden'];
        }  

        return $this->repository->with(['owner', 'client'])->find($id);
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        if($this->checkProjectOwner($id)==false) {
            return ['error' => 'Access Forbidden'];
        } 

        return $this->service->update($request->all(), $id);
               
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if($this->checkProjectPermissions($id)==false) {
            return ['error' => 'Access Forbidden'];
        } 

        $registro = $this->repository->find($id);
        $this->repository->delete($id);
        return "O projeto ". $registro->name . " foi deletado com sucesso";

    }

    private function checkProjectOwner($projectId) 
    {
        $userId = \Authorizer::getResourceOwnerId(); 
        

        return $this->repository->isOwner($projectId, $userId );
    }

    private function checkProjectMember($projectId) 
    {
        $userId = \Authorizer::getResourceOwnerId(); 
        

        return $this->repository->hasMember($projectId, $userId );
    }

    private function checkProjectPermissions($projectId) 
    {
        if ($this->checkProjectOwner($projectId) or $this->checkProjectMember($projectId)) {
           return true; 
        }

       return false; 
    }
}
