<?php

namespace App\Dto\Permission;

class RoleUpdateDto
{
    public readonly string $title;
    public $permissions;

    /**
     * Create a new controller instance.
     *
     * @return $reauest, $modal
     */
    public function __construct($request)
    {
        $this->title = $request['title'];
        $this->permissions = $request['permissions'];
    }

    public static function fromRequest($request)
    {
        return new self($request);
    }

    public function toArray()
    {
        return [
            'title' => $this->title,
            'permissions' => $this->permissions,
        ];
    }
}
