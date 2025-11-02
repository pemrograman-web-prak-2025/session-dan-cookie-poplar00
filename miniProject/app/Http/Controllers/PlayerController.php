<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class PlayerController extends Controller
{
    // show register page
    public function register(): View
    {
        return view('register');
    }

    // show login page
    public function login(): View
    {
        return view('login');
    }

    // homepage: show game UI & leaderboard
    public function homepage(): View
    {
        // if not logged in, redirect to register (you requested this behavior)
        if (!Auth::check()) {
            return redirect()->route('register');
        }

        // leaderboard: score desc, id asc (tie-breaker)
        $leaderboard = Player::orderByDesc('score')->orderBy('id', 'asc')->get();

        // current authenticated user
        $user = Auth::user();

        return view('homepage', compact('leaderboard', 'user'));
    }

    // register process
    public function validateRegist(Request $request): RedirectResponse
    {
        $request->validate([
            'usn'=> 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed'
        ]);

        $player = Player::create([
            'username' => $request->usn,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'score' => 0,
        ]);

        // login the user after register (optional, helpful UX)
        Auth::login($player);

        return redirect()->route('homepage');
    }

    // login process
    public function validateLogin(Request $request) : RedirectResponse
    {
        $request->validate([
            'usn'=> 'required',
            'password' => 'required'
        ]);

        $credentials = [
            'username' => $request->usn,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // ✅ Tambahan: buat cookie manual untuk "remember me"
            if ($request->filled('remember')) {
                // simpan cookie selama 7 hari (10080 menit)
                cookie()->queue('remembered_user', $request->usn, 60 * 24 * 7);
            } else {
                // hapus cookie kalau checkbox tidak dicentang
                cookie()->queue(cookie()->forget('remembered_user'));
            }

            return redirect()->route('homepage');
        }

        return back()->withErrors(['usn' => 'Username atau password salah']);
    }


    // logout
    public function logout(): RedirectResponse
    {
        Session::flush();
        Auth::logout();

        return redirect()->route('register');
    }

    //
    // Game logic: Rock Paper Scissors (AJAX)
    //
    public function playRPS(Request $request)
    {
        // ensure authenticated
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'pick' => 'required|in:rock,paper,scissors'
        ]);

        $user = Auth::user();

        // cpu random pick
        $options = ['rock', 'paper', 'scissors'];
        $cpu = $options[array_rand($options)];

        $pick = $request->pick;

        // rules: return 'win'|'lose'|'draw'
        $result = 'draw';
        if ($pick === $cpu) {
            $result = 'draw';
        } elseif (
            ($pick === 'rock' && $cpu === 'scissors') ||
            ($pick === 'scissors' && $cpu === 'paper') ||
            ($pick === 'paper' && $cpu === 'rock')
        ) {
            $result = 'win';
        } else {
            $result = 'lose';
        }

        // scoring: win +50, lose -25, draw 0
        $delta = 0;
        if ($result === 'win') $delta = 50;
        if ($result === 'lose') $delta = -25;

        $user->score = $user->score + $delta; // negative allowed per your choice
        $user->save();

        // return JSON response
        return response()->json([
            'result' => $result,
            'cpu' => $cpu,
            'delta' => $delta,
            'new_score' => $user->score,
        ]);
    }

    //
    // CRUD for players (simple admin-style)
    //
    public function index(): View
    {
        $players = Player::orderByDesc('score')->orderBy('id', 'asc')->paginate(20);
        return view('players.index', compact('players'));
    }

    public function create(): View
    {
        return view('players.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => 'required|unique:players,username',
            'email' => 'required|email|unique:players,email',
            'password' => 'nullable|confirmed'
        ]);

        $data = $request->only('username','email');
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        } else {
            $data['password'] = Hash::make('password'); // default fallback
        }
        $data['score'] = $request->input('score', 0);

        Player::create($data);

        return redirect()->route('players.index');
    }

    public function edit(Player $player): View
    {
        return view('players.edit', compact('player'));
    }

    public function update(Request $request, Player $player): RedirectResponse
    {
        $request->validate([
            'username' => 'required|unique:players,username,'.$player->id,
            'email' => 'required|email|unique:players,email,'.$player->id,
        ]);

        $player->username = $request->username;
        $player->email = $request->email;
        if ($request->filled('password')) {
            $player->password = Hash::make($request->password);
        }
        $player->score = $request->input('score', $player->score);
        $player->save();

        return redirect()->route('players.index');
    }

    public function destroy(Player $player): RedirectResponse
    {
        $player->delete();
        return redirect()->route('players.index');
    }

    public function deleteAccount(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // logout dulu biar session aman
        Auth::logout();

        // delete from db
        $user->delete();

        // flush session
        Session::flush();

        return redirect()->route('register');
    }

}
