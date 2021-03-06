<?php

namespace GetCandy\Api\Http\Controllers\Categories;

use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Categories\CreateRequest;
use GetCandy\Api\Http\Requests\Categories\ReorderRequest;
use GetCandy\Api\Http\Requests\Categories\UpdateRequest;
use GetCandy\Api\Http\Requests\Categories\DeleteRequest;
use GetCandy\Api\Http\Transformers\Fractal\Categories\CategoryFancytreeTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Categories\CategoryTreeTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Categories\CategoryTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryController extends BaseController
{
    public function index(Request $request)
    {
        if($request->view !== 'tree'){
            $paginator = app('api')->categories()->getPaginatedData($request->per_page,
                $request->current_page,
                $request->ids);
            return $this->respondWithCollection($paginator, new CategoryTransformer);
        }
        $collection = app('api')->categories()->getCategoryTree($request->channel);
        return $this->respondWithItem($collection, new CategoryTreeTransformer);

    }

    public function show($id)
    {
        try {
            $category = app('api')->categories()->getByHashedId($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }
        return $this->respondWithItem($category, new CategoryTransformer);
    }

    public function getNested()
    {
        $categories = app('api')->categories()->getNestedList();
        return $this->respondWithCollection($categories, new CategoryTransformer);
    }

    public function getByParent($parentID = null)
    {
        $categories = app('api')->categories()->getByParentID($parentID);
        return $this->respondWithCollection($categories, new CategoryFancytreeTransformer);
    }

    /**
     * Create new category from basic information
     * @param $request
     * @request String name
     * @request String slug
     * @request String parent_id (Optional)
     * @return array|\Illuminate\Http\Response
     */
    public function store(CreateRequest $request)
    {
        try {
            $response = app('api')->categories()->create($request->all());
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        } catch (HttpException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (InvalidLanguageException $e) {
            return $this->errorUnprocessable($e->getMessage());
        }

        if ($response){
            return $this->respondWithItem($response, new CategoryTransformer);
        }
        return response()->json('Error',500);
    }

    /**
     * Handles the request to reorder the categories
     * @param $request
     * @request String id
     * @request String siblings
     * @request String parent_id (Optional)
     * @return array \Illuminate\Http\Response
     */
    public function reorder(ReorderRequest $request)
    {
        try {
            $response = app('api')->categories()->reorder($request->all());
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        } catch (HttpException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (InvalidLanguageException $e) {
            return $this->errorUnprocessable($e->getMessage());
        }
        if ($response){
            return response()->json(['status' => 'success'],200);
        }
        return response()->json(['status' => 'error'],500);
    }


    /**
     * Handles the request to update  a channel
     * @param  String        $id
     * @param  UpdateRequest $request
     * @return Json
     */
    public function update($id, UpdateRequest $request)
    {
        try {
            $result = app('api')->categories()->update($id, $request->all());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }
        return $this->respondWithItem($result, new CategoryTransformer);
    }

    /**
     * Handles the request to delete a category
     * @param  String        $id
     * @param  DeleteRequest $request
     * @return Json
     */
    public function destroy($id, Request $request)
    {
        try {
            $result = app('api')->categories()->delete($id);
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }
        return $this->respondWithNoContent();
    }
}
