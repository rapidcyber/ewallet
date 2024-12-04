<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    {{-- <title>{{ $email->subject }}</title> --}}
    {{-- <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet"> --}}
</head>    

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
</style>

<body style="margin:0;padding:0;background-color:#F5F6F8;">
    <table width="100%" style="font-family: 'Poppins', Arial, sans-serif;max-width:500px;word-break:break-word;margin:0 auto;">
        <tr>
            <td style="text-align:center;padding:12px 0;">
                <a href="{{ route('home') }}">
                    <img src="{{ asset('images/repay-logo-colored.svg')}}" alt="Repay Logo">
                </a>
            </td>
        </tr>
        <tr>
            <td style="margin:0;padding:0;">
                <table width="100%" style="padding:0;background-color:#ffff;border-radius:24px;">
                    <tr>
                        <td style="text-align:center;padding:16px;background-color:#9479D2;border-top-left-radius:24px;border-top-right-radius:24px;">
                            <h1 style="text-transform:uppercase;font-size:24px;font-weight:700;color:#ffff;margin:0;padding:0;">your repay account</h1>
                            <h1 style="text-transform:uppercase;font-size:24px;font-weight:700;color:#ffff;margin:0;padding:0;">has been created!</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:44px;color:#647887;">
                            <p style="padding:0;">Hi {{ $name }},</p>
                            <p style="padding:0;">We’re excited to inform you that a RePay account has been created on your behalf!
                            </p>
                            <p style="padding:0;">What’s next?
                            </p>
                            <p style="padding:0;">You can now log in and explore your new RePay account, where you can easily track your finances, make payments, and manage your digital wallet. Here are your login details:
                            </p>
                            <p style="margin:0;padding:0;">Phone Number: {{ $phone_number }}
                            </p>
                            <p style="margin:0;padding:0;">Email: {{ $email }}
                            </p>
                            <p style="margin:0;padding:0;">Temporary Password: {{ $temp_password }}</p>
                            <p style="margin:0;padding:0;">PIN: {{ $pin }}</p>
                            <p style="padding:0;">To access your account, click the link below and change your password for security purposes.
                            </p>
                            <a href="{{ route('login') }}" style="display:block;text-transform:uppercase;text-decoration:none;color:#ffff;padding:10px;background-color:#7F56D9;font-weight: 700;border-radius:9px;text-align:center;">log-in</a>
                            <p style="padding:0;">If you have any questions or need assistance, feel free to contact our support team at <a href="mailto:solutions@repay.ph" style="color:inherit">solutions@repay.ph</a>.
                            </p>
                            <p style="padding:0;">Welcome to RePay – where managing your finances just got easier!
                            </p>
                            <p style="margin:0;padding:0;">Best regards,
                            </p>
                            <p style="margin:0;padding:0;">The RePay Team</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#9479D2;border-bottom-left-radius:24px;border-bottom-right-radius:24px;height:20px;">
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <table width="100%" style="padding:10px 0 0 0;font-size:12px;color:#647887;text-align:center;">
                    <tr>
                        <td>
                            <a href="{{ route('home') }}">
                                <img src="{{ asset('images/repay-logo-colored.svg')}}" style="width:100px;" alt="Repay Logo">
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p style="margin:0;padding:0;">We’d love to hear from you!</p>
                            <p  style="margin:0;padding:0;">If you have any questions, please don’t hesitate to <a href="{{ route('contact-us') }}" style="color:inherit;font-weight:700;">contact us.</a></p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <a href="https://www.linkedin.com/company/repay-digital-solutions/" target="_blank" style="padding: 0 2px;text-align:center;font-size:12px;display:inline-block;">
                                <img src="{{ asset('images/email/linkedin.png') }}"
                                    style="max-width:100%;height:auto;" alt="LinkedIn Logo" />
                            </a>
                            <a href="https://www.facebook.com/repayph" target="_blank" style="padding: 0 2px;text-align:center;font-size:12px;display:inline-block;">
                                <img src="{{ asset('images/email/facebook.png') }}"
                                    style="max-width:100%;height:auto;" alt="Facebook logo" />
                            </a>
                            <a href="https://www.tiktok.com/@repayph" target="_blank" style="padding: 0 2px;text-align:center;font-size:12px;display:inline-block;">
                                <img src="{{ asset('images/email/tiktok.png') }}"
                                    style="max-width:100%;height:auto;" alt="TikTok logo" />
                            </a>
                            <a href="https://www.instagram.com/repay_ph?igsh=MWI0dzFkcG1yYXNxdA==" target="_blank" style="padding: 0 2px;text-align:center;font-size:12px;display:inline-block;">
                                <img src="{{ asset('images/email/instagram.png') }}"
                                    style="max-width:100%;height:auto;" alt="Instragram Logo" />
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table style="margin:0 auto;text-align:center;padding:0 0 16px 0;">
                                <tr>
                                    <td style="padding: 0 5px 0 0;">
                                        <a href="{{ route('terms-and-conditions') }}"
                                            style="font-size:12px;text-decoration:none;color:#898E97;">Terms</a>
                                    </td>
                                    
                                    <td
                                        style="border-left: 1px solid rgba(0, 0, 0, 0.30);height: 100%;padding:0;">
            
                                    </td>
            
                                    <td style="padding: 0 5px;">
                                        <a href="{{ route('privacy-policy') }}"
                                            style="font-size:12px;text-decoration:none;color:#898E97;">Privacy
                                            Policy</a>
                                    </td>
            
                                    <td style="border-left: 1px solid rgba(0, 0, 0, 0.30);height: 100%;">
            
                                    </td>
            
                                    <td style="padding: 0 5px;">
                                        <a href="{{ route('about-us') }}"
                                            style="font-size:12px;text-decoration:none;color:#898E97;">About
                                            Us</a>
                                    </td>
            
                                    <td style="border-left: 1px solid rgba(0, 0, 0, 0.30);height: 100%;">
            
                                    </td>
            
                                    <td style="padding: 0 0 0 5px;">
                                        <a href="{{ route('contact-us') }}"
                                            style="font-size:12px;text-decoration:none;color:#898E97;">Contact
                                            Us</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p>This email was sent to <strong>{{ $email }}</strong></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>