
<head>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
</head>
<body>
    <h1>Reset Password Verification</h1>
    <h4>Hello <b>{{ $name }}</b>,</h4>
    <p>You are receiving this email because we received a password reset request for your account.</p>

    <a href="http://localhost:3000/forget-pass/new-password/{{ $token }}" ><button class="btn btn-info" type="button">Reset Password</button></a> <br/>

    <p>This password resent link will expire in 10 minutes.</p>
    <p>If you did not request a password reset, no further action is required.</p> <br/>
    <p>Regards,</p>
    <P>BSCL</P>
</body>