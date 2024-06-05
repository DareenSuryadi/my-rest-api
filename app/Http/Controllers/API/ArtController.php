<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Models\Art;
use OpenApi\Annotations as OA;


/**
 * Class ArtController,
 * 
 * @author Dareen <dareen.422023008@civitas.ukrida.ac.id>
 */

class ArtController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/art",
     *     tags={"Art"},
     *     summary="Display a listing of items",
     *     operationId="index",
     *     @OA\Response(
     *         response=200,
     *         description="successful",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Parameter(
     *         name="_page",
     *         in="query",
     *         description="current page",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64",
     *             example=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="_limit",
     *         in="query",
     *         description="max item in a page",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64",
     *             example=10
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="_search",
     *         in="query",
     *         description="word to search",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="_artist",
     *         in="query",
     *         description="search by artist like name",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="_sort_by",
     *         in="query",
     *         description="word to search",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             example="latest"
     *         )
     *     ),
     * )
     */

    public function index(Request $request)
    {
        try {
            $data['filter']       = $request->all();
            $page                 = $data['filter']['_page']  = (@$data['filter']['_page'] ? intval($data['filter']['_page']) : 1);
            $limit                = $data['filter']['_limit'] = (@$data['filter']['_limit'] ? intval($data['filter']['_limit']) : 1000);
            $offset               = ($page?($page-1)*$limit:0);
            $data['products']     = Art::whereRaw('1 = 1');
            
            if($request->get('_search')){
                $data['products'] = $data['products']->whereRaw('(LOWER(art_name) LIKE "%'.strtolower($request->get('_search')).'%" OR LOWER(artist) LIKE "%'.strtolower($request->get('_search')).'%")');
            }
            if($request->get('_artist')){
                $data['products'] = $data['products']->whereRaw('LOWER(artist) = "'.strtolower($request->get('_artist')).'"');
            }
            if($request->get('_sort_by')){
            switch ($request->get('_sort_by')) {
                default:
                case 'latest_added':
                $data['products'] = $data['products']->orderBy('created_at','DESC');
                break;
                case 'name_asc':
                $data['products'] = $data['products']->orderBy('art_name','ASC');
                break;
                case 'name_desc':
                $data['products'] = $data['products']->orderBy('art_name','DESC');
                break;
                case 'price_asc':
                $data['products'] = $data['products']->orderBy('price','ASC');
                break;
                case 'price_desc':
                $data['products'] = $data['products']->orderBy('price','DESC');
                break;
            }
            }
            $data['products_count_total']   = $data['products']->count();
            $data['products']               = ($limit==0 && $offset==0)?$data['products']:$data['products']->limit($limit)->offset($offset);
            // $data['products_raw_sql']       = $data['products']->toSql();
            $data['products']               = $data['products']->get();
            $data['products_count_start']   = ($data['products_count_total'] == 0 ? 0 : (($page-1)*$limit)+1);
            $data['products_count_end']     = ($data['products_count_total'] == 0 ? 0 : (($page-1)*$limit)+sizeof($data['products']));
           return response()->json($data, 200);

        } catch(\Exception $exception) {
            throw new HttpException(400, "Invalid data : {$exception->getMessage()}");
        }
    }



    /**
     * @OA\Post(
     *     path="/api/art",
     *     tags={"Art"},
     *     summary="Store a newly created item",
     *     operationId="store",
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful",
     *         @OA\JsonContent()
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Request body description",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/Art",
     *             example={"art_name": "Path to Spring",
     *                      "artist": "Paul",
     *                      "techniques": "Oil on canvas",
     *      *               "type": "Portrait",
     *                      "size": "45 x 33 cm",
     *                      "cover": "https://images-na.ssl-images-amazon.com/images/S/compressed.photo.goodreads.com/books/1482170055i/33511107.jpg",
     *                      "description": "Path to Spring is a special art painted by Paul",
     *                      "price": 200}
     *         ),
     *     ),
     *      security={{"passport_token_ready":{}, "passport":{}}}
     * )
     */


    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'art_name'  => 'required|unique:arts',
                'artist'  => 'required|max:100',
            ]);
            if ($validator->fails()) {
                throw new HttpException(400, $validator->messages()->first());
            }
            $art = new Art;
            $art->fill($request->all())->save();
            return $art;

        } catch(\Exception $exception) {
            throw new HttpException(400, "Invalid Data : {$exception->getMessage()}");
        }
    }


    /**
     * @OA\Get(
     *     path="/api/art/{id}",
     *     tags={"Art"},
     *     summary="Display the specified item",
     *     operationId="show",
     *     @OA\Response(
     *         response=404,
     *         description="Item not found",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of item that needs to be displayed",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     * )
     */


    public function show($id)
    {
        $art = Art::find($id);
        if(!$art){
            throw new HttpException(404, 'Item not found');
        }
        return $art;
    }


    /**
     * @OA\Put(
     *     path="/api/art/{id}",
     *     tags={"Art"},
     *     summary="Update the specified item",
     *     operationId="update",
     *     @OA\Response(
     *         response=404,
     *         description="Item not found",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of item that needs to be updated",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Request body description",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/Art",
     *             example={"art_name": "Path to Spring",
     *                      "artist": "Paul",
     *                      "techniques": "Oil on canvas",
     *      *               "type": "Portrait",
     *                      "size": "45 x 33 cm",
     *                      "cover": "https://images-na.ssl-images-amazon.com/images/S/compressed.photo.goodreads.com/books/1482170055i/33511107.jpg",
     *                      "description": "Path to Spring is a special art painted by Paul",
     *                      "price": 200}
     *         ),
     *     ),
     *      security={{"passport_token_ready":{}, "passport":{}}}
     * )
     */

    public function update(Request $request, $id)
    {
        $art = Art::find($id);
        if(!$art){
            throw new HttpException(404, 'Item not found');
        }

        try{
            $validator = Validator::make($request->all(), [
                'art_name'  => 'required|unique:arts',
                'artist'  => 'required|max:100',
            ]);
            if ($validator->fails()) {
                throw new HttpException(400, $validator->messages()->first());
            }
           $art->fill($request->all())->save();
           return response()->json(array('message'=>'Updated successfully'), 200);

        } catch(\Exception $exception) {
            throw new HttpException(400, "Invalid Data : {$exception->getMessage()}");
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/art/{id}",
     *     tags={"Art"},
     *     summary="Remove the specified item",
     *     operationId="destroy",
     *     @OA\Response(
     *         response=404,
     *         description="Item not found",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of item that needs to be removed",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *      security={{"passport_token_ready":{}, "passport":{}}}
     * )
     */
    
    public function destroy(string $id)
    {
        $art = Art::find($id);
        if(!$art){
            throw new HttpException(404, 'Item not found');
        }

        try {
            $art->delete();
            return response()->json(array('message'=>'Deleted successfully'), 200);

        } catch(\Exception $exception) {
            throw new HttpException(400, "Invalid data : {$exception->getMessage()}");
        }
    }
}