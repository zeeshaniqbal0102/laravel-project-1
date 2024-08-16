<?php


namespace App\Actions;


use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ScheduleCsvExport
{
    public function handle($export)
    {
        try {
            $rows = DB::connection('mysql_read_only')->select($export->sql);
            $rows = array_map(function($row) {
                return (array)$row;
            }, $rows);
            \Excel::create($export->file_name, function($excel) use($rows) {
                $excel->sheet('test', function($sheet) use($rows) {
                    $sheet->fromArray($rows);
                });
            })->store('csv', storage_path('app/public/hourly-reports'));
            echo "$export->file_name.csv exported";
        } catch (QueryException $ex) {
            logger()->warning("Sql Automation Query Error: Please check record with id($export->id)");
            logger()->warning('Sql Automation: '.  $ex->getMessage());
        } catch (\Exception $ex) {
            logger()->warning("Sql Automation Other Error: Please check record with id($export->id)");
            logger()->warning('Sql Automation: '.  $ex->getMessage());
        }
    }
}