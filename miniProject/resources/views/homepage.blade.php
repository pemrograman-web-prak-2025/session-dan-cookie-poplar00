<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage - RPS</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

</head>
<body>
    <h1>WELCOME</h1>

    <div>
        <form id="logoutForm" action="{{ route('logout') }}" method="get" style="display:inline;">
            <button type="submit">out</button>
        </form>
    </div>

    <hr>

    <div class="game">
        <h2>Play Rock Paper Scissors</h2>
        <p>Hello, <strong>{{ $user->username }}</strong> — Score: <span id="user-score">{{ $user->score }}</span></p>

        <div>
            <button class="btn" data-pick="rock">Rock</button>
            <button class="btn" data-pick="paper">Paper</button>
            <button class="btn" data-pick="scissors">Scissors</button>
        </div>

        <div class="result" id="game-result" aria-live="polite"></div>
    </div>

    <hr>

    <div class="leaderboard">
        <h2>Leaderboard</h2>
        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Username</th>
                    <th>Score</th>
                </tr>
            </thead>
            <tbody id="leaderboard-body">
                @foreach($leaderboard as $idx => $p)
                <tr data-player-id="{{ $p->id }}">
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $p->username }}</td>
                    <td class="score-cell">{{ $p->score }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <form action="{{ route('account.delete') }}" method="POST" style="display:inline;">
        @csrf
        @method('DELETE')
        <button type="submit" onclick="return confirm('Yakin hapus akun? Semua data hilang dan tidak bisa dikembalikan!')">
            Delete Account
        </button>
    </form>


    <script>
        // CSRF token
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // helper to update leaderboard row for current user (simple approach)
        function updateUserRow(userId, newScore) {
            const rows = document.querySelectorAll('#leaderboard-body tr');
            let found = false;
            rows.forEach(row => {
                if (row.dataset.playerId == userId) {
                    row.querySelector('.score-cell').textContent = newScore;
                    found = true;
                }
            });

            // if not found (e.g. score improved and moves up), we simply update current user's score cell shown above.
            // For a more robust leaderboard reorder you'd want to re-fetch the leaderboard from server.
        }

        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const pick = this.dataset.pick;

                fetch("{{ route('game.play') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ pick })
                })
                .then(res => {
                    if (!res.ok) throw res;
                    return res.json();
                })
                .then(data => {
                    // expected response: { result, cpu, delta, new_score }
                    const resultEl = document.getElementById('game-result');
                    let text = `CPU: ${data.cpu.toUpperCase()} — `;
                    if (data.result === 'win') text += `YOU WIN (+${data.delta})`;
                    else if (data.result === 'lose') text += `YOU LOSE (${data.delta})`;
                    else text += 'DRAW';

                    resultEl.textContent = text;

                    // update displayed user score
                    document.getElementById('user-score').textContent = data.new_score;

                    // update leaderboard row if present
                    const userId = "{{ $user->id }}";
                    updateUserRow(userId, data.new_score);

                    // optionally: highlight result
                    resultEl.style.color = data.result === 'win' ? 'green' : (data.result === 'lose' ? 'red' : 'black');
                })
                .catch(async err => {
                    // try to parse json error
                    let message = 'Request failed';
                    try {
                        const json = await err.json();
                        message = json.message || (json.error ?? JSON.stringify(json));
                    } catch (e) {
                        message = 'Request failed';
                    }
                    document.getElementById('game-result').textContent = message;
                    document.getElementById('game-result').style.color = 'red';
                });
            });
        });
    </script>
</body>
</html>
