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
        if(Auth::guard('web2')->attempt(['email' => request('email'), 'password' => request('password')])) {
            $user = Auth::guard('web2')->user();
            $success['token'] = $user->createToken('MyApp')->accessToken;
            return response()->json(['success' => true, 'token' => $success], $this->sucessStatus);
        }else {
            return response()->json(['success' => false,'erro' => 'E-mail ou senha inválidos'], 401);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'c_password' => ['required', 'string','min:8','same:password']
        ]);

        if($validator->fails()) {
            return response()->json(['success' => false,'erro' => $validator->errors()], 402);
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);

        try {
            $user = User::find($request['email']);
            if(!$user) {
                $user = User::create($input);
                $success['token'] = $user->createToken('MyApp')->accessToken;
                $success['name'] = $user->name;

                return response()->json(['success' => true, 'data' => $success], $this->sucessStatus);
            }else {
                return response()->json(['success' => false, 'erro' => 'E-mail já cadastrado']);
            }

        }catch(\Exception $e) {
            if(config('app.debug')) {
                return response()->json(['success' => false, 'erro' => ApiError::errorMessage($e->getMessage(), 402)]);
            }
            return response()->json(['success' => false, 'erro' => ApiError::errorMessage('Desculpe. Houve um problema ao processar sua requisição', 402)]);
            }
        }

    public function details()
    {
        $user = Auth::user();
        return response()->json(['success' => true, 'data' => $user], $this->sucessStatus);
    }

    public function delete($id)
    {
        if($this->user->find($id)){
            $this->user->where('id',$id)->delete();
            return response()->json(['success' => true], 200);
        }else{
            return response()->json(['success' => false,'erro'=> 'usuário não existe'],402);
        }
    }
}
