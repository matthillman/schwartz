<div class="member-list">
    <table class="unit-table">
        <tbody>
            <tr>
                <td>{{ $team }}</td>
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