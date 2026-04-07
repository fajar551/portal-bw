<?php
namespace App\Exceptions\Domain\Pricing;
use Exception;

class NoSaleException extends Exception
{

    public function report(){
         $message = $this->getMessage();
         $previous = $this->getPrevious();
         $code = $this->getCode();
         $file = $this->getFile();
         $line = $this->getLine();
         $trace = $this->getTrace();
         $traceString = $this->getTraceAsString();
        \Log::debug($message);
    }

}