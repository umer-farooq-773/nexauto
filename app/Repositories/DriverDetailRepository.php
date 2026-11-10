<?php

namespace App\Repositories;

use App\Constants\Constants;
use App\Interfaces\DriverDetailInterface;
use App\Dto\DriverDetailDto;
use App\Models\DriverDetail;
use App\Models\User;
use App\Notifications\DriverCredentialsNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DriverDetailRepository extends BaseRepository implements DriverDetailInterface
{
    protected $_imgPath = 'drivers/';

    /**
     * Create a new service instance.
     *
     * @return $reauest, $modal
     */
    public function __construct(DriverDetail $model)
    {
        $this->setModel($model);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DriverDetailDto $data)
    {
        try {
            $dataArray = $data->toArray();
            $image = $dataArray['image'];
            $companyIdCard = $dataArray['company_id_card'];
            $licenseCard = $dataArray['license_card'];

            unset($dataArray['image'], $dataArray['company_id_card'], $dataArray['license_card']);

            // Provision user account and credentials if email provided
            $plainPassword = null;
            if (!empty($dataArray['email'])) {
                $existingUser = User::where('email', $dataArray['email'])->first();
                if ($existingUser) {
                    $user = $existingUser;
                    if (!$user->hasRole(Constants::DRIVER)) {
                        $user->assignRole(Constants::DRIVER);
                    }
                    $dataArray['user_id'] = $user->id;
                } else {
                    $plainPassword = 'Drv@' . rand(10000, 99999);
                    $user = User::create([
                        'first_name'        => $dataArray['first_name'],
                        'last_name'         => $dataArray['last_name'],
                        'email'             => $dataArray['email'],
                        'phone_number'      => $dataArray['phone_number'],
                        'password'          => Hash::make($plainPassword),
                        'email_verified_at' => now(),
                    ]);
                    $user->assignRole(Constants::DRIVER);
                    $dataArray['user_id'] = $user->id;

                    // Send email with login credentials
                    try {
                        $user->notify(new DriverCredentialsNotification(
                            driverName: $user->full_name,
                            email: $user->email,
                            plainPassword: $plainPassword,
                            loginUrl: url('/account/signin')
                        ));
                        session()->flash('driver_credentials', [
                            'email'    => $user->email,
                            'password' => $plainPassword,
                        ]);
                    } catch (\Throwable $th) {
                        Log::warning('Could not dispatch driver credentials email: ' . $th->getMessage());
                    }
                }
            }

            $dataResult = $this->add($this->_model, $dataArray);

            if (!$dataResult) {
                throw new \Exception('Failed to create driver record');
            }

            if ($image != null) {
                try {
                    $imageUploaded = $this->uploadFile($image, $this->_imgPath);
                    if ($imageUploaded && isset($imageUploaded['path'])) {
                        $imageUploaded['type'] = 'image';
                        $dataResult->files()->create($imageUploaded);
                    }
                } catch (\Throwable $th) {
                    \Log::error('Driver image upload error: ' . $th->getMessage(), ['driver_id' => $dataResult->id]);
                }
            }

            foreach (['company_id_card' => $companyIdCard, 'license_card' => $licenseCard] as $type => $file) {
                if ($file != null) {
                    try {
                        $uploaded = $this->uploadFile($file, $this->_imgPath);
                        if ($uploaded && isset($uploaded['path'])) {
                            $uploaded['type'] = $type;
                            $dataResult->files()->create($uploaded);
                        }
                    } catch (\Throwable $th) {
                        \Log::error("Driver {$type} upload error: " . $th->getMessage(), ['driver_id' => $dataResult->id]);
                    }
                }
            }

            return true;
        } catch (\Throwable $th) {
            \Log::error('Driver store error: ' . $th->getMessage(), ['exception' => $th]);
            throw $th;
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id, DriverDetailDto $data)
    {
        try {
            $result = $this->checkRecord($id);

            if (!$result) {
                throw new \Exception('Driver not found');
            }

            $dataArray = $data->toArray();
            $image = $dataArray['image'];
            $companyIdCard = $dataArray['company_id_card'];
            $licenseCard = $dataArray['license_card'];
            unset($dataArray['image'], $dataArray['company_id_card'], $dataArray['license_card']);

            // Sync user account if email is provided
            if (!empty($dataArray['email'])) {
                if ($result->user_id && ($user = User::find($result->user_id))) {
                    $user->update([
                        'first_name'   => $dataArray['first_name'],
                        'last_name'    => $dataArray['last_name'],
                        'email'        => $dataArray['email'],
                        'phone_number' => $dataArray['phone_number'],
                    ]);
                    if (!$user->hasRole(Constants::DRIVER)) {
                        $user->assignRole(Constants::DRIVER);
                    }
                } else {
                    $existingUser = User::where('email', $dataArray['email'])->first();
                    if ($existingUser) {
                        $user = $existingUser;
                        if (!$user->hasRole(Constants::DRIVER)) {
                            $user->assignRole(Constants::DRIVER);
                        }
                    } else {
                        $plainPassword = 'Drv@' . rand(10000, 99999);
                        $user = User::create([
                            'first_name'        => $dataArray['first_name'],
                            'last_name'         => $dataArray['last_name'],
                            'email'             => $dataArray['email'],
                            'phone_number'      => $dataArray['phone_number'],
                            'password'          => Hash::make($plainPassword),
                            'email_verified_at' => now(),
                        ]);
                        $user->assignRole(Constants::DRIVER);

                        try {
                            $user->notify(new DriverCredentialsNotification(
                                driverName: $user->full_name,
                                email: $user->email,
                                plainPassword: $plainPassword,
                                loginUrl: url('/account/signin')
                            ));
                            session()->flash('driver_credentials', [
                                'email'    => $user->email,
                                'password' => $plainPassword,
                            ]);
                        } catch (\Throwable $th) {
                            Log::warning('Could not dispatch driver credentials email on update: ' . $th->getMessage());
                        }
                    }
                    $dataArray['user_id'] = $user->id;
                }
            }

            $result->update($dataArray);

            if ($image != null) {
                try {
                    $imageUploaded = $this->uploadFile($image, $this->_imgPath);
                    if ($imageUploaded && isset($imageUploaded['path'])) {
                        $imageUploaded['type'] = 'image';
                        $result->files()->updateOrCreate(['type' => 'image'], $imageUploaded);
                    }
                } catch (\Throwable $th) {
                    \Log::error('Driver image upload error on update: ' . $th->getMessage(), ['driver_id' => $result->id]);
                }
            }

            foreach (['company_id_card' => $companyIdCard, 'license_card' => $licenseCard] as $type => $file) {
                if ($file != null) {
                    try {
                        $uploaded = $this->uploadFile($file, $this->_imgPath);
                        if ($uploaded && isset($uploaded['path'])) {
                            $uploaded['type'] = $type;
                            $result->files()->updateOrCreate(['type' => $type], $uploaded);
                        }
                    } catch (\Throwable $th) {
                        \Log::error("Driver {$type} upload error on update: " . $th->getMessage(), ['driver_id' => $result->id]);
                    }
                }
            }

            return $result;
        } catch (\Throwable $th) {
            \Log::error('Driver update error: ' . $th->getMessage(), ['exception' => $th]);
            throw $th;
        }
    }

    /**
     * Resend portal credentials to driver.
     */
    public function resendCredentials($id): array
    {
        $driver = $this->checkRecord($id);
        if (!$driver) {
            throw new \Exception('Driver not found');
        }

        if (empty($driver->email)) {
            throw new \Exception('Driver does not have an email address configured.');
        }

        $plainPassword = 'Drv@' . rand(10000, 99999);
        $user = $driver->user;

        if (!$user) {
            $user = User::firstOrCreate(
                ['email' => $driver->email],
                [
                    'first_name'        => $driver->first_name,
                    'last_name'         => $driver->last_name,
                    'phone_number'      => $driver->phone_number,
                    'password'          => Hash::make($plainPassword),
                    'email_verified_at' => now(),
                ]
            );
            $driver->update(['user_id' => $user->id]);
        } else {
            $user->update([
                'password' => Hash::make($plainPassword),
            ]);
        }

        if (!$user->hasRole(Constants::DRIVER)) {
            $user->assignRole(Constants::DRIVER);
        }

        $user->notify(new DriverCredentialsNotification(
            driverName: $driver->full_name,
            email: $driver->email,
            plainPassword: $plainPassword,
            loginUrl: url('/account/signin')
        ));

        return [
            'email'    => $driver->email,
            'password' => $plainPassword,
        ];
    }
}
