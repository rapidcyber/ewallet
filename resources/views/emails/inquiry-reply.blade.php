<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Repay - Inquiry Reply</title>
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
                            <h1 style="text-transform:uppercase;font-size:24px;font-weight:700;color:#ffff;margin:0;padding:0;">thank you for your inquiry!</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:44px;color:#647887;">
                            <p style="padding:0;">Hi {{ $name }},</p>
                            <p style="padding:0;">
                                Thank you for reaching out to us! We’ve reviewed your inquiry, and we’re glad to provide you with the information you need.
                            </p>
                            <br>
                            <p style="padding:0;">
                                {!! $content !!}
                            </p>
                            <br>
                            <p style="padding:0;">
                                If there’s anything further we can assist you with or if you have additional questions, please feel free to let us know. We’re here to help!
                            </p>
                            <p style="margin:0;padding:0;">Best regards,</p>
                            <p style="margin:0;padding:0;">The RePay Team</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#9479D2;border-bottom-left-radius:24px;border-bottom-right-radius:24px;height:20px;">
                        </td>
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
