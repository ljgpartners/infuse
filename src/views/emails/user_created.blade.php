<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable">
    <tr>
        <td align="center" valign="top">
            <table border="0" cellpadding="20" cellspacing="0" width="600" id="emailContainer">
                <tr>
                    <td align="center" valign="top">
                        <h1>[Infuse]  New User Created</h1>
                        <p>Username: {{$username}} {{(($full_name != "")? "({$full_name})" : "")}}</p>
                        <p>Please click the following link to create your password for your account.</p>
                        <table border="0" cellpadding="0" cellspacing="0" style="background-color:#505050; border:1px solid #353535; border-radius:5px;">
											    <tr>
											        <td align="center" valign="middle" style="color:#FFFFFF; font-family:Helvetica, Arial, sans-serif; font-size:16px; font-weight:bold; letter-spacing:-.5px; line-height:150%; padding-top:15px; padding-right:30px; padding-bottom:15px; padding-left:30px;">
											            <a href="{{ URL::to('password/remind') }}" target="_blank" style="color:#FFFFFF; text-decoration:none;">Go to password reset</a>
											        </td>
											    </tr>
											</table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>