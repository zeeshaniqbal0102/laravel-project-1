<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;

use App\DB\Company;
use App\DB\InvoiceAdjustments;
use App\DB\PreselectOrder;
use App\DB\Order;
use App\DB\Giftcards;
use App\User;
use App\DB\ReceiverProduct;
use App\DB\Inserts;
use App\DB\Invoice;
use App\DB\CartItem;
use App\DB\InventoryTransaction;
use App\DB\UserCompanyPivot;

class ExportDataForGoogleDrive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:upload-to-google';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export All given tables in $tableArr.';

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
        $PrevDate = new \DateTime('now');
        $PrevDate->sub(new \DateInterval('P9D'));
        $PrevDateF = $PrevDate->format('Y-m-d');
        //Table to be exported
        $tableArr = [
            'users' => 'users', 
            'receiver_products' => 'receiver_products', 
            'company'=>'company', 
            'company_users_pivot' => 'users_pivot', 
            'inserts' => 'inserts', 
            'invoice' => 'invoice', 
            'invoice_adjustments'=>'invoice_adjustments',
            'preselect_orders'=>'preselects', 
            'cart' => 'cart', 
        ];

    //    $tableArr = ['cart'=>'cart'];

        foreach ($tableArr as $table => $fileName) {
            $data = null;
            switch ($table) {
                case 'users':
                    $data = User::all();
                    break;
                case 'receiver_products':
                    $data = ReceiverProduct::all();
                    break;
                case 'inserts':
                    $data = Inserts::all();
                    break;
                case 'invoice':
                    $data = Invoice::all();
                    break;
                case 'cart':
                    $data = CartItem::whereRaw("DATE(created_at) >= '" . $PrevDateF . "'")->get();
                    break;
                case 'company':
                    $data = Company::all();
                    break;
                case 'company_users_pivot':
                    $data = UserCompanyPivot::all();
                    break;
                case 'invoice_adjustments':
                    $data = InvoiceAdjustments::all();
                    break;
                case 'preselect_orders':
                    $data = PreselectOrder::all();
                    break;
            }
            if($data){
                \Excel::create($fileName, function($excel) use($data) {
                    $excel->sheet('Data', function($sheet) use($data) {
                        $sheet->fromModel($data);
                    });
                })->store('csv');

                $filename = $fileName.'.csv';
                $content = file_get_contents(storage_path('exports').'/'.$fileName.'.csv');
                // // Now find that file and use its ID (path) to delete it
                $dir = "/";
                $recursive = false; // Get subdirectories also?
                $contents = collect(\Storage::cloud()->listContents($dir, $recursive));

                $file = $contents
                    ->where('type', '=', 'file')
                    ->where('filename', '=', pathinfo($filename, PATHINFO_FILENAME))
                    ->where('extension', '=', pathinfo($filename, PATHINFO_EXTENSION))
                    ->first(); // there can be duplicate file names!
                \Storage::cloud()->delete($file['path']);

                $file = $contents
                    ->where('type', '=', 'file')
                    ->where('filename', '=', pathinfo($filename, PATHINFO_FILENAME))
                    ->where('mimetype', '=', 'application/vnd.google-apps.spreadsheet')
                    ->first();

                \Storage::cloud()->delete($file['path']);

                // //Upload csv file
                \Storage::cloud()->put($fileName.'.csv', $content);

                //Create and upload as google sheet
                // $client = new \Google_Client();
                // $client->setClientId(config('filesystems.disks.google.clientId'));
                // $client->setClientSecret(config('filesystems.disks.google.clientSecret'));
                // $client->refreshToken(config('filesystems.disks.google.refreshToken'));
                // $driveService = new \Google_Service_Drive($client);

                // $fileMetadata = new \Google_Service_Drive_DriveFile(array(
                //     'name' => $fileName,
                //     'parents' => array(config('filesystems.disks.google.folderId')),
                //     'mimeType' => 'application/vnd.google-apps.spreadsheet'));

                // $file = $driveService->files->create($fileMetadata, array(
                //     'data' => $content,
                //     'mimeType' => 'text/csv',
                //     'uploadType' => 'multipart',
                //     'fields' => 'id'));

                print(date('Y-m-d H:i') . ' - ' . $table." - Uploaded!\n");
                \Log::info(date('Y-m-d H:i'). ' - ' .$table . " - Uploaded!\n");
            }
        }
    }
}