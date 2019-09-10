<?php

namespace app\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Models\User;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\API\ApiError;


class UserController extends Controller
{

    public $sucessStatus = 200;
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function login()
    {
        if (Auth::guard('web2')->attempt(['email' => request('email'), 'password' => request('password')])) {
            $user = Auth::guard('web2')->user();
            $success['token'] = $user->createToken('MyApp')->accessToken;
            return response()->json(['sucess' => $success], $this->sucessStatus);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

    public function register(Request $request) {
        
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);

        try {
            $user = User::where('email', '=', $request->get('email'));
            
            if ($user === null) {
            
            $user = User::create($input);
            $success['token'] = $user->createToken('MyApp')->accessToken;
            $success['name'] = $user->name;

            return response()->json(['success' =>$success], $this->sucessStatus);
            
            } else {
                return response()->json(ApiError::errorMessage('E-mail ja cadastrado',1010));
            }

        } catch(\Exception $e){
            
                if (config('app.debug')) {
                    return response()->json(ApiError::errorMessage($e->getMessage(), 1010));
                }
                return response()->json(ApiError::errorMessage('houve um erro ao realizar a operação', 1010));
            }
        }

    public function details(){
        $user =Auth::user();
        return response()->json(['success' => $user], $this->sucessStatus);
    }

    public function all_users(){
        

        $data = ['data'=>$this->user->all()];
        return response()->json($data);
    }



    public function show(User $id){
        $data =['data'=>$id];
        return response()->json($data);
    }

    public function ok(){
        return ['status'=> true];
    }

    /* public function store(Request $request){
        $userData = $request->all();
        $this->user->create($userData);
        
    }*/

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'user_type'=> 'required',
            'password' => 'required',
            'remember_token ' =>str_random(10),
            'email_verified_at' => now(),


        ]);

        $data = User::create($request->all());

        return response()->json([
            'message' => 'Data Successfully Stored!',
            'data' => $data
        ]);
    }
}
