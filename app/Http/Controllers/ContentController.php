<?php namespace App\Http\Controllers;

use App\Cloud;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Services\DropBoxServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContentController extends Controller {

    protected $dropBoxService;

    public function __construct(DropBoxServices $dropBoxServices)
    {
        $this->dropBoxService = $dropBoxServices;
    }

	/**
	 * Display a listing of the resource.
	 *
     * @param  int  $cloudId
	 * @return Response
	 */
	public function index($cloudId)
	{
        $cloud = Cloud::findOrFail((int)$cloudId);
        if($cloud->type === Cloud::DropBox) {
            return $this->dropBoxService->getContents($cloudId, '/');
        }
        return [$cloudId];
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create($cloudId)
	{
        return [$cloudId];
	}

	/**
	 * Store a newly created resource in storage.
	 *
     * @param  int  $cloudId
	 * @return Response
	 */
	public function store($cloudId)
	{
        return [$cloudId];
	}

	/**
	 * Display the specified resource.
     * @param  int  $cloudId
	 * @param  int  $path
	 * @return Response
	 */
	public function show($cloudId, $path, Request $request)
	{
        $path = str_replace("\\", "/", $path);
        return $this->dropBoxService->getContents($cloudId, $path);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($cloudId, $id)
	{
        return [$cloudId, $id];
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($cloudId, $id)
	{
        return [$cloudId, $id];
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $path
     * @param  int  $cloudId
	 * @return Response
	 */
	public function destroy($cloudId, $path)
	{
        $cloud = Cloud::findOrFail((int)$cloudId);
        if($cloud->type === Cloud::DropBox) {
            $path = str_replace("\\", "/", $path);
            $response = $this->dropBoxService->remove($cloudId, $path);
            return $response;
        }
		return 'I don\'t can remove files from not dropbox';
	}

}