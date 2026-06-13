{{-- resources/views/mail/admin-panel-reply.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 15px;
            line-height: 1.7;
            color: #1e293b;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
        }
        .wrapper {
            width: 100%;
            background-color: #f4f7fa;
            padding: 32px 0;
        }
        .container {
            max-width: 620px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .header {
            background-color: #00466a;
            padding: 20px 32px;
            color: #ffffff;
            font-size: 18px;
            font-weight: 600;
        }
        .body {
            padding: 28px 32px 32px;
        }
        .body p   { margin: 0 0 14px; }
        .body ul,
        .body ol  { margin: 0 0 14px; padding-left: 22px; }
        .body li  { margin-bottom: 6px; }
        .body strong { font-weight: 700; }
        .body em     { font-style: italic; }
        .body h1, .body h2, .body h3 {
            margin: 20px 0 10px;
            line-height: 1.3;
        }
        .footer {
            background-color: #f1f5f9;
            border-top: 1px solid #e2e8f0;
            padding: 14px 32px;
            font-size: 12px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">Global Trading</div>
            <div class="body">
                {!! $body !!}
            </div>
            <div class="footer">
                This message was sent via the Global Trading admin panel.
            </div>
        </div>
    </div>
</body>
</html>