<div class="member-list">
    <table class="unit-table">
        <tbody>
            <tr>
            @if (isset($team))
                <td class="header">
                    <div>{{ $team }}</div>
                    <div class="small-note">Power: {{ ($member_characters ?: $member->characters)->whereIn('unit_name', $characters)->sum('power') }}</div>
                </td>
            @endif
            @foreach($characters as $character)
                <td>
                    <div class="team-set">
                    @include('shared.char', [
                        'character' => ($member_characters ?: $member->characters)->firstWhere('unit_name', $character),
                        'base_id' => $character,
                    ])
                    </div>
                </td>
            @endforeach
            </tr>
        </tbody>
    </table>
</div>