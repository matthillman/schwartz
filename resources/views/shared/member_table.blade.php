<div class="member-list">
    <table>
        <thead>
            <tr>
                <th>Member</th>
            @foreach($characters as $character)
                <th>
                    {{ $units->firstWhere('base_id', $character)->name }}
                </th>
            @endforeach
            </tr>
        </thead>
        <tbody>
    @foreach($members as $member)
        <tr>
            <td>
                <a href="https://swgoh.gg{{ $member->url }}" target="_gg">
                    {{ $member->player }}
                </a>
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
    @endforeach
        </tbody>
    </table>
</div>