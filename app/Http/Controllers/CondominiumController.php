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


    public function __construct(Condominium $condominium)
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

    public function update(Request $request){
        // try{
        //    $query =Condominium::where('id', '=', $request['id'])->get();
            $id = $request['id'];
            $query = Condominium::find($id);
           
            foreach($request as $value){
                if((isset($value)) && !(isNull($value))){
                   // $query = $value;
                }
            }

            // 'address_street', 'address_number', 'address_state',
            // 'address_city','manager_id','address_complement',
            // // 'address_country','address_state_abbr',
            $i=-1;
            
            return response()->json($query);
           //$query->address_number =3;
           //$query->save();
         
            // return $test;
        // }
        // catch(\Exception $e){
        //     if(config('app.debug')){
        //         return response()->json(ApiError::errorMessage($e->getMessage(),1010));
        //     }
        //     return response()->json(ApiError::errorMessage('houve um erro ao realizar a operacao',400));
        // }
    }


    public function show(Condominium $id){

        try{
            $id_exists = Condominium::where('id','=',$id->id)->exists();
            if($id_exists === false){
                return response()->json(['error'=>'Condominio não existe']);
            }
            else{
                $data = ['data'=>$id];
                return $data;
            }
        }
        catch(\Exception $e){
            if(config('app.debug')){
                return response()->json(ApiError::errorMessage($e->getMessage(),1010));
            }
            return response()->json(ApiError::errorMessage('houve um erro ao realizar a operação',1010));
        }
    }



}
