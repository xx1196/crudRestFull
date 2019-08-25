<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\ApiController;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Mail\UserCreatedMail;
use App\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UserController extends ApiController
{

    /**
     * Display a listing of the resource.
     *
     * @return User[]
     */
    public function index()
    {
        $users = User::all();

        if ($users->count()) {
            return $this->showAll($users);
        } else {
            return $this->showNone();
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param UserStoreRequest $request
     * @return Response
     */
    public function store(UserStoreRequest $request)
    {
        $fields = $request->all();
        $fields['verified'] = User::USER_NOT_VERIFIED;
        $fields['verified_token'] = User::generateVerifiedToken();
        $fields['admin'] = User::USER_REGULAR;

        $user = User::create(
            $fields
        );

        return response(
            [
                'data' => $user,
                'message' => "El usuario $user->name se ha creado con éxito",
            ], 201
        );
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     * @return JsonResponse
     */
    public function show(User $user)
    {
        return $this->showOne($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UserUpdateRequest $request
     * @param User $user
     * @return JsonResponse
     */
    public function update(UserUpdateRequest $request, User $user)
    {
        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email') && $user->email != $request->email) {
            $user->verified = User::USER_NOT_VERIFIED;
            $user->verified_token = User::generateVerifiedToken();
            $user->email = $request->email;
        }

        if ($request->has('password')) {
            $user->password = $request->password;
        }

        if ($request->has('admin')) {
            if (!$user->isVerified()) {
                return $this->errorResponse('Unicamente los usuarios verificados pueden ser administradores', 409);
            }
            $user->admin = $request->admin;
        }

        if (!$user->isDirty()) {
            return $this->errorResponse("No se encuentra cambios para el usuario $user->name", 422);
        }

        $user->save();

        return $this->showOne($user, "El usuario $user->name se ha modificado con éxito");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @return JsonResponse
     * @throws \Exception
     */
    public function destroy($user)
    {
        $user = User::withTrashed()
            ->whereId($user)
            ->first();

        $user->forceDelete();

        return $this->showOne($user, "El usuario $user->name se ha eliminado de forma permanente con éxito");
    }

    /**
     * deactivated the specified resource from storage.
     *
     * @param User $user
     * @return JsonResponse
     * @throws Exception
     */
    public function deactivated(User $user)
    {
        $user->delete();

        return $this->showOne($user, "El usuario $user->name se ha desactivado con éxito");
    }

    /**
     * Display a listing of the resource.
     *
     * @return User[]
     */
    public function deactivatedUsers()
    {
        $users = User::onlyTrashed()->get();

        if ($users->count()) {
            return $this->showAll($users);
        } else {
            return $this->showNone();
        }
    }

    /**
     * activated the specified resource from storage.
     *
     * @param User $user
     * @return JsonResponse
     * @throws Exception
     */
    public function activated($user)
    {
        $user = User::onlyTrashed()
            ->whereId($user)
            ->first();

        $user->restore();

        return $this->showOne($user, "El usuario $user->name se ha activado con éxito");
    }

    /**
     *
     * change the verified value of a user who coins with his token.
     *
     * @param $token
     * @return JsonResponse
     */
    public function verify($token)
    {
        $user = User::whereVerifiedToken($token)->firstOrFail();
        $user->verified = User::USER_VERIFIED;
        $user->verified_token = null;
        $user->save();

        return $this->showMessage("La cuenta ha sido verificada");
    }

    /**
     *
     * resend the verification email.
     *
     * @param User $user
     * @return JsonResponse
     * @throws Exception
     */
    public function resend(User $user)
    {
        if ($user->isVerified())
            return $this->errorResponse("el usuario $user->name ya está verificado", 409);

        retry(5, function () use ($user) {
            Mail::to($user)->send(new UserCreatedMail($user));
        },
            100
        );

        return $this->showMessage("EL correo de verificación se ha reenviado");
    }
    /**
     * Register api
     *
     * @return Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];
            return response()->json($response, 404);
        }

        $fields = $request->all();
        $fields['verified'] = User::USER_NOT_VERIFIED;
        $fields['verified_token'] = User::generateVerifiedToken();
        $fields['admin'] = User::USER_REGULAR;
        $user = User::create($fields);
        $success['token'] = $user->createToken('MyApp')->accessToken;
        $success['name'] = $user->name;

        $response = [
            'success' => true,
            'data' => $success,
            'message' => 'User register successfully.'
        ];

        return response()->json($response, 200);
    }

    /**
     * Login api
     *
     * @return Response
     */
    public function login()
    {
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
            $user = Auth::user();
            $userTokens = $user->tokens()->whereRevoked(0)->get();
            if ($userTokens) {
                foreach ($userTokens as $token) {
                    $token->revoke();
                }
            }
            $success['token'] = $user->createToken('MyApp')->accessToken;

            return response()->json(['success' => $success], 200);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
}
