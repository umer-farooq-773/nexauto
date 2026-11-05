<?php

namespace App\Http\Controllers;

use App\Constants\Constants;
use App\Dto\UserDto;
use App\Http\Requests\User\UserRequest;
use Illuminate\Http\Request;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Interface\UserInterface;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return $reauest, $modal
     */
    public function __construct(UserInterface $repo)
    {
        $this->setRepo($repo, "auth/pages/users", "users");
    }

    public function index()
    {
        $data['all'] = $this->_repo->get_all_users();
        return view($this->_directory . '.all', compact('data'));
    }

    public function driver()
    {
        try {
            $data['all'] = $this->_repo->getStaff(Constants::USER);
            return view($this->_directory . '.driver', compact('data'));

        } catch (\Exception $e) {
            return $this->handleError($e);
        }
    }

    private function handleError(\Exception $e)
    {
        Log::error('Error in ' . __METHOD__ . ': ' . $e->getMessage());

        $errorMessage = $this->getUserFriendlyMessage($e);
        $statusCode = $e->getCode() ?: 500;

        if (request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'debug' => config('app.debug') ? $e->getTraceAsString() : null
            ], $statusCode);
        }

        return redirect()->back()
            ->with('error', $errorMessage)
            ->withInput();
    }

    private function getUserFriendlyMessage(\Exception $e): string
    {
        if (config('app.debug')) {
            return $e->getMessage();
        }

        switch ($e->getCode()) {
            case 404:
                return 'Requested data not found.';
            case 500:
                return 'Server error occurred. Please try again later.';
            default:
                return 'Something went wrong. Please try again.';
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserRequest $request)
    {
        try {
            $result = $this->_repo->store(UserDto::fromRequest($request->validated()));
            $redirectRoute = route('users.index', ['user' => $result['role_name']]);

            if (! $result['password_email_sent']) {
                return redirect($redirectRoute)->with('success', 'User created successfully, but the password setup email could not be sent. Please check SMTP configuration.');
            }

            return redirect($redirectRoute)->with('success', 'User successfully created.');
        } catch (\Throwable $th) {
            \Log::error('User creation failed', [
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', __('language.something_went_wrong'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request Validation $validation
     * @return \Illuminate\Http\Response
     */
    public function update(UserUpdateRequest $request, $id)
    {
        try {
            $result = $this->_repo->update($id, UserDto::fromRequest($request->validated()));
            return redirect()->route('users.index', ['user' => $result['role_name']])->with('success', 'User updated successfully.');
        } catch (\Throwable $th) {
            Log::error('User update failed', [
                'user_id' => $id,
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString(),
            ]);
            if ($th instanceof NotFoundHttpException) {
                $message = $th->getMessage(); // Get the exception message
                return redirect()->back()->with('error', $message);
            } else {
                return redirect()->back()->with('error', __('language.something_went_wrong'));
            }
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function editprofile()
    {
        $data = Auth::user();
        return view($this->_directory . '.profile.my_profile', compact('data'));
    }

    /**
     * Update My profile.
     *
     * @param Request Validation $validation
     * @return \Illuminate\Http\Response
     */
    public function updatemyprofile(UpdateProfileRequest $request)
    {
        try {
            $this->_repo->update(Auth::id(), UserDto::fromRequest($request->validated()));
            return redirect()->route('myprofile')->with('success', 'Profile Updated succesfully');
        } catch (\Throwable $th) {
            $message = $th->getMessage();
            if ($th instanceof NotFoundHttpException) {
                return redirect()->route('myprofile')->with('error', $message);
            } else {
                return redirect()->route('myprofile')->with('error', __('language.something_went_wrong'));
            }
        }
    }

    public function safety_privacy()
    {
        return view($this->_directory . '.profile.privacy_safety');
    }

    public function getStaff($user)
    {
        $data['all'] = $this->_repo->getStaff($user);
        return view($this->_directory . '.all', compact('data'));
    }

    public function destroy($id)
    {
        $user = User::with('roles')->find($id);
        $roleName = $user?->roles->first()?->name ?? Constants::USER;

        try {
            $this->_repo->destroy($id);
            return redirect()->route('users.index', ['user' => $roleName])->with('success', 'User deleted successfully.');
        } catch (\Throwable $th) {
            if ($th instanceof NotFoundHttpException) {
                return redirect()->route('users.index', ['user' => $roleName])->with('error', $th->getMessage());
            }

            return redirect()->route('users.index', ['user' => $roleName])->with('error', __('language.something_went_wrong'));
        }
    }

    public function uploadPostImage(Request $request)
    {
        dd('');
    }
}
