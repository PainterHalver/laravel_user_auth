<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class hello extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:hello';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Custom command to say hello world!';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        echo 'Hello world!';
    }
}
