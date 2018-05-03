<?php

namespace GetCandy\Api\Core\Users\Contracts;

interface UserContract
{
    public function getByEmail($email);

    public function getPaginatedData();

    public function create($data);

    public function update($userId, array $data);

    public function resetPassword($old, $new, $user);

    public function getImpersonationToken($userId);
}
