@if (session('status'))
<div class="card">
    <div class="card-body">
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    </div>
</div>
@endif
@if (session('status-failed'))
<div class="card">
    <div class="card-body">
        <div class="alert alert-danger">
            {{ session('status-failed') }}
        </div>
    </div>
</div>
@endif