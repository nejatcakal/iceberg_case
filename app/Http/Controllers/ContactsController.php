<?php

namespace App\Http\Controllers;

use App\Models\Contacts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class ContactsController extends Controller
{

    protected $user;


    public function __construct()
    {
        $this->middleware('auth:api');
        $this->user = $this->guard()->user();

    }//end __construct()


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $contacts = Contacts::get();
        return response()->json($contacts->toArray());

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
                'name' => 'required|string',
                'email' => 'required|email|unique:contacts',
                'phone' => 'required|string',
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

        $contact = new Contacts();
        $contact->name = $request->name;
        $contact->email = $request->email;
        $contact->phone = $request->phone;
        $contact->added_at = date("Y-m-d H:i:s");
     
        
        if ($this->user->contacts('added_by')->save($contact)) {
            
            return response()->json(
                [
                    'status' => true,
                    'contact'   => $contact,
                ]
            );
        } else {
            return response()->json(
                [
                    'status'  => false,
                    'message' => 'Oops, the contact could not be saved.',
                ]
            );
        }


    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Contacts  $contact
     * @return \Illuminate\Http\Response
     */
    public function show(Contacts $contact)
    {
        return response()->json($contact);
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Contacts  $contacts
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Contacts $contact)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
                'email' => 'required|email|unique:contacts,email,'.$contact->id.",id",
                'phone' => 'required|string',
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

        $contact->name = $request->name;
        $contact->email = $request->email;
        $contact->phone = $request->phone;
        $contact->edited_at = date("Y-m-d H:i:s");
        $contact->edited = true;

        if ($this->user->contacts('edited_by')->save($contact)) {
            return response()->json(
                [
                    'status' => true,
                    'contact'   => $contact,
                ]
            );
        } else {
            return response()->json(
                [
                    'status'  => false,
                    'message' => 'Oops, the contact could not be updated.',
                ]
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Contacts  $contacts
     * @return \Illuminate\Http\Response
     */
    public function destroy(Contacts $contact)
    {
        $contact->deleted_at = date("Y-m-d H:i:s");
        $contact->deleted = true;
        if ($this->user->contacts('deleted_by')->save($contact)) {
            return response()->json(
                [
                    'status' => true,
                    'contact'   => $contact,
                ]
            );
        } else {
            return response()->json(
                [
                    'status'  => false,
                    'message' => 'Oops, the contact could not be deleted.',
                ]
            );
        }

    }


    public function contactEndpoint($contact_id,$endpoint)
    {
        $contact = Contacts::find($contact_id);
        return $contact->{$endpoint};
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
