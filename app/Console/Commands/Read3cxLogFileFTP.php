<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\File\File;
use Encore\Admin\Models\LogReport;
use Encore\Admin\Traits\LogRecord;
use DateTime;


class Read3cxLogFileFTP extends Command
{
    use LogRecord;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'read3cxlogfileFTP';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migration read 3cx cdr log records';

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
        try{
           // echo "start \n";
            $local_file = 'storage/logs/ftglog.csv';
            $remote_file = env('FTP_REMOTE_FILE');
            $ftp_server = env('FTP_IP');
            $ftp_conn = ftp_connect($ftp_server) or die("Could not connect to $ftp_server");
            $login = ftp_login($ftp_conn,env('FTP_USER_NAME'),env('FTP_PASS'));
            
            ftp_pasv($ftp_conn, true);
            
         
            if(ftp_get( $ftp_conn,$local_file,$remote_file,FTP_BINARY)){
                echo "successfully written to $local_file\n";
            } else {
               echo "There was a problem while downloading $remote_file to $local_file\n";
            }
            $todayDate = date("Y-m-d");
            //ftp_rename($ftp_conn,$remote_file,"cdr$todayDate.csv");
            
            ftp_close($ftp_conn); 
            //$path = ;//"storage/logs/newLog.csv";//cdr_new
            $file = new File($local_file);
            $content = $file->openFile();
            $count = 1;
            $indexArray = [];
            
            $record = "historyid,callid,duration,time-start,time-answered,time-end,reason-terminated,from-no,to-no,from-dn,to-dn,dial-no,reason-changed,final-number,final-dn,chain,from-type,to-type,final-type,from-dispname,to-dispname,final-dispname";
            $indexArray = explode(',', $record);
            foreach ($content as $record) {
                $modCount = $count%2;
                if($modCount != 0){
                    $recordIndexedArray = [];
                    $record = str_replace("\r\n","",$record);
                    $record = str_replace("\t",",",$record);
                    $recordArray = explode(',', $record);
                    foreach ($indexArray as $key => $value){
                        try{
                            if($key == 0){
                                $value = 'historyid';
                            }
                            $index = str_replace('-', '_',$value);
                            $arrayOfIndexesTime = ['time_start','time_answered','time_end'];
                            if(in_array($index, $arrayOfIndexesTime)){
                                $time = strtotime($recordArray[$key]);
                                $recordArray[$key] = date('Y-m-d H:i:s',$time);
                            }
                            if($index == 'duration'){
                                $time = strtotime('1970-01-01 '.$recordArray[$key]);
                                $recordArray[$key] = date('Y-m-d H:i:s',$time);
                            }
                            $index = str_replace("\n", '', $index);
                            $recordIndexedArray[$index] = $recordArray[$key];
                        } catch (\Exception $e){
                            continue;
                        }
                    }
                    
                    try {
                        $parsedArray = $this->parselogRecord($recordIndexedArray);
                    } catch (\Exception $e){
                        continue;
                    }
                   
                    if(!$parsedArray['skip']){
                        $logObject = new LogReport();
                        $logObject->fill($parsedArray);
                        $logObject->save();
                    } 
                }
                
              
            }
        } catch (\Exception $e){
            dd($e);
        }
        
    }
}