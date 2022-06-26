<h1>Reset Password Verification</h1>

<h4><b>Hello {{ $name }},</b></h4>
<p>You are receiving this email because we received a password reset request for your account.</p> <br/>

<a href="http://127.0.0.1:8000/api/auth/emailverification/{{ $token }}" ><button type="button">Reset Password</button></a> <br/>

<p>This password resent link will expire in 60 minutes.</p> <br/>
<p>If you did not request a password reset, no further action is required.</p> <br/>
<p>Regards,</p><br/>
<P>BSCL</P>