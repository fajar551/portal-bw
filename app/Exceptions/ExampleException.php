<?php

namespace App\Exceptions;

use Exception;

class ExampleException extends Exception
{
    /**
     * Custom report logic
     * 
     * Reference functions:
     * @see https://www.php.net/manual/en/exception.construct.php
     * 
     * uncomment the report() function for custom logic report.
     * for example, you can set the custom Log or connect to the BugSnag, Email report, etc...
     * 
     */
    // public function report()
    // {
    //     $message = $this->getMessage();
    //     $previous = $this->getPrevious();
    //     $code = $this->getCode();
    //     $file = $this->getFile();
    //     $line = $this->getLine();
    //     $trace = $this->getTrace();
    //     $traceString = $this->getTraceAsString();

    //     \Log::debug($message);
    // }

    /**
     * Custom view
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // public function render($request)
    // {
    //     return response()->view('errors.custom', [], 500);
    // }
}
