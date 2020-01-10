<div class="member-list">
    <table class="unit-table">
        <tbody>
            <tr>
                <td class="header">
                    <div>{{ $arena }}</div>
                    <div class="small-note">Current Rank: {{ $rank }}</div>
                </td>
            @foreach($team as $character)
                <td>
                    <div class="team-set">
                    @include('shared.char', [
                        'character' => $member->characters->firstWhere('unit_name', $character['defId']),
                        'noStats' => true,
                        'member' => $member,
                    ])
                    </div>
                </td>
            @endforeach
            </tr>
        </tbody>
    </table>
</div>