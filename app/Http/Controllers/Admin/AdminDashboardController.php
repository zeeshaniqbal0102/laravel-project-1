<?php namespace App\Http\Controllers\Admin;

use App\Helpers\MyHelper;
use Input, Auth, View, Excel, Request;
use App\Http\Controllers\BaseController;
use App\User;
use App\DB\Giftcards;

class AdminDashboardController extends BaseController
{
    private $scope = 'giftcards';
    private $interval = 6;
    public $pagination_limit = 5;

    public function getIndex()
    {
//        dd('load');
        return $this->listAction();
//        return View::make('admin.dashboard.index');
    }

    /**
     * Return Lists to the view
     * @return mixed
     */
    public function listAction()
    {
        $data = $this->getGiftcards();

        $cardCode = $this->getCollection(Giftcards::select('card_id', 'card_code')->orderBy('card_id', 'desc')->get(),
            'card_code');

        return View::make("admin.dashboard.index_more", array(
            "lists" => $data,
            "scope" => $this->scope,
            "url" => Request::url(),
            "search_arr" => ['card_code' => "Card Code", 'mail_from' => 'Mail From', 'mail_to' => 'Mail To'],
            "search_key" => isset($this->search_key) ? $this->search_key : '',
            "search_by" => isset($this->search_by) ? $this->search_by : '',
            "cardCodeCollection" => $cardCode
        ));
    }

    /**
     * Returns giftcard lists according to url params
     * @return mixed
     */
    protected function getGiftcards()
    {
        $this->search_by = (Input::get('search-by')) ? Input::get('search-by') : '';
        $this->search_key = (Input::get('search-key')) ? Input::get('search-key') : '';

        $search_key = trim(request('search-key'));

        switch (Input::get('search-by')) {
            case 'card_code':
                $data = Giftcards::select('card_id', 'card_code', 'card_status', 'mail_from_email', 'mail_to_email',
                    'created_time')
                    ->where('card_code', 'like', "%$search_key%")
                    ->orderBy('card_id', 'desc')
                    ->paginate($this->pagination_limit);
                break;

            case 'mail_from':
                $data = Giftcards::select('card_id', 'card_code', 'card_status', 'mail_from_email', 'mail_to_email',
                    'created_time')
                    ->where('mail_from_email', 'like', "%$search_key%")
                    ->orderBy('card_id', 'desc')
                    ->paginate($this->pagination_limit);
                break;

            case 'mail_to':
                $data = Giftcards::select('card_id', 'card_code', 'card_status', 'mail_from_email', 'mail_to_email',
                    'created_time')
                    ->where('mail_to_email', 'like', "%$search_key%")
                    ->orderBy('card_id', 'desc')
                    ->paginate($this->pagination_limit);
                break;

            default:
                $data = Giftcards::orderBy('card_id', 'desc')->paginate($this->pagination_limit);
        }

        return $data;
    }

    public function getCollection($dataCollection = array(), $arg = 'card_code')
    {
        $coll = array();
        $i = 0;
        foreach ($dataCollection as $code) {
            $coll[] = $code->$arg;
            $i++;
        }


        return json_encode($coll);
    }
}