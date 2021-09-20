<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Serial;
use Carbon\Carbon;

class UpdateSerial extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'serial:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        Serial::where('deleted', 0)->where('sold', 0)->get()
        ->map(function ($row) {
            $validTo = Carbon::parse($row->valid_to);
            if ($validTo->isPast()) {
                $row->deleted = 1;
                $row->save();
            }
        });
    }
}
