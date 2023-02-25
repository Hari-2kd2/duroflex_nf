<?php

namespace App\Http\Controllers\Developer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class DeveloperController extends Controller
{
    public function sample()
    {
        return response()->json([
            'message' => "API works Very Well",
        ], 201);
    }
    public function migrate()
    {
        $work = Artisan::call('migrate:refresh');
        return response()->json([
            'message' => "Migration Success",
        ]);
    }
    public function seed()
    {
        set_time_limit(180);
        $work = Artisan::call('db:seed');
        set_time_limit(30);
        return response()->json([
            'message' => "Seeding Success",
        ]);
    }
    public function optimize()
    {
        $work = Artisan::call('optimize:clear');
        return response()->json([
            'message' => "Optimize Success",
        ]);
    }
    public function storage()
    {
        $work = Artisan::call('storage:link');
        return response()->json([
            'message' => "Storage link created",
        ]);
    }
    public function key()
    {
        $work = Artisan::call('key:generate');
        $jwt = Artisan::call('jwt:secret');
        return response()->json([
            'message' => "Server Key Created Successfully",
        ]);
    }
    public function configcache()
    {
        $work = Artisan::call('config:cache');
        return response()->json([
            'message' => "Config Cache Cleared Successfully",
        ]);
    }
    public function routecache()
    {
        $work = Artisan::call('route:cache');
        return response()->json([
            'message' => "Route Cache Cleared Successfully",
        ]);
    }
    public function viewcache()
    {
        $work = Artisan::call('view:cache');
        return response()->json([
            'message' => "View Cache Cleared Successfully",
        ]);
    }
}
