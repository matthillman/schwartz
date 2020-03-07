@if (session('status'))
<div class="card">
    <div class="card-body">
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    </div>
</div>
@endif