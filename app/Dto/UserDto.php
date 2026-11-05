<?php

namespace App\Dto;

class UserDto
{
    public ?string $first_name;
    public ?string $last_name;
    public ?string $email;
    public ?string $phone_number;
    public ?string $role;
    public $file;

    /**
     * Create a new controller instance.
     *
     * @return $reauest, $modal
     */
    public function __construct($request)
    {
        $this->first_name = isset($request['f_name']) ? $request['f_name'] : null;
        $this->last_name = isset($request['l_name']) ? $request['l_name'] : null;
        $this->email = isset($request['email']) ? $request['email'] : null;
        $this->phone_number = isset($request['phone_number']) ? $request['phone_number'] : null;
        $this->role = isset($request['role']) ? $request['role'] : null;
        $this->file = isset($request['file']) ? $request['file'] : null;
    }

    public static function fromRequest($request)
    {
        return new self($request);
    }

    public function toArray()
    {
        $data = [];
        $this->first_name == null ?: $data['first_name'] = $this->first_name;
        $this->last_name == null ?: $data['last_name'] = $this->last_name;
        $this->phone_number == null ?: $data['phone_number'] = $this->phone_number;
        $this->role == null ?: $data['type'] = $this->role;
        $this->email == null ?: $data['email'] = $this->email;
        $this->file == null ?: $data['image'] = $this->file;

        return $data;
    }
}
