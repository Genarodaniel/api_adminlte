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
            $data = ['success' => true, 'data' => $this->utensil->paginate(20)];
            return response()->json($data,$this->successStatus);
        } catch (\Exception $e) {
            if(config('app.debug')) {
                return response()->json(['success' => false, 'erro' => ApiError::errorMessage($e->getMessage(), 402)]);
            }
            return response()->json(['success' => false, 'erro' => ApiError::errorMessage('Desculpe. Houve um problema ao processar sua requisição', 402)]);
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
                return response()->json(['success' => false, 'erro' => $validator->errors()],402);
            }
            $input = $request->all();
            $condominium_exists = Condominium::where('id', '=', $input['condominium_id'])->exists();

            if(!$condominium_exists) {
                return response()->json(['success' => false, 'erro' => 'Condomínio não encontrado'],402);
            }else {

                $utensil['name'] = $input['name'];
                $utensil['description'] = $input['description'];
                $utensil = Utensil::create($utensil);
                $utensil->save();

                $utensilCond['condominium_id'] = $input['condominium_id'];
                $utensilCond['utensil_id'] = $utensil->id;
                $utensilCond = UtensilCond::create($utensilCond);

                $success['utensil_id'] = $utensil->id;
                $success['condominium_id'] = $utensilCond->condominium_id;
                return  response()->json(['success' => true, 'data' => $success]);
            }
        } catch (\Exception $e) {
            if(config('app.debug')) {
                return response()->json(['success' => false, 'erro' => ApiError::errorMessage($e->getMessage(), 402)]);
            }
            return response()->json(['success' => false, 'erro' => ApiError::errorMessage('Desculpe. Houve um problema ao processar sua requisição', 402)]);
        }
    }


    public function update(Request $request,$id)
    {
        try {
            $utensil = Utensil::find($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required | string | max:255',
                'description' => 'required | string | max:255',
            ]);

            if($validator->fails()) {
                return response()->json(['success' => false, 'erro' => $validator->errors()],402);
            }elseif(!$utensil) {
                return response()->json(['success' => false, 'erro' => 'Utensílio não encontrado'],402);
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

                $utensil->updated_at = now();
                $utensil->save();
                return response()->json(['success' => true,'data' => $utensil],$this->successStatus);
            }
        }catch(\Exception $e) {
            if(config('app.debug')) {
                return response()->json(['success' => false, 'erro' => ApiError::errorMessage($e->getMessage(), 402)]);
            }
            return response()->json(['success' => false, 'erro' => ApiError::errorMessage('Desculpe. Houve um problema ao processar sua requisição', 402)]);
        }
    }

    public function show($id)
    {
        try {
            $utensil = Utensil::find($id);

            if(!$utensil) {
                return response()->json(['success' => false, 'erro'=>'Utensílio não encontrado']);
            }else {
                return response()->json(['success' => true, 'data'=> $utensil]);
            }
        }catch(\Exception $e) {
            if(config('app.debug')) {
                return response()->json(['success' => false, 'erro' => ApiError::errorMessage($e->getMessage(),402)]);
            }
            return response()->json(['success' => false, 'erro' => ApiError::errorMessage('Desculpe. Houve um problema ao processar sua requisição',402)]);
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
            return response()->json(['success' => false, 'erro'=>' Condominio não existe'],402);
        }
    }

}
