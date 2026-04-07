<?php

namespace App\Payment\Contracts;

interface RemoteTokenDetailsInterface
{
    public function getRemoteToken();
    public function setRemoteToken($value);
    public function createRemote();
    public function updateRemote();
    public function deleteRemote();
}

?>
