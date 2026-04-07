<?php

namespace App\User\Contracts;

interface ContactAwareInterface
{
    public function client();
    public function contact();
}
