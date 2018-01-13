<?php

namespace App\Http\Controllers\Api;


use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiController extends Controller
{
    protected $statusCode = 200;

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function respondNotFound($message = 'Not Found!'){
        return $this->setStatusCode(404)->respondWithError($message);
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function respondInternalError($message = 'Internal Error!'){
        return $this->setStatusCode(500)->respondWithError($message);
    }


    /**
     * @param $data
     * @param array $headers
     * @return mixed
     */
    public function respond($data, $headers = []){
        return Response::json($data,$this->getStatusCode(),$headers);
    }
   
    public function respondWithPagination(LengthAwarePaginator $paginator, $data)
    {
        $totalPages = ceil($paginator->total() / $paginator->perPage());
        $data = array_merge($data, [
            'paginator' => [
                'total_count' => $paginator->total(),
                'total_pages' => $totalPages,
                'prev_page' => $paginator->currentPage() > 1 ? $paginator->currentPage() - 1 : false,
                'current_page' => $paginator->currentPage(),
                'next_page' => $paginator->currentPage() < $totalPages ? $paginator->currentPage() + 1 : false,
                'limit' => $paginator->perPage()
            ]
        ]);

        return $this->respond($data);
    }

    /**
     * @param $message
     * @return mixed
     */
    public function respondWithError($message){
        return $this->respond([
            'errors'=> [
                0 => $message
            ]
        ]);
    }

    /**
     * Format the validation errors to be returned.
     *
     * @param  Validator  $validator
     * @return array
     */
    protected function formatValidationErrors(Validator $validator)
    {
        return [
            'errors' => $validator->errors()->all()
        ];
    }
}