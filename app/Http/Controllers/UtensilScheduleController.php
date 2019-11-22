<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;
use App\Http\Models\Utensil;
use App\API\ApiError;
use App\Rules\day;
use App\Http\Models\UtensilSchedule;

class UtensilScheduleController extends Controller
{

    public function __construct()
    {
        $this->utensil = new Utensil();
        $this->utensilSchedule = new UtensilSchedule();
        $this->successStatus = 200;
    }
    public function store(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'utensil_id'=>['required','integer'],
                'days' => ['required','array'],
                'days.*'=>['array',new day],
                'days.*.work_start' => ['date_format:H:i','required'],
                'days.*.work_end' => ['date_format:H:i','required'],
                'days.*.max_time' => ['date_format:H:i','required']
            ]);

            if($validator->fails()) {
                return response()->json(['error' => $validator->errors()],402);
            }
            $input = $request->all();
            $utensil_exists = $this->utensil->where('id', '=', $input['utensil_id'])->exists();


            if(!$utensil_exists) {
                return response()->json(['error' => 'utensil doesnt exists'],402);
            }else {

                foreach($input['days'] as $days => $day){

                    $schedule_exists = $this->utensilSchedule->where(
                        'utensil_id', '=',$input['utensil_id'])->where(
                        'days_work', '=', $days)->exists();

                    if($schedule_exists){
                        return response()->json(['error' => 'The schedule for days work '. $days . ' already exists'],402);
                    }

                    if($days < 1 || $days > 7){
                        return response()->json(['error' => "The day  must be between 1 and 7"],402);
                    }

                    $validateStart = $this->validateHour($day['work_start']);

                    if($validateStart !== true){
                        return response()->json(['error' => " The day " .$days .  " work start" . $validateStart],402);
                    }

                    $validateEnd = $this->validateHour($day['work_end']);

                    if($validateEnd !== true){
                        return response()->json(['error' => " The day " .$days .  " work start" . $validateEnd],402);
                    }

                    $verifyHour = $this->verifyHour($day['work_start'], $day['work_end'], $day['max_time'],402);

                    if($verifyHour !== true){
                        return response()->json(['error' =>$verifyHour],402);
                    }

                    $utensilSchedule['days_work'] = $days;
                    $utensilSchedule['utensil_id'] = trim($request['utensil_id']);
                    $utensilSchedule['work_start'] = trim($day['work_start']);
                    $utensilSchedule['work_end'] = trim($day['work_end']);
                    $utensilSchedule['max_time'] = trim($day['max_time']);
                    $utensilSchedule['created_at'] = now();
                    $utensilSchedule['updated_at'] = now();
                    $add[] = $days;

                    $data = $this->utensilSchedule->create($utensilSchedule);

                }
                $saves = implode(',',$add);
                return response()->json(['success' => 'Days ' . $saves ],$this->successStatus);

            }

        }catch (\Exception $e) {
            if(config('app.debug')) {
                return response()->json(ApiError::errorMessage($e->getMessage(), 402));
            }
            return response()->json(ApiError::errorMessage('Sorry, an error occurred while processing', 402));
        }

    }

    public function update(Request $request){

        try {
            $validator = Validator::make($request->all(), [
                'utensil_id'=>['required','integer'],
                'days_work' => ['required','integer' ,'between:1,7'],
                'days'=>['array'],
                'days.work_start' => ['date_format:G:i','required'],
                'days.work_end' => ['date_format:G:i','required'],
                'days.max_time' => ['date_format:G:i','required']
            ]);


            if($validator->fails()) {
                return response()->json(['error' => $validator->errors()],402);
            }

            $input = $request->all();
            $work_start = $input['days']['work_start'];
            $work_end = $input['days']['work_end'];
            $max_time = $input['days']['max_time'];

            $schedule_exists = $this->utensilSchedule->where(
                'utensil_id', '=',$input['utensil_id'])->where(
                'days_work', '=', $input['days_work'])->exists();


            if(!$schedule_exists){
                return response()->json(['error' => 'This schedule doenst exists, you need to create it'],402);
            }


            if($input['days_work'] < 1 || $input['days_work'] > 7){
                return response()->json(['error' => "The day  must be between 1 and 7"],402);
            }

            $validateStart = $this->validateHour($work_start);

            if($validateStart !== true){
                return response()->json(['error' => " This day  work start" . $validateStart],402);
            }

            $validateEnd = $this->validateHour($work_end);

            if($validateEnd !== true){
                return response()->json(['error' => " This day work start" . $validateEnd],402);
            }

            $verifyHour = $this->verifyHour($work_start, $work_end, $max_time);

            if($verifyHour !== true){
                return response()->json(['error' =>$verifyHour],402);
            }

            $utensilSchedule['days_work'] = $input['days_work'];
            $utensilSchedule['utensil_id'] = trim($input['utensil_id']);
            $utensilSchedule['work_start'] = trim($work_start);
            $utensilSchedule['work_end'] = trim($work_end);
            $utensilSchedule['max_time'] = trim($max_time);
            $utensilSchedule['updated_at'] = now();
            $data = $this->utensilSchedule->where(
                'utensil_id', '=',$input['utensil_id'])->where(
                'days_work', '=', $input['days_work'])->update($utensilSchedule);

            if($data){
                return response()->json(['success' => 'Day ' . $input['days_work'] . ' saved' ],$this->successStatus);
            }

            return response()->json(['error' => 'Sorry, an error occurred while update'], $this->successStatus);


        }catch (\Exception $e) {
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




    public function verifyHour($day_start, $day_end, $max_time)
    {
        $striped_start = explode(":", $day_start);
        $striped_end = explode(":", $day_end);
        $striped_max_time = explode(':', $max_time);
        $hour_start = (int)$striped_start[0];
        $hour_end = (int)$striped_end[0];
        $minute_start = (int)$striped_start[1];
        $minute_end = (int)$striped_end[1];
        $minute_max = (int)$striped_max_time[1];
        $hour_max = (int)$striped_max_time[0];

        $error = '';
        if(strlen($hour_start) > 2){
            $error =  "Work start hour need to have max 2 houses";
            return $error;
        }elseif(strlen($hour_end) > 2){
            $error =  "Work end hour need to have max 2 houses";
            return $error;
        }elseif(strlen($minute_start) > 2){
            $error =  "Work start minute need to have max 2 houses";
            return $error;
        }elseif(strlen($minute_end) > 2){
            $error =  "Work end minute need to have max 2 houses";
            return $error;
        }elseif(strlen($minute_max) > 2){
            $error =  "max time minute need to have max 2 houses";
            return $error;
        }elseif(strlen($minute_max) > 2){
            $error =  "max minute need to have max 2 houses";
            return $error;
        }elseif($hour_start > $hour_end){
            $error =  "Work start is bigger than end";
            return $error;
        }elseif($hour_start == $hour_end){
            if($minute_start > $minute_end){
                $error =  "Minute start is bigger than end";
                return $error;
            }
            return true;
        }
        return true;

    }

    public function list($utensil_id){
        $exists = $this->utensilSchedule->where('utensil_id', '=', $utensil_id)->exists();        $days = $this->utensilSchedule->where('utensil_id', '=', $utensil_id)->get();
        if(!$exists) {
            return response()->json(['success' => false, 'error' => 'Dont have schedules for this utensil']);
        }else {
            return response()->json($days,$this->successStatus);
        }
    }

    public function listAppointments($utensil_id){
        $days = $this->utensilSchedule->where('utensil_id', '=', $utensil_id)->get();
        return $days;
    }

    public function delete($id)
    {
        if($this->utensilSchedule->find($id)){
            $this->utensilSchedule->where('id',$id)->delete();
            return response()->json(['success' => true], 200);
        }else{
            return response()->json(['error', 'Horário de funcionamento não cadastrado'],402);
        }
    }


    public function verifyOpen($utensil_id, $day){
        $open = $this->utensilSchedule->where(
            'utensil_id', '=',$utensil_id)->where(
            'days_work', '=', $day)->exists();
        if($open){
            return true;
        }

        return false;
    }

    public function validateHour($hour){
        if(!stristr($hour,":")){
            $error = "format is invalid, this must be like 8:00";
        }else {
            $hour_strip = explode(":", $hour);
            if(!isset($hour_strip[0])){
                $error = "format is invalid, this must be like 8:00";
            }elseif(!($hour_strip[0] >= 00 && $hour_strip[0] <= 24)){
                $error = "Must be an hour between 00 and 24";
            }elseif(!isset($hour_strip[1])){
                $error = "format is invalid, this must be like 8:00";
            }elseif(!($hour_strip[1] >= 00 && $hour_strip[1] <= 60)){
                $error = "Must be an minute between 00 and 60";
            }else {
                $error = true;
            }
        }
        return $error;
    }
}
