<?php

namespace App\Payment\PayMethod\Adapter;

class BankAccount extends BankAccountModel implements \App\Payment\Contracts\BankAccountDetailsInterface
{
    use \App\Payment\PayMethod\Traits\BankAccountDetailsTrait;
}

?>
