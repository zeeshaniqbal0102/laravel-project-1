<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\SendgridController;
use Illuminate\Console\Command;

class CheckAndSendMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkAndSendMail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will check for emails every few hours and send email';

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
     * @return mixed
     */
    public function handle()
    {
        $sendgrid = new SendgridController();
//        $preparedArray = $sendgrid->checkDelivery(1);
        $sendgrid->checkBounced(1);
        echo 'Email Sent';
    }
}
