<team-sort
    units="{{ $units->filter(function($u) use ($characters) { return in_array($u->base_id, $characters); })->values()->toJson() }}"
    members="{{ $members->map(function($m) use ($characters) { return $m->characterSet($characters); })->toJson() }}"
>
</team-sort>