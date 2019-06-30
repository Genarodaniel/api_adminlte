<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Condominium;
use App\API\ApiError;
use Validator;
use App\User_app;
use function GuzzleHttp\json_decode;

class CondominiumController extends Controller
{
    public $successStatus = 200;
    private $condominium;


    public function __construct(\App\Condominium $condominium)
    {
        $this->condominium = $condominium;
    }

    public function list()
    {
        try {
            $data = ['data' => $this->condominium->all()];
            return response()->json($data, $this->successStatus);
        } catch (\Exception $e) {
            if (config('app.debug')) {
                return response()->json(ApiError::errorMessage($e->getMessage(), 1010));
            }
            return response()->json(ApiError::errorMessage('houve um erro ao realizar a operação', 1010));
        }
    }

    public function store(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'address_street' => 'required',
                'address_number' => 'required',
                'address_city' => 'required',
                'address_state' => 'required',
                'address_state_abbr' => 'required',
                'address_country' => 'required',
                'manager_id' => 'required|integer',
                'address_complement' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()]);
            }
            $input = $request->all();
            $user_exists = User_app::where('id', '=', $input['manager_id'])->exists();
            $query = User_app::where('id', '=', $input['manager_id'])->get('user_type');

            if ($user_exists === false) {
                return response()->json(['error' => 'manager doesnt exists']);
            } else {
                foreach ($query as $value) {
                    $user['user_type'] = $value->user_type;
                    $user['id'] = $value->id;
                }
                if ($user['user_type'] === 'am') {
                    $data = Condominium::create($input);
                    $data->save();
                    $success['id'] = $data->id;
                    $success['manager_id'] = $data->manager_id;
                    return  response()->json(['success' => $success], 200);
                } else {
                    return response()->json(['error' => 'User types doesnt meets the requirement'], 200);
                }
            }
        } catch (\Exception $e) {
            if (config('app.debug')) {
                return response()->json(ApiError::errorMessage($e->getMessage(), 1010));
            }
            return response()->json(ApiError::errorMessage('houve um erro ao realizar a operação', 1010));
        }
    }
}
