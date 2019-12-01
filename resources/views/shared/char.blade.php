@if (isset($character))
    <character :character="{{ $character->toJson() }}"></character>
@else
<span missing>
    <span>None</span>
</span>
@endif