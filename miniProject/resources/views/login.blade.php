<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <title>Login</title>
</head>
<body>
    <form action="{{ route('login.process') }}" method="post">
        @csrf
        <label for="usn">username</label>
        <input type="text" name="usn" id="usn" 
            value="{{ old('usn', request()->cookie('remembered_user')) }}">

        @error('usn')
            <span role="alert">{{ $message }}</span>
        @enderror

        <label for="password">password</label>
        <input type="password" name="password" id="password">

        @error('password')
            <span role="alert">{{ $message }}</span>
        @enderror

        <!-- Tambahan Remember Me -->
        <div style="display:flex; align-items:center; gap:8px;">
            <input type="checkbox" name="remember" id="remember" 
                {{ request()->cookie('remembered_user') ? 'checked' : '' }}>
            <label for="remember">Ingatkan saya / Remember me</label>
        </div>

        <div>
            <button type="submit">Login</button>
        </div>
    </form>

</body>
</html>