<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Models\Utensil;
use App\Http\Models\UtensilSchedule;
use App\Http\Models\User_app;
use Validator;
use App\API\ApiError;
use App\Http\Controllers\UtensilScheduleController as Us;


class ReservController extends Controller
{
    public function __construct()
    {
        $this->utensil = new Utensil();
        $this->utensilSchedule = new UtensilSchedule();
        $this->userApp = new User_app();
        $this->usController = new Us();
    }

    public function store(Request $request)
    {
        //try{
            $validator = Validator::make($request->all(), [
                'utensil_id'=>['required','integer'],
                'user_id' => ['integer','required'],
                'day' => ['required','date'],
                'time'=>['required','string'],
                'hour_start' => ['string','required'],
            ]);

            if($validator->fails()) {
                return response()->json(['error' => $validator->errors()],402);
            }

            $input = $request->all();
            $utensil_exists = $this->utensil->find($input['utensil_id']);
            $user_exists = $this->userApp->find($input['user_id']);

            if(!(isset($utensil_exists) && $utensil_exists)){
                return response()->json(['error' => 'utensil doens\'t exists']);
            }

            if(!(isset($user_exists) && $user_exists)){
                return response()->json(['error' => 'user doens\'t exists']);
            }

            $day_week = date('N',strtotime($input['day']));

            if(!$this->usController->verifyOpen($input['utensil_id'], $day_week)){
                return response()->json(['error' => 'This utensil is not open for this day']);
            }

            $validateHour = $this->validateHour($input['hour_start']);

            if($validateHour !== true){
                return response()->json(['error' =>"This day hour_start" . $validateHour]);
            }

            $validateTime = $this->validateHour($input['time']);

            if($validateHour !== true){
                return response()->json(['error' =>"This day time " . $validateTime]);
            }

            $hour_end = $this->setHourEnd($input['hour_start'],$input['time']);


            $appointments = $this->usController->listAppointments($input['utensil_id']);

            if(isset($hour_end['another_day']) && $hour_end['another_day']){

            }else {
                foreach($appointments as $app){
                    if(!$app['days_work'] == $day_week){
                        $work = false;
                    }else {
                        $work_start = strtotime($app['work_start']);
                        $hour_start = strtotime($input['hour_start']);
                        $work_end = strtotime($app['work_end']);
                        $hour_end = strtotime($hour_end['hour_end']);
                        $work = true;

                        if($work_start < $hour_start){
                            return response()->json(['error' => 'The hour start must be bigger than work start']);
                        }elseif($work_end < $hour_end){
                            return response()->json(['error' => 'The time must be less than work end']);
                        }elseif()

                    }
                }

            }
            // foreach($appointments as $app){
            //     if($input[''])
            // }die;



        // }catch (\Exception $e) {
        //     if(config('app.debug')) {
        //         return response()->json(ApiError::errorMessage($e->getMessage(), 402));
        //     }
        //     return response()->json(ApiError::errorMessage('Sorry, an error occurred while processing', 402));
        // }
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

    public function setHourEnd($hour, $time)
    {
        $hour_strip = explode(":", $hour);
        $hour_end_strip = explode(':', $time);
        $return = [];
        $minute_new = $hour_strip[1] + $hour_end_strip[1];

        if(strlen($minute_new) < 2){
            $minute_new = '0' . $minute_new;
        }

        if($minute_new >= 60){
            $diff = $minute_new - 60 ;
            $hour_strip[0] ++;

            if(strlen($diff) < 2){
                $diff = '0' . $diff;
            }

            $minute_new = $diff;
            $hour_new = $hour_strip[0] + $hour_end_strip[0];

            if($hour_new >= 24 ){
                $diff = $hour_new - 24 ;
                $return['another_day'] = true;
                $return['hour_end'] = '23:59';
                $return['another_start'] = '00:00';
                $return['another_end'] = $diff . ':' . $minute_new;
            }else {
                $return['another_day'] = false;
                $return['hour_end'] = $hour_new . ':' . $minute_new;
            }
        }else{
            $hour_new = $hour_strip[0] + $hour_end_strip[0];

            if($hour_new >= 24 ){
                $diff = $hour_new - 24 ;

                if(strlen($diff) < 2){
                    $diff = '0' . $diff;
                }

                $return['another_day'] = true;
                $return['hour_end'] = '23:59';
                $return['another_start'] = '00:00';
                $return['another_end'] = $diff . ':' . $minute_new;
            }else {
                $return['another_day'] = false;
                $return['hour_end'] = $hour_new . ':' . $minute_new;
            }
        }

        return $return;
    }
}
