<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class andiw extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'andiw {--function} {--b} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'testing ajahh';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //$name = $this->argument('name');
        $title='';
       if($this->option('function')){
           $title.='-- function ';
       }
       if($this->option('b')){
            $title.='-- b ';
    }
        return $this->info("Halloo {$title} created successfuly");
    }
}
