<div class="modal fade" id="itemlogModalLabel{{ $itemlog->id }}" tabindex="-1" aria-labelledby="itemlogModalLabel{{ $itemlog->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">


            <div class="modal-body" style="overflow:auto">
            <!-- <div class="modal-body"> -->
                <div class="container-fluid">
                    <div class="row">
                        <div class="col">
                            <!-- <div class="col-md-12">
                            <div class="card mb-3">
                                <div class="row g-0">
                                    <div class="col-md-6"> -->
                            <div class="card-body" style="max-width: 100%; word-wrap: break-word; overflow-wrap: break-word;">
                                <h4 class="card-test">備品ログ</h4><br>
                                <p class="card-test">ID:{{ $itemlog->id }}</p><br>
                                <p class="card-test">備品ID:{{ $itemlog->item_id }}</p><br>
                                <p class="card-test">備品名:{{ $itemlog->item_name }}</p><br>
                                <p class="card-test">保管場所:{{ $itemlog->item_type }}</p><br>
                                <p class="card-test">ユーザー名:{{ $itemlog->user_name }}</p><br>
                                <p class="card-text">所属部署: {{ $itemlog->user_type }}</p><br>
                                <p class="card-text">操作内容: {{ $itemlog->operation }}</p><br>
                                <p class="card-text" style="max-width: 100%; overflow:auto">操作詳細: {{ $itemlog->detail }}</p><br>
                                <p class="card-text">操作日時: {{ $itemlog->updated_at }}</p><br>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
        </div>
    </div>
</div>