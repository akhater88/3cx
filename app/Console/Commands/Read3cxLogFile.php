<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\File\File;
use Encore\Admin\Models\LogReport;
use Encore\Admin\Traits\LogRecord;

class Read3cxLogFile extends Command
{
    use LogRecord;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'read3cxlogfile';

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
            $path = "storage/logs/newLog.csv";//cdr_new
            $file = new File($path);
            $content = $file->openFile();
            $count = 1;
            $indexArray = [];
            foreach ($content as $record) {
                $modCount = $count%2;
                if($count == 1){
                    $record = str_replace("\r\n","",$record);
                    $indexArray = explode(',', $record);
                }
                if($modCount == 0){
                    $recordIndexedArray = [];
                    $record = str_replace("\r\n","",$record);
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
                    
                    $parsedArray = $this->parselogRecord($recordIndexedArray);
                    if(!$parsedArray['skip']){
                        $logObject = new LogReport();
                        $logObject->fill($parsedArray);
                        $logObject->save();
                    } 
                }
                
                $count++;
            }
        } catch (\Exception $e){
            
        }
        
    }
}