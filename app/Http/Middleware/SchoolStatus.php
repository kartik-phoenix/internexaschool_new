<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;

class SchoolStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
              // Database configuration
                $databaseConfig = [
                    'driver' => 'mysql',
                    'host' => env('DB_HOST'),
                    'port' =>  env('DB_PORT'),
                    'database' => 'internexaschool',
                    'username' => env('DB_USERNAME'),
                    'password' => env('DB_PASSWORD'),
                    'timestamps' => false,
                ];

                // // Establish the database connection
                $dbConnection = DB::connection('');
                $dbConnection->setPdo(new PDO(
                    "{$databaseConfig['driver']}:host={$databaseConfig['host']};port={$databaseConfig['port']};dbname={$databaseConfig['database']}",
                    $databaseConfig['username'],
                    $databaseConfig['password']
                ));

                $checkstatus = $dbConnection->table('master_schools')->where('school_dir', base_path())->first();
                if($checkstatus->status == 0){
                    abort(403, 'In-Active');
                }
                return $next($request);
    }
}
