<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make( 
            $request->all(),
            [
                'email' => 'required|email',
                'password' => 'required|string'
            ]
        );
        
        if($validator->fails()){
            return response()->json($validator->errors(),400);
        }

        if (! $token = $this->guard()->setTTL(24*60)->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request){
        $validator = Validator::make(
            $request->all(),
            [
                'name'=> 'required|string|between:2,100',
                'email'=> 'required|email|unique:users',
                'password' => 'required|confirmed|min:6' 
            ]
        );

        if($validator->fails()){
            return response()->json([
                $validator->errors()
            ],422);
        }

        $user = User::create(
            array_merge(
                $validator->validated(),
                ['password' => bcrypt($request->password)]
            )
        );

        return response()->json(['message'=>'User created successfully','user'=> $user]);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        return response()->json($this->guard()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->guard()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60
        ]);
    }//end respondWithToken()

    public function userEndpoint($user_id,$endpoint)
    {
        $user = User::find($user_id);
        return $user->{$endpoint};
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
