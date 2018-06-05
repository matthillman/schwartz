<div class="member-list">
        <table>
            <thead>
                <tr>
                    <th>Membrer</th>
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