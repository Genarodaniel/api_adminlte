<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Models\Condominium;
use App\Http\Models\Utensil;
use App\Http\Models\UtensilCond;
use App\API\ApiError;
use App\Http\Models\UtensilSchedule;
use Validator;

class UtensilController extends Controller
{
    private $utensil;

    public function __construct(Utensil $utensil)
    {
        $this->utensil = $utensil;
        $this->successStatus = 200;
        $this->utensil_cond = new UtensilCond();
        $this->utensil_schedule = new UtensilSchedule();
    }

    public function list()
    {
        try {
            $data = ['data' => $this->utensil->paginate(20)];
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
                'name' => ['required', 'string', 'max:255'],
                'description' => ['required', 'string','max:255'],
                'condominium_id' => ['required', 'integer']
            ]);

            if($validator->fails()) {
                return response()->json(['error' => $validator->errors()],402);
            }
            $input = $request->all();
            $condominium_exists = Condominium::where('id', '=', $input['condominium_id'])->exists();

            if(!$condominium_exists) {
                return response()->json(['error' => 'condominiums doesnt exists'],402);
            }else {

                $utensil['name'] = $input['name'];
                $utensil['description'] = $input['description'];
                $utensil = Utensil::create($utensil);
                $utensil->save();

                $utensilCond['condominium_id'] = $input['condominium_id'];
                $utensilCond['utensil_id'] = $utensil->id;
                $utensilCond = UtensilCond::create($utensilCond);

                $success['utensil_id'] = $utensil->id;
                $success['condominium_id'] = $utensilCond->id;
                return  response()->json(['success' => $success]);
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
            $utensil = Utensil::find($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required | string | max:255',
                'description' => 'nullable | string | max:255',
                'condominium_id' => 'nullable | integer',
            ]);

            if($validator->fails()) {
                return response()->json(['error' => $validator->errors()],402);
            }elseif(!$utensil) {
                return response()->json(['error' => 'Check utensil id'],402);
            }else {
                if(!isset($request['name']) && !$request['name']) {
                    $utensil->name = $utensil->name;
                }else {
                    $utensil->name = trim($request['name']);
                }

                if(!isset($request['description']) && !$request['description']) {
                    $utensil->description = $utensil->description;
                }else {
                    $utensil->description = trim($request['description']);
                }

                if(isset($request['condominium_id']) && $request['condominium_id']) {
                    $utensilCond = $this->utensil_cond->exists();
                    if($utensilCond) {
                        $utensilCond->condominium_id = $request['condominium_id'];
                        $utensilCond->updated_at = now();
                        $utensilCond->save();
                    }else {
                        return response()->json(['error' => 'The Condominium doesn\'t exists'],402);
                    }
                }
                $utensil->updated_at = now();
                $utensil->save();
                return response()->json(['success' => true,'data' => $utensil],$this->successStatus);
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
            $utensil = Utensil::find($id);

            if(!$utensil) {
                return response()->json(['error'=>'utensil doesn\'t exists']);
            }else {
                $data = ['data'=>$utensil];
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
        if($this->utensil->find($id)){
            $this->utensil->where('id', $id)->delete();
            $this->utensil_cond->where('utensil_id', $id)->delete();
            $this->utensil_schedule->where('utensil_id', $id)->delete();
            return response()->json(['success' => true], 200);
        }else{
            return response()->json(['error'=>' Condominio não existe'],402);
        }
    }

}
