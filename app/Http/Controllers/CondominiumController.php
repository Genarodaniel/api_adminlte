<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Models\Condominium;
use App\API\ApiError;
use Validator;
use App\Http\Models\User_app;
use App\Http\Models\UserCond;

use function GuzzleHttp\json_decode;
use Illuminate\Support\Facades\Facade;
use App\Http\Models\UtensilCond;


class CondominiumController extends Controller
{
    public $successStatus = 200;
    private $condominium;


    public function __construct(Condominium $condominium)
    {
        $this->condominium = $condominium;
        $this->utensil_cond = new UtensilCond();
        $this->user_cond = new UserCond();
    }

    public function list()
    {
        try {
            $data = ['data' => $this->condominium->paginate(20)];
            return response()->json($data,$this->successStatus);
        } catch (\Exception $e) {
            if(config('app.debug')) {
                return response()->json(ApiError::errorMessage($e->getMessage(), 402));
            }
            return response()->json(ApiError::errorMessage('Sorry, an error occurred while processing', 402));
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

            if($validator->fails()) {
                return response()->json(['error' => $validator->errors()],402);
            }
            $input = $request->all();
            $user_exists = User_app::where('id', '=', $input['manager_id'])->exists();
            $query = User_app::where('id', '=', $input['manager_id'])->get('user_type');

            if(!$user_exists) {
                return response()->json(['error' => 'manager doesnt exists'],402);
            }else {
                foreach ($query as $value) {
                    $user['user_type'] = $value->user_type;
                    $user['id'] = $value->id;
                }
                if($user['user_type'] == 'am') {
                    $data = Condominium::create($input);
                    $data->save();
                    $success['id'] = $data->id;
                    $success['manager_id'] = $data->manager_id;
                    return  response()->json(['success' => $success]);
                }else {
                    return response()->json(['error' => 'User types doesnt meets the requirement'], 402);
                }
            }
        } catch (\Exception $e) {
            if(config('app.debug')) {
                return response()->json(ApiError::errorMessage($e->getMessage(), 402));
            }
            return response()->json(ApiError::errorMessage('Sorry, an error occurred while processing', 402));
        }
    }

    public function update(Request $request,$id)
    {
        try {
            $condominium = Condominium::find($id);

            $validator = Validator::make($request->all(), [
                'address_street' => 'required',
                'address_number' => 'required',
                'address_city' => 'required',
                'address_state' => 'required',
                'address_state_abbr' => 'required',
                'address_country' => 'required',
                'address_complement' => 'nullable',
            ]);

            if($validator->fails()) {
                return response()->json(['error' => $validator->errors()],402);
            }elseif(!$condominium) {
                return response()->json(['error' => 'Check condominium id'],402);
            }else {
                if(!isset($request['address_street']) && !$request['address_street']) {
                    $condominium->address_street = $condominium->address_street;
                }else {
                    $condominium->address_street = trim($request['address_street']);
                }

                if(!isset($request['address_number']) && !$request['address_number']) {
                    $condominium->address_number = $condominium->address_number;
                }else {
                    $condominium->address_number = trim($request['address_number']);
                }

                if(!isset($request['address_city']) && !$request['address_city']) {
                    $condominium->address_city = $condominium->address_city;
                }else {
                    $condominium->address_city = trim($request['address_city']);
                }

                if(!isset($request['address_state']) && !$request['address_state']) {
                    $condominium->address_state = $condominium->address_state;
                }else {
                    $condominium->address_state = trim($request['address_state']);
                }

                if(!isset($request['address_state_abbr']) && !$request['address_state_abbr']) {
                    $condominium->address_state_abbr = $condominium->address_state_abbr;
                }else {
                    $condominium->address_state_abbr = trim($request['address_state_abbr']);
                }

                if(!isset($request['address_country']) && !$request['address_country']) {
                    $condominium->address_country = $condominium->address_country;
                }else {
                    $condominium->address_country = trim($request['address_country']);
                }

                if(!isset($request['address_complement']) && !$request['address_complement']) {
                    $condominium->address_complement = $condominium->address_complement;
                }else {
                    $condominium->address_complement = trim($request['address_complement']);
                }

                $condominium->updated_at = now();
                $condominium->save();
                return response()->json(['success' => true,'data' => $condominium],$this->successStatus);
            }
        }catch(\Exception $e) {
            if(config('app.debug')) {
                return response()->json(ApiError::errorMessage($e->getMessage(), 402));
            }
            return response()->json(ApiError::errorMessage('Sorry, an error occurred while processing', 402));
        }
    }

    public function show($id)
    {
        try {
            $condominium = Condominium::find($id);

            if(!$condominium) {
                return response()->json(['error'=>'condominium doesn\'t exists']);
            }else {
                $data = ['data'=>$condominium];
                return $data;
            }
        }catch(\Exception $e) {
            if(config('app.debug')) {
                return response()->json(ApiError::errorMessage($e->getMessage(),402));
            }
            return response()->json(ApiError::errorMessage('Sorry, an error occurred while processing',402));
        }
    }

    public function delete($id)
    {
        if($this->condominium->find($id)){
            $this->condominium->where('id',$id)->delete();
            $this->utensil_cond->where('condominium_id',$id)->delete();
            $this->user_cond->where('condominium_id',$id)->delete();
            return response()->json(['success' => true], 200);
        }else{
            return response()->json(['error', 'condominio não existe'],402);
        }
    }
}
