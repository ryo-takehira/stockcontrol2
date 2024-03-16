@extends('adminlte::page')



@section('title', '備品管理ログ')

@section('content_header')
<div class="d-flex">
    <h4 class="titlename">備品管理ログ</h4>

</div>

    @stop

    @section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <!-- 検索窓 -->
                    <div class="mb-2">
                        <div class="input-group input-group-sm">
                            <div class="d-flex">
                                <form class="d-flex text-align-center" action="{{ url('/items/itemlog_search') }}" method="get">
                                    @csrf
                                    <input class="search-window" type="text" name="itemlog_search" placeholder="検索キーワード" value=''>
                                    <button type="submit" class="search-button ms-4">検索</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive p-0">
                        <table class="table text-center text-nowrap">
                            <thead>
                                <tr>
                                    <th class="fixed01">ID</th>
                                    <th class="fixed01">備品ID</th>
                                    <th class="fixed01">備品名</th>
                                    <th class="fixed02">保管場所</th>
                                    <th class="fixed02">ユーザー名</th>
                                    <th class="fixed02">所属部署</th>
                                    <th class="fixed02">操作</th>
                                    <th class="fixed02">詳細</th>
                                    <th class="fixed02">更新日時</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($itemlogs as $itemlog)
                                <tr>
                                    <td class="align-middle">{{ $itemlog->id }}</td>
                                    <td class="align-middle">{{ $itemlog->item_id }}</td>
                                    <td class="align-middle fixed02 word-break">{{ $itemlog->item_name }}</td>
                                    <td class="align-middle">{{ $itemlog->item_type }}</td>
                                    <td class="align-middle">{{ $itemlog->user_name }}</td>
                                    <td class="align-middle word-break">{{ $itemlog->user_type }}</td>
                                    <td class="align-middle">{{ $itemlog->operation}}</td>
                                    <td class="align-middle word-break-detail">{{ $itemlog->detail}}</td>
                                    <td class="align-middle">{{ $itemlog->updated_at}}</td>
                    </div>
                    </tr>
                    @endforeach
                    </tbody>
                    </table>
                </div>

                <div class="page-link">
                    <div class="row">
                        <div class="col-6">
                        {{ $itemlogs->appends(request()->query())->links('pagination::bootstrap-5')}}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>


    <!-- jquery読込 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // ページロード後に実行されるコード

            // メッセージが存在する場合
            if ($('#successMessage').length) {
                // メッセージを3秒後にフェードアウト
                setTimeout(function() {
                    $('#successMessage').fadeOut('slow');
                }, 3000);
            }
        });
    </script>
    @stop

    @section('css')

    <link href="{{ asset('/css/reset.css') }}" rel="stylesheet">

    <!-- オリジナルstylecssファイル -->
    <link href="{{ asset('/css/item.css') }}" rel="stylesheet">

    @stop

    @section('js')
    <!-- Bootstrap JavaScriptのリンク -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @stop