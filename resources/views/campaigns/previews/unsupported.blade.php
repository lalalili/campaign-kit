<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Campaign Preview Unsupported</title>
    <style>
        html, body {
            margin: 0;
            min-height: 100%;
            font-family: "Noto Sans TC", sans-serif;
            background: #f3f4f6;
            color: #1f2937;
        }

        .preview-unsupported {
            display: grid;
            place-items: center;
            min-height: 100vh;
            padding: 24px;
        }

        .preview-unsupported__card {
            width: 100%;
            max-width: 520px;
            border: 1px solid #d1d5db;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 10px 25px -15px rgba(0, 0, 0, 0.25);
            padding: 20px;
        }

        .preview-unsupported__title {
            margin: 0 0 8px;
            font-size: 1.125rem;
            font-weight: 700;
        }

        .preview-unsupported__desc {
            margin: 0;
            line-height: 1.7;
            font-size: 0.95rem;
            color: #4b5563;
        }
    </style>
</head>
<body>
<div class="campaign-preview-root preview-unsupported">
    <div class="preview-unsupported__card">
        <h1 class="preview-unsupported__title">Preview is not available for this type</h1>
        <p class="preview-unsupported__desc">
            type={{ $type }}, variant={{ $variant }}, reason={{ $reason }}
        </p>
    </div>
</div>
</body>
</html>
