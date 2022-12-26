<?php

namespace App\Modules\Importer\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Importer\Models\ImporterLog;
use App\Modules\Importer\Models\WorkOrder;
use App\Modules\Importer\Repositories\ImporterRepository;
use App;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Class ImporterController
 *
 * @package App\Modules\Importer\Http\Controllers
 */
class ImporterController extends Controller
{


    /**
     * Parsing work orders
     *
     * @return string
     */
    public static function index(){
        return "<a href='/import'>Parse data from work_orders.html to work order table</a><br><a href='/import_log'>Show logs from import log table</a><br><a href='/import_new_file'>Parse data from given file</a><br><a href='/show_csv'>Show report from csv file</a><br><a href='/'>Home</a>";
    }
    /**
     * Parsing work orders
     * @param  \Illuminate\Http\Request $request
     * @return string
     */
    public function import_work_orders(Request $request)
    {
        include(app_path().'../Modules/Importer/Packages/simplehtmldom/simple_html_dom.php');
        $query = $request->query('file');
        if(!empty($query)){
            $parseworkorders = file_get_html('../storage/app/local/work_orders/work_orders_imported.html');
        }else{
            $parseworkorders = file_get_html('../work_orders.html');
        }
        //Get tables with data to parse
        $count_work_orders = $parseworkorders->find('tbody');
        $no_created_work_orders = 0;
        $no_processed_work_orders = 0;

        foreach ($count_work_orders as $count_work_order){
            //Get all work orders from this table
            $tr_orders = $count_work_order->find("tr");
            foreach($tr_orders as $tr_order){
                if(!is_null($tr_order->children(0)->find('a',0))){
                    $ticket = $tr_order->children(0)->find('a',0)->plaintext;
                }else{
                    continue;
                }

                if(!is_null($tr_order->children(0)->find('a',0))){
                    $entity = explode("=",$tr_order->children(0)->find('a',0)->href);
                    $external_id = $entity[1];
                }else{
                    continue;
                }

                $urgancy = $tr_order->children(3)->plaintext;
                $urgancy_check = substr($urgancy,0,1);
                if($urgancy_check!="P"){
                    continue;
                }

                if(!is_null($tr_order->children(4)->find('span',0))){
                    $rcvd_date = $tr_order->children(4)->find('span',0)->plaintext;
                    $received_datee=date_create($rcvd_date);
                    $received_datee = date_format($received_datee,"Y-m-d H:i:s");
                }else{
                    continue;
                }

                $category = $tr_order->children(8)->plaintext;

                $store_name = $tr_order->children(10)->plaintext;

                //storing data in an array
                $work_order = [
                    'work_order_number' => $ticket,
                    'external_id' => $external_id,
                    'priority' => $urgancy,
                    'received_date' => $received_datee,
                    'category' => $category,
                    'fin_loc' => $store_name
                ];
//checking whether a work order already exists
                $check_if_work_order_exists = WorkOrder::where('work_order_number','=',$ticket)->get();
                $work_order_exists_bool = false;
                if(count($check_if_work_order_exists)>0){
                    $work_order_exists_bool = true;
                }

                //Creating report to csv file
                $this->exporttocsv($work_order,$work_order_exists_bool);

                try
                {
                    if(count($check_if_work_order_exists)<=0){
                        WorkOrder::insert($work_order);
                        $no_created_work_orders++;
                    }
                }
                catch(Exception $e)
                {
                    dd($e->getMessage());
                }

                $no_processed_work_orders++;
            }
        }
        $new_importer_log = new ImporterLog;
        $new_importer_log->type='import';
        $new_importer_log->entries_processed = $no_processed_work_orders;
        $new_importer_log->entries_created = $no_created_work_orders;
        try
        {
            $new_importer_log->save();
        }
        catch(Exception $e)
        {
            dd($e->getMessage());
        }
        if(!empty($query)){
            Storage::delete('work_orders/work_orders_imported.html');
        }
        return "Parsed rows: ".$no_processed_work_orders."<br>Created rows: "
            .$no_created_work_orders."<br><a 
        href='/importer'>Home</a>";
    }

    public function importer_log(){
        $logs = ImporterLog::all();
        echo "<table>
  <tr>
    <th>id</th>
    <th>type</th>
    <th>run_at</th>
    <th>entries_processed</th>
    <th>entries_created</th>
  </tr>
 
";
        foreach ($logs as $log){
            echo "<tr>
    <td>{$log['id']}</td>
    <td>{$log['type']}</td>
    <td>{$log['run_at']}</td>
    <td>{$log['entries_processed']}</td>
    <td>{$log['entries_created']}</td>";
        }
        echo "</table>";

        return "<a href='/importer'>Home</a>";
    }

    public function import_file(){
        echo '<form method="post" enctype="multipart/form-data" action="/save">
  <div>
    <label for="file">Choose file to parse work orders from</label>
    <input type="file" id="myfile" name="myfile">
  </div>
  <div>
    <button>Submit</button>
  </div>
</form>';

        echo "<a href='/importer'>Home</a>";
    }

    public function save_file(Request $request){
        if($request->hasFile('myfile')){
            $path = Storage::putFileAs(
                'work_orders', $request->file('myfile'), 'work_orders_imported.html'
            );
        }

        return redirect()->to('import/file?file=fefe')->send();

    }
    public function exporttocsv($work_order,$work_order_exists_bool)
    {

        if(!file_exists(public_path().'/files/work_orders.csv')){
            $this->create_csv_file();
            $filename =  public_path("files/work_orders.csv");
            $handle = fopen($filename, 'a');
        }else{
            $filename =  public_path("files/work_orders.csv");
            $handle = fopen($filename, 'a');
        }

        if(!$work_order_exists_bool){
            $status = "created";
        }else{
            $status = "skipped";
        }

        //adding the data from the array
        fputcsv($handle, [
            $work_order['work_order_number'],
            $work_order['external_id'],
            $work_order['priority'],
            $work_order['received_date'],
            $work_order['category'],
            $work_order['fin_loc'],
            $status
        ]);
        fclose($handle);
    }

    public function create_csv_file(){
        if (!File::exists(public_path()."/files")) {
            File::makeDirectory(public_path() . "/files");
        }
        $filename = public_path("files/work_orders.csv");
        $handle = fopen($filename, 'a');
        //adding the first row
        fputcsv($handle, [
            "work order number",
            "external id",
            "priority",
            "received date",
            "category",
            "fin loc",
            "status"
        ]);
        fclose($handle);
    }

    public function show_csv(){
        echo "<a href='/importer'>Home</a>";
        echo "<br><br>";

        if (!File::exists(public_path()."/files/work_orders.csv")) {
            $this->create_csv_file();
        }

        $f = fopen(public_path().'/files/work_orders.csv', 'r');

        while(!feof($f)) {

            $row = fgetcsv($f, 0, ',');

            if (!empty($row)) {
                echo "$row[0],$row[1],$row[2],$row[3],$row[4],$row[5],$row[6] <br>";
            }
        }

        fclose($f);

    }
}
