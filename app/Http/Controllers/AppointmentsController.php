<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Appointments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AppointmentsController extends Controller
{

    protected $user;


    public function __construct()
    {
        $this->middleware('auth:api');
        $this->user = $this->guard()->user();

    }//end __construct()

     /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */
    public function index(Request $request){

        $validator = Validator::make(
            $request->all(),
            [
                'filter_date_1' => 'required|date',
                'filter_date_2' => 'required|date'
            ]
        );

        if($validator->fails()){
            return response()->json(
                [
                    'status' => false,
                    'errors' => $validator->erros()
                ],
                400
            );
        }

        $appointments = Appointments::join('users', 'users.id', '=', 'appointments.who_will_meet')
        ->join('contacts', 'contacts.id', '=', 'appointments.contact_id')
        ->whereBetween('appointment_date', [$request->filter_date_1, $request->filter_date_2]) 
        ->select('appointments.*', 'contacts.name as contact_name', 'users.name as who_will_meet_name')
        ->get();

        return response()->json($appointments->toArray());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */
    public function availableUser(Request $request){
        $validator = Validator::make(
            $request->all(),
            [
                'appointment_date' => 'required|date',
                'leave_office'     => 'required|date_format:H:i',
                'return_to_office' => 'required|date_format:H:i',
            ]
        );

        if($validator->fails()){
            return response()->json(
                [
                    'status' => false,
                    'errors' => $validator->erros()
                ],
                400
            );
        }
      
        $users = User::whereNotIn("id",function($query) use ($request){
            $query->select('who_will_meet')
                ->from('appointments')
                ->where(function($query) use ($request){
                    $query->where('appointment_date', '=', $request->appointment_date)
                        ->whereNotBetween('leave_office', [$request->leave_office, $request->return_to_office]);  
                })
                ->orWhere('appointment_date', '!=', $request->appointment_date);  
        })
        
        ->get();

        return response()->json($users->toArray());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'appointment_address' => 'required|string',
                'appointment_date' => 'required|date',
                'appointment_time' => 'required|date_format:H:i',
                'who_will_meet' => 'required|int',
                'contact_id' => 'required|int',
                'leave_office' => 'required|date_format:H:i',
                'return_to_office' => 'required|date_format:H:i',
            ]
        );

        if($validator->fails()){
            return response()->json(
                [
                    'status' => false,
                    'errors' => $validator->erros()
                ],
                400
            );
        }

        $appointment = new Appointments();
        $appointment->appointment_address = $request->appointment_address;
        $appointment->appointment_date = $request->appointment_date;
        $appointment->who_will_meet = $request->who_will_meet;
        $appointment->contact_id = $request->contact_id;
        $appointment->appointment_time = $request->appointment_time;
        $appointment->leave_office = $request->leave_office;
        $appointment->return_to_office = $request->return_to_office;
        $appointment->added_at = date("Y-m-d H:i:s");

        if ($this->user->appointments('added_by')->save($appointment)) {
            return response()->json(
                [
                    'status' => true,
                    'contact'   => $appointment,
                ]
            );
        } else {
            return response()->json(
                [
                    'status'  => false,
                    'message' => 'Oops, the appointment could not be saved.',
                ]
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\appointments  $appointments
     * @return \Illuminate\Http\Response
     */
    public function show(appointments $appointments)
    {
        //
    }

   

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\appointments  $appointment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, appointments $appointment)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'appointment_address' => 'required|string',
                'appointment_date' => 'required|date',
                'appointment_time' => 'required|date_format:H:i',
                'who_will_meet' => 'required|int',
                'contact_id' => 'required|int',
                'leave_office' => 'required|date_format:H:i',
                'return_to_office' => 'required|date_format:H:i',
            ]
        );

        if($validator->fails()){
            return response()->json(
                [
                    'status' => false,
                    'errors' => $validator->errors()
                ],
                400
            );
        }

        
        $appointment->appointment_address = $request->appointment_address;
        $appointment->appointment_date = $request->appointment_date;
        $appointment->who_will_meet = $request->who_will_meet;
        $appointment->contact_id = $request->contact_id;
        $appointment->appointment_time = $request->appointment_time;
        $appointment->leave_office = $request->leave_office;
        $appointment->return_to_office = $request->return_to_office;
        $appointment->edited_at = date("Y-m-d H:i:s");
        $appointment->edited = true;

        if ($this->user->appointments('edited_by')->save($appointment)) {
            return response()->json(
                [
                    'status' => true,
                    'appointment'   => $appointment,
                ]
            );
        } else {
            return response()->json(
                [
                    'status'  => false,
                    'message' => 'Oops, the appointment could not be saved.',
                ]
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\appointments  $appointments
     * @return \Illuminate\Http\Response
     */
    public function destroy(appointments $appointment)
    {
        $appointment->deleted_at = date("Y-m-d H:i:s");
        $appointment->delete = true;

        if ($this->user->appointments('deleted_by')->save($appointment)) {
            return response()->json(
                [
                    'status' => true,
                    'appointment'   => $appointment,
                ]
            );
        } else {
            return response()->json(
                [
                    'status'  => false,
                    'message' => 'Oops, the appointment could not be saved.',
                ]
            );
        }
    }


    /**
     * Return guard.
     *
     * @return Illuminate\Support\Facades\Auth\Guard
     */
    protected function guard()
    {
        return Auth::guard();

    }//end guard()


    


}
