<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Models\Condominium;
use App\Http\Models\Utensil;
use App\Http\Models\UtensilCond;
use App\API\ApiError;
use Validator;

class UtensilController extends Controller
{
    private $utensil;

    public function __construct(Utensil $utensil)
    {
        $this->utensil = $utensil;
        $this->successStatus = 200;
        $this->utensil_cond = new UtensilCond();
    }

    public function list()
    {
        try {
            $data = ['data' => $this->utensil->all()];
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
                'condominium_id' => ['required', 'integer'],
                'days' => ['required','array'],
                'days.*'=>['integer'],
                'work_start' => ['required','string'],
                'work_end' => ['required','string'],
                'max_time' => ['required','integer']
            ]);

            if($validator->fails()) {
                return response()->json(['error' => $validator->errors()],402);
            }
            $input = $request->all();
            $condominium_exists = Condominium::where('id', '=', $input['condominium_id'])->exists();

            if(!$condominium_exists) {
                return response()->json(['error' => 'condominiums doesnt exists'],402);
            }else {

                if(!$this->validateDays($request['days'])){
                    return response()->json(['error' => "The days must be between 1 and 7"]);
                }

                $validateStart = $this->validateHour($request['work_start']);

                if($validateStart !== true){
                    return response()->json(['error' => " The work start" . $validateStart]);
                }
                $validateEnd = $this->validateHour($request['work_end']);


                if($validateEnd !== true){
                    return response()->json(['error' => " The work end" . $validateEnd]);
                }


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

    public function validateDays($days)
    {
        $days_accept = [1,2,3,4,5,6,7];

        foreach($days as $day){
            if(!in_array($day, $days_accept)){
                return false;
            }
        }
        return true;
    }

    public function validateHour($hour){
        if(!stristr($hour,":")){
            $error = "The format is invalid, this must be like 8:00";
        }else {
            $hour_strip = explode(":", $hour);
            if(!isset($hour_strip[0])){
                $error = "The format is invalid, this must be like 8:00";
            }elseif(!($hour_strip[0] >= 00 && $hour_strip[0] <= 24)){
                $error = "Must be an hour between 00 and 24";
            }elseif(!isset($hour_strip[1])){
                $error = "The format is invalid, this must be like 8:00";
            }elseif(!($hour_strip[1] >= 00 && $hour_strip[1] <= 60)){
                $error = "Must be an minute between 00 and 60";
            }else {
                $error = true;
            }
        }
        return $error;
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

}
