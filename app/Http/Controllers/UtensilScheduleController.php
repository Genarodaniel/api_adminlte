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

    public function __construct(Utensil $utensil)
    {
        $this->utensil = $utensil;
        $this->utensilSchedule = new UtensilSchedule();
        $this->successStatus = 200;
    }
    public function store(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'utensil_id'=>['required','integer'],
                'days' => ['required','array'],
                'days.*'=>['array',new day],
                'days.*.work_start' => ['string','required'],
                'days.*.work_end' => ['string','required'],
                'days.*.max_time' => ['numeric','required']
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
                        return response()->json(['error' => 'The schedule for days work '. $days . ' already exists']);
                    }

                    if($days < 1 || $days > 7){
                        return response()->json(['error' => "The day  must be between 1 and 7"]);
                    }

                    $validateStart = $this->validateHour($day['work_start']);

                    if($validateStart !== true){
                        return response()->json(['error' => " The day " .$days .  " work start" . $validateStart]);
                    }

                    $validateEnd = $this->validateHour($day['work_end']);

                    if($validateEnd !== true){
                        return response()->json(['error' => " The day " .$days .  " work start" . $validateEnd]);
                    }

                    $verifyHour = $this->verifyHour($day['work_start'], $day['work_end'], $day['max_time']);

                    if($verifyHour !== true){
                        return response()->json(['error' =>"The day " . $days . " " . $verifyHour]);
                    }

                    $utensilSchedule['days_work'] = $days;
                    $utensilSchedule['utensil_id'] = trim($request['utensil_id']);
                    $utensilSchedule['work_start'] = trim($day['work_start']);
                    $utensilSchedule['work_end'] = trim($day['work_end']);
                    $utensilSchedule['max_time'] = trim($day['max_time']);
                    $utensilSchedule['created_at'] = now();
                    $utensilSchedule['updated_at'] = now();
                    $add = [];
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
                'days' => ['required','array'],
                'days.*'=>['array',new day],
                'days.*.work_start' => ['string','required'],
                'days.*.work_end' => ['string','required'],
                'days.*.max_time' => ['numeric','required']
            ]);


            if($validator->fails()) {
                return response()->json(['error' => $validator->errors()],402);
            }
            $input = $request->all();

            foreach($input['days'] as $days => $day) {

                $schedule_exists = $this->utensilSchedule->where(
                    'utensil_id', '=',$input['utensil_id'])->where(
                    'days_work', '=', $days)->exists();

                if(!$schedule_exists){
                    return response()->json(['error' => 'The schedule for days work '. $days . ' doenst exists']);
                }

                if($days < 1 || $days > 7){
                    return response()->json(['error' => "The day  must be between 1 and 7"]);
                }

                $validateStart = $this->validateHour($day['work_start']);

                if($validateStart !== true){
                    return response()->json(['error' => " The day " .$days .  " work start" . $validateStart]);
                }

                $validateEnd = $this->validateHour($day['work_end']);

                if($validateEnd !== true){
                    return response()->json(['error' => " The day " .$days .  " work start" . $validateEnd]);
                }

                $verifyHour = $this->verifyHour($day['work_start'], $day['work_end'], $day['max_time']);

                if($verifyHour !== true){
                    return response()->json(['error' =>"The day " . $days . " " . $verifyHour]);
                }

                $utensilSchedule['days_work'] = $days;
                $utensilSchedule['utensil_id'] = trim($request['utensil_id']);
                $utensilSchedule['work_start'] = trim($day['work_start']);
                $utensilSchedule['work_end'] = trim($day['work_end']);
                $utensilSchedule['max_time'] = trim($day['max_time']);
                $utensilSchedule['created_at'] = now();
                $utensilSchedule['updated_at'] = now();
                $add = [];
                $add[] = $days;

                $data = $this->utensilSchedule->create($utensilSchedule);

        }
        $saves = implode(',',$add);
        return response()->json(['success' => 'Days ' . $saves ],$this->successStatus);

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
        $hour_start = (int)$striped_start[0];
        $hour_end = (int)$striped_end[0];
        $minute_start = (int)$striped_start[1];
        $minute_end = (int)$striped_end[1];

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
        }elseif($hour_start > $hour_end){
            $error =  "Work start is bigger than end";
            return $error;
        }elseif($max_time >= 1){
            if(($hour_end - $hour_start) < $max_time){
                $error =  "The hour interval must be bigger than max time";
                return $error;
            }
        }elseif($hour_start == $hour_end){
            if($minute_start > $minute_end){
                $error =  "Minute start is bigger than end";
                return $error;

            }elseif(($minute_end - $minute_start) < $max_time){
                $error =  "The minute interval must be bigger than max time";
                return $error;
            }
        }
        return true;

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