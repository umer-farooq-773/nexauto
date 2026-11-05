<?php

namespace App\Repositories;

use App\Constants\Constants;
use App\Dto\UserDto;
use App\Interface\UserInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Password;
use App\Notifications\UserCreatedResetNotification;

class UserRepository extends BaseRepository implements UserInterface
{
    /**
     * Create a new service instance.
     *
     * @return $reauest, $modal
     */
    public function __construct(User $model)
    {
        $this->setModel($model);
    }

    public function get_all_users()
    {
        return $this->_model->with('roles')->whereHas('roles', function ($q) {
            $q->whereNotIn('name', [Constants::SUPERADMIN]);
        })->get();
    }

    public function getStaff($user)
    {
        if ($user === Constants::USER) {
            return $this->get_all_users();
        }

        return $this->_model->with('roles')->whereHas('roles', function ($q) use ($user) {
            $q->where('name', $user);
        })->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserDto $data)
    {
        Log::info('User store process started');

        $dataArray = $data->toArray();
        Log::info('DTO converted to array', [
            'data' => collect($dataArray)->except(['password'])->toArray(),
            'has_file' => isset($dataArray['file']),
        ]);

        $roleId = $dataArray['type'] ?? null;

        Log::info('Role ID extracted', [
            'role_id' => $roleId,
        ]);

        $dataArray['email_verified_at'] = now();

        $roleName = Role::whereId($roleId)->pluck('name')->first();

        Log::info('Role lookup result', [
            'role_id' => $roleId,
            'role_name' => $roleName,
        ]);

        if (is_null($roleName)) {
            Log::error('Role not found', [
                'role_id' => $roleId,
            ]);

            throw new NotFoundHttpException('The specified role does not exist.');
        }

        unset($dataArray['type']);

        Log::info('Creating user', [
            'data' => collect($dataArray)->except(['file'])->toArray(),
        ]);

        $user = $this->add($this->_model, $dataArray);

        Log::info('User created successfully', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        $user->assignRole($roleName);

        Log::info('Role assigned successfully', [
            'user_id' => $user->id,
            'role_name' => $roleName,
        ]);

        if (isset($dataArray['image'])) {

            Log::info('File upload started', [
                'original_name' => $dataArray['image']->getClientOriginalName(),
                'size' => $dataArray['image']->getSize(),
                'mime' => $dataArray['image']->getMimeType(),
            ]);
            $profileUpload = $this->uploadFile($dataArray['image'],"profile" . $this->_imgPath);
            Log::info('File uploaded successfully', [
                'upload_result' => $profileUpload,
            ]);

            $profileUpload['type'] = Constants::PROFILETYPE;

            $fileRecord = $user->file()->create($profileUpload);

            Log::info('File record created', [
                'file_id' => $fileRecord->id,
                'path' => $fileRecord->path,
            ]);
        } else {
            Log::warning('No file received in repository');
        }

        Log::info('Generating password reset token');

        $token = Password::broker()->createToken($user);

        Log::info('Password reset token generated');

        $passwordEmailSent = true;

        try {

            Log::info('Sending welcome email', [
                'email' => $user->email,
            ]);

            $user->notify(new UserCreatedResetNotification($token));

            Log::info('Welcome email sent successfully', [
                'email' => $user->email,
            ]);
        } catch (\Throwable $exception) {

            $passwordEmailSent = false;

            Log::error('Welcome email failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }
        Log::info('Repository process completed successfully');

        return [
            'user' => $user,
            'role_name' => $roleName,
            'password_email_sent' => $passwordEmailSent,
        ];
    }


    /**
     * Update the specified resource in storage.
     */
    public function update($id, UserDto $data)
    {
        Log::info('User update process started', [
            'user_id' => $id,
        ]);

        $dataArray = $data->toArray();

        Log::info('DTO converted to array for update', [
            'user_id' => $id,
            'data' => collect($dataArray)->except(['password'])->toArray(),
            'has_file' => isset($dataArray['image']),
        ]);

        if (isset($dataArray['password'])) {

            Log::info('Password found in update payload', [
                'user_id' => $id,
            ]);

            $dataArray['password'] = Hash::make($dataArray['password']);
        }

        Log::info('Fetching user record', [
            'user_id' => $id,
        ]);

        $dataResult = $this->get_by_id($this->_model, $id);

        Log::info('User record fetched successfully', [
            'user_id' => $dataResult->id,
            'email' => $dataResult->email,
        ]);

        if (isset($dataArray['image'])) {

            Log::info('File upload detected during update', [
                'user_id' => $id,
                'original_name' => $dataArray['image']->getClientOriginalName(),
                'size' => $dataArray['image']->getSize(),
                'mime' => $dataArray['image']->getMimeType(),
            ]);

            $existingImages = $dataResult->files()->where('type', Constants::PROFILETYPE)->get();

            Log::info('Existing image lookup completed', [
                'user_id' => $id,
                'image_found' => $existingImages->isNotEmpty(),
                'existing_paths' => $existingImages->pluck('path')->toArray(),
            ]);

            foreach ($existingImages as $existingImage) {

                Log::info('Deleting existing image', [
                    'user_id' => $id,
                    'path' => $existingImage->path,
                ]);

                $this->deleteFile($existingImage->path);

                Log::info('Physical file deleted', [
                    'user_id' => $id,
                    'path' => $existingImage->path,
                ]);

                $existingImage->delete();
            }

            Log::info('Database image records deleted', [
                'user_id' => $id,
                'count' => $existingImages->count(),
            ]);

            Log::info('Uploading new image', [
                'user_id' => $id,
            ]);

            // dd($this->_imgPath);
            $profileUpload = $this->uploadFile($dataArray['image'],"profile" . $this->_imgPath);
            Log::info('Image uploaded successfully', [
                'user_id' => $id,
                'upload_result' => $profileUpload,
            ]);

            $profileUpload['type'] = Constants::PROFILETYPE;

            $fileRecord = $dataResult->file()->create($profileUpload);

            Log::info('New image record created', [
                'user_id' => $id,
                'file_record_id' => $fileRecord->id,
                'path' => $fileRecord->path,
            ]);
        } else {

            Log::warning('No file received during update', [
                'user_id' => $id,
            ]);
        }

        Log::info('Updating user data', [
            'user_id' => $id,
        ]);

        $dataResult->update($dataArray);

        Log::info('User updated successfully', [
            'user_id' => $id,
            'email' => $dataResult->email,
        ]);

        $roleName = $dataResult->roles->first()?->name ?? Constants::USER;

        Log::info('User update process completed', [
            'user_id' => $id,
            'role_name' => $roleName,
        ]);

        return [
            'user' => $dataResult->loadMissing('roles'),
            'role_name' => $dataResult->roles->first()?->name ?? Constants::USER,
        ];
    }

    public function validateEmail($email)
    {
        $data = $this->get_by_column_single($this->_model, ['email' => $email]);

        if ($data == null) {
            return false;
        } else {
            return true;
        }
    }
}
