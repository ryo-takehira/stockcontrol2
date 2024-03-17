<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Itemlog;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Facades\ResizeImage;



class ItemController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * 備品管理画面
     */
    public function index()
    {

        // modelのItemから全てのデータを受け取る
        $items = Item::latest('updated_at')->paginate(6);

        $items_all = Item::all();
        // viewのItemにデータを受け渡す



        return view('item.index', compact('items', 'items_all'));
    }

    /**
     * 備品一覧
     */
    public function used_item()
    {

        // modelのItemから全てのデータを受け取る
        $items = Item::latest('updated_at')->paginate(6);
        $items_all = Item::all();
        // viewのItemにデータを受け渡す

        return view('item.used_item', compact('items', 'items_all'));
    }

    /**
     * 備品管理ログ
     */
    public function itemlog()
    {

        // modelのItemから全てのデータを受け取る
        $itemlogs = Itemlog::latest('updated_at')->paginate(6);
        $itemlogs_all = Itemlog::all();
        // viewのItemにデータを受け渡す

        return view('item.itemlog', compact('itemlogs', 'itemlogs_all'));
    }

    /**
     * 備品持出モーダルへ移動後、持出処理
     */
    public function take_out(Request $request, $id)
    {

        // $item = Item::find($id);
        // $items = $request->validate([
        //     'input_take_out' => 'required|integer|max:3',
        // ]);


        $used_quantity = $request->input('take_out');

        $model = Item::find($id);

        $model->update(['stock' => $model->stock - $used_quantity,]);

        Itemlog::create([
            'item_id' => $model['id'],
            'item_name' => $model['name'],
            'item_type' => $model['type'],
            'user_name' => Auth::user()->name,
            'user_type' => Auth::user()->user_type,
            'operation' => "持出",
            'detail' => $used_quantity . $model['stock_unit'] . '持出',
        ]);

        // if ($model->stock < $model->minimum_stock) {
        // ②メール送信に使うインスタンスを生成
        // $NotificationEmail = new NotificationEmail();
        // ③メール送信
        // Mail::send($NotificationEmail);

        // // ⑤送信成功か確認
        // if (count(Mail::failures()) > 0) {
        //     $message = 'メール送信に失敗しました';

        //     // 元の画面に戻る
        //     return back()->withErrors($message);
        // } else {
        //     $messages = 'メールを送信しました';

        //     // 別のページに遷移する
        //     return redirect()->route('hoge')->with(compact('messages'));
        // }
        // }

        // レコードが20件を超えているかどうかを確認
        $recordCount = Itemlog::count();

        if ($recordCount > 100) {
            // レコードを削除
            $recordsToDelete = Itemlog::orderBy('created_at')->take($recordCount - 100)->get();
            foreach ($recordsToDelete as $record) {
                $record->delete();
            }
        }

        // modelのItemから全てのデータを受け取る
        $items = Item::paginate(6);
        // viewのItemにデータを受け渡す

        // return view('item.used_item', compact('items'));

        // ルート/itemsにリダイレクト
        return redirect('/items')->with('usedsuccess', $model['name'] . ' が ' . $used_quantity . $model['stock_unit'] . ' 持出確定されました。');
    }

    /**
     * 備品登録
     */
    public function add(Request $request, Item $items)
    {
        // ユーザーid確認コード
        // dd(Auth::user()->id);

        if ($request->isMethod('post')) {

            $items = $request->validate(
                [
                    'name' => 'required|max:100',
                    'type' => 'required',
                    'image_name' => 'file|mimes:jpg,jpeg,png',
                    'model_no' => 'required|max:100',
                    'order_name' => 'required|max:15',
                    'order_phone' => ['regex:/^0[7-9]0\d{8}$|^0\d{9}$/', 'nullable'],
                    'stock_unit' => 'required|max:50',
                    'stock' => 'required|integer',
                    'minimum_stock' => 'required|integer',
                    'order_quantity' => 'required|integer',
                    'price' => 'required|integer',
                ],
                [
                    'name.required' => '備品名は必須です。',
                    'type.required' => '保管場所を選択してください。',
                    'image_name.mimes:jpg,jpeg,png' => '画像はjpg,jpeg,pngのみ登録できます。',
                    'model_no.required' => '型番、品番は必須です。',
                    'order_name.required' => '発注先は必須です。',
                    'order_phone.regex' => '電話番号ではありません。',
                    'stock_unit.required' => '在庫単位は必須です。',
                    'stock.required' => '在庫数は必須です。',
                    'stock.integer' => '在庫数は数字で入力してください。',
                    'minimum_stock.required' => '最低在庫数は必須です。',
                    'minimum_stock.integer' => '最低在庫数は数字で入力してください。',
                    'order_quantity.required' => '発注数は必須です。',
                    'order_quantity.integer' => '発注数は数字で入力してください。',
                    'price.required' => '単価は必須です。',
                    'price.integer' => '単価は数字で入力してください。',
                ]
            );

            // dd($items);  

            // dd($request->file('image_name'));

            // hasFile メソッドでアップロードファイルの存在を確認
            if ($request->hasFile('image_name')) {

                // ResizeImage::make($request->file('image_name'))->resize(100, 100);

                $image_file = $request->file('image_name');
                // $image_name = $request->file('image_name')->resize(300, 200);

                // ファイル名を取得(ファイル名.拡張子)
                $fileNmae = $image_file->getClientOriginalName();

                // 希望するドライバーで新しいマネージャー インスタンスを作成する
                $manager = new ImageManager(new Driver());

                // 画像のpublic/tmp//$fileNmaeのパスを変数に格納

                $imgPath = public_path('/tmp/' . $fileNmae);

                // dd($imgPath);

                // 希望するドライバーで新しいマネージャーでファイルを読み取る
                $img = $manager->read($image_file);
                // サイズ変更で圧縮
                $img->resize(height: 375, width: 500);

                // ピクセレーション効果で圧縮
                $img = $img->pixelate(0.5);

                // 保存されたファイルパスを取得し変数に格納する
                $path = storage_path('app/public/' . $fileNmae);
                // ファイルを保存する
                $img->save($path);


                // InterventionImage::make($image_name)->resize(1080, 700)->save(public_path('/images/' . $fileNmae ) );;

                // ファイルの名から拡張子のみを取り出す
                $type_name = pathinfo($fileNmae, PATHINFO_EXTENSION);

                // ファイル名をbase64形式でデータのimage_nameに入れる
                $items['image_name'] = 'data:image/' . $type_name . ';base64,' . base64_encode(file_get_contents($path));
                // $items['image_name'] = 'data:image/' . $type_name . ';base64,' . base64_encode(file_get_contents($image_file->path()));

                // アップロードファイルの存在なし 
                // no_image用の画像データ->config(定数);->$itemlists[image_name];へ
            } else {
                $items['image_name'] = config('noimage.no_image');
            }

            // print_r($items['image_name']);
            // exit;


            Item::create([
                'user_id' => Auth::user()->id,
                'name' => $request->name,
                'type' => $request->type,
                'image_name' => $items['image_name'],
                'model_no' => $request->model_no,
                'order_name' => $request->order_name,
                'order_person' => $request->order_person,
                'order_phone' => $request->order_phone,
                'stock_unit' => $request->stock_unit,
                'stock' => $request->stock,
                'minimum_stock' => $request->minimum_stock,
                'order_quantity' => $request->order_quantity,
                'price' => $request->price,
            ]);

            $latestRecord = Item::latest()->first(); // 最新のレコードを取得
            $latestId = $latestRecord->id; // 最新のレコードのIDを取得

            Itemlog::create([
                'item_name' => $request->name,
                'item_id' => $latestId,
                'item_type' => $request->type,
                'user_name' => Auth::user()->name,
                'user_type' => Auth::user()->user_type,
                'operation' => "登録",
                'detail' => $request->name . "を登録",
            ]);

            // レコードが20件を超えているかどうかを確認
            $recordCount = Itemlog::count();

            if ($recordCount > 100) {
                // レコードを削除
                $recordsToDelete = Itemlog::orderBy('created_at')->take($recordCount - 100)->get();
                foreach ($recordsToDelete as $record) {
                    $record->delete();
                }
            }

            Item::latest('updated_at')->paginate(6);

            // 備品管理画面へ
            return redirect('/items/index')->with('itemsuccess', $request['name'] . ' が登録されました。');
        }

        return view('item.add');
    }


    /**
     * 削除
     *
     * @param Item $itemlist
     */

    public function delete(Item $item)
    {

        $item->delete();

        Itemlog::create([
            'item_id' => $item['id'],
            'item_name' => $item['name'],
            'item_type' => $item['type'],
            'user_name' => Auth::user()->name,
            'user_type' => Auth::user()->user_type,
            'operation' => "削除",
            'detail' => $item['name'] . "を削除",
        ]);

        // レコードが20件を超えているかどうかを確認
        $recordCount = Itemlog::count();

        if ($recordCount > 100) {
            // レコードを削除
            $recordsToDelete = Itemlog::orderBy('created_at')->take($recordCount - 100)->get();
            foreach ($recordsToDelete as $record) {
                $record->delete();
            }
        }

        Item::latest('updated_at')->paginate(6);

        return redirect('/items/index')->with('itemsuccess', $item['name'] . ' が削除されました。');
    }



    /**
     * 備品編集
     */
    public function edit(Request $request, Item $item)
    {
        // ユーザーid確認コード
        // dd(Auth::user()->id);

        if ($request->isMethod('post')) {

            $item_id = $request['item_id'];

            // print_r($item_id);
            // exit;

            $itemupdate = $request->validate(
                [
                    'name' => 'required|max:100',
                    'type' => 'required',
                    'image_name' => 'file|mimes:jpg,jpeg,png',
                    'model_no' => 'required|max:100',
                    'order_name' => 'required|max:15',
                    'order_phone' => ['regex:/^0[7-9]0\d{8}$|^0\d{9}$/', 'nullable'],
                    'stock_unit' => 'required|max:50',
                    'stock' => 'required|integer',
                    'minimum_stock' => 'required|integer',
                    'order_quantity' => 'required|integer',
                    'price' => 'required|integer',
                ],
                [
                    'name.required' => '備品名は必須です。',
                    'type.required' => '保管場所を選択してください。',
                    'image_name.mimes:jpg,jpeg,png' => '画像はjpg,jpeg,pngのみ登録できます。',
                    'model_no.required' => '型番、品番は必須です。',
                    'order_name.required' => '発注先は必須です。',
                    'order_phone.regex' => '電話番号ではありません。',
                    'stock_unit.required' => '在庫単位は必須です。',
                    'stock.required' => '在庫数は必須です。',
                    'stock.integer' => '在庫数は数字で入力してください。',
                    'minimum_stock.required' => '最低在庫数は必須です。',
                    'minimum_stock.integer' => '最低在庫数は数字で入力してください。',
                    'order_quantity.required' => '発注数は必須です。',
                    'order_quantity.integer' => '発注数は数字で入力してください。',
                    'price.required' => '単価は必須です。',
                    'price.integer' => '単価は数字で入力してください。',
                ]
            );

            // hasFile メソッドでアップロードファイルの存在を確認
            if ($request->hasFile('image_name')) {

                $image_file = $request->file('image_name');
                // $image_name = $request->file('image_name')->resize(300, 200);

                // ファイル名を取得(ファイル名.拡張子)
                $fileNmae = $image_file->getClientOriginalName();

                // 希望するドライバーで新しいマネージャー インスタンスを作成する
                $manager = new ImageManager(new Driver());

                // 画像のpublic/tmp//$fileNmaeのパスを変数に格納

                // $imgPath = public_path('/tmp/' . $fileNmae);

                // dd($imgPath);

                // 希望するドライバーで新しいマネージャーでファイルを読み取る
                $img = $manager->read($image_file);
                // サイズ変更で圧縮
                $img->resize(height: 375, width: 500);

                // ピクセレーション効果で圧縮
                $img = $img->pixelate(0.5);
                // 保存されたファイルパスを取得し変数に格納する
                $path = storage_path('app/public/' . $fileNmae);
                // ファイルを保存する
                $img->save($path);


                // InterventionImage::make($image_name)->resize(1080, 700)->save(public_path('/images/' . $fileNmae ) );;

                // ファイルの名から拡張子のみを取り出す
                $type_name = pathinfo($fileNmae, PATHINFO_EXTENSION);

                // ファイル名をbase64形式でデータのimage_nameに入れる
                $itemupdate['image_name'] = 'data:image/' . $type_name . ';base64,' . base64_encode(file_get_contents($path));

                // アップロードファイルの存在なし 
                // no_image用の画像データ->config(定数);->$itemlists[image_name];へ
            } else {
                $items['image_name'] = config('noimage.no_image');
            }

            // dd($items);  

            // dd($request->file('image_name'));



            // データ圧縮前のコードここから

            //     if ($request->hasFile('image_name')) {

            //     $image_name = $request->file('image_name');

            //     // ファイル名を取得(ファイル名.拡張子)
            //     $fileNmae = $image_name->getClientOriginalName();

            //     // ファイルの名から拡張子のみを取り出す
            //     $type_name = pathinfo($fileNmae, PATHINFO_EXTENSION);

            //     // ファイル名をbase64形式でデータのimage_nameに入れる
            //     $itemupdate['image_name'] = 'data:image/' . $type_name . ';base64,' . base64_encode(file_get_contents($image_name->path()));

            //     // アップロードファイルの存在なし 
            //     // no_image用の画像データ->config(定数);->$itemlists[image_name];へ
            // } else {
            //     $item['image_name'] = config('noimage.no_image');
            // }

            // データ圧縮前のコードここまで



            // print_r($item);
            // exit;

            // $item_id

            // 変更前の値を取得
            $itemlog_id = Item::find($item_id);

            // attributeの値を確認
            // dd($itemlog_id->name);

            $item_name = $itemlog_id->name;

            $item_type = $itemlog_id->type;
            $item_image_name = $itemlog_id->image_name;
            $item_model_no = $itemlog_id->model_no;
            $item_order_name = $itemlog_id->order_name;
            $item_order_phone = $itemlog_id->order_phone;
            $item_stock_unit = $itemlog_id->stock_unit;
            $item_stock = $itemlog_id->stock;
            $item_minimum_stock = $itemlog_id->minimum_stock;
            $item_order_quantity = $itemlog_id->order_quantity;
            $item_price = $itemlog_id->price;


            $item->where('id', $item_id)->update($itemupdate);

            $detailmsg = "";

            if ($itemlog_id->name <> $request->input('name')) {
                $detailmsg = "【備品名】";
            }

            if ($itemlog_id->type <> $request->input('type')) {
                $detailmsg = $detailmsg . "【保管場所】";
            }


 if (!empty($itemlog_id->image_name)) {
                $detailmsg = $detailmsg . "【画像】";
            }


            if ($itemlog_id->model_no <> $request->input('model_no')) {
                $detailmsg = $detailmsg . "【品番】";
            }

            if ($itemlog_id->order_name <> $request->input('order_name')) {
                $detailmsg = $detailmsg . "【発注先】";
            }

            if ($itemlog_id->order_phone <> $request->input('order_phone')) {
                $detailmsg = $detailmsg . "【発注先電話番号】";
            }

            if ($itemlog_id->stock_unit <> $request->input('stock_unit')) {
                $detailmsg = $detailmsg . "【在庫単位】";
            }

            if ($itemlog_id->stock <> $request->input('stock')) {
                $detailmsg = $detailmsg . "【在庫数】";
            }

            if ($itemlog_id->minimum_stock <> $request->input('minimum_stock')) {
                $detailmsg = $detailmsg . "【最低在庫数】";
            }


            if ($itemlog_id->order_quantity <> $request->input('order_quantity')) {
                $detailmsg = $detailmsg . "【発注数】";
            }

            if ($itemlog_id->price <> $request->input('price')) {
                $detailmsg = $detailmsg . "【単価】";
            }

            if (!empty($detailmsg)) {

                Itemlog::create([
                    'item_id' => $item['id'],
                    'item_name' => $request->input('name'),
                    'item_type' =>  $request->input('type'),
                    'user_name' => Auth::user()->name,
                    'user_type' => Auth::user()->user_type,
                    'operation' => "編集",
                    'detail' => $detailmsg . "を更新",
                ]);


                // レコードが20件を超えているかどうかを確認
                $recordCount = Itemlog::count();

                if ($recordCount > 100) {
                    // レコードを削除
                    $recordsToDelete = Itemlog::orderBy('created_at')->take($recordCount - 100)->get();
                    foreach ($recordsToDelete as $record) {
                        $record->delete();
                    }
                }
            }

            // 更新前更新後の値確認
            // dd($itemlog_id->price,$request->input('price'));

            // 変更内容確認
            // dd($detailmsg);




            Item::latest('updated_at')->paginate(6);

            // 商品管理画面へ
            return redirect('/items/index')->with('itemsuccess', $request['name'] . ' が更新されました。');
        }

        $items_all = Item::all();

        return view('item.edit', compact('item', 'items_all'));
    }




    /**
     * 入庫
     *
     * @param Request $request
     */

    public function storing(Item $item)
    {
        // $itemlistのstockは$itemlistのstock足す$itemlistのorders
        $item->stock = $item->stock + $item->order_quantity;

        Itemlog::create([
            'item_id' => $item['id'],
            'item_name' => $item['name'],
            'item_type' => $item['type'],
            'user_name' => Auth::user()->name,
            'user_type' => Auth::user()->user_type,
            'operation' => "入庫",
            'detail' => $item['order_quantity'] . $item['stock_unit'] . '入庫',
        ]);

        // $itemを更新する
        $item->save();

        // レコードが20件を超えているかどうかを確認
        $recordCount = Itemlog::count();

        if ($recordCount > 100) {
            // レコードを削除
            $recordsToDelete = Itemlog::orderBy('created_at')->take($recordCount - 100)->get();
            foreach ($recordsToDelete as $record) {
                $record->delete();
            }
        }

        Item::latest('updated_at')->paginate(6);

        // 更新後item一覧へ
        return redirect('/items/index')->with('itemsuccess', $item['name'] . ' の在庫が入庫されました。');
    }

    // 備品一覧検索(管理画面)
    public function itemsearch(Request $request)
    {
        $items = Item::all();

        $search = $request->input('search');

        $query = Item::query();

        // $query = $query->paginate($query->count());

        if (!empty($search)) {

            // 全角スペースを半角に変換
            $spaceConversion = mb_convert_kana($search, 's');

            // 単語を半角スペースで区切り、配列にする（例："山田 翔" → ["山田", "翔"]）
            $wordArraySearched = preg_split('/[\s,]+/', $spaceConversion, -1, PREG_SPLIT_NO_EMPTY);

            // 単語をループで回し、ユーザーネームと部分一致するものがあれば、$queryとして保持される
            foreach ($wordArraySearched as $value) {

                $query = Item::where('name', 'like', '%' . $value . '%')
                    ->orWhere('type', 'like', '%' . $value . '%')
                    ->orWhere('model_no', 'like', '%' . $value . '%')
                    ->orWhere('order_name', 'like', '%' . $value . '%');
            }
        }

        $items = $query->latest('updated_at')->paginate(6);

        $items_all = Item::all();

        return view('item.index', compact('items', 'items_all'));
    }



    // 備品一覧検索(ユーザー画面)
    public function used_itemsearch(Request $request)
    {
        $items = Item::all();

        $search = $request->input('used_search');

        $query = Item::query();

        // $query = $query->paginate($query->count());

        if (!empty($search)) {

            // 全角スペースを半角に変換
            $spaceConversion = mb_convert_kana($search, 's');

            // 単語を半角スペースで区切り、配列にする（例："山田 翔" → ["山田", "翔"]）
            $wordArraySearched = preg_split('/[\s,]+/', $spaceConversion, -1, PREG_SPLIT_NO_EMPTY);

            // 単語をループで回し、ユーザーネームと部分一致するものがあれば、$queryとして保持される
            foreach ($wordArraySearched as $value) {

                $query = Item::where('name', 'like', '%' . $value . '%')
                    ->orWhere('type', 'like', '%' . $value . '%')
                    ->orWhere('model_no', 'like', '%' . $value . '%');
            }
        }

        $items = $query->latest('updated_at')->paginate(6);

        $items_all = Item::all();

        return view('item.used_item', compact('items', 'items_all'));
    }


    // 備品管理ログ(検索)
    public function itemlog_search(Request $request)
    {
        $itemlogs = Itemlog::all();

        $search = $request->input('itemlog_search');

        $query = Itemlog::query();

        // $query = $query->paginate($query->count());

        if (!empty($search)) {

            // 全角スペースを半角に変換
            $spaceConversion = mb_convert_kana($search, 's');

            // 単語を半角スペースで区切り、配列にする（例："山田 翔" → ["山田", "翔"]）
            $wordArraySearched = preg_split('/[\s,]+/', $spaceConversion, -1, PREG_SPLIT_NO_EMPTY);

            // 単語をループで回し、ユーザーネームと部分一致するものがあれば、$queryとして保持される
            foreach ($wordArraySearched as $value) {

                $query = Itemlog::where('item_name', 'like', '%' . $value . '%')
                    ->orWhere('item_type', 'like', '%' . $value . '%')
                    ->orWhere('user_name', 'like', '%' . $value . '%')
                    ->orWhere('user_type', 'like', '%' . $value . '%')
                    ->orWhere('operation', 'like', '%' . $value . '%');
            }
        }

        $itemlogs = $query->latest('updated_at')->paginate(6);

        $itemlogs_all = Itemlog::all();

        return view('item.itemlog', compact('itemlogs', 'itemlogs_all'));
    }
}
