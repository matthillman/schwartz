<div class="member-list">
    <table>
        <thead>
            <tr>
            @foreach($characters as $character)
                <th>
                    {{ $units->firstWhere('base_id', $character)->name }}
                </th>
            @endforeach
            </tr>
        </thead>
        <tbody>
            <tr>
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