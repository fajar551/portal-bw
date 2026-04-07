<?php

namespace App\User\Contracts;

interface UserInterface
{
    public function getUsernameAttribute();
    public function hasPermission($permission);
}

?>
