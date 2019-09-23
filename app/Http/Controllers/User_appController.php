<?php

namespace app\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Models\User_app;
use Validator;
use App\API\ApiError;
use Illuminate\Support\Facades\Auth;

class User_appController extends \App\Http\Controllers\Controller
{
    public $successStatus = 200;
    private $user_app;

    public function __construct(User_app $user_app) {
       $this->user_app = $user_app;
    }

 // Lembrar de arrumar um método decente com paginação
    // public function all_users(){
    //     try {
    //         $data = ['data'=>$this->user_app->all()];
    //         return response()->json($data);
    //    }catch(\Exception $e) {
    //         if(config('app.debug')){
    //             return response()->json(ApiError::errorMessage($e->getMessage(),402));
    //         }
    //         return response()->json(ApiError::errorMessage('Houve um erro ao realizar a operação',402));
    //     }
    // }

    public function show(User_app $id)
    {
       try {
            if(isset($id) && $id){
                $data =['data'=>$id];
                return response()->json($data,$this->$successStatus);
            }else {
                return response()->json(ApiError::errorMessage('Houve um erro ao realizar a operação',402));
            }
       }catch(\Exception $e) {
            if(config('app.debug')) {
                return response()->json(ApiError::errorMessage($e->getMessage(),402));
            }
            return response()->json(ApiError::errorMessage('Houve um erro ao realizar a operação',402));
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),[
                'name' => 'required',
                'email'=> 'required|email',
                'user_type'=> 'required',
                'password'=>'required'

            ]);

            if($validator->fails()){
                return response()->json(['error' => $validator->errors()],401);
            }

            $input = $request->all();
            $input['password'] = bcrypt($input['password']);
            $data = User_app::create($input);
            $success['name'] = $data->name;

            return response()->json(['success' => $success],$this->successStatus);
        }catch(\Exception $e) {
            if(config('app.debug')) {
                return response()->json(ApiError::errorMessage($e->getMessage(),402));
            }
            return response()->json(ApiError::errorMessage('Houve um erro ao realizar a operacao',402));
        }
    }

    public function login()
    {
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
            return response()->json(['sucess' => 'user authenticated'], 200);
        }else {
            return response()->json(['error' => request('email'), 'error2'=>request('password')], 401);
        }
    }
}
