<div class="member-list">
    <table class="unit-table">
        <tbody>
            <tr>
                <td>
                    <div>{{ $team }}</div>
                    <div class="small-note">Power: {{ $member->characters->whereIn('unit_name', $characters)->sum('power') }}</div>
                </td>
            @foreach($characters as $character)
                <td>
                    <div class="team-set">
                    @include('shared.char', [
                        'character' => $member->characters->firstWhere('unit_name', $character),
                    ])
                    </div>
                </td>
            @endforeach
            </tr>
        </tbody>
    </table>
</div>