<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Response from Dilse Jewels</title>
    <style>
        body { 
            font-family: 'Helvetica Neue', Arial, sans-serif; 
            line-height: 1.6; 
            color: #333; 
            margin: 0;
            padding: 0;
            background-color: #f8f8f8;
        }
        .container { 
            max-width: 650px; 
            margin: 30px auto; 
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .header { 
            background: #14344A; 
            color: white; 
            text-align: center; 
            padding: 40px 20px;
        }
        .header h1 { margin: 0; font-size: 28px; letter-spacing: 1px; }
        .header h2 { margin: 10px 0 0; font-size: 18px; font-weight: normal; color: #d4af37; }

        .content { padding: 30px; }
        .customer-info {
            background: #e9ecef;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 25px;
        }
        .response-box { 
            background: #f1f8ff; 
            padding: 20px; 
            border-left: 4px solid #14344A; 
            margin: 20px 0;
            border-radius: 5px;
        }
        .footer { 
            text-align: center; 
            color: #777; 
            font-size: 12px; 
            padding: 20px;
            border-top: 1px solid #eee;
        }
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background: #14344A;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
            font-weight: bold;
        }
        .btn:hover {
            background: #0f2838;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Dilse Jewels</h1>
            <h2>Your Trusted Jewellery Destination</h2>
        </div>

        <div class="content">
            <div class="customer-info">
                <p><strong>Dear {{ $contact->name }},</strong></p>
                <p>Thank you for reaching out to <strong>Dilse Jewels</strong>. We truly appreciate your interest in our jewellery and your time to connect with us. Below is our response to your recent enquiry.</p>
            </div>

            <div style="margin-bottom: 25px;">
                <h3 style="color: #14344A;">Your Query:</h3>
                <div style="background: #fff; padding: 15px; border-radius: 5px; border: 1px solid #ddd;">
                    <p style="margin: 0; color: #555; font-style: italic;">“{{ $contact->question }}”</p>
                </div>
            </div>

            <div style="margin-bottom: 25px;">
                <h3 style="color: #d4af37;">Our Response:</h3>
                <div class="response-box">
                    <p style="margin: 0; white-space: pre-wrap;">{{ $responseMessage }}</p>
                </div>
            </div>

            <div style="background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;">
                <h4 style="margin: 0 0 10px 0; color: #856404;">Query Summary</h4>
                <p><strong>Topic:</strong> {{ $contact->topic }}</p>
                <p><strong>Email:</strong> {{ $contact->email }}</p>
                @if($contact->phone)
                <p><strong>Phone:</strong> {{ $contact->phone }}</p>
                @endif
                <p><strong>Responded By:</strong> {{ $responderName }}</p>
                <p><strong>Date:</strong> {{ date('F j, Y, g:i a') }}</p>
            </div>

            <div style="text-align: center; margin-top: 35px;">
                <p>If you have any further queries or wish to explore more, feel free to reach out to us anytime.</p>
                <a href="mailto:service@withclarity.com" class="btn">Contact Dilse Jewels</a>
            </div>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Dilse Jewels. All Rights Reserved.</p>
            <p>This is an automated response — please do not reply directly to this email.</p>
            <p><a href="https://dilsejewels.com" style="color: #14344A; text-decoration: none;">Visit our website</a></p>
        </div>
    </div>
</body>
</html>
