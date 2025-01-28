<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('championships', function (Blueprint $table) {
            $table->enum('modality', ['presential', 'digital'])->after('type');
            $table->string('address')->nullable()->after('location');
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('venue')->nullable();
            $table->text('submission_instructions')->nullable();
            $table->string('allowed_formats')->nullable();
            $table->time('start_time')->after('start_date');
            $table->time('end_time')->after('end_date');
        });
    }

    public function down()
    {
        Schema::table('championships', function (Blueprint $table) {
            $table->dropColumn([
                'modality',
                'address',
                'city',
                'state',
                'venue',
                'submission_instructions',
                'allowed_formats',
                'start_time',
                'end_time'
            ]);
        });
    }
};
