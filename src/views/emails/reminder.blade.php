@if (!isset($create))

<table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin: 0px; padding: 0px; line-height: 1px; font-size: 1px;">
  <tr>
    <td style="background-color: #fff;"><img src="{{URL::to("/")}}/packages/bpez/infuse/images/spacer.gif" width="5" height="1" alt="" border="0" /></td>
    <td width="400" align="center" valign="top" style="width: 400px; background-color: #fff;">
      <table cellspacing="0" cellpadding="0" border="0" align="center" width="400" style="margin: 0px; padding: 0px; line-height: 1px; font-size: 1px;">
        <tr><td height="15" width="400"><img src="{{URL::to("/")}}/packages/bpez/infuse/images/spacer.gif" width="1" height="1" alt="" border="0" /></td></tr>
        <tr><td width="400" style="text-align: left;">
          <table cellspacing="0" cellpadding="0" border="0" width="400" >
            <tr><td width="400" style="background-color: #242527">
                <div style="height: 25px; width: 100%;"><img src="{{URL::to("/")}}/packages/bpez/infuse/images/spacer.gif" width="1" height="25" alt="" border="0" /></div>
                <div style="width: 100%; text-align: center;"><img src="{{URL::to("/")}}/packages/bpez/infuse/images/infuseLogo.png" alt="" border="0" width="145" height="33" /></div>
                <div style="height: 25px; width: 100%;"><img src="{{URL::to("/")}}/packages/bpez/infuse/images/spacer.gif" width="1" height="25" alt="" border="0" /></div>
            </td></tr>
            <tr><td width="400" style="background-color: #EEE;">
                <div style="height: 25px; width: 100%;"><img src="{{URL::to("/")}}/packages/bpez/infuse/images/spacer.gif" width="1" height="25" alt="" border="0" /></div>
                <div style="text-align: center; font-size: 16px; font-family: Arial; line-height: 22px; color: #073354; font-weight: bold;">
                    
                    <div style="text-align: center; font-size: 16px; font-family: Arial; line-height: 18px; color: #242527;">[Infuse] Password Reset</div>
                    <div style="text-align: center; font-size: 15px; font-family: Arial; font-weight: normal; line-height: 18px; color: #242527;">To reset your password, complete this form:</div>
                    <a href="{{ URL::to('password/reset', array($token)) }}" target="_blank" style="color:#428BCA; text-decoration:none; font-size: 15px;  font-weight: normal; tect-decoration:underline;">Reset password</a>

                </div>
                <div style="height: 25px; width: 100%;"><img src="{{URL::to("/")}}/packages/bpez/infuse/images/spacer.gif" width="1" height="25" alt="" border="0" /></div>
            </td></tr>
          </table>
      </td>
    </tr>
    <tr><td><img src="{{URL::to("/")}}/packages/bpez/infuse/images/spacer.gif" width="1" height="25" border="0" /></td></tr>
    </table>
  </td>
  <td style="background-color: #fff;"><img src="{{URL::to("/")}}/packages/bpez/infuse/images/spacer.gif" width="5" height="1" border="0" /></td>
</tr>
</table>




@else

<table width="100%" cellspacing="0" cellpadding="0" border="0" style="margin: 0px; padding: 0px; line-height: 1px; font-size: 1px;">
  <tr>
    <td style="background-color: #fff;"><img src="{{URL::to("/")}}/packages/bpez/infuse/images/spacer.gif" width="5" height="1" alt="" border="0" /></td>
    <td width="400" align="center" valign="top" style="width: 400px; background-color: #fff;">
      <table cellspacing="0" cellpadding="0" border="0" align="center" width="400" style="margin: 0px; padding: 0px; line-height: 1px; font-size: 1px;">
        <tr><td height="15" width="400"><img src="{{URL::to("/")}}/packages/bpez/infuse/images/spacer.gif" width="1" height="1" alt="" border="0" /></td></tr>
        <tr><td width="400" style="text-align: left;">
          <table cellspacing="0" cellpadding="0" border="0" width="400" >
            <tr><td width="400" style="background-color: #242527">
                <div style="height: 25px; width: 100%;"><img src="{{URL::to("/")}}/packages/bpez/infuse/images/spacer.gif" width="1" height="25" alt="" border="0" /></div>
                <div style="width: 100%; text-align: center;"><img src="{{URL::to("/")}}/packages/bpez/infuse/images/infuseLogo.png" alt="" border="0" width="145" height="33" /></div>
                <div style="height: 25px; width: 100%;"><img src="{{URL::to("/")}}/packages/bpez/infuse/images/spacer.gif" width="1" height="25" alt="" border="0" /></div>
            </td></tr>
            <tr><td width="400" style="background-color: #EEE;">
                <div style="height: 25px; width: 100%;"><img src="{{URL::to("/")}}/packages/bpez/infuse/images/spacer.gif" width="1" height="25" alt="" border="0" /></div>
                <div style="text-align: center; font-size: 16px; font-family: Arial; line-height: 22px; color: #073354; font-weight: bold;">
                    
                    <div style="text-align: center; font-size: 16px; font-family: Arial; line-height: 18px; color: #242527;">[Infuse] User Account Created</div>
                    <div style="text-align: center; font-size: 15px; font-family: Arial; font-weight: normal; line-height: 18px; color: #242527;">Username: {{$username}} {{(($full_name != "")? "({$full_name})" : "")}}</div>
                    <a href="{{ URL::to('password/reset', array($token)) }}" target="_blank" style="color:#428BCA; text-decoration:none; font-size: 15px;  font-weight: normal; tect-decoration:underline;">Create password</a>

                </div>
                <div style="height: 25px; width: 100%;"><img src="{{URL::to("/")}}/packages/bpez/infuse/images/spacer.gif" width="1" height="25" alt="" border="0" /></div>
            </td></tr>
          </table>
      </td>
    </tr>
    <tr><td><img src="{{URL::to("/")}}/packages/bpez/infuse/images/spacer.gif" width="1" height="25" border="0" /></td></tr>
    </table>
  </td>
  <td style="background-color: #fff;"><img src="{{URL::to("/")}}/packages/bpez/infuse/images/spacer.gif" width="5" height="1" border="0" /></td>
</tr>
</table>

@endif