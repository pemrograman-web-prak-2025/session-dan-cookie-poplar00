<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

</head>
<body>
    <form action="{{route('regist.process')}}" method="post">
        @csrf
        <label for="usn">username</label>
        <input type="text" name="usn" id="usn" value="{{old('usn')}}">

        @error('usn')
            <span role="alert">{{$message}}</span>
        @enderror

        <label for="email">email</label>
        <input type="mail" name="email" id="email" value="{{old('email')}}">

        
        @error('email')
            <span role="alert">{{$message}}</span>
        @enderror

        <label for="password">password</label>
        <input type="password" name="password" id="password">

        
        @error('password')
            <span role="alert">{{$message}}</span>
        @enderror

        <label for="password_confirmation">Confirm Password</label>
        <input type="password" name="password_confirmation" id="password_confirmation">

        <div>
            <button type="submit">Register</button>
        </div>
    </form>
</body>
</html>