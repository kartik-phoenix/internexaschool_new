<?php

namespace App\Http\Controllers;

use App\Models\MasterSchool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use PDO;
use Symfony\Component\Process\Process;

class MasterSchoolController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $settings = getSettings();
        // $getDateFormat = getDateFormat();
        // $getTimezoneList = getTimezoneList();
        // $getTimeFormat = getTimeFormat();

        // $session_year = SessionYear::orderBy('id', 'desc')->get();

        $schools = MasterSchool::get();

        return view('school.index',compact('schools'));
        // return view('school.index', compact('settings', 'getDateFormat', 'getTimezoneList', 'getTimeFormat', 'session_year'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $schoolUrl = env('SCHOOL_URL');
        $schoolPath = env('SCHOOL_PATH');

        $schooldir = strtolower(preg_replace('/[\s-]+/', '', $request->school_name));
        $schooldir = str_replace(['\'', '"'], '', $schooldir);

        $schoolFinalUrl = $schoolUrl . $schooldir;
        // $destinationFolder = dirname(base_path()) . '/' . $schooldir;
        $destinationFolder = $schoolPath .'/'. $schooldir;

        if (!File::isDirectory($destinationFolder)) {
            File::makeDirectory($destinationFolder, 0777, true);
            File::chmod($destinationFolder, 0777);
            Log::info("Directory created successfully.");
        } else {
            Log::info("Directory already exists.");
            return response()->json([
                'message' => "School directory already exists.",
                'error' => 1
            ]);
        }

        // Copy source Folder
        // $sourceFolder = dirname(base_path()) . '/internexaschool_clone';
        $sourceFolder = $schoolPath . '/internexa_update_school';


        File::copyDirectory($sourceFolder, $destinationFolder);
        $directoryPath = $destinationFolder;
        $permissionMode = 0777; // Full permissions for owner, group, and others

        // dd('php '. $destinationFolder .'/artisan command:schooladmin '. $request->first_name .' '. $request->last_name .' '. $request->school_email .' '.$request->password .'');
        if (File::isDirectory($directoryPath)) {
            // Set permissions for subdirectories
            foreach (File::directories($directoryPath) as $subdirectory) {
                File::chmod($subdirectory, $permissionMode);
            }

            // Set permissions for files
            foreach (File::files($directoryPath) as $file) {
                File::chmod($file, $permissionMode);
            }

            // echo "Permissions updated for directory contents.";
            Log::info('Permissions updated for directory contents.');
        } else {
            Log::info("The specified path is not a directory.");
        }

        try {
            $database = strtolower(preg_replace('/[\s-]+/', '_', $request->school_name));
            $database = str_replace(['\'', '"'], '_', $database);
            // dd($database);

            // $database = 'exclusiverp_1';
            for ($i = 1; $i < 10; $i++) {
                $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME =  ?";
                // $db = DB::select($query, [$request->clone]);
                $db = DB::select($query, [$database]);

                if (empty($db)) {
                    // echo 'No db exist of that name ';
                    $database = $database;
                    break;
                } elseif (!empty($db)) {
                    // echo 'db already exists!';
                    $database = $db[0]->SCHEMA_NAME . '_' . $i;
                } else {
                    // echo 'db already exists!';
                    // $database = $db[0]->SCHEMA_NAME . '_' . $i;
                }
            }
            // dd($database);
            $input['db_name'] = $database;

            // Create db

            if (!empty($database)) {
                shell_exec('mysqladmin -u ' . env('DB_USERNAME') . ' -p' . env('DB_PASSWORD') . ' create ' . $database . '');

                shell_exec('mysql -u ' . env('DB_USERNAME') . ' -p' . env('DB_PASSWORD') . ' ' . $database . ' < ' . $sourceFolder . '/database/internexaschool.sql');

          

                $envFile = $destinationFolder . '/.env';

                $newContent = '
                    APP_NAME="' . $request->school_name . '"
                    APP_ENV=local
                    APP_KEY=base64:u8d6RhhBTcEr6bi+mFLaxmtnKIrCeMjDe70rPu4j3q8=
                    APP_DEBUG=true
                    APP_URL='.$schoolFinalUrl.'

                    DEMO_MODE=false
                    LOG_CHANNEL=daily
                    LOG_DEPRECATIONS_CHANNEL=null
                    LOG_LEVEL=debug

                    DB_CONNECTION=mysql
                    DB_HOST=127.0.0.1
                    DB_PORT=3306
                    DB_DATABASE=' . $database . '
                    DB_USERNAME=' . env('DB_USERNAME') . '
                    DB_PASSWORD=' . env('DB_PASSWORD') . '

                    BROADCAST_DRIVER=log
                    CACHE_DRIVER=file
                    FILESYSTEM_DISK=local
                    QUEUE_CONNECTION=sync
                    SESSION_DRIVER=file
                    SESSION_LIFETIME=120

                    MEMCACHED_HOST=127.0.0.1
                    REDIS_HOST=127.0.0.1
                    REDIS_PASSWORD=null
                    REDIS_PORT=6379

                    MAIL_MAILER=smtp
                    MAIL_HOST=sandbox.smtp.mailtrap.io
                    MAIL_PORT=2525
                    MAIL_USERNAME=90d1e03bde7844
                    MAIL_PASSWORD=6162cd89bffce8
                    MAIL_ENCRYPTION=tls
                    MAIL_FROM_ADDRESS=kartik.phoenixbs@gmail.com
                    MAIL_FROM_NAME="${APP_NAME}"

                    AWS_ACCESS_KEY_ID=
                    AWS_SECRET_ACCESS_KEY=
                    AWS_DEFAULT_REGION=us-east-1
                    AWS_BUCKET=
                    AWS_USE_PATH_STYLE_ENDPOINT=false
                    
                    PUSHER_APP_ID=
                    PUSHER_APP_KEY=
                    PUSHER_APP_SECRET=
                    PUSHER_APP_CLUSTER=mt1
                    MIX_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
                    MIX_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
                    
                   
                    LOGO1=logo/u9JvGPZY2DiQKOX4ddLS8QujCTJsNtTsxcoapbZh.svg
                    LOGO2=logo/bKNSOPbYqDJRPQp0XbLapU4U31tk7umDuUAHy7Ia.svg
                    FAVICON=logo/rNzDaNIfirTa48D21hvLmiKJteUmqIbMWK0UbThW.svg
                    TIMEZONE="Asia/Kolkata"';

                // Overwrite the content of the file
                File::chmod($envFile, 0777);
                File::put($envFile, $newContent);

                shell_exec('ln -s '. $destinationFolder .'/storage/app/public/* '. $destinationFolder .'/public/storage/');

                // $superAdminPass = env("SUPER_ADMIN_PASS", 12345678);
                // dd($superAdminPass);
                // die('Db import');
                // $user = [
                //     [
                //         'first_name' => "Phoenix",
                //         'last_name' => "Binery",
                //         'email' => env("SUPER_ADMIN_MAIL", 'superadmin@gmail.com'),
                //         'password' => Hash::make($superAdminPass),
                //         'created_at' => Carbon::now()
                //     ],
                //     [
                //         'first_name' => $request->first_name,
                //         'last_name' => $request->last_name,
                //         'email' => $request->school_email,
                //         'password' => Hash::make($request->password),
                //         'created_at' => Carbon::now()
                //     ]
                // ];

                // Database configuration
                $databaseConfig = [
                    'driver' => 'mysql',
                    'host' => env('DB_HOST'),
                    'port' =>  env('DB_PORT'),
                    'database' => $database,
                    'username' => env('DB_USERNAME'),
                    'password' => env('DB_PASSWORD'),
                    'timestamps' => false,
                ];

                // // Establish the database connection
                $dbConnection = DB::connection('mysql_school');
                $dbConnection->setPdo(new PDO(
                    "{$databaseConfig['driver']}:host={$databaseConfig['host']};port={$databaseConfig['port']};dbname={$databaseConfig['database']}",
                    $databaseConfig['username'],
                    $databaseConfig['password']
                ));

                // shell_exec('php '. $destinationFolder .'/artisan command:schooladmin 1 2');

                // // Now you can use this connection for database operations
                $password = env('SCHOOL_ADMIN_PASSWORD', 123456);
                $users = $dbConnection->table('users')->where('id', 1)->update(['email'=> $request->school_email, 'password' => Hash::make($password)]);

                if ($users) {
                    $data = [
                        'subject' => 'Welcome to ' . $request->school_name,
                        'name' => '',
                        'email' => $request->school_email,
                        'password' => $password,
                        'school_name' => $request->school_name
                    ];
                    Mail::send('teacher.email', $data, function ($message) use ($data) {
                        $message->to($data['email'])->subject($data['subject']);
                    });

                    // $data = [
                    //     'subject' => $request->school_name . 'School Crated successfully',
                    //     'name' => $request->first_name,
                    //     'email' => env("SUPER_ADMIN_MAIL", 'test@gmail.com'),
                    //     'password' => $superAdminPass,
                    //     'school_name' => $request->school_name,
                    //     'database_name' => $request->school_name,
                    // ];
                    // Mail::send('teacher.email', $data, function ($message) use ($data) {
                    //     $message->to($data['email'])->subject($data['subject']);
                    // });
                }

               

                // $school = $request->except('_token', 'status');
                // $school['status'] = isset($request->status) ? 1 : 0;
                $school['school_dir'] = $destinationFolder;
                $Newschool = new MasterSchool;
                $Newschool->school_name = $request->school_name;
                $Newschool->status = $request->status;
                $Newschool->school_dir = $destinationFolder;
                $Newschool->school_url = $schoolFinalUrl;
                $Newschool->school_uid = $database;
                $Newschool->save();
                // // dd($sch);
                // SCHOOL_PATH
                // SCHOOL_URL

                // shell_exec('mysqladmin -u ' . env('DB_USERNAME') . ' -p' . env('DB_PASSWORD') . ' create ' . $database . '');
                // $cmd = shell_exec('php '. $destinationFolder .'/artisan migrate');
                // dd($cmd);
                shell_exec('php '. $destinationFolder .'/artisan optimize:clear');
                // $data = shell_exec('php '. $destinationFolder .'/artisan db:seed --class=DummyDataSeeder');
                // dd($data);
                // shell_exec('php '. $destinationFolder .'/artisan db:seed --class=InstallationSeeder');
                // shell_exec('php '. $destinationFolder .'/artisan db:seed --class=AddSuperAdminSeeder');
                // php artisan command:schooladmin kartik kk kartik@gmail.com 12345678
                // $cmd = shell_exec('php '. $destinationFolder .'/artisan command:schooladmin '. $request->first_name .' '. $request->last_name .' '. $request->school_email .' '.$request->password .'');
                // dd($cmd);
                // Set the paths to your directories
                // $firstDirectory = $destinationFolder;


                // $commandPath = "$destinationFolder/artisan"; // Replace with the actual path
                // $commandName = 'optimize:clear'; // Replace with the actual command name
                // $arguments = [];
                // $options = [];
                // Artisan::call('optimize:clear');
                // $outputBuffer = new BufferedOutput();
                // $exitCode = Artisan::call($commandName, array_merge($arguments, $options), $outputBuffer, $commandPath);
                // $output = $outputBuffer->fetch();
                // // echo $output;

                // $commandName = 'db:seed --class=AddSuperAdminSeeder'; // Replace with the actual command name
                // $commandName = 'db:seed --class=InstallationSeeder'; // Replace with the actual command name
                // $arguments = [];
                // $options = [];

                // $outputBuffer = new BufferedOutput();
                // $exitCode = Artisan::call($commandName, array_merge($arguments, $options), $outputBuffer, $commandPath);
                // $output = $outputBuffer->fetch();

                // Artisan::call('optimize:clear');
                // dd($output, $commandPath);


                // $commandPath = $destinationFolder . '/artisan'; // Replace with the actual path
                // $commandName = 'command:schooladmin'; // Replace with the actual command name
                // $arguments = [$request->first_name, $request->last_name, $request->email, $request->password];
                // $options = [];

                // $outputBuffer = new BufferedOutput();
                // $exitCode = Artisan::call($commandName, array_merge($arguments, $options), $outputBuffer, $commandPath);
                // $output = $outputBuffer->fetch();

                // echo $output;


                // Create a Symfony Process to change the directory
                // chdir($destinationFolder);
                // // $process = new Process([Artisan::call('db:seed')]);
                // $process = new Process(['cd', $destinationFolder]);

                // // Run the process to change the directory
                // $process->run();

                // // Check if the directory change was successful
                // if ($process->isSuccessful()) {
                //     // Now that we're in the first directory, call the Artisan command
                //     // echo $process->getOutput();
                //     // Artisan::call('migrate');
                //     // Artisan::call('db:seed', ['--class' => 'InstallationSeeder']);
                //     Artisan::call('db:seed', ['--class' => 'DummyDataSeeder']);
                //     // Artisan::call('db:seed', ['--class' => 'AddSuperAdminSeeder']);
                //     // $commandName = 'command:schooladmin';
                //     // $arguments = [$request->first_name, $request->last_name, $request->email, $request->password];
                //     // // $options = ['--option' => 'value', '--another-option' => 'another-value'];

                //     // Artisan::call($commandName, $arguments);

                //     // // Print the command output
                //     // echo Artisan::output();
                // } else {
                //     echo $process->getErrorOutput();

                //     // dd($process->getErrorOutput());
                //     echo "Failed to change directory.";
                // }

                return response()->json([
                    'message' => "Create school successfully",
                    'success' => 1
                ]);
            }
        } catch (\Throwable $th) {
            throw $th;
            return response()->json([
                'message' => $th->getMessage(),
                'error' => 1
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MasterSchool  $masterSchool
     * @return \Illuminate\Http\Response
     */
    public function show(MasterSchool $masterSchool)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MasterSchool  $masterSchool
     * @return \Illuminate\Http\Response
     */
    public function edit(MasterSchool $masterSchool)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MasterSchool  $masterSchool
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $masterSchool = MasterSchool::find($id); 
        $masterSchool->status = $request->status; 
        $masterSchool->update();
        // dd($masterSchool->toArray());
        return response()->json([
            'success' => 1
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MasterSchool  $masterSchool
     * @return \Illuminate\Http\Response
     */
    public function destroy(MasterSchool $masterSchool)
    {
        //
    }
}
