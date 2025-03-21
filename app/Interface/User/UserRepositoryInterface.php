<?php

namespace App\Interface\User;

interface UserRepositoryInterface
{
    public function all();
    public function create(array $data);

    public function update($id,array $data);

    public function delete($id);
    public function findById($id);
    public function findByEmail($email);

    public function createStatus();
}
