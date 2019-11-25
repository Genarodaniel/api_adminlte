<?php

namespace app\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Models\User_app;
use Validator;
use App\API\ApiError;
use App\Http\Models\Condominium;
use Illuminate\Support\Facades\Auth;
use App\Http\Models\UserCond;

class User_appController extends \App\Http\Controllers\Controller
{
    public $successStatus = 200;
    private $user_app;

    public function __construct(User_app $user_app) {
       $this->user_app = $user_app;
       $this->condominium = new Condominium();
       $this->userCond = new UserCond();
    }

    public function all_users(){

        try {
            $data = ['success' => true,'data' => $this->user_app->paginate(20)];
            return response()->json($data);
       }catch(\Exception $e) {
            if(config('app.debug')){
                return response()->json(['success' => false,'erro' => ApiError::errorMessage($e->getMessage(),402)]);
            }
            return response()->json(['success' => false,'erro' => ApiError::errorMessage('Desculpe. Houve um problema ao processar sua requisição',402)]);
        }
    }

    public function show($id)
    {
       try {
            if(isset($id) && $id){
                $user = $this->user_app->find($id);
                if($user) {
                    $data = ['data' => $user];
                    return response()->json($data,$this->successStatus);
                }else{
                    return response()->json(['success' => false, 'erro' => 'Usuário não encontrado']);
                }

            }else {
                return response()->json(['success' => false ,'erro' => 'id não informado'],402);
            }
       }catch(\Exception $e) {
            if(config('app.debug')) {
                return response()->json(['success' => false,'erro' => ApiError::errorMessage($e->getMessage(),402)]);
            }
            return response()->json(['success' => false,'erro' => ApiError::errorMessage('Desculpe. Houve um problema ao processar sua requisição',402)]);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),[
                'name' => 'required',
                'email'=> 'required|email',
                'user_type'=> 'required',
                'password'=>'required',
                'condominium_id' => 'int|nullable'

            ]);

            if($validator->fails()){
                return response()->json(['success' => false,'erro' => $validator->errors()],401);
            }

            if(!$this->user_app->where('email',$request->email)->first()){
                if($request->user_type == "ar") {
                    if(isset($request['condominium_id']) && $request['condominium_id']) {
                        $condominium = $this->condominium->find($request['condominium_id']);
                        if($condominium) {

                            $user['password'] = bcrypt($request['password']);
                            $user['name'] = $request['name'];
                            $user['email'] = $request['email'];
                            $user['user_type'] = $request['user_type'];
                            $user['condominium_id'] = $request['condominium_id'];
                            $user = $this->user_app->create($user);

                            $utensil_cond['condominium_id'] = $request['condominium_id'];
                            $utensil_cond['user_id'] = $user->id;
                            $this->userCond->create($utensil_cond);

                            $success['name'] = $user->name;
                            $success['email'] = $user->email;
                            $success['id'] = $user->id;

                            return response()->json(['success' => true, 'data' => $success], $this->successStatus);
                        }else {
                            return response()->json(['success' => false,'erro' => 'Condomínio não encontrado ']);
                        }

                    }else {
                        return response()->json(['success' => false,'erro' => "Para usuários do tipo 'AR' é preciso informar o condominium_id"]);
                    }
                }else {

                    $user['password'] = bcrypt($request['password']);
                    $user['name'] = $request['name'];
                    $user['email'] = $request['email'];
                    $user['user_type'] = $request['user_type'];

                    $data = User_app::create($user);
                    $success['name'] = $data->name;
                    $success['email'] = $data->email;
                    $success['id'] = $data->id;

                    return response()->json(['success' => true, 'data' => $success], $this->successStatus);
                }
            }else {
                return response()->json(['success' => false,'erro' => 'E-mail já está sendo utilizado.']);
            }

        }catch(\Exception $e) {
            if(config('app.debug')) {
                return response()->json(['success' => false,'erro' => ApiError::errorMessage($e->getMessage(),402)]);
            }
            return response()->json(['success' => false,'erro' => ApiError::errorMessage('Desculpe. Houve um problema ao processar sua requisição',402)]);
        }
    }

    public function login()
    {
        if (Auth::guard('web2')->attempt(['email' => request('email'), 'password' => request('password')])) {
            $user = $this->user_app->where('email', request('email'))->first();

            return response()->json([
                'success' => true,
                'id' => (int)$user->id,
                'name' => $user->name,
                'email' => $user->email
                    ], 200);
        }else {
            return response()->json(['success' => false, 'erro'=> 'e-mail ou senha inválidos'], 401);
        }
    }
    public function delete($id)
    {
        if($this->user_app->find($id)){
            $this->user_app->where('id',$id)->delete();
            $this->userCond->where('user_id', $id)->delete();
            return response()->json(['success' => true], 200);
        }else{
            return response()->json(['success' => false,'erro' => 'usuário não existe'],402);
        }
    }

    public function update(Request $request,$id)
    {
        try {
            $user = $this->user_app->find($id);

            $validator = Validator::make($request->all(), [
                'new_name' => 'nullable',
                'email' => 'required|email',
                'password' => 'required',
                'new_email' => 'nullable|unique:user_apps,email|email',
                'new_password'=>'nullable'
            ]);

            if($validator->fails()) {
                return response()->json(['success' => false,'erro' => $validator->errors()],402);
            }elseif(!$user) {
                return response()->json(['success' => false,'erro' => 'Usuário não encontrado'],402);
            }elseif(Auth::guard('web')->attempt(['email' => request('email'), 'password' => request('password')])) {

                if(isset($request['new_name']) && $request['new_name']) {
                    $user->name = trim($request['new_name']);
                }

                if(isset($request['new_email']) && $request['new_email']) {
                    $user->email = trim($request['new_email']);
                }

                if(isset($request['password']) && $request['password']) {
                    $user->password = bcrypt($request['new_password']);
                }

                $user->updated_at = now();
                $user->save();

                return response()->json(['success' => true,'data' => $user],$this->successStatus);
            }else {
                return response()->json(['success' => false,'erro' => 'E-mail ou senha incorretos']);
            }

        }catch(\Exception $e) {
            if(config('app.debug')) {
                return response()->json(['success' => false, 'erro' => ApiError::errorMessage($e->getMessage(), 402)]);
            }
            return response()->json(['success' => false, 'erro' => ApiError::errorMessage('Desculpe. Houve um problema ao processar sua requisição', 402)]);
        }
    }
}
