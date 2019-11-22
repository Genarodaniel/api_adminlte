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
use App\Http\Models\Reserv;

class ReservController extends Controller
{
    public function __construct()
    {
        $this->utensil = new Utensil();
        $this->utensilSchedule = new UtensilSchedule();
        $this->userApp = new User_app();
        $this->usController = new Us();
        $this->reserve = new Reserv();
    }

    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'utensil_id'=>['required','integer'],
                'user_id' => ['integer','required'],
                'day' => ['required','date','after:yesterday'],
                'time'=>['required','date_format:H:i'],
                'hour_start' => ['date_format:H:i','required'],
            ]);

            $date = strtotime(date($request->day . '-' . $request->hour_start));

            if($date < time()){
                return response()->json(['error'=> 'The day date is invalid, need to be future date']);
            }

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
            $tomorrow =  date('N',strtotime('+1 days', strtotime($input['day']) ));
            $hour_end = $this->setHourEnd($input['hour_start'],$input['time']);

            $appointments = $this->usController->listAppointments($input['utensil_id']);

            if(isset($hour_end['another_day']) && $hour_end['another_day']){
                if(!($this->usController->verifyOpen($input['utensil_id'], $day_week) || $this->usController->verifyOpen($input['utensil_id'], $tomorrow))){
                    return response()->json(['error' => 'This utensil is not open for this day']);
                }
                foreach($appointments as $app){
                    if($app['days_work'] == $day_week){
                        $work_start = strtotime($app['work_start']);
                        $hour_start = strtotime($input['hour_start']);
                        $work_end = strtotime($app['work_end']);
                        $day_hour_end = strtotime($hour_end['hour_end']);

                        if($work_start > $hour_start){
                            return response()->json(['error' => 'The hour start must be bigger than work start']);
                        }elseif($work_end < $day_hour_end){
                            return response()->json(['error' => 'The time must be less than work end']);
                        }else{
                            $reservs = $this->listReserv($input['utensil_id'], $input['day']);
                            if($reservs){
                                foreach($reservs as $reserv){
                                    $conflict = $this->getReserve($reserv['id']);
                                    if(strtotime($reserv['hour_start']) >= $hour_start){
                                        if($day_hour_end > strtotime($reserv['hour_start'])){
                                            return response()->json(['error'=> 'reserve conflict with another reserve','reserv'=> $conflict],402);
                                        }else{
                                            $reserve_today = true;
                                            continue;
                                        }
                                    }else{
                                        if($hour_start < $reserv['hour_end']){
                                            return response()->json(['error'=> 'reserve conflict with another reserve','reserv'=> $conflict],402);
                                        }else{
                                            $reserve_today = true;
                                            continue;
                                        }
                                    }
                                }
                            }else{
                                $reserve_today = true;
                            }
                        }
                    }else {
                        $response = ['error'=>  'This utensil doesn\'t work this day'];
                    }
                    if($app['days_work'] == $tomorrow){
                        $work_start = strtotime($app['work_start']);
                        $hour_start = strtotime($hour_end['another_start']);
                        $work_end = strtotime($app['work_end']);
                        $day_hour_end = strtotime($hour_end['another_end']);
                        $another_day = date('Y-m-d',strtotime('+1 days' , strtotime($input['day'])));

                        if($work_start > $hour_start){
                            return response()->json(['error' => 'The hour start must be bigger than work start']);
                        }elseif($work_end < $day_hour_end){
                            return response()->json(['error' => 'The time must be less than work end']);
                        }else{
                            $reservs = $this->listReserv($input['utensil_id'], $another_day);
                            if($reservs){
                                foreach($reservs as $reserv){
                                    $conflict = $this->getReserve($reserv['id']);
                                    if(strtotime($reserv['hour_start']) >= $hour_start){
                                        if($day_hour_end > strtotime($reserv['hour_start'])){
                                            return response()->json(['error'=> 'reserve conflict with another reserve','reserv'=> $conflict],402);
                                        }else{
                                            $reserve_another = true;
                                            continue;
                                        }
                                    }else{
                                        if($hour_start < $reserv['hour_end']){
                                            return response()->json(['error'=> 'reserve conflict with another reserve','reserv'=> $conflict],402);
                                        }else{
                                            $reserve_another = true;
                                            continue;
                                        }
                                    }
                                }
                            }else{
                                $reserve_another = true;
                            }
                        }
                    }else {
                        $response = ['error'=>  'This utensil doesn\'t work this day'];
                    }
                }
                if($reserve_today && $reserve_another){
                    $input['vinculated'] = 1;
                    $reserv = $this->buildReserv($hour_end['hour_end'], $input);
                    $reserv[] = $this->reserve->create($reserv);


                    $another['hour_start'] = $hour_end['another_start'];
                    $another['time'] = $hour_end['time'];
                    $another['utensil_id'] = $input['utensil_id'];
                    $another['user_id'] = $input['user_id'];
                    $another['day'] = $another_day;
                    $another['vinculated'] = $reserv[0]['id'];
                    $reserve_another = $this->buildReserv($hour_end['another_end'],$another);

                    $reserv[] = $this->reserve->create($reserve_another);
                    $data[] = $this->getReserve($reserv[0]['id']);
                    $data[] = $this->getReserve($reserv[1]['id']);
                    $json['success'] = true;
                    $json['reserves'][] = $this->buildJson($data[0],$reserv[0]);
                    $json['reserves'][] = $this->buildJson($data[1],$reserv[1]);
                    return response()->json($json,200);
                }
                return response()->json($response,402);

            }else {
                if(!$this->usController->verifyOpen($input['utensil_id'], $day_week)){
                    return response()->json(['error' => 'This utensil is not open for this day']);
                }

                foreach($appointments as $app){
                    if($app['days_work'] == $day_week){
                        $work_start = strtotime($app['work_start']);
                        $hour_start = strtotime($input['hour_start']);
                        $work_end = strtotime($app['work_end']);
                        $day_hour_end = strtotime($hour_end['hour_end']);

                        if($work_start > $hour_start){
                            return response()->json(['error' => 'The hour start must be bigger than work start']);
                        }elseif($work_end < $day_hour_end){
                            return response()->json(['error' => 'This utensil is already close']);
                        }else{
                            $reservs = $this->listReserv($input['utensil_id'], $input['day']);
                            if($reservs){
                                foreach($reservs as $reserv){
                                    $conflict = $this->getReserve($reserv['id']);
                                    if(strtotime($reserv['hour_start']) >= $hour_start){
                                        if($day_hour_end > strtotime($reserv['hour_start'])){
                                            return response()->json(['error'=> 'reserve conflict with another reserve','reserv'=> $conflict],402);
                                        }else{
                                            continue;
                                        }
                                    }else{
                                        if($hour_start < $reserv['hour_end']){
                                            return response()->json(['error'=> 'reserve conflict with another reserve','reserv'=> $conflict],402);
                                        }else{
                                            continue;
                                        }
                                    }
                                }
                                $input['vinculated'] = 0;
                                $reserv = $this->buildReserv($hour_end['hour_end'], $input);
                                $reserv = $this->reserve->create($reserv);
                                $data = $this->getReserve($reserv['id']);
                                $json['success'] = true;
                                $json['reserves'][] = $this->buildJson($data,$reserv);
                                return response()->json($json,200);
                            }else{
                                $input['vinculated'] = 0;
                                $reserv = $this->buildReserv($hour_end['hour_end'], $input);
                                $reserv = $this->reserve->create($reserv);
                                $data = $this->getReserve($reserv['id']);
                                $json['success'] = true;
                                $json['reserves'][] = $this->buildJson($data,$reserv);
                                return response()->json($json,200);
                            }
                        }
                    }else {
                        $response = ['error'=>  'This utensil doesn\'t work this day'];
                    }
                }
                return response()->json($response,402);
            }
        }catch (\Exception $e) {
            if(config('app.debug')) {
                return response()->json(ApiError::errorMessage($e->getMessage(), 402));
            }
            return response()->json(ApiError::errorMessage('Sorry, an error occurred while processing', 402));
        }

    }

    public function update(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'reserve_id' => ['required','integer'],
                'day' => ['required','date','after:yesterday'],
                'time'=>['required','date_format:H:i'],
                'hour_start' => ['date_format:H:i','required'],
            ]);

            $date = strtotime(date($request->day . '-' . $request->hour_start));

            if($date < time()){
                return response()->json(['error'=> 'The day date is invalid, need to be future date']);
            }

            if($validator->fails()) {
                return response()->json(['error' => $validator->errors()],402);
            }

            $input = $request->all();
            $reserve = $this->reserve->find($input['reserve_id']);

            if(!$reserve){
                return response()->json(['error' => 'Reserva não existe'],402);
            }

            $reserve = $this->reserve->find($input['reserve_id']);

            $day_week = date('N',strtotime($input['day']));
            $tomorrow =  date('N',strtotime('+1 days', strtotime($input['day']) ));
            $hour_end = $this->setHourEnd($input['hour_start'],$input['time']);

            $appointments = $this->usController->listAppointments($reserve['utensil_id']);

            if(isset($hour_end['another_day']) && $hour_end['another_day']){
                if(!($this->usController->verifyOpen($reserve['utensil_id'], $day_week) || $this->usController->verifyOpen($reserve['utensil_id'], $tomorrow))){
                    return response()->json(['error' => 'This utensil is not open for this day']);
                }
                foreach($appointments as $app){
                    if($app['days_work'] == $day_week){
                        $work_start = strtotime($app['work_start']);
                        $hour_start = strtotime($input['hour_start']);
                        $work_end = strtotime($app['work_end']);
                        $day_hour_end = strtotime($hour_end['hour_end']);

                        if($work_start > $hour_start){
                            return response()->json(['error' => 'The hour start must be bigger than work start']);
                        }elseif($work_end < $day_hour_end){
                            return response()->json(['error' => 'The time must be less than work end']);
                        }else{
                            $reservs = $this->listReserv($reserve['utensil_id'], $input['day']);
                            if($reservs){
                                foreach($reservs as $reserv){
                                    if($reserv['id'] != $input['reserve_id']){
                                        $conflict = $this->getReserve($reserv['id']);
                                        if(strtotime($reserv['hour_start']) >= $hour_start){
                                            if($day_hour_end > strtotime($reserv['hour_start'])){
                                                return response()->json(['error'=> 'reserve conflict with another reserve','reserv'=> $conflict],402);
                                            }else{
                                                $reserve_today = true;
                                                continue;
                                            }
                                        }else{
                                            if($hour_start < $reserv['hour_end']){
                                                return response()->json(['error'=> 'reserve conflict with another reserve','reserv'=> $conflict],402);
                                            }else{
                                                $reserve_today = true;
                                                continue;
                                            }
                                        }
                                    }
                                    $reserve_today = true;
                                    continue;
                                }
                            }else{
                                $reserve_today = true;
                            }
                        }
                    }else {
                        $response = ['error'=>  'This utensil doesn\'t work this day'];
                    }
                    if($app['days_work'] == $tomorrow){
                        $work_start = strtotime($app['work_start']);
                        $hour_start = strtotime($hour_end['another_start']);
                        $work_end = strtotime($app['work_end']);
                        $day_hour_end = strtotime($hour_end['another_end']);
                        $another_day = date('Y-m-d',strtotime('+1 days' , strtotime($input['day'])));

                        if($work_start > $hour_start){
                            return response()->json(['error' => 'The hour start must be bigger than work start']);
                        }elseif($work_end < $day_hour_end){
                            return response()->json(['error' => 'The time must be less than work end']);
                        }else{
                            $reservs = $this->listReserv($reserve['utensil_id'], $another_day);
                            if($reservs){
                                foreach($reservs as $reserv){
                                    if($reserv['vinculated'] != $reserve['id']){
                                        $conflict = $this->getReserve($reserv['id']);
                                        if(strtotime($reserv['hour_start']) >= $hour_start){
                                            if($day_hour_end > strtotime($reserv['hour_start'])){
                                                return response()->json(['error'=> 'reserve conflict with another reserve','reserv'=> $conflict],402);
                                            }else{
                                                $reserve_another = true;
                                                continue;
                                            }
                                        }else{
                                            if($hour_start < $reserv['hour_end']){
                                                return response()->json(['error'=> 'reserve conflict with another reserve','reserv'=> $conflict],402);
                                            }else{
                                                $reserve_another = true;
                                                continue;
                                            }
                                        }
                                    }else{
                                        $this->reserve->where('vinculated', $reserve->id)->delete();
                                        $reserve_another = true;
                                        continue;
                                    }
                                }
                            }else{
                                $reserve_another = true;
                            }
                        }
                    }else {
                        $response = ['error'=>  'This utensil doesn\'t work this day'];
                    }
                }
                if($reserve_today && $reserve_another){
                    $reservas = $this->listReservUser($reserve->user_id);
                        foreach($reservas as $reserv){
                            if($reserv['vinculated'] == $reserve->id){
                               $this->reserve->where('vinculated', $reserve->id)->delete();
                            }
                        }
                    $reserve->hour_end = $hour_end['hour_end'];
                    $reserve->hour_start = $input['hour_start'];
                    $reserve->time = date('H:i',(strtotime($input ['time']) - strtotime($hour_end['time'])));
                    $reserve->day = $input['day'];
                    $reserve->vinculated = 1;
                    $reserve->save();

                    $another['hour_start'] = $hour_end['another_start'];
                    $another['time'] = $hour_end['time'];
                    $another['utensil_id'] = $reserve['utensil_id'];
                    $another['user_id'] = $reserve['user_id'];
                    $another['day'] = $another_day;
                    $another['vinculated'] = $reserve->id;
                    $reserve_another = $this->buildReserv($hour_end['another_end'], $another);
                    $reserve_another = $this->reserve->create($reserve_another);

                    $data[] = $this->getReserve($reserve->id);
                    $data[] = $this->getReserve($reserve_another->id);
                    $json['success'] = true;
                    $json['reserves'][] = $this->buildJson($data[0],$reserve);
                    $json['reserves'][] = $this->buildJson($data[1],$reserve_another);
                    return response()->json($json,200);
                }
                return response()->json($response,402);

            }else {
                if(!$this->usController->verifyOpen($reserve['utensil_id'], $day_week)){
                    return response()->json(['error' => 'This utensil is not open for this day']);
                }

                foreach($appointments as $app){
                    if($app['days_work'] == $day_week){
                        $work_start = strtotime($app['work_start']);
                        $hour_start = strtotime($input['hour_start']);
                        $work_end = strtotime($app['work_end']);
                        $day_hour_end = strtotime($hour_end['hour_end']);

                        if($work_start > $hour_start){
                            return response()->json(['error' => 'The hour start must be bigger than work start']);
                        }elseif($work_end < $day_hour_end){
                            return response()->json(['error' => 'This utensil is already close']);
                        }else{
                            $reservs = $this->listReserv($reserve['utensil_id'], $input['day']);
                            $reservas = $this->listReservUser($reserve->user_id);
                            if($reservs){
                                foreach($reservs as $reserv){
                                    if($reserv['id'] != $input['reserve_id']){
                                        if($reserv['vinculated'] != $reserve->id){
                                            $conflict = $this->getReserve($reserv['id']);
                                            if(strtotime($reserv['hour_start']) >= $hour_start){
                                                if($day_hour_end > strtotime($reserv['hour_start'])){
                                                    return response()->json(['error'=> 'reserve conflict with another reserve','reserv'=> $conflict],402);
                                                }else{
                                                    continue;
                                                }
                                            }else{
                                                if($hour_start < $reserv['hour_end']){
                                                    return response()->json(['error'=> 'reserve conflict with another reserve','reserv'=> $conflict],402);
                                                }else{
                                                    continue;
                                                }
                                            }
                                        }else{
                                            $this->reserve->where('vinculated', $reserve->id)->delete();
                                            continue;
                                        }
                                    }
                                }

                                $reservas = $this->listReservUser($reserve->user_id);
                                foreach($reservas as $reserv){
                                    if($reserv['vinculated'] == $reserve->id){
                                        $this->reserve->where('vinculated', $reserve->id)->delete();
                                    }
                                }
                                $reserve->hour_end = $hour_end['hour_end'];
                                $reserve->hour_start = $input['hour_start'];
                                $reserve->time = $input['time'];
                                $reserve->day = $input['day'];
                                $reserve->vinculated = 0;
                                $reserve->save();
                                $data = $this->getReserve($reserve->id);
                                $json['success'] = true;
                                $json['reserves'][] = $this->buildJson($data,$reserve);
                                return response()->json($json,200);
                            }else{

                                $reservas = $this->listReservUser($reserve->user_id);
                                foreach($reservas as $reserv){
                                    if($reserv['vinculated'] == $reserve->id){
                                        $this->reserve->where('vinculated', $reserve->id)->delete();
                                    }
                                }
                                $reserve->hour_end = $hour_end['hour_end'];
                                $reserve->hour_start = $input['hour_start'];
                                $reserve->time = $input['time'];
                                $reserve->day = $input['day'];
                                $reserve->vinculated = 0;
                                $reserve->save();
                                $data = $this->getReserve($reserve->id);
                                $json['success'] = true;
                                $json['reserves'][] = $this->buildJson($data,$reserve);
                                return response()->json($json,200);
                            }
                        }
                    }else {
                        $response = ['error'=>  'This utensil doesn\'t work this day'];
                    }
                }
                return response()->json($response,402);
            }
        }catch (\Exception $e) {
            if(config('app.debug')) {
                return response()->json(ApiError::errorMessage($e->getMessage(), 402));
            }

            return response()->json(ApiError::errorMessage('Sorry, an error occurred while processing', 402));
        }
    }

    public function listReserv($utensil_id, $day)
    {
        if($this->reserve->where('utensil_id', $utensil_id)->where('day',$day)->exists()){
            $reservs = $this->reserve->where('utensil_id', $utensil_id)->where('day',$day)->join('user_apps', 'reserv.user_id', '=', 'user_apps.id')->select('hour_start','hour_end','user_apps.email','user_apps.name','reserv.id','vinculated')->get();
        }else{
            $reservs = false;
        }

        return json_decode(json_encode($reservs),true);
    }

    public function buildUpReserve($hour_end, $input, $reserve)
    {
        $reserv['day'] = $input['day'];
        $reserv['user_id'] = $reserve['user_id'];
        $reserv['utensil_id'] = $reserve['utensil_id'];
        $reserv['hour_start'] = $input['hour_start'];
        $reserv['hour_end'] = $hour_end;
        $reserv['time'] = $input['time'];
        $reserv['created_at'] = now();
        $reserv['updated_at'] = now();
        return $reserv;

    }

    public function delete($id)
    {
        $reserve = $this->reserve->find($id);
        if($reserve){
            if($reserve->vinculated == 1){
                $reservas = $this->listReservUser($reserve->user_id);
                    foreach($reservas as $reserv){
                        if($reserv['vinculated'] == $reserve->id){
                            $this->reserve->where('vinculated', $reserve->id)->delete();
                        }
                    }
            }
            $this->reserve->where('id',$id)->delete();
            return response()->json(['success' => true], 200);
        }else{
            return response()->json(['error', 'Reserva não existe'],402);
        }
    }

    public function listReservUser($user_id){
        if($this->reserve->where('user_id', $user_id)->exists()){
            $reservs = $this->reserve->where('user_id', $user_id)->get();
        }else{
            $reservs = false;
        }

        return json_decode(json_encode($reservs),true);
    }

    public function buildReserv($hour_end,$request)
    {
        $reserv['day'] = $request['day'];
        $reserv['user_id'] = $request['user_id'];
        $reserv['utensil_id'] = $request['utensil_id'];
        $reserv['hour_start'] = trim($request['hour_start']);
        $reserv['hour_end'] = $hour_end;
        $reserv['time'] = $request['time'];
        $reserv['created_at'] = now();
        $reserv['updated_at'] = now();
        $reserv['vinculated'] = $request['vinculated'];
        return $reserv;
    }

    public function buildJson($data, $reserv)
    {
        $json['success'] = true;
        $json['reserve_id'] = $reserv['id'];
        $json['user']['email'] = $data['email'];
        $json['user']['username'] = $data['name'];
        $json['hour_start'] = $data['hour_start'];
        $json['hour_end'] = $data['hour_end'];
        $json['day'] = $data['day'];
        $json['vinculated'] = $reserv['vinculated'];
        return $json;
    }

    public function getReserve($reserve_id)
    {
        if($this->reserve->find($reserve_id)){
            $reserv = $this->reserve->where('reserv.id',$reserve_id)->join('user_apps', 'reserv.user_id', '=', 'user_apps.id')->select('hour_start','hour_end','user_apps.email','user_apps.name','day','vinculated')->get();
            return json_decode(json_encode($reserv), true)[0];
        }else {
            return false;
        }
    }

    public function listByDate(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'utensil_id' => ['required','integer'],
                'day' => ['required','date']
            ]);

            if($validator->fails()) {
                return response()->json(['error' => $validator->errors()],402);
            }

            $reservs_exists = $this->reserve->where('utensil_id', $request->utensil_id)->where('day',$request->day)->join('user_apps', 'reserv.user_id', '=', 'user_apps.id')->select('hour_start','hour_end','user_apps.email','user_apps.name')->exists();

            $reservs = $this->reserve->where('utensil_id', $request->utensil_id)->where('day',$request->day)->join('user_apps', 'reserv.user_id', '=', 'user_apps.id')->select('hour_start','hour_end','user_apps.email','user_apps.name')->get();

            if($reservs_exists){
                return response()->json($reservs);
            }else {
                return response()->json(['success' => false, 'error' => 'doesn\'t have reserves this day']);
            }

        }catch (\Exception $e) {
            if(config('app.debug')) {
                return response()->json(ApiError::errorMessage($e->getMessage(), 402));
            }

            return response()->json(ApiError::errorMessage('Sorry, an error occurred while processing', 402));
        }
    }

    public function listByUser(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => ['required','integer']
            ]);

            if($validator->fails()) {
                return response()->json(['error' => $validator->errors()],402);
            }

            $reservs_exists = $this->reserve->where('user_id', $request->user_id)->join('user_apps', 'reserv.user_id', '=', 'user_apps.id')->select('hour_start','hour_end','user_apps.email','user_apps.name')->exists();

            $reservs = $this->reserve->where('user_id', $request->user_id)->join('user_apps', 'reserv.user_id', '=', 'user_apps.id')->select('hour_start','hour_end','user_apps.email','user_apps.name')->get();

            if($reservs_exists){
                return response()->json($reservs);
            }else {
                return response()->json(['success' => false, 'error' => 'doesn\'t have reserves for this user']);
            }


        }catch (\Exception $e) {
            if(config('app.debug')) {
                return response()->json(ApiError::errorMessage($e->getMessage(), 402));
            }

            return response()->json(ApiError::errorMessage('Sorry, an error occurred while processing', 402));
        }
    }

    public function listByUtensil(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'utensil_id' => ['required','integer']
            ]);

            if($validator->fails()) {
                return response()->json(['error' => $validator->errors()],402);
            }

            $reservs_exists = $this->reserve->where('utensil_id', $request->utensil_id)->join('user_apps', 'reserv.user_id', '=', 'user_apps.id')->select('hour_start','hour_end','user_apps.email','user_apps.name')->exists();

            $reservs = $this->reserve->where('utensil_id', $request->utensil_id)->join('user_apps', 'reserv.utensil_id', '=', 'user_apps.id')->select('hour_start','hour_end','user_apps.email','user_apps.name')->get();

            if($reservs_exists){
                return response()->json($reservs);
            }else {
                return response()->json(['success' => false, 'error' => 'doesn\'t have reserves for this utensil']);
            }


        }catch (\Exception $e) {
            if(config('app.debug')) {
                return response()->json(ApiError::errorMessage($e->getMessage(), 402));
            }

            return response()->json(ApiError::errorMessage('Sorry, an error occurred while processing', 402));
        }
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

                $end = date("H:i", strtotime($hour) + strtotime($time));

                if($end != "00:00"){
                    $return['another_day'] = true;
                    $return['hour_end'] = '23:59';
                    $return['another_start'] = '00:00';
                    $return['another_end'] = date("H:i", strtotime($hour) + strtotime($time));
                    $return['time'] = date('H:i',(mktime(date('H',strtotime($return['another_end'])),date('I',strtotime($return['another_end']))) - mktime(date('H',strtotime($return['another_start'])),date('I',strtotime($return['another_start']))))) ;
                }else{
                    $return['another_day'] = false;
                    $return['hour_end'] = '23:59';

                }
            }else {
                $return['another_day'] = false;
                $return['hour_end'] = date("H:i", strtotime($hour) + strtotime($time));
            }
        }else{
            $hour_new = $hour_strip[0] + $hour_end_strip[0];

            if($hour_new >= 24 ){
                $diff = $hour_new - 24 ;

                if(strlen($diff) < 2){
                    $diff = '0' . $diff;
                }

                $end = date("H:i", strtotime($hour) + strtotime($time));

                if($end != "00:00"){
                    $return['another_day'] = true;
                    $return['hour_end'] = '23:59';
                    $return['another_start'] = '00:00';
                    $return['another_end'] = date("H:i", strtotime($hour) + strtotime($time));
                    $return['time'] = date('H:i',(mktime(date('H',strtotime($return['another_end'])),date('I',strtotime($return['another_end']))) - mktime(date('H',strtotime($return['another_start'])),date('I',strtotime($return['another_start']))))) ;
                }else {
                    $return['another_day'] = false;
                    $return['hour_end'] = '23:59';
                }
            }else {
                $return['another_day'] = false;
                $return['hour_end'] = date("H:i", strtotime($hour) + strtotime($time));
            }
        }

        return $return;
    }
}
