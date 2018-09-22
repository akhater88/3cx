<?php

namespace Encore\Admin\Controllers\Tools;

use Encore\Admin\Grid\Exporters\AbstractExporter;
use Maatwebsite\Excel\Facades\Excel;


class ExcelExpoter extends AbstractExporter
{

	protected $fileName;

	protected $title;

	protected $cols;

	protected $extension = 'xls';

    public function export()
    {
       Excel::create($this->fileName, function($excel) {

            $excel->sheet($this->fileName, function($sheet) {

                $rows = collect($this->getData())->map(function ($item) {
                	
                    return array_only($item, $this->cols);
                });

            $sheet->prependRow(1,$this->cellsHeader($rows));
         //    $sheet->row(1, function ($row) {
	        //    // $row->setFontFamily('Comic Sans MS');
	        //    // $row->setFontWeight(true);
        	// });
            $sheet->rows($rows);

            });


        })->export($this->extension);
    }

    public function fileName($fileName = 'NotSet')
    {
    	$this->fileName = $fileName;
    	return $this;
    }
    public function title($fileName = 'NotSet')
    {
    	$this->fileName = $fileName;
    	return $this;
    }

    public function tableColumns(array $cols = [])
    {
    	$this->cols = $cols;
    	return $this;
    }

    public function header(array $header)
	{
		$this->cols = array_combine($header, $this->cols);
		
		return $this;
	}

	protected function hasStringKeys(array $array) 
	{
	  return count(array_filter(array_keys($array), 'is_string')) > 0;
	}

	protected function cellsHeader($rows)
	{
		if($this->hasStringKeys($this->cols)){
	       	return array_keys($this->cols);
		}
	        
	    return array_keys($rows->first());
	}
	/*
	    0 => "xlsx"
	    1 => "xlsm"
	    2 => "xltx"
	    3 => "xltm"
	    4 => "xls"
	    5 => "xlt"
	    6 => "ods"
	    7 => "ots"
	    8 => "slk"
	    9 => "xml"
	    10 => "gnumeric"
	    11 => "htm"
	    12 => "html"
	    13 => "csv"
	    14 => "txt"
    	15 => "pdf"
*/
	public function extension($extension)
	{
		$this->extension = $extension;
		return $this;
	}

}