<?php

namespace App\Http\Controllers\v1;

use App\Exceptions\CodecException;
use App\Project;
use App\Repository\Helper\Response;
use App\Repository\Services\CodecService;
use App\Repository\Services\CoreService;
use App\Thing;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CodecController extends Controller
{
    protected $codecService;
    protected $coreService;

    public function __construct(CodecService $codecService,
                                CoreService $coreService)
    {
        $this->codecService = $codecService;
        $this->coreService = $coreService;
    }

    /**
     * @param Project $project
     * @param Request $request
     * @return array
     * @throws CodecException
     */
    public function create(Project $project, Request $request)
    {
        $user = Auth::user();
        if ($project->owner['id'] != $user->id)
            abort(404);
        $this->codecService->validateCreateCodec($request);
        $codec = $this->codecService->insertCodec($request, $project);
        return Response::body(compact('codec'));
    }


    /**
     * @param Project $project
     * @param Thing $thing
     * @param Request $request
     * @return array
     * @throws \App\Exceptions\GeneralException
     */
    public function send(Project $project, Thing $thing, Request $request)
    {
        $codec = $request->get('codec');
        $this->coreService->sendCodec($project, $thing, $codec);
        $thing->codec = $codec;
        $thing->save();
        return Response::body(['success'=>'200']);
    }

    /**
     * @param Project $project
     * @param Thing $thing
     * @param Request $request
     * @return array
     */
    public function get(Project $project, Thing $thing, Request $request)
    {
        $user = Auth::user();
        if ($project->owner['id'] != $user->id)
            abort(404);
        $codec = $thing->codec;
        return Response::body(compact('codec'));
    }


    /**
     * @param Project $project
     * @return array
     */
    public function list(Project $project)
    {
        $user = Auth::user();
        if ($project->owner['id'] != $user->id)
            abort(404);
        $codecs = $project->codecs()->get();
        return Response::body(compact('codecs'));
    }

}
