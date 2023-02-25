<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee', function (Blueprint $table) {
            $table->increments('employee_id');
            $table->integer('user_id')->unsigned();
            $table->string('finger_id')->unique();
            $table->integer('department_id')->default(1);
            $table->integer('designation_id')->default(1);
            $table->integer('branch_id')->unsigned()->nullable();
            $table->integer('supervisor_id')->nullable();
            $table->integer('work_shift_id')->unsigned();
            $table->string('esi_card_number', 30)->nullable();
            $table->string('pf_account_number', 30)->nullable();
            $table->integer('pay_grade_id')->unsigned()->nullable()->default(0);
            $table->integer('hourly_salaries_id')->unsigned()->nullable()->default(0);
            $table->string('email', 50)->unique()->nullable();
            $table->string('first_name', 30);
            $table->string('last_name', 30)->nullable();
            $table->date('date_of_birth');
            $table->date('date_of_joining');
            $table->date('date_of_leaving')->nullable();
            $table->string('gender', 10);
            $table->string('religion', 50)->nullable();
            $table->string('marital_status', 10)->nullable();
            $table->string('photo', 250)->nullable();
            $table->text('address')->nullable();
            $table->text('emergency_contacts')->nullable();
            $table->string('document_title')->nullable();
            $table->string('document_name')->nullable();
            $table->date('document_expiry')->nullable();
            $table->string('document_title2')->nullable();
            $table->string('document_name2')->nullable();
            $table->date('document_expiry2')->nullable();
            $table->string('document_title3')->nullable();
            $table->string('document_name3')->nullable();
            $table->date('document_expiry3')->nullable();
            $table->string('document_title4')->nullable();
            $table->string('document_name4')->nullable();
            $table->date('document_expiry4')->nullable();
            $table->string('document_title5')->nullable();
            $table->string('document_name5')->nullable();
            $table->date('document_expiry5')->nullable();
            $table->integer('phone');
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('permanent_status')->default(0);
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->softDeletes();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee');
    }
}
