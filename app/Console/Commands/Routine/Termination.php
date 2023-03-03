<?php

namespace App\Console\Commands\Routine;

use App\Lib\Enumerations\TerminationStatus;
use App\Lib\Enumerations\UserStatus;
use App\Model\Employee;
use App\Model\Termination as MTermination;
use App\Model\Termination as TModel;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;

class Termination extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'terminate-user';
    protected $signature = 'user:termination';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Terminate User';

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
        $termination = TModel::where('termination_date', '<=', Carbon::today())->where('status', '!=', UserStatus::$TERMINATE)->get();
      
        foreach ($termination as $key => $value) {

            try {
                // dd($value);
                $employee = Employee::where('employee_id', $value->terminate_to)->first();
                $employee->update(['status' => UserStatus::$TERMINATE]);
                $user = User::where('user_id', $employee->user_id)->first();
                $user->update(['status' => UserStatus::$TERMINATE]);
                $termination = MTermination::where('finger_print_id', $employee->finger_id)->update(['status' => TerminationStatus::$APPROVED]);
            } catch (Exception $e) {
                info('Termination Error - ' . $e->getMessage());
            }
        }
    }
}